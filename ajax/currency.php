<?php
// ajax/currency.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
header('Content-Type: application/json');

$code = strtoupper(get('currency', DEFAULT_CURRENCY));
$db   = Database::getInstance();
$curr = $db->fetchOne("SELECT * FROM currencies WHERE currency_code = ? AND is_active = 1", [$code]);

if ($curr) {
    $_SESSION['currency']        = $curr['currency_code'];
    $_SESSION['currency_symbol'] = $curr['symbol'];
    $_SESSION['exchange_rate']   = (float)$curr['exchange_rate'];
    jsonResponse(['success' => true, 'currency' => $curr['currency_code'], 'symbol' => $curr['symbol'], 'rate' => $curr['exchange_rate']]);
}
jsonResponse(['success' => false, 'message' => 'Currency not found.']);
