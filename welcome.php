<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit;
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
    <title>Bank Sampah Zula</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-shell">
    <button id="theme-toggle" class="theme-toggle-btn" type="button">🌙</button>
    <div class="auth-card">
        <div class="auth-visual">
            <div>
                <span class="leaf-mark">🌿</span>
                <h2>Bank Sampah Zula</h2>
                <p>Setor sampah, tumbuhkan saldo. Satu buku catatan digital buat siswa, guru, pengepul & admin.</p>
            </div>
            <div class="stamp-deco">TERCATAT<br>RAPI</div>
        </div>
        <div class="auth-form">
            <h1>Mulai dari sini</h1>
            <p class="sub">Pilih salah satu buat lanjut</p>

            <a href="login.php" class="btn btn-primary">Masuk</a>
            <a href="register.php" class="btn btn-outline">Buat Akun Baru</a>
        </div>
    </div>
</div>
<script src="js/theme.js"></script>
<script src="js/ui.js"></script>
</body>
</html>
