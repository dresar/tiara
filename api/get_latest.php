<?php
// api/get_latest.php

// Izinkan akses dari domain manapun (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include file koneksi ke database
require_once '../koneksi.php';

try {
    // Query untuk mengambil satu data paling terakhir/terbaru
    $query = "SELECT id, nilai_adc, kadar_air, suhu, kelembaban, status_mutu, waktu, 
                     EXTRACT(EPOCH FROM (CURRENT_TIMESTAMP - waktu)) AS diff_seconds
              FROM tb_monitoring 
              ORDER BY id DESC 
              LIMIT 1";
              
    // Mempersiapkan statement
    $stmt = $pdo->prepare($query);
    
    // Eksekusi statement
    $stmt->execute();
    
    // Mengambil satu baris data saja (fetch)
    $row = $stmt->fetch();
    
    // Mengecek apakah data ada
    if ($row) {
        // Jika data ditemukan
        http_response_code(200); // 200 OK
        echo json_encode(["status" => true, "data" => $row, "server_time" => date('Y-m-d H:i:s')]);
    } else {
        // Jika data kosong
        http_response_code(404); // 404 Not Found
        echo json_encode(["status" => false, "message" => "Data belum ada di database."]);
    }
} catch (PDOException $e) {
    // Menangkap error jika query gagal
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(["status" => false, "message" => "Error database: " . $e->getMessage()]);
}
?>