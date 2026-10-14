<?php
// Panggil SETELAH require '../includes/auth.php'.
// Cuma pengepul yang boleh catat sampah keluar (dijual/diambil).
if (($_SESSION['role'] ?? '') !== 'pengepul') {
    header('Location: ' . ($base ?? '') . 'stok/list.php?status=error&pesan=' . urlencode('Cuma pengepul yang bisa catat pengambilan sampah.'));
    exit;
}
