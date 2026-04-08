# Panduan Koneksi ESP32 ke Web API

Dokumen ini berisi script C++ untuk mengunggah data sensor (Kadar Air, Suhu, Kelembaban) dari mikrokontroler ESP32 ke sistem Website Monitoring yang telah dibuat.

## Persiapan
Sebelum mengunggah kode ke ESP32, pastikan Anda telah menginstal *library* berikut di Arduino IDE:
1. `WiFi.h` (Bawaan Arduino Core for ESP32)
2. `HTTPClient.h` (Bawaan Arduino Core for ESP32)
3. `ArduinoJson.h` (Instal melalui Library Manager, buatan *Benoit Blanchon*)

## Konfigurasi Penting
Karena saat ini API berjalan di server lokal (localhost laptop Anda), ESP32 **TIDAK AKAN BISA** menjangkau alamat `localhost` atau `127.0.0.1`. 

Anda harus:
1. Menghubungkan laptop dan ESP32 ke jaringan WiFi (Hotspot) yang **SAMA**.
2. Mencari tahu **IP Address lokal (IPv4)** laptop Anda (Buka CMD -> ketik `ipconfig`). Misalnya `192.168.1.10`.
3. Jalankan server PHP Anda dengan bind ke IP address tersebut, bukan localhost:
   ```bash
   php -S 192.168.1.10:8000
   ```
4. Ganti variabel `serverUrl` di kode ESP32 di bawah dengan IP tersebut: `http://192.168.1.10:8000/api/simpan_data.php`

---

## Kode Program (ESP32_Sender.ino)

```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h> // Pastikan sudah install library ArduinoJson versi 6 atau 7

// ================= PENGATURAN WIFI =================
const char* ssid = "NAMA_WIFI_ANDA";
const char* password = "PASSWORD_WIFI_ANDA";

// ================= PENGATURAN API ==================
// GANTI DENGAN IP LAPTOP ANDA (Cek via CMD -> ipconfig -> IPv4 Address)
// Jangan gunakan 'localhost' karena ESP32 memiliki localhost-nya sendiri
const char* serverUrl = "http://192.168.1.5:8000/api/simpan_data.php"; 

// Pin Sensor (Contoh dummy)
const int pinADC = 34;

void setup() {
  Serial.begin(115200);
  delay(1000);
  
  // Koneksi ke WiFi
  WiFi.begin(ssid, password);
  Serial.print("Menghubungkan ke WiFi");
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println("");
  Serial.print("Terhubung ke jaringan. IP Address ESP32: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  // Cek apakah WiFi masih terhubung
  if (WiFi.status() == WL_CONNECTED) {
    
    // 1. Membaca/Mendapatkan Data Sensor (Simulasi Data)
    int nilai_adc = analogRead(pinADC); // Nilai real dari sensor (misal sensor YL-69)
    
    // Logika perhitungan dummy untuk testing
    // Anda bisa mengganti rumus ini sesuai spesifikasi sensor Anda
    float kadar_air = random(120, 180) / 10.0; // Angka acak 12.0 - 18.0 %
    float suhu = random(250, 350) / 10.0;      // Angka acak 25.0 - 35.0 C
    float kelembaban = random(600, 850) / 10.0;// Angka acak 60.0 - 85.0 %
    
    // Menentukan Status Mutu (Aman <= 14%, Waspada 14-16%, Bahaya > 16%)
    String status = "AMAN";
    if (kadar_air > 16.0) {
      status = "BAHAYA";
    } else if (kadar_air > 14.0) {
      status = "WASPADA";
    }
    
    // 2. Membuat JSON Payload
    // Alokasi memori untuk dokumen JSON (Cukup 200 bytes untuk data kecil)
    StaticJsonDocument<200> doc;
    
    doc["adc"] = nilai_adc;
    doc["kadar_air"] = kadar_air;
    doc["suhu"] = suhu;
    doc["kelembaban"] = kelembaban;
    doc["status"] = status;
    
    String jsonString;
    serializeJson(doc, jsonString);
    
    Serial.println("=================================");
    Serial.println("Mengirim Data:");
    Serial.println(jsonString);
    
    // 3. Mengirim HTTP POST Request
    HTTPClient http;
    
    // Inisialisasi URL
    http.begin(serverUrl);
    
    // Menentukan header tipe konten
    http.addHeader("Content-Type", "application/json");
    
    // Mengirimkan JSON
    int httpResponseCode = http.POST(jsonString);
    
    // 4. Mengecek Balasan dari Server
    if (httpResponseCode > 0) {
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
      
      String responseBody = http.getString();
      Serial.println("Response: " + responseBody);
    } else {
      Serial.print("Error saat HTTP POST. Kode error: ");
      Serial.println(httpResponseCode);
      Serial.println(http.errorToString(httpResponseCode).c_str());
    }
    
    // Menutup koneksi HTTP
    http.end();
    
  } else {
    Serial.println("Koneksi WiFi Terputus!");
    // Reconnect logic bisa ditambahkan di sini
  }
  
  // Tunggu 5 detik sebelum mengirim data lagi (Sesuai dengan interval refresh dashboard)
  delay(5000);
}
```

## Solusi Jika ESP32 Gagal Terhubung (-1 Error)
1. Matikan Windows Firewall untuk sementara, atau buat rule *Inbound* untuk port 8000.
2. Pastikan laptop dan ESP32 benar-benar berada di jaringan (router/hotspot) yang sama.
3. Pastikan tidak ada spasi ekstra di variabel `serverUrl` dan IP Address yang diketik sudah benar.
4. Pastikan Anda menjalankan web server dengan spesifikasi bind IP, bukan localhost. `php -S 0.0.0.0:8000` atau `php -S <ip-anda>:8000`.