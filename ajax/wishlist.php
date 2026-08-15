<?php
// ajax/wishlist.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/Inventory.php'; // contains Wishlist class
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf())
    jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
if (!isLoggedIn() || !isCustomer())
    jsonResponse(['success' => false, 'message' => 'Please login first.'], 401);

$wl     = new Wishlist();
$action = post('action');

if ($action === 'toggle') {
    $result = $wl->toggle((int)$_SESSION['customer_id'], (int)post('product_id'));
    $result['added'] = strpos($result['message'], 'Added') !== false;
    echo json_encode($result);
} else {
    jsonResponse(['success' => false, 'message' => 'Unknown action.']);
}
