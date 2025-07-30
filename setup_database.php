<?php
require_once "config/database.php";

// Disable foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

// Drop existing tables
$tables = [
    'news_updates',
    'maintenance_requests',
    'room_allocations',
    'rooms',
    'students',
    'hostels',
    'users'
];

foreach ($tables as $table) {
    mysqli_query($conn, "DROP TABLE IF EXISTS $table");
}

// Re-enable foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

// Create users table
$sql = "CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql);

// Create hostels table
$sql = "CREATE TABLE hostels (
    hostel_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    capacity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql);

// Create students table
$sql = "CREATE TABLE students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    gender ENUM('male', 'female') NOT NULL,
    program VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";
mysqli_query($conn, $sql);

// Create rooms table with fixed 12 rooms (A-L)
$sql = "CREATE TABLE rooms (
    room_id INT PRIMARY KEY AUTO_INCREMENT,
    hostel_id INT NOT NULL,
    room_letter CHAR(1) NOT NULL,
    capacity INT NOT NULL DEFAULT 2,
    status ENUM('available', 'occupied') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hostel_id) REFERENCES hostels(hostel_id),
    UNIQUE KEY unique_room (hostel_id, room_letter)
)";
mysqli_query($conn, $sql);

// Create room_allocations table
$sql = "CREATE TABLE room_allocations (
    allocation_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE,
    status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
)";
mysqli_query($conn, $sql);

// Create maintenance_requests table
$sql = "CREATE TABLE maintenance_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    room_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
)";
mysqli_query($conn, $sql);

// Create news_updates table
$sql = "CREATE TABLE news_updates (
    news_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";
mysqli_query($conn, $sql);

// Insert default admin user
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, password, role) VALUES ('admin', '$admin_password', 'admin')";
mysqli_query($conn, $sql);

echo "Database setup completed successfully!";
?> 