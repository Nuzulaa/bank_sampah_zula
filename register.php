<?php
session_start();
require_once 'koneksi.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi_password'];

    if ($nama === '' || $username === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $cek = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username = '$username'");
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Username sudah dipakai, coba yang lain.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (nama, username, password, role, saldo) VALUES ('$nama', '$username', '$hash', 'siswa', 0)";
            if (mysqli_query($koneksi, $sql)) {
                header('Location: login.php?status=daftar');
                exit;
            } else {
                $error = 'Gagal mendaftar: ' . mysqli_error($koneksi);
            }
        }
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
    <title>Daftar Akun - Bank Sampah Zula</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-shell">
    <button id="theme-toggle" class="theme-toggle-btn" type="button">🌙</button>
    <div class="auth-card">
        <div class="auth-visual">
            <div>
                <span class="leaf-mark">🌿</span>
                <h2>Gabung sebagai siswa</h2>
                <p>Daftar sekali, setoran & saldomu tercatat rapi setiap saat.</p>
            </div>
            <div class="stamp-deco">NASABAH<br>BARU</div>
        </div>
        <div class="auth-form">
            <h1>Buat Akun Baru</h1>
            <p class="sub">Isi data diri kamu di bawah</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="konfirmasi_password">Konfirmasi Password</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Daftar</button>
            </form>

            <p class="switch-link">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
        </div>
    </div>
</div>
<script src="js/theme.js"></script>
<script src="js/ui.js"></script>
</body>
</html>
