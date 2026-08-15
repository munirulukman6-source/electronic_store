<?php
// admin/coupons/index.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireSales();

$db = Database::getInstance();
$errors = [];

// Handle POST before layout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = post('action');
    if ($action === 'create_coupon') {
        $result = $db->insert('coupons', [
            'coupon_code'    => strtoupper(post('coupon_code')),
            'coupon_name'    => post('coupon_name'),
            'discount_type'  => post('discount_type'),
            'discount_value' => (float)post('discount_value'),
            'min_order_amount'=> (float)(post('min_order_amount') ?: 0),
            'max_discount'   => post('max_discount') ? (float)post('max_discount') : null,
            'max_uses'       => post('max_uses') ?: null,
            'max_uses_per_user'=> (int)(post('max_uses_per_user') ?: 1),
            'start_date'     => post('start_date') ?: null,
            'expiry_date'    => post('expiry_date') ?: null,
            'is_active'      => 1,
            'created_by'     => $_SESSION['user_id'],
        ]);
        setFlash('success', 'Coupon created successfully.');
        redirect(BASE_URL . 'admin/coupons/');
    }
}

$pageTitle  = 'Coupons';
$breadcrumb = [['label' => 'Coupons', 'active' => true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';

$coupons = $db->fetchAll("SELECT c.*, u.full_name AS created_by_name FROM coupons c LEFT JOIN users u ON c.created_by = u.id ORDER BY c.created_at DESC");
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-tags me-2 text-warning"></i>Coupons & Promo Codes</h4><p>Manage discount codes</p></div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCouponModal"><i class="fas fa-plus me-1"></i>New Coupon</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 data-table">
                <thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Value</th><th>Min Order</th><th>Used / Limit</th><th>Expires</th><th>Status</th><th class="no-sort">Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($coupons as $c): ?>
                    <tr>
                        <td><code class="fw-bold"><?= clean($c['coupon_code']) ?></code></td>
                        <td><?= clean($c['coupon_name'] ?? '—') ?></td>
                        <td><span class="badge bg-info"><?= ucwords(str_replace('_',' ',$c['discount_type'])) ?></span></td>
                        <td class="fw-bold"><?= $c['discount_type']==='percentage' ? $c['discount_value'].'%' : formatPrice($c['discount_value']) ?></td>
                        <td><?= $c['min_order_amount'] > 0 ? formatPrice($c['min_order_amount']) : 'No minimum' ?></td>
                        <td><?= $c['used_count'] ?> / <?= $c['max_uses'] ?? '∞' ?></td>
                        <td><?= $c['expiry_date'] ? formatDate($c['expiry_date']) : 'No expiry' ?></td>
                        <td><?= $c['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                        <td class="text-end">
                            <button onclick="adminAction('toggle_coupon',<?= $c['id'] ?>,'<?= $c['is_active'] ? 'Deactivate' : 'Activate' ?> this coupon?')" class="btn btn-xs btn-outline-<?= $c['is_active'] ? 'warning' : 'success' ?>"><i class="fas fa-<?= $c['is_active'] ? 'pause' : 'play' ?>"></i></button>
                            <button onclick="adminAction('delete_coupon',<?= $c['id'] ?>,'Delete coupon <?= clean($c['coupon_code']) ?>?')" class="btn btn-xs btn-outline-danger ms-1"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Coupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create_coupon">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="fas fa-tag me-2"></i>New Coupon</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Coupon Code *</label><input type="text" name="coupon_code" class="form-control text-uppercase" placeholder="SAVE20" required></div>
                        <div class="col-md-6"><label class="form-label">Name / Label</label><input type="text" name="coupon_name" class="form-control" placeholder="e.g. 20% Off All Orders"></div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Type *</label>
                            <select name="discount_type" class="form-select" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (<?= DEFAULT_CURRENCY_SYMBOL ?>)</option>
                                <option value="free_shipping">Free Shipping</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Discount Value *</label><input type="number" name="discount_value" class="form-control" step="0.01" min="0" placeholder="0" required></div>
                        <div class="col-md-4"><label class="form-label">Min. Order Amount</label><input type="number" name="min_order_amount" class="form-control" step="0.01" min="0" placeholder="0.00"></div>
                        <div class="col-md-4"><label class="form-label">Max Discount Cap</label><input type="number" name="max_discount" class="form-control" step="0.01" min="0" placeholder="Unlimited"></div>
                        <div class="col-md-4"><label class="form-label">Max Uses Total</label><input type="number" name="max_uses" class="form-control" min="1" placeholder="Unlimited"></div>
                        <div class="col-md-4"><label class="form-label">Max Uses / User</label><input type="number" name="max_uses_per_user" class="form-control" min="1" value="1"></div>
                        <div class="col-md-4"><label class="form-label">Start Date</label><input type="datetime-local" name="start_date" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Expiry Date</label><input type="datetime-local" name="expiry_date" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-save me-1"></i>Create Coupon</button>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';</script>
</body></html>
