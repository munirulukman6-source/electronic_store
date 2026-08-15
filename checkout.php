<?php
// ── Bootstrap + classes ─────────────────────────────────────────
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'classes/Order.php';
require_once ROOT_PATH . 'classes/Cart.php';
require_once ROOT_PATH . 'classes/Inventory.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();

// ── Auth check (must happen before any HTML output) ──────────────
requireLogin();

// ── Order placement – processed BEFORE HTML ─────────────────────
$cart      = new Cart();
$cartItems = $cart->getItems();
if (empty($cartItems)) {
    setFlash('warning', 'Your cart is empty.');
    redirect(BASE_URL . 'shop.php');
}

$userModel  = new User();
$profile    = $userModel->getCustomerProfile($_SESSION['user_id']);
$orderModel = new Order();
$zones      = $orderModel->getShippingZones();
$loyaltyPts = (int)($_SESSION['loyalty_points'] ?? 0);
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $coupon  = post('coupon_code');
    $points  = min((int)post('points_to_use', 0), $loyaltyPts);
    $summary = $cart->getSummary($coupon, $points);

    $address = implode(', ', array_filter([post('address'), post('city'), post('state'), post('country')]));

    $db  = Database::getInstance();
    $pdo = $db->getConnection();

    try {
        // Begin single transaction covering the entire checkout
        $pdo->beginTransaction();

        // Call the stored procedure (it does NOT commit on its own)
        $stmt = $pdo->prepare("CALL sp_place_order(?,?,?,?,?,?,@order_id,@order_number,@message)");
        $stmt->execute([
            $_SESSION['customer_id'],
            $address,
            post('payment_method'),
            $coupon ?: null,
            $points,
            post('notes')
        ]);

        // Retrieve output parameters
        $out = $db->fetchOne("SELECT @order_id AS oid, @order_number AS onum, @message AS msg");

        if (!$out['oid']) {
            // Procedure signalled an error – rollback and show message
            throw new Exception($out['msg'] ?? 'Order could not be placed.');
        }

        // Record payment for non‑COD methods (inside the same transaction)
        if (post('payment_method') !== 'cash_on_delivery') {
            $txnId = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
            $orderModel->recordPayment((int)$out['oid'], post('payment_method'), $summary['total'], $txnId);
        }

        // Everything succeeded – commit
        $pdo->commit();

        // Update session points
        if ($points > 0) {
            $_SESSION['loyalty_points'] = max(0, $loyaltyPts - $points);
        }

        setFlash('success', 'Order placed! Thank you for your purchase.');
        redirect(BASE_URL . 'order_success.php?order=' . urlencode($out['onum']));

    } catch (Exception $e) {
        // Something went wrong – rollback everything (order, stock, points, etc.)
        $pdo->rollBack();
        $errors[] = $e->getMessage();
    }
}

// ── From here on only GET requests (or POST with errors) ────────
$pageTitle = 'Checkout';
require_once __DIR__ . '/views/layouts/header.php';
$summary = $cart->getSummary(post('coupon_code', ''), 0);
?>

<div class="container py-4">
    <!-- Breadcrumb Steps Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h3 class="fw-extrabold mb-0 d-flex align-items-center gap-2">
            <span class="text-primary"><i class="fas fa-lock"></i></span> Secure Checkout
        </h3>
        <div class="d-flex align-items-center gap-2 small bg-body p-2 px-3 rounded-pill border">
            <a href="<?= BASE_URL ?>cart.php" class="text-primary text-decoration-none fw-bold"><i class="fas fa-shopping-bag me-1"></i>Cart</a>
            <i class="fas fa-chevron-right text-muted" style="font-size:0.75rem;"></i>
            <span class="fw-extrabold text-primary">Checkout</span>
            <i class="fas fa-chevron-right text-muted" style="font-size:0.75rem;"></i>
            <span class="text-muted">Confirmation</span>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger rounded-3 shadow-sm mb-4">
        <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following issues:</div>
        <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST" id="checkoutForm" novalidate>
        <?= csrfField() ?>
        <div class="row g-4">
            <!-- Left Column: Shipping + Payment + Discounts -->
            <div class="col-lg-7">
                <!-- 1. Shipping Address Card -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                    <div class="card-header bg-body border-bottom p-3 d-flex align-items-center gap-2 fw-bold fs-6">
                        <span class="checkout-step-badge">1</span>
                        <span>Shipping & Delivery Details</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-control" value="<?= clean($profile['first_name'] ?? '') ?>" placeholder="John" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-control" value="<?= clean($profile['last_name'] ?? '') ?>" placeholder="Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" value="<?= clean($profile['email'] ?? '') ?>" placeholder="john@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" name="phone" class="form-control" value="<?= clean($profile['phone'] ?? '') ?>" placeholder="024 123 4567" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country *</label>
                                <select name="country" class="form-select" required>
                                    <option value="Ghana" <?= ($profile['country'] ?? '') === 'Ghana' ? 'selected' : '' ?>>Ghana</option>
                                    <option value="Nigeria" <?= ($profile['country'] ?? '') === 'Nigeria' ? 'selected' : '' ?>>Nigeria</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City / Town *</label>
                                <input type="text" name="city" class="form-control" value="<?= clean($profile['city'] ?? '') ?>" placeholder="Accra, Kumasi, etc." required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Street Address *</label>
                                <input type="text" name="address" class="form-control" value="<?= clean($profile['address'] ?? '') ?>" placeholder="House / Flat number, Street name, Digital GPS" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Region / State</label>
                                <input type="text" name="state" class="form-control" value="<?= clean($profile['state'] ?? '') ?>" placeholder="Greater Accra">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Delivery Instructions (Optional)</label>
                                <input type="text" name="notes" class="form-control" placeholder="E.g., Call upon arrival, leave at gate">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Payment Method Card -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                    <div class="card-header bg-body border-bottom p-3 d-flex align-items-center gap-2 fw-bold fs-6">
                        <span class="checkout-step-badge">2</span>
                        <span>Select Payment Method</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex flex-column gap-3">
                            <?php
                            $payMethods = [
                                ['mobile_money', 'fas fa-mobile-alt text-warning', 'Mobile Money (MoMo / Telecel)', 'Instant & secure checkout via MTN Mobile Money or Telecel Cash.'],
                                ['cash_on_delivery', 'fas fa-money-bill-wave text-success', 'Cash on Delivery', 'Pay in cash directly to our delivery courier when the package arrives.'],
                                ['bank_transfer', 'fas fa-university text-primary', 'Direct Bank Transfer / Card', 'Make payment directly into our bank account or via online banking.'],
                            ];
                            foreach ($payMethods as [$val, $icon, $label, $desc]):
                            ?>
                            <label class="pay-option d-flex align-items-start gap-3 cursor-pointer <?= $val === 'mobile_money' ? 'selected' : '' ?>" style="cursor:pointer;">
                                <input type="radio" name="payment_method" value="<?= $val ?>" class="form-check-input mt-1 flex-shrink-0" <?= $val === 'mobile_money' ? 'checked' : '' ?> required>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="<?= $icon ?> fs-5"></i>
                                        <span class="fw-bold fs-6"><?= $label ?></span>
                                    </div>
                                    <small class="text-muted d-block"><?= $desc ?></small>
                                    <?php if ($val === 'mobile_money'): ?>
                                    <div class="mt-3 p-3 bg-body-tertiary rounded-3" id="momoFields">
                                        <label class="form-label small fw-bold">Mobile Money Number</label>
                                        <input type="text" name="momo_number" class="form-control form-control-sm" placeholder="Enter 10-digit number (e.g., 024 123 4567)">
                                        <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>A prompt will be sent to your phone to approve the transaction.</small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 3. Coupon & Loyalty Discounts -->
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-header bg-body border-bottom p-3 d-flex align-items-center gap-2 fw-bold fs-6">
                        <span class="checkout-step-badge">3</span>
                        <span>Discounts & Rewards</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label small">Have a Coupon Code?</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body"><i class="fas fa-ticket-alt text-muted"></i></span>
                                <input type="text" name="coupon_code" id="couponInput" class="form-control" placeholder="Enter coupon code">
                                <button type="button" class="btn btn-primary fw-bold" id="applyCouponBtn" onclick="applyCoupon()">Apply Coupon</button>
                            </div>
                        </div>

                        <?php if ($loyaltyPts > 0): ?>
                        <div class="d-flex align-items-center gap-3 p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 flex-wrap">
                            <i class="fas fa-coins text-warning fs-4"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold small">You have <?= number_format($loyaltyPts) ?> reward points</div>
                                <small class="text-muted">Redeem up to <?= formatPrice($loyaltyPts / POINTS_TO_GHS) ?> on this order</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="number" name="points_to_use" id="pointsInput" class="form-control form-control-sm" min="0" max="<?= $loyaltyPts ?>" value="0" style="max-width:100px;">
                                <span class="small text-muted">pts</span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sticky Order Review -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm position-sticky" style="top: 90px; border-radius:16px; overflow:hidden;">
                    <div class="card-header bg-body border-bottom p-3 fw-bold fs-6">
                        <i class="fas fa-shopping-bag text-primary me-2"></i>Order Summary (<?= count($cartItems) ?> items)
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" style="max-height:300px; overflow-y:auto;">
                            <?php foreach ($cartItems as $item): ?>
                            <li class="list-group-item d-flex gap-3 align-items-center p-3 bg-transparent">
                                <img src="<?= productImageUrl($item['product_image'] ?? '') ?>" width="50" height="50" class="rounded-3 border" style="object-fit:contain;background:#fff;padding:3px;">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="small fw-bold text-truncate mb-1"><?= clean($item['product_name']) ?></div>
                                    <small class="text-muted">Qty: <?= $item['quantity'] ?> × <?= formatPrice($item['price']) ?></small>
                                </div>
                                <div class="fw-bold text-body small"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="p-4 bg-body border-top">
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span>Subtotal</span>
                                <span class="fw-bold text-body"><?= formatPrice($summary['subtotal']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span>Estimated Tax (<?= DEFAULT_TAX_RATE ?>% VAT)</span>
                                <span class="fw-bold text-body"><?= formatPrice($summary['tax']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-3">
                                <span>Shipping Fee</span>
                                <span><?= $summary['shipping'] > 0 ? formatPrice($summary['shipping']) : '<span class="badge bg-success bg-opacity-10 text-success fw-bold">Free</span>' ?></span>
                            </div>
                            <hr class="my-3">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="fw-bold fs-5">Final Total</span>
                                <span class="fw-extrabold fs-3 text-primary" id="orderTotal"><?= formatPrice($summary['total']) ?></span>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow-sm mb-3">
                                <i class="fas fa-shield-alt me-2"></i>Place Order &amp; Complete
                            </button>
                            <div class="text-center">
                                <small class="text-muted"><i class="fas fa-lock text-success me-1"></i>Guaranteed 256-bit SSL encrypted checkout</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('[name="payment_method"]').forEach(r => {
    r.addEventListener('change', () => {
        document.querySelectorAll('.pay-option').forEach(el => el.classList.remove('selected'));
        r.closest('.pay-option')?.classList.add('selected');
        document.getElementById('momoFields')?.classList.toggle('d-none', r.value !== 'mobile_money');
    });
});
</script>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>