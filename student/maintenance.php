<?php
require_once __DIR__ . "/../config/auth.php";
require_role("student");
$page_title = "Maintenance";

$student = current_student($conn);
if (!$student) {
    flash_set("danger", "Student profile not found.");
    header("location: dashboard.php");
    exit;
}

$sid = (int) $student["student_id"];
$allocation = mysqli_query($conn, "SELECT room_id FROM room_allocations WHERE student_id = $sid AND status = 'active' LIMIT 1");
$allocation_row = mysqli_fetch_assoc($allocation);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    if (!$allocation_row) {
        flash_set("danger", "You need an assigned room before submitting a request.");
    } elseif ($title && $description) {
        $room_id = (int) $allocation_row["room_id"];
        $stmt = mysqli_prepare($conn, "INSERT INTO maintenance_requests (student_id, room_id, title, description) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiss", $sid, $room_id, $title, $description);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        flash_set("success", "Maintenance request submitted.");
    } else {
        flash_set("danger", "Please complete the form.");
    }
    header("location: maintenance.php");
    exit;
}

$requests = mysqli_query($conn, "SELECT * FROM maintenance_requests WHERE student_id = $sid ORDER BY created_at DESC");
include __DIR__ . "/../includes/header.php";
flash_show();
?>
<h1 class="mb-4">Maintenance Requests</h1>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="content-card">
            <h4>New Request</h4>
            <form method="post" class="mt-3">
                <div class="mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="5" required></textarea></div>
                <button class="btn btn-primary" type="submit">Submit</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="content-card">
            <table class="table">
                <thead><tr><th>Title</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($requests)) { ?>
                    <tr>
                        <td><?php echo e($row["title"]); ?><div class="small text-muted"><?php echo e($row["description"]); ?></div></td>
                        <td><?php echo e($row["status"]); ?></td>
                        <td><?php echo e($row["created_at"]); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>
