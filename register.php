<?php
session_start();
require_once 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');

    if (!empty($username) && !empty($password) && !empty($nama_lengkap)) {
        try {
            // Cek apakah username sudah ada
            $stmt = $pdo->prepare("SELECT id FROM tb_users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            if ($stmt->fetch()) {
                $error = "Username sudah digunakan, silakan pilih yang lain.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO tb_users (username, password, nama_lengkap, role) VALUES (:username, :password, :nama_lengkap, 'petani')");
                if ($stmt->execute([
                    ':username' => $username,
                    ':password' => $hash,
                    ':nama_lengkap' => $nama_lengkap
                ])) {
                    $success = "Pendaftaran berhasil! Silakan login dengan akun baru Anda.";
                } else {
                    $error = "Gagal mendaftar, terjadi kesalahan sistem.";
                }
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan database.";
        }
    } else {
        $error = "Harap isi semua kolom pendaftaran.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Petani | AgroIoT System</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header i {
            font-size: 3rem;
            color: #198754;
            background: rgba(25, 135, 84, 0.1);
            padding: 1rem;
            border-radius: 50%;
            margin-bottom: 1rem;
            display: inline-block;
        }
        .register-header h3 {
            font-weight: 700;
            color: #2b2b2b;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
            border-color: #198754;
        }
        .btn-register {
            background-color: #198754;
            border-color: #198754;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
            color: white;
        }
        .btn-register:hover {
            background-color: #157347;
            border-color: #146c43;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.4);
            color: white;
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="register-header">
        <i class="bi bi-person-plus-fill"></i>
        <h3>Daftar Akun Petani</h3>
        <p class="text-muted">Buat akun agar dapat memantau ladang Anda</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="nama_lengkap" class="form-label text-muted fw-bold" style="font-size: 0.85rem;">Nama Lengkap</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-person-badge text-muted"></i></span>
                <input type="text" class="form-control border-start-0 ps-0" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="username" class="form-label text-muted fw-bold" style="font-size: 0.85rem;">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
                <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" placeholder="Buat username" required>
            </div>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label text-muted fw-bold" style="font-size: 0.85rem;">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Buat password" required>
            </div>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-register">
                <i class="bi bi-person-plus me-2"></i> Daftar Sekarang
            </button>
        </div>
        <div class="mt-4 text-center">
            <span class="text-muted" style="font-size: 0.85rem;">Sudah punya akun? <a href="login.php" class="text-success fw-bold text-decoration-none">Masuk di sini</a></span>
        </div>
    </form>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
