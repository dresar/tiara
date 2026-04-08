<?php
// api/get_data.php

// Izinkan akses dari domain manapun (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include file koneksi ke database
require_once '../koneksi.php';

try {
    // Query untuk mengambil data
    // Untuk performa dan grafik yang rapi, kita ambil maksimal 50 data terakhir,
    // lalu diurutkan ascending (waktu terlama ke terbaru)
    $query = "SELECT * FROM (
                  SELECT id, nilai_adc, kadar_air, suhu, kelembaban, status_mutu, waktu 
                  FROM tb_monitoring 
                  ORDER BY id DESC 
                  LIMIT 50
              ) sub 
              ORDER BY id ASC";
              
    // Mempersiapkan statement
    $stmt = $pdo->prepare($query);
    
    // Eksekusi statement
    $stmt->execute();
    
    // Mengambil semua baris data
    $data = $stmt->fetchAll();
    
    // Mengecek apakah data ada
    if (count($data) > 0) {
        // Jika data ditemukan
        http_response_code(200); // 200 OK
        echo json_encode(["status" => true, "data" => $data]);
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