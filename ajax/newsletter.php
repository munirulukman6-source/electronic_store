<?php
// ajax/newsletter.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method.'], 405);

$email = filter_var(trim(post('email')), FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(['success' => false, 'message' => 'Please enter a valid email address.']);

$db  = Database::getInstance();
$exists = $db->fetchOne("SELECT id, status FROM newsletter_subscribers WHERE email = ?", [$email]);

if ($exists) {
    if ($exists['status'] === 'active') jsonResponse(['success' => false, 'message' => 'You are already subscribed!']);
    $db->update('newsletter_subscribers', ['status' => 'active'], 'id = ?', [$exists['id']]);
    jsonResponse(['success' => true, 'message' => 'Welcome back! You have been re-subscribed.']);
}

$db->insert('newsletter_subscribers', [
    'email'       => $email,
    'customer_id' => currentCustomerId(),
    'token'       => bin2hex(random_bytes(16)),
    'status'      => 'active',
]);
jsonResponse(['success' => true, 'message' => 'You are subscribed! Welcome to ElectroStore deals.']);
