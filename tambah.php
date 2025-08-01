<?php
include "inc/koneksi.php";
if (isset($_POST['save'])) {
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kategori  = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $foto      = $_FILES['foto']['name'];
    $target_dir = "images/";
    $target_file = $target_dir . basename($foto);
    if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO tb_destinasi (nama, kategori, foto, deskripsi) VALUES ('$nama', '$kategori', '$foto', '$deskripsi')";
        if (mysqli_query($koneksi, $sql)) {
            echo "<script>alert('Data berhasil disimpan!');window.location='index.php';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan data!');</script>";
        }
    } else {
        echo "<script>alert('Gagal upload gambar!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Destinasi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
        .form-container { max-width:420px; margin:3rem auto; background:#fff; padding:2rem 2rem 1.5rem 2rem; border-radius:20px; box-shadow:0 4px 24px rgba(102,126,234,0.08);}
        h2 { text-align:center; color:#764ba2; margin-bottom:1.5rem;}
        label { display:block; margin-bottom:0.5rem; color:#4a5568; font-weight:500;}
        .form-control { width:100%; padding:1rem; border:2px solid #e2e8f0; border-radius:12px; font-size:1rem; margin-bottom:1.1rem;}
        .form-control:focus { outline:none; border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,0.11);}
        button { width:100%; padding:1rem; background:linear-gradient(135deg,#667eea,#764ba2); color:white; border:none; border-radius:12px; font-size:1.1rem; font-weight:600; cursor:pointer; transition:all .25s;}
        button:hover { box-shadow:0 8px 25px rgba(102,126,234,0.24);}
        .back-link {display:block;text-align:center;color:#667eea;text-decoration:none;margin-top:1rem;}
        .back-link:hover {text-decoration:underline;}
    </style>
</head>
<body>
    <form method="POST" enctype="multipart/form-data" class="form-container">
        <h2>Tambah Destinasi</h2>
        <label>Nama Destinasi:</label>
        <input type="text" name="nama" class="form-control" required>
        <label>Kategori:</label>
        <select name="kategori" class="form-control" required>
            <option value="Wisata Alam">Wisata Alam</option>
            <option value="Wisata Kuliner">Wisata Kuliner</option>
            <option value="Wisata Sejarah">Wisata Sejarah</option>
            <option value="Wisata Festival">Wisata Festival</option>
        </select>
        <label>Foto:</label>
        <input type="file" name="foto" class="form-control" accept="image/*" required>
        <label>Deskripsi:</label>
        <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
        <button type="submit" name="save"><i class="fas fa-save"></i> Simpan</button>
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Kembali</a>
    </form>
</body>
</html>
