<?php
// Panggil file ini paling atas di setiap halaman yang butuh login.
// Variabel $base harus sudah diset ('' untuk root, '../' untuk subfolder modul)
// SEBELUM require file ini.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    header('Location: ' . ($base ?? '') . 'welcome.php');
    exit;
}
