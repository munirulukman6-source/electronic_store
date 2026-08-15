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

// Auth check must happen before any HTML output
requireLogin();
if (!isCustomer()) redirect(BASE_URL . 'admin/');

$pageTitle  = 'Order Detail';
$orderModel = new Order();
$orderId    = (int)get('id');
$orderNum   = get('order', '');

$order = $orderId
    ? $orderModel->getById($orderId, $_SESSION['customer_id'])
    : ($orderNum ? $orderModel->getByNumber($orderNum, $_SESSION['customer_id']) : null);

if (!$order) {
    setFlash('danger', 'Order not found.');
    redirect(BASE_URL . 'customer/orders.php');
}

// Handle return request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    if (post('action') === 'submit_return') {
        $result = $orderModel->submitReturn($order['id'], $_SESSION['customer_id'], [
            'reason'      => post('reason'),
            'description' => post('description'),
            'refund_type' => post('refund_type'),
        ]);
        setFlash($result['success'] ? 'success' : 'danger', $result['message']);
        redirect($_SERVER['REQUEST_URI']);
    }
}

require_once ROOT_PATH . 'views/layouts/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= BASE_URL ?>customer/orders.php" class="text-muted small text-decoration-none mb-1 d-inline-block">
                <i class="fas fa-arrow-left me-1"></i>Back to My Orders
            </a>
            <h4 class="fw-extrabold mb-0 d-flex align-items-center gap-2">
                Order #<?= clean($order['order_number']) ?>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?= orderStatusBadge($order['status']) ?>
            <?= paymentStatusBadge($order['payment_status']) ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Items Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                <div class="card-header bg-body border-bottom p-3 fw-bold">
                    <i class="fas fa-box-open text-primary me-2"></i>Purchased Items (<?= count($order['items']) ?>)
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($order['items'] as $item): ?>
                        <li class="list-group-item p-3 bg-transparent d-flex align-items-center gap-3">
                            <img src="<?= productImageUrl($item['product_image'] ?? '') ?>" width="56" height="56" style="object-fit:contain;border-radius:8px;background:#fff;padding:3px;" class="border">
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="fw-bold mb-1 text-truncate">
                                    <a href="<?= BASE_URL ?>product/<?= clean($item['slug'] ?? '') ?>" class="text-decoration-none text-body">
                                        <?= clean($item['product_name']) ?>
                                    </a>
                                </h6>
                                <small class="text-muted"><?= formatPrice($item['unit_price']) ?> × <?= $item['quantity'] ?></small>
                            </div>
                            <div class="fw-extrabold text-primary"><?= formatPrice($item['total_price']) ?></div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Summary Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                <div class="card-header bg-body border-bottom p-3 fw-bold">
                    <i class="fas fa-receipt text-primary me-2"></i>Order Summary
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Date Placed</span>
                        <span class="text-body fw-semibold"><?= formatDate($order['created_at']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Payment Method</span>
                        <span class="text-body fw-semibold"><?= ucwords(str_replace('_',' ', $order['payment_method'] ?? '—')) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Shipping City</span>
                        <span class="text-body fw-semibold"><?= clean($order['shipping_city'] ?? '—') ?></span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Total Amount</span>
                        <span class="fw-extrabold text-primary fs-5"><?= formatPrice($order['total']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
