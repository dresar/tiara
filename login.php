<?php
session_start();
require_once 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, nama_lengkap, role FROM tb_users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: index.php");
                exit;
            } else {
                $error = "Username atau password salah!";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem.";
        }
    } else {
        $error = "Harap isi username dan password.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AgroIoT System</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header i {
            font-size: 3rem;
            color: #4361ee;
            background: rgba(67, 97, 238, 0.1);
            padding: 1rem;
            border-radius: 50%;
            margin-bottom: 1rem;
            display: inline-block;
        }
        .login-header h3 {
            font-weight: 700;
            color: #2b2b2b;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
            border-color: #4361ee;
        }
        .btn-login {
            background-color: #4361ee;
            border-color: #4361ee;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background-color: #3f37c9;
            border-color: #3f37c9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }
        .quick-login-box {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px dashed #ced4da;
        }
        .btn-quick {
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <i class="bi bi-cpu"></i>
        <h3>AgroIoT System</h3>
        <p class="text-muted">Login untuk memantau perangkat ESP32</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="quick-login-box text-center">
        <p class="text-muted mb-2 fw-bold" style="font-size: 0.8rem;">LOGIN CEPAT (DEMO SKRIPSI)</p>
        <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-outline-primary btn-quick flex-fill" onclick="fillAndSubmit('admin', 'admin123')">
                <i class="bi bi-shield-lock me-1"></i> Admin
            </button>
            <button type="button" class="btn btn-outline-success btn-quick flex-fill" onclick="fillAndSubmit('petani', 'petani123')">
                <i class="bi bi-person me-1"></i> Petani
            </button>
        </div>
    </div>

    <form method="POST" action="" id="loginForm">
        <div class="mb-3">
            <label for="username" class="form-label text-muted fw-bold" style="font-size: 0.85rem;">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
                <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" placeholder="Masukkan username" required>
            </div>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label text-muted fw-bold" style="font-size: 0.85rem;">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Masukkan password" required>
            </div>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang
            </button>
        </div>
        <div class="mt-4 text-center">
            <span class="text-muted" style="font-size: 0.85rem;">Belum punya akun petani? <a href="register.php" class="text-primary fw-bold text-decoration-none">Daftar di sini</a></span>
        </div>
    </form>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
    function fillAndSubmit(user, pass) {
        document.getElementById('username').value = user;
        document.getElementById('password').value = pass;
    }
</script>
</body>
</html>
