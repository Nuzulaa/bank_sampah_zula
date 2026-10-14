<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../includes/admin_only.php';
require_once '../koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
$sql = "DELETE FROM sampah WHERE id_sampah = $id";

if (mysqli_query($koneksi, $sql)) {
    header('Location: list.php?status=hapus');
} else {
    // Kemungkinan besar gagal karena masih dipakai di tabel transaksi (FK RESTRICT)
    header('Location: list.php?status=error&pesan=' . urlencode('Tidak bisa dihapus, jenis sampah ini masih punya riwayat transaksi.'));
}
exit;
