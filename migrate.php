<?php
// migrate.php
echo "--------------------------------------------------\n";
echo "1. Mencoba menghubungkan ke Neon PostgreSQL...\n";
echo "--------------------------------------------------\n";

// Menggunakan koneksi.php untuk menghubungkan ke database
require_once __DIR__ . '/koneksi.php';

if ($pdo) {
    echo "=> Status: KONEKSI BERHASIL!\n\n";
} else {
    die("=> Status: KONEKSI GAGAL!\n");
}

echo "--------------------------------------------------\n";
echo "2. Membaca skrip SQL (database.sql)...\n";
echo "--------------------------------------------------\n";
$sql = file_get_contents(__DIR__ . '/database.sql');

if ($sql === false) {
    die("=> Error: File database.sql tidak ditemukan atau tidak bisa dibaca.\n");
}
echo "=> File database.sql berhasil dibaca.\n\n";

echo "--------------------------------------------------\n";
echo "3. Menjalankan Migrasi (Membuat Tabel & Data Awal)...\n";
echo "--------------------------------------------------\n";

try {
    // Eksekusi semua perintah SQL sekaligus
    $pdo->exec($sql);
    
    echo "==================================================\n";
    echo "               MIGRASI SUKSES!                    \n";
    echo "  Semua tabel dan data awal berhasil dibuat di DB.\n";
    echo "==================================================\n";
} catch (PDOException $e) {
    echo "\n=> ERROR MIGRASI: " . $e->getMessage() . "\n";
    echo "Pastikan struktur SQL benar dan pengguna memiliki hak akses untuk membuat tabel.\n";
}
?>