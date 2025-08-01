<?php
include "inc/koneksi.php";
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login | SI Wisata</title>
    <link rel="icon" href="dist/img/siwisata.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
        body {
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(120deg,#7fbcff 0%,#d8eafe 100%);
            position: relative;
        }
        .bg-ornament {
            position: fixed;
            z-index: 0;
            inset: 0;
            background: url('dist/img/bg-wisata.png') center center/cover no-repeat;
            opacity: 0.08;
            pointer-events: none;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            width: 360px;
            background: rgba(255,255,255,0.35);
            border-radius: 26px;
            box-shadow: 0 10px 40px 0 rgba(98,166,255,0.17);
            border: 1.5px solid #d3e9fc;
            padding: 42px 32px 30px 32px;
            backdrop-filter: blur(13px);
            animation: pop 0.7s;
            position: relative;
        }
        @keyframes pop {
            from { opacity: 0; transform: scale(0.92);}
            to { opacity: 1; transform: scale(1);}
        }
        .login-logo img {
            width: 80px;
            margin-bottom: 6px;
        }
        .login-title {
            font-size: 2rem;
            font-weight: 800;
            color: #2581bb;
            margin-bottom: 18px;
            text-align: center;
        }
        .form-group label {
            font-weight: 500;
            color: #2581bb;
            margin-bottom: 6px;
        }
        .form-control {
            border-radius: 11px;
            padding: 15px 13px;
            font-size: 1rem;
            border: 1.5px solid #b8defd;
            background: rgba(255,255,255,0.94);
            margin-bottom: 13px;
            transition: border .17s;
        }
        .form-control:focus {
            border-color: #2581bb;
            box-shadow: 0 0 0 2px #b6dfff6b;
        }
        .btn-login {
            border-radius: 13px;
            font-weight: 700;
            font-size: 1.09rem;
            padding: 13px 0;
            background: linear-gradient(90deg,#50b3ff 10%,#1976d2 80%);
            color: #fff;
            box-shadow: 0 2px 12px rgba(84, 165, 255, 0.12);
            border: none;
            margin-top: 5px;
            transition: background .19s,transform .16s;
        }
        .btn-login:hover {
            background: linear-gradient(90deg,#1976d2 60%,#50b3ff 100%);
            transform: translateY(-2px) scale(1.03);
            color: #fff;
        }
        .btn-back {
            width: 100%;
            border-radius: 13px;
            font-weight: 700;
            font-size: 1rem;
            padding: 11px 0;
            margin-top: 10px;
            background: linear-gradient(90deg, #f7b267 10%, #f4845f 90%);
            color: #fff;
            border: none;
            box-shadow: 0 1px 8px 0 rgba(255,169,93,0.13);
            transition: background .16s,transform .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }
        .btn-back:hover {
            background: linear-gradient(90deg, #f4845f 60%, #f7b267 100%);
            transform: translateY(-2px) scale(1.02);
            color: #fff;
        }
        .register-link {
            display: block;
            margin-top: 14px;
            font-size: 0.97rem;
            color: #1976d2;
            text-align: center;
        }
        .register-link:hover {
            color: #54a5ff;
            text-decoration: underline;
        }
        .fa-user, .fa-lock {
            color: #50b3ff;
            margin-right: 7px;
        }
        .login-logo h3 {
            color: #2581bb;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 1.08rem;
        }
        .floating-bg {
            position: absolute;
            top: -45px; left: -65px;
            width: 120px; height: 120px;
            background: linear-gradient(135deg,#8fd8ffcc 0%,#e2effd 100%);
            border-radius: 45% 65% 70% 50%;
            z-index: 1;
            filter: blur(9px);
            opacity: 0.7;
            animation: float 5s infinite alternate;
        }
        @keyframes float {
            0% {transform: translateY(0);}
            100% {transform: translateY(28px);}
        }
        @media (max-width: 480px) {
            .login-box { padding: 22px 7px 24px 7px; width:98vw;}
            .login-title { font-size: 1.17rem; }
        }
    </style>
</head>
<body>
    <div class="bg-ornament"></div>
    <div class="login-container">
        <div class="login-box">
            <div class="floating-bg"></div>
            <div class="login-logo text-center" style="position:relative;z-index:2;">
                <img src="dist/img/siwisata.png" alt="Logo SI Wisata">
                <h3>Sistem Informasi Wisata</h3>
            </div>
            <div>
                <div class="login-title">Login Akun</div>
                <form action="#" method="post" autocomplete="off">
                    <div class="form-group">
                        <label for="username"><i class="fa fa-user"></i>Username</label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan Username" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password"><i class="fa fa-lock"></i>Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan Password" required>
                    </div>
                    <button type="submit" name="btnLogin" class="btn btn-login btn-block" title="Masuk Sistem">
                        <b>Masuk</b>
                    </button>
                    <button type="button" class="btn btn-back btn-block" onclick="window.history.back();">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </button>
                    <a href="registrasi.php" class="register-link">Belum punya akun? <b>Daftar di sini</b></a>
                </form>
            </div>
        </div>
    </div>
    <script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
if (isset($_POST['btnLogin'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, md5($_POST['password']));

    $sql_login = "SELECT * FROM tb_pengguna WHERE BINARY username='$username' AND password='$password'";
    $query_login = mysqli_query($koneksi, $sql_login);
    $data_login  = mysqli_fetch_array($query_login, MYSQLI_BOTH);
    $jumlah_login= mysqli_num_rows($query_login);

    if ($jumlah_login == 1) {
        $_SESSION["ses_id"]       = $data_login["id_pengguna"];
        $_SESSION["ses_nama"]     = $data_login["nama_pengguna"];
        $_SESSION["ses_username"] = $data_login["username"];
        $_SESSION["ses_level"]    = $data_login["level"];
        echo "<script>
            Swal.fire({title:'Login Berhasil', icon:'success'}).then(()=>{window.location='index.php';});
        </script>";
    } else {
        echo "<script>
            Swal.fire({title:'Login Gagal', text:'Username atau password salah', icon:'error'});
        </script>";
    }
}
?>
</body>
</html>
