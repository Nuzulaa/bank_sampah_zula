<?php
$base = '';
require_once 'includes/auth.php';
require_once 'koneksi.php';
$judul_halaman = 'Profil Saya';
$menu_aktif = 'profil';

$id_saya = (int) $_SESSION['id_user'];
$error = '';
$sukses = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password_baru = $_POST['password_baru'];
    $konfirmasi    = $_POST['konfirmasi_password'];

    if ($nama === '' || $username === '') {
        $error = 'Nama dan username wajib diisi.';
    } elseif ($password_baru !== '' && strlen($password_baru) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } elseif ($password_baru !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Username gak boleh dipakai orang lain (selain diri sendiri)
        $cek = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username = '$username' AND id_user != $id_saya");
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Username sudah dipakai orang lain.';
        } else {
            if ($password_baru !== '') {
                $hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET nama='$nama', username='$username', password='$hash' WHERE id_user=$id_saya";
            } else {
                $sql = "UPDATE users SET nama='$nama', username='$username' WHERE id_user=$id_saya";
            }

            if (mysqli_query($koneksi, $sql)) {
                $_SESSION['nama'] = $nama;
                $sukses = true;
            } else {
                $error = 'Gagal menyimpan: ' . mysqli_error($koneksi);
            }
        }
    }
}

$q = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = $id_saya");
$data = mysqli_fetch_assoc($q);

include 'includes/header.php';
?>

<?php if ($sukses): ?>
    <div class="alert alert-success">Profil berhasil diperbarui.</div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width:480px;">
    <div class="card-header">
        <h2>Data Diri</h2>
        <span class="status <?= $data['role'] ?>"><?= ucfirst($data['role']) ?></span>
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
        <?php if ($data['role'] === 'siswa'): ?>
        <div class="form-group">
            <label>Saldo Saat Ini</label>
            <input type="text" value="Rp <?= number_format($data['saldo'], 0, ',', '.') ?>" disabled>
        </div>
        <?php endif; ?>
        <hr style="border:none; border-top:1px dashed var(--line); margin:18px 0;">
        <div class="form-group">
            <label for="password_baru">Password Baru</label>
            <input type="password" id="password_baru" name="password_baru" placeholder="Kosongkan kalau gak mau ganti">
        </div>
        <div class="form-group">
            <label for="konfirmasi_password">Konfirmasi Password Baru</label>
            <input type="password" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi password baru">
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
