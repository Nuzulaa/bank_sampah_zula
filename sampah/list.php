<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../includes/admin_only.php';
require_once '../koneksi.php';
$judul_halaman = 'Jenis Sampah';
$menu_aktif = 'sampah';
include '../includes/header.php';
?>

<?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] == 'tambah'): ?>
        <div class="alert alert-success">Jenis sampah berhasil ditambahkan.</div>
    <?php elseif ($_GET['status'] == 'edit'): ?>
        <div class="alert alert-success">Jenis sampah berhasil diperbarui.</div>
    <?php elseif ($_GET['status'] == 'hapus'): ?>
        <div class="alert alert-success">Jenis sampah berhasil dihapus.</div>
    <?php elseif ($_GET['status'] == 'error'): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['pesan'] ?? 'Terjadi kesalahan.') ?></div>
    <?php endif; ?>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Daftar Jenis Sampah</h2>
        <a href="tambah.php" class="btn btn-primary">+ Tambah Jenis Sampah</a>
    </div>

    <form method="GET" class="toolbar-form">
        <input type="text" name="q" placeholder="Cari jenis sampah..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit" class="btn btn-outline">Cari</button>
        <?php if (!empty($_GET['q'])): ?><a href="list.php" class="btn btn-outline">Reset</a><?php endif; ?>
    </form>
    
    <div class="form-group">
    <label for="kategori">Kategori Sampah</label>
    <select id="kategori" name="kategori" required>
        <option value="Plastik">Plastik (Botol, Gelas, dll)</option>
        <option value="Kertas">Kertas (Kardus, HVS, Koran)</option>
        <option value="Logam">Logam (Besi, Kaleng, Tembaga)</option>
        <option value="Kaca">Kaca (Botol Kaca, Cermin)</option>
        <option value="Lainnya">Lainnya</option>
    </select>
</div>

    <table>
        <thead>
            <tr>
                <th>Jenis Sampah</th>
                <th>Harga / kg</th>
                <th>Stok (kg)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $cari = trim($_GET['q'] ?? '');
            if ($cari !== '') {
                $cari_esc = mysqli_real_escape_string($koneksi, $cari);
                $q = mysqli_query($koneksi, "SELECT * FROM sampah WHERE jenis_sampah LIKE '%$cari_esc%' ORDER BY jenis_sampah ASC");
            } else {
                $q = mysqli_query($koneksi, "SELECT * FROM sampah ORDER BY jenis_sampah ASC");
            }
            if (mysqli_num_rows($q) > 0):
                while ($r = mysqli_fetch_assoc($q)):
            ?>
                <tr>
                    <td><?= htmlspecialchars($r['jenis_sampah']) ?></td>
                    <td>Rp <?= number_format($r['harga_per_kg'], 0, ',', '.') ?></td>
                    <td><?= number_format($r['stok_kg'], 1) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $r['id_sampah'] ?>" class="btn btn-outline">Edit</a>
                        <a href="hapus.php?id=<?= $r['id_sampah'] ?>" class="btn btn-danger"
                           onclick="return confirm('Yakin mau hapus jenis sampah ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="4" style="text-align:center; color:var(--ink-soft);"><?= $cari !== '' ? 'Gak ada jenis sampah yang cocok dengan pencarian' : 'Belum ada data jenis sampah' ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
