<?php
session_start();

// Check if user is logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if ($_SESSION["role"] === "admin") {
        header("location: admin/dashboard.php");
    } else {
        header("location: student/dashboard.php");
    }
    exit;
}

include "includes/header.php";
?>

<div class="login-container">
    <div class="login-card">
        <div class="text-center mb-4">
            <img src="images/logo.jpg" alt="College Logo" class="img-fluid mb-3" style="max-height: 100px;">
            <h2>Login</h2>
        </div>

        <?php if (isset($_SESSION['error'])) { ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php } ?>

        <form action="login.php" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </div>
</div>

<?php include "includes/footer.php"; ?> 