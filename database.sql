-- ============================================================
-- MAHALUXMI TRADING HOUSE PVT. LTD. - DATABASE
-- MySQL 5.7+ / MariaDB 10.4+ | InnoDB | utf8mb4
-- IMPORT NOTE: Create/select your cPanel database in phpMyAdmin first,
-- then import this file. The SQL intentionally does not create or USE a
-- database because shared-hosting users normally do not have that privilege.
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;

-- ---------- USERS / ROLES ----------
CREATE TABLE admin_roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  permissions JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  status TINYINT(1) DEFAULT 1,
  last_login DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES admin_roles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- CUSTOMERS ----------
CREATE TABLE customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,
  company_name VARCHAR(150) NULL,
  gstin VARCHAR(20) NULL,
  business_type VARCHAR(60) NULL,
  is_verified TINYINT(1) DEFAULT 0,
  is_business TINYINT(1) DEFAULT 0,
  b2b_status ENUM('none','pending','approved','rejected') DEFAULT 'none',
  price_tier VARCHAR(30) NULL,
  status TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE customer_addresses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  type ENUM('billing','shipping') DEFAULT 'shipping',
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  address TEXT NOT NULL,
  landmark VARCHAR(150) NULL,
  city VARCHAR(80) NOT NULL,
  state VARCHAR(80) NOT NULL,
  pin_code VARCHAR(12) NOT NULL,
  country VARCHAR(80) DEFAULT 'India',
  is_default TINYINT(1) DEFAULT 0,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- CATALOG ----------
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parent_id INT NULL,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(150) NOT NULL UNIQUE,
  description TEXT NULL,
  image VARCHAR(255) NULL,
  banner_image VARCHAR(255) NULL,
  sort_order INT DEFAULT 0,
  is_featured TINYINT(1) DEFAULT 0,
  status TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE brands (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(150) NOT NULL UNIQUE,
  logo VARCHAR(255) NULL,
  status TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE attributes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NULL,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  input_type ENUM('select','text','number') DEFAULT 'select',
  status TINYINT(1) DEFAULT 1,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE attribute_values (
  id INT AUTO_INCREMENT PRIMARY KEY,
  attribute_id INT NOT NULL,
  value VARCHAR(120) NOT NULL,
  FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  sku VARCHAR(60) NOT NULL UNIQUE,
  brand_id INT NULL,
  category_id INT NOT NULL,
  product_type VARCHAR(60) DEFAULT 'simple',
  short_description TEXT NULL,
  description LONGTEXT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  mrp DECIMAL(12,2) NULL,
  cost_price DECIMAL(12,2) NULL,
  tax_class_id INT NULL,
  hsn VARCHAR(20) NULL,
  weight DECIMAL(10,3) NULL,
  dimensions VARCHAR(100) NULL,
  stock INT DEFAULT 0,
  low_stock_threshold INT DEFAULT 5,
  is_featured TINYINT(1) DEFAULT 0,
  is_bestseller TINYINT(1) DEFAULT 0,
  is_new TINYINT(1) DEFAULT 0,
  purchasable TINYINT(1) DEFAULT 1,
  quote_only TINYINT(1) DEFAULT 0,
  status TINYINT(1) DEFAULT 1,
  views INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
  INDEX idx_cat (category_id), INDEX idx_status (status), INDEX idx_price (price)
) ENGINE=InnoDB;

CREATE TABLE product_variants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  variant_name VARCHAR(150) NOT NULL,
  sku VARCHAR(60) NOT NULL UNIQUE,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  mrp DECIMAL(12,2) NULL,
  stock INT DEFAULT 0,
  weight DECIMAL(10,3) NULL,
  image VARCHAR(255) NULL,
  status TINYINT(1) DEFAULT 1,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image VARCHAR(255) NOT NULL,
  alt_text VARCHAR(200) NULL,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE product_attribute_values (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  attribute_id INT NOT NULL,
  value VARCHAR(200) NOT NULL,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE product_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- MEDIA ----------
CREATE TABLE media (
  id INT AUTO_INCREMENT PRIMARY KEY,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  mime_type VARCHAR(80) NOT NULL,
  alt_text VARCHAR(200) NULL,
  folder VARCHAR(80) DEFAULT 'general',
  uploaded_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- CART / WISHLIST / COMPARE ----------
CREATE TABLE carts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NULL,
  session_id VARCHAR(80) NULL,
  coupon_code VARCHAR(40) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cart_id INT NOT NULL,
  product_id INT NOT NULL,
  variant_id INT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE wishlists (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  product_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_wish (customer_id, product_id),
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- ORDERS ----------
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(30) NOT NULL UNIQUE,
  customer_id INT NULL,
  guest_email VARCHAR(150) NULL,
  guest_phone VARCHAR(20) NULL,
  billing_address TEXT NULL,
  shipping_address TEXT NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount DECIMAL(12,2) DEFAULT 0,
  tax_total DECIMAL(12,2) DEFAULT 0,
  shipping_total DECIMAL(12,2) DEFAULT 0,
  grand_total DECIMAL(12,2) NOT NULL DEFAULT 0,
  coupon_code VARCHAR(40) NULL,
  payment_method VARCHAR(40) DEFAULT 'cod',
  payment_status ENUM('pending','paid','failed','refunded','partial_refund') DEFAULT 'pending',
  status ENUM('placed','confirmed','processing','packed','shipped','out_for_delivery','delivered','cancelled') DEFAULT 'placed',
  courier_name VARCHAR(100) NULL,
  tracking_number VARCHAR(100) NULL,
  tracking_url VARCHAR(255) NULL,
  placed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  INDEX idx_status (status), INDEX idx_cust (customer_id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NULL,
  product_name VARCHAR(200) NOT NULL,
  sku VARCHAR(60) NULL,
  variant_name VARCHAR(150) NULL,
  hsn VARCHAR(20) NULL,
  quantity INT NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  tax_rate DECIMAL(5,2) DEFAULT 0,
  tax_amount DECIMAL(12,2) DEFAULT 0,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_status_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  status VARCHAR(40) NOT NULL,
  note VARCHAR(255) NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  gateway VARCHAR(40) NOT NULL,
  gateway_order_id VARCHAR(120) NULL,
  gateway_txn_id VARCHAR(120) NULL,
  method VARCHAR(60) NULL,
  amount DECIMAL(12,2) NOT NULL,
  status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
  raw_response JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  invoice_number VARCHAR(40) NOT NULL UNIQUE,
  issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE returns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  order_item_id INT NOT NULL,
  customer_id INT NOT NULL,
  reason VARCHAR(200) NOT NULL,
  description TEXT NULL,
  images JSON NULL,
  status ENUM('requested','approved','rejected','pickup','received','refunded') DEFAULT 'requested',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- INVENTORY ----------
CREATE TABLE inventory_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  variant_id INT NULL,
  change INT NOT NULL,
  reason ENUM('sale','cancel','return','adjustment') NOT NULL,
  reference VARCHAR(60) NULL,
  note VARCHAR(255) NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- SHIPPING / TAX ----------
CREATE TABLE shipping_zones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  states TEXT NULL,
  status TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE shipping_methods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  zone_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  type ENUM('flat','free','order_value','weight') DEFAULT 'flat',
  rate DECIMAL(10,2) DEFAULT 0,
  min_order DECIMAL(12,2) NULL,
  max_order DECIMAL(12,2) NULL,
  per_kg DECIMAL(10,2) NULL,
  status TINYINT(1) DEFAULT 1,
  FOREIGN KEY (zone_id) REFERENCES shipping_zones(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tax_classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE tax_rates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tax_class_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  percentage DECIMAL(5,2) NOT NULL,
  hsn VARCHAR(20) NULL,
  is_inclusive TINYINT(1) DEFAULT 0,
  FOREIGN KEY (tax_class_id) REFERENCES tax_classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- COUPONS ----------
CREATE TABLE coupons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  type ENUM('percent','fixed') NOT NULL,
  value DECIMAL(12,2) NOT NULL,
  min_cart DECIMAL(12,2) DEFAULT 0,
  max_discount DECIMAL(12,2) NULL,
  starts_at DATE NULL,
  expires_at DATE NULL,
  usage_limit INT NULL,
  per_user_limit INT NULL,
  status TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE coupon_usage (
  id INT AUTO_INCREMENT PRIMARY KEY,
  coupon_id INT NOT NULL,
  customer_id INT NULL,
  order_id INT NULL,
  used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- B2B / QUOTES ----------
CREATE TABLE bulk_quotes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quote_number VARCHAR(30) NOT NULL UNIQUE,
  customer_id INT NULL,
  company VARCHAR(150) NULL,
  contact_person VARCHAR(120) NULL,
  phone VARCHAR(20) NULL,
  email VARCHAR(150) NULL,
  gstin VARCHAR(20) NULL,
  business_type VARCHAR(60) NULL,
  destination VARCHAR(150) NULL,
  requirement_date DATE NULL,
  message TEXT NULL,
  status ENUM('new','contacted','quoted','converted','closed') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE bulk_quote_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quote_id INT NOT NULL,
  product_id INT NULL,
  variant_id INT NULL,
  quantity INT NOT NULL,
  FOREIGN KEY (quote_id) REFERENCES bulk_quotes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- CONTENT ----------
CREATE TABLE contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  company VARCHAR(150) NULL,
  phone VARCHAR(20) NOT NULL,
  email VARCHAR(150) NOT NULL,
  subject VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE newsletter_subscribers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  consent TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(200) NOT NULL UNIQUE,
  content LONGTEXT NULL,
  meta_title VARCHAR(200) NULL,
  meta_description TEXT NULL,
  status TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE blog_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE blogs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NULL,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  featured_image VARCHAR(255) NULL,
  excerpt TEXT NULL,
  content LONGTEXT NULL,
  author VARCHAR(120) NULL,
  tags VARCHAR(255) NULL,
  meta_title VARCHAR(200) NULL,
  meta_description TEXT NULL,
  og_image VARCHAR(255) NULL,
  status ENUM('draft','published') DEFAULT 'draft',
  published_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE videos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  category VARCHAR(80) NULL,
  video_type ENUM('youtube','vimeo','mp4') DEFAULT 'youtube',
  video_url VARCHAR(255) NOT NULL,
  thumbnail VARCHAR(255) NULL,
  attached_to VARCHAR(60) NULL,
  attached_id INT NULL,
  sort_order INT DEFAULT 0,
  status TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE faqs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  category VARCHAR(80) NULL,
  sort_order INT DEFAULT 0,
  status TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  company VARCHAR(150) NULL,
  rating TINYINT DEFAULT 5,
  review TEXT NOT NULL,
  image VARCHAR(255) NULL,
  status TINYINT(1) DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  customer_id INT NOT NULL,
  order_id INT NULL,
  rating TINYINT NOT NULL,
  title VARCHAR(150) NULL,
  review TEXT NOT NULL,
  images JSON NULL,
  is_verified_purchase TINYINT(1) DEFAULT 0,
  status TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- TRADE / MARKETS ----------
CREATE TABLE trade_markets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  country VARCHAR(100) NOT NULL,
  trade_type ENUM('import','export','both') DEFAULT 'both',
  products VARCHAR(255) NULL,
  description TEXT NULL,
  status TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- ---------- SETTINGS / SEO ----------
CREATE TABLE site_settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE social_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  platform VARCHAR(60) NOT NULL,
  url VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE seo_meta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page_path VARCHAR(200) NOT NULL,
  meta_title VARCHAR(200) NULL,
  meta_description TEXT NULL,
  og_image VARCHAR(255) NULL,
  canonical VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE redirects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  from_url VARCHAR(255) NOT NULL,
  to_url VARCHAR(255) NOT NULL,
  status_code INT DEFAULT 301
) ENGINE=InnoDB;

CREATE TABLE admin_activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  action VARCHAR(100) NOT NULL,
  details TEXT NULL,
  ip_address VARCHAR(60) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE password_reset_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  token VARCHAR(100) NOT NULL,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE stock_notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  email VARCHAR(150) NOT NULL,
  is_notified TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- DEFAULT DATA ----------
INSERT INTO admin_roles (name, permissions) VALUES
('Super Admin', NULL),
('Order Manager', '["orders.view","orders.update"]'),
('Catalogue Manager', '["products.manage","categories.manage"]'),
('Content Manager', '["pages.manage","blogs.manage"]');

-- Default admin: email admin@mahaluxmi.in | password Admin@123 (CHANGE IMMEDIATELY)
INSERT INTO admins (role_id, name, email, password) VALUES
(1, 'Super Admin', 'admin@mahaluxmi.in', '$2y$10$tbQ2s2PRg3OmeE5GhHE5DOmeqsAJbcWJEc3mG6H9kDT1R3PyzHiHe');

INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'Mahaluxmi Trading House Pvt. Ltd.'),
('site_tagline', 'Trade • Supply • Import • Export'),
('company_email', 'mltradinghouse@gmail.com'),
('company_mobile', '+91 99888 88889'),
('company_phone1', '+91 164 2250015'),
('company_phone2', '+91 164 5006992'),
('registered_address', '1st Floor, Plot No. 2029, The Mall Road, Bathinda, Punjab – 151001, India'),
('office_address', 'Plot No. 2028, The Mall Road, Bathinda – 151001, Punjab'),
('cin', 'U74999PB2019PTC049303'),
('gstin', ''),
('whatsapp', ''),
('map_embed_url', ''),
('map_directions_url', ''),
('guest_checkout', '1'),
('maintenance_mode', '0'),
('smtp_host', ''), ('smtp_port', '587'), ('smtp_user', ''), ('smtp_pass', ''),
('smtp_from_email', ''), ('smtp_from_name', 'Mahaluxmi Trading House'),
('razorpay_key_id', ''), ('razorpay_key_secret', ''),
('ga4_id', ''), ('gtm_id', ''), ('meta_pixel', '');

INSERT INTO categories (parent_id, name, slug, description, sort_order, is_featured) VALUES
(NULL, 'Industrial & Electrical', 'industrial-electrical', 'Electrical & electronic components, circuit breakers, fuses, motors and sensors.', 1, 1),
(NULL, 'Agri, Food & Dry Fruits', 'agri-food-dry-fruits', 'Dry fruits, nuts, peanuts, processed food and beverages.', 2, 1);

INSERT INTO categories (parent_id, name, slug, description, sort_order) VALUES
(1, 'Circuit Breakers', 'circuit-breakers', 'Industrial circuit breakers and protection devices.', 1),
(1, 'Fuses', 'fuses', 'Industrial fuses and safety components.', 2),
(1, 'Electric Motors', 'electric-motors', 'AC/DC motors and industrial drives.', 3),
(1, 'Sensors', 'sensors', 'Industrial sensors and automation components.', 4),
(1, 'Electrical Components', 'electrical-components', 'Wiring, connectors, switches and more.', 5),
(2, 'Dry Fruits', 'dry-fruits', 'Premium almonds, cashews, walnuts, pistachios and more.', 1),
(2, 'Nuts & Peanuts', 'nuts-peanuts', 'Peanuts and assorted nuts for wholesale supply.', 2),
(2, 'Processed Foods', 'processed-foods', 'Quality processed food products.', 3),
(2, 'Beverages', 'beverages', 'Beverage products for retail and wholesale.', 4);

INSERT INTO tax_classes (name, description) VALUES ('Standard Goods','Default tax class'),('Exempt','Zero-rated goods');
INSERT INTO shipping_zones (name, states) VALUES ('Bathinda Local','Punjab'),('Punjab','Punjab'),('Rest of India',NULL);
INSERT INTO shipping_methods (zone_id, name, type, rate, min_order) VALUES
(1,'Local Delivery','flat',49,NULL),
(2,'Punjab Standard','flat',79,499),
(3,'All India Standard','flat',99,999);

INSERT INTO pages (title, slug, content, status) VALUES
('Privacy Policy','privacy-policy','<p>[Legal review required] Add your Privacy Policy here.</p>',1),
('Terms & Conditions','terms-conditions','<p>[Legal review required] Add your Terms &amp; Conditions here.</p>',1),
('Shipping Policy','shipping-policy','<p>[Legal review required] Add your Shipping Policy here.</p>',1),
('Return & Refund Policy','return-refund-policy','<p>[Legal review required] Add your Return &amp; Refund Policy here.</p>',1),
('Cancellation Policy','cancellation-policy','<p>[Legal review required] Add your Cancellation Policy here.</p>',1),
('Cookie Policy','cookie-policy','<p>[Legal review required] Add your Cookie Policy here.</p>',1);

SET FOREIGN_KEY_CHECKS = 1;
