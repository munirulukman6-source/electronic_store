<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
$db = Database::getInstance();

// Count all products
echo "Total products: " . $db->fetchColumn("SELECT COUNT(*) FROM products") . "<br>";

// Try the same JOIN query used in the admin page
$products = $db->fetchAll(
    "SELECT p.id, p.product_name, p.slug, p.sku, p.price, p.quantity, p.status,
            c.category_name, b.brand_name
     FROM products p
     JOIN categories c ON p.category_id = c.id
     JOIN brands b ON p.brand_id = b.id
     ORDER BY p.created_at DESC"
);

echo "Products from JOIN query: " . count($products) . "<br>";
if (count($products) > 0) {
    echo "First product: " . $products[0]['product_name'] . " (category: " . $products[0]['category_name'] . ")<br>";
} else {
    echo "No products found via JOIN.<br>";

    // Check if categories/brands tables are empty
    echo "Categories: " . $db->fetchColumn("SELECT COUNT(*) FROM categories") . "<br>";
    echo "Brands: " . $db->fetchColumn("SELECT COUNT(*) FROM brands") . "<br>";

    // Show a product without joins
    $p = $db->fetchOne("SELECT * FROM products LIMIT 1");
    if ($p) {
        echo "Sample product ID: " . $p['id'] . ", category_id: " . $p['category_id'] . ", brand_id: " . $p['brand_id'] . "<br>";
        // Check if those IDs exist
        echo "Category ID " . $p['category_id'] . " exists: " . ($db->fetchColumn("SELECT COUNT(*) FROM categories WHERE id = ?", [$p['category_id']]) ? "Yes" : "No") . "<br>";
        echo "Brand ID " . $p['brand_id'] . " exists: " . ($db->fetchColumn("SELECT COUNT(*) FROM brands WHERE id = ?", [$p['brand_id']]) ? "Yes" : "No") . "<br>";
    }
}
?>