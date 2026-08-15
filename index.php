<?php
$pageTitle = 'Home';
require_once __DIR__ . '/views/layouts/header.php';

$productModel = new Product();
$featured     = $productModel->getFeatured(8);
$bestSellers  = $productModel->getBestSellers(6);
$flashSales   = $productModel->getActiveFlashSales();
$newArrivals  = $productModel->getAll(['is_new' => true], 1, 8)['data'];
$bundles      = $productModel->getBundles(true);
$allCategories = $productModel->getCategories(true);
$featuredCats  = array_filter($allCategories, fn($c) => $c['is_featured'] && !$c['parent_id']);
?>

<!-- ── Full-Width Hero Swiper Carousel ──────────────────────── -->
<div class="swiper hero-swiper">
    <div class="swiper-wrapper">
        <div class="swiper-slide hero-slide hero-slide-1">
            <div class="container h-100 d-flex align-items-center">
                <div class="hero-content">
                    <span class="hero-label"><i class="fas fa-sparkles text-warning"></i> Flagship Release</span>
                    <h1 class="hero-title">Samsung Galaxy S24 Ultra</h1>
                    <p class="hero-sub">200MP ProVisual Camera · Titanium Armor · Galaxy AI Built-in · Snapdragon 8 Gen 3</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="<?= BASE_URL ?>product/samsung-galaxy-s24-ultra" class="btn btn-warning fw-bold px-4">
                            <i class="fas fa-shopping-bag me-1"></i>Order Now
                        </a>
                        <a href="<?= BASE_URL ?>shop.php?category=smartphones" class="btn btn-outline-light px-4">
                            Browse Phones
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-slide hero-slide hero-slide-2">
            <div class="container h-100 d-flex align-items-center">
                <div class="hero-content">
                    <span class="hero-label"><i class="fab fa-apple text-white"></i> Titanium Edition</span>
                    <h1 class="hero-title">Apple iPhone 15 Pro Max</h1>
                    <p class="hero-sub">Aerospace-grade Titanium · A17 Pro Chip · 5x Telephoto Optical Zoom · Action Button</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="<?= BASE_URL ?>product/apple-iphone-15-pro-max" class="btn btn-primary fw-bold px-4">
                            <i class="fas fa-bolt me-1"></i>Explore Device
                        </a>
                        <a href="<?= BASE_URL ?>shop.php?brand=apple" class="btn btn-outline-light px-4">
                            Apple Store
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-slide hero-slide hero-slide-3">
            <div class="container h-100 d-flex align-items-center">
                <div class="hero-content">
                    <span class="hero-label text-warning"><i class="fas fa-fire"></i> Mega Savings</span>
                    <h1 class="hero-title">Massive Tech Flash Sale</h1>
                    <p class="hero-sub">Enjoy up to 30% discount on ultra-fast gaming laptops, wireless earbuds & accessories!</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="<?= BASE_URL ?>shop.php?filter=flash_sale" class="btn btn-danger fw-bold px-4">
                            <i class="fas fa-bolt me-1"></i>Grab Flash Deals
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
</div>

<div class="container py-4">
    <!-- ── Value Proposition Features Strip ─────────────────────── -->
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="feature-card">
                <div class="feature-icon-wrap bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Free Express Delivery</h6>
                    <small class="text-muted">On all orders over <?= DEFAULT_CURRENCY_SYMBOL ?>500</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="feature-card">
                <div class="feature-icon-wrap bg-success bg-opacity-10 text-success">
                    <i class="fas fa-shield-check"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Official Warranty</h6>
                    <small class="text-muted">100% Genuine & covered</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="feature-card">
                <div class="feature-icon-wrap bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Instant MoMo & Card</h6>
                    <small class="text-muted">Fast & encrypted payments</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="feature-card">
                <div class="feature-icon-wrap bg-info bg-opacity-10 text-info">
                    <i class="fas fa-headset"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">24/7 Expert Support</h6>
                    <small class="text-muted">Dedicated tech assistance</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Category Explorer ──────────────────────────────────────── -->
<section class="py-5">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="section-title"><i class="fas fa-th-large text-primary me-2"></i>Explore Categories</h2>
                <div class="section-subtitle">Find high-tech gear tailored to your daily workflow</div>
            </div>
            <a href="<?= BASE_URL ?>shop.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="swiper cat-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($featuredCats as $cat): ?>
                <div class="swiper-slide">
                    <a href="<?= BASE_URL ?>shop.php?category=<?= $cat['slug'] ?>" class="cat-chip text-decoration-none">
                        <div class="cat-chip-icon"><i class="fas <?= clean($cat['icon'] ?? 'fa-tag') ?>"></i></div>
                        <div class="cat-chip-name"><?= clean($cat['category_name']) ?></div>
                        <?php if ($cat['product_count'] ?? 0): ?>
                            <div class="cat-chip-count"><?= $cat['product_count'] ?> Products</div>
                        <?php endif; ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ── Flash Sales ─────────────────────────────────────────────── -->
<?php if (!empty($flashSales)): ?>
<section class="py-4" style="background:linear-gradient(180deg, rgba(239,68,68,0.03) 0%, transparent 100%);">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="section-title text-danger"><i class="fas fa-bolt text-warning me-2"></i>Flash Sales</h2>
                <div class="section-subtitle">Special limited-time discounts ending soon</div>
            </div>
            <a href="<?= BASE_URL ?>shop.php?filter=flash_sale" class="btn btn-sm btn-danger fw-bold">
                See All Deals <i class="fas fa-chevron-right ms-1"></i>
            </a>
        </div>
        <div class="row g-3">
            <?php foreach ($flashSales as $fs): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card product-card flash-card h-100">
                    <div class="flash-timer-badge">
                        <i class="fas fa-clock"></i>
                        <span class="countdown" data-end="<?= strtotime($fs['end_time']) * 1000 ?>">00:00:00</span>
                    </div>
                    <a href="<?= BASE_URL ?>product/<?= $fs['slug'] ?>" class="card-img-top-wrap text-decoration-none">
                        <img src="<?= productImageUrl($fs['primary_image']) ?>" alt="<?= clean($fs['product_name']) ?>">
                    </a>
                    <div class="card-body">
                        <div class="product-brand"><?= clean($fs['brand_name'] ?? 'ElectroStore') ?></div>
                        <a href="<?= BASE_URL ?>product/<?= $fs['slug'] ?>" class="text-decoration-none">
                            <div class="product-name"><?= truncate(clean($fs['product_name']), 45) ?></div>
                        </a>
                        <div class="price-block">
                            <span class="price-current text-danger"><?= formatPrice($fs['sale_price']) ?></span>
                            <span class="price-old"><?= formatPrice($fs['original_price']) ?></span>
                            <span class="price-badge">-<?= round($fs['discount_pct']) ?>%</span>
                        </div>
                        <?php if ($fs['qty_limit']): $pct = min(100, ($fs['sold_count'] / $fs['qty_limit']) * 100); ?>
                        <div class="progress my-2" style="height:6px;border-radius:4px;">
                            <div class="progress-bar bg-danger" style="width:<?= $pct ?>%"></div>
                        </div>
                        <small class="text-muted d-block mb-2"><?= $fs['sold_count'] ?> claimed / <?= $fs['qty_limit'] ?> total</small>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>product/<?= $fs['slug'] ?>" class="btn btn-danger btn-sm w-100 fw-bold mt-auto">
                            Claim Deal <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Promo Banners ───────────────────────────────────────────── -->
<section class="py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="promo-banner promo-1">
                    <span class="hero-label bg-white bg-opacity-20 text-white" style="width:fit-content;">Gaming Zone</span>
                    <h4 class="fw-extrabold mt-2 mb-2">Next-Gen Gaming<br>Consoles & Gear</h4>
                    <p class="small text-white text-opacity-80 mb-3">Immersive 4K graphics and low latency accessories.</p>
                    <a href="<?= BASE_URL ?>shop.php?category=gaming" class="btn btn-sm btn-light fw-bold" style="width:fit-content;">Shop Now</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="promo-banner promo-2">
                    <span class="hero-label bg-white bg-opacity-20 text-white" style="width:fit-content;">Work & Study</span>
                    <h4 class="fw-extrabold mt-2 mb-2">Ultra-Slim Laptops<br>High Performance</h4>
                    <p class="small text-white text-opacity-80 mb-3">Power through work and creative tasks anywhere.</p>
                    <a href="<?= BASE_URL ?>shop.php?category=laptops" class="btn btn-sm btn-light fw-bold" style="width:fit-content;">Explore Laptops</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="promo-banner promo-3">
                    <span class="hero-label bg-white bg-opacity-20 text-white" style="width:fit-content;">Pure Sound</span>
                    <h4 class="fw-extrabold mt-2 mb-2">Premium Audio &<br>Noise-Cancelling</h4>
                    <p class="small text-white text-opacity-80 mb-3">Crystal clear studio sound on the move.</p>
                    <a href="<?= BASE_URL ?>shop.php?category=audio" class="btn btn-sm btn-light fw-bold" style="width:fit-content;">Browse Audio</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Featured Products ───────────────────────────────────────── -->
<section class="py-5">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="section-title"><i class="fas fa-sparkles text-warning me-2"></i>Featured Products</h2>
                <div class="section-subtitle">Curated collection of top-rated electronics</div>
            </div>
            <a href="<?= BASE_URL ?>shop.php?filter=featured" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="row g-3" id="featuredGrid">
            <?php foreach ($featured as $p): ?>
                <?php include __DIR__ . '/views/products/_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Best Sellers ────────────────────────────────────────────── -->
<section class="py-5 bg-body-tertiary">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="section-title"><i class="fas fa-fire text-danger me-2"></i>Best Sellers</h2>
                <div class="section-subtitle">Most popular customer choices this month</div>
            </div>
            <a href="<?= BASE_URL ?>shop.php?sort=popular" class="btn btn-sm btn-outline-danger">View Best Sellers</a>
        </div>
        <div class="row g-3">
            <?php foreach ($bestSellers as $p): ?>
                <?php include __DIR__ . '/views/products/_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Product Bundles ─────────────────────────────────────────── -->
<?php if (!empty($bundles)): ?>
<section class="py-5">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-layer-group text-primary me-2"></i>Exclusive Value Bundles</h2>
            <div class="section-subtitle">Complete packages curated to save you more money</div>
        </div>
        <div class="row g-4">
            <?php foreach ($bundles as $bundle): ?>
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm" style="border-left:5px solid var(--es-primary) !important; border-radius:14px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold mb-0"><?= clean($bundle['bundle_name']) ?></h5>
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill">Save <?= round($bundle['discount_pct']) ?>%</span>
                        </div>
                        <p class="text-muted small mb-3"><?= clean($bundle['description']) ?></p>
                        <div class="p-3 bg-body-tertiary rounded-3 mb-3 small">
                            <i class="fas fa-check-circle text-success me-2"></i><strong>Includes:</strong> <?= clean($bundle['product_names']) ?>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <div class="fw-extrabold fs-4 text-primary"><?= formatPrice($bundle['bundle_price']) ?></div>
                                <div class="text-muted text-decoration-line-through small"><?= formatPrice($bundle['original_price']) ?></div>
                            </div>
                            <a href="<?= BASE_URL ?>bundle/<?= $bundle['slug'] ?>" class="btn btn-primary fw-bold ms-auto px-4">
                                <i class="fas fa-cart-plus me-1"></i>Get Bundle Deal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── New Arrivals ────────────────────────────────────────────── -->
<section class="py-5 bg-body-tertiary">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="section-title"><i class="fas fa-star text-warning me-2"></i>New Arrivals</h2>
                <div class="section-subtitle">Freshly stocked gadgets and accessories</div>
            </div>
            <a href="<?= BASE_URL ?>shop.php?filter=new" class="btn btn-sm btn-outline-warning text-dark">View New Arrivals</a>
        </div>
        <div class="row g-3">
            <?php foreach ($newArrivals as $p): ?>
                <?php include __DIR__ . '/views/products/_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Loyalty Rewards CTA ─────────────────────────────────────── -->
<?php if (!isLoggedIn()): ?>
<section class="py-5 my-4">
    <div class="container">
        <div class="p-5 text-center text-white rounded-4 shadow-lg position-relative overflow-hidden" style="background:linear-gradient(135deg, #091a3e 0%, #1e3a8a 50%, #2563eb 100%);">
            <div style="position:relative; z-index:2;">
                <div class="mb-3">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold"><i class="fas fa-coins me-1"></i>ElectroStore Rewards Club</span>
                </div>
                <h2 class="fw-extrabold display-6 mb-3">Earn Reward Points on Every Purchase</h2>
                <p class="lead mb-4 text-white text-opacity-80 mx-auto" style="max-width:620px;">Join our member loyalty program today to earn points on every order, unlock VIP tier discounts, and redeem exclusive tech perks!</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-warning btn-lg fw-bold px-5">
                        <i class="fas fa-user-plus me-2"></i>Create Free Account
                    </a>
                    <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-outline-light btn-lg px-5">
                        Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>
