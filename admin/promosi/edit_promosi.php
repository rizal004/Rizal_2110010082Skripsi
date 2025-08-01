<?php
include "inc/koneksi.php";

$id = $_GET['id'];
$query = $koneksi->query("SELECT * FROM tb_promosi WHERE id_promosi='$id'");
$data = $query->fetch_assoc();

if (isset($_POST['update'])) {
    $judul_promosi  = mysqli_real_escape_string($koneksi, $_POST['judul_promosi']);
    $jenis_promosi  = mysqli_real_escape_string($koneksi, $_POST['jenis_promosi']);
    $deskripsi      = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $lokasi         = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $tanggal_mulai  = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
    $tanggal_selesai= mysqli_real_escape_string($koneksi, $_POST['tanggal_selesai']);
    $harga          = mysqli_real_escape_string($koneksi, $_POST['harga']);
    $kontak         = mysqli_real_escape_string($koneksi, $_POST['kontak']);
    $gambarLama     = $data['gambar'];

    // Upload gambar baru jika ada
    $gambarNames = [];
    if (!empty($_FILES['gambar']['name'][0])) {
        foreach ($_FILES['gambar']['tmp_name'] as $i => $tmp) {
            $name = date('YmdHis') . '_' . basename($_FILES['gambar']['name'][$i]);
            if (move_uploaded_file($tmp, "uploads/promosi/" . $name)) {
                $gambarNames[] = $name;
            }
        }
        // Hapus gambar lama (opsional, bisa skip kalau tidak ingin dihapus)
        if ($gambarLama) {
            foreach (explode(',', $gambarLama) as $img) {
                if ($img && file_exists("uploads/promosi/".$img)) unlink("uploads/promosi/".$img);
            }
        }
    } else {
        $gambarNames = explode(',', $gambarLama);
    }
    $gambar = mysqli_real_escape_string($koneksi, implode(',', $gambarNames));

    $sql = $koneksi->query("UPDATE tb_promosi SET
        judul_promosi='$judul_promosi',
        jenis_promosi='$jenis_promosi',
        deskripsi='$deskripsi',
        lokasi='$lokasi',
        tanggal_mulai='$tanggal_mulai',
        tanggal_selesai='$tanggal_selesai',
        harga='$harga',
        kontak='$kontak',
        gambar='$gambar'
        WHERE id_promosi='$id'
    ");

    if ($sql) {
        echo "<script>alert('Data berhasil diupdate!');window.location='?page=MyApp/data_promosi';</script>";
    } else {
        echo "<script>alert('Gagal update data!');</script>";
    }
}
?>

<section class="content-header">
    <h1 style="text-align:center;">Edit Promosi</h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Form Edit Promosi</h3>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="box-body">
                <div class="form-group">
                    <label>Judul Promosi</label>
                    <input type="text" name="judul_promosi" class="form-control" value="<?= htmlspecialchars($data['judul_promosi']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Jenis Promosi</label>
                    <select name="jenis_promosi" class="form-control" required>
                        <option <?= ($data['jenis_promosi']=='Pembuatan Baliho'?'selected':'') ?>>Pembuatan Baliho</option>
                        <option <?= ($data['jenis_promosi']=='Pemasangan Baliho'?'selected':'') ?>>Pemasangan Baliho</option>
                        <option <?= ($data['jenis_promosi']=='Sewa Konten Kreator'?'selected':'') ?>>Sewa Konten Kreator</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" required><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                </div>
                <div class="form-group">
                    <label>Lokasi</label>
                    <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($data['lokasi']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Promosi</label>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="<?= $data['tanggal_mulai'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="<?= $data['tanggal_selesai'] ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Harga</label>
                    <input type="number" name="harga" class="form-control" value="<?= $data['harga'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Kontak</label>
                    <input type="text" name="kontak" class="form-control" value="<?= htmlspecialchars($data['kontak']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Gambar Promosi (upload baru untuk ganti)</label>
                    <input type="file" name="gambar[]" class="form-control" accept="image/*" multiple>
                    <?php
                        if ($data['gambar']) {
                            foreach (explode(',', $data['gambar']) as $img) {
                                echo "<img src='uploads/promosi/$img' width='80' style='margin:4px 6px 4px 0;'>";
                            }
                        }
                    ?>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" name="update" class="btn btn-success">Update</button>
                <a href="?page=MyApp/data_promosi" class="btn btn-warning">Batal</a>
            </div>
        </form>
    </div>
</section>
