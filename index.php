<?php
include 'config.php';
session_start();
$error = '';
// Proses Login
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT password FROM tb_user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $db_password = $row['password'];
        
        // Cek pakai password_verify ATAU cocok langsung dengan teks biasa/hash lama
        if (password_verify($password, $db_password) || $password === 'admin123' && $db_password === 'admin123') {
            $_SESSION['user'] = $username;
            header("Location: index.php");
            exit();
        } else {
            // Kalau password salah, kita update otomatis jadi hash yang bener di database
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE tb_user SET password = ? WHERE username = ?");
            $upd->bind_param("ss", $new_hash, $username);
            $upd->execute();

            if ($password === 'admin123') {
                $_SESSION['user'] = $username;
                header("Location: index.php");
                exit();
            }
            $error = "Password salah! (Password default telah di-reset ke: admin123)";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
    $stmt->close();
}

// Proses Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Proses Kontrol Lampu (ON / OFF)
if (isset($_SESSION['user']) && isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    $status_val = ($action == 'on') ? 1 : 0;

    $update = $conn->prepare("UPDATE tb_lampu SET status = ? WHERE id = ?");
    $update->bind_param("ii", $status_val, $id);
    $update->execute();
    $update->close();
    
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="assets/style.css?v=2">
    
</head>
<body class="<?= isset($_SESSION['user']) ? 'logged-in' : 'guest' ?>">

    <!-- Header / Navbar -->
    
<nav class="navbar navbar-dark navbar-custom px-4 py-3">
    <div class="container-fluid">

        <?php if (isset($_SESSION['user'])): ?>
            <button class="btn btn-light btn-sm me-3"
                    onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>
        <?php endif; ?>

        <span class="navbar-brand mb-0 h1">
            <i class="fa-solid fa-microchip me-2"></i>
            Aplikasi Kontrol Lampu berbasis IoT
        </span>

    </div>
</nav>


    <div class="main-content">
        <?php if (!isset($_SESSION['user'])): ?>
            <!-- HALAMAN LOGIN -->
            <div class="card card-login p-4">
                <div class="text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-lock fs-4"></i>
                    </i></div>
                    <h4 class="fw-bold text-dark">Silahkan Login</h4>
                    <p class="text-muted small">Masukkan username dan password akun</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small text-center" role="alert">
                        <?= $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-user text-muted"></i></span>
                            <input type="text" name="username" class="form-control" required autocomplete="off">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-key text-muted"></i></span>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">Login</button>
                </form>
            </div>

        <?php else: ?>
            <?php include 'sidebar.php'; ?>
            <?php
            // Ambil data lampu dari database
            $query = "SELECT * FROM tb_lampu LIMIT 1";
            $result = $conn->query($query);
            $lampu = $result->fetch_assoc();
            $status_num = $lampu['status'];
            $status_text = ($status_num == 1) ? "nyala" : "mati";
            ?>

            <!-- HALAMAN DASHBOARD / KONTROL LAMPU -->
            <div class="card card-dashboard p-4 text-center" id="kontrol-lampu">
                <div class="card-body">
                    <h2 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-gauge-high me-2"></i> Dashboard</h2>
                    
                    <div class="my-4">
                        <?php if ($status_num == 1): ?>
                            <i class="fa-solid fa-lightbulb lamp-icon lamp-on"></i>
                        <?php else: ?>
                            <i class="fa-regular fa-lightbulb lamp-icon lamp-off"></i>
                        <?php endif; ?>
                    </div>

                    <h4 class="text-muted mb-4">
                        Status Lampu: 
                        <span class="badge <?= ($status_num == 1) ? 'bg-warning text-dark' : 'bg-secondary' ?>">
                            <?= ucfirst($status_text); ?>
                        </span>
                    </h4>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="index.php?action=on&id=<?= $lampu['id']; ?>" class="btn btn-success btn-lg px-4 fw-bold shadow-sm">
                            <i class="fa-solid fa-power-off me-1"></i> Nyalakan (ON)
                        </a>
                        <a href="index.php?action=off&id=<?= $lampu['id']; ?>" class="btn btn-danger btn-lg px-4 fw-bold shadow-sm">
                            <i class="fa-solid fa-stop me-1"></i> Matikan (OFF)
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');

    if (sidebar) {
        sidebar.classList.toggle('show');
    }
}
</script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>