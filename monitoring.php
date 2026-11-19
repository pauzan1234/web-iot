<?php
session_start();
include 'config.php';

// Proteksi halaman
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Ambil data lampu
$query = "SELECT * FROM tb_lampu LIMIT 1";
$result = $conn->query($query);
$lampu = $result->fetch_assoc();

$status_num = $lampu['status'] ?? 0;
$status_text = ($status_num == 1) ? "MENYALA" : "MATI";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Monitoring IoT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS UTAMA -->
    <link rel="stylesheet" href="assets/style.css?v=2">
</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark navbar-custom px-4">
        <button class="btn btn-light btn-sm" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <span class="navbar-brand ms-3 mb-0">Monitoring IoT</span>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <h3 class="fw-bold mb-1">
            <i class="fa-solid fa-chart-line me-2"></i>
            Monitoring Perangkat
        </h3>

        <p class="text-muted mb-4">
            Informasi kondisi perangkat IoT secara real-time.
        </p>

        <div class="row g-4">

            <!-- STATUS LAMPU -->
            <div class="col-md-6 col-lg-3">
                <div class="card monitor-card p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-2">Status Lampu</p>
                            <div class="status-indicator <?= $status_num == 1 ? 'text-success' : 'text-secondary' ?>">
                                <?= $status_text ?>
                            </div>
                        </div>
                        <i class="fa-solid fa-lightbulb monitor-icon"></i>
                    </div>
                </div>
            </div>

            <!-- JARAK TERAKHIR -->
            <div class="col-md-6 col-lg-3">
                <div class="card monitor-card p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-2">Jarak Terakhir</p>
                            <h4 class="fw-bold">
                                <span id="jarakTerakhir">--</span> cm
                            </h4>
                        </div>
                        <i class="fa-solid fa-ruler-vertical monitor-icon"></i>
                    </div>
                </div>
            </div>

            <!-- IDENTITAS PERANGKAT -->
            <div class="col-md-6 col-lg-3">
                <div class="card monitor-card p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-2">Perangkat</p>
                            <h4 class="fw-bold">ESP32</h4>
                        </div>
                        <i class="fa-solid fa-microchip monitor-icon"></i>
                    </div>
                </div>
            </div>

            <!-- KONEKSI -->
            <div class="col-md-6 col-lg-3">
                <div class="card monitor-card p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-2">Database</p>
                            <h4 class="fw-bold text-success">Connected</h4>
                        </div>
                        <i class="fa-solid fa-database monitor-icon"></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- GRAFIK -->
        <div class="card monitor-card mt-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Grafik Monitoring Jarak (Ultrasonic)</h5>
                <small class="text-muted" id="updateInfo">Memuat data...</small>
            </div>

            <div style="position: relative; height: 350px;">
                <canvas id="myChart"></canvas>
            </div>
        </div>

    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }

    // ===== GRAFIK =====
    const chart = new Chart(document.getElementById('myChart'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Jarak (cm)',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Jarak (cm)' }
                },
                x: {
                    title: { display: true, text: 'Waktu' }
                }
            }
        }
    });

    // ===== AMBIL DATA TERBARU =====
    async function loadData() {
        try {
            const res = await fetch('get_data.php', { cache: 'no-store' });

            if (res.status === 401) {
                window.location.href = 'index.php'; // sesi habis
                return;
            }

            const json = await res.json();
            if (json.status !== 'success') return;

            chart.data.labels = json.labels;
            chart.data.datasets[0].data = json.values;
            chart.update();

            document.getElementById('jarakTerakhir').textContent =
                json.latest !== null ? json.latest.toFixed(1) : '--';

            document.getElementById('updateInfo').textContent =
                'Diperbarui: ' + new Date().toLocaleTimeString('id-ID');
        } catch (err) {
            document.getElementById('updateInfo').textContent = 'Gagal memuat data';
            console.error(err);
        }
    }

    loadData();
    setInterval(loadData, 3000); // refresh tiap 3 detik
</script>
</body>
</html>
