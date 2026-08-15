<?php
// admin/reports/customers.php
$pageTitle  = 'Customer Report';
$breadcrumb = [['label'=>'Reports','url'=>BASE_URL.'admin/reports/'],['label'=>'Customers','active'=>true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
requireStaff();

$from = get('from', date('Y-m-01'));
$to   = get('to',   date('Y-m-d'));

$reportModel = new Report();
$customers   = $reportModel->customerReport($from, $to);

// Summary stats
$totalRevenue = array_sum(array_column($customers,'spent_in_period'));
$totalOrders  = array_sum(array_column($customers,'orders_in_period'));
$totalCust    = count(array_filter($customers, fn($c) => $c['orders_in_period'] > 0));

// Tier breakdown
$tierBreak = $db->fetchAll(
    "SELECT ct.tier_name, ct.badge_color, COUNT(c.id) AS count, COALESCE(SUM(c.total_spent),0) AS revenue
     FROM customer_tiers ct LEFT JOIN customers c ON c.tier_id=ct.id
     GROUP BY ct.id ORDER BY ct.min_points"
);
$tierLabels  = json_encode(array_column($tierBreak,'tier_name'));
$tierCounts  = json_encode(array_column($tierBreak,'count'));
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-users me-2 text-success"></i>Customer Report</h4><p>Customer acquisition, retention and value analysis</p></div>
    <button onclick="exportReport('customers','<?= $from ?>','<?= $to ?>')" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i>Export</button>
</div>

<!-- Date filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
            <div><label class="form-label small mb-1">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>"></div>
            <div><label class="form-label small mb-1">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>"></div>
            <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-sync me-1"></i>Update</button>
            <a href="?from=<?= date('Y-01-01') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-outline-secondary btn-sm">This Year</a>
        </form>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="kpi-card kpi-primary"><div class="kpi-icon"><i class="fas fa-users"></i></div><div class="kpi-info"><div class="kpi-value"><?= number_format($totalCust) ?></div><div class="kpi-label">Active Customers</div></div></div></div>
    <div class="col-md-3"><div class="kpi-card kpi-success"><div class="kpi-icon"><i class="fas fa-shopping-bag"></i></div><div class="kpi-info"><div class="kpi-value"><?= number_format($totalOrders) ?></div><div class="kpi-label">Total Orders</div></div></div></div>
    <div class="col-md-3"><div class="kpi-card kpi-warning"><div class="kpi-icon"><i class="fas fa-chart-line"></i></div><div class="kpi-info"><div class="kpi-value"><?= formatPrice($totalRevenue) ?></div><div class="kpi-label">Total Revenue</div></div></div></div>
    <div class="col-md-3"><div class="kpi-card kpi-danger"><div class="kpi-icon"><i class="fas fa-calculator"></i></div><div class="kpi-info"><div class="kpi-value"><?= $totalCust>0 ? formatPrice($totalRevenue/$totalCust) : '₵0' ?></div><div class="kpi-label">Avg Revenue/Customer</div></div></div></div>
</div>

<!-- Tier chart + table -->
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-layer-group me-2 text-info"></i>Customers by Tier</div>
            <div class="card-body">
                <canvas id="tierChart" height="220"></canvas>
                <div class="mt-3">
                    <?php foreach ($tierBreak as $tb): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle" style="width:10px;height:10px;background:<?= clean($tb['badge_color']??'#6c757d') ?>"></div>
                            <span class="small"><?= clean($tb['tier_name']) ?></span>
                        </div>
                        <div class="d-flex gap-3">
                            <span class="badge bg-light text-dark"><?= number_format($tb['count']) ?> customers</span>
                            <span class="small fw-bold"><?= formatPrice($tb['revenue']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 fw-semibold"><i class="fas fa-trophy me-2 text-warning"></i>Top Customers by Revenue</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Tier</th><th class="text-center">Orders</th><th class="text-end">Revenue (Period)</th><th class="text-end">Total Spent</th><th>Points</th></tr></thead>
                        <tbody>
                            <?php foreach (array_slice($customers,0,15) as $i=>$c): ?>
                            <tr>
                                <td class="text-muted fw-bold"><?= $i+1 ?></td>
                                <td>
                                    <div class="fw-semibold small"><?= clean($c['customer_name']) ?></div>
                                    <small class="text-muted"><?= clean($c['email']) ?></small>
                                </td>
                                <td>
                                    <?php if ($c['tier_name']): ?>
                                    <span class="badge bg-secondary" style="font-size:.65rem"><?= clean($c['tier_name']) ?></span>
                                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                                </td>
                                <td class="text-center fw-bold"><?= $c['orders_in_period'] ?></td>
                                <td class="text-end fw-bold text-success"><?= formatPrice($c['spent_in_period']??0) ?></td>
                                <td class="text-end"><?= formatPrice($c['total_spent']??0) ?></td>
                                <td><span class="badge bg-warning text-dark"><?= number_format($c['loyalty_points']??0) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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
const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';
const tierColors=[<?= implode(',',array_map(fn($t)=>"'".($t['badge_color']??'#6c757d')."'",$tierBreak)) ?>];
new Chart(document.getElementById('tierChart'),{type:'doughnut',data:{labels:<?= $tierLabels ?>,datasets:[{data:<?= $tierCounts ?>,backgroundColor:tierColors}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:11}}}}}});
function exportReport(t,f,to){window.open(`${BASE_URL}admin/reports/export.php?type=${t}&from=${f}&to=${to}&csrf_token=${CSRF_TOKEN}`,'_blank');}
</script>
</body></html>
