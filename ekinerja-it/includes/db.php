<?php
define('DB_PATH', __DIR__ . '/../data/ekinerja.db');

function getDB() {
    if (!file_exists(dirname(DB_PATH))) {
        mkdir(dirname(DB_PATH), 0755, true);
    }
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    initDB($db);
    return $db;
}

function initDB($db) {
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        nama TEXT NOT NULL,
        password TEXT NOT NULL,
        role TEXT DEFAULT 'user',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS kegiatan (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        tanggal DATE NOT NULL,
        nama_kegiatan TEXT NOT NULL,
        keterangan TEXT,
        kategori TEXT DEFAULT 'Lainnya',
        status TEXT DEFAULT 'Selesai',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // Seed default users if empty
    $count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count == 0) {
        $users = [
            ['admin',    'Administrator',   password_hash('admin123', PASSWORD_DEFAULT),   'admin'],
            ['budi',     'Budi Santoso',    password_hash('budi123',  PASSWORD_DEFAULT),   'user'],
            ['rina',     'Rina Marlina',    password_hash('rina123',  PASSWORD_DEFAULT),   'user'],
            ['dedi',     'Dedi Kurniawan',  password_hash('dedi123',  PASSWORD_DEFAULT),   'user'],
            ['ayu',      'Ayu Fitriani',    password_hash('ayu123',   PASSWORD_DEFAULT),   'user'],
        ];
        $stmt = $db->prepare("INSERT INTO users (username,nama,password,role) VALUES (?,?,?,?)");
        foreach ($users as $u) $stmt->execute($u);

        // Seed sample data
        $today = date('Y-m-d');
        $month = date('Y-m');
        $sampleData = [
            [2, $today,                      'Membersihkan CPU Kantor Direktur',      'Debu tebal, sudah dibersihkan',           'Hardware',   'Selesai'],
            [2, $today,                      'Membersihkan Printer Farmasi',          'Cartridge diganti, test print OK',        'Printer',    'Selesai'],
            [2, $today,                      'Merapihkan Kabel LAN Poliklinik',       'Kabel di-label dan diikat rapi',          'Jaringan',   'Selesai'],
            [3, $today,                      'Install ulang Windows PC IGD',          'Windows 11 bersih, driver lengkap',       'Software',   'Selesai'],
            [3, $today,                      'Setting WiFi Ruang Rapat',              'SSID baru, password diperbarui',          'Jaringan',   'Selesai'],
            [4, $today,                      'Backup data server harian',             'Backup 120GB ke NAS berhasil',            'Server',     'Selesai'],
            [4, date('Y-m-d', strtotime('-1 day')), 'Perbaikan proyektor Aula',      'Kabel HDMI diganti, gambar normal',       'Hardware',   'Selesai'],
            [5, $today,                      'Update antivirus 20 unit PC',           'Definisi terbaru terinstall semua',       'Software',   'Proses'],
            [2, date('Y-m-d', strtotime('-2 day')), 'Ganti switch Poli Gigi',        'Switch lama rusak, diganti unit baru',    'Jaringan',   'Selesai'],
            [3, date('Y-m-d', strtotime('-3 day')), 'Troubleshoot email server',     'Queue email stuck, sudah diperbaiki',     'Server',     'Selesai'],
        ];
        $stmt = $db->prepare("INSERT INTO kegiatan (user_id,tanggal,nama_kegiatan,keterangan,kategori,status) VALUES (?,?,?,?,?,?)");
        foreach ($sampleData as $d) $stmt->execute($d);
    }
}
?>
