<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../includes/admin_only.php';
require_once '../koneksi.php';
$judul_halaman = 'Catat Transaksi';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user   = (int) $_POST['id_user'];
    $id_sampah = (int) $_POST['id_sampah'];
    $tipe      = $_POST['tipe'] === 'tarik' ? 'tarik' : 'setor';
    $berat_kg  = (float) $_POST['berat_kg'];
    $tanggal   = $_POST['tanggal'];

    // Ambil harga per kg jenis sampah yang dipilih
    $q_sampah = mysqli_query($koneksi, "SELECT harga_per_kg, stok_kg FROM sampah WHERE id_sampah = $id_sampah");
    $sampah   = mysqli_fetch_assoc($q_sampah);

    if (!$sampah) {
        header('Location: list.php?status=error&pesan=' . urlencode('Jenis sampah tidak ditemukan.'));
        exit;
    }

    if ($tipe === 'tarik' && $berat_kg > $sampah['stok_kg']) {
        header('Location: list.php?status=error&pesan=' . urlencode('Stok sampah tidak cukup untuk ditarik.'));
        exit;
    }

    $total_rp = $sampah['harga_per_kg'] * $berat_kg;

    mysqli_begin_transaction($koneksi);
    try {
        // Simpan transaksi
        $sql_insert = "INSERT INTO transaksi (id_user, id_sampah, tipe, berat_kg, total_rp, tanggal)
                        VALUES ($id_user, $id_sampah, '$tipe', $berat_kg, $total_rp, '$tanggal')";
        mysqli_query($koneksi, $sql_insert) or throw new Exception(mysqli_error($koneksi));

        // Update stok sampah & saldo user sesuai tipe
        if ($tipe === 'setor') {
            mysqli_query($koneksi, "UPDATE sampah SET stok_kg = stok_kg + $berat_kg WHERE id_sampah = $id_sampah") or throw new Exception(mysqli_error($koneksi));
            mysqli_query($koneksi, "UPDATE users SET saldo = saldo + $total_rp WHERE id_user = $id_user") or throw new Exception(mysqli_error($koneksi));
        } else {
            mysqli_query($koneksi, "UPDATE sampah SET stok_kg = stok_kg - $berat_kg WHERE id_sampah = $id_sampah") or throw new Exception(mysqli_error($koneksi));
            mysqli_query($koneksi, "UPDATE users SET saldo = saldo - $total_rp WHERE id_user = $id_user") or throw new Exception(mysqli_error($koneksi));
        }

        mysqli_commit($koneksi);
        header('Location: list.php?status=tambah');
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header('Location: list.php?status=error&pesan=' . urlencode($e->getMessage()));
        exit;
    }
}

$menu_aktif = 'transaksi';
include '../includes/header.php';

$daftar_user   = mysqli_query($koneksi, "SELECT id_user, nama FROM users WHERE role = 'siswa' ORDER BY nama ASC");
$daftar_sampah = mysqli_query($koneksi, "SELECT id_sampah, jenis_sampah, harga_per_kg, stok_kg FROM sampah ORDER BY jenis_sampah ASC");
?>

<div class="card" style="max-width:520px;">
    <div class="card-header">
        <h2>Catat Transaksi</h2>
    </div>
    <form method="POST">
        <div class="form-group">
            <label for="id_user">Nasabah</label>
            <select id="id_user" name="id_user" required>
                <option value="">-- Pilih Nasabah --</option>
                <?php while ($u = mysqli_fetch_assoc($daftar_user)): ?>
                    <option value="<?= $u['id_user'] ?>"><?= htmlspecialchars($u['nama']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="id_sampah">Jenis Sampah</label>
            <select id="id_sampah" name="id_sampah" required>
                <option value="">-- Pilih Jenis Sampah --</option>
                <?php while ($s = mysqli_fetch_assoc($daftar_sampah)): ?>
                    <option value="<?= $s['id_sampah'] ?>">
                        <?= htmlspecialchars($s['jenis_sampah']) ?> (Rp <?= number_format($s['harga_per_kg'], 0, ',', '.') ?>/kg, stok <?= number_format($s['stok_kg'], 1) ?> kg)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="tipe">Tipe Transaksi</label>
            <select id="tipe" name="tipe" required>
                <option value="setor">Setor (siswa nyetor sampah)</option>
                <option value="tarik">Tarik (sampah keluar dari stok)</option>
            </select>
        </div>
        <div class="form-group">
            <label for="berat_kg">Berat (kg)</label>
            <input type="number" id="berat_kg" name="berat_kg" step="0.1" min="0.1" required>
        </div>
        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="list.php" class="btn btn-outline">Batal</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
