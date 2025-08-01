<?php
// inc/koneksi.php
include "inc/koneksi.php";

if (isset($_POST['save'])) {
    // Generate unique ID
    $id_kuliner   = uniqid('KUL');
    // Sanitize input
    
    $nama_kuliner = mysqli_real_escape_string($koneksi, $_POST['nama_kuliner']);
    $provinsi     = mysqli_real_escape_string($koneksi, $_POST['provinsi']);
    $kabupaten    = mysqli_real_escape_string($koneksi, $_POST['kabupaten']);
    $kecamatan    = mysqli_real_escape_string($koneksi, $_POST['kecamatan']);
    $alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    // Latitude & Longitude
    $latitude     = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
    $longitude    = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;

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

    // Menu & special
    $menu          = mysqli_real_escape_string($koneksi, $_POST['menu']);
    $special_menu  = mysqli_real_escape_string($koneksi, $_POST['special_menu']);
    $deskripsi     = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $tanggal_upload = date('Y-m-d H:i:s');

    // Upload gambar
    $gambarNames = [];
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

    // Insert ke database
    $sql = $koneksi->query("
        INSERT INTO tb_kuliner (
            id_kuliner, nama_kuliner, provinsi, kabupaten, kecamatan,
            alamat, latitude, longitude, harga_range, jam_operasional, menu, special_menu,
            gambar, deskripsi, tanggal_upload
        ) VALUES (
            '$id_kuliner', '$nama_kuliner', '$provinsi', '$kabupaten', '$kecamatan',
            '$alamat', '$latitude', '$longitude', '$harga_range', '$jam_operasional', '$menu', '$special_menu',
            '$gambar', '$deskripsi', '$tanggal_upload'
        )
    ");

    if ($sql) {
        echo "<script>
                alert('Kuliner berhasil ditambahkan!');
                window.location='index.php?page=MyApp/tabel_kuliner';
              </script>";
    } else {
        echo "<script>alert('Gagal menyimpan data!');</script>";
    }
}

// Data kabupaten dan kecamatan Kalimantan Tengah
$data_wilayah = [
    "Barito Selatan" => ["Dusun Hilir", "Dusun Selatan", "Dusun Tengah", "Dusun Utara", "Gunung Bintang Awai", "Jenamas", "Karau Kuala", "Lemo", "Pematang Karau"],
    "Barito Timur" => ["Awang", "Benua Lima", "Dusun Timur", "Karimun", "Montalat", "Paju Epat", "Pematang Gubernur", "Pematang Hulu Sungai", "Raren Batuah", "Tamiang Layang"],
    "Barito Utara" => ["Gunung Timang", "Lahei", "Lahei Barat", "Montalat", "Muara Teweh", "Teweh Baru", "Teweh Selatan", "Teweh Tengah", "Teweh Timur"],
    "Gunung Mas" => ["Damang Batu", "Kurun", "Manuhing", "Manuhing Raya", "Rungan", "Rungan Barat", "Sepang", "Tewah"],
    "Kapuas" => ["Basiniang", "Dadahup", "Kapuas Barat", "Kapuas Hilir", "Kapuas Hulu", "Kapuas Kuala", "Kapuas Murung", "Kapuas Tengah", "Kapuas Timur", "Mantangai", "Pulau Malan", "Selat", "Tamban Catur"],
    "Katingan" => ["Bukit Santuai", "Katingan Hilir", "Katingan Hulu", "Katingan Kuala", "Katingan Tengah", "Marikit", "Mendawai", "Petak Malai", "Pulau Malan", "Sanaman Mantikei", "Tasik Payawan", "Tewang Sangalang Garing", "Pulau Hanaut"],
    "Kotawaringin Barat" => ["Arut Selatan", "Arut Utara", "Kotawaringin Lama", "Kumai", "Pangkalan Banteng", "Pangkalan Lada", "Sukamara"],
    "Kotawaringin Timur" => ["Antang Kalang", "Aruta", "Baamang", "Cempaga", "Cempaga Hulu", "Kota Besi", "Kurun", "Mentaya Hilir Selatan", "Mentawa", "Mentaya Hilir Utara", "Mentaya Hulu", "Parenggean", "Pulau Hanaut", "Sampit", "Seranau", "Teluk Sampit", "Telawang", "Tualan Hulu"],
    "Lamandau" => ["Batang Kawa", "Belantikan Raya", "Bulik", "Bulik Timur", "Delang", "Lamandau", "Menthobi Raya", "Sematu Jaya"],
    "Murung Raya" => ["Barito Tuhup Raya", "Laung Tuhup", "Murung", "Murung Pudak", "Permata Kecubung", "Pujon", "Sumber Barito", "Tanah Siang", "Tanah Siang Selatan"],
    "Pulang Pisau" => ["Banama Tingang", "Jabiren Raya", "Kahayan Hilir", "Kahayan Tengah", "Maliku", "Pandih Batu", "Sebangau", "Sebangau Kuala"],
    "Seruyan" => ["Batu Ampar", "Danau Sembuluh", "Danau Seluluk", "Hanau", "Pembuang", "Pembuang Hulu", "Seruyan Hilir", "Seruyan Hilir Timur", "Seruyan Hulu", "Seruyan Tengah"],
    "Sukamara" => ["Balai Riam", "Jelai", "Pantai Lunci", "Permata Kecubung", "Sukamara"],
    "Kota Palangka Raya" => ["Bukit Batu", "Jekan Raya", "Pahandut", "Rakumpit", "Sebangau"]
];
?>

<!-- CSS untuk Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--single {
    height: 34px;
    border: 1px solid #d2d6de;
    border-radius: 4px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 32px;
    padding-left: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 32px;
}
</style>

<section class="content-header">
    <h1 style="text-align:center;">Tambah Informasi Kuliner</h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Form Tambah Kuliner</h3>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="box-body">

                <!-- Nama Kuliner -->
                <div class="form-group">
                    <label>Nama Kuliner</label>
                    <input type="text" name="nama_kuliner" class="form-control" required>
                </div>

                <!-- Lokasi -->
                <div class="form-group">
                    <label>Provinsi</label>
                    <input type="text" name="provinsi" class="form-control" value="Kalimantan Tengah" readonly>
                </div>
                <div class="form-group">
                    <label>Kabupaten</label>
                    <select name="kabupaten" id="kabupaten" class="form-control select2" required>
                        <option value="">-- Pilih Kabupaten --</option>
                        <?php
                        foreach ($data_wilayah as $kabupaten => $kecamatan_list) {
                            echo "<option value='$kabupaten'>$kabupaten</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kecamatan</label>
                    <select name="kecamatan" id="kecamatan" class="form-control select2" required>
                        <option value="">-- Pilih Kecamatan --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <input type="text" name="alamat" class="form-control" required>
                </div>

                <!-- Latitude & Longitude -->
                <div class="form-group">
                    <label>Latitude (contoh: -2.2053)</label>
                    <input type="text" name="latitude" class="form-control" required placeholder="Latitude lokasi kuliner">
                </div>
                <div class="form-group">
                    <label>Longitude (contoh: 113.8697)</label>
                    <input type="text" name="longitude" class="form-control" required placeholder="Longitude lokasi kuliner">
                </div>

                <!-- Rentang Harga -->
                <div class="form-group">
                    <label>Rentang Harga</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" name="harga_mulai" class="form-control" placeholder="Harga Mulai" required>
                        </div>
                        <div class="col-md-6">
                            <input type="number" name="harga_selesai" class="form-control" placeholder="Harga Selesai" required>
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
                                <option>Senin</option><option>Selasa</option><option>Rabu</option>
                                <option>Kamis</option><option>Jumat</option><option>Sabtu</option><option>Minggu</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="hari_selesai_operasional" class="form-control">
                                <option value="">-- Sampai Hari --</option>
                                <option>Senin</option><option>Selasa</option><option>Rabu</option>
                                <option>Kamis</option><option>Jumat</option><option>Sabtu</option><option>Minggu</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="time" name="jam_buka" class="form-control" placeholder="Jam Buka">
                        </div>
                        <div class="col-md-3">
                            <input type="time" name="jam_tutup" class="form-control" placeholder="Jam Tutup">
                        </div>
                    </div>
                </div>

                <!-- Menu & Spesial -->
                <div class="form-group">
                    <label>Daftar Menu Dijual (pisah dengan koma)</label>
                    <textarea name="menu" class="form-control" rows="2" placeholder="Contoh: Soto, Nasi Goreng, Teh Manis" required></textarea>
                </div>
                <div class="form-group">
                    <label>Menu Spesial</label>
                    <input type="text" name="special_menu" class="form-control" placeholder="Contoh: Nasi Liwet Komplit" required>
                </div>

                <!-- Gambar -->
                <div class="form-group">
                    <label>Gambar (maks 5)</label>
                    <input type="file" name="gambar[]" class="form-control" accept="image/*" multiple>
                </div>

                <!-- Deskripsi -->
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4"></textarea>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" name="save" class="btn btn-primary">Simpan</button>
                <a href="?page=MyApp/tabel_kuliner" class="btn btn-warning">Batal</a>
            </div>
        </form>
    </div>
</section>

<!-- JavaScript untuk Select2 -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Data wilayah dari PHP ke JavaScript
    var dataWilayah = <?php echo json_encode($data_wilayah); ?>;
    
    // Inisialisasi Select2
    $('.select2').select2({
        theme: 'default',
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder');
        }
    });
    
    // Event handler untuk perubahan kabupaten
    $('#kabupaten').on('change', function() {
        var selectedKabupaten = $(this).val();
        var kecamatanSelect = $('#kecamatan');
        
        // Reset kecamatan
        kecamatanSelect.empty();
        kecamatanSelect.append('<option value="">-- Pilih Kecamatan --</option>');
        
        if (selectedKabupaten && dataWilayah[selectedKabupaten]) {
            // Tambahkan kecamatan sesuai kabupaten yang dipilih
            $.each(dataWilayah[selectedKabupaten], function(index, kecamatan) {
                kecamatanSelect.append('<option value="' + kecamatan + '">' + kecamatan + '</option>');
            });
        }
        
        // Refresh Select2 untuk kecamatan
        kecamatanSelect.trigger('change');
    });
    
    // Set placeholder untuk select2
    $('#kabupaten').attr('data-placeholder', '-- Pilih Kabupaten --');
    $('#kecamatan').attr('data-placeholder', '-- Pilih Kecamatan --');
});
</script>