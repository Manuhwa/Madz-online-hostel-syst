<?php
require_once __DIR__ . "/../config/auth.php";
require_role("admin");
$page_title = "Admin Dashboard";

$counts = [
    "hostels" => 0,
    "students" => 0,
    "rooms" => 0,
    "allocations" => 0,
    "maintenance" => 0,
];
$queries = [
    "hostels" => "SELECT COUNT(*) AS total FROM hostels",
    "students" => "SELECT COUNT(*) AS total FROM students",
    "rooms" => "SELECT COUNT(*) AS total FROM rooms",
    "allocations" => "SELECT COUNT(*) AS total FROM room_allocations WHERE status = 'active'",
    "maintenance" => "SELECT COUNT(*) AS total FROM maintenance_requests WHERE status = 'pending'",
];
foreach ($queries as $key => $sql) {
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $counts[$key] = (int) ($row["total"] ?? 0);
}

$recent_maintenance = mysqli_query($conn, "SELECT m.title, m.status, m.created_at, s.first_name, s.last_name
    FROM maintenance_requests m
    JOIN students s ON m.student_id = s.student_id
    ORDER BY m.created_at DESC LIMIT 5");

include __DIR__ . "/../includes/header.php";
?>
<h1 class="mb-4">Admin Dashboard</h1>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card"><div class="text-muted">Hostels</div><h2><?php echo $counts["hostels"]; ?></h2></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="text-muted">Students</div><h2><?php echo $counts["students"]; ?></h2></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="text-muted">Active Allocations</div><h2><?php echo $counts["allocations"]; ?></h2></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="text-muted">Pending Maintenance</div><h2><?php echo $counts["maintenance"]; ?></h2></div></div>
</div>

<div class="content-card">
    <h4>Recent Maintenance Requests</h4>
    <table class="table table-striped mt-3">
        <thead><tr><th>Student</th><th>Title</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($recent_maintenance)) { ?>
            <tr>
                <td><?php echo e($row["first_name"] . " " . $row["last_name"]); ?></td>
                <td><?php echo e($row["title"]); ?></td>
                <td><?php echo e($row["status"]); ?></td>
                <td><?php echo e($row["created_at"]); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>
