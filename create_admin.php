<?php
require_once "config/database.php";

// Admin credentials
$username = "admin";
$password = "admin123"; // This will be hashed
$email = "admin@hostel.com";
$role = "admin";

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connection successful.<br>";

// First, let's check if the users table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($table_check) == 0) {
    die("Error: The 'users' table does not exist. Please run setup_database.php first.<br>");
}

echo "Users table exists.<br>";

// Check if admin already exists
$check_sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);

if (!$check_stmt) {
    die("Error preparing check statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($check_stmt, "s", $username);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    echo "Admin account already exists!<br>";
    echo "Username: " . $username . "<br>";
    echo "Password: " . $password . "<br>";
    
    // Show the existing admin account details
    mysqli_stmt_bind_result($check_stmt, $existing_id, $existing_username, $existing_password, $existing_role);
    mysqli_stmt_fetch($check_stmt);
    echo "<br>Existing admin details:<br>";
    echo "ID: " . $existing_id . "<br>";
    echo "Username: " . $existing_username . "<br>";
    echo "Role: " . $existing_role . "<br>";
} else {
    // Insert admin user
    $sql = "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed_password, $email, $role);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "Admin account created successfully!<br>";
            echo "Username: " . $username . "<br>";
            echo "Password: " . $password . "<br>";
            echo "Email: " . $email . "<br>";
            echo "Role: " . $role . "<br>";
            echo "<br>Please delete this file after creating the admin account for security reasons.";
        } else {
            echo "Error creating admin account: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing insert statement: " . mysqli_error($conn);
    }
}

// Show the users table structure
echo "<br><br>Users table structure:<br>";
$result = mysqli_query($conn, "DESCRIBE users");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
}

// Show all users in the database
echo "<br>Current users in database:<br>";
$result = mysqli_query($conn, "SELECT user_id, username, email, role FROM users");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: " . $row['user_id'] . ", Username: " . $row['username'] . 
             ", Email: " . $row['email'] . ", Role: " . $row['role'] . "<br>";
    }
}

mysqli_close($conn);
?> 