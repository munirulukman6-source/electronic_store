<?php
// admin/newsletter/index.php
$pageTitle  = 'Newsletter';
$breadcrumb = [['label'=>'Newsletter','active'=>true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
requireStaff();

if ($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrf()) {
    $action = post('action');
    if ($action==='queue_email') {
        // Queue newsletter to all active subscribers
        $subs = $db->fetchAll("SELECT email,name FROM newsletter_subscribers WHERE status='active'");
        $cnt  = 0;
        foreach ($subs as $sub) {
            $db->insert('email_queue',[
                'to_email'  => $sub['email'],
                'to_name'   => $sub['name'] ?? '',
                'subject'   => post('subject'),
                'body'      => post('body'),
                'priority'  => 2,
                'status'    => 'pending',
            ]);
            $cnt++;
        }
        setFlash('success', "Newsletter queued for $cnt subscribers.");
        redirect(BASE_URL.'admin/newsletter/');
    }
    if ($action==='delete_subscriber') {
        $db->delete('newsletter_subscribers','id=?',[(int)post('id')]);
        setFlash('success','Subscriber removed.');
        redirect(BASE_URL.'admin/newsletter/');
    }
}

$search = get('search','');
$where  = $search ? "WHERE email LIKE '%$search%' OR name LIKE '%$search%'" : '';
$page   = max(1,(int)get('page',1));
$result = $db->paginate("SELECT * FROM newsletter_subscribers $where ORDER BY subscribed_at DESC", [], $page, 25);
$subs   = $result['data'];
$stats  = $db->fetchOne("SELECT SUM(status='active') AS active, SUM(status='unsubscribed') AS unsub, COUNT(*) AS total FROM newsletter_subscribers");
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-envelope-open-text me-2 text-primary"></i>Newsletter</h4><p>Manage subscribers and send campaigns</p></div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#composeModal">
        <i class="fas fa-paper-plane me-1"></i>Send Newsletter
    </button>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="kpi-card kpi-success"><div class="kpi-icon"><i class="fas fa-users"></i></div><div class="kpi-info"><div class="kpi-value"><?= number_format($stats['active']??0) ?></div><div class="kpi-label">Active Subscribers</div></div></div></div>
    <div class="col-md-4"><div class="kpi-card kpi-warning"><div class="kpi-icon"><i class="fas fa-user-slash"></i></div><div class="kpi-info"><div class="kpi-value"><?= number_format($stats['unsub']??0) ?></div><div class="kpi-label">Unsubscribed</div></div></div></div>
    <div class="col-md-4"><div class="kpi-card kpi-primary"><div class="kpi-icon"><i class="fas fa-envelope"></i></div><div class="kpi-info"><div class="kpi-value"><?= number_format($stats['total']??0) ?></div><div class="kpi-label">Total Subscribers</div></div></div></div>
</div>

<!-- Search + table -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent border-0">
        <form method="GET" class="row g-2">
            <div class="col-md-6"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search email or name…" value="<?= clean($search) ?>"></div>
            <div class="col-auto"><button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search me-1"></i>Search</button></div>
            <div class="col-auto"><a href="<?= BASE_URL ?>admin/newsletter/" class="btn btn-outline-secondary btn-sm">Clear</a></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Email</th><th>Name</th><th>Status</th><th>Subscribed</th><th class="no-sort text-end">Actions</th></tr></thead>
                <tbody>
                    <?php if (empty($subs)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No subscribers found.</td></tr>
                    <?php else: foreach ($subs as $s): ?>
                    <tr>
                        <td class="fw-semibold small"><?= clean($s['email']) ?></td>
                        <td class="small"><?= clean($s['name']??'—') ?></td>
                        <td><?= $s['status']==='active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">'.ucfirst($s['status']).'</span>' ?></td>
                        <td><small><?= formatDate($s['subscribed_at']) ?></small></td>
                        <td class="text-end">
                            <form method="POST" class="d-inline" onsubmit="return confirm('Remove subscriber?')">
                                <?= csrfField() ?><input type="hidden" name="action" value="delete_subscriber"><input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($result['last_page']>1): ?>
    <div class="card-footer bg-transparent border-0 py-2"><?= paginationLinks($result, BASE_URL.'admin/newsletter/?search='.urlencode($search)) ?></div>
    <?php endif; ?>
</div>

<!-- Compose Modal -->
<div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <?= csrfField() ?><input type="hidden" name="action" value="queue_email">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2 text-primary"></i>Compose Newsletter</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i>This will send to <strong><?= number_format($stats['active']??0) ?> active subscribers</strong>.</div>
                    <div class="mb-3"><label class="form-label">Subject *</label><input type="text" name="subject" class="form-control" placeholder="e.g. 🔥 Flash Weekend Deals — Up to 30% Off!" required></div>
                    <div class="mb-0"><label class="form-label">Message Body *</label>
                        <textarea name="body" class="form-control" rows="10" placeholder="Write your newsletter content here. HTML is supported." required></textarea>
                        <div class="form-text">You can use basic HTML tags for formatting.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-paper-plane me-2"></i>Queue &amp; Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';</script>
</body></html>
