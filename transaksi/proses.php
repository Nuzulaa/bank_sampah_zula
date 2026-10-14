<?php
require_once '../includes/auth.php';
require_once '../koneksi.php';

$role = $_SESSION['role'] ?? '';
$id_saya = (int) $_SESSION['id_user'];

if (!in_array($role, ['admin', 'guru', 'siswa']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: list.php');
    exit;
}

// Tentukan ID Siswa
if ($role === 'siswa') {
    $id_user = $id_saya;
} else {
    $id_user = (int) ($_POST['id_user'] ?? 0);
}

$id_sampah = (int) ($_POST['id_sampah'] ?? 0);
$berat_kg  = (float) ($_POST['berat_kg'] ?? 0);
$tanggal   = date('Y-m-d');

if ($id_user <= 0 || $id_sampah <= 0 || $berat_kg <= 0) {
    header('Location: list.php?status=error&pesan=Mohon+lengkapi+semua+data+dengan+benar.');
    exit;
}

// 1. Cari harga per kg sampah
$q_sampah = mysqli_query($koneksi, "SELECT harga_per_kg FROM sampah WHERE id_sampah = $id_sampah");
$sampah   = mysqli_fetch_assoc($q_sampah);

if (!$sampah) {
    header('Location: list.php?status=error&pesan=Jenis+sampah+tidak+ditemukan.');
    exit;
}

$harga_per_kg = (float) $sampah['harga_per_kg'];
$total_rp     = $berat_kg * $harga_per_kg;

// 2. Tambah Stok Sampah di Admin
$u1 = mysqli_query($koneksi, "UPDATE sampah SET stok_kg = stok_kg + $berat_kg WHERE id_sampah = $id_sampah");

// 3. Tambah Saldo Tabungan Siswa
$u2 = mysqli_query($koneksi, "UPDATE users SET saldo = saldo + $total_rp WHERE id_user = $id_user");

// 4. Catat Transaksi
$sql = "INSERT INTO transaksi (id_user, id_sampah, tipe, berat_kg, total_rp, tanggal) 
        VALUES ($id_user, $id_sampah, 'setor', $berat_kg, $total_rp, '$tanggal')";

if ($u1 && $u2 && mysqli_query($koneksi, $sql)) {
    header('Location: list.php?status=sukses&pesan=Setor+sampah+berhasil!+Saldo+siswa+otomatis+bertambah.');
} else {
    header('Location: list.php?status=error&pesan=' . urlencode(mysqli_error($koneksi)));
}
exit;