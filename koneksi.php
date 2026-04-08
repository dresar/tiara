<?php
// koneksi.php

/**
 * Konfigurasi Koneksi Database PostgreSQL (Neon DB)
 * Membaca dari file .env agar kredensial lebih aman
 */

// Fungsi sederhana untuk membaca file .env
function loadEnv($path) {
    if (!file_exists($path)) {
        die("File .env tidak ditemukan. Buat file .env terlebih dahulu.");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Lewati komentar
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key=value
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Muat file .env dari root directory
loadEnv(__DIR__ . '/.env');

// Ambil variabel dari .env
$host     = getenv('DB_HOST');
$port     = getenv('DB_PORT');
$dbname   = getenv('DB_NAME');
$user     = getenv('DB_USER');
$password = getenv('DB_PASS');

try {
    // DSN (Data Source Name) untuk PostgreSQL
    // sslmode=require sangat direkomendasikan untuk koneksi ke cloud database seperti Neon
    // channel_binding=require sesuai dengan parameter URL Neon Anda
    // endpoint parameter diperlukan untuk Neon (SNI) menggunakan libpq versi lama
    $endpoint_id = explode('.', $host)[0];
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;options='endpoint=$endpoint_id'";
    
    // Membuat instance PDO baru
    $pdo = new PDO($dsn, $user, $password);
    
    // Mengatur PDO error mode menjadi exception agar mudah di-debug
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mengatur fetch mode default menjadi associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Jika gagal, hentikan eksekusi dan tampilkan pesan error
    die("Koneksi database gagal: " . $e->getMessage());
}
?>