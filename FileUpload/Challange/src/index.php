<!DOCTYPE html>
<html>
<head>
    <title>Evil Avatar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <h2>🖼️ Upload Your Avatar</h2>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="avatar" required>
        <button>Upload</button>
    </form>
    <p class="hint">Only <b>.jpg</b> or <b>.png</b> allowed 😉</p>
</div>
</body>
</html>
