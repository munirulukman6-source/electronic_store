<?php
// admin/returns/index.php
$pageTitle  = 'Return Requests';
$breadcrumb = [['label' => 'Returns', 'active' => true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
requireStaff();

$status = get('status', '');
$page   = max(1, (int)get('page', 1));
$where  = ['1=1'];
$params = [];
if ($status) { $where[] = 'r.status = ?'; $params[] = $status; }

$sql    = "SELECT r.*, o.order_number, CONCAT(c.first_name,' ',c.last_name) AS customer_name, u.email
           FROM returns r JOIN orders o ON r.order_id=o.id
           JOIN customers c ON r.customer_id=c.id JOIN users u ON c.user_id=u.id
           WHERE ".implode(' AND ',$where)." ORDER BY r.created_at DESC";
$result = $db->paginate($sql, $params, $page, 20);
$returns= $result['data'];
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-undo-alt me-2 text-info"></i>Return Requests</h4><p>Manage product returns and refunds</p></div>
</div>

<div class="d-flex gap-2 mb-3 flex-wrap">
    <?php foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'completed' => 'Completed'] as $s => $l): ?>
    <a href="?status=<?= $s ?>" class="btn btn-sm <?= $status===$s ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $l ?></a>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Return #</th><th>Customer</th><th>Order</th><th>Reason</th><th>Refund Type</th><th>Date</th><th>Status</th><th class="no-sort text-end">Actions</th></tr></thead>
                <tbody>
                    <?php if (empty($returns)): ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x opacity-25 mb-3 d-block"></i>No return requests found.</td></tr>
                    <?php else: foreach ($returns as $ret):
                        $statusColors = ['pending'=>'warning','approved'=>'success','rejected'=>'danger','processing'=>'info','completed'=>'secondary'];
                    ?>
                    <tr>
                        <td><span class="fw-semibold"><?= clean($ret['return_number']) ?></span></td>
                        <td>
                            <div class="small fw-semibold"><?= clean($ret['customer_name']) ?></div>
                            <small class="text-muted"><?= clean($ret['email']) ?></small>
                        </td>
                        <td><a href="<?= BASE_URL ?>admin/orders/view.php?id=<?= $ret['order_id'] ?>" class="text-decoration-none"><?= clean($ret['order_number']) ?></a></td>
                        <td><span class="badge bg-light text-dark"><?= ucwords(str_replace('_',' ',$ret['reason'])) ?></span></td>
                        <td><?= ucwords(str_replace('_',' ',$ret['refund_type'])) ?></td>
                        <td><small><?= formatDate($ret['created_at']) ?></small></td>
                        <td><span class="badge bg-<?= $statusColors[$ret['status']] ?? 'secondary' ?>"><?= ucfirst($ret['status']) ?></span></td>
                        <td class="text-end">
                            <?php if ($ret['status'] === 'pending'): ?>
                            <button onclick="adminAction('approve_return',<?= $ret['id'] ?>,'Approve this return request?')" class="btn btn-xs btn-success" title="Approve"><i class="fas fa-check"></i></button>
                            <button onclick="adminAction('reject_return',<?= $ret['id'] ?>,'Reject this return request?')" class="btn btn-xs btn-danger ms-1" title="Reject"><i class="fas fa-times"></i></button>
                            <?php else: ?>
                            <a href="<?= BASE_URL ?>admin/returns/view.php?id=<?= $ret['id'] ?>" class="btn btn-xs btn-light"><i class="fas fa-eye"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($result['last_page'] > 1): ?>
    <div class="card-footer bg-transparent border-0 py-2"><?= paginationLinks($result, BASE_URL.'admin/returns/?status='.$status) ?></div>
    <?php endif; ?>
</div>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';</script>
</body></html>
