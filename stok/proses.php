<?php
require_once '../includes/auth.php';
require_once '../koneksi.php';

$role = $_SESSION['role'] ?? '';
$id_saya = (int) $_SESSION['id_user'];

if ($role !== 'pengepul' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: list.php');
    exit;
}

$id_sampah = (int) ($_POST['id_sampah'] ?? 0);
$berat_kg  = (float) ($_POST['berat_kg'] ?? 0);
$catatan   = mysqli_real_escape_string($koneksi, trim($_POST['catatan'] ?? ''));
$tanggal   = date('Y-m-d');

if ($id_sampah <= 0 || $berat_kg <= 0) {
    header('Location: list.php?status=error&pesan=Data+input+tidak+valid');
    exit;
}

// 1. Cek Saldo Pengepul
$q_user = mysqli_query($koneksi, "SELECT saldo FROM users WHERE id_user = $id_saya");
$user = mysqli_fetch_assoc($q_user);
$saldo_saya = $user['saldo'] ?? 0;

// 2. Cek Stok Sampah & Harga per kg
$q_sampah = mysqli_query($koneksi, "SELECT harga_per_kg, stok_kg FROM sampah WHERE id_sampah = $id_sampah");
$sampah = mysqli_fetch_assoc($q_sampah);

if (!$sampah) {
    header('Location: list.php?status=error&pesan=Jenis+sampah+tidak+ditemukan');
    exit;
}

$stok_tersedia = $sampah['stok_kg'];
$harga_per_kg  = $sampah['harga_per_kg'];
$total_harga   = $berat_kg * $harga_per_kg;

if ($berat_kg > $stok_tersedia) {
    header('Location: list.php?status=error&pesan=Stok+sampah+di+admin+tidak+mencukupi+(Tersedia:+'.$stok_tersedia.'+kg)');
    exit;
}

if ($total_harga > $saldo_saya) {
    header('Location: list.php?status=error&pesan=Saldo+deposit+kamu+tidak+cukup.+Silakan+topup+terlebih+dahulu.');
    exit;
}

// Eksekusi Pemotongan Stok & Saldo
mysqli_query($koneksi, "UPDATE sampah SET stok_kg = stok_kg - $berat_kg WHERE id_sampah = $id_sampah");
mysqli_query($koneksi, "UPDATE users SET saldo = saldo - $total_harga WHERE id_user = $id_saya");

$sql = "INSERT INTO pengambilan_sampah (id_sampah, id_user, berat_kg, total_harga, tanggal, catatan) 
        VALUES ($id_sampah, $id_saya, $berat_kg, $total_harga, '$tanggal', '$catatan')";

if (mysqli_query($koneksi, $sql)) {
    header('Location: list.php?status=sukses&pesan=Pembelian+sampah+berhasil');
} else {
    header('Location: list.php?status=error&pesan=' . urlencode(mysqli_error($koneksi)));
}
exit;