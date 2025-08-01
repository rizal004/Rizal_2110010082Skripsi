<?php
include "inc/koneksi.php";

if (isset($_POST['save'])) {
    $nama_pengguna  = mysqli_real_escape_string($koneksi, $_POST['nama_pengguna']);
    $username       = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password       = mysqli_real_escape_string($koneksi, $_POST['password']);
    $level          = mysqli_real_escape_string($koneksi, $_POST['level']);
    $alamat         = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_hp          = mysqli_real_escape_string($koneksi, $_POST['no_hp']);

    // Hash password (rekomendasi)
    // $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = $koneksi->query("INSERT INTO tb_pengguna (nama_pengguna, username, password, level, alamat, no_hp) 
        VALUES ('$nama_pengguna', '$username', '$password', '$level', '$alamat', '$no_hp')");
    // Ganti '$password' dengan '$password_hash' jika ingin hash

    if ($sql) {
        echo "<script>alert('Pengguna berhasil ditambahkan!');window.location='?page=MyApp/data_pengguna';</script>";
    } else {
        echo "<script>alert('Gagal menambah pengguna!');</script>";
    }
}
?>

<section class="content-header">
    <h1 style="text-align:center;">Tambah Pengguna</h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Form Tambah Pengguna</h3>
        </div>
        <form method="POST">
            <div class="box-body">
                <div class="form-group">
                    <label>Nama Pengguna</label>
                    <input type="text" name="nama_pengguna" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Level</label>
                    <select name="level" class="form-control" required>
                        <option value="">-- Pilih Level --</option>
                        <option value="Administrator">Administrator</option>
                        <option value="pengguna">Pengguna</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" name="alamat" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>No HP</label>
                    <input type="text" name="no_hp" class="form-control" required>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" name="save" class="btn btn-primary">Simpan</button>
                <a href="?page=MyApp/data_pengguna" class="btn btn-warning">Batal</a>
            </div>
        </form>
    </div>
</section>
