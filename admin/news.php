<?php
require_once __DIR__ . "/../config/auth.php";
require_role("admin");
$page_title = "News and Updates";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "create";
    if ($action === "create") {
        $title = trim($_POST["title"] ?? "");
        $content = trim($_POST["content"] ?? "");
        $user_id = current_user_id();
        if ($title && $content) {
            $stmt = mysqli_prepare($conn, "INSERT INTO news_updates (user_id, title, content) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iss", $user_id, $title, $content);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            flash_set("success", "News posted.");
        }
    } elseif ($action === "delete") {
        $news_id = (int) ($_POST["news_id"] ?? 0);
        $stmt = mysqli_prepare($conn, "DELETE FROM news_updates WHERE news_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $news_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        flash_set("success", "News deleted.");
    }
    header("location: news.php");
    exit;
}

$news = mysqli_query($conn, "SELECT n.*, u.username FROM news_updates n JOIN users u ON n.user_id = u.user_id ORDER BY n.created_at DESC");
include __DIR__ . "/../includes/header.php";
flash_show();
?>
<h1 class="mb-4">News and Updates</h1>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="content-card">
            <h4>Post Update</h4>
            <form method="post" class="mt-3">
                <input type="hidden" name="action" value="create">
                <div class="mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
                <div class="mb-3"><label class="form-label">Content</label><textarea class="form-control" name="content" rows="5" required></textarea></div>
                <button class="btn btn-primary" type="submit">Publish</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <?php while ($row = mysqli_fetch_assoc($news)) { ?>
            <div class="content-card mb-3">
                <div class="d-flex justify-content-between">
                    <h5><?php echo e($row["title"]); ?></h5>
                    <form method="post">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="news_id" value="<?php echo (int) $row["news_id"]; ?>">
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
                <p class="text-muted mb-2"><?php echo e($row["created_at"]); ?> · <?php echo e($row["username"]); ?></p>
                <p class="mb-0"><?php echo nl2br(e($row["content"])); ?></p>
            </div>
        <?php } ?>
    </div>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>
