<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../includes/admin_only.php';
require_once '../koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
$q  = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id_transaksi = $id");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    header('Location: list.php?status=error&pesan=' . urlencode('Transaksi tidak ditemukan.'));
    exit;
}

mysqli_begin_transaction($koneksi);
try {
    // Kembalikan stok & saldo seperti sebelum transaksi ini dibuat
    if ($data['tipe'] === 'setor') {
        mysqli_query($koneksi, "UPDATE sampah SET stok_kg = stok_kg - {$data['berat_kg']} WHERE id_sampah = {$data['id_sampah']}") or throw new Exception(mysqli_error($koneksi));
        mysqli_query($koneksi, "UPDATE users SET saldo = saldo - {$data['total_rp']} WHERE id_user = {$data['id_user']}") or throw new Exception(mysqli_error($koneksi));
    } else {
        mysqli_query($koneksi, "UPDATE sampah SET stok_kg = stok_kg + {$data['berat_kg']} WHERE id_sampah = {$data['id_sampah']}") or throw new Exception(mysqli_error($koneksi));
        mysqli_query($koneksi, "UPDATE users SET saldo = saldo + {$data['total_rp']} WHERE id_user = {$data['id_user']}") or throw new Exception(mysqli_error($koneksi));
    }

    mysqli_query($koneksi, "DELETE FROM transaksi WHERE id_transaksi = $id") or throw new Exception(mysqli_error($koneksi));

    mysqli_commit($koneksi);
    header('Location: list.php?status=hapus');
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    header('Location: list.php?status=error&pesan=' . urlencode($e->getMessage()));
}
exit;
