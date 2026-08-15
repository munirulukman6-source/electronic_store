<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'classes/Cart.php';
require_once ROOT_PATH . 'classes/Inventory.php'; // also defines Wishlist, Loyalty, Notification, Report
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();

$cart         = new Cart();
$cartCount    = $cart->getCount();
$wishCount    = 0;
$notifCount   = 0;
$loyaltyPts   = 0;

if (isLoggedIn() && isCustomer()) {
    $wl         = new Wishlist();
    $wishCount  = $wl->getCount($_SESSION['customer_id']);
    $notif      = new Notification();
    $notifCount = $notif->getCount($_SESSION['user_id']);
    $loyaltyPts = $_SESSION['loyalty_points'] ?? 0;
}

$productModel = new Product();
$categories   = $productModel->getCategories();
$siteName     = getSetting('site_name', APP_NAME);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= getSetting('site_tagline', APP_TAGLINE) ?>">
    <title><?= isset($pageTitle) ? clean($pageTitle) . ' — ' : '' ?><?= $siteName ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/product_images.css">
</head>
<body>

<!-- ── Top Announcement Bar ───────────────────────────────────── -->
<div class="top-bar d-none d-md-block">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-start">
                <span><i class="fas fa-phone-alt me-1"></i><?= getSetting('site_phone', '+233 555 332 271') ?></span>
                <span class="separator">|</span>
                <span><i class="fas fa-envelope me-1"></i><?= getSetting('site_email', 'info@electrostore.com') ?></span>
            </div>
            <div class="col-md-6 text-end">
                <span><i class="fas fa-shipping-fast me-1 text-warning"></i>Free delivery over <?= DEFAULT_CURRENCY_SYMBOL ?>500</span>
                <span class="separator">|</span>
                <span><i class="fas fa-shield-alt me-1 text-success"></i>100% Genuine Tech</span>
                <span class="separator">|</span>
                <!-- Dark mode toggle -->
                <button class="btn btn-link btn-sm p-0 text-decoration-none text-light" id="themeToggle" title="Toggle theme">
                    <i class="fas fa-moon"></i>
                </button>
                <!-- Currency switcher -->
                <select class="form-select form-select-sm d-inline-block w-auto ms-2 border-0 bg-transparent text-light" id="currencySwitcher" style="font-size:0.75rem;">
                    <option value="GHS" class="text-dark">₵ GHS</option>
                    <option value="USD" class="text-dark">$ USD</option>
                    <option value="EUR" class="text-dark">€ EUR</option>
                    <option value="GBP" class="text-dark">£ GBP</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- ── Main Header Wrapper ────────────────────────────────────── -->
<header class="header-wrapper shadow-sm">
    <!-- Tier 1: Main Bar -->
    <div class="container">
        <div class="main-header-row d-flex align-items-center justify-content-between gap-3">
            <!-- Brand Logo -->
            <a class="brand-link d-flex align-items-center text-decoration-none flex-shrink-0" href="<?= BASE_URL ?>">
                <span class="brand-icon-wrap"><i class="fas fa-bolt"></i></span>
                <span class="brand-name"><?= $siteName ?></span>
            </a>

            <!-- Search (Desktop) -->
            <form class="d-none d-lg-flex flex-grow-1 mx-3 search-form" action="<?= BASE_URL ?>shop.php" method="GET">
                <div class="search-input-group w-100">
                    <select name="category" class="search-cat-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): if ($cat['parent_id']) continue; ?>
                            <option value="<?= $cat['id'] ?>"><?= clean($cat['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" class="search-input" name="q" id="searchInput"
                           placeholder="Search devices, accessories, brands..."
                           value="<?= clean($_GET['q'] ?? '') ?>" autocomplete="off">
                    <button class="search-btn" type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
                </div>
                <!-- AJAX Live Search Dropdown -->
                <div id="searchDropdown" class="search-dropdown d-none"></div>
            </form>

            <!-- Right Actions -->
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <!-- Wishlist -->
                <a href="<?= BASE_URL ?>wishlist.php" class="nav-icon-btn" title="Wishlist">
                    <i class="far fa-heart"></i>
                    <?php if ($wishCount > 0): ?>
                        <span class="badge-count wishlist-count"><?= $wishCount ?></span>
                    <?php endif; ?>
                </a>

                <!-- Notifications (logged in) -->
                <?php if (isLoggedIn()): ?>
                <div class="dropdown">
                    <button class="nav-icon-btn" data-bs-toggle="dropdown" title="Notifications" aria-expanded="false">
                        <i class="far fa-bell"></i>
                        <?php if ($notifCount > 0): ?>
                            <span class="badge-count" id="notifBadge"><?= $notifCount ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notif-dropdown p-0 shadow-lg border-0" style="width:340px; max-height:460px; overflow-y:auto; border-radius:14px;">
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <strong class="fs-6">Notifications</strong>
                            <a href="#" class="small text-primary text-decoration-none mark-all-read">Mark all read</a>
                        </div>
                        <div id="notifList">
                            <div class="text-center p-4 text-muted small"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Loading…</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Cart -->
                <a href="<?= BASE_URL ?>cart.php" class="nav-icon-btn" title="Cart">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="badge-count" id="cartCount"><?= $cartCount > 0 ? $cartCount : '' ?></span>
                </a>

                <!-- User Profile / Auth (Desktop) -->
                <?php if (isLoggedIn()): ?>
                <div class="dropdown d-none d-sm-block">
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if (!empty($_SESSION['avatar'])): ?>
                            <img src="<?= UPLOAD_URL . clean($_SESSION['avatar']) ?>" class="rounded-circle" width="22" height="22" alt="">
                        <?php else: ?>
                            <i class="fas fa-user-circle fs-6"></i>
                        <?php endif; ?>
                        <span class="fw-bold small"><?= clean(explode(' ', $_SESSION['full_name'])[0]) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" style="border-radius:14px; min-width:220px;">
                        <?php if (isCustomer()): ?>
                            <li class="px-3 py-2 border-bottom mb-1">
                                <div class="fw-bold"><?= clean($_SESSION['full_name']) ?></div>
                                <small class="text-muted"><i class="fas fa-coins me-1 text-warning"></i><?= number_format($loyaltyPts) ?> reward points</small>
                            </li>
                            <li><a class="dropdown-item rounded-2 py-2" href="<?= BASE_URL ?>customer/profile.php"><i class="fas fa-user me-2 text-primary"></i>My Profile</a></li>
                            <li><a class="dropdown-item rounded-2 py-2" href="<?= BASE_URL ?>customer/orders.php"><i class="fas fa-box me-2 text-success"></i>My Orders</a></li>
                            <li><a class="dropdown-item rounded-2 py-2" href="<?= BASE_URL ?>wishlist.php"><i class="fas fa-heart me-2 text-danger"></i>My Wishlist</a></li>
                            <li><a class="dropdown-item rounded-2 py-2" href="<?= BASE_URL ?>customer/loyalty.php"><i class="fas fa-coins me-2 text-warning"></i>Loyalty Rewards</a></li>
                            <li><a class="dropdown-item rounded-2 py-2" href="<?= BASE_URL ?>customer/reviews.php"><i class="fas fa-star me-2 text-warning"></i>My Reviews</a></li>
                        <?php endif; ?>
                        <?php if (isStaff()): ?>
                            <li><a class="dropdown-item rounded-2 py-2" href="<?= BASE_URL ?>admin/"><i class="fas fa-tachometer-alt me-2 text-info"></i>Admin Dashboard</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li><a class="dropdown-item rounded-2 py-2 text-danger" href="<?= BASE_URL ?>auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <div class="d-none d-sm-flex align-items-center gap-2">
                    <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="fas fa-user me-1"></i>Sign In
                    </a>
                    <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-sm btn-primary rounded-pill px-3 d-none d-md-inline-flex">
                        Register
                    </a>
                </div>
                <?php endif; ?>

                <!-- Mobile Hamburger Drawer Button -->
                <button class="nav-icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNavDrawer" aria-label="Open Navigation Drawer">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Tier 2: Category & Navigation Sub-Bar (Desktop) -->
    <div class="sub-nav-bar d-none d-lg-block">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <!-- Category Dropdown Menu -->
                <div class="dropdown">
                    <button class="category-dropdown-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-th-large"></i> All Categories
                    </button>
                    <div class="dropdown-menu shadow-lg border-0 p-2 mt-1" style="min-width: 250px; border-radius: 12px;">
                        <?php foreach ($categories as $cat): if ($cat['parent_id']) continue; ?>
                        <a href="<?= BASE_URL ?>shop.php?category=<?= $cat['slug'] ?>" class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2">
                            <i class="fas <?= clean($cat['icon'] ?? 'fa-tag') ?> text-primary" style="width:20px;text-align:center;"></i>
                            <span><?= clean($cat['category_name']) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Navigation Links -->
                <ul class="sub-nav-links ms-2">
                    <li><a class="sub-nav-link <?= navActive('index.php') ?>" href="<?= BASE_URL ?>"><i class="fas fa-home me-1 opacity-75"></i>Home</a></li>
                    <li><a class="sub-nav-link <?= navActive('shop.php') ?>" href="<?= BASE_URL ?>shop.php"><i class="fas fa-store me-1 opacity-75"></i>All Products</a></li>
                    <li>
                        <a class="sub-nav-link text-danger fw-bold" href="<?= BASE_URL ?>shop.php?filter=flash_sale">
                            <i class="fas fa-bolt text-warning me-1"></i>Flash Sales
                        </a>
                    </li>
                    <li><a class="sub-nav-link" href="<?= BASE_URL ?>compare.php"><i class="fas fa-exchange-alt me-1 opacity-75"></i>Compare</a></li>
                    <li><a class="sub-nav-link" href="<?= BASE_URL ?>customer/loyalty.php"><i class="fas fa-coins text-warning me-1"></i>Rewards</a></li>
                </ul>
            </div>

            <!-- Right support assurance -->
            <div class="small text-muted d-flex align-items-center gap-3">
                <span><i class="fas fa-truck text-success me-1"></i>Express Shipping Available</span>
            </div>
        </div>
    </div>
</header>

<!-- ── Mobile Offcanvas Navigation Drawer ─────────────────────── -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNavDrawer" style="max-width: 320px;">
    <div class="offcanvas-header border-bottom p-3">
        <div class="d-flex align-items-center gap-2">
            <span class="brand-icon-wrap" style="width:32px;height:32px;font-size:1rem;"><i class="fas fa-bolt"></i></span>
            <span class="fw-extrabold fs-5 text-body"><?= $siteName ?></span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3">
        <!-- Search bar inside drawer -->
        <form class="mb-4" action="<?= BASE_URL ?>shop.php" method="GET">
            <div class="search-input-group">
                <input type="text" class="search-input" name="q" placeholder="Search devices…" value="<?= clean($_GET['q'] ?? '') ?>">
                <button class="search-btn" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <!-- User section -->
        <?php if (isLoggedIn()): ?>
        <div class="p-3 bg-body-tertiary rounded-3 mb-3 border">
            <div class="fw-bold mb-0"><?= clean($_SESSION['full_name']) ?></div>
            <small class="text-muted d-block mb-2"><?= clean($_SESSION['email']) ?></small>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>customer/profile.php" class="btn btn-sm btn-primary w-100 fw-bold">My Account</a>
                <a href="<?= BASE_URL ?>auth/logout.php" class="btn btn-sm btn-outline-danger"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
        <?php else: ?>
        <div class="d-flex gap-2 mb-4">
            <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-outline-primary btn-sm w-100 fw-bold">Sign In</a>
            <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-primary btn-sm w-100 fw-bold">Register</a>
        </div>
        <?php endif; ?>

        <!-- Quick Nav Links -->
        <h6 class="text-uppercase text-muted fw-bold small mb-2">Main Navigation</h6>
        <div class="d-flex flex-column gap-1 mb-4">
            <a href="<?= BASE_URL ?>" class="brand-filter-item text-decoration-none"><span><i class="fas fa-home me-2 text-primary"></i>Home</span></a>
            <a href="<?= BASE_URL ?>shop.php" class="brand-filter-item text-decoration-none"><span><i class="fas fa-store me-2 text-primary"></i>All Products</span></a>
            <a href="<?= BASE_URL ?>shop.php?filter=flash_sale" class="brand-filter-item text-decoration-none text-danger fw-bold"><span><i class="fas fa-bolt me-2 text-warning"></i>Flash Sales</span></a>
            <a href="<?= BASE_URL ?>compare.php" class="brand-filter-item text-decoration-none"><span><i class="fas fa-exchange-alt me-2 text-primary"></i>Compare Products</span></a>
            <a href="<?= BASE_URL ?>wishlist.php" class="brand-filter-item text-decoration-none"><span><i class="fas fa-heart me-2 text-danger"></i>Wishlist (<?= $wishCount ?>)</span></a>
        </div>

        <!-- Categories List -->
        <h6 class="text-uppercase text-muted fw-bold small mb-2">Browse Categories</h6>
        <div class="d-flex flex-column gap-1 mb-4">
            <?php foreach ($categories as $cat): if ($cat['parent_id']) continue; ?>
            <a href="<?= BASE_URL ?>shop.php?category=<?= $cat['slug'] ?>" class="brand-filter-item text-decoration-none">
                <span><i class="fas <?= clean($cat['icon'] ?? 'fa-tag') ?> me-2 text-muted"></i><?= clean($cat['category_name']) ?></span>
                <?php if ($cat['product_count'] ?? 0): ?><span class="badge bg-light text-muted"><?= $cat['product_count'] ?></span><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Footer tools in drawer -->
        <div class="pt-3 border-top d-flex justify-content-between align-items-center">
            <span class="small text-muted">Theme Mode</span>
            <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('themeToggle')?.click()">
                <i class="fas fa-adjust me-1"></i>Toggle
            </button>
        </div>
    </div>
</div>

<!-- ── Mobile App Bottom Navigation Bar ─────────────────────────── -->
<nav class="mobile-bottom-nav d-lg-none">
    <a href="<?= BASE_URL ?>" class="mobile-nav-item <?= navActive('index.php') ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="<?= BASE_URL ?>shop.php" class="mobile-nav-item <?= navActive('shop.php') ?>">
        <i class="fas fa-store"></i>
        <span>Shop</span>
    </a>
    <a href="<?= BASE_URL ?>cart.php" class="mobile-nav-item position-relative <?= navActive('cart.php') ?>">
        <i class="fas fa-shopping-bag"></i>
        <span>Cart</span>
        <?php if ($cartCount > 0): ?>
            <span class="badge-count" style="top:-2px;right:10px;"><?= $cartCount ?></span>
        <?php endif; ?>
    </a>
    <a href="<?= BASE_URL ?>wishlist.php" class="mobile-nav-item <?= navActive('wishlist.php') ?>">
        <i class="far fa-heart"></i>
        <span>Wishlist</span>
    </a>
    <a href="<?= isLoggedIn() ? BASE_URL . 'customer/profile.php' : BASE_URL . 'auth/login.php' ?>" class="mobile-nav-item">
        <i class="far fa-user"></i>
        <span>Account</span>
    </a>
</nav>

<!-- Flash messages -->
<div class="container mt-3"><?= displayFlash() ?></div>
