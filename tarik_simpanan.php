<?php
// Memulai session
session_start();

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Menghubungkan ke database
include 'koneksi.php';

$anggota_info = null;
$simpanans = [];
$error = '';
$success = '';

// Proses pencarian anggota
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $nama_anggota = trim($_POST['nama_anggota']);
    $nik_anggota = trim($_POST['nik_anggota']);

    // Query untuk mencari anggota berdasarkan nama dan NIK
    $sql = "SELECT id_anggota, nama FROM anggota WHERE nama LIKE ? AND no_ktp = ?";
    if ($stmt = $conn->prepare($sql)) {
        $search_param_nama = "%" . $nama_anggota . "%";
        $stmt->bind_param("ss", $search_param_nama, $nik_anggota);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            // Jika ditemukan, ambil data anggota
            $stmt->bind_result($anggota_id, $nama);
            $stmt->fetch();
            $anggota_info = ['id' => $anggota_id, 'nama' => htmlspecialchars($nama)];

            // Periksa apakah anggota memiliki data saldo_keluar
            $sql_check_saldo_keluar = "SELECT COUNT(*) FROM simpanan_wajib WHERE anggota_id = ? AND saldo_keluar > 0";
            if ($stmt_check = $conn->prepare($sql_check_saldo_keluar)) {
                $stmt_check->bind_param("i", $anggota_id);
                $stmt_check->execute();
                $stmt_check->bind_result($count_saldo_keluar);
                $stmt_check->fetch();

                if ($count_saldo_keluar > 0) {
                    // Jika ditemukan saldo_keluar, tampilkan pesan error
                    $error = "Data simpanan dengan NIK dan nama anggota tersebut sudah berakhir.";
                } else {
                    // Ambil semua data simpanan untuk anggota ini
                    $stmt_check->close(); // Pastikan resource ditutup sebelum query berikutnya
                    $sql_simpanan = "SELECT id_simpan, tgl_transaksi, saldo_masuk, jumlah_saldo, jh_simpanan 
                                     FROM simpanan_wajib 
                                     WHERE anggota_id = ? 
                                     ORDER BY id_simpan ASC";

                    if ($stmt_simpanan = $conn->prepare($sql_simpanan)) {
                        $stmt_simpanan->bind_param("i", $anggota_id);
                        if ($stmt_simpanan->execute()) {
                            $result = $stmt_simpanan->get_result();
                            while ($row = $result->fetch_assoc()) {
                                $simpanans[] = $row;
                            }
                            $result->close(); // Tutup result set setelah digunakan
                        } else {
                            $error = "Terjadi kesalahan saat mengeksekusi query: " . $stmt_simpanan->error;
                        }
                        $stmt_simpanan->close(); // Tutup statement setelah digunakan
                    } else {
                        $error = "Gagal menyiapkan query simpanan: " . $conn->error;
                    }
                }
            }
        } else {
            $error = "Anggota tidak ditemukan.";
        }
        $stmt->close();
    }
}

// Proses penarikan saldo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tarik'])) {
    if (isset($_POST['anggota_id']) && isset($_POST['last_simpanan_id'])) {
        $anggota_id = $_POST['anggota_id'];
        $last_simpanan_id = $_POST['last_simpanan_id'];

        // Cari data simpanan terakhir berdasarkan id_simpan
        $sql_last_simpanan = "SELECT * FROM simpanan_wajib WHERE id_simpan = ?";
        if ($stmt_last = $conn->prepare($sql_last_simpanan)) {
            $stmt_last->bind_param("i", $last_simpanan_id);
            $stmt_last->execute();
            $result_last = $stmt_last->get_result();

            if ($result_last->num_rows == 1) {
                $last_simpanan = $result_last->fetch_assoc();

                if ($last_simpanan && isset($last_simpanan['jumlah_saldo'])) {
                    $jumlah_saldo_value = (float)$last_simpanan['jumlah_saldo'];

                    if ($jumlah_saldo_value > 0) {
                        $sql_insert = "INSERT INTO simpanan_wajib (admin_id, anggota_id, tgl_transaksi, saldo_masuk, jumlah_saldo, saldo_keluar, jh_simpanan) VALUES (?, ?, NOW(), ?, ?, ?, '')";
                        if ($insert_stmt = $conn->prepare($sql_insert)) {
                            $admin_id = $_SESSION['id_admin'];
                            $saldo_masuk = 0;
                            $jumlah_saldo = 0;
                            $insert_stmt->bind_param("iiidd", 
                                $admin_id, 
                                $anggota_id, 
                                $saldo_masuk, 
                                $jumlah_saldo, 
                                $jumlah_saldo_value);
                            if ($insert_stmt->execute()) {
                                $simpanans = [];
                                $success = "Penarikan saldo berhasil dicatat!";
                            } else {
                                $error = "Terjadi kesalahan saat mencatat penarikan: " . $insert_stmt->error;
                            }
                            $insert_stmt->close();
                        }
                    } else {
                        $error = "Tidak ada saldo yang dapat ditarik.";
                    }
                } else {
                    $error = "Data simpanan tidak ditemukan.";
                }
            }
            $stmt_last->close();
        }
    }
}
?>

<!-- HTML di bagian bawah dengan tampilan yang lebih menarik -->

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarik Simpanan - Koperasi Simpan Pinjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a7c7e7, #66a3d2); /* Gradasi biru lembut */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card-header {
            background-color:rgb(104, 144, 189);
            color: white;
            font-weight: bold;
        }

        .btn-primary {
            background-color:rgb(31, 122, 168);
            border-color: #6c5ce7;
        }

        .btn-primary:hover {
            background-color:rgb(86, 121, 145);
            border-color:rgb(91, 131, 201);
        }

        .btn-danger {
            background-color:rgb(54, 96, 145);
            border-color:rgb(39, 35, 35);
        }

        .btn-danger:hover {
            background-color:rgb(86, 121, 145);
            border-color:rgb(59, 57, 57);
        }

        .alert-custom {
            font-size: 1.1rem;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }

        .form-control:focus {
            border-color: #6c5ce7;
            box-shadow: 0 0 0 0.2rem rgba(185, 184, 195, 0.25);
        }

        .container {
            max-width: 1200px;
        }
    </style>
</head>

<body>
<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header text-center">
            <h4>Form Penarikan Simpanan</h4>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-custom"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-custom"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="nama_anggota" class="form-label">Nama Anggota:</label>
                    <input type="text" class="form-control" id="nama_anggota" name="nama_anggota" required placeholder="Masukkan Nama Anggota">
                </div>
                <div class="mb-3">
                    <label for="nik_anggota" class="form-label">NO KTP Anggota:</label>
                    <input type="text" class="form-control" id="nik_anggota" name="nik_anggota" required placeholder="Masukkan NIK Anggota">
                </div>
                <button type="submit" name="search" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Cari Anggota
                </button>
                <a href="simpanan.php" class="btn btn-secondary w-100 mt-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </form>

            <?php if ($anggota_info): ?>
                <h4 class="mt-4 text-center">Data Simpanan untuk: <?php echo htmlspecialchars($anggota_info['nama']); ?></h4>

                <table class='table table-striped table-hover mt-3'>
                    <thead>
                        <tr>
                            <th>Tanggal Transaksi</th>
                            <th>Saldo Masuk</th>
                            <th>Jumlah Saldo</th>
                            <th>Jatuh Tempo Simpanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($simpanans) > 0): ?>
                            <?php foreach ($simpanans as $simpanan): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($simpanan['tgl_transaksi']); ?></td>
                                    <td><?php echo number_format($simpanan['saldo_masuk'], 2, ',', '.'); ?></td>
                                    <td><?php echo number_format($simpanan['jumlah_saldo'], 2, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($simpanan['jh_simpanan']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan='4' class="text-center">Tidak ada data simpanan ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="anggota_id" value="<?php echo htmlspecialchars($anggota_info['id']); ?>">
                    <?php if (!empty($simpanans)): ?>
                        <input type="hidden" name="last_simpanan_id" value="<?php echo htmlspecialchars(end($simpanans)['id_simpan']); ?>">
                    <?php endif; ?>
                    <button type="submit" name="tarik" class="btn btn-danger w-100 mt-3">
                        <i class="fas fa-money-bill-wave"></i> Tarik Semua Saldo
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$conn->close();
?>
