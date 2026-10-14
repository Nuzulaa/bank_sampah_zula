<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../koneksi.php';
$judul_halaman = 'Setoran Saldo';
$menu_aktif = 'setoran';
$role = $_SESSION['role'] ?? '';

// Pengepul gak relevan sama modul ini, lempar balik ke dashboard
if ($role === 'pengepul') {
    header('Location: ../index.php?status=error&pesan=' . urlencode('Halaman ini gak berlaku buat akun kamu.'));
    exit;
}

$is_finance = in_array($role, ['admin', 'guru']); // boleh lihat semua + approve/tolak
include '../includes/header.php';
?>

<?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] == 'tambah'): ?>
        <div class="alert alert-success">Pengajuan setoran berhasil dibuat.</div>
    <?php elseif ($_GET['status'] == 'disetujui'): ?>
        <div class="alert alert-success">Pengajuan disetujui, saldo siswa sudah dipotong.</div>
    <?php elseif ($_GET['status'] == 'ditolak'): ?>
        <div class="alert alert-success">Pengajuan ditolak.</div>
    <?php elseif ($_GET['status'] == 'error'): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['pesan'] ?? 'Terjadi kesalahan.') ?></div>
    <?php endif; ?>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><?= $is_finance ? 'Semua Pengajuan Setoran / Penarikan' : 'Pengajuan Saya' ?></h2>
        <?php if ($role === 'siswa' || $role === 'admin'): ?>
            <a href="tambah.php" class="btn btn-primary">+ Ajukan Setoran</a>
        <?php endif; ?>
    </div>

    <?php if ($is_finance): ?>
    <form method="GET" class="toolbar-form">
        <input type="text" name="q" placeholder="Cari nama siswa..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <select name="status">
            <option value="">Semua Status</option>
            <option value="pending" <?= ($_GET['status_filter'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="disetujui" <?= ($_GET['status_filter'] ?? '') == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
            <option value="ditolak" <?= ($_GET['status_filter'] ?? '') == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
        </select>
        <button type="submit" class="btn btn-outline">Filter</button>
        <?php if (!empty($_GET['q']) || !empty($_GET['status_filter'])): ?><a href="list.php" class="btn btn-outline">Reset</a><?php endif; ?>
    </form>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <?php if ($is_finance): ?><th>Nasabah</th><?php endif; ?>
                <th>Nominal</th>
                <th>Tanggal</th>
                <th>Status</th>
                <?php if ($is_finance): ?><th>Aksi</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $ada_filter = false;
            if ($is_finance) {
                $cari   = trim($_GET['q'] ?? '');
                $status_filter = $_GET['status_filter'] ?? '';
                $kondisi = [];
                if ($cari !== '') {
                    $cari_esc = mysqli_real_escape_string($koneksi, $cari);
                    $kondisi[] = "u.nama LIKE '%$cari_esc%'";
                }
                if (in_array($status_filter, ['pending', 'disetujui', 'ditolak'])) {
                    $kondisi[] = "st.status = '$status_filter'";
                }
                $ada_filter = count($kondisi) > 0;
                $where = $ada_filter ? 'WHERE ' . implode(' AND ', $kondisi) : '';

                $q = mysqli_query($koneksi, "
                    SELECT st.id_setoran, u.nama, st.nominal, st.tanggal, st.status
                    FROM setoran st
                    JOIN users u ON st.id_user = u.id_user
                    $where
                    ORDER BY st.tanggal DESC, st.id_setoran DESC
                ");
            } else {
                $id_saya = (int) $_SESSION['id_user'];
                $q = mysqli_query($koneksi, "
                    SELECT st.id_setoran, st.nominal, st.tanggal, st.status
                    FROM setoran st
                    WHERE st.id_user = $id_saya
                    ORDER BY st.tanggal DESC, st.id_setoran DESC
                ");
            }

            $kolom_kosong = $is_finance ? 5 : 3;

            if (mysqli_num_rows($q) > 0):
                while ($r = mysqli_fetch_assoc($q)):
            ?>
                <tr>
                    <?php if ($is_finance): ?><td><?= htmlspecialchars($r['nama']) ?></td><?php endif; ?>
                    <td>Rp <?= number_format($r['nominal'], 0, ',', '.') ?></td>
                    <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                    <td><span class="status <?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                    <?php if ($is_finance): ?>
                    <td>
                        <?php if ($r['status'] === 'pending'): ?>
                            <a href="proses.php?id=<?= $r['id_setoran'] ?>&aksi=setuju" class="btn btn-primary"
                               onclick="return confirm('Setujui pengajuan ini? Saldo siswa akan dipotong.')">Setujui</a>
                            <a href="proses.php?id=<?= $r['id_setoran'] ?>&aksi=tolak" class="btn btn-danger"
                               onclick="return confirm('Tolak pengajuan ini?')">Tolak</a>
                        <?php else: ?>
                            <span style="color:var(--ink-soft); font-size:13px;">-</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="<?= $kolom_kosong ?>" style="text-align:center; color:var(--ink-soft);"><?= $ada_filter ? 'Gak ada pengajuan yang cocok dengan filter' : 'Belum ada pengajuan setoran' ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
