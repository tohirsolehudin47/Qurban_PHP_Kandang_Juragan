-- Create database with proper character set
CREATE DATABASE IF NOT EXISTS qurban_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qurban_app;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    role ENUM('admin', 'reseller') NOT NULL DEFAULT 'reseller',
    profile_image VARCHAR(255) NULL,
    is_active BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Reseller Profiles Table
CREATE TABLE reseller_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    address TEXT NULL,
    city VARCHAR(50) NULL,
    province VARCHAR(50) NULL,
    postal_code VARCHAR(10) NULL,
    id_card_number VARCHAR(20) NULL,
    id_card_image VARCHAR(255) NULL,
    sales_target ENUM('low', 'medium', 'high') DEFAULT 'medium',
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reseller Banks Table
CREATE TABLE reseller_banks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bank_name VARCHAR(50) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    account_holder VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Product Categories Table
CREATE TABLE product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT NULL,
    weight DECIMAL(10,2) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    stock INT NOT NULL DEFAULT 1,
    age INT NULL,
    location VARCHAR(100) NULL,
    image1 VARCHAR(255) NULL,
    image2 VARCHAR(255) NULL,
    image3 VARCHAR(255) NULL,
    video_url VARCHAR(255) NULL,
    is_available BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
);

-- Customers Table
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NULL,
    reseller_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reseller_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    reseller_id INT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    deposit_amount DECIMAL(12,2) NOT NULL,
    remaining_amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'delivered', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    is_qurban_saving BOOLEAN NOT NULL DEFAULT 0,
    payment_proof VARCHAR(255) NULL,
    delivery_date DATE NULL,
    delivery_address TEXT NULL,
    delivery_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (reseller_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Commission Rates Table
CREATE TABLE commission_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    min_weight DECIMAL(10,2) NOT NULL,
    max_weight DECIMAL(10,2) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
);

-- Commissions Table
CREATE TABLE commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    reseller_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'paid') NOT NULL DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    payment_proof VARCHAR(255) NULL,
    payment_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (reseller_id) REFERENCES users(id)
);

-- Affiliate Links Table
CREATE TABLE affiliate_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reseller_id INT NOT NULL,
    link_type ENUM('qurban', 'savings', 'catalog') NOT NULL,
    unique_code VARCHAR(50) NOT NULL UNIQUE,
    full_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reseller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Link Clicks Table
CREATE TABLE link_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    affiliate_link_id INT NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    referrer VARCHAR(255) NULL,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (affiliate_link_id) REFERENCES affiliate_links(id) ON DELETE CASCADE
);

-- Marketing Materials Table
CREATE TABLE marketing_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NULL,
    type ENUM('ebook', 'video', 'image', 'document') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255) NULL,
    is_public BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Savings Plans Table
CREATE TABLE savings_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    target_amount DECIMAL(12,2) NOT NULL,
    min_deposit DECIMAL(12,2) NOT NULL,
    duration_months INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Savings Transactions Table
CREATE TABLE savings_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    reseller_id INT NULL,
    plan_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_proof VARCHAR(255) NULL,
    status ENUM('pending', 'confirmed', 'rejected') NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (reseller_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (plan_id) REFERENCES savings_plans(id)
);

-- WhatsApp Templates Table
CREATE TABLE whatsapp_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    variables TEXT NULL COMMENT 'JSON array of variable placeholders',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- WhatsApp Tracking Table
CREATE TABLE whatsapp_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reseller_id INT NULL,
    customer_id INT NULL,
    type ENUM('catalog', 'product', 'inquiry', 'order') NOT NULL,
    reference_id INT NULL COMMENT 'Related ID based on type',
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reseller_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- Insert initial data

-- Insert admin account
INSERT INTO users (username, email, password, phone, role, is_active) 
VALUES ('admin', 'admin@qurbanapp.com', '$2y$10$YCIgGOFxJxJ.EEWHXzIK1O8t.8KQ2DRpDUUC5YCpfEH1CX5UVUuHa', '081234567890', 'admin', 1);

-- Insert product categories
INSERT INTO product_categories (name, description) VALUES 
('Sapi', 'Kategori untuk hewan qurban sapi'),
('Kambing', 'Kategori untuk hewan qurban kambing'),
('Domba', 'Kategori untuk hewan qurban domba');

-- Insert commission rates
INSERT INTO commission_rates (category_id, min_weight, max_weight, amount) VALUES 
(1, 0, 500, 500000),
(1, 501, 750, 750000),
(1, 751, 9999, 1500000),
(2, 0, 9999, 150000),
(3, 0, 9999, 150000);

-- Insert savings plans
INSERT INTO savings_plans (name, description, target_amount, min_deposit, duration_months) VALUES
('Tabungan Sapi Ekonomis', 'Tabungan untuk sapi qurban kelas ekonomis', 15000000, 500000, 12),
('Tabungan Sapi Premium', 'Tabungan untuk sapi qurban kelas premium', 25000000, 1000000, 12),
('Tabungan Kambing', 'Tabungan untuk kambing qurban', 3000000, 250000, 12);

-- Insert WhatsApp templates
INSERT INTO whatsapp_templates (name, content, variables) VALUES
('Katalog Produk', 'Assalamualaikum Wr. Wb.\n\nTerima kasih telah mengklik link katalog produk kami.\n\nUntuk melihat detail produk qurban kami, silahkan kunjungi: {{catalog_link}}\n\nUntuk konsultasi dan pemesanan, silahkan hubungi kami.\n\nWassalamualaikum Wr. Wb.', '["catalog_link"]'),
('Konfirmasi Order', 'Assalamualaikum Wr. Wb.\n\nTerima kasih atas pesanan Anda.\n\nDetail Pesanan:\nKode: {{order_code}}\nTotal: {{total_amount}}\nDP: {{deposit_amount}}\nSisa: {{remaining_amount}}\n\nSilakan lakukan pembayaran DP ke rekening berikut:\nBank: BCA\nNo. Rek: 1234567890\nAtas Nama: PT Qurban App\n\nWassalamualaikum Wr. Wb.', '["order_code", "total_amount", "deposit_amount", "remaining_amount"]');

-- Insert marketing materials
INSERT INTO marketing_materials (title, description, type, file_path, thumbnail, is_public) VALUES
('Panduan Reseller Qurban', 'Ebook panduan lengkap menjadi reseller qurban sukses', 'ebook', 'marketing_kit/ebooks/panduan_reseller.pdf', 'marketing_kit/thumbnails/panduan_reseller.jpg', 1),
('Video Testimoni Peternak', 'Video testimoni dari peternak sapi qurban', 'video', 'marketing_kit/videos/testimoni_peternak.mp4', 'marketing_kit/thumbnails/testimoni_peternak.jpg', 1),
('Brosur Qurban Digital', 'Brosur digital untuk promosi produk qurban', 'image', 'marketing_kit/images/brosur_qurban.jpg', 'marketing_kit/thumbnails/brosur_qurban.jpg', 1);

-- Insert sample products
INSERT INTO products (category_id, name, code, description, weight, price, stock, age, location, image1, is_available) VALUES
(1, 'Sapi Limosin A', 'SAP001', 'Sapi Limosin kelas A dengan bobot 450 kg', 450, 25000000, 1, 24, 'Kandang Bogor', 'uploads/animals/sapi_limosin_a.jpg', 1),
(1, 'Sapi Limosin B', 'SAP002', 'Sapi Limosin kelas B dengan bobot 550 kg', 550, 32000000, 1, 30, 'Kandang Bogor', 'uploads/animals/sapi_limosin_b.jpg', 1),
(1, 'Sapi Brahman A', 'SAP003', 'Sapi Brahman kelas A dengan bobot 650 kg', 650, 40000000, 1, 36, 'Kandang Depok', 'uploads/animals/sapi_brahman_a.jpg', 1),
(2, 'Kambing Etawa A', 'KAM001', 'Kambing Etawa kelas A dengan bobot 35 kg', 35, 3500000, 1, 12, 'Kandang Bogor', 'uploads/animals/kambing_etawa_a.jpg', 1),
(2, 'Kambing Etawa B', 'KAM002', 'Kambing Etawa kelas B dengan bobot 40 kg', 40, 4000000, 1, 15, 'Kandang Bogor', 'uploads/animals/kambing_etawa_b.jpg', 1),
(3, 'Domba Garut A', 'DOM001', 'Domba Garut kelas A dengan bobot 30 kg', 30, 3000000, 1, 10, 'Kandang Depok', 'uploads/animals/domba_garut_a.jpg', 1);
