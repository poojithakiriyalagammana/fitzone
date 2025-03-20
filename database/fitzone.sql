-- Create database
CREATE DATABASE fitzone;
USE fitzone;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    user_type ENUM('customer', 'staff', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Membership plans table
CREATE TABLE membership_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL
);

-- Classes table
CREATE TABLE classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    schedule VARCHAR(100) NOT NULL,
    trainer VARCHAR(100) NOT NULL
);

-- Queries table
CREATE TABLE queries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'answered') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Bookings table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    class_id INT,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

-- Insert some sample data
INSERT INTO membership_plans (name, description, price, duration) VALUES
('Basic', 'Access to gym facilities during off-peak hours', 29.99, 30),
('Standard', 'Full access to gym facilities and one free class per week', 49.99, 30),
('Premium', 'Full access to gym facilities, unlimited classes, and one personal training session per month', 79.99, 30);

INSERT INTO classes (name, description, schedule, trainer) VALUES
('Cardio Blast', 'High-intensity cardio workout to burn calories', 'Monday, Wednesday, Friday - 6:00 PM', 'John Smith'),
('Yoga Flow', 'Relaxing yoga session for flexibility and mindfulness', 'Tuesday, Thursday - 7:00 AM', 'Sarah Johnson'),
('Strength Training', 'Build muscle and improve strength', 'Monday, Wednesday, Friday - 8:00 AM', 'Mike Wilson');