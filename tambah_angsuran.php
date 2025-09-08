<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "koneksi.php";

$nik_anggota = "";
$pinjaman = null;
$angsuran_ke = 1;
$jh_angsuran = 0; 
$tgl_jh_tempo = "";
$admin_id = $_SESSION['id_admin']; 
$anggota_id = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nik_anggota"])) {
    $nik_anggota = $_POST["nik_anggota"];
    
    $anggota_result = $conn->query("SELECT * FROM anggota WHERE no_ktp = '$nik_anggota'");
    if ($anggota_result->num_rows > 0) {
        $anggota = $anggota_result->fetch_assoc();
        $anggota_id = $anggota["id_anggota"];
        
        $pinjaman_result = $conn->query("SELECT * FROM pinjaman WHERE anggota_id = '$anggota_id' AND status_pinjaman = 'Aktif'");
        if ($pinjaman_result->num_rows > 0) {
            $pinjaman = $pinjaman_result->fetch_assoc();
            $lama_angsuran = $pinjaman["lma_angsur"];
            $jh_angsuran = $pinjaman["biaya_angsuran"];
            
            $angsuran_result = $conn->query("SELECT COUNT(*) as total_angsuran FROM angsuran WHERE pinjam_id = '{$pinjaman['id_pinjam']}'");
            $angsuran_data = $angsuran_result->fetch_assoc();
            $angsuran_ke = $angsuran_data['total_angsuran'] + 1;

            if ($angsuran_data['total_angsuran'] > 0) {
                $last_due_date_result = $conn->query("SELECT tgl_jh_tempo FROM angsuran WHERE pinjam_id = '{$pinjaman['id_pinjam']}' ORDER BY tgl_jh_tempo DESC LIMIT 1");
                if ($last_due_date_result->num_rows > 0) {
                    $last_due_date_row = $last_due_date_result->fetch_assoc();
                    $tgl_jh_tempo = date("Y-m-05", strtotime("+1 month", strtotime($last_due_date_row['tgl_jh_tempo'])));
                }
            } else {
                $tgl_jh_tempo = date("Y-m-05", strtotime("+1 month"));
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_angsuran"])) {
    $pinjam_id = $_POST["pinjam_id"];
    $anggota_id = $_POST["anggota_id"];
    $angsuran_ke = $_POST["angsuran_ke"];
    
    $tgl_bayar = date("Y-m-d H:i:s");
    
    $sql = "INSERT INTO angsuran (admin_id, anggota_id, pinjam_id, angsuran_ke, jh_angsuran, tgl_bayar, tgl_jh_tempo) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        if (isset($admin_id)) {
            $stmt->bind_param("iiissss", $admin_id, $anggota_id, $pinjam_id, $angsuran_ke, $jh_angsuran, $tgl_bayar, $tgl_jh_tempo);
            if ($stmt->execute()) {
                if ($angsuran_ke == $lama_angsuran) {
                    $conn->query("UPDATE pinjaman SET status_pinjaman = 'Non-Aktif' WHERE id_pinjam = '$pinjam_id'");
                }
                
                header("location: angsuran.php");
                exit;
            }
        } else {
            echo "Admin ID is not set.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Angsuran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a7c7e7, #66a3d2); /* Gradasi biru lembut */
        }

        .container-fluid {
            max-width: 900px;
            margin-top: 50px;
        }

        .card {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color:rgb(80, 114, 150);
            color: white;
            text-align: center;
            padding: 15px 0;
        }

        .form-label {
            font-weight: bold;
        }

        .btn-info, .btn-primary, .btn-secondary {
            font-size: 1rem;
        }

        .btn-info {
            background-color:rgb(107, 171, 181);
        }

        .btn-primary {
            background-color:rgb(48, 83, 120);
        }

        .btn-secondary {
            background-color:rgb(125, 155, 182);
        }

        .table th, .table td {
            text-align: center;
        }

        .form-control {
            font-size: 1rem;
            padding: 0.8rem;
        }

        .alert {
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .btn {
            font-size: 1rem;
            padding: 10px 20px;
        }

        .mb-3 {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <h2 class="my-4 text-center">Tambah Angsuran</h2>

    <div class="card">
        <div class="card-header">
            <h4>Formulir Tambah Angsuran</h4>
        </div>
        <form action="tambah_angsuran.php" method="POST">
            <div class="mb-3">
                <label for="nik_anggota" class="form-label">No KTP Anggota</label>
                <input type="text" class="form-control" id="nik_anggota" name="nik_anggota" value="<?= htmlspecialchars($nik_anggota) ?>" required>
            </div>

            <button type="submit" name="cari_anggota" class="btn btn-info">Tampilkan Data</button>

            <?php if ($pinjaman): ?>
                <div class="mt-4">
                    <h5>Pinjaman Anggota</h5>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>Lama Angsuran (Bulan)</th>
                            <td><?= htmlspecialchars($lama_angsuran) ?></td>
                        </tr>
                        <tr>
                            <th>Biaya Angsuran</th>
                            <td><?= number_format($jh_angsuran, 0, ',', '.') ?></td>
                        </tr>
                    </table>

                    <div class="mb-3">
                        <label for="angsuran_ke" class="form-label">Angsuran Ke</label>
                        <input type="number" class="form-control" id="angsuran_ke" name="angsuran_ke" value="<?= htmlspecialchars($angsuran_ke) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="tgl_jh_tempo" class="form-label">Tanggal Jatuh Tempo</label>
                        <input type="date" class="form-control" id="tgl_jh_tempo" name="tgl_jh_tempo" value="<?= htmlspecialchars($tgl_jh_tempo) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <input type="hidden" name="pinjam_id" value="<?= htmlspecialchars($pinjaman['id_pinjam']) ?>">
                        <input type="hidden" name="anggota_id" value="<?= htmlspecialchars($anggota_id) ?>">
                        <button type="submit" name="add_angsuran" class="btn btn-primary">Tambah Angsuran</button>
                        <a href="angsuran.php" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            <?php elseif ($nik_anggota != null): ?>
                <div class="alert alert-warning">Anggota dengan No KTP tersebut tidak memiliki pinjaman aktif.</div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
