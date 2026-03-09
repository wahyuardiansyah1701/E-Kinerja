<?php
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$activePage = 'rekap';
$pageTitle  = 'Rekap Bulanan – E-Kinerja IT';

$bulan    = $_GET['bulan']    ?? date('Y-m');
$userId   = $_GET['user_id']  ?? ($user['role']==='admin' ? 'all' : $user['id']);
$kategori = $_GET['kategori'] ?? 'all';
$status   = $_GET['status']   ?? 'all';

// Build query
$where = ["strftime('%Y-%m', k.tanggal) = :bulan"];
$params = [':bulan' => $bulan];

if ($user['role'] !== 'admin') {
    $where[] = "k.user_id = :uid";
    $params[':uid'] = $user['id'];
} elseif ($userId !== 'all') {
    $where[] = "k.user_id = :uid";
    $params[':uid'] = $userId;
}
if ($kategori !== 'all') { $where[] = "k.kategori = :kat"; $params[':kat'] = $kategori; }
if ($status   !== 'all') { $where[] = "k.status = :st";  $params[':st']  = $status; }

$sql   = "SELECT k.*,u.nama,u.username FROM kegiatan k JOIN users u ON k.user_id=u.id WHERE " . implode(' AND ',$where) . " ORDER BY k.tanggal DESC, k.created_at DESC";
$stmt  = $db->prepare($sql);
$stmt->execute($params);
$rows  = $stmt->fetchAll();

// Summary per user
$sqlSum = "SELECT u.nama, COUNT(k.id) as total, SUM(k.status='Selesai') as selesai, SUM(k.status='Proses') as proses, SUM(k.status='Tertunda') as tertunda
           FROM users u LEFT JOIN kegiatan k ON u.id=k.user_id AND strftime('%Y-%m',k.tanggal)=:bulan
           WHERE u.role='user' GROUP BY u.id ORDER BY total DESC";
$sumStmt = $db->prepare($sqlSum);
$sumStmt->execute([':bulan'=>$bulan]);
$summary = $sumStmt->fetchAll();

// Kategori breakdown
$katSql  = "SELECT kategori, COUNT(*) as total FROM kegiatan WHERE strftime('%Y-%m',tanggal)=:bulan GROUP BY kategori ORDER BY total DESC";
$katStmt = $db->prepare($katSql);
$katStmt->execute([':bulan'=>$bulan]);
$katStats = $katStmt->fetchAll();

$staffList = $db->query("SELECT id,nama FROM users WHERE role='user' ORDER BY nama")->fetchAll();

include 'includes/header.php';
?>

<div class="main">
  <div class="topbar">
    <div>
      <div class="topbar-title">📊 Rekap Kegiatan Bulanan</div>
      <div class="topbar-sub">Periode: <?= date('F Y', strtotime($bulan.'-01')) ?></div>
    </div>
    <div class="topbar-right">
      <a href="laporan.php?bulan=<?= $bulan ?>&user_id=<?= $userId ?>" class="btn btn-secondary" target="_blank">🖨️ Cetak</a>
    </div>
  </div>

  <div class="content">

    <!-- FILTER -->
    <div class="card fade-up" style="margin-bottom:18px">
      <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <div>
          <label class="form-label">Bulan</label>
          <input type="month" name="bulan" class="form-control" value="<?= $bulan ?>" style="width:170px">
        </div>
        <?php if ($user['role']==='admin'): ?>
        <div>
          <label class="form-label">Staf</label>
          <select name="user_id" class="form-control" style="width:180px">
            <option value="all" <?= $userId==='all'?'selected':'' ?>>Semua Staf</option>
            <?php foreach ($staffList as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $userId==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <div>
          <label class="form-label">Kategori</label>
          <select name="kategori" class="form-control" style="width:150px">
            <option value="all" <?= $kategori==='all'?'selected':'' ?>>Semua</option>
            <?php foreach (['Hardware','Software','Jaringan','Server','Printer','SIMRS Khanza','Lainnya'] as $k): ?>
            <option value="<?= $k ?>" <?= $kategori===$k?'selected':'' ?>><?= $k ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">Status</label>
          <select name="status" class="form-control" style="width:140px">
            <option value="all" <?= $status==='all'?'selected':'' ?>>Semua</option>
            <?php foreach (['Selesai','Proses','Tertunda'] as $s): ?>
            <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
        <a href="rekap.php" class="btn btn-secondary">Reset</a>
      </form>
    </div>

    <div style="display:grid;grid-template-columns:1fr 280px;gap:18px;">

      <!-- TABLE -->
      <div>
        <!-- SUMMARY CARDS (admin) -->
        <?php if ($user['role']==='admin'): ?>
        <div style="display:grid;grid-template-columns:repeat(<?= min(count($summary),4) ?>,1fr);gap:12px;margin-bottom:16px;">
          <?php foreach ($summary as $idx => $s): ?>
          <div class="card" style="padding:14px;animation-delay:<?= $idx*.05 ?>s" class="fade-up">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
              <div class="mini-avatar" style="width:32px;height:32px;font-size:11px;background:<?= avatarColor($s['nama']) ?>22;color:<?= avatarColor($s['nama']) ?>">
                <?= strtoupper(substr($s['nama'],0,2)) ?>
              </div>
              <div style="font-size:12px;font-weight:700;"><?= explode(' ',$s['nama'])[0] ?></div>
            </div>
            <div style="font-size:22px;font-weight:800;font-family:'DM Mono',monospace;color:#4ade80"><?= $s['total'] ?></div>
            <div style="font-size:11px;color:var(--muted)">kegiatan</div>
            <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
              <?php if ($s['selesai']): ?><span class="badge badge-green" style="font-size:10px"><?= $s['selesai'] ?> ✓</span><?php endif; ?>
              <?php if ($s['proses']): ?><span class="badge badge-yellow" style="font-size:10px"><?= $s['proses'] ?> ↻</span><?php endif; ?>
              <?php if ($s['tertunda']): ?><span class="badge badge-red" style="font-size:10px"><?= $s['tertunda'] ?> ⏸</span><?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- DETAIL TABLE -->
        <div class="card fade-up">
          <div class="card-header">
            <div>
              <div class="card-title">📋 Detail Kegiatan</div>
              <div class="card-sub"><?= count($rows) ?> kegiatan ditemukan</div>
            </div>
          </div>

          <?php if (empty($rows)): ?>
          <div style="text-align:center;padding:40px 0;color:var(--muted);">
            <div style="font-size:36px;margin-bottom:10px">📭</div>
            <div>Tidak ada kegiatan pada periode ini</div>
          </div>
          <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Tanggal</th>
                  <?php if ($user['role']==='admin' && $userId==='all'): ?><th>Staf</th><?php endif; ?>
                  <th>Nama Kegiatan</th>
                  <th>Kategori</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rows as $i => $k): ?>
                <tr>
                  <td style="color:var(--muted);font-family:'DM Mono',monospace"><?= $i+1 ?></td>
                  <td style="white-space:nowrap">
                    <div style="font-weight:600"><?= date('d M', strtotime($k['tanggal'])) ?></div>
                    <div style="font-size:10.5px;color:var(--muted)"><?= date('Y', strtotime($k['tanggal'])) ?></div>
                  </td>
                  <?php if ($user['role']==='admin' && $userId==='all'): ?>
                  <td>
                    <div style="display:flex;align-items:center;gap:7px">
                      <div class="mini-avatar" style="background:<?= avatarColor($k['nama']) ?>22;color:<?= avatarColor($k['nama']) ?>"><?= strtoupper(substr($k['nama'],0,2)) ?></div>
                      <span style="font-size:12px;font-weight:600"><?= htmlspecialchars(explode(' ',$k['nama'])[0]) ?></span>
                    </div>
                  </td>
                  <?php endif; ?>
                  <td>
                    <div style="font-weight:600"><?= htmlspecialchars($k['nama_kegiatan']) ?></div>
                    <?php if ($k['keterangan']): ?>
                    <div style="font-size:11px;color:var(--muted);margin-top:2px"><?= htmlspecialchars(substr($k['keterangan'],0,55)).(strlen($k['keterangan'])>55?'...':'') ?></div>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge <?= kategoriColor($k['kategori']) ?>"><?= $k['kategori'] ?></span></td>
                  <td><span class="badge <?= statusColor($k['status']) ?>"><?= $k['status'] ?></span></td>
                  <td style="white-space:nowrap">
                    <?php if ($user['role']==='admin' || $k['user_id']==$user['id']): ?>
                    <a href="edit.php?id=<?= $k['id'] ?>&ref=rekap" class="btn btn-secondary btn-sm">✏️</a>
                    <a href="delete.php?id=<?= $k['id'] ?>&ref=rekap" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">🗑️</a>
                    <?php else: ?><span style="color:var(--muted)">—</span><?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- SIDEBAR STATS -->
      <div style="display:flex;flex-direction:column;gap:14px;">
        <div class="card fade-up" style="animation-delay:.15s">
          <div class="card-title" style="margin-bottom:14px">📈 Statistik Bulan Ini</div>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:var(--surface2);border-radius:8px;">
              <span style="font-size:12.5px">Total Kegiatan</span>
              <span style="font-weight:800;font-family:'DM Mono',monospace;color:#4ade80"><?= count($rows) ?></span>
            </div>
            <?php
            $selesai  = count(array_filter($rows, fn($r)=>$r['status']==='Selesai'));
            $proses   = count(array_filter($rows, fn($r)=>$r['status']==='Proses'));
            $tertunda = count(array_filter($rows, fn($r)=>$r['status']==='Tertunda'));
            ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:var(--surface2);border-radius:8px;">
              <span style="font-size:12.5px">✅ Selesai</span>
              <span style="font-weight:700;color:var(--green)"><?= $selesai ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:var(--surface2);border-radius:8px;">
              <span style="font-size:12.5px">🔄 Proses</span>
              <span style="font-weight:700;color:var(--yellow)"><?= $proses ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:var(--surface2);border-radius:8px;">
              <span style="font-size:12.5px">⏸️ Tertunda</span>
              <span style="font-weight:700;color:var(--red)"><?= $tertunda ?></span>
            </div>
            <?php if (count($rows) > 0): ?>
            <div style="margin-top:4px;">
              <div style="font-size:11px;color:var(--muted);margin-bottom:6px;">Tingkat Penyelesaian</div>
              <div style="height:8px;background:var(--surface2);border-radius:10px;overflow:hidden;">
                <div style="height:100%;width:<?= round($selesai/count($rows)*100) ?>%;background:linear-gradient(to right,#16a34a,#f97316);border-radius:10px;"></div>
              </div>
              <div style="font-size:12px;font-weight:700;color:var(--green);margin-top:5px;font-family:'DM Mono',monospace"><?= round($selesai/count($rows)*100) ?>%</div>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card fade-up" style="animation-delay:.2s">
          <div class="card-title" style="margin-bottom:14px">🏷️ Per Kategori</div>
          <?php foreach ($katStats as $k): ?>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <span class="badge <?= kategoriColor($k['kategori']) ?>" style="min-width:76px;justify-content:center"><?= $k['kategori'] ?></span>
            <div style="flex:1;height:5px;background:var(--surface2);border-radius:10px;overflow:hidden;">
              <?php $pct2 = $katStats[0]['total']>0 ? round($k['total']/$katStats[0]['total']*100) : 0; ?>
              <div style="height:100%;width:<?= $pct2 ?>%;background:linear-gradient(to right,#16a34a,#f97316);border-radius:10px;"></div>
            </div>
            <span style="font-size:12px;font-weight:700;font-family:'DM Mono',monospace;min-width:20px;text-align:right"><?= $k['total'] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</div>

</body></html>
