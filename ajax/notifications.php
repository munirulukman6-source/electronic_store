<?php
// ajax/notifications.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/Inventory.php'; // contains Notification class
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
header('Content-Type: application/json');

if (!isLoggedIn()) jsonResponse([], 401);

$notif  = new Notification();
$action = get('action', post('action', 'list'));

if ($action === 'list') {
    $rows = $notif->getUnread($_SESSION['user_id'], 15);
    foreach ($rows as &$r) { $r['time_ago'] = timeAgo($r['created_at']); }
    echo json_encode($rows);

} elseif ($action === 'mark_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) jsonResponse(['success' => false], 403);
    $notif->markRead((int)post('id'), $_SESSION['user_id']);
    jsonResponse(['success' => true]);

} elseif ($action === 'mark_all_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) jsonResponse(['success' => false], 403);
    $notif->markAllRead($_SESSION['user_id']);
    jsonResponse(['success' => true]);

} else {
    jsonResponse(['success' => false, 'message' => 'Unknown action.']);
}
