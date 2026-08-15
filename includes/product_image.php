<?php
/**
 * ── productImageUrl() — UPDATED v2 ───────────────────────────────
 * Returns the best available image URL for a product.
 * Falls back to a beautiful generated SVG if no real image exists.
 *
 * Usage: productImageUrl($path, $categorySlug, $productName)
 */
function productImageUrl(?string $path, string $category = '', string $name = ''): string
{
    // 1. Real uploaded image — serve it
    if ($path && file_exists(UPLOAD_PATH . $path)) {
        return UPLOAD_URL . $path;
    }

    // 2. Path looks like a URL (external CDN) — return as-is
    if ($path && str_starts_with($path, 'http')) {
        return $path;
    }

    // 3. Dynamic SVG generator — always works, even offline
    $cat  = urlencode($category ?: 'electronics');
    $nm   = urlencode(mb_strimwidth($name, 0, 36, ''));
    $v    = $name ? (abs(crc32($name)) % 4) : 0;   // deterministic variant per product
    return BASE_URL . "assets/images/product_img.php?cat={$cat}&name={$nm}&v={$v}";
}

/**
 * Resolve a full product row → image URL, pulling category & name automatically.
 */
function productRowImageUrl(array $product): string
{
    return productImageUrl(
        $product['primary_image'] ?? $product['image_path'] ?? null,
        $product['category_slug'] ?? '',
        $product['product_name']  ?? ''
    );
}

/**
 * Build a srcset-friendly <img> tag for a product (responsive, lazy-loaded).
 */
function productImg(array $product, string $class = '', string $alt = '', int $width = 400, int $height = 300): string
{
    $src  = productRowImageUrl($product);
    $alt  = $alt ?: htmlspecialchars($product['product_name'] ?? 'Product', ENT_QUOTES);
    $cls  = $class ? " class=\"$class\"" : '';
    return "<img src=\"$src\" alt=\"$alt\" width=\"$width\" height=\"$height\" loading=\"lazy\"{$cls} onerror=\"this.onerror=null;this.src='" . BASE_URL . "assets/images/product_img.php?cat=electronics&name=Product'\">";
}
