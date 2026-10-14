<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../koneksi.php';
$judul_halaman = 'Top Up Saldo Pengepul';
$menu_aktif = 'topup';
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
        <div class="alert alert-success"><?= htmlspecialchars($_GET['pesan'] ?? 'Berhasil disimpan.') ?></div>
    <?php elseif ($_GET['status'] === 'error'): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['pesan'] ?? 'Terjadi kesalahan.') ?></div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($role === 'pengepul'): ?>
    <?php
    $q_saldo = mysqli_query($koneksi, "SELECT saldo FROM users WHERE id_user = $id_saya");
    $saldo_saya = mysqli_fetch_assoc($q_saldo)['saldo'] ?? 0;

    $q_history = mysqli_query($koneksi, "SELECT * FROM topup_pengepul WHERE id_user = $id_saya ORDER BY tanggal DESC, id_topup DESC");
    ?>

    <div class="card" style="max-width: 500px; margin-bottom: 24px;">
        <div class="card-header">
            <h2>Form Top Up Deposit</h2>
            <span class="status status-info">Saldo Deposit: Rp <?= number_format($saldo_saya, 0, ',', '.') ?></span>
        </div>
        <form action="proses.php?aksi=tambah" method="POST">
            <div class="form-group">
                <label for="nominal">Nominal Top Up (Rp)</label>
                <input type="number" id="nominal" name="nominal" min="10000" step="1000" placeholder="Contoh: 100000" required>
            </div>
            <button type="submit" class="btn btn-primary">Ajukan Top Up</button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Riwayat Pengajuan Top Up</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nominal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($q_history) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($q_history)): ?>
                    <tr>
                        <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                        <td>Rp <?= number_format($r['nominal'], 0, ',', '.') ?></td>
                        <td><span class="status <?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align:center; color:var(--ink-soft);">Belum ada riwayat top up</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($role === 'admin'): ?>
    <?php
    $q_admin = mysqli_query($koneksi, "
        SELECT tp.*, u.nama, u.saldo as saldo_saat_ini 
        FROM topup_pengepul tp 
        JOIN users u ON tp.id_user = u.id_user 
        ORDER BY FIELD(tp.status, 'pending', 'disetujui', 'ditolak'), tp.tanggal DESC
    ");
    ?>

    <div class="card">
        <div class="card-header">
            <h2>Persetujuan Top Up Pengepul</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Pengepul</th>
                    <th>Saldo Saat Ini</th>
                    <th>Nominal Top Up</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($q_admin) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($q_admin)): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nama']) ?></td>
                        <td><strong>Rp <?= number_format($r['saldo_saat_ini'], 0, ',', '.') ?></strong></td>
                        <td>Rp <?= number_format($r['nominal'], 0, ',', '.') ?></td>
                        <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                        <td><span class="status <?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                        <td>
                            <?php if ($r['status'] === 'pending'): ?>
                                <a href="proses.php?aksi=setuju&id=<?= $r['id_topup'] ?>" class="btn btn-primary" onclick="return confirm('Setujui top up ini?')">Setujui</a>
                                <a href="proses.php?aksi=tolak&id=<?= $r['id_topup'] ?>" class="btn btn-danger" onclick="return confirm('Tolak top up ini?')">Tolak</a>
                            <?php else: ?>
                                <span style="color:var(--ink-soft);">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; color:var(--ink-soft);">Belum ada pengajuan top up</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>