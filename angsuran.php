<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "koneksi.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $pinjam_id = $_POST["pinjam_id"];
    $anggota_id = $_POST["anggota_id"];
    $angsuran_ke = $_POST["angsuran_ke"];
    $jh_angsuran = $_POST["jh_angsuran"];
    $denda = $_POST["denda"];
    $tgl_bayar = date("Y-m-d H:i:s");

    $sql = "INSERT INTO angsuran (admin_id, anggota_id, pinjam_id, angsuran_ke, jh_angsuran, denda, tgl_bayar) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iiissis", $_SESSION["admin_id"], $anggota_id, $pinjam_id, $angsuran_ke, $jh_angsuran, $denda, $tgl_bayar);
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_GET["delete"])) {
    $no_angsuran = $_GET["delete"];
    $conn->query("DELETE FROM angsuran WHERE no_angsuran = $no_angsuran");
    header("location: kelola_angsuran.php");
    exit;
}

$angsuran = $conn->query("
    SELECT a.no_angsuran, a.pinjam_id, a.anggota_id, a.angsuran_ke, a.jh_angsuran, a.tgl_bayar, 
           ang.no_ktp, ang.nama, a.tgl_jh_tempo
    FROM angsuran a
    JOIN anggota ang ON a.anggota_id = ang.id_anggota
    ORDER BY a.tgl_bayar DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Angsuran</title>
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
        <li class="nav-item">
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
        <li class="nav-item active">
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

                <h3 class="my-4">Riwayat Angsuran</h3>
                <div class="mb-4">
                    <a href="tambah_angsuran.php" class="btn btn-primary">Tambah Angsuran</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No KTP Anggota</th>
                                <th>Nama Anggota</th>
                                <th>Jumlah Angsuran</th>
                                <th>Angsuran Ke</th>
                                <th>Tanggal Jatuh Tempo</th>
                                <th>Tanggal Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $angsuran->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row["no_ktp"] ?></td>
                                    <td><?= $row["nama"] ?></td>
                                    <td><?= number_format($row["jh_angsuran"], 0, ',', '.') ?></td>
                                    <td><?= $row["angsuran_ke"] ?></td>
                                    <td><?= $row["tgl_jh_tempo"] ?></td>
                                    <td><?= $row["tgl_bayar"] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Menutup koneksi
$conn->close();
?>
