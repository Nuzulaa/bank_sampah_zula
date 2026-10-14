<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../includes/admin_only.php';
require_once '../koneksi.php';
$judul_halaman = 'Edit Jenis Sampah';

$id = (int) ($_GET['id'] ?? 0);
$q  = mysqli_query($koneksi, "SELECT * FROM sampah WHERE id_sampah = $id");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    header('Location: list.php?status=error&pesan=Data tidak ditemukan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_sampah']);
    $harga = (float) $_POST['harga_per_kg'];
    $stok  = (float) $_POST['stok_kg'];

    $sql = "UPDATE sampah SET jenis_sampah='$jenis', harga_per_kg=$harga, stok_kg=$stok WHERE id_sampah=$id";
    if (mysqli_query($koneksi, $sql)) {
        header('Location: list.php?status=edit');
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
        <h2>Edit Jenis Sampah</h2>
    </div>
    <form method="POST">
        <div class="form-group">
            <label for="jenis_sampah">Jenis Sampah</label>
            <input type="text" id="jenis_sampah" name="jenis_sampah" value="<?= htmlspecialchars($data['jenis_sampah']) ?>" required>
        </div>
        <div class="form-group">
            <label for="harga_per_kg">Harga per kg (Rp)</label>
            <input type="number" id="harga_per_kg" name="harga_per_kg" step="0.01" min="0" value="<?= $data['harga_per_kg'] ?>" required>
        </div>
        <div class="form-group">
            <label for="stok_kg">Stok (kg)</label>
            <input type="number" id="stok_kg" name="stok_kg" step="0.01" min="0" value="<?= $data['stok_kg'] ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="list.php" class="btn btn-outline">Batal</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
