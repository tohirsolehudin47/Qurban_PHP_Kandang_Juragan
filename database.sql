-- Database: qurban_reseller_affiliate

CREATE DATABASE IF NOT EXISTS qurban_reseller_affiliate;
USE qurban_reseller_affiliate;

-- Table: users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'reseller') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dummy users
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$e0NRzQ1v1Q6vQ6vQ6vQ6vOq1vQ6vQ6vQ6vQ6vQ6vQ6vQ6vQ6vQ6vQ6', 'admin'), -- password: admin123
('Reseller One', 'reseller1@example.com', '$2y$10$e0NRzQ1v1Q6vQ6vQ6vQ6vOq1vQ6vQ6vQ6vQ6vQ6vQ6vQ6vQ6vQ6vQ6', 'reseller'); -- password: reseller123

-- Table: products
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  category ENUM('Sapi', 'Kambing', 'Domba') NOT NULL,
  description TEXT,
  price DECIMAL(12,2) NOT NULL,
  image_url TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dummy products
INSERT INTO products (name, category, description, price, image_url) VALUES
('Sapi Qurban Premium', 'Sapi', 'Sapi qurban berkualitas premium.', 15000000.00, 'https://app.nusaqu.id/assets/img/sapi1.png'),
('Kambing Qurban Standard', 'Kambing', 'Kambing qurban dengan harga terjangkau.', 2500000.00, 'https://app.nusaqu.id/assets/img/kambing1.png'),
('Domba Qurban Pilihan', 'Domba', 'Domba qurban pilihan terbaik.', 1800000.00, 'https://app.nusaqu.id/assets/img/domba1.png');

-- Table: orders
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  reseller_id INT NOT NULL,
  buyer_name VARCHAR(100) NOT NULL,
  buyer_phone VARCHAR(20) NOT NULL,
  order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (reseller_id) REFERENCES users(id)
);

-- Dummy orders
INSERT INTO orders (product_id, reseller_id, buyer_name, buyer_phone) VALUES
(1, 2, 'Customer A', '081234567890'),
(2, 2, 'Customer B', '082345678901');

-- Table: commissions
CREATE TABLE commissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reseller_id INT NOT NULL,
  order_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reseller_id) REFERENCES users(id),
  FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Dummy commissions
INSERT INTO commissions (reseller_id, order_id, amount) VALUES
(2, 1, 1500000.00),
(2, 2, 250000.00);

-- Table: clicks
CREATE TABLE clicks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reseller_id INT NOT NULL,
  product_id INT NOT NULL,
  ip_address VARCHAR(45),
  click_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reseller_id) REFERENCES users(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Dummy clicks
INSERT INTO clicks (reseller_id, product_id, ip_address) VALUES
(2, 1, '192.168.1.10'),
(2, 2, '192.168.1.11');

-- Table: marketing_kit
CREATE TABLE marketing_kit (
  id INT AUTO_INCREMENT PRIMARY KEY,
  file_name VARCHAR(255) NOT NULL,
  file_url TEXT NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dummy marketing kit
INSERT INTO marketing_kit (file_name, file_url) VALUES
('Ebook Qurban.pdf', 'https://example.com/marketing/ebook_qurban.pdf'),
('Video Marketing.mp4', 'https://example.com/marketing/video_marketing.mp4');
