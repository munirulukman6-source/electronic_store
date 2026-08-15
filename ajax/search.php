<?php
// ajax/search.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
header('Content-Type: application/json');
$q       = get('q', '');
$results = [];
if (strlen(trim($q)) >= 2) {
    $model   = new Product();
    $raw     = $model->search($q, 8);
    foreach ($raw as $r) {
        $r['primary_image']      = productImageUrl($r['primary_image'] ?? '');
        $r['effective_price_fmt']= formatPrice($r['effective_price']);
        $results[] = $r;
    }
    // Log search
    $db = Database::getInstance();
    $db->insert('search_logs', [
        'search_term'   => $q,
        'results_count' => count($results),
        'customer_id'   => currentCustomerId(),
        'session_id'    => session_id(),
        'ip_address'    => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
}
echo json_encode($results);
