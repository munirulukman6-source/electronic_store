<?php
// admin/notifications/index.php
$pageTitle  = 'Notifications';
$breadcrumb = [['label'=>'Notifications','active'=>true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
requireStaff();

if ($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrf()) {
    $action = post('action');
    if ($action==='mark_all_read') {
        $db->update('notifications',['is_read'=>1,'read_at'=>date('Y-m-d H:i:s')],'user_id=? AND is_read=0',[$_SESSION['user_id']]);
        setFlash('success','All notifications marked as read.');
        redirect(BASE_URL.'admin/notifications/');
    }
    if ($action==='delete_all') {
        $db->delete('notifications','user_id=? AND is_read=1',[$_SESSION['user_id']]);
        setFlash('success','Read notifications cleared.');
        redirect(BASE_URL.'admin/notifications/');
    }
}

$page   = max(1,(int)get('page',1));
$result = $db->paginate(
    "SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC",
    [$_SESSION['user_id']], $page, 25
);
$notifs    = $result['data'];
$unreadCnt = $db->count('notifications','user_id=? AND is_read=0',[$_SESSION['user_id']]);
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4><i class="fas fa-bell me-2 text-warning"></i>Notifications
            <?php if ($unreadCnt>0): ?><span class="badge bg-danger ms-2"><?= $unreadCnt ?></span><?php endif; ?>
        </h4>
        <p><?= number_format($result['total']) ?> total notifications</p>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" class="d-inline">
            <?= csrfField() ?><input type="hidden" name="action" value="mark_all_read">
            <button class="btn btn-outline-primary btn-sm"><i class="fas fa-check-double me-1"></i>Mark All Read</button>
        </form>
        <form method="POST" class="d-inline">
            <?= csrfField() ?><input type="hidden" name="action" value="delete_all">
            <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete all read notifications?')">
                <i class="fas fa-trash me-1"></i>Clear Read
            </button>
        </form>
    </div>
</div>

<?php if (empty($notifs)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="fas fa-bell-slash fa-4x text-muted opacity-25 mb-3"></i>
        <h5 class="text-muted">No notifications</h5>
        <p class="text-muted">You're all caught up!</p>
    </div>
</div>
<?php else: ?>
<div class="d-flex flex-column gap-2">
    <?php foreach ($notifs as $n): ?>
    <div class="card border-0 shadow-sm <?= !$n['is_read'] ? 'border-start border-primary border-3' : '' ?>"
         onclick="<?= $n['link'] ? "window.location.href='".clean($n['link'])."'" : '' ?>"
         style="<?= $n['link'] ? 'cursor:pointer' : '' ?>">
        <div class="card-body d-flex align-items-start gap-3 py-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:44px;height:44px;background:<?= clean($n['color']??'#dee2e6') ?>22;color:<?= clean($n['color']??'#6c757d') ?>">
                <i class="<?= clean($n['icon']??'fas fa-bell') ?>"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div class="fw-semibold <?= !$n['is_read']?'text-body':'text-muted' ?>"><?= clean($n['title']) ?></div>
                    <div class="flex-shrink-0">
                        <small class="text-muted"><?= timeAgo($n['created_at']) ?></small>
                        <?php if (!$n['is_read']): ?><span class="badge bg-primary ms-1" style="font-size:.6rem">New</span><?php endif; ?>
                    </div>
                </div>
                <?php if ($n['message']): ?><div class="small text-muted mt-1"><?= clean($n['message']) ?></div><?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php if ($result['last_page']>1): ?>
<div class="mt-3"><?= paginationLinks($result, BASE_URL.'admin/notifications/') ?></div>
<?php endif; ?>
<?php endif; ?>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';</script>
</body></html>
