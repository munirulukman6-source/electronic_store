<?php
// order_success.php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'classes/Order.php';
require_once ROOT_PATH . 'classes/Cart.php';
require_once ROOT_PATH . 'classes/Inventory.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();

// Auth check – must happen before any HTML output
requireLogin();

$pageTitle  = 'Order Confirmed';
$orderNum   = get('order', '');
$orderModel = new Order();
$order      = $orderNum ? $orderModel->getByNumber($orderNum, $_SESSION['customer_id'] ?? null) : null;

require_once __DIR__ . '/views/layouts/header.php';
?>
<div class="container py-5">
    <div class="text-center mb-5">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 mb-3 shadow-sm" style="width:90px;height:90px">
            <i class="fas fa-check-circle fa-3x text-success"></i>
        </div>
        <h2 class="fw-extrabold text-success mb-2">Order Confirmed!</h2>
        <p class="lead text-muted mx-auto" style="max-width:500px;">Thank you for shopping with ElectroStore. We have received your order and our fulfillment team is preparing it for delivery.</p>
        <?php if ($order): ?>
        <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fs-6 fw-bold">
            Order Reference: <?= clean($order['order_number']) ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($order): ?>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
                <div class="card-header bg-body border-bottom p-3 fw-bold fs-6">
                    <i class="fas fa-receipt text-primary me-2"></i>Order Summary Details
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Order #</small>
                            <span class="fw-bold font-monospace"><?= clean($order['order_number']) ?></span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Date &amp; Time</small>
                            <span class="fw-semibold small"><?= formatDate($order['created_at']) ?></span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Payment Method</small>
                            <span class="fw-semibold small"><?= ucwords(str_replace('_',' ', $order['payment_method'] ?? '—')) ?></span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Order Status</small>
                            <div><?= orderStatusBadge($order['status']) ?></div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Shipping Address</small>
                            <div class="fw-semibold small"><?= clean($order['shipping_address'] ?? '') ?>, <?= clean($order['shipping_city'] ?? '') ?></div>
                        </div>
                    </div>
                    
                    <hr class="my-3">
                    <h6 class="fw-bold mb-3">Purchased Items</h6>
                    <div class="d-flex flex-column gap-2 mb-3">
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="d-flex align-items-center gap-3 p-2 bg-body-tertiary rounded-3">
                            <img src="<?= productImageUrl($item['product_image'] ?? '') ?>" width="44" height="44" style="object-fit:contain;border-radius:8px;background:#fff;padding:2px;" class="border">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold small text-truncate"><?= clean($item['product_name']) ?></div>
                                <small class="text-muted">Quantity: <?= $item['quantity'] ?></small>
                            </div>
                            <div class="fw-extrabold small text-primary"><?= formatPrice($item['total_price']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5">Total Paid</span>
                        <span class="fw-extrabold fs-4 text-primary"><?= formatPrice($order['total']) ?></span>
                    </div>

                    <?php if ($order['points_earned'] > 0): ?>
                    <div class="mt-3 p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 d-flex align-items-center gap-3">
                        <i class="fas fa-coins text-warning fs-4"></i>
                        <div>
                            <span class="fw-bold text-dark d-block">Reward Earned!</span>
                            <small class="text-muted">You gained <strong><?= number_format($order['points_earned']) ?> loyalty points</strong> from this order.</small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="<?= BASE_URL ?>customer/orders.php" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm">
                    <i class="fas fa-box me-2"></i>Track Orders
                </a>
                <a href="<?= BASE_URL ?>shop.php" class="btn btn-outline-secondary btn-lg px-4 fw-bold">
                    <i class="fas fa-store me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>