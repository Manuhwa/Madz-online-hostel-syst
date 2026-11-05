<?php
require_once __DIR__ . "/../config/auth.php";
require_role("admin");
$page_title = "Students";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $gender = $_POST["gender"] === "female" ? "female" : "male";
    $program = trim($_POST["program"] ?? "");

    if ($username && $password && $first_name && $last_name && $email && $program) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = "student";
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $username, $hashed, $role);
        if (mysqli_stmt_execute($stmt)) {
            $user_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($conn, "INSERT INTO students (user_id, first_name, last_name, email, phone, gender, program) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "issssss", $user_id, $first_name, $last_name, $email, $phone, $gender, $program);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            flash_set("success", "Student account created.");
        } else {
            flash_set("danger", "Could not create student. Username may already exist.");
        }
    } else {
        flash_set("danger", "Please fill in all required fields.");
    }
    header("location: students.php");
    exit;
}

$students = mysqli_query($conn, "SELECT s.*, u.username,
    (SELECT r.room_letter || ' - ' || h.name FROM room_allocations a
     JOIN rooms r ON a.room_id = r.room_id
     JOIN hostels h ON r.hostel_id = h.hostel_id
     WHERE a.student_id = s.student_id AND a.status = 'active' LIMIT 1) AS room_info
    FROM students s JOIN users u ON s.user_id = u.user_id ORDER BY s.created_at DESC");

include __DIR__ . "/../includes/header.php";
flash_show();
?>
<h1 class="mb-4">Students</h1>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="content-card">
            <h4>Add Student</h4>
            <form method="post" class="mt-3">
                <div class="mb-2"><label class="form-label">Username</label><input class="form-control" name="username" required></div>
                <div class="mb-2"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required></div>
                <div class="mb-2"><label class="form-label">First name</label><input class="form-control" name="first_name" required></div>
                <div class="mb-2"><label class="form-label">Last name</label><input class="form-control" name="last_name" required></div>
                <div class="mb-2"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                <div class="mb-2"><label class="form-label">Phone</label><input class="form-control" name="phone"></div>
                <div class="mb-2">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Program</label><input class="form-control" name="program" required></div>
                <button class="btn btn-primary" type="submit">Create student</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="content-card">
            <table class="table table-striped">
                <thead><tr><th>Name</th><th>Username</th><th>Program</th><th>Room</th></tr></thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($students)) { ?>
                    <tr>
                        <td><?php echo e($row["first_name"] . " " . $row["last_name"]); ?></td>
                        <td><?php echo e($row["username"]); ?></td>
                        <td><?php echo e($row["program"]); ?></td>
                        <td><?php echo e($row["room_info"] ?: "Unassigned"); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>
