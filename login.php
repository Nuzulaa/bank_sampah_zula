<?php
session_start();
require_once 'koneksi.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $q = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    $user = mysqli_fetch_assoc($q);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
    (function () {
        try {
            var t = localStorage.getItem("bsz-theme");
            if (!t) t = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            document.documentElement.setAttribute("data-theme", t);
        } catch (e) {}
    })();
    </script>
    <title>Login - Bank Sampah Zula</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-shell">
    <button id="theme-toggle" class="theme-toggle-btn" type="button">🌙</button>
    <div class="auth-card">
        <div class="auth-visual">
            <div>
                <span class="leaf-mark">🌿</span>
                <h2>Selamat datang lagi</h2>
                <p>Masuk buat lanjut kelola setoran sampah dan pantau saldo tabungan.</p>
            </div>
            <div class="stamp-deco">BUKU<br>TABUNGAN</div>
        </div>
        <div class="auth-form">
            <h1>Masuk</h1>
            <p class="sub">Pakai username & password akunmu</p>

            <?php if (isset($_GET['status']) && $_GET['status'] === 'daftar'): ?>
                <div class="alert alert-success">Akun berhasil dibuat, silakan masuk.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Masuk</button>
            </form>

            <p class="switch-link">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>
</div>
<script src="js/theme.js"></script>
<script src="js/ui.js"></script>
</body>
</html>
