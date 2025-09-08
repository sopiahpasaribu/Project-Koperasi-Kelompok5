<?php
session_start();

include("koneksi.php");

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nik_user = $_POST['nik_user'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $jabatan = $_POST['jabatan']; 

    // Check if username already exists
    $sqlUsernameCheck = "SELECT * FROM login WHERE username='$username'";
    $resultUsernameCheck = $conn->query($sqlUsernameCheck);

    if ($resultUsernameCheck->num_rows > 0) {
        $error_message = "Username sudah terdaftar. Silakan gunakan username lain.";
    } else {
        $sqlAdmin = "SELECT id_admin FROM admin WHERE nik_user='$nik_user'";
        $resultAdmin = $conn->query($sqlAdmin);

        if ($resultAdmin->num_rows == 1) {
            $rowAdmin = $resultAdmin->fetch_assoc();
            $id_admin = $rowAdmin['id_admin'];

            $sqlRoleCheck = "SELECT * FROM role WHERE admin_id='$id_admin' AND jabatan='$jabatan'";
            $resultRoleCheck = $conn->query($sqlRoleCheck);

            if ($resultRoleCheck->num_rows > 0) {
                $sqlLoginInsert = "INSERT INTO login (admin_id, username, PASSWORD, email) VALUES ('$id_admin', '$username', '$password', '$email')";

                if ($conn->query($sqlLoginInsert) === TRUE) {
                    $success_message = "Akun berhasil dibuat!";
                    $_POST['nik_user'] = '';
                    $_POST['username'] = '';
                    $_POST['password'] = '';
                    $_POST['email'] = '';
                    $_POST['jabatan'] = ''; 
                } else {
                    $error_message = "Error saat membuat akun: " . $conn->error;
                }
            } else {
                $error_message = "NIK tersebut tidak memiliki jabatan yang sesuai.";
            }
        } else {
            $error_message = "NIK tidak ditemukan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Koperasi Simpan Pinjam</title>
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
                            <h1 class="h4 text-gray-900 mb-4">Registrasi Koperasi Simpan Pinjam</h1>
                        </div>
                        <?php 
                        if (!empty($error_message)) {
                            echo '<div class="alert alert-danger">' . $error_message . '</div>';
                        }
                        if (!empty($success_message)) {
                            echo '<div class="alert alert-success">' . $success_message . '</div>';
                        }
                        ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group mb-3">
                                <label for="nik_user" class="form-label">NIK User</label>
                                <input type="text" class="form-control" id="nik_user" name="nik_user" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class='mb-3'>
                                <label for='jabatan' class='form-label'>Jabatan</label>
                                <select id='jabatan' name='jabatan' class='form-select' required>
                                    <option value=''>Pilih Jabatan</option>
                                    <option value='Sekertaris'>Sekertaris</option>
                                    <option value='Bendahara'>Bendahara</option>
                                    <option value='Unit Usaha'>Unit Usaha</option>
                                </select>
                            </div>

                            <button type='submit' class='btn btn-primary w-100'>Daftar</button>
                            <hr>
                            <div class="text-center">
                                <p class="small">Sudah punya akun? <a href="login.php">Login di sini</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>

</body>

</html>

<?php
$conn->close();
?>
