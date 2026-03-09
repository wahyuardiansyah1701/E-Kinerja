<?php
require_once 'includes/auth.php';
startSession();
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: dashboard.php'); exit;
    }
    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login – E-Kinerja Unit IT</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<style>
:root{--bg:#0b1a0f;--surface:#111f15;--surface2:#192b1e;--border:rgba(255,255,255,0.08);--accent:#16a34a;--accent2:#f97316;--text:#e8f5e9;--muted:#5a7d62;--muted2:#93b89a;--green:#22c55e;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;}
body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(rgba(22,163,74,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(22,163,74,.03) 1px,transparent 1px);background-size:40px 40px;pointer-events:none;}
/* Decorative blobs */
body::after{content:'';position:fixed;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(22,163,74,.07),transparent 70%);top:-100px;right:-100px;pointer-events:none;}
.login-box{width:100%;max-width:420px;padding:15px;position:relative;z-index:1;}
.brand{text-align:center;margin-bottom:10px;}
.brand-name{font-size:19px;font-weight:800;margin-top:2px;}
.brand-sub{font-size:12.5px;color:var(--muted2);margin-top:2px;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,.4);}
.card-top-bar{height:3px;background:linear-gradient(to right,var(--accent),var(--accent2));border-radius:16px 16px 0 0;margin:-28px -28px 24px;}
.form-group{margin-bottom:16px;}
.form-label{display:block;font-size:12.5px;font-weight:600;color:var(--muted2);margin-bottom:7px;}
.form-control{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:10px 14px;color:var(--text);font-size:13.5px;font-family:inherit;transition:all .15s;}
.form-control:focus{outline:none;border-color:var(--accent);background:rgba(22,163,74,.06);box-shadow:0 0 0 3px rgba(22,163,74,.1);}
.btn{width:100%;padding:11px;background:linear-gradient(135deg,var(--accent),#15803d);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s;margin-top:6px;box-shadow:0 4px 14px rgba(22,163,74,.3);}
.btn:hover{background:linear-gradient(135deg,#15803d,#166534);box-shadow:0 6px 18px rgba(22,163,74,.4);}
.alert{padding:11px 14px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#ef4444;border-radius:8px;font-size:13px;margin-bottom:16px;}
.demo-accounts{margin-top:20px;padding-top:18px;border-top:1px solid var(--border);}
.demo-title{font-size:11.5px;color:var(--muted2);margin-bottom:10px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;}
.demo-list{display:flex;flex-direction:column;gap:6px;}
.demo-item{display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--surface2);border-radius:8px;font-size:12px;cursor:pointer;transition:all .15s;border:1px solid transparent;}
.demo-item:hover{background:#243328;border-color:rgba(22,163,74,.3);}
.demo-user{font-weight:600;display:flex;align-items:center;gap:6px;}
.demo-pass{color:var(--muted2);font-family:'DM Mono',monospace;letter-spacing:2px;}
</style>
</head>
<body>
<div class="login-box">
  <div class="brand">
        <img src="rs.png" alt="logo" width="100">
    <div class="brand-name">E-Kinerja Information Technology</div>
    <div class="brand-sub">Sistem Pencatatan Kegiatan Harian</div>
  </div>

  <div class="card">
    <div class="card-top-bar"></div>
    <?php if ($error): ?>
    <div class="alert">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
      </div>
      <button type="submit" class="btn">🔐 Masuk</button>
    </form>


    <div class="demo-accounts">
      <div class="demo-title">Silahkan Pilih User Login</div>
      <div class="demo-list">
        <div class="demo-item" onclick="fillLogin('admin','admin123')">
          <span class="demo-user">👑 admin</span><span class="demo-pass">*******</span>
        </div>
        <div class="demo-item" onclick="fillLogin('wahyu','jangandiingat')">
          <span class="demo-user">👤 wahyu</span><span class="demo-pass">*****</span>
        </div>
        <div class="demo-item" onclick="fillLogin('silvi','silvi')">
          <span class="demo-user">👤 silvi</span><span class="demo-pass">*****</span>
        </div>
      </div>
    </div>
  </div>

      <div style="text-align:center; padding: 8px 0 20px; font-size: 12px; color: var(--green); margin-top: 2px;">
  © 2026 Create By. Wahyu Ardiansyah.
</div>
</div>

<script>
function fillLogin(u, p) {
  document.querySelector('[name=username]').value = u;
  document.querySelector('[name=password]').value = p;
}
</script>
</body>
</html>
