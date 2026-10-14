<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../includes/admin_only.php';
require_once '../koneksi.php';
$judul_halaman = 'Tambah Jenis Sampah';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis  = mysqli_real_escape_string($koneksi, $_POST['jenis_sampah']);
    $harga  = (float) $_POST['harga_per_kg'];
    $stok   = (float) $_POST['stok_kg'];

    $sql = "INSERT INTO sampah (jenis_sampah, harga_per_kg, stok_kg) VALUES ('$jenis', $harga, $stok)";
    if (mysqli_query($koneksi, $sql)) {
        header('Location: list.php?status=tambah');
        exit;
    } else {
        header('Location: list.php?status=error&pesan=' . urlencode(mysqli_error($koneksi)));
        exit;
    }
}

$menu_aktif = 'sampah';
include '../includes/header.php';
?>

<div class="card" style="max-width:480px;">
    <div class="card-header">
        <h2>Tambah Jenis Sampah</h2>
    </div>
    <form method="POST">
        <div class="form-group">
            <label for="jenis_sampah">Jenis Sampah</label>
            <input type="text" id="jenis_sampah" name="jenis_sampah" required>
        </div>
        <div class="form-group">
            <label for="harga_per_kg">Harga per kg (Rp)</label>
            <input type="number" id="harga_per_kg" name="harga_per_kg" step="0.01" min="0" required>
        </div>
        <div class="form-group">
            <label for="stok_kg">Stok Awal (kg)</label>
            <input type="number" id="stok_kg" name="stok_kg" step="0.01" min="0" value="0" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="list.php" class="btn btn-outline">Batal</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
