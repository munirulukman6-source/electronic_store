<?php
/** Inventory Management — stock-in, adjustments, transfers, reports */
class Inventory
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function stockIn(int $productId, int $qty, float $unitCost = 0,
                            ?int $supplierId = null, string $ref = '', string $notes = ''): array
    {
        $product = $this->db->fetchOne("SELECT id, quantity, product_name FROM products WHERE id = ?", [$productId]);
        if (!$product) return ['success' => false, 'message' => 'Product not found.'];

        $newStock = $product['quantity'] + $qty;
        $this->db->insert('inventory', [
            'product_id'       => $productId,
            'transaction_type' => 'stock_in',
            'quantity'         => $qty,
            'previous_stock'   => $product['quantity'],
            'new_stock'        => $newStock,
            'unit_cost'        => $unitCost,
            'total_cost'       => $unitCost * $qty,
            'supplier_id'      => $supplierId,
            'reference_no'     => $ref ?: 'STK-IN-' . time(),
            'notes'            => $notes,
            'user_id'          => $_SESSION['user_id'] ?? null,
        ]);
        $this->db->update('products', ['quantity' => $newStock], 'id = ?', [$productId]);
        $this->db->audit('stock_in', 'inventory', $productId, ['qty' => $product['quantity']], ['qty' => $newStock]);
        return ['success' => true, 'new_stock' => $newStock, 'message' => "Stock updated: {$product['product_name']} → $newStock units."];
    }

    public function adjust(int $productId, int $newQty, string $reason = ''): array
    {
        $product = $this->db->fetchOne("SELECT id, quantity FROM products WHERE id = ?", [$productId]);
        $this->db->insert('inventory', [
            'product_id'       => $productId,
            'transaction_type' => 'adjustment',
            'quantity'         => abs($newQty - $product['quantity']),
            'previous_stock'   => $product['quantity'],
            'new_stock'        => $newQty,
            'reference_no'     => 'ADJ-' . time(),
            'notes'            => $reason,
            'user_id'          => $_SESSION['user_id'] ?? null,
        ]);
        $this->db->update('products', ['quantity' => $newQty], 'id = ?', [$productId]);
        return ['success' => true, 'new_stock' => $newQty, 'message' => 'Inventory adjusted.'];
    }

    public function getHistory(int $productId, int $page = 1): array
    {
        $sql = "SELECT i.*, p.product_name, u.full_name AS recorded_by, s.supplier_name
                FROM inventory i
                JOIN products p ON i.product_id = p.id
                LEFT JOIN users u ON i.user_id = u.id
                LEFT JOIN suppliers s ON i.supplier_id = s.id
                WHERE i.product_id = ? ORDER BY i.created_at DESC";
        return $this->db->paginate($sql, [$productId], $page, 20);
    }

    public function getAllHistory(array $filters = [], int $page = 1): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['type']))       { $where[] = 'i.transaction_type = ?'; $params[] = $filters['type']; }
        if (!empty($filters['product_id'])) { $where[] = 'i.product_id = ?';       $params[] = $filters['product_id']; }
        if (!empty($filters['date_from']))  { $where[] = 'DATE(i.created_at) >= ?'; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to']))    { $where[] = 'DATE(i.created_at) <= ?'; $params[] = $filters['date_to']; }
        $sql = "SELECT i.*, p.product_name, p.sku, c.category_name, b.brand_name,
                       u.full_name AS recorded_by, s.supplier_name
                FROM inventory i
                JOIN products p ON i.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                JOIN brands b ON p.brand_id = b.id
                LEFT JOIN users u ON i.user_id = u.id
                LEFT JOIN suppliers s ON i.supplier_id = s.id
                WHERE " . implode(' AND ', $where) . " ORDER BY i.created_at DESC";
        return $this->db->paginate($sql, $params, $page, 25);
    }

    public function getLowStockProducts(): array
    {
        return $this->db->fetchAll("SELECT * FROM vw_low_stock ORDER BY quantity ASC");
    }

    public function getStockValue(): array
    {
        return $this->db->fetchOne(
            "SELECT SUM(p.quantity * COALESCE(p.cost_price, p.price)) AS total_cost_value,
                    SUM(p.quantity * p.price) AS total_retail_value,
                    COUNT(*) AS total_products,
                    SUM(p.quantity) AS total_units
             FROM products p WHERE p.status = 'active'"
        ) ?? [];
    }

    public function getSuppliers(): array
    {
        return $this->db->fetchAll("SELECT * FROM suppliers WHERE status = 1 ORDER BY supplier_name");
    }

    public function createSupplier(array $data): int
    {
        return $this->db->insert('suppliers', $data);
    }

    public function updateSupplier(int $id, array $data): void
    {
        $this->db->update('suppliers', $data, 'id = ?', [$id]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
/** Wishlist — per-customer saved products */
class Wishlist
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function add(int $customerId, int $productId): array
    {
        if ($this->db->count('wishlist', 'customer_id = ? AND product_id = ?', [$customerId, $productId])) {
            return ['success' => false, 'already' => true, 'message' => 'Already in wishlist.'];
        }
        $this->db->insert('wishlist', ['customer_id' => $customerId, 'product_id' => $productId]);
        return ['success' => true, 'count' => $this->getCount($customerId), 'message' => 'Added to wishlist.'];
    }

    public function remove(int $customerId, int $productId): array
    {
        $this->db->delete('wishlist', 'customer_id = ? AND product_id = ?', [$customerId, $productId]);
        return ['success' => true, 'count' => $this->getCount($customerId), 'message' => 'Removed from wishlist.'];
    }

    public function toggle(int $customerId, int $productId): array
    {
        return $this->db->count('wishlist', 'customer_id = ? AND product_id = ?', [$customerId, $productId])
            ? $this->remove($customerId, $productId)
            : $this->add($customerId, $productId);
    }

    public function getItems(int $customerId): array
    {
        return $this->db->fetchAll(
            "SELECT w.created_at AS wishlisted_at,
                    p.id, p.product_name, p.slug, p.price, p.quantity AS stock,
                    p.avg_rating, p.short_description, pi.image_path AS primary_image,
                    b.brand_name, COALESCE(fs.sale_price, p.price) AS effective_price
             FROM wishlist w
             JOIN products p ON w.product_id = p.id
             JOIN brands b ON p.brand_id = b.id
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             LEFT JOIN flash_sales fs ON p.id = fs.product_id AND fs.status='active' AND NOW() BETWEEN fs.start_time AND fs.end_time
             WHERE w.customer_id = ? AND p.status = 'active' ORDER BY w.created_at DESC", [$customerId]
        );
    }

    public function getCount(int $customerId): int
    {
        return $this->db->count('wishlist', 'customer_id = ?', [$customerId]);
    }

    public function isWishlisted(int $customerId, int $productId): bool
    {
        return $this->db->count('wishlist', 'customer_id = ? AND product_id = ?', [$customerId, $productId]) > 0;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
/** Loyalty Points — earn, redeem, tier management */
class Loyalty
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getBalance(int $customerId): int
    {
        return (int) $this->db->fetchColumn("SELECT loyalty_points FROM customers WHERE id = ?", [$customerId]);
    }

    public function addPoints(int $customerId, int $points, string $type, string $desc, ?int $orderId = null): void
    {
        $this->db->query("UPDATE customers SET loyalty_points = loyalty_points + ?, total_points_earned = total_points_earned + ? WHERE id = ?",
            [$points, $points, $customerId]);
        $balance = $this->getBalance($customerId);
        $this->db->insert('loyalty_points_log', [
            'customer_id'      => $customerId,
            'order_id'         => $orderId,
            'points'           => $points,
            'transaction_type' => $type,
            'description'      => $desc,
            'balance_after'    => $balance,
        ]);
        $this->updateTier($customerId);
    }

    public function redeemPoints(int $customerId, int $points): array
    {
        $balance = $this->getBalance($customerId);
        if ($points > $balance) return ['success' => false, 'message' => 'Insufficient points.'];
        $this->db->query("UPDATE customers SET loyalty_points = loyalty_points - ? WHERE id = ?", [$points, $customerId]);
        $balance = $this->getBalance($customerId);
        $this->db->insert('loyalty_points_log', [
            'customer_id'      => $customerId,
            'points'           => -$points,
            'transaction_type' => 'redeemed',
            'description'      => 'Points redeemed at checkout',
            'balance_after'    => $balance,
        ]);
        $this->updateTier($customerId);
        return ['success' => true, 'discount' => $points / POINTS_TO_GHS, 'balance' => $balance];
    }

    public function getHistory(int $customerId, int $page = 1): array
    {
        $sql = "SELECT lpl.*, o.order_number FROM loyalty_points_log lpl
                LEFT JOIN orders o ON lpl.order_id = o.id
                WHERE lpl.customer_id = ? ORDER BY lpl.created_at DESC";
        return $this->db->paginate($sql, [$customerId], $page, 20);
    }

    public function getTiers(): array
    {
        return $this->db->fetchAll("SELECT * FROM customer_tiers WHERE is_active = 1 ORDER BY min_points");
    }

    private function updateTier(int $customerId): void
    {
        $points = $this->getBalance($customerId);
        $tier = $this->db->fetchOne("SELECT id FROM customer_tiers WHERE min_points <= ? AND max_points >= ? AND is_active = 1", [$points, $points]);
        if ($tier) $this->db->update('customers', ['tier_id' => $tier['id']], 'id = ?', [$customerId]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
/** Notification — in-app bell notifications */
class Notification
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function create(int $userId, string $type, string $title, string $msg, string $link = '', string $icon = 'fa-bell', string $color = '#17a2b8'): void
    {
        $this->db->insert('notifications', compact('userId', 'type', 'title') + [
            'user_id' => $userId, 'message' => $msg, 'link' => $link, 'icon' => $icon, 'color' => $color
        ]);
    }

    public function getUnread(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public function getAll(int $userId, int $page = 1): array
    {
        return $this->db->paginate(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC",
            [$userId], $page, 20
        );
    }

    public function markRead(int $notifId, int $userId): void
    {
        $this->db->update('notifications', ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')], 'id = ? AND user_id = ?', [$notifId, $userId]);
    }

    public function markAllRead(int $userId): void
    {
        $this->db->update('notifications', ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')], 'user_id = ? AND is_read = 0', [$userId]);
    }

    public function getCount(int $userId): int
    {
        return $this->db->count('notifications', 'user_id = ? AND is_read = 0', [$userId]);
    }

    public function notifyAdmins(string $type, string $title, string $msg, string $link = ''): void
    {
        $admins = $this->db->fetchAll("SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.slug = 'admin' AND u.status = 'active'");
        foreach ($admins as $admin) $this->create($admin['id'], $type, $title, $msg, $link);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
/** Report generator — sales, inventory, customer, revenue */
class Report
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function salesSummary(string $from, string $to): array
    {
        return $this->db->fetchOne(
            "SELECT COUNT(*) AS total_orders,
                    SUM(total) AS total_revenue, AVG(total) AS avg_order,
                    SUM(CASE WHEN status='delivered' THEN 1 ELSE 0 END) AS delivered,
                    SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) AS cancelled,
                    SUM(CASE WHEN payment_status='paid' THEN total ELSE 0 END) AS paid_revenue,
                    SUM(discount_amount) AS total_discounts,
                    SUM(tax_amount) AS total_tax
             FROM orders WHERE DATE(created_at) BETWEEN ? AND ?", [$from, $to]
        ) ?? [];
    }

    public function salesByPeriod(string $from, string $to, string $groupBy = 'daily'): array
    {
        $fmt = $groupBy === 'monthly' ? "'%Y-%m'" : ($groupBy === 'weekly' ? "YEARWEEK(created_at)" : "'%Y-%m-%d'");
        return $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at, $fmt) AS period,
                    COUNT(*) AS orders, SUM(total) AS revenue, AVG(total) AS avg_order
             FROM orders WHERE payment_status='paid' AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY period ORDER BY period", [$from, $to]
        );
    }

    public function topProducts(string $from, string $to, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.product_name, p.sku, b.brand_name,
                    SUM(oi.quantity) AS qty_sold, SUM(oi.total_price) AS revenue,
                    pi.image_path AS primary_image
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.id AND o.payment_status='paid'
             JOIN products p ON oi.product_id = p.id JOIN brands b ON p.brand_id = b.id
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             WHERE DATE(o.created_at) BETWEEN ? AND ?
             GROUP BY oi.product_id ORDER BY qty_sold DESC LIMIT ?", [$from, $to, $limit]
        );
    }

    public function inventoryReport(): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.product_name, p.sku, p.quantity, p.low_stock_alert,
                    p.price, (p.quantity * p.price) AS retail_value,
                    COALESCE(p.cost_price, 0) AS cost_price,
                    (p.quantity * COALESCE(p.cost_price, p.price)) AS cost_value,
                    c.category_name, b.brand_name,
                    CASE WHEN p.quantity = 0 THEN 'Out of Stock'
                         WHEN p.quantity <= p.low_stock_alert THEN 'Low Stock' ELSE 'In Stock' END AS stock_status
             FROM products p JOIN categories c ON p.category_id = c.id JOIN brands b ON p.brand_id = b.id
             WHERE p.status = 'active' ORDER BY p.quantity ASC"
        );
    }

    public function customerReport(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT CONCAT(c.first_name, ' ', c.last_name) AS customer_name, u.email,
                    ct.tier_name, c.loyalty_points, c.total_orders, c.total_spent,
                    COUNT(o.id) AS orders_in_period, SUM(o.total) AS spent_in_period,
                    MAX(o.created_at) AS last_order
             FROM customers c JOIN users u ON c.user_id = u.id
             LEFT JOIN customer_tiers ct ON c.tier_id = ct.id
             LEFT JOIN orders o ON c.id = o.customer_id
               AND DATE(o.created_at) BETWEEN ? AND ? AND o.payment_status='paid'
             GROUP BY c.id ORDER BY spent_in_period DESC", [$from, $to]
        );
    }

    public function revenueByCategory(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT cat.category_name, SUM(oi.total_price) AS revenue, SUM(oi.quantity) AS qty_sold
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.id AND o.payment_status='paid'
             JOIN products p ON oi.product_id = p.id JOIN categories cat ON p.category_id = cat.id
             WHERE DATE(o.created_at) BETWEEN ? AND ?
             GROUP BY cat.id ORDER BY revenue DESC", [$from, $to]
        );
    }

    public function revenueByBrand(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT b.brand_name, SUM(oi.total_price) AS revenue, SUM(oi.quantity) AS qty_sold
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.id AND o.payment_status='paid'
             JOIN products p ON oi.product_id = p.id JOIN brands b ON p.brand_id = b.id
             WHERE DATE(o.created_at) BETWEEN ? AND ?
             GROUP BY b.id ORDER BY revenue DESC", [$from, $to]
        );
    }

    public function paymentMethodBreakdown(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT payment_method, COUNT(*) AS count, SUM(amount) AS total
             FROM payments WHERE status='completed' AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY payment_method ORDER BY total DESC", [$from, $to]
        );
    }
}
