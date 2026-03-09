<?php
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$activePage = 'input';
$pageTitle  = 'Input Kegiatan – E-Kinerja IT';

$success = $error = '';
$kategoriList = ['Hardware','Software','Jaringan','Server','Printer','SIMRS Khanza','Lainnya'];
$statusList   = ['Selesai','Proses','Tertunda'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kegiatan = trim($_POST['nama_kegiatan'] ?? '');
    $tanggal       = $_POST['tanggal']       ?? date('Y-m-d');
    $keterangan    = trim($_POST['keterangan'] ?? '');
    $kategori      = $_POST['kategori']      ?? 'Lainnya';
    $status        = $_POST['status']        ?? 'Selesai';
    $for_user_id   = ($user['role']==='admin' && isset($_POST['user_id'])) ? (int)$_POST['user_id'] : $user['id'];

    if (empty($nama_kegiatan)) {
        $error = 'Nama kegiatan tidak boleh kosong.';
    } elseif (empty($tanggal)) {
        $error = 'Tanggal tidak boleh kosong.';
    } else {
        $stmt = $db->prepare("INSERT INTO kegiatan (user_id,tanggal,nama_kegiatan,keterangan,kategori,status) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$for_user_id, $tanggal, $nama_kegiatan, $keterangan, $kategori, $status]);
        $success = 'Kegiatan berhasil disimpan!';
    }
}

$staffList = $db->query("SELECT id,nama FROM users WHERE role='user' ORDER BY nama")->fetchAll();

include 'includes/header.php';
?>

<div class="main">
  <div class="topbar">
    <div>
      <div class="topbar-title">Input Kegiatan Harian</div>
      <div class="topbar-sub">Catat kegiatan IT hari ini</div>
    </div>
    <div class="topbar-right">
      <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
    </div>
  </div>

  <div class="content">
    <div style="max-width:680px;">

      <?php if ($success): ?>
      <div class="alert alert-success">✅ <?= $success ?>
        <a href="input.php" style="margin-left:10px;color:var(--green);font-weight:700;">+ Tambah lagi</a>
        <a href="dashboard.php" style="margin-left:10px;color:var(--green);font-weight:700;">Lihat Dashboard →</a>
      </div>
      <?php endif; ?>
      <?php if ($error): ?>
      <div class="alert alert-error">⚠️ <?= $error ?></div>
      <?php endif; ?>

      <div class="card fade-up">
        <div class="card-header">
          <div>
            <div class="card-title">📝 Form Input Kegiatan</div>
            <div class="card-sub">Isi detail kegiatan yang telah atau sedang dikerjakan</div>
          </div>
        </div>

        <form method="POST">
          <?php if ($user['role'] === 'admin'): ?>
          <div class="form-group">
            <label class="form-label">Input untuk Staf</label>
            <select name="user_id" class="form-control">
              <?php foreach ($staffList as $s): ?>
              <option value="<?= $s['id'] ?>" <?= (isset($_POST['user_id']) && $_POST['user_id']==$s['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['nama']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Tanggal Kegiatan *</label>
              <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Kategori</label>
              <select name="kategori" class="form-control">
                <?php foreach ($kategoriList as $kat): ?>
                <option value="<?= $kat ?>" <?= (($_POST['kategori'] ?? '') === $kat) ? 'selected' : '' ?>><?= $kat ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Nama Kegiatan *</label>
            <input type="text" name="nama_kegiatan" class="form-control" placeholder="Contoh: Membersihkan CPU Kantor Direktur" value="<?= htmlspecialchars($_POST['nama_kegiatan'] ?? '') ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Keterangan / Detail</label>
            <textarea name="keterangan" class="form-control" placeholder="Tambahkan detail kegiatan, hasil, atau catatan..."><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Status</label>
            <div style="display:flex;gap:10px;">
              <?php foreach ($statusList as $st): ?>
              <label style="display:flex;align-items:center;gap:7px;cursor:pointer;padding:8px 14px;border-radius:8px;border:1px solid var(--border);flex:1;justify-content:center;transition:all .15s;"
                     id="lbl_<?= $st ?>"
                     onclick="selectStatus('<?= $st ?>')">
                <input type="radio" name="status" value="<?= $st ?>" style="display:none"
                  <?= (($_POST['status'] ?? 'Selesai') === $st) ? 'checked' : '' ?>>
                <?= $st === 'Selesai' ? '✅' : ($st === 'Proses' ? '🔄' : '⏸️') ?>
                <span style="font-size:13px;font-weight:600"><?= $st ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div style="display:flex;gap:10px;margin-top:4px;">
            <button type="submit" class="btn btn-primary" style="flex:1">💾 Simpan Kegiatan</button>
            <a href="dashboard.php" class="btn btn-secondary">Batal</a>
          </div>
        </form>
      </div>

      <!-- QUICK TEMPLATES -->
      <div class="card fade-up" style="margin-top:14px;animation-delay:.1s">
        <div class="card-header" style="margin-bottom:10px">
          <div class="card-title">⚡ Template Cepat</div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
          <?php
          $templates = [
            ['Membersihkan CPU','Hardware'],['Membersihkan Printer','Printer'],
            ['Merapihkan Kabel LAN','Jaringan'],['Install Ulang Windows','Software'],
            ['Setting WiFi','Jaringan'],['Backup Data Server','Server'],
            ['Update Antivirus','Software'],['Perbaikan Proyektor','Hardware'],
            ['Ganti Switch/Hub','Jaringan'],['Troubleshoot Internet','Jaringan'],
            ['Setting IP Address','Jaringan'],['Install Driver','Software'],
          ];
          foreach ($templates as $t): ?>
          <button type="button" class="btn btn-secondary btn-sm" style="font-size:11.5px"
            onclick="fillTemplate('<?= $t[0] ?>','<?= $t[1] ?>')">
            <?= $t[0] ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
function selectStatus(val) {
  document.querySelectorAll('[id^=lbl_]').forEach(l => {
    l.style.background = '';
    l.style.borderColor = 'var(--border)';
    l.style.color = '';
  });
  const lbl = document.getElementById('lbl_' + val);
  if (lbl) {
    lbl.style.background = 'rgba(22,163,74,.15)';
    lbl.style.borderColor = '#2563eb';
    lbl.querySelector('input').checked = true;
  }
}
function fillTemplate(nama, kat) {
  document.querySelector('[name=nama_kegiatan]').value = nama;
  document.querySelector('[name=kategori]').value = kat;
}
// Init status highlight
document.addEventListener('DOMContentLoaded', () => {
  const checked = document.querySelector('[name=status]:checked');
  if (checked) selectStatus(checked.value);
});
</script>

</body></html>
