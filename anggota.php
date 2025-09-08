<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

include("koneksi.php"); 

if ($conn === false) {
    die("ERROR: Tidak bisa terhubung ke database. " . mysqli_connect_error());
}

if (isset($_GET['delete'])) {
    $id_anggota = $_GET['delete'];

    $query_pinjaman = "SELECT COUNT(*) AS total FROM pinjaman WHERE anggota_id = '$id_anggota' AND status_pinjaman = 'aktif'";

    $result_pinjaman = mysqli_query($conn, $query_pinjaman);

    if (!$result_pinjaman) {
        die('Error executing query: ' . mysqli_error($conn));
    }

    $data_pinjaman = mysqli_fetch_assoc($result_pinjaman);

    $query_simpanan = "SELECT COUNT(*) AS total FROM simpanan_wajib WHERE anggota_id = '$id_anggota' AND saldo_keluar IS NOT NULL";
    
    $result_simpanan = mysqli_query($conn, $query_simpanan);

    if (!$result_simpanan) {
        die('Error executing query: ' . mysqli_error($conn));
    }

    $data_simpanan = mysqli_fetch_assoc($result_simpanan);

    if ($data_pinjaman['total'] > 0) {
        $_SESSION['error_message'] = "Anggota memiliki pinjaman aktif. Tidak dapat dinonaktifkan.";
    } elseif ($data_simpanan['total'] == 0) {
        $_SESSION['error_message'] = "Anggota belum melakukan penarikan simpanan. Tidak dapat dinonaktifkan.";
    } else {
        if ($conn->query("UPDATE anggota SET STATUS='non-aktif' WHERE id_anggota='$id_anggota'") === TRUE) {
            $_SESSION['success_message'] = "Data anggota berhasil dinonaktifkan!";
        } else {
            $_SESSION['error_message'] = "Error: " . $conn->error;
        }
    }
}
$result = $conn->query("SELECT * FROM anggota WHERE STATUS='aktif'");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Anggota - Koperasi Simpan Pinjam</title>
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
        <li class="nav-item active">
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
                <h1>Data Anggota</h1>
                <a href='tambah-anggota.php' class='btn btn-primary mb-3'>Tambah Anggota</a>
                <table class='table table-bordered'>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>No KTP</th>
                            <th>Alamat</th>
                            <th>Gambar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['nama']; ?></td>
                                <td><?php echo $row['no_ktp']; ?></td>
                                <td><?php echo $row['alamat']; ?></td>
                                <td><img src="<?php echo $row['alamat_gmb']; ?>" alt='Gambar' style='width: 100px;'></td>
                                <td><?php echo $row['STATUS']; ?></td>
                                <td><a href='anggota.php?delete=<?php echo $row['id_anggota']; ?>' class='btn btn-danger'>Hapus</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>

    <?php if (isset($_SESSION['success_message'])): ?>
        <script>alert('<?php echo $_SESSION['success_message']; ?>');</script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <script>alert('<?php echo $_SESSION['error_message']; ?>');</script>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

</body>

</html>

<?php
$conn->close();
?>