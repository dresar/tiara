<?php
// api/simpan_data.php

// Izinkan akses dari domain manapun (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Include file koneksi ke database
require_once '../koneksi.php';

// Mendapatkan data JSON dari request body ESP32
$rawBody = file_get_contents("php://input");
$data = json_decode($rawBody);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "JSON tidak valid."]);
    exit;
}

// Validasi jika JSON valid dan tidak kosong
if (
    isset($data->adc, $data->kadar_air, $data->suhu, $data->kelembaban, $data->status) &&
    is_numeric($data->adc) &&
    is_numeric($data->kadar_air) &&
    is_numeric($data->suhu) &&
    is_numeric($data->kelembaban) &&
    trim((string)$data->status) !== ''
) {
    // Sanitasi input sebagai langkah pencegahan tambahan
    $adc = (int)$data->adc;
    $kadar_air = (float)$data->kadar_air;
    $suhu = (float)$data->suhu;
    $kelembaban = (float)$data->kelembaban;
    $status = strtoupper(trim((string)$data->status));
    $allowedStatus = ['AMAN', 'WASPADA', 'BAHAYA'];
    if (!in_array($status, $allowedStatus, true)) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Status tidak valid. Gunakan: AMAN / WASPADA / BAHAYA."]);
        exit;
    }

    try {
        // Query untuk menyimpan data
        // Menggunakan Prepared Statement untuk menghindari SQL Injection
        $query = "INSERT INTO tb_monitoring (nilai_adc, kadar_air, suhu, kelembaban, status_mutu) 
                  VALUES (:adc, :kadar_air, :suhu, :kelembaban, :status)";
        
        // Mempersiapkan query
        $stmt = $pdo->prepare($query);
        
        // Mengikat nilai parameter
        $stmt->bindParam(':adc', $adc, PDO::PARAM_INT);
        $stmt->bindParam(':kadar_air', $kadar_air);
        $stmt->bindParam(':suhu', $suhu);
        $stmt->bindParam(':kelembaban', $kelembaban);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        
        // Eksekusi statement
        if ($stmt->execute()) {
            // Ambil ID yang baru dimasukkan
            $insertedId = $pdo->lastInsertId();
            
            // Ambil data utuh yang barusan masuk (termasuk generate CURRENT_TIMESTAMP nya)
            $stmtLast = $pdo->prepare("SELECT * FROM tb_monitoring WHERE id = :id");
            $stmtLast->bindParam(':id', $insertedId, PDO::PARAM_INT);
            $stmtLast->execute();
            $newRow = $stmtLast->fetch(PDO::FETCH_ASSOC);
            
            if ($newRow) {
                // Panggil Node.js websocket server secara internal (Opsional, hanya jika Node.js berjalan)
                // Di cPanel shared hosting, fitur ini TIDAK akan aktif (dan itu normal).
                // Dashboard tetap berjalan via HTTP Polling otomatis tiap 5 detik.
                if (function_exists('curl_init') && getenv('WS_ENABLED') !== 'false') {
                    $ch = curl_init('http://localhost:8080/broadcast');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($newRow));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
                    @curl_exec($ch); // @ = suppress error jika Node.js tidak ada
                    curl_close($ch);
                }
            }

            // Jika berhasil
            http_response_code(201); // 201 Created
            echo json_encode(["status" => true, "message" => "Data berhasil disimpan."]);
        } else {
            // Jika gagal eksekusi
            http_response_code(503); // 503 Service Unavailable
            echo json_encode(["status" => false, "message" => "Gagal menyimpan data."]);
        }
    } catch (PDOException $e) {
        // Menangkap error database
        http_response_code(500); // 500 Internal Server Error
        echo json_encode(["status" => false, "message" => "Error database: " . $e->getMessage()]);
    }
} else {
    // Jika data JSON yang dikirim tidak lengkap atau kosong
    http_response_code(400); // 400 Bad Request
    echo json_encode(["status" => false, "message" => "Data tidak lengkap. Pastikan mengirim format JSON yang sesuai."]);
}
?>
