<?php
$page = $_GET['page'] ?? 'home.php';
if (strpos($page, 'http') !== false) {
    die("Remote include not allowed!");
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Lost in Pages</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
  <h1>📄 Lost in Pages</h1>
  <p class="subtitle">A simple CMS — but something feels off...</p>
  <div class="nav">
    <a href="?page=home.php">Home</a>
    <a href="?page=about.php">About</a>
  </div>
  <div class="content">
    <?php include("pages/" . $page); ?>
  </div>
</div>
<script src="assets/script.js"></script>
</body>
</html>
