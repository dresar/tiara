<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Kadar Air Jagung | IoT Dashboard</title>
    
    <!-- Bootstrap CSS (Lokal) -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons (via CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Alert Banner (Akan muncul jika status BAHAYA) -->
    <div id="alertBanner"></div>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-sidebar border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-white fw-bold py-4 px-3 fs-5">
                <i class="bi bi-cpu text-primary me-2"></i> AgroIoT System
            </div>
            <div class="list-group list-group-flush mt-2">
                <a href="#" class="list-group-item list-group-item-action sidebar-item active">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="#" class="list-group-item list-group-item-action sidebar-item">
                    <i class="bi bi-bar-chart-line me-2"></i> Riwayat Data
                </a>
                <a href="#" class="list-group-item list-group-item-action sidebar-item">
                    <i class="bi bi-wifi me-2"></i> Tes Koneksi Perangkat
                </a>
                <a href="#" class="list-group-item list-group-item-action sidebar-item">
                    <i class="bi bi-router me-2"></i> Koneksi & Dokumentasi
                </a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper" class="w-100 bg-light">
            <!-- Navbar Atas -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center">
                    <button class="btn btn-primary me-3 d-lg-none" id="menu-toggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <h4 class="m-0 fw-bold text-dark d-none d-sm-block" id="pageTitle">Dashboard</h4>
                </div>
                
                <div class="ms-auto d-flex align-items-center">
                    <span class="badge bg-primary px-3 py-2 rounded-pill me-3 d-none d-md-inline">
                        <i class="bi bi-circle-fill me-1 text-success" style="font-size: 0.5rem; vertical-align: middle;"></i> Live Connection
                    </span>
                    <div class="text-muted d-none d-md-block" style="font-size: 0.8rem;" id="lastUpdate">Loading data...</div>
                </div>
            </nav>

            <!-- Main Content Container -->
            <div class="container-fluid px-4 py-4">
                
                <!-- ID pembungkus untuk SPA Dashboard -->
                <div id="mainDashboardContent">
                    <!-- Welcome Banner -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-4" style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="fw-bold mb-1">Selamat Datang di Dashboard AgroIoT! 👋</h3>
                                        <p class="mb-0 opacity-75">Pantau kualitas hasil panen jagung secara real-time dari seluruh perangkat.</p>
                                    </div>
                                    <i class="bi bi-cloud-check display-1 opacity-25 d-none d-md-block"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cards Section (Data Terbaru) -->
                    <div class="row g-4 mb-4">
                        
                        <!-- Card Kadar Air -->
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card-stat">
                                <i class="bi bi-droplet-half icon text-primary"></i>
                                <h3>Kadar Air</h3>
                                <div class="d-flex align-items-baseline">
                                    <span class="value" id="valKadarAir">--</span>
                                    <span class="unit ms-1">%</span>
                                </div>
                                <div class="mt-2 text-muted" style="font-size: 0.8rem;">Target Aman: ≤14%</div>
                            </div>
                        </div>

                        <!-- Card Suhu -->
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card-stat">
                                <i class="bi bi-thermometer-half icon text-danger"></i>
                                <h3>Suhu Ruangan</h3>
                                <div class="d-flex align-items-baseline">
                                    <span class="value" id="valSuhu">--</span>
                                    <span class="unit ms-1">°C</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Kelembaban -->
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card-stat">
                                <i class="bi bi-cloud-haze2 icon text-success"></i>
                                <h3>Kelembaban</h3>
                                <div class="d-flex align-items-baseline">
                                    <span class="value" id="valKelembaban">--</span>
                                    <span class="unit ms-1">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Status Mutu -->
                        <div class="col-12 col-sm-6 col-xl-3">
                            <!-- ID cardStatus ini yang akan dimanipulasi class CSS-nya oleh JavaScript (status-aman/waspada/bahaya) -->
                            <div class="card-stat" id="cardStatus">
                                <i class="bi bi-shield-check icon"></i>
                                <h3>Status Mutu</h3>
                                <div class="d-flex align-items-baseline">
                                    <span class="value" id="valStatus">--</span>
                                </div>
                                <div class="mt-2 text-muted" style="font-size: 0.8rem;">Kondisi saat ini</div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section (Grafik Historis) -->
                    <div class="row g-4">
                        
                        <!-- Grafik Kadar Air -->
                        <div class="col-12 col-lg-6">
                            <div class="chart-container h-100">
                                <h3 class="chart-title"><i class="bi bi-graph-up me-2 text-primary"></i>Grafik Historis Kadar Air</h3>
                                <div style="height: 300px; position: relative;">
                                    <!-- Canvas untuk Chart.js -->
                                    <canvas id="chartKadarAir"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Grafik Suhu dan Kelembaban -->
                        <div class="col-12 col-lg-6">
                            <div class="chart-container h-100">
                                <h3 class="chart-title"><i class="bi bi-thermometer-sun me-2 text-danger"></i>Suhu & Kelembaban Ruangan</h3>
                                <div style="height: 300px; position: relative;">
                                    <!-- Canvas untuk Chart.js -->
                                    <canvas id="chartSuhuKelembaban"></canvas>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- End of mainDashboardContent -->

                <!-- Halaman Dokumentasi ESP32 -->
                <div id="docsContent" style="display: none;">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h3 class="fw-bold mb-4 text-primary"><i class="bi bi-journal-code me-2"></i>Panduan Koneksi ESP32 ke Web API</h3>
                        
                        <div class="alert alert-info border-0 rounded-3">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Dokumen ini berisi instruksi dan script C++ untuk mengunggah data sensor (Kadar Air, Suhu, Kelembaban) dari mikrokontroler ESP32 ke sistem Website Monitoring ini.
                        </div>

                        <h5 class="fw-bold mt-4">1. Persiapan Library</h5>
                        <p>Sebelum mengunggah kode ke ESP32, pastikan Anda telah menginstal <em>library</em> berikut di Arduino IDE:</p>
                        <ul>
                            <li><code>WiFi.h</code> (Bawaan Arduino Core for ESP32)</li>
                            <li><code>HTTPClient.h</code> (Bawaan Arduino Core for ESP32)</li>
                            <li><code>ArduinoJson.h</code> (Instal via Library Manager)</li>
                            <li><code>Wire.h</code> (Bawaan)</li>
                            <li><code>LiquidCrystal_I2C.h</code> (Instal via Library Manager)</li>
                            <li><code>DHT.h</code> (Instal via Library Manager)</li>
                        </ul>

                        <h5 class="fw-bold mt-4">2. Konfigurasi Penting</h5>
                        <p>Karena API berjalan di server lokal laptop Anda, ESP32 <strong>TIDAK AKAN BISA</strong> menjangkau alamat <code>localhost</code>. Ikuti langkah ini:</p>
                        <ol>
                            <li>Hubungkan laptop dan ESP32 ke jaringan WiFi (Hotspot) yang <strong>SAMA</strong>.</li>
                            <li>Cari tahu <strong>IP Address lokal (IPv4)</strong> laptop Anda (Buka CMD -> ketik <code>ipconfig</code>). Misalnya <code>192.168.1.10</code>.</li>
                            <li>Jalankan server PHP agar bisa diakses dari HP/ESP32 (bind ke semua interface):<br>
                                <code class="bg-light p-1 rounded">php -S 0.0.0.0:8000 -t c:\Users\eka\Downloads\tiara</code>
                            </li>
                            <li>Pastikan URL API yang dipakai ESP32 seperti ini (contoh dari halaman yang sedang Anda buka):<br>
                                <code class="bg-light p-1 rounded">http://<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? '192.168.x.x:8000') ?>/api/simpan_data.php</code>
                            </li>
                        </ol>

                        <h5 class="fw-bold mt-4">3. Source Code (ESP32_Sender.ino)</h5>
                        <div class="bg-dark text-light p-3 rounded-3 mt-2 overflow-auto" style="max-height: 400px;">
<pre><code class="language-cpp">#include &lt;WiFi.h&gt;
#include &lt;HTTPClient.h&gt;
#include &lt;ArduinoJson.h&gt;
#include &lt;Wire.h&gt;
#include &lt;LiquidCrystal_I2C.h&gt;
#include "DHT.h"
 
// WIFI
const char* ssid     = "eka";
const char* password = "1234567890";
 
// SERVER (GANTI IP SESUAI LAPTOP)
const char* serverUrl = "http://IP_LAPTOP:8000/api/simpan_data.php";
 
// SENSOR
#define DHTPIN 27
#define DHTTYPE DHT22
 
DHT dht(DHTPIN, DHTTYPE);
LiquidCrystal_I2C lcd(0x27, 16, 2);
 
const int soilPin = 34;
 
// KALIBRASI
int dryValue = 2559;
int wetValue = 1110;
 
// TIME
unsigned long lastSend = 0;
const unsigned long sendInterval = 5000;
 
// ================= WIFI =================
void konekWiFi() {
  WiFi.begin(ssid, password);
 
  lcd.clear();
  lcd.setCursor(0,0);
  lcd.print("Connecting WiFi");
 
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
 
  lcd.clear();
  lcd.setCursor(0,0);
  lcd.print("WiFi Connected");
  delay(1000);
}
 
// ================= SENSOR =================
int readADCavg(int pin) {
  long total = 0;
  for (int i = 0; i &lt; 10; i++) {
    total += analogRead(pin);
    delay(10);
  }
  return total / 10;
}
 
float readMoisturePercent(int adc) {
  float moisture = map(adc, dryValue, wetValue, 0, 100);
  return constrain(moisture, 0, 100);
}
 
String getStatusMutu(float kadar_air) {
  if (kadar_air &lt;= 14.0) return "AMAN";
  else if (kadar_air &lt;= 16.0) return "WASPADA";
  else return "BAHAYA";
}
 
// ================= LCD =================
void tampilLCD(float kadar_air, float suhu, float kelembaban, String status) {
  lcd.clear();
 
  lcd.setCursor(0,0);
  lcd.print("M:");
  lcd.print(kadar_air,1);
  lcd.print("% ");
  lcd.print(status[0]);
 
  lcd.setCursor(0,1);
  lcd.print("T:");
  lcd.print(suhu,1);
  lcd.print(" H:");
  lcd.print(kelembaban,0);
}
 
// ================= KIRIM =================
void kirimKeServer(int adc, float kadar_air, float suhu, float kelembaban, String status) {
 
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi putus!");
    return;
  }
 
  StaticJsonDocument&lt;256&gt; doc;
  doc["adc"] = adc;
  doc["kadar_air"] = kadar_air;
  doc["suhu"] = suhu;
  doc["kelembaban"] = kelembaban;
  doc["status"] = status;
 
  String json;
  serializeJson(doc, json);
 
  HTTPClient http;
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");
 
  int code = http.POST(json);
 
  if (code &gt; 0) {
    Serial.print("Sukses kirim: ");
    Serial.println(code);
  } else {
    Serial.print("Error: ");
    Serial.println(code);
  }
 
  http.end();
}
 
// ================= SETUP =================
void setup() {
  Serial.begin(115200);
 
  lcd.init();
  lcd.backlight();
 
  dht.begin();
 
  analogReadResolution(12);
 
  konekWiFi();
}
 
// ================= LOOP =================
void loop() {
 
  if (WiFi.status() != WL_CONNECTED) {
    konekWiFi();
  }
 
  if (millis() - lastSend &gt;= sendInterval) {
    lastSend = millis();
 
    int adc = readADCavg(soilPin);
    float kadar_air = readMoisturePercent(adc);
 
    float suhu = dht.readTemperature();
    float kelembaban = dht.readHumidity();
 
    if (isnan(suhu)) suhu = 0;
    if (isnan(kelembaban)) kelembaban = 0;
 
    String status = getStatusMutu(kadar_air);
 
    // SERIAL
    Serial.println("===== DATA =====");
    Serial.println(adc);
    Serial.println(kadar_air);
 
    // LCD (FOCUS MONITORING)
    tampilLCD(kadar_air, suhu, kelembaban, status);
 
    // KIRIM
    kirimKeServer(adc, kadar_air, suhu, kelembaban, status);
  }
}
</code></pre>
                        </div>

                        <h5 class="fw-bold mt-4">4. Solusi Jika Gagal Terhubung (-1 Error)</h5>
                        <ul>
                            <li>Matikan Windows Firewall untuk sementara, atau buat rule <em>Inbound</em> untuk port 8000.</li>
                            <li>Pastikan laptop dan ESP32 benar-benar berada di jaringan (router/hotspot) yang sama.</li>
                        </ul>
                    </div>
                </div>

                <!-- Halaman Riwayat Data -->
                <div id="historyContent" style="display: none;">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-table me-2 text-primary"></i>Tabel Riwayat Data</h3>
                            <button class="btn btn-outline-primary btn-sm" onclick="fetchAllData()"><i class="bi bi-arrow-clockwise me-1"></i> Refresh</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Nilai ADC</th>
                                        <th>Kadar Air (%)</th>
                                        <th>Suhu (°C)</th>
                                        <th>Kelembaban (%)</th>
                                        <th>Status Mutu</th>
                                    </tr>
                                </thead>
                                <tbody id="tableHistoryBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Memuat data riwayat...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Halaman Tes Koneksi Perangkat -->
                <div id="testDeviceContent" style="display: none;">
                    <div class="row">
                        <div class="col-lg-6 mx-auto">
                            <div class="card border-0 shadow-sm rounded-4 p-4">
                                <h3 class="fw-bold mb-3 text-dark"><i class="bi bi-wifi me-2 text-primary"></i>Simulator Pengiriman Data</h3>
                                <p class="text-muted mb-4">Gunakan alat ini untuk mensimulasikan pengiriman data HTTP POST dari ESP32 ke sistem untuk mengetes apakah API dan Dashboard berjalan lancar.</p>
                                
                                <form id="formSimulasi">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nilai ADC (Simulasi)</label>
                                        <input type="number" class="form-control" id="simAdc" value="2150" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Kadar Air (%)</label>
                                            <input type="number" step="0.1" class="form-control" id="simKadar" value="14.5" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Status Mutu</label>
                                            <select class="form-select" id="simStatus">
                                                <option value="AMAN">AMAN (≤ 14%)</option>
                                                <option value="WASPADA" selected>WASPADA (14.1 - 16%)</option>
                                                <option value="BAHAYA">BAHAYA (> 16%)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-bold">Suhu Ruangan (°C)</label>
                                            <input type="number" step="0.1" class="form-control" id="simSuhu" value="28.5" required>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-bold">Kelembaban (%)</label>
                                            <input type="number" step="0.1" class="form-control" id="simKelembaban" value="65.0" required>
                                        </div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg" id="btnKirimSimulasi">
                                            <i class="bi bi-send-fill me-2"></i>Kirim Data Sekarang
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="mt-4" id="simulasiResponse" style="display: none;">
                                    <h6 class="fw-bold">Response Server:</h6>
                                    <pre class="bg-dark text-success p-3 rounded-3" id="simulasiLog"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Halaman Kosong (Untuk Simulasi Menu Lain SPA) -->
                <div id="dummyContent" style="display: none; min-height: 60vh;" class="text-center pt-5">
                    <i class="bi bi-tools text-muted opacity-25" style="font-size: 5rem;"></i>
                    <h2 class="mt-3 text-muted" id="dummyTitle">Halaman Dalam Perbaikan</h2>
                    <p class="text-muted">Modul ini sedang dalam tahap pengembangan (Under Construction).</p>
                    <button class="btn btn-outline-primary mt-3" onclick="document.querySelector('.sidebar-item').click()">Kembali ke Dashboard</button>
                </div>

                <!-- Footer -->
                <footer class="mt-5 text-center text-muted" style="font-size: 0.85rem;">
                    &copy; 2026 Sistem Monitoring IoT. Semua hak cipta dilindungi.
                </footer>

            </div>
            <!-- /#page-content-wrapper -->
        </div>
        </div>
    </div>

    <!-- Bootstrap JS (Lokal) -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js (Lokal) -->
    <script src="assets/js/chart.min.js"></script>
    
    <!-- Custom JS untuk fetch data dan render grafik -->
    <script src="assets/js/main.js"></script>
</body>
</html>
