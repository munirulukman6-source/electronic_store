<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'classes/Order.php';
require_once ROOT_PATH . 'classes/Cart.php';
require_once ROOT_PATH . 'classes/Inventory.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireRole('admin','inventory_manager');

$inventoryModel = new Inventory();
$lowStock       = $inventoryModel->getLowStockProducts();
$suppliers      = $inventoryModel->getSuppliers();

// ── Process POST (bulk restock) BEFORE any HTML output ──────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    if (post('action') === 'bulk_restock') {
        $ids  = $_POST['product_ids'] ?? [];
        $qty  = (int)post('quantity', 10);
        $done = 0;
        foreach ($ids as $pid) {
            $inventoryModel->stockIn((int)$pid, $qty, 0, null, 'BULK-' . date('Ymd'), 'Bulk restock from low-stock alert');
            $done++;
        }
        setFlash('success', "Restocked $done product(s) with $qty units each.");
        redirect(BASE_URL . 'admin/inventory/low_stock.php');
    }
}

// ── Page setup (only for GET or POST with errors) ───────────────
$pageTitle  = 'Low Stock Alerts';
$breadcrumb = [
    ['label' => 'Inventory', 'url' => BASE_URL . 'admin/inventory/'],
    ['label' => 'Low Stock', 'active' => true],
];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';

// Ensure $lowStock is an array
if (!is_array($lowStock)) {
    $lowStock = [];
}
$outCount = count(array_filter($lowStock, fn($p) => $p['quantity'] == 0));
$lowCount = count(array_filter($lowStock, fn($p) => $p['quantity'] > 0 && $p['quantity'] <= $p['low_stock_alert']));
?>

<!-- The rest of your HTML form and table remain exactly as before -->
<!-- ... (the exact same HTML you already have) ... -->

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Low Stock Alerts</h4>
        <p><?= count($lowStock) ?> product<?= count($lowStock)!=1?'s':'' ?> need restocking</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="exportReport('low_stock','<?= date('Y-m-01') ?>','<?= date('Y-m-d') ?>')" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-excel me-1"></i>Export
        </button>
        <a href="<?= BASE_URL ?>admin/inventory/" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<!-- Summary -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card kpi-danger">
            <div class="kpi-icon"><i class="fas fa-times-circle"></i></div>
            <div class="kpi-info"><div class="kpi-value"><?= $outCount ?></div><div class="kpi-label">Out of Stock</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card kpi-warning">
            <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="kpi-info"><div class="kpi-value"><?= $lowCount ?></div><div class="kpi-label">Critically Low</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card kpi-primary">
            <div class="kpi-icon"><i class="fas fa-list"></i></div>
            <div class="kpi-info"><div class="kpi-value"><?= count($lowStock) ?></div><div class="kpi-label">Total Affected</div></div>
        </div>
    </div>
</div>

<?php if (empty($lowStock)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="fas fa-check-circle fa-4x text-success opacity-50 mb-3"></i>
        <h5 class="text-success">All products are well-stocked!</h5>
        <p class="text-muted">No items currently below their alert threshold.</p>
        <a href="<?= BASE_URL ?>admin/inventory/" class="btn btn-primary mt-2">View Inventory</a>
    </div>
</div>
<?php else: ?>

<!-- Bulk restock form -->
<div class="card border-0 shadow-sm mb-3 border-start border-primary border-3">
    <div class="card-body py-2">
        <form method="POST" class="d-flex gap-3 align-items-center flex-wrap">
            <?= csrfField() ?><input type="hidden" name="action" value="bulk_restock">
            <span class="fw-semibold small">Bulk Restock Selected:</span>
            <div class="input-group" style="max-width:200px">
                <input type="number" name="quantity" class="form-control form-control-sm" value="10" min="1" placeholder="Units each">
                <span class="input-group-text">units</span>
            </div>
            <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                <i class="fas fa-arrow-circle-down me-1"></i>Restock Selected
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllBtn">Select All</button>
        </form>
    </div>
</div>

<!-- Low stock table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px"><input type="checkbox" class="form-check-input" id="masterCheck"></th>
                        <th>Product</th>
                        <th>Category / Brand</th>
                        <th>Supplier</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Alert Level</th>
                        <th class="text-center">Deficit</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lowStock as $p):
                        $isOut   = $p['quantity'] == 0;
                        $deficit = max(0, ($p['low_stock_alert'] * 2) - $p['quantity']);
                        $pct     = $p['low_stock_alert'] > 0 ? min(100, ($p['quantity'] / $p['low_stock_alert']) * 100) : 0;
                    ?>
                    <tr class="<?= $isOut ? 'table-danger bg-opacity-25' : 'table-warning bg-opacity-10' ?>">
                        <td>
                            <input type="checkbox" class="form-check-input row-check" name="product_ids[]" value="<?= $p['id'] ?>" form="bulkForm">
                        </td>
                        <td>
                            <div class="fw-semibold"><?= truncate(clean($p['product_name']),40) ?></div>
                            <code class="small text-muted"><?= clean($p['sku']??'') ?></code>
                        </td>
                        <td>
                            <div class="small"><?= clean($p['category_name']) ?></div>
                            <small class="text-muted"><?= clean($p['brand_name']) ?></small>
                        </td>
                        <td class="small text-muted"><?= clean($p['supplier_name']??'—') ?></td>
                        <td class="text-center">
                            <div class="fw-bold fs-5 <?= $isOut ? 'text-danger' : 'text-warning' ?>">
                                <?= $p['quantity'] ?>
                            </div>
                            <div class="progress mt-1 mx-auto" style="height:4px;width:60px">
                                <div class="progress-bar <?= $isOut ? 'bg-danger' : 'bg-warning' ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                        </td>
                        <td class="text-center text-muted"><?= $p['low_stock_alert'] ?> units</td>
                        <td class="text-center">
                            <span class="badge bg-<?= $isOut ? 'danger' : 'warning text-dark' ?>">+<?= $deficit ?> needed</span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-success fw-semibold"
                                    data-bs-toggle="modal" data-bs-target="#stockInModal"
                                    onclick="prefillStockIn(<?= $p['id'] ?>,'<?= addslashes(clean($p['product_name'])) ?>')">
                                <i class="fas fa-plus-circle me-1"></i>Restock
                            </button>
                            <a href="<?= BASE_URL ?>admin/products/edit.php?id=<?= $p['id'] ?>"
                               class="btn btn-sm btn-outline-secondary ms-1">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Stock In Modal (reuse from inventory/index.php) -->
<div class="modal fade" id="stockInModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?= BASE_URL ?>admin/inventory/">
                <?= csrfField() ?><input type="hidden" name="action" value="stock_in">
                <div class="modal-header border-0 bg-success bg-opacity-10">
                    <h5 class="modal-title fw-bold text-success"><i class="fas fa-arrow-circle-down me-2"></i>Stock In</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" id="stockInProductName" class="form-control" readonly>
                        <input type="hidden" name="product_id" id="stockInProductId">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="form-label">Quantity *</label><input type="number" name="quantity" class="form-control" min="1" required placeholder="0"></div>
                        <div class="col-6"><label class="form-label">Unit Cost (<?= DEFAULT_CURRENCY_SYMBOL ?>)</label><input type="number" name="unit_cost" class="form-control" step="0.01" min="0" placeholder="0.00"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">None</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= clean($s['supplier_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-0"><label class="form-label">Reference No.</label><input type="text" name="reference_no" class="form-control" placeholder="e.g. DN-20241001"></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-semibold"><i class="fas fa-save me-2"></i>Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endif; ?>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>
const BASE_URL='<?= BASE_URL ?>'; const CSRF_TOKEN='<?= csrfToken() ?>';
function prefillStockIn(id, name) {
    document.getElementById('stockInProductId').value   = id;
    document.getElementById('stockInProductName').value = name;
}
document.getElementById('masterCheck')?.addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
});
document.getElementById('selectAllBtn')?.addEventListener('click', function() {
    document.querySelectorAll('.row-check').forEach(c => c.checked = true);
    document.getElementById('masterCheck').checked = true;
});
function exportReport(t,f,to){window.open(`${BASE_URL}admin/reports/export.php?type=${t}&from=${f}&to=${to}&csrf_token=${CSRF_TOKEN}`,'_blank');}
</script>
</body></html>