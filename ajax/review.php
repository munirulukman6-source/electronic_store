<?php
// ajax/review.php — Submit product review
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('danger', 'Invalid request.');
    redirect(BASE_URL);
}

csrfCheck();
requireLogin();

if (!isCustomer()) {
    setFlash('danger', 'Only customers can submit reviews.');
    redirect(BASE_URL);
}

$db        = Database::getInstance();
$productId = (int)post('product_id');
$rating    = (int)post('rating');

// Validate
if ($rating < 1 || $rating > 5) {
    setFlash('danger', 'Please select a rating between 1 and 5.');
    redirect(BASE_URL . 'product/' . get('slug', ''));
}

if (!$productId) {
    setFlash('danger', 'Invalid product.');
    redirect(BASE_URL . 'shop.php');
}

// Check if already reviewed
$existing = $db->count('reviews', 'product_id = ? AND customer_id = ?',
    [$productId, $_SESSION['customer_id']]);
if ($existing) {
    setFlash('warning', 'You have already reviewed this product.');
    $slug = $db->fetchColumn("SELECT slug FROM products WHERE id = ?", [$productId]);
    redirect(BASE_URL . 'product/' . $slug . '#reviews');
}

// Check if customer purchased the product
$verified = $db->fetchOne(
    "SELECT o.id FROM orders o
     JOIN order_items oi ON o.id = oi.order_id
     WHERE o.customer_id = ? AND oi.product_id = ? AND o.status = 'delivered'
     LIMIT 1",
    [$_SESSION['customer_id'], $productId]
);

$db->insert('reviews', [
    'product_id'   => $productId,
    'customer_id'  => $_SESSION['customer_id'],
    'order_id'     => $verified['id'] ?? null,
    'rating'       => $rating,
    'title'        => post('title'),
    'review_text'  => post('review_text'),
    'pros'         => post('pros'),
    'cons'         => post('cons'),
    'is_verified'  => $verified ? 1 : 0,
    'status'       => getSetting('review_approval', '1') === '1' ? 'pending' : 'approved',
]);

$msg = getSetting('review_approval', '1') === '1'
    ? 'Thank you! Your review has been submitted and is awaiting approval.'
    : 'Thank you! Your review has been published.';

setFlash('success', $msg);
$slug = $db->fetchColumn("SELECT slug FROM products WHERE id = ?", [$productId]);
redirect(BASE_URL . 'product/' . $slug . '#reviews');
