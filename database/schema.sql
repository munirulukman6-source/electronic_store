-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 15, 2026 at 02:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

--
-- Database: `electronic_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `abandoned_carts`
--

CREATE TABLE `abandoned_carts` (
  `id` int(10) UNSIGNED NOT NULL,
  `cart_id` int(10) UNSIGNED DEFAULT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `items_count` int(11) DEFAULT NULL,
  `cart_total` decimal(12,2) DEFAULT NULL,
  `reminder_count` int(11) DEFAULT 0,
  `last_reminder` datetime DEFAULT NULL,
  `recovered` tinyint(1) DEFAULT 0,
  `recovered_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Abandoned cart recovery tracking';

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(100) DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Comprehensive audit trail';

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, 'register', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:08:28'),
(2, 16, 'login', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:08:56'),
(3, 16, 'update_profile', 'users', 'users', 16, '{\"id\":16,\"role_id\":4,\"username\":\"ibs123\",\"email\":\"ibrahims@gmail.com\",\"password\":\"$2y$12$ZJ5cXGm6ldMXTaJY6OySnu0IAEqv\\/3hC\\/5WImklXLtl3asfipSl5e\",\"full_name\":\"Ibrahim Sumani\",\"phone\":\"0535532383\",\"avatar\":\"avatars\\/img_6a565215918331.37336843.jpg\",\"status\":\"active\",\"email_verified\":0,\"email_verify_token\":\"d21710abb0c2bc6cd46d85b00282a79fb2f22434aa39612eb6a316c6dfb59d45\",\"two_factor_enabled\":0,\"two_factor_secret\":null,\"password_reset_token\":null,\"password_reset_expires\":null,\"last_login\":\"2026-07-14 15:08:56\",\"last_ip\":\"::1\",\"login_attempts\":0,\"locked_until\":null,\"remember_token\":null,\"created_at\":\"2026-07-14 15:08:28\",\"updated_at\":\"2026-07-14 15:13:25\",\"role_name\":\"Customer\",\"role_slug\":\"customer\",\"permissions\":\"{\\\"shop\\\":true,\\\"orders\\\":true,\\\"reviews\\\":true,\\\"profile\\\":true}\"}', '{\"full_name\":\"Ibrahim Sumani\",\"first_name\":\"Ibrahim\",\"last_name\":\"Sumani\",\"phone\":\"0535532383\",\"address\":\"\",\"city\":\"\",\"state\":\"\",\"country\":\"\",\"postal_code\":\"\",\"gender\":\"male\",\"date_of_birth\":\"1999-06-01\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:13:25'),
(4, 16, 'logout', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:14:00'),
(5, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:14:37'),
(6, 1, 'update_product', 'products', 'products', 1, '{\"id\":1,\"category_id\":8,\"brand_id\":6,\"supplier_id\":2,\"product_name\":\"Sony PlayStation 5 Console\",\"slug\":\"sony-playstation-5\",\"model\":\"CFI-1218A01X\",\"sku\":\"SON-PS5-DISC\",\"barcode\":\"5901234567897\",\"price\":\"7999.00\",\"compare_price\":\"8999.00\",\"cost_price\":\"5800.00\",\"tax_id\":1,\"quantity\":8,\"low_stock_alert\":3,\"weight_kg\":\"4.500\",\"dimensions\":null,\"description\":\"Experience lightning-fast loading, stunning graphics, and next-gen gameplay with custom SSD and DualSense controller.\",\"short_description\":\"Sony PS5 gaming console with DualSense controller\",\"specifications\":{\"CPU\":\"AMD Zen 2 8 cores 3.5GHz\",\"GPU\":\"AMD RDNA 2 10.28 TFLOPS\",\"RAM\":\"16GB GDDR6\",\"Storage\":\"825GB Custom NVMe SSD\",\"Resolution\":\"Up to 8K\",\"FPS\":\"Up to 120fps\",\"Ray Tracing\":\"Yes\",\"HDR\":\"Yes\"},\"features\":[],\"warranty_months\":12,\"warranty_info\":null,\"is_featured\":1,\"is_new_arrival\":0,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":null,\"meta_desc\":null,\"meta_keywords\":null,\"status\":\"active\",\"views\":2212,\"total_sold\":160,\"avg_rating\":\"0.00\",\"review_count\":0,\"created_at\":\"2026-07-14 14:46:29\",\"updated_at\":\"2026-07-14 15:18:00\",\"category_name\":\"Gaming Consoles\",\"category_slug\":\"gaming\",\"brand_name\":\"Sony\",\"brand_slug\":\"sony\",\"brand_logo\":null,\"brand_website\":\"https:\\/\\/sony.com\",\"supplier_name\":\"ElectroWorld Distributors\",\"tax_rate\":\"12.50\",\"effective_price\":\"7999.00\",\"flash_end\":null,\"flash_id\":null,\"flash_qty_limit\":null,\"flash_remaining\":null,\"images\":[{\"id\":8,\"product_id\":1,\"image_path\":\"\",\"alt_text\":\"Sony PlayStation 5\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 14:46:29\"},{\"id\":20,\"product_id\":1,\"image_path\":\"\",\"alt_text\":\"Sony PlayStation 5\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 15:05:07\"},{\"id\":32,\"product_id\":1,\"image_path\":\"\",\"alt_text\":\"Sony PlayStation 5\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 15:06:35\"}],\"tags\":[{\"tag_name\":\"Gaming\"},{\"tag_name\":\"Next-Gen\"},{\"tag_name\":\"4K\"},{\"tag_name\":\"PlayStation\"},{\"tag_name\":\"Gaming\"},{\"tag_name\":\"Next-Gen\"},{\"tag_name\":\"4K\"},{\"tag_name\":\"PlayStation\"},{\"tag_name\":\"Gaming\"},{\"tag_name\":\"Next-Gen\"},{\"tag_name\":\"4K\"},{\"tag_name\":\"PlayStation\"}],\"faqs\":[],\"videos\":[]}', '{\"category_id\":8,\"brand_id\":6,\"supplier_id\":\"2\",\"product_name\":\"Sony PlayStation 5 Console\",\"model\":\"CFI-1218A01X\",\"sku\":\"SON-PS5-DISC\",\"barcode\":\"5901234567897\",\"price\":7999,\"compare_price\":8999,\"cost_price\":5800,\"quantity\":8,\"low_stock_alert\":3,\"weight_kg\":4.5,\"short_description\":\"Sony PS5 gaming console with DualSense controller\",\"description\":\"Experience lightning-fast loading, stunning graphics, and next-gen gameplay with custom SSD and DualSense controller.\",\"specifications\":{\"CPU\":\"AMD Zen 2 8 cores 3.5GHz\",\"GPU\":\"AMD RDNA 2 10.28 TFLOPS\",\"RAM\":\"16GB GDDR6\",\"Storage\":\"825GB Custom NVMe SSD\",\"Resolution\":\"Up to 8K\",\"FPS\":\"Up to 120fps\",\"Ray Tracing\":\"Yes\",\"HDR\":\"Yes\"},\"warranty_months\":12,\"warranty_info\":\"\",\"is_featured\":1,\"is_new_arrival\":0,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"\",\"meta_desc\":\"\",\"meta_keywords\":\"\",\"status\":\"active\",\"tags\":\"Gaming, Next-Gen, 4K, PlayStation, Gaming, Next-Gen, 4K, PlayStation, Gaming, Next-Gen, 4K, PlayStation\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:18:00'),
(7, 1, 'update_product', 'products', 'products', 1, '{\"id\":1,\"category_id\":8,\"brand_id\":6,\"supplier_id\":2,\"product_name\":\"Sony PlayStation 5 Console\",\"slug\":\"sony-playstation-5\",\"model\":\"CFI-1218A01X\",\"sku\":\"SON-PS5-DISC\",\"barcode\":\"5901234567897\",\"price\":\"7999.00\",\"compare_price\":\"8999.00\",\"cost_price\":\"5800.00\",\"tax_id\":1,\"quantity\":8,\"low_stock_alert\":3,\"weight_kg\":\"4.500\",\"dimensions\":null,\"description\":\"Experience lightning-fast loading, stunning graphics, and next-gen gameplay with custom SSD and DualSense controller.\",\"short_description\":\"Sony PS5 gaming console with DualSense controller\",\"specifications\":{\"CPU\":\"AMD Zen 2 8 cores 3.5GHz\",\"GPU\":\"AMD RDNA 2 10.28 TFLOPS\",\"RAM\":\"16GB GDDR6\",\"Storage\":\"825GB Custom NVMe SSD\",\"Resolution\":\"Up to 8K\",\"FPS\":\"Up to 120fps\",\"Ray Tracing\":\"Yes\",\"HDR\":\"Yes\"},\"features\":[],\"warranty_months\":12,\"warranty_info\":\"\",\"is_featured\":1,\"is_new_arrival\":0,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"\",\"meta_desc\":\"\",\"meta_keywords\":null,\"status\":\"active\",\"views\":2215,\"total_sold\":160,\"avg_rating\":\"0.00\",\"review_count\":0,\"created_at\":\"2026-07-14 14:46:29\",\"updated_at\":\"2026-07-14 15:18:56\",\"category_name\":\"Gaming Consoles\",\"category_slug\":\"gaming\",\"brand_name\":\"Sony\",\"brand_slug\":\"sony\",\"brand_logo\":null,\"brand_website\":\"https:\\/\\/sony.com\",\"supplier_name\":\"ElectroWorld Distributors\",\"tax_rate\":\"12.50\",\"effective_price\":\"7999.00\",\"flash_end\":null,\"flash_id\":null,\"flash_qty_limit\":null,\"flash_remaining\":null,\"images\":[{\"id\":8,\"product_id\":1,\"image_path\":\"\",\"alt_text\":\"Sony PlayStation 5\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 14:46:29\"},{\"id\":20,\"product_id\":1,\"image_path\":\"\",\"alt_text\":\"Sony PlayStation 5\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 15:05:07\"},{\"id\":32,\"product_id\":1,\"image_path\":\"\",\"alt_text\":\"Sony PlayStation 5\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 15:06:35\"},{\"id\":37,\"product_id\":1,\"image_path\":\"products\\/img_6a565328d9ef57.93765745.jpg\",\"alt_text\":null,\"is_primary\":0,\"sort_order\":4,\"created_at\":\"2026-07-14 15:18:00\"}],\"tags\":[{\"tag_name\":\"Gaming\"},{\"tag_name\":\"Next-Gen\"},{\"tag_name\":\"4K\"},{\"tag_name\":\"PlayStation\"},{\"tag_name\":\"Gaming\"},{\"tag_name\":\"Next-Gen\"},{\"tag_name\":\"4K\"},{\"tag_name\":\"PlayStation\"},{\"tag_name\":\"Gaming\"},{\"tag_name\":\"Next-Gen\"},{\"tag_name\":\"4K\"},{\"tag_name\":\"PlayStation\"}],\"faqs\":[],\"videos\":[]}', '{\"category_id\":8,\"brand_id\":6,\"supplier_id\":\"2\",\"product_name\":\"Sony PlayStation 5 Console\",\"model\":\"CFI-1218A01X\",\"sku\":\"SON-PS5-DISC\",\"barcode\":\"5901234567897\",\"price\":7999,\"compare_price\":8999,\"cost_price\":5800,\"quantity\":8,\"low_stock_alert\":3,\"weight_kg\":4.5,\"short_description\":\"Sony PS5 gaming console with DualSense controller\",\"description\":\"Experience lightning-fast loading, stunning graphics, and next-gen gameplay with custom SSD and DualSense controller.\",\"specifications\":{\"CPU\":\"AMD Zen 2 8 cores 3.5GHz\",\"GPU\":\"AMD RDNA 2 10.28 TFLOPS\",\"RAM\":\"16GB GDDR6\",\"Storage\":\"825GB Custom NVMe SSD\",\"Resolution\":\"Up to 8K\",\"FPS\":\"Up to 120fps\",\"Ray Tracing\":\"Yes\",\"HDR\":\"Yes\"},\"warranty_months\":12,\"warranty_info\":\"\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"\",\"meta_desc\":\"\",\"meta_keywords\":\"\",\"status\":\"active\",\"tags\":\"Gaming, Next-Gen, 4K, PlayStation, Gaming, Next-Gen, 4K, PlayStation, Gaming, Next-Gen, 4K, PlayStation\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:18:56'),
(8, 1, 'update_product', 'products', 'products', 2, '{\"id\":2,\"category_id\":10,\"brand_id\":14,\"supplier_id\":2,\"product_name\":\"JBL Tune 760NC Wireless Headphones\",\"slug\":\"jbl-tune-760nc\",\"model\":\"JBLT760NCBLKAM\",\"sku\":\"JBL-T760-BLK\",\"barcode\":\"5901234567898\",\"price\":\"899.00\",\"compare_price\":\"1199.00\",\"cost_price\":\"650.00\",\"tax_id\":1,\"quantity\":50,\"low_stock_alert\":10,\"weight_kg\":\"0.218\",\"dimensions\":null,\"description\":\"Enjoy powerful JBL Pure Bass sound with up to 50 hours playback and Adaptive Noise Cancelling.\",\"short_description\":\"JBL wireless headphones with 50hr battery and ANC\",\"specifications\":{\"Driver\":\"40mm\",\"Frequency\":\"20Hz-20kHz\",\"Noise Cancelling\":\"Active Noise Cancelling (ANC)\",\"Battery\":\"50 hours ANC off 35 hours ANC on\",\"Charging\":\"USB-C fast charge 5min=2hrs\",\"Bluetooth\":\"5.0 multipoint\"},\"features\":[],\"warranty_months\":12,\"warranty_info\":null,\"is_featured\":0,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":null,\"meta_desc\":null,\"meta_keywords\":null,\"status\":\"active\",\"views\":1652,\"total_sold\":280,\"avg_rating\":\"0.00\",\"review_count\":0,\"created_at\":\"2026-07-14 14:46:29\",\"updated_at\":\"2026-07-14 15:19:41\",\"category_name\":\"Audio Equipment\",\"category_slug\":\"audio\",\"brand_name\":\"JBL\",\"brand_slug\":\"jbl\",\"brand_logo\":null,\"brand_website\":\"https:\\/\\/jbl.com\",\"supplier_name\":\"ElectroWorld Distributors\",\"tax_rate\":\"12.50\",\"effective_price\":\"749.00\",\"flash_end\":\"2026-07-15 14:46:29\",\"flash_id\":1,\"flash_qty_limit\":30,\"flash_remaining\":30,\"images\":[{\"id\":9,\"product_id\":2,\"image_path\":\"\",\"alt_text\":\"JBL Tune 760NC\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 14:46:29\"},{\"id\":21,\"product_id\":2,\"image_path\":\"\",\"alt_text\":\"JBL Tune 760NC\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 15:05:07\"},{\"id\":33,\"product_id\":2,\"image_path\":\"\",\"alt_text\":\"JBL Tune 760NC\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 15:06:35\"}],\"tags\":[{\"tag_name\":\"Wireless\"},{\"tag_name\":\"ANC\"},{\"tag_name\":\"Bass\"},{\"tag_name\":\"JBL\"},{\"tag_name\":\"Wireless\"},{\"tag_name\":\"ANC\"},{\"tag_name\":\"Bass\"},{\"tag_name\":\"JBL\"},{\"tag_name\":\"Wireless\"},{\"tag_name\":\"ANC\"},{\"tag_name\":\"Bass\"},{\"tag_name\":\"JBL\"}],\"faqs\":[],\"videos\":[]}', '{\"category_id\":10,\"brand_id\":14,\"supplier_id\":\"2\",\"product_name\":\"JBL Tune 760NC Wireless Headphones\",\"model\":\"JBLT760NCBLKAM\",\"sku\":\"JBL-T760-BLK\",\"barcode\":\"5901234567898\",\"price\":899,\"compare_price\":1199,\"cost_price\":650,\"quantity\":50,\"low_stock_alert\":10,\"weight_kg\":0.218,\"short_description\":\"JBL wireless headphones with 50hr battery and ANC\",\"description\":\"Enjoy powerful JBL Pure Bass sound with up to 50 hours playback and Adaptive Noise Cancelling.\",\"specifications\":{\"Driver\":\"40mm\",\"Frequency\":\"20Hz-20kHz\",\"Noise Cancelling\":\"Active Noise Cancelling (ANC)\",\"Battery\":\"50 hours ANC off 35 hours ANC on\",\"Charging\":\"USB-C fast charge 5min=2hrs\",\"Bluetooth\":\"5.0 multipoint\"},\"warranty_months\":12,\"warranty_info\":\"\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"\",\"meta_desc\":\"\",\"meta_keywords\":\"\",\"status\":\"active\",\"tags\":\"Wireless, ANC, Bass, JBL, Wireless, ANC, Bass, JBL, Wireless, ANC, Bass, JBL\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:19:41'),
(9, 1, 'update_product', 'products', 'products', 2, '{\"id\":2,\"category_id\":10,\"brand_id\":14,\"supplier_id\":2,\"product_name\":\"JBL Tune 760NC Wireless Headphones\",\"slug\":\"jbl-tune-760nc\",\"model\":\"JBLT760NCBLKAM\",\"sku\":\"JBL-T760-BLK\",\"barcode\":\"5901234567898\",\"price\":\"899.00\",\"compare_price\":\"1199.00\",\"cost_price\":\"650.00\",\"tax_id\":1,\"quantity\":50,\"low_stock_alert\":10,\"weight_kg\":\"0.218\",\"dimensions\":null,\"description\":\"Enjoy powerful JBL Pure Bass sound with up to 50 hours playback and Adaptive Noise Cancelling.\",\"short_description\":\"JBL wireless headphones with 50hr battery and ANC\",\"specifications\":{\"Driver\":\"40mm\",\"Frequency\":\"20Hz-20kHz\",\"Noise Cancelling\":\"Active Noise Cancelling (ANC)\",\"Battery\":\"50 hours ANC off 35 hours ANC on\",\"Charging\":\"USB-C fast charge 5min=2hrs\",\"Bluetooth\":\"5.0 multipoint\"},\"features\":[],\"warranty_months\":12,\"warranty_info\":\"\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"\",\"meta_desc\":\"\",\"meta_keywords\":null,\"status\":\"active\",\"views\":1657,\"total_sold\":280,\"avg_rating\":\"0.00\",\"review_count\":0,\"created_at\":\"2026-07-14 14:46:29\",\"updated_at\":\"2026-07-14 15:21:03\",\"category_name\":\"Audio Equipment\",\"category_slug\":\"audio\",\"brand_name\":\"JBL\",\"brand_slug\":\"jbl\",\"brand_logo\":null,\"brand_website\":\"https:\\/\\/jbl.com\",\"supplier_name\":\"ElectroWorld Distributors\",\"tax_rate\":\"12.50\",\"effective_price\":\"749.00\",\"flash_end\":\"2026-07-15 14:46:29\",\"flash_id\":1,\"flash_qty_limit\":30,\"flash_remaining\":30,\"images\":[{\"id\":9,\"product_id\":2,\"image_path\":\"\",\"alt_text\":\"JBL Tune 760NC\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 14:46:29\"},{\"id\":21,\"product_id\":2,\"image_path\":\"\",\"alt_text\":\"JBL Tune 760NC\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 15:05:07\"},{\"id\":33,\"product_id\":2,\"image_path\":\"\",\"alt_text\":\"JBL Tune 760NC\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 15:06:35\"}],\"tags\":[{\"tag_name\":\"Wireless\"},{\"tag_name\":\"ANC\"},{\"tag_name\":\"Bass\"},{\"tag_name\":\"JBL\"},{\"tag_name\":\"Wireless\"},{\"tag_name\":\"ANC\"},{\"tag_name\":\"Bass\"},{\"tag_name\":\"JBL\"},{\"tag_name\":\"Wireless\"},{\"tag_name\":\"ANC\"},{\"tag_name\":\"Bass\"},{\"tag_name\":\"JBL\"}],\"faqs\":[],\"videos\":[]}', '{\"category_id\":10,\"brand_id\":14,\"supplier_id\":\"2\",\"product_name\":\"JBL Tune 760NC Wireless Headphones\",\"model\":\"JBLT760NCBLKAM\",\"sku\":\"JBL-T760-BLK\",\"barcode\":\"5901234567898\",\"price\":899,\"compare_price\":1199,\"cost_price\":650,\"quantity\":50,\"low_stock_alert\":10,\"weight_kg\":0.218,\"short_description\":\"JBL wireless headphones with 50hr battery and ANC\",\"description\":\"Enjoy powerful JBL Pure Bass sound with up to 50 hours playback and Adaptive Noise Cancelling.\",\"specifications\":{\"Driver\":\"40mm\",\"Frequency\":\"20Hz-20kHz\",\"Noise Cancelling\":\"Active Noise Cancelling (ANC)\",\"Battery\":\"50 hours ANC off 35 hours ANC on\",\"Charging\":\"USB-C fast charge 5min=2hrs\",\"Bluetooth\":\"5.0 multipoint\"},\"warranty_months\":12,\"warranty_info\":\"\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"\",\"meta_desc\":\"\",\"meta_keywords\":\"\",\"status\":\"active\",\"tags\":\"Wireless, ANC, Bass, JBL, Wireless, ANC, Bass, JBL, Wireless, ANC, Bass, JBL\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:21:03'),
(10, 1, 'update_product', 'products', 'products', 3, '{\"id\":3,\"category_id\":11,\"brand_id\":13,\"supplier_id\":2,\"product_name\":\"Logitech MX Keys S Wireless Keyboard\",\"slug\":\"logitech-mx-keys-s\",\"model\":\"MXKEYSBLA\",\"sku\":\"LOG-MXKEYS-S\",\"barcode\":\"5901234567901\",\"price\":\"549.00\",\"compare_price\":\"699.00\",\"cost_price\":\"380.00\",\"tax_id\":1,\"quantity\":45,\"low_stock_alert\":8,\"weight_kg\":\"0.810\",\"dimensions\":null,\"description\":\"The MX Keys S features spherically-shaped keys for accurate comfortable typing with multi-device connectivity for up to 3 devices.\",\"short_description\":\"Logitech wireless keyboard for up to 3 devices\",\"specifications\":{\"Layout\":\"Full-size with numpad\",\"Connectivity\":\"Bluetooth USB receiver (Bolt)\",\"Battery\":\"Up to 10 days backlight on 5 months off\",\"Backlight\":\"Intelligent illumination per key\",\"Devices\":\"Up to 3 simultaneous\",\"Compatibility\":\"Windows macOS Linux iOS Android\"},\"features\":[],\"warranty_months\":24,\"warranty_info\":null,\"is_featured\":0,\"is_new_arrival\":0,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":null,\"meta_desc\":null,\"meta_keywords\":null,\"status\":\"active\",\"views\":762,\"total_sold\":135,\"avg_rating\":\"0.00\",\"review_count\":0,\"created_at\":\"2026-07-14 14:46:29\",\"updated_at\":\"2026-07-14 15:23:30\",\"category_name\":\"Accessories\",\"category_slug\":\"accessories\",\"brand_name\":\"Logitech\",\"brand_slug\":\"logitech\",\"brand_logo\":null,\"brand_website\":\"https:\\/\\/logitech.com\",\"supplier_name\":\"ElectroWorld Distributors\",\"tax_rate\":\"12.50\",\"effective_price\":\"549.00\",\"flash_end\":null,\"flash_id\":null,\"flash_qty_limit\":null,\"flash_remaining\":null,\"images\":[{\"id\":12,\"product_id\":3,\"image_path\":\"\",\"alt_text\":\"Logitech MX Keys S\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 14:46:29\"},{\"id\":24,\"product_id\":3,\"image_path\":\"\",\"alt_text\":\"Logitech MX Keys S\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 15:05:07\"},{\"id\":36,\"product_id\":3,\"image_path\":\"\",\"alt_text\":\"Logitech MX Keys S\",\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 15:06:35\"}],\"tags\":[{\"tag_name\":\"Wireless\"},{\"tag_name\":\"Keyboard\"},{\"tag_name\":\"Logitech\"},{\"tag_name\":\"Multi-Device\"},{\"tag_name\":\"Wireless\"},{\"tag_name\":\"Keyboard\"},{\"tag_name\":\"Logitech\"},{\"tag_name\":\"Multi-Device\"},{\"tag_name\":\"Wireless\"},{\"tag_name\":\"Keyboard\"},{\"tag_name\":\"Logitech\"},{\"tag_name\":\"Multi-Device\"}],\"faqs\":[],\"videos\":[]}', '{\"category_id\":11,\"brand_id\":13,\"supplier_id\":\"2\",\"product_name\":\"Logitech MX Keys S Wireless Keyboard\",\"model\":\"MXKEYSBLA\",\"sku\":\"LOG-MXKEYS-S\",\"barcode\":\"5901234567901\",\"price\":549,\"compare_price\":699,\"cost_price\":380,\"quantity\":45,\"low_stock_alert\":8,\"weight_kg\":0.81,\"short_description\":\"Logitech wireless keyboard for up to 3 devices\",\"description\":\"The MX Keys S features spherically-shaped keys for accurate comfortable typing with multi-device connectivity for up to 3 devices.\",\"specifications\":{\"Layout\":\"Full-size with numpad\",\"Connectivity\":\"Bluetooth USB receiver (Bolt)\",\"Battery\":\"Up to 10 days backlight on 5 months off\",\"Backlight\":\"Intelligent illumination per key\",\"Devices\":\"Up to 3 simultaneous\",\"Compatibility\":\"Windows macOS Linux iOS Android\"},\"warranty_months\":24,\"warranty_info\":\"\",\"is_featured\":0,\"is_new_arrival\":0,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"\",\"meta_desc\":\"\",\"meta_keywords\":\"\",\"status\":\"active\",\"tags\":\"Wireless, Keyboard, Logitech, Multi-Device, Wireless, Keyboard, Logitech, Multi-Device, Wireless, Keyboard, Logitech, Multi-Device\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:23:30'),
(11, 1, 'logout', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:23:41'),
(12, 16, 'login', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:23:55'),
(13, 16, 'logout', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:41:21'),
(14, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:45:49'),
(15, 1, 'create_product', 'products', 'products', 28, NULL, '{\"category_id\":14,\"brand_id\":2,\"supplier_id\":null,\"product_name\":\"samsung-galaxy-s24-ultra\",\"model\":\"SM-4343A\",\"sku\":\"SAM-1784044296\",\"barcode\":\"235233232346\",\"price\":5000,\"compare_price\":4999,\"cost_price\":5000,\"quantity\":1,\"low_stock_alert\":5,\"weight_kg\":null,\"short_description\":\"samsung-galaxy-s24-ultra\",\"description\":\"samsung-galaxy-s24-ultra\",\"specifications\":{\"250GB\":\"10INCHH\"},\"warranty_months\":12,\"warranty_info\":\"12 months full warranty\",\"is_featured\":0,\"is_new_arrival\":0,\"is_best_seller\":0,\"allow_reviews\":1,\"meta_title\":\"samsung-galaxy-s24-ultra\",\"meta_desc\":\"\",\"meta_keywords\":\"SMARTPHONE\",\"status\":\"active\",\"tags\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 15:51:36'),
(16, 1, 'logout', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 16:19:52'),
(17, NULL, 'password_reset', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 16:20:48'),
(18, 16, 'login', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 16:21:06'),
(19, 16, 'logout', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 19:57:19'),
(20, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 19:58:53'),
(21, 1, 'logout', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 20:04:33'),
(22, 16, 'login', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 20:05:10'),
(23, 16, 'logout', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 21:23:17'),
(24, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 21:23:44'),
(25, 1, 'create_product', 'products', 'products', 29, NULL, '{\"category_id\":1,\"brand_id\":2,\"supplier_id\":null,\"product_name\":\"samsung-galaxy-s25-ultra\",\"model\":\"SM-S938\",\"sku\":\"SAM-1784065523\",\"barcode\":\"235236532346\",\"price\":9000,\"compare_price\":8500,\"cost_price\":8000,\"quantity\":1,\"low_stock_alert\":5,\"weight_kg\":null,\"short_description\":\"The Samsung Galaxy S25 Ultra 256GB is a premium, flagship smartphone featuring a 6.9-inch display, a lightweight titanium frame, and an integrated S Pen stylus.\",\"description\":\"The Samsung Galaxy S25 Ultra 256GB is a premium, flagship smartphone featuring a 6.9-inch display, a lightweight titanium frame, and an integrated S Pen stylus. It runs on the high-performance Snapdragon 8 Elite processor, offers an advanced 200MP quad-camera system with 5x optical zoom, and includes built-in Galaxy AI features.\",\"specifications\":{\"6.9\\\" Quad HD+ (3120 x 1440) Dynamic LTPO AMOLED 2X, up to 120Hz refresh rate and 2600 nits peak brightness\":\"6.9-inch Dynamic LTPO AMOLED\"},\"warranty_months\":12,\"warranty_info\":\"12 months full warranty\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"samsung-galaxy-s24-ultra\",\"meta_desc\":\"The Samsung Galaxy S25 Ultra 256GB is a premium, flagship smartphone featuring a 6.9-inch display, a lightweight titanium frame, and an integrated S Pen stylus.\",\"meta_keywords\":\"SMARTPHONE\",\"status\":\"active\",\"tags\":\"5G, Android\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 21:45:23'),
(26, 1, 'create_product', 'products', 'products', 30, NULL, '{\"category_id\":2,\"brand_id\":5,\"supplier_id\":null,\"product_name\":\"Lenovo\",\"model\":\"deaPad Slim 3 15IAN8\",\"sku\":\"LEN-1784066463\",\"barcode\":\"41278255344257\",\"price\":7630,\"compare_price\":7300,\"cost_price\":7000,\"quantity\":1,\"low_stock_alert\":5,\"weight_kg\":null,\"short_description\":\"The IdeaPad Slim 3 15IAN8 delivers reliable everyday computing in a clean, portable design. Built for students, professionals, and home users who need performance without complexity.\",\"description\":\"The IdeaPad Slim 3 15IAN8 delivers reliable everyday computing in a clean, portable design. Built for students, professionals, and home users who need performance without complexity.\\r\\n\\r\\nThe Intel Core i3-N305 processor handles multitasking, web browsing, and productivity apps with ease. 8GB of RAM keeps your workflow smooth across documents, spreadsheets, and streaming. The 256GB SSD ensures fast boot times and responsive application loading for everyday tasks.\\r\\n\\r\\nLenovo engineering meets practical value in a laptop designed to simply work.\",\"specifications\":{\"15.6 Inches\":\"15.6 Inches\"},\"warranty_months\":12,\"warranty_info\":\"1 year warranty\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"lenovo-ideapad\",\"meta_desc\":\"The IdeaPad Slim 3 15IAN8 delivers reliable everyday computing in a clean, portable design\",\"meta_keywords\":\"windows 11\",\"status\":\"active\",\"tags\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-14 22:01:03'),
(27, 1, 'create_product', 'products', 'products', 31, NULL, '{\"category_id\":2,\"brand_id\":3,\"supplier_id\":null,\"product_name\":\"HP Pavilion Laptop\",\"model\":\"15-eg3198nia (AE3Q9EA)\",\"sku\":\"HP -1784080988\",\"barcode\":\"535727y28324776\",\"price\":12900,\"compare_price\":12600,\"cost_price\":12500,\"quantity\":1,\"low_stock_alert\":5,\"weight_kg\":null,\"short_description\":\"The HP Pavilion 15-eg3198nia pairs Intel&#039;s 13th Gen Core i7-1355U processor with a responsive touchscreen display for seamless productivity and creative work.\",\"description\":\"The HP Pavilion 15-eg3198nia pairs Intel\'s 13th Gen Core i7-1355U processor with a responsive touchscreen display for seamless productivity and creative work. Built for professionals and students who demand performance without compromise.\\r\\n\\r\\nThe 10-core i7-1355U processor handles multitasking with precision, whether you\'re running design software, managing data, or streaming content. 16GB RAM and a spacious 1TB SSD deliver the speed and storage needed for modern workflows. The 15.6-inch FHD IPS touchscreen adds intuitive control to every task.\\r\\n\\r\\nHP engineering meets premium design in a laptop that delivers where it counts.\\r\\n\\r\\nKey Features\\r\\n13th Gen Intel Core i7-1355U Processor: 10-core hybrid architecture with up to 5.0GHz turbo for responsive multitasking and demanding applications\\r\\n\\r\\n16GB DDR4 RAM: Ample memory for seamless switching between heavy workloads, browser tabs, and creative software\\r\\n\\r\\n1TB PCIe NVMe SSD: Fast boot times, rapid file access, and generous storage for your entire digital library\\r\\n\\r\\n15.6-inch FHD IPS Touchscreen: 1920x1080 resolution with wide viewing angles and touch functionality for natural interaction\\r\\n\\r\\nIntel Iris Xe Graphics: Integrated graphics that handle photo editing, light video work, and casual gaming with ease\\r\\n\\r\\nWindows 11 Home: The latest Windows experience with enhanced productivity features and refined interface design\\r\\n\\r\\nFull-size Backlit Keyboard: Comfortable typing in any lighting condition with precision key travel\\r\\n\\r\\nPremium Silver Finish: Clean, professional aesthetic that fits boardrooms and coffee shops alike\\r\\n\\r\\nComprehensive Connectivity: USB Type-C, USB-A, HDMI, and audio jack for all your peripherals and displays\\r\\n\\r\\nHP Fast Charge Technology: Get back to full power quickly when you need it most\",\"specifications\":{\"15.6 inch FHD IPS LED Touchscreen\":\"15.6 inch FHD IPS LED Touchscreen\"},\"warranty_months\":12,\"warranty_info\":\"1 year warranty\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"HP Pavilion Laptop\",\"meta_desc\":\"The HP Pavilion 15-eg3198nia pairs Intel&#039;s 13th Gen Core i7-1355U processor with a responsive touchscreen display for seamless productivity and creative work.\",\"meta_keywords\":\"windows 11\",\"status\":\"active\",\"tags\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 02:03:08'),
(28, 1, 'create_product', 'products', 'products', 32, NULL, '{\"category_id\":5,\"brand_id\":3,\"supplier_id\":null,\"product_name\":\"HP Pro Tower\",\"model\":\"Small Form Factor (SFF)\",\"sku\":\"HP -1784082107\",\"barcode\":\"7276388W78U9479\",\"price\":9990,\"compare_price\":9590,\"cost_price\":9090,\"quantity\":1,\"low_stock_alert\":5,\"weight_kg\":null,\"short_description\":\"HP Pro Tower 290 G9 SFF Core i5-12400 16GB 512GB SSD DVDRW Win11 Pro + 22inches Monitor - Complete\",\"description\":\"The HP Pro Tower 290 G9 delivers 12th Gen Intel performance in a space-saving small form factor, complete with a matching 22-inch display. Built for professionals who need reliable desktop power without compromising desk real estate.\\r\\n\\r\\nThe Intel Core i5-12400 processor handles multitasking, productivity software, and business applications with ease. 16GB of RAM ensures smooth operation across multiple browser tabs, office suites, and video calls. The 512GB SSD delivers fast boot times and application launches, while Windows 11 Pro brings enterprise-grade security and remote management tools.\\r\\n\\r\\nHP\'s commercial-grade engineering means this system is built to run all day, every day.\\r\\n\\r\\nKey Features\\r\\n12th Gen Intel Core i5-12400: Six-core processor with hybrid architecture for efficient multitasking and productivity performance\\r\\n\\r\\n16GB DDR4 Memory: Ample RAM for seamless multitasking across business applications and data-intensive workflows\\r\\n\\r\\n512GB NVMe SSD: Fast storage for quick system responsiveness and ample space for documents and files\\r\\n\\r\\nSmall Form Factor Design: Space-efficient chassis that fits comfortably in compact workspaces without sacrificing expandability\\r\\n\\r\\nWindows 11 Pro: Professional operating system with remote desktop\\r\\n\\r\\n22-Inch Display Included: Complete desktop solution with matched monitor for immediate productivity out of the box\\r\\n\\r\\nHP Pro Series Build Quality: Commercial-grade reliability with rigorous testing for business-critical environments\\r\\n\\r\\nMultiple I\\/O Ports: USB Type-A and Type-C connectivity for peripherals, displays, and modern accessories\\r\\n\\r\\nTool-Free Serviceability: Easy access to internal components for upgrades and maintenance without specialized tools\",\"specifications\":{\"22-inch Monitor Included\":\"22-inch Monitor Included\"},\"warranty_months\":12,\"warranty_info\":\"1 year warranty\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"HP Pro Tower\",\"meta_desc\":\"HP Pro Tower 290 G9 SFF Core i5-12400 16GB 512GB SSD DVDRW Win11 Pro + 22inches Monitor - Complete\",\"meta_keywords\":\"windows 11 Pro\",\"status\":\"active\",\"tags\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 02:21:47'),
(29, 1, 'update_product', 'products', 'products', 32, '{\"id\":32,\"category_id\":5,\"brand_id\":3,\"supplier_id\":null,\"product_name\":\"HP Pro Tower\",\"slug\":\"hp-pro-tower\",\"model\":\"Small Form Factor (SFF)\",\"sku\":\"HP -1784082107\",\"barcode\":\"7276388W78U9479\",\"price\":\"9990.00\",\"compare_price\":\"9590.00\",\"cost_price\":\"9090.00\",\"tax_id\":null,\"quantity\":1,\"low_stock_alert\":5,\"weight_kg\":null,\"dimensions\":null,\"description\":\"The HP Pro Tower 290 G9 delivers 12th Gen Intel performance in a space-saving small form factor, complete with a matching 22-inch display. Built for professionals who need reliable desktop power without compromising desk real estate.\\r\\n\\r\\nThe Intel Core i5-12400 processor handles multitasking, productivity software, and business applications with ease. 16GB of RAM ensures smooth operation across multiple browser tabs, office suites, and video calls. The 512GB SSD delivers fast boot times and application launches, while Windows 11 Pro brings enterprise-grade security and remote management tools.\\r\\n\\r\\nHP\'s commercial-grade engineering means this system is built to run all day, every day.\\r\\n\\r\\nKey Features\\r\\n12th Gen Intel Core i5-12400: Six-core processor with hybrid architecture for efficient multitasking and productivity performance\\r\\n\\r\\n16GB DDR4 Memory: Ample RAM for seamless multitasking across business applications and data-intensive workflows\\r\\n\\r\\n512GB NVMe SSD: Fast storage for quick system responsiveness and ample space for documents and files\\r\\n\\r\\nSmall Form Factor Design: Space-efficient chassis that fits comfortably in compact workspaces without sacrificing expandability\\r\\n\\r\\nWindows 11 Pro: Professional operating system with remote desktop\\r\\n\\r\\n22-Inch Display Included: Complete desktop solution with matched monitor for immediate productivity out of the box\\r\\n\\r\\nHP Pro Series Build Quality: Commercial-grade reliability with rigorous testing for business-critical environments\\r\\n\\r\\nMultiple I\\/O Ports: USB Type-A and Type-C connectivity for peripherals, displays, and modern accessories\\r\\n\\r\\nTool-Free Serviceability: Easy access to internal components for upgrades and maintenance without specialized tools\",\"short_description\":\"HP Pro Tower 290 G9 SFF Core i5-12400 16GB 512GB SSD DVDRW Win11 Pro + 22inches Monitor - Complete\",\"specifications\":{\"22-inch Monitor Included\":\"22-inch Monitor Included\"},\"features\":[],\"warranty_months\":12,\"warranty_info\":\"1 year warranty\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":0,\"allow_reviews\":1,\"meta_title\":\"HP Pro Tower\",\"meta_desc\":\"HP Pro Tower 290 G9 SFF Core i5-12400 16GB 512GB SSD DVDRW Win11 Pro + 22inches Monitor - Complete\",\"meta_keywords\":null,\"status\":\"active\",\"views\":2,\"total_sold\":0,\"avg_rating\":\"0.00\",\"review_count\":0,\"created_at\":\"2026-07-15 02:21:47\",\"updated_at\":\"2026-07-15 02:38:56\",\"category_name\":\"Desktop Computers\",\"category_slug\":\"desktops\",\"brand_name\":\"HP\",\"brand_slug\":\"hp\",\"brand_logo\":null,\"brand_website\":\"https:\\/\\/hp.com\",\"supplier_name\":null,\"tax_rate\":null,\"effective_price\":\"9990.00\",\"flash_end\":null,\"flash_id\":null,\"flash_qty_limit\":null,\"flash_remaining\":null,\"images\":[{\"id\":45,\"product_id\":32,\"image_path\":\"products\\/img_6a56eebbee65e5.24748091.png\",\"alt_text\":null,\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-15 02:21:47\"}],\"tags\":[],\"faqs\":[],\"videos\":[]}', '{\"category_id\":10,\"brand_id\":14,\"supplier_id\":null,\"product_name\":\"JBL Tune 770NC\",\"model\":\"Small Form Factor (SFF)\",\"sku\":\"HP -1784082107\",\"barcode\":\"53t86388W78U9479\",\"price\":9990,\"compare_price\":9590,\"cost_price\":9090,\"quantity\":1,\"low_stock_alert\":5,\"weight_kg\":220,\"short_description\":\"JBL Tune 770NC Adaptive Noise Cancelling Headphones\",\"description\":\"Equipped with Adaptive Noise Cancelling technology and JBL Pure Bass sound, the JBL Tune 770NC offers an immersive, distraction-free listening experience. Featuring Bluetooth 5.3 connectivity and multi-point pairing, it allows smooth transitions between devices, whether you\\u2019re working or on the move.\\r\\n\\r\\nThe 70-hour battery life (or 44 hours with ANC on) ensures long-lasting performance, while fast charging delivers up to 3 hours of playback in just 5 minutes. With Lightweight over-ear comfort, customizable EQ through the JBL Headphones app, and Voice Assistant support, the JBL-TUNE770NC blends premium sound, noise control, and smart connectivity into a sleek, travel-ready headset.\\r\\n\\r\\nBuilt for everyday listening and professional focus, it\\u2019s the ideal choice for users who want both performance and comfort without compromise.\\r\\n\\r\\nTop Features & Highlights:\\r\\nSound: JBL Pure Bass audio with dynamic 40mm drivers\\r\\n\\r\\nNoise Cancelling: Adaptive Noise Cancelling with Smart Ambient mode\\r\\n\\r\\nConnectivity: Bluetooth 5.3 wireless technology\\r\\n\\r\\nBattery Life: Up to 70 hours playback (44h with ANC)\\r\\n\\r\\nFast Charging: 5-minute charge = 3 hours playtime\\r\\n\\r\\nMulti-Point Connection: Seamless switch between two devices\\r\\n\\r\\nApp Support: JBL Headphones app with EQ customization\\r\\n\\r\\nVoice Assistants: Compatible with Siri and Google Assistant\\r\\n\\r\\nControls: On-ear buttons for calls, music, and ANC modes\\r\\n\\r\\nDesign: Lightweight, foldable over-ear construction\",\"specifications\":{\"Weight\":\"220g\",\"Charging\":\"USB-C, 5 minutes for 3 hours playback\",\"Microphone\":\"Build-in for calls\",\"Drivers size\":\"40mm\",\"Battery health\":\"Up to 70 hours (ANC off), Up to 55 hours (ANC on)\",\"Connectivity\":\"Bluetooth 5.3\",\"Frequency response\":\"20Hz - 20kHz\",\"Active Noise Cancelling\":\"Adaptive ANC\"},\"warranty_months\":12,\"warranty_info\":\"1 year warranty\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"HP Pro Tower\",\"meta_desc\":\"HP Pro Tower 290 G9 SFF Core i5-12400 16GB 512GB SSD DVDRW Win11 Pro + 22inches Monitor - Complete\",\"meta_keywords\":\"\",\"status\":\"active\",\"tags\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 02:38:56'),
(30, 1, 'stock_in', 'inventory', 'inventory', 32, '{\"qty\":1}', '{\"qty\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 02:42:44'),
(31, 1, 'delete_product', 'products', 'products', 32, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 02:45:29'),
(32, 1, 'delete_product', 'products', 'products', 32, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 02:45:53'),
(33, 1, 'create_product', 'products', 'products', 33, NULL, '{\"category_id\":10,\"brand_id\":14,\"supplier_id\":null,\"product_name\":\"JBL Tune 760NC Wireless Headphones\",\"model\":\"JBLT760NCBLKAM\",\"sku\":\"JBL-1784084402\",\"barcode\":\"\",\"price\":520,\"compare_price\":510,\"cost_price\":500,\"quantity\":1,\"low_stock_alert\":5,\"weight_kg\":220,\"short_description\":\"JBL Tune 770NC Adaptive Noise Cancelling Headphones\",\"description\":\"Equipped with Adaptive Noise Cancelling technology and JBL Pure Bass sound, the JBL Tune 770NC offers an immersive, distraction-free listening experience. Featuring Bluetooth 5.3 connectivity and multi-point pairing, it allows smooth transitions between devices, whether you\\u2019re working or on the move.\\r\\n\\r\\nThe 70-hour battery life (or 44 hours with ANC on) ensures long-lasting performance, while fast charging delivers up to 3 hours of playback in just 5 minutes. With Lightweight over-ear comfort, customizable EQ through the JBL Headphones app, and Voice Assistant support, the JBL-TUNE770NC blends premium sound, noise control, and smart connectivity into a sleek, travel-ready headset.\\r\\n\\r\\nBuilt for everyday listening and professional focus, it\\u2019s the ideal choice for users who want both performance and comfort without compromise.\\r\\n\\r\\nTop Features & Highlights:\\r\\nSound: JBL Pure Bass audio with dynamic 40mm drivers\\r\\n\\r\\nNoise Cancelling: Adaptive Noise Cancelling with Smart Ambient mode\\r\\n\\r\\nConnectivity: Bluetooth 5.3 wireless technology\\r\\n\\r\\nBattery Life: Up to 70 hours playback (44h with ANC)\\r\\n\\r\\nFast Charging: 5-minute charge = 3 hours playtime\\r\\n\\r\\nMulti-Point Connection: Seamless switch between two devices\\r\\n\\r\\nApp Support: JBL Headphones app with EQ customization\\r\\n\\r\\nVoice Assistants: Compatible with Siri and Google Assistant\\r\\n\\r\\nControls: On-ear buttons for calls, music, and ANC modes\\r\\n\\r\\nDesign: Lightweight, foldable over-ear construction\",\"specifications\":{\"Weight\":\"220g\",\"Charging\":\"220g\",\"Microphone\":\"Build-in for calls\",\"Drivers size\":\"40mm\",\"Battery life\":\"Up to 70 hours (ANC off), Up to 55 hours (ANC on)\",\"Connectivity\":\"Bluetooth 5.3\",\"Frequency response\":\"20Hz - 20kHz\",\"Active Noise Cancelling\":\"Adaptive ANC\"},\"warranty_months\":12,\"warranty_info\":\"1 year warranty\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"\",\"meta_desc\":\"\",\"meta_keywords\":\"\",\"status\":\"active\",\"tags\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 03:00:02'),
(34, 1, 'delete_product', 'products', 'products', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 03:01:39'),
(35, 1, 'delete_product', 'products', 'products', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 03:01:55'),
(36, 1, 'delete_product', 'products', 'products', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 03:18:11'),
(37, 1, 'delete_product', 'products', 'products', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 03:20:57'),
(38, 1, 'delete_product', 'products', 'products', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 03:21:04'),
(39, 1, 'delete_product', 'products', 'products', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 03:21:13'),
(40, 1, 'update_product', 'products', 'products', 29, '{\"id\":29,\"category_id\":1,\"brand_id\":2,\"supplier_id\":null,\"product_name\":\"samsung-galaxy-s25-ultra\",\"slug\":\"samsung-galaxy-s25-ultra\",\"model\":\"SM-S938\",\"sku\":\"SAM-1784065523\",\"barcode\":\"235236532346\",\"price\":\"9000.00\",\"compare_price\":\"8500.00\",\"cost_price\":\"8000.00\",\"tax_id\":null,\"quantity\":1,\"low_stock_alert\":5,\"weight_kg\":null,\"dimensions\":null,\"description\":\"The Samsung Galaxy S25 Ultra 256GB is a premium, flagship smartphone featuring a 6.9-inch display, a lightweight titanium frame, and an integrated S Pen stylus. It runs on the high-performance Snapdragon 8 Elite processor, offers an advanced 200MP quad-camera system with 5x optical zoom, and includes built-in Galaxy AI features.\",\"short_description\":\"The Samsung Galaxy S25 Ultra 256GB is a premium, flagship smartphone featuring a 6.9-inch display, a lightweight titanium frame, and an integrated S Pen stylus.\",\"specifications\":{\"6.9\\\" Quad HD+ (3120 x 1440) Dynamic LTPO AMOLED 2X, up to 120Hz refresh rate and 2600 nits peak brightness\":\"6.9-inch Dynamic LTPO AMOLED\"},\"features\":[],\"warranty_months\":12,\"warranty_info\":\"12 months full warranty\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":0,\"allow_reviews\":1,\"meta_title\":\"samsung-galaxy-s24-ultra\",\"meta_desc\":\"The Samsung Galaxy S25 Ultra 256GB is a premium, flagship smartphone featuring a 6.9-inch display, a lightweight titanium frame, and an integrated S Pen stylus.\",\"meta_keywords\":null,\"status\":\"active\",\"views\":4,\"total_sold\":0,\"avg_rating\":\"0.00\",\"review_count\":0,\"created_at\":\"2026-07-14 21:45:23\",\"updated_at\":\"2026-07-15 03:24:31\",\"category_name\":\"Smartphones\",\"category_slug\":\"smartphones\",\"brand_name\":\"Samsung\",\"brand_slug\":\"samsung\",\"brand_logo\":null,\"brand_website\":\"https:\\/\\/samsung.com\",\"supplier_name\":null,\"tax_rate\":null,\"effective_price\":\"9000.00\",\"flash_end\":null,\"flash_id\":null,\"flash_qty_limit\":null,\"flash_remaining\":null,\"images\":[{\"id\":42,\"product_id\":29,\"image_path\":\"products\\/img_6a56adf3afa101.72959666.jpg\",\"alt_text\":null,\"is_primary\":1,\"sort_order\":1,\"created_at\":\"2026-07-14 21:45:23\"}],\"tags\":[{\"tag_name\":\"5G\"},{\"tag_name\":\"Android\"}],\"faqs\":[],\"videos\":[]}', '{\"category_id\":1,\"brand_id\":2,\"supplier_id\":null,\"product_name\":\"samsung-galaxy-s25-ultra\",\"model\":\"SM-S938\",\"sku\":\"SAM-1784065523\",\"barcode\":\"235236532346\",\"price\":9000,\"compare_price\":8500,\"cost_price\":8000,\"quantity\":8,\"low_stock_alert\":5,\"weight_kg\":null,\"short_description\":\"The Samsung Galaxy S25 Ultra 256GB is a premium, flagship smartphone featuring a 6.9-inch display, a lightweight titanium frame, and an integrated S Pen stylus.\",\"description\":\"The Samsung Galaxy S25 Ultra 256GB is a premium, flagship smartphone featuring a 6.9-inch display, a lightweight titanium frame, and an integrated S Pen stylus. It runs on the high-performance Snapdragon 8 Elite processor, offers an advanced 200MP quad-camera system with 5x optical zoom, and includes built-in Galaxy AI features.\",\"specifications\":{\"6.9\\\" Quad HD+ (3120 x 1440) Dynamic LTPO AMOLED 2X, up to 120Hz refresh rate and 2600 nits peak brightness\":\"6.9-inch Dynamic LTPO AMOLED\"},\"warranty_months\":12,\"warranty_info\":\"12 months full warranty\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"samsung-galaxy-s24-ultra\",\"meta_desc\":\"The Samsung Galaxy S25 Ultra 256GB is a premium, flagship smartphone featuring a 6.9-inch display, a lightweight titanium frame, and an integrated S Pen stylus.\",\"meta_keywords\":\"\",\"status\":\"active\",\"tags\":\"5G, Android\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 03:24:31'),
(41, 1, 'update_order_status', 'orders', 'orders', 2, '{\"status\":\"pending\"}', '{\"status\":\"pending\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-07-15 03:30:40');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(42, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-07-15 21:58:41'),
(43, 1, 'create_product', 'products', 'products', 34, NULL, '{\"category_id\":1,\"brand_id\":1,\"supplier_id\":\"1\",\"product_name\":\"Apple iPhone 12 Pro Max - 6.7&quot; - 256GB ROM - 6GB RAM - 12MP Rear\\/12MP Front - 3687 mAh\",\"model\":\"iPhone 12 Pro Max\",\"sku\":\"APP-1784155811\",\"barcode\":\"\",\"price\":7350,\"compare_price\":7200,\"cost_price\":6830,\"quantity\":8,\"low_stock_alert\":3,\"weight_kg\":3,\"short_description\":\"Apple iPhone 12 Pro Max - 6.7&quot; - 256GB ROM - 6GB RAM - 12MP Rear\\/12MP Front - 3687 mAh\",\"description\":\"The iPhone 12 Pro Max standing as the larger of the two pro-level models. This device has a bigger 6.7-inch Super Retina XDR display compared to the iPhone 11 Pro Max. It also has a triple-camera system and LiDAR. round the back.\\r\\nApple has gone with the same camera arrangement as in 2019, with three 12-megapixel cameras covering Ultra Wide, Wide, and Telephoto ranges, with a 4x optical zoom in, 2x optical zoom out, and a 10x digital zoom in. Equipped with dual optical image stabilization, it has Portrait Mode and Portrait Lighting effects, a Night mode, Smart HDR, and Panorama features.\\r\\nThe optical image stabilization has been upgraded to a DSLR-style Sensor Shift, where the sensor moves but the lens does not, enabling the image to stay sharper, and for longer exposures to be made that capture more light, even up to two seconds long when hand-held. It can adjust up to 5,000 times per second, approximately five times as many adjustments than similar systems used in the iPhone 11 Pro range.\\r\\nApple also introduced Apple ProRAW for its Pro line, a new imaging format combining RAW photography with computational photography features like Deep Fusion and Smart HDR. This includes having full control over color, details, and dynamic range, all from the iPhone\'s Camera app.Video has been boosted to include the ability to record in 10-bit HDR, and is the first to record in Dolby Vision HDR, something that can even be edited on the iPhone and even played through a compatible screen over AirPlay. It is able to do this even at 4K resolution at 60fps, as well as supporting 1080p slo-mo at 240fps, records stereo audio, and supports Audio Zoom.\\r\\n\\r\\n\\r\\nBuy this iPhone 12 Pro Max - 256GB HDD HDD - 6GB Now From ElectroStore Ghana And Have It Delivered Right At Your Doorstep.\\r\\n\",\"specifications\":{\"Display\":\"6.7 inches, Super Retina XDR OLED, HDR10, 800 nits (typ), 1200 nits (peak)\",\"Memory\":\"256GB HDD, 6GB RAM\",\"Camera\":\"12MP rear, 12MP front\",\"OS\":\"iOS 14.1, upgradable to iOS 14.2\",\"CPU\":\"Hexa-core (2x3.1 GHz Firestorm + 4x1.8 GHz Icestorm)\",\"Battery\":\"Li-Ion 3687 mAh, non-removable (14.13 Wh)\"},\"warranty_months\":12,\"warranty_info\":\"1 year warranty\",\"is_featured\":1,\"is_new_arrival\":1,\"is_best_seller\":1,\"allow_reviews\":1,\"meta_title\":\"iPhone 12 Pro Max\",\"meta_desc\":\"iPhone 12 Pro Max\",\"meta_keywords\":\"iOS\",\"status\":\"active\",\"tags\":\"iPhine\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-07-15 22:50:11'),
(44, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 19:06:57'),
(45, 1, 'delete_product', 'products', 'products', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 19:07:28'),
(46, 1, 'delete_product', 'products', 'products', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 19:07:44'),
(47, 1, 'logout', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 20:28:09'),
(48, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 20:29:24'),
(49, 1, 'logout', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 20:30:33'),
(50, 16, 'login', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 20:32:15'),
(51, 16, 'logout', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 20:46:13'),
(52, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 20:46:34'),
(53, 1, 'logout', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 20:52:24'),
(54, 16, 'login', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 20:52:45'),
(55, 16, 'logout', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 21:06:54'),
(56, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', '2026-08-07 21:07:14'),
(57, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-09 16:23:05'),
(58, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-11 15:55:39'),
(59, 16, 'login', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-12 13:32:50'),
(60, 16, 'logout', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-12 13:38:47'),
(61, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-12 13:39:17'),
(62, NULL, 'register', 'users', 'users', 17, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-13 16:09:55'),
(63, NULL, 'password_reset', 'users', 'users', 17, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-13 16:11:19'),
(64, 17, 'login', 'users', 'users', 17, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-13 16:11:48'),
(65, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-14 20:57:49'),
(66, 1, 'logout', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-14 21:31:47'),
(67, 16, 'login', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-14 21:35:07'),
(68, 16, 'logout', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-14 21:44:53'),
(69, 3, 'login', 'users', 'users', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-14 21:46:23'),
(70, 1, 'login', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-15 11:59:08'),
(71, 1, 'logout', 'users', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-15 11:59:54'),
(72, 16, 'login', 'users', 'users', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-15 12:11:41'),
(73, 3, 'login', 'users', 'users', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-15 12:21:56'),
(74, 3, 'update_order_status', 'orders', 'orders', 3, '{\"status\":\"pending\"}', '{\"status\":\"processing\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-15 12:23:29');

-- --------------------------------------------------------

--
-- Table structure for table `backup_logs`
--

CREATE TABLE `backup_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT 0,
  `backup_type` enum('manual','scheduled','auto') DEFAULT 'manual',
  `status` enum('success','failed','in_progress') DEFAULT 'success',
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Database backup history';

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(10) UNSIGNED NOT NULL,
  `brand_name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Product brands';

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `brand_name`, `slug`, `logo`, `website`, `description`, `country`, `status`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 'Apple', 'apple', NULL, 'https://apple.com', 'Think Different — premium consumer electronics', 'USA', 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(2, 'Samsung', 'samsung', NULL, 'https://samsung.com', 'Leader in mobile and display technology', 'South Korea', 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(3, 'HP', 'hp', NULL, 'https://hp.com', 'Trusted computing and printing solutions', 'USA', 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(4, 'Dell', 'dell', NULL, 'https://dell.com', 'Enterprise and consumer computing solutions', 'USA', 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(5, 'Lenovo', 'lenovo', NULL, 'https://lenovo.com', 'Smart technology for smarter people', 'China', 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(6, 'Sony', 'sony', NULL, 'https://sony.com', 'Audio, visual and gaming innovation', 'Japan', 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(7, 'LG', 'lg', NULL, 'https://lg.com', 'Life is Good — electronics and appliances', 'South Korea', 1, 0, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(8, 'Canon', 'canon', NULL, 'https://canon.com', 'Professional camera and imaging solutions', 'Japan', 1, 0, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(9, 'Nikon', 'nikon', NULL, 'https://nikon.com', 'I am Nikon — precision optics', 'Japan', 1, 0, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(10, 'Xiaomi', 'xiaomi', NULL, 'https://mi.com', 'Innovation for everyone', 'China', 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(11, 'Tecno', 'tecno', NULL, 'https://tecno-mobile.com', 'Stop at Nothing — Africa-focused devices', 'Ghana/China', 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(12, 'Infinix', 'infinix', NULL, 'https://infinixmobility.com', 'Dare to Leap — budget smartphones', 'Hong Kong', 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(13, 'Logitech', 'logitech', NULL, 'https://logitech.com', 'Design for people — peripherals', 'Switzerland', 1, 0, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(14, 'JBL', 'jbl', NULL, 'https://jbl.com', 'Legendary sound — audio equipment', 'USA', 1, 0, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(15, 'Huawei', 'huawei', NULL, 'https://huawei.com', 'Building a fully connected world', 'China', 1, 0, '2026-07-14 14:46:29', '2026-07-14 14:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `bundle_items`
--

CREATE TABLE `bundle_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `bundle_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Products within a bundle';

--
-- Dumping data for table `bundle_items`
--

INSERT INTO `bundle_items` (`id`, `bundle_id`, `product_id`, `quantity`) VALUES
(1, 1, 0, 1),
(2, 1, 0, 1),
(3, 2, 0, 1),
(4, 2, 2, 1),
(5, 1, 0, 1),
(6, 1, 0, 1),
(7, 2, 0, 1),
(8, 2, 2, 1),
(9, 1, 0, 1),
(10, 1, 0, 1),
(11, 2, 0, 1),
(12, 2, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `currency` char(3) DEFAULT 'GHS',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Shopping cart sessions';

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `customer_id`, `session_id`, `currency`, `created_at`, `updated_at`) VALUES
(1, NULL, 'pt7u9dcgvl9fugqv0mr04k5ou6', 'GHS', '2026-07-14 15:02:24', '2026-07-14 15:02:24'),
(2, 7, 's4052cnfrrga54a1k9vacvireg', 'GHS', '2026-07-14 15:08:56', '2026-07-14 15:08:56'),
(3, NULL, '9t2l2vog81tv4c0sbvu4mj1mgf', 'GHS', '2026-07-14 15:15:31', '2026-07-14 15:15:31'),
(4, NULL, '4ikt5ihhfdanb89luulfe3ufjj', 'GHS', '2026-07-14 15:49:56', '2026-07-14 15:49:56'),
(5, NULL, 'sga5r6k3oj74p2e59tdpq2kdo9', 'GHS', '2026-07-14 19:59:25', '2026-07-14 19:59:25'),
(6, NULL, 'nv4oa2fr0da7hs5u04mn9g2tq4', 'GHS', '2026-07-14 21:24:57', '2026-07-14 21:24:57'),
(7, NULL, 'uvjlrjr6p2jomuljb132vpk73k', 'GHS', '2026-07-15 21:58:05', '2026-07-15 21:58:05'),
(8, NULL, '9qv72gb8das6up3erra4tb0kol', 'GHS', '2026-07-15 22:00:03', '2026-07-15 22:00:03'),
(9, NULL, 'rsggdni6tctg5j1ick2pj7tear', 'GHS', '2026-08-07 19:03:20', '2026-08-07 19:03:20'),
(10, NULL, '19n446ok15sqc0qaguqcs1lk4h', 'GHS', '2026-08-07 19:08:40', '2026-08-07 19:08:40'),
(11, NULL, 'n4ehk767s6qlg8accngf69fu6l', 'GHS', '2026-08-07 20:30:26', '2026-08-07 20:30:26'),
(12, NULL, '00ou103ejaqad12a1oflrls3ps', 'GHS', '2026-08-07 20:50:29', '2026-08-07 20:50:29'),
(13, NULL, 'b31mprdcg2t7fjiv8ab15od848', 'GHS', '2026-08-07 21:07:22', '2026-08-07 21:07:22'),
(14, NULL, 'trttflvkt35efhc9e2n8e87bqf', 'GHS', '2026-08-09 16:24:47', '2026-08-09 16:24:47'),
(15, NULL, '2seq6ebbs1vkjoblhe3qioq91a', 'GHS', '2026-08-10 11:25:38', '2026-08-10 11:25:38'),
(16, NULL, 'bn7qlt1gik90hmackgbdf3e7g4', 'GHS', '2026-08-11 13:30:46', '2026-08-11 13:30:46'),
(17, NULL, 'goingf95rpm52lquflup0ulkuv', 'GHS', '2026-08-11 15:55:58', '2026-08-11 15:55:58'),
(18, NULL, 'rm3tlevjo6pa4pguu7ub3gfbb0', 'GHS', '2026-08-12 13:28:23', '2026-08-12 13:28:23'),
(19, NULL, '06oggfbh5mv4kuibihpm7ss9ni', 'GHS', '2026-08-13 16:07:40', '2026-08-13 16:07:40'),
(20, 8, 'aidljntdmgp5esdalf813j0ed0', 'GHS', '2026-08-13 16:11:48', '2026-08-13 16:11:48'),
(21, NULL, 'h3gf66ccqhv26ct254mv84qh49', 'GHS', '2026-08-14 20:56:38', '2026-08-14 20:56:38'),
(22, NULL, '5joelpc3m516qm401sununiepg', 'GHS', '2026-08-14 21:10:27', '2026-08-14 21:10:27'),
(23, NULL, 'meqav0ovfukd21vnb0pgmlk1cv', 'GHS', '2026-08-14 21:47:50', '2026-08-14 21:47:50'),
(24, NULL, 'v0ojqtvi1nd6egta25jnv8790s', 'GHS', '2026-08-15 11:47:51', '2026-08-15 11:47:51'),
(25, NULL, 'k0ad2hcs58amjpb3vu49vnd6es', 'GHS', '2026-08-15 12:00:02', '2026-08-15 12:00:02'),
(26, NULL, 's7v6t5886mdntg0vumba8f14ku', 'GHS', '2026-08-15 12:12:35', '2026-08-15 12:12:35');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `cart_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `bundle_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(12,2) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Shopping cart line items';

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `bundle_id`, `quantity`, `price`, `added_at`) VALUES
(4, 4, 1, NULL, 1, 7999.00, '2026-07-14 16:16:26'),
(5, 5, 1, NULL, 1, 7999.00, '2026-07-14 20:01:23'),
(14, 13, 29, NULL, 1, 9000.00, '2026-08-07 21:09:54'),
(15, 14, 34, NULL, 1, 7350.00, '2026-08-09 16:27:25'),
(16, 18, 29, NULL, 1, 9000.00, '2026-08-12 13:31:43'),
(17, 25, 28, NULL, 1, 5000.00, '2026-08-15 12:00:26');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `category_name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_desc` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Product categories with hierarchical support';

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `category_name`, `slug`, `image`, `icon`, `description`, `meta_title`, `meta_desc`, `sort_order`, `is_featured`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Smartphones', 'smartphones', NULL, 'fa-mobile-alt', 'Latest mobile phones and handsets', NULL, NULL, 1, 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(2, NULL, 'Laptops', 'laptops', NULL, 'fa-laptop', 'Portable computers for work and gaming', NULL, NULL, 2, 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(3, NULL, 'Tablets', 'tablets', NULL, 'fa-tablet-alt', 'iPad, Android tablets and e-readers', NULL, NULL, 3, 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(4, NULL, 'Smartwatches', 'smartwatches', NULL, 'fa-clock', 'Wearable technology and fitness trackers', NULL, NULL, 4, 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(5, NULL, 'Desktop Computers', 'desktops', NULL, 'fa-desktop', 'All-in-one and tower desktop systems', NULL, NULL, 5, 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(6, NULL, 'Printers', 'printers', NULL, 'fa-print', 'Inkjet, laser and multifunction printers', NULL, NULL, 6, 0, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(7, NULL, 'Cameras', 'cameras', NULL, 'fa-camera', 'DSLR, mirrorless and action cameras', NULL, NULL, 7, 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(8, NULL, 'Gaming Consoles', 'gaming', NULL, 'fa-gamepad', 'PlayStation, Xbox, Nintendo gaming systems', NULL, NULL, 8, 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(9, NULL, 'Networking', 'networking', NULL, 'fa-wifi', 'Routers, switches and access points', NULL, NULL, 9, 0, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(10, NULL, 'Audio Equipment', 'audio', NULL, 'fa-headphones', 'Headphones, speakers and earbuds', NULL, NULL, 10, 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(11, NULL, 'Accessories', 'accessories', NULL, 'fa-plug', 'Cases, chargers and cables', NULL, NULL, 11, 0, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(12, 2, 'Gaming Laptops', 'gaming-laptops', NULL, 'fa-gamepad', 'High-performance gaming laptops', NULL, NULL, 1, 1, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(13, 2, 'Business Laptops', 'business-laptops', NULL, 'fa-briefcase', 'Professional business laptops', NULL, NULL, 2, 0, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(14, 1, 'Budget Smartphones', 'budget-smartphones', NULL, 'fa-mobile', 'Affordable smartphones under ₵1500', NULL, NULL, 1, 0, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(15, 1, 'Flagship Smartphones', 'flagship-smartphones', NULL, 'fa-star', 'Premium flagship devices', NULL, NULL, 2, 0, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(10) UNSIGNED NOT NULL,
  `coupon_code` varchar(50) NOT NULL,
  `coupon_name` varchar(150) DEFAULT NULL,
  `discount_type` enum('percentage','fixed','free_shipping','buy_x_get_y') DEFAULT 'percentage',
  `discount_value` decimal(12,2) NOT NULL,
  `min_order_amount` decimal(12,2) DEFAULT 0.00,
  `max_discount` decimal(12,2) DEFAULT NULL,
  `buy_quantity` int(11) DEFAULT 1,
  `get_quantity` int(11) DEFAULT 0,
  `applicable_to` enum('all','category','product','brand') DEFAULT 'all',
  `applicable_ids` text DEFAULT NULL,
  `max_uses` int(11) DEFAULT NULL,
  `max_uses_per_user` int(11) DEFAULT 1,
  `used_count` int(11) DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Discount coupons and promo codes';

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `coupon_code`, `coupon_name`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount`, `buy_quantity`, `get_quantity`, `applicable_to`, `applicable_ids`, `max_uses`, `max_uses_per_user`, `used_count`, `start_date`, `expiry_date`, `is_active`, `created_by`, `created_at`) VALUES
(1, 'WELCOME10', 'Welcome 10% Off', 'percentage', 10.00, 200.00, 500.00, 1, 0, 'all', NULL, 500, 1, 0, NULL, '2027-01-14 14:46:29', 1, NULL, '2026-07-14 14:46:29'),
(2, 'SAVE50', 'GHS 50 Off', 'fixed', 50.00, 500.00, NULL, 1, 0, 'all', NULL, 200, 1, 0, NULL, '2026-10-14 14:46:29', 1, NULL, '2026-07-14 14:46:29'),
(3, 'FREESHIP', 'Free Shipping', 'free_shipping', 0.00, 300.00, NULL, 1, 0, 'all', NULL, NULL, 1, 0, NULL, '2026-08-14 14:46:29', 1, NULL, '2026-07-14 14:46:29'),
(4, 'STUDENT15', 'Student Discount 15%', 'percentage', 15.00, 1000.00, 1000.00, 1, 0, 'all', NULL, 100, 1, 0, NULL, '2027-07-14 14:46:29', 1, NULL, '2026-07-14 14:46:29'),
(5, 'FLASH20', 'Flash Sale 20% Off', 'percentage', 20.00, 0.00, 2000.00, 1, 0, 'all', NULL, 50, 1, 0, NULL, '2026-07-16 14:46:29', 1, NULL, '2026-07-14 14:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `id` int(10) UNSIGNED NOT NULL,
  `coupon_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `order_id` int(10) UNSIGNED DEFAULT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Coupon usage tracking';

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` int(10) UNSIGNED NOT NULL,
  `currency_code` char(3) NOT NULL,
  `currency_name` varchar(100) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `exchange_rate` decimal(12,6) DEFAULT 1.000000,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Multi-currency support';

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `currency_code`, `currency_name`, `symbol`, `exchange_rate`, `is_default`, `is_active`, `last_updated`) VALUES
(1, 'GHS', 'Ghanaian Cedi', '₵', 1.000000, 1, 1, '2026-07-14 14:46:29'),
(2, 'USD', 'US Dollar', '$', 0.067000, 0, 1, '2026-07-14 14:46:29'),
(3, 'EUR', 'Euro', '€', 0.062000, 0, 1, '2026-07-14 14:46:29'),
(4, 'GBP', 'British Pound', '£', 0.053000, 0, 1, '2026-07-14 14:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Ghana',
  `postal_code` varchar(20) DEFAULT NULL,
  `loyalty_points` int(11) DEFAULT 0,
  `total_points_earned` int(11) DEFAULT 0,
  `tier_id` int(10) UNSIGNED DEFAULT NULL,
  `newsletter` tinyint(1) DEFAULT 1,
  `sms_notifications` tinyint(1) DEFAULT 1,
  `email_notifications` tinyint(1) DEFAULT 1,
  `total_orders` int(11) DEFAULT 0,
  `total_spent` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Extended customer profiles';

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `first_name`, `last_name`, `phone`, `date_of_birth`, `gender`, `avatar`, `address`, `city`, `state`, `country`, `postal_code`, `loyalty_points`, `total_points_earned`, `tier_id`, `newsletter`, `sms_notifications`, `email_notifications`, `total_orders`, `total_spent`, `created_at`, `updated_at`) VALUES
(1, 4, 'Kwame', 'Asante', '+233241234570', NULL, NULL, NULL, NULL, NULL, NULL, 'Ghana', NULL, 2500, 0, 2, 1, 1, 1, 0, 0.00, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(2, 5, 'Abena', 'Boateng', '+233241234571', NULL, NULL, NULL, NULL, NULL, NULL, 'Ghana', NULL, 500, 0, 1, 1, 1, 1, 0, 0.00, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(7, 16, 'Ibrahim', 'Sumani', '0535532383', '1999-06-01', 'male', 'avatars/img_6a565215918331.37336843.jpg', '', '', '', '', '', 3643, 0, 1, 1, 1, 1, 3, 18222.76, '2026-07-14 15:08:28', '2026-08-14 21:38:35'),
(8, 17, 'Lukman', 'Muniru', '0555332271', NULL, NULL, NULL, NULL, NULL, NULL, 'Ghana', NULL, 0, 0, 1, 1, 1, 1, 0, 0.00, '2026-08-13 16:09:55', '2026-08-13 16:09:55');

-- --------------------------------------------------------

--
-- Table structure for table `customer_tiers`
--

CREATE TABLE `customer_tiers` (
  `id` int(10) UNSIGNED NOT NULL,
  `tier_name` varchar(50) NOT NULL,
  `min_points` int(10) UNSIGNED DEFAULT 0,
  `max_points` int(10) UNSIGNED DEFAULT 999999,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `cashback_rate` decimal(5,2) DEFAULT 0.00,
  `free_shipping` tinyint(1) DEFAULT 0,
  `priority_support` tinyint(1) DEFAULT 0,
  `badge_color` varchar(20) DEFAULT '#6c757d',
  `badge_icon` varchar(50) DEFAULT 'fa-medal',
  `benefits` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Customer loyalty tier levels';

--
-- Dumping data for table `customer_tiers`
--

INSERT INTO `customer_tiers` (`id`, `tier_name`, `min_points`, `max_points`, `discount_percentage`, `cashback_rate`, `free_shipping`, `priority_support`, `badge_color`, `badge_icon`, `benefits`, `is_active`, `created_at`) VALUES
(1, 'Bronze', 0, 999, 0.00, 0.00, 0, 0, '#CD7F32', 'fa-medal', 'Basic membership benefits', 1, '2026-07-14 14:46:29'),
(2, 'Silver', 1000, 4999, 2.00, 1.00, 0, 0, '#C0C0C0', 'fa-medal', 'Silver: 2% discount, 1% cashback', 1, '2026-07-14 14:46:29'),
(3, 'Gold', 5000, 14999, 5.00, 2.00, 1, 0, '#FFD700', 'fa-award', 'Gold: 5% discount, 2% cashback, free shipping', 1, '2026-07-14 14:46:29'),
(4, 'Platinum', 15000, 999999, 10.00, 5.00, 1, 1, '#E5E4E2', 'fa-crown', 'Platinum: 10% discount, 5% cashback, all perks', 1, '2026-07-14 14:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(10) UNSIGNED NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `to_name` varchar(200) DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `body` longtext NOT NULL,
  `attachments` text DEFAULT NULL,
  `priority` tinyint(4) DEFAULT 1,
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `status` enum('pending','sent','failed','cancelled') DEFAULT 'pending',
  `error_msg` text DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Email sending queue';

-- --------------------------------------------------------

--
-- Table structure for table `flash_sales`
--

CREATE TABLE `flash_sales` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `sale_price` decimal(12,2) NOT NULL,
  `original_price` decimal(12,2) NOT NULL,
  `discount_pct` decimal(5,2) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `qty_limit` int(11) DEFAULT NULL,
  `sold_count` int(11) DEFAULT 0,
  `banner_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','ended') DEFAULT 'active',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Time-limited flash sale events';

--
-- Dumping data for table `flash_sales`
--

INSERT INTO `flash_sales` (`id`, `product_id`, `title`, `sale_price`, `original_price`, `discount_pct`, `start_time`, `end_time`, `qty_limit`, `sold_count`, `banner_image`, `status`, `created_by`, `created_at`) VALUES
(1, 2, 'JBL Headphones Flash Deal', 749.00, 899.00, 16.69, '2026-07-14 14:46:29', '2026-07-15 14:46:29', 30, 0, NULL, 'active', NULL, '2026-07-14 14:46:29'),
(2, 0, 'Tecno POVA 5G Weekend Sale', 1399.00, 1599.00, 12.51, '2026-07-14 14:46:29', '2026-07-16 14:46:29', 50, 0, NULL, 'active', NULL, '2026-07-14 14:46:29'),
(3, 2, 'JBL Headphones Flash Deal', 749.00, 899.00, 16.69, '2026-07-14 15:05:07', '2026-07-15 15:05:07', 30, 0, NULL, 'active', NULL, '2026-07-14 15:05:07'),
(4, 0, 'Tecno POVA 5G Weekend Sale', 1399.00, 1599.00, 12.51, '2026-07-14 15:05:07', '2026-07-16 15:05:07', 50, 0, NULL, 'active', NULL, '2026-07-14 15:05:07'),
(5, 2, 'JBL Headphones Flash Deal', 749.00, 899.00, 16.69, '2026-07-14 15:06:35', '2026-07-15 15:06:35', 30, 0, NULL, 'active', NULL, '2026-07-14 15:06:35'),
(6, 0, 'Tecno POVA 5G Weekend Sale', 1399.00, 1599.00, 12.51, '2026-07-14 15:06:35', '2026-07-16 15:06:35', 50, 0, NULL, 'active', NULL, '2026-07-14 15:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `transaction_type` enum('stock_in','stock_out','adjustment','return','damage','transfer') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `unit_cost` decimal(12,2) DEFAULT NULL,
  `total_cost` decimal(12,2) DEFAULT NULL,
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Inventory transaction history';

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `transaction_type`, `quantity`, `previous_stock`, `new_stock`, `unit_cost`, `total_cost`, `supplier_id`, `reference_no`, `batch_number`, `expiry_date`, `location`, `notes`, `user_id`, `created_at`) VALUES
(1, 2, 'stock_out', 1, 50, 49, NULL, NULL, NULL, 'ORD-20267-57602', NULL, NULL, NULL, 'Sale order', NULL, '2026-07-14 15:34:06'),
(2, 2, 'stock_out', 1, 49, 48, NULL, NULL, NULL, 'ORD-20267-32118', NULL, NULL, NULL, 'Sale order', NULL, '2026-07-14 15:37:01'),
(3, 28, 'stock_in', 1, 0, 1, 5000.00, NULL, NULL, 'INITIAL-28', NULL, NULL, NULL, 'Initial stock entry', 1, '2026-07-14 15:51:36'),
(4, 29, 'stock_in', 1, 0, 1, 8000.00, NULL, NULL, 'INITIAL-29', NULL, NULL, NULL, 'Initial stock entry', 1, '2026-07-14 21:45:23'),
(5, 30, 'stock_in', 1, 0, 1, 7000.00, NULL, NULL, 'INITIAL-30', NULL, NULL, NULL, 'Initial stock entry', 1, '2026-07-14 22:01:03'),
(6, 31, 'stock_in', 1, 0, 1, 12500.00, NULL, NULL, 'INITIAL-31', NULL, NULL, NULL, 'Initial stock entry', 1, '2026-07-15 02:03:08'),
(7, 32, 'stock_in', 1, 0, 1, 9090.00, NULL, NULL, 'INITIAL-32', NULL, NULL, NULL, 'Initial stock entry', 1, '2026-07-15 02:21:47'),
(8, 32, 'stock_in', 2, 1, 3, 6500.00, 13000.00, NULL, 'STK-IN-1784083364', NULL, NULL, NULL, '', 1, '2026-07-15 02:42:44'),
(9, 33, 'stock_in', 1, 0, 1, 500.00, NULL, NULL, 'INITIAL-33', NULL, NULL, NULL, 'Initial stock entry', 1, '2026-07-15 03:00:02'),
(10, 34, 'stock_in', 8, 0, 8, 6830.00, NULL, NULL, 'INITIAL-34', NULL, NULL, NULL, 'Initial stock entry', 1, '2026-07-15 22:50:11'),
(11, 34, 'stock_out', 2, 8, 6, NULL, NULL, NULL, 'ORD-20268-46340', NULL, NULL, NULL, 'Sale order', NULL, '2026-08-14 21:38:35');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `issued_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL,
  `tax_amount` decimal(12,2) DEFAULT NULL,
  `total` decimal(12,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `status` enum('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Customer invoices';

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `order_id`, `invoice_number`, `issued_date`, `due_date`, `subtotal`, `tax_amount`, `total`, `notes`, `pdf_path`, `status`, `created_at`) VALUES
(1, 1, 'INV-20260714-00001', '2026-07-14', '2026-07-14', 749.00, 93.63, 842.63, NULL, NULL, 'paid', '2026-07-14 15:34:06'),
(2, 2, 'INV-20260714-00002', '2026-07-14', '2026-07-14', 749.00, 93.63, 842.63, NULL, NULL, 'paid', '2026-07-14 15:37:01'),
(3, 3, 'INV-20260814-00003', '2026-08-14', '2026-08-14', 14700.00, 1837.50, 16537.50, NULL, NULL, 'paid', '2026-08-14 21:38:35');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_points_log`
--

CREATE TABLE `loyalty_points_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED DEFAULT NULL,
  `points` int(11) NOT NULL,
  `transaction_type` enum('earned','redeemed','bonus','expired','adjusted','refunded') NOT NULL,
  `description` text DEFAULT NULL,
  `balance_after` int(11) NOT NULL,
  `expires_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Loyalty points transaction history';

--
-- Dumping data for table `loyalty_points_log`
--

INSERT INTO `loyalty_points_log` (`id`, `customer_id`, `order_id`, `points`, `transaction_type`, `description`, `balance_after`, `expires_at`, `created_at`) VALUES
(1, 7, 1, 168, 'earned', 'Points earned from order ORD-20267-57602', 168, NULL, '2026-07-14 15:34:06'),
(2, 7, 2, 168, 'earned', 'Points earned from order ORD-20267-32118', 336, NULL, '2026-07-14 15:37:01'),
(3, 7, 3, 3307, 'earned', 'Points earned from order ORD-20268-46340', 3643, NULL, '2026-08-14 21:38:35');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `status` enum('active','unsubscribed','bounced') DEFAULT 'active',
  `token` varchar(100) DEFAULT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unsubscribed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Newsletter subscribers';

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='System notifications for users';

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `icon`, `color`, `link`, `is_read`, `read_at`, `created_at`) VALUES
(1, 1, 'system', 'Welcome to ElectroStore Admin!', 'Your store is set up and ready. Add products and start selling!', 'fa-star', '#198754', '/electronic_store/admin/', 0, NULL, '2026-07-14 14:46:29'),
(2, 1, 'stock', 'Low Stock Alert', 'Sony PlayStation 5 has only 8 units remaining.', 'fa-exclamation-triangle', '#ffc107', '/electronic_store/admin/inventory/low_stock.php', 0, NULL, '2026-07-14 14:46:29'),
(3, 1, 'system', 'Welcome to ElectroStore Admin!', 'Your store is set up and ready. Add products and start selling!', 'fa-star', '#198754', '/electronic_store/admin/', 0, NULL, '2026-07-14 15:05:08'),
(4, 1, 'stock', 'Low Stock Alert', 'Sony PlayStation 5 has only 8 units remaining.', 'fa-exclamation-triangle', '#ffc107', '/electronic_store/admin/inventory/low_stock.php', 0, NULL, '2026-07-14 15:05:08'),
(5, 1, 'system', 'Welcome to ElectroStore Admin!', 'Your store is set up and ready. Add products and start selling!', 'fa-star', '#198754', '/electronic_store/admin/', 0, NULL, '2026-07-14 15:06:35'),
(6, 1, 'stock', 'Low Stock Alert', 'Sony PlayStation 5 has only 8 units remaining.', 'fa-exclamation-triangle', '#ffc107', '/electronic_store/admin/inventory/low_stock.php', 0, NULL, '2026-07-14 15:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `tax_amount` decimal(12,2) DEFAULT 0.00,
  `shipping_cost` decimal(12,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `currency` char(3) DEFAULT 'GHS',
  `coupon_id` int(10) UNSIGNED DEFAULT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `points_used` int(11) DEFAULT 0,
  `points_discount` decimal(12,2) DEFAULT 0.00,
  `points_earned` int(11) DEFAULT 0,
  `status` enum('pending','processing','approved','packed','shipped','out_for_delivery','delivered','cancelled','returned') DEFAULT 'pending',
  `payment_status` enum('pending','paid','partial','refunded','failed') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `shipping_zone_id` int(10) UNSIGNED DEFAULT NULL,
  `shipping_name` varchar(200) DEFAULT NULL,
  `shipping_email` varchar(255) DEFAULT NULL,
  `shipping_phone` varchar(30) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_country` varchar(100) DEFAULT NULL,
  `shipping_postal` varchar(20) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Customer orders';

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `order_number`, `subtotal`, `tax_amount`, `shipping_cost`, `discount_amount`, `total`, `currency`, `coupon_id`, `coupon_code`, `points_used`, `points_discount`, `points_earned`, `status`, `payment_status`, `payment_method`, `shipping_zone_id`, `shipping_name`, `shipping_email`, `shipping_phone`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_country`, `shipping_postal`, `tracking_number`, `estimated_delivery`, `notes`, `admin_notes`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 7, 'ORD-20267-57602', 749.00, 93.63, 0.00, 0.00, 842.63, 'GHS', NULL, NULL, 0, 0.00, 168, 'pending', 'paid', 'mobile_money', NULL, NULL, NULL, NULL, 'NY-0054-0583, YENDI, NORTHERN, Ghana', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, '2026-07-14 15:34:06', '2026-07-14 15:34:06'),
(2, 7, 'ORD-20267-32118', 749.00, 93.63, 0.00, 0.00, 842.63, 'GHS', NULL, NULL, 0, 0.00, 168, 'pending', 'paid', 'mobile_money', NULL, NULL, NULL, NULL, 'Ghana', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, '2026-07-14 15:37:01', '2026-07-14 15:37:01'),
(3, 7, 'ORD-20268-46340', 14700.00, 1837.50, 0.00, 0.00, 16537.50, 'GHS', NULL, NULL, 0, 0.00, 3307, 'processing', 'paid', 'mobile_money', NULL, NULL, NULL, NULL, 'Ghana', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, '2026-08-14 21:38:35', '2026-08-15 12:23:29');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) DEFAULT 0.00,
  `total_price` decimal(12,2) NOT NULL,
  `tax_amount` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Order line items';

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `sku`, `quantity`, `unit_price`, `discount`, `total_price`, `tax_amount`) VALUES
(1, 1, 2, 'JBL Tune 760NC Wireless Headphones', 'JBL-T760-BLK', 1, 749.00, 0.00, 749.00, 0.00),
(2, 2, 2, 'JBL Tune 760NC Wireless Headphones', 'JBL-T760-BLK', 1, 749.00, 0.00, 749.00, 0.00),
(3, 3, 34, 'Apple iPhone 12 Pro Max - 6.7&quot; - 256GB ROM - 6GB RAM - 12MP Rear/12MP Front - 3687 mAh', 'APP-1784155811', 2, 7350.00, 0.00, 14700.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_tracking`
--

CREATE TABLE `order_tracking` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `status` varchar(100) NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Order delivery tracking history';

--
-- Dumping data for table `order_tracking`
--

INSERT INTO `order_tracking` (`id`, `order_id`, `status`, `location`, `description`, `user_id`, `created_at`) VALUES
(1, 1, 'Order Placed', NULL, 'Order ORD-20267-57602 placed successfully', NULL, '2026-07-14 15:34:06'),
(2, 2, 'Order Placed', NULL, 'Order ORD-20267-32118 placed successfully', NULL, '2026-07-14 15:37:01'),
(3, 2, 'Pending', NULL, '', 1, '2026-07-15 03:30:40'),
(4, 3, 'Order Placed', NULL, 'Order ORD-20268-46340 placed successfully', NULL, '2026-08-14 21:38:35'),
(5, 3, 'Processing', NULL, '', 3, '2026-08-15 12:23:29');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `transaction_id` varchar(150) DEFAULT NULL,
  `payment_method` enum('cash_on_delivery','mobile_money','credit_card','bank_transfer','loyalty_points') NOT NULL,
  `gateway` varchar(100) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` char(3) DEFAULT 'GHS',
  `status` enum('pending','completed','failed','refunded','partial') DEFAULT 'pending',
  `payment_date` datetime DEFAULT NULL,
  `gateway_ref` varchar(200) DEFAULT NULL,
  `gateway_status` varchar(100) DEFAULT NULL,
  `receipt_url` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Payment transactions';

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `transaction_id`, `payment_method`, `gateway`, `amount`, `currency`, `status`, `payment_date`, `gateway_ref`, `gateway_status`, `receipt_url`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'TXN-68AB427CA672', 'mobile_money', NULL, 842.63, 'GHS', 'completed', '2026-07-14 15:34:06', NULL, NULL, NULL, NULL, '2026-07-14 15:34:06', '2026-07-14 15:34:06'),
(2, 2, 'TXN-8BC7DAF41F6A', 'mobile_money', NULL, 842.63, 'GHS', 'completed', '2026-07-14 15:37:01', NULL, NULL, NULL, NULL, '2026-07-14 15:37:01', '2026-07-14 15:37:01'),
(3, 3, 'TXN-914319AF964F', 'mobile_money', NULL, 16537.50, 'GHS', 'completed', '2026-08-14 21:38:35', NULL, NULL, NULL, NULL, '2026-08-14 21:38:35', '2026-08-14 21:38:35');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `brand_id` int(10) UNSIGNED NOT NULL,
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `model` varchar(150) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `compare_price` decimal(12,2) DEFAULT NULL,
  `cost_price` decimal(12,2) DEFAULT NULL,
  `tax_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `low_stock_alert` int(11) DEFAULT 5,
  `weight_kg` decimal(8,3) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `warranty_months` int(11) DEFAULT 12,
  `warranty_info` text DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_new_arrival` tinyint(1) DEFAULT 1,
  `is_best_seller` tinyint(1) DEFAULT 0,
  `allow_reviews` tinyint(1) DEFAULT 1,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_desc` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `status` enum('active','inactive','draft','discontinued') DEFAULT 'active',
  `views` int(11) DEFAULT 0,
  `total_sold` int(11) DEFAULT 0,
  `avg_rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Main products table';

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `brand_id`, `supplier_id`, `product_name`, `slug`, `model`, `sku`, `barcode`, `price`, `compare_price`, `cost_price`, `tax_id`, `quantity`, `low_stock_alert`, `weight_kg`, `dimensions`, `description`, `short_description`, `specifications`, `features`, `warranty_months`, `warranty_info`, `is_featured`, `is_new_arrival`, `is_best_seller`, `allow_reviews`, `meta_title`, `meta_desc`, `meta_keywords`, `status`, `views`, `total_sold`, `avg_rating`, `review_count`, `created_at`, `updated_at`) VALUES
(1, 8, 6, 2, 'Sony PlayStation 5 Console', 'sony-playstation-5', 'CFI-1218A01X', 'SON-PS5-DISC', '5901234567897', 7999.00, 8999.00, 5800.00, 1, 8, 3, 4.500, NULL, 'Experience lightning-fast loading, stunning graphics, and next-gen gameplay with custom SSD and DualSense controller.', 'Sony PS5 gaming console with DualSense controller', '{\"CPU\":\"AMD Zen 2 8 cores 3.5GHz\",\"GPU\":\"AMD RDNA 2 10.28 TFLOPS\",\"RAM\":\"16GB GDDR6\",\"Storage\":\"825GB Custom NVMe SSD\",\"Resolution\":\"Up to 8K\",\"FPS\":\"Up to 120fps\",\"Ray Tracing\":\"Yes\",\"HDR\":\"Yes\"}', NULL, 12, '', 1, 1, 1, 1, '', '', NULL, 'discontinued', 2219, 160, 0.00, 0, '2026-07-14 14:46:29', '2026-07-15 03:01:39'),
(2, 10, 14, 2, 'JBL Tune 760NC Wireless Headphones', 'jbl-tune-760nc', 'JBLT760NCBLKAM', 'JBL-T760-BLK', '5901234567898', 899.00, 1199.00, 650.00, 1, 48, 10, 0.218, NULL, 'Enjoy powerful JBL Pure Bass sound with up to 50 hours playback and Adaptive Noise Cancelling.', 'JBL wireless headphones with 50hr battery and ANC', '{\"Driver\":\"40mm\",\"Frequency\":\"20Hz-20kHz\",\"Noise Cancelling\":\"Active Noise Cancelling (ANC)\",\"Battery\":\"50 hours ANC off 35 hours ANC on\",\"Charging\":\"USB-C fast charge 5min=2hrs\",\"Bluetooth\":\"5.0 multipoint\"}', NULL, 12, '', 1, 1, 1, 1, '', '', NULL, 'discontinued', 1665, 282, 0.00, 0, '2026-07-14 14:46:29', '2026-07-15 03:01:55'),
(3, 11, 13, 2, 'Logitech MX Keys S Wireless Keyboard', 'logitech-mx-keys-s', 'MXKEYSBLA', 'LOG-MXKEYS-S', '5901234567901', 549.00, 699.00, 380.00, 1, 45, 8, 0.810, NULL, 'The MX Keys S features spherically-shaped keys for accurate comfortable typing with multi-device connectivity for up to 3 devices.', 'Logitech wireless keyboard for up to 3 devices', '{\"Layout\":\"Full-size with numpad\",\"Connectivity\":\"Bluetooth USB receiver (Bolt)\",\"Battery\":\"Up to 10 days backlight on 5 months off\",\"Backlight\":\"Intelligent illumination per key\",\"Devices\":\"Up to 3 simultaneous\",\"Compatibility\":\"Windows macOS Linux iOS Android\"}', NULL, 24, '', 0, 0, 1, 1, '', '', NULL, 'discontinued', 765, 135, 0.00, 0, '2026-07-14 14:46:29', '2026-07-15 03:18:11'),
(28, 14, 2, NULL, 'samsung-galaxy-s24-ultra', 'samsung-galaxy-s24-ultra', 'SM-4343A', 'SAM-1784044296', '235233232346', 5000.00, 4999.00, 5000.00, NULL, 1, 5, NULL, NULL, 'samsung-galaxy-s24-ultra', 'samsung-galaxy-s24-ultra', '{\"250GB\":\"10INCHH\"}', NULL, 12, '12 months full warranty', 0, 0, 0, 1, 'samsung-galaxy-s24-ultra', '', NULL, 'active', 3, 0, 0.00, 0, '2026-07-14 15:51:36', '2026-07-14 22:09:41'),
(29, 1, 2, NULL, 'samsung-galaxy-s25-ultra', 'samsung-galaxy-s25-ultra', 'SM-S938', 'SAM-1784065523', '235236532346', 9000.00, 8500.00, 8000.00, NULL, 1, 5, NULL, NULL, 'The Samsung Galaxy S25 Ultra 256GB is a premium, flagship smartphone featuring a 6.9-inch display, a lightweight titanium frame, and an integrated S Pen stylus. It runs on the high-performance Snapdragon 8 Elite processor, offers an advanced 200MP quad-camera system with 5x optical zoom, and includes built-in Galaxy AI features.', 'The Samsung Galaxy S25 Ultra 256GB is a premium, flagship smartphone featuring a 6.9-inch display, a lightweight titanium frame, and an integrated S Pen stylus.', '{\"6.9\\\" Quad HD+ (3120 x 1440) Dynamic LTPO AMOLED 2X, up to 120Hz refresh rate and 2600 nits peak brightness\":\"6.9-inch Dynamic LTPO AMOLED\"}', NULL, 12, '12 months full warranty', 1, 1, 1, 1, 'samsung-galaxy-s24-ultra', 'The Samsung Galaxy S25 Ultra 256GB is a premium, flagship smartphone featuring a 6.9-inch display, a lightweight titanium frame, and an integrated S Pen stylus.', NULL, 'active', 7, 0, 0.00, 0, '2026-07-14 21:45:23', '2026-08-07 21:07:28'),
(30, 2, 5, NULL, 'Lenovo', 'lenovo', 'deaPad Slim 3 15IAN8', 'LEN-1784066463', '41278255344257', 7630.00, 7300.00, 7000.00, NULL, 1, 5, NULL, NULL, 'The IdeaPad Slim 3 15IAN8 delivers reliable everyday computing in a clean, portable design. Built for students, professionals, and home users who need performance without complexity.\r\n\r\nThe Intel Core i3-N305 processor handles multitasking, web browsing, and productivity apps with ease. 8GB of RAM keeps your workflow smooth across documents, spreadsheets, and streaming. The 256GB SSD ensures fast boot times and responsive application loading for everyday tasks.\r\n\r\nLenovo engineering meets practical value in a laptop designed to simply work.', 'The IdeaPad Slim 3 15IAN8 delivers reliable everyday computing in a clean, portable design. Built for students, professionals, and home users who need performance without complexity.', '{\"15.6 Inches\":\"15.6 Inches\"}', NULL, 12, '1 year warranty', 1, 1, 0, 1, 'lenovo-ideapad', 'The IdeaPad Slim 3 15IAN8 delivers reliable everyday computing in a clean, portable design', NULL, 'active', 2, 0, 0.00, 0, '2026-07-14 22:01:03', '2026-07-15 03:23:38'),
(31, 2, 3, NULL, 'HP Pavilion Laptop', 'hp-pavilion-laptop', '15-eg3198nia (AE3Q9EA)', 'HP -1784080988', '535727y28324776', 12900.00, 12600.00, 12500.00, NULL, 1, 5, NULL, NULL, 'The HP Pavilion 15-eg3198nia pairs Intel\'s 13th Gen Core i7-1355U processor with a responsive touchscreen display for seamless productivity and creative work. Built for professionals and students who demand performance without compromise.\r\n\r\nThe 10-core i7-1355U processor handles multitasking with precision, whether you\'re running design software, managing data, or streaming content. 16GB RAM and a spacious 1TB SSD deliver the speed and storage needed for modern workflows. The 15.6-inch FHD IPS touchscreen adds intuitive control to every task.\r\n\r\nHP engineering meets premium design in a laptop that delivers where it counts.\r\n\r\nKey Features\r\n13th Gen Intel Core i7-1355U Processor: 10-core hybrid architecture with up to 5.0GHz turbo for responsive multitasking and demanding applications\r\n\r\n16GB DDR4 RAM: Ample memory for seamless switching between heavy workloads, browser tabs, and creative software\r\n\r\n1TB PCIe NVMe SSD: Fast boot times, rapid file access, and generous storage for your entire digital library\r\n\r\n15.6-inch FHD IPS Touchscreen: 1920x1080 resolution with wide viewing angles and touch functionality for natural interaction\r\n\r\nIntel Iris Xe Graphics: Integrated graphics that handle photo editing, light video work, and casual gaming with ease\r\n\r\nWindows 11 Home: The latest Windows experience with enhanced productivity features and refined interface design\r\n\r\nFull-size Backlit Keyboard: Comfortable typing in any lighting condition with precision key travel\r\n\r\nPremium Silver Finish: Clean, professional aesthetic that fits boardrooms and coffee shops alike\r\n\r\nComprehensive Connectivity: USB Type-C, USB-A, HDMI, and audio jack for all your peripherals and displays\r\n\r\nHP Fast Charge Technology: Get back to full power quickly when you need it most', 'The HP Pavilion 15-eg3198nia pairs Intel&#039;s 13th Gen Core i7-1355U processor with a responsive touchscreen display for seamless productivity and creative work.', '{\"15.6 inch FHD IPS LED Touchscreen\":\"15.6 inch FHD IPS LED Touchscreen\"}', NULL, 12, '1 year warranty', 1, 1, 0, 1, 'HP Pavilion Laptop', 'The HP Pavilion 15-eg3198nia pairs Intel&#039;s 13th Gen Core i7-1355U processor with a responsive touchscreen display for seamless productivity and creative work.', NULL, 'active', 6, 0, 0.00, 0, '2026-07-15 02:03:08', '2026-08-14 21:44:45'),
(32, 10, 14, NULL, 'JBL Tune 770NC', 'hp-pro-tower', 'Small Form Factor (SFF)', 'HP -1784082107', '53t86388W78U9479', 9990.00, 9590.00, 9090.00, NULL, 3, 5, 220.000, NULL, 'Equipped with Adaptive Noise Cancelling technology and JBL Pure Bass sound, the JBL Tune 770NC offers an immersive, distraction-free listening experience. Featuring Bluetooth 5.3 connectivity and multi-point pairing, it allows smooth transitions between devices, whether you’re working or on the move.\r\n\r\nThe 70-hour battery life (or 44 hours with ANC on) ensures long-lasting performance, while fast charging delivers up to 3 hours of playback in just 5 minutes. With Lightweight over-ear comfort, customizable EQ through the JBL Headphones app, and Voice Assistant support, the JBL-TUNE770NC blends premium sound, noise control, and smart connectivity into a sleek, travel-ready headset.\r\n\r\nBuilt for everyday listening and professional focus, it’s the ideal choice for users who want both performance and comfort without compromise.\r\n\r\nTop Features & Highlights:\r\nSound: JBL Pure Bass audio with dynamic 40mm drivers\r\n\r\nNoise Cancelling: Adaptive Noise Cancelling with Smart Ambient mode\r\n\r\nConnectivity: Bluetooth 5.3 wireless technology\r\n\r\nBattery Life: Up to 70 hours playback (44h with ANC)\r\n\r\nFast Charging: 5-minute charge = 3 hours playtime\r\n\r\nMulti-Point Connection: Seamless switch between two devices\r\n\r\nApp Support: JBL Headphones app with EQ customization\r\n\r\nVoice Assistants: Compatible with Siri and Google Assistant\r\n\r\nControls: On-ear buttons for calls, music, and ANC modes\r\n\r\nDesign: Lightweight, foldable over-ear construction', 'JBL Tune 770NC Adaptive Noise Cancelling Headphones', '{\"Weight\":\"220g\",\"Charging\":\"USB-C, 5 minutes for 3 hours playback\",\"Microphone\":\"Build-in for calls\",\"Drivers size\":\"40mm\",\"Battery health\":\"Up to 70 hours (ANC off), Up to 55 hours (ANC on)\",\"Connectivity\":\"Bluetooth 5.3\",\"Frequency response\":\"20Hz - 20kHz\",\"Active Noise Cancelling\":\"Adaptive ANC\"}', NULL, 12, '1 year warranty', 1, 1, 1, 1, 'HP Pro Tower', 'HP Pro Tower 290 G9 SFF Core i5-12400 16GB 512GB SSD DVDRW Win11 Pro + 22inches Monitor - Complete', NULL, 'discontinued', 10, 0, 0.00, 0, '2026-07-15 02:21:47', '2026-07-15 02:45:29'),
(33, 10, 14, NULL, 'JBL Tune 760NC Wireless Headphones', 'jbl-tune-760nc-wireless-headphones', 'JBLT760NCBLKAM', 'JBL-1784084402', '', 520.00, 510.00, 500.00, NULL, 1, 5, 220.000, NULL, 'Equipped with Adaptive Noise Cancelling technology and JBL Pure Bass sound, the JBL Tune 770NC offers an immersive, distraction-free listening experience. Featuring Bluetooth 5.3 connectivity and multi-point pairing, it allows smooth transitions between devices, whether you’re working or on the move.\r\n\r\nThe 70-hour battery life (or 44 hours with ANC on) ensures long-lasting performance, while fast charging delivers up to 3 hours of playback in just 5 minutes. With Lightweight over-ear comfort, customizable EQ through the JBL Headphones app, and Voice Assistant support, the JBL-TUNE770NC blends premium sound, noise control, and smart connectivity into a sleek, travel-ready headset.\r\n\r\nBuilt for everyday listening and professional focus, it’s the ideal choice for users who want both performance and comfort without compromise.\r\n\r\nTop Features & Highlights:\r\nSound: JBL Pure Bass audio with dynamic 40mm drivers\r\n\r\nNoise Cancelling: Adaptive Noise Cancelling with Smart Ambient mode\r\n\r\nConnectivity: Bluetooth 5.3 wireless technology\r\n\r\nBattery Life: Up to 70 hours playback (44h with ANC)\r\n\r\nFast Charging: 5-minute charge = 3 hours playtime\r\n\r\nMulti-Point Connection: Seamless switch between two devices\r\n\r\nApp Support: JBL Headphones app with EQ customization\r\n\r\nVoice Assistants: Compatible with Siri and Google Assistant\r\n\r\nControls: On-ear buttons for calls, music, and ANC modes\r\n\r\nDesign: Lightweight, foldable over-ear construction', 'JBL Tune 770NC Adaptive Noise Cancelling Headphones', '{\"Weight\":\"220g\",\"Charging\":\"220g\",\"Microphone\":\"Build-in for calls\",\"Drivers size\":\"40mm\",\"Battery life\":\"Up to 70 hours (ANC off), Up to 55 hours (ANC on)\",\"Connectivity\":\"Bluetooth 5.3\",\"Frequency response\":\"20Hz - 20kHz\",\"Active Noise Cancelling\":\"Adaptive ANC\"}', NULL, 12, '1 year warranty', 1, 1, 0, 1, '', '', NULL, 'active', 3, 0, 0.00, 0, '2026-07-15 03:00:02', '2026-07-15 03:19:00'),
(34, 1, 1, 1, 'Apple iPhone 12 Pro Max - 6.7&quot; - 256GB ROM - 6GB RAM - 12MP Rear/12MP Front - 3687 mAh', 'apple-iphone-12-pro-max-6-7-quot-256gb-rom-6gb-ram-12mp-rear-12mp-front-3687-mah', 'iPhone 12 Pro Max', 'APP-1784155811', '', 7350.00, 7200.00, 6830.00, NULL, 6, 3, 3.000, NULL, 'The iPhone 12 Pro Max standing as the larger of the two pro-level models. This device has a bigger 6.7-inch Super Retina XDR display compared to the iPhone 11 Pro Max. It also has a triple-camera system and LiDAR. round the back.\r\nApple has gone with the same camera arrangement as in 2019, with three 12-megapixel cameras covering Ultra Wide, Wide, and Telephoto ranges, with a 4x optical zoom in, 2x optical zoom out, and a 10x digital zoom in. Equipped with dual optical image stabilization, it has Portrait Mode and Portrait Lighting effects, a Night mode, Smart HDR, and Panorama features.\r\nThe optical image stabilization has been upgraded to a DSLR-style Sensor Shift, where the sensor moves but the lens does not, enabling the image to stay sharper, and for longer exposures to be made that capture more light, even up to two seconds long when hand-held. It can adjust up to 5,000 times per second, approximately five times as many adjustments than similar systems used in the iPhone 11 Pro range.\r\nApple also introduced Apple ProRAW for its Pro line, a new imaging format combining RAW photography with computational photography features like Deep Fusion and Smart HDR. This includes having full control over color, details, and dynamic range, all from the iPhone\'s Camera app.Video has been boosted to include the ability to record in 10-bit HDR, and is the first to record in Dolby Vision HDR, something that can even be edited on the iPhone and even played through a compatible screen over AirPlay. It is able to do this even at 4K resolution at 60fps, as well as supporting 1080p slo-mo at 240fps, records stereo audio, and supports Audio Zoom.\r\n\r\n\r\nBuy this iPhone 12 Pro Max - 256GB HDD HDD - 6GB Now From ElectroStore Ghana And Have It Delivered Right At Your Doorstep.\r\n', 'Apple iPhone 12 Pro Max - 6.7&quot; - 256GB ROM - 6GB RAM - 12MP Rear/12MP Front - 3687 mAh', '{\"Display\":\"6.7 inches, Super Retina XDR OLED, HDR10, 800 nits (typ), 1200 nits (peak)\",\"Memory\":\"256GB HDD, 6GB RAM\",\"Camera\":\"12MP rear, 12MP front\",\"OS\":\"iOS 14.1, upgradable to iOS 14.2\",\"CPU\":\"Hexa-core (2x3.1 GHz Firestorm + 4x1.8 GHz Icestorm)\",\"Battery\":\"Li-Ion 3687 mAh, non-removable (14.13 Wh)\"}', NULL, 12, '1 year warranty', 1, 1, 0, 1, 'iPhone 12 Pro Max', 'iPhone 12 Pro Max', NULL, 'active', 13, 2, 0.00, 0, '2026-07-15 22:50:11', '2026-08-15 12:01:14');

-- --------------------------------------------------------

--
-- Table structure for table `product_bundles`
--

CREATE TABLE `product_bundles` (
  `id` int(10) UNSIGNED NOT NULL,
  `bundle_name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `original_price` decimal(12,2) DEFAULT NULL,
  `bundle_price` decimal(12,2) NOT NULL,
  `discount_pct` decimal(5,2) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `quantity_available` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Product bundle offers';

--
-- Dumping data for table `product_bundles`
--

INSERT INTO `product_bundles` (`id`, `bundle_name`, `slug`, `description`, `image`, `original_price`, `bundle_price`, `discount_pct`, `is_featured`, `quantity_available`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Mobile Power Bundle', 'mobile-power-bundle', 'Samsung Galaxy S24 Ultra + Galaxy Watch 6 Classic at a special bundled price!', NULL, 12298.00, 10999.00, 10.56, 1, 15, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(2, 'Creator Starter Pack', 'creator-starter-pack', 'Canon EOS R50 + JBL Tune 760NC — everything you need to start creating content!', NULL, 7698.00, 6999.00, 9.08, 1, 10, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `product_faqs`
--

CREATE TABLE `product_faqs` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Product FAQs';

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Product image gallery';

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES
(1, 0, '', 'Samsung Galaxy S24 Ultra', 1, 1, '2026-07-14 14:46:29'),
(2, 0, '', 'Apple iPhone 15 Pro Max', 1, 1, '2026-07-14 14:46:29'),
(3, 0, '', 'HP Pavilion 15 Laptop', 1, 1, '2026-07-14 14:46:29'),
(4, 0, '', 'Lenovo Chromebook 14', 1, 1, '2026-07-14 14:46:29'),
(5, 0, '', 'Apple iPad Pro M2', 1, 1, '2026-07-14 14:46:29'),
(6, 0, '', 'Samsung Galaxy Watch 6', 1, 1, '2026-07-14 14:46:29'),
(7, 0, '', 'Canon EOS R50', 1, 1, '2026-07-14 14:46:29'),
(8, 1, '', 'Sony PlayStation 5', 1, 1, '2026-07-14 14:46:29'),
(9, 2, '', 'JBL Tune 760NC', 1, 1, '2026-07-14 14:46:29'),
(10, 0, '', 'Tecno POVA 5 Pro 5G', 1, 1, '2026-07-14 14:46:29'),
(11, 0, '', 'Dell XPS 15 OLED', 1, 1, '2026-07-14 14:46:29'),
(12, 3, '', 'Logitech MX Keys S', 1, 1, '2026-07-14 14:46:29'),
(13, 0, '', 'Samsung Galaxy S24 Ultra', 1, 1, '2026-07-14 15:05:07'),
(14, 0, '', 'Apple iPhone 15 Pro Max', 1, 1, '2026-07-14 15:05:07'),
(15, 0, '', 'HP Pavilion 15 Laptop', 1, 1, '2026-07-14 15:05:07'),
(16, 0, '', 'Lenovo Chromebook 14', 1, 1, '2026-07-14 15:05:07'),
(17, 0, '', 'Apple iPad Pro M2', 1, 1, '2026-07-14 15:05:07'),
(18, 0, '', 'Samsung Galaxy Watch 6', 1, 1, '2026-07-14 15:05:07'),
(19, 0, '', 'Canon EOS R50', 1, 1, '2026-07-14 15:05:07'),
(20, 1, '', 'Sony PlayStation 5', 1, 1, '2026-07-14 15:05:07'),
(21, 2, '', 'JBL Tune 760NC', 1, 1, '2026-07-14 15:05:07'),
(22, 0, '', 'Tecno POVA 5 Pro 5G', 1, 1, '2026-07-14 15:05:07'),
(23, 0, '', 'Dell XPS 15 OLED', 1, 1, '2026-07-14 15:05:07'),
(24, 3, '', 'Logitech MX Keys S', 1, 1, '2026-07-14 15:05:07'),
(25, 0, '', 'Samsung Galaxy S24 Ultra', 1, 1, '2026-07-14 15:06:35'),
(26, 0, '', 'Apple iPhone 15 Pro Max', 1, 1, '2026-07-14 15:06:35'),
(27, 0, '', 'HP Pavilion 15 Laptop', 1, 1, '2026-07-14 15:06:35'),
(28, 0, '', 'Lenovo Chromebook 14', 1, 1, '2026-07-14 15:06:35'),
(29, 0, '', 'Apple iPad Pro M2', 1, 1, '2026-07-14 15:06:35'),
(30, 0, '', 'Samsung Galaxy Watch 6', 1, 1, '2026-07-14 15:06:35'),
(31, 0, '', 'Canon EOS R50', 1, 1, '2026-07-14 15:06:35'),
(32, 1, '', 'Sony PlayStation 5', 1, 1, '2026-07-14 15:06:35'),
(33, 2, '', 'JBL Tune 760NC', 1, 1, '2026-07-14 15:06:35'),
(34, 0, '', 'Tecno POVA 5 Pro 5G', 1, 1, '2026-07-14 15:06:35'),
(35, 0, '', 'Dell XPS 15 OLED', 1, 1, '2026-07-14 15:06:35'),
(36, 3, '', 'Logitech MX Keys S', 1, 1, '2026-07-14 15:06:35'),
(37, 1, 'products/img_6a565328d9ef57.93765745.jpg', NULL, 0, 4, '2026-07-14 15:18:00'),
(39, 2, 'products/img_6a5653dfae1967.04754262.jpg', NULL, 0, 4, '2026-07-14 15:21:03'),
(40, 3, 'products/img_6a565472ec18d8.72280946.jpg', NULL, 0, 4, '2026-07-14 15:23:30'),
(41, 28, 'products/img_6a565b083217b0.28043992.jpg', NULL, 1, 1, '2026-07-14 15:51:36'),
(42, 29, 'products/img_6a56adf3afa101.72959666.jpg', NULL, 1, 1, '2026-07-14 21:45:23'),
(43, 30, 'products/img_6a56b19f73b555.62175898.jpg', NULL, 1, 1, '2026-07-14 22:01:03'),
(44, 31, 'products/img_6a56ea5cf1faa6.96871575.jpg', NULL, 1, 1, '2026-07-15 02:03:08'),
(45, 32, 'products/img_6a56eebbee65e5.24748091.png', NULL, 1, 1, '2026-07-15 02:21:47'),
(46, 33, 'products/img_6a56f7b2710ad3.83621805.png', NULL, 1, 1, '2026-07-15 03:00:02'),
(47, 33, 'products/img_6a56f7b2733678.65339338.png', NULL, 0, 2, '2026-07-15 03:00:02'),
(48, 33, 'products/img_6a56f7b274e9c0.78353136.png', NULL, 0, 3, '2026-07-15 03:00:02'),
(49, 33, 'products/img_6a56f7b276f397.52219005.png', NULL, 0, 4, '2026-07-15 03:00:02'),
(50, 33, 'products/img_6a56f7b2794764.91560151.png', NULL, 0, 5, '2026-07-15 03:00:02'),
(51, 34, 'products/img_6a580ea3a057b2.57758157.png', NULL, 1, 1, '2026-07-15 22:50:11');

-- --------------------------------------------------------

--
-- Table structure for table `product_tags`
--

CREATE TABLE `product_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `tag_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Product searchable tags';

--
-- Dumping data for table `product_tags`
--

INSERT INTO `product_tags` (`id`, `product_id`, `tag_name`) VALUES
(1, 0, '5G'),
(2, 0, 'S Pen'),
(3, 0, 'Android'),
(4, 0, '200MP'),
(5, 0, 'Flagship'),
(6, 0, '5G'),
(7, 0, 'iOS'),
(8, 0, 'A17 Pro'),
(9, 0, 'Titanium'),
(10, 0, 'Flagship'),
(11, 0, 'Windows 11'),
(12, 0, 'SSD'),
(13, 0, 'Intel Core i5'),
(14, 0, 'Student'),
(15, 0, 'Chromebook'),
(16, 0, 'Student'),
(17, 0, 'Budget'),
(18, 0, 'iPad'),
(19, 0, 'Apple'),
(20, 0, 'M2'),
(21, 0, 'Tablet'),
(22, 0, 'Smartwatch'),
(23, 0, 'Health'),
(24, 0, 'ECG'),
(25, 0, 'Samsung'),
(26, 0, 'Camera'),
(27, 0, 'Mirrorless'),
(28, 0, '4K'),
(29, 0, 'Canon'),
(38, 0, '5G'),
(39, 0, 'Budget'),
(40, 0, 'Big Battery'),
(41, 0, 'Tecno'),
(42, 0, 'OLED'),
(43, 0, 'Gaming Laptop'),
(44, 0, 'Dell'),
(45, 0, 'RTX 4060'),
(50, 0, '5G'),
(51, 0, 'S Pen'),
(52, 0, 'Android'),
(53, 0, '200MP'),
(54, 0, 'Flagship'),
(55, 0, '5G'),
(56, 0, 'iOS'),
(57, 0, 'A17 Pro'),
(58, 0, 'Titanium'),
(59, 0, 'Flagship'),
(60, 0, 'Windows 11'),
(61, 0, 'SSD'),
(62, 0, 'Intel Core i5'),
(63, 0, 'Student'),
(64, 0, 'Chromebook'),
(65, 0, 'Student'),
(66, 0, 'Budget'),
(67, 0, 'iPad'),
(68, 0, 'Apple'),
(69, 0, 'M2'),
(70, 0, 'Tablet'),
(71, 0, 'Smartwatch'),
(72, 0, 'Health'),
(73, 0, 'ECG'),
(74, 0, 'Samsung'),
(75, 0, 'Camera'),
(76, 0, 'Mirrorless'),
(77, 0, '4K'),
(78, 0, 'Canon'),
(87, 0, '5G'),
(88, 0, 'Budget'),
(89, 0, 'Big Battery'),
(90, 0, 'Tecno'),
(91, 0, 'OLED'),
(92, 0, 'Gaming Laptop'),
(93, 0, 'Dell'),
(94, 0, 'RTX 4060'),
(99, 0, '5G'),
(100, 0, 'S Pen'),
(101, 0, 'Android'),
(102, 0, '200MP'),
(103, 0, 'Flagship'),
(104, 0, '5G'),
(105, 0, 'iOS'),
(106, 0, 'A17 Pro'),
(107, 0, 'Titanium'),
(108, 0, 'Flagship'),
(109, 0, 'Windows 11'),
(110, 0, 'SSD'),
(111, 0, 'Intel Core i5'),
(112, 0, 'Student'),
(113, 0, 'Chromebook'),
(114, 0, 'Student'),
(115, 0, 'Budget'),
(116, 0, 'iPad'),
(117, 0, 'Apple'),
(118, 0, 'M2'),
(119, 0, 'Tablet'),
(120, 0, 'Smartwatch'),
(121, 0, 'Health'),
(122, 0, 'ECG'),
(123, 0, 'Samsung'),
(124, 0, 'Camera'),
(125, 0, 'Mirrorless'),
(126, 0, '4K'),
(127, 0, 'Canon'),
(136, 0, '5G'),
(137, 0, 'Budget'),
(138, 0, 'Big Battery'),
(139, 0, 'Tecno'),
(140, 0, 'OLED'),
(141, 0, 'Gaming Laptop'),
(142, 0, 'Dell'),
(143, 0, 'RTX 4060'),
(160, 1, 'Gaming'),
(161, 1, 'Next-Gen'),
(162, 1, '4K'),
(163, 1, 'PlayStation'),
(164, 1, 'Gaming'),
(165, 1, 'Next-Gen'),
(166, 1, '4K'),
(167, 1, 'PlayStation'),
(168, 1, 'Gaming'),
(169, 1, 'Next-Gen'),
(170, 1, '4K'),
(171, 1, 'PlayStation'),
(184, 2, 'Wireless'),
(185, 2, 'ANC'),
(186, 2, 'Bass'),
(187, 2, 'JBL'),
(188, 2, 'Wireless'),
(189, 2, 'ANC'),
(190, 2, 'Bass'),
(191, 2, 'JBL'),
(192, 2, 'Wireless'),
(193, 2, 'ANC'),
(194, 2, 'Bass'),
(195, 2, 'JBL'),
(196, 3, 'Wireless'),
(197, 3, 'Keyboard'),
(198, 3, 'Logitech'),
(199, 3, 'Multi-Device'),
(200, 3, 'Wireless'),
(201, 3, 'Keyboard'),
(202, 3, 'Logitech'),
(203, 3, 'Multi-Device'),
(204, 3, 'Wireless'),
(205, 3, 'Keyboard'),
(206, 3, 'Logitech'),
(207, 3, 'Multi-Device'),
(210, 29, '5G'),
(211, 29, 'Android'),
(212, 34, 'iPhine');

-- --------------------------------------------------------

--
-- Table structure for table `product_videos`
--

CREATE TABLE `product_videos` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `video_url` varchar(500) NOT NULL,
  `video_type` enum('youtube','vimeo','mp4') DEFAULT 'youtube',
  `title` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Product demo videos';

-- --------------------------------------------------------

--
-- Table structure for table `product_views`
--

CREATE TABLE `product_views` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Product page view analytics';

-- --------------------------------------------------------

--
-- Table structure for table `recently_viewed`
--

CREATE TABLE `recently_viewed` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Recently viewed products tracking';

--
-- Dumping data for table `recently_viewed`
--

INSERT INTO `recently_viewed` (`id`, `customer_id`, `session_id`, `product_id`, `viewed_at`) VALUES
(1, 7, NULL, 2, '2026-07-14 15:35:06'),
(4, 7, NULL, 34, '2026-08-14 21:40:30'),
(6, 7, NULL, 31, '2026-08-14 21:44:45');

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `return_number` varchar(50) NOT NULL,
  `reason` enum('defective','wrong_item','not_as_described','changed_mind','damaged_in_transit','other') NOT NULL,
  `description` text DEFAULT NULL,
  `refund_type` enum('full','partial','store_credit','exchange') DEFAULT 'full',
  `refund_amount` decimal(12,2) DEFAULT 0.00,
  `status` enum('pending','approved','rejected','processing','completed') DEFAULT 'pending',
  `images` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `processed_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Product return requests';

-- --------------------------------------------------------

--
-- Table structure for table `return_items`
--

CREATE TABLE `return_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `return_id` int(10) UNSIGNED NOT NULL,
  `order_item_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `condition` enum('unopened','opened','damaged','missing_parts') DEFAULT 'opened'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Items in return requests';

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED DEFAULT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `title` varchar(255) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `pros` text DEFAULT NULL,
  `cons` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `helpful_count` int(11) DEFAULT 0,
  `not_helpful` int(11) DEFAULT 0,
  `admin_reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Product customer reviews';

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `customer_id`, `order_id`, `rating`, `title`, `review_text`, `pros`, `cons`, `images`, `is_verified`, `status`, `helpful_count`, `not_helpful`, `admin_reply`, `created_at`, `updated_at`) VALUES
(1, 31, 7, NULL, 1, '', '.', '', '', NULL, 0, 'pending', 0, 0, NULL, '2026-08-14 21:44:00', '2026-08-14 21:44:00');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='System roles with JSON permissions';

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `slug`, `permissions`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin', '{\"all\":true}', 'Full system access', 1, '2026-07-14 15:08:06', '2026-07-14 15:08:06'),
(2, 'Inventory Manager', 'inventory_manager', '{\"products\":true,\"inventory\":true,\"suppliers\":true,\"reports\":[\"inventory\"]}', 'Manages stock and suppliers', 1, '2026-07-14 15:08:06', '2026-07-14 15:08:06'),
(3, 'Sales Officer', 'sales_officer', '{\"orders\":true,\"customers\":true,\"reports\":[\"sales\"],\"coupons\":true}', 'Handles orders and customers', 1, '2026-07-14 15:08:06', '2026-07-14 15:08:06'),
(4, 'Customer', 'customer', '{\"shop\":true,\"orders\":true,\"reviews\":true,\"profile\":true}', 'Regular customer', 1, '2026-07-14 15:08:06', '2026-07-14 15:08:06'),
(5, 'Guest', 'guest', '{\"shop\":true}', 'Guest browsing access', 1, '2026-07-14 15:08:06', '2026-07-14 15:08:06');

-- --------------------------------------------------------

--
-- Table structure for table `search_logs`
--

CREATE TABLE `search_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `search_term` varchar(500) NOT NULL,
  `results_count` int(11) DEFAULT 0,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `clicked_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Search analytics log';

--
-- Dumping data for table `search_logs`
--

INSERT INTO `search_logs` (`id`, `search_term`, `results_count`, `customer_id`, `session_id`, `ip_address`, `clicked_id`, `created_at`) VALUES
(1, 'ip', 2, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:54:34'),
(2, 'iph', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:54:38'),
(3, 'ipho', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:54:38'),
(4, 'iphon', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:54:43'),
(5, 'iphone', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:54:44'),
(6, 'iphone', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:54:59'),
(7, 'iphone', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:55:03'),
(8, 'iphone 12', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:55:04'),
(9, 'iphone 1', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:55:06'),
(10, 'iphone 13', 0, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:55:08'),
(11, 'iphone 1', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:55:10'),
(12, 'iphone', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:55:12'),
(13, 'iphon', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:55:12'),
(14, 'Appp', 0, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:55:16'),
(15, 'App', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:55:17'),
(16, 'Appl', 1, NULL, '9qv72gb8das6up3erra4tb0kol', '::1', NULL, '2026-07-15 22:55:19');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `setting_group` varchar(100) DEFAULT 'general',
  `label` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `input_type` varchar(50) DEFAULT 'text',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `is_public` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Application settings';

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `label`, `description`, `input_type`, `options`, `is_public`, `updated_at`) VALUES
(1, 'site_name', 'ElectroStore Ghana', 'general', 'Site Name', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(2, 'site_tagline', 'Ghana\'s #1 Electronics Store', 'general', 'Site Tagline', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(3, 'site_email', 'info@electrostore.com.gh', 'general', 'Contact Email', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(4, 'site_phone', '+233 535 532 383', 'general', 'Contact Phone', NULL, 'text', NULL, 1, '2026-08-07 20:51:27'),
(5, 'site_address', '14 Independence Avenue, Accra, Ghana', 'general', 'Physical Address', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(6, 'site_logo', 'assets/images/logo.png', 'general', 'Site Logo', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(7, 'currency_default', 'GHS', 'payment', 'Default Currency', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(8, 'tax_rate', '12.5', 'payment', 'Default Tax Rate (%)', NULL, 'text', NULL, 0, '2026-07-14 14:46:29'),
(9, 'tax_inclusive', '0', 'payment', 'Prices Tax Inclusive', NULL, 'text', NULL, 0, '2026-07-14 14:46:29'),
(10, 'loyalty_points_ratio', '5', 'loyalty', 'Points per GHS spent', NULL, 'text', NULL, 0, '2026-07-14 14:46:29'),
(11, 'loyalty_redeem_ratio', '100', 'loyalty', 'Points to GHS ratio', NULL, 'text', NULL, 0, '2026-07-14 14:46:29'),
(12, 'low_stock_threshold', '5', 'inventory', 'Low Stock Threshold', NULL, 'text', NULL, 0, '2026-07-14 14:46:29'),
(13, 'products_per_page', '12', 'display', 'Products Per Page', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(14, 'order_prefix', 'ORD', 'orders', 'Order Number Prefix', NULL, 'text', NULL, 0, '2026-07-14 14:46:29'),
(15, 'invoice_prefix', 'INV', 'orders', 'Invoice Number Prefix', NULL, 'text', NULL, 0, '2026-07-14 14:46:29'),
(16, 'enable_reviews', '1', 'reviews', 'Enable Product Reviews', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(17, 'review_approval', '1', 'reviews', 'Reviews Require Approval', NULL, 'text', NULL, 0, '2026-07-14 14:46:29'),
(18, 'enable_wishlist', '1', 'wishlist', 'Enable Wishlist', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(19, 'enable_compare', '1', 'compare', 'Enable Comparison', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(20, 'max_compare', '4', 'compare', 'Max Compare Items', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(21, 'enable_newsletter', '1', 'newsletter', 'Enable Newsletter', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(22, 'maintenance_mode', '0', 'general', 'Maintenance Mode', NULL, 'text', NULL, 0, '2026-07-14 14:46:29'),
(23, 'dark_mode_default', '0', 'display', 'Default to Dark Mode', NULL, 'text', NULL, 1, '2026-07-14 14:46:29'),
(24, 'smtp_host', 'smtp.gmail.com', 'email', 'SMTP Host', NULL, 'text', NULL, 0, '2026-07-14 14:46:29'),
(25, 'smtp_port', '587', 'email', 'SMTP Port', NULL, 'text', NULL, 0, '2026-07-14 14:46:29'),
(26, 'smtp_from_name', 'ElectroStore Ghana', 'email', 'Email Sender Name', NULL, 'text', NULL, 0, '2026-07-14 14:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_zones`
--

CREATE TABLE `shipping_zones` (
  `id` int(10) UNSIGNED NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `countries` text DEFAULT NULL,
  `base_rate` decimal(10,2) DEFAULT 0.00,
  `per_kg_rate` decimal(10,2) DEFAULT 0.00,
  `free_above` decimal(10,2) DEFAULT NULL,
  `est_days_min` int(11) DEFAULT 1,
  `est_days_max` int(11) DEFAULT 7,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Shipping zones and rates';

--
-- Dumping data for table `shipping_zones`
--

INSERT INTO `shipping_zones` (`id`, `zone_name`, `countries`, `base_rate`, `per_kg_rate`, `free_above`, `est_days_min`, `est_days_max`, `is_active`, `created_at`) VALUES
(1, 'Greater Accra', 'Ghana-AccraRegion', 20.00, 2.00, 500.00, 1, 2, 1, '2026-07-14 14:46:29'),
(2, 'Ashanti Region', 'Ghana-AshantiRegion', 35.00, 3.00, 800.00, 2, 3, 1, '2026-07-14 14:46:29'),
(3, 'Other Regions Ghana', 'Ghana-Other', 50.00, 4.00, 1000.00, 3, 5, 1, '2026-07-14 14:46:29'),
(4, 'International', 'International', 150.00, 8.00, NULL, 7, 14, 1, '2026-07-14 14:46:29'),
(5, 'Greater Accra', 'Ghana-AccraRegion', 20.00, 2.00, 500.00, 1, 2, 1, '2026-07-14 15:05:07'),
(6, 'Ashanti Region', 'Ghana-AshantiRegion', 35.00, 3.00, 800.00, 2, 3, 1, '2026-07-14 15:05:07'),
(7, 'Other Regions Ghana', 'Ghana-Other', 50.00, 4.00, 1000.00, 3, 5, 1, '2026-07-14 15:05:07'),
(8, 'International', 'International', 150.00, 8.00, NULL, 7, 14, 1, '2026-07-14 15:05:07'),
(9, 'Greater Accra', 'Ghana-AccraRegion', 20.00, 2.00, 500.00, 1, 2, 1, '2026-07-14 15:06:35'),
(10, 'Ashanti Region', 'Ghana-AshantiRegion', 35.00, 3.00, 800.00, 2, 3, 1, '2026-07-14 15:06:35'),
(11, 'Other Regions Ghana', 'Ghana-Other', 50.00, 4.00, 1000.00, 3, 5, 1, '2026-07-14 15:06:35'),
(12, 'International', 'International', 150.00, 8.00, NULL, 7, 14, 1, '2026-07-14 15:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `sms_queue`
--

CREATE TABLE `sms_queue` (
  `id` int(10) UNSIGNED NOT NULL,
  `phone_number` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `provider` varchar(50) DEFAULT 'mnotify',
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `response` text DEFAULT NULL,
  `attempts` int(11) DEFAULT 0,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='SMS sending queue';

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(12,2) DEFAULT NULL,
  `supervisor_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('active','on_leave','terminated') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Staff and employee records';

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(10) UNSIGNED NOT NULL,
  `supplier_name` varchar(200) NOT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `alt_phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `tax_number` varchar(50) DEFAULT NULL,
  `payment_terms` varchar(100) DEFAULT NULL,
  `lead_time_days` int(11) DEFAULT 7,
  `rating` decimal(3,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Product suppliers';

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_person`, `email`, `phone`, `alt_phone`, `address`, `city`, `state`, `country`, `postal_code`, `tax_number`, `payment_terms`, `lead_time_days`, `rating`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Tech Imports Ghana Ltd', 'Kofi Acheampong', 'imports@techgh.com', '+233302123456', NULL, NULL, 'Accra', NULL, 'Ghana', NULL, NULL, 'Net 30', 7, 4.50, NULL, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(2, 'ElectroWorld Distributors', 'Yaw Darko', 'orders@electroworld.gh', '+233244567890', NULL, NULL, 'Accra', NULL, 'Ghana', NULL, NULL, 'Net 15', 5, 4.20, NULL, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(3, 'Global Tech Supplies', 'James Asante', 'sales@globaltech.com', '+1-555-0123', NULL, NULL, 'New York', NULL, 'USA', NULL, NULL, 'Net 60', 21, 4.80, NULL, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(4, 'Asian Electronics Hub', 'Li Wei', 'supply@asiahub.com', '+86-10-12345678', NULL, NULL, 'Shenzhen', NULL, 'China', NULL, NULL, 'Prepaid', 35, 4.00, NULL, 1, '2026-07-14 14:46:29', '2026-07-14 14:46:29'),
(5, 'Tech Imports Ghana Ltd', 'Kofi Acheampong', 'imports@techgh.com', '+233302123456', NULL, NULL, 'Accra', NULL, 'Ghana', NULL, NULL, 'Net 30', 7, 4.50, NULL, 1, '2026-07-14 15:05:07', '2026-07-14 15:05:07'),
(6, 'ElectroWorld Distributors', 'Yaw Darko', 'orders@electroworld.gh', '+233244567890', NULL, NULL, 'Accra', NULL, 'Ghana', NULL, NULL, 'Net 15', 5, 4.20, NULL, 1, '2026-07-14 15:05:07', '2026-07-14 15:05:07'),
(7, 'Global Tech Supplies', 'James Asante', 'sales@globaltech.com', '+1-555-0123', NULL, NULL, 'New York', NULL, 'USA', NULL, NULL, 'Net 60', 21, 4.80, NULL, 1, '2026-07-14 15:05:07', '2026-07-14 15:05:07'),
(8, 'Asian Electronics Hub', 'Li Wei', 'supply@asiahub.com', '+86-10-12345678', NULL, NULL, 'Shenzhen', NULL, 'China', NULL, NULL, 'Prepaid', 35, 4.00, NULL, 1, '2026-07-14 15:05:07', '2026-07-14 15:05:07'),
(9, 'Tech Imports Ghana Ltd', 'Kofi Acheampong', 'imports@techgh.com', '+233302123456', NULL, NULL, 'Accra', NULL, 'Ghana', NULL, NULL, 'Net 30', 7, 4.50, NULL, 1, '2026-07-14 15:06:35', '2026-07-14 15:06:35'),
(10, 'ElectroWorld Distributors', 'Yaw Darko', 'orders@electroworld.gh', '+233244567890', NULL, NULL, 'Accra', NULL, 'Ghana', NULL, NULL, 'Net 15', 5, 4.20, NULL, 1, '2026-07-14 15:06:35', '2026-07-14 15:06:35'),
(11, 'Global Tech Supplies', 'James Asante', 'sales@globaltech.com', '+1-555-0123', NULL, NULL, 'New York', NULL, 'USA', NULL, NULL, 'Net 60', 21, 4.80, NULL, 1, '2026-07-14 15:06:35', '2026-07-14 15:06:35'),
(12, 'Asian Electronics Hub', 'Li Wei', 'supply@asiahub.com', '+86-10-12345678', NULL, NULL, 'Shenzhen', NULL, 'China', NULL, NULL, 'Prepaid', 35, 4.00, NULL, 1, '2026-07-14 15:06:35', '2026-07-14 15:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` int(10) UNSIGNED NOT NULL,
  `tax_name` varchar(100) NOT NULL,
  `rate` decimal(5,2) NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tax rates by region';

--
-- Dumping data for table `taxes`
--

INSERT INTO `taxes` (`id`, `tax_name`, `rate`, `country`, `region`, `is_default`, `is_active`, `created_at`) VALUES
(1, 'Ghana VAT (NHIL+GETFund)', 12.50, 'Ghana', NULL, 1, 1, '2026-07-14 14:46:29'),
(2, 'No Tax', 0.00, 'Export', NULL, 0, 1, '2026-07-14 14:46:29'),
(3, 'Ghana VAT (NHIL+GETFund)', 12.50, 'Ghana', NULL, 1, 1, '2026-07-14 15:05:07'),
(4, 'No Tax', 0.00, 'Export', NULL, 0, 1, '2026-07-14 15:05:07'),
(5, 'Ghana VAT (NHIL+GETFund)', 12.50, 'Ghana', NULL, 1, 1, '2026-07-14 15:06:35'),
(6, 'No Tax', 0.00, 'Export', NULL, 0, 1, '2026-07-14 15:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended','pending') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verify_token` varchar(100) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(32) DEFAULT NULL,
  `password_reset_token` varchar(100) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='All system users including staff';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `username`, `email`, `password`, `full_name`, `phone`, `avatar`, `status`, `email_verified`, `email_verify_token`, `two_factor_enabled`, `two_factor_secret`, `password_reset_token`, `password_reset_expires`, `last_login`, `last_ip`, `login_attempts`, `locked_until`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin', 'admin@electrostore.com', '$2a$12$3pIiJZqcwetc5O0PmcPtvufe/ss1cR74yQ8fBZOBSQbwAh3xZG0Ci', 'System Administrator', '+233241234567', NULL, 'active', 1, NULL, 0, NULL, NULL, NULL, '2026-08-15 11:59:08', '::1', 0, NULL, NULL, '2026-07-14 14:46:29', '2026-08-15 11:59:08'),
(2, 2, 'inventory1', 'inventory@electrostore.com', '$2a$12$3pIiJZqcwetc5O0PmcPtvufe/ss1cR74yQ8fBZOBSQbwAh3xZG0Ci', 'John Mensah', '+233241234568', NULL, 'active', 1, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-07-14 14:46:29', '2026-07-14 14:52:14'),
(3, 3, 'sales1', 'sales@electrostore.com', '$2a$12$3pIiJZqcwetc5O0PmcPtvufe/ss1cR74yQ8fBZOBSQbwAh3xZG0Ci', 'Ama Owusu', '+233241234569', NULL, 'active', 1, NULL, 0, NULL, NULL, NULL, '2026-08-15 12:21:56', '::1', 0, NULL, NULL, '2026-07-14 14:46:29', '2026-08-15 12:21:56'),
(4, 4, 'customer1', 'customer1@gmail.com', '$2a$12$3pIiJZqcwetc5O0PmcPtvufe/ss1cR74yQ8fBZOBSQbwAh3xZG0Ci', 'Kwame Asante', '+233241234570', NULL, 'active', 1, NULL, 0, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, '2026-07-14 14:46:29', '2026-08-12 13:32:22'),
(5, 4, 'customer2', 'customer2@gmail.com', '$2a$12$3pIiJZqcwetc5O0PmcPtvufe/ss1cR74yQ8fBZOBSQbwAh3xZG0Ci', 'Abena Boateng', '+233241234571', NULL, 'active', 1, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-07-14 14:46:29', '2026-07-14 14:52:14'),
(16, 4, 'ibs123', 'ibrahims@gmail.com', '$2y$12$.dbbZ4AvjF7jj4jWqo6DFOwjBv3PYqvCkC9fGec6sXSlve4bSbd.S', 'Ibrahim Sumani', '0535532383', 'avatars/img_6a565215918331.37336843.jpg', 'active', 0, 'd21710abb0c2bc6cd46d85b00282a79fb2f22434aa39612eb6a316c6dfb59d45', 0, NULL, NULL, NULL, '2026-08-15 12:11:41', '::1', 0, NULL, 'cde86f65dbffc002a719511b7114007fba26122c89899836961d080ebe0ec110', '2026-07-14 15:08:28', '2026-08-15 12:11:41'),
(17, 4, 'LukasM', 'munirulukman6@gmail.com', '$2y$12$sRsN.t.jiH974dLn7x2pdeEW9IPBlusm.ywA0x7SULb6HGQ2GKn7y', 'Lukman Muniru', '0555332271', NULL, 'active', 0, 'f2b3b4361928197fdff8ab025c253bc12394bdc0f14c407983432aaa0bd0dc1a', 0, NULL, NULL, NULL, '2026-08-13 16:11:48', '::1', 1, NULL, NULL, '2026-08-13 16:09:55', '2026-08-14 21:34:31');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_dashboard_stats`
-- (See below for the actual view)
--
CREATE TABLE `vw_dashboard_stats` (
`total_products` bigint(21)
,`total_customers` bigint(21)
,`total_orders` bigint(21)
,`total_revenue` decimal(34,2)
,`pending_orders` bigint(21)
,`low_stock_count` bigint(21)
,`today_orders` bigint(21)
,`today_revenue` decimal(34,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_low_stock`
-- (See below for the actual view)
--
CREATE TABLE `vw_low_stock` (
`id` int(10) unsigned
,`product_name` varchar(255)
,`sku` varchar(100)
,`quantity` int(11)
,`low_stock_alert` int(11)
,`category_name` varchar(150)
,`brand_name` varchar(150)
,`supplier_name` varchar(200)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_orders`
-- (See below for the actual view)
--
CREATE TABLE `vw_orders` (
`id` int(10) unsigned
,`customer_id` int(10) unsigned
,`order_number` varchar(30)
,`subtotal` decimal(12,2)
,`tax_amount` decimal(12,2)
,`shipping_cost` decimal(12,2)
,`discount_amount` decimal(12,2)
,`total` decimal(12,2)
,`currency` char(3)
,`coupon_id` int(10) unsigned
,`coupon_code` varchar(50)
,`points_used` int(11)
,`points_discount` decimal(12,2)
,`points_earned` int(11)
,`status` enum('pending','processing','approved','packed','shipped','out_for_delivery','delivered','cancelled','returned')
,`payment_status` enum('pending','paid','partial','refunded','failed')
,`payment_method` varchar(50)
,`shipping_zone_id` int(10) unsigned
,`shipping_name` varchar(200)
,`shipping_email` varchar(255)
,`shipping_phone` varchar(30)
,`shipping_address` text
,`shipping_city` varchar(100)
,`shipping_state` varchar(100)
,`shipping_country` varchar(100)
,`shipping_postal` varchar(20)
,`tracking_number` varchar(100)
,`estimated_delivery` date
,`notes` text
,`admin_notes` text
,`ip_address` varchar(45)
,`user_agent` text
,`created_at` timestamp
,`updated_at` timestamp
,`customer_name` varchar(201)
,`customer_phone` varchar(20)
,`customer_email` varchar(255)
,`item_count` bigint(21)
,`transaction_id` varchar(150)
,`pay_method` enum('cash_on_delivery','mobile_money','credit_card','bank_transfer','loyalty_points')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_products`
-- (See below for the actual view)
--
CREATE TABLE `vw_products` (
`id` int(10) unsigned
,`product_name` varchar(255)
,`slug` varchar(255)
,`model` varchar(150)
,`sku` varchar(100)
,`barcode` varchar(100)
,`price` decimal(12,2)
,`compare_price` decimal(12,2)
,`quantity` int(11)
,`status` enum('active','inactive','draft','discontinued')
,`is_featured` tinyint(1)
,`is_new_arrival` tinyint(1)
,`is_best_seller` tinyint(1)
,`avg_rating` decimal(3,2)
,`review_count` int(11)
,`views` int(11)
,`total_sold` int(11)
,`warranty_months` int(11)
,`short_description` text
,`category_name` varchar(150)
,`category_slug` varchar(150)
,`brand_name` varchar(150)
,`brand_slug` varchar(150)
,`brand_logo` varchar(255)
,`primary_image` varchar(255)
,`flash_price` decimal(12,2)
,`flash_end` datetime
,`effective_price` decimal(12,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Customer product wishlists';

-- --------------------------------------------------------

--
-- Structure for view `vw_dashboard_stats`
--
DROP TABLE IF EXISTS `vw_dashboard_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_dashboard_stats`  AS SELECT (select count(0) from `products` where `products`.`status` = 'active') AS `total_products`, (select count(0) from `customers`) AS `total_customers`, (select count(0) from `orders`) AS `total_orders`, (select coalesce(sum(`orders`.`total`),0) from `orders` where `orders`.`payment_status` = 'paid') AS `total_revenue`, (select count(0) from `orders` where `orders`.`status` = 'pending') AS `pending_orders`, (select count(0) from `products` where `products`.`quantity` <= `products`.`low_stock_alert` and `products`.`status` = 'active') AS `low_stock_count`, (select count(0) from `orders` where cast(`orders`.`created_at` as date) = curdate()) AS `today_orders`, (select coalesce(sum(`orders`.`total`),0) from `orders` where cast(`orders`.`created_at` as date) = curdate() and `orders`.`payment_status` = 'paid') AS `today_revenue` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_low_stock`
--
DROP TABLE IF EXISTS `vw_low_stock`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_low_stock`  AS SELECT `p`.`id` AS `id`, `p`.`product_name` AS `product_name`, `p`.`sku` AS `sku`, `p`.`quantity` AS `quantity`, `p`.`low_stock_alert` AS `low_stock_alert`, `c`.`category_name` AS `category_name`, `b`.`brand_name` AS `brand_name`, `s`.`supplier_name` AS `supplier_name` FROM (((`products` `p` join `categories` `c` on(`p`.`category_id` = `c`.`id`)) join `brands` `b` on(`p`.`brand_id` = `b`.`id`)) left join `suppliers` `s` on(`p`.`supplier_id` = `s`.`id`)) WHERE `p`.`quantity` <= `p`.`low_stock_alert` AND `p`.`status` = 'active' ;

-- --------------------------------------------------------

--
-- Structure for view `vw_orders`
--
DROP TABLE IF EXISTS `vw_orders`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_orders`  AS SELECT `o`.`id` AS `id`, `o`.`customer_id` AS `customer_id`, `o`.`order_number` AS `order_number`, `o`.`subtotal` AS `subtotal`, `o`.`tax_amount` AS `tax_amount`, `o`.`shipping_cost` AS `shipping_cost`, `o`.`discount_amount` AS `discount_amount`, `o`.`total` AS `total`, `o`.`currency` AS `currency`, `o`.`coupon_id` AS `coupon_id`, `o`.`coupon_code` AS `coupon_code`, `o`.`points_used` AS `points_used`, `o`.`points_discount` AS `points_discount`, `o`.`points_earned` AS `points_earned`, `o`.`status` AS `status`, `o`.`payment_status` AS `payment_status`, `o`.`payment_method` AS `payment_method`, `o`.`shipping_zone_id` AS `shipping_zone_id`, `o`.`shipping_name` AS `shipping_name`, `o`.`shipping_email` AS `shipping_email`, `o`.`shipping_phone` AS `shipping_phone`, `o`.`shipping_address` AS `shipping_address`, `o`.`shipping_city` AS `shipping_city`, `o`.`shipping_state` AS `shipping_state`, `o`.`shipping_country` AS `shipping_country`, `o`.`shipping_postal` AS `shipping_postal`, `o`.`tracking_number` AS `tracking_number`, `o`.`estimated_delivery` AS `estimated_delivery`, `o`.`notes` AS `notes`, `o`.`admin_notes` AS `admin_notes`, `o`.`ip_address` AS `ip_address`, `o`.`user_agent` AS `user_agent`, `o`.`created_at` AS `created_at`, `o`.`updated_at` AS `updated_at`, concat(`c`.`first_name`,' ',`c`.`last_name`) AS `customer_name`, `c`.`phone` AS `customer_phone`, `u`.`email` AS `customer_email`, count(`oi`.`id`) AS `item_count`, `p`.`transaction_id` AS `transaction_id`, `p`.`payment_method` AS `pay_method` FROM ((((`orders` `o` join `customers` `c` on(`o`.`customer_id` = `c`.`id`)) join `users` `u` on(`c`.`user_id` = `u`.`id`)) left join `order_items` `oi` on(`o`.`id` = `oi`.`order_id`)) left join `payments` `p` on(`o`.`id` = `p`.`order_id` and `p`.`status` = 'completed')) GROUP BY `o`.`id` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_products`
--
DROP TABLE IF EXISTS `vw_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_products`  AS SELECT `p`.`id` AS `id`, `p`.`product_name` AS `product_name`, `p`.`slug` AS `slug`, `p`.`model` AS `model`, `p`.`sku` AS `sku`, `p`.`barcode` AS `barcode`, `p`.`price` AS `price`, `p`.`compare_price` AS `compare_price`, `p`.`quantity` AS `quantity`, `p`.`status` AS `status`, `p`.`is_featured` AS `is_featured`, `p`.`is_new_arrival` AS `is_new_arrival`, `p`.`is_best_seller` AS `is_best_seller`, `p`.`avg_rating` AS `avg_rating`, `p`.`review_count` AS `review_count`, `p`.`views` AS `views`, `p`.`total_sold` AS `total_sold`, `p`.`warranty_months` AS `warranty_months`, `p`.`short_description` AS `short_description`, `c`.`category_name` AS `category_name`, `c`.`slug` AS `category_slug`, `b`.`brand_name` AS `brand_name`, `b`.`slug` AS `brand_slug`, `b`.`logo` AS `brand_logo`, `pi`.`image_path` AS `primary_image`, `fs`.`sale_price` AS `flash_price`, `fs`.`end_time` AS `flash_end`, CASE WHEN `fs`.`id` is not null AND `fs`.`status` = 'active' AND current_timestamp() between `fs`.`start_time` and `fs`.`end_time` THEN `fs`.`sale_price` ELSE `p`.`price` END AS `effective_price` FROM ((((`products` `p` join `categories` `c` on(`p`.`category_id` = `c`.`id`)) join `brands` `b` on(`p`.`brand_id` = `b`.`id`)) left join `product_images` `pi` on(`p`.`id` = `pi`.`product_id` and `pi`.`is_primary` = 1)) left join `flash_sales` `fs` on(`p`.`id` = `fs`.`product_id` and `fs`.`status` = 'active')) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abandoned_carts`
--
ALTER TABLE `abandoned_carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_recovered` (`recovered`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brand_name` (`brand_name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `bundle_items`
--
ALTER TABLE `bundle_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_bundle` (`bundle_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_session` (`session_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bundle_id` (`bundle_id`),
  ADD KEY `idx_cart` (`cart_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_code` (`coupon_code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_code` (`coupon_code`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_coupon` (`coupon_id`),
  ADD KEY `idx_customer` (`customer_id`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `currency_code` (`currency_code`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_tier` (`tier_id`),
  ADD KEY `idx_points` (`loyalty_points`);

--
-- Indexes for table `customer_tiers`
--
ALTER TABLE `customer_tiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tier_name` (`tier_name`);

--
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_scheduled` (`scheduled_at`);

--
-- Indexes for table `flash_sales`
--
ALTER TABLE `flash_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_dates` (`start_time`,`end_time`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_type` (`transaction_type`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_invoice_number` (`invoice_number`);

--
-- Indexes for table `loyalty_points_log`
--
ALTER TABLE `loyalty_points_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_type` (`transaction_type`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_read` (`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `shipping_zone_id` (`shipping_zone_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_order` (`order_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_transaction` (`transaction_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `tax_id` (`tax_id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_brand` (`brand_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_sku` (`sku`);
ALTER TABLE `products` ADD FULLTEXT KEY `idx_search` (`product_name`,`model`,`description`,`short_description`);

--
-- Indexes for table `product_bundles`
--
ALTER TABLE `product_bundles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `product_faqs`
--
ALTER TABLE `product_faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_primary` (`is_primary`);

--
-- Indexes for table `product_tags`
--
ALTER TABLE `product_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_tag` (`tag_name`);

--
-- Indexes for table `product_videos`
--
ALTER TABLE `product_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_views`
--
ALTER TABLE `product_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_view` (`customer_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_session` (`session_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `return_number` (`return_number`),
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `return_items`
--
ALTER TABLE `return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_item_id` (`order_item_id`),
  ADD KEY `idx_return` (`return_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`product_id`,`customer_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `search_logs`
--
ALTER TABLE `search_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_term` (`search_term`(100)),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_group` (`setting_group`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Indexes for table `shipping_zones`
--
ALTER TABLE `shipping_zones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms_queue`
--
ALTER TABLE `sms_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `supervisor_id` (`supervisor_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_role` (`role_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`customer_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_customer` (`customer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abandoned_carts`
--
ALTER TABLE `abandoned_carts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `backup_logs`
--
ALTER TABLE `backup_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `bundle_items`
--
ALTER TABLE `bundle_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customer_tiers`
--
ALTER TABLE `customer_tiers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flash_sales`
--
ALTER TABLE `flash_sales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loyalty_points_log`
--
ALTER TABLE `loyalty_points_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_tracking`
--
ALTER TABLE `order_tracking`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `product_bundles`
--
ALTER TABLE `product_bundles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_faqs`
--
ALTER TABLE `product_faqs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `product_tags`
--
ALTER TABLE `product_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- AUTO_INCREMENT for table `product_videos`
--
ALTER TABLE `product_videos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_views`
--
ALTER TABLE `product_views`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_items`
--
ALTER TABLE `return_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `search_logs`
--
ALTER TABLE `search_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `shipping_zones`
--
ALTER TABLE `shipping_zones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sms_queue`
--
ALTER TABLE `sms_queue`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `abandoned_carts`
--
ALTER TABLE `abandoned_carts`
  ADD CONSTRAINT `abandoned_carts_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `abandoned_carts_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD CONSTRAINT `backup_logs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bundle_items`
--
ALTER TABLE `bundle_items`
  ADD CONSTRAINT `bundle_items_ibfk_1` FOREIGN KEY (`bundle_id`) REFERENCES `product_bundles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bundle_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`bundle_id`) REFERENCES `product_bundles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD CONSTRAINT `coupon_usage_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`tier_id`) REFERENCES `customer_tiers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `flash_sales`
--
ALTER TABLE `flash_sales`
  ADD CONSTRAINT `flash_sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `flash_sales_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loyalty_points_log`
--
ALTER TABLE `loyalty_points_log`
  ADD CONSTRAINT `loyalty_points_log_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loyalty_points_log_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD CONSTRAINT `newsletter_subscribers_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`shipping_zone_id`) REFERENCES `shipping_zones` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD CONSTRAINT `order_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_tracking_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`),
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_4` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_faqs`
--
ALTER TABLE `product_faqs`
  ADD CONSTRAINT `product_faqs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_tags`
--
ALTER TABLE `product_tags`
  ADD CONSTRAINT `product_tags_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_videos`
--
ALTER TABLE `product_videos`
  ADD CONSTRAINT `product_videos_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_views`
--
ALTER TABLE `product_views`
  ADD CONSTRAINT `product_views_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_views_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD CONSTRAINT `recently_viewed_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recently_viewed_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `returns_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `return_items`
--
ALTER TABLE `return_items`
  ADD CONSTRAINT `return_items_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `return_items_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `search_logs`
--
ALTER TABLE `search_logs`
  ADD CONSTRAINT `search_logs_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`supervisor_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;
