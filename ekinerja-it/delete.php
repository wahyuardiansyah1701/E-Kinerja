<?php
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$id   = (int)($_GET['id'] ?? 0);
$ref  = $_GET['ref'] ?? 'dashboard';

$stmt = $db->prepare("SELECT * FROM kegiatan WHERE id=?");
$stmt->execute([$id]);
$k = $stmt->fetch();

if ($k && ($user['role']==='admin' || $k['user_id']==$user['id'])) {
    $db->prepare("DELETE FROM kegiatan WHERE id=?")->execute([$id]);
}
header('Location: '.($ref==='rekap'?'rekap.php':'dashboard.php'));
exit;
