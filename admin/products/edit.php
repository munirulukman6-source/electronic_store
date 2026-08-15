<?php
// admin/products/edit.php — Loads the add.php form in edit mode
// The add.php already handles both create and update via $isEdit flag
$_GET['id'] = $_GET['id'] ?? 0;
require __DIR__ . '/add.php';
