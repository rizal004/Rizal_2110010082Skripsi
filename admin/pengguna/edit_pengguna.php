<?php
include "inc/koneksi.php";

$id = $_GET['kode'];
$query = $koneksi->query("SELECT * FROM tb_pengguna WHERE id_pengguna='$id'");
$data = $query->fetch_assoc();

if (isset($_POST['update'])) {
    $nama_pengguna  = mysqli_real_escape_string($koneksi, $_POST['nama_pengguna']);
    $username       = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password       = mysqli_real_escape_string($koneksi, $_POST['password']);
    $level          = mysqli_real_escape_string($koneksi, $_POST['level']);
    $alamat         = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_hp          = mysqli_real_escape_string($koneksi, $_POST['no_hp']);

    // Optional: jika password tidak diubah, pakai yang lama
    if (empty($password)) {
        $password = $data['password'];
    }

    $sql = $koneksi->query("UPDATE tb_pengguna SET 
        nama_pengguna='$nama_pengguna', 
        username='$username', 
        password='$password', 
        level='$level', 
        alamat='$alamat', 
        no_hp='$no_hp' 
        WHERE id_pengguna='$id'");

    if ($sql) {
        echo "<script>alert('Pengguna berhasil diupdate!');window.location='?page=MyApp/data_pengguna';</script>";
    } else {
        echo "<script>alert('Gagal update pengguna!');</script>";
    }
}
?>

<section class="content-header">
    <h1 style="text-align:center;">Edit Pengguna</h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Form Edit Pengguna</h3>
        </div>
        <form method="POST">
            <div class="box-body">
                <div class="form-group">
                    <label>Nama Pengguna</label>
                    <input type="text" name="nama_pengguna" class="form-control" value="<?= htmlspecialchars($data['nama_pengguna']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($data['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Password (isi jika ingin ganti password)</label>
                    <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak diganti">
                </div>
                <div class="form-group">
                    <label>Level</label>
                    <select name="level" class="form-control" required>
                        <option value="Administrator" <?= ($data['level']=='Administrator')?'selected':''; ?>>Administrator</option>
                        <option value="pengguna" <?= ($data['level']=='pengguna')?'selected':''; ?>>Pengguna</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($data['alamat']) ?>" required>
                </div>
                <div class="form-group">
                    <label>No HP</label>
                    <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($data['no_hp']) ?>" required>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" name="update" class="btn btn-success">Update</button>
                <a href="?page=MyApp/data_pengguna" class="btn btn-warning">Batal</a>
            </div>
        </form>
    </div>
</section>
