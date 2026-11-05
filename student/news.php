<?php
require_once __DIR__ . "/../config/auth.php";
require_role("student");
$page_title = "News";
$news = mysqli_query($conn, "SELECT title, content, created_at FROM news_updates ORDER BY created_at DESC");
include __DIR__ . "/../includes/header.php";
?>
<h1 class="mb-4">News and Updates</h1>
<?php while ($row = mysqli_fetch_assoc($news)) { ?>
    <div class="content-card mb-3">
        <h5><?php echo e($row["title"]); ?></h5>
        <p class="text-muted"><?php echo e($row["created_at"]); ?></p>
        <p class="mb-0"><?php echo nl2br(e($row["content"])); ?></p>
    </div>
<?php } ?>
<?php include __DIR__ . "/../includes/footer.php"; ?>
