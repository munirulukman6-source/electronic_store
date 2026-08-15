<?php
// admin/reports/inventory.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireInventory();

$pageTitle  = 'Inventory Report';
$breadcrumb = [['label'=>'Reports','url'=>BASE_URL.'admin/reports/'],['label'=>'Inventory','active'=>true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';

$reportModel = new Report();
$invModel    = new Inventory();
$data        = $reportModel->inventoryReport();
$stockVal    = $invModel->getStockValue();

// Categorise
$outOfStock  = array_filter($data, fn($r) => $r['stock_status']==='Out of Stock');
$lowStock    = array_filter($data, fn($r) => $r['stock_status']==='Low Stock');
$inStock     = array_filter($data, fn($r) => $r['stock_status']==='In Stock');

// Category breakdown
$byCat = $db->fetchAll(
    "SELECT c.category_name, COUNT(*) AS products,
            SUM(p.quantity) AS units, SUM(p.quantity*p.price) AS retail_value
     FROM products p JOIN categories c ON p.category_id=c.id
     WHERE p.status='active' GROUP BY c.id ORDER BY retail_value DESC"
);
$catLabels = json_encode(array_column($byCat,'category_name'));
$catValues = json_encode(array_column($byCat,'retail_value'));
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-boxes me-2 text-warning"></i>Inventory Report</h4><p>Stock levels, valuation, and distribution</p></div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print me-1"></i>Print</button>
        <button onclick="exportReport('inventory','<?= date('Y-m-01') ?>','<?= date('Y-m-d') ?>')" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i>Export</button>
    </div>
</div>

<!-- KPI cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="kpi-card kpi-primary"><div class="kpi-icon"><i class="fas fa-boxes"></i></div><div class="kpi-info"><div class="kpi-value"><?= number_format($stockVal['total_units']??0) ?></div><div class="kpi-label">Total Units</div></div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-success"><div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div><div class="kpi-info"><div class="kpi-value"><?= formatPrice($stockVal['total_retail_value']??0) ?></div><div class="kpi-label">Retail Value</div></div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-warning"><div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div><div class="kpi-info"><div class="kpi-value"><?= count($lowStock) ?></div><div class="kpi-label">Low Stock</div></div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-danger"><div class="kpi-icon"><i class="fas fa-times-circle"></i></div><div class="kpi-info"><div class="kpi-value"><?= count($outOfStock) ?></div><div class="kpi-label">Out of Stock</div></div></div></div>
</div>

<!-- Charts row -->
<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-chart-pie me-2 text-primary"></i>Stock by Category (Value)</div>
            <div class="card-body"><canvas id="catChart" height="240"></canvas></div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-chart-bar me-2 text-success"></i>Category Breakdown</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th>Category</th><th class="text-center">Products</th><th class="text-center">Units</th><th class="text-end">Retail Value</th></tr></thead>
                        <tbody>
                            <?php foreach ($byCat as $bc): ?>
                            <tr>
                                <td class="fw-semibold small"><?= clean($bc['category_name']) ?></td>
                                <td class="text-center"><?= $bc['products'] ?></td>
                                <td class="text-center"><?= number_format($bc['units']) ?></td>
                                <td class="text-end fw-bold"><?= formatPrice($bc['retail_value']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert sections -->
<?php if (!empty($outOfStock)): ?>
<div class="card border-0 shadow-sm mb-3 border-start border-danger border-3">
    <div class="card-header bg-transparent border-0 fw-semibold text-danger"><i class="fas fa-times-circle me-2"></i>Out of Stock (<?= count($outOfStock) ?>)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Product</th><th>SKU</th><th>Category</th><th>Brand</th><th class="text-end">Retail Price</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($outOfStock as $r): ?>
                    <tr>
                        <td class="fw-semibold small"><?= truncate(clean($r['product_name']),40) ?></td>
                        <td><code class="small"><?= clean($r['sku']??'—') ?></code></td>
                        <td class="small"><?= clean($r['category_name']) ?></td>
                        <td class="small"><?= clean($r['brand_name']) ?></td>
                        <td class="text-end"><?= formatPrice($r['price']) ?></td>
                        <td class="text-end">
                            <button onclick="quickStockIn(<?= $r['id'] ?>)" class="btn btn-xs btn-success"><i class="fas fa-plus me-1"></i>Restock</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($lowStock)): ?>
<div class="card border-0 shadow-sm mb-3 border-start border-warning border-3">
    <div class="card-header bg-transparent border-0 fw-semibold text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock (<?= count($lowStock) ?>)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Product</th><th>SKU</th><th class="text-center">Stock</th><th class="text-center">Alert At</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($lowStock as $r): ?>
                    <tr>
                        <td class="fw-semibold small"><?= truncate(clean($r['product_name']),40) ?></td>
                        <td><code class="small"><?= clean($r['sku']??'—') ?></code></td>
                        <td class="text-center"><span class="fw-bold text-warning"><?= $r['quantity'] ?></span></td>
                        <td class="text-center text-muted small"><?= $r['low_stock_alert'] ?></td>
                        <td class="text-end">
                            <button onclick="quickStockIn(<?= $r['id'] ?>)" class="btn btn-xs btn-outline-success"><i class="fas fa-plus me-1"></i>Restock</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Full inventory table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-table me-2"></i>Full Inventory — <?= count($data) ?> Products</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 data-table">
                <thead><tr><th>Product</th><th>SKU</th><th>Category</th><th>Brand</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Cost Value</th><th class="text-end">Retail Value</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($data as $r):
                        $sc = ['In Stock'=>'success','Low Stock'=>'warning','Out of Stock'=>'danger'];
                        $col= $sc[$r['stock_status']]??'secondary';
                    ?>
                    <tr>
                        <td class="fw-semibold small"><?= truncate(clean($r['product_name']),35) ?></td>
                        <td><code style="font-size:.75rem"><?= clean($r['sku']??'') ?></code></td>
                        <td class="small text-muted"><?= clean($r['category_name']) ?></td>
                        <td class="small text-muted"><?= clean($r['brand_name']) ?></td>
                        <td class="text-center fw-bold <?= $r['quantity']==0?'text-danger':($r['quantity']<=$r['low_stock_alert']?'text-warning':'text-success') ?>"><?= number_format($r['quantity']) ?></td>
                        <td class="text-end"><?= formatPrice($r['price']) ?></td>
                        <td class="text-end"><?= formatPrice($r['cost_value']) ?></td>
                        <td class="text-end fw-bold"><?= formatPrice($r['retail_value']) ?></td>
                        <td><span class="badge bg-<?= $col ?>"><?= $r['stock_status'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>
const BASE_URL='<?= BASE_URL ?>'; const CSRF_TOKEN='<?= csrfToken() ?>';
const COLORS=['#0d6efd','#198754','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14','#20c997','#adb5bd','#e83e8c'];
new Chart(document.getElementById('catChart'),{type:'doughnut',data:{labels:<?= $catLabels ?>,datasets:[{data:<?= $catValues ?>,backgroundColor:COLORS}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:11}}}}}});
function exportReport(t,f,to){window.open(`${BASE_URL}admin/reports/export.php?type=${t}&from=${f}&to=${to}&csrf_token=${CSRF_TOKEN}`,'_blank');}
</script>
</body></html>
