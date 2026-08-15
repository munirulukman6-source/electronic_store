<?php
$pageTitle = 'Shop';
require_once __DIR__ . '/views/layouts/header.php';

$db = Database::getInstance();
$productModel = new Product(); // still used for categories, brands, price range

// ── Filters & Sorting ─────────────────────────────────────────
$search      = get('q', '');
$categorySlug= get('category', '');
$brandSlug   = get('brand', '');
$minPrice    = get('min_price', '');
$maxPrice    = get('max_price', '');
$sort        = get('sort', 'newest');
$inStock     = get('in_stock', '');
$rating      = get('rating', '');
$filter      = get('filter', '');   // featured, flash_sale, new
$page        = max(1, (int)get('page', 1));
$perPage     = PRODUCTS_PER_PAGE;

// Build WHERE conditions
$where  = ["p.status = 'active'"];
$params = [];

if ($search) {
    $where[] = '(p.product_name LIKE ? OR p.model LIKE ? OR b.brand_name LIKE ? OR c.category_name LIKE ?)';
    $s = "%$search%";
    array_push($params, $s, $s, $s, $s);
}
if ($categorySlug) {
    $where[] = 'c.slug = ?';
    $params[] = $categorySlug;
}
if ($brandSlug) {
    $where[] = 'b.slug = ?';
    $params[] = $brandSlug;
}
if ($minPrice !== '') {
    $where[] = 'p.price >= ?';
    $params[] = (float)$minPrice;
}
if ($maxPrice !== '') {
    $where[] = 'p.price <= ?';
    $params[] = (float)$maxPrice;
}
if ($inStock) {
    $where[] = 'p.quantity > 0';
}
if ($rating) {
    $where[] = 'p.avg_rating >= ?';
    $params[] = (int)$rating;
}
if ($filter === 'featured') {
    $where[] = 'p.is_featured = 1';
} elseif ($filter === 'new') {
    $where[] = 'p.is_new_arrival = 1';
}
// flash_sale filter is handled later via JOIN, not needed in WHERE

// Sorting
$orderMap = [
    'newest'     => 'p.created_at DESC',
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'popular'    => 'p.total_sold DESC',
    'rating'     => 'p.avg_rating DESC',
    'views'      => 'p.views DESC',
];
$order = $orderMap[$sort] ?? 'p.created_at DESC';

// Count total products
$totalSql = "SELECT COUNT(*) FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN brands b ON p.brand_id = b.id
             WHERE " . implode(' AND ', $where);
$total = $db->fetchColumn($totalSql, $params);

// Fetch products
$offset = ($page - 1) * $perPage;
$productsSql = "SELECT p.id, p.product_name, p.slug, p.model, p.sku, p.price,
                       p.compare_price, p.quantity, p.is_featured, p.is_new_arrival,
                       p.is_best_seller, p.avg_rating, p.review_count, p.views,
                       p.total_sold, p.short_description, p.warranty_months,
                       c.category_name, c.slug AS category_slug,
                       b.brand_name, b.slug AS brand_slug, b.logo AS brand_logo,
                       pi.image_path AS primary_image,
                       COALESCE(fs.sale_price, p.price) AS effective_price,
                       fs.end_time AS flash_end, fs.id AS flash_id
                FROM products p
                JOIN categories c ON p.category_id = c.id
                JOIN brands b ON p.brand_id = b.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                LEFT JOIN flash_sales fs ON p.id = fs.product_id
                    AND fs.status = 'active' AND NOW() BETWEEN fs.start_time AND fs.end_time
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $order
                LIMIT $perPage OFFSET $offset";
$products = $db->fetchAll($productsSql, $params);

// Build paginator array for paginationLinks function
$lastPage = max(1, ceil($total / $perPage));
$paginator = [
    'data'         => $products,
    'total'        => $total,
    'per_page'     => $perPage,
    'current_page' => $page,
    'last_page'    => $lastPage,
    'from'         => $offset + 1,
    'to'           => min($offset + $perPage, $total),
];

// Sidebar data (still uses Product model)
$categories  = $productModel->getCategories(true);
$brands      = $productModel->getBrands(true);
$priceRange  = $productModel->getPriceRange();

$activeCategory = '';
if ($categorySlug) {
    foreach ($categories as $c) {
        if ($c['slug'] === $categorySlug) { $activeCategory = $c['category_name']; break; }
    }
}
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>shop.php">Shop</a></li>
            <?php if ($activeCategory): ?>
                <li class="breadcrumb-item active"><?= clean($activeCategory) ?></li>
            <?php elseif ($search): ?>
                <li class="breadcrumb-item active">Search: "<?= clean($search) ?>"</li>
            <?php else: ?>
                <li class="breadcrumb-item active">All Products</li>
            <?php endif; ?>
        </ol>
    </nav>

    <div class="row g-4">

        <!-- ── Sidebar filters ──────────────────────────────────── -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="filter-sidebar">
                <?php if (array_filter([$search, $categorySlug, $brandSlug, $minPrice, $maxPrice, $inStock, $rating, $filter])): ?>
                <div class="filter-section">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="filter-heading mb-0">Active Filters</span>
                        <a href="<?= BASE_URL ?>shop.php" class="small text-danger fw-semibold"><i class="fas fa-times-circle me-1"></i>Clear</a>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php if ($search): ?>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">"<?= clean($search) ?>" <a href="?<?= http_build_query(array_merge($_GET, ['q'=>''])) ?>" class="text-primary ms-1">×</a></span>
                        <?php endif; ?>
                        <?php if ($categorySlug): ?>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1"><?= clean($activeCategory) ?> <a href="?<?= http_build_query(array_merge($_GET, ['category'=>''])) ?>" class="text-info ms-1">×</a></span>
                        <?php endif; ?>
                        <?php if ($brandSlug): ?>
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1"><?= clean($brandSlug) ?> <a href="?<?= http_build_query(array_merge($_GET, ['brand'=>''])) ?>" class="text-warning ms-1">×</a></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Categories -->
                <div class="filter-section">
                    <div class="filter-heading">Categories</div>
                    <a href="<?= BASE_URL ?>shop.php" class="brand-filter-item <?= !$categorySlug ? 'bg-primary text-white rounded-3 fw-bold' : '' ?>">
                        <span><i class="fas fa-th me-2"></i>All Categories</span>
                    </a>
                    <?php foreach ($categories as $cat): if ($cat['parent_id']) continue; ?>
                    <a href="<?= BASE_URL ?>shop.php?category=<?= $cat['slug'] ?>&sort=<?= $sort ?>"
                       class="brand-filter-item <?= $categorySlug === $cat['slug'] ? 'bg-primary text-white rounded-3 fw-bold' : '' ?>">
                        <span><i class="fas <?= clean($cat['icon'] ?? 'fa-tag') ?> me-2"></i><?= clean($cat['category_name']) ?></span>
                        <?php if ($cat['product_count'] ?? 0): ?>
                            <span class="badge <?= $categorySlug === $cat['slug'] ? 'bg-white text-primary' : 'bg-light text-muted' ?>"><?= $cat['product_count'] ?></span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Price range -->
                <div class="filter-section">
                    <div class="filter-heading">Price Range</div>
                    <form method="GET" id="priceForm">
                        <?php foreach (array_merge($_GET, ['min_price' => '', 'max_price' => '']) as $k => $v): ?>
                            <?php if (!in_array($k, ['min_price','max_price'])): ?>
                            <input type="hidden" name="<?= $k === 'search' ? 'q' : $k ?>" value="<?= clean($v) ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <input type="number" name="min_price" class="form-control form-control-sm" placeholder="<?= $priceRange['min_price'] ?>" value="<?= clean($minPrice) ?>">
                            <span class="text-muted">—</span>
                            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="<?= $priceRange['max_price'] ?>" value="<?= clean($maxPrice) ?>">
                        </div>
                        <button class="btn btn-primary btn-sm w-100 fw-bold" type="submit">Filter Price</button>
                    </form>
                </div>

                <!-- Brands -->
                <div class="filter-section">
                    <div class="filter-heading">Brands</div>
                    <div style="max-height:200px;overflow-y:auto">
                        <?php foreach ($brands as $b): ?>
                        <a href="<?= BASE_URL ?>shop.php?brand=<?= $b['slug'] ?>&sort=<?= $sort ?>"
                           class="brand-filter-item <?= $brandSlug === $b['slug'] ? 'bg-primary text-white rounded-3 fw-bold' : '' ?>">
                            <span><?= clean($b['brand_name']) ?></span>
                            <?php if ($b['product_count'] ?? 0): ?><span class="badge <?= $brandSlug === $b['slug'] ? 'bg-white text-primary' : 'bg-light text-muted' ?>"><?= $b['product_count'] ?></span><?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Rating & Stock -->
                <div class="filter-section">
                    <div class="filter-heading">Customer Rating</div>
                    <?php for ($r = 4; $r >= 1; $r--): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['rating'=>$r])) ?>" class="brand-filter-item <?= (int)$rating === $r ? 'bg-primary text-white rounded-3 fw-bold' : '' ?>">
                        <span><?= starRating($r) ?><span class="small ms-1 opacity-75">&amp; up</span></span>
                    </a>
                    <?php endfor; ?>
                </div>
                <div class="filter-section">
                    <div class="filter-heading">Availability</div>
                    <a href="?<?= http_build_query(array_merge($_GET, ['in_stock'=>'1'])) ?>" class="brand-filter-item <?= $inStock ? 'bg-success text-white rounded-3 fw-bold' : '' ?>">
                        <span><i class="fas fa-check-circle me-2"></i>In Stock Only</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- ── Product grid ──────────────────────────────────────── -->
        <div class="col-lg-9">
            <!-- Toolbar -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 p-3 bg-body rounded-3 border">
                <div>
                    <h5 class="fw-bold mb-0">
                        <?= $activeCategory ? clean($activeCategory) : ($search ? 'Search: "' . clean($search) . '"' : 'All Products') ?>
                    </h5>
                    <small class="text-muted"><?= number_format($total) ?> tech products available</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-outline-primary btn-sm d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                        <i class="fas fa-filter me-1"></i>Filters
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted d-none d-sm-inline">Sort by:</small>
                        <select class="form-select form-select-sm" style="width:160px" onchange="window.location.href=this.value">
                            <?php
                            $sortOptions = ['newest'=>'Newest Arrivals','price_asc'=>'Price: Low → High','price_desc'=>'Price: High → Low','popular'=>'Best Selling','rating'=>'Top Rated','views'=>'Most Popular'];
                            foreach ($sortOptions as $val => $label):
                                $url = BASE_URL . 'shop.php?' . http_build_query(array_merge($_GET, ['sort' => $val]));
                            ?>
                            <option value="<?= $url ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <?php if (empty($products)): ?>
            <div class="text-center py-5 bg-body rounded-4 border">
                <i class="fas fa-box-open fa-4x text-muted opacity-25 mb-3"></i>
                <h5 class="fw-bold text-muted">No products found</h5>
                <p class="text-muted">Try adjusting your keyword, category, or price range filters.</p>
                <a href="<?= BASE_URL ?>shop.php" class="btn btn-primary px-4">Browse All Devices</a>
            </div>
            <?php else: ?>
            <div class="row g-3" id="productGrid">
                <?php foreach ($products as $p): include __DIR__ . '/views/products/_card.php'; endforeach; ?>
            </div>

            <?php if ($lastPage > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-4 p-3 bg-body rounded-3 border flex-wrap gap-2">
                <small class="text-muted">Showing <strong><?= $paginator['from'] ?>–<?= $paginator['to'] ?></strong> of <strong><?= number_format($total) ?></strong> results</small>
                <?= paginationLinks($paginator, BASE_URL . 'shop.php?' . http_build_query(array_merge($_GET, ['q' => $search]))) ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Mobile filter offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold"><i class="fas fa-sliders-h me-2 text-primary"></i>Filter Products</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-3">
        <a href="<?= BASE_URL ?>shop.php" class="btn btn-outline-danger btn-sm mb-3 w-100">Clear All Filters</a>
        <h6 class="fw-bold mb-2">Categories</h6>
        <div class="d-flex flex-column gap-1 mb-4">
            <?php foreach ($categories as $cat): if ($cat['parent_id']) continue; ?>
            <a href="<?= BASE_URL ?>shop.php?category=<?= $cat['slug'] ?>" class="brand-filter-item <?= $categorySlug===$cat['slug']?'bg-primary text-white rounded-3 fw-bold':'' ?>">
                <span><i class="fas <?= clean($cat['icon'] ?? 'fa-tag') ?> me-2"></i><?= clean($cat['category_name']) ?></span>
                <?php if ($cat['product_count'] ?? 0): ?><span class="badge bg-light text-dark"><?= $cat['product_count'] ?></span><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <h6 class="fw-bold mb-2">Popular Brands</h6>
        <div class="d-flex flex-column gap-1">
            <?php foreach ($brands as $b): ?>
            <a href="<?= BASE_URL ?>shop.php?brand=<?= $b['slug'] ?>" class="brand-filter-item <?= $brandSlug===$b['slug']?'bg-primary text-white rounded-3 fw-bold':'' ?>">
                <span><?= clean($b['brand_name']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>