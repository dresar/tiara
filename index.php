<?php
require_once 'auth.php';
check_login();
$role = $_SESSION['role'] ?? 'petani';
$nama = $_SESSION['nama_lengkap'] ?? 'User';
?>
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

                <?php if (has_role('admin')): ?>
                <a href="#" class="list-group-item list-group-item-action sidebar-item">
                    <i class="bi bi-router me-2"></i> Koneksi & Dokumentasi
                </a>
                <?php endif; ?>
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
                    <span class="badge bg-secondary px-3 py-2 rounded-pill me-3 d-none d-md-inline" id="connectionBadge">
                        <i class="bi bi-circle-fill me-1 text-danger" style="font-size: 0.5rem; vertical-align: middle;" id="connectionIcon"></i> <span id="connectionText">ESP32 Offline</span>
                    </span>
                    <div class="text-muted d-none d-md-block me-4" style="font-size: 0.8rem;" id="lastUpdate">Loading data...</div>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle bg-transparent border-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 text-primary align-middle me-1"></i> 
                            <span class="fw-bold fs-6 align-middle"><?= htmlspecialchars($nama) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="dropdownMenuButton1">
                            <li><h6 class="dropdown-header">Role: <span class="badge bg-primary text-uppercase"><?= htmlspecialchars($role) ?></span></h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger fw-bold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </div>
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
                <?php if (has_role('admin')): ?>
                <div id="docsContent" style="display: none;">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <h3 class="fw-bold mb-2 text-primary"><i class="bi bi-mortarboard me-2"></i>Tentang Sistem</h3>
                        <p class="text-muted mb-3">Sistem ini dikembangkan sebagai bagian dari Proposal Skripsi:</p>
                        <div class="alert alert-primary border-0 rounded-3 mb-3">
                            <h5 class="fw-bold mb-1"><i class="bi bi-journal-bookmark-fill me-2"></i>PERANCANGAN SISTEM PENGUKUR KADAR AIR BIJI JAGUNG MENGGUNAKAN SENSOR KELEMBABAN BERBASIS INTERNET OF THINGS</h5>
                            <p class="mb-1">Oleh: <strong>Tiara Dwi Nazra</strong> (2209020163)</p>
                            <p class="mb-0">Program Studi Teknologi Informasi &mdash; Fakultas Ilmu Komputer dan Teknologi Informasi<br>Universitas Muhammadiyah Sumatera Utara &mdash; 2026</p>
                        </div>

                        <h5 class="fw-bold mt-4"><i class="bi bi-clipboard-data me-2 text-success"></i>Standar Mutu Jagung (SNI 8926:2020)</h5>
                        <p>Sistem monitoring mengacu pada <strong>SNI 8926:2020</strong> dari Badan Standardisasi Nasional (BSN) untuk klasifikasi status mutu biji jagung berdasarkan kadar air:</p>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-hover align-middle text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Status</th>
                                        <th>Kadar Air</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-info">
                                        <td><span class="badge bg-info text-dark">SANGAT KERING</span></td>
                                        <td>&lt; 8%</td>
                                        <td>Jagung sangat kering, siap simpan jangka panjang</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-secondary">KERING</span></td>
                                        <td>8% &ndash; &lt;10%</td>
                                        <td>Jagung kering, kualitas baik</td>
                                    </tr>
                                    <tr class="table-success">
                                        <td><span class="badge bg-success">AMAN</span></td>
                                        <td>&le;14% (Mutu I Premium)</td>
                                        <td>Memenuhi SNI 8926:2020 Mutu I &mdash; aman disimpan &amp; dijual</td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td><span class="badge bg-warning text-dark">WASPADA</span></td>
                                        <td>14.1% &ndash; 16%</td>
                                        <td>Mendekati batas bahaya, perlu pengeringan segera</td>
                                    </tr>
                                    <tr class="table-danger">
                                        <td><span class="badge bg-danger">BAHAYA</span></td>
                                        <td>&gt;16%</td>
                                        <td>Risiko tinggi jamur Aspergillus flavus &amp; aflatoksin karsinogenik</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="fw-bold mt-4"><i class="bi bi-cpu me-2 text-primary"></i>Arsitektur Perangkat Keras</h5>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Komponen</th>
                                        <th>Tipe</th>
                                        <th>Fungsi</th>
                                        <th>Pin ESP32</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><i class="bi bi-cpu-fill text-primary me-1"></i> Mikrokontroler</td>
                                        <td>ESP32 DevKit v1</td>
                                        <td>Otak sistem, Wi-Fi, ADC 12-bit, Dual-Core 240MHz</td>
                                        <td>&mdash;</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-moisture text-info me-1"></i> Sensor Kadar Air</td>
                                        <td>Capacitive Soil Moisture v1.2</td>
                                        <td>Mengukur konstanta dielektrik biji jagung (&epsilon;<sub>r</sub> air &asymp; 80)</td>
                                        <td>GPIO 32 (ADC1_CH4)</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-thermometer-half text-danger me-1"></i> Sensor Suhu &amp; RH</td>
                                        <td>DHT22 (AM2302)</td>
                                        <td>Monitoring suhu &amp; kelembaban udara lingkungan</td>
                                        <td>GPIO 27</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-display text-success me-1"></i> Display Lokal</td>
                                        <td>LCD 16x2 I2C (0x27)</td>
                                        <td>Menampilkan data langsung tanpa perlu koneksi internet</td>
                                        <td>GPIO 21 (SDA), GPIO 22 (SCL)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="fw-bold mt-4"><i class="bi bi-calculator me-2 text-warning"></i>Metode Kalibrasi: Regresi Linear</h5>
                        <div class="alert alert-warning border-0 rounded-3">
                            <p class="mb-1">Sensor kapasitif dikalibrasi menggunakan metode <strong>Regresi Linear</strong> dengan rumus:</p>
                            <h4 class="fw-bold text-center my-3 font-monospace">Y = aX + b &rarr; kadar_air = (-0.0157 &times; ADC) + 45.4</h4>
                            <p class="mb-0"><small>Dimana <strong>a = -0.0157</strong> dan <strong>b = 45.4</strong> merupakan koefisien regresi linear hasil kalibrasi pembacaan ADC terhadap nilai rujukan Grain Moisture Meter / Metode Oven Gravimetri (SNI 7947:2013). Hasil kalibrasi diharapkan memiliki R&sup2; &gt; 0.95 dengan error &lt; 2-5%.</small></p>
                        </div>
                    </div>

                    <!-- Card Source Code -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <h3 class="fw-bold mb-4 text-primary"><i class="bi bi-journal-code me-2"></i>Source Code Firmware ESP32</h3>
                        
                        <div class="alert alert-info border-0 rounded-3">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Kode berikut merupakan firmware final yang berjalan di mikrokontroler ESP32. Firmware ini membaca sensor kapasitif &amp; DHT22, menampilkan ke LCD 16x2, lalu mengirim data via <strong>HTTPS POST JSON</strong> ke server dashboard ini setiap <strong>5 detik</strong>.
                        </div>

                        <h5 class="fw-bold mt-3">1. Library yang Dibutuhkan</h5>
                        <p>Instal library berikut di <strong>Arduino IDE</strong> (Tools &rarr; Manage Libraries):</p>
                        <ul>
                            <li><code>WiFi.h</code> &amp; <code>WiFiClientSecure.h</code> (Bawaan ESP32 Core)</li>
                            <li><code>HTTPClient.h</code> (Bawaan ESP32 Core)</li>
                            <li><code>ArduinoJson.h</code> &mdash; Instal via Library Manager (v6.x)</li>
                            <li><code>Wire.h</code> (Bawaan, untuk I2C)</li>
                            <li><code>LiquidCrystal_I2C.h</code> &mdash; Instal via Library Manager</li>
                            <li><code>DHT.h</code> &mdash; Instal via Library Manager (Adafruit DHT Sensor Library)</li>
                        </ul>

                        <h5 class="fw-bold mt-4">2. Konfigurasi Koneksi</h5>
                        <div class="alert alert-warning border-0 rounded-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Penting!</strong> Ubah variabel <code>ssid</code>, <code>password</code>, dan <code>serverUrl</code> sesuai jaringan WiFi dan alamat server Anda.
                        </div>
                        <ul>
                            <li><strong>Jika server lokal (laptop):</strong> Hubungkan ESP32 &amp; laptop ke WiFi yang <strong>SAMA</strong>. Gunakan IP lokal laptop (cek via <code>ipconfig</code>):<br>
                                <code class="bg-light p-1 rounded">http://<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? '192.168.x.x:8000') ?>/api/simpan_data.php</code>
                            </li>
                            <li><strong>Jika server online (Cloudflare Tunnel / VPS):</strong> Gunakan URL HTTPS seperti pada kode di bawah (menggunakan <code>WiFiClientSecure</code>).</li>
                        </ul>

                        <h5 class="fw-bold mt-4">3. Source Code Lengkap (<code>ESP32_KadarAirJagung.ino</code>)</h5>
                        <div class="bg-dark text-light p-3 rounded-3 mt-2 overflow-auto" style="max-height: 500px;">
<pre><code class="language-cpp">#include &lt;WiFi.h&gt;
#include &lt;HTTPClient.h&gt;
#include &lt;ArduinoJson.h&gt;
#include &lt;Wire.h&gt;
#include &lt;LiquidCrystal_I2C.h&gt;
#include "DHT.h"
#include &lt;WiFiClientSecure.h&gt;

// ================= WIFI =================
const char* ssid     = "eka";
const char* password = "1234567890";

// ================= SERVER =================
const char* serverUrl = "https://trailer-occupational-omissions-occurs.trycloudflare.com/api/simpan_data.php";

// ================= PIN =================
#define DHTPIN 27
#define DHTTYPE DHT22
const int soilPin = 32;

// ================= OBJECT =================
DHT dht(DHTPIN, DHTTYPE);
LiquidCrystal_I2C lcd(0x27, 16, 2);

// ================= KALIBRASI (Regresi Linear) =================
// Y = aX + b (Hasil kalibrasi)
float a = -0.0157;
float b = 45.4;

// ================= TIME =================
unsigned long lastSend = 0;
const unsigned long sendInterval = 5000;

// ================= WIFI =================
void konekWiFi() {
  WiFi.begin(ssid, password);

  lcd.clear();
  lcd.setCursor(0,0);
  lcd.print("Connecting WiFi");

  int retry = 0;
  while (WiFi.status() != WL_CONNECTED &amp;&amp; retry &lt; 20) {
    delay(500);
    Serial.print(".");
    retry++;
  }

  lcd.clear();
  if (WiFi.status() == WL_CONNECTED) {
    lcd.print("WiFi Connected");
  } else {
    lcd.print("WiFi Failed");
  }
  delay(1000);
}

// ================= FILTER ADC (Median Filter) =================
int readADCstable(int pin) {
  int readings[10];

  for (int i = 0; i &lt; 10; i++) {
    readings[i] = analogRead(pin);
    delay(5);
  }

  // Sorting (Bubble Sort)
  for (int i = 0; i &lt; 9; i++) {
    for (int j = i + 1; j &lt; 10; j++) {
      if (readings[j] &lt; readings[i]) {
        int t = readings[i];
        readings[i] = readings[j];
        readings[j] = t;
      }
    }
  }

  // Trimmed Mean (buang 2 terkecil &amp; 2 terbesar)
  long total = 0;
  for (int i = 2; i &lt; 8; i++) {
    total += readings[i];
  }

  return total / 6;
}

// ================= DHT STABIL (Retry 3x) =================
bool readDHT(float &amp;suhu, float &amp;hum) {
  for (int i = 0; i &lt; 3; i++) {
    suhu = dht.readTemperature();
    hum  = dht.readHumidity();

    if (!isnan(suhu) &amp;&amp; !isnan(hum)) return true;

    delay(2000);
  }
  return false;
}

// ================= KONVERSI ADC -&gt; KADAR AIR =================
float hitungKadar(int adc) {
  float kadar = (a * adc) + b;
  return constrain(kadar, 0, 100);
}

// ================= STATUS SNI 8926:2020 =================
String statusJagung(float kadar) {
  if (kadar &lt; 8) return "S.KERING";
  else if (kadar &lt; 10) return "KERING";
  else if (kadar &lt;= 14) return "AMAN";
  else if (kadar &lt;= 16) return "WASPADA";
  else return "BAHAYA";
}

// ================= LCD =================
void tampilLCD(float kadar, float suhu, float hum, String status) {
  lcd.setCursor(0,0);
  lcd.print("K:");
  lcd.print(kadar,1);
  lcd.print("% ");
  lcd.print(status);
  lcd.print("   ");

  lcd.setCursor(0,1);
  lcd.print("T:");
  lcd.print(suhu,1);
  lcd.print(" H:");
  lcd.print(hum,0);
  lcd.print("   ");
}

// ================= KIRIM KE SERVER =================
void kirimKeServer(int adc, float kadar, float suhu, float hum, String status) {

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi putus!");
    return;
  }

  StaticJsonDocument&lt;256&gt; doc;
  doc["adc"] = adc;
  doc["kadar_air"] = kadar;
  doc["suhu"] = suhu;
  doc["kelembaban"] = hum;
  doc["status"] = status;

  String json;
  serializeJson(doc, json);

  WiFiClientSecure client;
  client.setInsecure(); // Skip SSL cert verification

  HTTPClient http;
  http.begin(client, serverUrl);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(5000);

  int code = http.POST(json);

  Serial.print("HTTP Code: ");
  Serial.println(code);

  http.end();
}

// ================= SETUP =================
void setup() {
  Serial.begin(115200);

  Wire.begin(21, 22); // I2C SDA=21, SCL=22

  lcd.init();
  lcd.backlight();

  dht.begin();

  analogReadResolution(12); // ADC 12-bit (0-4095)

  konekWiFi();
}

// ================= LOOP =================
void loop() {

  if (WiFi.status() != WL_CONNECTED) {
    konekWiFi();
  }

  if (millis() - lastSend &gt;= sendInterval) {
    lastSend = millis();

    int adc = readADCstable(soilPin);

    // Proteksi sensor error (ADC terlalu rendah)
    if (adc &lt; 500) {
      Serial.println("SENSOR ERROR!");
      return;
    }

    float suhu, hum;
    bool ok = readDHT(suhu, hum);

    if (!ok) {
      Serial.println("DHT ERROR!");
      suhu = 25;
      hum = 60;
    }

    float kadar = hitungKadar(adc);
    String status = statusJagung(kadar);

    // SERIAL MONITOR
    Serial.println("===== DATA =====");
    Serial.print("ADC: "); Serial.println(adc);
    Serial.print("Kadar: "); Serial.println(kadar);
    Serial.print("Suhu: "); Serial.println(suhu);
    Serial.print("RH: "); Serial.println(hum);

    // LCD
    tampilLCD(kadar, suhu, hum, status);

    // KIRIM KE SERVER
    kirimKeServer(adc, kadar, suhu, hum, status);
  }
}</code></pre>
                        </div>
                    </div>

                    <!-- Card Alur Data -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <h3 class="fw-bold mb-4 text-primary"><i class="bi bi-diagram-3 me-2"></i>Alur Data Sistem</h3>
                        <div class="row g-3 text-center">
                            <div class="col-md-3 col-6">
                                <div class="p-3 bg-light rounded-4">
                                    <i class="bi bi-moisture display-5 text-info"></i>
                                    <p class="fw-bold mt-2 mb-0">Sensor Kapasitif</p>
                                    <small class="text-muted">ADC 12-bit &rarr; GPIO 32</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 bg-light rounded-4">
                                    <i class="bi bi-cpu display-5 text-primary"></i>
                                    <p class="fw-bold mt-2 mb-0">ESP32</p>
                                    <small class="text-muted">Kalibrasi Y=aX+b</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 bg-light rounded-4">
                                    <i class="bi bi-cloud-arrow-up display-5 text-success"></i>
                                    <p class="fw-bold mt-2 mb-0">HTTP POST JSON</p>
                                    <small class="text-muted">WiFi &rarr; Server PHP</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 bg-light rounded-4">
                                    <i class="bi bi-speedometer2 display-5 text-danger"></i>
                                    <p class="fw-bold mt-2 mb-0">Dashboard Web</p>
                                    <small class="text-muted">Real-time + Historis</small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-light rounded-3">
                            <h6 class="fw-bold"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Format JSON yang Dikirim ESP32</h6>
<pre class="bg-dark text-success p-3 rounded-3 mb-0"><code>{
  "adc": 2150,
  "kadar_air": 11.6,
  "suhu": 28.5,
  "kelembaban": 65.0,
  "status": "AMAN"
}</code></pre>
                        </div>
                    </div>

                    <!-- Card Troubleshooting -->
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h3 class="fw-bold mb-4 text-danger"><i class="bi bi-tools me-2"></i>Troubleshooting</h3>
                        <div class="accordion" id="troubleAccordion">
                            <div class="accordion-item border-0 mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button bg-light rounded-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#ts1">
                                        <i class="bi bi-wifi-off text-danger me-2"></i> WiFi Gagal Terhubung
                                    </button>
                                </h2>
                                <div id="ts1" class="accordion-collapse collapse show" data-bs-parent="#troubleAccordion">
                                    <div class="accordion-body">
                                        <ul class="mb-0">
                                            <li>Pastikan SSID dan Password WiFi benar (case-sensitive).</li>
                                            <li>ESP32 dan Laptop/Server harus terhubung ke jaringan <strong>yang SAMA</strong>.</li>
                                            <li>Jika menggunakan hotspot HP, pastikan frekuensi WiFi di <strong>2.4 GHz</strong> (ESP32 tidak mendukung 5 GHz).</li>
                                            <li>Restart ESP32 jika WiFi terus gagal setelah 20 percobaan (timeout otomatis).</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-light rounded-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#ts2">
                                        <i class="bi bi-exclamation-diamond text-warning me-2"></i> HTTP Error Code -1
                                    </button>
                                </h2>
                                <div id="ts2" class="accordion-collapse collapse" data-bs-parent="#troubleAccordion">
                                    <div class="accordion-body">
                                        <ul class="mb-0">
                                            <li>ESP32 tidak bisa menjangkau server. Cek apakah <code>serverUrl</code> sudah benar.</li>
                                            <li>Jika pakai <strong>server lokal</strong>: Matikan Windows Firewall sementara atau buat rule <em>Inbound</em> untuk port 8000. Jalankan PHP dengan bind: <code>php -S 0.0.0.0:8000</code>.</li>
                                            <li>Jika pakai <strong>Cloudflare Tunnel</strong>: Pastikan tunnel masih aktif dan URL belum berubah.</li>
                                            <li>Cek <code>Serial Monitor</code> Arduino IDE (baudrate 115200) untuk melihat kode error detail.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-light rounded-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#ts3">
                                        <i class="bi bi-bug text-danger me-2"></i> SENSOR ERROR / DHT ERROR di Serial Monitor
                                    </button>
                                </h2>
                                <div id="ts3" class="accordion-collapse collapse" data-bs-parent="#troubleAccordion">
                                    <div class="accordion-body">
                                        <ul class="mb-0">
                                            <li><strong>SENSOR ERROR:</strong> ADC membaca nilai &lt; 500. Cek kabel sensor kapasitif ke GPIO 32. Pastikan sensor tertancap di biji jagung, bukan di udara kosong.</li>
                                            <li><strong>DHT ERROR:</strong> Sensor DHT22 gagal dibaca 3x berturut-turut. Cek kabel data di GPIO 27 dan resistor pull-up 10K&Omega;.</li>
                                            <li>Pastikan koneksi kabel tidak longgar (terutama pada breadboard).</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-light rounded-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#ts4">
                                        <i class="bi bi-graph-down text-info me-2"></i> Nilai Kadar Air Tidak Akurat
                                    </button>
                                </h2>
                                <div id="ts4" class="accordion-collapse collapse" data-bs-parent="#troubleAccordion">
                                    <div class="accordion-body">
                                        <ul class="mb-0">
                                            <li>Kalibrasi ulang koefisien regresi <code>a</code> dan <code>b</code> menggunakan data pembanding dari Grain Moisture Meter atau metode oven (SNI 7947:2013).</li>
                                            <li>Pastikan biji jagung dalam wadah padat dan merata (tidak ada celah udara besar).</li>
                                            <li>Suhu lingkungan mempengaruhi konstanta dielektrik (2-4% per &deg;C). Data DHT22 dapat digunakan sebagai parameter koreksi.</li>
                                            <li>Gunakan rentang ADC tengah (500-3500) untuk menghindari non-linearitas ADC ESP32 di ujung bawah dan atas.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>




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
