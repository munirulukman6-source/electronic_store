<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'classes/Cart.php';
require_once ROOT_PATH . 'classes/Inventory.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();

// Auth check must happen before any HTML output
requireLogin();
if (!isCustomer()) redirect(BASE_URL);

$pageTitle = 'Loyalty Points';
$loyalty   = new Loyalty();
$balance   = $loyalty->getBalance($_SESSION['customer_id']);
$tiers     = $loyalty->getTiers();
$history   = $loyalty->getHistory($_SESSION['customer_id'], max(1,(int)get('page',1)));

$db      = Database::getInstance();
$profile = $db->fetchOne(
    "SELECT c.*, ct.tier_name, ct.badge_color, ct.badge_icon, ct.min_points, ct.max_points,
            ct.discount_percentage, ct.free_shipping, ct.cashback_rate, ct.benefits
     FROM customers c LEFT JOIN customer_tiers ct ON c.tier_id = ct.id
     WHERE c.user_id = ?", [$_SESSION['user_id']]
);
$tierClass = strtolower(str_replace(' ','-', $profile['tier_name'] ?? 'bronze'));

require_once ROOT_PATH . 'views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Account nav -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-body p-3 text-center border-bottom">
                    <div class="rounded-circle bg-primary bg-opacity-15 d-flex align-items-center justify-content-center mx-auto mb-2" style="width:56px;height:56px">
                        <i class="fas fa-user-circle fa-2x text-primary"></i>
                    </div>
                    <div class="fw-bold"><?= clean($_SESSION['full_name']) ?></div>
                    <small class="text-muted"><?= clean($_SESSION['email']) ?></small>
                </div>
                <div class="list-group list-group-flush p-2">
                    <a href="<?= BASE_URL ?>customer/profile.php" class="list-group-item list-group-item-action rounded-2 py-2"><i class="fas fa-user me-2 text-primary"></i>My Profile</a>
                    <a href="<?= BASE_URL ?>customer/orders.php" class="list-group-item list-group-item-action rounded-2 py-2"><i class="fas fa-box me-2 text-success"></i>My Orders</a>
                    <a href="<?= BASE_URL ?>wishlist.php" class="list-group-item list-group-item-action rounded-2 py-2"><i class="fas fa-heart me-2 text-danger"></i>Wishlist</a>
                    <a href="<?= BASE_URL ?>customer/loyalty.php" class="list-group-item list-group-item-action active rounded-2 py-2"><i class="fas fa-coins me-2 text-warning"></i>Loyalty Points</a>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <h4 class="fw-extrabold mb-4 d-flex align-items-center gap-2">
                <i class="fas fa-coins text-warning"></i> Loyalty Points &amp; Rewards
            </h4>

            <!-- Tier card -->
            <div class="tier-card tier-<?= $tierClass ?> mb-4 shadow-sm">
                <div class="row align-items-center g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <i class="fas <?= clean($profile['badge_icon'] ?? 'fa-medal') ?> fa-3x text-white opacity-90"></i>
                            <div>
                                <div class="fs-4 fw-extrabold text-white"><?= clean($profile['tier_name'] ?? 'Bronze') ?> Member</div>
                                <div class="text-white opacity-75 small"><?= clean($_SESSION['full_name']) ?></div>
                            </div>
                        </div>
                        <div class="fs-1 fw-extrabold text-white"><?= number_format($balance) ?></div>
                        <div class="text-white opacity-75 small">Available Reward Points</div>
                        <div class="text-white opacity-90 small mt-1 fw-semibold">≈ <?= formatPrice($balance / POINTS_TO_GHS) ?> discount value</div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="text-white opacity-75 small mb-2 fw-bold text-uppercase">Active Tier Benefits</div>
                        <div class="d-flex flex-column gap-1 text-white small">
                            <?php if ($profile['discount_percentage'] > 0): ?>
                            <div><i class="fas fa-check-circle me-1 text-warning"></i><?= $profile['discount_percentage'] ?>% storewide discount</div>
                            <?php endif; ?>
                            <?php if ($profile['cashback_rate'] > 0): ?>
                            <div><i class="fas fa-check-circle me-1 text-warning"></i><?= $profile['cashback_rate'] ?>% cashback in points</div>
                            <?php endif; ?>
                            <?php if ($profile['free_shipping']): ?>
                            <div><i class="fas fa-check-circle me-1 text-warning"></i>Free shipping on all orders</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tiers Overview -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                <div class="card-header bg-body border-bottom p-3 fw-bold">
                    <i class="fas fa-crown text-warning me-2"></i>Membership Tiers
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <?php foreach ($tiers as $t): ?>
                        <div class="col-md-3 col-6">
                            <div class="p-3 rounded-3 text-center border <?= ($profile['tier_name'] ?? '') === $t['tier_name'] ? 'border-primary bg-primary bg-opacity-10' : 'bg-body' ?>">
                                <div class="fw-bold mb-1"><?= clean($t['tier_name']) ?></div>
                                <div class="small text-muted mb-2"><?= number_format($t['min_points']) ?>+ pts</div>
                                <span class="badge bg-secondary small"><?= $t['discount_percentage'] ?>% off</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
