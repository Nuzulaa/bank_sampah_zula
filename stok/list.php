<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../koneksi.php';
$judul_halaman = 'Beli / Ambil Sampah';
$menu_aktif = 'stok';
$role = $_SESSION['role'] ?? '';
$id_saya = (int) $_SESSION['id_user'];

if ($role !== 'admin' && $role !== 'pengepul') {
    header('Location: ../index.php');
    exit;
}

include '../includes/header.php';
?>

<?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] === 'sukses'): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['pesan'] ?? 'Transaksi berhasil.') ?></div>
    <?php elseif ($_GET['status'] === 'error'): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['pesan'] ?? 'Terjadi kesalahan.') ?></div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($role === 'pengepul'): ?>
    <?php
    $q_saldo = mysqli_query($koneksi, "SELECT saldo FROM users WHERE id_user = $id_saya");
    $saldo_saya = mysqli_fetch_assoc($q_saldo)['saldo'] ?? 0;

    $q_sampah = mysqli_query($koneksi, "SELECT * FROM sampah WHERE stok_kg > 0 ORDER BY jenis_sampah ASC");
    
    $q_history = mysqli_query($koneksi, "
        SELECT p.*, s.jenis_sampah 
        FROM pengambilan_sampah p 
        JOIN sampah s ON p.id_sampah = s.id_sampah 
        WHERE p.id_user = $id_saya 
        ORDER BY p.tanggal DESC, p.id_pengambilan DESC
    ");
    ?>

    <div class="card" style="max-width: 500px; margin-bottom: 24px;">
        <div class="card-header">
            <h2>Beli Sampah dari Admin</h2>
            <span class="status status-info">Saldo Deposit: Rp <?= number_format($saldo_saya, 0, ',', '.') ?></span>
        </div>
        <form action="proses.php" method="POST">
            <div class="form-group">
                <label for="id_sampah">Pilih Jenis Sampah</label>
                <select id="id_sampah" name="id_sampah" required>
                    <option value="">-- Pilih Sampah --</option>
                    <?php while ($s = mysqli_fetch_assoc($q_sampah)): ?>
                        <option value="<?= $s['id_sampah'] ?>">
                            <?= htmlspecialchars($s['jenis_sampah']) ?> (Stok: <?= number_format($s['stok_kg'], 1) ?> kg @ Rp <?= number_format($s['harga_per_kg'], 0, ',', '.') ?>/kg)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="berat_kg">Berat Pembelian (kg)</label>
                <input type="number" id="berat_kg" name="berat_kg" step="0.1" min="0.1" placeholder="Contoh: 10.5" required>
            </div>
            <div class="form-group">
                <label for="catatan">Catatan (Opsional)</label>
                <input type="text" id="catatan" name="catatan" placeholder="Catatan tambahan">
            </div>
            <button type="submit" class="btn btn-primary">Beli Sampah</button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Riwayat Pembelian Sampah</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Jenis Sampah</th>
                    <th>Berat (kg)</th>
                    <th>Total Harga</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($q_history) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($q_history)): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['jenis_sampah']) ?></td>
                        <td><?= number_format($r['berat_kg'], 1) ?> kg</td>
                        <td>Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
                        <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; color:var(--ink-soft);">Belum ada riwayat pembelian</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($role === 'admin'): ?>
    <?php
    $q_admin = mysqli_query($koneksi, "
        SELECT p.*, s.jenis_sampah, u.nama as nama_pengepul 
        FROM pengambilan_sampah p 
        JOIN sampah s ON p.id_sampah = s.id_sampah 
        JOIN users u ON p.id_user = u.id_user 
        ORDER BY p.tanggal DESC, p.id_pengambilan DESC
    ");
    ?>

    <div class="card">
        <div class="card-header">
            <h2>Riwayat Penjualan Sampah ke Pengepul</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Pengepul</th>
                    <th>Jenis Sampah</th>
                    <th>Berat (kg)</th>
                    <th>Total Nilai</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($q_admin) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($q_admin)): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nama_pengepul']) ?></td>
                        <td><?= htmlspecialchars($r['jenis_sampah']) ?></td>
                        <td><?= number_format($r['berat_kg'], 1) ?> kg</td>
                        <td>Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
                        <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; color:var(--ink-soft);">Belum ada riwayat penjualan ke pengepul</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>