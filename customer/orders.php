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

// Authentication checks MUST run before any HTML output
requireLogin();
if (!isCustomer()) {
    redirect(BASE_URL . 'admin/');
}

// Now it’s safe to start output
$pageTitle  = 'My Orders';
$orderModel = new Order();
$page       = max(1, (int)get('page', 1));
$status     = get('status', '');
$result     = $orderModel->getCustomerOrders($_SESSION['customer_id'], $page);
$orders     = $result['data'];
$paginator  = $result;

require_once ROOT_PATH . 'views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar account nav (kept exactly as before) -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 text-center border-bottom">
                    <div class="rounded-circle bg-primary bg-opacity-15 d-flex align-items-center justify-content-center mx-auto mb-2" style="width:60px;height:60px">
                        <i class="fas fa-user-circle fa-2x text-primary"></i>
                    </div>
                    <div class="fw-bold"><?= clean($_SESSION['full_name']) ?></div>
                    <small class="text-muted"><?= clean($_SESSION['email']) ?></small>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= BASE_URL ?>customer/profile.php" class="list-group-item list-group-item-action"><i class="fas fa-user me-2 text-primary"></i>My Profile</a>
                    <a href="<?= BASE_URL ?>customer/orders.php" class="list-group-item list-group-item-action active"><i class="fas fa-box me-2"></i>My Orders</a>
                    <a href="<?= BASE_URL ?>wishlist.php" class="list-group-item list-group-item-action"><i class="fas fa-heart me-2 text-danger"></i>Wishlist</a>
                    <a href="<?= BASE_URL ?>customer/loyalty.php" class="list-group-item list-group-item-action"><i class="fas fa-coins me-2 text-warning"></i>Loyalty Points</a>
                    <a href="<?= BASE_URL ?>customer/reviews.php" class="list-group-item list-group-item-action"><i class="fas fa-star me-2 text-warning"></i>My Reviews</a>
                    <a href="<?= BASE_URL ?>auth/logout.php" class="list-group-item list-group-item-action text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </div>
            </div>
        </div>

        <!-- Orders content (unchanged) -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0"><i class="fas fa-box me-2 text-primary"></i>My Orders</h4>
                <span class="text-muted small"><?= number_format($paginator['total']) ?> orders total</span>
            </div>

            <!-- Status filter tabs -->
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <?php foreach ([''=>'All','pending'=>'Pending','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled'] as $s => $l): ?>
                <a href="?status=<?= $s ?>" class="btn btn-sm <?= $status===$s ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $l ?></a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($orders)): ?>
            <div class="text-center py-5 bg-light rounded-3">
                <i class="fas fa-box-open fa-4x text-muted opacity-25 mb-3"></i>
                <h5 class="text-muted">No orders found</h5>
                <a href="<?= BASE_URL ?>shop.php" class="btn btn-primary mt-2"><i class="fas fa-store me-2"></i>Start Shopping</a>
            </div>
            <?php else: foreach ($orders as $o): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="fw-bold">Order #<?= clean($o['order_number']) ?></span>
                        <small class="text-muted ms-2"><i class="fas fa-calendar me-1"></i><?= formatDate($o['created_at']) ?></small>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <?= orderStatusBadge($o['status']) ?>
                        <?= paymentStatusBadge($o['payment_status']) ?>
                    </div>
                </div>
                <div class="card-body py-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted"><i class="fas fa-shopping-basket me-1"></i><?= $o['item_count'] ?> item(s)</small>
                            <span class="mx-2">·</span>
                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= clean($o['shipping_city'] ?? '—') ?></small>
                        </div>
                        <div class="col-md-3 text-md-end fw-bold text-primary"><?= formatPrice($o['total']) ?></div>
                        <div class="col-md-3 text-md-end">
                            <a href="<?= BASE_URL ?>customer/order_detail.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                            <?php if ($o['status'] === 'pending'): ?>
                            <button onclick="if(confirm('Cancel this order?')) location.href='<?= BASE_URL ?>customer/cancel_order.php?id=<?= $o['id'] ?>'" class="btn btn-sm btn-outline-danger ms-1">Cancel</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; endif; ?>

            <?php if ($paginator['last_page'] > 1): ?>
            <div class="mt-3"><?= paginationLinks($paginator, BASE_URL.'customer/orders.php') ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>