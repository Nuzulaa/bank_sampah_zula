<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../koneksi.php';
$judul_halaman = 'Transaksi Setor Sampah';
$menu_aktif = 'transaksi';
$role = $_SESSION['role'] ?? '';
$id_saya = (int) $_SESSION['id_user'];

if (!in_array($role, ['admin', 'guru', 'siswa'])) {
    header('Location: ../index.php');
    exit;
}

// Ambil data jenis sampah untuk dropdown
$q_sampah = mysqli_query($koneksi, "SELECT * FROM sampah ORDER BY jenis_sampah ASC");

// Jika Admin/Guru, ambil daftar siswa untuk dipilih
$q_siswa = mysqli_query($koneksi, "SELECT id_user, nama FROM users WHERE role = 'siswa' ORDER BY nama ASC");

// Ambil riwayat transaksi
if ($role === 'siswa') {
    $q_transaksi = mysqli_query($koneksi, "
        SELECT t.*, s.jenis_sampah, u.nama 
        FROM transaksi t
        JOIN sampah s ON t.id_sampah = s.id_sampah
        JOIN users u ON t.id_user = u.id_user
        WHERE t.id_user = $id_saya AND t.tipe = 'setor'
        ORDER BY t.tanggal DESC, t.id_transaksi DESC
    ");
} else {
    $q_transaksi = mysqli_query($koneksi, "
        SELECT t.*, s.jenis_sampah, u.nama 
        FROM transaksi t
        JOIN sampah s ON t.id_sampah = s.id_sampah
        JOIN users u ON t.id_user = u.id_user
        WHERE t.tipe = 'setor'
        ORDER BY t.tanggal DESC, t.id_transaksi DESC
    ");
}

include '../includes/header.php';
?>

<?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] === 'sukses'): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['pesan'] ?? 'Transaksi berhasil disimpan.') ?></div>
    <?php elseif ($_GET['status'] === 'error'): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['pesan'] ?? 'Terjadi kesalahan.') ?></div>
    <?php endif; ?>
<?php endif; ?>

<div class="card" style="max-width: 540px; margin-bottom: 24px;">
    <div class="card-header">
        <h2>Form Setor Sampah</h2>
    </div>
    <form action="proses.php" method="POST">
        <?php if ($role === 'admin' || $role === 'guru'): ?>
            <div class="form-group">
                <label for="id_user">Pilih Siswa</label>
                <select id="id_user" name="id_user" required>
                    <option value="">-- Pilih Siswa --</option>
                    <?php while ($siswa = mysqli_fetch_assoc($q_siswa)): ?>
                        <option value="<?= $siswa['id_user'] ?>"><?= htmlspecialchars($siswa['nama']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        <?php else: ?>
            <input type="hidden" name="id_user" value="<?= $id_saya ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="id_sampah">Jenis Sampah</label>
            <select id="id_sampah" name="id_sampah" required>
                <option value="">-- Pilih Jenis Sampah --</option>
                <?php while ($s = mysqli_fetch_assoc($q_sampah)): ?>
                    <option value="<?= $s['id_sampah'] ?>">
                        <?= htmlspecialchars($s['jenis_sampah']) ?> (Rp <?= number_format($s['harga_per_kg'], 0, ',', '.') ?> / kg)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="berat_kg">Berat Sampah (kg)</label>
            <input type="number" id="berat_kg" name="berat_kg" step="0.1" min="0.1" placeholder="Contoh: 2.5" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Setoran</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2>Riwayat Setor Sampah</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Jenis Sampah</th>
                <th>Berat (kg)</th>
                <th>Total Diterima (Rp)</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($q_transaksi) > 0): ?>
                <?php while ($r = mysqli_fetch_assoc($q_transaksi)): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nama']) ?></td>
                    <td><?= htmlspecialchars($r['jenis_sampah']) ?></td>
                    <td><?= number_format($r['berat_kg'], 1) ?> kg</td>
                    <td><strong style="color:var(--primary, #2ecc71);">Rp <?= number_format($r['total_rp'], 0, ',', '.') ?></strong></td>
                    <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; color:var(--ink-soft);">Belum ada data transaksi setor.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>