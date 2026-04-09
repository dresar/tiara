-- =====================================================
-- Skrip SQL untuk Database Monitoring IoT (MySQL/cPanel)
-- Jalankan skrip ini di phpMyAdmin pada cPanel Anda.
-- =====================================================

-- Pilih engine dan charset yang tepat
SET NAMES utf8mb4;

-- 1. TABEL MONITORING (Data Sensor)
CREATE TABLE IF NOT EXISTS tb_monitoring ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    nilai_adc INT, 
    kadar_air FLOAT, 
    suhu FLOAT, 
    kelembaban FLOAT, 
    status_mutu VARCHAR(20), 
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. TABEL USERS (Manajemen Pengguna)
CREATE TABLE IF NOT EXISTS tb_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    status VARCHAR(15) DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert User Default (Password: admin123 -> bcrypt hash)
INSERT IGNORE INTO tb_users (username, password, nama_lengkap, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Sistem', 'admin');

-- 3. TABEL DEVICES (Manajemen Perangkat IoT / ESP32)
CREATE TABLE IF NOT EXISTS tb_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(50) UNIQUE NOT NULL,
    nama_perangkat VARCHAR(100) NOT NULL,
    lokasi VARCHAR(150),
    status_koneksi VARCHAR(20) DEFAULT 'offline',
    last_seen TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Device Default
INSERT IGNORE INTO tb_devices (device_id, nama_perangkat, lokasi) 
VALUES ('ESP32_JAGUNG_01', 'Sensor Gudang Utama', 'Gudang Penyimpanan A');

-- 4. TABEL LOGS (Riwayat Aktivitas Sistem)
CREATE TABLE IF NOT EXISTS tb_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    aktivitas VARCHAR(255) NOT NULL,
    keterangan TEXT,
    ip_address VARCHAR(45),
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tb_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. TABEL SETTINGS (Pengaturan Batas Toleransi Sistem)
CREATE TABLE IF NOT EXISTS tb_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kunci VARCHAR(50) UNIQUE NOT NULL,
    nilai VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Setting Default
INSERT IGNORE INTO tb_settings (kunci, nilai, deskripsi) VALUES 
('batas_aman_max', '14.0', 'Batas maksimal kadar air (%) untuk status AMAN'),
('batas_waspada_max', '16.0', 'Batas maksimal kadar air (%) untuk status WASPADA'),
('interval_refresh', '5', 'Interval refresh dashboard dalam detik');
