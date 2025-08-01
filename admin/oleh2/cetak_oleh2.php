<?php
include "inc/koneksi.php";
?>
<style>
body {
    background: linear-gradient(120deg, #ff6b35 0%, #f7931e 100%) !important;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.content-header {
    background: linear-gradient(90deg, #e74c3c 0%, #ff6b35 100%);
    padding: 18px 12px 8px 12px;
    border-radius: 0 0 20px 20px;
    margin-bottom: 18px;
    box-shadow: 0 4px 16px rgba(231,76,60,0.08);
    color: #fff;
}
.content-header h1 { color: #fff !important; text-shadow:1px 2px 8px rgba(231,76,60,0.3); }
.breadcrumb {
    background: rgba(255,255,255,0.33);
    border-radius: 10px;
    padding: 8px 18px;
}
.box.box-primary, .box.box-info {
    border-top: 4px solid #e74c3c !important;
    border-radius: 12px;
    box-shadow: 0 4px 20px 0 rgba(231,76,60,0.06);
    background: rgba(255,255,255,0.97);
}
.box-header.with-border {
    background: #ffeaa7;
    border-radius: 12px 12px 0 0;
    color: #d63031;
}
.btn-info, .btn-primary, .btn-success {
    border-radius: 20px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(231,76,60,0.10);
    letter-spacing: 1px;
    transition: background 0.18s;
}
.btn-info { background: linear-gradient(90deg, #fd79a8 0%, #e84393 100%); border: none; color: #fff;}
.btn-primary { background: linear-gradient(90deg, #e74c3c 0%, #ff6b35 100%); border: none; color: #fff;}
.btn-success { background: linear-gradient(90deg, #00b894 0%, #00cec9 100%); border: none; color: #fff;}
.btn-info:hover, .btn-primary:hover, .btn-success:hover { filter: brightness(1.12); }
.table-responsive { border-radius: 16px; box-shadow: 0 4px 18px rgba(231,76,60,0.09); background: #fff;}
.table-bordered>thead>tr { background: linear-gradient(90deg, #e74c3c 10%, #ff6b35 100%); color: #fff; font-size: 15px;}
.table-bordered>tbody>tr:nth-child(even) { background: #fff5f5;}
.table-bordered>tbody>tr:nth-child(odd) { background: #fff;}
.table>tbody>tr>td, .table>thead>tr>th { vertical-align: middle;}
@media (max-width: 600px) {
    .content-header h1 { font-size: 1.4rem;}
    .box.box-primary, .box.box-info { border-radius: 7px; }
    .table-responsive { border-radius: 7px;}
}
</style>

<section class="content-header">
    <h1 style="text-align:center; color:#fff !important;">Laporan Data Oleh-oleh</h1>
    <ol class="breadcrumb">
        <li>
            <a href="index.php" style="color:black;">
                <i class="fa fa-home"></i> <b>Si Wisata</b>
            </a>
        </li>
        <li>
            <a href="?page=MyApp/tabel_oleh2" style="color:black;">Data Oleh-oleh</a>
        </li>
        <li class="active" style="color:black;">Cetak Data</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Filter Data Cetak</h3>
        </div>
        <form method="GET" action="">
            <input type="hidden" name="page" value="MyApp/cetak_oleh2">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                <option value="">-- Pilih Tahun --</option>
                                <?php
                                $tahun_query = $koneksi->query("SELECT DISTINCT YEAR(tanggal_upload) as tahun FROM tb_oleh2 ORDER BY tahun DESC");
                                while($tahun_data = $tahun_query->fetch_assoc()) {
                                    $selected = (isset($_GET['tahun']) && $_GET['tahun'] == $tahun_data['tahun']) ? 'selected' : '';
                                    echo "<option value='{$tahun_data['tahun']}' $selected>{$tahun_data['tahun']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Bulan</label>
                            <select name="bulan" class="form-control">
                                <option value="">-- Pilih Bulan --</option>
                                <?php
                                $bulan_array = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                                foreach($bulan_array as $key => $bulan) {
                                    $selected = (isset($_GET['bulan']) && $_GET['bulan'] == $key) ? 'selected' : '';
                                    echo "<option value='$key' $selected>$bulan</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Kabupaten</label>
                            <select name="kabupaten" class="form-control">
                                <option value="">-- Pilih Kabupaten --</option>
                                <?php
                                $kabupaten_query = $koneksi->query("SELECT DISTINCT kabupaten FROM tb_oleh2 ORDER BY kabupaten ASC");
                                while($kabupaten_data = $kabupaten_query->fetch_assoc()) {
                                    $selected = (isset($_GET['kabupaten']) && $_GET['kabupaten'] == $kabupaten_data['kabupaten']) ? 'selected' : '';
                                    echo "<option value='{$kabupaten_data['kabupaten']}' $selected>{$kabupaten_data['kabupaten']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label><br>
                            <button type="submit" name="filter" class="btn btn-info">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                            <button type="submit" name="cetak_semua" class="btn btn-success">
                                <i class="fa fa-print"></i> Cetak Semua
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if(isset($_GET['filter']) || isset($_GET['cetak_semua'])): ?>
    <div class="box box-info">
        <div class="box-header with-border" style="background:linear-gradient(90deg, #e74c3c 0%, #ff6b35 100%);color:#fff;">
            <h3 class="box-title">Hasil Filter</h3>
            <div class="box-tools pull-right">
                <button onclick="printData()" class="btn btn-primary">
                    <i class="fa fa-print"></i> Cetak
                </button>
            </div>
        </div>
        <div class="box-body">
            <div id="printArea">
                <div class="text-center" style="margin-bottom: 20px;">
                    <h2 style="color:#222;"><b>Laporan Data Oleh-Oleh</b></h2>
                    <h4 style="color:#e74c3c;">Si Wisata</h4>
                    <?php
                    $bulan_array = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    if(isset($_GET['cetak_semua'])) {
                        echo "<p><b>Semua Data Oleh-oleh</b></p>";
                    } else {
                        $filter_text = [];
                        if(!empty($_GET['tahun'])) $filter_text[] = "Tahun: ".$_GET['tahun'];
                        if(!empty($_GET['bulan'])) $filter_text[] = "Bulan: ".$bulan_array[$_GET['bulan']];
                        if(!empty($_GET['kabupaten'])) $filter_text[] = "Kabupaten: ".$_GET['kabupaten'];
                        echo "<p><b>" . implode(" | ", $filter_text) . "</b></p>";
                    }
                    ?>
                    <hr>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th style="text-align:center;">No</th>
                                <th>Nama Toko</th>
                                <th>Lokasi</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Harga Range</th>
                                <th>Jam Operasional</th>
                                <th>Barang Dijual</th>
                                <th>Deskripsi</th>
                                <th>Tanggal Upload</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $where_conditions = [];
                            if(isset($_GET['filter'])) {
                                if(!empty($_GET['tahun'])) {
                                    $where_conditions[] = "YEAR(tanggal_upload) = '".$_GET['tahun']."'";
                                }
                                if(!empty($_GET['bulan'])) {
                                    $where_conditions[] = "MONTH(tanggal_upload) = '".$_GET['bulan']."'";
                                }
                                if(!empty($_GET['kabupaten'])) {
                                    $where_conditions[] = "kabupaten = '".$koneksi->real_escape_string($_GET['kabupaten'])."'";
                                }
                            }
                            $where_clause = '';
                            if(!empty($where_conditions)) {
                                $where_clause = "WHERE " . implode(' AND ', $where_conditions);
                            }
                            $sql = $koneksi->query("SELECT * FROM tb_oleh2 $where_clause ORDER BY tanggal_upload DESC");
                            if($sql->num_rows > 0) {
                                while ($data = $sql->fetch_assoc()) {
                            ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($data['nama_toko']); ?></td>
                                <td>
                                    <?= htmlspecialchars($data['alamat']); ?>,
                                    <?= htmlspecialchars($data['kecamatan']); ?>,
                                    <?= htmlspecialchars($data['kabupaten']); ?>
                                </td>
                                <td><?= htmlspecialchars($data['latitude']); ?></td>
                                <td><?= htmlspecialchars($data['longitude']); ?></td>
                                <td><?= htmlspecialchars($data['harga_range']); ?></td>
                                <td><?= htmlspecialchars($data['jam_operasional']); ?></td>
                                <td><?= htmlspecialchars($data['barang_dijual']); ?></td>
                                <td>
                                    <?php 
                                    $deskripsi = htmlspecialchars($data['deskripsi']);
                                    echo strlen($deskripsi) > 100 ? substr($deskripsi, 0, 100) . '...' : $deskripsi;
                                    ?>
                                </td>
                                <td><?= date('d-m-Y H:i', strtotime($data['tanggal_upload'])); ?></td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='10' class='text-center'>Tidak ada data yang ditemukan</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<script>
function printData() {
    var printContents = document.getElementById('printArea').innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script>
<style media="print">
    @page {
        size: A4 landscape;
        margin: 1cm;
    }
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        line-height: 1.3;
        background: #fff !important;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #000;
        padding: 4px;
        text-align: left;
    }
    th {
        background-color: #f0f0f0 !important;
        font-weight: bold;
    }
    .text-center { text-align: center; }
    hr { border: 1px solid #000; }
    .btn, .box-header, .breadcrumb, .box-title { display: none !important; }
    .box, .box-info, .box-primary { box-shadow: none !important; border-radius:0 !important; }
</style>