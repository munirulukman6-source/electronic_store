<?php
// admin/flash_sales/index.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireSales();

$db = Database::getInstance();

// Handle create flash sale before layout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = post('action');
    if ($action === 'create_flash') {
        $product = $db->fetchOne("SELECT price FROM products WHERE id = ?", [(int)post('product_id')]);
        $salePrice = (float)post('sale_price');
        $discPct   = $product ? round((1 - $salePrice / $product['price']) * 100, 2) : 0;
        $db->insert('flash_sales', [
            'product_id'     => (int)post('product_id'),
            'title'          => post('title'),
            'sale_price'     => $salePrice,
            'original_price' => $product['price'] ?? $salePrice,
            'discount_pct'   => $discPct,
            'start_time'     => post('start_time'),
            'end_time'       => post('end_time'),
            'qty_limit'      => post('qty_limit') ?: null,
            'status'         => 'active',
            'created_by'     => $_SESSION['user_id'],
        ]);
        setFlash('success', 'Flash sale created!');
        redirect(BASE_URL . 'admin/flash_sales/');
    }
}

$pageTitle  = 'Flash Sales';
$breadcrumb = [['label' => 'Flash Sales', 'active' => true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';

$sales    = $db->fetchAll(
    "SELECT fs.*, p.product_name, p.slug AS product_slug,
            pi.image_path AS product_image, u.full_name AS created_by_name
     FROM flash_sales fs
     JOIN products p ON fs.product_id = p.id
     LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
     LEFT JOIN users u ON fs.created_by = u.id
     ORDER BY fs.created_at DESC"
);
$products = $db->fetchAll("SELECT id, product_name, price, sku FROM products WHERE status='active' ORDER BY product_name");
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-bolt me-2 text-warning"></i>Flash Sales</h4><p>Time-limited deals with countdown timers</p></div>
    <button class="btn btn-warning btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addFlashModal">
        <i class="fas fa-plus me-1"></i>New Flash Sale
    </button>
</div>

<!-- Active sales stats -->
<?php
$activeCount  = count(array_filter($sales, fn($s) => $s['status'] === 'active' && strtotime($s['end_time']) > time()));
$totalRevenue = array_sum(array_column(array_filter($sales, fn($s) => $s['sold_count'] > 0), 'sold_count'));
?>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="kpi-card kpi-warning"><div class="kpi-icon"><i class="fas fa-bolt"></i></div><div class="kpi-info"><div class="kpi-value"><?= $activeCount ?></div><div class="kpi-label">Active Sales</div></div></div></div>
    <div class="col-md-3"><div class="kpi-card kpi-success"><div class="kpi-icon"><i class="fas fa-fire"></i></div><div class="kpi-info"><div class="kpi-value"><?= count($sales) ?></div><div class="kpi-label">Total Sales Created</div></div></div></div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Product</th><th>Title</th><th>Sale Price</th><th>Discount</th><th>Period</th><th>Sold / Limit</th><th>Status</th><th class="no-sort text-end">Actions</th></tr></thead>
                <tbody>
                    <?php if (empty($sales)): ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-bolt fa-3x opacity-25 mb-3 d-block"></i>No flash sales yet.</td></tr>
                    <?php else: foreach ($sales as $s):
                        $now     = time();
                        $start   = strtotime($s['start_time']);
                        $end     = strtotime($s['end_time']);
                        $isLive  = $s['status']==='active' && $now>=$start && $now<=$end;
                        $isEnded = $now > $end;
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= productImageUrl($s['product_image'] ?? null, '', $s['product_name']) ?>"
                                     width="40" height="40" class="rounded" style="object-fit:cover">
                                <span class="small fw-semibold"><?= truncate(clean($s['product_name']), 28) ?></span>
                            </div>
                        </td>
                        <td class="small"><?= clean($s['title'] ?? '—') ?></td>
                        <td>
                            <div class="fw-bold text-danger"><?= formatPrice($s['sale_price']) ?></div>
                            <small class="text-muted text-decoration-line-through"><?= formatPrice($s['original_price']) ?></small>
                        </td>
                        <td><span class="badge bg-danger">-<?= number_format($s['discount_pct'], 1) ?>%</span></td>
                        <td>
                            <div class="small"><?= formatDateTime($s['start_time']) ?></div>
                            <div class="small text-muted">to <?= formatDateTime($s['end_time']) ?></div>
                            <?php if ($isLive): ?>
                                <span class="badge bg-success mt-1">LIVE</span>
                            <?php elseif ($isEnded): ?>
                                <span class="badge bg-secondary mt-1">Ended</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark mt-1">Scheduled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="small fw-bold"><?= number_format($s['sold_count']) ?></div>
                            <?php if ($s['qty_limit']): ?>
                                <div class="progress mt-1" style="height:4px;width:80px">
                                    <div class="progress-bar bg-danger" style="width:<?= min(100, $s['sold_count']/$s['qty_limit']*100) ?>%"></div>
                                </div>
                                <small class="text-muted">of <?= $s['qty_limit'] ?></small>
                            <?php else: ?>
                                <small class="text-muted">No limit</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php $sc = ['active'=>'success','inactive'=>'secondary','ended'=>'danger']; ?>
                            <span class="badge bg-<?= $sc[$s['status']] ?? 'secondary' ?>"><?= ucfirst($s['status']) ?></span>
                        </td>
                        <td class="text-end">
                            <button onclick="adminAction('toggle_flash_sale',<?= $s['id'] ?>,'Toggle this flash sale?')"
                                    class="btn btn-xs btn-outline-<?= $s['status']==='active'?'warning':'success' ?>">
                                <i class="fas fa-<?= $s['status']==='active'?'pause':'play' ?>"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Flash Sale Modal -->
<div class="modal fade" id="addFlashModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create_flash">
                <div class="modal-header border-0 bg-warning bg-opacity-10">
                    <h5 class="modal-title fw-bold text-warning"><i class="fas fa-bolt me-2"></i>New Flash Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Product *</label>
                            <select name="product_id" class="form-select select2" required>
                                <option value="">— Select Product —</option>
                                <?php foreach ($products as $prod): ?>
                                    <option value="<?= $prod['id'] ?>" data-price="<?= $prod['price'] ?>">
                                        <?= clean($prod['product_name']) ?> — <?= formatPrice($prod['price']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Flash Sale Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Weekend Special — 20% Off!">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Flash Sale Price (<?= DEFAULT_CURRENCY_SYMBOL ?>) *</label>
                            <input type="number" name="sale_price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity Limit</label>
                            <input type="number" name="qty_limit" class="form-control" min="1" placeholder="Unlimited">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Date &amp; Time *</label>
                            <input type="datetime-local" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date &amp; Time *</label>
                            <input type="datetime-local" name="end_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-semibold"><i class="fas fa-bolt me-2"></i>Launch Flash Sale</button>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';</script>
</body></html>
