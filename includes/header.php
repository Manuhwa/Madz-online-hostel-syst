<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_subfolder = preg_match("#/(admin|student)/#", $_SERVER["PHP_SELF"] ?? "");
$home = $is_subfolder ? "../index.php" : "index.php";
$role = $_SESSION["role"] ?? "";
$title = $page_title ?? "Hostel Management System";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f8; }
        .navbar-brand { font-weight: 700; }
        .login-container { max-width: 420px; margin: 80px auto; }
        .login-card, .content-card { background: #fff; border-radius: 10px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08); padding: 28px; }
        .stat-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06); }
        .table thead { background: #eef2f6; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $home; ?>">Madz Hostel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <?php if ($role === "admin") { ?>
                    <li class="nav-item"><a class="nav-link" href="/admin/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/hostels.php">Hostels</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/students.php">Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/rooms.php">Rooms</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/allocations.php">Allocations</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/maintenance.php">Maintenance</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/news.php">News</a></li>
                <?php } elseif ($role === "student") { ?>
                    <li class="nav-item"><a class="nav-link" href="/student/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/student/maintenance.php">Maintenance</a></li>
                    <li class="nav-item"><a class="nav-link" href="/student/news.php">News</a></li>
                    <li class="nav-item"><a class="nav-link" href="/student/profile.php">Profile</a></li>
                <?php } ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (!empty($_SESSION["loggedin"])) { ?>
                    <li class="nav-item"><span class="navbar-text text-white me-3"><?php echo htmlspecialchars($_SESSION["username"] ?? ""); ?></span></li>
                    <li class="nav-item"><a class="nav-link" href="/logout.php">Logout</a></li>
                <?php } else { ?>
                    <li class="nav-item"><a class="nav-link" href="/admin_login.php">Admin</a></li>
                    <li class="nav-item"><a class="nav-link" href="/student_login.php">Student</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container mb-5">
