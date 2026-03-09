<?php
require_once 'includes/auth.php';
requireAdmin();
$user = currentUser();
$db   = getDB();
$activePage = 'users';
$pageTitle  = 'Kelola User – E-Kinerja IT';

$success = $error = '';

// Add user
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='add') {
    $username = trim($_POST['username']??'');
    $nama     = trim($_POST['nama']??'');
    $password = $_POST['password']??'';
    $role     = $_POST['role']??'user';

    if (empty($username)||empty($nama)||empty($password)) { $error='Semua field wajib diisi.'; }
    else {
        try {
            $stmt = $db->prepare("INSERT INTO users (username,nama,password,role) VALUES (?,?,?,?)");
            $stmt->execute([$username,$nama,password_hash($password,PASSWORD_DEFAULT),$role]);
            $success = "User '$nama' berhasil ditambahkan.";
        } catch (Exception $e) { $error = 'Username sudah digunakan.'; }
    }
}

// Delete user
if (isset($_GET['del'])) {
    $delId = (int)$_GET['del'];
    if ($delId !== $user['id']) {
        $db->prepare("DELETE FROM users WHERE id=?")->execute([$delId]);
        $db->prepare("DELETE FROM kegiatan WHERE user_id=?")->execute([$delId]);
        $success = 'User berhasil dihapus.';
    }
}

// Reset password
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='reset') {
    $resetId = (int)$_POST['reset_id'];
    $newpass = $_POST['new_password']??'';
    if (strlen($newpass)>=6) {
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($newpass,PASSWORD_DEFAULT),$resetId]);
        $success = 'Password berhasil direset.';
    } else { $error = 'Password minimal 6 karakter.'; }
}

$users = $db->query("SELECT u.*,COUNT(k.id) as total_kegiatan FROM users u LEFT JOIN kegiatan k ON u.id=k.user_id GROUP BY u.id ORDER BY u.role DESC, u.nama")->fetchAll();

include 'includes/header.php';
?>
<div class="main">
  <div class="topbar">
    <div>
      <div class="topbar-title">👥 Kelola User</div>
      <div class="topbar-sub">Manajemen akun staf IT</div>
    </div>
  </div>
  <div class="content">

    <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error">⚠️ <?= $error ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:18px;">

      <!-- USER LIST -->
      <div class="card fade-up">
        <div class="card-header">
          <div class="card-title">Daftar Pengguna</div>
          <div class="card-sub"><?= count($users) ?> akun terdaftar</div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Pengguna</th><th>Username</th><th>Role</th><th>Kegiatan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <div class="mini-avatar" style="width:32px;height:32px;background:<?= avatarColor($u['nama']) ?>22;color:<?= avatarColor($u['nama']) ?>">
                      <?= strtoupper(substr($u['nama'],0,2)) ?>
                    </div>
                    <div>
                      <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($u['nama']) ?></div>
                      <div style="font-size:11px;color:var(--muted)"><?= date('d M Y', strtotime($u['created_at'])) ?></div>
                    </div>
                  </div>
                </td>
                <td style="font-family:'DM Mono',monospace;font-size:12.5px"><?= htmlspecialchars($u['username']) ?></td>
                <td>
                  <?php if ($u['role']==='admin'): ?>
                  <span class="badge badge-purple">👑 Admin</span>
                  <?php else: ?>
                  <span class="badge badge-blue">👤 Staf</span>
                  <?php endif; ?>
                </td>
                <td style="font-weight:700;font-family:'DM Mono',monospace"><?= $u['total_kegiatan'] ?></td>
                <td>
                  <?php if ($u['id'] !== $user['id']): ?>
                  <button class="btn btn-secondary btn-sm" onclick="showReset(<?= $u['id'] ?>,'<?= htmlspecialchars($u['nama']) ?>')">🔑</button>
                  <a href="?del=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus user ini beserta semua kegiatannya?')">🗑️</a>
                  <?php else: ?>
                  <span style="font-size:11px;color:var(--muted)">Akun Anda</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ADD USER FORM -->
      <div style="display:flex;flex-direction:column;gap:14px;">
        <div class="card fade-up" style="animation-delay:.1s">
          <div class="card-title" style="margin-bottom:14px">➕ Tambah User Baru</div>
          <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" required>
            </div>
            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" placeholder="Contoh: budi" required>
            </div>
            <div class="form-group">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
            </div>
            <div class="form-group">
              <label class="form-label">Role</label>
              <select name="role" class="form-control">
                <option value="user">👤 Staf IT</option>
                <option value="admin">👑 Administrator</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">➕ Tambah User</button>
          </form>
        </div>

        <!-- RESET PASSWORD MODAL (hidden) -->
        <div class="card fade-up" style="animation-delay:.15s" id="resetForm" style="display:none">
          <div class="card-title" style="margin-bottom:14px">🔑 Reset Password</div>
          <form method="POST">
            <input type="hidden" name="action" value="reset">
            <input type="hidden" name="reset_id" id="reset_id">
            <div class="form-group">
              <label class="form-label">User: <span id="reset_name" style="color:#4ade80"></span></label>
            </div>
            <div class="form-group">
              <label class="form-label">Password Baru</label>
              <input type="text" name="new_password" class="form-control" placeholder="Min. 6 karakter" required>
            </div>
            <div style="display:flex;gap:8px">
              <button type="submit" class="btn btn-primary" style="flex:1">💾 Simpan</button>
              <button type="button" class="btn btn-secondary" onclick="hideReset()">Batal</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>
<script>
function showReset(id,nama){
  document.getElementById('resetForm').style.display='block';
  document.getElementById('reset_id').value=id;
  document.getElementById('reset_name').textContent=nama;
  document.getElementById('resetForm').scrollIntoView({behavior:'smooth'});
}
function hideReset(){ document.getElementById('resetForm').style.display='none'; }
</script>
</body></html>
