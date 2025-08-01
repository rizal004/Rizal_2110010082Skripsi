<?php
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['save'])) {
    $id_wisata     = uniqid('WIS');
    $kategori      = $_POST['kategori'];
    $nama_wisata   = $_POST['nama_wisata'];
    $provinsi      = $_POST['provinsi'];
    $kabupaten     = $_POST['kabupaten'];
    $kecamatan     = $_POST['kecamatan'];
    $alamat        = $_POST['alamat'];
    $harga_tiket   = $_POST['harga_tiket'];
    $jam_operasional = $_POST['hari_mulai'] . " - " . $_POST['hari_selesai'] . ", " . $_POST['jam_buka'] . " - " . $_POST['jam_tutup'];
    $fasilitas      = $_POST['fasilitas'];
    $kondisi_jalan  = $_POST['kondisi_jalan'];
    $deskripsi      = $_POST['deskripsi'];
    $latitude       = $_POST['latitude'];
    $longitude      = $_POST['longitude'];
    $harga_makanan_min   = $_POST['harga_makanan_min'];
    $harga_makanan_max   = $_POST['harga_makanan_max'];
    $harga_minuman_min   = $_POST['harga_minuman_min'];
    $harga_minuman_max   = $_POST['harga_minuman_max'];
    $biaya_sewa          = $_POST['biaya_sewa'];
    $estimasi_biaya      = $_POST['estimasi_biaya'];
    $tanggal_upload = date('Y-m-d H:i:s');

    // Handle upload gambar (multiple)
    $gambarArray = $_FILES['gambar'];
    $gambarNames = [];
    for ($i = 0; $i < count($gambarArray['name']); $i++) {
        $namaFile = $gambarArray['name'][$i];
        $tmpFile = $gambarArray['tmp_name'][$i];
        if ($namaFile != '') {
            $target = "uploads/" . basename($namaFile);
            if (move_uploaded_file($tmpFile, $target)) {
                $gambarNames[] = $namaFile;
            }
        }
    }
    $gambar = implode(',', $gambarNames);


    $sql = $koneksi->query("INSERT INTO tb_wisata (
        id_wisata, kategori, nama_wisata, provinsi, kabupaten, kecamatan, alamat,
        harga_tiket, jam_operasional, fasilitas, kondisi_jalan, gambar, deskripsi, tanggal_upload,
        latitude, longitude, harga_makanan_min, harga_makanan_max, harga_minuman_min, harga_minuman_max, biaya_sewa, estimasi_biaya
    ) VALUES (
        '$id_wisata', '$kategori', '$nama_wisata', '$provinsi', '$kabupaten', '$kecamatan', '$alamat',
        '$harga_tiket', '$jam_operasional', '$fasilitas', '$kondisi_jalan', '$gambar', '$deskripsi', '$tanggal_upload',
        '$latitude', '$longitude', '$harga_makanan_min', '$harga_makanan_max', '$harga_minuman_min', '$harga_minuman_max', '$biaya_sewa', '$estimasi_biaya'
    )");

    if ($sql) {
        echo "<script>alert('Data berhasil disimpan!');window.location.href='index.php?page=MyApp/tabel_wisata';</script>";
    } else {
        echo "<script>alert('Data gagal disimpan!');</script>";
    }
    exit;
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
    <h1 style="text-align:center;">Tambah Informasi Wisata</h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">Tambah Data Wisata</h3></div>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="box-body">
                <div class="form-group"><label>Kategori</label>
                    <select name="kategori" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option>Wisata Alam</option>
                        <option>Wisata Edukasi</option>
                        <option>Wisata Religi</option>
                        <option>Wisata Buatan</option>
                        <option>Wisata Sejarah</option>
                    </select>
                </div>
                <div class="form-group"><label>Nama Wisata</label>
                    <input type="text" name="nama_wisata" class="form-control" required>
                </div>
                <div class="form-group"><label>Provinsi</label>
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
                <div class="form-group"><label>Alamat Lengkap</label>
                    <input type="text" name="alamat" class="form-control" required>
                </div>
                <div class="form-group"><label>Harga Tiket</label>
                    <input type="text" name="harga_tiket" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Hari & Jam Operasional</label>
                    <div class="row">
                        <div class="col-md-3">
                            <select name="hari_mulai" class="form-control" required>
                                <option value="">-- Hari Mulai --</option>
                                <option>Senin</option><option>Selasa</option><option>Rabu</option>
                                <option>Kamis</option><option>Jumat</option><option>Sabtu</option><option>Minggu</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="hari_selesai" class="form-control" required>
                                <option value="">-- Hari Selesai --</option>
                                <option>Senin</option><option>Selasa</option><option>Rabu</option>
                                <option>Kamis</option><option>Jumat</option><option>Sabtu</option><option>Minggu</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="time" name="jam_buka" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <input type="time" name="jam_tutup" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="form-group"><label>Fasilitas</label>
                    <input type="text" name="fasilitas" class="form-control" required>
                </div>
                <div class="form-group"><label>Kondisi Jalan</label>
                    <input type="text" name="kondisi_jalan" class="form-control" required>
                </div>
                <div class="form-group"><label>Gambar (Maks 5 gambar)</label>
                    <input type="file" name="gambar[]" class="form-control" accept="image/*" multiple required>
                    <small class="text-muted">Tekan Ctrl (atau Command) untuk memilih lebih dari satu.</small>
                </div>
                
                <div class="form-group"><label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="text" name="latitude" class="form-control" placeholder="-1.6429" required>
                    <small class="text-muted">Contoh: -1.6429</small>
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="text" name="longitude" class="form-control" placeholder="113.6197" required>
                    <small class="text-muted">Contoh: 113.6197</small>
                </div>
                <!-- Kolom Biaya Makanan/Minuman/Sewa/Estimasi -->
                <div class="form-group"><label>Harga Makanan (Rp)</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" name="harga_makanan_min" class="form-control" placeholder="Harga Makanan Termurah" required>
                        </div>
                        <div class="col-md-6">
                            <input type="number" name="harga_makanan_max" class="form-control" placeholder="Harga Makanan Termahal" required>
                        </div>
                    </div>
                    <small class="text-muted">Isi perkiraan harga makanan per porsi.</small>
                </div>
                <div class="form-group"><label>Harga Minuman (Rp)</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" name="harga_minuman_min" class="form-control" placeholder="Harga Minuman Termurah" required>
                        </div>
                        <div class="col-md-6">
                            <input type="number" name="harga_minuman_max" class="form-control" placeholder="Harga Minuman Termahal" required>
                        </div>
                    </div>
                    <small class="text-muted">Isi perkiraan harga minuman per gelas/botol.</small>
                </div>
                <div class="form-group"><label>Biaya Penyewaan (Kapal/Motor/Alat, Rp)</label>
                    <input type="text" name="biaya_sewa" class="form-control" placeholder="Contoh: Sewa kapal mulai Rp50.000/jam, Sewa motor Rp75.000/hari">
                    <small class="text-muted">Kosongkan jika tidak ada.</small>
                </div>
                <div class="form-group"><label>Estimasi Biaya Perjalanan Total</label>
                    <input type="text" name="estimasi_biaya" class="form-control" placeholder="Contoh: Rp250.000 – Rp500.000 per orang">
                    <small class="text-muted">Masukkan estimasi biaya perjalanan ke lokasi dari kota terdekat.</small>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" name="save" class="btn btn-primary">Simpan</button>
                <a href="?page=MyApp/data_wisata" class="btn btn-warning">Batal</a>
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