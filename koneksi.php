<?php
// koneksi.php

/**
 * Konfigurasi Koneksi Database
 * Mendukung: PostgreSQL (Neon DB), MySQL (cPanel), dan Vercel Serverless
 * Prioritas: Environment Variables → .env file
 */

// Fungsi sederhana untuk membaca file .env (akan diskip jika file tidak ada)
function loadEnv($path) {
    if (!file_exists($path)) {
        return; // Skip jika .env tidak ada (misalnya di Vercel, env sudah di-inject)
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        $parts = explode('=', $line, 2);
        if (count($parts) < 2) continue;
        
        $name = trim($parts[0]);
        $value = trim($parts[1]);
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Coba muat file .env dari root directory (akan di-skip di Vercel)
loadEnv(__DIR__ . '/.env');

// Ambil variabel (dari env Vercel atau file .env)
$db_driver = getenv('DB_DRIVER') ?: 'pgsql';
$host      = getenv('DB_HOST');
$port      = getenv('DB_PORT');
$dbname    = getenv('DB_NAME');
$user      = getenv('DB_USER');
$password  = getenv('DB_PASS');

try {
    if ($db_driver === 'mysql') {
        // ========== MODE MYSQL (cPanel Hosting) ==========
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $password);

    } else {
        // ========== MODE POSTGRESQL (Neon DB / Vercel) ==========
        $endpoint_id = explode('.', $host)[0];
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;options='endpoint=$endpoint_id'";
        $pdo = new PDO($dsn, $user, $password);
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(["status" => false, "message" => "Koneksi database gagal."]));
}
?>