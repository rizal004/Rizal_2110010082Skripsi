<?php
include "inc/koneksi.php";

// Ambil ID dari URL
$id_wisata = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
if (empty($id_wisata)) {
    header('Location: index.php?page=MyApp/tabel_wisata');
    exit;
}

// Proses update saat form disubmit
if (isset($_POST['update'])) {
    $kategori         = $_POST['kategori'];
    $nama_wisata      = $_POST['nama_wisata'];
    $provinsi         = $_POST['provinsi'];
    $kabupaten        = $_POST['kabupaten'];
    $kecamatan        = $_POST['kecamatan'];
    $alamat           = $_POST['alamat'];
    $harga_tiket      = $_POST['harga_tiket'];
    $jam_operasional  = $_POST['hari_mulai'] . " - " . $_POST['hari_selesai'] . ", " . $_POST['jam_buka'] . " - " . $_POST['jam_tutup'];
    $fasilitas        = $_POST['fasilitas'];
    $kondisi_jalan    = $_POST['kondisi_jalan'];
    $deskripsi        = $_POST['deskripsi'];
    $latitude         = $_POST['latitude'];
    $longitude        = $_POST['longitude'];
    $harga_makanan_min   = $_POST['harga_makanan_min'];
    $harga_makanan_max   = $_POST['harga_makanan_max'];
    $harga_minuman_min   = $_POST['harga_minuman_min'];
    $harga_minuman_max   = $_POST['harga_minuman_max'];
    $biaya_sewa          = $_POST['biaya_sewa'];
    $estimasi_biaya      = $_POST['estimasi_biaya'];

    // FOTO
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
    $gambarArray = $_FILES['gambar'];
    for ($i = 0; $i < count($gambarArray['name']); $i++) {
        $namaFile = $gambarArray['name'][$i];
        $tmpFile  = $gambarArray['tmp_name'][$i];
        if ($namaFile != '') {
            $target = "uploads/" . basename($namaFile);
            if (move_uploaded_file($tmpFile, $target)) {
                $gambarNames[] = $namaFile;
            }
        }
    }
    $gambarString = implode(',', $gambarNames);

    // Update query
    $sql = $koneksi->query("UPDATE tb_wisata SET 
        kategori = '$kategori',
        nama_wisata = '$nama_wisata',
        provinsi = '$provinsi',
        kabupaten = '$kabupaten',
        kecamatan = '$kecamatan',
        alamat = '$alamat',
        harga_tiket = '$harga_tiket',
        jam_operasional = '$jam_operasional',
        fasilitas = '$fasilitas',
        kondisi_jalan = '$kondisi_jalan',
        gambar = '$gambarString',
        deskripsi = '$deskripsi',
        latitude = '$latitude',
        longitude = '$longitude',
        harga_makanan_min = '$harga_makanan_min',
        harga_makanan_max = '$harga_makanan_max',
        harga_minuman_min = '$harga_minuman_min',
        harga_minuman_max = '$harga_minuman_max',
        biaya_sewa = '$biaya_sewa',
        estimasi_biaya = '$estimasi_biaya'
        WHERE id_wisata = '$id_wisata'");

    if ($sql) {
        echo "<script>alert('Data berhasil diperbarui!');window.location.href='index.php?page=MyApp/tabel_wisata';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data!');</script>";
    }
}

// Ambil data untuk form
$query = $koneksi->query("SELECT * FROM tb_wisata WHERE id_wisata = '$id_wisata'");
$data  = $query->fetch_assoc();

$hariMulai = 'Senin';
$hariSelesai = 'Minggu';
$jamBuka = '08:00';
$jamTutup = '17:00';
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
?>

<section class="content-header">
    <h1 style="text-align:center;">Edit Data Wisata</h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">Form Edit Wisata</h3></div>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="box-body">
                <input type="hidden" name="old_gambar" value="<?= htmlspecialchars($data['gambar']); ?>">

                <div class="form-group"><label>ID Wisata</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['id_wisata']); ?>" readonly>
                </div>

                <div class="form-group"><label>Kategori</label>
                    <select name="kategori" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php
                        $cats = ['Wisata Alam','Wisata Edukasi','Wisata Religi','Wisata Buatan','Wisata Sejarah'];
                        foreach ($cats as $c) {
                            $sel = $data['kategori'] === $c ? 'selected' : '';
                            echo "<option value=\"$c\" $sel>$c</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group"><label>Nama Wisata</label>
                    <input type="text" name="nama_wisata" class="form-control" value="<?= htmlspecialchars($data['nama_wisata']); ?>" required>
                </div>

                <div class="form-group"><label>Provinsi</label>
                    <input type="text" name="provinsi" class="form-control" value="<?= htmlspecialchars($data['provinsi']); ?>" readonly>
                </div>

                <div class="form-group"><label>Kabupaten</label>
                    <select name="kabupaten" class="form-control" required>
                        <option value="">-- Pilih Kabupaten --</option>
                        <?php
                        $kab = [
                            'Barito Selatan','Barito Timur','Barito Utara','Gunung Mas','Kapuas',
                            'Katingan','Kotawaringin Barat','Kotawaringin Timur','Lamandau',
                            'Murung Raya','Pulang Pisau','Seruyan','Sukamara','Kota Palangka Raya'
                        ];
                        foreach ($kab as $k) {
                            $sel = $data['kabupaten'] === $k ? 'selected' : '';
                            echo "<option value=\"$k\" $sel>$k</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group"><label>Kecamatan</label>
                    <input type="text" name="kecamatan" class="form-control" value="<?= htmlspecialchars($data['kecamatan']); ?>" required>
                </div>

                <div class="form-group"><label>Alamat Lengkap</label>
                    <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($data['alamat']); ?>" required>
                </div>

                <div class="form-group"><label>Harga Tiket</label>
                    <input type="text" name="harga_tiket" class="form-control" value="<?= htmlspecialchars($data['harga_tiket']); ?>" required>
                </div>

                <div class="form-group"><label>Hari Operasional</label>
                    <div class="row">
                        <div class="col-md-6">
                            <select name="hari_mulai" class="form-control" required>
                                <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $h) {
                                    $sel = $hariMulai === $h ? 'selected' : '';
                                    echo "<option value=\"$h\" $sel>$h</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select name="hari_selesai" class="form-control" required>
                                <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $h) {
                                    $sel = $hariSelesai === $h ? 'selected' : '';
                                    echo "<option value=\"$h\" $sel>$h</option>";
                                } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group"><label>Jam Operasional</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="time" name="jam_buka" class="form-control" value="<?= $jamBuka; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <input type="time" name="jam_tutup" class="form-control" value="<?= $jamTutup; ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group"><label>Fasilitas</label>
                    <input type="text" name="fasilitas" class="form-control" value="<?= htmlspecialchars($data['fasilitas']); ?>" required>
                </div>

                <div class="form-group"><label>Kondisi Jalan</label>
                    <input type="text" name="kondisi_jalan" class="form-control" value="<?= htmlspecialchars($data['kondisi_jalan']); ?>" required>
                </div>

                <div class="form-group"><label>Latitude</label>
                    <input type="text" name="latitude" class="form-control" value="<?= htmlspecialchars($data['latitude']); ?>" required>
                    <small class="text-muted">Contoh: -1.6429</small>
                </div>
                <div class="form-group"><label>Longitude</label>
                    <input type="text" name="longitude" class="form-control" value="<?= htmlspecialchars($data['longitude']); ?>" required>
                    <small class="text-muted">Contoh: 113.6197</small>
                </div>

                <!-- FOTO -->
                <div class="form-group"><label>Gambar Wisata</label>
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

                <!-- Kolom Biaya Makanan/Minuman/Sewa/Estimasi -->
                <div class="form-group"><label>Harga Makanan (Rp)</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" name="harga_makanan_min" class="form-control" placeholder="Harga Makanan Termurah" value="<?= htmlspecialchars($data['harga_makanan_min']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <input type="number" name="harga_makanan_max" class="form-control" placeholder="Harga Makanan Termahal" value="<?= htmlspecialchars($data['harga_makanan_max']); ?>" required>
                        </div>
                    </div>
                    <small class="text-muted">Isi perkiraan harga makanan per porsi.</small>
                </div>
                <div class="form-group"><label>Harga Minuman (Rp)</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" name="harga_minuman_min" class="form-control" placeholder="Harga Minuman Termurah" value="<?= htmlspecialchars($data['harga_minuman_min']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <input type="number" name="harga_minuman_max" class="form-control" placeholder="Harga Minuman Termahal" value="<?= htmlspecialchars($data['harga_minuman_max']); ?>" required>
                        </div>
                    </div>
                    <small class="text-muted">Isi perkiraan harga minuman per gelas/botol.</small>
                </div>
                <div class="form-group"><label>Biaya Penyewaan (Kapal/Motor/Alat, Rp)</label>
                    <input type="text" name="biaya_sewa" class="form-control" placeholder="Contoh: Sewa kapal mulai Rp50.000/jam, Sewa motor Rp75.000/hari" value="<?= htmlspecialchars($data['biaya_sewa']); ?>">
                    <small class="text-muted">Kosongkan jika tidak ada.</small>
                </div>
                <div class="form-group"><label>Estimasi Biaya Perjalanan Total</label>
                    <input type="text" name="estimasi_biaya" class="form-control" placeholder="Contoh: Rp250.000 – Rp500.000 per orang" value="<?= htmlspecialchars($data['estimasi_biaya']); ?>">
                    <small class="text-muted">Masukkan estimasi biaya perjalanan ke lokasi dari kota terdekat.</small>
                </div>
                <div class="form-group"><label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4" required><?= htmlspecialchars($data['deskripsi']); ?></textarea>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" name="update" class="btn btn-success">Perbarui</button>
                <a href="?page=MyApp/tabel_wisata" class="btn btn-warning">Batal</a>
            </div>
        </form>
    </div>
</section>
