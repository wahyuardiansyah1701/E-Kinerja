<?php
require_once __DIR__ . '/auth.php';
$user = currentUser();
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'E-Kinerja Unit IT' ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg:#0b1a0f; --surface:#111f15; --surface2:#192b1e;
  --border:rgba(255,255,255,0.08);
  --accent:#16a34a; --accent-light:#4ade80;
  --accent2:#f97316; --accent2-light:#fb923c;
  --green:#22c55e; --yellow:#f59e0b; --red:#ef4444; --purple:#a855f7;
  --text:#e8f5e9; --muted:#5a7d62; --muted2:#93b89a;
  --sidebar-w:256px;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;}
body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(rgba(22,163,74,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(22,163,74,.025) 1px,transparent 1px);background-size:36px 36px;pointer-events:none;z-index:0;}
a{text-decoration:none;color:inherit;}
input,select,textarea{font-family:inherit;}

/* SIDEBAR */
.sidebar{width:var(--sidebar-w);background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100;}
.sidebar-logo{padding:16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:11px;background:linear-gradient(135deg,rgba(22,163,74,.1),rgba(249,115,22,.07));}
.logo-icon{width:50px;height:50px;border-radius:10px;background:linear-gradient(135deg,#16a34a,#f97316);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;box-shadow:0 4px 14px rgba(22,163,74,.3);}
.logo-text{font-size:20px;font-weight:800;line-height:1.25;color:var(--text);}
.logo-sub{padding:5px 0px 0px;font-size:9.5px;color:var(--muted2);text-transform:uppercase;letter-spacing:1.2px;margin-top:2px;}
.nav-section{padding:14px 14px 4px;font-size:9.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:1.5px;}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 14px;margin:2px 8px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:var(--muted2);transition:all .15s;position:relative;}
.nav-item:hover{background:rgba(22,163,74,.1);color:var(--text);}
.nav-item.active{background:linear-gradient(90deg,rgba(22,163,74,.18),rgba(249,115,22,.07));color:var(--accent-light);}
.nav-item.active::before{content:'';position:absolute;left:-8px;top:50%;transform:translateY(-50%);width:3px;height:65%;background:linear-gradient(to bottom,var(--accent),var(--accent2));border-radius:0 3px 3px 0;}
.nav-icon{font-size:15px;width:18px;text-align:center;}
.nav-badge{margin-left:auto;background:var(--accent2);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;}
.sidebar-footer{margin-top:auto;padding:12px 10px;border-top:1px solid var(--border);}
.user-card{display:flex;align-items:center;gap:10px;padding:9px 8px;border-radius:8px;background:rgba(22,163,74,.08);border:1px solid rgba(22,163,74,.15);}
.avatar{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;}
.user-info{flex:1;min-width:0;}
.user-name{font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.user-role{font-size:10.5px;color:var(--muted2);}

/* MAIN */
.main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh;position:relative;z-index:1;}
.topbar{height:60px;background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 24px;gap:12px;position:sticky;top:0;z-index:50;box-shadow:0 1px 12px rgba(0,0,0,.3);}
.topbar-title{font-size:15px;font-weight:700;}
.topbar-sub{font-size:11.5px;color:var(--muted2);margin-top:1px;}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:10px;}
.content{padding:22px 24px;flex:1;}

/* CARDS */
.card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:18px;transition:box-shadow .2s;}
.card:hover{box-shadow:0 4px 20px rgba(0,0,0,.25);}
.card-title{font-size:13.5px;font-weight:700;margin-bottom:4px;}
.card-sub{font-size:11.5px;color:var(--muted2);}
.card-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;}

/* STATS */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:18px 18px 16px;transition:transform .2s,box-shadow .2s;position:relative;overflow:hidden;}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.3);}
.stat-icon{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:17px;margin-bottom:12px;}
.stat-value{font-size:26px;font-weight:800;font-family:'DM Mono',monospace;line-height:1;}
.stat-label{font-size:11.5px;color:var(--muted2);margin-top:4px;}
.stat-trend{font-size:11px;margin-top:8px;font-weight:600;}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .15s;font-family:inherit;text-decoration:none;}
.btn-primary{background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;box-shadow:0 3px 10px rgba(22,163,74,.25);}
.btn-primary:hover{background:linear-gradient(135deg,#15803d,#166534);box-shadow:0 4px 14px rgba(22,163,74,.35);}
.btn-secondary{background:var(--surface2);color:var(--text);border:1px solid var(--border);}
.btn-secondary:hover{background:#243328;border-color:rgba(22,163,74,.3);}
.btn-danger{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.25);}
.btn-danger:hover{background:rgba(239,68,68,.2);}
.btn-sm{padding:5px 11px;font-size:12px;}
.btn-green{background:rgba(34,197,94,.15);color:var(--green);border:1px solid rgba(34,197,94,.3);}

/* FORM */
.form-group{margin-bottom:16px;}
.form-label{display:block;font-size:12.5px;font-weight:600;color:var(--muted2);margin-bottom:7px;}
.form-control{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:9px 13px;color:var(--text);font-size:13px;transition:all .15s;}
.form-control:focus{outline:none;border-color:var(--accent);background:rgba(22,163,74,.06);box-shadow:0 0 0 3px rgba(22,163,74,.1);}
textarea.form-control{resize:vertical;min-height:80px;}
select.form-control option{background:#192b1e;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}

/* TABLE */
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
thead th{text-align:left;padding:10px 12px;font-size:11px;color:var(--muted2);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);background:rgba(22,163,74,.04);}
tbody tr{border-bottom:1px solid var(--border);transition:background .1s;}
tbody tr:last-child{border-bottom:none;}
tbody tr:hover{background:rgba(22,163,74,.05);}
td{padding:10px 12px;font-size:12.5px;vertical-align:middle;}

/* BADGE */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;}
.badge-green{background:rgba(34,197,94,.15);color:#4ade80;}
.badge-yellow{background:rgba(245,158,11,.15);color:var(--yellow);}
.badge-red{background:rgba(239,68,68,.15);color:var(--red);}
.badge-blue{background:rgba(249,115,22,.15);color:var(--accent2-light);}
.badge-purple{background:rgba(168,85,247,.15);color:var(--purple);}
.badge-gray{background:var(--surface2);color:var(--muted2);}
.badge-orange{background:rgba(249,115,22,.15);color:var(--accent2-light);}

/* ALERT */
.alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:10px;}
.alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#4ade80;}
.alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:var(--red);}

/* MINI AVATAR */
.mini-avatar{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;}

/* SCROLLBAR */
::-webkit-scrollbar{width:5px;height:5px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:rgba(22,163,74,.3);border-radius:10px;}
::-webkit-scrollbar-thumb:hover{background:rgba(22,163,74,.5);}

@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.fade-up{animation:fadeUp .4s ease both;}

@media(max-width:768px){
  .sidebar{transform:translateX(-100%);transition:transform .3s;}
  .sidebar.open{transform:translateX(0);box-shadow:4px 0 24px rgba(0,0,0,.5);}
  .main{margin-left:0!important;}
  .stats-grid{grid-template-columns:repeat(2,1fr)!important;}
  .topbar{padding:0 14px;}
  .content{padding:14px;}
}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">
    <img src="rs.png" alt="RS Logo" style="width:60px;height:60px;border-radius:10px;object-fit:cover;flex-shrink:0;box-shadow:0 3px 10px rgba(0,0,0,.3);">
    <div>
      <div class="logo-text">E-Kinerja IT</div>
      <div class="logo-sub">RS. Kartika Husada Setu</div>
    </div>
  </div>

  <div class="nav-section">Menu</div>
  <a href="dashboard.php" class="nav-item <?= $activePage==='dashboard'?'active':'' ?>">
    <span class="nav-icon">🏠</span> Dashboard
  </a>
  <a href="input.php" class="nav-item <?= $activePage==='input'?'active':'' ?>">
    <span class="nav-icon">➕</span> Input Kegiatan
  </a>
  <a href="rekap.php" class="nav-item <?= $activePage==='rekap'?'active':'' ?>">
    <span class="nav-icon">📊</span> Rekap Bulanan
  </a>
  <a href="laporan.php" class="nav-item <?= $activePage==='laporan'?'active':'' ?>">
    <span class="nav-icon">📄</span> Laporan Cetak
  </a>

  <?php if ($user['role'] === 'admin'): ?>
  <div class="nav-section">Admin</div>
  <a href="users.php" class="nav-item <?= $activePage==='users'?'active':'' ?>">
    <span class="nav-icon">👥</span> Kelola User
  </a>
  <a href="semua_kegiatan.php" class="nav-item <?= $activePage==='semua'?'active':'' ?>">
    <span class="nav-icon">📋</span> Semua Kegiatan
  </a>
  <?php endif; ?>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="avatar" style="background:<?= avatarColor($user['nama']) ?>;color:#fff">
        <?= strtoupper(substr($user['nama'],0,2)) ?>
      </div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($user['nama']) ?></div>
        <div class="user-role"><?= $user['role'] === 'admin' ? 'Administrator' : 'Staf IT' ?></div>
      </div>
      <a href="logout.php" title="Logout" style="flex-shrink:0;opacity:.7;transition:opacity .15s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=.7">
        <img src="exit.png" alt="logout" style="width:30px;height:30px;object-fit:contain;display:block;">
      </a>
    </div>
    <div style="text-align:center;padding:7px 0 2px;font-size:9.5px;color:var(--muted);">
      © 2026 Create By. Wahyu Ardiansyah
    </div>
  </div>
</aside>

<!-- Mobile hamburger -->
<button onclick="document.querySelector('.sidebar').classList.toggle('open')" id="hamburger"
  style="display:none;position:fixed;top:12px;left:12px;z-index:200;background:var(--surface);border:1px solid var(--border);color:var(--text);width:36px;height:36px;border-radius:8px;cursor:pointer;font-size:18px;align-items:center;justify-content:center;">☰</button>
<style>@media(max-width:768px){#hamburger{display:flex!important;}}</style>

<?php
function avatarColor($name) {
    $colors = ['#16a34a','#f97316','#0ea5e9','#d97706','#7c3aed','#dc2626'];
    return $colors[ord($name[0]) % count($colors)];
}
function kategoriColor($k) {
    $map = ['Hardware'=>'blue','Software'=>'purple','Jaringan'=>'green','Server'=>'yellow','Printer'=>'red','SIMRS Khanza'=>'orange','Lainnya'=>'gray'];
    return 'badge-'.($map[$k] ?? 'gray');
}
function statusColor($s) {
    return $s === 'Selesai' ? 'badge-green' : ($s === 'Proses' ? 'badge-yellow' : 'badge-red');
}
?>
