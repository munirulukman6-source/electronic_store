<?php
/**
 * Application Configuration
 * Electronic Devices E-Commerce System
 */

// ─── Path / URL ───────────────────────────────────────────────────────────────
define('ROOT_PATH',   dirname(__DIR__) . '/');
define('BASE_URL',    'http://localhost/electronic_store/');
define('ASSETS_URL',  BASE_URL . 'assets/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');
define('UPLOAD_URL',  BASE_URL . 'uploads/');

// ─── Application ──────────────────────────────────────────────────────────────
define('APP_NAME',      'ElectroStore Ghana');
define('APP_TAGLINE',   "Ghana's #1 Electronics Store");
define('APP_VERSION',   '2.0.0');
define('APP_ENV',       'development'); // development | production
define('APP_DEBUG',     true);
define('APP_TIMEZONE',  'Africa/Accra');

// ─── Session ──────────────────────────────────────────────────────────────────
define('SESSION_NAME',     'electrostore_session');
define('SESSION_LIFETIME', 3600 * 8); // 8 hours
define('REMEMBER_DAYS',    30);

// ─── Security ─────────────────────────────────────────────────────────────────
define('CSRF_TOKEN_LENGTH', 32);
define('PASSWORD_ALGO',     PASSWORD_BCRYPT);
define('PASSWORD_COST',     12);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_MINUTES',    15);

// ─── Pagination ───────────────────────────────────────────────────────────────
define('PRODUCTS_PER_PAGE',  12);
define('ORDERS_PER_PAGE',    20);
define('CUSTOMERS_PER_PAGE', 25);
define('LOGS_PER_PAGE',      50);

// ─── File Uploads ─────────────────────────────────────────────────────────────
define('MAX_FILE_SIZE',    5 * 1024 * 1024); // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('THUMB_WIDTH',   300);
define('THUMB_HEIGHT',  300);

// ─── Currency & Tax ───────────────────────────────────────────────────────────
define('DEFAULT_CURRENCY',  'GHS');
define('DEFAULT_CURRENCY_SYMBOL', '₵');
define('DEFAULT_TAX_RATE',  12.5); // Ghana VAT (NHIL+GETFund)
define('TAX_INCLUSIVE',     false);

// ─── Loyalty ──────────────────────────────────────────────────────────────────
define('POINTS_PER_GHS',     5);   // Spend GHS 5 → earn 1 point
define('POINTS_TO_GHS',      100); // 100 points → GHS 1.00

// ─── Inventory ────────────────────────────────────────────────────────────────
define('LOW_STOCK_DEFAULT', 5);
define('OUT_OF_STOCK',      0);

// ─── Order ────────────────────────────────────────────────────────────────────
define('ORDER_PREFIX',   'ORD');
define('INVOICE_PREFIX', 'INV');
define('RETURN_PREFIX',  'RET');

// ─── Email (PHPMailer / SMTP) ─────────────────────────────────────────────────
define('MAIL_HOST',      'smtp.gmail.com');
define('MAIL_PORT',      587);
define('MAIL_USER',      '');
define('MAIL_PASS',      '');
define('MAIL_FROM',      'noreply@electrostore.com.gh');
define('MAIL_FROM_NAME', 'ElectroStore Ghana');
define('MAIL_ENABLED',   false);

// ─── SMS (mNotify / Hubtel) ───────────────────────────────────────────────────
define('SMS_ENABLED',   false);
define('SMS_PROVIDER',  'mnotify');
define('SMS_API_KEY',   '');
define('SMS_SENDER_ID', 'ElectroStore');

// ─── Compare ──────────────────────────────────────────────────────────────────
define('MAX_COMPARE_ITEMS', 4);

// ─── Date / Time ──────────────────────────────────────────────────────────────
date_default_timezone_set(APP_TIMEZONE);
define('DATE_FORMAT',     'd M Y');
define('DATETIME_FORMAT', 'd M Y, h:i A');

// ─── Error Handling ───────────────────────────────────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . 'logs/error.log');
}
