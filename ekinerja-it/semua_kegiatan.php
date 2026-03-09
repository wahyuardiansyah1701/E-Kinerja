<?php
require_once 'includes/auth.php';
requireAdmin();
$user = currentUser();
$db   = getDB();
$activePage = 'semua';
$pageTitle  = 'Semua Kegiatan – E-Kinerja IT';

$cari    = trim($_GET['cari'] ?? '');
$tgl1    = $_GET['tgl1']    ?? date('Y-m-01');
$tgl2    = $_GET['tgl2']    ?? date('Y-m-d');
$userId  = $_GET['user_id'] ?? 'all';

$where  = ["k.tanggal BETWEEN :tgl1 AND :tgl2"];
$params = [':tgl1'=>$tgl1, ':tgl2'=>$tgl2];
if ($cari)          { $where[] = "k.nama_kegiatan LIKE :cari"; $params[':cari'] = "%$cari%"; }
if ($userId!=='all'){ $where[] = "k.user_id = :uid"; $params[':uid'] = $userId; }

$sql  = "SELECT k.*,u.nama FROM kegiatan k JOIN users u ON k.user_id=u.id WHERE ".implode(' AND ',$where)." ORDER BY k.tanggal DESC, k.created_at DESC LIMIT 200";
$stmt = $db->prepare($sql); $stmt->execute($params);
$rows = $stmt->fetchAll();

$staffList = $db->query("SELECT id,nama FROM users WHERE role='user' ORDER BY nama")->fetchAll();
include 'includes/header.php';
?>
<div class="main">
  <div class="topbar">
    <div>
      <div class="topbar-title">📋 Semua Kegiatan</div>
      <div class="topbar-sub"><?= count($rows) ?> kegiatan ditemukan</div>
    </div>
    <div class="topbar-right">
      <a href="laporan.php?bulan=<?= date('Y-m') ?>" class="btn btn-secondary" target="_blank">🖨️ Cetak</a>
    </div>
  </div>
  <div class="content">
    <!-- FILTER -->
    <div class="card fade-up" style="margin-bottom:16px">
      <form method="GET" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
        <div>
          <label class="form-label">Dari Tanggal</label>
          <input type="date" name="tgl1" class="form-control" value="<?= $tgl1 ?>" style="width:155px">
        </div>
        <div>
          <label class="form-label">Sampai</label>
          <input type="date" name="tgl2" class="form-control" value="<?= $tgl2 ?>" style="width:155px">
        </div>
        <div>
          <label class="form-label">Staf</label>
          <select name="user_id" class="form-control" style="width:170px">
            <option value="all">Semua Staf</option>
            <?php foreach ($staffList as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $userId==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">Cari</label>
          <input type="text" name="cari" class="form-control" placeholder="Nama kegiatan..." value="<?= htmlspecialchars($cari) ?>" style="width:200px">
        </div>
        <button type="submit" class="btn btn-primary">🔍 Cari</button>
        <a href="semua_kegiatan.php" class="btn btn-secondary">Reset</a>
      </form>
    </div>

    <div class="card fade-up" style="animation-delay:.1s">
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Tanggal</th><th>Staf</th><th>Nama Kegiatan</th><th>Keterangan</th><th>Kategori</th><th>Status</th><th>Aksi</th></tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:30px">Tidak ada data</td></tr>
            <?php else: foreach ($rows as $i => $k): ?>
            <tr>
              <td style="color:var(--muted);font-family:'DM Mono',monospace"><?= $i+1 ?></td>
              <td style="white-space:nowrap">
                <div style="font-weight:600"><?= date('d M', strtotime($k['tanggal'])) ?></div>
                <div style="font-size:10.5px;color:var(--muted)"><?= date('Y', strtotime($k['tanggal'])) ?></div>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:7px">
                  <div class="mini-avatar" style="background:<?= avatarColor($k['nama']) ?>22;color:<?= avatarColor($k['nama']) ?>"><?= strtoupper(substr($k['nama'],0,2)) ?></div>
                  <span style="font-size:12px;font-weight:600"><?= htmlspecialchars(explode(' ',$k['nama'])[0]) ?></span>
                </div>
              </td>
              <td style="font-weight:600;max-width:200px"><?= htmlspecialchars($k['nama_kegiatan']) ?></td>
              <td style="color:var(--muted);font-size:11.5px;max-width:180px"><?= htmlspecialchars(substr($k['keterangan']??'',0,50)) ?><?= strlen($k['keterangan']??'')>50?'...':'' ?></td>
              <td><span class="badge <?= kategoriColor($k['kategori']) ?>"><?= $k['kategori'] ?></span></td>
              <td><span class="badge <?= statusColor($k['status']) ?>"><?= $k['status'] ?></span></td>
              <td style="white-space:nowrap">
                <a href="edit.php?id=<?= $k['id'] ?>&ref=semua" class="btn btn-secondary btn-sm">✏️</a>
                <a href="delete.php?id=<?= $k['id'] ?>&ref=dashboard" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">🗑️</a>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body></html>
