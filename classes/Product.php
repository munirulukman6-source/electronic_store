<?php
/**
 * Product Class — Full product lifecycle management
 */
class Product
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    // ── Get products with filters & pagination ───────────────────────────────
    public function getAll(array $filters = [], int $page = 1, int $perPage = PRODUCTS_PER_PAGE): array
    {
        $where  = ['p.status = "active"'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = '(p.category_id = ? OR c.parent_id = ?)';
            $params[] = $filters['category_id']; $params[] = $filters['category_id'];
        }
        if (!empty($filters['brand_id']))    { $where[] = 'p.brand_id = ?';   $params[] = $filters['brand_id']; }
        if (!empty($filters['category_slug'])){ $where[] = 'c.slug = ?';       $params[] = $filters['category_slug']; }
        if (!empty($filters['brand_slug']))  { $where[] = 'b.slug = ?';        $params[] = $filters['brand_slug']; }
        if (isset($filters['min_price']))    { $where[] = 'p.price >= ?';      $params[] = $filters['min_price']; }
        if (isset($filters['max_price']))    { $where[] = 'p.price <= ?';      $params[] = $filters['max_price']; }
        if (!empty($filters['is_featured'])) { $where[] = 'p.is_featured = 1'; }
        if (!empty($filters['is_new']))      { $where[] = 'p.is_new_arrival = 1'; }
        if (!empty($filters['is_best']))     { $where[] = 'p.is_best_seller = 1'; }
        if (!empty($filters['in_stock']))    { $where[] = 'p.quantity > 0'; }
        if (!empty($filters['rating']))      { $where[] = 'p.avg_rating >= ?'; $params[] = $filters['rating']; }
        if (!empty($filters['search'])) {
            $where[] = 'MATCH(p.product_name, p.model, p.description, p.short_description) AGAINST(? IN BOOLEAN MODE)';
            $params[] = $filters['search'] . '*';
        }
        if (!empty($filters['tag'])) {
            $where[] = 'EXISTS (SELECT 1 FROM product_tags pt WHERE pt.product_id = p.id AND pt.tag_name = ?)';
            $params[] = $filters['tag'];
        }

        $orderMap = [
            'price_asc'   => 'p.price ASC',
            'price_desc'  => 'p.price DESC',
            'newest'      => 'p.created_at DESC',
            'popular'     => 'p.total_sold DESC',
            'rating'      => 'p.avg_rating DESC',
            'views'       => 'p.views DESC',
        ];
        $order = $orderMap[$filters['sort'] ?? 'newest'] ?? 'p.created_at DESC';

        $sql = "SELECT p.id, p.product_name, p.slug, p.model, p.sku, p.price,
                       p.compare_price, p.quantity, p.is_featured, p.is_new_arrival,
                       p.is_best_seller, p.avg_rating, p.review_count, p.views,
                       p.total_sold, p.short_description, p.warranty_months, p.status,
                       c.category_name, c.slug AS category_slug,
                       b.brand_name, b.slug AS brand_slug, b.logo AS brand_logo,
                       pi.image_path AS primary_image,
                       COALESCE(fs.sale_price, p.price) AS effective_price,
                       fs.end_time AS flash_end, fs.id AS flash_id
                FROM products p
                JOIN categories c ON p.category_id = c.id
                JOIN brands b ON p.brand_id = b.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                LEFT JOIN flash_sales fs ON p.id = fs.product_id
                    AND fs.status = 'active' AND NOW() BETWEEN fs.start_time AND fs.end_time
                WHERE " . implode(' AND ', $where) . " ORDER BY $order";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    // ── Get single product by slug or ID ────────────────────────────────────
    public function getBySlug(string $slug): ?array
    {
        $product = $this->db->fetchOne(
            "SELECT p.*, c.category_name, c.slug AS category_slug,
                    b.brand_name, b.slug AS brand_slug, b.logo AS brand_logo, b.website AS brand_website,
                    s.supplier_name, t.rate AS tax_rate,
                    COALESCE(fs.sale_price, p.price) AS effective_price,
                    fs.end_time AS flash_end, fs.id AS flash_id, fs.qty_limit AS flash_qty_limit,
                    (fs.qty_limit - fs.sold_count) AS flash_remaining
             FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN brands b ON p.brand_id = b.id
             LEFT JOIN suppliers s ON p.supplier_id = s.id
             LEFT JOIN taxes t ON p.tax_id = t.id
             LEFT JOIN flash_sales fs ON p.id = fs.product_id
                 AND fs.status = 'active' AND NOW() BETWEEN fs.start_time AND fs.end_time
             WHERE p.slug = ? AND p.status = 'active' LIMIT 1", [$slug]
        );
        if (!$product) return null;

        $product['images']       = $this->getImages($product['id']);
        $product['tags']         = $this->getTags($product['id']);
        $product['faqs']         = $this->getFaqs($product['id']);
        $product['videos']       = $this->getVideos($product['id']);
        $product['specifications'] = $product['specifications'] ? json_decode($product['specifications'], true) : [];
        $product['features']       = $product['features']       ? json_decode($product['features'], true)       : [];

        // Increment view count
        $this->db->query("UPDATE products SET views = views + 1 WHERE id = ?", [$product['id']]);

        return $product;
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->fetchOne("SELECT slug FROM products WHERE id = ?", [$id]);
        return $row ? $this->getBySlug($row['slug']) : null;
    }

    // ── Images ───────────────────────────────────────────────────────────────
    public function getImages(int $productId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, is_primary DESC", [$productId]
        );
    }

    // ── Tags ─────────────────────────────────────────────────────────────────
    public function getTags(int $productId): array
    {
        return $this->db->fetchAll("SELECT tag_name FROM product_tags WHERE product_id = ?", [$productId]);
    }

    // ── FAQs ─────────────────────────────────────────────────────────────────
    public function getFaqs(int $productId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM product_faqs WHERE product_id = ? ORDER BY sort_order", [$productId]
        );
    }

    // ── Videos ───────────────────────────────────────────────────────────────
    public function getVideos(int $productId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM product_videos WHERE product_id = ? ORDER BY sort_order", [$productId]
        );
    }

    // ── Related products ─────────────────────────────────────────────────────
    public function getRelated(int $productId, int $categoryId, int $brandId, int $limit = 6): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.product_name, p.slug, p.price, p.avg_rating,
                    p.short_description, pi.image_path AS primary_image,
                    COALESCE(fs.sale_price, p.price) AS effective_price
             FROM products p
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             LEFT JOIN flash_sales fs ON p.id = fs.product_id AND fs.status='active' AND NOW() BETWEEN fs.start_time AND fs.end_time
             WHERE p.id != ? AND (p.category_id = ? OR p.brand_id = ?) AND p.status = 'active'
             ORDER BY p.total_sold DESC LIMIT ?",
            [$productId, $categoryId, $brandId, $limit]
        );
    }

    // ── Best sellers ─────────────────────────────────────────────────────────
    public function getBestSellers(int $limit = 8): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.product_name, p.slug, p.price, p.avg_rating,
                    p.short_description, pi.image_path AS primary_image,
                    COALESCE(fs.sale_price, p.price) AS effective_price, p.total_sold
             FROM products p
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             LEFT JOIN flash_sales fs ON p.id = fs.product_id AND fs.status='active' AND NOW() BETWEEN fs.start_time AND fs.end_time
             WHERE p.status = 'active' ORDER BY p.total_sold DESC LIMIT ?", [$limit]
        );
    }

    // ── Featured products ─────────────────────────────────────────────────────
    public function getFeatured(int $limit = 8): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.product_name, p.slug, p.price, p.compare_price,
                    p.avg_rating, p.short_description, pi.image_path AS primary_image,
                    b.brand_name, c.category_name,
                    COALESCE(fs.sale_price, p.price) AS effective_price, fs.end_time AS flash_end
             FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN brands b ON p.brand_id = b.id
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             LEFT JOIN flash_sales fs ON p.id = fs.product_id AND fs.status='active' AND NOW() BETWEEN fs.start_time AND fs.end_time
             WHERE p.is_featured = 1 AND p.status = 'active' ORDER BY p.total_sold DESC LIMIT ?", [$limit]
        );
    }

    // ── Active flash sales ────────────────────────────────────────────────────
    public function getActiveFlashSales(): array
    {
        return $this->db->fetchAll(
            "SELECT fs.*, p.product_name, p.slug, pi.image_path AS primary_image
             FROM flash_sales fs
             JOIN products p ON fs.product_id = p.id
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             WHERE fs.status = 'active' AND NOW() BETWEEN fs.start_time AND fs.end_time
             ORDER BY fs.end_time ASC"
        );
    }

    // ── Product bundles ──────────────────────────────────────────────────────
    public function getBundles(bool $featuredOnly = false): array
    {
        $where = $featuredOnly ? 'WHERE pb.status = 1 AND pb.is_featured = 1' : 'WHERE pb.status = 1';
        return $this->db->fetchAll(
            "SELECT pb.*,
                    GROUP_CONCAT(p.product_name SEPARATOR ' + ') AS product_names
             FROM product_bundles pb
             JOIN bundle_items bi ON pb.id = bi.bundle_id
             JOIN products p ON bi.product_id = p.id
             $where GROUP BY pb.id ORDER BY pb.created_at DESC"
        );
    }

    // ── AJAX Search ──────────────────────────────────────────────────────────
    public function search(string $query, int $limit = 10): array
    {
        $q = '%' . $query . '%';
        return $this->db->fetchAll(
            "SELECT p.id, p.product_name, p.slug, p.price,
                    pi.image_path AS primary_image, c.category_name, b.brand_name,
                    COALESCE(fs.sale_price, p.price) AS effective_price
             FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN brands b ON p.brand_id = b.id
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             LEFT JOIN flash_sales fs ON p.id = fs.product_id AND fs.status='active' AND NOW() BETWEEN fs.start_time AND fs.end_time
             WHERE p.status = 'active'
               AND (p.product_name LIKE ? OR p.model LIKE ? OR b.brand_name LIKE ? OR c.category_name LIKE ?)
             ORDER BY p.total_sold DESC LIMIT ?",
            [$q, $q, $q, $q, $limit]
        );
    }

    // ── Product comparison ────────────────────────────────────────────────────
    public function getForComparison(array $ids): array
    {
        if (empty($ids)) return [];
        $in = implode(',', array_map('intval', $ids));
        return $this->db->fetchAll(
            "SELECT p.*, c.category_name, b.brand_name, pi.image_path AS primary_image
             FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN brands b ON p.brand_id = b.id
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             WHERE p.id IN ($in) AND p.status = 'active'"
        );
    }

    // ── Admin CRUD: Create ────────────────────────────────────────────────────
    public function create(array $data): array
    {
        // Generate slug
        $slug = $this->generateSlug($data['product_name']);
        // Auto-generate SKU
        if (empty($data['sku'])) {
            $data['sku'] = strtoupper(substr($data['product_name'], 0, 3)) . '-' . time();
        }

        $productId = $this->db->insert('products', [
            'category_id'       => $data['category_id'],
            'brand_id'          => $data['brand_id'],
            'supplier_id'       => $data['supplier_id'] ?? null,
            'product_name'      => $data['product_name'],
            'slug'              => $slug,
            'model'             => $data['model'] ?? null,
            'sku'               => $data['sku'],
            'barcode'           => $data['barcode'] ?? null,
            'price'             => $data['price'],
            'compare_price'     => $data['compare_price'] ?? null,
            'cost_price'        => $data['cost_price'] ?? null,
            'quantity'          => $data['quantity'] ?? 0,
            'low_stock_alert'   => $data['low_stock_alert'] ?? LOW_STOCK_DEFAULT,
            'weight_kg'         => $data['weight_kg'] ?? null,
            'description'       => $data['description'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'specifications'    => isset($data['specifications']) ? json_encode($data['specifications']) : null,
            'features'          => isset($data['features']) ? json_encode($data['features']) : null,
            'warranty_months'   => $data['warranty_months'] ?? 12,
            'warranty_info'     => $data['warranty_info'] ?? null,
            'is_featured'       => $data['is_featured'] ?? 0,
            'is_new_arrival'    => $data['is_new_arrival'] ?? 1,
            'meta_title'        => $data['meta_title'] ?? $data['product_name'],
            'meta_desc'         => $data['meta_desc'] ?? null,
            'status'            => $data['status'] ?? 'active',
        ]);

        // Record initial stock if any
        if (!empty($data['quantity'])) {
            $this->db->insert('inventory', [
                'product_id'       => $productId,
                'transaction_type' => 'stock_in',
                'quantity'         => $data['quantity'],
                'previous_stock'   => 0,
                'new_stock'        => $data['quantity'],
                'unit_cost'        => $data['cost_price'] ?? null,
                'reference_no'     => 'INITIAL-' . $productId,
                'notes'            => 'Initial stock entry',
                'user_id'          => $_SESSION['user_id'] ?? null,
            ]);
        }

        // Save tags
        if (!empty($data['tags'])) {
            foreach (array_filter(array_map('trim', explode(',', $data['tags']))) as $tag) {
                $this->db->insert('product_tags', ['product_id' => $productId, 'tag_name' => $tag]);
            }
        }

        $this->db->audit('create_product', 'products', $productId, null, $data);
        return ['success' => true, 'product_id' => $productId, 'message' => 'Product created successfully.'];
    }

    // ── Admin CRUD: Update ────────────────────────────────────────────────────
    public function update(int $id, array $data): array
    {
        $old = $this->getById($id);
        $this->db->update('products', [
            'category_id'       => $data['category_id'],
            'brand_id'          => $data['brand_id'],
            'supplier_id'       => $data['supplier_id'] ?? null,
            'product_name'      => $data['product_name'],
            'model'             => $data['model'] ?? null,
            'sku'               => $data['sku'],
            'barcode'           => $data['barcode'] ?? null,
            'price'             => $data['price'],
            'compare_price'     => $data['compare_price'] ?? null,
            'cost_price'        => $data['cost_price'] ?? null,
            'low_stock_alert'   => $data['low_stock_alert'] ?? LOW_STOCK_DEFAULT,
            'weight_kg'         => $data['weight_kg'] ?? null,
            'description'       => $data['description'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'specifications'    => isset($data['specifications']) ? json_encode($data['specifications']) : null,
            'features'          => isset($data['features']) ? json_encode($data['features']) : null,
            'warranty_months'   => $data['warranty_months'] ?? 12,
            'warranty_info'     => $data['warranty_info'] ?? null,
            'is_featured'       => $data['is_featured'] ?? 0,
            'is_new_arrival'    => $data['is_new_arrival'] ?? 0,
            'is_best_seller'    => $data['is_best_seller'] ?? 0,
            'meta_title'        => $data['meta_title'] ?? $data['product_name'],
            'meta_desc'         => $data['meta_desc'] ?? null,
            'status'            => $data['status'] ?? 'active',
        ], 'id = ?', [$id]);

        // Refresh tags
        $this->db->delete('product_tags', 'product_id = ?', [$id]);
        if (!empty($data['tags'])) {
            foreach (array_filter(array_map('trim', explode(',', $data['tags']))) as $tag) {
                $this->db->insert('product_tags', ['product_id' => $id, 'tag_name' => $tag]);
            }
        }

        $this->db->audit('update_product', 'products', $id, $old, $data);
        return ['success' => true, 'message' => 'Product updated successfully.'];
    }

    // ── Admin CRUD: Delete (soft) ─────────────────────────────────────────────
    public function delete(int $id): array
    {
        $this->db->update('products', ['status' => 'discontinued'], 'id = ?', [$id]);
        $this->db->audit('delete_product', 'products', $id);
        return ['success' => true, 'message' => 'Product discontinued.'];
    }

    // ── Add product image ─────────────────────────────────────────────────────
    public function addImage(int $productId, string $path, bool $isPrimary = false): int
    {
        if ($isPrimary) {
            $this->db->update('product_images', ['is_primary' => 0], 'product_id = ?', [$productId]);
        }
        return $this->db->insert('product_images', [
            'product_id' => $productId,
            'image_path' => $path,
            'is_primary' => $isPrimary ? 1 : 0,
            'sort_order' => $this->db->count('product_images', 'product_id = ?', [$productId]) + 1,
        ]);
    }

    // ── Low stock list ────────────────────────────────────────────────────────
    public function getLowStock(): array
    {
        return $this->db->fetchAll("SELECT * FROM vw_low_stock ORDER BY quantity ASC");
    }

    // ── Dashboard top sellers ─────────────────────────────────────────────────
    public function getTopSellers(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.product_name, p.total_sold, p.price, pi.image_path AS primary_image,
                    (p.total_sold * p.price) AS revenue
             FROM products p
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             ORDER BY p.total_sold DESC LIMIT ?", [$limit]
        );
    }

    // ── Categories ────────────────────────────────────────────────────────────
    public function getCategories(bool $withCount = false): array
    {
        $countSql = $withCount
            ? "(SELECT COUNT(*) FROM products WHERE category_id = c.id AND status='active') AS product_count,"
            : '';
        return $this->db->fetchAll(
            "SELECT c.*, $countSql parent.category_name AS parent_name
             FROM categories c LEFT JOIN categories parent ON c.parent_id = parent.id
             WHERE c.status = 1 ORDER BY c.sort_order, c.category_name"
        );
    }

    // ── Brands ───────────────────────────────────────────────────────────────
    public function getBrands(bool $withCount = false): array
    {
        $countSql = $withCount
            ? "(SELECT COUNT(*) FROM products WHERE brand_id = b.id AND status='active') AS product_count,"
            : '';
        return $this->db->fetchAll(
            "SELECT b.*, {$countSql} '' AS dummy FROM brands b WHERE b.status = 1 ORDER BY b.brand_name"
        );
    }

    // ── Price range ───────────────────────────────────────────────────────────
    public function getPriceRange(?int $categoryId = null): array
    {
        $where  = 'p.status = "active"';
        $params = [];
        if ($categoryId) { $where .= ' AND p.category_id = ?'; $params[] = $categoryId; }
        return $this->db->fetchOne(
            "SELECT MIN(p.price) AS min_price, MAX(p.price) AS max_price FROM products p WHERE $where", $params
        ) ?? ['min_price' => 0, 'max_price' => 50000];
    }

    // ── Slug generator ────────────────────────────────────────────────────────
    private function generateSlug(string $name): string
    {
        $slug  = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($name)));
        $base  = $slug;
        $count = 1;
        while ($this->db->count('products', 'slug = ?', [$slug])) {
            $slug = "$base-$count";
            $count++;
        }
        return $slug;
    }
}
