<?php
// customer/cancel_order.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'classes/Order.php';
require_once ROOT_PATH . 'classes/Cart.php';
require_once ROOT_PATH . 'classes/Inventory.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();

requireLogin();
if (!isCustomer()) redirect(BASE_URL);

$id     = (int)get('id');
$model  = new Order();
$result = $model->cancel($id, $_SESSION['customer_id'], 'Cancelled by customer');
setFlash($result['success'] ? 'success' : 'danger', $result['message']);
redirect(BASE_URL . 'customer/orders.php');
