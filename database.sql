-- ============================================
-- CAR RENTAL MANAGEMENT SYSTEM - DATABASE SCHEMA
-- ============================================

CREATE DATABASE IF NOT EXISTS car_rental_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE car_rental_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cars Table
CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    license_plate VARCHAR(20) NOT NULL UNIQUE,
    category ENUM('economy', 'sedan', 'suv', 'luxury', 'van', 'sports') NOT NULL DEFAULT 'sedan',
    seats INT NOT NULL DEFAULT 5,
    fuel_type ENUM('petrol', 'diesel', 'electric', 'hybrid') NOT NULL DEFAULT 'petrol',
    transmission ENUM('automatic', 'manual') NOT NULL DEFAULT 'automatic',
    price_per_day DECIMAL(10,2) NOT NULL,
    fine_per_day DECIMAL(10,2) NOT NULL DEFAULT 15.00,
    status ENUM('available', 'rented', 'maintenance') NOT NULL DEFAULT 'available',
    image_url VARCHAR(255) DEFAULT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Rentals Table
CREATE TABLE IF NOT EXISTS rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    rent_date DATE NOT NULL,
    due_date DATE NOT NULL,
    actual_return_date DATE DEFAULT NULL,
    status ENUM('active', 'returned', 'overdue') NOT NULL DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rental_id INT NOT NULL,
    user_id INT NOT NULL,
    base_amount DECIMAL(10,2) NOT NULL,
    fine_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    days_rented INT NOT NULL,
    overdue_days INT NOT NULL DEFAULT 0,
    payment_status ENUM('pending', 'paid', 'refunded') NOT NULL DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_id) REFERENCES rentals(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- SEED DATA
-- ============================================

-- Default Admin User (password: admin123)
INSERT INTO users (name, email, password, phone, role) VALUES
('System Admin', 'admin@carrental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-0100', 'admin');

-- Note: The above password hash is for 'password' - change it!
-- Use this PHP to generate: echo password_hash('admin123', PASSWORD_BCRYPT);

-- Sample Cars
INSERT INTO cars (name, brand, model, year, license_plate, category, seats, fuel_type, transmission, price_per_day, fine_per_day, status, description) VALUES
('Toyota Camry 2023', 'Toyota', 'Camry', 2023, 'ABC-1234', 'sedan', 5, 'petrol', 'automatic', 55.00, 15.00, 'available', 'Comfortable mid-size sedan with excellent fuel economy.'),
('Honda CR-V 2022', 'Honda', 'CR-V', 2022, 'XYZ-5678', 'suv', 5, 'hybrid', 'automatic', 75.00, 20.00, 'available', 'Spacious SUV with hybrid powertrain.'),
('Ford Mustang 2023', 'Ford', 'Mustang', 2023, 'MST-9012', 'sports', 4, 'petrol', 'manual', 120.00, 30.00, 'available', 'Iconic American muscle car.'),
('BMW 3 Series 2022', 'BMW', '3 Series', 2022, 'BMW-3456', 'luxury', 5, 'petrol', 'automatic', 150.00, 35.00, 'available', 'Premium luxury sedan with advanced technology.'),
('Kia Sorento 2023', 'Kia', 'Sorento', 2023, 'KIA-7890', 'suv', 7, 'diesel', 'automatic', 85.00, 22.00, 'available', 'Family-friendly 7-seater SUV.'),
('Tesla Model 3 2023', 'Tesla', 'Model 3', 2023, 'TES-1111', 'sedan', 5, 'electric', 'automatic', 130.00, 30.00, 'available', 'All-electric sedan with cutting-edge autopilot.'),
('Toyota Sienna 2022', 'Toyota', 'Sienna', 2022, 'VAN-2222', 'van', 8, 'hybrid', 'automatic', 95.00, 25.00, 'available', 'Hybrid minivan perfect for family trips.'),
('Hyundai Elantra 2023', 'Hyundai', 'Elantra', 2023, 'HYU-3333', 'economy', 5, 'petrol', 'automatic', 40.00, 12.00, 'available', 'Affordable compact car with great mileage.');

-- Sample Customer
INSERT INTO users (name, email, password, phone, role) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-0200', 'customer');
