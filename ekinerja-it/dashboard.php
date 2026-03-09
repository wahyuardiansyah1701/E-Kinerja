<?php
require_once 'includes/auth.php';
requireLogin();
$user     = currentUser();
$db       = getDB();
$today    = date('Y-m-d');
$month    = date('Y-m');
$activePage = 'dashboard';
$pageTitle  = 'Dashboard – E-Kinerja IT';

// Stats
$totalHari   = $db->query("SELECT COUNT(*) FROM kegiatan WHERE tanggal='$today'")->fetchColumn();
$totalBulan  = $db->query("SELECT COUNT(*) FROM kegiatan WHERE strftime('%Y-%m',tanggal)='$month'")->fetchColumn();
$totalUser   = $db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$selesaiBulan= $db->query("SELECT COUNT(*) FROM kegiatan WHERE strftime('%Y-%m',tanggal)='$month' AND status='Selesai'")->fetchColumn();

// Today's activities (self if not admin)
if ($user['role'] === 'admin') {
    $kHari = $db->query("SELECT k.*,u.nama,u.username FROM kegiatan k JOIN users u ON k.user_id=u.id WHERE k.tanggal='$today' ORDER BY k.created_at DESC")->fetchAll();
} else {
    $stmt = $db->prepare("SELECT k.*,u.nama,u.username FROM kegiatan k JOIN users u ON k.user_id=u.id WHERE k.tanggal=? AND k.user_id=? ORDER BY k.created_at DESC");
    $stmt->execute([$today, $user['id']]);
    $kHari = $stmt->fetchAll();
}

// Rekap per user hari ini (admin)
$rekapUserHari = $db->query("SELECT u.nama,u.username,COUNT(k.id) as total,SUM(k.status='Selesai') as selesai FROM users u LEFT JOIN kegiatan k ON u.id=k.user_id AND k.tanggal='$today' WHERE u.role='user' GROUP BY u.id ORDER BY total DESC")->fetchAll();

// Kategori bulan ini
$kategoriStats = $db->query("SELECT kategori,COUNT(*) as total FROM kegiatan WHERE strftime('%Y-%m',tanggal)='$month' GROUP BY kategori ORDER BY total DESC")->fetchAll();

// Kegiatan saya hari ini (untuk user)
$myToday = 0;
if ($user['role'] !== 'admin') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM kegiatan WHERE user_id=? AND tanggal=?");
    $stmt->execute([$user['id'], $today]);
    $myToday = $stmt->fetchColumn();
}

include 'includes/header.php';
?>

<div class="main">
  <div class="topbar">
    <div>
      <div class="topbar-title" style="font-size:15.5px;">Dashboard Kegiatan Harian</div>
<div class="topbar-sub">📅 <span id="realtime-clock"></span></div>
    </div>
    <div class="topbar-right">
      <a href="input.php" class="btn btn-primary">➕ Input Kegiatan</a>
    </div>
  </div>

  <div class="content">

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card fade-up">
        <div class="stat-icon" style="background:rgba(22,163,74,.15)">📋</div>
        <div class="stat-value" style="color:#4ade80"><?= $totalHari ?></div>
        <div class="stat-label">Kegiatan Hari Ini</div>
        <div class="stat-trend" style="color:var(--muted)">Seluruh unit · <?= $today ?></div>
      </div>
      <div class="stat-card fade-up" style="animation-delay:.07s">
        <div class="stat-icon" style="background:rgba(34,197,94,.15)">✅</div>
        <div class="stat-value" style="color:var(--green)"><?= $selesaiBulan ?></div>
        <div class="stat-label">Selesai Bulan Ini</div>
        <div class="stat-trend" style="color:var(--muted)">dari <?= $totalBulan ?> total kegiatan</div>
      </div>
      <div class="stat-card fade-up" style="animation-delay:.14s">
        <div class="stat-icon" style="background:rgba(245,158,11,.15)">📅</div>
        <div class="stat-value" style="color:var(--yellow)"><?= $totalBulan ?></div>
        <div class="stat-label">Total Kegiatan Bulan Ini</div>
        <div class="stat-trend" style="color:var(--muted)"><?= date('F Y') ?></div>
      </div>
      <div class="stat-card fade-up" style="animation-delay:.21s">
        <div class="stat-icon" style="background:rgba(249,115,22,.15)">👥</div>
        <div class="stat-value" style="color:var(--accent2-light)"><?= $totalUser ?></div>
        <div class="stat-label">Staf IT Aktif</div>
        <div class="stat-trend" style="color:var(--muted)">terdaftar di sistem</div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:18px;margin-bottom:18px;">

      <!-- KEGIATAN HARI INI -->
      <div class="card fade-up" style="animation-delay:.25s">
        <div class="card-header">
          <div>
            <div class="card-title">🗓️ Kegiatan Hari Ini</div>
            <div class="card-sub"><?= $user['role']==='admin' ? 'Semua staf' : 'Kegiatan saya' ?> · <?= $today ?></div>
          </div>
          <a href="input.php" class="btn btn-primary btn-sm">➕ Tambah</a>
        </div>

        <?php if (empty($kHari)): ?>
        <div style="text-align:center;padding:32px 0;color:var(--muted);">
          <div style="font-size:36px;margin-bottom:10px;">📭</div>
          <div style="font-size:13.5px;">Belum ada kegiatan hari ini</div>
          <a href="input.php" style="color:#4ade80;font-size:13px;font-weight:600;">Input sekarang →</a>
        </div>
        <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <?php if ($user['role']==='admin'): ?><th>Staf</th><?php endif; ?>
                <th>Nama Kegiatan</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($kHari as $k): ?>
              <tr>
                <?php if ($user['role']==='admin'): ?>
                <td>
                  <div style="display:flex;align-items:center;gap:8px;">
                    <div class="mini-avatar" style="background:<?= avatarColor($k['nama']) ?>22;color:<?= avatarColor($k['nama']) ?>">
                      <?= strtoupper(substr($k['nama'],0,2)) ?>
                    </div>
                    <span style="font-weight:600;font-size:12px"><?= htmlspecialchars($k['nama']) ?></span>
                  </div>
                </td>
                <?php endif; ?>
                <td>
                  <div style="font-weight:600;"><?= htmlspecialchars($k['nama_kegiatan']) ?></div>
                  <?php if ($k['keterangan']): ?>
                  <div style="font-size:11px;color:var(--muted);margin-top:2px;"><?= htmlspecialchars(substr($k['keterangan'],0,60)).(strlen($k['keterangan'])>60?'...':'') ?></div>
                  <?php endif; ?>
                </td>
                <td><span class="badge <?= kategoriColor($k['kategori']) ?>"><?= $k['kategori'] ?></span></td>
                <td><span class="badge <?= statusColor($k['status']) ?>"><?= $k['status'] ?></span></td>
                <td>
                  <?php if ($user['role']==='admin' || $k['user_id']==$user['id']): ?>
                  <a href="edit.php?id=<?= $k['id'] ?>" class="btn btn-secondary btn-sm">✏️</a>
                  <a href="delete.php?id=<?= $k['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus kegiatan ini?')">🗑️</a>
                  <?php else: ?>
                  <span style="color:var(--muted);font-size:11px;">—</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- SIDEBAR RIGHT -->
      <div style="display:flex;flex-direction:column;gap:14px;">

        <?php if ($user['role']==='admin'): ?>
        <!-- REKAP USER HARI INI -->
        <div class="card fade-up" style="animation-delay:.28s">
          <div class="card-header" style="margin-bottom:12px">
            <div class="card-title">👥 Staf Hari Ini</div>
          </div>
          <?php foreach ($rekapUserHari as $r): ?>
          <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);">
            <div class="mini-avatar" style="background:<?= avatarColor($r['nama']) ?>22;color:<?= avatarColor($r['nama']) ?>">
              <?= strtoupper(substr($r['nama'],0,2)) ?>
            </div>
            <div style="flex:1;min-width:0;">
              <div style="font-size:12.5px;font-weight:600;"><?= htmlspecialchars($r['nama']) ?></div>
              <div style="font-size:11px;color:var(--muted);"><?= $r['total'] ?> kegiatan</div>
            </div>
            <?php if ($r['total'] > 0): ?>
            <span class="badge badge-green"><?= $r['selesai'] ?>/<?= $r['total'] ?></span>
            <?php else: ?>
            <span class="badge badge-gray">0</span>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- KATEGORI BULAN INI -->
        <div class="card fade-up" style="animation-delay:.32s">
          <div class="card-header" style="margin-bottom:12px">
            <div>
              <div class="card-title">📊 Kategori Bulan Ini</div>
              <div class="card-sub"><?= date('F Y') ?></div>
            </div>
          </div>
          <?php if (empty($kategoriStats)): ?>
          <div style="text-align:center;color:var(--muted);font-size:13px;padding:16px 0;">Belum ada data</div>
          <?php else:
            $maxKat = max(array_column($kategoriStats,'total'));
            foreach ($kategoriStats as $k):
              $pct = $maxKat > 0 ? round($k['total']/$maxKat*100) : 0;
          ?>
          <div style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:5px;">
              <span style="font-weight:600;"><?= $k['kategori'] ?></span>
              <span style="font-family:'DM Mono',monospace;color:var(--muted2)"><?= $k['total'] ?></span>
            </div>
            <div style="height:5px;background:var(--surface2);border-radius:10px;overflow:hidden;">
              <div style="height:100%;width:<?= $pct ?>%;background:linear-gradient(to right,#16a34a,#f97316);border-radius:10px;"></div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>

      </div>
    </div>

  </div>
</div>
<script>
function updateClock() {
    const now = new Date();
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    
    const hari   = days[now.getDay()];
    const tgl    = String(now.getDate()).padStart(2,'0');
    const bulan  = months[now.getMonth()];
    const tahun  = now.getFullYear();
    const jam    = String(now.getHours()).padStart(2,'0');
    const menit  = String(now.getMinutes()).padStart(2,'0');
    const detik  = String(now.getSeconds()).padStart(2,'0');

    document.getElementById('realtime-clock').textContent = 
        `${hari}, ${tgl} ${bulan} ${tahun}  |  ⏰ ${jam}:${menit}:${detik}`;
}

updateClock();
setInterval(updateClock, 1000);
</script>
</body></html>
