<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
$pageTitle  = 'Products';
$breadcrumb = [['label' => 'Products', 'active' => true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
requireStaff();

$productModel = new Product();
$db = Database::getInstance();

$filters = [
    'search'      => get('search', ''),
    'category_id' => get('category_id', ''),
    'brand_id'    => get('brand_id', ''),
    'status'      => get('status', ''),
];
$page    = max(1, (int)get('page', 1));

// Build the admin query (no forced status filter)
$where  = ['1=1'];
$params = [];
if ($filters['search'])      { $where[] = 'p.product_name LIKE ?'; $params[] = '%'.$filters['search'].'%'; }
if ($filters['category_id']) { $where[] = 'p.category_id = ?'; $params[] = $filters['category_id']; }
if ($filters['brand_id'])    { $where[] = 'p.brand_id = ?';    $params[] = $filters['brand_id']; }
if ($filters['status'])      { $where[] = 'p.status = ?';      $params[] = $filters['status']; }

$sql = "SELECT p.id, p.product_name, p.slug, p.sku, p.price, p.quantity, p.status,
               p.is_featured, p.avg_rating, p.total_sold, p.low_stock_alert, p.created_at,
               c.category_name, b.brand_name,
               pi.image_path AS primary_image
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN brands b ON p.brand_id = b.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE " . implode(' AND ', $where) . "
        ORDER BY p.created_at DESC";

$result   = $db->paginate($sql, $params, $page, 20);
$paginator = $result;
$products  = $result['data'];

$categories = $productModel->getCategories();
$brands     = $productModel->getBrands();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4><i class="fas fa-box me-2 text-primary"></i>Products</h4>
        <p>Manage your product catalog — <?= number_format($paginator['total']) ?> total</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>admin/products/add.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Add Product
        </a>
        <button onclick="exportReport('products','<?= date('Y-m-01') ?>','<?= date('Y-m-d') ?>')" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-excel me-1"></i>Export
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search product name, SKU…" value="<?= clean($filters['search']) ?>"></div>
            <div class="col-md-2">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $c): if ($c['parent_id']) continue; ?>
                        <option value="<?= $c['id'] ?>" <?= $filters['category_id'] == $c['id'] ? 'selected' : '' ?>><?= clean($c['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="brand_id" class="form-select form-select-sm">
                    <option value="">All Brands</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $filters['brand_id'] == $b['id'] ? 'selected' : '' ?>><?= clean($b['brand_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach (['active','inactive','draft','discontinued'] as $s): ?>
                        <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-primary btn-sm flex-fill" type="submit"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="<?= BASE_URL ?>admin/products/" class="btn btn-outline-secondary btn-sm px-3"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:40px"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category / Brand</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Sold</th>
                        <th class="no-sort text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-box-open fa-3x mb-3 d-block opacity-25"></i>No products found.</td></tr>
                    <?php else: foreach ($products as $p): ?>
                    <tr>
                        <td><input type="checkbox" class="form-check-input row-check" value="<?= $p['id'] ?>"></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= productImageUrl($p['primary_image'] ?? '') ?>" class="prod-thumb" alt="">
                                <div>
                                    <a href="<?= BASE_URL ?>admin/products/edit.php?id=<?= $p['id'] ?>" class="fw-semibold small text-decoration-none d-block"><?= truncate(clean($p['product_name']), 42) ?></a>
                                    <div class="d-flex gap-1 mt-1">
                                        <?php if ($p['is_featured']): ?><span class="badge bg-warning text-dark" style="font-size:.6rem">Featured</span><?php endif; ?>
                                        <?php if ($p['avg_rating'] > 0): ?><span class="badge bg-light text-dark" style="font-size:.6rem"><i class="fas fa-star text-warning"></i> <?= number_format($p['avg_rating'],1) ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><code class="small"><?= clean($p['sku'] ?? '—') ?></code></td>
                        <td>
                            <div class="small"><?= clean($p['category_name']) ?></div>
                            <small class="text-muted"><?= clean($p['brand_name']) ?></small>
                        </td>
                        <td class="fw-bold"><?= formatPrice($p['price']) ?></td>
                        <td>
                            <?php
                            $stockClass = $p['quantity'] == 0 ? 'stock-out' : ($p['quantity'] <= ($p['low_stock_alert'] ?? 5) ? 'stock-low' : 'stock-ok');
                            ?>
                            <span class="<?= $stockClass ?>"><?= number_format($p['quantity']) ?></span>
                            <?php if ($p['quantity'] <= ($p['low_stock_alert'] ?? 5) && $p['quantity'] > 0): ?>
                                <i class="fas fa-exclamation-triangle text-warning ms-1" title="Low Stock"></i>
                            <?php elseif ($p['quantity'] == 0): ?>
                                <i class="fas fa-times-circle text-danger ms-1" title="Out of Stock"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php $statusColors = ['active'=>'success','inactive'=>'secondary','draft'=>'warning','discontinued'=>'danger']; ?>
                            <span class="badge bg-<?= $statusColors[$p['status']] ?? 'secondary' ?>"><?= ucfirst($p['status']) ?></span>
                        </td>
                        <td><?= number_format($p['total_sold']) ?></td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="<?= BASE_URL ?>product/<?= $p['slug'] ?>" class="btn btn-xs btn-light" target="_blank" title="Preview"><i class="fas fa-eye"></i></a>
                                <a href="<?= BASE_URL ?>admin/products/edit.php?id=<?= $p['id'] ?>" class="btn btn-xs btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <button onclick="quickStockIn(<?= $p['id'] ?>)" class="btn btn-xs btn-outline-success" title="Stock In"><i class="fas fa-plus"></i></button>
                                <button onclick="adminAction('delete_product',<?= $p['id'] ?>,'Delete product permanently?')" class="btn btn-xs btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($paginator['last_page'] > 1): ?>
    <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Showing <?= $paginator['from'] ?>–<?= $paginator['to'] ?> of <?= number_format($paginator['total']) ?></small>
        <?= paginationLinks($paginator, BASE_URL . 'admin/products/?search=' . urlencode($filters['search']) . '&status=' . $filters['status']) ?>
    </div>
    <?php endif; ?>
</div>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>
const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';
document.getElementById('selectAll')?.addEventListener('change', function(){
    document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
});
</script>
</body></html>