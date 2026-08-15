<?php
// ajax/admin_action.php — Central AJAX handler for admin operations
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'classes/Order.php';
require_once ROOT_PATH . 'classes/Inventory.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
header('Content-Type: application/json');

if (!isStaff())                 jsonResponse(['success' => false, 'message' => 'Unauthorized.'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
if (!verifyCsrf())              jsonResponse(['success' => false, 'message' => 'CSRF token mismatch.'], 403);

$db     = Database::getInstance();
$action = post('action');
$id     = (int)post('id');

switch ($action) {

    // ── Products ────────────────────────────────────────────────
    case 'delete_product':
        requireAdmin();
        $p = new Product();
        echo json_encode($p->delete($id));
        break;

    case 'toggle_featured':
        requireStaff();
        $cur = $db->fetchColumn("SELECT is_featured FROM products WHERE id = ?", [$id]);
        $db->update('products', ['is_featured' => $cur ? 0 : 1], 'id = ?', [$id]);
        $db->audit('toggle_featured', 'products', $id);
        jsonResponse(['success' => true, 'message' => 'Updated.']);
        break;

    case 'toggle_product_status':
        requireStaff();
        $status = post('status', 'active');
        $db->update('products', ['status' => $status], 'id = ?', [$id]);
        $db->audit("set_product_status_$status", 'products', $id);
        jsonResponse(['success' => true, 'message' => 'Product status updated.']);
        break;

    case 'delete_image':
        requireStaff();
        $img = $db->fetchOne("SELECT image_path, is_primary FROM product_images WHERE id = ?", [$id]);
        if (!$img) jsonResponse(['success' => false, 'message' => 'Image not found.']);
        if ($img['is_primary']) jsonResponse(['success' => false, 'message' => 'Cannot delete primary image. Set another as primary first.']);
        deleteFile($img['image_path']);
        $db->delete('product_images', 'id = ?', [$id]);
        jsonResponse(['success' => true, 'message' => 'Image removed.']);
        break;

    case 'set_primary_image':
        requireStaff();
        $img = $db->fetchOne("SELECT product_id FROM product_images WHERE id = ?", [$id]);
        if (!$img) jsonResponse(['success' => false, 'message' => 'Image not found.']);
        $db->update('product_images', ['is_primary' => 0], 'product_id = ?', [$img['product_id']]);
        $db->update('product_images', ['is_primary' => 1], 'id = ?', [$id]);
        jsonResponse(['success' => true, 'message' => 'Primary image updated.']);
        break;

    // ── Orders ──────────────────────────────────────────────────
    case 'update_order_status':
        requireStaff();
        $o    = new Order();
        $res  = $o->updateStatus($id, post('status'), post('note'));
        echo json_encode($res);
        break;

    case 'cancel_order':
        requireStaff();
        $o   = new Order();
        echo json_encode($o->cancel($id, null, post('reason', 'Cancelled by admin')));
        break;

    // ── Inventory ───────────────────────────────────────────────
    case 'stock_in':
        requireRole('admin', 'inventory_manager');
        $inv = new Inventory();
        echo json_encode($inv->stockIn(
            (int)post('product_id'),
            (int)post('qty'),
            (float)(post('cost') ?: 0),
            null,
            post('ref', '')
        ));
        break;

    // ── Customers ───────────────────────────────────────────────
    case 'toggle_user_status':
        requireAdmin();
        $u = new User();
        $u->toggleStatus($id, post('status', 'active'));
        jsonResponse(['success' => true, 'message' => 'User status updated.']);
        break;

    case 'delete_user':
        requireAdmin();
        $db->update('users', ['status' => 'suspended'], 'id = ?', [$id]);
        $db->audit('suspend_user', 'users', $id);
        jsonResponse(['success' => true, 'message' => 'User suspended.']);
        break;

    // ── Reviews ─────────────────────────────────────────────────
    case 'approve_review':
        requireStaff();
        $db->update('reviews', ['status' => 'approved'], 'id = ?', [$id]);
        $db->audit('approve_review', 'reviews', $id);
        jsonResponse(['success' => true, 'message' => 'Review approved.']);
        break;

    case 'reject_review':
        requireStaff();
        $db->update('reviews', ['status' => 'rejected'], 'id = ?', [$id]);
        $db->audit('reject_review', 'reviews', $id);
        jsonResponse(['success' => true, 'message' => 'Review rejected.']);
        break;

    // ── Coupons ─────────────────────────────────────────────────
    case 'toggle_coupon':
        requireStaff();
        $cur = $db->fetchColumn("SELECT is_active FROM coupons WHERE id = ?", [$id]);
        $db->update('coupons', ['is_active' => $cur ? 0 : 1], 'id = ?', [$id]);
        jsonResponse(['success' => true, 'message' => 'Coupon updated.']);
        break;

    case 'delete_coupon':
        requireAdmin();
        $db->delete('coupons', 'id = ?', [$id]);
        $db->audit('delete_coupon', 'coupons', $id);
        jsonResponse(['success' => true, 'message' => 'Coupon deleted.']);
        break;

    // ── Flash Sales ─────────────────────────────────────────────
    case 'toggle_flash_sale':
        requireStaff();
        $cur = $db->fetchColumn("SELECT status FROM flash_sales WHERE id = ?", [$id]);
        $new = $cur === 'active' ? 'inactive' : 'active';
        $db->update('flash_sales', ['status' => $new], 'id = ?', [$id]);
        jsonResponse(['success' => true, 'message' => 'Flash sale ' . $new . '.']);
        break;

    // ── Returns ─────────────────────────────────────────────────
    case 'approve_return':
        requireStaff();
        $ret = $db->fetchOne("SELECT * FROM returns WHERE id = ?", [$id]);
        if (!$ret) jsonResponse(['success' => false, 'message' => 'Return not found.']);
        $db->update('returns', ['status' => 'approved', 'processed_by' => $_SESSION['user_id']], 'id = ?', [$id]);
        $db->audit('approve_return', 'returns', $id);
        jsonResponse(['success' => true, 'message' => 'Return approved.']);
        break;

    case 'reject_return':
        requireStaff();
        $db->update('returns', ['status' => 'rejected', 'processed_by' => $_SESSION['user_id'], 'admin_notes' => post('reason')], 'id = ?', [$id]);
        $db->audit('reject_return', 'returns', $id);
        jsonResponse(['success' => true, 'message' => 'Return rejected.']);
        break;

    // ── Settings ─────────────────────────────────────────────────
    case 'save_settings':
        requireAdmin();
        foreach ($_POST as $key => $val) {
            if (in_array($key, ['action', 'csrf_token'])) continue;
            $exists = $db->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
            if ($exists) $db->update('settings', ['setting_value' => $val], 'setting_key = ?', [$key]);
            else          $db->insert('settings', ['setting_key' => $key, 'setting_value' => $val]);
        }
        $db->audit('save_settings', 'settings');
        jsonResponse(['success' => true, 'message' => 'Settings saved.']);
        break;

    // ── Notifications ────────────────────────────────────────────
    case 'dismiss_notification':
        $db->update('notifications', ['is_read' => 1], 'id = ? AND user_id = ?', [$id, $_SESSION['user_id']]);
        jsonResponse(['success' => true]);
        break;

    // ── Brands ──────────────────────────────────────────────────
    case 'delete_brand':
        requireAdmin();
        $inUse = $db->count('products', 'brand_id = ?', [$id]);
        if ($inUse) jsonResponse(['success' => false, 'message' => "Cannot delete: $inUse products use this brand."]);
        $db->delete('brands', 'id = ?', [$id]);
        jsonResponse(['success' => true, 'message' => 'Brand deleted.']);
        break;

    // ── Categories ───────────────────────────────────────────────
    case 'delete_category':
        requireAdmin();
        $inUse = $db->count('products', 'category_id = ?', [$id]);
        if ($inUse) jsonResponse(['success' => false, 'message' => "Cannot delete: $inUse products in this category."]);
        $db->delete('categories', 'id = ?', [$id]);
        jsonResponse(['success' => true, 'message' => 'Category deleted.']);
        break;

    // ── Suppliers ────────────────────────────────────────────────
    case 'delete_supplier':
        requireAdmin();
        $db->update('suppliers', ['status' => 0], 'id = ?', [$id]);
        jsonResponse(['success' => true, 'message' => 'Supplier deactivated.']);
        break;

    default:
        jsonResponse(['success' => false, 'message' => "Unknown action: $action"], 400);
}
