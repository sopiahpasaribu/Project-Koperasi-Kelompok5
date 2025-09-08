<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

include 'koneksi.php';

$sql = "SELECT a.nama AS nama_anggota, s.tgl_transaksi, s.saldo_masuk, s.jumlah_saldo, s.saldo_keluar, s.jh_simpanan, s.id_simpan, ad.nama_user AS nama_admin
        FROM simpanan_wajib s
        JOIN anggota a ON s.anggota_id = a.id_anggota
        JOIN admin ad ON s.admin_id = ad.id_admin
        WHERE a.status = 'aktif' 
        ORDER BY s.id_simpan";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Simpanan - Koperasi Simpan Pinjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.0.7/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body>
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Koperasi</div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item active">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <li class="nav-item active">
                <a class="nav-link" href="simpanan.php">
                    <i class="fas fa-fw fa-wallet"></i>
                    <span>Simpanan</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="pinjaman.php">
                    <i class="fas fa-fw fa-hand-holding-usd"></i>
                    <span>Pinjaman</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="anggota.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Anggota</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="admin.php">
                    <i class="fas fa-fw fa-user-shield"></i>
                    <span>Admin</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="angsuran.php">
                    <i class="fas fa-fw fa-money-check-alt"></i>
                    <span>Angsuran</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-fw fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Selamat datang, <?php echo $_SESSION["username"]; ?>
                <i class="fas fa-user-circle fa-2x text-primary" style="padding:15px"></i>
            </a>
        </li>
    </ul>
</nav>

                <div class="container-fluid">
                    <h1>Data Simpanan</h1>

                    <a href="tambah_simpanan.php" class="btn btn-primary mb-3">Tambah Simpanan</a>
                    <a href='tarik_simpanan.php?id_simpan=" . $row["id_simpan"] . "' class='btn btn-info mb-3'>Tarik Simpanan</a>

                    <table class='table table-bordered'>
                        <thead>
                            <tr>
                                <th>Nama Anggota</th>
                                <th>Tanggal Transaksi</th>
                                <th>Saldo Masuk</th>
                                <th>Jumlah Saldo</th>
                                <th>Saldo Keluar</th>
                                <th>Jatuh Tempo Simpanan</th>
                                <th>Admin Penambah Simpanan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row["nama_anggota"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["tgl_transaksi"]) . "</td>";
                                    echo "<td>" . number_format($row["saldo_masuk"], 2, ',', '.') . "</td>";
                                    echo "<td>" . number_format($row["jumlah_saldo"], 2, ',', '.') . "</td>";
                                    echo "<td>" . number_format($row["saldo_keluar"], 2, ',', '.') . "</td>";
                                    echo "<td>" . htmlspecialchars($row["jh_simpanan"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["nama_admin"]) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>Tidak ada data simpanan ditemukan.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>

</body>

</html>

<?php
$conn->close();
?>