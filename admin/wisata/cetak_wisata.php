<?php
include "inc/koneksi.php";
?>
<style>
body {
    background: linear-gradient(120deg, #2272ff 0%, #66aaff 100%) !important;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.content-header {
    background: linear-gradient(90deg, #2dd6c1 0%, #1e90ff 100%);
    padding: 18px 12px 8px 12px;
    border-radius: 0 0 20px 20px;
    margin-bottom: 18px;
    box-shadow: 0 4px 16px rgba(30,144,255,0.08);
    color: #fff;
}
.content-header h1 { color: #222 !important; text-shadow:1px 2px 8px #1292df30; }
.breadcrumb {
    background: rgba(255,255,255,0.33);
    border-radius: 10px;
    padding: 8px 18px;
}
.box.box-primary, .box.box-info {
    border-top: 4px solid #1e90ff !important;
    border-radius: 12px;
    box-shadow: 0 4px 20px 0 rgba(30,144,255,0.06);
    background: rgba(255,255,255,0.97);
}
.box-header.with-border {
    background: #eaf4ff;
    border-radius: 12px 12px 0 0;
    color: #1363c6;
}
.btn-info, .btn-primary, .btn-success {
    border-radius: 20px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(30,144,255,0.10);
    letter-spacing: 1px;
    transition: background 0.18s;
}
.btn-info { background: linear-gradient(90deg, #2dd6c1 0%, #1e90ff 100%); border: none; color: #fff;}
.btn-primary { background: linear-gradient(90deg, #2272ff 0%, #66aaff 100%); border: none; color: #fff;}
.btn-success { background: linear-gradient(90deg, #50b08f 0%, #2dd6c1 100%); border: none; color: #fff;}
.btn-info:hover, .btn-primary:hover, .btn-success:hover { filter: brightness(1.12); }
.table-responsive { border-radius: 16px; box-shadow: 0 4px 18px rgba(30,144,255,0.09); background: #fff;}
.table-bordered>thead>tr { background: linear-gradient(90deg, #2dd6c1 10%, #1e90ff 100%); color: #fff; font-size: 15px;}
.table-bordered>tbody>tr:nth-child(even) { background: #eaf4ff;}
.table-bordered>tbody>tr:nth-child(odd) { background: #fff;}
.table>tbody>tr>td, .table>thead>tr>th { vertical-align: middle;}
@media (max-width: 600px) {
    .content-header h1 { font-size: 1.4rem;}
    .box.box-primary, .box.box-info { border-radius: 7px; }
    .table-responsive { border-radius: 7px;}
}
</style>

<section class="content-header">
    <h1 style="text-align:center; color:#222 !important;">Laporan Data Wisata</h1>
    <ol class="breadcrumb">
        <li>
            <a href="index.php" style="color:black;">
                <i class="fa fa-home"></i> <b>Si Wisata</b>
            </a>
        </li>
        <li>
            <a href="?page=MyApp/data_wisata" style="color:black;">Data Wisata</a>
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
            <input type="hidden" name="page" value="MyApp/cetak_wisata">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                <option value="">-- Pilih Tahun --</option>
                                <?php
                                $tahun_query = $koneksi->query("SELECT DISTINCT YEAR(tanggal_upload) as tahun FROM tb_wisata ORDER BY tahun DESC");
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
                            <label>Kategori</label>
                            <select name="kategori" class="form-control">
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                $kategori_query = $koneksi->query("SELECT DISTINCT kategori FROM tb_wisata ORDER BY kategori ASC");
                                while($kategori_data = $kategori_query->fetch_assoc()) {
                                    $selected = (isset($_GET['kategori']) && $_GET['kategori'] == $kategori_data['kategori']) ? 'selected' : '';
                                    echo "<option value='{$kategori_data['kategori']}' $selected>{$kategori_data['kategori']}</option>";
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
        <div class="box-header with-border" style="background:linear-gradient(90deg, #2dd6c1 0%, #1e90ff 100%);color:#fff;">
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
                    <h2 style="color:#222;"><b>LAPORAN DATA WISATA</b></h2>
                    <h4 style="color:#1e90ff;">Si Wisata</h4>
                    <?php
                    $bulan_array = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    if(isset($_GET['cetak_semua'])) {
                        echo "<p><b>Semua Data Wisata</b></p>";
                    } else {
                        $filter_text = [];
                        if(!empty($_GET['tahun'])) $filter_text[] = "Tahun: ".$_GET['tahun'];
                        if(!empty($_GET['bulan'])) $filter_text[] = "Bulan: ".$bulan_array[$_GET['bulan']];
                        if(!empty($_GET['kategori'])) $filter_text[] = "Kategori: ".$_GET['kategori'];
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
                                <th>Kategori</th>
                                <th>Nama Wisata</th>
                                <th>Lokasi</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Harga Tiket</th>
                                <th>Makanan</th>
                                <th>Minuman</th>
                                <th>Sewa</th>
                                <th>Estimasi Biaya</th>
                                <th>Jam Operasional</th>
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
                                if(!empty($_GET['kategori'])) {
                                    $where_conditions[] = "kategori = '".$koneksi->real_escape_string($_GET['kategori'])."'";
                                }
                            }
                            $where_clause = '';
                            if(!empty($where_conditions)) {
                                $where_clause = "WHERE " . implode(' AND ', $where_conditions);
                            }
                            $sql = $koneksi->query("SELECT * FROM tb_wisata $where_clause ORDER BY tanggal_upload DESC");
                            if($sql->num_rows > 0) {
                                while ($data = $sql->fetch_assoc()) {
                            ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($data['kategori']); ?></td>
                                <td><?= htmlspecialchars($data['nama_wisata']); ?></td>
                                <td>
                                    <?= htmlspecialchars(str_replace('negara, ', '', $data['alamat'])); ?>,
                                    <?= htmlspecialchars($data['kecamatan']); ?>,
                                    <?= htmlspecialchars($data['kabupaten']); ?>
                                </td>
                                <td><?= htmlspecialchars($data['latitude']); ?></td>
                                <td><?= htmlspecialchars($data['longitude']); ?></td>
                                <td>
                                <?= is_numeric($data['harga_tiket']) && $data['harga_tiket'] > 0 
                                    ? 'Rp' . number_format($data['harga_tiket'], 0, ',', '.') 
                                    : htmlspecialchars($data['harga_tiket']) ?>
                            </td>
                                <td>
                                    <?php
                                    if ($data['harga_makanan_min'] && $data['harga_makanan_max']) {
                                        echo 'Rp'.number_format($data['harga_makanan_min'],0,',','.').' - Rp'.number_format($data['harga_makanan_max'],0,',','.');
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($data['harga_minuman_min'] && $data['harga_minuman_max']) {
                                        echo 'Rp'.number_format($data['harga_minuman_min'],0,',','.').' - Rp'.number_format($data['harga_minuman_max'],0,',','.');
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?= !empty($data['biaya_sewa']) ? htmlspecialchars($data['biaya_sewa']) : '-'; ?></td>
                                <td><?= !empty($data['estimasi_biaya']) ? htmlspecialchars($data['estimasi_biaya']) : '-'; ?></td>
                                <td><?= htmlspecialchars($data['jam_operasional']); ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($data['tanggal_upload'])); ?></td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='13' class='text-center'>Tidak ada data yang ditemukan</td></tr>";
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
