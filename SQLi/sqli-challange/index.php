<?php
session_start();

// cek login
if (!isset($_SESSION['username'])) {
  header('Location: login.php');
  exit;
}

$dbFile = __DIR__ . '/data/ctf.db';
$flagDbFile = __DIR__ . '/data/flag.db';
if (!file_exists($dbFile)) {
  header('Location: login.php');
  exit;
}

try {
  $db = new PDO('sqlite:' . $dbFile);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
  echo '<pre>Cannot open database: ' . htmlspecialchars($e->getMessage()) . '</pre>';
  exit;
}

$flagDbExists = file_exists($flagDbFile);
if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: login.php');
  exit;
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : '';
$contentRows = [];
$contentCols = [];
$tab_error = '';
$blocked_flag_db = false;

if ($tab !== '') {
  if ($tab === 'Fl4gs' && $flagDbExists) {
    $blocked_flag_db = true;
  } else {
    try {
      // VULN SQLI sengaja dipertahankan
      $query = "SELECT * FROM '" . $tab . "'";
      $res = $db->query($query);
      $contentRows = $res->fetchAll(PDO::FETCH_ASSOC);
      if (count($contentRows) > 0) $contentCols = array_keys($contentRows[0]);
    } catch (Exception $e) {
      $tab_error = $e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SIQIL - Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
  --bg: #ffffff;
  --card: #fbfcfe;
  --border: #e5e7eb;
  --text: #111827;
  --muted: #6b7280;
}
* { box-sizing:border-box; font-family:Inter,system-ui,Segoe UI,Roboto,Arial; }
body { margin:0; background:var(--bg); color:var(--text); }
.container { max-width:1200px; margin:30px auto; padding:20px; }
.header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.btn { border:1px solid var(--border); padding:8px 12px; border-radius:8px; text-decoration:none; color:var(--text); }
.card { background:#fff; border-radius:12px; border:1px solid var(--border); padding:20px; }
.small-note { color:var(--muted); font-size:14px; }
.chart-main canvas { width:100%; height:320px; display:block; background:var(--card); border-radius:10px; border:1px solid var(--border); padding:10px; }
.chart-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:20px; align-items:flex-start; }
.donut-box { display:flex; align-items:center; justify-content:center; background:var(--card); border:1px solid var(--border); border-radius:10px; padding:10px; height:250px; }
.note { background:#f3f4f6; border-radius:10px; padding:20px; font-size:14px; line-height:1.5; }
.table { width:100%; border-collapse:collapse; margin-top:20px; }
.table th, .table td { border-bottom:1px solid #e5e7eb; padding:10px; text-align:left; }
.table thead { background:var(--card); }
.php-error { background:#fff8f8; border-left:6px solid #ef4444; padding:10px; margin-top:10px; font-family:monospace; color:#991b1b; }
.footer { text-align:center; margin-top:20px; font-size:13px; color:var(--muted); }
@media(max-width:900px){ .chart-grid{grid-template-columns:1fr;} }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div><h2>SIQIL Dashboard</h2><div class="small-note">Sistem Manajemen Kantor</div></div>
    <div>
      <span class="small-note">Halo, <strong><?=htmlspecialchars($_SESSION['username'])?></strong></span>
      <a class="btn" href="?logout=1">Logout</a>
    </div>
  </div>

  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
      <h3 style="margin:0">Beranda</h3>
      <div style="display:flex;gap:8px;">
        <a class="btn" href="index.php">Home</a>
        <a class="btn" href="?tab=sales">Penjualan</a>
        <a class="btn" href="?tab=stock">Stok</a>
        <a class="btn" href="?tab=schedule">Jadwal</a>
      </div>
    </div>

    <?php if ($tab === ''): ?>
      <p class="small-note">Selamat datang di halaman utama manajemen perusahaan.</p>

      <h4>Diagram Batang Perkembangan Perusahaan (2019–2025)</h4>
      <div class="chart-main"><canvas id="growthChart"></canvas></div>

      <div class="chart-grid">
        <div>
          <h4>Survei Kepuasan Pelanggan</h4>
          <div class="donut-box">
            <canvas id="surveyChart"></canvas>
          </div>
        </div>
        <div class="note">
          <h4 style="margin-top:0">Tujuan & Visi Misi</h4>
          <p><strong>Tujuan:</strong> Meningkatkan efisiensi, kualitas layanan, dan kepuasan pelanggan.</p>
          <p><strong>Visi:</strong> Menjadi perusahaan digital terpercaya yang berinovasi tinggi.</p>
          <p><strong>Misi:</strong></p>
          <ul>
            <li>Mengembangkan sumber daya manusia yang unggul.</li>
            <li>Memberikan layanan terbaik dan modern.</li>
            <li>Membangun kerja sama strategis.</li>
            <li>Menjaga integritas dan profesionalisme.</li>
          </ul>
        </div>
      </div>

    <?php else: ?>
      <?php if ($blocked_flag_db): ?>
        <p class="small-note">Tabel tidak ditemukan.</p>
      <?php elseif ($tab_error !== ''): ?>
        <div class="php-error">PHP Parse error: <?=htmlspecialchars($tab_error)?></div>
      <?php elseif (count($contentRows) === 0): ?>
        <p class="small-note">Tidak ada data untuk ditampilkan.</p>
      <?php else: ?>
        <table class="table">
          <thead><tr>
            <?php foreach ($contentCols as $col): ?>
              <th><?=htmlspecialchars($col)?></th>
            <?php endforeach; ?>
          </tr></thead>
          <tbody>
            <?php foreach ($contentRows as $r): ?>
              <tr>
                <?php foreach ($contentCols as $col): ?>
                  <td><?=htmlspecialchars($r[$col])?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    <?php endif; ?>

    <div class="footer">© 2025 SIQIL • Dashboard Internal</div>
  </div>
</div>

<script>
// Chart Bar
const barCtx = document.getElementById('growthChart').getContext('2d');
const growthData = [40, 55, 60, 72, 68, 80, 77];
new Chart(barCtx, {
  type: 'bar',
  data: {
    labels: ['2019','2020','2021','2022','2023','2024','2025'],
    datasets: [{
      label: 'Persentase Pertumbuhan',
      data: growthData,
      backgroundColor: growthData.map((v,i)=>i>0&&growthData[i]>growthData[i-1]?'#22c55e':'#ef4444')
    }]
  },
  options: {
    maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    interaction:{mode:null},
    scales:{y:{beginAtZero:true}}
  }
});

// Chart Pie
const pieCtx = document.getElementById('surveyChart').getContext('2d');
new Chart(pieCtx, {
  type:'doughnut',
  data:{
    labels:['Sangat Puas','Puas','Cukup','Kurang'],
    datasets:[{data:[45,35,15,5], backgroundColor:['#22c55e','#3b82f6','#fbbf24','#ef4444'], borderWidth:0}]
  },
  options:{
    maintainAspectRatio:false,
    plugins:{legend:{position:'bottom'}},
    interaction:{mode:null}
  }
});
</script>
</body>
</html>
