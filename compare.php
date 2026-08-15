<?php
$pageTitle = 'Compare Products';
require_once __DIR__ . '/views/layouts/header.php';

// Get product IDs from URL: compare.php?ids=1,2,3,4
$rawIds  = get('ids', '');
$ids     = array_filter(array_map('intval', explode(',', $rawIds)));
$ids     = array_slice(array_unique($ids), 0, MAX_COMPARE_ITEMS);

$productModel = new Product();
$products     = !empty($ids) ? $productModel->getForComparison($ids) : [];

// Collect all unique spec keys across compared products
$allSpecKeys = [];
foreach ($products as $p) {
    $specs = is_array($p['specifications']) ? $p['specifications'] : json_decode($p['specifications'] ?? '{}', true);
    $p['_specs'] = $specs;
    foreach (array_keys($specs ?? []) as $k) {
        if (!in_array($k, $allSpecKeys)) $allSpecKeys[] = $k;
    }
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-extrabold mb-1 d-flex align-items-center gap-2">
                <i class="fas fa-exchange-alt text-primary"></i> Compare Products
            </h3>
            <p class="text-muted small mb-0">Side-by-side comparison (up to <?= MAX_COMPARE_ITEMS ?> items)</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>shop.php" class="btn btn-outline-primary btn-sm fw-bold">
                <i class="fas fa-plus me-1"></i>Add More
            </a>
            <button onclick="localStorage.removeItem('es_compare'); location.href='<?= BASE_URL ?>compare.php'"
                    class="btn btn-outline-danger btn-sm fw-bold">
                <i class="fas fa-trash me-1"></i>Clear All
            </button>
        </div>
    </div>

    <?php if (empty($products)): ?>
    <!-- Empty state -->
    <div class="text-center py-5 bg-body rounded-4 border shadow-sm">
        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px">
            <i class="fas fa-exchange-alt fa-3x text-primary opacity-50"></i>
        </div>
        <h4 class="fw-bold mb-2">No Products Selected</h4>
        <p class="text-muted mb-4 mx-auto" style="max-width:420px;">Browse products and click the compare icon on any item to view side-by-side technical differences here.</p>
        <a href="<?= BASE_URL ?>shop.php" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm">
            <i class="fas fa-store me-2"></i>Browse Products
        </a>
    </div>
    <?php else: ?>

    <div class="table-responsive shadow-sm rounded-4 overflow-hidden mb-4">
        <table class="table compare-table align-middle mb-0">
            <!-- Product images & names header -->
            <thead>
                <tr>
                    <th style="width:180px;min-width:180px" class="fw-bold small text-uppercase text-muted">Feature</th>
                    <?php foreach ($products as $p): ?>
                    <th class="text-center p-3" style="min-width:220px">
                        <div class="position-relative">
                            <button onclick="removeFromCompare(<?= $p['id'] ?>)"
                                    class="btn btn-sm btn-outline-danger rounded-circle position-absolute top-0 end-0 d-flex align-items-center justify-content-center"
                                    style="width:26px;height:26px;padding:0;" title="Remove">
                                <i class="fas fa-times" style="font-size:0.75rem;"></i>
                            </button>
                        </div>
                        <!-- Product image -->
                        <a href="<?= BASE_URL ?>product/<?= clean($p['slug']) ?>" class="d-inline-block my-2">
                            <img src="<?= productImageUrl($p['primary_image'] ?? null, $p['category_slug'] ?? '', $p['product_name']) ?>"
                                 alt="<?= clean($p['product_name']) ?>"
                                 class="compare-product-img"
                                 onerror="this.onerror=null;this.src='<?= BASE_URL ?>assets/images/product_img.php?cat=<?= urlencode($p['category_slug'] ?? '') ?>&name=<?= urlencode($p['product_name']) ?>'">
                        </a>
                        <div class="small text-muted mb-1 text-uppercase fw-bold" style="font-size:0.75rem;letter-spacing:0.5px;"><?= clean($p['brand_name']) ?></div>
                        <a href="<?= BASE_URL ?>product/<?= clean($p['slug']) ?>" class="fw-bold text-decoration-none text-body d-block mb-2" style="font-size:.9rem;line-height:1.3">
                            <?= clean($p['product_name']) ?>
                        </a>
                        <?= starRating($p['avg_rating'] ?? 0) ?>
                        <div class="small text-muted mt-1">(<?= $p['review_count'] ?? 0 ?> reviews)</div>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <!-- Price row -->
                <tr>
                    <td class="fw-bold small text-muted">Price</td>
                    <?php foreach ($products as $p):
                        $eff = (float)($p['effective_price'] ?? $p['price']);
                        $hasDisc = $eff < (float)$p['price'] - 0.01;
                    ?>
                    <td class="text-center compare-price-cell">
                        <div class="fw-extrabold fs-4 text-primary"><?= formatPrice($eff) ?></div>
                        <?php if ($hasDisc): ?>
                            <div class="text-muted text-decoration-line-through small"><?= formatPrice($p['price']) ?></div>
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill fw-bold">Save <?= round((1 - $eff/$p['price'])*100) ?>%</span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>

                <!-- Add to cart row -->
                <tr>
                    <td class="fw-bold small text-muted">Availability</td>
                    <?php foreach ($products as $p): ?>
                    <td class="text-center">
                        <?php if (($p['quantity'] ?? 0) > 0): ?>
                            <button class="btn btn-primary btn-sm w-100 fw-bold btn-add-cart mb-2" data-product-id="<?= $p['id'] ?>">
                                <i class="fas fa-cart-plus me-1"></i>Add to Cart
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm w-100 mb-2" disabled>Out of Stock</button>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>product/<?= clean($p['slug']) ?>" class="btn btn-outline-secondary btn-sm w-100">View Details</a>
                    </td>
                    <?php endforeach; ?>
                </tr>

                <!-- General info section -->
                <tr>
                    <td colspan="<?= count($products) + 1 ?>" class="compare-section-header py-2 px-3">
                        <i class="fas fa-info-circle me-1"></i>General Information
                    </td>
                </tr>
                <?php
                $generalRows = [
                    ['Category',  'category_name'],
                    ['Brand',     'brand_name'],
                    ['Model',     'model'],
                    ['SKU',       'sku'],
                    ['Warranty',  'warranty_months'],
                    ['Stock',     'quantity'],
                ];
                foreach ($generalRows as [$label, $field]):
                ?>
                <tr>
                    <td class="fw-semibold small text-muted"><?= $label ?></td>
                    <?php foreach ($products as $p): ?>
                    <td class="text-center small">
                        <?php
                        $val = $p[$field] ?? '—';
                        if ($field === 'warranty_months') $val = $val ? $val . ' months' : '—';
                        if ($field === 'quantity') {
                            echo $val > 0
                                ? "<span class='text-success fw-semibold'><i class='fas fa-check-circle me-1'></i>In Stock ($val)</span>"
                                : "<span class='text-danger fw-semibold'><i class='fas fa-times-circle me-1'></i>Out of Stock</span>";
                        } else {
                            echo clean($val ?: '—');
                        }
                        ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>

                <!-- Technical specifications -->
                <?php if (!empty($allSpecKeys)): ?>
                <tr>
                    <td colspan="<?= count($products) + 1 ?>" class="compare-section-header py-2 px-3">
                        <i class="fas fa-microchip me-1"></i>Technical Specifications
                    </td>
                </tr>
                <?php foreach ($allSpecKeys as $specKey): ?>
                <tr>
                    <td class="fw-semibold small text-muted"><?= clean($specKey) ?></td>
                    <?php foreach ($products as $p):
                        $specs = is_array($p['specifications'])
                            ? $p['specifications']
                            : json_decode($p['specifications'] ?? '{}', true);
                        $val = $specs[$specKey] ?? null;
                    ?>
                    <td class="text-center small">
                        <?php if ($val === null): ?>
                            <span class="text-muted">—</span>
                        <?php elseif (strtolower((string)$val) === 'true' || $val === true): ?>
                            <i class="fas fa-check-circle text-success fs-6"></i>
                        <?php elseif (strtolower((string)$val) === 'false' || $val === false): ?>
                            <i class="fas fa-times-circle text-danger fs-6"></i>
                        <?php else: ?>
                            <span class="fw-semibold"><?= clean($val) ?></span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; endif; ?>

            </tbody>
        </table>
    </div>

    <!-- Bottom Action Card -->
    <div class="card border-0 shadow-sm p-4 text-center" style="border-radius:14px;">
        <p class="text-muted mb-3">Ready to proceed? Add your chosen product to your shopping bag.</p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <?php foreach ($products as $p): ?>
            <?php if (($p['quantity'] ?? 0) > 0): ?>
            <button class="btn btn-primary btn-add-cart fw-bold" data-product-id="<?= $p['id'] ?>">
                <i class="fas fa-cart-plus me-1"></i><?= truncate(clean($p['product_name']), 25) ?>
            </button>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
function removeFromCompare(id) {
    let items = JSON.parse(localStorage.getItem('es_compare') || '[]');
    items = items.filter(i => i.id != id);
    localStorage.setItem('es_compare', JSON.stringify(items));
    const ids = items.map(i => i.id).join(',');
    window.location.href = '<?= BASE_URL ?>compare.php' + (ids ? '?ids=' + ids : '');
}

document.addEventListener('DOMContentLoaded', () => {
    <?php if (empty($ids)): ?>
    const stored = JSON.parse(localStorage.getItem('es_compare') || '[]');
    if (stored.length) {
        window.location.href = '<?= BASE_URL ?>compare.php?ids=' + stored.map(i => i.id).join(',');
    }
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>
