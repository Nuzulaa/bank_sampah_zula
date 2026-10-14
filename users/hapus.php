<?php
$base = '../';
session_start();
require_once '../includes/auth.php';
require_once '../includes/admin_only.php';
require_once '../koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

// Cegah admin hapus akunnya sendiri
if ($id === ($_SESSION['id_user'] ?? -1)) {
    header('Location: list.php?status=error&pesan=' . urlencode('Tidak bisa menghapus akun sendiri.'));
    exit;
}

$sql = "DELETE FROM users WHERE id_user = $id";
if (mysqli_query($koneksi, $sql)) {
    header('Location: list.php?status=hapus');
} else {
    // Kemungkinan gagal karena masih punya riwayat transaksi/setoran (FK RESTRICT)
    header('Location: list.php?status=error&pesan=' . urlencode('Tidak bisa dihapus, akun ini masih punya riwayat transaksi/setoran.'));
}
exit;
