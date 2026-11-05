<?php
require_once __DIR__ . "/../config/auth.php";
require_role("student");
$page_title = "Profile";
$student = current_student($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST" && $student) {
    $phone = trim($_POST["phone"] ?? "");
    $program = trim($_POST["program"] ?? "");
    $sid = (int) $student["student_id"];
    $stmt = mysqli_prepare($conn, "UPDATE students SET phone = ?, program = ? WHERE student_id = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $phone, $program, $sid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    flash_set("success", "Profile updated.");
    header("location: profile.php");
    exit;
}

include __DIR__ . "/../includes/header.php";
flash_show();
?>
<h1 class="mb-4">My Profile</h1>
<div class="content-card" style="max-width: 560px;">
    <?php if ($student) { ?>
        <p><strong>Name:</strong> <?php echo e($student["first_name"] . " " . $student["last_name"]); ?></p>
        <p><strong>Email:</strong> <?php echo e($student["email"]); ?></p>
        <p><strong>Gender:</strong> <?php echo e($student["gender"]); ?></p>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input class="form-control" name="phone" value="<?php echo e($student["phone"]); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Program</label>
                <input class="form-control" name="program" value="<?php echo e($student["program"]); ?>" required>
            </div>
            <button class="btn btn-primary" type="submit">Save changes</button>
        </form>
    <?php } else { ?>
        <p>No student profile is linked to this account.</p>
    <?php } ?>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>
