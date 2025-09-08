<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

include("koneksi.php");

$anggota_info = null;
$saldo_masuk = null; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nik_anggota = trim($_POST['nik_anggota']);
    
    $sql = "SELECT id_anggota, nama, alamat, telp, tgl_lahir, status FROM anggota WHERE no_ktp = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $nik_anggota);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($anggota_id, $nama, $alamat, $telp, $tgl_lahir, $status);
            $stmt->fetch();
            
            if (strtolower($status) !== 'aktif') {
                $_SESSION['error'] = "Anggota dengan NIK ini tidak aktif dan tidak dapat menambahkan simpanan.";
            } else {
                $anggota_info = [
                    'id' => $anggota_id,
                    'nama' => $nama,
                    'alamat' => $alamat,
                    'telp' => $telp,
                    'tgl_lahir' => $tgl_lahir,
                ];
            }
        } else {
            $_SESSION['error'] = "NIK anggota tidak ditemukan.";
        }
        $stmt->close();
    }

    if (isset($_POST['saldo_masuk'])) {
        $saldo_masuk = trim($_POST['saldo_masuk']);
    }

    if (isset($anggota_info) && !is_null($saldo_masuk)) {
        $sql = "SELECT SUM(saldo_masuk), SUM(saldo_keluar) FROM simpanan_wajib WHERE anggota_id = ?";
        $jumlah_saldo = 0;
        $jumlah_saldo_keluar = 0;
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $anggota_info['id']);
            $stmt->execute();
            $stmt->bind_result($jumlah_saldo, $jumlah_saldo_keluar);
            $stmt->fetch();
            $stmt->close();
        }

        $saldo_tersedia = $jumlah_saldo - $jumlah_saldo_keluar;

        $saldo_tersedia += intval($saldo_masuk);

        $sql = "SELECT MAX(jh_simpanan) FROM simpanan_wajib WHERE anggota_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $anggota_info['id']);
            $stmt->execute();
            $stmt->bind_result($last_jh_simpanan);
            $stmt->fetch();
            $stmt->close();
        }
        
        if ($last_jh_simpanan) {
            $jh_simpanan = date('Y-m-05', strtotime($last_jh_simpanan . " +1 month"));
        } else {
            $jh_simpanan = date('Y-m-05', strtotime("+1 month"));
        }
        
        if (!empty($saldo_masuk)) {
            $admin_id = $_SESSION['id_admin'];
            if ($stmt = $conn->prepare("INSERT INTO simpanan_wajib (admin_id, anggota_id, saldo_masuk, jumlah_saldo, jh_simpanan) VALUES (?, ?, ?, ?, ?)")) {
                $saldo_masuk_int = intval($saldo_masuk);
                $jumlah_saldo_int = intval($saldo_tersedia); 

                if ($stmt->bind_param("iiiss", 
                    $admin_id, 
                    $anggota_info['id'], 
                    $saldo_masuk_int, 
                    $jumlah_saldo_int, 
                    $jh_simpanan)) {
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Simpanan berhasil ditambahkan!";
                        header("Location: simpanan.php");
                        exit;
                    } else {
                        $_SESSION['error'] = "Terjadi kesalahan: " . htmlspecialchars($stmt->error);
                    }
                } else {
                    $_SESSION['error'] = "Bind parameter gagal.";
                }
                $stmt->close();
            } else {
                $_SESSION['error'] = "Query prepare gagal: " . htmlspecialchars($conn->error);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Simpanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a7c7e7, #66a3d2); /* Gradasi biru lembut */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin-top: 50px;
            transition: transform 0.3s ease;
        }
        .container:hover {
            transform: scale(1.02);
        }
        h2 {
            color: #2a4d79; /* Biru gelap untuk header */
            text-align: center;
            font-size: 2rem;
        }
        .alert-info {
            background-color: #b0c8e2; /* Biru muda lembut */
            border-color: #7ea7c4;
            color: #1e3c59;
        }
        .btn-primary {
            background-color: #4f82b6; /* Biru lebih terang */
            border-color: #2c4a72;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #2c4a72;
        }
        .btn-secondary {
            background-color: #a7c7e7;
            border-color: #88b0d0;
            transition: background-color 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #88b0d0;
        }
        .form-label {
            font-weight: 600;
            color: #2a4d79;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #c4d7e0;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .form-control:focus {
            border-color: #4f82b6;
            box-shadow: 0 0 5px rgba(79, 130, 182, 0.5);
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Tambah Simpanan</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if ($anggota_info): ?>
        <div class="alert alert-info">
            <strong>Informasi Anggota:</strong><br>
            Nama: <?php echo htmlspecialchars($anggota_info['nama']); ?><br>
            Alamat: <?php echo htmlspecialchars($anggota_info['alamat']); ?><br>
            Telepon: <?php echo htmlspecialchars($anggota_info['telp']); ?><br>
            Tanggal Lahir: <?php echo htmlspecialchars(date('d-m-Y', strtotime($anggota_info['tgl_lahir']))); ?><br>
        </div>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="nik_anggota" value="<?php echo htmlspecialchars($nik_anggota); ?>">
            
            <div class="mb-3">
                <label for="saldo_masuk" class="form-label">Saldo Masuk:</label>
                <input type="number" class="form-control" id="saldo_masuk" name="saldo_masuk" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="simpanan.php" class="btn btn-secondary">Kembali</a>
        </form>

    <?php else: ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="nik_anggota" class="form-label">No KTP Anggota:</label>
                <input type="text" class="form-control" id="nik_anggota" name="nik_anggota" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Lanjutkan</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
