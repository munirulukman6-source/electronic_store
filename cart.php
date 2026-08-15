<?php
// cart.php — Modern Responsive Shopping Cart
$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/views/layouts/header.php';
$cartItems = $cart->getItems();
$summary   = $cart->getSummary();
?>
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h3 class="fw-extrabold mb-0 d-flex align-items-center gap-2">
            <span class="text-primary"><i class="fas fa-shopping-bag"></i></span> Shopping Cart
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill fs-6 fw-bold"><?= $cart->getCount() ?> item<?= $cart->getCount() !== 1 ? 's' : '' ?></span>
        </h3>
        <a href="<?= BASE_URL ?>shop.php" class="btn btn-outline-primary btn-sm fw-bold">
            <i class="fas fa-arrow-left me-1"></i>Continue Shopping
        </a>
    </div>

    <?php if (empty($cartItems)): ?>
    <div class="card border-0 shadow-sm p-5 text-center my-4" style="border-radius:18px;">
        <div style="width:90px;height:90px;border-radius:50%;background:rgba(37,99,235,0.08);color:var(--es-primary);display:inline-flex;align-items:center;justify-content:center;font-size:2.5rem;margin:0 auto 1.5rem;">
            <i class="fas fa-shopping-bag"></i>
        </div>
        <h4 class="fw-bold mb-2">Your shopping cart is empty</h4>
        <p class="text-muted mb-4 mx-auto" style="max-width:420px;">Explore the latest tech deals, flagship phones, and top gaming gear to add items to your cart.</p>
        <div>
            <a href="<?= BASE_URL ?>shop.php" class="btn btn-primary btn-lg px-5 fw-bold">
                <i class="fas fa-store me-2"></i>Explore Products
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <!-- Cart Items List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th class="ps-4 py-3">Product Details</th>
                                <th class="text-center py-3">Quantity</th>
                                <th class="text-end py-3">Unit Price</th>
                                <th class="text-end py-3">Total</th>
                                <th class="pe-4 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cartTable">
                            <?php foreach ($cartItems as $item): ?>
                            <tr id="cart-row-<?= $item['product_id'] ?>">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= productImageUrl($item['product_image'] ?? '') ?>" class="cart-item-img" alt="">
                                        <div>
                                            <a href="<?= BASE_URL ?>product/<?= clean($item['slug'] ?? '') ?>" class="fw-bold text-decoration-none text-body fs-6 d-block mb-1">
                                                <?= clean($item['product_name']) ?>
                                            </a>
                                            <div class="small text-muted mb-1">SKU: <span class="font-monospace"><?= clean($item['sku'] ?? '—') ?></span></div>
                                            <?php if ($item['stock'] < 10): ?>
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" style="font-size:0.7rem;"><i class="fas fa-exclamation-triangle me-1"></i>Only <?= $item['stock'] ?> left</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center py-3">
                                    <div class="qty-control d-inline-flex" data-product-id="<?= $item['product_id'] ?>">
                                        <button class="qty-btn qty-minus">−</button>
                                        <input class="qty-input" type="number" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" data-max="<?= $item['stock'] ?>" data-product-id="<?= $item['product_id'] ?>">
                                        <button class="qty-btn qty-plus">+</button>
                                    </div>
                                </td>
                                <td class="text-end py-3 text-muted"><?= formatPrice($item['price']) ?></td>
                                <td class="text-end py-3 fw-extrabold text-primary fs-6"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                                <td class="pe-4 py-3 text-center">
                                    <button class="btn btn-sm btn-outline-danger rounded-circle p-2" onclick="Cart.remove(<?= $item['product_id'] ?>)" title="Remove item" style="width:34px;height:34px;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-body border-0 p-3 d-flex justify-content-between align-items-center">
                    <button class="btn btn-outline-danger btn-sm fw-bold" onclick="if(confirm('Clear all items from your cart?')) location.href='<?= BASE_URL ?>ajax/cart.php?action=clear'">
                        <i class="fas fa-trash me-1"></i>Clear Shopping Cart
                    </button>
                    <small class="text-muted"><i class="fas fa-shield-alt text-success me-1"></i>Free & secure checkout guaranteed</small>
                </div>
            </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm position-sticky" style="top: 90px; border-radius:16px;">
                <div class="card-header bg-body border-bottom p-3 fw-bold fs-6">
                    <i class="fas fa-receipt text-primary me-2"></i>Order Summary
                </div>
                <div class="card-body p-4">
                    <!-- Coupon code input -->
                    <div class="mb-3">
                        <label class="form-label small text-muted">Promo / Coupon Code</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body border-end-0"><i class="fas fa-ticket-alt text-muted"></i></span>
                            <input type="text" id="couponInput" class="form-control border-start-0" placeholder="Enter coupon code">
                            <button class="btn btn-primary fw-bold" id="applyCouponBtn" onclick="applyCoupon()">Apply</button>
                        </div>
                    </div>

                    <!-- Loyalty points section -->
                    <?php if (isLoggedIn() && isCustomer() && ($_SESSION['loyalty_points'] ?? 0) > 0): ?>
                    <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-coins text-warning fs-5"></i>
                            <span class="small fw-bold">Reward Points Available</span>
                        </div>
                        <div class="small text-muted mb-2">You have <strong><?= number_format($_SESSION['loyalty_points']) ?> pts</strong> (worth <?= formatPrice($_SESSION['loyalty_points'] / POINTS_TO_GHS) ?>)</div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" id="pointsInput" class="form-control form-control-sm" min="0" max="<?= $_SESSION['loyalty_points'] ?>" value="0" style="max-width:100px;">
                            <span class="small text-muted">points to redeem</span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Subtotal</span>
                        <span class="fw-bold text-body" id="cartSubtotal"><?= formatPrice($summary['subtotal']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small d-none" id="discountRow">
                        <span class="text-success">Discount</span>
                        <span class="text-success fw-bold" id="discountAmt">-<?= formatPrice($summary['discount']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Estimated Tax (<?= DEFAULT_TAX_RATE ?>% VAT)</span>
                        <span class="fw-bold text-body"><?= formatPrice($summary['tax']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 small text-muted">
                        <span>Estimated Delivery</span>
                        <span><?= $summary['shipping'] > 0 ? formatPrice($summary['shipping']) : '<span class="badge bg-success bg-opacity-10 text-success fw-bold">Free</span>' ?></span>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold fs-5">Estimated Total</span>
                        <span class="fw-extrabold fs-4 text-primary" id="orderTotal"><?= formatPrice($summary['total']) ?></span>
                    </div>

                    <?php if (isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>checkout.php" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow-sm">
                        <i class="fas fa-lock me-2"></i>Proceed to Checkout
                    </a>
                    <?php else: ?>
                    <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow-sm">
                        <i class="fas fa-sign-in-alt me-2"></i>Login to Checkout
                    </a>
                    <?php endif; ?>

                    <div class="d-flex justify-content-center gap-3 mt-4 pt-2 border-top">
                        <small class="text-muted"><i class="fas fa-shield-alt text-success me-1"></i>Encrypted</small>
                        <small class="text-muted"><i class="fas fa-undo text-primary me-1"></i>30-Day Returns</small>
                        <small class="text-muted"><i class="fas fa-truck text-info me-1"></i>Fast Dispatch</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>
