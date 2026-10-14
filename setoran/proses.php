<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../includes/finance_only.php';
require_once '../koneksi.php';

$id   = (int) ($_GET['id'] ?? 0);
$aksi = $_GET['aksi'] ?? '';

$q = mysqli_query($koneksi, "SELECT * FROM setoran WHERE id_setoran = $id");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    header('Location: list.php?status=error&pesan=' . urlencode('Pengajuan tidak ditemukan.'));
    exit;
}

if ($data['status'] !== 'pending') {
    header('Location: list.php?status=error&pesan=' . urlencode('Pengajuan ini sudah diproses sebelumnya.'));
    exit;
}

if ($aksi === 'setuju') {
    // Cek saldo cukup sebelum motong
    $q_user = mysqli_query($koneksi, "SELECT saldo FROM users WHERE id_user = {$data['id_user']}");
    $user   = mysqli_fetch_assoc($q_user);

    if ($user['saldo'] < $data['nominal']) {
        header('Location: list.php?status=error&pesan=' . urlencode('Saldo siswa tidak cukup untuk disetujui.'));
        exit;
    }

    mysqli_begin_transaction($koneksi);
    try {
        mysqli_query($koneksi, "UPDATE users SET saldo = saldo - {$data['nominal']} WHERE id_user = {$data['id_user']}") or throw new Exception(mysqli_error($koneksi));
        mysqli_query($koneksi, "UPDATE setoran SET status = 'disetujui' WHERE id_setoran = $id") or throw new Exception(mysqli_error($koneksi));
        mysqli_commit($koneksi);
        header('Location: list.php?status=disetujui');
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header('Location: list.php?status=error&pesan=' . urlencode($e->getMessage()));
    }
} elseif ($aksi === 'tolak') {
    mysqli_query($koneksi, "UPDATE setoran SET status = 'ditolak' WHERE id_setoran = $id");
    header('Location: list.php?status=ditolak');
} else {
    header('Location: list.php?status=error&pesan=' . urlencode('Aksi tidak dikenali.'));
}
exit;
