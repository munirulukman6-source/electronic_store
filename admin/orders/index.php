<?php
// admin/orders/index.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireSales();

$pageTitle  = 'Orders';
$breadcrumb = [['label' => 'Orders', 'active' => true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
$orderModel = new Order();
$filters    = ['status' => get('status', ''), 'payment_status' => get('payment_status', ''), 'search' => get('search', ''), 'date_from' => get('date_from', ''), 'date_to' => get('date_to', '')];
$page       = max(1, (int)get('page', 1));
$result     = $orderModel->getAll($filters, $page);
$orders     = $result['data'];
$paginator  = $result;

$statusOptions = ['', 'pending', 'processing', 'approved', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'returned'];
$payOptions    = ['', 'pending', 'paid', 'refunded', 'failed'];
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4><i class="fas fa-shopping-bag me-2 text-warning"></i>Orders</h4>
        <p>Manage all customer orders</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="exportReport('orders','<?= date('Y-m-01') ?>','<?= date('Y-m-d') ?>')" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-excel me-1"></i>Export
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search order #, customer…" value="<?= clean($filters['search']) ?>"></div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach ($statusOptions as $s): if (!$s) continue; ?>
                        <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">Payment Status</option>
                    <?php foreach ($payOptions as $s): if (!$s) continue; ?>
                        <option value="<?= $s ?>" <?= $filters['payment_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="<?= $filters['date_from'] ?>"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm" value="<?= $filters['date_to'] ?>"></div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-primary btn-sm px-3" type="submit"><i class="fas fa-filter"></i></button>
                <a href="<?= BASE_URL ?>admin/orders/" class="btn btn-outline-secondary btn-sm px-3"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-2">
        <span class="small text-muted">Showing <?= $paginator['from'] ?>–<?= $paginator['to'] ?> of <?= number_format($paginator['total']) ?> orders</span>
        <div class="d-flex gap-2">
            <?php foreach (['pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'delivered' => 'success'] as $s => $c): ?>
                <a href="?status=<?= $s ?>" class="badge bg-<?= $c ?> text-decoration-none <?= $filters['status'] === $s ? 'opacity-100' : 'opacity-50' ?>">
                    <?= ucfirst($s) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th>Order #</th><th>Customer</th><th>Items</th>
                    <th>Total</th><th>Payment</th><th>Status</th>
                    <th>Date</th><th class="no-sort text-end">Actions</th>
                </tr></thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>No orders found</td></tr>
                    <?php else: foreach ($orders as $o): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>admin/orders/view.php?id=<?= $o['id'] ?>" class="fw-semibold text-primary"><?= clean($o['order_number']) ?></a></td>
                        <td>
                            <div class="fw-semibold small"><?= clean($o['customer_name']) ?></div>
                            <small class="text-muted"><?= clean($o['customer_email']) ?></small>
                        </td>
                        <td><span class="badge bg-light text-dark"><?= $o['item_count'] ?> item<?= $o['item_count'] != 1 ? 's' : '' ?></span></td>
                        <td class="fw-bold"><?= formatPrice($o['total']) ?></td>
                        <td><?= paymentStatusBadge($o['payment_status']) ?></td>
                        <td><?= orderStatusBadge($o['status']) ?></td>
                        <td><small><?= formatDateTime($o['created_at']) ?></small></td>
                        <td class="text-end">
                            <a href="<?= BASE_URL ?>admin/orders/view.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-light" title="View"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($paginator['last_page'] > 1): ?>
    <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Page <?= $paginator['current_page'] ?> of <?= $paginator['last_page'] ?></small>
        <?= paginationLinks($paginator, BASE_URL . 'admin/orders/?status=' . $filters['status'] . '&search=' . $filters['search']) ?>
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
