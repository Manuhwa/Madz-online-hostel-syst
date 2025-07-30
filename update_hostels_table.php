<?php
require_once "config/database.php";

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Add new columns if they don't exist
    $alter_queries = [
        "ALTER TABLE hostels ADD COLUMN IF NOT EXISTS description TEXT AFTER address",
        "ALTER TABLE hostels ADD COLUMN IF NOT EXISTS room_capacity INT AFTER total_rooms",
        "ALTER TABLE hostels ADD COLUMN IF NOT EXISTS gender ENUM('male', 'female') AFTER room_capacity"
    ];

    foreach ($alter_queries as $query) {
        if (!mysqli_query($conn, $query)) {
            throw new Exception("Error executing query: " . $query);
        }
    }

    // Commit transaction
    mysqli_commit($conn);
    echo "Hostels table updated successfully!";
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    echo "Error updating hostels table: " . $e->getMessage();
}
?> 