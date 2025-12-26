*//kod dla phpMyAdmin
CREATE DATABASE IF NOT EXISTS users_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE users_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,

    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,

    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    date_of_birth DATE,

    country VARCHAR(50),
    city VARCHAR(50),
    address VARCHAR(255),
    postal_code VARCHAR(20),

    role ENUM('user','admin') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO users (
    username,
    email,
    password,
    first_name,
    last_name,
    phone,
    city,
    role
) VALUES (
    'admin',
    'admin@mail.com',
    '$2y$10$examplehashedpassword',
    'Администратор',
    'Системы',
    '+79990000000',
    'Москва',
    'admin'
);
