-- ============================================================
-- ElectroStore Ghana — SEED DATA (run AFTER schema import)
-- Compatible with MariaDB 10.4+ (their exact running schema)
-- Run this in phpMyAdmin → SQL tab on electronic_store DB
-- ============================================================

USE electronic_store;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. CUSTOMER TIERS (critical — empty = registration breaks)
-- ============================================================
INSERT IGNORE INTO `customer_tiers`
    (`tier_name`,`min_points`,`max_points`,`discount_percentage`,`cashback_rate`,`free_shipping`,`priority_support`,`badge_color`,`badge_icon`,`benefits`,`is_active`)
VALUES
    ('Bronze',  0,     999,   0.00, 0.00, 0, 0, '#CD7F32', 'fa-medal',  'Basic membership benefits', 1),
    ('Silver',  1000,  4999,  2.00, 1.00, 0, 0, '#C0C0C0', 'fa-medal',  'Silver: 2% discount, 1% cashback', 1),
    ('Gold',    5000,  14999, 5.00, 2.00, 1, 0, '#FFD700', 'fa-award',  'Gold: 5% discount, 2% cashback, free shipping', 1),
    ('Platinum',15000, 999999,10.00,5.00, 1, 1, '#E5E4E2', 'fa-crown',  'Platinum: 10% discount, 5% cashback, all perks', 1);

-- ============================================================
-- 2. ADMIN USER  (login: admin / admin123)
-- ============================================================
INSERT IGNORE INTO `users`
    (`role_id`,`username`,`email`,`password`,`full_name`,`phone`,`status`,`email_verified`)
VALUES
    (1,'admin','admin@electrostore.com',
     '$2y$12$YKiN7OFZ1ZNiZn0FOcRRIeIg8kJWsOBP0/fXpO72CAnuGnPF/nQwq',
     'System Administrator','+233241234567','active',1),
    (2,'inventory1','inventory@electrostore.com',
     '$2y$12$YKiN7OFZ1ZNiZn0FOcRRIeIg8kJWsOBP0/fXpO72CAnuGnPF/nQwq',
     'John Mensah','+233241234568','active',1),
    (3,'sales1','sales@electrostore.com',
     '$2y$12$YKiN7OFZ1ZNiZn0FOcRRIeIg8kJWsOBP0/fXpO72CAnuGnPF/nQwq',
     'Ama Owusu','+233241234569','active',1),
    (4,'customer1','customer1@gmail.com',
     '$2y$12$YKiN7OFZ1ZNiZn0FOcRRIeIg8kJWsOBP0/fXpO72CAnuGnPF/nQwq',
     'Kwame Asante','+233241234570','active',1),
    (4,'customer2','customer2@gmail.com',
     '$2y$12$YKiN7OFZ1ZNiZn0FOcRRIeIg8kJWsOBP0/fXpO72CAnuGnPF/nQwq',
     'Abena Boateng','+233241234571','active',1);

-- Also upgrade existing abdulai to admin so they keep access
UPDATE `users` SET `role_id`=1, `email_verified`=1 WHERE `username`='abdulai';

-- ============================================================
-- 3. CUSTOMER PROFILES for seeded users
-- ============================================================
-- Get the Bronze tier id dynamically
SET @bronze_id = (SELECT id FROM customer_tiers WHERE tier_name='Bronze' LIMIT 1);
SET @silver_id = (SELECT id FROM customer_tiers WHERE tier_name='Silver' LIMIT 1);

-- Get user IDs
SET @admin_uid     = (SELECT id FROM users WHERE username='admin' LIMIT 1);
SET @inv_uid       = (SELECT id FROM users WHERE username='inventory1' LIMIT 1);
SET @sales_uid     = (SELECT id FROM users WHERE username='sales1' LIMIT 1);
SET @cust1_uid     = (SELECT id FROM users WHERE username='customer1' LIMIT 1);
SET @cust2_uid     = (SELECT id FROM users WHERE username='customer2' LIMIT 1);

INSERT IGNORE INTO `customers`
    (`user_id`,`first_name`,`last_name`,`phone`,`country`,`loyalty_points`,`tier_id`)
VALUES
    (@cust1_uid,'Kwame','Asante','+233241234570','Ghana',2500,@silver_id),
    (@cust2_uid,'Abena','Boateng','+233241234571','Ghana',500,@bronze_id);

-- Fix existing abdulai customer record tier
UPDATE `customers` SET `tier_id`=@bronze_id WHERE `tier_id` IS NULL;

-- ============================================================
-- 4. CURRENCIES
-- ============================================================
INSERT IGNORE INTO `currencies` (`currency_code`,`currency_name`,`symbol`,`exchange_rate`,`is_default`,`is_active`) VALUES
('GHS','Ghanaian Cedi','₵',1.000000,1,1),
('USD','US Dollar','$',0.067,0,1),
('EUR','Euro','€',0.062,0,1),
('GBP','British Pound','£',0.053,0,1);

-- ============================================================
-- 5. TAXES
-- ============================================================
INSERT IGNORE INTO `taxes` (`tax_name`,`rate`,`country`,`is_default`,`is_active`) VALUES
('Ghana VAT (NHIL+GETFund)',12.50,'Ghana',1,1),
('No Tax',0.00,'Export',0,1);

-- ============================================================
-- 6. SHIPPING ZONES
-- ============================================================
INSERT IGNORE INTO `shipping_zones` (`zone_name`,`countries`,`base_rate`,`per_kg_rate`,`free_above`,`est_days_min`,`est_days_max`,`is_active`) VALUES
('Greater Accra','Ghana-AccraRegion',20.00,2.00,500.00,1,2,1),
('Ashanti Region','Ghana-AshantiRegion',35.00,3.00,800.00,2,3,1),
('Other Regions Ghana','Ghana-Other',50.00,4.00,1000.00,3,5,1),
('International','International',150.00,8.00,NULL,7,14,1);

-- ============================================================
-- 7. CATEGORIES
-- ============================================================
INSERT IGNORE INTO `categories` (`parent_id`,`category_name`,`slug`,`icon`,`description`,`is_featured`,`sort_order`,`status`) VALUES
(NULL,'Smartphones','smartphones','fa-mobile-alt','Latest mobile phones and handsets',1,1,1),
(NULL,'Laptops','laptops','fa-laptop','Portable computers for work and gaming',1,2,1),
(NULL,'Tablets','tablets','fa-tablet-alt','iPad, Android tablets and e-readers',1,3,1),
(NULL,'Smartwatches','smartwatches','fa-clock','Wearable technology and fitness trackers',1,4,1),
(NULL,'Desktop Computers','desktops','fa-desktop','All-in-one and tower desktop systems',1,5,1),
(NULL,'Printers','printers','fa-print','Inkjet, laser and multifunction printers',0,6,1),
(NULL,'Cameras','cameras','fa-camera','DSLR, mirrorless and action cameras',1,7,1),
(NULL,'Gaming Consoles','gaming','fa-gamepad','PlayStation, Xbox, Nintendo gaming systems',1,8,1),
(NULL,'Networking','networking','fa-wifi','Routers, switches and access points',0,9,1),
(NULL,'Audio Equipment','audio','fa-headphones','Headphones, speakers and earbuds',1,10,1),
(NULL,'Accessories','accessories','fa-plug','Cases, chargers and cables',0,11,1);

-- Sub-categories (use parent slugs dynamically)
SET @laptops_id     = (SELECT id FROM categories WHERE slug='laptops' LIMIT 1);
SET @smartphones_id = (SELECT id FROM categories WHERE slug='smartphones' LIMIT 1);

INSERT IGNORE INTO `categories` (`parent_id`,`category_name`,`slug`,`icon`,`description`,`is_featured`,`sort_order`,`status`) VALUES
(@laptops_id,    'Gaming Laptops',      'gaming-laptops',      'fa-gamepad','High-performance gaming laptops',1,1,1),
(@laptops_id,    'Business Laptops',    'business-laptops',    'fa-briefcase','Professional business laptops',0,2,1),
(@smartphones_id,'Budget Smartphones',  'budget-smartphones',  'fa-mobile','Affordable smartphones under ₵1500',0,1,1),
(@smartphones_id,'Flagship Smartphones','flagship-smartphones','fa-star','Premium flagship devices',0,2,1);

-- ============================================================
-- 8. BRANDS
-- ============================================================
INSERT IGNORE INTO `brands` (`brand_name`,`slug`,`website`,`description`,`country`,`is_featured`,`status`) VALUES
('Apple',    'apple',    'https://apple.com',    'Think Different — premium consumer electronics','USA',1,1),
('Samsung',  'samsung',  'https://samsung.com',  'Leader in mobile and display technology','South Korea',1,1),
('HP',       'hp',       'https://hp.com',       'Trusted computing and printing solutions','USA',1,1),
('Dell',     'dell',     'https://dell.com',     'Enterprise and consumer computing solutions','USA',1,1),
('Lenovo',   'lenovo',   'https://lenovo.com',   'Smart technology for smarter people','China',1,1),
('Sony',     'sony',     'https://sony.com',     'Audio, visual and gaming innovation','Japan',1,1),
('LG',       'lg',       'https://lg.com',       'Life is Good — electronics and appliances','South Korea',0,1),
('Canon',    'canon',    'https://canon.com',    'Professional camera and imaging solutions','Japan',0,1),
('Nikon',    'nikon',    'https://nikon.com',    'I am Nikon — precision optics','Japan',0,1),
('Xiaomi',   'xiaomi',   'https://mi.com',       'Innovation for everyone','China',1,1),
('Tecno',    'tecno',    'https://tecno-mobile.com','Stop at Nothing — Africa-focused devices','Ghana/China',1,1),
('Infinix',  'infinix',  'https://infinixmobility.com','Dare to Leap — budget smartphones','Hong Kong',1,1),
('Logitech', 'logitech', 'https://logitech.com', 'Design for people — peripherals','Switzerland',0,1),
('JBL',      'jbl',      'https://jbl.com',      'Legendary sound — audio equipment','USA',0,1),
('Huawei',   'huawei',   'https://huawei.com',   'Building a fully connected world','China',0,1);

-- ============================================================
-- 9. SUPPLIERS
-- ============================================================
INSERT IGNORE INTO `suppliers` (`supplier_name`,`contact_person`,`email`,`phone`,`city`,`country`,`payment_terms`,`lead_time_days`,`rating`,`status`) VALUES
('Tech Imports Ghana Ltd','Kofi Acheampong','imports@techgh.com','+233302123456','Accra','Ghana','Net 30',7,4.5,1),
('ElectroWorld Distributors','Yaw Darko','orders@electroworld.gh','+233244567890','Accra','Ghana','Net 15',5,4.2,1),
('Global Tech Supplies','James Asante','sales@globaltech.com','+1-555-0123','New York','USA','Net 60',21,4.8,1),
('Asian Electronics Hub','Li Wei','supply@asiahub.com','+86-10-12345678','Shenzhen','China','Prepaid',35,4.0,1);

-- ============================================================
-- 10. PRODUCTS
-- ============================================================
-- Resolve IDs
SET @cat_phones  = (SELECT id FROM categories WHERE slug='smartphones' LIMIT 1);
SET @cat_laptops = (SELECT id FROM categories WHERE slug='laptops'     LIMIT 1);
SET @cat_tablets = (SELECT id FROM categories WHERE slug='tablets'     LIMIT 1);
SET @cat_watch   = (SELECT id FROM categories WHERE slug='smartwatches'LIMIT 1);
SET @cat_desktop = (SELECT id FROM categories WHERE slug='desktops'    LIMIT 1);
SET @cat_cameras = (SELECT id FROM categories WHERE slug='cameras'     LIMIT 1);
SET @cat_gaming  = (SELECT id FROM categories WHERE slug='gaming'      LIMIT 1);
SET @cat_audio   = (SELECT id FROM categories WHERE slug='audio'       LIMIT 1);
SET @cat_access  = (SELECT id FROM categories WHERE slug='accessories' LIMIT 1);

SET @br_samsung  = (SELECT id FROM brands WHERE slug='samsung'  LIMIT 1);
SET @br_apple    = (SELECT id FROM brands WHERE slug='apple'    LIMIT 1);
SET @br_hp       = (SELECT id FROM brands WHERE slug='hp'       LIMIT 1);
SET @br_lenovo   = (SELECT id FROM brands WHERE slug='lenovo'   LIMIT 1);
SET @br_dell     = (SELECT id FROM brands WHERE slug='dell'     LIMIT 1);
SET @br_sony     = (SELECT id FROM brands WHERE slug='sony'     LIMIT 1);
SET @br_canon    = (SELECT id FROM brands WHERE slug='canon'    LIMIT 1);
SET @br_jbl      = (SELECT id FROM brands WHERE slug='jbl'      LIMIT 1);
SET @br_tecno    = (SELECT id FROM brands WHERE slug='tecno'    LIMIT 1);
SET @br_logitech = (SELECT id FROM brands WHERE slug='logitech' LIMIT 1);

SET @sup1 = (SELECT id FROM suppliers WHERE supplier_name='Tech Imports Ghana Ltd' LIMIT 1);
SET @sup2 = (SELECT id FROM suppliers WHERE supplier_name='ElectroWorld Distributors' LIMIT 1);

SET @tax1 = (SELECT id FROM taxes WHERE tax_name LIKE 'Ghana VAT%' LIMIT 1);

INSERT IGNORE INTO `products`
    (`category_id`,`brand_id`,`supplier_id`,`product_name`,`slug`,`model`,`sku`,`barcode`,
     `price`,`compare_price`,`cost_price`,`tax_id`,`quantity`,`low_stock_alert`,`weight_kg`,
     `short_description`,`description`,`specifications`,`warranty_months`,
     `is_featured`,`is_new_arrival`,`is_best_seller`,`status`,`views`,`total_sold`)
VALUES
(
  @cat_phones,@br_samsung,@sup1,
  'Samsung Galaxy S24 Ultra','samsung-galaxy-s24-ultra','SM-S928B','SAM-S24U-256','5901234567890',
  8999.00,9999.00,6500.00,@tax1,25,5,0.232,
  'Samsung flagship with 200MP camera and S Pen',
  'Experience the ultimate Samsung flagship with a built-in S Pen, 200MP camera system, and Snapdragon 8 Gen 3 processor.',
  '{"Display":"6.8\" Dynamic AMOLED 2X 120Hz","Processor":"Snapdragon 8 Gen 3","RAM":"12GB","Storage":"256GB","Camera":"200MP main + 12MP ultra + 10MP 3x + 10MP 5x","Battery":"5000mAh","OS":"Android 14","5G":"Yes"}',
  24,1,1,1,'active',2450,180
),
(
  @cat_phones,@br_apple,@sup1,
  'Apple iPhone 15 Pro Max','apple-iphone-15-pro-max','A3108','APL-IP15PM-256','5901234567891',
  12999.00,14500.00,9800.00,@tax1,15,3,0.221,
  'Apple iPhone with A17 Pro chip and titanium build',
  'The most powerful iPhone ever, featuring a 48MP camera system, A17 Pro chip, and titanium design.',
  '{"Display":"6.7\" Super Retina XDR ProMotion 120Hz","Chip":"A17 Pro","RAM":"8GB","Storage":"256GB","Camera":"48MP main + 12MP ultra + 12MP 5x telephoto","Battery":"4422mAh","OS":"iOS 17","5G":"Yes"}',
  12,1,1,1,'active',3102,245
),
(
  @cat_laptops,@br_hp,@sup2,
  'HP Pavilion 15 Laptop','hp-pavilion-15-laptop','EH3020WM','HP-PAV15-I5','5901234567892',
  4599.00,5200.00,3200.00,@tax1,40,8,1.75,
  'HP 15.6\" laptop with Intel Core i5 and SSD',
  'The HP Pavilion 15 delivers everyday performance for students and professionals with 12th Gen Intel Core i5.',
  '{"Display":"15.6\" Full HD IPS 250nits","Processor":"Intel Core i5-1235U","RAM":"8GB DDR4","Storage":"512GB NVMe SSD","Graphics":"Intel Iris Xe","Battery":"41Wh up to 8hrs","OS":"Windows 11 Home"}',
  12,1,1,0,'active',1820,120
),
(
  @cat_laptops,@br_lenovo,@sup2,
  'Lenovo IdeaPad 3 Chromebook','lenovo-ideapad-3-chromebook','CB14IGL05','LEN-CB14-N4020','5901234567893',
  1999.00,2500.00,1400.00,@tax1,30,5,1.5,
  'Lenovo 14\" Chromebook for students',
  'Perfect for students — lightweight design, all-day battery life, and seamless Google integration.',
  '{"Display":"14\" HD TN","Processor":"Intel Celeron N4020","RAM":"4GB LPDDR4X","Storage":"64GB eMMC","Battery":"42Wh up to 10hrs","OS":"Chrome OS"}',
  12,0,0,0,'active',892,67
),
(
  @cat_tablets,@br_apple,@sup1,
  'Apple iPad Pro M2 12.9"','apple-ipad-pro-m2-129','MNXR3','APL-IPDPM2-128','5901234567894',
  9499.00,10999.00,7000.00,@tax1,18,4,0.682,
  'Apple iPad Pro with M2 chip, 12.9 inch Liquid Retina XDR',
  'The most advanced iPad ever. M2 chip. Stunning Liquid Retina XDR display. Works with Apple Pencil 2 and Magic Keyboard.',
  '{"Display":"12.9\" Liquid Retina XDR","Chip":"Apple M2","RAM":"8GB","Storage":"128GB","Camera":"12MP Wide + 10MP Ultra Wide","Battery":"10541mAh","OS":"iPadOS 16","Connectivity":"Wi-Fi 6E Bluetooth 5.3"}',
  12,1,1,0,'active',1543,89
),
(
  @cat_watch,@br_samsung,@sup1,
  'Samsung Galaxy Watch 6 Classic','samsung-galaxy-watch-6-classic','SM-R960NZSAXFE','SAM-GW6C-47','5901234567895',
  3299.00,3799.00,2300.00,@tax1,22,5,0.059,
  'Samsung premium smartwatch with health monitoring',
  'Monitor your health with ECG, blood pressure monitoring, and sapphire crystal glass protection.',
  '{"Display":"1.5\" Super AMOLED 480x480","Processor":"Exynos W930","RAM":"2GB","Storage":"16GB","Battery":"425mAh up to 3 days","Sensors":"ECG Blood Pressure SpO2","OS":"Wear OS 4","Water Resistance":"5ATM + IP68"}',
  24,1,1,1,'active',1120,95
),
(
  @cat_cameras,@br_canon,@sup2,
  'Canon EOS R50 Mirrorless Camera','canon-eos-r50-mirrorless','R50BLKIT18-45','CAN-EOSR50-KIT','5901234567896',
  6799.00,7500.00,5000.00,@tax1,12,3,0.375,
  'Canon mirrorless 24.2MP with 4K video',
  'Perfect entry into mirrorless photography. 24.2MP APS-C sensor, 4K video, Eye Detect AF.',
  '{"Sensor":"24.2MP APS-C CMOS","Autofocus":"Dual Pixel CMOS AF II Eye Detection","Video":"4K 30fps Full HD 120fps","LCD":"3\" vari-angle touchscreen","Connectivity":"Wi-Fi 5GHz Bluetooth 5.0"}',
  12,1,1,0,'active',880,55
),
(
  @cat_gaming,@br_sony,@sup2,
  'Sony PlayStation 5 Console','sony-playstation-5','CFI-1218A01X','SON-PS5-DISC','5901234567897',
  7999.00,8999.00,5800.00,@tax1,8,3,4.5,
  'Sony PS5 gaming console with DualSense controller',
  'Experience lightning-fast loading, stunning graphics, and next-gen gameplay with custom SSD and DualSense controller.',
  '{"CPU":"AMD Zen 2 8 cores 3.5GHz","GPU":"AMD RDNA 2 10.28 TFLOPS","RAM":"16GB GDDR6","Storage":"825GB Custom NVMe SSD","Resolution":"Up to 8K","FPS":"Up to 120fps","Ray Tracing":"Yes","HDR":"Yes"}',
  12,1,0,1,'active',2210,160
),
(
  @cat_audio,@br_jbl,@sup2,
  'JBL Tune 760NC Wireless Headphones','jbl-tune-760nc','JBLT760NCBLKAM','JBL-T760-BLK','5901234567898',
  899.00,1199.00,650.00,@tax1,50,10,0.218,
  'JBL wireless headphones with 50hr battery and ANC',
  'Enjoy powerful JBL Pure Bass sound with up to 50 hours playback and Adaptive Noise Cancelling.',
  '{"Driver":"40mm","Frequency":"20Hz-20kHz","Noise Cancelling":"Active Noise Cancelling (ANC)","Battery":"50 hours ANC off 35 hours ANC on","Charging":"USB-C fast charge 5min=2hrs","Bluetooth":"5.0 multipoint"}',
  12,0,1,1,'active',1650,280
),
(
  @cat_phones,@br_tecno,@sup1,
  'Tecno POVA 5 Pro 5G','tecno-pova-5-pro-5g','POVA5PRO','TEC-POVA5P-8256','5901234567899',
  1599.00,1799.00,1100.00,@tax1,60,10,0.218,
  'Tecno 5G smartphone with 6000mAh battery',
  'The Tecno POVA 5 Pro 5G offers incredible value with a 6000mAh battery, 64MP triple camera, and 5G connectivity.',
  '{"Display":"6.78\" FHD+ IPS 120Hz","Processor":"MediaTek Dimensity 6080 5G","RAM":"8GB + 8GB virtual","Storage":"256GB expandable","Camera":"64MP triple AI","Battery":"6000mAh 33W fast charge","OS":"Android 13 HiOS 13"}',
  12,1,1,0,'active',2100,320
),
(
  @cat_laptops,@br_dell,@sup2,
  'Dell XPS 15 OLED Laptop','dell-xps-15-oled','9530-A3501','DEL-XPS15-I7','5901234567900',
  12499.00,14000.00,9000.00,@tax1,10,3,1.86,
  'Dell premium laptop with 3.5K OLED display',
  'The Dell XPS 15 OLED is the pinnacle of laptop design. Features Intel Core i7-13700H, 32GB RAM, and a stunning 3.5K OLED touchscreen.',
  '{"Display":"15.6\" 3.5K OLED Touch 120Hz","Processor":"Intel Core i7-13700H","RAM":"32GB DDR5","Storage":"1TB NVMe SSD","Graphics":"NVIDIA GeForce RTX 4060 8GB","Battery":"86Wh up to 12hrs","OS":"Windows 11 Pro"}',
  12,1,1,0,'active',1300,45
),
(
  @cat_access,@br_logitech,@sup2,
  'Logitech MX Keys S Wireless Keyboard','logitech-mx-keys-s','MXKEYSBLA','LOG-MXKEYS-S','5901234567901',
  549.00,699.00,380.00,@tax1,45,8,0.810,
  'Logitech wireless keyboard for up to 3 devices',
  'The MX Keys S features spherically-shaped keys for accurate comfortable typing with multi-device connectivity for up to 3 devices.',
  '{"Layout":"Full-size with numpad","Connectivity":"Bluetooth USB receiver (Bolt)","Battery":"Up to 10 days backlight on 5 months off","Backlight":"Intelligent illumination per key","Devices":"Up to 3 simultaneous","Compatibility":"Windows macOS Linux iOS Android"}',
  24,0,0,1,'active',760,135
);

-- ============================================================
-- 11. PRODUCT IMAGES (category-aware for SVG generator)
-- ============================================================
SET @p1  = (SELECT id FROM products WHERE slug='samsung-galaxy-s24-ultra'    LIMIT 1);
SET @p2  = (SELECT id FROM products WHERE slug='apple-iphone-15-pro-max'     LIMIT 1);
SET @p3  = (SELECT id FROM products WHERE slug='hp-pavilion-15-laptop'       LIMIT 1);
SET @p4  = (SELECT id FROM products WHERE slug='lenovo-ideapad-3-chromebook' LIMIT 1);
SET @p5  = (SELECT id FROM products WHERE slug='apple-ipad-pro-m2-129'       LIMIT 1);
SET @p6  = (SELECT id FROM products WHERE slug='samsung-galaxy-watch-6-classic' LIMIT 1);
SET @p7  = (SELECT id FROM products WHERE slug='canon-eos-r50-mirrorless'    LIMIT 1);
SET @p8  = (SELECT id FROM products WHERE slug='sony-playstation-5'          LIMIT 1);
SET @p9  = (SELECT id FROM products WHERE slug='jbl-tune-760nc'              LIMIT 1);
SET @p10 = (SELECT id FROM products WHERE slug='tecno-pova-5-pro-5g'         LIMIT 1);
SET @p11 = (SELECT id FROM products WHERE slug='dell-xps-15-oled'            LIMIT 1);
SET @p12 = (SELECT id FROM products WHERE slug='logitech-mx-keys-s'          LIMIT 1);

INSERT IGNORE INTO `product_images` (`product_id`,`image_path`,`alt_text`,`is_primary`,`sort_order`) VALUES
(@p1, '', 'Samsung Galaxy S24 Ultra',   1, 1),
(@p2, '', 'Apple iPhone 15 Pro Max',    1, 1),
(@p3, '', 'HP Pavilion 15 Laptop',      1, 1),
(@p4, '', 'Lenovo Chromebook 14',       1, 1),
(@p5, '', 'Apple iPad Pro M2',          1, 1),
(@p6, '', 'Samsung Galaxy Watch 6',     1, 1),
(@p7, '', 'Canon EOS R50',              1, 1),
(@p8, '', 'Sony PlayStation 5',         1, 1),
(@p9, '', 'JBL Tune 760NC',             1, 1),
(@p10,'', 'Tecno POVA 5 Pro 5G',        1, 1),
(@p11,'', 'Dell XPS 15 OLED',           1, 1),
(@p12,'', 'Logitech MX Keys S',         1, 1);

-- ============================================================
-- 12. PRODUCT TAGS
-- ============================================================
INSERT IGNORE INTO `product_tags` (`product_id`,`tag_name`) VALUES
(@p1,'5G'),(@p1,'S Pen'),(@p1,'Android'),(@p1,'200MP'),(@p1,'Flagship'),
(@p2,'5G'),(@p2,'iOS'),(@p2,'A17 Pro'),(@p2,'Titanium'),(@p2,'Flagship'),
(@p3,'Windows 11'),(@p3,'SSD'),(@p3,'Intel Core i5'),(@p3,'Student'),
(@p4,'Chromebook'),(@p4,'Student'),(@p4,'Budget'),
(@p5,'iPad'),(@p5,'Apple'),(@p5,'M2'),(@p5,'Tablet'),
(@p6,'Smartwatch'),(@p6,'Health'),(@p6,'ECG'),(@p6,'Samsung'),
(@p7,'Camera'),(@p7,'Mirrorless'),(@p7,'4K'),(@p7,'Canon'),
(@p8,'Gaming'),(@p8,'Next-Gen'),(@p8,'4K'),(@p8,'PlayStation'),
(@p9,'Wireless'),(@p9,'ANC'),(@p9,'Bass'),(@p9,'JBL'),
(@p10,'5G'),(@p10,'Budget'),(@p10,'Big Battery'),(@p10,'Tecno'),
(@p11,'OLED'),(@p11,'Gaming Laptop'),(@p11,'Dell'),(@p11,'RTX 4060'),
(@p12,'Wireless'),(@p12,'Keyboard'),(@p12,'Logitech'),(@p12,'Multi-Device');

-- ============================================================
-- 13. COUPONS
-- ============================================================
INSERT IGNORE INTO `coupons`
    (`coupon_code`,`coupon_name`,`discount_type`,`discount_value`,`min_order_amount`,`max_discount`,`max_uses`,`expiry_date`,`is_active`)
VALUES
('WELCOME10','Welcome 10% Off','percentage',10.00,200.00,500.00,500,DATE_ADD(NOW(),INTERVAL 6 MONTH),1),
('SAVE50','GHS 50 Off','fixed',50.00,500.00,NULL,200,DATE_ADD(NOW(),INTERVAL 3 MONTH),1),
('FREESHIP','Free Shipping','free_shipping',0.00,300.00,NULL,NULL,DATE_ADD(NOW(),INTERVAL 1 MONTH),1),
('STUDENT15','Student Discount 15%','percentage',15.00,1000.00,1000.00,100,DATE_ADD(NOW(),INTERVAL 12 MONTH),1),
('FLASH20','Flash Sale 20% Off','percentage',20.00,0.00,2000.00,50,DATE_ADD(NOW(),INTERVAL 2 DAY),1);

-- ============================================================
-- 14. FLASH SALES
-- ============================================================
INSERT IGNORE INTO `flash_sales`
    (`product_id`,`title`,`sale_price`,`original_price`,`discount_pct`,`start_time`,`end_time`,`qty_limit`,`status`)
VALUES
(@p9,'JBL Headphones Flash Deal',  749.00,899.00,16.69,NOW(),DATE_ADD(NOW(),INTERVAL 24 HOUR),30,'active'),
(@p10,'Tecno POVA 5G Weekend Sale',1399.00,1599.00,12.51,NOW(),DATE_ADD(NOW(),INTERVAL 48 HOUR),50,'active');

-- ============================================================
-- 15. PRODUCT BUNDLES
-- ============================================================
INSERT IGNORE INTO `product_bundles`
    (`bundle_name`,`slug`,`description`,`original_price`,`bundle_price`,`discount_pct`,`is_featured`,`quantity_available`,`status`)
VALUES
('Mobile Power Bundle','mobile-power-bundle',
 'Samsung Galaxy S24 Ultra + Galaxy Watch 6 Classic at a special bundled price!',
 12298.00,10999.00,10.56,1,15,1),
('Creator Starter Pack','creator-starter-pack',
 'Canon EOS R50 + JBL Tune 760NC — everything you need to start creating content!',
 7698.00,6999.00,9.08,1,10,1);

SET @bundle1 = (SELECT id FROM product_bundles WHERE slug='mobile-power-bundle'  LIMIT 1);
SET @bundle2 = (SELECT id FROM product_bundles WHERE slug='creator-starter-pack' LIMIT 1);

INSERT IGNORE INTO `bundle_items` (`bundle_id`,`product_id`,`quantity`) VALUES
(@bundle1,@p1,1),(@bundle1,@p6,1),
(@bundle2,@p7,1),(@bundle2,@p9,1);

-- ============================================================
-- 16. SETTINGS
-- ============================================================
INSERT IGNORE INTO `settings` (`setting_key`,`setting_value`,`setting_group`,`label`,`is_public`) VALUES
('site_name',       'ElectroStore Ghana',                    'general', 'Site Name',        1),
('site_tagline',    'Ghana''s #1 Electronics Store',         'general', 'Site Tagline',     1),
('site_email',      'info@electrostore.com.gh',              'general', 'Contact Email',    1),
('site_phone',      '+233 302 123 456',                      'general', 'Contact Phone',    1),
('site_address',    '14 Independence Avenue, Accra, Ghana',  'general', 'Physical Address', 1),
('site_logo',       'assets/images/logo.png',                'general', 'Site Logo',        1),
('currency_default','GHS',                                   'payment', 'Default Currency', 1),
('tax_rate',        '12.5',                                  'payment', 'Default Tax Rate (%)',0),
('tax_inclusive',   '0',                                     'payment', 'Prices Tax Inclusive',0),
('loyalty_points_ratio','5',                                 'loyalty', 'Points per GHS spent',0),
('loyalty_redeem_ratio','100',                               'loyalty', 'Points to GHS ratio',0),
('low_stock_threshold','5',                                  'inventory','Low Stock Threshold',0),
('products_per_page','12',                                   'display', 'Products Per Page', 1),
('order_prefix',    'ORD',                                   'orders',  'Order Number Prefix',0),
('invoice_prefix',  'INV',                                   'orders',  'Invoice Number Prefix',0),
('enable_reviews',  '1',                                     'reviews', 'Enable Product Reviews',1),
('review_approval', '1',                                     'reviews', 'Reviews Require Approval',0),
('enable_wishlist', '1',                                     'wishlist','Enable Wishlist',   1),
('enable_compare',  '1',                                     'compare', 'Enable Comparison', 1),
('max_compare',     '4',                                     'compare', 'Max Compare Items', 1),
('enable_newsletter','1',                                    'newsletter','Enable Newsletter',1),
('maintenance_mode','0',                                     'general', 'Maintenance Mode',  0),
('dark_mode_default','0',                                    'display', 'Default to Dark Mode',1),
('smtp_host',       'smtp.gmail.com',                        'email',   'SMTP Host',         0),
('smtp_port',       '587',                                   'email',   'SMTP Port',         0),
('smtp_from_name',  'ElectroStore Ghana',                    'email',   'Email Sender Name', 0);

-- ============================================================
-- 17. SAMPLE NOTIFICATIONS for admin
-- ============================================================
SET @admin_id = (SELECT id FROM users WHERE username='admin' LIMIT 1);

INSERT IGNORE INTO `notifications` (`user_id`,`type`,`title`,`message`,`icon`,`color`,`link`) VALUES
(@admin_id,'system','Welcome to ElectroStore Admin!',
 'Your store is set up and ready. Add products and start selling!',
 'fa-star','#198754','/electronic_store/admin/'),
(@admin_id,'stock','Low Stock Alert',
 'Sony PlayStation 5 has only 8 units remaining.',
 'fa-exclamation-triangle','#ffc107','/electronic_store/admin/inventory/low_stock.php');

-- ============================================================
-- 18. STORED PROCEDURE: sp_update_customer_tier (if missing)
-- ============================================================
DROP PROCEDURE IF EXISTS `sp_update_customer_tier`;
DELIMITER $$
CREATE PROCEDURE `sp_update_customer_tier`(IN p_customer_id INT UNSIGNED)
BEGIN
    DECLARE v_points INT;
    DECLARE v_tier_id INT UNSIGNED;
    SELECT loyalty_points INTO v_points FROM customers WHERE id = p_customer_id;
    SELECT id INTO v_tier_id FROM customer_tiers
    WHERE min_points <= v_points AND max_points >= v_points AND is_active = 1 LIMIT 1;
    IF v_tier_id IS NOT NULL THEN
        UPDATE customers SET tier_id = v_tier_id WHERE id = p_customer_id;
    END IF;
END$$
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DONE! 
-- Login credentials:
--   Admin:    admin / admin123     → http://localhost/electronic_store/admin/
--   Customer: customer1 / admin123 → http://localhost/electronic_store/
--   Customer: customer2 / admin123
--   Your account (abdulai) → now upgraded to Admin role
-- ============================================================
