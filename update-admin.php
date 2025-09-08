<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

include("koneksi.php");

$isKetua = false;
if (isset($_SESSION["role"]) && $_SESSION["role"] === 'Ketua Koperasi') {
    $isKetua = true;
}

if (isset($_GET['id_admin'])) {
    $id_admin = $_GET['id_admin'];

    $sql = "SELECT a.*, r.jabatan FROM admin a JOIN role r ON a.id_admin = r.admin_id WHERE a.id_admin = '$id_admin'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $nik_user = $row['nik_user'];
        $nama_user = $row['nama_user'];
        $tmp_lahir = $row['tmp_lahir'];
        $tgl_lahir = $row['tgl_lahir'];
        $agama = $row['agama'];
        $j_kelamin = $row['j_kelamin'];
        $alamat = $row['alamat'];
        $nohp = $row['nohp'];
        $jabatan = $row['jabatan'];
    } else {
        $_SESSION['error_message'] = "Admin tidak ditemukan.";
        header("location: admin.php");
        exit;
    }
} else {
    $_SESSION['error_message'] = "ID Admin tidak valid.";
    header("location: admin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $isKetua) {
    $nik_user = $_POST['nik_user'];
    $nama_user = $_POST['nama_user'];
    $tmp_lahir = $_POST['tmp_lahir'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $agama = $_POST['agama'];
    $j_kelamin = $_POST['j_kelamin'];
    $alamat = $_POST['alamat'];
    $nohp = $_POST['nohp'];
    $jabatan = $_POST['jabatan'];

    $sqlAdminUpdate = "UPDATE admin SET nik_user='$nik_user', nama_user='$nama_user', tmp_lahir='$tmp_lahir', 
                       tgl_lahir='$tgl_lahir', agama='$agama', j_kelamin='$j_kelamin', alamat='$alamat', nohp='$nohp' 
                       WHERE id_admin='$id_admin'";

    if ($conn->query($sqlAdminUpdate) === TRUE) {
        $sqlRoleUpdate = "UPDATE role SET jabatan='$jabatan' WHERE admin_id='$id_admin'";
        
        if ($conn->query($sqlRoleUpdate) === TRUE) {
            $_SESSION['success_message'] = "Data admin berhasil diperbarui!";
            header("location: admin.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Error saat memperbarui data role: " . $conn->error;
        }
    } else {
        $_SESSION['error_message'] = "Error saat memperbarui data admin: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Admin - Koperasi Simpan Pinjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a7c7e7, #66a3d2); 
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 900px;
            margin-top: 50px;
        }
        .form-control {
            height: 35px;
        }
        .form-label {
            font-weight: 600;
        }
        .btn-primary {
            padding: 10px 20px;
            font-size: 14px;
        }
        .alert {
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h3 class="text-center mb-4">Update Admin</h3>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $_SESSION['error_message']; ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nik_user" class="form-label">NIK User</label>
                        <input type="text" class="form-control" id="nik_user" name="nik_user" value="<?php echo htmlspecialchars($nik_user); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama_user" class="form-label">Nama User</label>
                        <input type="text" class="form-control" id="nama_user" name="nama_user" value="<?php echo htmlspecialchars($nama_user); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="tmp_lahir" class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control" id="tmp_lahir" name="tmp_lahir" value="<?php echo htmlspecialchars($tmp_lahir); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <input type="text" class="form-control" id="alamat" name="alamat" value="<?php echo htmlspecialchars($alamat); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="nohp" class="form-label">No HP</label>
                        <input type="text" class="form-control" id="nohp" name="nohp" value="<?php echo htmlspecialchars($nohp); ?>" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir" value="<?php echo htmlspecialchars($tgl_lahir); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="agama" class="form-label">Agama</label>
                        <input type="text" class="form-control" id="agama" name="agama" value="<?php echo htmlspecialchars($agama); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="j_kelamin" class="form-label">Jenis Kelamin</label>
                        <select id="j_kelamin" name="j_kelamin" class="form-select" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" <?php echo ($j_kelamin == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo ($j_kelamin == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="jabatan" class="form-label">Jabatan</label>
                        <select id="jabatan" name="jabatan" class="form-select" required>
                            <option value="">Pilih Jabatan</option>
                            <option value="Sekertaris" <?php echo ($jabatan == 'Sekertaris') ? 'selected' : ''; ?>>Sekertaris</option>
                            <option value="Bendahara" <?php echo ($jabatan == 'Bendahara') ? 'selected' : ''; ?>>Bendahara</option>
                            <option value="Unit Usaha" <?php echo ($jabatan == 'Unit Usaha') ? 'selected' : ''; ?>>Unit Usaha</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Update Admin</button>
            <a href="admin.php" class="btn btn-secondary w-100 mt-2">Kembali</a>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>
