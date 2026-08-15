<?php
// ajax/quick_view.php — Returns product quick-view HTML
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'classes/Cart.php';
require_once ROOT_PATH . 'classes/Inventory.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();

$slug    = get('slug', '');
if (!$slug) { echo '<div class="p-4 text-center text-danger">Product not found.</div>'; exit; }

$model   = new Product();
$product = $model->getBySlug($slug);
if (!$product) { echo '<div class="p-4 text-center text-danger">Product not found.</div>'; exit; }

$imgUrl      = productImageUrl($product['images'][0]['image_path'] ?? null, $product['category_slug'] ?? '', $product['product_name']);
$effectivePrice = (float)($product['effective_price'] ?? $product['price']);
$hasDiscount = $effectivePrice < (float)$product['price'] - 0.01;
$discPct     = $hasDiscount ? round((1 - $effectivePrice / $product['price']) * 100) : 0;
$inStock     = ($product['quantity'] ?? 0) > 0;
?>
<div class="row g-0">
    <!-- Image -->
    <div class="col-md-5 bg-light d-flex align-items-center justify-content-center p-3" style="min-height:320px">
        <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES) ?>"
             id="qvMainImage"
             alt="<?= clean($product['product_name']) ?>"
             style="max-height:300px;max-width:100%;object-fit:cover;border-radius:12px"
             onerror="this.src='<?= BASE_URL ?>assets/images/product_img.php?cat=<?= urlencode($product['category_slug']??'') ?>&name=<?= urlencode($product['product_name']) ?>'">
    </div>

    <!-- Info -->
    <div class="col-md-7 p-4">
        <span class="badge bg-light text-dark border mb-2"><?= clean($product['brand_name']) ?></span>
        <h5 class="fw-bold mb-1"><?= clean($product['product_name']) ?></h5>
        <?php if ($product['model']): ?>
        <p class="text-muted small mb-2">Model: <?= clean($product['model']) ?></p>
        <?php endif; ?>

        <!-- Rating -->
        <?php if ($product['review_count'] > 0): ?>
        <div class="d-flex align-items-center gap-1 mb-2">
            <?= starRating($product['avg_rating']) ?>
            <span class="text-muted small">(<?= $product['review_count'] ?> reviews)</span>
        </div>
        <?php endif; ?>

        <!-- Price -->
        <div class="mb-3">
            <span class="fw-bold fs-4 text-primary"><?= formatPrice($effectivePrice) ?></span>
            <?php if ($hasDiscount): ?>
                <span class="text-muted text-decoration-line-through ms-2"><?= formatPrice($product['price']) ?></span>
                <span class="badge bg-danger ms-1">-<?= $discPct ?>%</span>
            <?php endif; ?>
        </div>

        <!-- Flash sale -->
        <?php if ($product['flash_id'] ?? null): ?>
        <div class="alert alert-danger py-2 small mb-3">
            <i class="fas fa-bolt me-1"></i>Flash deal ends in:
            <strong class="countdown ms-1" data-end="<?= strtotime($product['flash_end']) * 1000 ?>">00:00:00</strong>
        </div>
        <?php endif; ?>

        <!-- Short description -->
        <?php if ($product['short_description']): ?>
        <p class="text-muted small mb-3"><?= clean($product['short_description']) ?></p>
        <?php endif; ?>

        <!-- Stock -->
        <div class="mb-3">
            <?php if ($inStock): ?>
            <span class="text-success small fw-semibold"><i class="fas fa-check-circle me-1"></i>In Stock
                <?php if ($product['quantity'] < 10): ?><span class="text-warning ms-1">(Only <?= $product['quantity'] ?> left)</span><?php endif; ?>
            </span>
            <?php else: ?>
            <span class="text-danger small fw-semibold"><i class="fas fa-times-circle me-1"></i>Out of Stock</span>
            <?php endif; ?>
        </div>

        <!-- Add to cart -->
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <div class="qty-control">
                <button class="qty-btn qty-minus">−</button>
                <input class="qty-input" type="number" id="qvQty" value="1" min="1" max="<?= $product['quantity'] ?>" data-max="<?= $product['quantity'] ?>">
                <button class="qty-btn qty-plus">+</button>
            </div>
            <?php if ($inStock): ?>
            <button class="btn btn-primary fw-semibold flex-grow-1" id="qvAddToCart" data-product-id="<?= $product['id'] ?>">
                <i class="fas fa-cart-plus me-2"></i>Add to Cart
            </button>
            <?php else: ?>
            <button class="btn btn-secondary flex-grow-1" disabled>Out of Stock</button>
            <?php endif; ?>
        </div>

        <!-- Thumbnail strip -->
        <?php if (count($product['images']) > 1): ?>
        <div class="d-flex gap-2 flex-wrap mb-3">
            <?php foreach ($product['images'] as $i => $img): ?>
            <div class="thumb-item <?= $i === 0 ? 'active' : '' ?>"
                 data-full="<?= productImageUrl($img['image_path'], $product['category_slug'] ?? '', $product['product_name']) ?>"
                 onclick="document.getElementById('qvMainImage').src=this.dataset.full;document.querySelectorAll('.thumb-item').forEach(t=>t.classList.remove('active'));this.classList.add('active')"
                 style="width:50px;height:50px;cursor:pointer">
                <img src="<?= productImageUrl($img['image_path'], $product['category_slug'] ?? '', $product['product_name']) ?>" alt="">
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- View full details -->
        <a href="<?= BASE_URL ?>product/<?= clean($product['slug']) ?>"
           class="btn btn-outline-secondary btn-sm w-100">
            <i class="fas fa-expand me-1"></i>View Full Details
        </a>
    </div>
</div>
<script>
document.getElementById('qvAddToCart')?.addEventListener('click', function () {
    const qty = parseInt(document.getElementById('qvQty')?.value || 1);
    Cart.add(<?= $product['id'] ?>, qty, this);
});
initQtyControls();
initCountdowns();
</script>
