
<?php
include 'config.php';

header('Content-Type: application/json');

// Ambil status lampu1
$stmt = $conn->prepare(
    "SELECT status FROM tb_lampu WHERE nama_lampu = ? LIMIT 1"
);

$nama_lampu = "lampu1";

$stmt->bind_param("s", $nama_lampu);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "nama_lampu" => "lampu1",
        "status" => (int) $row['status']
    ]);
} else {
    echo json_encode([
        "nama_lampu" => "lampu1",
        "status" => 0
    ]);
}

$stmt->close();
$conn->close();
?>
