<?php
// wishlist.php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'classes/Cart.php';
require_once ROOT_PATH . 'classes/Inventory.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();

// Auth check must happen before any HTML output
requireLogin();

$pageTitle = 'My Wishlist';
$wl        = new Wishlist();
$items     = $wl->getItems($_SESSION['customer_id']);

require_once __DIR__ . '/views/layouts/header.php';
?>
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-extrabold mb-0 d-flex align-items-center gap-2">
            <i class="fas fa-heart text-danger"></i> My Wishlist
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1 fs-6"><?= count($items) ?> items</span>
        </h3>
        <a href="<?= BASE_URL ?>shop.php" class="btn btn-outline-primary btn-sm fw-bold">
            <i class="fas fa-plus me-1"></i>Discover More
        </a>
    </div>

    <?php if (empty($items)): ?>
    <div class="text-center py-5 bg-body rounded-4 border shadow-sm">
        <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px">
            <i class="far fa-heart fa-3x text-danger opacity-50"></i>
        </div>
        <h4 class="fw-bold mb-2">Your wishlist is empty</h4>
        <p class="text-muted mb-4 mx-auto" style="max-width:420px;">Save items you love by clicking the heart icon on any product to easily purchase them later.</p>
        <a href="<?= BASE_URL ?>shop.php" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm">
            <i class="fas fa-store me-2"></i>Explore Products
        </a>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($items as $p):
            $p['primary_image']    = $p['primary_image'] ?? null;
            $p['is_new_arrival']   = 0;
            $p['is_best_seller']   = 0;
            $p['review_count']     = 0;
            $p['flash_end']        = null;
            include __DIR__ . '/views/products/_card.php';
        endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>
