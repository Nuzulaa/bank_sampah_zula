<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../includes/pengepul_only.php';
require_once '../koneksi.php';
$judul_halaman = 'Catat Pengambilan Sampah';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sampah = (int) $_POST['id_sampah'];
    $berat_kg  = (float) $_POST['berat_kg'];
    $tanggal   = $_POST['tanggal'];
    $catatan   = mysqli_real_escape_string($koneksi, trim($_POST['catatan']));
    $id_user   = (int) $_SESSION['id_user'];

    $q_sampah = mysqli_query($koneksi, "SELECT stok_kg FROM sampah WHERE id_sampah = $id_sampah");
    $sampah   = mysqli_fetch_assoc($q_sampah);

    if (!$sampah) {
        header('Location: list.php?status=error&pesan=' . urlencode('Jenis sampah tidak ditemukan.'));
        exit;
    }

    if ($berat_kg > $sampah['stok_kg']) {
        header('Location: list.php?status=error&pesan=' . urlencode('Stok gak cukup buat diambil sebanyak itu.'));
        exit;
    }

    mysqli_begin_transaction($koneksi);
    try {
        $sql = "INSERT INTO pengambilan_sampah (id_sampah, id_user, berat_kg, tanggal, catatan)
                VALUES ($id_sampah, $id_user, $berat_kg, '$tanggal', " . ($catatan !== '' ? "'$catatan'" : "NULL") . ")";
        mysqli_query($koneksi, $sql) or throw new Exception(mysqli_error($koneksi));

        mysqli_query($koneksi, "UPDATE sampah SET stok_kg = stok_kg - $berat_kg WHERE id_sampah = $id_sampah") or throw new Exception(mysqli_error($koneksi));

        mysqli_commit($koneksi);
        header('Location: list.php?status=tambah');
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header('Location: list.php?status=error&pesan=' . urlencode($e->getMessage()));
        exit;
    }
}

$menu_aktif = 'stok';
include '../includes/header.php';
$daftar_sampah = mysqli_query($koneksi, "SELECT id_sampah, jenis_sampah, stok_kg FROM sampah ORDER BY jenis_sampah ASC");
?>

<div class="card" style="max-width:480px;">
    <div class="card-header">
        <h2>Catat Pengambilan Sampah</h2>
    </div>
    <form method="POST">
        <div class="form-group">
            <label for="id_sampah">Jenis Sampah</label>
            <select id="id_sampah" name="id_sampah" required>
                <option value="">-- Pilih Jenis Sampah --</option>
                <?php while ($s = mysqli_fetch_assoc($daftar_sampah)): ?>
                    <option value="<?= $s['id_sampah'] ?>">
                        <?= htmlspecialchars($s['jenis_sampah']) ?> (stok: <?= number_format($s['stok_kg'], 1) ?> kg)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="berat_kg">Berat Diambil (kg)</label>
            <input type="number" id="berat_kg" name="berat_kg" step="0.1" min="0.1" required>
        </div>
        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
            <label for="catatan">Catatan (opsional)</label>
            <input type="text" id="catatan" name="catatan" placeholder="Misal: harga jual di luar sistem">
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="list.php" class="btn btn-outline">Batal</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
