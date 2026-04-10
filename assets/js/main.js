/**
 * assets/js/main.js
 * Logika Frontend untuk Fetch Data dan Rendering Chart
 */

// Konfigurasi Endpoint API
// Kita kembali ke relative path paling aman karena built-in server PHP terkadang gagal membaca basePath origin yang benar
const API_BASE_URL = 'api/';
const REFRESH_INTERVAL = 2000; // 2 detik (Lebih cepat tanpa WebSocket)

// Variabel untuk menyimpan instance Chart.js
let chartKadarAir = null;
let chartSuhuKelembaban = null;
let lastSeenId = null; // Menyimpan ID data terakhir

// Event ketika DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi Chart kosong terlebih dahulu
    initCharts();
    
    // Tarik data pertama kali penuh beserta riwayat
    fetchData();
    
    // Polling HTTP tiap REFRESH_INTERVAL untuk update data dari server PHP
    setInterval(fetchLatestData, REFRESH_INTERVAL);

    // Sidebar Toggle Logic
    const toggleButton = document.getElementById('menu-toggle');
    const sidebarWrapper = document.getElementById('sidebar-wrapper');

    const updateToggleIcon = () => {
        if (!toggleButton) return;
        const isOpen = document.body.classList.contains('toggled');
        toggleButton.innerHTML = isOpen ? '<i class="bi bi-x-lg"></i>' : '<i class="bi bi-list"></i>';
    };

    if (toggleButton) {
        toggleButton.addEventListener('click', (e) => {
            e.preventDefault();
            document.body.classList.toggle('toggled');
            updateToggleIcon();
            
            // Re-render chart agar menyesuaikan ukuran container yang baru
            setTimeout(() => {
                if(chartKadarAir) chartKadarAir.resize();
                if(chartSuhuKelembaban) chartSuhuKelembaban.resize();
            }, 300);
        });
    }

    document.addEventListener('click', (e) => {
        if (window.innerWidth >= 992) return;
        if (!document.body.classList.contains('toggled')) return;

        const clickedInsideSidebar = sidebarWrapper ? sidebarWrapper.contains(e.target) : false;
        const clickedToggle = toggleButton ? toggleButton.contains(e.target) : false;

        if (!clickedInsideSidebar && !clickedToggle) {
            document.body.classList.remove('toggled');
            updateToggleIcon();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            document.body.classList.remove('toggled');
        }
        updateToggleIcon();
    });

    updateToggleIcon();

    // Logika Klik Menu Sidebar Aktif (Simulasi Single Page Application / SPA)
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    const pageTitle = document.getElementById('pageTitle');
    
    // Ambil semua kontainer halaman
    const mainDashboardContent = document.getElementById('mainDashboardContent');
    const docsContent = document.getElementById('docsContent');
    const historyContent = document.getElementById('historyContent');
    const dummyContent = document.getElementById('dummyContent');
    const dummyTitle = document.getElementById('dummyTitle');

    sidebarItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Hapus class active dari semua item
            sidebarItems.forEach(i => i.classList.remove('active'));
            
            // Tambahkan class active ke item yang di klik
            this.classList.add('active');

            // Ambil nama menu dari teks di dalamnya
            const menuName = this.innerText.trim();

            // Ganti Judul Halaman di Navbar
            if(pageTitle) pageTitle.innerText = menuName;

            // Sembunyikan semua konten terlebih dahulu
            if(mainDashboardContent) mainDashboardContent.style.display = 'none';
            if(docsContent) docsContent.style.display = 'none';
            if(historyContent) historyContent.style.display = 'none';
            if(dummyContent) dummyContent.style.display = 'none';

            // Logika pergantian tampilan (SPA) tanpa loading
            if(menuName === "Dashboard") {
                if(mainDashboardContent) mainDashboardContent.style.display = 'block';
                // Re-render chart setelah display block
                setTimeout(() => {
                    if(chartKadarAir) chartKadarAir.resize();
                    if(chartSuhuKelembaban) chartSuhuKelembaban.resize();
                }, 100);
            } else if(menuName === "Koneksi & Dokumentasi") {
                if(docsContent) docsContent.style.display = 'block';
            } else if(menuName === "Riwayat Data") {
                if(historyContent) historyContent.style.display = 'block';
                fetchAllData(); // Tarik data terbaru saat membuka halaman ini
            } else {
                if(dummyContent) dummyContent.style.display = 'block';
                if(dummyTitle) dummyTitle.innerText = "Halaman " + menuName;
            }

            // Otomatis tutup sidebar di mobile setelah klik menu
            if(window.innerWidth < 992) {
                document.body.classList.remove('toggled');
                updateToggleIcon();
            }
        });
    });

});

/**
 * Fungsi utama untuk mengambil semua data (Terbaru & Historis)
 */
function fetchData() {
    fetchLatestData();
    fetchAllData();
}

/**
 * Mengambil 1 data terbaru untuk diisi ke Card
 */
function fetchLatestData() {
    fetch(API_BASE_URL + 'get_latest.php')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(res => {
            if(res.status && res.data) {
                updateCards(res.data);
                checkAlert(res.data.status_mutu);
                
                // Cek status ESP32 (Online/Offline) menggunakan diff_seconds langsung dari database
                checkEspStatus(parseFloat(res.data.diff_seconds));
                
                // Update waktu terakhir diperbarui
                const lastUpdateEl = document.getElementById('lastUpdate');
                if(lastUpdateEl) {
                    lastUpdateEl.innerText = "Terakhir diperbarui: " + formatWaktuIndo(res.data.waktu);
                }

                // Tambahkan data ke Grafik/Tabel jika ada data baru masuk
                if (lastSeenId !== null && res.data.id !== lastSeenId) {
                    appendChartData(res.data);
                    prependTableHistory(res.data);
                }
                lastSeenId = res.data.id;
            }
        })
        .catch(error => {
            console.error('Error fetching latest data:', error);
            // Paksa set Offline jika error ambil API
            const badge = document.getElementById('connectionBadge');
            const icon = document.getElementById('connectionIcon');
            const text = document.getElementById('connectionText');
            if (badge && icon && text) {
                badge.className = 'badge bg-secondary px-3 py-2 rounded-pill me-3 d-none d-md-inline';
                icon.className = 'bi bi-circle-fill me-1 text-danger';
                text.innerText = 'ESP32 Offline';
            }
        });
}

/**
 * Mengambil semua data untuk diisi ke Grafik Historis
 */
function fetchAllData() {
    fetch(API_BASE_URL + 'get_data.php')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(res => {
            if(res.status && res.data) {
                updateCharts(res.data);
                updateHistoryTable(res.data);
            }
        })
        .catch(error => {
            console.error('Error fetching historical data:', error);
            
            const tableBody = document.getElementById('tableHistoryBody');
            if(tableBody) {
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Gagal mengambil data riwayat.</td></tr>`;
            }
        });
}

/**
 * Memperbarui tabel riwayat data HTML
 */
function updateHistoryTable(dataArray) {
    const tableBody = document.getElementById('tableHistoryBody');
    if(!tableBody) return;
    
    if(dataArray.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">Belum ada data tersedia.</td></tr>`;
        return;
    }
    
    let html = '';
    
    // Looping data dari yang terbaru (karena dataArray diurutkan ASC dari backend, kita balik urutannya dengan reverse())
    const reversedData = [...dataArray].reverse();
    
    reversedData.forEach(item => {
        // Tentukan warna badge berdasarkan status
        let badgeClass = 'bg-success';
        let statusText = item.status_mutu.toUpperCase();
        
        if(statusText === 'WASPADA') badgeClass = 'bg-warning text-dark';
        if(statusText === 'BAHAYA') badgeClass = 'bg-danger';
        
        html += `
            <tr>
                <td><i class="bi bi-clock me-2 text-muted"></i>${formatWaktuIndo(item.waktu)}</td>
                <td class="fw-bold">${item.nilai_adc}</td>
                <td>${parseFloat(item.kadar_air).toFixed(1)}%</td>
                <td>${parseFloat(item.suhu).toFixed(1)}°C</td>
                <td>${parseFloat(item.kelembaban).toFixed(1)}%</td>
                <td><span class="badge ${badgeClass}">${statusText}</span></td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
}

/**
 * Memperbarui nilai pada elemen Card HTML
 */
function updateCards(data) {
    // Ambil elemen HTML
    const elKadar = document.getElementById('valKadarAir');
    const elSuhu = document.getElementById('valSuhu');
    const elKelembaban = document.getElementById('valKelembaban');
    const elStatus = document.getElementById('valStatus');
    
    // Ambil elemen kontainer status untuk styling
    const cardStatus = document.getElementById('cardStatus');
    
    // Update teks nilai
    if(elKadar) elKadar.innerText = parseFloat(data.kadar_air).toFixed(1);
    if(elSuhu) elSuhu.innerText = parseFloat(data.suhu).toFixed(1);
    if(elKelembaban) elKelembaban.innerText = parseFloat(data.kelembaban).toFixed(1);
    
    // Update Status Mutu dan warnanya
    if(elStatus && cardStatus) {
        let status = data.status_mutu.toUpperCase();
        elStatus.innerText = status;
        
        // Reset kelas status sebelumnya
        cardStatus.className = 'card-stat';
        
        // Terapkan kelas CSS berdasarkan status
        if(status === 'AMAN') {
            cardStatus.classList.add('status-aman');
        } else if(status === 'WASPADA') {
            cardStatus.classList.add('status-waspada');
        } else if(status === 'BAHAYA') {
            cardStatus.classList.add('status-bahaya');
        }
    }
}

/**
 * Mengecek apakah perlu menampilkan Notifikasi Bahaya
 */
function checkAlert(status) {
    const alertBanner = document.getElementById('alertBanner');
    if(!alertBanner) return;
    
    if(status.toUpperCase() === 'BAHAYA') {
        alertBanner.style.display = 'block';
        // Teks notifikasi
        alertBanner.innerHTML = `
            <div class="alert alert-danger mb-0 d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><strong>PERINGATAN!</strong> Status mutu saat ini BAHAYA (>16%). Segera lakukan tindakan!</div>
                <button type="button" class="btn-close ms-auto" onclick="document.getElementById('alertBanner').style.display='none'"></button>
            </div>
        `;
    } else {
        alertBanner.style.display = 'none';
    }
}

/**
 * Menginisialisasi Chart.js saat pertama kali load
 */
function initCharts() {
    const ctxKadar = document.getElementById('chartKadarAir');
    const ctxSuhuLembab = document.getElementById('chartSuhuKelembaban');
    
    if(!ctxKadar || !ctxSuhuLembab) return;

    // Opsi umum untuk grafik
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 0 // Matikan animasi saat update agar tidak berkedip setiap 5 detik
        },
        scales: {
            x: {
                display: true,
                title: { display: true, text: 'Waktu' },
                ticks: { maxTicksLimit: 10 } // Batasi jumlah label x-axis agar tidak menumpuk
            },
            y: {
                display: true,
                beginAtZero: false
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        }
    };

    // Chart Kadar Air (Line Chart)
    chartKadarAir = new Chart(ctxKadar, {
        type: 'line',
        data: { 
            labels: [], 
            datasets: [{ 
                label: 'Kadar Air (%)', 
                data: [], 
                borderColor: '#4361ee', 
                backgroundColor: 'rgba(67, 97, 238, 0.1)', 
                borderWidth: 2, 
                fill: true, 
                tension: 0.4,
                pointRadius: 2
            }] 
        },
        options: commonOptions
    });

    // Chart Suhu & Kelembaban (Multi-axis Line Chart)
    chartSuhuKelembaban = new Chart(ctxSuhuLembab, {
        type: 'line',
        data: { 
            labels: [], 
            datasets: [
                { 
                    label: 'Suhu (°C)', 
                    data: [], 
                    borderColor: '#e74c3c', 
                    backgroundColor: 'transparent', 
                    borderWidth: 2, 
                    tension: 0.4,
                    pointRadius: 2
                },
                { 
                    label: 'Kelembaban (%)', 
                    data: [], 
                    borderColor: '#2ecc71', 
                    backgroundColor: 'transparent', 
                    borderWidth: 2, 
                    tension: 0.4,
                    pointRadius: 2
                }
            ] 
        },
        options: commonOptions
    });
}

/**
 * Memperbarui data pada Chart.js
 */
function updateCharts(dataArray) {
    if(!chartKadarAir || !chartSuhuKelembaban) return;
    
    const labels = [];
    const dataKadar = [];
    const dataSuhu = [];
    const dataKelembaban = [];
    
    // Parsing data array (diurutkan ASC dari backend)
    dataArray.forEach(item => {
        // Format waktu untuk label (HH:MM:SS)
        const timeStr = formatJamSekarang(item.waktu);
        
        labels.push(timeStr);
        dataKadar.push(parseFloat(item.kadar_air));
        dataSuhu.push(parseFloat(item.suhu));
        dataKelembaban.push(parseFloat(item.kelembaban));
    });
    
    // Update Data Chart Kadar Air
    chartKadarAir.data.labels = labels;
    chartKadarAir.data.datasets[0].data = dataKadar;
    chartKadarAir.update();
    
    // Update Data Chart Suhu & Kelembaban
    chartSuhuKelembaban.data.labels = labels;
    chartSuhuKelembaban.data.datasets[0].data = dataSuhu;
    chartSuhuKelembaban.data.datasets[1].data = dataKelembaban;
    chartSuhuKelembaban.update();
}


/**
 * Menambah Data Baru ke Ujung Grafik secara Instan
 */
function appendChartData(item) {
    if(!chartKadarAir || !chartSuhuKelembaban) return;

    const timeStr = formatJamSekarang(item.waktu);

    // Chart Kadar Air
    chartKadarAir.data.labels.push(timeStr);
    chartKadarAir.data.datasets[0].data.push(parseFloat(item.kadar_air));
    
    // Chart Suhu & Kelembaban
    chartSuhuKelembaban.data.labels.push(timeStr);
    chartSuhuKelembaban.data.datasets[0].data.push(parseFloat(item.suhu));
    chartSuhuKelembaban.data.datasets[1].data.push(parseFloat(item.kelembaban));

    // Batasi maksimum titik di grafik (misal 50) agar tidak lag/memory leak
    const MAX_POINTS = 50;
    if (chartKadarAir.data.labels.length > MAX_POINTS) {
        chartKadarAir.data.labels.shift();
        chartKadarAir.data.datasets[0].data.shift();
        
        chartSuhuKelembaban.data.labels.shift();
        chartSuhuKelembaban.data.datasets[0].data.shift();
        chartSuhuKelembaban.data.datasets[1].data.shift();
    }

    // Hanya update animasinya untuk titik baru
    chartKadarAir.update();
    chartSuhuKelembaban.update();
}

/**
 * Memasukkan Data Teratas di Tabel Riwayat
 */
function prependTableHistory(item) {
    const tableBody = document.getElementById('tableHistoryBody');
    if(!tableBody) return;

    // Bersihkan text "Belum ada data" / "Memuat data" jika ada
    if (tableBody.children.length === 1 && tableBody.children[0].innerText.includes('Memuat')) {
        tableBody.innerHTML = '';
    }

    let badgeClass = 'bg-success';
    let statusText = item.status_mutu.toUpperCase();
    
    if(statusText === 'WASPADA') badgeClass = 'bg-warning text-dark';
    if(statusText === 'BAHAYA') badgeClass = 'bg-danger';
    
    const tr = document.createElement('tr');
    tr.classList.add('table-primary'); // Highlight data baru sesaat
    setTimeout(() => tr.classList.remove('table-primary'), 2000); // hilangkan highlight

    tr.innerHTML = `
        <td><i class="bi bi-clock me-2 text-muted"></i>${formatWaktuIndo(item.waktu)}</td>
        <td class="fw-bold">${item.nilai_adc}</td>
        <td>${parseFloat(item.kadar_air).toFixed(1)}%</td>
        <td>${parseFloat(item.suhu).toFixed(1)}°C</td>
        <td>${parseFloat(item.kelembaban).toFixed(1)}%</td>
        <td><span class="badge ${badgeClass}">${statusText}</span></td>
    `;
    
    // Masukkan ke paling atas
    tableBody.prepend(tr);
}

/**
 * Mengecek Koneksi ESP32 berdasarkan selisih waktu database dan server backend (PHP)
 */
function checkEspStatus(diffSeconds) {
    const badge = document.getElementById('connectionBadge');
    const icon = document.getElementById('connectionIcon');
    const text = document.getElementById('connectionText');

    if (!badge || !icon || !text || diffSeconds === undefined) return;

    // ESP mengirim per 5 detik, jadi batas toleransi d kasih max 15 detik
    if (diffSeconds >= 0 && diffSeconds <= 15) {
        badge.className = 'badge bg-primary px-3 py-2 rounded-pill me-3 d-none d-md-inline';
        icon.className = 'bi bi-circle-fill me-1 text-success';
        text.innerText = 'ESP32 Online';
    } else {
        badge.className = 'badge bg-secondary px-3 py-2 rounded-pill me-3 d-none d-md-inline';
        icon.className = 'bi bi-circle-fill me-1 text-danger';
        text.innerText = 'ESP32 Offline';
    }
}

/**
 * Format Waktu ke Format Indonesia (dd Bulan yyyy, HH:mm:ss WIB)
 */
function formatWaktuIndo(waktuStr) {
    if (!waktuStr) return '-';
    // Hilangkan microsecond untuk parsing yang lebih aman
    const cleanWaktu = waktuStr.split('.')[0];
    // Reformat string untuk parsing yang lebih luas didukung (mengubah - menjadi /)
    const safeWaktu = cleanWaktu.replace(/-/g, '/');
    const dateObj = new Date(safeWaktu);

    if (isNaN(dateObj.getTime())) return waktuStr; // Fallback jika gagal parse

    const bulanIndo = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    const hari = dateObj.getDate().toString().padStart(2, '0');
    const bulan = bulanIndo[dateObj.getMonth()];
    const tahun = dateObj.getFullYear();
    const jam = dateObj.getHours().toString().padStart(2, '0');
    const menit = dateObj.getMinutes().toString().padStart(2, '0');
    const detik = dateObj.getSeconds().toString().padStart(2, '0');

    return `${hari} ${bulan} ${tahun}, ${jam}:${menit}:${detik} WIB`;
}

/**
 * Mendapatkan Jam:Menit:Detik dari Waktu agar aman di parsing semua browser
 */
function formatJamSekarang(waktuStr) {
    if (!waktuStr) return '-';
    const cleanWaktu = waktuStr.split('.')[0];
    const safeWaktu = cleanWaktu.replace(/-/g, '/');
    const dateObj = new Date(safeWaktu);
    
    if (isNaN(dateObj.getTime())) return waktuStr;

    const jam = dateObj.getHours().toString().padStart(2, '0');
    const menit = dateObj.getMinutes().toString().padStart(2, '0');
    const detik = dateObj.getSeconds().toString().padStart(2, '0');

    return `${jam}:${menit}:${detik}`;
}
