-- Skrip SQL untuk Database Monitoring IoT (PostgreSQL)
-- Eksekusi skrip ini di SQL Editor pada Neon Database Anda.

-- 1. TABEL MONITORING (Data Sensor)
CREATE TABLE IF NOT EXISTS tb_monitoring ( 
    id SERIAL PRIMARY KEY, 
    nilai_adc INTEGER, 
    kadar_air FLOAT, 
    suhu FLOAT, 
    kelembaban FLOAT, 
    status_mutu VARCHAR(20), 
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
);

-- 2. TABEL USERS (Manajemen Pengguna)
CREATE TABLE IF NOT EXISTS tb_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin', -- admin, operator, viewer
    status VARCHAR(15) DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP
);

-- Insert User Default (Password: admin123 -> di-hash dengan BCRYPT nantinya, ini sekadar contoh)
-- Note: Di aplikasi nyata, pastikan password disimpan menggunakan password_hash() dari PHP.
INSERT INTO tb_users (username, password, nama_lengkap, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Sistem', 'admin')
ON CONFLICT (username) DO NOTHING;

-- 3. TABEL DEVICES (Manajemen Perangkat IoT / ESP32)
CREATE TABLE IF NOT EXISTS tb_devices (
    id SERIAL PRIMARY KEY,
    device_id VARCHAR(50) UNIQUE NOT NULL,
    nama_perangkat VARCHAR(100) NOT NULL,
    lokasi VARCHAR(150),
    status_koneksi VARCHAR(20) DEFAULT 'offline', -- online / offline
    last_seen TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Device Default
INSERT INTO tb_devices (device_id, nama_perangkat, lokasi) 
VALUES ('ESP32_JAGUNG_01', 'Sensor Gudang Utama', 'Gudang Penyimpanan A')
ON CONFLICT (device_id) DO NOTHING;

-- 4. TABEL LOGS (Riwayat Aktivitas Sistem)
CREATE TABLE IF NOT EXISTS tb_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES tb_users(id) ON DELETE SET NULL,
    aktivitas VARCHAR(255) NOT NULL,
    keterangan TEXT,
    ip_address VARCHAR(45),
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. TABEL SETTINGS (Pengaturan Batas Toleransi Sistem)
CREATE TABLE IF NOT EXISTS tb_settings (
    id SERIAL PRIMARY KEY,
    kunci VARCHAR(50) UNIQUE NOT NULL,
    nilai VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Setting Default
INSERT INTO tb_settings (kunci, nilai, deskripsi) VALUES 
('batas_aman_max', '14.0', 'Batas maksimal kadar air (%) untuk status AMAN'),
('batas_waspada_max', '16.0', 'Batas maksimal kadar air (%) untuk status WASPADA'),
('interval_refresh', '5', 'Interval refresh dashboard dalam detik')
ON CONFLICT (kunci) DO NOTHING;