<?php
$pageTitle  = 'Inventory';
$breadcrumb = [['label' => 'Inventory', 'active' => true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
requireRole('admin', 'inventory_manager');

$inventoryModel = new Inventory();

// Handle Stock In POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = post('action');
    if ($action === 'stock_in') {
        $result = $inventoryModel->stockIn(
            (int)post('product_id'),
            (int)post('quantity'),
            (float)(post('unit_cost') ?: 0),
            post('supplier_id') ? (int)post('supplier_id') : null,
            post('reference_no'),
            post('notes')
        );
        setFlash($result['success'] ? 'success' : 'danger', $result['message']);
        redirect($_SERVER['REQUEST_URI']);
    }
    if ($action === 'adjust') {
        $result = $inventoryModel->adjust((int)post('product_id'), (int)post('new_quantity'), post('reason'));
        setFlash($result['success'] ? 'success' : 'danger', $result['message']);
        redirect($_SERVER['REQUEST_URI']);
    }
}

$db       = Database::getInstance();
$filters  = ['type' => get('type',''), 'date_from' => get('date_from',''), 'date_to' => get('date_to','')];
$page     = max(1,(int)get('page',1));
$history  = $inventoryModel->getAllHistory($filters, $page);
$lowStock = $inventoryModel->getLowStockProducts();
$stockVal = $inventoryModel->getStockValue();
$suppliers= $inventoryModel->getSuppliers();
$products = $db->fetchAll("SELECT id, product_name, sku, quantity FROM products WHERE status='active' ORDER BY product_name");
?>

<!-- KPI row -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-warehouse me-2 text-warning"></i>Inventory Management</h4><p>Monitor stock levels and record transactions</p></div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#stockInModal"><i class="fas fa-arrow-circle-down me-1"></i>Stock In</button>
        <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#adjustModal"><i class="fas fa-sliders-h me-1"></i>Adjust</button>
        <button onclick="exportReport('inventory','<?= date('Y-m-01') ?>','<?= date('Y-m-d') ?>')" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i>Export</button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="kpi-card kpi-primary"><div class="kpi-icon"><i class="fas fa-boxes"></i></div><div class="kpi-info"><div class="kpi-value"><?= number_format($stockVal['total_units'] ?? 0) ?></div><div class="kpi-label">Total Units</div></div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="kpi-card kpi-success"><div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div><div class="kpi-info"><div class="kpi-value"><?= formatPrice($stockVal['total_retail_value'] ?? 0) ?></div><div class="kpi-label">Retail Value</div></div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="kpi-card kpi-warning"><div class="kpi-icon"><i class="fas fa-chart-line"></i></div><div class="kpi-info"><div class="kpi-value"><?= formatPrice($stockVal['total_cost_value'] ?? 0) ?></div><div class="kpi-label">Cost Value</div></div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="kpi-card kpi-danger"><div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div><div class="kpi-info"><div class="kpi-value"><?= count($lowStock) ?></div><div class="kpi-label">Low Stock Items</div></div></div>
    </div>
</div>

<!-- Low Stock Alerts -->
<?php if (!empty($lowStock)): ?>
<div class="card border-0 shadow-sm mb-3 border-start border-danger border-3">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alerts (<?= count($lowStock) ?>)</h6>
        <a href="<?= BASE_URL ?>admin/inventory/low_stock.php" class="btn btn-sm btn-outline-danger">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Product</th><th>Category</th><th>Brand</th><th>Current Stock</th><th>Alert Level</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach (array_slice($lowStock, 0, 8) as $ls): ?>
                    <tr>
                        <td class="fw-semibold small"><?= truncate(clean($ls['product_name']), 38) ?></td>
                        <td class="small text-muted"><?= clean($ls['category_name']) ?></td>
                        <td class="small text-muted"><?= clean($ls['brand_name']) ?></td>
                        <td><span class="fw-bold <?= $ls['quantity'] == 0 ? 'text-danger' : 'text-warning' ?>"><?= $ls['quantity'] ?></span></td>
                        <td class="text-muted small"><?= $ls['low_stock_alert'] ?> units</td>
                        <td>
                            <button onclick="prefillStockIn(<?= $ls['id'] ?>, '<?= addslashes(clean($ls['product_name'])) ?>')"
                                    class="btn btn-xs btn-success" data-bs-toggle="modal" data-bs-target="#stockInModal">
                                <i class="fas fa-plus me-1"></i>Restock
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Transaction History -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="fas fa-history me-2"></i>Transaction History</h6>
        <form method="GET" class="d-flex gap-2">
            <select name="type" class="form-select form-select-sm" style="width:140px">
                <option value="">All Types</option>
                <?php foreach (['stock_in','stock_out','adjustment','return','damage'] as $t): ?>
                    <option value="<?= $t ?>" <?= $filters['type']===$t?'selected':'' ?>><?= ucwords(str_replace('_',' ',$t)) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_from" class="form-control form-control-sm" value="<?= $filters['date_from'] ?>">
            <input type="date" name="date_to"   class="form-control form-control-sm" value="<?= $filters['date_to'] ?>">
            <button class="btn btn-primary btn-sm px-3"><i class="fas fa-filter"></i></button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Date</th><th>Product</th><th>Type</th><th class="text-center">Qty</th><th class="text-center">Before</th><th class="text-center">After</th><th>Reference</th><th>Supplier</th><th>Recorded By</th></tr></thead>
                <tbody>
                    <?php foreach ($history['data'] as $h):
                        $typeColors = ['stock_in'=>'success','stock_out'=>'danger','adjustment'=>'warning','return'=>'info','damage'=>'dark'];
                        $typeColor  = $typeColors[$h['transaction_type']] ?? 'secondary';
                    ?>
                    <tr>
                        <td><small><?= formatDateTime($h['created_at']) ?></small></td>
                        <td>
                            <div class="small fw-semibold"><?= truncate(clean($h['product_name']),32) ?></div>
                            <code class="x-small text-muted"><?= clean($h['sku'] ?? '') ?></code>
                        </td>
                        <td><span class="badge bg-<?= $typeColor ?>"><?= ucwords(str_replace('_',' ',$h['transaction_type'])) ?></span></td>
                        <td class="text-center fw-bold <?= $h['transaction_type']==='stock_in'?'text-success':'text-danger' ?>">
                            <?= $h['transaction_type']==='stock_in'?'+':'-' ?><?= abs($h['quantity']) ?>
                        </td>
                        <td class="text-center text-muted"><?= $h['previous_stock'] ?></td>
                        <td class="text-center fw-semibold"><?= $h['new_stock'] ?></td>
                        <td><code class="small"><?= clean($h['reference_no'] ?? '—') ?></code></td>
                        <td class="small text-muted"><?= clean($h['supplier_name'] ?? '—') ?></td>
                        <td class="small text-muted"><?= clean($h['recorded_by'] ?? 'System') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($history['last_page'] > 1): ?>
    <div class="card-footer bg-transparent border-0 py-2">
        <?= paginationLinks($history, BASE_URL.'admin/inventory/?type='.$filters['type']) ?>
    </div>
    <?php endif; ?>
</div>

<!-- Stock In Modal -->
<div class="modal fade" id="stockInModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="stock_in">
                <div class="modal-header border-0 bg-success bg-opacity-10">
                    <h5 class="modal-title fw-bold text-success"><i class="fas fa-arrow-circle-down me-2"></i>Stock In</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product *</label>
                        <select name="product_id" id="stockInProduct" class="form-select select2" required>
                            <option value="">— Select Product —</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= clean($p['product_name']) ?> (Current: <?= $p['quantity'] ?>)</option>
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
                            <option value="">None</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= clean($s['supplier_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference / Delivery Note No.</label>
                        <input type="text" name="reference_no" class="form-control" placeholder="e.g. DN-20241001">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes about this stock entry…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-semibold"><i class="fas fa-save me-2"></i>Save Stock Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Adjust Modal -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="adjust">
                <div class="modal-header border-0 bg-warning bg-opacity-10">
                    <h5 class="modal-title fw-bold text-warning"><i class="fas fa-sliders-h me-2"></i>Inventory Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product *</label>
                        <select name="product_id" class="form-select select2" required>
                            <option value="">— Select Product —</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= clean($p['product_name']) ?> (Current: <?= $p['quantity'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Quantity *</label>
                        <input type="number" name="new_quantity" class="form-control" min="0" required placeholder="Set exact quantity">
                    </div>
                    <div>
                        <label class="form-label">Reason *</label>
                        <textarea name="reason" class="form-control" rows="2" required placeholder="e.g. Physical count, damaged goods, theft…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-semibold"><i class="fas fa-save me-2"></i>Apply Adjustment</button>
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
<script>
const BASE_URL='<?= BASE_URL ?>'; const CSRF_TOKEN='<?= csrfToken() ?>';
function prefillStockIn(id, name){
    const sel = document.getElementById('stockInProduct');
    if(sel) { for(let o of sel.options) if(o.value==id){o.selected=true;break;} if(window.$)$(sel).trigger('change'); }
}
</script>
</body></html>
