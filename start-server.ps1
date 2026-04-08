$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$port = 8000

$ip = $null

try {
    $cfg = Get-NetIPConfiguration | Where-Object { $_.IPv4DefaultGateway -and $_.NetAdapter.Status -eq 'Up' } | Select-Object -First 1
    if ($cfg -and $cfg.IPv4Address -and $cfg.IPv4Address.IPAddress) {
        $ip = $cfg.IPv4Address.IPAddress
    }
} catch {
}

if (-not $ip) {
    $ip = Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue |
        Where-Object { $_.IPAddress -match '^(192\.168\.|10\.|172\.(1[6-9]|2\d|3[0-1])\.)' } |
        Select-Object -ExpandProperty IPAddress -First 1
}

if (-not $ip) {
    $ip = '192.168.x.x'
}

Write-Host "Folder project : $projectRoot"
Write-Host "IP laptop      : $ip"
Write-Host "URL Website    : http://$ip`:$port/"
Write-Host "URL API ESP32  : http://$ip`:$port/api/simpan_data.php"
Write-Host ""
Write-Host "Menjalankan server PHP (Ctrl+C untuk stop)..."

php -S 0.0.0.0:$port -t $projectRoot

