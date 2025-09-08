<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "koneksi.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_SESSION['id_admin'];
    $nik_anggota = $_POST["nik_anggota"];
    $adm = intval($_POST["adm"]);
    $jumlah_pinjaman = intval($_POST["jumlah_pinjaman"]);
    $lma_angsur = intval($_POST["lma_angsur"]);

    // Ambil anggota_id dan status berdasarkan NIK anggota
    $anggota_query = $conn->prepare("SELECT id_anggota, STATUS FROM anggota WHERE no_ktp = ?");
    $anggota_query->bind_param("s", $nik_anggota);
    $anggota_query->execute();
    $anggota_result = $anggota_query->get_result();

    if ($anggota_result->num_rows > 0) {
        $anggota = $anggota_result->fetch_assoc();
        $anggota_id = $anggota["id_anggota"];
        $status_anggota = $anggota["STATUS"];

        // Periksa status anggota
        if (strtolower($status_anggota) !== "aktif") {
            echo "<script>alert('Anggota dengan NIK $nik_anggota memiliki status tidak aktif! Pinjaman tidak dapat ditambahkan.');</script>";
        } else {
            // Cek apakah anggota sudah memiliki pinjaman aktif
            $pinjaman_query = $conn->prepare("SELECT id_pinjam FROM pinjaman WHERE anggota_id = ? AND status_pinjaman = 'aktif'");
            $pinjaman_query->bind_param("i", $anggota_id);
            $pinjaman_query->execute();
            $pinjaman_result = $pinjaman_query->get_result();

            if ($pinjaman_result->num_rows > 0) {
                echo "<script>alert('Anggota dengan NIK $nik_anggota sudah memiliki pinjaman aktif! Tidak dapat menambahkan pinjaman baru.');</script>";
            } else {
                // Set tanggal pinjam dan jatuh tempo pinjam
                $tgl_pinjam = date("Y-m-d");
                $jh_pinjam = date("Y-m-05", strtotime("+1 month", strtotime($tgl_pinjam)));

                // Set jatuh tempo angsuran
                $jh_angsur = date("Y-m-05", strtotime("+$lma_angsur month", strtotime($tgl_pinjam)));

                // Hitung biaya angsuran
                $bunga_per_bulan = $jumlah_pinjaman * 0.04; // 4% bunga per bulan
                $total_bunga = $bunga_per_bulan * $lma_angsur; // Total bunga selama angsuran
                $total_pinjaman = $jumlah_pinjaman + $total_bunga; // Total pinjaman termasuk bunga
                $biaya_angsuran = ceil($total_pinjaman / $lma_angsur); // Biaya angsuran per bulan

                // Insert ke tabel pinjaman
                $insert_query = $conn->prepare(
                    "INSERT INTO pinjaman (admin_id, anggota_id, tgl_pinjam, jh_pinjam, jumlah_pinjaman, biaya_angsuran, adm, bunga, lma_angsur, jh_angsur, status_pinjaman) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')"
                );
                $insert_query->bind_param("iissiiidis", $admin_id, $anggota_id, $tgl_pinjam, $jh_pinjam, $jumlah_pinjaman, $biaya_angsuran, $adm, $bunga_per_bulan, $lma_angsur, $jh_angsur);

                if ($insert_query->execute()) {
                    echo "<script>alert('Pinjaman berhasil ditambahkan!'); window.location.href='pinjaman.php';</script>";
                } else {
                    echo "<script>alert('Gagal menambahkan pinjaman: " . $conn->error . "');</script>";
                }
            }
        }
    } else {
        echo "<script>alert('Anggota dengan NIK $nik_anggota tidak ditemukan!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pinjaman - Koperasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a7c7e7, #66a3d2); /* Gradasi biru lembut */
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }
        .form-label {
            font-weight: bold;
            color: #333;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .form-select {
            background-color: #f0f8ff;
        }
        .form-control {
            background-color: #f0f8ff;
        }
        .container {
            margin-top: 50px;
        }
        h3 {
            color: #007bff;
            font-size: 28px;
        }
        .card p {
            color: #555;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card p-4">
        <h3 class="mb-4 text-center">Tambah Pinjaman Anggota</h3>
        <form method="POST">
            <div class="mb-3">
                <label for="nik_anggota" class="form-label">NO KTP Anggota</label>
                <input type="text" class="form-control" id="nik_anggota" name="nik_anggota" required>
            </div>
            <div class="mb-3">
                <label for="adm" class="form-label">Biaya Administrasi</label>
                <select class="form-select" id="adm" name="adm" required>
                    <option value="">-- Pilih Biaya Admin --</option>
                    <option value="5000">5,000</option>
                    <option value="10000">10,000</option>
                    <option value="25000">25,000</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="jumlah_pinjaman" class="form-label">Jumlah Pinjaman</label>
                <select class="form-select" id="jumlah_pinjaman" name="jumlah_pinjaman" required>
                    <option value="">-- Pilih Jumlah Pinjaman --</option>
                    <option value="2000000">2,000,000</option>
                    <option value="3000000">3,000,000</option>
                    <option value="4000000">4,000,000</option>
                    <option value="5000000">5,000,000</option>
                    <option value="6000000">6,000,000</option>
                    <option value="7000000">7,000,000</option>
                    <option value="8000000">8,000,000</option>
                    <option value="9000000">9,000,000</option>
                    <option value="10000000">10,000,000</option>
                    <option value="12000000">12,000,000</option>
                    <option value="15000000">15,000,000</option>
                    <option value="20000000">20,000,000</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="lma_angsur" class="form-label">Lama Angsuran (bulan)</label>
                <select class="form-select" id="lma_angsur" name="lma_angsur" required>
                    <option value="">-- Pilih Lama Angsuran --</option>
                    <option value="3">3 Bulan</option>
                    <option value="4">4 Bulan</option>
                    <option value="5">5 Bulan</option>
                    <option value="6">6 Bulan</option>
                    <option value="10">10 Bulan</option>
                    <option value="12">12 Bulan</option>
                    <option value="15">15 Bulan</option>
                    <option value="18">18 Bulan</option>
                    <option value="24">24 Bulan</option>
                </select>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Tambah Pinjaman</button>
                <a href="pinjaman.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
