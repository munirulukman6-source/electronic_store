<?php
$pageTitle  = 'Sales Report';
$breadcrumb = [['label' => 'Reports', 'active' => true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
requireStaff();

$reportModel = new Report();
$from = get('from', date('Y-m-01'));
$to   = get('to',   date('Y-m-d'));
$groupBy = get('group', 'daily');

$summary    = $reportModel->salesSummary($from, $to);
$byPeriod   = $reportModel->salesByPeriod($from, $to, $groupBy);
$topProds   = $reportModel->topProducts($from, $to, 10);
$byCat      = $reportModel->revenueByCategory($from, $to);
$byBrand    = $reportModel->revenueByBrand($from, $to);
$byPayment  = $reportModel->paymentMethodBreakdown($from, $to);

$chartLabels  = json_encode(array_column($byPeriod, 'period'));
$chartRevenue = json_encode(array_column($byPeriod, 'revenue'));
$chartOrders  = json_encode(array_column($byPeriod, 'orders'));
$catLabels    = json_encode(array_column($byCat, 'category_name'));
$catRevenue   = json_encode(array_column($byCat, 'revenue'));
$brandLabels  = json_encode(array_column($byBrand, 'brand_name'));
$brandRevenue = json_encode(array_column($byBrand, 'revenue'));
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-chart-bar me-2 text-info"></i>Sales Report</h4><p>Revenue and performance analytics</p></div>
    <div class="d-flex gap-2">
        <button onclick="exportReport('sales','<?= $from ?>','<?= $to ?>')" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i>Excel</button>
        <button onclick="exportReport('sales_pdf','<?= $from ?>','<?= $to ?>')" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-pdf me-1"></i>PDF</button>
    </div>
</div>

<!-- Date filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Group By</label>
                <select name="group" class="form-select form-select-sm">
                    <option value="daily"   <?= $groupBy==='daily'?'selected':'' ?>>Daily</option>
                    <option value="weekly"  <?= $groupBy==='weekly'?'selected':'' ?>>Weekly</option>
                    <option value="monthly" <?= $groupBy==='monthly'?'selected':'' ?>>Monthly</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-1">
                <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-sync me-1"></i>Generate</button>
                <a href="?from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-outline-secondary btn-sm">This Month</a>
                <a href="?from=<?= date('Y-01-01') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-outline-secondary btn-sm">This Year</a>
            </div>
        </form>
    </div>
</div>

<!-- Summary KPIs -->
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['Total Orders',    $summary['total_orders'] ?? 0,                       'fa-shopping-bag',   'kpi-primary', false],
        ['Total Revenue',   formatPrice($summary['total_revenue'] ?? 0),          'fa-chart-line',     'kpi-success', false],
        ['Avg Order Value', formatPrice($summary['avg_order'] ?? 0),              'fa-calculator',     'kpi-warning', false],
        ['Delivered',       $summary['delivered'] ?? 0,                           'fa-check-circle',   'kpi-success', false],
        ['Cancelled',       $summary['cancelled'] ?? 0,                           'fa-times-circle',   'kpi-danger',  false],
        ['Total Discounts', formatPrice($summary['total_discounts'] ?? 0),        'fa-tag',            'kpi-warning', false],
        ['Tax Collected',   formatPrice($summary['total_tax'] ?? 0),              'fa-receipt',        'kpi-primary', false],
        ['Paid Revenue',    formatPrice($summary['paid_revenue'] ?? 0),           'fa-wallet',         'kpi-success', false],
    ];
    foreach ($kpis as [$label, $val, $icon, $cls, $]): ?>
    <div class="col-6 col-md-3">
        <div class="kpi-card <?= $cls ?>">
            <div class="kpi-icon"><i class="fas <?= $icon ?>"></i></div>
            <div class="kpi-info"><div class="kpi-value" style="font-size:1.15rem"><?= $val ?></div><div class="kpi-label"><?= $label ?></div></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Revenue Chart -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 fw-semibold">
                <i class="fas fa-chart-area me-2 text-primary"></i>Revenue Over Time
                <span class="text-muted small ms-2"><?= formatDate($from) ?> – <?= formatDate($to) ?></span>
            </div>
            <div class="card-body"><canvas id="revenueChart" height="260"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-chart-pie me-2 text-success"></i>Revenue by Category</div>
            <div class="card-body"><canvas id="categoryChart" height="220"></canvas></div>
        </div>
    </div>
</div>

<!-- Top products + brand + payment -->
<div class="row g-3 mb-4">
    <!-- Top products -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-trophy me-2 text-warning"></i>Top 10 Selling Products</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>#</th><th>Product</th><th class="text-center">Sold</th><th class="text-end">Revenue</th></tr></thead>
                        <tbody>
                            <?php foreach ($topProds as $i => $tp): ?>
                            <tr>
                                <td class="text-muted fw-bold"><?= $i+1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= productImageUrl($tp['primary_image'] ?? '') ?>" width="32" height="32" class="rounded" style="object-fit:contain">
                                        <div>
                                            <div class="small fw-semibold"><?= truncate(clean($tp['product_name']),28) ?></div>
                                            <small class="text-muted"><?= clean($tp['brand_name']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center fw-bold"><?= number_format($tp['qty_sold']) ?></td>
                                <td class="text-end fw-bold text-success"><?= formatPrice($tp['revenue']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Brand revenue -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-certificate me-2 text-primary"></i>Revenue by Brand</div>
            <div class="card-body"><canvas id="brandChart" height="280"></canvas></div>
        </div>
    </div>

    <!-- Payment breakdown -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-credit-card me-2 text-info"></i>Payment Methods</div>
            <div class="card-body">
                <?php foreach ($byPayment as $pm):
                    $total = array_sum(array_column($byPayment,'total')) ?: 1;
                    $pct   = round($pm['total'] / $total * 100, 1);
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold"><?= ucwords(str_replace('_',' ',$pm['payment_method'])) ?></span>
                        <span class="small text-muted"><?= $pct ?>%</span>
                    </div>
                    <div class="progress" style="height:6px">
                        <div class="progress-bar" style="width:<?= $pct ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted"><?= $pm['count'] ?> orders</small>
                        <small class="fw-bold"><?= formatPrice($pm['total']) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Detailed period table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-table me-2"></i>Period Breakdown</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Period</th><th class="text-center">Orders</th><th class="text-end">Revenue</th><th class="text-end">Avg Order</th></tr></thead>
                <tbody>
                    <?php foreach ($byPeriod as $bp): ?>
                    <tr>
                        <td class="fw-semibold"><?= clean($bp['period']) ?></td>
                        <td class="text-center"><?= number_format($bp['orders']) ?></td>
                        <td class="text-end fw-bold text-success"><?= formatPrice($bp['revenue']) ?></td>
                        <td class="text-end text-muted"><?= formatPrice($bp['avg_order'] ?? 0) ?></td>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>
const BASE_URL='<?= BASE_URL ?>'; const CSRF_TOKEN='<?= csrfToken() ?>';
const COLORS=['#0d6efd','#198754','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14','#20c997','#adb5bd','#e83e8c'];

new Chart(document.getElementById('revenueChart').getContext('2d'),{type:'bar',data:{labels:<?= $chartLabels ?>,datasets:[{label:'Revenue',data:<?= $chartRevenue ?>,backgroundColor:'rgba(13,110,253,.2)',borderColor:'#0d6efd',borderWidth:2,borderRadius:4},{label:'Orders',data:<?= $chartOrders ?>,type:'line',borderColor:'#198754',backgroundColor:'transparent',tension:.4,pointRadius:4,yAxisID:'y1'}]},options:{responsive:true,plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true,title:{display:true,text:'Revenue (<?= DEFAULT_CURRENCY_SYMBOL ?>)'}},y1:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},title:{display:true,text:'Orders'}}}}});

new Chart(document.getElementById('categoryChart').getContext('2d'),{type:'doughnut',data:{labels:<?= $catLabels ?>,datasets:[{data:<?= $catRevenue ?>,backgroundColor:COLORS}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:11}}}}}});

new Chart(document.getElementById('brandChart').getContext('2d'),{type:'bar',indexAxis:'y',data:{labels:<?= $brandLabels ?>,datasets:[{data:<?= $brandRevenue ?>,backgroundColor:COLORS}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{x:{beginAtZero:true}}}});

function exportReport(type,from,to){window.open(`${BASE_URL}admin/reports/export.php?type=${type}&from=${from}&to=${to}&csrf_token=${CSRF_TOKEN}`,'_blank');}
</script>
</body></html>
