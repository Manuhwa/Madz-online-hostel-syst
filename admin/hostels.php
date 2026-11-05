<?php
require_once __DIR__ . "/../config/auth.php";
require_role("admin");
$page_title = "Hostels";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    if ($action === "create") {
        $name = trim($_POST["name"] ?? "");
        $gender = $_POST["gender"] === "female" ? "female" : "male";
        $capacity = 24;
        if ($name !== "") {
            $stmt = mysqli_prepare($conn, "INSERT INTO hostels (name, gender, capacity) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssi", $name, $gender, $capacity);
            mysqli_stmt_execute($stmt);
            $hostel_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            foreach (range("A", "L") as $letter) {
                $room_capacity = 2;
                $status = "available";
                $room_stmt = mysqli_prepare($conn, "INSERT INTO rooms (hostel_id, room_letter, capacity, status) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($room_stmt, "isis", $hostel_id, $letter, $room_capacity, $status);
                mysqli_stmt_execute($room_stmt);
                mysqli_stmt_close($room_stmt);
            }
            flash_set("success", "Hostel created with rooms A-L.");
        }
    } elseif ($action === "delete") {
        $hostel_id = (int) ($_POST["hostel_id"] ?? 0);
        mysqli_query($conn, "DELETE FROM rooms WHERE hostel_id = $hostel_id");
        $stmt = mysqli_prepare($conn, "DELETE FROM hostels WHERE hostel_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $hostel_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        flash_set("success", "Hostel deleted.");
    }
    header("location: hostels.php");
    exit;
}

$hostels = mysqli_query($conn, "SELECT * FROM hostels ORDER BY name");
include __DIR__ . "/../includes/header.php";
flash_show();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Hostels</h1>
</div>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="content-card">
            <h4>Add Hostel</h4>
            <form method="post" class="mt-3">
                <input type="hidden" name="action" value="create">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Save hostel</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="content-card">
            <table class="table">
                <thead><tr><th>Name</th><th>Gender</th><th>Capacity</th><th></th></tr></thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($hostels)) { ?>
                    <tr>
                        <td><?php echo e($row["name"]); ?></td>
                        <td><?php echo e($row["gender"]); ?></td>
                        <td><?php echo e($row["capacity"]); ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Delete this hostel?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="hostel_id" value="<?php echo (int) $row["hostel_id"]; ?>">
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
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
