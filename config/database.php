<?php
$SQLITE_DB_PATH = dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "hostel_management.sqlite";

if (!extension_loaded("mysqli")) {
    require_once __DIR__ . "/sqlite_mysqli.php";
}

$conn = mysqli_connect("localhost", "root", "", "hostel_management");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

hostel_ensure_schema($conn);

function hostel_ensure_schema($conn)
{
    $check = mysqli_query($conn, "SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
    if ($check && mysqli_num_rows($check) > 0) {
        return;
    }

    mysqli_query($conn, "CREATE TABLE users (
        user_id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'student') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    mysqli_query($conn, "CREATE TABLE hostels (
        hostel_id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        gender ENUM('male', 'female') NOT NULL,
        capacity INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    mysqli_query($conn, "CREATE TABLE students (
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
    )");

    mysqli_query($conn, "CREATE TABLE rooms (
        room_id INT PRIMARY KEY AUTO_INCREMENT,
        hostel_id INT NOT NULL,
        room_letter CHAR(1) NOT NULL,
        capacity INT NOT NULL DEFAULT 2,
        status ENUM('available', 'occupied') NOT NULL DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (hostel_id) REFERENCES hostels(hostel_id),
        UNIQUE KEY unique_room (hostel_id, room_letter)
    )");

    mysqli_query($conn, "CREATE TABLE room_allocations (
        allocation_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        room_id INT NOT NULL,
        check_in_date DATE NOT NULL,
        check_out_date DATE,
        status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(student_id),
        FOREIGN KEY (room_id) REFERENCES rooms(room_id)
    )");

    mysqli_query($conn, "CREATE TABLE maintenance_requests (
        request_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        room_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(student_id),
        FOREIGN KEY (room_id) REFERENCES rooms(room_id)
    )");

    mysqli_query($conn, "CREATE TABLE news_updates (
        news_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )");

    $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $admin_user = "admin";
    $admin_role = "admin";
    mysqli_stmt_bind_param($stmt, "sss", $admin_user, $admin_password, $admin_role);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    hostel_seed_demo_data($conn);
}

function hostel_seed_demo_data($conn)
{
    $hostels = [
        ["North Block", "male", 24],
        ["South Block", "female", 24],
    ];

    foreach ($hostels as $hostel) {
        $stmt = mysqli_prepare($conn, "INSERT INTO hostels (name, gender, capacity) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssi", $hostel[0], $hostel[1], $hostel[2]);
        mysqli_stmt_execute($stmt);
        $hostel_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        foreach (range("A", "L") as $letter) {
            $capacity = 2;
            $status = "available";
            $room_stmt = mysqli_prepare($conn, "INSERT INTO rooms (hostel_id, room_letter, capacity, status) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($room_stmt, "isis", $hostel_id, $letter, $capacity, $status);
            mysqli_stmt_execute($room_stmt);
            mysqli_stmt_close($room_stmt);
        }
    }

    $student_password = password_hash("student123", PASSWORD_DEFAULT);
    $username = "student";
    $role = "student";
    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $username, $student_password, $role);
    mysqli_stmt_execute($stmt);
    $user_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    $first = "Jane";
    $last = "Moyo";
    $email = "jane.moyo@student.local";
    $phone = "0770000000";
    $gender = "female";
    $program = "Computer Science";
    $stmt = mysqli_prepare($conn, "INSERT INTO students (user_id, first_name, last_name, email, phone, gender, program) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssss", $user_id, $first, $last, $email, $phone, $gender, $program);
    mysqli_stmt_execute($stmt);
    $student_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    $room = mysqli_query($conn, "SELECT room_id FROM rooms r JOIN hostels h ON r.hostel_id = h.hostel_id WHERE h.gender = 'female' ORDER BY r.room_id LIMIT 1");
    $room_row = mysqli_fetch_assoc($room);
    if ($room_row) {
        $room_id = (int) $room_row["room_id"];
        $today = date("Y-m-d");
        $status = "active";
        $stmt = mysqli_prepare($conn, "INSERT INTO room_allocations (student_id, room_id, check_in_date, status) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiss", $student_id, $room_id, $today, $status);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        hostel_refresh_room_status($conn, $room_id);
    }

    $admin = mysqli_query($conn, "SELECT user_id FROM users WHERE username = 'admin' LIMIT 1");
    $admin_row = mysqli_fetch_assoc($admin);
    if ($admin_row) {
        $admin_id = (int) $admin_row["user_id"];
        $title = "Welcome to Madz Hostel";
        $content = "Use this portal to manage rooms, students, maintenance requests, and campus news.";
        $stmt = mysqli_prepare($conn, "INSERT INTO news_updates (user_id, title, content) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iss", $admin_id, $title, $content);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function hostel_refresh_room_status($conn, $room_id)
{
    $room_id = (int) $room_id;
    $room = mysqli_query($conn, "SELECT capacity FROM rooms WHERE room_id = $room_id");
    $room_row = mysqli_fetch_assoc($room);
    if (!$room_row) {
        return;
    }
    $occupied = mysqli_query($conn, "SELECT COUNT(*) AS total FROM room_allocations WHERE room_id = $room_id AND status = 'active'");
    $occupied_row = mysqli_fetch_assoc($occupied);
    $status = ((int) $occupied_row["total"] >= (int) $room_row["capacity"]) ? "occupied" : "available";
    $stmt = mysqli_prepare($conn, "UPDATE rooms SET status = ? WHERE room_id = ?");
    mysqli_stmt_bind_param($stmt, "si", $status, $room_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
