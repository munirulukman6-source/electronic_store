<?php
// auth/logout.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
$user = new User();
$user->logout();
setFlash('success', 'You have been logged out successfully.');
redirect(BASE_URL . 'auth/login.php');
