<?php
require_once __DIR__ . "/../config/auth.php";
require_role("admin");
$page_title = "Rooms";

$rooms = mysqli_query($conn, "SELECT r.*, h.name AS hostel_name, h.gender,
    (SELECT COUNT(*) FROM room_allocations a WHERE a.room_id = r.room_id AND a.status = 'active') AS occupants
    FROM rooms r JOIN hostels h ON r.hostel_id = h.hostel_id
    ORDER BY h.name, r.room_letter");

include __DIR__ . "/../includes/header.php";
?>
<h1 class="mb-4">Rooms</h1>
<div class="content-card">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Hostel</th>
                <th>Room</th>
                <th>Gender</th>
                <th>Occupants</th>
                <th>Capacity</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($rooms)) { ?>
            <tr>
                <td><?php echo e($row["hostel_name"]); ?></td>
                <td><?php echo e($row["room_letter"]); ?></td>
                <td><?php echo e($row["gender"]); ?></td>
                <td><?php echo e($row["occupants"]); ?></td>
                <td><?php echo e($row["capacity"]); ?></td>
                <td><?php echo e($row["status"]); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>
