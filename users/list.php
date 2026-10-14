<?php
$base = '../';
require_once '../includes/auth.php';
require_once '../includes/admin_only.php';
require_once '../koneksi.php';
$judul_halaman = 'Pengguna';
$menu_aktif = 'users';
include '../includes/header.php';
?>

<?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] == 'tambah'): ?>
        <div class="alert alert-success">Akun berhasil ditambahkan.</div>
    <?php elseif ($_GET['status'] == 'edit'): ?>
        <div class="alert alert-success">Akun berhasil diperbarui.</div>
    <?php elseif ($_GET['status'] == 'hapus'): ?>
        <div class="alert alert-success">Akun berhasil dihapus.</div>
    <?php elseif ($_GET['status'] == 'error'): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['pesan'] ?? 'Terjadi kesalahan.') ?></div>
    <?php endif; ?>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Daftar Pengguna</h2>
        <a href="tambah.php" class="btn btn-primary">+ Tambah Akun</a>
    </div>

    <form method="GET" class="toolbar-form">
        <input type="text" name="q" placeholder="Cari nama/username..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <select name="role">
            <option value="">Semua Role</option>
            <option value="admin" <?= ($_GET['role'] ?? '') == 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="guru" <?= ($_GET['role'] ?? '') == 'guru' ? 'selected' : '' ?>>Guru</option>
            <option value="pengepul" <?= ($_GET['role'] ?? '') == 'pengepul' ? 'selected' : '' ?>>Pengepul</option>
            <option value="siswa" <?= ($_GET['role'] ?? '') == 'siswa' ? 'selected' : '' ?>>Siswa</option>
        </select>
        <button type="submit" class="btn btn-outline">Filter</button>
        <?php if (!empty($_GET['q']) || !empty($_GET['role'])): ?><a href="list.php" class="btn btn-outline">Reset</a><?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Username</th>
                <th>Role</th>
                <th>Saldo</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $cari = trim($_GET['q'] ?? '');
            $role_filter = $_GET['role'] ?? '';

            $kondisi = [];
            if ($cari !== '') {
                $cari_esc = mysqli_real_escape_string($koneksi, $cari);
                $kondisi[] = "(nama LIKE '%$cari_esc%' OR username LIKE '%$cari_esc%')";
            }
            if (in_array($role_filter, ['admin', 'guru', 'pengepul', 'siswa'])) {
                $kondisi[] = "role = '$role_filter'";
            }
            $ada_filter = count($kondisi) > 0;
            $where = $ada_filter ? 'WHERE ' . implode(' AND ', $kondisi) : '';

            $q = mysqli_query($koneksi, "SELECT * FROM users $where ORDER BY role ASC, nama ASC");
            if (mysqli_num_rows($q) > 0):
                while ($r = mysqli_fetch_assoc($q)):
            ?>
                <tr>
                    <td><?= htmlspecialchars($r['nama']) ?></td>
                    <td><?= htmlspecialchars($r['username']) ?></td>
                    <td><span class="status <?= $r['role'] ?>"><?= ucfirst($r['role']) ?></span></td>
                    <td><?= $r['role'] === 'siswa' ? 'Rp ' . number_format($r['saldo'], 0, ',', '.') : '-' ?></td>
                    <td>
                        <a href="edit.php?id=<?= $r['id_user'] ?>" class="btn btn-outline">Edit</a>
                        <?php if ($r['id_user'] != ($_SESSION['id_user'] ?? 0)): ?>
                            <a href="hapus.php?id=<?= $r['id_user'] ?>" class="btn btn-danger"
                               onclick="return confirm('Yakin mau hapus akun ini?')">Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="5" style="text-align:center; color:var(--ink-soft);"><?= $ada_filter ? 'Gak ada akun yang cocok dengan filter' : 'Belum ada akun' ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
