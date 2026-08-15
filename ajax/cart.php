<?php
// ajax/cart.php — AJAX handler (returns JSON, no HTML)
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/Cart.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
    jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
}

$cart   = new Cart();
$action = post('action');

switch ($action) {
    case 'add':
        $productId = (int)post('product_id');
        $qty       = max(1, (int)post('qty', 1));
        $result    = $cart->add($productId, $qty);
        echo json_encode($result);
        break;

    case 'update':
        $productId = (int)post('product_id');
        $qty       = (int)post('qty');
        $result    = $cart->update($productId, $qty);
        $result['subtotal_fmt'] = formatPrice($cart->getTotal());
        $result['total_fmt']    = formatPrice($cart->getTotal());
        echo json_encode($result);
        break;

    case 'remove':
        $productId = (int)post('product_id');
        echo json_encode($cart->remove($productId));
        break;

    case 'clear':
        $cart->clear();
        jsonResponse(['success' => true, 'message' => 'Cart cleared.', 'count' => 0]);
        break;

    case 'apply_coupon':
        $summary = $cart->getSummary(post('coupon'), (int)post('points', 0));
        echo json_encode([
            'success'       => !empty($summary['couponId']) || post('coupon') === '',
            'message'       => $summary['couponMsg'] ?: 'Summary calculated.',
            'coupon_msg'    => $summary['couponMsg'],
            'discount_fmt'  => formatPrice($summary['discount']),
            'total_fmt'     => formatPrice($summary['total']),
            'subtotal_fmt'  => formatPrice($summary['subtotal']),
            'shipping_fmt'  => formatPrice($summary['shipping']),
            'tax_fmt'       => formatPrice($summary['tax']),
        ]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Unknown action.']);
}