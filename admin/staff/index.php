<?php
// admin/staff/index.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireAdmin();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = post('action');
    if ($action === 'create_staff') {
        // Create user first
        $userModel = new User();
        $result    = $userModel->register([
            'full_name' => post('full_name'),
            'email'     => post('email'),
            'username'  => post('username'),
            'password'  => post('password'),
            'phone'     => post('phone'),
        ]);
        if ($result['success']) {
            // Set correct role
            $roleId = $db->fetchColumn("SELECT id FROM roles WHERE slug = ?", [post('role_slug', 'sales_officer')]);
            $db->update('users', ['role_id' => $roleId, 'email_verified' => 1], 'id = ?', [$result['user_id']]);
            // Create staff record
            $empId = 'EMP-' . strtoupper(substr(md5(time()), 0, 6));
            $db->insert('staff', [
                'user_id'     => $result['user_id'],
                'department'  => post('department'),
                'position'    => post('position'),
                'employee_id' => $empId,
                'hire_date'   => post('hire_date') ?: date('Y-m-d'),
                'salary'      => post('salary') ?: null,
                'status'      => 'active',
            ]);
            setFlash('success', "Staff member created (ID: $empId).");
        } else {
            setFlash('danger', $result['message']);
        }
        redirect(BASE_URL . 'admin/staff/');
    }
}

$pageTitle  = 'Staff Management';
$breadcrumb = [['label' => 'Staff', 'active' => true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';

$staff = $db->fetchAll(
    "SELECT u.id, u.full_name, u.email, u.phone, u.status, u.last_login, u.avatar,
            r.role_name, r.slug AS role_slug,
            s.department, s.position, s.employee_id, s.hire_date, s.salary
     FROM users u
     JOIN roles r ON u.role_id = r.id
     LEFT JOIN staff s ON u.id = s.user_id
     WHERE r.slug != 'customer' AND r.slug != 'guest'
     ORDER BY r.id, u.full_name"
);
$roles = $db->fetchAll("SELECT * FROM roles WHERE slug NOT IN ('customer','guest') ORDER BY id");
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-user-tie me-2 text-primary"></i>Staff Management</h4><p>Manage admin, inventory and sales team</p></div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
        <i class="fas fa-user-plus me-1"></i>Add Staff
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 data-table">
                <thead><tr><th>Staff Member</th><th>Role</th><th>Department / Position</th><th>Employee ID</th><th>Hire Date</th><th>Last Login</th><th>Status</th><th class="no-sort text-end">Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($staff as $s): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-15 d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;font-size:.9rem">
                                    <?php if ($s['avatar']): ?>
                                        <img src="<?= productImageUrl($s['avatar']) ?>" class="rounded-circle" width="38" height="38" style="object-fit:cover">
                                    <?php else: ?>
                                        <i class="fas fa-user text-primary"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="fw-semibold small"><?= clean($s['full_name']) ?></div>
                                    <small class="text-muted"><?= clean($s['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php $roleColors = ['admin'=>'danger','inventory_manager'=>'warning','sales_officer'=>'info']; ?>
                            <span class="badge bg-<?= $roleColors[$s['role_slug']] ?? 'secondary' ?>"><?= clean($s['role_name']) ?></span>
                        </td>
                        <td>
                            <div class="small"><?= clean($s['department'] ?? '—') ?></div>
                            <small class="text-muted"><?= clean($s['position'] ?? '—') ?></small>
                        </td>
                        <td><code class="small"><?= clean($s['employee_id'] ?? '—') ?></code></td>
                        <td><small><?= $s['hire_date'] ? formatDate($s['hire_date']) : '—' ?></small></td>
                        <td><small><?= $s['last_login'] ? timeAgo($s['last_login']) : 'Never' ?></small></td>
                        <td><?= $s['status']==='active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">'.ucfirst($s['status']).'</span>' ?></td>
                        <td class="text-end">
                            <button onclick="adminAction('toggle_user_status',<?= $s['id'] ?>,'<?= $s['status']==='active'?'Suspend':'Activate' ?> this staff member?')"
                                    class="btn btn-xs btn-outline-<?= $s['status']==='active'?'warning':'success' ?>">
                                <i class="fas fa-<?= $s['status']==='active'?'ban':'check' ?>"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create_staff">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="fas fa-user-tie me-2"></i>Add Staff Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="full_name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" value="admin123" required></div>
                        <div class="col-md-6">
                            <label class="form-label">Role *</label>
                            <select name="role_slug" class="form-select" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['slug'] ?>"><?= clean($r['role_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Department</label><input type="text" name="department" class="form-control" placeholder="e.g. Sales, IT, Operations"></div>
                        <div class="col-md-6"><label class="form-label">Position</label><input type="text" name="position" class="form-control" placeholder="e.g. Sales Manager"></div>
                        <div class="col-md-6"><label class="form-label">Hire Date</label><input type="date" name="hire_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Salary (<?= DEFAULT_CURRENCY_SYMBOL ?>)</label><input type="number" name="salary" class="form-control" step="0.01" min="0"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-user-plus me-1"></i>Create Staff Account</button>
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
