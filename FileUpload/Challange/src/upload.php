<?php
$dir = "uploads/";
$file = $_FILES['avatar'];
$name = $file['name'];
$tmp  = $file['tmp_name'];

if (strpos($name, '.jpg') !== false || strpos($name, '.png') !== false) {
    move_uploaded_file($tmp, $dir . $name);
    echo "<link rel='stylesheet' href='style.css'>";
    echo "<div class='card'>";
    echo "<h2>Upload Success 🎉</h2>";
    echo "<a class='link' href='$dir$name'>View Avatar</a>";
    echo "</div>";
} else {
    echo "Only images allowed!";
}
?>
