<?php
// bundle.php — Modern Bundle Detail Showcase
$slug = trim($_GET['slug'] ?? basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
if (!$slug) { redirect(BASE_URL . 'shop.php'); }

require_once __DIR__ . '/views/layouts/header.php';

$db     = Database::getInstance();
$bundle = $db->fetchOne(
    "SELECT * FROM product_bundles WHERE slug = ? AND status = 1", [$slug]
);

if (!$bundle) {
    http_response_code(404);
    echo '<div class="container py-5 text-center"><h2 class="fw-bold">Bundle not found.</h2><a href="'.BASE_URL.'shop.php" class="btn btn-primary mt-3">Browse Shop</a></div>';
    require_once __DIR__ . '/views/layouts/footer.php';
    exit;
}

$pageTitle = clean($bundle['bundle_name']);

// Get bundle items with product details
$items = $db->fetchAll(
    "SELECT bi.quantity AS bundle_qty, p.*, c.category_name, b.brand_name,
            pi.image_path AS primary_image
     FROM bundle_items bi
     JOIN products p ON bi.product_id = p.id
     JOIN categories c ON p.category_id = c.id
     JOIN brands b ON p.brand_id = b.id
     LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
     WHERE bi.bundle_id = ?", [$bundle['id']]
);

$savings = $bundle['original_price'] - $bundle['bundle_price'];
$discPct = $bundle['original_price'] > 0
    ? round((1 - $bundle['bundle_price'] / $bundle['original_price']) * 100, 1)
    : 0;
?>

<div class="container py-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>shop.php">Shop</a></li>
            <li class="breadcrumb-item active">Bundles</li>
            <li class="breadcrumb-item active" aria-current="page"><?= truncate(clean($bundle['bundle_name']), 35) ?></li>
        </ol>
    </nav>

    <!-- Bundle Hero -->
    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <!-- Product collage -->
            <div class="row g-3">
                <?php foreach ($items as $i => $item):
                    $imgUrl = productImageUrl($item['primary_image'] ?? null, $item['category_name'] ?? '', $item['product_name']);
                ?>
                <div class="col-6">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-radius:14px;">
                        <div style="height:210px; background:#fff; display:flex; align-items:center; justify-content:center; padding:12px;" class="border-bottom">
                            <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES) ?>"
                                 alt="<?= clean($item['product_name']) ?>"
                                 style="max-height:180px; max-width:100%; object-fit:contain;"
                                 onerror="this.src='<?= BASE_URL ?>assets/images/product_img.php?cat=<?= urlencode($item['category_name']??'') ?>&name=<?= urlencode($item['product_name']) ?>'">
                        </div>
                        <div class="card-body p-3">
                            <div class="small fw-bold text-truncate mb-1"><?= clean($item['product_name']) ?></div>
                            <span class="text-primary fw-extrabold small"><?= formatPrice($item['price']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                        <i class="fas fa-layer-group me-1"></i>Exclusive Bundle
                    </span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                        Save <?= $discPct ?>%
                    </span>
                </div>

                <h1 class="fs-2 fw-extrabold mb-0"><?= clean($bundle['bundle_name']) ?></h1>

                <?php if ($bundle['description']): ?>
                <p class="text-muted mb-0"><?= clean($bundle['description']) ?></p>
                <?php endif; ?>

                <!-- What's included list -->
                <div class="p-3 bg-body rounded-3 border">
                    <div class="fw-bold small text-uppercase text-muted mb-2"><i class="fas fa-check-circle text-success me-1"></i>Included in this Package:</div>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($items as $item): ?>
                        <div class="d-flex align-items-center justify-content-between p-2 bg-body-tertiary rounded-2 small">
                            <div class="text-truncate me-2 fw-semibold">
                                <i class="fas fa-box-open text-primary me-2"></i><?= clean($item['product_name']) ?>
                            </div>
                            <span class="text-muted"><?= formatPrice($item['price']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Price Box -->
                <div class="p-4 bg-body rounded-3 border">
                    <div class="d-flex justify-content-between mb-1 small text-muted">
                        <span>Individual Total</span>
                        <span class="text-decoration-line-through"><?= formatPrice($bundle['original_price']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small text-success fw-bold">
                        <span>Instant Savings</span>
                        <span>-<?= formatPrice($savings) ?></span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5">Bundle Price</span>
                        <span class="fw-extrabold fs-3 text-primary"><?= formatPrice($bundle['bundle_price']) ?></span>
                    </div>
                </div>

                <!-- Add to cart -->
                <button class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow-sm" id="addBundleToCart">
                    <i class="fas fa-shopping-bag me-2"></i>Add Complete Bundle to Cart
                </button>

                <div class="d-flex gap-3 justify-content-center small text-muted pt-2 border-top">
                    <span><i class="fas fa-shield-alt text-success me-1"></i>Genuine</span>
                    <span><i class="fas fa-undo text-primary me-1"></i>30-Day Returns</span>
                    <span><i class="fas fa-truck text-info me-1"></i>Fast Delivery</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Bundles -->
    <?php
    $related = $db->fetchAll(
        "SELECT * FROM product_bundles WHERE id != ? AND status = 1 ORDER BY RAND() LIMIT 3",
        [$bundle['id']]
    );
    if (!empty($related)):
    ?>
    <div class="mb-5">
        <div class="section-header">
            <h4 class="section-title"><i class="fas fa-layer-group text-primary me-2"></i>Other Bundle Deals</h4>
        </div>
        <div class="row g-3">
            <?php foreach ($related as $rb): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                    <div class="card-body p-4 d-flex flex-column">
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1 rounded-pill mb-2" style="width:fit-content;">Save <?= number_format($rb['discount_pct'],1) ?>%</span>
                        <h6 class="fw-bold mb-2"><?= clean($rb['bundle_name']) ?></h6>
                        <p class="text-muted small mb-3"><?= truncate(clean($rb['description']??''),60) ?></p>
                        <div class="mt-auto d-flex align-items-center justify-content-between mb-3">
                            <span class="fw-extrabold text-primary fs-5"><?= formatPrice($rb['bundle_price']) ?></span>
                            <span class="text-muted text-decoration-line-through small"><?= formatPrice($rb['original_price']) ?></span>
                        </div>
                        <a href="<?= BASE_URL ?>bundle/<?= clean($rb['slug']) ?>" class="btn btn-outline-primary btn-sm fw-bold">View Bundle</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
document.getElementById('addBundleToCart')?.addEventListener('click', async function () {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding Bundle…';
    const products = <?= json_encode(array_column($items,'id')) ?>;
    let lastCount  = 0;
    for (const pid of products) {
        try {
            const res  = await fetch('<?= BASE_URL ?>ajax/cart.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `action=add&product_id=${pid}&qty=1&csrf_token=<?= csrfToken() ?>`
            });
            const data = await res.json();
            if (data.count) lastCount = data.count;
        } catch(e) {}
    }
    Cart.updateBadge(lastCount);
    toastr.success('Bundle items added to cart!');
    this.disabled = false;
    this.innerHTML = '<i class="fas fa-check me-2"></i>Added to Cart!';
    setTimeout(()=>{this.innerHTML='<i class="fas fa-shopping-bag me-2"></i>Add Complete Bundle to Cart';}, 3000);
});
</script>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>
