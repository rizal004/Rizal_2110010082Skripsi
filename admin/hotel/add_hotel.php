<?php
// File: add_hotel.php (Form Tambah Hotel)
include "inc/koneksi.php";

if (isset($_POST['save'])) {
    $id_hotel      = uniqid('HTL');
    $nama_hotel    = mysqli_real_escape_string($koneksi, $_POST['nama_hotel']);
    $provinsi      = mysqli_real_escape_string($koneksi, $_POST['provinsi']);
    $kabupaten     = mysqli_real_escape_string($koneksi, $_POST['kabupaten']);
    $kecamatan     = mysqli_real_escape_string($koneksi, $_POST['kecamatan']);
    $alamat        = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $latitude      = mysqli_real_escape_string($koneksi, $_POST['latitude']);
    $longitude     = mysqli_real_escape_string($koneksi, $_POST['longitude']);
    $kontak        = mysqli_real_escape_string($koneksi, $_POST['kontak']);
    $fasilitas     = mysqli_real_escape_string($koneksi, $_POST['fasilitas']);
    $deskripsi     = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $tanggal_upload= date('Y-m-d H:i:s');

    // Harga Hotel (range)
    $harga_mulai   = mysqli_real_escape_string($koneksi, $_POST['harga_mulai']);
    $harga_sampai  = mysqli_real_escape_string($koneksi, $_POST['harga_sampai']);
    $harga_hotel = "Rp " . number_format($harga_mulai, 0, ',', '.') . " - Rp " . number_format($harga_sampai, 0, ',', '.') . " / malam";

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

    // Query sudah dengan tanggal_upload
    $sql = $koneksi->query("INSERT INTO tb_hotel (
        id_hotel, nama_hotel, provinsi, kabupaten, kecamatan, alamat,
        latitude, longitude, harga_hotel, kontak, fasilitas, gambar, deskripsi, tanggal_upload
    ) VALUES (
        '$id_hotel', '$nama_hotel', '$provinsi', '$kabupaten', '$kecamatan', '$alamat',
        '$latitude', '$longitude', '$harga_hotel', '$kontak', '$fasilitas', '$gambar', '$deskripsi', '$tanggal_upload'
    )");

    if ($sql) {
        echo "<script>
                alert('Hotel berhasil ditambahkan!');
                window.location='index.php?page=MyApp/tabel_hotel';
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
    "Kotawaringin Timur" => ["Antang Kalang", "Aruta", "Baamang", "Cempaga", "Cempaga Hulu", "Kota Besi", "Kurun", "Mentaya Hilir Selatan", "Mentaya Hilir Utara", "Mentaya Hulu", "Parenggean", "Pulau Hanaut", "Sampit", "Seranau", "Teluk Sampit", "Telawang", "Tualan Hulu"],
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
    <h1 style="text-align:center;">Tambah Hotel</h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Form Tambah Hotel</h3>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="box-body">
                <!-- Nama Hotel -->
                <div class="form-group">
                    <label>Nama Hotel</label>
                    <input type="text" name="nama_hotel" class="form-control" required>
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
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="text" name="latitude" class="form-control" placeholder="Contoh: -2.2096" required>
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="text" name="longitude" class="form-control" placeholder="Contoh: 113.9137" required>
                </div>
                
                <!-- Harga Hotel (Range) -->
                <div class="form-group">
                    <label>Harga Hotel</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" name="harga_mulai" class="form-control" placeholder="Harga Mulai (contoh: 200000)" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="harga_sampai" class="form-control" placeholder="Harga Sampai (contoh: 1000000)" required>
                        </div>
                    </div>
                    <small class="text-muted">Masukkan angka tanpa Rp atau tanda titik.</small>
                </div>
                <div class="form-group">
                    <label>Kontak</label>
                    <input type="text" name="kontak" class="form-control" placeholder="Nomor Telepon / WA" required>
                </div>
                <div class="form-group">
                    <label>Fasilitas</label>
                    <input type="text" name="fasilitas" class="form-control" placeholder="Contoh: WiFi, Parkir, Kolam Renang" required>
                </div>
                <div class="form-group">
                    <label>Gambar (maks 10)</label>
                    <input type="file" name="gambar[]" class="form-control" accept="image/*" multiple>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4"></textarea>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" name="save" class="btn btn-primary">Simpan</button>
                <a href="?page=MyApp/tabel_hotel" class="btn btn-warning">Batal</a>
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