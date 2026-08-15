-- ============================================================
-- Product Images Update Script
-- Run this AFTER importing electronic_store.sql
-- Wires all products to the dynamic SVG image generator
-- so every product shows a beautiful visual immediately.
-- ============================================================

USE electronic_store;

-- ── Clear placeholder paths so the fallback generator is used ──
-- (Leave real image paths if you upload actual product photos)
UPDATE product_images SET image_path = '' WHERE image_path LIKE 'products/%.jpg';

-- ── Re-insert clean image records keyed to the generator ────────
-- The PHP generator reads category_slug + product_name automatically
-- via productImageUrl(), so no hard-coded paths needed.

-- ── Optionally: set external hi-res images for seed products ────
-- Uncomment the lines below if you want to point to live CDN images
-- (requires internet access; best for demos / presentations)

/*
UPDATE product_images pi
JOIN products p ON pi.product_id = p.id
SET pi.image_path = CASE p.slug
    WHEN 'samsung-galaxy-s24-ultra'
        THEN 'https://images.samsung.com/is/image/samsung/p6pim/global/2401/gallery/global-galaxy-s24-ultra-s928-sm-s928bzkheub-thumb-539340568'
    WHEN 'apple-iphone-15-pro-max'
        THEN 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/iphone-15-pro-max-naturaltitanium-select'
    WHEN 'sony-playstation-5'
        THEN 'https://gmedia.playstation.com/is/image/SIEPDC/ps5-product-thumbnail-01'
    WHEN 'jbl-tune-760nc'
        THEN 'https://www.jbl.com/dw/image/v2/AAUJ_PRD/on/demandware.static/-/Sites-masterCatalog_Harman/default/product/JBLT760NCBLKAM.png'
    ELSE pi.image_path
END
WHERE pi.is_primary = 1;
*/

-- ── Verify the image records ─────────────────────────────────────
SELECT
    p.product_name,
    p.slug,
    c.slug AS category_slug,
    pi.image_path,
    pi.is_primary
FROM products p
JOIN categories c ON p.category_id = c.id
LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
ORDER BY p.id;
