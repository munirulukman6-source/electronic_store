-- ============================================================
-- Electronic Devices E-Commerce Management System
-- Enhanced Database Schema v2.0
-- Compatible with: MySQL 8.0+
-- Project: Final Year Project - Computer Science
-- ============================================================

CREATE DATABASE IF NOT EXISTS electronic_store
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE electronic_store;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- ============================================================
-- TABLE: roles
-- ============================================================
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_name   VARCHAR(50) NOT NULL UNIQUE,
    slug        VARCHAR(50) NOT NULL UNIQUE,
    permissions JSON,
    description TEXT,
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='System roles with JSON permissions';

-- ============================================================
-- TABLE: users
-- ============================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id               INT UNSIGNED NOT NULL,
    username              VARCHAR(100) NOT NULL UNIQUE,
    email                 VARCHAR(255) NOT NULL UNIQUE,
    password              VARCHAR(255) NOT NULL,
    full_name             VARCHAR(200) NOT NULL,
    phone                 VARCHAR(20),
    avatar                VARCHAR(255),
    status                ENUM('active','inactive','suspended','pending') DEFAULT 'active',
    email_verified        TINYINT(1) DEFAULT 0,
    email_verify_token    VARCHAR(100),
    two_factor_enabled    TINYINT(1) DEFAULT 0,
    two_factor_secret     VARCHAR(32),
    password_reset_token  VARCHAR(100),
    password_reset_expires DATETIME,
    last_login            DATETIME,
    last_ip               VARCHAR(45),
    login_attempts        INT DEFAULT 0,
    locked_until          DATETIME,
    remember_token        VARCHAR(255),
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_role (role_id)
) ENGINE=InnoDB COMMENT='All system users including staff';

-- ============================================================
-- TABLE: customer_tiers
-- ============================================================
DROP TABLE IF EXISTS customer_tiers;
CREATE TABLE customer_tiers (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tier_name           VARCHAR(50) NOT NULL UNIQUE,
    min_points          INT UNSIGNED DEFAULT 0,
    max_points          INT UNSIGNED DEFAULT 999999,
    discount_percentage DECIMAL(5,2) DEFAULT 0.00,
    cashback_rate       DECIMAL(5,2) DEFAULT 0.00,
    free_shipping       TINYINT(1) DEFAULT 0,
    priority_support    TINYINT(1) DEFAULT 0,
    badge_color         VARCHAR(20) DEFAULT '#6c757d',
    badge_icon          VARCHAR(50) DEFAULT 'fa-medal',
    benefits            TEXT,
    is_active           TINYINT(1) DEFAULT 1,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Customer loyalty tier levels';

-- ============================================================
-- TABLE: customers
-- ============================================================
DROP TABLE IF EXISTS customers;
CREATE TABLE customers (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id           INT UNSIGNED NOT NULL UNIQUE,
    first_name        VARCHAR(100) NOT NULL,
    last_name         VARCHAR(100) NOT NULL,
    phone             VARCHAR(20),
    date_of_birth     DATE,
    gender            ENUM('male','female','other','prefer_not_to_say'),
    avatar            VARCHAR(255),
    address           TEXT,
    city              VARCHAR(100),
    state             VARCHAR(100),
    country           VARCHAR(100) DEFAULT 'Ghana',
    postal_code       VARCHAR(20),
    loyalty_points    INT DEFAULT 0,
    total_points_earned INT DEFAULT 0,
    tier_id           INT UNSIGNED,
    newsletter        TINYINT(1) DEFAULT 1,
    sms_notifications TINYINT(1) DEFAULT 1,
    email_notifications TINYINT(1) DEFAULT 1,
    total_orders      INT DEFAULT 0,
    total_spent       DECIMAL(12,2) DEFAULT 0.00,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tier_id) REFERENCES customer_tiers(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_tier (tier_id),
    INDEX idx_points (loyalty_points)
) ENGINE=InnoDB COMMENT='Extended customer profiles';

-- ============================================================
-- TABLE: staff
-- ============================================================
DROP TABLE IF EXISTS staff;
CREATE TABLE staff (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL UNIQUE,
    department   VARCHAR(100),
    position     VARCHAR(100),
    employee_id  VARCHAR(50) UNIQUE,
    hire_date    DATE,
    salary       DECIMAL(12,2),
    supervisor_id INT UNSIGNED,
    status       ENUM('active','on_leave','terminated') DEFAULT 'active',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor_id) REFERENCES staff(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Staff and employee records';

-- ============================================================
-- TABLE: categories
-- ============================================================
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id     INT UNSIGNED,
    category_name VARCHAR(150) NOT NULL,
    slug          VARCHAR(150) NOT NULL UNIQUE,
    image         VARCHAR(255),
    icon          VARCHAR(100),
    description   TEXT,
    meta_title    VARCHAR(255),
    meta_desc     TEXT,
    sort_order    INT DEFAULT 0,
    is_featured   TINYINT(1) DEFAULT 0,
    status        TINYINT(1) DEFAULT 1,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_parent (parent_id),
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB COMMENT='Product categories with hierarchical support';

-- ============================================================
-- TABLE: brands
-- ============================================================
DROP TABLE IF EXISTS brands;
CREATE TABLE brands (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand_name  VARCHAR(150) NOT NULL UNIQUE,
    slug        VARCHAR(150) NOT NULL UNIQUE,
    logo        VARCHAR(255),
    website     VARCHAR(255),
    description TEXT,
    country     VARCHAR(100),
    status      TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB COMMENT='Product brands';

-- ============================================================
-- TABLE: suppliers
-- ============================================================
DROP TABLE IF EXISTS suppliers;
CREATE TABLE suppliers (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_name  VARCHAR(200) NOT NULL,
    contact_person VARCHAR(150),
    email          VARCHAR(255),
    phone          VARCHAR(30),
    alt_phone      VARCHAR(30),
    address        TEXT,
    city           VARCHAR(100),
    state          VARCHAR(100),
    country        VARCHAR(100),
    postal_code    VARCHAR(20),
    tax_number     VARCHAR(50),
    payment_terms  VARCHAR(100),
    lead_time_days INT DEFAULT 7,
    rating         DECIMAL(3,2) DEFAULT 0.00,
    notes          TEXT,
    status         TINYINT(1) DEFAULT 1,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB COMMENT='Product suppliers';

-- ============================================================
-- TABLE: currencies
-- ============================================================
DROP TABLE IF EXISTS currencies;
CREATE TABLE currencies (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    currency_code CHAR(3) NOT NULL UNIQUE,
    currency_name VARCHAR(100) NOT NULL,
    symbol        VARCHAR(10) NOT NULL,
    exchange_rate DECIMAL(12,6) DEFAULT 1.000000,
    is_default    TINYINT(1) DEFAULT 0,
    is_active     TINYINT(1) DEFAULT 1,
    last_updated  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Multi-currency support';

-- ============================================================
-- TABLE: taxes
-- ============================================================
DROP TABLE IF EXISTS taxes;
CREATE TABLE taxes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tax_name   VARCHAR(100) NOT NULL,
    rate       DECIMAL(5,2) NOT NULL,
    country    VARCHAR(100),
    region     VARCHAR(100),
    is_default TINYINT(1) DEFAULT 0,
    is_active  TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Tax rates by region';

-- ============================================================
-- TABLE: shipping_zones
-- ============================================================
DROP TABLE IF EXISTS shipping_zones;
CREATE TABLE shipping_zones (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    zone_name    VARCHAR(100) NOT NULL,
    countries    TEXT,
    base_rate    DECIMAL(10,2) DEFAULT 0.00,
    per_kg_rate  DECIMAL(10,2) DEFAULT 0.00,
    free_above   DECIMAL(10,2),
    est_days_min INT DEFAULT 1,
    est_days_max INT DEFAULT 7,
    is_active    TINYINT(1) DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Shipping zones and rates';

-- ============================================================
-- TABLE: products
-- ============================================================
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id       INT UNSIGNED NOT NULL,
    brand_id          INT UNSIGNED NOT NULL,
    supplier_id       INT UNSIGNED,
    product_name      VARCHAR(255) NOT NULL,
    slug              VARCHAR(255) NOT NULL UNIQUE,
    model             VARCHAR(150),
    sku               VARCHAR(100) UNIQUE,
    barcode           VARCHAR(100),
    price             DECIMAL(12,2) NOT NULL,
    compare_price     DECIMAL(12,2),
    cost_price        DECIMAL(12,2),
    tax_id            INT UNSIGNED,
    quantity          INT DEFAULT 0,
    low_stock_alert   INT DEFAULT 5,
    weight_kg         DECIMAL(8,3),
    dimensions        VARCHAR(100),
    description       LONGTEXT,
    short_description TEXT,
    specifications    JSON,
    features          JSON,
    warranty_months   INT DEFAULT 12,
    warranty_info     TEXT,
    is_featured       TINYINT(1) DEFAULT 0,
    is_new_arrival    TINYINT(1) DEFAULT 1,
    is_best_seller    TINYINT(1) DEFAULT 0,
    allow_reviews     TINYINT(1) DEFAULT 1,
    meta_title        VARCHAR(255),
    meta_desc         TEXT,
    meta_keywords     TEXT,
    status            ENUM('active','inactive','draft','discontinued') DEFAULT 'active',
    views             INT DEFAULT 0,
    total_sold        INT DEFAULT 0,
    avg_rating        DECIMAL(3,2) DEFAULT 0.00,
    review_count      INT DEFAULT 0,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (tax_id) REFERENCES taxes(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_brand (brand_id),
    INDEX idx_status (status),
    INDEX idx_slug (slug),
    INDEX idx_sku (sku),
    FULLTEXT idx_search (product_name, model, description, short_description)
) ENGINE=InnoDB COMMENT='Main products table';

-- ============================================================
-- TABLE: product_images
-- ============================================================
DROP TABLE IF EXISTS product_images;
CREATE TABLE product_images (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    alt_text    VARCHAR(255),
    is_primary  TINYINT(1) DEFAULT 0,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB COMMENT='Product image gallery';

-- ============================================================
-- TABLE: product_tags
-- ============================================================
DROP TABLE IF EXISTS product_tags;
CREATE TABLE product_tags (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    tag_name   VARCHAR(100) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_tag (tag_name)
) ENGINE=InnoDB COMMENT='Product searchable tags';

-- ============================================================
-- TABLE: product_videos
-- ============================================================
DROP TABLE IF EXISTS product_videos;
CREATE TABLE product_videos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED NOT NULL,
    video_url   VARCHAR(500) NOT NULL,
    video_type  ENUM('youtube','vimeo','mp4') DEFAULT 'youtube',
    title       VARCHAR(255),
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Product demo videos';

-- ============================================================
-- TABLE: product_faqs
-- ============================================================
DROP TABLE IF EXISTS product_faqs;
CREATE TABLE product_faqs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED NOT NULL,
    question    TEXT NOT NULL,
    answer      TEXT NOT NULL,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB COMMENT='Product FAQs';

-- ============================================================
-- TABLE: inventory
-- ============================================================
DROP TABLE IF EXISTS inventory;
CREATE TABLE inventory (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id       INT UNSIGNED NOT NULL,
    transaction_type ENUM('stock_in','stock_out','adjustment','return','damage','transfer') NOT NULL,
    quantity         INT NOT NULL,
    previous_stock   INT NOT NULL,
    new_stock        INT NOT NULL,
    unit_cost        DECIMAL(12,2),
    total_cost       DECIMAL(12,2),
    supplier_id      INT UNSIGNED,
    reference_no     VARCHAR(100),
    batch_number     VARCHAR(100),
    expiry_date      DATE,
    location         VARCHAR(100),
    notes            TEXT,
    user_id          INT UNSIGNED,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_type (transaction_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB COMMENT='Inventory transaction history';

-- ============================================================
-- TABLE: flash_sales
-- ============================================================
DROP TABLE IF EXISTS flash_sales;
CREATE TABLE flash_sales (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id     INT UNSIGNED NOT NULL,
    title          VARCHAR(200),
    sale_price     DECIMAL(12,2) NOT NULL,
    original_price DECIMAL(12,2) NOT NULL,
    discount_pct   DECIMAL(5,2),
    start_time     DATETIME NOT NULL,
    end_time       DATETIME NOT NULL,
    qty_limit      INT,
    sold_count     INT DEFAULT 0,
    banner_image   VARCHAR(255),
    status         ENUM('active','inactive','ended') DEFAULT 'active',
    created_by     INT UNSIGNED,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_dates (start_time, end_time),
    INDEX idx_status (status)
) ENGINE=InnoDB COMMENT='Time-limited flash sale events';

-- ============================================================
-- TABLE: product_bundles
-- ============================================================
DROP TABLE IF EXISTS product_bundles;
CREATE TABLE product_bundles (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bundle_name         VARCHAR(255) NOT NULL,
    slug                VARCHAR(255) NOT NULL UNIQUE,
    description         TEXT,
    image               VARCHAR(255),
    original_price      DECIMAL(12,2),
    bundle_price        DECIMAL(12,2) NOT NULL,
    discount_pct        DECIMAL(5,2),
    is_featured         TINYINT(1) DEFAULT 0,
    quantity_available  INT DEFAULT 0,
    status              TINYINT(1) DEFAULT 1,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB COMMENT='Product bundle offers';

-- ============================================================
-- TABLE: bundle_items
-- ============================================================
DROP TABLE IF EXISTS bundle_items;
CREATE TABLE bundle_items (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bundle_id  INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity   INT DEFAULT 1,
    FOREIGN KEY (bundle_id) REFERENCES product_bundles(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_bundle (bundle_id)
) ENGINE=InnoDB COMMENT='Products within a bundle';

-- ============================================================
-- TABLE: coupons
-- ============================================================
DROP TABLE IF EXISTS coupons;
CREATE TABLE coupons (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_code       VARCHAR(50) NOT NULL UNIQUE,
    coupon_name       VARCHAR(150),
    discount_type     ENUM('percentage','fixed','free_shipping','buy_x_get_y') DEFAULT 'percentage',
    discount_value    DECIMAL(12,2) NOT NULL,
    min_order_amount  DECIMAL(12,2) DEFAULT 0.00,
    max_discount      DECIMAL(12,2),
    buy_quantity      INT DEFAULT 1,
    get_quantity      INT DEFAULT 0,
    applicable_to     ENUM('all','category','product','brand') DEFAULT 'all',
    applicable_ids    TEXT,
    max_uses          INT,
    max_uses_per_user INT DEFAULT 1,
    used_count        INT DEFAULT 0,
    start_date        DATETIME,
    expiry_date       DATETIME,
    is_active         TINYINT(1) DEFAULT 1,
    created_by        INT UNSIGNED,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (coupon_code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB COMMENT='Discount coupons and promo codes';

-- ============================================================
-- TABLE: coupon_usage
-- ============================================================
DROP TABLE IF EXISTS coupon_usage;
CREATE TABLE coupon_usage (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id   INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED,
    order_id    INT UNSIGNED,
    used_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    INDEX idx_coupon (coupon_id),
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB COMMENT='Coupon usage tracking';

-- ============================================================
-- TABLE: cart
-- ============================================================
DROP TABLE IF EXISTS cart;
CREATE TABLE cart (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED,
    session_id  VARCHAR(255),
    currency    CHAR(3) DEFAULT 'GHS',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_session (session_id)
) ENGINE=InnoDB COMMENT='Shopping cart sessions';

-- ============================================================
-- TABLE: cart_items
-- ============================================================
DROP TABLE IF EXISTS cart_items;
CREATE TABLE cart_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id     INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED NOT NULL,
    bundle_id   INT UNSIGNED,
    quantity    INT NOT NULL DEFAULT 1,
    price       DECIMAL(12,2) NOT NULL,
    added_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (bundle_id) REFERENCES product_bundles(id) ON DELETE SET NULL,
    INDEX idx_cart (cart_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB COMMENT='Shopping cart line items';

-- ============================================================
-- TABLE: wishlist
-- ============================================================
DROP TABLE IF EXISTS wishlist;
CREATE TABLE wishlist (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED NOT NULL,
    note        TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (customer_id, product_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB COMMENT='Customer product wishlists';

-- ============================================================
-- TABLE: orders
-- ============================================================
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id         INT UNSIGNED NOT NULL,
    order_number        VARCHAR(30) NOT NULL UNIQUE,
    subtotal            DECIMAL(12,2) NOT NULL,
    tax_amount          DECIMAL(12,2) DEFAULT 0.00,
    shipping_cost       DECIMAL(12,2) DEFAULT 0.00,
    discount_amount     DECIMAL(12,2) DEFAULT 0.00,
    total               DECIMAL(12,2) NOT NULL,
    currency            CHAR(3) DEFAULT 'GHS',
    coupon_id           INT UNSIGNED,
    coupon_code         VARCHAR(50),
    points_used         INT DEFAULT 0,
    points_discount     DECIMAL(12,2) DEFAULT 0.00,
    points_earned       INT DEFAULT 0,
    status              ENUM('pending','processing','approved','packed','shipped','out_for_delivery','delivered','cancelled','returned') DEFAULT 'pending',
    payment_status      ENUM('pending','paid','partial','refunded','failed') DEFAULT 'pending',
    payment_method      VARCHAR(50),
    shipping_zone_id    INT UNSIGNED,
    shipping_name       VARCHAR(200),
    shipping_email      VARCHAR(255),
    shipping_phone      VARCHAR(30),
    shipping_address    TEXT,
    shipping_city       VARCHAR(100),
    shipping_state      VARCHAR(100),
    shipping_country    VARCHAR(100),
    shipping_postal     VARCHAR(20),
    tracking_number     VARCHAR(100),
    estimated_delivery  DATE,
    notes               TEXT,
    admin_notes         TEXT,
    ip_address          VARCHAR(45),
    user_agent          TEXT,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL,
    FOREIGN KEY (shipping_zone_id) REFERENCES shipping_zones(id) ON DELETE SET NULL,
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_order_number (order_number),
    INDEX idx_created (created_at)
) ENGINE=InnoDB COMMENT='Customer orders';

-- ============================================================
-- TABLE: order_items
-- ============================================================
DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    product_id   INT UNSIGNED NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    sku          VARCHAR(100),
    quantity     INT NOT NULL,
    unit_price   DECIMAL(12,2) NOT NULL,
    discount     DECIMAL(12,2) DEFAULT 0.00,
    total_price  DECIMAL(12,2) NOT NULL,
    tax_amount   DECIMAL(12,2) DEFAULT 0.00,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB COMMENT='Order line items';

-- ============================================================
-- TABLE: order_tracking
-- ============================================================
DROP TABLE IF EXISTS order_tracking;
CREATE TABLE order_tracking (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED NOT NULL,
    status      VARCHAR(100) NOT NULL,
    location    VARCHAR(200),
    description TEXT,
    user_id     INT UNSIGNED,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order (order_id)
) ENGINE=InnoDB COMMENT='Order delivery tracking history';

-- ============================================================
-- TABLE: payments
-- ============================================================
DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        INT UNSIGNED NOT NULL,
    transaction_id  VARCHAR(150) UNIQUE,
    payment_method  ENUM('cash_on_delivery','mobile_money','credit_card','bank_transfer','loyalty_points') NOT NULL,
    gateway         VARCHAR(100),
    amount          DECIMAL(12,2) NOT NULL,
    currency        CHAR(3) DEFAULT 'GHS',
    status          ENUM('pending','completed','failed','refunded','partial') DEFAULT 'pending',
    payment_date    DATETIME,
    gateway_ref     VARCHAR(200),
    gateway_status  VARCHAR(100),
    receipt_url     VARCHAR(500),
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT,
    INDEX idx_order (order_id),
    INDEX idx_status (status),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB COMMENT='Payment transactions';

-- ============================================================
-- TABLE: invoices
-- ============================================================
DROP TABLE IF EXISTS invoices;
CREATE TABLE invoices (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id       INT UNSIGNED NOT NULL UNIQUE,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    issued_date    DATE NOT NULL,
    due_date       DATE,
    subtotal       DECIMAL(12,2),
    tax_amount     DECIMAL(12,2),
    total          DECIMAL(12,2),
    notes          TEXT,
    pdf_path       VARCHAR(255),
    status         ENUM('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_invoice_number (invoice_number)
) ENGINE=InnoDB COMMENT='Customer invoices';

-- ============================================================
-- TABLE: returns
-- ============================================================
DROP TABLE IF EXISTS returns;
CREATE TABLE returns (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id       INT UNSIGNED NOT NULL,
    customer_id    INT UNSIGNED NOT NULL,
    return_number  VARCHAR(50) NOT NULL UNIQUE,
    reason         ENUM('defective','wrong_item','not_as_described','changed_mind','damaged_in_transit','other') NOT NULL,
    description    TEXT,
    refund_type    ENUM('full','partial','store_credit','exchange') DEFAULT 'full',
    refund_amount  DECIMAL(12,2) DEFAULT 0.00,
    status         ENUM('pending','approved','rejected','processing','completed') DEFAULT 'pending',
    images         TEXT,
    admin_notes    TEXT,
    processed_by   INT UNSIGNED,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status)
) ENGINE=InnoDB COMMENT='Product return requests';

-- ============================================================
-- TABLE: return_items
-- ============================================================
DROP TABLE IF EXISTS return_items;
CREATE TABLE return_items (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_id     INT UNSIGNED NOT NULL,
    order_item_id INT UNSIGNED NOT NULL,
    quantity      INT NOT NULL,
    reason        TEXT,
    `condition`  ENUM('unopened','opened','damaged','missing_parts') DEFAULT 'opened',
    FOREIGN KEY (return_id) REFERENCES returns(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE RESTRICT,
    INDEX idx_return (return_id)
) ENGINE=InnoDB COMMENT='Items in return requests';

-- ============================================================
-- TABLE: reviews
-- ============================================================
DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id    INT UNSIGNED NOT NULL,
    customer_id   INT UNSIGNED NOT NULL,
    order_id      INT UNSIGNED,
    rating        TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title         VARCHAR(255),
    review_text   TEXT,
    pros          TEXT,
    cons          TEXT,
    images        TEXT,
    is_verified   TINYINT(1) DEFAULT 0,
    status        ENUM('pending','approved','rejected') DEFAULT 'pending',
    helpful_count INT DEFAULT 0,
    not_helpful   INT DEFAULT 0,
    admin_reply   TEXT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    UNIQUE KEY unique_review (product_id, customer_id),
    INDEX idx_product (product_id),
    INDEX idx_status (status),
    INDEX idx_rating (rating)
) ENGINE=InnoDB COMMENT='Product customer reviews';

-- ============================================================
-- TABLE: loyalty_points_log
-- ============================================================
DROP TABLE IF EXISTS loyalty_points_log;
CREATE TABLE loyalty_points_log (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id      INT UNSIGNED NOT NULL,
    order_id         INT UNSIGNED,
    points           INT NOT NULL,
    transaction_type ENUM('earned','redeemed','bonus','expired','adjusted','refunded') NOT NULL,
    description      TEXT,
    balance_after    INT NOT NULL,
    expires_at       DATE,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_customer (customer_id),
    INDEX idx_type (transaction_type)
) ENGINE=InnoDB COMMENT='Loyalty points transaction history';

-- ============================================================
-- TABLE: notifications
-- ============================================================
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED,
    type       VARCHAR(100) NOT NULL,
    title      VARCHAR(255) NOT NULL,
    message    TEXT,
    icon       VARCHAR(100),
    color      VARCHAR(20),
    link       VARCHAR(500),
    is_read    TINYINT(1) DEFAULT 0,
    read_at    DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB COMMENT='System notifications for users';

-- ============================================================
-- TABLE: newsletter_subscribers
-- ============================================================
DROP TABLE IF EXISTS newsletter_subscribers;
CREATE TABLE newsletter_subscribers (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(255) NOT NULL UNIQUE,
    name          VARCHAR(200),
    customer_id   INT UNSIGNED,
    preferences   JSON,
    status        ENUM('active','unsubscribed','bounced') DEFAULT 'active',
    token         VARCHAR(100),
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at DATETIME,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB COMMENT='Newsletter subscribers';

-- ============================================================
-- TABLE: email_queue
-- ============================================================
DROP TABLE IF EXISTS email_queue;
CREATE TABLE email_queue (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    to_email    VARCHAR(255) NOT NULL,
    to_name     VARCHAR(200),
    subject     VARCHAR(500) NOT NULL,
    body        LONGTEXT NOT NULL,
    attachments TEXT,
    priority    TINYINT DEFAULT 1,
    attempts    INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    status      ENUM('pending','sent','failed','cancelled') DEFAULT 'pending',
    error_msg   TEXT,
    scheduled_at DATETIME,
    sent_at     DATETIME,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_scheduled (scheduled_at)
) ENGINE=InnoDB COMMENT='Email sending queue';

-- ============================================================
-- TABLE: sms_queue
-- ============================================================
DROP TABLE IF EXISTS sms_queue;
CREATE TABLE sms_queue (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(30) NOT NULL,
    message      TEXT NOT NULL,
    provider     VARCHAR(50) DEFAULT 'mnotify',
    status       ENUM('pending','sent','failed') DEFAULT 'pending',
    response     TEXT,
    attempts     INT DEFAULT 0,
    sent_at      DATETIME,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB COMMENT='SMS sending queue';

-- ============================================================
-- TABLE: recently_viewed
-- ============================================================
DROP TABLE IF EXISTS recently_viewed;
CREATE TABLE recently_viewed (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED,
    session_id  VARCHAR(255),
    product_id  INT UNSIGNED NOT NULL,
    viewed_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_view (customer_id, product_id),
    INDEX idx_customer (customer_id),
    INDEX idx_session (session_id)
) ENGINE=InnoDB COMMENT='Recently viewed products tracking';

-- ============================================================
-- TABLE: abandoned_carts
-- ============================================================
DROP TABLE IF EXISTS abandoned_carts;
CREATE TABLE abandoned_carts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id         INT UNSIGNED,
    customer_id     INT UNSIGNED,
    email           VARCHAR(255),
    items_count     INT,
    cart_total      DECIMAL(12,2),
    reminder_count  INT DEFAULT 0,
    last_reminder   DATETIME,
    recovered       TINYINT(1) DEFAULT 0,
    recovered_at    DATETIME,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_recovered (recovered)
) ENGINE=InnoDB COMMENT='Abandoned cart recovery tracking';

-- ============================================================
-- TABLE: search_logs
-- ============================================================
DROP TABLE IF EXISTS search_logs;
CREATE TABLE search_logs (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    search_term   VARCHAR(500) NOT NULL,
    results_count INT DEFAULT 0,
    customer_id   INT UNSIGNED,
    session_id    VARCHAR(255),
    ip_address    VARCHAR(45),
    clicked_id    INT UNSIGNED,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_term (search_term(100)),
    INDEX idx_created (created_at)
) ENGINE=InnoDB COMMENT='Search analytics log';

-- ============================================================
-- TABLE: product_views
-- ============================================================
DROP TABLE IF EXISTS product_views;
CREATE TABLE product_views (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED,
    session_id  VARCHAR(255),
    ip_address  VARCHAR(45),
    referrer    VARCHAR(500),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB COMMENT='Product page view analytics';

-- ============================================================
-- TABLE: audit_logs
-- ============================================================
DROP TABLE IF EXISTS audit_logs;
CREATE TABLE audit_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED,
    action      VARCHAR(100) NOT NULL,
    module      VARCHAR(100),
    table_name  VARCHAR(100),
    record_id   INT UNSIGNED,
    old_values  JSON,
    new_values  JSON,
    ip_address  VARCHAR(45),
    user_agent  TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_module (module),
    INDEX idx_created (created_at)
) ENGINE=InnoDB COMMENT='Comprehensive audit trail';

-- ============================================================
-- TABLE: settings
-- ============================================================
DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key   VARCHAR(150) NOT NULL UNIQUE,
    setting_value LONGTEXT,
    setting_group VARCHAR(100) DEFAULT 'general',
    label         VARCHAR(200),
    description   TEXT,
    input_type    VARCHAR(50) DEFAULT 'text',
    options       JSON,
    is_public     TINYINT(1) DEFAULT 0,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_group (setting_group),
    INDEX idx_key (setting_key)
) ENGINE=InnoDB COMMENT='Application settings';

-- ============================================================
-- TABLE: backup_logs
-- ============================================================
DROP TABLE IF EXISTS backup_logs;
CREATE TABLE backup_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_name   VARCHAR(255) NOT NULL,
    file_path   VARCHAR(500),
    file_size   BIGINT DEFAULT 0,
    backup_type ENUM('manual','scheduled','auto') DEFAULT 'manual',
    status      ENUM('success','failed','in_progress') DEFAULT 'success',
    notes       TEXT,
    created_by  INT UNSIGNED,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB COMMENT='Database backup history';

-- ============================================================
-- VIEWS
-- ============================================================

-- View: Product with category and brand info
CREATE OR REPLACE VIEW vw_products AS
SELECT
    p.id, p.product_name, p.slug, p.model, p.sku, p.barcode,
    p.price, p.compare_price, p.quantity, p.status,
    p.is_featured, p.is_new_arrival, p.is_best_seller,
    p.avg_rating, p.review_count, p.views, p.total_sold,
    p.warranty_months, p.short_description,
    c.category_name, c.slug AS category_slug,
    b.brand_name, b.slug AS brand_slug, b.logo AS brand_logo,
    pi.image_path AS primary_image,
    fs.sale_price AS flash_price, fs.end_time AS flash_end,
    CASE WHEN fs.id IS NOT NULL AND fs.status='active' AND NOW() BETWEEN fs.start_time AND fs.end_time
         THEN fs.sale_price ELSE p.price END AS effective_price
FROM products p
JOIN categories c ON p.category_id = c.id
JOIN brands b ON p.brand_id = b.id
LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
LEFT JOIN flash_sales fs ON p.id = fs.product_id AND fs.status = 'active';

-- View: Order summary
CREATE OR REPLACE VIEW vw_orders AS
SELECT
    o.*,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
    c.phone AS customer_phone,
    u.email AS customer_email,
    COUNT(oi.id) AS item_count,
    p.transaction_id, p.payment_method AS pay_method
FROM orders o
JOIN customers c ON o.customer_id = c.id
JOIN users u ON c.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN payments p ON o.id = p.order_id AND p.status = 'completed'
GROUP BY o.id;

-- View: Low stock products
CREATE OR REPLACE VIEW vw_low_stock AS
SELECT p.id, p.product_name, p.sku, p.quantity, p.low_stock_alert,
       c.category_name, b.brand_name, s.supplier_name
FROM products p
JOIN categories c ON p.category_id = c.id
JOIN brands b ON p.brand_id = b.id
LEFT JOIN suppliers s ON p.supplier_id = s.id
WHERE p.quantity <= p.low_stock_alert AND p.status = 'active';

-- View: Dashboard stats
CREATE OR REPLACE VIEW vw_dashboard_stats AS
SELECT
    (SELECT COUNT(*) FROM products WHERE status='active') AS total_products,
    (SELECT COUNT(*) FROM customers) AS total_customers,
    (SELECT COUNT(*) FROM orders) AS total_orders,
    (SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid') AS total_revenue,
    (SELECT COUNT(*) FROM orders WHERE status='pending') AS pending_orders,
    (SELECT COUNT(*) FROM products WHERE quantity <= low_stock_alert AND status='active') AS low_stock_count,
    (SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()) AS today_orders,
    (SELECT COALESCE(SUM(total),0) FROM orders WHERE DATE(created_at)=CURDATE() AND payment_status='paid') AS today_revenue;

-- ============================================================
-- STORED PROCEDURES
-- ============================================================

DELIMITER //

-- Procedure: Place Order
CREATE PROCEDURE sp_place_order(
    IN p_customer_id INT UNSIGNED,
    IN p_shipping_address TEXT,
    IN p_payment_method VARCHAR(50),
    IN p_coupon_code VARCHAR(50),
    IN p_points_to_use INT,
    IN p_notes TEXT,
    OUT p_order_id INT UNSIGNED,
    OUT p_order_number VARCHAR(30),
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_cart_id INT UNSIGNED;
    DECLARE v_subtotal DECIMAL(12,2) DEFAULT 0;
    DECLARE v_discount DECIMAL(12,2) DEFAULT 0;
    DECLARE v_tax DECIMAL(12,2) DEFAULT 0;
    DECLARE v_shipping DECIMAL(12,2) DEFAULT 0;
    DECLARE v_total DECIMAL(12,2) DEFAULT 0;
    DECLARE v_coupon_id INT UNSIGNED DEFAULT NULL;
    DECLARE v_points_discount DECIMAL(12,2) DEFAULT 0;
    DECLARE v_points_earned INT DEFAULT 0;
    DECLARE v_order_num VARCHAR(30);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'Order failed due to a database error.';
    END;

    START TRANSACTION;

    -- Get cart
    SELECT id INTO v_cart_id FROM cart WHERE customer_id = p_customer_id LIMIT 1;

    -- Calculate subtotal
    SELECT COALESCE(SUM(ci.quantity * ci.price), 0) INTO v_subtotal
    FROM cart_items ci WHERE ci.cart_id = v_cart_id;

    IF v_subtotal = 0 THEN
        SET p_message = 'Cart is empty.';
        ROLLBACK;
        LEAVE sp_place_order;
    END IF;

    -- Apply coupon
    IF p_coupon_code IS NOT NULL AND p_coupon_code != '' THEN
        SELECT id INTO v_coupon_id FROM coupons
        WHERE coupon_code = p_coupon_code AND is_active = 1
          AND (expiry_date IS NULL OR expiry_date >= NOW())
          AND (max_uses IS NULL OR used_count < max_uses)
          AND min_order_amount <= v_subtotal LIMIT 1;

        IF v_coupon_id IS NOT NULL THEN
            SELECT
                CASE discount_type
                    WHEN 'percentage' THEN LEAST(v_subtotal * discount_value / 100, COALESCE(max_discount, 999999))
                    WHEN 'fixed' THEN LEAST(discount_value, v_subtotal)
                    ELSE 0 END INTO v_discount
            FROM coupons WHERE id = v_coupon_id;

            UPDATE coupons SET used_count = used_count + 1 WHERE id = v_coupon_id;
        END IF;
    END IF;

    -- Loyalty points discount (100 points = 1 GHS)
    IF p_points_to_use > 0 THEN
        SET v_points_discount = p_points_to_use / 100;
    END IF;

    -- Tax (default 12.5% VAT in Ghana)
    SET v_tax = (v_subtotal - v_discount) * 0.125;

    -- Calculate total
    SET v_total = v_subtotal - v_discount - v_points_discount + v_tax + v_shipping;
    IF v_total < 0 THEN SET v_total = 0; END IF;

    -- Generate order number
    SET v_order_num = CONCAT('ORD-', YEAR(NOW()), MONTH(NOW()), '-', LPAD(FLOOR(RAND()*99999), 5, '0'));

    -- Points earned (1 point per 5 GHS)
    SET v_points_earned = FLOOR(v_total / 5);

    -- Insert order
    INSERT INTO orders (customer_id, order_number, subtotal, tax_amount, shipping_cost,
                        discount_amount, total, coupon_id, coupon_code, points_used,
                        points_discount, points_earned, payment_method, status,
                        shipping_address, notes)
    VALUES (p_customer_id, v_order_num, v_subtotal, v_tax, v_shipping,
            v_discount, v_total, v_coupon_id, p_coupon_code, p_points_to_use,
            v_points_discount, v_points_earned, p_payment_method, 'pending',
            p_shipping_address, p_notes);

    SET p_order_id = LAST_INSERT_ID();

    -- Copy cart items to order items
    INSERT INTO order_items (order_id, product_id, product_name, sku, quantity, unit_price, total_price)
    SELECT p_order_id, ci.product_id, p.product_name, p.sku, ci.quantity, ci.price, ci.quantity * ci.price
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.cart_id = v_cart_id;

    -- Update product stock
    UPDATE products p
    JOIN cart_items ci ON p.id = ci.product_id
    SET p.quantity = p.quantity - ci.quantity,
        p.total_sold = p.total_sold + ci.quantity
    WHERE ci.cart_id = v_cart_id;

    -- Record inventory out
    INSERT INTO inventory (product_id, transaction_type, quantity, previous_stock, new_stock, reference_no, notes)
    SELECT ci.product_id, 'stock_out', ci.quantity,
           p.quantity + ci.quantity, p.quantity, v_order_num, 'Sale order'
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.cart_id = v_cart_id;

    -- Award loyalty points
    UPDATE customers SET loyalty_points = loyalty_points + v_points_earned - p_points_to_use,
                         total_orders = total_orders + 1,
                         total_spent = total_spent + v_total
    WHERE id = p_customer_id;

    -- Log points
    INSERT INTO loyalty_points_log (customer_id, order_id, points, transaction_type, description, balance_after)
    SELECT p_customer_id, p_order_id, v_points_earned, 'earned',
           CONCAT('Points earned from order ', v_order_num),
           (SELECT loyalty_points FROM customers WHERE id = p_customer_id);

    -- Clear cart
    DELETE FROM cart_items WHERE cart_id = v_cart_id;

    -- Track abandoned cart as recovered
    UPDATE abandoned_carts SET recovered = 1, recovered_at = NOW()
    WHERE customer_id = p_customer_id AND recovered = 0;

    -- Insert order tracking
    INSERT INTO order_tracking (order_id, status, description)
    VALUES (p_order_id, 'Order Placed', CONCAT('Order ', v_order_num, ' placed successfully'));

    SET p_order_number = v_order_num;
    SET p_message = 'Order placed successfully.';
    COMMIT;
END //

-- Procedure: Generate Sales Report
CREATE PROCEDURE sp_sales_report(
    IN p_start_date DATE,
    IN p_end_date DATE,
    IN p_group_by VARCHAR(20)
)
BEGIN
    IF p_group_by = 'daily' THEN
        SELECT DATE(created_at) AS period,
               COUNT(*) AS total_orders,
               SUM(total) AS revenue,
               AVG(total) AS avg_order,
               SUM(CASE WHEN status='delivered' THEN 1 ELSE 0 END) AS completed
        FROM orders
        WHERE DATE(created_at) BETWEEN p_start_date AND p_end_date
          AND payment_status = 'paid'
        GROUP BY DATE(created_at) ORDER BY period;
    ELSEIF p_group_by = 'weekly' THEN
        SELECT YEARWEEK(created_at) AS period,
               COUNT(*) AS total_orders,
               SUM(total) AS revenue,
               AVG(total) AS avg_order,
               SUM(CASE WHEN status='delivered' THEN 1 ELSE 0 END) AS completed
        FROM orders
        WHERE DATE(created_at) BETWEEN p_start_date AND p_end_date
          AND payment_status = 'paid'
        GROUP BY YEARWEEK(created_at) ORDER BY period;
    ELSE
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS period,
               COUNT(*) AS total_orders,
               SUM(total) AS revenue,
               AVG(total) AS avg_order,
               SUM(CASE WHEN status='delivered' THEN 1 ELSE 0 END) AS completed
        FROM orders
        WHERE DATE(created_at) BETWEEN p_start_date AND p_end_date
          AND payment_status = 'paid'
        GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY period;
    END IF;
END //

-- Procedure: Update Customer Tier
CREATE PROCEDURE sp_update_customer_tier(IN p_customer_id INT UNSIGNED)
BEGIN
    DECLARE v_points INT;
    DECLARE v_tier_id INT UNSIGNED;

    SELECT loyalty_points INTO v_points FROM customers WHERE id = p_customer_id;

    SELECT id INTO v_tier_id FROM customer_tiers
    WHERE min_points <= v_points AND max_points >= v_points
      AND is_active = 1 LIMIT 1;

    IF v_tier_id IS NOT NULL THEN
        UPDATE customers SET tier_id = v_tier_id WHERE id = p_customer_id;
    END IF;
END //

DELIMITER ;

-- ============================================================
-- TRIGGERS
-- ============================================================

DELIMITER //

-- Trigger: After inventory stock_in update product quantity
CREATE TRIGGER trg_after_inventory_insert
AFTER INSERT ON inventory
FOR EACH ROW
BEGIN
    IF NEW.transaction_type = 'stock_in' THEN
        UPDATE products SET quantity = NEW.new_stock WHERE id = NEW.product_id;
    END IF;
    IF NEW.transaction_type = 'adjustment' THEN
        UPDATE products SET quantity = NEW.new_stock WHERE id = NEW.product_id;
    END IF;
END //

-- Trigger: After review approved, update product avg_rating
CREATE TRIGGER trg_after_review_update
AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' AND OLD.status != 'approved' THEN
        UPDATE products p SET
            avg_rating = (SELECT AVG(rating) FROM reviews WHERE product_id = NEW.product_id AND status='approved'),
            review_count = (SELECT COUNT(*) FROM reviews WHERE product_id = NEW.product_id AND status='approved')
        WHERE p.id = NEW.product_id;
    END IF;
END //

-- Trigger: After order delivered, award bonus points
CREATE TRIGGER trg_after_order_delivered
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status = 'delivered' AND OLD.status != 'delivered' THEN
        UPDATE customers SET loyalty_points = loyalty_points + NEW.points_earned
        WHERE id = NEW.customer_id;

        INSERT INTO loyalty_points_log (customer_id, order_id, points, transaction_type, description, balance_after)
        SELECT NEW.customer_id, NEW.id, NEW.points_earned, 'earned',
               CONCAT('Points confirmed for delivered order ', NEW.order_number),
               (SELECT loyalty_points FROM customers WHERE id = NEW.customer_id);
    END IF;
END //

-- Trigger: After review insert
CREATE TRIGGER trg_after_review_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE products SET
            avg_rating = (SELECT AVG(rating) FROM reviews WHERE product_id = NEW.product_id AND status='approved'),
            review_count = (SELECT COUNT(*) FROM reviews WHERE product_id = NEW.product_id AND status='approved')
        WHERE id = NEW.product_id;
    END IF;
END //

-- Trigger: Flash sale sold_count update
CREATE TRIGGER trg_after_order_item_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE flash_sales SET sold_count = sold_count + NEW.quantity
    WHERE product_id = NEW.product_id AND status = 'active'
      AND NOW() BETWEEN start_time AND end_time;
END //

DELIMITER ;

-- ============================================================
-- SEED DATA: Roles
-- ============================================================
INSERT INTO roles (role_name, slug, permissions, description) VALUES
('Administrator', 'admin', '{"all":true}', 'Full system access'),
('Inventory Manager', 'inventory_manager', '{"products":true,"inventory":true,"suppliers":true,"reports":["inventory"]}', 'Manages stock and suppliers'),
('Sales Officer', 'sales_officer', '{"orders":true,"customers":true,"reports":["sales"],"coupons":true}', 'Handles orders and customers'),
('Customer', 'customer', '{"shop":true,"orders":true,"reviews":true,"profile":true}', 'Regular customer'),
('Guest', 'guest', '{"shop":true}', 'Guest browsing access');

-- ============================================================
-- SEED DATA: Users (Admin)
-- ============================================================
INSERT INTO users (role_id, username, email, password, full_name, phone, status, email_verified) VALUES
(1, 'admin', 'admin@electrostore.com', '$2y$12$YKiN7OFZ1ZNiZn0FOcRRIeIg8kJWsOBP0/fXpO72CAnuGnPF/nQwq', 'System Administrator', '+233241234567', 'active', 1),
(2, 'inventory1', 'inventory@electrostore.com', '$2y$12$YKiN7OFZ1ZNiZn0FOcRRIeIg8kJWsOBP0/fXpO72CAnuGnPF/nQwq', 'John Mensah', '+233241234568', 'active', 1),
(3, 'sales1', 'sales@electrostore.com', '$2y$12$YKiN7OFZ1ZNiZn0FOcRRIeIg8kJWsOBP0/fXpO72CAnuGnPF/nQwq', 'Ama Owusu', '+233241234569', 'active', 1),
(4, 'customer1', 'customer1@gmail.com', '$2y$12$YKiN7OFZ1ZNiZn0FOcRRIeIg8kJWsOBP0/fXpO72CAnuGnPF/nQwq', 'Kwame Asante', '+233241234570', 'active', 1),
(4, 'customer2', 'customer2@gmail.com', '$2y$12$YKiN7OFZ1ZNiZn0FOcRRIeIg8kJWsOBP0/fXpO72CAnuGnPF/nQwq', 'Abena Boateng', '+233241234571', 'active', 1);
-- Default password for all: admin123

-- ============================================================
-- SEED DATA: Customer Tiers
-- ============================================================
INSERT INTO customer_tiers (tier_name, min_points, max_points, discount_percentage, cashback_rate, free_shipping, priority_support, badge_color, badge_icon, benefits) VALUES
('Bronze', 0, 999, 0.00, 0.00, 0, 0, '#CD7F32', 'fa-medal', 'Basic membership benefits'),
('Silver', 1000, 4999, 2.00, 1.00, 0, 0, '#C0C0C0', 'fa-medal', 'Silver benefits: 2% discount, 1% cashback'),
('Gold', 5000, 14999, 5.00, 2.00, 1, 0, '#FFD700', 'fa-award', 'Gold benefits: 5% discount, 2% cashback, Free shipping'),
('Platinum', 15000, 999999, 10.00, 5.00, 1, 1, '#E5E4E2', 'fa-crown', 'Platinum elite: 10% discount, 5% cashback, Free shipping, Priority support');

-- ============================================================
-- SEED DATA: Customers
-- ============================================================
INSERT INTO customers (user_id, first_name, last_name, phone, date_of_birth, gender, address, city, country, loyalty_points, tier_id) VALUES
(4, 'Kwame', 'Asante', '+233241234570', '1995-04-15', 'male', '12 Independence Ave', 'Accra', 'Ghana', 2500, 2),
(5, 'Abena', 'Boateng', '+233241234571', '1998-07-22', 'female', '45 Kumasi Road', 'Kumasi', 'Ghana', 500, 1);

-- ============================================================
-- SEED DATA: Currencies
-- ============================================================
INSERT INTO currencies (currency_code, currency_name, symbol, exchange_rate, is_default) VALUES
('GHS', 'Ghanaian Cedi', '₵', 1.000000, 1),
('USD', 'US Dollar', '$', 0.067, 0),
('EUR', 'Euro', '€', 0.062, 0),
('GBP', 'British Pound', '£', 0.053, 0);

-- ============================================================
-- SEED DATA: Taxes
-- ============================================================
INSERT INTO taxes (tax_name, rate, country, is_default) VALUES
('Ghana VAT (NHIL+GETFund)', 12.50, 'Ghana', 1),
('No Tax', 0.00, 'Export', 0);

-- ============================================================
-- SEED DATA: Shipping Zones
-- ============================================================
INSERT INTO shipping_zones (zone_name, countries, base_rate, per_kg_rate, free_above, est_days_min, est_days_max) VALUES
('Greater Accra', 'Ghana-AccraRegion', 20.00, 2.00, 500.00, 1, 2),
('Ashanti Region', 'Ghana-AshantiRegion', 35.00, 3.00, 800.00, 2, 3),
('Other Regions Ghana', 'Ghana-Other', 50.00, 4.00, 1000.00, 3, 5),
('International', 'International', 150.00, 8.00, NULL, 7, 14);

-- ============================================================
-- SEED DATA: Brands
-- ============================================================
INSERT INTO brands (brand_name, slug, website, description, country, is_featured) VALUES
('Apple', 'apple', 'https://apple.com', 'Think Different — premium consumer electronics', 'USA', 1),
('Samsung', 'samsung', 'https://samsung.com', 'Leader in mobile and display technology', 'South Korea', 1),
('HP', 'hp', 'https://hp.com', 'Trusted computing and printing solutions', 'USA', 1),
('Dell', 'dell', 'https://dell.com', 'Enterprise and consumer computing solutions', 'USA', 1),
('Lenovo', 'lenovo', 'https://lenovo.com', 'Smart technology for smarter people', 'China', 1),
('Sony', 'sony', 'https://sony.com', 'Audio, visual and gaming innovation', 'Japan', 1),
('LG', 'lg', 'https://lg.com', 'Life is Good — electronics and appliances', 'South Korea', 0),
('Canon', 'canon', 'https://canon.com', 'Professional camera and imaging solutions', 'Japan', 0),
('Nikon', 'nikon', 'https://nikon.com', 'I am Nikon — precision optics', 'Japan', 0),
('Xiaomi', 'xiaomi', 'https://mi.com', 'Innovation for everyone', 'China', 1),
('Huawei', 'huawei', 'https://huawei.com', 'Building a fully connected world', 'China', 0),
('Tecno', 'tecno', 'https://tecno-mobile.com', 'Stop at Nothing — Africa-focused devices', 'Ghana/China', 1),
('Infinix', 'infinix', 'https://infinixmobility.com', 'Dare to Leap — budget smartphones', 'Hong Kong', 1),
('Logitech', 'logitech', 'https://logitech.com', 'Design for people — peripherals', 'Switzerland', 0),
('JBL', 'jbl', 'https://jbl.com', 'Legendary sound — audio equipment', 'USA', 0);

-- ============================================================
-- SEED DATA: Categories
-- ============================================================
INSERT INTO categories (parent_id, category_name, slug, icon, description, is_featured, sort_order) VALUES
(NULL, 'Smartphones', 'smartphones', 'fa-mobile-alt', 'Latest mobile phones and handsets', 1, 1),
(NULL, 'Laptops', 'laptops', 'fa-laptop', 'Portable computers for work and gaming', 1, 2),
(NULL, 'Tablets', 'tablets', 'fa-tablet-alt', 'iPad, Android tablets and e-readers', 1, 3),
(NULL, 'Smartwatches', 'smartwatches', 'fa-clock', 'Wearable technology and fitness trackers', 1, 4),
(NULL, 'Desktop Computers', 'desktops', 'fa-desktop', 'All-in-one and tower desktop systems', 1, 5),
(NULL, 'Printers', 'printers', 'fa-print', 'Inkjet, laser and multifunction printers', 0, 6),
(NULL, 'Cameras', 'cameras', 'fa-camera', 'DSLR, mirrorless and action cameras', 1, 7),
(NULL, 'Gaming Consoles', 'gaming', 'fa-gamepad', 'PlayStation, Xbox, Nintendo gaming systems', 1, 8),
(NULL, 'Networking', 'networking', 'fa-wifi', 'Routers, switches and access points', 0, 9),
(NULL, 'Audio Equipment', 'audio', 'fa-headphones', 'Headphones, speakers and earbuds', 1, 10),
(NULL, 'Accessories', 'accessories', 'fa-plug', 'Cases, chargers and cables', 0, 11),
(2, 'Gaming Laptops', 'gaming-laptops', 'fa-gamepad', 'High-performance gaming laptops', 1, 1),
(2, 'Business Laptops', 'business-laptops', 'fa-briefcase', 'Professional business laptops', 0, 2),
(1, 'Budget Smartphones', 'budget-smartphones', 'fa-mobile', 'Affordable smartphones under GHS 1500', 0, 1),
(1, 'Flagship Smartphones', 'flagship-smartphones', 'fa-star', 'Premium flagship devices', 0, 2);

-- ============================================================
-- SEED DATA: Suppliers
-- ============================================================
INSERT INTO suppliers (supplier_name, contact_person, email, phone, address, city, country, payment_terms, lead_time_days, rating) VALUES
('Tech Imports Ghana Ltd', 'Kofi Acheampong', 'imports@techgh.com', '+233302123456', '5 Liberation Road', 'Accra', 'Ghana', 'Net 30', 7, 4.5),
('ElectroWorld Distributors', 'Yaw Darko', 'orders@electroworld.gh', '+233244567890', 'Ring Road Central', 'Accra', 'Ghana', 'Net 15', 5, 4.2),
('Global Tech Supplies', 'James Asante', 'sales@globaltech.com', '+1-555-0123', '123 Tech Blvd', 'New York', 'USA', 'Net 60', 21, 4.8),
('Asian Electronics Hub', 'Li Wei', 'supply@asiahub.com', '+86-10-12345678', 'Shenzhen Industrial Park', 'Shenzhen', 'China', 'Prepaid', 35, 4.0);

-- ============================================================
-- SEED DATA: Products
-- ============================================================
INSERT INTO products (category_id, brand_id, supplier_id, product_name, slug, model, sku, barcode, price, compare_price, cost_price, quantity, low_stock_alert, weight_kg, description, short_description, specifications, warranty_months, is_featured, is_new_arrival, is_best_seller, status, views, total_sold) VALUES

(1, 2, 1, 'Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra', 'SM-S928B', 'SAM-S24U-256', '5901234567890', 8999.00, 9999.00, 6500.00, 25, 5, 0.232,
'Experience the ultimate Samsung flagship with a built-in S Pen, 200MP camera system, and Snapdragon 8 Gen 3 processor for unmatched performance.',
'Samsung flagship with 200MP camera and S Pen',
'{"display":"6.8 inch Dynamic AMOLED 2X, 120Hz","processor":"Snapdragon 8 Gen 3","ram":"12GB","storage":"256GB","camera":"200MP main + 12MP ultra + 10MP 3x + 10MP 5x","battery":"5000mAh","os":"Android 14","5G":true}',
24, 1, 1, 1, 'active', 2450, 180),

(1, 1, 1, 'Apple iPhone 15 Pro Max', 'apple-iphone-15-pro-max', 'A3108', 'APL-IP15PM-256', '5901234567891', 12999.00, 14500.00, 9800.00, 15, 3, 0.221,
'The most powerful iPhone ever, featuring a 48MP camera system, A17 Pro chip, and titanium design. Shoot cinematic 4K ProRes video directly to USB-C.',
'Apple iPhone with A17 Pro chip and titanium build',
'{"display":"6.7 inch Super Retina XDR ProMotion 120Hz","chip":"A17 Pro","ram":"8GB","storage":"256GB","camera":"48MP main + 12MP ultra + 12MP 5x telephoto","battery":"4422mAh","os":"iOS 17","5G":true}',
12, 1, 1, 1, 'active', 3102, 245),

(2, 3, 2, 'HP Pavilion 15 Laptop', 'hp-pavilion-15-laptop', 'EH3020WM', 'HP-PAV15-I5', '5901234567892', 4599.00, 5200.00, 3200.00, 40, 8, 1.75,
'The HP Pavilion 15 delivers everyday performance for students and professionals. Features 12th Gen Intel Core i5, 8GB RAM, and a Full HD display.',
'HP 15.6" laptop with Intel Core i5 and SSD',
'{"display":"15.6 inch Full HD IPS 250nits","processor":"Intel Core i5-1235U","ram":"8GB DDR4","storage":"512GB NVMe SSD","graphics":"Intel Iris Xe","battery":"41Wh, up to 8hrs","os":"Windows 11 Home","ports":"USB-A x2, USB-C, HDMI, SD card"}',
12, 1, 1, 0, 'active', 1820, 120),

(2, 5, 2, 'Lenovo IdeaPad 3 Chromebook', 'lenovo-ideapad-3-chromebook', 'CB14IGL05', 'LEN-CB14-N4020', '5901234567893', 1999.00, 2500.00, 1400.00, 30, 5, 1.5,
'Perfect for students, the Lenovo Chromebook features a lightweight design, all-day battery life, and seamless Google integration.',
'Lenovo 14" Chromebook for students',
'{"display":"14 inch HD TN","processor":"Intel Celeron N4020","ram":"4GB LPDDR4X","storage":"64GB eMMC","battery":"42Wh, up to 10hrs","os":"Chrome OS","webcam":"720p"}',
12, 0, 0, 0, 'active', 892, 67),

(3, 1, 1, 'Apple iPad Pro M2 12.9"', 'apple-ipad-pro-m2-129', 'MNXR3', 'APL-IPDPM2-128', '5901234567894', 9499.00, 10999.00, 7000.00, 18, 4, 0.682,
'The most advanced iPad ever. M2 chip. Stunning Liquid Retina XDR display. Works with Apple Pencil 2 and Magic Keyboard.',
'Apple iPad Pro with M2 chip, 12.9 inch Liquid Retina XDR',
'{"display":"12.9 inch Liquid Retina XDR","chip":"Apple M2","ram":"8GB","storage":"128GB","camera":"12MP Wide + 10MP Ultra Wide","battery":"10541mAh","os":"iPadOS 16","connectivity":"Wi-Fi 6E, Bluetooth 5.3"}',
12, 1, 1, 0, 'active', 1543, 89),

(4, 2, 1, 'Samsung Galaxy Watch 6 Classic', 'samsung-galaxy-watch-6-classic', 'SM-R960NZSAXFE', 'SAM-GW6C-47', '5901234567895', 3299.00, 3799.00, 2300.00, 22, 5, 0.059,
'Monitor your health with the Samsung Galaxy Watch 6 Classic. Advanced sleep tracking, ECG, blood pressure monitoring, and sapphire crystal glass protection.',
'Samsung premium smartwatch with health monitoring',
'{"display":"1.5 inch Super AMOLED 480x480","processor":"Exynos W930 Dual-core 1.4GHz","ram":"2GB","storage":"16GB","battery":"425mAh, up to 3 days","sensors":"ECG, Blood Pressure, SpO2, Skin Temperature","os":"Wear OS 4 with One UI Watch 5","water_resistance":"5ATM + IP68"}',
24, 1, 1, 1, 'active', 1120, 95),

(7, 8, 3, 'Canon EOS R50 Mirrorless Camera', 'canon-eos-r50-mirrorless', 'R50BLKIT18-45', 'CAN-EOSR50-KIT', '5901234567896', 6799.00, 7500.00, 5000.00, 12, 3, 0.375,
'Perfect entry into mirrorless photography. 24.2MP APS-C sensor, 4K video, Eye Detect AF, and Creative Assist for stunning results.',
'Canon mirrorless 24.2MP with 4K video',
'{"sensor":"24.2MP APS-C CMOS","processor":"DIGIC X","autofocus":"Dual Pixel CMOS AF II, Eye Detection","video":"4K 30fps, Full HD 120fps","viewfinder":"0.39 inch EVF 2.36M dots","lcd":"3 inch vari-angle touchscreen","connectivity":"Wi-Fi 5 GHz, Bluetooth 5.0"}',
12, 1, 1, 0, 'active', 880, 55),

(8, 6, 3, 'Sony PlayStation 5 Console', 'sony-playstation-5', 'CFI-1218A01X', 'SON-PS5-DISC', '5901234567897', 7999.00, 8999.00, 5800.00, 8, 3, 4.500,
'Experience lightning-fast loading, stunning graphics, and next-gen gameplay. PlayStation 5 features a custom SSD and DualSense controller.',
'Sony PS5 gaming console with DualSense controller',
'{"cpu":"AMD Zen 2, 8 cores 3.5GHz","gpu":"AMD RDNA 2, 10.28 TFLOPS","ram":"16GB GDDR6","storage":"825GB Custom NVMe SSD","resolution":"Up to 8K","fps":"Up to 120fps","ray_tracing":true,"hdr":true,"audio":"3D Audio (Tempest Engine)"}',
12, 1, 0, 1, 'active', 2210, 160),

(10, 15, 2, 'JBL Tune 760NC Wireless Headphones', 'jbl-tune-760nc', 'JBLT760NCBLKAM', 'JBL-T760-BLK', '5901234567898', 899.00, 1199.00, 650.00, 50, 10, 0.218,
'Enjoy powerful JBL Pure Bass sound with up to 50 hours playback and Adaptive Noise Cancelling in a comfortable on-ear design.',
'JBL wireless headphones with 50hr battery and ANC',
'{"driver":"40mm","frequency":"20Hz-20kHz","noise_cancelling":"Active Noise Cancelling (ANC)","battery":"50 hours (ANC off), 35 hours (ANC on)","charging":"USB-C, fast charge (5min = 2hrs)","bluetooth":"5.0, multipoint connection","microphone":"Hands-free call, voice assistant"}',
12, 0, 1, 1, 'active', 1650, 280),

(1, 12, 1, 'Tecno POVA 5 Pro 5G', 'tecno-pova-5-pro-5g', 'POVA5PRO', 'TEC-POVA5P-8256', '5901234567899', 1599.00, 1799.00, 1100.00, 60, 10, 0.218,
'The Tecno POVA 5 Pro 5G offers incredible value with a 6000mAh battery, 64MP triple camera, and 5G connectivity at an affordable price.',
'Tecno 5G smartphone with 6000mAh battery',
'{"display":"6.78 inch FHD+ IPS 120Hz","processor":"MediaTek Dimensity 6080 5G","ram":"8GB + 8GB virtual","storage":"256GB expandable","camera":"64MP triple AI","battery":"6000mAh 33W fast charge","os":"Android 13 HiOS 13"}',
12, 1, 1, 0, 'active', 2100, 320),

(2, 4, 2, 'Dell XPS 15 OLED Laptop', 'dell-xps-15-oled', '9530-A3501', 'DEL-XPS15-I7', '5901234567900', 12499.00, 14000.00, 9000.00, 10, 3, 1.86,
'The Dell XPS 15 OLED is the pinnacle of laptop design. Features Intel Core i7-13700H, 32GB RAM, and a stunning 3.5K OLED touchscreen.',
'Dell premium laptop with 3.5K OLED display',
'{"display":"15.6 inch 3.5K OLED Touch 120Hz","processor":"Intel Core i7-13700H","ram":"32GB DDR5","storage":"1TB NVMe SSD","graphics":"NVIDIA GeForce RTX 4060 8GB","battery":"86Wh, up to 12hrs","os":"Windows 11 Pro"}',
12, 1, 1, 0, 'active', 1300, 45),

(11, 14, 2, 'Logitech MX Keys S Wireless Keyboard', 'logitech-mx-keys-s', 'MXKEYSBLA', 'LOG-MXKEYS-S', '5901234567901', 549.00, 699.00, 380.00, 45, 8, 0.810,
'The MX Keys S features spherically-shaped keys for accurate and comfortable typing, with multi-device connectivity for up to 3 devices.',
'Logitech wireless keyboard for up to 3 devices',
'{"layout":"Full-size with numpad","connectivity":"Bluetooth, USB receiver (Bolt)","battery":"Up to 10 days (backlight on), 5 months (off)","backlight":"Intelligent illumination, per key","devices":"Up to 3 simultaneous","os_compatibility":"Windows, macOS, Linux, iOS, Android"}',
24, 0, 0, 1, 'active', 760, 135);

-- ============================================================
-- SEED DATA: Product Images
-- ============================================================
INSERT INTO product_images (product_id, image_path, alt_text, is_primary, sort_order) VALUES
(1, 'products/samsung-s24-ultra-1.jpg', 'Samsung Galaxy S24 Ultra Front', 1, 1),
(1, 'products/samsung-s24-ultra-2.jpg', 'Samsung Galaxy S24 Ultra Back', 0, 2),
(1, 'products/samsung-s24-ultra-3.jpg', 'Samsung Galaxy S24 Ultra S Pen', 0, 3),
(2, 'products/iphone-15-pro-max-1.jpg', 'iPhone 15 Pro Max Front', 1, 1),
(2, 'products/iphone-15-pro-max-2.jpg', 'iPhone 15 Pro Max Back', 0, 2),
(3, 'products/hp-pavilion-15-1.jpg', 'HP Pavilion 15 Laptop', 1, 1),
(4, 'products/lenovo-chromebook-1.jpg', 'Lenovo Chromebook 14', 1, 1),
(5, 'products/ipad-pro-m2-1.jpg', 'Apple iPad Pro M2 Front', 1, 1),
(6, 'products/galaxy-watch6-1.jpg', 'Samsung Galaxy Watch 6 Classic', 1, 1),
(7, 'products/canon-r50-1.jpg', 'Canon EOS R50 Camera', 1, 1),
(8, 'products/ps5-1.jpg', 'Sony PlayStation 5 Console', 1, 1),
(8, 'products/ps5-2.jpg', 'Sony PS5 with DualSense Controller', 0, 2),
(9, 'products/jbl-t760-1.jpg', 'JBL Tune 760NC Headphones', 1, 1),
(10, 'products/tecno-pova5-1.jpg', 'Tecno POVA 5 Pro 5G', 1, 1),
(11, 'products/dell-xps15-1.jpg', 'Dell XPS 15 OLED Front', 1, 1),
(12, 'products/logitech-mx-keys-1.jpg', 'Logitech MX Keys S Keyboard', 1, 1);

-- ============================================================
-- SEED DATA: Product Tags
-- ============================================================
INSERT INTO product_tags (product_id, tag_name) VALUES
(1, '5G'), (1, 'S Pen'), (1, 'Android'), (1, '200MP'),
(2, '5G'), (2, 'iOS'), (2, 'A17 Pro'), (2, 'Titanium'),
(3, 'Windows 11'), (3, 'SSD'), (3, 'Intel Core i5'),
(8, 'Gaming'), (8, 'Next-Gen'), (8, '4K'),
(9, 'Wireless'), (9, 'ANC'), (9, 'Bass'),
(10, '5G'), (10, 'Budget'), (10, 'Big Battery');

-- ============================================================
-- SEED DATA: Coupons
-- ============================================================
INSERT INTO coupons (coupon_code, coupon_name, discount_type, discount_value, min_order_amount, max_discount, max_uses, expiry_date, is_active) VALUES
('WELCOME10', 'Welcome 10% Off', 'percentage', 10.00, 200.00, 500.00, 500, DATE_ADD(NOW(), INTERVAL 6 MONTH), 1),
('SAVE50', 'GHS 50 Off', 'fixed', 50.00, 500.00, NULL, 200, DATE_ADD(NOW(), INTERVAL 3 MONTH), 1),
('FREESHIP', 'Free Shipping', 'free_shipping', 0.00, 300.00, NULL, NULL, DATE_ADD(NOW(), INTERVAL 1 MONTH), 1),
('STUDENT15', 'Student Discount 15%', 'percentage', 15.00, 1000.00, 1000.00, 100, DATE_ADD(NOW(), INTERVAL 12 MONTH), 1),
('FLASH20', 'Flash Sale 20% Off', 'percentage', 20.00, 0.00, 2000.00, 50, DATE_ADD(NOW(), INTERVAL 2 DAY), 1);

-- ============================================================
-- SEED DATA: Flash Sales
-- ============================================================
INSERT INTO flash_sales (product_id, title, sale_price, original_price, discount_pct, start_time, end_time, qty_limit, status) VALUES
(9, 'JBL Headphones Flash Deal', 749.00, 899.00, 16.69, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR), 30, 'active'),
(10, 'Tecno POVA 5G Weekend Sale', 1399.00, 1599.00, 12.51, NOW(), DATE_ADD(NOW(), INTERVAL 48 HOUR), 50, 'active');

-- ============================================================
-- SEED DATA: Product Bundles
-- ============================================================
INSERT INTO product_bundles (bundle_name, slug, description, original_price, bundle_price, discount_pct, is_featured, quantity_available, status) VALUES
('Mobile Power Bundle', 'mobile-power-bundle', 'Samsung Galaxy S24 Ultra + Galaxy Watch 6 Classic at a special bundled price!', 12298.00, 10999.00, 10.56, 1, 15, 1),
('Creator Starter Pack', 'creator-starter-pack', 'Canon EOS R50 + JBL Tune 760NC — everything you need to start creating content!', 7698.00, 6999.00, 9.08, 1, 10, 1);

INSERT INTO bundle_items (bundle_id, product_id, quantity) VALUES
(1, 1, 1), (1, 6, 1),
(2, 7, 1), (2, 9, 1);

-- ============================================================
-- SEED DATA: Sample Orders
-- ============================================================
INSERT INTO orders (customer_id, order_number, subtotal, tax_amount, shipping_cost, discount_amount, total, status, payment_status, payment_method, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_country) VALUES
(1, 'ORD-2024-00001', 8999.00, 1124.88, 20.00, 0.00, 10143.88, 'delivered', 'paid', 'mobile_money', 'Kwame Asante', '+233241234570', '12 Independence Ave', 'Accra', 'Ghana'),
(1, 'ORD-2024-00002', 4599.00, 574.88, 20.00, 459.90, 4734.00, 'shipped', 'paid', 'mobile_money', 'Kwame Asante', '+233241234570', '12 Independence Ave', 'Accra', 'Ghana'),
(2, 'ORD-2024-00003', 899.00, 112.38, 35.00, 0.00, 1046.38, 'processing', 'pending', 'cash_on_delivery', 'Abena Boateng', '+233241234571', '45 Kumasi Road', 'Kumasi', 'Ghana');

INSERT INTO order_items (order_id, product_id, product_name, sku, quantity, unit_price, total_price) VALUES
(1, 1, 'Samsung Galaxy S24 Ultra', 'SAM-S24U-256', 1, 8999.00, 8999.00),
(2, 3, 'HP Pavilion 15 Laptop', 'HP-PAV15-I5', 1, 4599.00, 4599.00),
(3, 9, 'JBL Tune 760NC Wireless Headphones', 'JBL-T760-BLK', 1, 899.00, 899.00);

INSERT INTO order_tracking (order_id, status, description) VALUES
(1, 'Order Placed', 'Order received and confirmed'),
(1, 'Processing', 'Payment verified, preparing order'),
(1, 'Shipped', 'Order dispatched via GH Express'),
(1, 'Delivered', 'Order delivered successfully'),
(2, 'Order Placed', 'Order received and confirmed'),
(2, 'Shipped', 'Order dispatched, estimated 2 days');

INSERT INTO payments (order_id, transaction_id, payment_method, amount, status, payment_date) VALUES
(1, 'MTN-TXN-20241001-001', 'mobile_money', 10143.88, 'completed', '2024-10-01 10:30:00'),
(2, 'MTN-TXN-20241005-002', 'mobile_money', 4734.00, 'completed', '2024-10-05 14:20:00');

-- ============================================================
-- SEED DATA: Reviews
-- ============================================================
INSERT INTO reviews (product_id, customer_id, order_id, rating, title, review_text, pros, cons, is_verified, status) VALUES
(1, 1, 1, 5, 'Absolutely Amazing Phone!', 'The Samsung S24 Ultra is a beast. The camera system is out of this world. S Pen integration is seamless. Battery lasts all day. 100% recommend!', 'Camera quality, S Pen, battery life, display', 'Price is high', 1, 'approved');

-- ============================================================
-- SEED DATA: Wishlist
-- ============================================================
INSERT INTO wishlist (customer_id, product_id) VALUES
(1, 2), (1, 11), (2, 1), (2, 8);

-- ============================================================
-- SEED DATA: Newsletter Subscribers
-- ============================================================
INSERT INTO newsletter_subscribers (email, name, status) VALUES
('newsletter1@gmail.com', 'Esi Mensah', 'active'),
('newsletter2@gmail.com', 'Fiifi Owusu', 'active'),
('newsletter3@gmail.com', 'Adjoa Boateng', 'active');

-- ============================================================
-- SEED DATA: Settings
-- ============================================================
INSERT INTO settings (setting_key, setting_value, setting_group, label, is_public) VALUES
('site_name', 'ElectroStore Ghana', 'general', 'Site Name', 1),
('site_tagline', 'Ghana\'s #1 Electronics Store', 'general', 'Site Tagline', 1),
('site_email', 'info@electrostore.com.gh', 'general', 'Contact Email', 1),
('site_phone', '+233 302 123 456', 'general', 'Contact Phone', 1),
('site_address', '14 Independence Avenue, Accra, Ghana', 'general', 'Physical Address', 1),
('site_logo', 'assets/images/logo.png', 'general', 'Site Logo', 1),
('site_favicon', 'assets/images/favicon.ico', 'general', 'Favicon', 1),
('currency_default', 'GHS', 'payment', 'Default Currency', 1),
('tax_rate', '12.5', 'payment', 'Default Tax Rate (%)', 0),
('tax_inclusive', '0', 'payment', 'Prices Tax Inclusive', 0),
('loyalty_points_ratio', '5', 'loyalty', 'Points per GHS spent (1 point per X GHS)', 0),
('loyalty_redeem_ratio', '100', 'loyalty', 'Points to GHS conversion (N points = 1 GHS)', 0),
('low_stock_threshold', '5', 'inventory', 'Global Low Stock Alert Threshold', 0),
('products_per_page', '12', 'display', 'Products Per Page', 1),
('order_prefix', 'ORD', 'orders', 'Order Number Prefix', 0),
('invoice_prefix', 'INV', 'orders', 'Invoice Number Prefix', 0),
('enable_reviews', '1', 'reviews', 'Enable Product Reviews', 1),
('review_approval', '1', 'reviews', 'Reviews Require Approval', 0),
('enable_wishlist', '1', 'wishlist', 'Enable Wishlist', 1),
('enable_compare', '1', 'compare', 'Enable Product Comparison', 1),
('max_compare', '4', 'compare', 'Max Products in Comparison', 1),
('enable_newsletter', '1', 'newsletter', 'Enable Newsletter Signup', 1),
('smtp_host', 'smtp.gmail.com', 'email', 'SMTP Host', 0),
('smtp_port', '587', 'email', 'SMTP Port', 0),
('smtp_user', '', 'email', 'SMTP Username', 0),
('smtp_pass', '', 'email', 'SMTP Password', 0),
('smtp_from_name', 'ElectroStore', 'email', 'Email Sender Name', 0),
('google_analytics_id', '', 'analytics', 'Google Analytics ID', 0),
('facebook_pixel', '', 'analytics', 'Facebook Pixel ID', 0),
('maintenance_mode', '0', 'general', 'Maintenance Mode', 0),
('dark_mode_default', '0', 'display', 'Default to Dark Mode', 1);

-- ============================================================
-- SEED DATA: Loyalty Points Logs
-- ============================================================
INSERT INTO loyalty_points_log (customer_id, order_id, points, transaction_type, description, balance_after) VALUES
(1, 1, 2000, 'earned', 'Points earned from order ORD-2024-00001', 2000),
(1, 2, 500, 'earned', 'Points earned from order ORD-2024-00002', 2500);

-- ============================================================
-- SEED DATA: Notifications
-- ============================================================
INSERT INTO notifications (user_id, type, title, message, icon, color, link) VALUES
(1, 'order', 'New Order Received', 'Order ORD-2024-00003 received from Abena Boateng', 'fa-shopping-cart', '#28a745', '/admin/orders/view.php?id=3'),
(1, 'stock', 'Low Stock Alert', 'Sony PlayStation 5 has only 8 units remaining', 'fa-exclamation-triangle', '#ffc107', '/admin/inventory'),
(4, 'order', 'Order Shipped', 'Your order ORD-2024-00002 has been shipped!', 'fa-truck', '#17a2b8', '/customer/orders');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF SCRIPT
-- ============================================================
