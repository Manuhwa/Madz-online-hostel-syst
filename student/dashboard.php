<?php
require_once __DIR__ . "/../config/auth.php";
require_role("student");
$page_title = "Student Dashboard";

$student = current_student($conn);
$allocation = null;
if ($student) {
    $sid = (int) $student["student_id"];
    $result = mysqli_query($conn, "SELECT a.check_in_date, r.room_letter, h.name AS hostel_name
        FROM room_allocations a
        JOIN rooms r ON a.room_id = r.room_id
        JOIN hostels h ON r.hostel_id = h.hostel_id
        WHERE a.student_id = $sid AND a.status = 'active' LIMIT 1");
    $allocation = mysqli_fetch_assoc($result);
}

$news = mysqli_query($conn, "SELECT title, content, created_at FROM news_updates ORDER BY created_at DESC LIMIT 3");
include __DIR__ . "/../includes/header.php";
?>
<h1 class="mb-4">Welcome, <?php echo e($student["first_name"] ?? $_SESSION["username"]); ?></h1>
<div class="row g-4">
    <div class="col-md-6">
        <div class="content-card">
            <h4>Room Allocation</h4>
            <?php if ($allocation) { ?>
                <p class="mb-1"><strong>Hostel:</strong> <?php echo e($allocation["hostel_name"]); ?></p>
                <p class="mb-1"><strong>Room:</strong> <?php echo e($allocation["room_letter"]); ?></p>
                <p class="mb-0"><strong>Check-in:</strong> <?php echo e($allocation["check_in_date"]); ?></p>
            <?php } else { ?>
                <p class="mb-0 text-muted">No active room has been assigned yet.</p>
            <?php } ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="content-card">
            <h4>Latest News</h4>
            <?php while ($row = mysqli_fetch_assoc($news)) { ?>
                <div class="mb-3">
                    <strong><?php echo e($row["title"]); ?></strong>
                    <div class="text-muted small"><?php echo e($row["created_at"]); ?></div>
                    <p class="mb-0"><?php echo e($row["content"]); ?></p>
                </div>
            <?php } ?>
            <a href="news.php">View all news</a>
        </div>
    </div>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>
