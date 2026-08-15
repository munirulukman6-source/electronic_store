<?php
/**
 * Product Card Partial — Modern v3 with enhanced micro-interactions
 */
$imgUrl = productImageUrl(
    $p['primary_image'] ?? null,
    $p['category_slug'] ?? '',
    $p['product_name']  ?? ''
);
$effectivePrice = (float)($p['effective_price'] ?? $p['price']);
$originalPrice  = (float)$p['price'];
$hasDiscount    = $effectivePrice < $originalPrice - 0.01;
$discPct        = $hasDiscount ? round((1 - $effectivePrice / $originalPrice) * 100) : 0;
$isWishlisted   = false;
if (isLoggedIn() && isCustomer() && isset($_SESSION['customer_id'])) {
    static $wlInstance = null;
    if (!$wlInstance) $wlInstance = new Wishlist();
    $isWishlisted = $wlInstance->isWishlisted((int)$_SESSION['customer_id'], (int)$p['id']);
}
$inStock = ($p['quantity'] ?? 0) > 0;
?>
<div class="col-6 col-sm-6 col-md-4 col-lg-3">
    <div class="card product-card h-100 position-relative">

        <div class="product-badges">
            <?php if (!empty($p['is_new_arrival'])): ?><span class="badge-pill-custom badge-new">New</span><?php endif; ?>
            <?php if ($hasDiscount): ?><span class="badge-pill-custom badge-sale">-<?= $discPct ?>%</span><?php endif; ?>
            <?php if (!empty($p['is_best_seller'])): ?><span class="badge-pill-custom badge-hot">Hot</span><?php endif; ?>
            <?php if (!$inStock): ?><span class="badge-pill-custom badge-out">Out</span><?php endif; ?>
        </div>

        <div class="product-actions">
            <button class="action-btn btn-wishlist <?= $isWishlisted ? 'wishlisted' : '' ?>"
                    data-product-id="<?= (int)$p['id'] ?>"
                    title="<?= $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                <i class="<?= $isWishlisted ? 'fas' : 'far' ?> fa-heart"></i>
            </button>
            <button class="action-btn btn-compare"
                    data-product-id="<?= (int)$p['id'] ?>"
                    data-product-name="<?= htmlspecialchars($p['product_name'], ENT_QUOTES) ?>"
                    data-product-img="<?= htmlspecialchars($imgUrl, ENT_QUOTES) ?>"
                    title="Compare">
                <i class="fas fa-exchange-alt"></i>
            </button>
            <button class="action-btn" onclick="quickView('<?= htmlspecialchars($p['slug'], ENT_QUOTES) ?>')" title="Quick View">
                <i class="fas fa-eye"></i>
            </button>
        </div>

        <a href="<?= BASE_URL ?>product/<?= htmlspecialchars($p['slug'], ENT_QUOTES) ?>"
           class="card-img-top-wrap text-decoration-none d-block">
            <?php if (!empty($p['flash_end'])): ?>
            <div class="flash-timer-badge">
                <i class="fas fa-bolt"></i>
                <span class="countdown" data-end="<?= strtotime($p['flash_end']) * 1000 ?>">00:00:00</span>
            </div>
            <?php endif; ?>
            <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES) ?>"
                 alt="<?= htmlspecialchars($p['product_name'] ?? '', ENT_QUOTES) ?>"
                 loading="lazy" width="400" height="300"
                 onerror="this.onerror=null;this.src='<?= BASE_URL ?>assets/images/product_img.php?cat=<?= urlencode($p['category_slug'] ?? 'electronics') ?>&name=<?= urlencode($p['product_name'] ?? '') ?>'">
        </a>

        <div class="card-body">
            <div class="product-brand"><?= htmlspecialchars($p['brand_name'] ?? '', ENT_QUOTES) ?></div>
            <a href="<?= BASE_URL ?>product/<?= htmlspecialchars($p['slug'], ENT_QUOTES) ?>" class="text-decoration-none">
                <div class="product-name"><?= htmlspecialchars($p['product_name'] ?? '', ENT_QUOTES) ?></div>
            </a>
            <?php if (($p['review_count'] ?? 0) > 0): ?>
            <div class="d-flex align-items-center gap-1 mb-2">
                <?= starRating((float)($p['avg_rating'] ?? 0)) ?>
                <span class="rating-count">(<?= (int)$p['review_count'] ?>)</span>
            </div>
            <?php endif; ?>
            <div class="price-block">
                <span class="price-current"><?= formatPrice($effectivePrice) ?></span>
                <?php if ($hasDiscount): ?>
                    <span class="price-old"><?= formatPrice($originalPrice) ?></span>
                    <span class="price-badge">-<?= $discPct ?>%</span>
                <?php endif; ?>
            </div>
            <?php if ($inStock): ?>
                <button class="btn btn-add-cart" data-product-id="<?= (int)$p['id'] ?>">
                    <i class="fas fa-shopping-bag me-1"></i>Add to Cart
                </button>
            <?php else: ?>
                <button class="btn btn-secondary btn-sm w-100" disabled style="border-radius:8px;font-weight:700;">
                    <i class="fas fa-times-circle me-1"></i>Out of Stock
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
