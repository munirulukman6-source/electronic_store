<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'classes/Order.php';
require_once ROOT_PATH . 'classes/Cart.php';
require_once ROOT_PATH . 'classes/Inventory.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireStaff();

$isEdit     = isset($_GET['id']);
$productModel = new Product();
$categories   = $productModel->getCategories();
$brands       = $productModel->getBrands();
$inventory    = new Inventory();
$suppliers    = $inventory->getSuppliers();

$product = [];
$errors  = [];

if ($isEdit) {
    $product = $productModel->getById((int)$_GET['id']);
    if (!$product) { setFlash('danger','Product not found.'); redirect(BASE_URL.'admin/products/'); }
}

// ── Process form submission ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();

    $specs = [];
    if (!empty($_POST['spec_key'])) {
        foreach ($_POST['spec_key'] as $i => $key) {
            $key = trim($key);
            $val = trim($_POST['spec_val'][$i] ?? '');
            if ($key && $val) $specs[$key] = $val;
        }
    }

    $data = [
        'category_id'       => (int)post('category_id'),
        'brand_id'          => (int)post('brand_id'),
        'supplier_id'       => post('supplier_id') ?: null,
        'product_name'      => post('product_name'),
        'model'             => post('model'),
        'sku'               => post('sku'),
        'barcode'           => post('barcode'),
        'price'             => (float)post('price'),
        'compare_price'     => post('compare_price') ? (float)post('compare_price') : null,
        'cost_price'        => post('cost_price') ? (float)post('cost_price') : null,
        'quantity'          => (int)post('quantity'),
        'low_stock_alert'   => (int)(post('low_stock_alert') ?: LOW_STOCK_DEFAULT),
        'weight_kg'         => post('weight_kg') ? (float)post('weight_kg') : null,
        'short_description' => post('short_description'),
        'description'       => $_POST['description'] ?? '',
        'specifications'    => $specs,
        'warranty_months'   => (int)(post('warranty_months') ?: 12),
        'warranty_info'     => post('warranty_info'),
        'is_featured'       => isset($_POST['is_featured']) ? 1 : 0,
        'is_new_arrival'    => isset($_POST['is_new_arrival']) ? 1 : 0,
        'is_best_seller'    => isset($_POST['is_best_seller']) ? 1 : 0,
        'allow_reviews'     => isset($_POST['allow_reviews']) ? 1 : 0,
        'meta_title'        => post('meta_title'),
        'meta_desc'         => post('meta_desc'),
        'meta_keywords'     => post('meta_keywords'),
        'status'            => post('status', 'active'),
        'tags'              => post('tags'),
    ];

    if (!$data['product_name']) $errors[] = 'Product name is required.';
    if (!$data['category_id'])  $errors[] = 'Category is required.';
    if (!$data['brand_id'])     $errors[] = 'Brand is required.';
    if ($data['price'] <= 0)    $errors[] = 'Price must be greater than 0.';

    if (empty($errors)) {
        $result = $isEdit
            ? $productModel->update((int)$_GET['id'], $data)
            : $productModel->create($data);

        if ($result['success']) {
            $pid = $isEdit ? (int)$_GET['id'] : $result['product_id'];

            if (!empty($_FILES['images']['name'][0])) {
                $isPrimary = !$isEdit;
                foreach ($_FILES['images']['name'] as $i => $name) {
                    if (!$name) continue;
                    $file = [
                        'name'     => $name,
                        'type'     => $_FILES['images']['type'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error'    => $_FILES['images']['error'][$i],
                        'size'     => $_FILES['images']['size'][$i],
                    ];
                    $upload = uploadImage($file, 'products');
                    if ($upload['success']) {
                        $productModel->addImage($pid, $upload['path'], $isPrimary && $i === 0);
                    }
                }
            }

            setFlash('success', $result['message']);
            redirect(BASE_URL . 'admin/products/edit.php?id=' . $pid);
        }
        $errors[] = $result['message'];
    }
}

// ── Page setup (only reached via GET or POST with errors) ──────────
$pageTitle  = $isEdit ? 'Edit Product' : 'Add Product';
$breadcrumb = [
    ['label' => 'Products', 'url' => BASE_URL . 'admin/products/'],
    ['label' => $isEdit ? 'Edit' : 'Add', 'active' => true],
];

require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';

$specs    = $product['specifications'] ?? [];
$tagsStr  = implode(', ', array_column($product['tags'] ?? [], 'tag_name'));
$images   = $isEdit ? $productModel->getImages($product['id']) : [];
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4><i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?> me-2 text-primary"></i><?= $pageTitle ?></h4>
        <p><?= $isEdit ? 'Update product information' : 'Add a new product to your catalog' ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($isEdit): ?>
            <a href="<?= BASE_URL ?>product/<?= clean($product['slug'] ?? '') ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye me-1"></i>Preview</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>admin/products/" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" novalidate>
    <?= csrfField() ?>
    <div class="row g-3">

        <!-- ── Left column ──────────────────────────────────────── -->
        <div class="col-lg-8">

            <!-- Basic info -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header border-0 fw-semibold"><i class="fas fa-info-circle me-2 text-primary"></i>Basic Information</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="product_name" id="productName" class="form-control" placeholder="e.g. Samsung Galaxy S24 Ultra" value="<?= clean($product['product_name'] ?? '') ?>" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Model Number</label>
                            <input type="text" name="model" class="form-control" placeholder="e.g. SM-S928B" value="<?= clean($product['model'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" placeholder="Auto-generated" value="<?= clean($product['sku'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Barcode / EAN</label>
                            <input type="text" name="barcode" class="form-control" placeholder="5901234567890" value="<?= clean($product['barcode'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" class="form-control" rows="2" placeholder="Brief one-line summary shown on product cards"><?= clean($product['short_description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Full Description</label>
                        <textarea name="description" class="form-control" rows="6" placeholder="Detailed product description with features, use cases, and specs"><?= clean($product['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header border-0 fw-semibold"><i class="fas fa-tags me-2 text-success"></i>Pricing</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Sale Price (<?= DEFAULT_CURRENCY_SYMBOL ?>) *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" placeholder="0.00" value="<?= $product['price'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Compare-at Price (<?= DEFAULT_CURRENCY_SYMBOL ?>)</label>
                            <input type="number" name="compare_price" class="form-control" step="0.01" min="0" placeholder="Original price (strikethrough)" value="<?= $product['compare_price'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cost Price (<?= DEFAULT_CURRENCY_SYMBOL ?>)</label>
                            <input type="number" name="cost_price" class="form-control" step="0.01" min="0" placeholder="Your purchase cost" value="<?= $product['cost_price'] ?? '' ?>">
                            <div class="form-text">Used for profit margin calculation</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header border-0 fw-semibold"><i class="fas fa-warehouse me-2 text-warning"></i>Inventory</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Quantity *</label>
                            <input type="number" name="quantity" class="form-control" min="0" value="<?= $product['quantity'] ?? 1 ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Low Stock Alert</label>
                            <input type="number" name="low_stock_alert" class="form-control" min="0" value="<?= $product['low_stock_alert'] ?? LOW_STOCK_DEFAULT ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" name="weight_kg" class="form-control" step="0.001" placeholder="0.000" value="<?= $product['weight_kg'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">None</option>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ($product['supplier_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= clean($s['supplier_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specifications builder -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header border-0 d-flex justify-content-between align-items-center fw-semibold">
                    <span><i class="fas fa-list-ul me-2 text-info"></i>Technical Specifications</span>
                    <button type="button" id="addSpecBtn" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus me-1"></i>Add Row</button>
                </div>
                <div class="card-body">
                    <div id="specContainer">
                        <?php if (!empty($specs)): foreach ($specs as $k => $v): ?>
                        <div class="spec-row">
                            <input type="text" class="form-control" name="spec_key[]" placeholder="Key (e.g. Display)" value="<?= clean($k) ?>">
                            <input type="text" class="form-control" name="spec_val[]" placeholder="Value (e.g. 6.8 inch AMOLED)" value="<?= clean($v) ?>">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-spec"><i class="fas fa-trash"></i></button>
                        </div>
                        <?php endforeach; else: ?>
                        <div class="spec-row">
                            <input type="text" class="form-control" name="spec_key[]" placeholder="Key (e.g. Display)">
                            <input type="text" class="form-control" name="spec_val[]" placeholder="Value (e.g. 6.8 inch AMOLED)">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-spec"><i class="fas fa-trash"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-text mt-2">Specs appear in the product detail comparison table.</div>
                </div>
            </div>

            <!-- Warranty -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header border-0 fw-semibold"><i class="fas fa-shield-alt me-2 text-success"></i>Warranty</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Warranty (months)</label>
                            <input type="number" name="warranty_months" class="form-control" min="0" value="<?= $product['warranty_months'] ?? 12 ?>">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Warranty Details</label>
                            <input type="text" name="warranty_info" class="form-control" placeholder="e.g. Manufacturer warranty, covers hardware defects" value="<?= clean($product['warranty_info'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header border-0 fw-semibold"><i class="fas fa-search me-2 text-secondary"></i>SEO &amp; Meta</div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" placeholder="Defaults to product name" value="<?= clean($product['meta_title'] ?? '') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_desc" class="form-control" rows="2" placeholder="Short description for search engines (150–160 chars)"><?= clean($product['meta_desc'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" placeholder="smartphone, android, 5G, …" value="<?= clean($product['meta_keywords'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Right column ─────────────────────────────────────── -->
        <div class="col-lg-4">

            <!-- Publish -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header border-0 fw-semibold"><i class="fas fa-paper-plane me-2 text-primary"></i>Publish</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['active' => 'Active', 'inactive' => 'Inactive', 'draft' => 'Draft', 'discontinued' => 'Discontinued'] as $v => $l): ?>
                                <option value="<?= $v ?>" <?= ($product['status'] ?? 'active') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <div class="form-check">
                            <input type="checkbox" name="is_featured" id="isFeatured" class="form-check-input" value="1" <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isFeatured"><i class="fas fa-star text-warning me-1"></i>Featured Product</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_new_arrival" id="isNew" class="form-check-input" value="1" <?= ($product['is_new_arrival'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isNew"><i class="fas fa-certificate text-info me-1"></i>New Arrival</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_best_seller" id="isBest" class="form-check-input" value="1" <?= ($product['is_best_seller'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isBest"><i class="fas fa-fire text-danger me-1"></i>Best Seller</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="allow_reviews" id="allowReviews" class="form-check-input" value="1" <?= ($product['allow_reviews'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="allowReviews"><i class="fas fa-comments text-success me-1"></i>Allow Reviews</label>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="fas fa-save me-2"></i><?= $isEdit ? 'Update Product' : 'Create Product' ?>
                    </button>
                </div>
            </div>

            <!-- Category & Brand -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header border-0 fw-semibold"><i class="fas fa-layer-group me-2 text-info"></i>Category &amp; Brand</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-select select2" required>
                            <option value="">— Select Category —</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($product['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                    <?= $c['parent_id'] ? '&nbsp;&nbsp;&nbsp;↳ ' : '' ?><?= clean($c['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Brand *</label>
                        <select name="brand_id" class="form-select select2" required>
                            <option value="">— Select Brand —</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= ($product['brand_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= clean($b['brand_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header border-0 fw-semibold"><i class="fas fa-images me-2 text-warning"></i>Product Images</div>
                <div class="card-body">
                    <?php if (!empty($images)): ?>
                    <div class="upload-preview mb-3">
                        <?php foreach ($images as $img): ?>
                        <div class="upload-thumb" title="<?= $img['is_primary'] ? 'Primary' : '' ?>">
                            <img src="<?= productImageUrl($img['image_path']) ?>" alt="">
                            <?php if ($img['is_primary']): ?>
                                <div class="remove-img" style="background:rgba(13,110,253,.8)" title="Primary"><i class="fas fa-star"></i></div>
                            <?php else: ?>
                                <div class="remove-img" onclick="deleteImage(<?= $img['id'] ?>, this.closest('.upload-thumb'))"><i class="fas fa-times"></i></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="upload-zone">
                        <input type="file" name="images[]" multiple accept="image/*" class="d-none">
                        <i class="fas fa-cloud-upload-alt mb-2"></i>
                        <div class="small fw-semibold">Drag &amp; drop or click to upload</div>
                        <div class="x-small text-muted">JPG, PNG, WEBP · Max 5MB each</div>
                    </div>
                    <div class="upload-preview mt-2"></div>
                </div>
            </div>

            <!-- Tags -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header border-0 fw-semibold"><i class="fas fa-hashtag me-2 text-secondary"></i>Tags</div>
                <div class="card-body">
                    <input type="text" name="tags" class="form-control" placeholder="5G, Android, Camera, Gaming, …" value="<?= clean($tagsStr) ?>">
                    <div class="form-text">Comma-separated. Help customers find this product via search.</div>
                </div>
            </div>
        </div>
    </div>
</form>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>
const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';
async function deleteImage(id, el){
    const r = await Swal.fire({title:'Remove image?',icon:'warning',showCancelButton:true,confirmButtonColor:'#dc3545'});
    if(!r.isConfirmed) return;
    const res = await fetch(`${BASE_URL}ajax/admin_action.php`,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=delete_image&id=${id}&csrf_token=${CSRF_TOKEN}`});
    const d = await res.json();
    d.success ? (el.remove(), toastr.success('Image removed.')) : toastr.error(d.message);
}
</script>
</body></html>