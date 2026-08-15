<?php
/** Order Class — place, track, manage orders, returns & invoices */
class Order
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    // ── Get order list (admin / customer) ────────────────────────────────────
    public function getAll(array $filters = [], int $page = 1, int $perPage = ORDERS_PER_PAGE): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['customer_id'])) { $where[] = 'o.customer_id = ?';    $params[] = $filters['customer_id']; }
        if (!empty($filters['status']))      { $where[] = 'o.status = ?';         $params[] = $filters['status']; }
        if (!empty($filters['payment_status'])) { $where[] = 'o.payment_status = ?'; $params[] = $filters['payment_status']; }
        if (!empty($filters['search']))  {
            $where[] = '(o.order_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR u.email LIKE ?)';
            $s = '%' . $filters['search'] . '%';
            array_push($params, $s, $s, $s, $s);
        }
        if (!empty($filters['date_from'])) { $where[] = 'DATE(o.created_at) >= ?'; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to']))   { $where[] = 'DATE(o.created_at) <= ?'; $params[] = $filters['date_to']; }

        $sql = "SELECT o.id, o.order_number, o.total, o.status, o.payment_status,
                       o.payment_method, o.created_at, o.shipping_city, o.shipping_country,
                       CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
                       u.email AS customer_email,
                       (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
                JOIN users u ON c.user_id = u.id
                WHERE " . implode(' AND ', $where) . " ORDER BY o.created_at DESC";
        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    // ── Get single order ─────────────────────────────────────────────────────
    public function getById(int $orderId, ?int $customerId = null): ?array
    {
        $where  = 'o.id = ?';
        $params = [$orderId];
        if ($customerId) { $where .= ' AND o.customer_id = ?'; $params[] = $customerId; }

        $order = $this->db->fetchOne(
            "SELECT o.*, CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
                    u.email AS customer_email, u.phone AS customer_phone,
                    ct.tier_name, ct.badge_color
             FROM orders o
             JOIN customers c ON o.customer_id = c.id
             JOIN users u ON c.user_id = u.id
             LEFT JOIN customer_tiers ct ON c.tier_id = ct.id
             WHERE $where LIMIT 1", $params
        );
        if (!$order) return null;
        $order['items']    = $this->getItems($orderId);
        $order['tracking'] = $this->getTracking($orderId);
        $order['payment']  = $this->db->fetchOne("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1", [$orderId]);
        $order['invoice']  = $this->db->fetchOne("SELECT * FROM invoices WHERE order_id = ?", [$orderId]);
        return $order;
    }

    public function getByNumber(string $num, ?int $customerId = null): ?array
    {
        $row = $this->db->fetchOne("SELECT id FROM orders WHERE order_number = ?", [$num]);
        return $row ? $this->getById($row['id'], $customerId) : null;
    }

    public function getItems(int $orderId): array
    {
        return $this->db->fetchAll(
            "SELECT oi.*, p.slug AS product_slug, pi.image_path AS product_image
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             WHERE oi.order_id = ?", [$orderId]
        );
    }

    public function getTracking(int $orderId): array
    {
        return $this->db->fetchAll(
            "SELECT ot.*, u.full_name AS updated_by
             FROM order_tracking ot LEFT JOIN users u ON ot.user_id = u.id
             WHERE ot.order_id = ? ORDER BY ot.created_at ASC", [$orderId]
        );
    }

    // ── Update order status ───────────────────────────────────────────────────
    public function updateStatus(int $orderId, string $status, ?string $note = null): array
    {
        $old = $this->db->fetchOne("SELECT status FROM orders WHERE id = ?", [$orderId]);
        $this->db->update('orders', ['status' => $status], 'id = ?', [$orderId]);
        $this->db->insert('order_tracking', [
            'order_id'    => $orderId,
            'status'      => ucfirst(str_replace('_', ' ', $status)),
            'description' => $note ?? "Order status updated to: $status",
            'user_id'     => $_SESSION['user_id'] ?? null,
        ]);
        $this->db->audit('update_order_status', 'orders', $orderId, $old, ['status' => $status]);
        return ['success' => true, 'message' => 'Order status updated.'];
    }

    // ── Update payment status ─────────────────────────────────────────────────
    public function updatePaymentStatus(int $orderId, string $status): void
    {
        $this->db->update('orders', ['payment_status' => $status], 'id = ?', [$orderId]);
    }

    // ── Record payment ────────────────────────────────────────────────────────
    public function recordPayment(int $orderId, string $method, float $amount, string $txnId = ''): int
    {
        $payId = $this->db->insert('payments', [
            'order_id'       => $orderId,
            'transaction_id' => $txnId ?: 'TXN-' . time() . '-' . $orderId,
            'payment_method' => $method,
            'amount'         => $amount,
            'status'         => 'completed',
            'payment_date'   => date('Y-m-d H:i:s'),
        ]);
        $this->updatePaymentStatus($orderId, 'paid');
        $this->generateInvoice($orderId);
        return $payId;
    }

    // ── Generate invoice ──────────────────────────────────────────────────────
    public function generateInvoice(int $orderId): void
    {
        if ($this->db->count('invoices', 'order_id = ?', [$orderId])) return;
        $order = $this->db->fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
        $invNo = INVOICE_PREFIX . '-' . date('Ymd') . '-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);
        $this->db->insert('invoices', [
            'order_id'       => $orderId,
            'invoice_number' => $invNo,
            'issued_date'    => date('Y-m-d'),
            'due_date'       => date('Y-m-d'),
            'subtotal'       => $order['subtotal'],
            'tax_amount'     => $order['tax_amount'],
            'total'          => $order['total'],
            'status'         => 'paid',
        ]);
    }

    // ── Cancel order ─────────────────────────────────────────────────────────
    public function cancel(int $orderId, ?int $customerId = null, string $reason = ''): array
    {
        $where  = 'id = ? AND status IN ("pending","processing")';
        $params = [$orderId];
        if ($customerId) { $where .= ' AND customer_id = ?'; $params[] = $customerId; }
        $rows = $this->db->update('orders', ['status' => 'cancelled'], $where, $params);
        if (!$rows) return ['success' => false, 'message' => 'Order cannot be cancelled at this stage.'];

        // Restock items
        $items = $this->getItems($orderId);
        foreach ($items as $item) {
            $this->db->query(
                "UPDATE products SET quantity = quantity + ?, total_sold = GREATEST(0, total_sold - ?) WHERE id = ?",
                [$item['quantity'], $item['quantity'], $item['product_id']]
            );
        }
        $this->db->insert('order_tracking', [
            'order_id'    => $orderId,
            'status'      => 'Cancelled',
            'description' => "Order cancelled. Reason: $reason",
            'user_id'     => $_SESSION['user_id'] ?? null,
        ]);
        return ['success' => true, 'message' => 'Order cancelled successfully.'];
    }

    // ── Submit return request ─────────────────────────────────────────────────
    public function submitReturn(int $orderId, int $customerId, array $data): array
    {
        $order = $this->db->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND customer_id = ? AND status = 'delivered'",
            [$orderId, $customerId]
        );
        if (!$order) return ['success' => false, 'message' => 'This order cannot be returned.'];

        $retNum = RETURN_PREFIX . '-' . date('Ymd') . '-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);
        $retId  = $this->db->insert('returns', [
            'order_id'      => $orderId,
            'customer_id'   => $customerId,
            'return_number' => $retNum,
            'reason'        => $data['reason'],
            'description'   => $data['description'] ?? null,
            'refund_type'   => $data['refund_type'] ?? 'full',
            'status'        => 'pending',
        ]);
        return ['success' => true, 'return_id' => $retId, 'message' => 'Return request submitted.'];
    }

    // ── Customer order history ────────────────────────────────────────────────
    public function getCustomerOrders(int $customerId, int $page = 1): array
    {
        return $this->getAll(['customer_id' => $customerId], $page);
    }

    // ── Dashboard recent orders ───────────────────────────────────────────────
    public function getRecent(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT o.id, o.order_number, o.total, o.status, o.payment_status, o.created_at,
                    CONCAT(c.first_name, ' ', c.last_name) AS customer_name, u.email
             FROM orders o JOIN customers c ON o.customer_id = c.id JOIN users u ON c.user_id = u.id
             ORDER BY o.created_at DESC LIMIT ?", [$limit]
        );
    }

    // ── Order stats for dashboard ─────────────────────────────────────────────
    public function getStats(): array
    {
        return $this->db->fetchOne("SELECT * FROM vw_dashboard_stats") ?? [];
    }

    // ── Revenue chart data (last 12 months) ───────────────────────────────────
    public function getRevenueChart(int $months = 12): array
    {
        return $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%b %Y') AS month_label,
                    DATE_FORMAT(created_at, '%Y-%m') AS month_key,
                    COUNT(*) AS order_count,
                    COALESCE(SUM(total), 0) AS revenue
             FROM orders WHERE payment_status = 'paid'
               AND created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month_key ASC", [$months]
        );
    }

    // ── Shipping zones ─────────────────────────────────────────────────────────
    public function getShippingZones(): array
    {
        return $this->db->fetchAll("SELECT * FROM shipping_zones WHERE is_active = 1 ORDER BY base_rate");
    }
}
