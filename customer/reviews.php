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

$pageTitle = 'My Reviews';
$db      = Database::getInstance();
$page    = max(1, (int)get('page', 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$total   = $db->count('reviews', 'customer_id = ?', [$_SESSION['customer_id']]);
$reviews = $db->fetchAll(
    "SELECT r.*, p.product_name, p.slug AS product_slug, pi.image_path AS product_image
     FROM reviews r
     JOIN products p ON r.product_id = p.id
     LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
     WHERE r.customer_id = ?
     ORDER BY r.created_at DESC LIMIT ? OFFSET ?",
    [$_SESSION['customer_id'], $perPage, $offset]
);
$pages = max(1, ceil($total / $perPage));

require_once ROOT_PATH . 'views/layouts/header.php';
?>
<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="list-group list-group-flush p-2">
                    <a href="<?= BASE_URL ?>customer/profile.php" class="list-group-item list-group-item-action rounded-2 py-2"><i class="fas fa-user me-2 text-primary"></i>My Profile</a>
                    <a href="<?= BASE_URL ?>customer/orders.php" class="list-group-item list-group-item-action rounded-2 py-2"><i class="fas fa-box me-2 text-success"></i>My Orders</a>
                    <a href="<?= BASE_URL ?>wishlist.php" class="list-group-item list-group-item-action rounded-2 py-2"><i class="fas fa-heart me-2 text-danger"></i>Wishlist</a>
                    <a href="<?= BASE_URL ?>customer/loyalty.php" class="list-group-item list-group-item-action rounded-2 py-2"><i class="fas fa-coins me-2 text-warning"></i>Loyalty Points</a>
                    <a href="<?= BASE_URL ?>customer/reviews.php" class="list-group-item list-group-item-action active rounded-2 py-2"><i class="fas fa-star me-2 text-warning"></i>My Reviews</a>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <h4 class="fw-extrabold mb-4 d-flex align-items-center gap-2">
                <i class="fas fa-star text-warning"></i> My Product Reviews
            </h4>

            <?php if (empty($reviews)): ?>
            <div class="text-center py-5 bg-body rounded-4 border shadow-sm">
                <i class="far fa-star fa-3x text-muted opacity-50 mb-3"></i>
                <h5 class="fw-bold text-muted">No reviews yet</h5>
                <p class="text-muted small">You haven't reviewed any items. Leave reviews on products you've purchased!</p>
                <a href="<?= BASE_URL ?>shop.php" class="btn btn-primary btn-sm fw-bold">Browse Shop</a>
            </div>
            <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($reviews as $rev): ?>
                <div class="card border-0 shadow-sm p-3" style="border-radius:14px;">
                    <div class="d-flex align-items-start gap-3">
                        <img src="<?= productImageUrl($rev['product_image'] ?? '') ?>" width="60" height="60" style="object-fit:contain;border-radius:8px;background:#fff;padding:3px;" class="border">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1">
                                <a href="<?= BASE_URL ?>product/<?= clean($rev['product_slug']) ?>" class="text-decoration-none text-body">
                                    <?= clean($rev['product_name']) ?>
                                </a>
                            </h6>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="star-rating">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <i class="<?= $s <= $rev['rating'] ? 'fas text-warning' : 'far text-muted' ?> fa-star small"></i>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted"><?= formatDate($rev['created_at']) ?></small>
                            </div>
                            <?php if ($rev['title']): ?>
                            <div class="fw-bold small mb-1"><?= clean($rev['title']) ?></div>
                            <?php endif; ?>
                            <p class="small text-muted mb-0"><?= nl2br(clean($rev['comment'])) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
