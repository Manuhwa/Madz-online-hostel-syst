<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/database.php";

function current_user_id()
{
    return isset($_SESSION["user_id"]) ? (int) $_SESSION["user_id"] : 0;
}

function require_login()
{
    if (empty($_SESSION["loggedin"])) {
        header("location: /index.php");
        exit;
    }
}

function require_role($role)
{
    require_login();
    if (($_SESSION["role"] ?? "") !== $role) {
        if (($_SESSION["role"] ?? "") === "admin") {
            header("location: /admin/dashboard.php");
        } else {
            header("location: /student/dashboard.php");
        }
        exit;
    }
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function flash_set($type, $message)
{
    $_SESSION["flash"] = ["type" => $type, "message" => $message];
}

function flash_show()
{
    if (empty($_SESSION["flash"])) {
        return;
    }
    $flash = $_SESSION["flash"];
    unset($_SESSION["flash"]);
    echo '<div class="alert alert-' . e($flash["type"]) . '">' . e($flash["message"]) . "</div>";
}

function current_student($conn)
{
    $user_id = current_user_id();
    $stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $student;
}
