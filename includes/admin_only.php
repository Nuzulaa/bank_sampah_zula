<?php
// Panggil SETELAH require '../includes/auth.php' (butuh $_SESSION udah kebentuk).
// $base juga harus udah diset ('' untuk root, '../' untuk subfolder modul).
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . ($base ?? '') . 'index.php?status=error&pesan=' . urlencode('Halaman ini khusus admin.'));
    exit;
}
