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

    $status_default = 'Aktif';

    $sqlAdmin = "INSERT INTO admin (nik_user, nama_user, tmp_lahir, tgl_lahir, agama, j_kelamin, alamat,STATUS, nohp) 
                 VALUES ('$nik_user', '$nama_user', '$tmp_lahir', '$tgl_lahir', '$agama', '$j_kelamin', '$alamat','$status_default', '$nohp')";
    
    if ($conn->query($sqlAdmin) === TRUE) {
        $admin_id = $conn->insert_id;
        $tgl_awal_kerja = date('Y-m-d H:i:s');
        $sqlRole = "INSERT INTO role (admin_id, jabatan, tgl_awal_kerja) 
                    VALUES ('$admin_id', '$jabatan', '$tgl_awal_kerja')";
        
        if ($conn->query($sqlRole) === TRUE) {
            $_SESSION['success_message'] = "Admin baru berhasil ditambahkan!";
            header("location: admin.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Error saat menambahkan data role: " . $conn->error;
        }
    } else {
        $_SESSION['error_message'] = "Error saat menambahkan data admin: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Admin - Koperasi Simpan Pinjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a7c7e7, #66a3d2); 
            font-family: 'Arial', sans-serif;
        }

        .container {
            max-width: 700px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5rem;
            color: #333;
            font-weight: 600;
        }

        .form-label {
            font-weight: bold;
            color: #333;
        }

        .form-control {
            border-radius: 8px;
            padding: 8px;
            font-size: 0.9rem;
            border: 1px solid #ccc;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }

        .btn {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border-radius: 8px;
            margin-top: 20px;
        }

        .btn-primary {
            background-color:rgb(45, 123, 174);
            border: none;
        }

        .btn-primary:hover {
            background-color:rgb(138, 164, 219);
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .alert {
            font-size: 0.9rem;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .row .col-md-6 {
            padding: 0 10px;
        }

        .footer {
            text-align: center;
            font-size: 0.9rem;
            color: #555;
            margin-top: 40px;
            margin-bottom: 20px;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .form-section {
            margin-bottom: 20px;
        }

        .form-control:hover {
            border-color: #80bdff;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Tambah Admin Baru</h1>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $_SESSION['error_message']; ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-section">
                        <label for="nik_user" class="form-label">NIK User</label>
                        <input type="text" class="form-control" id="nik_user" name="nik_user" required>
                    </div>

                    <div class="form-section">
                        <label for="nama_user" class="form-label">Nama User</label>
                        <input type="text" class="form-control" id="nama_user" name="nama_user" required>
                    </div>

                    <div class="form-section">
                        <label for="tmp_lahir" class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control" id="tmp_lahir" name="tmp_lahir" required>
                    </div>

                    <div class="form-section">
                        <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir" required>
                    </div>

                    <div class="form-section">
                        <label for="agama" class="form-label">Agama</label>
                        <input type="text" class="form-control" id="agama" name="agama" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-section">
                        <label for="j_kelamin" class="form-label">Jenis Kelamin</label>
                        <select id="j_kelamin" name="j_kelamin" class="form-select" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>

                    <div class="form-section">
                        <label for="alamat" class="form-label">Alamat</label>
                        <input type="text" class="form-control" id="alamat" name="alamat" required>
                    </div>

                    <div class="form-section">
                        <label for="nohp" class="form-label">No HP</label>
                        <input type="text" class="form-control" id="nohp" name="nohp" required>
                    </div>

                    <div class="form-section">
                        <label for="jabatan" class="form-label">Jabatan</label>
                        <select id="jabatan" name="jabatan" class="form-select" required>
                            <option value="">Pilih Jabatan</option>
                            <option value="Sekertaris">Sekertaris</option>
                            <option value="Bendahara">Bendahara</option>
                            <option value="Unit Usaha">Unit Usaha</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Tambah Admin</button>
            <a href="admin.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>

    <div class="footer">
        <p>&copy; 2025 Koperasi Simpan Pinjam | <a href="https://koperasi.com">Website Koperasi</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>
