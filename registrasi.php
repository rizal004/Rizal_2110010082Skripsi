<?php
include "inc/koneksi.php";
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Registrasi | SI Perpustakaan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <!-- Bootstrap -->
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <style>
    body {
      min-height: 100vh;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #cbe5ff 0%, #54a5ff 100%);
      background-size: cover;
      margin: 0;
    }
    .register-box {
      max-width: 410px;
      margin: 4% auto;
      background: rgba(255,255,255,0.20);
      box-shadow: 0 8px 32px 0 rgba(84, 165, 255, 0.19);
      border-radius: 24px;
      backdrop-filter: blur(6px);
      border: 1.5px solid #aee1ff;
      padding: 38px 32px 26px 32px;
      animation: fadein 1.2s;
    }
    @keyframes fadein {
      from { opacity: 0; transform: translateY(-40px);}
      to { opacity: 1; transform: translateY(0);}
    }
    .register-logo {
      text-align: center;
      margin-bottom: 8px;
    }
    .register-logo a {
      font-size: 1.7rem;
      color: #23597c;
      font-weight: 700;
      text-decoration: none;
      letter-spacing: 1px;
    }
    .register-box-body {
      background: rgba(255,255,255,0.12);
      border-radius: 18px;
      padding: 20px 0 0 0;
      box-shadow: none;
    }
    .login-box-msg {
      font-size: 1.18rem;
      color: #23597c;
      font-weight: 500;
      text-align: center;
      margin-bottom: 18px;
    }
    .form-control {
      border-radius: 10px;
      padding: 15px 12px;
      font-size: 1rem;
      border: 1.5px solid #aee1ff;
      background: rgba(255,255,255,0.85);
      margin-bottom: 14px;
      transition: border 0.2s;
    }
    .form-control:focus {
      border: 1.5px solid #54a5ff;
      box-shadow: 0 0 0 2px #b6dfff6b;
    }
    .input-group-text {
      background: transparent;
      border: none;
      color: #54a5ff;
    }
    .btn-modern {
      border-radius: 12px;
      font-weight: 600;
      font-size: 1rem;
      padding: 11px 0;
      transition: background 0.2s, transform 0.18s;
      background: linear-gradient(90deg, #54a5ff 0%, #cbe5ff 100%);
      color: #fff;
      box-shadow: 0 2px 8px rgba(84, 165, 255, 0.10);
      border: none;
    }
    .btn-modern:hover {
      background: linear-gradient(90deg, #54a5ff 60%, #90cdfd 100%);
      transform: translateY(-2px) scale(1.02);
      color: #fff;
    }
    .have-account {
      margin-top: 9px;
      text-align: left;
      font-size: 0.98rem;
      color: #23597c;
    }
    .have-account a {
      color: #54a5ff;
      font-weight: 600;
      text-decoration: none;
      margin-left: 2px;
    }
    .have-account a:hover {
      text-decoration: underline;
      color: #23597c;
    }
    .fa-user, .fa-lock, .fa-phone, .fa-map-marker-alt {
      color: #54a5ff;
    }
    @media (max-width: 480px) {
      .register-box { padding: 18px 7px; }
      .register-logo a { font-size: 1.18rem; }
    }
  </style>
</head>
<body>
  <div class="register-box">
    <div class="register-logo">
      <a href="#"><b>Daftar</b> Pengguna</a>
    </div>
    <div class="register-box-body">
      <p class="login-box-msg">Buat akun baru</p>
      <form action="#" method="post" autocomplete="off">
        <div class="form-group">
          <label for="nama_pengguna"><i class="fa fa-user"></i> Nama Lengkap</label>
          <input type="text" name="nama_pengguna" id="nama_pengguna" class="form-control" placeholder="Nama Lengkap" required>
        </div>
        <div class="form-group">
          <label for="username"><i class="fa fa-user"></i> Username</label>
          <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="form-group">
          <label for="password"><i class="fa fa-lock"></i> Password</label>
          <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="form-group">
          <label for="no_hp"><i class="fa fa-phone"></i> No. HP</label>
          <input type="text" name="no_hp" id="no_hp" class="form-control" placeholder="Nomor HP" required>
        </div>
        <div class="form-group">
          <label for="alamat"><i class="fa fa-map-marker-alt"></i> Alamat</label>
          <textarea name="alamat" id="alamat" class="form-control" placeholder="Alamat lengkap" rows="3" required></textarea>
        </div>
        <!-- Hidden input untuk level -->
        <input type="hidden" name="level" value="pengguna">
        
        <div class="row">
          <div class="col-xs-8 have-account">
            Sudah punya akun?
            <a href="login.php">Masuk</a>
          </div>
          <div class="col-xs-4">
            <button type="submit" name="btnRegister" class="btn btn-modern btn-block">Daftar</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
  <script src="bootstrap/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
if (isset($_POST['btnRegister'])) {
  $nama   = mysqli_real_escape_string($koneksi, $_POST['nama_pengguna']);
  $user   = mysqli_real_escape_string($koneksi, $_POST['username']);
  $pass   = mysqli_real_escape_string($koneksi, md5($_POST['password']));
  $level  = mysqli_real_escape_string($koneksi, $_POST['level']);
  $no_hp  = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
  $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);

  // Cek username sudah ada atau belum
  $cek = mysqli_query($koneksi, "SELECT 1 FROM tb_pengguna WHERE username='$user'");
  if (mysqli_num_rows($cek) > 0) {
    echo "<script>
      Swal.fire({title:'Gagal', text:'Username sudah dipakai', icon:'error'});
    </script>";
    exit;
  }

  // Insert dengan semua field termasuk no_hp dan alamat
  $sql = "INSERT INTO tb_pengguna (nama_pengguna, username, password, level, alamat, no_hp)
          VALUES ('$nama', '$user', '$pass', '$level', '$alamat', '$no_hp')";
  if (mysqli_query($koneksi, $sql)) {
    echo "<script>
      Swal.fire({title:'Registrasi Berhasil', icon:'success'})
        .then(()=>{ window.location='login.php' });
    </script>";
  } else {
    echo "<script>
      Swal.fire({title:'Error', text:'".mysqli_error($koneksi)."', icon:'error'});
    </script>";
  }
}
?>
</body>
</html>