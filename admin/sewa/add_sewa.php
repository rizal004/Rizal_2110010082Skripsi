<?php
// File: add_motor.php (Form Tambah Motor/Mobil) - KALIMANTAN TENGAH
include "inc/koneksi.php";

if (isset($_POST['save'])) {
    $id_motor      = uniqid('MTR');
    $nama_motor    = mysqli_real_escape_string($koneksi, $_POST['nama_motor']);
    $jenis_kendaraan = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $merk          = mysqli_real_escape_string($koneksi, $_POST['merk']);
    $tahun         = mysqli_real_escape_string($koneksi, $_POST['tahun']);
    $warna         = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $harga_sewa    = mysqli_real_escape_string($koneksi, $_POST['harga_sewa']);
    $provinsi      = mysqli_real_escape_string($koneksi, $_POST['provinsi']);
    $kabupaten     = mysqli_real_escape_string($koneksi, $_POST['kabupaten']);
    $kecamatan     = mysqli_real_escape_string($koneksi, $_POST['kecamatan']);
    $fasilitas     = mysqli_real_escape_string($koneksi, $_POST['fasilitas']);
    $nama_kontak   = mysqli_real_escape_string($koneksi, $_POST['nama_kontak']);
    $no_telepon    = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
    $tanggal_upload= date('Y-m-d H:i:s');

    // Upload gambar - DIPERBAIKI
    $gambarNames = [];
    $uploadDir = "uploads/";
    
    // Pastikan folder uploads ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Cek apakah ada file yang diupload
    if (isset($_FILES['gambar']) && !empty($_FILES['gambar']['name'])) {
        // Jika upload single file (bukan array)
        if (is_string($_FILES['gambar']['name'])) {
            $fileName = $_FILES['gambar']['name'];
            $fileTmp = $_FILES['gambar']['tmp_name'];
            $fileSize = $_FILES['gambar']['size'];
            $fileError = $_FILES['gambar']['error'];
            
            if ($fileError === 0 && $fileSize > 0) {
                // Validasi tipe file
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                if (in_array($fileExt, $allowedTypes)) {
                    // Generate nama file unik
                    $newFileName = uniqid() . '.' . $fileExt;
                    $targetPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmp, $targetPath)) {
                        $gambarNames[] = $newFileName;
                    } else {
                        echo "<script>alert('Gagal mengupload gambar!');</script>";
                    }
                } else {
                    echo "<script>alert('Format file tidak didukung! Gunakan JPG, PNG, atau GIF.');</script>";
                }
            }
        }
        // Jika upload multiple files (array)
        else if (is_array($_FILES['gambar']['name'])) {
            foreach ($_FILES['gambar']['name'] as $i => $fileName) {
                if (!empty($fileName)) {
                    $fileTmp = $_FILES['gambar']['tmp_name'][$i];
                    $fileSize = $_FILES['gambar']['size'][$i];
                    $fileError = $_FILES['gambar']['error'][$i];
                    
                    if ($fileError === 0 && $fileSize > 0) {
                        // Validasi tipe file
                        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        
                        if (in_array($fileExt, $allowedTypes)) {
                            // Generate nama file unik
                            $newFileName = uniqid() . '.' . $fileExt;
                            $targetPath = $uploadDir . $newFileName;
                            
                            if (move_uploaded_file($fileTmp, $targetPath)) {
                                $gambarNames[] = $newFileName;
                            }
                        }
                    }
                }
            }
        }
    }
    
    $gambar = mysqli_real_escape_string($koneksi, implode(',', $gambarNames));

    $sql = $koneksi->query("INSERT INTO tb_motor (
        id_motor, nama_motor, jenis_kendaraan, merk, tahun, warna, harga_sewa,
        provinsi, kabupaten, kecamatan, fasilitas, nama_kontak, no_telepon, gambar, tanggal_upload
    ) VALUES (
        '$id_motor', '$nama_motor', '$jenis_kendaraan', '$merk', '$tahun', '$warna', '$harga_sewa',
        '$provinsi', '$kabupaten', '$kecamatan', '$fasilitas', '$nama_kontak', '$no_telepon', '$gambar', '$tanggal_upload'
    )");

    if ($sql) {
        echo "<script>
                alert('Kendaraan berhasil ditambahkan!');
                window.location='index.php?page=MyApp/tabel_sewa';
              </script>";
    } else {
        echo "<script>alert('Gagal menyimpan data! Error: " . mysqli_error($koneksi) . "');</script>";
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
.contact-section {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.contact-section h4 {
    color: #007bff;
    margin-bottom: 15px;
    font-weight: bold;
}
</style>

<section class="content-header">
    <h1 style="text-align:center;">Tambah Motor & Mobil</h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Form Tambah Kendaraan</h3>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="box-body">
                <!-- Nama Kendaraan -->
                <div class="form-group">
                    <label>Nama Kendaraan</label>
                    <input type="text" name="nama_motor" class="form-control" placeholder="Contoh: Honda Beat, Toyota Avanza" required>
                </div>
                
                <!-- Jenis Kendaraan -->
                <div class="form-group">
                    <label>Jenis Kendaraan</label>
                    <select name="jenis_kendaraan" class="form-control" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="Motor">Motor</option>
                        <option value="Mobil">Mobil</option>
                    </select>
                </div>
                
                <!-- Merk -->
                <div class="form-group">
                    <label>Merk</label>
                    <input type="text" name="merk" class="form-control" placeholder="Contoh: Honda, Toyota, Yamaha" required>
                </div>
                
                <!-- Tahun -->
                <div class="form-group">
                    <label>Tahun</label>
                    <input type="number" name="tahun" class="form-control" placeholder="Contoh: 2020" min="1990" max="2025" required>
                </div>
                
                <!-- Warna -->
                <div class="form-group">
                    <label>Warna</label>
                    <input type="text" name="warna" class="form-control" placeholder="Contoh: Merah, Hitam, Putih" required>
                </div>
                
                <!-- Harga Sewa -->
                <div class="form-group">
                    <label>Harga Sewa per Hari</label>
                    <input type="number" name="harga_sewa" class="form-control" placeholder="Contoh: 50000" required>
                    <small class="text-muted">Masukkan angka tanpa Rp atau tanda titik.</small>
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
                
                <!-- Informasi Kontak -->
                <div class="contact-section">
                    <h4><i class="fa fa-user"></i> Informasi Kontak</h4>
                    
                    <div class="form-group">
                        <label>Nama Kontak</label>
                        <input type="text" name="nama_kontak" class="form-control" placeholder="Contoh: Budi Santoso" required>
                        <small class="text-muted">Nama orang yang dapat dihubungi untuk sewa kendaraan.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Nomor Telepon</label>
                        <input type="text" name="no_telepon" class="form-control" placeholder="Contoh: 0812-3456-7890" required>
                        <small class="text-muted">Nomor WhatsApp/telepon yang aktif.</small>
                    </div>
                    
                </div>
                
                <!-- Fasilitas -->
                <div class="form-group">
                    <label>Fasilitas</label>
                    <textarea name="fasilitas" class="form-control" rows="3" placeholder="Contoh: Helm, Jas Hujan, STNK Lengkap, BBM Penuh"></textarea>
                </div>
                
                <!-- Gambar -->
                <div class="form-group">
                    <label>Gambar Kendaraan</label>
                    <input type="file" name="gambar" class="form-control" accept="image/*" required>
                    <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB. Wajib diisi!</small>
                </div>
            
            </div>
            
            <div class="box-footer">
                <button type="submit" name="save" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan
                </button>
                <a href="?page=MyApp/tabel_sewa" class="btn btn-warning">
                    <i class="fa fa-arrow-left"></i> Batal
                </a>
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
    
    // Validasi nomor telepon
    $('input[name="no_telepon"]').on('input', function() {
        var value = $(this).val();
        // Hapus karakter non-digit dan non-dash
        value = value.replace(/[^0-9\-\+\(\)\s]/g, '');
        $(this).val(value);
    });
});
</script>