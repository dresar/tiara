<?php
require_once 'koneksi.php';

$users = [
    ['admin', 'admin123', 'Administrator Sistem', 'admin'],
    ['petani', 'petani123', 'Petani Jagung', 'petani']
];

foreach ($users as $u) {
    $hash = password_hash($u[1], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO tb_users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?) ON CONFLICT (username) DO UPDATE SET password = EXCLUDED.password");
    $stmt->execute([$u[0], $hash, $u[2], $u[3]]);
}
echo "Berhasil mengatur user admin dan petani.";
?>
