<?php
require_once '../includes/auth.php';
require_once '../koneksi.php';

$role = $_SESSION['role'] ?? '';
$id_saya = (int) $_SESSION['id_user'];
$aksi = $_GET['aksi'] ?? '';

if ($aksi === 'tambah' && $role === 'pengepul' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nominal = (float) ($_POST['nominal'] ?? 0);
    $tanggal = date('Y-m-d');

    if ($nominal <= 0) {
        header('Location: list.php?status=error&pesan=Nominal+tidak+valid');
        exit;
    }

    $sql = "INSERT INTO topup_pengepul (id_user, nominal, tanggal, status) VALUES ($id_saya, $nominal, '$tanggal', 'pending')";
    if (mysqli_query($koneksi, $sql)) {
        header('Location: list.php?status=sukses&pesan=Pengajuan+topup+berhasil+dikirim');
    } else {
        header('Location: list.php?status=error&pesan=' . urlencode(mysqli_error($koneksi)));
    }
    exit;
}

if (($aksi === 'setuju' || $aksi === 'tolak') && $role === 'admin') {
    $id_topup = (int) ($_GET['id'] ?? 0);

    $q = mysqli_query($koneksi, "SELECT * FROM topup_pengepul WHERE id_topup = $id_topup AND status = 'pending'");
    $topup = mysqli_fetch_assoc($q);

    if (!$topup) {
        header('Location: list.php?status=error&pesan=Data+topup+tidak+ditemukan+atau+sudah+diproses');
        exit;
    }

    if ($aksi === 'setuju') {
        $id_pengepul = $topup['id_user'];
        $nominal = $topup['nominal'];

        mysqli_query($koneksi, "UPDATE topup_pengepul SET status = 'disetujui' WHERE id_topup = $id_topup");
        mysqli_query($koneksi, "UPDATE users SET saldo = saldo + $nominal WHERE id_user = $id_pengepul");

        header('Location: list.php?status=sukses&pesan=Topup+berhasil+disetujui');
    } else {
        mysqli_query($koneksi, "UPDATE topup_pengepul SET status = 'ditolak' WHERE id_topup = $id_topup");
        header('Location: list.php?status=sukses&pesan=Topup+berhasil+ditolak');
    }
    exit;
}

header('Location: list.php');
exit;