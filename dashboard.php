<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Koperasi Simpan Pinjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.0.7/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .dashboard-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr); 
            gap: 20px; 
            padding: 0;
            list-style: none;
            justify-items: center; 
        }

        .dashboard-list li {
            background-color: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 5px;
            padding: 60px; 
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
            min-height: 190px; 
            min-width: 200px;
        }

        .dashboard-list li:hover {
            background-color: rgb(230, 234, 247);
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        .welcome-banner {
            margin-left: 40px; 
            padding-left: 20px;
        }
    </style>
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

            <?php if ($_SESSION["role"] === "Ketua Koperasi"): ?>
                <li class="nav-item">
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
        <div class="welcome-banner">
                <h3>Selamat Datang di Koperasi Simpan Pinjam!</h3>
                <p>Halo, <?php echo $_SESSION["username"]; ?>! Anda masuk sebagai <?php echo $_SESSION["role"]; ?>.</p>
        </div>

        <ul class="dashboard-list">
            <li data-link="anggota.php">
                <i class="fas fa-users fa-2x mb-2 text-primary"></i>
                Kelola data Anggota
            </li>

            <?php if ($_SESSION["role"] === "Ketua Koperasi"): ?>
                <li data-link="admin.php">
                    <i class="fas fa-user-shield fa-2x mb-2 text-success"></i>
                    Kelola data Admin
                </li>
            <?php endif; ?>

            <li data-link="simpanan.php">
                <i class="fas fa-wallet fa-2x mb-2 text-info"></i>
                Kelola data Simpanan
            </li>

            <li data-link="pinjaman.php">
                <i class="fas fa-hand-holding-usd fa-2x mb-2 text-warning"></i>
                Kelola data Pinjaman
            </li>

            <li data-link="angsuran.php">
                <i class="fas fa-money-check-alt fa-2x mb-2 text-danger"></i>
                Kelola data Angsuran
            </li>
        </ul>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.querySelectorAll('.dashboard-list li').forEach(item => {
          item.addEventListener('click', () => {
              const link = item.getAttribute('data-link');
              if (link) {
                  window.location.href = link;
              }
          });
        });
    </script>

</body>

</html>
