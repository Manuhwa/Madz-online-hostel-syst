<?php
require_once __DIR__ . "/../config/auth.php";
require_role("admin");
$page_title = "Maintenance Requests";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $request_id = (int) ($_POST["request_id"] ?? 0);
    $status = $_POST["status"] ?? "pending";
    $allowed = ["pending", "in_progress", "completed", "cancelled"];
    if (in_array($status, $allowed, true)) {
        $stmt = mysqli_prepare($conn, "UPDATE maintenance_requests SET status = ? WHERE request_id = ?");
        mysqli_stmt_bind_param($stmt, "si", $status, $request_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        flash_set("success", "Request updated.");
    }
    header("location: maintenance.php");
    exit;
}

$requests = mysqli_query($conn, "SELECT m.*, s.first_name, s.last_name, r.room_letter, h.name AS hostel_name
    FROM maintenance_requests m
    JOIN students s ON m.student_id = s.student_id
    JOIN rooms r ON m.room_id = r.room_id
    JOIN hostels h ON r.hostel_id = h.hostel_id
    ORDER BY m.created_at DESC");

include __DIR__ . "/../includes/header.php";
flash_show();
?>
<h1 class="mb-4">Maintenance Requests</h1>
<div class="content-card">
    <table class="table table-striped">
        <thead><tr><th>Student</th><th>Room</th><th>Title</th><th>Description</th><th>Status</th><th>Update</th></tr></thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($requests)) { ?>
            <tr>
                <td><?php echo e($row["first_name"] . " " . $row["last_name"]); ?></td>
                <td><?php echo e($row["hostel_name"] . " " . $row["room_letter"]); ?></td>
                <td><?php echo e($row["title"]); ?></td>
                <td><?php echo e($row["description"]); ?></td>
                <td><?php echo e($row["status"]); ?></td>
                <td>
                    <form method="post" class="d-flex gap-2">
                        <input type="hidden" name="request_id" value="<?php echo (int) $row["request_id"]; ?>">
                        <select name="status" class="form-select form-select-sm">
                            <?php foreach (["pending", "in_progress", "completed", "cancelled"] as $status) { ?>
                                <option value="<?php echo $status; ?>" <?php echo $row["status"] === $status ? "selected" : ""; ?>><?php echo $status; ?></option>
                            <?php } ?>
                        </select>
                        <button class="btn btn-sm btn-primary">Save</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>
