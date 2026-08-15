<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireStaff();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = post('action');
    if ($action === 'create') {
        $name = post('category_name');
        $slug = slugify($name);
        // make slug unique
        $base = $slug; $n = 1;
        while ($db->count('categories','slug = ?',[$slug])) $slug = $base.'-'.$n++;
        $db->insert('categories',[
            'parent_id'     => post('parent_id') ?: null,
            'category_name' => $name,
            'slug'          => $slug,
            'icon'          => post('icon') ?: 'fa-tag',
            'description'   => post('description'),
            'sort_order'    => (int)(post('sort_order') ?: 0),
            'is_featured'   => isset($_POST['is_featured']) ? 1 : 0,
            'status'        => 1,
        ]);
        setFlash('success','Category created.'); redirect(BASE_URL.'admin/products/categories.php');
    }
    if ($action === 'toggle') {
        $cur = $db->fetchColumn("SELECT status FROM categories WHERE id=?",(int)post('id') ? [(int)post('id')] : [0]);
        $db->update('categories',['status'=>$cur?0:1],'id=?',[(int)post('id')]);
        setFlash('success','Category updated.'); redirect(BASE_URL.'admin/products/categories.php');
    }
    if ($action === 'delete') {
        $id = (int)post('id');
        if ($db->count('products','category_id=?',[$id])) { setFlash('danger','Cannot delete — products exist in this category.'); }
        else { $db->delete('categories','id=?',[$id]); setFlash('success','Category deleted.'); }
        redirect(BASE_URL.'admin/products/categories.php');
    }
}

$pageTitle  = 'Categories';
$breadcrumb = [['label'=>'Products','url'=>BASE_URL.'admin/products/'],['label'=>'Categories','active'=>true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';

$categories = $db->fetchAll("SELECT c.*,p.category_name AS parent_name,
    (SELECT COUNT(*) FROM products WHERE category_id=c.id AND status='active') AS product_count
    FROM categories c LEFT JOIN categories p ON c.parent_id=p.id ORDER BY c.sort_order,c.category_name");
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-tags me-2 text-primary"></i>Product Categories</h4><p>Manage your product taxonomy</p></div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCatModal">
        <i class="fas fa-plus me-1"></i>Add Category
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 data-table">
                <thead><tr><th>Category</th><th>Slug</th><th>Parent</th><th>Icon</th><th class="text-center">Products</th><th class="text-center">Featured</th><th>Status</th><th class="no-sort text-end">Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= $c['parent_id'] ? '&nbsp;&nbsp;↳ ' : '' ?><?= clean($c['category_name']) ?></div>
                            <?php if ($c['description']): ?><small class="text-muted"><?= truncate(clean($c['description']),40) ?></small><?php endif; ?>
                        </td>
                        <td><code class="small"><?= clean($c['slug']) ?></code></td>
                        <td class="small text-muted"><?= clean($c['parent_name'] ?? '—') ?></td>
                        <td><i class="fas <?= clean($c['icon']??'fa-tag') ?> text-primary"></i></td>
                        <td class="text-center"><span class="badge bg-light text-dark"><?= $c['product_count'] ?></span></td>
                        <td class="text-center"><?= $c['is_featured'] ? '<i class="fas fa-star text-warning"></i>' : '—' ?></td>
                        <td><?= $c['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                        <td class="text-end">
                            <form method="POST" class="d-inline">
                                <?= csrfField() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button class="btn btn-xs btn-outline-<?= $c['status']?'warning':'success' ?>"><i class="fas fa-<?= $c['status']?'eye-slash':'eye' ?>"></i></button>
                            </form>
                            <form method="POST" class="d-inline ms-1" onsubmit="return confirm('Delete category?')">
                                <?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button class="btn btn-xs btn-outline-danger" <?= $c['product_count']>0?'disabled':'' ?>><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <?= csrfField() ?><input type="hidden" name="action" value="create">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold">Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Category Name *</label><input type="text" name="category_name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Parent Category</label>
                        <select name="parent_id" class="form-select"><option value="">None (Top-Level)</option>
                            <?php foreach ($categories as $c): if ($c['parent_id']) continue; ?>
                            <option value="<?= $c['id'] ?>"><?= clean($c['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-8"><label class="form-label">Font Awesome Icon</label><input type="text" name="icon" class="form-control" placeholder="fa-mobile-alt" value="fa-tag"></div>
                        <div class="col-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="0"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    <div class="form-check"><input type="checkbox" name="is_featured" class="form-check-input" id="catFeat"><label class="form-check-label" for="catFeat">Featured on homepage</label></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-save me-1"></i>Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';</script>
</body></html>