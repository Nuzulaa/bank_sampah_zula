<?php
$base = '';
require_once 'includes/auth.php';
require_once 'koneksi.php';
$judul_halaman = 'Dashboard';
$menu_aktif = 'dashboard';
$role = $_SESSION['role'] ?? '';
include 'includes/header.php';
?>

<?php if (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['pesan'] ?? 'Terjadi kesalahan.') ?></div>
<?php endif; ?>

<?php if ($role === 'admin'): ?>

    <?php
    // ================= DASHBOARD ADMIN =================
    $q_sampah = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM sampah");
    $total_sampah = mysqli_fetch_assoc($q_sampah)['total'];

    $q_user = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role = 'siswa'");
    $total_user = mysqli_fetch_assoc($q_user)['total'];

    $q_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi WHERE MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())");
    $row_transaksi = mysqli_fetch_assoc($q_transaksi);

    $q_saldo = mysqli_query($koneksi, "SELECT COALESCE(SUM(saldo),0) as total FROM users WHERE role = 'siswa'");
    $total_saldo = mysqli_fetch_assoc($q_saldo)['total'];

    // Total Deposit Pengepul
    $q_saldo_pengepul = mysqli_query($koneksi, "SELECT COALESCE(SUM(saldo),0) as total FROM users WHERE role = 'pengepul'");
    $total_saldo_pengepul = mysqli_fetch_assoc($q_saldo_pengepul)['total'];

    // Daftar Detail Saldo Pengepul
    $q_list_pengepul = mysqli_query($koneksi, "SELECT nama, username, saldo FROM users WHERE role = 'pengepul'");

    // Data Grafik 6 Bulan Terakhir
    $label_bulan = [];
    $data_setor  = [];
    $data_jual   = [];

    for ($i = 5; $i >= 0; $i--) {
        $bln_num  = date('m', strtotime("-$i month"));
        $thn_num  = date('Y', strtotime("-$i month"));
        $bln_nama = date('M Y', strtotime("-$i month"));

        $label_bulan[] = $bln_nama;

        $q_st = mysqli_query($koneksi, "SELECT COALESCE(SUM(berat_kg),0) as total FROM transaksi WHERE tipe = 'setor' AND MONTH(tanggal) = '$bln_num' AND YEAR(tanggal) = '$thn_num'");
        $data_setor[] = (float) mysqli_fetch_assoc($q_st)['total'];

        $q_jl = mysqli_query($koneksi, "SELECT COALESCE(SUM(berat_kg),0) as total FROM pengambilan_sampah WHERE MONTH(tanggal) = '$bln_num' AND YEAR(tanggal) = '$thn_num'");
        $data_jual[] = (float) mysqli_fetch_assoc($q_jl)['total'];
    }

    $q_terbaru = mysqli_query($koneksi, "
        SELECT t.id_transaksi, u.nama, s.jenis_sampah, t.tipe, t.berat_kg, t.total_rp, t.tanggal
        FROM transaksi t
        JOIN users u ON t.id_user = u.id_user
        JOIN sampah s ON t.id_sampah = s.id_sampah
        ORDER BY t.tanggal DESC, t.id_transaksi DESC
        LIMIT 5
    ");
    ?>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="label">Jenis Sampah</div>
            <div class="value"><span class="stat-number" data-target="<?= $total_sampah ?>" data-format="int">0</span> <small>jenis</small></div>
        </div>
        <div class="stat-card">
            <div class="label">Siswa Terdaftar</div>
            <div class="value"><span class="stat-number" data-target="<?= $total_user ?>" data-format="int">0</span> <small>orang</small></div>
        </div>
        <div class="stat-card">
            <div class="label">Transaksi Bulan Ini</div>
            <div class="value"><span class="stat-number" data-target="<?= $row_transaksi['total'] ?>" data-format="int">0</span> <small>kali</small></div>
        </div>
        <div class="stat-card saldo">
            <div class="label">Total Saldo Siswa</div>
            <div class="value"><span class="stat-number" data-target="<?= $total_saldo ?>" data-format="currency">Rp 0</span></div>
        </div>
        <div class="stat-card saldo" style="border-left: 4px solid var(--accent, #3498db);">
            <div class="label">Total Deposit Pengepul</div>
            <div class="value"><span class="stat-number" data-target="<?= $total_saldo_pengepul ?>" data-format="currency">Rp 0</span></div>
        </div>
    </div>

    <!-- CARD GRAFIK SETORAN VS PENJUALAN -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>📈 Grafik Setoran vs Penjualan Sampah (kg)</h2>
            <small style="color: var(--ink-soft);">6 Bulan Terakhir</small>
        </div>
        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="grafikBankSampah"></canvas>
        </div>
    </div>

    <!-- TABEL SALDO DEPOSIT PENGEPUL -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>💰 Saldo Deposit Pengepul</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Nama Pengepul</th>
                    <th>Username</th>
                    <th>Saldo Deposit</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($q_list_pengepul) > 0): ?>
                    <?php while ($p = mysqli_fetch_assoc($q_list_pengepul)): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nama']) ?></td>
                        <td><?= htmlspecialchars($p['username']) ?></td>
                        <td><strong>Rp <?= number_format($p['saldo'], 0, ',', '.') ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align:center; color:var(--ink-soft);">Belum ada akun pengepul terdaftar</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Transaksi Terbaru</h2>
            <a href="transaksi/list.php" class="btn btn-outline">Lihat semua</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Jenis Sampah</th>
                    <th>Tipe</th>
                    <th>Berat (kg)</th>
                    <th>Total (Rp)</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($q_terbaru) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($q_terbaru)): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nama']) ?></td>
                        <td><?= htmlspecialchars($r['jenis_sampah']) ?></td>
                        <td><?= ucfirst($r['tipe']) ?></td>
                        <td><?= number_format($r['berat_kg'], 1) ?></td>
                        <td>Rp <?= number_format($r['total_rp'], 0, ',', '.') ?></td>
                        <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; color:var(--ink-soft);">Belum ada transaksi</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('grafikBankSampah');
        if (el && typeof Chart !== 'undefined') {
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($label_bulan) ?>,
                    datasets: [
                        {
                            label: 'Masuk / Disetor Siswa (kg)',
                            data: <?= json_encode($data_setor) ?>,
                            backgroundColor: 'rgba(46, 204, 113, 0.75)',
                            borderColor: '#2ecc71',
                            borderWidth: 2,
                            borderRadius: 6
                        },
                        {
                            label: 'Keluar / Dibeli Pengepul (kg)',
                            data: <?= json_encode($data_jual) ?>,
                            backgroundColor: 'rgba(52, 152, 219, 0.75)',
                            borderColor: '#3498db',
                            borderWidth: 2,
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    });
    </script>

<?php elseif ($role === 'guru'): ?>

    <?php
    $q_user = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role = 'siswa'");
    $total_siswa = mysqli_fetch_assoc($q_user)['total'];

    $q_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi WHERE MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())");
    $total_transaksi_bulan = mysqli_fetch_assoc($q_transaksi)['total'];

    $q_saldo = mysqli_query($koneksi, "SELECT COALESCE(SUM(saldo),0) as total FROM users WHERE role = 'siswa'");
    $total_saldo = mysqli_fetch_assoc($q_saldo)['total'];

    $q_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM setoran WHERE status = 'pending'");
    $total_pending = mysqli_fetch_assoc($q_pending)['total'];

    $q_pending_list = mysqli_query($koneksi, "
        SELECT st.id_setoran, u.nama, st.nominal, st.tanggal
        FROM setoran st
        JOIN users u ON st.id_user = u.id_user
        WHERE st.status = 'pending'
        ORDER BY st.tanggal ASC
        LIMIT 5
    ");
    ?>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="label">Siswa Terdaftar</div>
            <div class="value"><span class="stat-number" data-target="<?= $total_siswa ?>" data-format="int">0</span> <small>orang</small></div>
        </div>
        <div class="stat-card">
            <div class="label">Transaksi Bulan Ini</div>
            <div class="value"><span class="stat-number" data-target="<?= $total_transaksi_bulan ?>" data-format="int">0</span> <small>kali</small></div>
        </div>
        <div class="stat-card saldo">
            <div class="label">Total Saldo Siswa</div>
            <div class="value"><span class="stat-number" data-target="<?= $total_saldo ?>" data-format="currency">Rp 0</span></div>
        </div>
        <div class="stat-card">
            <div class="label">Pengajuan Pending</div>
            <div class="value"><span class="stat-number" data-target="<?= $total_pending ?>" data-format="int">0</span> <small>pengajuan</small></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Pengajuan Menunggu Persetujuan</h2>
            <a href="setoran/list.php" class="btn btn-outline">Lihat semua</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Nominal</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($q_pending_list) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($q_pending_list)): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nama']) ?></td>
                        <td>Rp <?= number_format($r['nominal'], 0, ',', '.') ?></td>
                        <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                        <td>
                            <a href="setoran/proses.php?id=<?= $r['id_setoran'] ?>&aksi=setuju" class="btn btn-primary" onclick="return confirm('Setujui pengajuan ini?')">Setujui</a>
                            <a href="setoran/proses.php?id=<?= $r['id_setoran'] ?>&aksi=tolak" class="btn btn-danger" onclick="return confirm('Tolak pengajuan ini?')">Tolak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; color:var(--ink-soft);">Tidak ada pengajuan pending</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($role === 'pengepul'): ?>

    <?php
    $id_saya = (int) $_SESSION['id_user'];
    $q_my_saldo = mysqli_query($koneksi, "SELECT saldo FROM users WHERE id_user = $id_saya");
    $saldo_saya = mysqli_fetch_assoc($q_my_saldo)['saldo'] ?? 0;

    $q_stok = mysqli_query($koneksi, "SELECT COALESCE(SUM(stok_kg),0) as total FROM sampah");
    $total_stok = mysqli_fetch_assoc($q_stok)['total'];

    $q_ambil_bulan = mysqli_query($koneksi, "SELECT COALESCE(SUM(berat_kg),0) as total_kg FROM pengambilan_sampah WHERE id_user = $id_saya AND MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())");
    $row_ambil = mysqli_fetch_assoc($q_ambil_bulan);

    $q_riwayat = mysqli_query($koneksi, "
        SELECT s.jenis_sampah, p.berat_kg, p.total_harga, p.tanggal
        FROM pengambilan_sampah p
        JOIN sampah s ON p.id_sampah = s.id_sampah
        WHERE p.id_user = $id_saya
        ORDER BY p.tanggal DESC, p.id_pengambilan DESC
        LIMIT 5
    ");
    ?>

    <div class="stat-grid">
        <div class="stat-card saldo">
            <div class="label">Saldo Deposit Saya</div>
            <div class="value"><span class="stat-number" data-target="<?= $saldo_saya ?>" data-format="currency">Rp 0</span></div>
        </div>
        <div class="stat-card">
            <div class="label">Total Stok Tersedia di Admin</div>
            <div class="value"><span class="stat-number" data-target="<?= $total_stok ?>" data-format="decimal">0</span> <small>kg</small></div>
        </div>
        <div class="stat-card">
            <div class="label">Dibeli Bulan Ini</div>
            <div class="value"><span class="stat-number" data-target="<?= $row_ambil['total_kg'] ?>" data-format="decimal">0</span> <small>kg</small></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Riwayat Pembelian Terbaru</h2>
            <a href="stok/list.php" class="btn btn-outline">Beli Sampah</a>
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
                <?php if (mysqli_num_rows($q_riwayat) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($q_riwayat)): ?>
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

<?php else: ?>

    <?php
    $id_saya = (int) $_SESSION['id_user'];
    $q_saldo = mysqli_query($koneksi, "SELECT saldo FROM users WHERE id_user = $id_saya");
    $saldo_saya = mysqli_fetch_assoc($q_saldo)['saldo'];

    $q_setor = mysqli_query($koneksi, "SELECT COALESCE(SUM(berat_kg),0) as total_kg, COUNT(*) as jumlah FROM transaksi WHERE id_user = $id_saya AND tipe = 'setor'");
    $row_setor = mysqli_fetch_assoc($q_setor);

    $q_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM setoran WHERE id_user = $id_saya AND status = 'pending'");
    $total_pending = mysqli_fetch_assoc($q_pending)['total'];

    $q_riwayat = mysqli_query($koneksi, "
        SELECT s.jenis_sampah, t.tipe, t.berat_kg, t.total_rp, t.tanggal
        FROM transaksi t
        JOIN sampah s ON t.id_sampah = s.id_sampah
        WHERE t.id_user = $id_saya
        ORDER BY t.tanggal DESC, t.id_transaksi DESC
        LIMIT 5
    ");
    ?>

    <div class="stat-grid">
        <div class="stat-card saldo">
            <div class="label">Saldo Saya</div>
            <div class="value"><span class="stat-number" data-target="<?= $saldo_saya ?>" data-format="currency">Rp 0</span></div>
        </div>
        <div class="stat-card">
            <div class="label">Total Sampah Disetor</div>
            <div class="value"><span class="stat-number" data-target="<?= $row_setor['total_kg'] ?>" data-format="decimal">0</span> <small>kg</small></div>
        </div>
        <div class="stat-card">
            <div class="label">Jumlah Transaksi</div>
            <div class="value"><span class="stat-number" data-target="<?= $row_setor['jumlah'] ?>" data-format="int">0</span> <small>kali</small></div>
        </div>
        <div class="stat-card">
            <div class="label">Pengajuan Pending</div>
            <div class="value"><span class="stat-number" data-target="<?= $total_pending ?>" data-format="int">0</span> <small>pengajuan</small></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Riwayat Transaksi Saya</h2>
            <a href="setoran/list.php" class="btn btn-outline">Cek Setoran Saldo</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Jenis Sampah</th>
                    <th>Tipe</th>
                    <th>Berat (kg)</th>
                    <th>Total (Rp)</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($q_riwayat) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($q_riwayat)): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['jenis_sampah']) ?></td>
                        <td><?= ucfirst($r['tipe']) ?></td>
                        <td><?= number_format($r['berat_kg'], 1) ?></td>
                        <td>Rp <?= number_format($r['total_rp'], 0, ',', '.') ?></td>
                        <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; color:var(--ink-soft);">Belum ada transaksi</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>