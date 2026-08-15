<?php
/** Shopping Cart with session + DB sync, coupon application, order placement */
class Cart
{
    private Database $db;
    private ?int $customerId;
    private string $sessionId;

    public function __construct()
    {
        $this->db         = Database::getInstance();
        $this->customerId = $_SESSION['customer_id'] ?? null;
        $this->sessionId  = session_id();
        $this->sync();
    }

    // ── Ensure cart row exists ────────────────────────────────────────────────
    private function getCartId(): int
    {
        $row = $this->db->fetchOne(
            "SELECT id FROM cart WHERE " . ($this->customerId ? "customer_id = ?" : "session_id = ?"),
            [$this->customerId ?? $this->sessionId]
        );
        if ($row) return $row['id'];
        return $this->db->insert('cart', [
            'customer_id' => $this->customerId,
            'session_id'  => $this->sessionId,
        ]);
    }

    // ── On login, merge guest cart into customer cart ─────────────────────────
    public function sync(): void
    {
        if (!$this->customerId) return;
        $guestCart = $this->db->fetchOne("SELECT id FROM cart WHERE session_id = ? AND customer_id IS NULL", [$this->sessionId]);
        if (!$guestCart) return;
        $custCart  = $this->db->fetchOne("SELECT id FROM cart WHERE customer_id = ?", [$this->customerId]);
        $destId    = $custCart ? $custCart['id'] : $this->db->insert('cart', ['customer_id' => $this->customerId, 'session_id' => $this->sessionId]);
        $guestItems = $this->db->fetchAll("SELECT * FROM cart_items WHERE cart_id = ?", [$guestCart['id']]);
        foreach ($guestItems as $item) {
            $exists = $this->db->fetchOne("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?", [$destId, $item['product_id']]);
            if ($exists) {
                $this->db->update('cart_items', ['quantity' => $exists['quantity'] + $item['quantity']], 'id = ?', [$exists['id']]);
            } else {
                $this->db->insert('cart_items', ['cart_id' => $destId, 'product_id' => $item['product_id'], 'quantity' => $item['quantity'], 'price' => $item['price']]);
            }
        }
        $this->db->delete('cart', 'id = ?', [$guestCart['id']]);
    }

    // ── Add / update item ─────────────────────────────────────────────────────
    public function add(int $productId, int $qty = 1): array
    {
        $product = $this->db->fetchOne(
            "SELECT p.id, p.product_name, p.price, p.quantity, p.status,
                    COALESCE(fs.sale_price, p.price) AS effective_price
             FROM products p
             LEFT JOIN flash_sales fs ON p.id = fs.product_id AND fs.status='active' AND NOW() BETWEEN fs.start_time AND fs.end_time
             WHERE p.id = ? AND p.status = 'active'", [$productId]
        );
        if (!$product) return ['success' => false, 'message' => 'Product not available.'];
        if ($product['quantity'] < $qty) return ['success' => false, 'message' => 'Insufficient stock.'];

        $cartId  = $this->getCartId();
        $existing = $this->db->fetchOne("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?", [$cartId, $productId]);
        if ($existing) {
            $newQty = $existing['quantity'] + $qty;
            if ($newQty > $product['quantity']) return ['success' => false, 'message' => 'Cannot add more than available stock.'];
            $this->db->update('cart_items', ['quantity' => $newQty, 'price' => $product['effective_price']], 'id = ?', [$existing['id']]);
        } else {
            $this->db->insert('cart_items', ['cart_id' => $cartId, 'product_id' => $productId, 'quantity' => $qty, 'price' => $product['effective_price']]);
        }
        return ['success' => true, 'count' => $this->getCount(), 'message' => 'Added to cart.'];
    }

    // ── Update quantity ───────────────────────────────────────────────────────
    public function update(int $productId, int $qty): array
    {
        if ($qty < 1) return $this->remove($productId);
        $product = $this->db->fetchOne("SELECT quantity FROM products WHERE id = ?", [$productId]);
        if ($qty > $product['quantity']) return ['success' => false, 'message' => 'Insufficient stock.'];
        $cartId = $this->getCartId();
        $this->db->update('cart_items', ['quantity' => $qty], 'cart_id = ? AND product_id = ?', [$cartId, $productId]);
        return ['success' => true, 'total' => $this->getTotal(), 'message' => 'Cart updated.'];
    }

    // ── Remove item ───────────────────────────────────────────────────────────
    public function remove(int $productId): array
    {
        $cartId = $this->getCartId();
        $this->db->delete('cart_items', 'cart_id = ? AND product_id = ?', [$cartId, $productId]);
        return ['success' => true, 'count' => $this->getCount(), 'message' => 'Item removed.'];
    }

    // ── Clear cart ────────────────────────────────────────────────────────────
    public function clear(): void
    {
        $cartId = $this->getCartId();
        $this->db->delete('cart_items', 'cart_id = ?', [$cartId]);
    }

    // ── Get all items ─────────────────────────────────────────────────────────
    public function getItems(): array
    {
        $cartId = $this->getCartId();
        return $this->db->fetchAll(
            "SELECT ci.id, ci.quantity, ci.price,
                    p.id AS product_id, p.product_name, p.slug, p.sku,
                    p.quantity AS stock, pi.image_path AS product_image
             FROM cart_items ci
             JOIN products p ON ci.product_id = p.id
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             WHERE ci.cart_id = ?", [$cartId]
        );
    }

    public function getCount(): int
    {
        $cartId = $this->getCartId();
        return (int) $this->db->fetchColumn("SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE cart_id = ?", [$cartId]);
    }

    public function getTotal(): float
    {
        $cartId = $this->getCartId();
        return (float) $this->db->fetchColumn("SELECT COALESCE(SUM(quantity*price),0) FROM cart_items WHERE cart_id = ?", [$cartId]);
    }

    public function getSummary(string $couponCode = '', int $pointsToUse = 0): array
    {
        $subtotal   = $this->getTotal();
        $tax        = round($subtotal * (DEFAULT_TAX_RATE / 100), 2);
        $shipping   = $subtotal >= 500 ? 0 : 20; // Example free shipping > GHS 500
        $discount   = 0;
        $couponId   = null;
        $couponMsg  = '';

        if ($couponCode) {
            $coupon = $this->db->fetchOne(
                "SELECT * FROM coupons WHERE coupon_code = ? AND is_active = 1 AND (expiry_date IS NULL OR expiry_date >= NOW()) AND min_order_amount <= ?",
                [$couponCode, $subtotal]
            );
            if ($coupon) {
                $discount  = $coupon['discount_type'] === 'percentage'
                    ? min($subtotal * $coupon['discount_value'] / 100, $coupon['max_discount'] ?? 999999)
                    : min((float)$coupon['discount_value'], $subtotal);
                if ($coupon['discount_type'] === 'free_shipping') { $shipping = 0; $discount = 0; }
                $couponId  = $coupon['id'];
                $couponMsg = 'Coupon applied!';
            } else {
                $couponMsg = 'Invalid or expired coupon.';
            }
        }

        $pointsDiscount = min($pointsToUse / POINTS_TO_GHS, $subtotal * 0.1); // max 10% via points
        $total = max(0, $subtotal - $discount - $pointsDiscount + $tax + $shipping);

        return compact('subtotal', 'tax', 'shipping', 'discount', 'couponId',
                       'couponMsg', 'pointsToUse', 'pointsDiscount', 'total');
    }
}