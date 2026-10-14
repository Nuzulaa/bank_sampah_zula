<?php
// Panggil SETELAH require '../includes/auth.php'.
// Dipakai buat halaman approve/tolak setoran — boleh admin ATAU guru.
if (!in_array($_SESSION['role'] ?? '', ['admin', 'guru'])) {
    header('Location: ' . ($base ?? '') . 'index.php?status=error&pesan=' . urlencode('Halaman ini khusus admin/guru.'));
    exit;
}
