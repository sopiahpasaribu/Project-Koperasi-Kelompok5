<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "koneksi.php";

if (isset($_GET["delete"])) {
    $id_pinjam = $_GET["delete"];

    $delete_angsuran_sql = "DELETE FROM angsuran WHERE pinjam_id = ?";
    if ($stmt = $conn->prepare($delete_angsuran_sql)) {
        $stmt->bind_param("i", $id_pinjam);
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
            exit;
        }
        $stmt->close();
    }

    $delete_pinjaman_sql = "DELETE FROM pinjaman WHERE id_pinjam = ?";
    if ($stmt = $conn->prepare($delete_pinjaman_sql)) {
        $stmt->bind_param("i", $id_pinjam);
        if ($stmt->execute()) {
            header("location: pinjaman.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
}

$pinjaman = $conn->query("
    SELECT 
        p.id_pinjam, 
        a.no_ktp, 
        a.nama, 
        p.tgl_pinjam, 
        p.jh_pinjam, 
        p.jumlah_pinjaman,
        p.adm, 
        p.biaya_angsuran, 
        p.lma_angsur, 
        p.jh_angsur 
    FROM 
        pinjaman p
    JOIN anggota a ON p.anggota_id = a.id_anggota
    WHERE p.status_pinjaman = 'Aktif'  -- Tambahkan kondisi untuk status 'Aktif'
    ORDER BY p.tgl_pinjam DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjaman - Koperasi</title>
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
        <li class="nav-item  active">
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
                <h2 class="my-4">Kelola Pinjaman</h2>

                <div class="d-flex mb-4">
                    <a href="tambah_pinjaman.php" class="btn btn-primary me-2">Tambah Pinjaman</a>
                </div>

                <h3 class="my-4">Daftar Pinjaman</h3>
                <table class="table table-bordered">
                <thead>
    <tr>
        <th>NO KTP Anggota</th>
        <th>Nama Anggota</th>
        <th>Tanggal Pinjam</th>
        <th>Jatuh Tempo Pinjam</th>
        <th>Jumlah Pinjaman</th>
        <th>Administrasi</th>
        <th>Angsuran per-bulan</th>
        <th>Lama Angsuran</th>
        <th>Jatuh Tempo Angsuran</th>
    </tr>
</thead>
<tbody>
    <?php while ($row = $pinjaman->fetch_assoc()): ?>
        <tr>
            <td><?= $row["no_ktp"] ?></td>
            <td><?= $row["nama"] ?></td>
            <td><?= $row["tgl_pinjam"] ?></td>
            <td><?= $row["jh_pinjam"] ?></td>
            <td>Rp. <?= number_format($row["jumlah_pinjaman"], 0, ',', '.') ?></td>
            <td>Rp. <?= $row["adm"] ?></td>
            <td>Rp. <?= number_format($row["biaya_angsuran"], 0, ',', '.') ?></td>
            <td><?= $row["lma_angsur"] ?> Bulan</td>
            <td><?= $row["jh_angsur"] ?></td>
        </tr>
    <?php endwhile; ?>
</tbody>
                </table>
            </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>

<?php
$conn->close();
?>
