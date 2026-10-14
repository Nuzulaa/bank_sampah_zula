<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../includes/admin_only.php';
require_once '../koneksi.php';
$judul_halaman = 'Edit Akun';

$id = (int) ($_GET['id'] ?? 0);
$q  = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = $id");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    header('Location: list.php?status=error&pesan=Data tidak ditemukan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role     = in_array($_POST['role'], ['admin', 'guru', 'pengepul', 'siswa']) ? $_POST['role'] : 'siswa';

    if (!empty($_POST['password'])) {
        // Ganti password hanya kalau field diisi
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET nama='$nama', username='$username', password='$password', role='$role' WHERE id_user=$id";
    } else {
        $sql = "UPDATE users SET nama='$nama', username='$username', role='$role' WHERE id_user=$id";
    }

    if (mysqli_query($koneksi, $sql)) {
        header('Location: list.php?status=edit');
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
        <h2>Edit Akun</h2>
    </div>
    <form method="POST">
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($data['username']) ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diganti">
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="siswa" <?= $data['role'] == 'siswa' ? 'selected' : '' ?>>Siswa</option>
                <option value="guru" <?= $data['role'] == 'guru' ? 'selected' : '' ?>>Guru</option>
                <option value="pengepul" <?= $data['role'] == 'pengepul' ? 'selected' : '' ?>>Pengepul</option>
                <option value="admin" <?= $data['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="list.php" class="btn btn-outline">Batal</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
