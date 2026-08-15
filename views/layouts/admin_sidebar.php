<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'classes/Order.php';
require_once ROOT_PATH . 'classes/Cart.php';
require_once ROOT_PATH . 'classes/Inventory.php'; // also defines Wishlist, Loyalty, Notification, Report
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireStaff();

$notif      = new Notification();
$notifCount = $notif->getCount($_SESSION['user_id']);
$db         = Database::getInstance();
$lowStock   = $db->count('products', 'quantity <= low_stock_alert AND status = "active"');
$pendingOrders = $db->count('orders', 'status = "pending"');
$pendingReturns = $db->count('returns', 'status = "pending"');
$siteName   = getSetting('site_name', APP_NAME);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? clean($pageTitle) . ' — Admin | ' : 'Admin | ' ?><?= $siteName ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/admin.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/product_images.css">
</head>
<body class="admin-layout">

<!-- ── Sidebar ────────────────────────────────────────────────── -->
<nav id="adminSidebar" class="admin-sidebar">
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>admin/" class="sidebar-brand">
            <i class="fas fa-bolt text-warning"></i>
            <span class="brand-text"><?= $siteName ?></span>
        </a>
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    </div>

    <!-- User info -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <?php if (!empty($_SESSION['avatar'])): ?>
                <img src="<?= UPLOAD_URL . clean($_SESSION['avatar']) ?>" alt="">
            <?php else: ?>
                <i class="fas fa-user-circle"></i>
            <?php endif; ?>
        </div>
        <div class="user-info">
            <div class="user-name"><?= clean(explode(' ', $_SESSION['full_name'])[0]) ?></div>
            <div class="user-role badge"><?= ucfirst(str_replace('_', ' ', $_SESSION['role'])) ?></div>
        </div>
    </div>

    <!-- Nav menu -->
    <ul class="sidebar-menu list-unstyled">
        <li class="menu-label">Main</li>

        <li class="<?= isCurrentPage('/admin/index') || (isset($_SERVER['REQUEST_URI']) && rtrim($_SERVER['REQUEST_URI'], '/') === rtrim(str_replace(BASE_URL, '/', BASE_URL . 'admin/'), '/')) ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
        </li>

        <li class="menu-label">Catalog</li>

        <li class="has-submenu <?= isCurrentPage('/admin/products') ? 'open active' : '' ?>">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-box"></i><span>Products</span><i class="fas fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu list-unstyled">
                <li><a href="<?= BASE_URL ?>admin/products/"><i class="fas fa-list me-2"></i>All Products</a></li>
                <li><a href="<?= BASE_URL ?>admin/products/add.php"><i class="fas fa-plus me-2"></i>Add Product</a></li>
                <li><a href="<?= BASE_URL ?>admin/products/categories.php"><i class="fas fa-tags me-2"></i>Categories</a></li>
                <li><a href="<?= BASE_URL ?>admin/products/brands.php"><i class="fas fa-certificate me-2"></i>Brands</a></li>
                <li><a href="<?= BASE_URL ?>admin/flash_sales/"><i class="fas fa-bolt me-2"></i>Flash Sales</a></li>
                <li><a href="<?= BASE_URL ?>admin/bundles/"><i class="fas fa-layer-group me-2"></i>Bundles</a></li>
            </ul>
        </li>

        <li class="has-submenu <?= isCurrentPage('/admin/inventory') ? 'open active' : '' ?>">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-warehouse"></i><span>Inventory</span>
                <?php if ($lowStock > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $lowStock ?></span>
                <?php else: ?>
                    <i class="fas fa-chevron-right arrow"></i>
                <?php endif; ?>
            </a>
            <ul class="submenu list-unstyled">
                <li><a href="<?= BASE_URL ?>admin/inventory/"><i class="fas fa-clipboard-list me-2"></i>Stock Levels</a></li>
                <li><a href="<?= BASE_URL ?>admin/inventory/stock_in.php"><i class="fas fa-arrow-circle-down me-2"></i>Stock In</a></li>
                <li><a href="<?= BASE_URL ?>admin/inventory/adjust.php"><i class="fas fa-sliders-h me-2"></i>Adjustments</a></li>
                <li><a href="<?= BASE_URL ?>admin/inventory/low_stock.php"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Low Stock</a></li>
                <li><a href="<?= BASE_URL ?>admin/suppliers/"><i class="fas fa-truck-loading me-2"></i>Suppliers</a></li>
            </ul>
        </li>

        <li class="menu-label">Sales</li>

        <li class="has-submenu <?= isCurrentPage('/admin/orders') ? 'open active' : '' ?>">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-shopping-bag"></i><span>Orders</span>
                <?php if ($pendingOrders > 0): ?>
                    <span class="badge bg-warning ms-auto"><?= $pendingOrders ?></span>
                <?php else: ?>
                    <i class="fas fa-chevron-right arrow"></i>
                <?php endif; ?>
            </a>
            <ul class="submenu list-unstyled">
                <li><a href="<?= BASE_URL ?>admin/orders/"><i class="fas fa-list me-2"></i>All Orders</a></li>
                <li><a href="<?= BASE_URL ?>admin/orders/?status=pending"><i class="fas fa-clock me-2"></i>Pending</a></li>
                <li><a href="<?= BASE_URL ?>admin/orders/?status=processing"><i class="fas fa-cog me-2"></i>Processing</a></li>
                <li><a href="<?= BASE_URL ?>admin/orders/?status=shipped"><i class="fas fa-truck me-2"></i>Shipped</a></li>
                <li><a href="<?= BASE_URL ?>admin/orders/?status=delivered"><i class="fas fa-check-circle me-2"></i>Delivered</a></li>
            </ul>
        </li>

        <li class="<?= isCurrentPage('/admin/customers') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/customers/"><i class="fas fa-users"></i><span>Customers</span></a>
        </li>

        <li class="<?= isCurrentPage('/admin/coupons') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/coupons/"><i class="fas fa-tags"></i><span>Coupons</span></a>
        </li>

        <li class="<?= isCurrentPage('/admin/returns') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/returns/">
                <i class="fas fa-undo-alt"></i><span>Returns</span>
                <?php if ($pendingReturns > 0): ?>
                    <span class="badge bg-info ms-auto"><?= $pendingReturns ?></span>
                <?php endif; ?>
            </a>
        </li>

        <li class="menu-label">Analytics</li>

        <li class="has-submenu <?= isCurrentPage('/admin/reports') ? 'open active' : '' ?>">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-chart-bar"></i><span>Reports</span><i class="fas fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu list-unstyled">
                <li><a href="<?= BASE_URL ?>admin/reports/"><i class="fas fa-chart-line me-2"></i>Sales Report</a></li>
                <li><a href="<?= BASE_URL ?>admin/reports/inventory.php"><i class="fas fa-boxes me-2"></i>Inventory Report</a></li>
                <li><a href="<?= BASE_URL ?>admin/reports/customers.php"><i class="fas fa-user-chart me-2"></i>Customer Report</a></li>
                <li><a href="<?= BASE_URL ?>admin/reports/revenue.php"><i class="fas fa-dollar-sign me-2"></i>Revenue Report</a></li>
            </ul>
        </li>

        <li class="menu-label">Management</li>

        <li class="<?= isCurrentPage('/admin/staff') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/staff/"><i class="fas fa-user-tie"></i><span>Staff</span></a>
        </li>

        <li class="<?= isCurrentPage('/admin/newsletter') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/newsletter/"><i class="fas fa-envelope-open-text"></i><span>Newsletter</span></a>
        </li>

        <li class="<?= isCurrentPage('/admin/reviews') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/reviews/"><i class="fas fa-star"></i><span>Reviews</span></a>
        </li>

        <li class="<?= isCurrentPage('/admin/settings') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/settings/"><i class="fas fa-cog"></i><span>Settings</span></a>
        </li>

        <li class="<?= isCurrentPage('/admin/audit') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/audit/"><i class="fas fa-history"></i><span>Audit Logs</span></a>
        </li>

        <li class="menu-label">Account</li>
        <li><a href="<?= BASE_URL ?>customer/profile.php"><i class="fas fa-user-cog"></i><span>My Profile</span></a></li>
        <li><a href="<?= BASE_URL ?>auth/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
    </ul>
</nav>

<!-- ── Main content wrapper ──────────────────────────────────── -->
<div class="admin-main" id="adminMain">
    <!-- Top bar -->
    <header class="admin-topbar">
        <button class="btn btn-sm btn-light" id="sidebarToggleBtn"><i class="fas fa-bars"></i></button>

        <nav aria-label="breadcrumb" class="ms-3 d-none d-md-block">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/">Dashboard</a></li>
                <?php if (isset($breadcrumb)): foreach ($breadcrumb as $bc): ?>
                    <li class="breadcrumb-item <?= isset($bc['active']) ? 'active' : '' ?>">
                        <?= isset($bc['url']) ? '<a href="' . $bc['url'] . '">' . clean($bc['label']) . '</a>' : clean($bc['label']) ?>
                    </li>
                <?php endforeach; endif; ?>
            </ol>
        </nav>

        <div class="ms-auto d-flex align-items-center gap-2">
            <!-- Theme toggle -->
            <button class="btn btn-sm btn-light" id="themeToggle" title="Toggle theme">
                <i class="fas fa-moon"></i>
            </button>

            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn btn-sm btn-light position-relative" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <?php if ($notifCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge">
                            <?= $notifCount ?>
                        </span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow p-0" style="width:340px; max-height:420px; overflow-y:auto;">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                        <strong>Notifications</strong>
                        <a href="#" class="small text-primary mark-all-read">Mark all read</a>
                    </div>
                    <div id="notifList"><div class="text-center p-3 text-muted small">Loading…</div></div>
                    <div class="p-2 border-top text-center">
                        <a href="<?= BASE_URL ?>admin/notifications/" class="small text-primary">View all notifications</a>
                    </div>
                </div>
            </div>

            <!-- View store -->
            <a href="<?= BASE_URL ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                <i class="fas fa-external-link-alt me-1"></i><span class="d-none d-md-inline">View Store</span>
            </a>

            <!-- User -->
            <div class="dropdown">
                <button class="btn btn-sm btn-light d-flex align-items-center gap-1" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle"></i>
                    <span class="d-none d-md-inline"><?= clean(explode(' ', $_SESSION['full_name'])[0]) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>customer/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/settings/"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Page content starts here -->
    <div class="admin-content p-3 p-md-4">
        <?= displayFlash() ?>
