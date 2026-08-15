<?php
// admin/audit/index.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireAdmin();

$pageTitle  = 'Audit Logs';
$breadcrumb = [['label'=>'Audit Logs','active'=>true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';

$filters = [
    'module'    => get('module',''),
    'user_id'   => get('user_id',''),
    'date_from' => get('date_from', date('Y-m-01')),
    'date_to'   => get('date_to',   date('Y-m-d')),
    'search'    => get('search',''),
];
$page   = max(1,(int)get('page',1));
$where  = ['1=1'];
$params = [];
if ($filters['module'])    { $where[] = 'al.module = ?';                $params[] = $filters['module']; }
if ($filters['user_id'])   { $where[] = 'al.user_id = ?';              $params[] = $filters['user_id']; }
if ($filters['date_from']) { $where[] = 'DATE(al.created_at) >= ?';    $params[] = $filters['date_from']; }
if ($filters['date_to'])   { $where[] = 'DATE(al.created_at) <= ?';    $params[] = $filters['date_to']; }
if ($filters['search'])    { $where[] = 'al.action LIKE ?';            $params[] = '%'.$filters['search'].'%'; }

$sql    = "SELECT al.*, u.full_name, u.email FROM audit_logs al
           LEFT JOIN users u ON al.user_id = u.id
           WHERE ".implode(' AND ',$where)." ORDER BY al.created_at DESC";
$result = $db->paginate($sql, $params, $page, 50);
$logs   = $result['data'];
$modules = $db->fetchAll("SELECT DISTINCT module FROM audit_logs WHERE module IS NOT NULL ORDER BY module");
$users   = $db->fetchAll("SELECT DISTINCT u.id, u.full_name FROM audit_logs al JOIN users u ON al.user_id=u.id ORDER BY u.full_name");

// Action color map
$actionColors = [
    'login'             => 'success',
    'logout'            => 'secondary',
    'register'          => 'info',
    'create_product'    => 'primary',
    'update_product'    => 'warning',
    'delete_product'    => 'danger',
    'stock_in'          => 'success',
    'update_order_status' => 'warning',
    'cancel_order'      => 'danger',
    'approve_review'    => 'success',
    'reject_review'     => 'danger',
    'save_settings'     => 'info',
    'password_reset'    => 'warning',
    'change_password'   => 'warning',
];
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-history me-2 text-secondary"></i>Audit Logs</h4><p>Complete system activity trail</p></div>
    <a href="?<?= http_build_query(array_merge($filters,['export'=>'csv'])) ?>" class="btn btn-outline-success btn-sm">
        <i class="fas fa-file-csv me-1"></i>Export CSV
    </a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search action…" value="<?= clean($filters['search']) ?>"></div>
            <div class="col-md-2">
                <select name="module" class="form-select form-select-sm">
                    <option value="">All Modules</option>
                    <?php foreach ($modules as $m): ?>
                    <option value="<?= clean($m['module']) ?>" <?= $filters['module']===$m['module']?'selected':'' ?>><?= ucwords(clean($m['module'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $filters['user_id']==$u['id']?'selected':'' ?>><?= clean($u['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="<?= $filters['date_from'] ?>"></div>
            <div class="col-md-2"><input type="date" name="date_to"   class="form-control form-control-sm" value="<?= $filters['date_to'] ?>"></div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-primary btn-sm flex-fill" type="submit"><i class="fas fa-filter"></i></button>
                <a href="<?= BASE_URL ?>admin/audit/" class="btn btn-outline-secondary btn-sm px-2"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Stats strip -->
<div class="row g-2 mb-3">
    <?php
    $topActions = $db->fetchAll("SELECT action, COUNT(*) AS cnt FROM audit_logs WHERE DATE(created_at) = CURDATE() GROUP BY action ORDER BY cnt DESC LIMIT 4");
    foreach ($topActions as $ta):
        $col = $actionColors[$ta['action']] ?? 'secondary';
    ?>
    <div class="col-6 col-md-3">
        <div class="d-flex align-items-center gap-2 p-2 bg-<?= $col ?> bg-opacity-10 rounded-3 border border-<?= $col ?> border-opacity-25">
            <span class="badge bg-<?= $col ?>"><?= $ta['cnt'] ?></span>
            <span class="small fw-semibold"><?= ucwords(str_replace('_',' ',$ta['action'])) ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Logs table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between">
        <span class="small text-muted">Showing <?= $result['from'] ?>–<?= $result['to'] ?> of <?= number_format($result['total']) ?> entries</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.82rem">
                <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Module</th><th>Record ID</th><th>IP Address</th><th>Changes</th></tr></thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-history fa-3x opacity-25 mb-3 d-block"></i>No audit logs found.</td></tr>
                    <?php else: foreach ($logs as $log):
                        $col = $actionColors[$log['action']] ?? 'secondary';
                    ?>
                    <tr>
                        <td class="text-nowrap">
                            <div><?= formatDate($log['created_at']) ?></div>
                            <small class="text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= clean($log['full_name'] ?? 'System') ?></div>
                            <small class="text-muted"><?= clean($log['email'] ?? '') ?></small>
                        </td>
                        <td><span class="badge bg-<?= $col ?>"><?= ucwords(str_replace('_',' ',clean($log['action']))) ?></span></td>
                        <td><span class="badge bg-light text-dark"><?= clean($log['module'] ?? '—') ?></span></td>
                        <td class="text-center text-muted"><?= $log['record_id'] ?? '—' ?></td>
                        <td><code class="small"><?= clean($log['ip_address'] ?? '—') ?></code></td>
                        <td>
                            <?php if ($log['old_values'] || $log['new_values']): ?>
                            <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#diff<?= $log['id'] ?>">
                                <i class="fas fa-code-branch"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($log['old_values'] || $log['new_values']): ?>
                    <tr class="collapse" id="diff<?= $log['id'] ?>">
                        <td colspan="7" class="bg-light py-2 px-3">
                            <div class="row g-2">
                                <?php if ($log['old_values']): ?>
                                <div class="col-md-6">
                                    <div class="small fw-semibold text-danger mb-1">Before:</div>
                                    <pre class="small mb-0 text-wrap" style="max-height:120px;overflow-y:auto"><?= htmlspecialchars(json_encode(json_decode($log['old_values']),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
                                </div>
                                <?php endif; ?>
                                <?php if ($log['new_values']): ?>
                                <div class="col-md-6">
                                    <div class="small fw-semibold text-success mb-1">After:</div>
                                    <pre class="small mb-0 text-wrap" style="max-height:120px;overflow-y:auto"><?= htmlspecialchars(json_encode(json_decode($log['new_values']),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($result['last_page']>1): ?>
    <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Page <?= $result['current_page'] ?> of <?= $result['last_page'] ?></small>
        <?= paginationLinks($result, BASE_URL.'admin/audit/?'.http_build_query($filters)) ?>
    </div>
    <?php endif; ?>
</div>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';</script>
</body></html>
