<?php
// admin/reports/export.php — CSV/Excel report generator
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/Inventory.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireStaff();

if (!verifyCsrf()) { http_response_code(403); die('Forbidden'); }

$type = get('type', 'sales');
$from = get('from', date('Y-m-01'));
$to   = get('to',   date('Y-m-d'));
$db   = Database::getInstance();

// ── Helper: output CSV ────────────────────────────────────────────
function outputCsv(array $headers, array $rows, string $filename): void
{
    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Pragma: no-cache');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
    fputcsv($out, $headers);
    foreach ($rows as $row) fputcsv($out, array_values($row));
    fclose($out);
    exit;
}

switch ($type) {

    // ── Sales report ────────────────────────────────────────────
    case 'sales':
        $rows = $db->fetchAll(
            "SELECT o.order_number, DATE(o.created_at) AS date,
                    CONCAT(c.first_name,' ',c.last_name) AS customer,
                    u.email, o.subtotal, o.tax_amount, o.shipping_cost,
                    o.discount_amount, o.total, o.status, o.payment_status,
                    o.payment_method, o.coupon_code
             FROM orders o
             JOIN customers c ON o.customer_id=c.id JOIN users u ON c.user_id=u.id
             WHERE DATE(o.created_at) BETWEEN ? AND ?
             ORDER BY o.created_at DESC", [$from, $to]
        );
        outputCsv(
            ['Order #','Date','Customer','Email','Subtotal','Tax','Shipping','Discount','Total','Status','Payment Status','Payment Method','Coupon'],
            $rows, "sales_report_{$from}_to_{$to}.csv"
        );
        break;

    // ── Inventory report ─────────────────────────────────────────
    case 'inventory':
        $invModel = new Inventory();
        $rows     = $invModel->getAllHistory([], 1)['data'];
        $full     = $db->fetchAll(
            "SELECT p.product_name, p.sku, p.barcode, p.quantity, p.price,
                    (p.quantity * p.price) AS retail_value,
                    (p.quantity * COALESCE(p.cost_price,0)) AS cost_value,
                    c.category_name, b.brand_name, p.low_stock_alert,
                    CASE WHEN p.quantity=0 THEN 'Out of Stock'
                         WHEN p.quantity<=p.low_stock_alert THEN 'Low Stock' ELSE 'In Stock' END AS stock_status
             FROM products p JOIN categories c ON p.category_id=c.id JOIN brands b ON p.brand_id=b.id
             WHERE p.status='active' ORDER BY p.quantity ASC"
        );
        outputCsv(
            ['Product Name','SKU','Barcode','Quantity','Unit Price','Retail Value','Cost Value','Category','Brand','Low Stock Alert','Status'],
            $full, "inventory_report_{$from}.csv"
        );
        break;

    // ── Customers report ─────────────────────────────────────────
    case 'customers':
        $rows = $db->fetchAll(
            "SELECT u.full_name, u.email, u.phone, u.status, u.last_login, u.created_at,
                    c.city, c.country, c.loyalty_points, c.total_orders, c.total_spent,
                    ct.tier_name
             FROM users u JOIN customers c ON u.id=c.user_id
             LEFT JOIN customer_tiers ct ON c.tier_id=ct.id
             JOIN roles r ON u.role_id=r.id WHERE r.slug='customer'
             ORDER BY c.total_spent DESC"
        );
        outputCsv(
            ['Name','Email','Phone','Status','Last Login','Joined','City','Country','Loyalty Points','Total Orders','Total Spent','Tier'],
            $rows, "customers_report_{$from}.csv"
        );
        break;

    // ── Products report ──────────────────────────────────────────
    case 'products':
        $rows = $db->fetchAll(
            "SELECT p.product_name, p.sku, p.barcode, p.model, p.price, p.quantity,
                    p.total_sold, p.avg_rating, p.review_count, p.status,
                    c.category_name, b.brand_name, p.created_at
             FROM products p JOIN categories c ON p.category_id=c.id JOIN brands b ON p.brand_id=b.id
             ORDER BY p.total_sold DESC"
        );
        outputCsv(
            ['Product Name','SKU','Barcode','Model','Price','Stock Qty','Total Sold','Avg Rating','Reviews','Status','Category','Brand','Created'],
            $rows, "products_report.csv"
        );
        break;

    // ── Orders report (with items) ───────────────────────────────
    case 'orders':
        $rows = $db->fetchAll(
            "SELECT o.order_number, DATE(o.created_at) AS date,
                    CONCAT(c.first_name,' ',c.last_name) AS customer, u.email,
                    oi.product_name, oi.sku, oi.quantity, oi.unit_price, oi.total_price,
                    o.status, o.payment_status
             FROM order_items oi
             JOIN orders o ON oi.order_id=o.id
             JOIN customers c ON o.customer_id=c.id JOIN users u ON c.user_id=u.id
             WHERE DATE(o.created_at) BETWEEN ? AND ?
             ORDER BY o.created_at DESC, oi.id", [$from, $to]
        );
        outputCsv(
            ['Order #','Date','Customer','Email','Product','SKU','Qty','Unit Price','Line Total','Order Status','Payment Status'],
            $rows, "orders_items_{$from}_to_{$to}.csv"
        );
        break;

    // ── Low stock report ─────────────────────────────────────────
    case 'low_stock':
        $rows = $db->fetchAll("SELECT * FROM vw_low_stock ORDER BY quantity ASC");
        outputCsv(
            ['Product','SKU','Current Stock','Alert Level','Category','Brand','Supplier'],
            $rows, "low_stock_report.csv"
        );
        break;

    default:
        http_response_code(400);
        die('Unknown report type: ' . htmlspecialchars($type));
}
