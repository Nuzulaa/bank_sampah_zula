<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base = $base ?? '';
$menu_aktif = $menu_aktif ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
    (function () {
        try {
            var t = localStorage.getItem('bsz-theme');
            if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', t);
        } catch (e) {}
    })();
    </script>
    <title>Bank Sampah Zula</title>
    <link rel="stylesheet" href="<?= $base ?>css/style.css">
    <!-- CDN Chart.js untuk grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="wrapper">

    <aside class="sidebar">
        <div class="brand">
            <span class="leaf">🌿</span>
            <span>Bank Sampah<br>Zula</span>
        </div>
        <nav>
            <a href="<?= $base ?>index.php" class="<?= $menu_aktif == 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
            
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <a href="<?= $base ?>sampah/list.php" class="<?= $menu_aktif == 'sampah' ? 'active' : '' ?>">♻️ Jenis Sampah</a>
            <?php endif; ?>

            <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'guru', 'siswa'])): ?>
            <a href="<?= $base ?>transaksi/list.php" class="<?= $menu_aktif == 'transaksi' ? 'active' : '' ?>">🧾 Transaksi Setor</a>
            <?php endif; ?>

            <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'guru', 'siswa'])): ?>
            <a href="<?= $base ?>setoran/list.php" class="<?= $menu_aktif == 'setoran' ? 'active' : '' ?>">💰 Setoran Saldo</a>
            <?php endif; ?>

            <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'pengepul'])): ?>
            <a href="<?= $base ?>topup/list.php" class="<?= $menu_aktif == 'topup' ? 'active' : '' ?>">💳 Top Up Saldo</a>
            <?php endif; ?>

            <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'guru', 'pengepul'])): ?>
            <a href="<?= $base ?>stok/list.php" class="<?= $menu_aktif == 'stok' ? 'active' : '' ?>">📦 Stok Sampah</a>
            <?php endif; ?>

            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <a href="<?= $base ?>users/list.php" class="<?= $menu_aktif == 'users' ? 'active' : '' ?>">👤 Pengguna</a>
            <?php endif; ?>

            <a href="<?= $base ?>profil.php" class="<?= $menu_aktif == 'profil' ? 'active' : '' ?>">🙍 Profil Saya</a>
            <a href="<?= $base ?>logout.php">🚪 Keluar</a>
        </nav>
    </aside>

    <div class="main">
        <div class="topbar">
            <h1><?= isset($judul_halaman) ? $judul_halaman : 'Dashboard' ?></h1>
            <div class="user-info">
                <button id="theme-toggle" class="theme-toggle-btn" type="button">🌙</button>
                <div class="user-info-name">
                    <span class="user-avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'T', 0, 1)) ?></span>
                    <span><?= isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Tamu' ?></span>
                </div>
                <span class="role-badge"><?= isset($_SESSION['role']) ? $_SESSION['role'] : 'siswa' ?></span>
            </div>
        </div>
        <div class="content">
<?php $__theme_js = $base . 'js/theme.js'; ?>