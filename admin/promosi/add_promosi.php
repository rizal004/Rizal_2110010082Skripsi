<?php
include "inc/koneksi.php";

if (isset($_POST['save'])) {
    $judul_promosi  = mysqli_real_escape_string($koneksi, $_POST['judul_promosi']);
    
    // Handle jenis promosi - bisa dari dropdown atau input baru
    if ($_POST['jenis_promosi'] == 'other' && !empty($_POST['jenis_promosi_baru'])) {
        $jenis_promosi = mysqli_real_escape_string($koneksi, $_POST['jenis_promosi_baru']);
    } else {
        $jenis_promosi = mysqli_real_escape_string($koneksi, $_POST['jenis_promosi']);
    }
    
    $deskripsi      = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $lokasi         = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $tanggal_mulai  = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
    $tanggal_selesai= mysqli_real_escape_string($koneksi, $_POST['tanggal_selesai']);
    $harga          = mysqli_real_escape_string($koneksi, $_POST['harga']);
    $kontak         = mysqli_real_escape_string($koneksi, $_POST['kontak']);
    $tanggal_upload = date('Y-m-d H:i:s');

    // Upload gambar
    $gambarNames = [];
    if (!empty($_FILES['gambar']['name'][0])) {
        foreach ($_FILES['gambar']['tmp_name'] as $i => $tmp) {
            $name = date('YmdHis') . '_' . basename($_FILES['gambar']['name'][$i]);
            if (move_uploaded_file($tmp, "uploads/promosi/" . $name)) {
                $gambarNames[] = $name;
            }
        }
    }
    $gambar = mysqli_real_escape_string($koneksi, implode(',', $gambarNames));

    $sql = $koneksi->query("INSERT INTO tb_promosi (
        judul_promosi, jenis_promosi, deskripsi, lokasi, tanggal_mulai, tanggal_selesai, harga, kontak, gambar, tanggal_upload
    ) VALUES (
        '$judul_promosi', '$jenis_promosi', '$deskripsi', '$lokasi', '$tanggal_mulai', '$tanggal_selesai', '$harga', '$kontak', '$gambar', '$tanggal_upload'
    )");

    if ($sql) {
        echo "<script>alert('Promosi berhasil ditambahkan!');window.location='?page=MyApp/data_promosi';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan data!');</script>";
    }
}

// Ambil jenis promosi yang sudah ada dari database
$jenis_promosi_query = $koneksi->query("SELECT DISTINCT jenis_promosi FROM tb_promosi WHERE jenis_promosi != '' ORDER BY jenis_promosi ASC");
$existing_jenis = [];
while ($row = $jenis_promosi_query->fetch_assoc()) {
    $existing_jenis[] = $row['jenis_promosi'];
}
?>

<section class="content-header">
    <h1 style="text-align:center;">Tambah Promosi</h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Form Tambah Promosi</h3>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="box-body">
                <div class="form-group">
                    <label>Nama Promosi</label>
                    <input type="text" name="judul_promosi" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Jenis Promosi</label>
                    <select name="jenis_promosi" id="jenis_promosi" class="form-control" required onchange="toggleJenisPromosiInput()">
                        <option value="">-- Pilih Jenis Promosi --</option>
                        <?php 
                        // Tampilkan jenis promosi yang sudah ada dari database
                        foreach ($existing_jenis as $jenis) {
                            echo "<option value='".htmlspecialchars($jenis)."'>".htmlspecialchars($jenis)."</option>";
                        }
                        
                        // Tambahkan opsi default jika belum ada di database
                        $default_jenis = ['Pembuatan Baliho', 'Pemasangan Baliho', 'Sewa Konten Kreator'];
                        foreach ($default_jenis as $jenis) {
                            if (!in_array($jenis, $existing_jenis)) {
                                echo "<option value='".htmlspecialchars($jenis)."'>".htmlspecialchars($jenis)."</option>";
                            }
                        }
                        ?>
                        <option value="other">+ Tambah Jenis Promosi Baru</option>
                    </select>
                </div>
                <div class="form-group" id="jenis_promosi_baru_group" style="display: none;">
                    <label>Jenis Promosi Baru</label>
                    <input type="text" name="jenis_promosi_baru" id="jenis_promosi_baru" class="form-control" placeholder="Masukkan jenis promosi baru">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label>Lokasi</label>
                    <input type="text" name="lokasi" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Promosi</label>
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
                <div class="form-group">
                    <label>Harga</label>
                    <input type="number" name="harga" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Kontak</label>
                    <input type="text" name="kontak" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Bukti Promosi (boleh lebih dari satu)</label>
                    <input type="file" name="gambar[]" class="form-control" accept="image/*" multiple>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" name="save" class="btn btn-primary">Simpan</button>
                <a href="?page=MyApp/data_promosi" class="btn btn-warning">Batal</a>
            </div>
        </form>
    </div>
</section>

<script>
function toggleJenisPromosiInput() {
    var select = document.getElementById('jenis_promosi');
    var inputGroup = document.getElementById('jenis_promosi_baru_group');
    var input = document.getElementById('jenis_promosi_baru');
    
    if (select.value === 'other') {
        inputGroup.style.display = 'block';
        input.required = true;
    } else {
        inputGroup.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}
</script>