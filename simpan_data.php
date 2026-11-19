<?php
// simpan_data.php
// Menerima data jarak dari ESP32 (HTTP POST) dan menyimpannya ke tb_monitoring
include 'config.php';

header('Content-Type: application/json');

// Ganti dengan kunci rahasia Anda (harus sama dengan yang ada di kode ESP32)
$API_KEY = 'sgtrahasia';

// Hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit();
}

// Validasi API key
$key = $_POST['api_key'] ?? '';
if (!hash_equals($API_KEY, $key)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'API key salah']);
    exit();
}

// Validasi data jarak
$jarak = $_POST['jarak'] ?? null;
if ($jarak === null || !is_numeric($jarak)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parameter jarak tidak valid']);
    exit();
}

$jarak = (float) $jarak;

// HC-SR04 normalnya membaca 2 - 400 cm
if ($jarak < 0 || $jarak > 500) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nilai jarak di luar rentang']);
    exit();
}

// Simpan ke database (prepared statement)
$stmt = $conn->prepare("INSERT INTO tb_monitoring (jarak) VALUES (?)");
$stmt->bind_param("d", $jarak);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'jarak' => $jarak]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data']);
}

$stmt->close();
