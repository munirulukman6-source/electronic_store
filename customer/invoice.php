<?php
// customer/invoice.php — Printable invoice for customer orders
$pageTitle = 'Invoice';
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'classes/Order.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireLogin();

$orderId    = (int)get('order', 0);
$customerId = isCustomer() ? ($_SESSION['customer_id'] ?? null) : null;
$orderModel = new Order();
$order      = $orderModel->getById($orderId, $customerId);

if (!$order || !$order['invoice']) {
    setFlash('danger', 'Invoice not found.');
    redirect(BASE_URL . 'customer/orders.php');
}

$inv       = $order['invoice'];
$siteName  = getSetting('site_name', APP_NAME);
$siteEmail = getSetting('site_email', '');
$sitePhone = getSetting('site_phone', '');
$siteAddr  = getSetting('site_address', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Invoice <?= clean($inv['invoice_number']) ?> — <?= $siteName ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        @media print {
            .no-print { display:none!important; }
            body { font-size:12px; }
            .invoice-box { box-shadow:none!important; border:none!important; }
        }
        body { background:#f5f5f5; font-family:'Segoe UI',sans-serif; }
        .invoice-box { background:#fff; max-width:800px; margin:30px auto; padding:40px; box-shadow:0 4px 20px rgba(0,0,0,.1); border-radius:12px; }
        .invoice-header { border-bottom:3px solid #0d6efd; padding-bottom:20px; margin-bottom:24px; }
        .brand-name { font-size:1.6rem; font-weight:900; color:#0d6efd; }
        .invoice-title { font-size:2rem; font-weight:800; color:#212529; }
        .info-label { font-size:.75rem; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; font-weight:700; }
        .table thead th { background:#f8f9fa; font-size:.78rem; text-transform:uppercase; letter-spacing:.4px; }
        .total-row td { font-size:1.1rem; font-weight:800; }
        .status-badge { padding:6px 14px; border-radius:20px; font-size:.75rem; font-weight:700; text-transform:uppercase; }
        .footer-note { border-top:1px solid #dee2e6; padding-top:16px; margin-top:24px; font-size:.8rem; color:#6c757d; }
        .watermark { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%) rotate(-30deg); font-size:5rem; font-weight:900; opacity:.04; color:#0d6efd; pointer-events:none; white-space:nowrap; }
    </style>
</head>
<body>

<!-- Print / Download buttons -->
<div class="text-center py-3 no-print">
    <button onclick="window.print()" class="btn btn-primary me-2">
        <i class="fas fa-print me-2"></i>Print Invoice
    </button>
    <a href="<?= BASE_URL ?>customer/orders.php" class="btn btn-outline-secondary me-2">
        <i class="fas fa-arrow-left me-2"></i>Back to Orders
    </a>
    <a href="<?= BASE_URL ?>customer/order_detail.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary">
        <i class="fas fa-eye me-2"></i>View Order
    </a>
</div>

<div class="invoice-box position-relative">
    <div class="watermark"><?= strtoupper($siteName) ?></div>

    <!-- Header -->
    <div class="invoice-header d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div class="brand-name mb-1">⚡ <?= $siteName ?></div>
            <div class="small text-muted"><?= clean($siteAddr) ?></div>
            <div class="small text-muted"><?= clean($sitePhone) ?> · <?= clean($siteEmail) ?></div>
        </div>
        <div class="text-end">
            <div class="invoice-title">INVOICE</div>
            <div class="text-muted small"># <?= clean($inv['invoice_number']) ?></div>
            <div class="mt-2">
                <?php
                $statusColor = $inv['status']==='paid' ? 'success' : ($inv['status']==='overdue' ? 'danger' : 'warning');
                ?>
                <span class="status-badge bg-<?= $statusColor ?> text-white"><?= strtoupper($inv['status']) ?></span>
            </div>
        </div>
    </div>

    <!-- Invoice meta + Bill To -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6">
            <div class="info-label mb-2">Bill To</div>
            <div class="fw-bold"><?= clean($order['customer_name']) ?></div>
            <div class="small"><?= clean($order['customer_email']) ?></div>
            <div class="small"><?= clean($order['customer_phone'] ?? '') ?></div>
            <div class="small mt-1 text-muted">
                <?= clean($order['shipping_address'] ?? '') ?><br>
                <?= clean($order['shipping_city'] ?? '') ?>, <?= clean($order['shipping_state'] ?? '') ?><br>
                <?= clean($order['shipping_country'] ?? '') ?>
            </div>
        </div>
        <div class="col-sm-6 text-sm-end">
            <div class="row gy-2">
                <div class="col-6 col-sm-12">
                    <div class="info-label">Invoice Date</div>
                    <div class="fw-semibold"><?= formatDate($inv['issued_date']) ?></div>
                </div>
                <div class="col-6 col-sm-12">
                    <div class="info-label">Order Number</div>
                    <div class="fw-semibold"><?= clean($order['order_number']) ?></div>
                </div>
                <div class="col-6 col-sm-12">
                    <div class="info-label">Payment Method</div>
                    <div class="fw-semibold"><?= ucwords(str_replace('_',' ',$order['payment_method']??'—')) ?></div>
                </div>
                <div class="col-6 col-sm-12">
                    <div class="info-label">Order Status</div>
                    <div class="fw-semibold"><?= ucwords(str_replace('_',' ',$order['status'])) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Line items -->
    <table class="table table-bordered mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>SKU</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Unit Price</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['items'] as $i => $item): ?>
            <tr>
                <td class="text-muted"><?= $i+1 ?></td>
                <td class="fw-semibold"><?= clean($item['product_name']) ?></td>
                <td><code class="small"><?= clean($item['sku']??'—') ?></code></td>
                <td class="text-center"><?= $item['quantity'] ?></td>
                <td class="text-end"><?= formatPrice($item['unit_price']) ?></td>
                <td class="text-end fw-semibold"><?= formatPrice($item['total_price']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end text-muted small">Subtotal</td>
                <td class="text-end"><?= formatPrice($order['subtotal']) ?></td>
            </tr>
            <?php if ($order['discount_amount']>0): ?>
            <tr>
                <td colspan="5" class="text-end text-success small">
                    Discount <?= $order['coupon_code'] ? '('.clean($order['coupon_code']).')' : '' ?>
                </td>
                <td class="text-end text-success">-<?= formatPrice($order['discount_amount']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($order['points_discount']>0): ?>
            <tr>
                <td colspan="5" class="text-end text-warning small">Loyalty Points Redemption</td>
                <td class="text-end text-warning">-<?= formatPrice($order['points_discount']) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="5" class="text-end text-muted small">Tax (<?= DEFAULT_TAX_RATE ?>% VAT)</td>
                <td class="text-end"><?= formatPrice($order['tax_amount']) ?></td>
            </tr>
            <tr>
                <td colspan="5" class="text-end text-muted small">Shipping</td>
                <td class="text-end"><?= $order['shipping_cost']>0 ? formatPrice($order['shipping_cost']) : 'Free' ?></td>
            </tr>
            <tr class="total-row table-primary">
                <td colspan="5" class="text-end fw-bold">TOTAL</td>
                <td class="text-end fw-bold text-primary"><?= formatPrice($order['total']) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Payment record -->
    <?php if ($order['payment']): $pay=$order['payment']; ?>
    <div class="mt-4 p-3 bg-success bg-opacity-5 border border-success border-opacity-25 rounded-3">
        <div class="row">
            <div class="col-sm-6">
                <div class="info-label mb-1">Payment Received</div>
                <div class="fw-semibold"><?= formatPrice($pay['amount']) ?></div>
                <div class="small text-muted"><?= ucwords(str_replace('_',' ',$pay['payment_method'])) ?></div>
            </div>
            <div class="col-sm-6 text-sm-end">
                <div class="info-label mb-1">Transaction ID</div>
                <code><?= clean($pay['transaction_id']??'—') ?></code>
                <div class="small text-muted"><?= $pay['payment_date'] ? formatDateTime($pay['payment_date']) : '—' ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Notes -->
    <?php if ($inv['notes'] || $order['notes']): ?>
    <div class="mt-4">
        <div class="info-label mb-1">Notes</div>
        <div class="small text-muted"><?= nl2br(clean($inv['notes'] ?: $order['notes'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer-note text-center">
        <p class="mb-1">Thank you for shopping at <strong><?= $siteName ?></strong>!</p>
        <p class="mb-0">Questions? Contact us at <?= clean($siteEmail) ?> · <?= clean($sitePhone) ?></p>
        <p class="mt-1 opacity-50 small">This is a computer-generated invoice and does not require a signature.</p>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</body>
</html>
