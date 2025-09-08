<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

include 'koneksi.php';

$sql = "SELECT a.nik_user, a.nama_user, a.alamat, a.nohp, r.jabatan, a.id_admin 
        FROM admin a 
        JOIN role r ON a.id_admin = r.admin_id 
        WHERE a.STATUS = 'Aktif'"; 
$result = $conn->query($sql);

$isKetua = false;
if (isset($_SESSION["role"]) && $_SESSION["role"] === 'Ketua Koperasi') {
    $isKetua = true;
}

if (isset($_GET['delete'])) {
    $id_admin = $_GET['delete'];
    $sqlDelete = "UPDATE admin SET STATUS = 'Tidak Aktif' WHERE id_admin = $id_admin";
    
    if ($conn->query($sqlDelete) === TRUE) {
        $_SESSION['success_message'] = "Admin berhasil dihapus!";
        header("location: admin.php");
        exit;
    } else {
        $_SESSION['error_message'] = "Error saat menghapus admin: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Admin - Koperasi Simpan Pinjam</title>
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

            <?php if ($isKetua): ?>
                <li class="nav-item active">
                    <a class="nav-link" href="admin.php">
                        <i class="fas fa-fw fa-user-shield"></i>
                        <span>Admin</span>
                    </a>
                </li>
            <?php endif; ?>

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
                    <h1>Data Admin</h1>

                    <?php if ($isKetua): ?>
                        <a href='tambah-admin.php' class='btn btn-primary mb-3'>Tambahkan Admin</a>
                    <?php endif; ?>


                    <table class='table table-bordered'>
                        <thead>
                            <tr>
                                <th>NIK User</th>
                                <th>Nama User</th>
                                <th>Alamat</th>
                                <th>No HP</th>
                                <th>Jabatan</th>
                                <?php if ($isKetua): ?>
                                    <th>Aksi</th> 
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row["nik_user"] . "</td>";
                                    echo "<td>" . $row["nama_user"] . "</td>";
                                    echo "<td>" . $row["alamat"] . "</td>";
                                    echo "<td>" . $row["nohp"] . "</td>";
                                    echo "<td>" . $row["jabatan"] . "</td>"; 
                                    
                                    if ($isKetua) {
                                        if ($row["jabatan"] != 'Ketua Koperasi') { 
                                            echo "<td>";
                                            echo "<a href='update-admin.php?id_admin=".$row['id_admin']."' class='btn btn-warning'>Update</a> ";
                                            echo "<a href='admin.php?delete=".$row['id_admin']."' onclick=\"return confirm('Apakah Anda yakin ingin menghapus admin ini?');\" class='btn btn-danger'>Hapus</a>";
                                            echo "</td>";
                                        } else {
                                            echo "<td><em>Tidak dapat dihapus</em></td>";
                                        }
                                    }
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>Tidak ada data admin ditemukan.</td></tr>";
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
