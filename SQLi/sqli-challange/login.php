<?php
// File: login.php
session_start();
$dbFile = __DIR__ . '/data/ctf.db';
$flagDbFile = __DIR__ . '/data/flag.db';
if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0755, true);
$init = !file_exists($dbFile);
$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($init) {
    // Main office DB (no Fl4gs table here)
    $db->exec("CREATE TABLE users(id INTEGER PRIMARY KEY, username TEXT, password TEXT);");
    $db->exec("CREATE TABLE sales(id INTEGER PRIMARY KEY, item TEXT, qty INTEGER, price INTEGER);");
    $db->exec("CREATE TABLE stock(id INTEGER PRIMARY KEY, item TEXT, qty INTEGER, location TEXT);");
    $db->exec("CREATE TABLE schedule(id INTEGER PRIMARY KEY, event TEXT, date TEXT);");

    // Populate tables with more rows for a denser dataset
    $db->exec("INSERT INTO users(username,password) VALUES ('admin_arif','aDmin#123arif'), ('staff43533','staff123hbytd');");

    $db->exec("INSERT INTO sales(item,qty,price) VALUES
        ('Laptop',2,15000000),
        ('Printer',1,2500000),
        ('Monitor',5,1800000),
        ('Keyboard',10,150000),
        ('Mouse',25,75000),
        ('Router',3,650000),
        ('Switch',4,450000),
        ('SSD 512GB',6,1200000),
        ('HDD 2TB',8,900000),
        ('Projector',1,3500000),
        ('Desk',7,400000),
        ('Chair',12,200000)
    ;");

    $db->exec("INSERT INTO stock(item,qty,location) VALUES
        ('Laptop',5,'Gudang A'),
        ('Mouse',50,'Gudang B'),
        ('Monitor',20,'Gudang A'),
        ('Keyboard',30,'Gudang B'),
        ('Printer',6,'Gudang C'),
        ('Router',10,'Gudang A'),
        ('Switch',15,'Gudang C'),
        ('SSD 512GB',25,'Gudang B'),
        ('HDD 2TB',18,'Gudang B'),
        ('Projector',2,'Gudang A'),
        ('Desk',9,'Gudang C'),
        ('Chair',40,'Gudang C')
    ;");

    $db->exec("INSERT INTO schedule(event,date) VALUES
        ('Rapat Bulanan','2025-10-25'),
        ('Audit','2025-11-01'),
        ('Pelatihan Keamanan','2025-11-10'),
        ('Pemeriksaan Inventaris','2025-11-20'),
        ('Rapat Divisi','2025-12-05'),
        ('Pemeliharaan Server','2025-12-15'),
        ('Pemasangan Jaringan','2026-01-07'),
        ('Kegiatan Sosial','2026-01-20')
    ;");

    // Create separate DB for flags so the Fl4gs table is not stored in the office DB
    $flagDb = new PDO('sqlite:' . $flagDbFile);
    $flagDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $flagDb->exec("CREATE TABLE Fl4gs(id INTEGER PRIMARY KEY, flag TEXT);");
    $flag = "SMKN22{sql_Sql_sQl_sqL_SQL_Injection_101010121}";
    $stmt = $flagDb->prepare("INSERT INTO Fl4gs(flag) VALUES (?)");
    $stmt->execute([$flag]);
}

$warning = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Detect exact-case "OR" or "or" substrings and block them.
    // This intentionally blocks only the exact all-uppercase "OR" and the exact all-lowercase "or",
    // while allowing mixed-case variants like "oR" or "Or".
    if (strpos($user, 'OR') !== false || strpos($pass, 'OR') !== false) {
        $warning = '"OR" teridentifikasi sql injection';
    } elseif (strpos($user, 'or') !== false || strpos($pass, 'or') !== false) {
        $warning = '"or" teridentifikasi sql injection';
    } else {
        // VULNERABLE: raw interpolation (intentionally)
        $sql = "SELECT * FROM users WHERE username = '" . $user . "' AND password = '" . $pass . "' LIMIT 1";
        try {
            $res = $db->query($sql);
            $row = $res->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $_SESSION['username'] = $row['username'];
                header('Location: index.php');
                exit;
            } else {
                $warning = 'Login gagal.';
            }
        } catch (Exception $e) {
            // silent
            $warning = 'Login gagal.';
        }
    }
}

?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SIQIL - Login</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#ffffff;--text:#111827;--muted:#6b7280;--accent:#0ea5a3}
*{box-sizing:border-box;font-family:Inter,system-ui,Segoe UI,Roboto,Arial}
body{margin:0;background:var(--bg);color:var(--text);display:flex;align-items:center;justify-content:center;height:100vh}
.card{width:480px;padding:28px;border-radius:10px;background:#ffffff;border:1px solid #eef2f7;box-shadow:0 8px 30px rgba(17,24,39,0.06)}
.input{width:100%;padding:12px;border-radius:10px;border:1px solid #e5e7eb;background:transparent;color:inherit;margin-top:10px}
.btn{width:100%;padding:12px;border-radius:10px;border:none;margin-top:12px;background:var(--accent);color:#ffffff;font-weight:700;cursor:pointer}
.hint{font-size:13px;color:var(--muted);margin-top:8px;text-align:center}
.alert{background:#fee2e2;padding:10px;border-radius:8px;margin-top:10px;color:#991b1b;text-align:center}
.title{font-size:22px;font-weight:700;margin-bottom:6px}
.form-row{display:flex;gap:10px}
.small{font-size:13px;color:var(--muted)}
</style>
</head>
<body>
  <div class="card">
    <div class="title">Masuk ke SIQIL</div>
    <form method="post">
      <input class="input" name="username" placeholder="username" autocomplete="off">
      <input class="input" name="password" placeholder="password" type="password" autocomplete="off">
      <button class="btn" type="submit" name="login">Login</button>
    </form>
    <?php if ($warning): ?><div class="alert"><?=htmlspecialchars($warning)?></div><?php endif; ?>
    <div class="hint">Masukkan kredensial Anda untuk melanjutkan.</div>
  </div>
</body>
</html>
