<?php
// product.php — Dynamic product detail page
$slug = trim($_GET['slug'] ?? basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '.php'));
if (!$slug) { header('Location: ' . BASE_URL . 'shop.php'); exit; }

require_once __DIR__ . '/views/layouts/header.php';

$productModel = new Product();
$product      = $productModel->getBySlug($slug);

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Product Not Found';
    echo '<div class="container py-5 text-center"><h2 class="fw-bold">Product not found.</h2><p class="text-muted">The product you are looking for might have been removed or is temporarily unavailable.</p><a href="' . BASE_URL . 'shop.php" class="btn btn-primary mt-2">Browse Store</a></div>';
    require_once __DIR__ . '/views/layouts/footer.php';
    exit;
}

$pageTitle   = $product['product_name'] . ' — ' . $product['brand_name'];
$related     = $productModel->getRelated($product['id'], $product['category_id'], $product['brand_id'], 4);
$isWishlisted= isLoggedIn() && isCustomer() ? (new Wishlist())->isWishlisted($_SESSION['customer_id'], $product['id']) : false;
$hasDiscount = $product['effective_price'] < $product['price'];
$discPct     = $hasDiscount ? round((1 - $product['effective_price'] / $product['price']) * 100) : 0;
$inStock     = $product['quantity'] > 0;
$specs       = $product['specifications'] ?? [];
$images      = $product['images'];

// Track recently viewed
if (isLoggedIn() && isCustomer()) {
    $db = Database::getInstance();
    try {
        $db->query("INSERT INTO recently_viewed (customer_id, product_id) VALUES (?,?) ON DUPLICATE KEY UPDATE viewed_at = NOW()", [$_SESSION['customer_id'], $product['id']]);
    } catch (Exception $e) {}
}
?>

<div class="container py-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>shop.php">Shop</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>shop.php?category=<?= clean($product['category_slug']) ?>"><?= clean($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= truncate(clean($product['product_name']), 35) ?></li>
        </ol>
    </nav>

    <!-- Main Product Showcase -->
    <div class="row g-4 mb-5">

        <!-- Gallery Left -->
        <div class="col-lg-6">
            <div class="position-sticky" style="top: 90px;">
                <div class="product-gallery-main" id="mainImageWrap">
                    <img src="<?= productImageUrl($images[0]['image_path'] ?? '') ?>" alt="<?= clean($product['product_name']) ?>" id="mainProductImage">
                </div>
                <?php if (count($images) > 1): ?>
                <div class="product-gallery-thumbs">
                    <?php foreach ($images as $i => $img): ?>
                    <div class="thumb-item <?= $i === 0 ? 'active' : '' ?>" data-full="<?= productImageUrl($img['image_path']) ?>">
                        <img src="<?= productImageUrl($img['image_path']) ?>" alt="Thumbnail <?= $i + 1 ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Right -->
        <div class="col-lg-6">
            <div class="d-flex flex-column gap-3">
                <!-- Badges & Brand -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="<?= BASE_URL ?>shop.php?brand=<?= clean($product['brand_slug']) ?>" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 text-decoration-none rounded-pill fw-bold">
                        <i class="fas fa-certificate me-1"></i><?= clean($product['brand_name']) ?>
                    </a>
                    <?php if ($product['is_new_arrival']): ?><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill fw-bold">New Arrival</span><?php endif; ?>
                    <?php if ($product['is_best_seller']): ?><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill fw-bold">Best Seller</span><?php endif; ?>
                    <?php if ($product['is_featured']): ?><span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill fw-bold">Featured</span><?php endif; ?>
                </div>

                <h1 class="fs-2 fw-extrabold mb-0"><?= clean($product['product_name']) ?></h1>
                
                <?php if ($product['model']): ?>
                <div class="small text-muted">
                    Model: <strong class="text-body"><?= clean($product['model']) ?></strong>
                    <?php if ($product['sku']): ?> · SKU: <span class="badge bg-light text-muted font-monospace"><?= clean($product['sku']) ?></span><?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Rating -->
                <?php if ($product['review_count'] > 0): ?>
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center text-warning">
                        <?= starRating($product['avg_rating']) ?>
                    </div>
                    <span class="fw-bold"><?= number_format($product['avg_rating'], 1) ?></span>
                    <span class="text-muted">·</span>
                    <a href="#reviews" class="text-primary small text-decoration-none fw-semibold">
                        <?= $product['review_count'] ?> customer review<?= $product['review_count'] != 1 ? 's' : '' ?>
                    </a>
                </div>
                <?php endif; ?>

                <!-- Price Block -->
                <div class="p-3 bg-body rounded-3 border d-flex align-items-baseline gap-3 flex-wrap">
                    <span class="price-current fs-2 text-primary"><?= formatPrice($product['effective_price']) ?></span>
                    <?php if ($hasDiscount): ?>
                        <span class="price-old fs-5"><?= formatPrice($product['price']) ?></span>
                        <span class="badge bg-danger px-2 py-1 rounded-pill fw-bold">-<?= $discPct ?>% OFF</span>
                    <?php endif; ?>
                </div>

                <!-- Flash Sale Countdown (If active) -->
                <?php if ($product['flash_id'] ?? null): ?>
                <div class="alert alert-danger d-flex align-items-center gap-3 mb-0 rounded-3 border-danger border-opacity-25">
                    <div style="width:36px;height:36px;border-radius:50%;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center;color:#ef4444;font-size:1.1rem;">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-danger">Flash Deal Ends In:</div>
                        <span class="countdown fs-5 fw-extrabold text-danger" data-end="<?= strtotime($product['flash_end']) * 1000 ?>">00:00:00</span>
                    </div>
                    <?php if ($product['flash_remaining'] !== null): ?>
                        <span class="badge bg-danger ms-auto px-2 py-1">Only <?= $product['flash_remaining'] ?> left</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Short description -->
                <?php if ($product['short_description']): ?>
                <p class="text-muted lead fs-6 mb-0"><?= clean($product['short_description']) ?></p>
                <?php endif; ?>

                <!-- Stock availability -->
                <div>
                    <?php if ($inStock): ?>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                            <i class="fas fa-check-circle me-1"></i>In Stock (<?= number_format($product['quantity']) ?> available)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                            <i class="fas fa-times-circle me-1"></i>Currently Out of Stock
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Quantity and Add to Cart Section -->
                <div class="d-flex align-items-center gap-3 pt-2 flex-wrap">
                    <div class="qty-control">
                        <button class="qty-btn qty-minus">−</button>
                        <input class="qty-input" type="number" id="mainQtyInput" value="1" min="1" max="<?= $product['quantity'] ?>" data-max="<?= $product['quantity'] ?>">
                        <button class="qty-btn qty-plus">+</button>
                    </div>
                    <?php if ($inStock): ?>
                    <button class="btn btn-primary btn-lg px-4 fw-bold flex-grow-1" id="mainAddToCart" data-product-id="<?= $product['id'] ?>">
                        <i class="fas fa-shopping-bag me-2"></i>Add to Cart
                    </button>
                    <?php else: ?>
                    <button class="btn btn-secondary btn-lg px-4 flex-grow-1" disabled><i class="fas fa-times me-2"></i>Out of Stock</button>
                    <?php endif; ?>
                    <button class="btn btn-outline-danger btn-lg btn-wishlist <?= $isWishlisted ? 'wishlisted' : '' ?>" data-product-id="<?= $product['id'] ?>" title="<?= $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                        <i class="<?= $isWishlisted ? 'fas' : 'far' ?> fa-heart"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-lg btn-compare"
                            data-product-id="<?= $product['id'] ?>"
                            data-product-name="<?= clean($product['product_name']) ?>"
                            data-product-img="<?= productImageUrl($images[0]['image_path'] ?? '') ?>" title="Add to Compare">
                        <i class="fas fa-exchange-alt"></i>
                    </button>
                </div>

                <!-- Trust Guarantees Grid -->
                <div class="row g-2 pt-3">
                    <div class="col-6">
                        <div class="p-2 border rounded-3 d-flex align-items-center gap-2 small text-muted">
                            <i class="fas fa-shield-check text-success fs-5"></i>
                            <div><strong>100% Genuine</strong><br><small>Direct from brand</small></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded-3 d-flex align-items-center gap-2 small text-muted">
                            <i class="fas fa-shipping-fast text-primary fs-5"></i>
                            <div><strong>Fast Delivery</strong><br><small>Safe packaging</small></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded-3 d-flex align-items-center gap-2 small text-muted">
                            <i class="fas fa-undo text-warning fs-5"></i>
                            <div><strong>Easy Returns</strong><br><small>30 days warranty</small></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded-3 d-flex align-items-center gap-2 small text-muted">
                            <i class="fas fa-headset text-info fs-5"></i>
                            <div><strong>24/7 Tech Support</strong><br><small>Expert guidance</small></div>
                        </div>
                    </div>
                </div>

                <!-- Meta & Social Share -->
                <div class="pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 small text-muted">
                    <div>Category: <a href="<?= BASE_URL ?>shop.php?category=<?= clean($product['category_slug']) ?>" class="fw-bold text-decoration-none"><?= clean($product['category_name']) ?></a></div>
                    <div class="d-flex align-items-center gap-2">
                        <span>Share:</span>
                        <a href="https://wa.me/?text=<?= urlencode($product['product_name'] . ' ' . BASE_URL . 'product/' . $product['slug']) ?>" target="_blank" class="social-btn text-decoration-none" style="width:30px;height:30px;font-size:0.75rem;"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . 'product/' . $product['slug']) ?>" target="_blank" class="social-btn text-decoration-none" style="width:30px;height:30px;font-size:0.75rem;"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(BASE_URL . 'product/' . $product['slug']) ?>&text=<?= urlencode($product['product_name']) ?>" target="_blank" class="social-btn text-decoration-none" style="width:30px;height:30px;font-size:0.75rem;"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs: Description, Specs, Reviews, FAQs -->
    <div class="card border-0 shadow-sm mb-5" style="border-radius:16px; overflow:hidden;">
        <div class="card-header bg-body p-3">
            <ul class="nav nav-pills gap-2" id="productTabs">
                <li class="nav-item"><button class="nav-link active rounded-pill fw-bold" data-bs-toggle="tab" data-bs-target="#tabDesc">Description</button></li>
                <?php if (!empty($specs)): ?><li class="nav-item"><button class="nav-link rounded-pill fw-bold" data-bs-toggle="tab" data-bs-target="#tabSpecs">Specifications</button></li><?php endif; ?>
                <li class="nav-item"><button class="nav-link rounded-pill fw-bold" data-bs-toggle="tab" data-bs-target="#tabReviews">Reviews (<?= $product['review_count'] ?>)</button></li>
                <?php if (!empty($product['faqs'])): ?><li class="nav-item"><button class="nav-link rounded-pill fw-bold" data-bs-toggle="tab" data-bs-target="#tabFaq">FAQ</button></li><?php endif; ?>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content">
                <!-- Description -->
                <div class="tab-pane fade show active" id="tabDesc">
                    <?= $product['description'] ? nl2br(clean($product['description'])) : '<p class="text-muted">No description available for this product.</p>' ?>
                </div>

                <!-- Specifications -->
                <?php if (!empty($specs)): ?>
                <div class="tab-pane fade" id="tabSpecs">
                    <div class="table-responsive">
                        <table class="table spec-table table-bordered mb-0">
                            <tbody>
                                <?php foreach ($specs as $k => $v): ?>
                                <tr><th class="p-3 bg-body-tertiary"><?= clean($k) ?></th><td class="p-3"><?= clean($v) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Reviews -->
                <div class="tab-pane fade" id="tabReviews">
                    <?php
                    $db      = Database::getInstance();
                    $reviews = $db->fetchAll(
                        "SELECT r.*, CONCAT(c.first_name,' ',LEFT(c.last_name,1),'.') AS customer_name
                         FROM reviews r JOIN customers c ON r.customer_id=c.id
                         WHERE r.product_id=? AND r.status='approved'
                         ORDER BY r.created_at DESC", [$product['id']]
                    );
                    if (empty($reviews)): ?>
                    <div class="text-center py-4">
                        <i class="far fa-star fa-3x text-muted opacity-25 mb-2"></i>
                        <p class="text-muted">No reviews yet for this product. Be the first to share your thoughts!</p>
                    </div>
                    <?php else: foreach ($reviews as $rv): ?>
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <span class="text-warning"><?= starRating($rv['rating']) ?></span>
                                <strong class="ms-2 fs-6"><?= clean($rv['title'] ?? '') ?></strong>
                                <?php if ($rv['is_verified']): ?><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1">Verified Buyer</span><?php endif; ?>
                            </div>
                            <small class="text-muted"><?= formatDate($rv['created_at']) ?></small>
                        </div>
                        <div class="text-muted small mb-2">by <strong><?= clean($rv['customer_name']) ?></strong></div>
                        <p class="mb-2"><?= nl2br(clean($rv['review_text'])) ?></p>
                        <?php if ($rv['pros'] || $rv['cons']): ?>
                        <div class="row g-2 mt-1">
                            <?php if ($rv['pros']): ?><div class="col-md-6"><span class="badge bg-success bg-opacity-10 text-success p-2"><i class="fas fa-plus-circle me-1"></i><?= clean($rv['pros']) ?></span></div><?php endif; ?>
                            <?php if ($rv['cons']): ?><div class="col-md-6"><span class="badge bg-danger bg-opacity-10 text-danger p-2"><i class="fas fa-minus-circle me-1"></i><?= clean($rv['cons']) ?></span></div><?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; endif; ?>

                    <!-- Write a review form -->
                    <?php if (isLoggedIn() && isCustomer()): ?>
                    <div class="mt-4 p-4 bg-body-tertiary rounded-3">
                        <h5 class="fw-bold mb-3"><i class="fas fa-pencil-alt text-primary me-2"></i>Write a Customer Review</h5>
                        <form action="<?= BASE_URL ?>ajax/review.php" method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Your Rating *</label>
                                <div class="d-flex gap-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" required>
                                        <label class="form-check-label fw-bold" for="star<?= $i ?>"><?= $i ?> <i class="fas fa-star text-warning"></i></label>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Review Headline</label>
                                <input type="text" name="title" class="form-control" placeholder="E.g., Phenomenal battery life & great display!">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Detailed Review *</label>
                                <textarea name="review_text" class="form-control" rows="3" placeholder="Describe your experience using this product…" required></textarea>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Pros</label>
                                    <input type="text" name="pros" class="form-control" placeholder="What did you like best?">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cons</label>
                                    <input type="text" name="cons" class="form-control" placeholder="Any drawbacks?">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="fas fa-paper-plane me-1"></i>Submit Review
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="p-3 bg-body-tertiary rounded-3 text-center mt-3">
                        <p class="text-muted mb-2">Have you purchased this device?</p>
                        <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-sm btn-outline-primary fw-bold">Sign In to Leave a Review</a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- FAQ -->
                <?php if (!empty($product['faqs'])): ?>
                <div class="tab-pane fade" id="tabFaq">
                    <div class="accordion accordion-flush" id="faqAccordion">
                        <?php foreach ($product['faqs'] as $fi => $faq): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $fi !== 0 ? 'collapsed' : '' ?> fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $fi ?>">
                                    <?= clean($faq['question']) ?>
                                </button>
                            </h2>
                            <div id="faq<?= $fi ?>" class="accordion-collapse collapse <?= $fi === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted"><?= nl2br(clean($faq['answer'])) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
    <section class="mb-5">
        <div class="section-header">
            <h3 class="section-title"><i class="fas fa-layer-group text-primary me-2"></i>You Might Also Like</h3>
            <div class="section-subtitle">Similar electronics curated for you</div>
        </div>
        <div class="row g-3">
            <?php foreach ($related as $p): include __DIR__ . '/views/products/_card.php'; endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>

<script>
document.getElementById('mainAddToCart')?.addEventListener('click', function () {
    const qty = parseInt(document.getElementById('mainQtyInput')?.value || 1);
    Cart.add(<?= $product['id'] ?>, qty, this);
});
</script>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>
