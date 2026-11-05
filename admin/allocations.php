<?php
require_once __DIR__ . "/../config/auth.php";
require_role("admin");
$page_title = "Room Allocations";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    if ($action === "allocate") {
        $student_id = (int) ($_POST["student_id"] ?? 0);
        $room_id = (int) ($_POST["room_id"] ?? 0);
        $today = date("Y-m-d");
        $status = "active";
        $existing = mysqli_query($conn, "SELECT allocation_id FROM room_allocations WHERE student_id = $student_id AND status = 'active'");
        if (mysqli_num_rows($existing) > 0) {
            flash_set("danger", "That student already has an active room.");
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO room_allocations (student_id, room_id, check_in_date, status) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iiss", $student_id, $room_id, $today, $status);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            hostel_refresh_room_status($conn, $room_id);
            flash_set("success", "Room allocated.");
        }
    } elseif ($action === "complete") {
        $allocation_id = (int) ($_POST["allocation_id"] ?? 0);
        $room_res = mysqli_query($conn, "SELECT room_id FROM room_allocations WHERE allocation_id = $allocation_id");
        $room_row = mysqli_fetch_assoc($room_res);
        $today = date("Y-m-d");
        $stmt = mysqli_prepare($conn, "UPDATE room_allocations SET status = 'completed', check_out_date = ? WHERE allocation_id = ?");
        mysqli_stmt_bind_param($stmt, "si", $today, $allocation_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($room_row) {
            hostel_refresh_room_status($conn, (int) $room_row["room_id"]);
        }
        flash_set("success", "Allocation closed.");
    }
    header("location: allocations.php");
    exit;
}

$students = mysqli_query($conn, "SELECT s.student_id, s.first_name, s.last_name FROM students s
    WHERE s.student_id NOT IN (SELECT student_id FROM room_allocations WHERE status = 'active')
    ORDER BY s.first_name");
$rooms = mysqli_query($conn, "SELECT r.room_id, r.room_letter, r.capacity, h.name AS hostel_name,
    (SELECT COUNT(*) FROM room_allocations a WHERE a.room_id = r.room_id AND a.status = 'active') AS occupants
    FROM rooms r JOIN hostels h ON r.hostel_id = h.hostel_id
    WHERE r.status = 'available'
    ORDER BY h.name, r.room_letter");
$allocations = mysqli_query($conn, "SELECT a.*, s.first_name, s.last_name, r.room_letter, h.name AS hostel_name
    FROM room_allocations a
    JOIN students s ON a.student_id = s.student_id
    JOIN rooms r ON a.room_id = r.room_id
    JOIN hostels h ON r.hostel_id = h.hostel_id
    WHERE a.status = 'active'
    ORDER BY h.name, r.room_letter");

include __DIR__ . "/../includes/header.php";
flash_show();
?>
<h1 class="mb-4">Room Allocations</h1>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="content-card">
            <h4>Assign Room</h4>
            <form method="post" class="mt-3">
                <input type="hidden" name="action" value="allocate">
                <div class="mb-3">
                    <label class="form-label">Student</label>
                    <select name="student_id" class="form-select" required>
                        <option value="">Select student</option>
                        <?php while ($row = mysqli_fetch_assoc($students)) { ?>
                            <option value="<?php echo (int) $row["student_id"]; ?>"><?php echo e($row["first_name"] . " " . $row["last_name"]); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Room</label>
                    <select name="room_id" class="form-select" required>
                        <option value="">Select room</option>
                        <?php while ($row = mysqli_fetch_assoc($rooms)) { ?>
                            <option value="<?php echo (int) $row["room_id"]; ?>">
                                <?php echo e($row["hostel_name"] . " - " . $row["room_letter"] . " (" . $row["occupants"] . "/" . $row["capacity"] . ")"); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Allocate</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="content-card">
            <table class="table table-striped">
                <thead><tr><th>Student</th><th>Hostel</th><th>Room</th><th>Check-in</th><th></th></tr></thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($allocations)) { ?>
                    <tr>
                        <td><?php echo e($row["first_name"] . " " . $row["last_name"]); ?></td>
                        <td><?php echo e($row["hostel_name"]); ?></td>
                        <td><?php echo e($row["room_letter"]); ?></td>
                        <td><?php echo e($row["check_in_date"]); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="action" value="complete">
                                <input type="hidden" name="allocation_id" value="<?php echo (int) $row["allocation_id"]; ?>">
                                <button class="btn btn-sm btn-outline-secondary">Check out</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>
