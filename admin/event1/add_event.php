<?php
// File: add_event.php (Form Tambah Event)
include "inc/koneksi.php";

if (isset($_POST['save'])) {
    // Generate unique ID
    $id_event       = uniqid('EVT');
    // Sanitize input
    $nama_event     = mysqli_real_escape_string($koneksi, $_POST['nama_event']);
    $kategori       = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $provinsi       = mysqli_real_escape_string($koneksi, $_POST['provinsi']);
    $kabupaten      = mysqli_real_escape_string($koneksi, $_POST['kabupaten']);
    $kecamatan      = mysqli_real_escape_string($koneksi, $_POST['kecamatan']);
    $alamat         = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $latitude       = mysqli_real_escape_string($koneksi, $_POST['latitude']);
    $longitude      = mysqli_real_escape_string($koneksi, $_POST['longitude']);
    $tanggal_mulai  = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
    $tanggal_selesai= mysqli_real_escape_string($koneksi, $_POST['tanggal_selesai']);
    $no_hp          = mysqli_real_escape_string($koneksi, $_POST['no_hp']); // Field baru untuk nomor HP
    
    // Format jam operasional dengan pilihan "Sampai Selesai"
    if (!empty($_POST['jam_buka'])) {
        $jam_buka = mysqli_real_escape_string($koneksi, $_POST['jam_buka']);
        
        // Cek apakah user memilih "Sampai Selesai"
        if (isset($_POST['sampai_selesai']) && $_POST['sampai_selesai'] == '1') {
            $jam_operasional = "$jam_buka - Sampai Selesai";
        } else if (!empty($_POST['jam_tutup'])) {
            $jam_tutup = mysqli_real_escape_string($koneksi, $_POST['jam_tutup']);
            $jam_operasional = "$jam_buka - $jam_tutup";
        } else {
            $jam_operasional = '';
        }
    } else {
        $jam_operasional = '';
    }
    
    $harga_tiket    = mysqli_real_escape_string($koneksi, $_POST['harga_tiket']);
    $deskripsi      = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
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

    // Insert ke database (dengan field no_hp)
    $sql = $koneksi->query("INSERT INTO tb_event (
        id_event, nama_event, kategori, provinsi, kabupaten, kecamatan, alamat,
        latitude, longitude, tanggal_mulai, tanggal_selesai, jam_operasional,
        harga_tiket, no_hp, deskripsi, gambar, tanggal_upload
    ) VALUES (
        '$id_event', '$nama_event', '$kategori', '$provinsi', '$kabupaten', '$kecamatan', '$alamat',
        '$latitude', '$longitude', '$tanggal_mulai', '$tanggal_selesai', '$jam_operasional',
        '$harga_tiket', '$no_hp', '$deskripsi', '$gambar', '$tanggal_upload'
    )");

    if ($sql) {
        echo "<script>
                alert('Event berhasil ditambahkan!');
                window.location='index.php?page=MyApp/tabel_event';
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
.checkbox-custom {
    margin-top: 10px;
}
.checkbox-custom label {
    font-weight: normal;
    margin-left: 5px;
}
</style>

<section class="content-header">
    <h1 style="text-align:center;">Tambah Event </h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Form Tambah Event</h3>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="box-body">
                <!-- Nama Event -->
                <div class="form-group">
                    <label>Nama Event</label>
                    <input type="text" name="nama_event" class="form-control" required>
                </div>

                <!-- Kategori -->
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option>Festival</option>
                        <option>Budaya</option>
                        <option>Religi</option>
                        <option>Kuliner</option>
                        <option>Olahraga</option>
                        <option>UMKM</option>
                    </select>
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

                <!-- Nomor HP (Field Baru) -->
                <div class="form-group">
                    <label>Nomor HP/WhatsApp <small class="text-muted">(opsional)</small></label>
                    <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 081234567890 atau +6281234567890">
                    <small class="text-muted">Nomor HP/WhatsApp untuk informasi lebih lanjut tentang event ini</small>
                </div>

                <!-- Tanggal Event -->
                <div class="form-group">
                    <label>Tanggal Event</label>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- Jam Operasional -->
                <div class="form-group">
                    <label>Jam Operasional</label>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Jam Buka</label>
                            <input type="time" name="jam_buka" id="jam_buka" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Jam Tutup</label>
                            <input type="time" name="jam_tutup" id="jam_tutup" class="form-control">
                        </div>
                    </div>
                    <div class="checkbox-custom">
                        <input type="checkbox" name="sampai_selesai" id="sampai_selesai" value="1">
                        <label for="sampai_selesai">Sampai Selesai (Event berlangsung tanpa jam tutup tertentu)</label>
                    </div>
                </div>

                <!-- Harga Tiket -->
                <div class="form-group">
                    <label>Harga Tiket</label>
                    <input type="text" name="harga_tiket" class="form-control" placeholder="Contoh: Gratis atau Rp. 50.000" required>
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
                <a href="?page=MyApp/tabel_event" class="btn btn-warning">Batal</a>
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
    
    // Event handler untuk checkbox "Sampai Selesai"
    $('#sampai_selesai').on('change', function() {
        var jamTutup = $('#jam_tutup');
        
        if ($(this).is(':checked')) {
            // Jika checkbox dicentang, disable jam tutup dan kosongkan nilainya
            jamTutup.prop('disabled', true);
            jamTutup.val('');
            jamTutup.prop('required', false);
        } else {
            // Jika checkbox tidak dicentang, enable jam tutup dan set required
            jamTutup.prop('disabled', false);
            jamTutup.prop('required', true);
        }
    });
    
    // Set placeholder untuk select2
    $('#kabupaten').attr('data-placeholder', '-- Pilih Kabupaten --');
    $('#kecamatan').attr('data-placeholder', '-- Pilih Kecamatan --');
    
    // Validasi format nomor HP (opsional)
    $('input[name="no_hp"]').on('input', function() {
        var noHp = $(this).val();
        if (noHp && !noHp.match(/^(\+62|62|0)[0-9]{9,13}$/)) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Format nomor HP tidak valid</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
});
</script>