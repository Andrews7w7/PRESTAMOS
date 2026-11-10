CREATE DATABASE IF NOT EXISTS proveeduria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE proveeduria;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    can_deliver TINYINT(1) NOT NULL DEFAULT 1,
    can_view_history TINYINT(1) NOT NULL DEFAULT 1,
    can_manage_catalog TINYINT(1) NOT NULL DEFAULT 0,
    can_manage_users TINYINT(1) NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    national_id VARCHAR(30) NOT NULL UNIQUE,
    department VARCHAR(100) NOT NULL,
    branch VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    sku VARCHAR(50) NOT NULL UNIQUE,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE deliveries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    delivered_by_user_id INT UNSIGNED NULL,
    delivery_branch VARCHAR(100) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    notes VARCHAR(500) NULL,
    delivered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_deliveries_employee FOREIGN KEY (employee_id) REFERENCES employees(id),
    CONSTRAINT fk_deliveries_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_deliveries_user FOREIGN KEY (delivered_by_user_id) REFERENCES users(id),
    INDEX idx_deliveries_date (delivered_at),
    INDEX idx_deliveries_employee (employee_id),
    INDEX idx_deliveries_product (product_id),
    INDEX idx_deliveries_user (delivered_by_user_id)
) ENGINE=InnoDB;

INSERT INTO users (username, password_hash, full_name) VALUES
('admin', '$2y$10$mDxY0QqYqzzGBQAbsRaAOOawy2d4hpvD4XDWdTlihInQG.XNVsgom', 'Administrador Titanio', 'superadmin', 1, 1, 1, 1);
