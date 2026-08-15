<?php
// admin/reports/revenue.php
$pageTitle  = 'Revenue Report';
$breadcrumb = [['label'=>'Reports','url'=>BASE_URL.'admin/reports/'],['label'=>'Revenue','active'=>true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
requireStaff();

$from = get('from', date('Y-01-01'));
$to   = get('to',   date('Y-m-d'));

$reportModel = new Report();
$summary     = $reportModel->salesSummary($from, $to);
$monthly     = $reportModel->salesByPeriod($from, $to, 'monthly');
$byCat       = $reportModel->revenueByCategory($from, $to);
$byBrand     = $reportModel->revenueByBrand($from, $to);
$byPayment   = $reportModel->paymentMethodBreakdown($from, $to);
$topProds    = $reportModel->topProducts($from, $to, 5);

// Compare to previous period
$periodDays  = max(1, (strtotime($to) - strtotime($from)) / 86400);
$prevTo      = date('Y-m-d', strtotime($from) - 86400);
$prevFrom    = date('Y-m-d', strtotime($prevTo) - ($periodDays * 86400));
$prevSummary = $reportModel->salesSummary($prevFrom, $prevTo);

function pctChange(float $cur, float $prev): string {
    if ($prev == 0) return '<span class="text-success small">New</span>';
    $pct = (($cur - $prev) / $prev) * 100;
    $cls = $pct >= 0 ? 'success' : 'danger';
    $arrow = $pct >= 0 ? '▲' : '▼';
    return "<span class=\"text-$cls small fw-semibold\">$arrow ".number_format(abs($pct),1)."%</span>";
}

$chartLabels  = json_encode(array_column($monthly,'period'));
$chartRevenue = json_encode(array_column($monthly,'revenue'));
$chartOrders  = json_encode(array_column($monthly,'orders'));
$catLabels    = json_encode(array_column($byCat,'category_name'));
$catRevenue   = json_encode(array_column($byCat,'revenue'));
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-wallet me-2 text-success"></i>Revenue Report</h4><p>Detailed financial performance analysis</p></div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print me-1"></i>Print</button>
        <button onclick="exportReport('sales','<?= $from ?>','<?= $to ?>')" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i>Export</button>
    </div>
</div>

<!-- Date filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
            <div><label class="form-label small mb-1">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>"></div>
            <div><label class="form-label small mb-1">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>"></div>
            <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-sync me-1"></i>Update</button>
            <a href="?from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-outline-secondary btn-sm">This Month</a>
            <a href="?from=<?= date('Y-01-01') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-outline-secondary btn-sm">This Year</a>
            <a href="?from=<?= date('Y-m-d', strtotime('-30 days')) ?>&to=<?= date('Y-m-d') ?>" class="btn btn-outline-secondary btn-sm">Last 30 Days</a>
        </form>
    </div>
</div>

<!-- KPI cards with comparison -->
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['Total Revenue',  $summary['paid_revenue']??0,   $prevSummary['paid_revenue']??0,   'fa-dollar-sign', 'kpi-success', true],
        ['Total Orders',   $summary['total_orders']??0,   $prevSummary['total_orders']??0,   'fa-shopping-bag','kpi-primary', false],
        ['Avg Order Value',$summary['avg_order']??0,      $prevSummary['avg_order']??0,      'fa-calculator',  'kpi-warning', true],
        ['Tax Collected',  $summary['total_tax']??0,      $prevSummary['total_tax']??0,      'fa-receipt',     'kpi-danger',  true],
    ];
    foreach ($kpis as [$label, $cur, $prev, $icon, $cls, $isCurrency]):
    ?>
    <div class="col-6 col-md-3">
        <div class="kpi-card <?= $cls ?>">
            <div class="kpi-icon"><i class="fas <?= $icon ?>"></i></div>
            <div class="kpi-info">
                <div class="kpi-value" style="font-size:1.2rem"><?= $isCurrency ? formatPrice($cur) : number_format($cur) ?></div>
                <div class="kpi-label"><?= $label ?></div>
                <div class="mt-1"><?= pctChange($cur, $prev) ?> <span class="text-muted" style="font-size:.7rem">vs prev period</span></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Revenue trend chart -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 fw-semibold">
                <i class="fas fa-chart-area me-2 text-primary"></i>Monthly Revenue Trend
            </div>
            <div class="card-body"><canvas id="revenueChart" height="260"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-chart-pie me-2 text-success"></i>Revenue by Category</div>
            <div class="card-body"><canvas id="catChart" height="240"></canvas></div>
        </div>
    </div>
</div>

<!-- Payment methods + top products + brand -->
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-credit-card me-2 text-info"></i>Payment Methods</div>
            <div class="card-body">
                <?php
                $totalRev = array_sum(array_column($byPayment,'total')) ?: 1;
                foreach ($byPayment as $pm):
                    $pct = round($pm['total'] / $totalRev * 100, 1);
                    $payIcons = ['mobile_money'=>'fa-mobile-alt','cash_on_delivery'=>'fa-money-bill-wave','bank_transfer'=>'fa-university'];
                    $payIcon = $payIcons[$pm['payment_method']] ?? 'fa-credit-card';
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold d-flex align-items-center gap-1">
                            <i class="fas <?= $payIcon ?> text-primary"></i>
                            <?= ucwords(str_replace('_',' ',$pm['payment_method'])) ?>
                        </span>
                        <span class="small fw-bold"><?= formatPrice($pm['total']) ?></span>
                    </div>
                    <div class="progress mb-1" style="height:6px">
                        <div class="progress-bar" style="width:<?= $pct ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted"><?= $pm['count'] ?> transactions</small>
                        <small class="text-muted"><?= $pct ?>%</small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-trophy me-2 text-warning"></i>Top Revenue Products</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($topProds as $i=>$tp): ?>
                    <li class="list-group-item d-flex align-items-center gap-2 py-2">
                        <span class="fw-bold text-muted" style="min-width:20px"><?= $i+1 ?></span>
                        <img src="<?= productImageUrl($tp['primary_image']??null,'', $tp['product_name']) ?>" width="36" height="36" style="border-radius:6px;object-fit:cover">
                        <div class="flex-grow-1 min-w-0">
                            <div class="small fw-semibold text-truncate"><?= clean($tp['product_name']) ?></div>
                            <small class="text-muted"><?= number_format($tp['qty_sold']) ?> units</small>
                        </div>
                        <div class="fw-bold small text-success text-nowrap"><?= formatPrice($tp['revenue']) ?></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-certificate me-2 text-primary"></i>Revenue by Brand</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice($byBrand,0,8) as $i=>$br): ?>
                    <li class="list-group-item d-flex align-items-center justify-content-between py-2">
                        <div class="small fw-semibold"><?= clean($br['brand_name']) ?></div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge bg-light text-dark"><?= number_format($br['qty_sold']) ?> sold</span>
                            <span class="small fw-bold text-primary"><?= formatPrice($br['revenue']) ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Summary table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-table me-2"></i>Monthly Summary</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Month</th><th class="text-center">Orders</th><th class="text-end">Revenue</th><th class="text-end">Avg Order</th></tr></thead>
                <tbody>
                    <?php foreach ($monthly as $m): ?>
                    <tr>
                        <td class="fw-semibold"><?= clean($m['period']) ?></td>
                        <td class="text-center"><?= number_format($m['orders']) ?></td>
                        <td class="text-end fw-bold text-success"><?= formatPrice($m['revenue']) ?></td>
                        <td class="text-end text-muted"><?= formatPrice($m['avg_order']??0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-light fw-bold">
                        <td>TOTAL</td>
                        <td class="text-center"><?= number_format(array_sum(array_column($monthly,'orders'))) ?></td>
                        <td class="text-end text-success"><?= formatPrice(array_sum(array_column($monthly,'revenue'))) ?></td>
                        <td></td>
                    </tr>
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
const COLORS=['#0d6efd','#198754','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14','#20c997','#adb5bd'];

new Chart(document.getElementById('revenueChart'),{
    type:'line',
    data:{
        labels:<?= $chartLabels ?>,
        datasets:[
            {label:'Revenue',data:<?= $chartRevenue ?>,borderColor:'#198754',backgroundColor:'rgba(25,135,84,.08)',tension:.4,fill:true,pointRadius:4},
            {label:'Orders', data:<?= $chartOrders ?>, borderColor:'#0d6efd',backgroundColor:'transparent',tension:.4,borderDash:[5,4],yAxisID:'y1',pointRadius:3}
        ]
    },
    options:{responsive:true,plugins:{legend:{position:'top'}},scales:{
        y:{beginAtZero:true,title:{display:true,text:'Revenue (<?= DEFAULT_CURRENCY_SYMBOL ?>)'}},
        y1:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},title:{display:true,text:'Orders'}}
    }}
});
new Chart(document.getElementById('catChart'),{type:'doughnut',data:{labels:<?= $catLabels ?>,datasets:[{data:<?= $catRevenue ?>,backgroundColor:COLORS}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:11}}}}}});
function exportReport(t,f,to){window.open(`${BASE_URL}admin/reports/export.php?type=${t}&from=${f}&to=${to}&csrf_token=${CSRF_TOKEN}`,'_blank');}
</script>
</body></html>
