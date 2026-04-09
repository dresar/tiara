@echo off
echo ==============================================================
echo  Menjalankan Cloudflare Tunnel untuk Sistem Monitoring AgroIoT
echo ==============================================================
echo.
echo Pastikan Anda sudah menjalankan PHP server di port 8000 denga perintah:
echo php -S 0.0.0.0:8000
echo.
echo Sedang meminta link publik dari Cloudflare...
echo.
echo Silakan copy link berawalan "https://" (tengah-tengah tulisan yang muncul)
echo dan masukkan ke dalam variabel serverUrl di source code ESP32 Anda.
echo ==============================================================
echo.

cloudflared tunnel --url http://localhost:8000
