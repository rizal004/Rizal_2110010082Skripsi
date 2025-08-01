<?php
// edit_kuliner.php
include "inc/koneksi.php";

// 1. Ambil ID dari URL
$id_kuliner = isset($_GET['id']) 
    ? mysqli_real_escape_string($koneksi, $_GET['id']) 
    : '';

// 2. Jika tidak ada ID, redirect ke data_kuliner
if (empty($id_kuliner)) {
    header('Location: index.php?page=MyApp/data_kuliner');
    exit;
}

// 3. Proses update saat form disubmit
if (isset($_POST['update'])) {
    // ambil & bersihkan input
    $nama           = mysqli_real_escape_string($koneksi, $_POST['nama_kuliner']);
    $kabupaten      = mysqli_real_escape_string($koneksi, $_POST['kabupaten']);
    $kecamatan      = mysqli_real_escape_string($koneksi, $_POST['kecamatan']);
    $alamat         = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    // rentang harga
    $h1              = (int) $_POST['harga_mulai'];
    $h2              = (int) $_POST['harga_selesai'];
    $harga_range     = "{$h1} - {$h2}";
    // hari & jam operasional
    $hm              = mysqli_real_escape_string($koneksi, $_POST['hari_mulai_operasional']);
    $hs              = mysqli_real_escape_string($koneksi, $_POST['hari_selesai_operasional']);
    $jb              = mysqli_real_escape_string($koneksi, $_POST['jam_buka']);
    $jt              = mysqli_real_escape_string($koneksi, $_POST['jam_tutup']);
    $jam_operasional = "$hm - $hs, $jb - $jt";
    // menu & special
    $menu            = mysqli_real_escape_string($koneksi, $_POST['menu']);
    $special_menu    = mysqli_real_escape_string($koneksi, $_POST['special_menu']);
    $deskripsi       = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    // Latitude & Longitude
    $latitude        = mysqli_real_escape_string($koneksi, $_POST['latitude']);
    $longitude       = mysqli_real_escape_string($koneksi, $_POST['longitude']);
    
    // GAMBAR - sama seperti edit oleh2
    $oldGambar = isset($_POST['old_gambar']) ? explode(',', $_POST['old_gambar']) : [];
    $hapusGambar = isset($_POST['hapus_gambar']) ? $_POST['hapus_gambar'] : [];
    $gambarNames = [];
    
    // Hapus gambar lama yang dicentang
    foreach ($oldGambar as $img) {
        if (!in_array($img, $hapusGambar) && $img != '') {
            $gambarNames[] = $img;
        } else if ($img != '' && file_exists("uploads/$img")) {
            @unlink("uploads/$img");
        }
    }
    
    // Tambah gambar baru
    if (!empty($_FILES['gambar']['name'][0])) {
        foreach ($_FILES['gambar']['tmp_name'] as $i => $tmp) {
            $name = $_FILES['gambar']['name'][$i];
            if ($name) {
                $target = "uploads/" . basename($name);
                if (move_uploaded_file($tmp, $target)) {
                    $gambarNames[] = $name;
                }
            }
        }
    }
    $gambarStr = mysqli_real_escape_string($koneksi, implode(',', $gambarNames));

    // jalankan UPDATE
    $sql = "UPDATE tb_kuliner SET
              nama_kuliner    = '$nama',
              kabupaten       = '$kabupaten',
              kecamatan       = '$kecamatan',
              alamat          = '$alamat',
              harga_range     = '$harga_range',
              jam_operasional = '$jam_operasional',
              menu            = '$menu',
              special_menu    = '$special_menu',
              gambar          = '$gambarStr',
              deskripsi       = '$deskripsi',
              latitude        = '$latitude',
              longitude       = '$longitude'
            WHERE id_kuliner = '$id_kuliner'";
    $koneksi->query($sql);

    echo "<script>
            alert('Data kuliner berhasil diperbarui!');
            window.location='index.php?page=MyApp/tabel_kuliner';
          </script>";
    exit;
}

// 4. Ambil data untuk form
$res = $koneksi->query("SELECT * FROM tb_kuliner WHERE id_kuliner = '$id_kuliner'");
if (!$res || $res->num_rows === 0) {
    echo "<script>
            alert('Data tidak ditemukan!');
            window.location='index.php?page=MyApp/data_kuliner';
          </script>";
    exit;
}
$data       = $res->fetch_assoc();
$oldImages  = explode(',', $data['gambar']);

// pecah harga_range
list($hmin, $hmax) = explode(' - ', $data['harga_range']);
// pecah jam_operasional
$parts       = explode(', ', $data['jam_operasional']);
list($dayPart, $timePart) = array_pad($parts, 2, '');
list($d1, $d2) = explode(' - ', $dayPart.' - ');
list($j1, $j2) = explode(' - ', $timePart.' - ');
?>
<section class="content-header">
    <h1 style="text-align:center;">Edit Data Kuliner</h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">Form Edit Kuliner</h3></div>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="box-body">

                <input type="hidden" name="old_gambar" value="<?= htmlspecialchars($data['gambar']) ?>">

                <div class="form-group">
                    <label>ID Kuliner</label>
                    <input class="form-control" value="<?= htmlspecialchars($data['id_kuliner']) ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Nama Kuliner</label>
                    <input type="text" name="nama_kuliner" class="form-control"
                           value="<?= htmlspecialchars($data['nama_kuliner']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Kabupaten</label>
                    <select name="kabupaten" class="form-control" required>
                        <?php
                        $kabList = ["Barito Selatan","Barito Timur","Barito Utara","Gunung Mas",
                                    "Kapuas","Katingan","Kotawaringin Barat","Kotawaringin Timur",
                                    "Lamandau","Murung Raya","Pulang Pisau","Seruyan",
                                    "Sukamara","Kota Palangka Raya"];
                        foreach ($kabList as $kab) {
                            $sel = $data['kabupaten'] === $kab ? 'selected' : '';
                            echo "<option value=\"$kab\" $sel>$kab</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Kecamatan</label>
                    <input type="text" name="kecamatan" class="form-control"
                           value="<?= htmlspecialchars($data['kecamatan']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <input type="text" name="alamat" class="form-control"
                           value="<?= htmlspecialchars($data['alamat']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Latitude (contoh: -2.2053)</label>
                    <input type="text" name="latitude" class="form-control"
                           value="<?= htmlspecialchars($data['latitude']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Longitude (contoh: 113.8697)</label>
                    <input type="text" name="longitude" class="form-control"
                           value="<?= htmlspecialchars($data['longitude']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Rentang Harga</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" name="harga_mulai" class="form-control"
                                   value="<?= (int)$hmin ?>" required>
                        </div>
                        <div class="col-md-6">
                            <input type="number" name="harga_selesai" class="form-control"
                                   value="<?= (int)$hmax ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Hari & Jam Operasional</label>
                    <div class="row">
                        <div class="col-md-3">
                            <select name="hari_mulai_operasional" class="form-control" required>
                                <?php foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $d): ?>
                                    <option <?= trim($d1) === $d ? 'selected' : '' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="hari_selesai_operasional" class="form-control" required>
                                <?php foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $d): ?>
                                    <option <?= trim($d2) === $d ? 'selected' : '' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="time" name="jam_buka" class="form-control"
                                   value="<?= htmlspecialchars($j1) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <input type="time" name="jam_tutup" class="form-control"
                                   value="<?= htmlspecialchars($j2) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Daftar Menu (pisah koma)</label>
                    <textarea name="menu" class="form-control" rows="2" required><?= htmlspecialchars($data['menu']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Menu Spesial</label>
                    <input type="text" name="special_menu" class="form-control"
                           value="<?= htmlspecialchars($data['special_menu']) ?>" required>
                </div>

                <!-- GAMBAR - sama seperti edit oleh2 -->
                <div class="form-group">
                    <label>Gambar (maks 5)</label>
                    <div style="margin-bottom:12px;">
                        <?php
                        $fotoLama = explode(',', $data['gambar']);
                        foreach ($fotoLama as $key => $foto) {
                            if ($foto != '') {
                                echo '<div style="display:inline-block;margin:3px 12px 7px 0;text-align:center;">
                                    <img src="uploads/' . htmlspecialchars($foto) . '" style="width:70px;height:55px;object-fit:cover;border-radius:6px;border:1px solid #dedede;">
                                    <br>
                                    <label style="font-size:12px;"><input type="checkbox" name="hapus_gambar[]" value="' . htmlspecialchars($foto) . '"> Hapus</label>
                                </div>';
                            }
                        }
                        ?>
                    </div>
                    <input type="file" name="gambar[]" class="form-control" accept="image/*" multiple>
                    <small class="text-muted">Upload gambar baru jika ingin menambah. Centang "Hapus" untuk menghapus foto lama.</small>
                </div>

                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4" required><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                </div>

            </div>
            <div class="box-footer">
                <button type="submit" name="update" class="btn btn-success">Perbarui</button>
                <a href="index.php?page=MyApp/data_kuliner" class="btn btn-warning">Batal</a>
            </div>
        </form>
    </div>
</section>