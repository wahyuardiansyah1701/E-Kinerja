<?php
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$activePage = 'input';
$pageTitle  = 'Edit Kegiatan – E-Kinerja IT';

$id  = (int)($_GET['id'] ?? 0);
$ref = $_GET['ref'] ?? 'dashboard';
$stmt = $db->prepare("SELECT * FROM kegiatan WHERE id=?");
$stmt->execute([$id]);
$k = $stmt->fetch();

if (!$k || ($user['role']!=='admin' && $k['user_id']!=$user['id'])) {
    header('Location: dashboard.php'); exit;
}

$success = $error = '';
$kategoriList = ['Hardware','Software','Jaringan','Server','Printer','SIMRS Khanza','Lainnya'];
$statusList   = ['Selesai','Proses','Tertunda'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kegiatan = trim($_POST['nama_kegiatan'] ?? '');
    $tanggal       = $_POST['tanggal']    ?? $k['tanggal'];
    $keterangan    = trim($_POST['keterangan'] ?? '');
    $kategori      = $_POST['kategori']   ?? $k['kategori'];
    $status        = $_POST['status']     ?? $k['status'];

    if (empty($nama_kegiatan)) { $error = 'Nama kegiatan tidak boleh kosong.'; }
    else {
        $stmt = $db->prepare("UPDATE kegiatan SET tanggal=?,nama_kegiatan=?,keterangan=?,kategori=?,status=? WHERE id=?");
        $stmt->execute([$tanggal,$nama_kegiatan,$keterangan,$kategori,$status,$id]);
        $k = array_merge($k,['tanggal'=>$tanggal,'nama_kegiatan'=>$nama_kegiatan,'keterangan'=>$keterangan,'kategori'=>$kategori,'status'=>$status]);
        $success = 'Kegiatan berhasil diperbarui!';
    }
}

include 'includes/header.php';
?>
<div class="main">
  <div class="topbar">
    <div>
      <div class="topbar-title">✏️ Edit Kegiatan</div>
      <div class="topbar-sub">ID #<?= $id ?></div>
    </div>
    <div class="topbar-right">
      <a href="<?= $ref==='rekap'?'rekap.php':'dashboard.php' ?>" class="btn btn-secondary">← Kembali</a>
    </div>
  </div>
  <div class="content">
    <div style="max-width:640px">
      <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>
      <?php if ($error):   ?><div class="alert alert-error">⚠️ <?= $error ?></div><?php endif; ?>
      <div class="card fade-up">
        <div class="card-header">
          <div class="card-title">Edit Kegiatan</div>
        </div>
        <form method="POST">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Tanggal</label>
              <input type="date" name="tanggal" class="form-control" value="<?= $k['tanggal'] ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Kategori</label>
              <select name="kategori" class="form-control">
                <?php foreach ($kategoriList as $kat): ?>
                <option value="<?= $kat ?>" <?= $k['kategori']===$kat?'selected':'' ?>><?= $kat ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Nama Kegiatan</label>
            <input type="text" name="nama_kegiatan" class="form-control" value="<?= htmlspecialchars($k['nama_kegiatan']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control"><?= htmlspecialchars($k['keterangan']) ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <div style="display:flex;gap:10px;">
              <?php foreach ($statusList as $st): ?>
              <label style="display:flex;align-items:center;gap:7px;cursor:pointer;padding:8px 14px;border-radius:8px;border:1px solid var(--border);flex:1;justify-content:center;" id="lbl_<?= $st ?>" onclick="selectStatus('<?= $st ?>')">
                <input type="radio" name="status" value="<?= $st ?>" style="display:none" <?= $k['status']===$st?'checked':'' ?>>
                <?= $st==='Selesai'?'✅':($st==='Proses'?'🔄':'⏸️') ?>
                <span style="font-size:13px;font-weight:600"><?= $st ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary" style="flex:1">💾 Update</button>
            <a href="<?= $ref==='rekap'?'rekap.php':'dashboard.php' ?>" class="btn btn-secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
function selectStatus(val) {
  document.querySelectorAll('[id^=lbl_]').forEach(l=>{l.style.background='';l.style.borderColor='var(--border)';});
  const lbl = document.getElementById('lbl_'+val);
  if(lbl){lbl.style.background='rgba(22,163,74,.15)';lbl.style.borderColor='#16a34a';lbl.querySelector('input').checked=true;}
}
document.addEventListener('DOMContentLoaded',()=>{const c=document.querySelector('[name=status]:checked');if(c)selectStatus(c.value);});
</script>
</body></html>
