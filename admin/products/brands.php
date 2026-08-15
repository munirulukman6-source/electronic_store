<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireStaff();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = post('action');
    if ($action === 'create' || $action === 'update') {
        $name = post('brand_name');
        $slug = slugify($name);
        $base = $slug; $n = 1;
        $excl = $action==='update' ? ' AND id != '.(int)post('brand_id') : '';
        while ($db->fetchColumn("SELECT id FROM brands WHERE slug=?$excl", [$slug])) $slug = $base.'-'.$n++;

        // Logo upload
        $logoPath = post('existing_logo','');
        if (!empty($_FILES['logo']['name'])) {
            $up = uploadImage($_FILES['logo'],'brands');
            if ($up['success']) $logoPath = $up['path'];
        }

        $data = ['brand_name'=>$name,'slug'=>$slug,'website'=>post('website'),'description'=>post('description'),
                 'country'=>post('country'),'logo'=>$logoPath,'is_featured'=>isset($_POST['is_featured'])?1:0,'status'=>1];
        $action==='create' ? $db->insert('brands',$data) : $db->update('brands',$data,'id=?',[(int)post('brand_id')]);
        setFlash('success','Brand '.($action==='create'?'created':'updated').'.');
        redirect(BASE_URL.'admin/products/brands.php');
    }
    if ($action === 'delete') {
        $id=(int)post('id');
        if ($db->count('products','brand_id=?',[$id])) setFlash('danger','Cannot delete — products use this brand.');
        else { $db->delete('brands','id=?',[$id]); setFlash('success','Brand deleted.'); }
        redirect(BASE_URL.'admin/products/brands.php');
    }
}

$pageTitle  = 'Brands';
$breadcrumb = [['label'=>'Products','url'=>BASE_URL.'admin/products/'],['label'=>'Brands','active'=>true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';

$brands = $db->fetchAll("SELECT b.*,(SELECT COUNT(*) FROM products WHERE brand_id=b.id AND status='active') AS product_count
    FROM brands b ORDER BY b.brand_name");
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-certificate me-2 text-warning"></i>Brands</h4><p>Manage product brands and manufacturers</p></div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBrandModal">
        <i class="fas fa-plus me-1"></i>Add Brand
    </button>
</div>

<div class="row g-3 mb-3">
    <?php foreach (array_slice(array_filter($brands,fn($b)=>$b['is_featured']),0,6) as $fb): ?>
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <?php if ($fb['logo'] && file_exists(UPLOAD_PATH.$fb['logo'])): ?>
            <img src="<?= UPLOAD_URL.clean($fb['logo']) ?>" height="36" class="mx-auto mb-1 object-fit-contain" alt="">
            <?php else: ?>
            <div class="fw-bold text-primary mb-1"><?= substr(clean($fb['brand_name']),0,2) ?></div>
            <?php endif; ?>
            <div class="small fw-semibold text-truncate"><?= clean($fb['brand_name']) ?></div>
            <div class="badge bg-light text-dark mt-1" style="font-size:.65rem"><?= $fb['product_count'] ?> products</div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 data-table">
                <thead><tr><th>Brand</th><th>Slug</th><th>Country</th><th>Website</th><th class="text-center">Products</th><th class="text-center">Featured</th><th>Status</th><th class="no-sort text-end">Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($brands as $b): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($b['logo'] && file_exists(UPLOAD_PATH.$b['logo'])): ?>
                                <img src="<?= UPLOAD_URL.clean($b['logo']) ?>" class="brand-logo" width="32" height="32" style="object-fit:contain">
                                <?php else: ?>
                                <div class="brand-logo d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold" style="width:32px;height:32px;font-size:.7rem;border-radius:6px"><?= strtoupper(substr($b['brand_name'],0,2)) ?></div>
                                <?php endif; ?>
                                <span class="fw-semibold"><?= clean($b['brand_name']) ?></span>
                            </div>
                        </td>
                        <td><code class="small"><?= clean($b['slug']) ?></code></td>
                        <td class="small"><?= clean($b['country']??'—') ?></td>
                        <td><?= $b['website'] ? '<a href="'.clean($b['website']).'" target="_blank" class="small text-truncate d-block" style="max-width:140px">'.parse_url($b['website'],PHP_URL_HOST).'</a>' : '<span class="text-muted small">—</span>' ?></td>
                        <td class="text-center"><span class="badge bg-primary"><?= $b['product_count'] ?></span></td>
                        <td class="text-center"><?= $b['is_featured'] ? '<i class="fas fa-star text-warning"></i>' : '—' ?></td>
                        <td><?= $b['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                        <td class="text-end">
                            <button class="btn btn-xs btn-outline-primary edit-brand-btn"
                                    data-bs-toggle="modal" data-bs-target="#editBrandModal"
                                    data-brand="<?= htmlspecialchars(json_encode($b),ENT_QUOTES) ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" class="d-inline ms-1" onsubmit="return confirm('Delete brand?')">
                                <?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $b['id'] ?>">
                                <button class="btn btn-xs btn-outline-danger" <?= $b['product_count']>0?'disabled':'' ?>><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Brand Modal -->
<div class="modal fade" id="addBrandModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" enctype="multipart/form-data">
                <?= csrfField() ?><input type="hidden" name="action" value="create">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold">Add Brand</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Brand Name *</label><input type="text" name="brand_name" class="form-control" required></div>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="form-label">Website</label><input type="url" name="website" class="form-control" placeholder="https://"></div>
                        <div class="col-6"><label class="form-label">Country</label><input type="text" name="country" class="form-control" placeholder="USA, Japan…"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Logo</label><input type="file" name="logo" class="form-control" accept="image/*"></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    <div class="form-check"><input type="checkbox" name="is_featured" class="form-check-input" id="bFeat"><label class="form-check-label" for="bFeat">Featured brand</label></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-save me-1"></i>Save Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Brand Modal -->
<div class="modal fade" id="editBrandModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" enctype="multipart/form-data" id="editBrandForm">
                <?= csrfField() ?><input type="hidden" name="action" value="update"><input type="hidden" name="brand_id" id="eBrandId"><input type="hidden" name="existing_logo" id="eLogoPath">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold">Edit Brand</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Brand Name *</label><input type="text" name="brand_name" id="eBrandName" class="form-control" required></div>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="form-label">Website</label><input type="url" name="website" id="eWebsite" class="form-control"></div>
                        <div class="col-6"><label class="form-label">Country</label><input type="text" name="country" id="eCountry" class="form-control"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">New Logo (optional)</label><input type="file" name="logo" class="form-control" accept="image/*"><div class="form-text" id="eLogoNote"></div></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="eDesc" class="form-control" rows="2"></textarea></div>
                    <div class="form-check"><input type="checkbox" name="is_featured" id="eBFeat" class="form-check-input"><label class="form-check-label" for="eBFeat">Featured brand</label></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-save me-1"></i>Update Brand</button>
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
<script>
const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';
document.querySelectorAll('.edit-brand-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
        const b=JSON.parse(btn.dataset.brand);
        document.getElementById('eBrandId').value   = b.id;
        document.getElementById('eBrandName').value = b.brand_name||'';
        document.getElementById('eWebsite').value   = b.website||'';
        document.getElementById('eCountry').value   = b.country||'';
        document.getElementById('eDesc').value      = b.description||'';
        document.getElementById('eBFeat').checked   = b.is_featured=='1';
        document.getElementById('eLogoPath').value  = b.logo||'';
        document.getElementById('eLogoNote').textContent = b.logo ? 'Current: '+b.logo : '';
    });
});
</script>
</body></html>