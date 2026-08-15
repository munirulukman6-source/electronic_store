<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';

$pageTitle  = 'Stock In';
$breadcrumb = [['label'=>'Inventory','url'=>BASE_URL.'admin/inventory/'],['label'=>'Stock In','active'=>true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
requireRole('admin','inventory_manager');

$inventoryModel = new Inventory();
$suppliers      = $inventoryModel->getSuppliers();
$db2            = Database::getInstance();
$products       = $db2->fetchAll("SELECT id, product_name, sku, quantity, cost_price FROM products WHERE status='active' ORDER BY product_name");
$preselect      = (int)get('product_id', 0);
$errors         = [];

if ($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrf()) {
    $productId = (int)post('product_id');
    $qty       = (int)post('quantity');
    $cost      = (float)(post('unit_cost') ?: 0);
    $suppId    = post('supplier_id') ?: null;
    $ref       = post('reference_no') ?: '';
    $notes     = post('notes') ?: '';

    if (!$productId) $errors[] = 'Please select a product.';
    if ($qty < 1)    $errors[] = 'Quantity must be at least 1.';

    if (empty($errors)) {
        $result = $inventoryModel->stockIn($productId, $qty, $cost, $suppId ? (int)$suppId : null, $ref, $notes);
        if ($result['success']) {
            setFlash('success', $result['message']);
            redirect(BASE_URL.'admin/inventory/stock_in.php');
        }
        $errors[] = $result['message'];
    }
}

// Recent stock-in entries
$recent = $db2->fetchAll(
    "SELECT i.*, p.product_name, p.sku, s.supplier_name, u.full_name AS recorded_by
     FROM inventory i JOIN products p ON i.product_id=p.id
     LEFT JOIN suppliers s ON i.supplier_id=s.id
     LEFT JOIN users u ON i.user_id=u.id
     WHERE i.transaction_type='stock_in'
     ORDER BY i.created_at DESC LIMIT 15"
);
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-arrow-circle-down me-2 text-success"></i>Stock In</h4><p>Record new stock deliveries and purchases</p></div>
    <a href="<?= BASE_URL ?>admin/inventory/" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Stock in form -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 fw-semibold bg-success bg-opacity-10">
                <i class="fas fa-plus-circle me-2 text-success"></i>Record Stock Entry
            </div>
            <div class="card-body">
                <form method="POST" novalidate>
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Product *</label>
                        <select name="product_id" class="form-select select2" required>
                            <option value="">— Select Product —</option>
                            <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $preselect==$p['id']?'selected':'' ?>>
                                <?= clean($p['product_name']) ?> (<?= clean($p['sku']??'') ?>) — Current: <?= $p['quantity'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Quantity *</label>
                            <input type="number" name="quantity" class="form-control" min="1" required placeholder="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Unit Cost (<?= DEFAULT_CURRENCY_SYMBOL ?>)</label>
                            <input type="number" name="unit_cost" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">None / Direct</option>
                            <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= clean($s['supplier_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference / Delivery Note No.</label>
                        <input type="text" name="reference_no" class="form-control" placeholder="e.g. DN-20241001-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Batch Number</label>
                        <input type="text" name="batch_number" class="form-control" placeholder="Optional batch or lot number">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes about this delivery…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                        <i class="fas fa-save me-2"></i>Record Stock Entry
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Recent entries -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 fw-semibold"><i class="fas fa-history me-2"></i>Recent Stock Entries</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.83rem">
                        <thead class="table-light"><tr><th>Date</th><th>Product</th><th class="text-center">Qty</th><th class="text-center">Before</th><th class="text-center">After</th><th>Reference</th><th>By</th></tr></thead>
                        <tbody>
                            <?php if (empty($recent)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No entries yet.</td></tr>
                            <?php else: foreach ($recent as $r): ?>
                            <tr>
                                <td><small><?= formatDateTime($r['created_at']) ?></small></td>
                                <td>
                                    <div class="fw-semibold"><?= truncate(clean($r['product_name']),28) ?></div>
                                    <small class="text-muted"><?= clean($r['supplier_name']??'—') ?></small>
                                </td>
                                <td class="text-center fw-bold text-success">+<?= number_format($r['quantity']) ?></td>
                                <td class="text-center text-muted"><?= $r['previous_stock'] ?></td>
                                <td class="text-center fw-semibold"><?= $r['new_stock'] ?></td>
                                <td><code class="small"><?= clean($r['reference_no']??'—') ?></code></td>
                                <td class="small text-muted"><?= clean($r['recorded_by']??'System') ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
                <a href="<?= BASE_URL ?>admin/inventory/" class="small text-primary">View full history →</a>
            </div>
        </div>
    </div>
</div>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>const BASE_URL='<?= BASE_URL ?>'; const CSRF_TOKEN='<?= csrfToken() ?>';</script>
</body></html>