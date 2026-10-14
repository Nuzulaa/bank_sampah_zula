<?php
// Panggil SETELAH require '../includes/auth.php'.
// Dipakai buat halaman stok sampah — siswa gak relevan, jadi diblok.
if (!in_array($_SESSION['role'] ?? '', ['admin', 'guru', 'pengepul'])) {
    header('Location: ' . ($base ?? '') . 'index.php?status=error&pesan=' . urlencode('Halaman ini gak berlaku buat akun kamu.'));
    exit;
}
