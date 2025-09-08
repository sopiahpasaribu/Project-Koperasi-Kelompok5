<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "koneksi.php";

if (isset($_GET["edit"]) && is_numeric($_GET["edit"])) {
    $no_angsuran = $_GET["edit"];

    $result = $conn->query("SELECT * FROM angsuran WHERE no_angsuran = $no_angsuran");

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        header("location: kelola_angsuran.php");
        exit;
    }
} else {
    header("location: kelola_angsuran.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pinjam_id = $_POST["pinjam_id"];
    $anggota_id = $_POST["anggota_id"];
    $angsuran_ke = $_POST["angsuran_ke"];
    $jh_angsuran = $_POST["jh_angsuran"];
    $denda = $_POST["denda"];

    $sql = "UPDATE angsuran SET pinjam_id = ?, anggota_id = ?, angsuran_ke = ?, jh_angsuran = ?, denda = ? WHERE no_angsuran = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iiissi", $pinjam_id, $anggota_id, $angsuran_ke, $jh_angsuran, $denda, $no_angsuran);
        $stmt->execute();
        $stmt->close();
        header("location: kelola_angsuran.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Angsuran</title>
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
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="container">
                <h2 class="my-4">Edit Angsuran</h2>
                <form method="post">
                    <div class="mb-3">
                        <label for="pinjam_id" class="form-label">ID Pinjaman</label>
                        <input type="number" name="pinjam_id" class="form-control" value="<?= htmlspecialchars($data['pinjam_id']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="anggota_id" class="form-label">ID Anggota</label>
                        <input type="number" name="anggota_id" class="form-control" value="<?= htmlspecialchars($data['anggota_id']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="angsuran_ke" class="form-label">Angsuran Ke-</label>
                        <input type="number" name="angsuran_ke" class="form-control" value="<?= htmlspecialchars($data['angsuran_ke']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="jh_angsuran" class="form-label">Jumlah Angsuran</label>
                        <input type="number" name="jh_angsuran" class="form-control" value="<?= htmlspecialchars($data['jh_angsuran']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="denda" class="form-label">Denda</label>
                        <input type="number" name="denda" class="form-control" value="<?= htmlspecialchars($data['denda']) ?>">
                    </div>
                    <button type="submit" class="btn btn-success">Update Angsuran</button>
                    <a href="kelola_angsuran.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
