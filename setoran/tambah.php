<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../koneksi.php';
$judul_halaman = 'Ajukan Setoran';
$is_admin = ($_SESSION['role'] ?? '') === 'admin';

// Cuma admin (buat siapapun) atau siswa (buat dirinya sendiri) yang boleh ajukan
if (!in_array($_SESSION['role'] ?? '', ['admin', 'siswa'])) {
    header('Location: list.php?status=error&pesan=' . urlencode('Fitur ini cuma buat siswa/admin.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Siswa cuma boleh ajukan buat dirinya sendiri, gak peduli apa yang dikirim di form
    $id_user = $is_admin ? (int) $_POST['id_user'] : (int) $_SESSION['id_user'];
    $nominal = (float) $_POST['nominal'];
    $tanggal = $_POST['tanggal'];

    $sql = "INSERT INTO setoran (id_user, nominal, tanggal, status) VALUES ($id_user, $nominal, '$tanggal', 'pending')";
    if (mysqli_query($koneksi, $sql)) {
        header('Location: list.php?status=tambah');
        exit;
    } else {
        header('Location: list.php?status=error&pesan=' . urlencode(mysqli_error($koneksi)));
        exit;
    }
}

$menu_aktif = 'setoran';
include '../includes/header.php';

if ($is_admin) {
    $daftar_user = mysqli_query($koneksi, "SELECT id_user, nama, saldo FROM users WHERE role = 'siswa' ORDER BY nama ASC");
} else {
    $q_saldo = mysqli_query($koneksi, "SELECT saldo FROM users WHERE id_user = {$_SESSION['id_user']}");
    $saldo_saya = mysqli_fetch_assoc($q_saldo)['saldo'];
}
?>

<div class="card" style="max-width:480px;">
    <div class="card-header">
        <h2>Ajukan Setoran / Penarikan Saldo</h2>
    </div>
    <form method="POST">
        <?php if ($is_admin): ?>
            <div class="form-group">
                <label for="id_user">Nasabah</label>
                <select id="id_user" name="id_user" required>
                    <option value="">-- Pilih Nasabah --</option>
                    <?php while ($u = mysqli_fetch_assoc($daftar_user)): ?>
                        <option value="<?= $u['id_user'] ?>">
                            <?= htmlspecialchars($u['nama']) ?> (saldo: Rp <?= number_format($u['saldo'], 0, ',', '.') ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        <?php else: ?>
            <div class="form-group">
                <label>Saldo Kamu Saat Ini</label>
                <input type="text" value="Rp <?= number_format($saldo_saya, 0, ',', '.') ?>" disabled>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="nominal">Nominal (Rp)</label>
            <input type="number" id="nominal" name="nominal" step="1000" min="1000" required>
        </div>
        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Ajukan</button>
        <a href="list.php" class="btn btn-outline">Batal</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
