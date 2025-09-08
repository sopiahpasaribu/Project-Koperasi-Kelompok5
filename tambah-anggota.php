<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

include("koneksi.php");

$target_dir = "uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $no_ktp = $_POST['no_ktp'];
    $nama = $_POST['nama'];
    $tmp_lahir = $_POST['tmp_lahir'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $j_kelamin = $_POST['j_kelamin'];
    $agama = $_POST['agama'];
    $alamat = $_POST['alamat'];
    $telp = $_POST['telp'];
    $pekerjaan = $_POST['pekerjaan'];
    $status = 'aktif'; 
    $simpanan_pokok = $_POST['simpanan_pokok'];

    $check_sql = "SELECT * FROM anggota WHERE no_ktp = ?";
    if ($stmt_check = $conn->prepare($check_sql)) {
        $stmt_check->bind_param("s", $no_ktp);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $_SESSION['error_message'] = "Anggota dengan No KTP ini sudah ada.";
            header("location: tambah-anggota.php"); 
            exit; 
        }
        $stmt_check->close();
    }

    $target_file = $target_dir . basename($_FILES["alamat_gmb"]["name"]);
    $upload_ok = 1;
    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES["alamat_gmb"]["tmp_name"]);
    if ($check === false) {
        $_SESSION['error_message'] = "File bukan gambar.";
        header("location: tambah-anggota.php");
        exit;
    }

    if ($_FILES["alamat_gmb"]["size"] > 500000) {
        $_SESSION['error_message'] = "Ukuran file terlalu besar.";
        header("location: tambah-anggota.php");
        exit;
    }

    if (!in_array($image_file_type, ["jpg", "png", "jpeg", "gif"])) {
        $_SESSION['error_message'] = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        header("location: tambah-anggota.php");
        exit;
    }

    if ($upload_ok && move_uploaded_file($_FILES["alamat_gmb"]["tmp_name"], $target_file)) {
        $alamat_gmb = $target_file;

        $admin_id = $_SESSION['id_admin'] ?? null;

        if ($admin_id) {
            $sql = "INSERT INTO anggota (admin_id, no_ktp, nama, tmp_lahir, tgl_lahir, j_kelamin, agama, alamat, telp, pekerjaan, STATUS, alamat_gmb, simpanan_pokok) 
                    VALUES ('$admin_id', '$no_ktp', '$nama', '$tmp_lahir', '$tgl_lahir', '$j_kelamin', '$agama', '$alamat', '$telp', '$pekerjaan', '$status', '$alamat_gmb', '$simpanan_pokok')";

            if ($conn->query($sql) === TRUE) {
                $_SESSION['success_message'] = "Data anggota berhasil ditambahkan!";
                header("location: anggota.php"); 
                exit;
            } else {
                $_SESSION['error_message'] = "Error: " . $conn->error;
                header("location: tambah-anggota.php");
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Admin ID tidak ditemukan di session.";
            header("location: tambah-anggota.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Gagal mengunggah gambar.";
        header("location: tambah-anggota.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Anggota - Koperasi Simpan Pinjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a7c7e7, #66a3d2); /* Gradasi biru lembut */
        }
        .header {
            background-color:rgb(114, 150, 186);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .form-container {
            max-width: 700px;
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .form-container h2 {
            font-size: 1.5rem;
            color:rgb(17, 17, 18);
        }
        .form-label {
            font-weight: bold;
            font-size: 0.95rem;
        }
        .form-control {
            font-size: 0.95rem;
            padding: 0.8rem;
            border-radius: 5px;
        }
        .btn {
            padding: 10px 15px;
            font-size: 1rem;
            border-radius: 5px;
        }
        .btn-primary {
            background-color:rgb(92, 122, 155);
            border: none;
        }
        .btn-secondary {
            background-color:rgb(43, 97, 144);
            border: none;
        }
        .alert {
            font-size: 0.95rem;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Tambah Anggota Koperasi</h1>
    </div>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4">Tambah Anggota</h2>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $_SESSION['success_message']; ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $_SESSION['error_message']; ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-12 col-md-6">
                        <label for="no_ktp" class="form-label">No KTP</label>
                        <input type="text" name="no_ktp" class="form-control" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 col-md-6">
                        <label for="tmp_lahir" class="form-label">Tempat Lahir</label>
                        <input type="text" name="tmp_lahir" class="form-control" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tgl_lahir" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 col-md-6">
                        <label for="j_kelamin" class="form-label">Jenis Kelamin</label><br>
                        <input type="radio" id="laki-laki" name="j_kelamin" value="Laki-laki" required>
                        <label for="laki-laki">Laki-laki</label><br>
                        <input type="radio" id="perempuan" name="j_kelamin" value="Perempuan" required>
                        <label for="perempuan">Perempuan</label><br>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="agama" class="form-label">Agama</label>
                        <input type="text" name="agama" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 col-md-6">
                        <label for="alamat" class="form-label">Alamat</label>
                        <input type="text" name="alamat" class="form-control" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="telp" class="form-label">Telepon</label>
                        <input type="text" name="telp" required class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 col-md-6">
                        <label for="pekerjaan" class="form-label">Pekerjaan</label>
                        <input type="text" name="pekerjaan" required class="form-control">
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="simpanan_pokok" class="form-label">Simpanan Pokok</label>
                        <input type="number" name="simpanan_pokok" required class="form-control" placeholder="Simpanan Pokok">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="alamat_gmb" class="form-label">Upload Gambar</label>
                    <input type="file" name="alamat_gmb" required class="form-control">
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" name="add" class="btn btn-primary">Tambah Anggota</button>
                    <a href="anggota.php" class="btn btn-secondary">Batalkan</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
