<?php
// admin/suppliers/index.php
$pageTitle  = 'Suppliers';
$breadcrumb = [['label' => 'Suppliers', 'active' => true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
requireRole('admin', 'inventory_manager');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = post('action');
    if (in_array($action, ['create_supplier', 'update_supplier'])) {
        $data = [
            'supplier_name'  => post('supplier_name'),
            'contact_person' => post('contact_person'),
            'email'          => post('email'),
            'phone'          => post('phone'),
            'alt_phone'      => post('alt_phone'),
            'address'        => post('address'),
            'city'           => post('city'),
            'country'        => post('country'),
            'payment_terms'  => post('payment_terms'),
            'lead_time_days' => (int)(post('lead_time_days') ?: 7),
            'notes'          => post('notes'),
            'status'         => 1,
        ];
        if ($action === 'create_supplier') {
            $db->insert('suppliers', $data);
            setFlash('success', 'Supplier added successfully.');
        } else {
            $db->update('suppliers', $data, 'id = ?', [(int)post('supplier_id')]);
            setFlash('success', 'Supplier updated.');
        }
        redirect(BASE_URL . 'admin/suppliers/');
    }
}

$suppliers = $db->fetchAll(
    "SELECT s.*, COUNT(DISTINCT p.id) AS product_count, COALESCE(SUM(i.quantity),0) AS total_stocked
     FROM suppliers s
     LEFT JOIN products p ON s.id = p.supplier_id AND p.status='active'
     LEFT JOIN inventory i ON s.id = i.supplier_id AND i.transaction_type='stock_in'
     WHERE s.status = 1 GROUP BY s.id ORDER BY s.supplier_name"
);
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-truck-loading me-2 text-info"></i>Suppliers</h4><p>Manage your product suppliers</p></div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
        <i class="fas fa-plus me-1"></i>Add Supplier
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 data-table">
                <thead><tr><th>Supplier</th><th>Contact</th><th>Location</th><th>Payment Terms</th><th>Lead Time</th><th class="text-center">Products</th><th class="text-center">Rating</th><th class="no-sort text-end">Actions</th></tr></thead>
                <tbody>
                    <?php if (empty($suppliers)): ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-truck fa-3x opacity-25 mb-3 d-block"></i>No suppliers added yet.</td></tr>
                    <?php else: foreach ($suppliers as $s): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= clean($s['supplier_name']) ?></div>
                            <small class="text-muted"><i class="fas fa-envelope me-1"></i><?= clean($s['email'] ?? '—') ?></small>
                        </td>
                        <td>
                            <div class="small"><?= clean($s['contact_person'] ?? '—') ?></div>
                            <small class="text-muted"><?= clean($s['phone'] ?? '—') ?></small>
                        </td>
                        <td class="small"><?= clean($s['city'] ?? '') ?><?= $s['country'] ? ', '.clean($s['country']) : '' ?></td>
                        <td class="small"><?= clean($s['payment_terms'] ?? '—') ?></td>
                        <td class="text-center"><span class="badge bg-info"><?= $s['lead_time_days'] ?> days</span></td>
                        <td class="text-center fw-bold"><?= $s['product_count'] ?></td>
                        <td class="text-center">
                            <?php if ($s['rating'] > 0): ?>
                                <i class="fas fa-star text-warning"></i> <?= number_format($s['rating'], 1) ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-xs btn-outline-primary edit-supplier-btn"
                                    data-supplier="<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>"
                                    data-bs-toggle="modal" data-bs-target="#editSupplierModal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="adminAction('delete_supplier',<?= $s['id'] ?>,'Deactivate this supplier?')"
                                    class="btn btn-xs btn-outline-danger ms-1"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create_supplier">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="fas fa-truck me-2"></i>Add Supplier</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Supplier Name *</label><input type="text" name="supplier_name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Alt. Phone</label><input type="tel" name="alt_phone" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Country</label><input type="text" name="country" class="form-control" value="Ghana"></div>
                        <div class="col-md-6"><label class="form-label">City</label><input type="text" name="city" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Payment Terms</label>
                            <select name="payment_terms" class="form-select">
                                <option>Net 30</option><option>Net 15</option><option>Net 60</option><option>Prepaid</option><option>COD</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Lead Time (days)</label><input type="number" name="lead_time_days" class="form-control" value="7" min="1"></div>
                        <div class="col-md-6"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-save me-1"></i>Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal (same layout, prefilled via JS) -->
<div class="modal fade" id="editSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="editSupplierForm">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update_supplier">
                <input type="hidden" name="supplier_id" id="editSupplierId">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Supplier</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Supplier Name *</label><input type="text" name="supplier_name" id="eSupName" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Contact Person</label><input type="text" name="contact_person" id="eContact" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" id="eEmail" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" name="phone" id="ePhone" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">City</label><input type="text" name="city" id="eCity" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Country</label><input type="text" name="country" id="eCountry" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Payment Terms</label><input type="text" name="payment_terms" id="eTerms" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Lead Time (days)</label><input type="number" name="lead_time_days" id="eLeadTime" class="form-control" min="1"></div>
                        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" id="eNotes" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-save me-1"></i>Update Supplier</button>
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
<script>
const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';
document.querySelectorAll('.edit-supplier-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const s = JSON.parse(btn.dataset.supplier);
        document.getElementById('editSupplierId').value = s.id;
        document.getElementById('eSupName').value    = s.supplier_name || '';
        document.getElementById('eContact').value    = s.contact_person || '';
        document.getElementById('eEmail').value      = s.email || '';
        document.getElementById('ePhone').value      = s.phone || '';
        document.getElementById('eCity').value       = s.city || '';
        document.getElementById('eCountry').value    = s.country || '';
        document.getElementById('eTerms').value      = s.payment_terms || '';
        document.getElementById('eLeadTime').value   = s.lead_time_days || 7;
        document.getElementById('eNotes').value      = s.notes || '';
    });
});
</script>
</body></html>
