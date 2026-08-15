<?php
// admin/customers/index.php
$pageTitle  = 'Customers';
$breadcrumb = [['label' => 'Customers', 'active' => true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
requireStaff();

$search = get('search', '');
$tier   = get('tier', '');
$page   = max(1, (int)get('page', 1));

$where  = ['1=1'];
$params = [];
if ($search) {
    $where[] = '(u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ? OR c.phone LIKE ?)';
    $s = "%$search%"; array_push($params, $s, $s, $s, $s);
}
if ($tier) { $where[] = 'c.tier_id = ?'; $params[] = $tier; }

$sql      = "SELECT u.id, u.username, u.email, u.full_name, u.status, u.last_login, u.created_at,
                    c.id AS customer_id, c.phone, c.city, c.country, c.loyalty_points, c.total_orders, c.total_spent,
                    ct.tier_name, ct.badge_color
             FROM users u
             JOIN customers c ON u.id = c.user_id
             LEFT JOIN customer_tiers ct ON c.tier_id = ct.id
             JOIN roles r ON u.role_id = r.id
             WHERE r.slug = 'customer' AND " . implode(' AND ', $where) . "
             ORDER BY c.total_spent DESC";
$result   = $db->paginate($sql, $params, $page, 25);
$customers= $result['data'];
$tiers    = $db->fetchAll("SELECT * FROM customer_tiers ORDER BY min_points");
$stats    = $db->fetchOne("SELECT COUNT(*) AS total, SUM(total_spent) AS revenue, AVG(loyalty_points) AS avg_points FROM customers");
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-users me-2 text-success"></i>Customers</h4><p><?= number_format($result['total']) ?> registered customers</p></div>
    <button onclick="exportReport('customers','<?= date('Y-01-01') ?>','<?= date('Y-m-d') ?>')" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i>Export</button>
</div>

<!-- Stats -->
<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="kpi-card kpi-primary"><div class="kpi-icon"><i class="fas fa-users"></i></div><div class="kpi-info"><div class="kpi-value"><?= number_format($result['total']) ?></div><div class="kpi-label">Total Customers</div></div></div></div>
    <div class="col-md-4"><div class="kpi-card kpi-success"><div class="kpi-icon"><i class="fas fa-chart-line"></i></div><div class="kpi-info"><div class="kpi-value"><?= formatPrice($stats['revenue'] ?? 0) ?></div><div class="kpi-label">Total Customer Revenue</div></div></div></div>
    <div class="col-md-4"><div class="kpi-card kpi-warning"><div class="kpi-icon"><i class="fas fa-coins"></i></div><div class="kpi-info"><div class="kpi-value"><?= number_format($stats['avg_points'] ?? 0) ?></div><div class="kpi-label">Avg Loyalty Points</div></div></div></div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, email, phone…" value="<?= clean($search) ?>"></div>
            <div class="col-md-3">
                <select name="tier" class="form-select form-select-sm">
                    <option value="">All Tiers</option>
                    <?php foreach ($tiers as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $tier == $t['id'] ? 'selected' : '' ?>><?= clean($t['tier_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button class="btn btn-primary btn-sm flex-fill" type="submit"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="<?= BASE_URL ?>admin/customers/" class="btn btn-outline-secondary btn-sm px-3"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Customer</th><th>Contact</th><th>Tier</th><th class="text-center">Orders</th><th class="text-end">Spent</th><th class="text-center">Points</th><th>Joined</th><th>Status</th><th class="no-sort text-end">Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($customers as $cust): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold small"><?= clean($cust['full_name']) ?></div>
                            <small class="text-muted"><?= clean($cust['email']) ?></small>
                        </td>
                        <td>
                            <div class="small"><?= clean($cust['phone'] ?? '—') ?></div>
                            <small class="text-muted"><?= clean($cust['city'] ?? '') ?><?= $cust['country'] ? ', '.$cust['country'] : '' ?></small>
                        </td>
                        <td>
                            <?php if ($cust['tier_name']): ?>
                            <span class="badge" style="background:<?= clean($cust['badge_color'] ?? '#6c757d') ?>"><?= clean($cust['tier_name']) ?></span>
                            <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                        </td>
                        <td class="text-center fw-bold"><?= number_format($cust['total_orders']) ?></td>
                        <td class="text-end fw-bold text-success"><?= formatPrice($cust['total_spent']) ?></td>
                        <td class="text-center"><span class="badge bg-warning text-dark"><?= number_format($cust['loyalty_points']) ?></span></td>
                        <td><small><?= formatDate($cust['created_at']) ?></small></td>
                        <td><?= $cust['status'] === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">'.ucfirst($cust['status']).'</span>' ?></td>
                        <td class="text-end">
                            <a href="<?= BASE_URL ?>admin/customers/view.php?id=<?= $cust['id'] ?>" class="btn btn-xs btn-light" title="View"><i class="fas fa-eye"></i></a>
                            <button onclick="adminAction('toggle_user_status',<?= $cust['id'] ?>,'<?= $cust['status']==='active' ? 'Suspend' : 'Activate' ?> this customer?')" class="btn btn-xs btn-outline-<?= $cust['status']==='active'?'warning':'success' ?> ms-1">
                                <i class="fas fa-<?= $cust['status']==='active'?'ban':'check' ?>"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($result['last_page'] > 1): ?>
    <div class="card-footer bg-transparent border-0 py-2 d-flex justify-content-between">
        <small class="text-muted">Showing <?= $result['from'] ?>–<?= $result['to'] ?> of <?= $result['total'] ?></small>
        <?= paginationLinks($result, BASE_URL.'admin/customers/?search='.urlencode($search).'&tier='.$tier) ?>
    </div>
    <?php endif; ?>
</div>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';</script>
</body></html>
