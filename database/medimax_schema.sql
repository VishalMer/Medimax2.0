-- ========================================================
-- MediMax Complete Database Schema & Seed Data
-- Auto-generated for fresh deployments & GitHub repository clones
-- ========================================================

CREATE DATABASE IF NOT EXISTS `medimax_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `medimax_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- Table structure for `users`
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(191) NOT NULL COMMENT 'Unique login email address',
  `password` varchar(255) NOT NULL COMMENT 'Bcrypt or Argon2 hashed password',
  `role` enum('customer','admin','owner') NOT NULL DEFAULT 'customer' COMMENT 'Role permissions for storefront and console',
  `image` varchar(255) DEFAULT NULL COMMENT 'Profile photo filename in public/images/',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Optional contact phone number',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `unique_username` (`username`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Users, administrators, and store owners';

-- Table structure for `categories`
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Display name of shelf/category',
  `slug` varchar(120) NOT NULL COMMENT 'URL-safe identifier',
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_categories_slug` (`slug`),
  KEY `idx_categories_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Product categories and shelf groups';

-- Table structure for `products`
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL COMMENT 'Shelf/Category relation',
  `sku` varchar(50) NOT NULL COMMENT 'Stock Keeping Unit (e.g. MM001)',
  `name` varchar(200) NOT NULL COMMENT 'Brand, formulation, and pack strength',
  `slug` varchar(220) NOT NULL COMMENT 'SEO URL slug',
  `description` text DEFAULT NULL COMMENT 'Product specifications and usage details',
  `price` decimal(10,2) NOT NULL COMMENT 'Active selling price in INR',
  `original_price` decimal(10,2) DEFAULT NULL COMMENT 'List MRP in INR for discount tag computation',
  `stock_quantity` int(11) NOT NULL DEFAULT 50 COMMENT 'Available inventory count',
  `image` varchar(255) NOT NULL COMMENT 'Image filename located in public/images/',
  `requires_prescription` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 if doctor prescription upload is mandatory',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 to display under Best Sellers on homepage',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 if available on storefront catalogue',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_price` (`price`),
  KEY `idx_products_featured` (`is_featured`),
  KEY `idx_products_active` (`is_active`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catalogue inventory items';

-- Table structure for `coupons`
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL COMMENT 'Coupon voucher code (e.g. MEDI10)',
  `title` varchar(150) NOT NULL COMMENT 'Display label (e.g. 10% OFF)',
  `description` varchar(255) DEFAULT NULL COMMENT 'Offer terms description',
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL COMMENT 'Percentage value or flat rupee deduction',
  `minimum_order_value` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Order spend threshold to qualify',
  `maximum_discount` decimal(10,2) DEFAULT NULL COMMENT 'Cap ceiling for percentage discounts in INR',
  `applicable_category_id` int(10) unsigned DEFAULT NULL COMMENT 'NULL for all categories, or specific category ID',
  `usage_limit` int(10) unsigned DEFAULT NULL COMMENT 'Maximum total redemption times allowed',
  `used_count` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Number of times already redeemed',
  `expiry_date` date NOT NULL COMMENT 'Expiry deadline date',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `applicable_category_id` (`applicable_category_id`),
  KEY `idx_coupons_code` (`code`),
  KEY `idx_coupons_active_expiry` (`is_active`,`expiry_date`),
  CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`applicable_category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Coupons and promotional discount rules';

-- Table structure for `user_addresses`
CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT 'Owner of this address',
  `address_type` enum('Home','Work','Other') NOT NULL DEFAULT 'Home',
  `full_name` varchar(150) NOT NULL COMMENT 'Recipient full name',
  `mobile` varchar(20) NOT NULL COMMENT '10-digit delivery contact number',
  `email` varchar(191) DEFAULT NULL COMMENT 'Notification email',
  `address_line1` varchar(255) NOT NULL COMMENT 'Flat, House no., Building, Company',
  `address_line2` varchar(255) DEFAULT NULL COMMENT 'Area, Street, Sector, Village',
  `city` varchar(100) NOT NULL COMMENT 'City or Town',
  `state` varchar(100) NOT NULL COMMENT 'State (e.g. Gujarat, Maharashtra)',
  `pincode` varchar(10) NOT NULL COMMENT '6-digit postal pincode',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 if primary shipping address',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_addresses_user` (`user_id`),
  KEY `idx_addresses_is_active` (`is_active`),
  CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer saved delivery addresses';

-- Table structure for `orders`
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL COMMENT 'Public reference (e.g. #MX-0001 or MED-2026-12345)',
  `user_id` int(10) unsigned NOT NULL COMMENT 'Placing customer account',
  `shipping_name` varchar(150) NOT NULL COMMENT 'Recipient name at checkout',
  `shipping_mobile` varchar(20) NOT NULL COMMENT 'Contact phone number',
  `shipping_email` varchar(191) NOT NULL COMMENT 'Order confirmation email',
  `shipping_address_line1` varchar(255) NOT NULL COMMENT 'Flat/building address',
  `shipping_address_line2` varchar(255) DEFAULT NULL COMMENT 'Street/area',
  `shipping_city` varchar(100) NOT NULL,
  `shipping_state` varchar(100) NOT NULL,
  `shipping_pincode` varchar(10) NOT NULL,
  `prescription_file` varchar(255) DEFAULT NULL COMMENT 'Prescription document path if required',
  `coupon_id` int(10) unsigned DEFAULT NULL COMMENT 'Applied coupon if any',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'Sum of item prices (MRP/base)',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Coupon and promotional savings',
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Shipping charges (0.00 if over 499 threshold)',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Final net amount payable by customer',
  `payment_method` enum('UPI','CARD','NETBANK','COD') NOT NULL DEFAULT 'COD',
  `payment_status` enum('Pending','Completed','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  `delivery_status` enum('Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Processing',
  `order_notes` text DEFAULT NULL,
  `placed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `coupon_id` (`coupon_id`),
  KEY `idx_orders_user` (`user_id`),
  KEY `idx_orders_number` (`order_number`),
  KEY `idx_orders_payment_status` (`payment_status`),
  KEY `idx_orders_delivery_status` (`delivery_status`),
  KEY `idx_orders_placed_at` (`placed_at`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Orders placed by customers';

-- Table structure for `order_items`
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `product_name` varchar(200) NOT NULL COMMENT 'Snapshot of product title at purchase',
  `product_sku` varchar(50) NOT NULL COMMENT 'Snapshot of SKU',
  `product_image` varchar(255) DEFAULT NULL COMMENT 'Snapshot of thumbnail',
  `unit_price` decimal(10,2) NOT NULL COMMENT 'Price per single unit in INR',
  `quantity` int(10) unsigned NOT NULL DEFAULT 1 COMMENT 'Ordered quantity count',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'unit_price * quantity',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order` (`order_id`),
  KEY `idx_order_items_product` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Individual line items attached to an order';

-- Table structure for `payments`
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `transaction_ref` varchar(100) DEFAULT NULL COMMENT 'Bank/gateway transaction ID',
  `payment_method` enum('UPI','CARD','NETBANK','COD') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_details` text DEFAULT NULL COMMENT 'UPI ID, masked card number, or bank name',
  `status` enum('Pending','Completed','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_payments_order` (`order_id`),
  KEY `idx_payments_status` (`status`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payment gateway attempts and receipts';

-- Table structure for `coupon_redemptions`
CREATE TABLE IF NOT EXISTS `coupon_redemptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `idx_coupon_redemptions_user` (`user_id`),
  KEY `idx_coupon_redemptions_order` (`order_id`),
  CONSTRAINT `coupon_redemptions_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_redemptions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_redemptions_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Coupon redemption history';

-- Table structure for `cart_items`
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL COMMENT 'Associated user if logged in',
  `session_id` varchar(100) DEFAULT NULL COMMENT 'Session identifier for guest users',
  `product_id` int(10) unsigned NOT NULL COMMENT 'Product added to basket',
  `quantity` int(10) unsigned NOT NULL DEFAULT 1 COMMENT 'Cart unit quantity',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cart_user_product` (`user_id`,`product_id`),
  UNIQUE KEY `uk_cart_session_product` (`session_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_cart_session` (`session_id`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Live shopping cart items';

-- Table structure for `wishlist`
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wishlist_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer saved wishlist items';

-- Table structure for `contact_messages`
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL COMMENT 'Sender full name',
  `email` varchar(191) NOT NULL COMMENT 'Sender email address',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Optional contact number',
  `subject` varchar(150) DEFAULT NULL COMMENT 'Inquiry title or related order #',
  `message` text NOT NULL COMMENT 'Full inquiry message body',
  `status` enum('unread','read','replied','closed') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_contact_status` (`status`),
  KEY `idx_contact_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contact form submissions and support tickets';

-- ========================================================
-- Default Seed Data
-- ========================================================

-- 1. Default Users (Admin & Customers)
INSERT INTO `users` (`username`, `name`, `email`, `password`, `role`, `image`, `phone`, `status`, `is_active`)
SELECT 'admin', 'Admin', 'admin@medimax.com', '$2y$10$SNb/jipByJZ/8a6iSInYneeVLed1rUgNgb/Mrdwf8YG1a2J86P5D6', 'owner', 'Rahul.jpg', '9876543210', 'active', 1
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'admin@medimax.com' OR `username` = 'admin');

INSERT INTO `users` (`username`, `name`, `email`, `password`, `role`, `image`, `phone`, `status`, `is_active`)
SELECT 'user01', 'User One', 'user01@medimax.com', '$2y$10$yoXF9GdE/.oxRrk0.IuowOe9Vj8qQB6RA1JPLvSUryhL9cEjRBcge', 'customer', 'User PP (11).jpeg', '9876543211', 'active', 1
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'user01@medimax.com' OR `username` = 'user01');

INSERT INTO `users` (`username`, `name`, `email`, `password`, `role`, `image`, `phone`, `status`, `is_active`)
SELECT 'rahul', 'Rahul', 'rahul@medimax.com', '$2y$10$yoXF9GdE/.oxRrk0.IuowOe9Vj8qQB6RA1JPLvSUryhL9cEjRBcge', 'customer', 'Rahul.jpg', '9876543212', 'active', 1
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'rahul@medimax.com' OR `username` = 'rahul');

-- 2. Categories & Products
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`) VALUES (1, 'Vitamins', 'vitamins', 'Essential daily vitamins, calcium supplements, and nutritional defense formulas.', 'vitamin-c.jpeg', 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `image`=VALUES(`image`);
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`) VALUES (2, 'Supplements', 'supplements', 'Athletic nutrition, creatine, whey proteins, and pure omega-3 fish oils.', 'creatine.jpeg', 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `image`=VALUES(`image`);
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`) VALUES (3, 'Skincare', 'skincare', 'Dermatological face washes, hydrating serums, soothing moisturizers, and UV protection.', 'simple-facewash.jpg', 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `image`=VALUES(`image`);
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`) VALUES (4, 'Hair Care', 'hair-care', 'Clinical shampoos, anti-dandruff solutions, hair growth vitamins, and nourishing oils.', 'dove-shampoo.jpg', 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `image`=VALUES(`image`);
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`) VALUES (5, 'First Aid', 'first-aid', 'Antiseptic cleansers, crepe bandages, sterile gauze swabs, and surgical masks.', 'dettol.jpg', 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `image`=VALUES(`image`);
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`) VALUES (6, 'Baby Care', 'baby-care', 'Pediatric dermatologist approved barrier creams and infant care formulations.', 'diaper-care-cream.jpg', 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `image`=VALUES(`image`);
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`) VALUES (7, 'Personal Care', 'personal-care', 'Soothing pain relief sprays, grooming essentials, and therapeutic essential oils.', 'OHBT-spray.jpg', 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `image`=VALUES(`image`);
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`) VALUES (8, 'Beverages', 'beverages', 'Antioxidant-rich herbal teas, pure green teas, and classic roasted coffee.', 'green-tea.jpg', 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `image`=VALUES(`image`);
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`) VALUES (9, 'Ayurveda & Herbal', 'ayurveda-herbal', 'Authentic botanical extracts, single-origin amla powder, and turmeric formulations.', 'thejal-turmeric-powder.jpg', 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `image`=VALUES(`image`);

INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (1, 1, 'MM001', 'Vitamin C 1000mg Effervescent Tablets', 'vitamin-c-1000mg-effervescent-tablets', 'Daily immunity booster effervescent fizzy tablets with natural Amla extract and Zinc in a refreshing tangy orange flavor.', 299.00, 349.00, 140, 'vitamin-c.jpeg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (2, 2, 'MM002', 'Omega-3 Triple Strength Fish Oil 1000mg', 'omega-3-triple-strength-fish-oil-1000mg', 'Molecularly distilled enteric-coated fish oil softgels high in EPA and DHA to support cardiovascular wellness, joint flexibility, and brain health.', 680.00, 799.00, 95, 'Fish Oil.jpg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (3, 2, 'MM003', 'Micronized Creatine Monohydrate 250g', 'micronized-creatine-monohydrate-250g', 'Pure 100% micronized creatine monohydrate powder to increase muscle strength, explosive power, and athletic endurance.', 1250.00, 1499.00, 45, 'creatine.jpeg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (4, 2, 'MM004', 'High Protein Belgian Chocolate Shake 500ml', 'high-protein-belgian-chocolate-shake-500ml', 'Ready-to-drink Belgian chocolate recovery shake delivering 30g pure whey protein isolate with zero added refined sugars.', 1899.00, 2199.00, 50, 'protein-shake.jpeg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (5, 2, 'MM005', 'Multisource Plant Protein Powder 1kg', 'multisource-plant-protein-powder-1kg', 'Complete vegan protein blend combining pea, brown rice, and chia seeds delivering 25g clean protein and BCAAs per serving.', 2100.00, 2499.00, 35, 'multisource-protein.jpeg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (6, 4, 'MM006', 'Biotin & Keratin Hair Growth Vitamins', 'biotin-keratin-hair-growth-vitamins', 'Synergistic formula of 10,000 mcg Biotin, zinc, folic acid, and saw palmetto to reduce hair shedding and stimulate strong follicle growth.', 599.00, 699.00, 85, 'hair-vitamins.jpg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (7, 1, 'MM007', 'Calcium + Vitamin D3 Syrup 200ml', 'calcium-vitamin-d3-syrup-200ml', 'Pediatric and adult dietary supplement syrup formulated with calcium gluconate and vitamin D3 to build strong bones and teeth.', 320.00, 375.00, 65, 'Calcium Syrup.jpeg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (8, 2, 'MM008', 'Curcumin 95% Turmeric Extract Capsules', 'curcumin-95-turmeric-extract-capsules', 'Standardized 95% curcuminoids enhanced with BioPerine black pepper extract for maximum absorption to support joint comfort and vitality.', 275.00, 320.00, 85, 'turmeric.jpg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (9, 3, 'MM009', 'Acne Control Gel 30g', 'acne-control-gel-30g', 'Fast-acting salicylic acid and tea tree formula to clear breakouts, unclog pores, and prevent future acne flare-ups without over-drying.', 399.00, 450.00, 85, 'acne-control-gel.jpeg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (10, 4, 'MM010', 'Pure Moroccan Argan Oil 100ml', 'pure-moroccan-argan-oil-100ml', '100% cold-pressed organic argan oil to tame frizz, repair heat damage, and impart a brilliant shine to dull hair.', 550.00, 650.00, 70, 'argan-oil.jpeg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (11, 3, 'MM011', 'Deep Moisture Barrier Nourishing Cream 150ml', 'deep-moisture-barrier-nourishing-cream-150ml', 'Ceramide and shea butter enriched emulsion providing 48-hour continuous moisture protection against dry, irritated skin.', 485.00, 550.00, 55, 'nourishing-cream.jpeg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (12, 3, 'MM012', 'Floslek Sun Care SPF 50+ Oil-Free Tint', 'floslek-sun-care-spf-50-oil-free-tint', 'Broad-spectrum UVA/UVB high protection sunscreen with matte finish, water resistance, and antioxidant protection for sensitive skin.', 780.00, 899.00, 40, 'floslek-sun-care.jpeg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (13, 5, 'MM013', 'Elastic Cotton Bandage Roll', 'elastic-cotton-bandage-roll', 'Breathable, stretchable surgical cotton bandage roll designed for sprain support, dressing retention, and wound compression.', 120.00, 140.00, 150, 'Bandage Roll.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (14, 5, 'MM014', 'Adhesive Fabric Bandages (Pack of 50)', 'adhesive-fabric-bandages-pack-of-50', 'Flexible, sterile fabric adhesive strips with non-stick pads for quick protection of minor cuts, scrapes, and blisters.', 85.00, 99.00, 200, 'Bandage.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (15, 5, 'MM015', 'Dettol Antiseptic Disinfectant Liquid 500ml', 'dettol-antiseptic-disinfectant-liquid-500ml', 'Trusted antibacterial antiseptic liquid for first aid wound cleansing, surface disinfection, and personal hygiene protection.', 195.00, 215.00, 110, 'dettol.jpg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (16, 6, 'MM016', 'Diaper Rash Relief Cream 100g', 'diaper-rash-relief-cream-100g', 'Zinc oxide and calendula barrier cream that soothes redness, calms diaper rash irritation, and shields delicate baby skin.', 350.00, 399.00, 75, 'diaper-care-cream.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (17, 4, 'MM017', 'Keratin Moisture Repair Conditioner 250ml', 'keratin-moisture-repair-conditioner-250ml', 'Intensive leave-in conditioning treatment with plant keratin and argan butter to detangle, smooth split ends, and restore elasticity.', 420.00, 490.00, 55, 'conditioner.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (18, 4, 'MM018', 'Dove Daily Moisture Shampoo 340ml', 'dove-daily-moisture-shampoo-340ml', 'Pro-Moisture complex shampoo designed to protect hair from daily wear and tear, providing up to 5x smoother hair in one wash.', 375.00, 420.00, 85, 'dove-shampoo.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (19, 8, 'MM019', 'Pure Organic Himalayan Green Tea (25 Bags)', 'pure-organic-himalayan-green-tea-25-bags', 'Whole leaf green tea harvested from high-elevation gardens, loaded with EGCG antioxidants for metabolism and natural detox.', 299.00, 350.00, 120, 'green-tea.jpg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (20, 8, 'MM020', 'Nescafe Classic 100% Pure Instant Coffee 200g', 'nescafe-classic-instant-coffee-200g', 'Medium-dark roasted Arabica and Robusta coffee beans delivering rich aroma and full-bodied distinctive morning flavor.', 450.00, 485.00, 100, 'nescafe-cofee.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (21, 7, 'MM021', 'Pure Peppermint Essential Oil 30ml', 'pure-peppermint-essential-oil-30ml', 'Steam-distilled pure mentha piperita essential oil for headache relief, soothing aroma diffuser therapy, and muscle relaxation.', 310.00, 360.00, 80, 'peperment.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (22, 3, 'MM022', 'Pure Hydro-Distilled Rose Water Mist 200ml', 'pure-hydro-distilled-rose-water-mist-200ml', '100% natural steam-distilled Kannauj rose water toner that refreshes tired skin, balances natural pH, and closes open pores.', 220.00, 260.00, 110, 'rose-water.jpg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (23, 7, 'MM023', 'Cold Pressed Extra Virgin Olive Oil 250ml', 'cold-pressed-extra-virgin-olive-oil-250ml', 'Multipurpose 100% pure extra virgin olive oil ideal for body massage, scalp nourishing, and skin conditioning.', 340.00, 395.00, 60, 'olive-oil.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (24, 3, 'MM024', 'Simple Kind to Skin Refreshing Gel Wash 150ml', 'simple-kind-to-skin-refreshing-gel-wash-150ml', '100% soap-free facial wash enriched with provitamin B5 and vitamin E; contains no artificial perfume, color, or harsh chemicals.', 465.00, 525.00, 95, 'simple-facewash.jpg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (25, 5, 'MM025', 'OHBT Herbal Fast Pain Relief Spray 100ml', 'ohbt-herbal-pain-relief-spray-100ml', 'Micro-penetrating herbal analgesic spray formulated with gandhapura and menthol for fast relief from muscular aches, sprains, and back pain.', 290.00, 330.00, 70, 'OHBT-spray.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (26, 3, 'MM026', 'Allure Natural Dreamy Avocado Cream 200ml', 'allure-natural-dreamy-avocado-cream-200ml', 'Deeply hydrating body and hand cream enriched with pure avocado butter and vitamin E for velvety smooth skin.', 425.00, 499.00, 60, 'allure-natural-dreamy-avocado.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (27, 3, 'MM027', 'Arch Beaute Mega Multitasker Serum 50ml', 'arch-beaute-mega-multitasker-serum-50ml', 'All-in-one restorative face serum featuring hyaluronic acid, niacinamide, and peptides to brighten, firm, and hydrate.', 890.00, 1050.00, 40, 'arch-beaute-megamultitasker.jpg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (28, 9, 'MM028', 'Ayurvedam Organic Amla Powder 200g', 'ayurvedam-organic-amla-powder-200g', 'Pure sun-dried Indian gooseberry (Amla) powder, rich in natural Vitamin C and antioxidants for immunity, digestion, and hair strength.', 220.00, 260.00, 90, 'ayurvedam-amla-powder.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (29, 4, 'MM029', 'Dove Pink Beauty Moisture Shampoo 340ml', 'dove-pink-beauty-moisture-shampoo-340ml', 'Gentle moisturizing formula infused with Nutritive Serum that leaves hair noticeably softer, smoother, and manageable.', 385.00, 430.00, 80, 'dove-pink-shampoo.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (30, 4, 'MM030', 'DR.7 Anti-Dandruff Clinical Shampoo 250ml', 'dr7-anti-dandruff-clinical-shampoo-250ml', 'Medicated zinc pyrithione and ketoconazole formulation that relieves scalp itching, removes persistent flakes, and restores scalp balance.', 495.00, 575.00, 50, 'dr7-shampoo.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (31, 7, 'MM031', 'Greeko Man Beard & Face Wash 100ml', 'greeko-man-beard-face-wash-100ml', 'Dual action activated charcoal face wash that cleanses facial skin, softens coarse beard hair, and unclogs deep pores.', 299.00, 349.00, 65, 'greeko-beard-n-facewash.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (32, 3, 'MM032', 'Greeko Man Detox Clay Mask 100g', 'greeko-man-detox-clay-mask-100g', 'Mineral-rich volcanic bentonite and kaolin clay mask that draws out toxins, balances excess sebum, and tightens pores.', 349.00, 399.00, 50, 'greeko-clay-mask.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (33, 4, 'MM033', 'Head & Shoulders Cool Menthol Shampoo 340ml', 'head-shoulders-cool-menthol-shampoo-340ml', 'Clinically proven pyrithione zinc formula with cooling menthol that purifies scalp, cools upon contact, and eliminates up to 100% of visible flakes.', 360.00, 399.00, 90, 'head-n-shoulders-shampoo.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (34, 3, 'MM034', 'House of Beauty Nourishing Night Cream 50g', 'house-of-beauty-nourishing-night-cream-50g', 'Ultra-rich overnight peptide and collagen replenishment cream designed to repair the skin barrier and restore morning radiance.', 520.00, 599.00, 45, 'house-of-beauty-cream.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (35, 5, 'MM035', '3-Ply Surgical Protective Face Masks (Pack of 50)', '3-ply-surgical-protective-face-masks-pack-of-50', 'Certified melt-blown filter surgical grade face masks with soft elastic ear loops and moldable nose clip for superior respiratory protection.', 199.00, 250.00, 300, 'Mask1.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (36, 5, 'MM036', 'Sterile Cotton Gauze Swab Pads (Pack of 20)', 'sterile-cotton-gauze-swab-pads-pack-of-20', 'Highly absorbent 8-ply 100% medical cotton gauze pads individually packaged for aseptic wound cleansing and dressing.', 95.00, 120.00, 180, 'Pad.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (37, 4, 'MM037', 'Salon Professional Repair & Shine Shampoo 300ml', 'salon-professional-repair-shine-shampoo-300ml', 'Sulfate-free restorative shampoo with silk amino acids and botanical extracts to protect color-treated and heat-damaged hair.', 540.00, 620.00, 40, 'premium-shampoo.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (38, 9, 'MM038', 'Thejal Pure Lakadong Turmeric Powder 200g', 'thejal-pure-lakadong-turmeric-powder-200g', 'High curcumin (7%+) single-origin heirloom turmeric powder from Meghalaya with superior anti-inflammatory and culinary potency.', 240.00, 280.00, 70, 'thejal-turmeric-powder.jpg', 0, 0, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `original_price`, `stock_quantity`, `image`, `requires_prescription`, `is_featured`, `is_active`) VALUES (39, 3, 'MM039', 'Vitamin C 20% Glow Face Serum 30ml', 'vitamin-c-20-glow-face-serum-30ml', 'Potent L-ascorbic acid serum with ferulic acid and vitamin E that fades dark spots, protects against photo-aging, and boosts collagen.', 599.00, 699.00, 60, 'vitamin-c.jpg', 0, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `original_price`=VALUES(`original_price`), `stock_quantity`=VALUES(`stock_quantity`), `image`=VALUES(`image`);

-- 3. Promotional Coupons
INSERT INTO `coupons` (`code`, `title`, `description`, `discount_type`, `discount_value`, `minimum_order_value`, `maximum_discount`, `expiry_date`, `is_active`)
SELECT 'MEDI10', '10% OFF', 'Get 10% off on your medicine order', 'percentage', 10, 499, 100, '2028-12-31', 1
WHERE NOT EXISTS (SELECT 1 FROM `coupons` WHERE `code` = 'MEDI10');
INSERT INTO `coupons` (`code`, `title`, `description`, `discount_type`, `discount_value`, `minimum_order_value`, `maximum_discount`, `expiry_date`, `is_active`)
SELECT 'MEDI20', '20% OFF', 'Get 20% off on orders above Rs. 999', 'percentage', 20, 999, 300, '2028-12-31', 1
WHERE NOT EXISTS (SELECT 1 FROM `coupons` WHERE `code` = 'MEDI20');
INSERT INTO `coupons` (`code`, `title`, `description`, `discount_type`, `discount_value`, `minimum_order_value`, `maximum_discount`, `expiry_date`, `is_active`)
SELECT 'SAVE50', 'Flat Rs. 50 OFF', 'Flat Rs. 50 discount on your order', 'fixed', 50, 499, 50, '2028-12-31', 1
WHERE NOT EXISTS (SELECT 1 FROM `coupons` WHERE `code` = 'SAVE50');
INSERT INTO `coupons` (`code`, `title`, `description`, `discount_type`, `discount_value`, `minimum_order_value`, `maximum_discount`, `expiry_date`, `is_active`)
SELECT 'HEALTH15', '15% OFF', '15% off on healthcare and first aid', 'percentage', 15, 799, 200, '2028-12-31', 1
WHERE NOT EXISTS (SELECT 1 FROM `coupons` WHERE `code` = 'HEALTH15');
INSERT INTO `coupons` (`code`, `title`, `description`, `discount_type`, `discount_value`, `minimum_order_value`, `maximum_discount`, `expiry_date`, `is_active`)
SELECT 'WELCOME100', 'Rs. 100 OFF', 'Welcome discount for all shoppers', 'fixed', 100, 999, 100, '2028-12-31', 1
WHERE NOT EXISTS (SELECT 1 FROM `coupons` WHERE `code` = 'WELCOME100');
SET FOREIGN_KEY_CHECKS = 1;
