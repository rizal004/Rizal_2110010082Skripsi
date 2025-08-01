<?php
include "inc/koneksi.php";

// Ambil ID dari URL
$id_oleh2 = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
if (empty($id_oleh2)) {
    header('Location: index.php?page=MyApp/tabel_oleh2');
    exit;
}

// Proses update saat form disubmit
if (isset($_POST['update'])) {
    $nama_toko     = mysqli_real_escape_string($koneksi, $_POST['nama_toko']);
    $provinsi      = mysqli_real_escape_string($koneksi, $_POST['provinsi']);
    $kabupaten     = mysqli_real_escape_string($koneksi, $_POST['kabupaten']);
    $kecamatan     = mysqli_real_escape_string($koneksi, $_POST['kecamatan']);
    $alamat        = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    // Latitude & Longitude
    $latitude      = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
    $longitude     = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;

    // Rentang harga
    $harga_mulai   = (int) $_POST['harga_mulai'];
    $harga_selesai = (int) $_POST['harga_selesai'];
    $harga_range   = $harga_mulai . ' - ' . $harga_selesai;

    // Hari & jam operasional
    if (
        !empty($_POST['hari_mulai_operasional']) &&
        !empty($_POST['hari_selesai_operasional']) &&
        !empty($_POST['jam_buka']) &&
        !empty($_POST['jam_tutup'])
    ) {
        $hari_mulai = mysqli_real_escape_string($koneksi, $_POST['hari_mulai_operasional']);
        $hari_selesai = mysqli_real_escape_string($koneksi, $_POST['hari_selesai_operasional']);
        $jam_buka = mysqli_real_escape_string($koneksi, $_POST['jam_buka']);
        $jam_tutup = mysqli_real_escape_string($koneksi, $_POST['jam_tutup']);
        $jam_operasional = "$hari_mulai - $hari_selesai, $jam_buka - $jam_tutup";
    } else {
        $jam_operasional = '';
    }

    // Barang dijual
    $barang_dijual = mysqli_real_escape_string($koneksi, $_POST['barang_dijual']);

    // Deskripsi
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    // GAMBAR - sama seperti edit wisata
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
    $gambar = mysqli_real_escape_string($koneksi, implode(',', $gambarNames));

    // Update query
    $sql = $koneksi->query("UPDATE tb_oleh2 SET 
        nama_toko = '$nama_toko',
        provinsi = '$provinsi',
        kabupaten = '$kabupaten',
        kecamatan = '$kecamatan',
        alamat = '$alamat',
        harga_range = '$harga_range',
        jam_operasional = '$jam_operasional',
        barang_dijual = '$barang_dijual',
        gambar = '$gambar',
        deskripsi = '$deskripsi',
        latitude = ".($latitude!==null?"'$latitude'":"NULL").",
        longitude = ".($longitude!==null?"'$longitude'":"NULL")."
        WHERE id_oleh2 = '$id_oleh2'");

    if ($sql) {
        echo "<script>alert('Data berhasil diperbarui!');window.location.href='index.php?page=MyApp/tabel_oleh2';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data!');</script>";
    }
}

// Ambil data untuk form
$query = $koneksi->query("SELECT * FROM tb_oleh2 WHERE id_oleh2 = '$id_oleh2'");
$data  = $query->fetch_assoc();

// Parse jam operasional
$hariMulai = '';
$hariSelesai = '';
$jamBuka = '';
$jamTutup = '';
if (!empty($data['jam_operasional'])) {
    $jam_parts = explode(', ', $data['jam_operasional']);
    if (count($jam_parts) > 0) {
        $hari_parts = explode(' - ', $jam_parts[0]);
        if (count($hari_parts) > 0) {
            $hariMulai = $hari_parts[0];
            if (isset($hari_parts[1])) $hariSelesai = $hari_parts[1];
        }
        if (isset($jam_parts[1])) {
            $waktu_parts = explode(' - ', $jam_parts[1]);
            if (isset($waktu_parts[0])) $jamBuka = $waktu_parts[0];
            if (isset($waktu_parts[1])) $jamTutup = $waktu_parts[1];
        }
    }
}

// Parse harga range
$hargaMulai = '';
$hargaSelesai = '';
if (!empty($data['harga_range'])) {
    $harga_parts = explode(' - ', $data['harga_range']);
    if (isset($harga_parts[0])) $hargaMulai = $harga_parts[0];
    if (isset($harga_parts[1])) $hargaSelesai = $harga_parts[1];
}
?>

<section class="content-header">
    <h1 style="text-align:center;">Edit Data Oleh-oleh Khas</h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Form Edit Oleh-oleh</h3>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="box-body">
                <input type="hidden" name="old_gambar" value="<?= htmlspecialchars($data['gambar']); ?>">
                
                <!-- ID Oleh-oleh -->
                <div class="form-group">
                    <label>ID Oleh-oleh</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['id_oleh2']); ?>" readonly>
                </div>

                <!-- Nama Toko -->
                <div class="form-group">
                    <label>Nama Toko</label>
                    <input type="text" name="nama_toko" class="form-control" value="<?= htmlspecialchars($data['nama_toko']); ?>" required>
                </div>

                <!-- Lokasi -->
                <div class="form-group">
                    <label>Provinsi</label>
                    <input type="text" name="provinsi" class="form-control" value="<?= htmlspecialchars($data['provinsi']); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label>Kabupaten</label>
                    <select name="kabupaten" class="form-control" required>
                        <option value="">-- Pilih Kabupaten --</option>
                        <?php
                        $kabList = ["Barito Selatan","Barito Timur","Barito Utara","Gunung Mas",
                                    "Kapuas","Katingan","Kotawaringin Barat","Kotawaringin Timur",
                                    "Lamandau","Murung Raya","Pulang Pisau","Seruyan",
                                    "Sukamara","Kota Palangka Raya"];
                        foreach ($kabList as $kab) {
                            $selected = $data['kabupaten'] === $kab ? 'selected' : '';
                            echo "<option value=\"$kab\" $selected>$kab</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Kecamatan</label>
                    <input type="text" name="kecamatan" class="form-control" value="<?= htmlspecialchars($data['kecamatan']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($data['alamat']); ?>" required>
                </div>

                <!-- Latitude & Longitude -->
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="text" step="any" name="latitude" class="form-control" placeholder="-1.6..." value="<?= htmlspecialchars($data['latitude']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="text" step="any" name="longitude" class="form-control" placeholder="113.6..." value="<?= htmlspecialchars($data['longitude']); ?>" required>
                </div>

                <!-- Rentang Harga -->
                <div class="form-group">
                    <label>Rentang Harga</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" name="harga_mulai" class="form-control" placeholder="Harga Mulai" value="<?= htmlspecialchars($hargaMulai); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <input type="number" name="harga_selesai" class="form-control" placeholder="Harga Selesai" value="<?= htmlspecialchars($hargaSelesai); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Hari & Jam Operasional -->
                <div class="form-group">
                    <label>Hari & Jam Operasional</label>
                    <div class="row">
                        <div class="col-md-3">
                            <select name="hari_mulai_operasional" class="form-control">
                                <option value="">-- Dari Hari --</option>
                                <?php
                                $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
                                foreach ($hariList as $hari) {
                                    $selected = $hariMulai === $hari ? 'selected' : '';
                                    echo "<option value=\"$hari\" $selected>$hari</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="hari_selesai_operasional" class="form-control">
                                <option value="">-- Sampai Hari --</option>
                                <?php
                                foreach ($hariList as $hari) {
                                    $selected = $hariSelesai === $hari ? 'selected' : '';
                                    echo "<option value=\"$hari\" $selected>$hari</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="time" name="jam_buka" class="form-control" placeholder="Jam Buka" value="<?= htmlspecialchars($jamBuka); ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="time" name="jam_tutup" class="form-control" placeholder="Jam Tutup" value="<?= htmlspecialchars($jamTutup); ?>">
                        </div>
                    </div>
                </div>

                <!-- Barang Dijual -->
                <div class="form-group">
                    <label>Daftar Barang Dijual (pisah dengan koma)</label>
                    <textarea name="barang_dijual" class="form-control" rows="2" placeholder="Contoh: Kain Tenun, Souvenir Kayu, Sirup Aren" required><?= htmlspecialchars($data['barang_dijual']); ?></textarea>
                </div>

                <!-- GAMBAR - sama seperti edit wisata -->
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

                <!-- Deskripsi -->
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4"><?= htmlspecialchars($data['deskripsi']); ?></textarea>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" name="update" class="btn btn-success">Perbarui</button>
                <a href="?page=MyApp/tabel_oleh2" class="btn btn-warning">Batal</a>
            </div>
        </form>
    </div>
</section>