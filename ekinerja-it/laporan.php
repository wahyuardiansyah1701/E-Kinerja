<?php
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();

$bulan   = $_GET['bulan']   ?? date('Y-m');
$userId  = $_GET['user_id'] ?? ($user['role']==='admin' ? 'all' : $user['id']);

$where  = ["strftime('%Y-%m', k.tanggal) = :bulan"];
$params = [':bulan' => $bulan];

if ($user['role'] !== 'admin') {
    $where[] = "k.user_id = :uid"; $params[':uid'] = $user['id'];
} elseif ($userId !== 'all') {
    $where[] = "k.user_id = :uid"; $params[':uid'] = $userId;
}

$sql  = "SELECT k.*,u.nama FROM kegiatan k JOIN users u ON k.user_id=u.id WHERE ".implode(' AND ',$where)." ORDER BY u.nama, k.tanggal ASC";
$stmt = $db->prepare($sql); $stmt->execute($params);
$rows = $stmt->fetchAll();

// Group by user
$byUser = [];
foreach ($rows as $r) {
    $byUser[$r['nama']][] = $r;
}

$bulanLabel = date('F Y', strtotime($bulan.'-01'));
$namaUnit   = 'Unit Teknologi Informasi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Kegiatan IT – <?= $bulanLabel ?></title>
<style>
  @media print { .no-print{display:none!important} }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{font-family:'Segoe UI',Arial,sans-serif;background:#fff;color:#111;font-size:12px;padding:20px;}
  .print-btn{position:fixed;top:16px;right:16px;padding:8px 18px;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;}
  h1{font-size:16px;text-align:center;margin-bottom:4px;}
  .subtitle{text-align:center;color:#555;margin-bottom:20px;font-size:12px;}
  table{width:100%;border-collapse:collapse;margin-bottom:20px;}
  th,td{border:1px solid #ccc;padding:7px 9px;text-align:left;font-size:11.5px;}
  th{background:#f0f4f8;font-weight:700;}
  .section-title{font-size:13px;font-weight:700;margin:16px 0 6px;padding:6px 10px;background:#f0fdf4;border-left:4px solid #16a34a;}
  .total-row{background:#f8fafc;font-weight:700;}
  .badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10.5px;font-weight:600;}
  .s-selesai{background:#dcfce7;color:#166534;} .s-proses{background:#fef9c3;color:#854d0e;} .s-tertunda{background:#fee2e2;color:#991b1b;}
  .k-hardware{background:#dbeafe;color:#1e40af;} .k-software{background:#ede9fe;color:#6d28d9;} .k-jaringan{background:#d1fae5;color:#065f46;} .k-server{background:#fef3c7;color:#92400e;} .k-printer{background:#fee2e2;color:#991b1b;} .k-lainnya{background:#f1f5f9;color:#475569;}
  .summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;}
  .summary-box{border:1px solid #e2e8f0;border-radius:8px;padding:12px;text-align:center;}
  .sum-val{font-size:24px;font-weight:800;color:#16a34a;}
  .sum-label{font-size:11px;color:#64748b;margin-top:4px;}
  .header-logo{display:flex;align-items:center;gap:14px;margin-bottom:12px;padding-bottom:12px;border-bottom:2px solid #16a34a;}
</style>
</head>
<body>
<button class="print-btn no-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

<div class="header-logo">
  <div style="width:50px;height:50px;background:linear-gradient(135deg,#16a34a,#f97316);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0">💻</div>
  <div>
    <h1 style="text-align:left">Laporan Kegiatan Harian <?= $namaUnit ?></h1>
    <div style="text-align:left;color:#555;font-size:12px">Periode: <?= $bulanLabel ?> &nbsp;|&nbsp; Dicetak: <?= date('d/m/Y H:i') ?></div>
  </div>
</div>

<?php
$total    = count($rows);
$selesai  = count(array_filter($rows,fn($r)=>$r['status']==='Selesai'));
$proses   = count(array_filter($rows,fn($r)=>$r['status']==='Proses'));
?>
<div class="summary-grid">
  <div class="summary-box"><div class="sum-val"><?= $total ?></div><div class="sum-label">Total Kegiatan</div></div>
  <div class="summary-box"><div class="sum-val" style="color:#16a34a"><?= $selesai ?></div><div class="sum-label">Selesai</div></div>
  <div class="summary-box"><div class="sum-val" style="color:#d97706"><?= $proses ?></div><div class="sum-label">Proses / Tertunda</div></div>
</div>

<?php foreach ($byUser as $nama => $kegiatanUser): ?>
<div class="section-title">👤 <?= htmlspecialchars($nama) ?> — <?= count($kegiatanUser) ?> kegiatan</div>
<table>
  <thead>
    <tr><th>No</th><th>Tanggal</th><th>Nama Kegiatan</th><th>Keterangan</th><th>Kategori</th><th>Status</th></tr>
  </thead>
  <tbody>
    <?php foreach ($kegiatanUser as $i => $k):
      $kClass = 'k-'.strtolower($k['kategori']);
      $sClass = 's-'.strtolower($k['status']);
    ?>
    <tr>
      <td><?= $i+1 ?></td>
      <td><?= date('d/m/Y', strtotime($k['tanggal'])) ?></td>
      <td><?= htmlspecialchars($k['nama_kegiatan']) ?></td>
      <td><?= htmlspecialchars($k['keterangan'] ?? '') ?></td>
      <td><span class="badge <?= $kClass ?>"><?= $k['kategori'] ?></span></td>
      <td><span class="badge <?= $sClass ?>"><?= $k['status'] ?></span></td>
    </tr>
    <?php endforeach; ?>
    <tr class="total-row">
      <td colspan="5" style="text-align:right">Total Kegiatan <?= htmlspecialchars($nama) ?>:</td>
      <td><?= count($kegiatanUser) ?></td>
    </tr>
  </tbody>
</table>
<?php endforeach; ?>

<?php if (empty($rows)): ?>
<p style="text-align:center;color:#888;padding:30px">Tidak ada data kegiatan untuk periode ini.</p>
<?php endif; ?>

<div style="margin-top:40px;display:flex;justify-content:flex-end;">
  <div style="text-align:center;width:200px;">
    <div style="font-size:12px">Mengetahui,</div>
    <div style="font-size:12px">Kepala <?= $namaUnit ?></div>
    <div style="height:60px"></div>
    <div style="border-top:1px solid #333;padding-top:4px;font-size:12px">(__________________)</div>
  </div>
</div>
</body>
</html>
