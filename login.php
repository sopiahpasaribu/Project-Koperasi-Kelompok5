<?php
session_start();

include("koneksi.php"); 

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

$username = $password = "";
$username_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Harap masukkan username.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Harap masukkan password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($username_err) && empty($password_err)) {
        $sql = $sql = "SELECT l.admin_id, l.username, l.password, r.jabatan
        FROM login l
        LEFT JOIN ROLE r ON l.admin_id = r.admin_id
        WHERE l.username = ?";


        if (isset($conn) && $stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($admin_id, $username, $stored_password, $jabatan);
                    if ($stmt->fetch()) {
                        if ($password === $stored_password) { 
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id_admin"] = $admin_id; 
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $jabatan;

                            header("location: dashboard.php");
                        } else {
                            $login_err = "Password tidak valid.";
                        }
                    }
                } else {
                    $login_err = "Username tidak ditemukan.";
                }
            } else {
                echo "Terjadi kesalahan. Silakan coba lagi nanti.";
            }

            $stmt->close();
        } else {
            echo "Terjadi kesalahan pada koneksi database.";
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Koperasi Simpan Pinjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.0.7/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('image/background.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Login Koperasi Simpan Pinjam</h1>
                        </div>
                        <?php 
                        if (!empty($username_err)) {
                            echo '<div class="alert alert-danger">' . $username_err . '</div>';
                        }
                        if (!empty($password_err)) {
                            echo '<div class="alert alert-danger">' . $password_err . '</div>';
                        }
                        ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo $username; ?>" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <hr>
                        <div class="text-center">
                            <p class="small">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
