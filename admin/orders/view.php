<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireStaff();

$orderModel = new Order();
$id         = (int)get('id');
$order      = $orderModel->getById($id);
if (!$order) {
    setFlash('danger', 'Order not found.');
    redirect(BASE_URL . 'admin/orders/');
}

// Handle status update POST before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = post('action');
    if ($action === 'update_status') {
        $result = $orderModel->updateStatus($id, post('status'), post('note'));
        setFlash($result['success'] ? 'success' : 'danger', $result['message']);
        redirect(BASE_URL . 'admin/orders/view.php?id=' . $id);
    }
    if ($action === 'record_payment') {
        $orderModel->recordPayment($id, post('payment_method'), (float)post('amount'), post('txn_id'));
        setFlash('success', 'Payment recorded.');
        redirect(BASE_URL . 'admin/orders/view.php?id=' . $id);
    }
}

$pageTitle  = 'Order Detail';
$breadcrumb = [
    ['label' => 'Orders', 'url' => BASE_URL . 'admin/orders/'],
    ['label' => '#' . $order['order_number'], 'active' => true]
];
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4><i class="fas fa-receipt me-2"></i>Order #<?= clean($order['order_number']) ?></h4>
        <p class="mb-0">Placed <?= formatDateTime($order['created_at']) ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?= orderStatusBadge($order['status']) ?>
        <?= paymentStatusBadge($order['payment_status']) ?>
        <a href="<?= BASE_URL ?>admin/orders/" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
        <?php if ($order['invoice']): ?>
            <a href="<?= BASE_URL ?>admin/orders/invoice.php?id=<?= $id ?>" class="btn btn-sm btn-outline-info" target="_blank"><i class="fas fa-file-pdf me-1"></i>Invoice</a>
        <?php endif; ?>
    </div>
</div>

<?= displayFlash() ?>

<div class="row g-3">
    <!-- Left column -->
    <div class="col-lg-8">
        <!-- Order items -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header border-0 fw-semibold"><i class="fas fa-box me-2"></i>Order Items</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th>Product</th><th>SKU</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= productImageUrl($item['product_image'] ?? '') ?>" class="prod-thumb" alt="">
                                        <div>
                                            <a href="<?= BASE_URL ?>product/<?= $item['product_slug'] ?>" target="_blank" class="fw-semibold small text-decoration-none"><?= clean($item['product_name']) ?></a>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="small"><?= clean($item['sku'] ?? '—') ?></code></td>
                                <td class="text-center fw-bold"><?= $item['quantity'] ?></td>
                                <td class="text-end"><?= formatPrice($item['unit_price']) ?></td>
                                <td class="text-end fw-bold"><?= formatPrice($item['total_price']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr><td colspan="4" class="text-end small">Subtotal</td><td class="text-end"><?= formatPrice($order['subtotal']) ?></td></tr>
                            <?php if ($order['discount_amount'] > 0): ?>
                            <tr><td colspan="4" class="text-end small text-danger">Coupon (<?= clean($order['coupon_code'] ?? '') ?>)</td><td class="text-end text-danger">-<?= formatPrice($order['discount_amount']) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($order['points_discount'] > 0): ?>
                            <tr><td colspan="4" class="text-end small text-warning">Loyalty Points</td><td class="text-end text-warning">-<?= formatPrice($order['points_discount']) ?></td></tr>
                            <?php endif; ?>
                            <tr><td colspan="4" class="text-end small">Tax (VAT)</td><td class="text-end"><?= formatPrice($order['tax_amount']) ?></td></tr>
                            <tr><td colspan="4" class="text-end small">Shipping</td><td class="text-end"><?= formatPrice($order['shipping_cost']) ?></td></tr>
                            <tr><td colspan="4" class="text-end fw-bold">TOTAL</td><td class="text-end fw-bold fs-6"><?= formatPrice($order['total']) ?></td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Update status -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header border-0 fw-semibold"><i class="fas fa-edit me-2"></i>Update Status</div>
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_status">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">New Status</label>
                            <select name="status" class="form-select">
                                <?php foreach (['pending','processing','approved','packed','shipped','out_for_delivery','delivered','cancelled','returned'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Note (optional)</label>
                            <input type="text" name="note" class="form-control" placeholder="Tracking update or note…">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tracking timeline -->
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 fw-semibold"><i class="fas fa-history me-2"></i>Order Timeline</div>
            <div class="card-body">
                <div class="admin-timeline">
                    <?php foreach (array_reverse($order['tracking']) as $t): ?>
                    <div class="admin-timeline-item">
                        <div class="fw-semibold small"><?= clean($t['status']) ?></div>
                        <div class="text-muted small"><?= clean($t['description']) ?></div>
                        <div class="timeline-time"><?= formatDateTime($t['created_at']) ?> <?= $t['updated_by'] ? '· ' . clean($t['updated_by']) : '' ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right column -->
    <div class="col-lg-4">
        <!-- Customer info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header border-0 fw-semibold"><i class="fas fa-user me-2"></i>Customer</div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-15 d-flex align-items-center justify-content-center" style="width:44px;height:44px">
                        <i class="fas fa-user text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-semibold"><?= clean($order['customer_name']) ?></div>
                        <small class="text-muted"><?= clean($order['customer_email']) ?></small>
                    </div>
                </div>
                <div class="d-flex flex-column gap-1 small">
                    <div><span class="text-muted me-2">Phone:</span><?= clean($order['customer_phone'] ?? '—') ?></div>
                    <div><span class="text-muted me-2">Tier:</span>
                        <span class="badge" style="background:<?= clean($order['badge_color'] ?? '#6c757d') ?>"><?= clean($order['tier_name'] ?? 'Bronze') ?></span>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>admin/customers/view.php?id=<?= $order['customer_id'] ?>" class="btn btn-outline-primary btn-sm w-100 mt-3">View Customer</a>
            </div>
        </div>

        <!-- Shipping address -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header border-0 fw-semibold"><i class="fas fa-map-marker-alt me-2"></i>Shipping Address</div>
            <div class="card-body small">
                <div class="fw-semibold"><?= clean($order['shipping_name']) ?></div>
                <div><?= clean($order['shipping_phone'] ?? '') ?></div>
                <div class="mt-1"><?= clean($order['shipping_address']) ?></div>
                <div><?= clean($order['shipping_city']) ?>, <?= clean($order['shipping_state'] ?? '') ?></div>
                <div><?= clean($order['shipping_country']) ?> <?= clean($order['shipping_postal'] ?? '') ?></div>
                <?php if ($order['tracking_number']): ?>
                    <div class="mt-2 fw-semibold">Tracking: <code><?= clean($order['tracking_number']) ?></code></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header border-0 fw-semibold"><i class="fas fa-credit-card me-2"></i>Payment</div>
            <div class="card-body small">
                <?php if ($order['payment']): $pay = $order['payment']; ?>
                    <div class="order-info-label">Method</div>
                    <div class="order-info-value mb-2"><?= ucwords(str_replace('_', ' ', $pay['payment_method'])) ?></div>
                    <div class="order-info-label">Transaction ID</div>
                    <div class="order-info-value mb-2"><code><?= clean($pay['transaction_id']) ?></code></div>
                    <div class="order-info-label">Amount</div>
                    <div class="order-info-value mb-2 fw-bold text-success"><?= formatPrice($pay['amount']) ?></div>
                    <div class="order-info-label">Date</div>
                    <div class="order-info-value"><?= formatDateTime($pay['payment_date'] ?? '') ?></div>
                <?php elseif ($order['payment_status'] === 'pending'): ?>
                    <p class="text-muted mb-3">No payment recorded yet.</p>
                    <button class="btn btn-success btn-sm w-100" data-bs-toggle="collapse" data-bs-target="#recordPaymentForm">
                        <i class="fas fa-plus me-1"></i>Record Payment
                    </button>
                    <div class="collapse mt-2" id="recordPaymentForm">
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="record_payment">
                            <div class="mb-2">
                                <select name="payment_method" class="form-select form-select-sm">
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="cash_on_delivery">Cash on Delivery</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="mb-2"><input type="number" name="amount" class="form-control form-control-sm" placeholder="Amount" value="<?= $order['total'] ?>" step="0.01" required></div>
                            <div class="mb-2"><input type="text" name="txn_id" class="form-control form-control-sm" placeholder="Transaction ID"></div>
                            <button type="submit" class="btn btn-success btn-sm w-100">Confirm Payment</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Admin notes -->
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 fw-semibold"><i class="fas fa-sticky-note me-2"></i>Notes</div>
            <div class="card-body small">
                <?php if ($order['notes']): ?>
                    <div class="mb-2"><strong>Customer note:</strong><br><?= nl2br(clean($order['notes'])) ?></div>
                <?php endif; ?>
                <div class="text-muted"><?= $order['admin_notes'] ? nl2br(clean($order['admin_notes'])) : 'No admin notes.' ?></div>
            </div>
        </div>
    </div>
</div>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';</script>
</body></html>