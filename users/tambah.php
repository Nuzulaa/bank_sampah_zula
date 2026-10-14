<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../includes/admin_only.php';
require_once '../koneksi.php';
$judul_halaman = 'Tambah Akun';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // penting: pakai password_hash PHP, bukan hash dari tool lain
    $role     = in_array($_POST['role'], ['admin', 'guru', 'pengepul', 'siswa']) ? $_POST['role'] : 'siswa';

    $sql = "INSERT INTO users (nama, username, password, role, saldo) VALUES ('$nama', '$username', '$password', '$role', 0)";
    if (mysqli_query($koneksi, $sql)) {
        header('Location: list.php?status=tambah');
        exit;
    } else {
        header('Location: list.php?status=error&pesan=' . urlencode(mysqli_error($koneksi)));
        exit;
    }
}

$menu_aktif = 'users';
include '../includes/header.php';
?>

<div class="card" style="max-width:480px;">
    <div class="card-header">
        <h2>Tambah Akun</h2>
    </div>
    <form method="POST">
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" required>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="siswa">Siswa</option>
                <option value="guru">Guru</option>
                <option value="pengepul">Pengepul</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="list.php" class="btn btn-outline">Batal</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
