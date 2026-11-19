<?php
// get_data.php
// Mengirim 20 data jarak terakhir dalam format JSON untuk grafik
session_start();
include 'config.php';

header('Content-Type: application/json');

// Proteksi: hanya user yang sudah login
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'unauthorized']);
    exit();
}

$query = "SELECT jarak, waktu FROM (
              SELECT id, jarak, waktu FROM tb_monitoring ORDER BY id DESC LIMIT 20
          ) AS t ORDER BY id ASC";
$result = $conn->query($query);

$labels = [];
$values = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = date('H:i:s', strtotime($row['waktu']));
    $values[] = (float) $row['jarak'];
}

echo json_encode([
    'status' => 'success',
    'labels' => $labels,
    'values' => $values,
    'latest' => count($values) ? end($values) : null
]);
