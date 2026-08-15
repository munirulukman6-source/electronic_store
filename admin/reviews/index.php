<?php
// admin/reviews/index.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireSales();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrf()) {
    $action = post('action'); $id = (int)post('id');
    if ($action==='approve')  { $db->update('reviews',['status'=>'approved'],'id=?',[$id]); setFlash('success','Review approved.'); }
    if ($action==='reject')   { $db->update('reviews',['status'=>'rejected'],'id=?',[$id]); setFlash('success','Review rejected.'); }
    if ($action==='reply')    { $db->update('reviews',['admin_reply'=>post('reply'),'status'=>'approved'],'id=?',[$id]); setFlash('success','Reply saved.'); }
    redirect(BASE_URL.'admin/reviews/');
}

$pageTitle  = 'Product Reviews';
$breadcrumb = [['label'=>'Reviews','active'=>true]];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';

$status  = get('status','pending');
$page    = max(1,(int)get('page',1));
$params  = [];
$where   = '1=1';
if ($status) { $where.=' AND r.status=?'; $params[]=$status; }
$result  = $db->paginate(
    "SELECT r.*,p.product_name,p.slug AS product_slug,
            CONCAT(c.first_name,' ',c.last_name) AS customer_name, u.email,
            o.order_number
     FROM reviews r JOIN products p ON r.product_id=p.id
     JOIN customers c ON r.customer_id=c.id JOIN users u ON c.user_id=u.id
     LEFT JOIN orders o ON r.order_id=o.id
     WHERE $where ORDER BY r.created_at DESC", $params, $page, 20
);
$reviews = $result['data'];
$counts  = $db->fetchOne("SELECT
    SUM(status='pending') AS pending,
    SUM(status='approved') AS approved,
    SUM(status='rejected') AS rejected
    FROM reviews");
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h4><i class="fas fa-star me-2 text-warning"></i>Product Reviews</h4><p>Moderate customer reviews</p></div>
</div>

<!-- Tab filter -->
<div class="d-flex gap-2 mb-3 flex-wrap">
    <?php foreach (['pending'=>'warning','approved'=>'success','rejected'=>'danger',''=>'secondary'] as $s=>$c): ?>
    <a href="?status=<?= $s ?>" class="btn btn-sm btn-<?= $status===$s?'':'outline-' ?><?= $c ?>">
        <?= ucfirst($s?:'All') ?>
        <?php if ($s && isset($counts[$s?:''])): ?><span class="badge bg-white text-<?= $c ?> ms-1"><?= $counts[$s] ?></span><?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Product</th><th>Customer</th><th>Rating</th><th>Review</th><th>Verified</th><th>Date</th><th>Status</th><th class="no-sort text-end">Actions</th></tr></thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-star fa-3x opacity-25 mb-3 d-block"></i>No reviews found.</td></tr>
                    <?php else: foreach ($reviews as $rv): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>product/<?= clean($rv['product_slug']) ?>" target="_blank" class="small fw-semibold text-decoration-none d-block" style="max-width:160px"><?= truncate(clean($rv['product_name']),30) ?></a>
                        </td>
                        <td>
                            <div class="small fw-semibold"><?= clean($rv['customer_name']) ?></div>
                            <small class="text-muted"><?= clean($rv['email']) ?></small>
                        </td>
                        <td>
                            <?= starRating($rv['rating']) ?>
                            <div class="small text-muted"><?= $rv['rating'] ?>/5</div>
                        </td>
                        <td style="max-width:220px">
                            <?php if ($rv['title']): ?><div class="fw-semibold small"><?= clean($rv['title']) ?></div><?php endif; ?>
                            <div class="small text-muted text-truncate"><?= clean($rv['review_text']) ?></div>
                            <?php if ($rv['admin_reply']): ?>
                            <div class="mt-1 small text-primary"><i class="fas fa-reply me-1"></i><?= truncate(clean($rv['admin_reply']),40) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?= $rv['is_verified'] ? '<span class="badge bg-success"><i class="fas fa-check"></i></span>' : '<span class="badge bg-light text-muted">No</span>' ?>
                        </td>
                        <td><small><?= timeAgo($rv['created_at']) ?></small></td>
                        <td>
                            <?php $sc=['approved'=>'success','pending'=>'warning','rejected'=>'danger']; ?>
                            <span class="badge bg-<?= $sc[$rv['status']]?>'secondary' ?>"><?= ucfirst($rv['status']) ?></span>
                        </td>
                        <td class="text-end">
                            <?php if ($rv['status']==='pending'): ?>
                            <form method="POST" class="d-inline">
                                <?= csrfField() ?><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?= $rv['id'] ?>">
                                <button class="btn btn-xs btn-success" title="Approve"><i class="fas fa-check"></i></button>
                            </form>
                            <form method="POST" class="d-inline ms-1">
                                <?= csrfField() ?><input type="hidden" name="action" value="reject"><input type="hidden" name="id" value="<?= $rv['id'] ?>">
                                <button class="btn btn-xs btn-danger" title="Reject"><i class="fas fa-times"></i></button>
                            </form>
                            <?php endif; ?>
                            <button class="btn btn-xs btn-outline-primary ms-1 reply-btn"
                                    data-id="<?= $rv['id'] ?>" data-product="<?= clean($rv['product_name']) ?>"
                                    data-bs-toggle="modal" data-bs-target="#replyModal" title="Reply">
                                <i class="fas fa-reply"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($result['last_page']>1): ?>
    <div class="card-footer bg-transparent border-0 py-2"><?= paginationLinks($result, BASE_URL.'admin/reviews/?status='.$status) ?></div>
    <?php endif; ?>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <?= csrfField() ?><input type="hidden" name="action" value="reply"><input type="hidden" name="id" id="replyId">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold">Reply to Review</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p class="small text-muted mb-1">Replying to review on:</p>
                    <p class="fw-semibold mb-3" id="replyProduct"></p>
                    <label class="form-label">Your Reply *</label>
                    <textarea name="reply" class="form-control" rows="4" placeholder="Thank you for your review…" required></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-paper-plane me-1"></i>Post Reply</button>
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
<script>
const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';
document.querySelectorAll('.reply-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
        document.getElementById('replyId').value=btn.dataset.id;
        document.getElementById('replyProduct').textContent=btn.dataset.product;
    });
});
</script>
</body></html>
