<?php
// admin/bundles/index.php
$pageTitle  = 'Product Bundles';
$breadcrumb = [['label'=>'Bundles','active'=>true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
requireStaff();

if ($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrf()) {
    $action = post('action');

    if ($action==='create_bundle') {
        $name  = post('bundle_name');
        $slug  = slugify($name);
        $base  = $slug; $n=1;
        while ($db->count('product_bundles','slug=?',[$slug])) $slug=$base.'-'.$n++;

        $productIds = array_filter(array_map('intval', $_POST['bundle_products']??[]));
        $origPrice  = 0;
        foreach ($productIds as $pid) {
            $price = $db->fetchColumn("SELECT price FROM products WHERE id=?", [$pid]);
            $origPrice += (float)$price;
        }
        $bundlePrice = (float)post('bundle_price');
        $discPct     = $origPrice > 0 ? round((1 - $bundlePrice/$origPrice)*100, 2) : 0;

        $bundleId = $db->insert('product_bundles', [
            'bundle_name'      => $name,
            'slug'             => $slug,
            'description'      => post('description'),
            'original_price'   => $origPrice,
            'bundle_price'     => $bundlePrice,
            'discount_pct'     => $discPct,
            'is_featured'      => isset($_POST['is_featured'])?1:0,
            'quantity_available'=> (int)post('quantity_available',0),
            'status'           => 1,
        ]);

        foreach ($productIds as $pid) {
            $db->insert('bundle_items', ['bundle_id'=>$bundleId,'product_id'=>$pid,'quantity'=>1]);
        }
        setFlash('success','Bundle created successfully!');
        redirect(BASE_URL.'admin/bundles/');
    }

    if ($action==='toggle_bundle') {
        $id  = (int)post('id');
        $cur = $db->fetchColumn("SELECT status FROM product_bundles WHERE id=?",[$id]);
        $db->update('product_bundles',['status'=>$cur?0:1],'id=?',[$id]);
        setFlash('success','Bundle status updated.');
        redirect(BASE_URL.'admin/bundles/');
    }

    if ($action==='delete_bundle') {
        $id = (int)post('id');
        $db->delete('bundle_items','bundle_id=?',[$id]);
        $db->delete('product_bundles','id=?',[$id]);
        setFlash('success','Bundle deleted.');
        redirect(BASE_URL.'admin/bundles/');
    }
}

$bundles  = $db->fetchAll(
    "SELECT pb.*,
            GROUP_CONCAT(p.product_name ORDER BY p.product_name SEPARATOR ' + ') AS product_names,
            COUNT(bi.id) AS item_count
     FROM product_bundles pb
     LEFT JOIN bundle_items bi ON pb.id=bi.bundle_id
     LEFT JOIN products p ON bi.product_id=p.id
     GROUP BY pb.id ORDER BY pb.created_at DESC"
);
$products = $db->fetchAll("SELECT id,product_name,sku,price FROM products WHERE status='active' ORDER BY product_name");
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-layer-group me-2 text-primary"></i>Product Bundles</h4><p>Sell multiple products together at a discounted price</p></div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBundleModal">
        <i class="fas fa-plus me-1"></i>Create Bundle
    </button>
</div>

<?php if (empty($bundles)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="fas fa-layer-group fa-4x text-muted opacity-25 mb-3"></i>
        <h5 class="text-muted">No bundles yet</h5>
        <p class="text-muted">Create your first product bundle to offer customers great value deals.</p>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBundleModal">Create First Bundle</button>
    </div>
</div>
<?php else: ?>

<div class="row g-3">
    <?php foreach ($bundles as $b):
        $sc = $b['status'] ? 'success' : 'secondary';
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-<?= $sc ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0"><?= clean($b['bundle_name']) ?></h6>
                    <span class="badge bg-<?= $sc ?>"><?= $b['status']?'Active':'Inactive' ?></span>
                </div>
                <?php if ($b['description']): ?>
                <p class="text-muted small mb-2"><?= truncate(clean($b['description']),80) ?></p>
                <?php endif; ?>
                <div class="small mb-2">
                    <i class="fas fa-box text-primary me-1"></i>
                    <strong><?= $b['item_count'] ?></strong> products:
                    <span class="text-muted"><?= truncate(clean($b['product_names']??''), 60) ?></span>
                </div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div>
                        <div class="fw-bold fs-5 text-primary"><?= formatPrice($b['bundle_price']) ?></div>
                        <div class="text-muted text-decoration-line-through small"><?= formatPrice($b['original_price']) ?></div>
                    </div>
                    <span class="badge bg-success fs-6">Save <?= number_format($b['discount_pct'],1) ?>%</span>
                </div>
                <?php if ($b['is_featured']): ?>
                <div class="small mb-2"><i class="fas fa-star text-warning me-1"></i>Featured on homepage</div>
                <?php endif; ?>
                <div class="small text-muted mb-3">
                    <i class="fas fa-boxes me-1"></i>
                    <?= $b['quantity_available'] > 0 ? number_format($b['quantity_available']).' available' : 'Unlimited' ?>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 d-flex gap-2 pb-3">
                <a href="<?= BASE_URL ?>bundle/<?= clean($b['slug']) ?>" target="_blank" class="btn btn-xs btn-light flex-fill">
                    <i class="fas fa-eye me-1"></i>Preview
                </a>
                <form method="POST" class="flex-fill">
                    <?= csrfField() ?><input type="hidden" name="action" value="toggle_bundle"><input type="hidden" name="id" value="<?= $b['id'] ?>">
                    <button class="btn btn-xs btn-outline-<?= $b['status']?'warning':'success' ?> w-100">
                        <i class="fas fa-<?= $b['status']?'pause':'play' ?> me-1"></i><?= $b['status']?'Disable':'Enable' ?>
                    </button>
                </form>
                <form method="POST" onsubmit="return confirm('Delete this bundle?')">
                    <?= csrfField() ?><input type="hidden" name="action" value="delete_bundle"><input type="hidden" name="id" value="<?= $b['id'] ?>">
                    <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Add Bundle Modal -->
<div class="modal fade" id="addBundleModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <?= csrfField() ?><input type="hidden" name="action" value="create_bundle">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="fas fa-layer-group me-2"></i>Create Bundle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Bundle Name *</label>
                            <input type="text" name="bundle_name" class="form-control" placeholder="e.g. Mobile Power Pack" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Products to Include *</label>
                            <select name="bundle_products[]" class="form-select select2" multiple required style="height:160px">
                                <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>">
                                    <?= clean($p['product_name']) ?> (<?= formatPrice($p['price']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Hold Ctrl/Cmd to select multiple products</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bundle Price (<?= DEFAULT_CURRENCY_SYMBOL ?>) *</label>
                            <input type="number" name="bundle_price" class="form-control" step="0.01" min="0" required placeholder="0.00">
                            <div class="form-text">Should be less than the sum of individual prices</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity Available</label>
                            <input type="number" name="quantity_available" class="form-control" min="0" value="0" placeholder="0 = unlimited">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Describe the value of this bundle…"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_featured" class="form-check-input" id="bundleFeat">
                                <label class="form-check-label" for="bundleFeat"><i class="fas fa-star text-warning me-1"></i>Feature on homepage</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-save me-2"></i>Create Bundle</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';</script>
</body></html>
