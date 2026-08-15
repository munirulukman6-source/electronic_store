<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireStaff();

$pageTitle  = 'Dashboard';
$breadcrumb = [['label' => 'Dashboard', 'active' => true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';

$orderModel   = new Order();
$productModel = new Product();
$reportModel  = new Report();

$stats        = $orderModel->getStats();
$recentOrders = $orderModel->getRecent(8);
$lowStock     = $productModel->getLowStock();
$topSellers   = $productModel->getTopSellers(5);
$revenueChart = $orderModel->getRevenueChart(12);

$chartLabels  = json_encode(array_column($revenueChart, 'month_label'));
$chartRevenue = json_encode(array_column($revenueChart, 'revenue'));
$chartOrders  = json_encode(array_column($revenueChart, 'order_count'));

$todaySales   = $db->fetchOne("SELECT COUNT(*) AS c, COALESCE(SUM(total),0) AS r FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status='paid'");
$thisMonth    = $db->fetchOne("SELECT COUNT(*) AS c, COALESCE(SUM(total),0) AS r FROM orders WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW()) AND payment_status='paid'");
$catRevenue   = $reportModel->revenueByCategory(date('Y-m-01'), date('Y-m-d'));
$payBreakdown = $reportModel->paymentMethodBreakdown(date('Y-01-01'), date('Y-m-d'));
?>

<!-- ── KPI Cards ──────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-primary">
            <div class="kpi-icon"><i class="fas fa-box"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?= number_format($stats['total_products'] ?? 0) ?></div>
                <div class="kpi-label">Total Products</div>
            </div>
            <a href="<?= BASE_URL ?>admin/products/" class="kpi-link"><i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-icon"><i class="fas fa-users"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?= number_format($stats['total_customers'] ?? 0) ?></div>
                <div class="kpi-label">Total Customers</div>
            </div>
            <a href="<?= BASE_URL ?>admin/customers/" class="kpi-link"><i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-icon"><i class="fas fa-shopping-bag"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?= number_format($stats['total_orders'] ?? 0) ?></div>
                <div class="kpi-label">Total Orders</div>
                <?php if ($stats['pending_orders'] ?? 0): ?>
                    <span class="badge bg-warning"><?= $stats['pending_orders'] ?> pending</span>
                <?php endif; ?>
            </div>
            <a href="<?= BASE_URL ?>admin/orders/" class="kpi-link"><i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-danger">
            <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?= formatPrice($stats['total_revenue'] ?? 0) ?></div>
                <div class="kpi-label">Total Revenue</div>
            </div>
            <a href="<?= BASE_URL ?>admin/reports/" class="kpi-link"><i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</div>

<!-- ── Today & Month quick stats ──────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-mini bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 p-3 text-center">
            <div class="fw-bold fs-5 text-primary"><?= formatPrice($todaySales['r']) ?></div>
            <small class="text-muted">Today's Revenue</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-mini bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 p-3 text-center">
            <div class="fw-bold fs-5 text-success"><?= $todaySales['c'] ?></div>
            <small class="text-muted">Today's Orders</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-mini bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 p-3 text-center">
            <div class="fw-bold fs-5 text-warning"><?= formatPrice($thisMonth['r']) ?></div>
            <small class="text-muted">This Month Revenue</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-mini bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 p-3 text-center">
            <div class="fw-bold fs-5 text-danger"><?= number_format($stats['low_stock_count'] ?? 0) ?></div>
            <small class="text-muted">Low Stock Items</small>
        </div>
    </div>
</div>

<!-- ── Charts row ─────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Revenue &amp; Orders (Last 12 Months)</h6>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" onclick="toggleChart('revenue')">Revenue</button>
                    <button class="btn btn-outline-secondary" onclick="toggleChart('orders')">Orders</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="280"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h6 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2 text-success"></i>Revenue by Category</h6>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ── Recent Orders + Low Stock ──────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-shopping-bag me-2 text-warning"></i>Recent Orders</h6>
                <a href="<?= BASE_URL ?>admin/orders/" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><span class="fw-semibold"><?= clean($order['order_number']) ?></span></td>
                                <td>
                                    <div><?= clean($order['customer_name']) ?></div>
                                    <small class="text-muted"><?= clean($order['email']) ?></small>
                                </td>
                                <td class="fw-bold"><?= formatPrice($order['total']) ?></td>
                                <td><?= orderStatusBadge($order['status']) ?></td>
                                <td><small><?= timeAgo($order['created_at']) ?></small></td>
                                <td>
                                    <a href="<?= BASE_URL ?>admin/orders/view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-light">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <!-- Low Stock -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alert</h6>
                <a href="<?= BASE_URL ?>admin/inventory/low_stock.php" class="btn btn-sm btn-outline-danger">Manage</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Product</th><th>Stock</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($lowStock, 0, 6) as $item): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold small"><?= truncate(clean($item['product_name']), 28) ?></div>
                                    <small class="text-muted"><?= clean($item['brand_name']) ?></small>
                                </td>
                                <td>
                                    <?php $pct = $item['low_stock_alert'] > 0 ? min(100, ($item['quantity'] / $item['low_stock_alert']) * 100) : 0; ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge <?= $item['quantity'] == 0 ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= $item['quantity'] ?></span>
                                        <div class="progress flex-grow-1" style="height:4px;">
                                            <div class="progress-bar <?= $item['quantity'] == 0 ? 'bg-danger' : 'bg-warning' ?>" style="width:<?= $pct ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>admin/inventory/stock_in.php?product_id=<?= $item['id'] ?>" class="btn btn-xs btn-outline-success">
                                        <i class="fas fa-plus-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Sellers -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h6 class="fw-bold mb-0"><i class="fas fa-fire me-2 text-danger"></i>Top Sellers</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($topSellers as $i => $ts): ?>
                    <li class="list-group-item d-flex align-items-center gap-2 py-2 px-3">
                        <span class="fw-bold text-muted" style="min-width:20px"><?= $i + 1 ?></span>
                        <img src="<?= productImageUrl($ts['primary_image']) ?>" width="36" height="36" class="rounded object-fit-cover">
                        <div class="flex-grow-1 min-w-0">
                            <div class="small fw-semibold text-truncate"><?= clean($ts['product_name']) ?></div>
                            <small class="text-muted"><?= number_format($ts['total_sold']) ?> sold</small>
                        </div>
                        <div class="fw-bold small text-success"><?= formatPrice($ts['revenue']) ?></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ── Payment Method Breakdown ───────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h6 class="fw-bold mb-0"><i class="fas fa-credit-card me-2 text-info"></i>Payment Methods (This Year)</h6>
            </div>
            <div class="card-body">
                <?php foreach ($payBreakdown as $pm): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small"><i class="fas fa-circle me-2 text-primary"></i><?= ucwords(str_replace('_', ' ', $pm['payment_method'])) ?></span>
                    <div class="d-flex gap-3">
                        <span class="badge bg-light text-dark"><?= $pm['count'] ?> orders</span>
                        <span class="fw-bold small"><?= formatPrice($pm['total']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h6 class="fw-bold mb-0"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6"><a href="<?= BASE_URL ?>admin/products/add.php" class="btn btn-outline-primary w-100 btn-sm"><i class="fas fa-plus me-1"></i>Add Product</a></div>
                    <div class="col-6"><a href="<?= BASE_URL ?>admin/inventory/stock_in.php" class="btn btn-outline-success w-100 btn-sm"><i class="fas fa-boxes me-1"></i>Stock In</a></div>
                    <div class="col-6"><a href="<?= BASE_URL ?>admin/orders/?status=pending" class="btn btn-outline-warning w-100 btn-sm"><i class="fas fa-clock me-1"></i>Pending Orders</a></div>
                    <div class="col-6"><a href="<?= BASE_URL ?>admin/reports/" class="btn btn-outline-info w-100 btn-sm"><i class="fas fa-chart-bar me-1"></i>Sales Report</a></div>
                    <div class="col-6"><a href="<?= BASE_URL ?>admin/coupons/add.php" class="btn btn-outline-danger w-100 btn-sm"><i class="fas fa-tag me-1"></i>New Coupon</a></div>
                    <div class="col-6"><a href="<?= BASE_URL ?>admin/flash_sales/add.php" class="btn btn-outline-secondary w-100 btn-sm"><i class="fas fa-bolt me-1"></i>Flash Sale</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>
const BASE_URL  = '<?= BASE_URL ?>';
const CSRF_TOKEN = '<?= csrfToken() ?>';

// Revenue / Orders line chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const chartLabels = <?= $chartLabels ?>;
const chartRevenue = <?= $chartRevenue ?>;
const chartOrders  = <?= $chartOrders ?>;

let revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [{
            label: 'Revenue (<?= DEFAULT_CURRENCY_SYMBOL ?>)',
            data: chartRevenue,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.08)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false }, tooltip: { callbacks: {
            label: ctx => '<?= DEFAULT_CURRENCY_SYMBOL ?>' + Number(ctx.raw).toLocaleString('en-GH', {minimumFractionDigits: 2})
        }}},
        scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false } } }
    }
});

function toggleChart(type) {
    const ds = revenueChart.data.datasets[0];
    if (type === 'revenue') {
        ds.data = chartRevenue;
        ds.label = 'Revenue (<?= DEFAULT_CURRENCY_SYMBOL ?>)';
        ds.borderColor = '#0d6efd';
        ds.backgroundColor = 'rgba(13,110,253,0.08)';
    } else {
        ds.data = chartOrders;
        ds.label = 'Orders';
        ds.borderColor = '#198754';
        ds.backgroundColor = 'rgba(25,135,84,0.08)';
    }
    revenueChart.update();
    document.querySelectorAll('.btn-group button').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
}

// Category doughnut
<?php
$catNames  = json_encode(array_column($catRevenue, 'category_name'));
$catValues = json_encode(array_column($catRevenue, 'revenue'));
?>
new Chart(document.getElementById('categoryChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: <?= $catNames ?>,
        datasets: [{ data: <?= $catValues ?>, backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14','#20c997'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
});
</script>
</div></div><!-- close admin-content & admin-main -->
</body>
</html>
