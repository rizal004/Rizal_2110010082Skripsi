<?php
include "inc/koneksi.php";
?>
<style>
body {
    background: linear-gradient(120deg, #28a745 0%, #20c997 100%) !important;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.content-header {
    background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
    padding: 18px 12px 8px 12px;
    border-radius: 0 0 20px 20px;
    margin-bottom: 18px;
    box-shadow: 0 4px 16px rgba(40,167,69,0.08);
    color: #fff;
}
.content-header h1 { color: #fff !important; text-shadow:1px 2px 8px rgba(40,167,69,0.3); }
.breadcrumb {
    background: rgba(255,255,255,0.33);
    border-radius: 10px;
    padding: 8px 18px;
}
.box.box-primary, .box.box-info {
    border-top: 4px solid #28a745 !important;
    border-radius: 12px;
    box-shadow: 0 4px 20px 0 rgba(40,167,69,0.06);
    background: rgba(255,255,255,0.97);
}
.box-header.with-border {
    background: #a8e6c1;
    border-radius: 12px 12px 0 0;
    color: #155724;
}
.btn-info, .btn-primary, .btn-success {
    border-radius: 20px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(40,167,69,0.10);
    letter-spacing: 1px;
    transition: background 0.18s;
}
.btn-info { background: linear-gradient(90deg, #17a2b8 0%, #138496 100%); border: none; color: #fff;}
.btn-primary { background: linear-gradient(90deg, #28a745 0%, #20c997 100%); border: none; color: #fff;}
.btn-success { background: linear-gradient(90deg, #28a745 0%, #20c997 100%); border: none; color: #fff;}
.btn-info:hover, .btn-primary:hover, .btn-success:hover { filter: brightness(1.12); }
.table-responsive { border-radius: 16px; box-shadow: 0 4px 18px rgba(40,167,69,0.09); background: #fff;}
.table-bordered>thead>tr { background: linear-gradient(90deg, #28a745 10%, #20c997 100%); color: #fff; font-size: 15px;}
.table-bordered>tbody>tr:nth-child(even) { background: #f8fff8;}
.table-bordered>tbody>tr:nth-child(odd) { background: #fff;}
.table>tbody>tr>td, .table>thead>tr>th { vertical-align: middle;}
@media (max-width: 600px) {
    .content-header h1 { font-size: 1.4rem;}
    .box.box-primary, .box.box-info { border-radius: 7px; }
    .table-responsive { border-radius: 7px;}
}
.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}
.status-pending { background: #ffeaa7; color: #6c5ce7; }
.status-approved { background: #74b9ff; color: #0984e3; }
.status-selesai { background: #55a3ff; color: #2d3436; }
.status-ditolak { background: #fd79a8; color: #e84393; }
</style>

<section class="content-header">
    <h1 style="text-align:center; color:#fff !important;">Cetak Data Transaksi Sewa</h1>
    <ol class="breadcrumb">
        <li>
            <a href="index.php" style="color:black;">
                <i class="fa fa-home"></i> <b>Si Kuliner</b>
            </a>
        </li>
        <li>
            <a href="?page=MyApp/transaksi_sewa" style="color:black;">Transaksi Sewa</a>
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
            <input type="hidden" name="page" value="MyApp/cetak_sewa">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                <option value="">-- Pilih Tahun --</option>
                                <?php
                                $tahun_query = $koneksi->query("SELECT DISTINCT YEAR(tanggal) as tahun FROM tb_transaksi_sewa ORDER BY tahun DESC");
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
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">-- Pilih Status --</option>
                                <?php
                                $status_options = ['pending', 'approved', 'selesai', 'ditolak'];
                                foreach($status_options as $status) {
                                    $selected = (isset($_GET['status']) && $_GET['status'] == $status) ? 'selected' : '';
                                    echo "<option value='$status' $selected>" . ucfirst($status) . "</option>";
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
        <div class="box-header with-border" style="background:linear-gradient(90deg, #28a745 0%, #20c997 100%);color:#fff;">
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
                    <h2 style="color:#222;"><b>LAPORAN DATA TRANSAKSI SEWA</b></h2>
                    <h4 style="color:#28a745;">Si Kuliner</h4>
                    <?php
                    $bulan_array = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    if(isset($_GET['cetak_semua'])) {
                        echo "<p><b>Semua Data Transaksi Sewa</b></p>";
                    } else {
                        $filter_text = [];
                        if(!empty($_GET['tahun'])) $filter_text[] = "Tahun: ".$_GET['tahun'];
                        if(!empty($_GET['bulan'])) $filter_text[] = "Bulan: ".$bulan_array[$_GET['bulan']];
                        if(!empty($_GET['status'])) $filter_text[] = "Status: ".ucfirst($_GET['status']);
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
                                <th>ID Transaksi</th>
                                <th>Nama Penyewa</th>
                                <th>Kendaraan</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Lama Sewa</th>
                                <th>Total Bayar</th>
                                <th>Status</th>
                                <th>Tanggal Transaksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $total_pendapatan = 0;
                            $where_conditions = [];
                            if(isset($_GET['filter'])) {
                                if(!empty($_GET['tahun'])) {
                                    $where_conditions[] = "YEAR(ts.tanggal) = '".$_GET['tahun']."'";
                                }
                                if(!empty($_GET['bulan'])) {
                                    $where_conditions[] = "MONTH(ts.tanggal) = '".$_GET['bulan']."'";
                                }
                                if(!empty($_GET['status'])) {
                                    $where_conditions[] = "ts.status = '".$koneksi->real_escape_string($_GET['status'])."'";
                                }
                            }
                            $where_clause = '';
                            if(!empty($where_conditions)) {
                                $where_clause = "WHERE " . implode(' AND ', $where_conditions);
                            }
                            $sql = $koneksi->query("SELECT ts.*, tp.nama_pengguna, tm.nama_motor, tm.jenis_kendaraan, tm.merk 
                                                   FROM tb_transaksi_sewa ts 
                                                   LEFT JOIN tb_pengguna tp ON ts.id_pengguna = tp.id_pengguna 
                                                   LEFT JOIN tb_motor tm ON ts.id_motor = tm.id_motor 
                                                   $where_clause 
                                                   ORDER BY ts.tanggal DESC");
                            if($sql->num_rows > 0) {
                                while ($data = $sql->fetch_assoc()) {
                                    if($data['status'] == 'selesai' || $data['status'] == 'approved') {
                                        $total_pendapatan += $data['total_bayar'];
                                    }
                            ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($data['id_transaksi']); ?></td>
                                <td><?= htmlspecialchars($data['nama_pengguna']); ?></td>
                                <td>
                                    <?= htmlspecialchars($data['nama_motor']); ?><br>
                                    <small><?= htmlspecialchars($data['jenis_kendaraan'] . ' - ' . $data['merk']); ?></small>
                                </td>
                                <td><?= date('d-m-Y', strtotime($data['tanggal_mulai'])); ?></td>
                                <td><?= date('d-m-Y', strtotime($data['tanggal_selesai'])); ?></td>
                                <td style="text-align: center;"><?= $data['lama_sewa']; ?> hari</td>
                                <td>Rp <?= number_format($data['total_bayar'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="status-badge status-<?= $data['status']; ?>">
                                        <?= ucfirst($data['status']); ?>
                                    </span>
                                </td>
                                <td><?= date('d-m-Y H:i', strtotime($data['tanggal'])); ?></td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='10' class='text-center'>Tidak ada data yang ditemukan</td></tr>";
                            }
                            ?>
                        </tbody>
                        <?php if($total_pendapatan > 0): ?>
                        <tfoot>
                            <tr style="background: #e8f5e8; font-weight: bold;">
                                <td colspan="7" style="text-align: right;">Total Pendapatan:</td>
                                <td colspan="3">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
                
                <?php if($total_pendapatan > 0): ?>
                <div style="margin-top: 20px; padding: 15px; background: #e8f5e8; border-radius: 8px;">
                    <h4 style="color: #28a745; margin: 0;">
                        <i class="fa fa-chart-line"></i> Ringkasan Laporan
                    </h4>
                    <p style="margin: 10px 0 0 0;">
                        Total Pendapatan dari Transaksi Selesai/Approved: 
                        <strong style="color: #28a745;">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></strong>
                    </p>
                </div>
                <?php endif; ?>
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
    .box, .box-info, .box.box-primary { box-shadow: none !important; border-radius:0 !important; }
    .status-badge { 
        padding: 2px 6px; 
        border: 1px solid #000; 
        border-radius: 4px; 
        background: #f0f0f0 !important; 
        color: #000 !important; 
    }
    tfoot tr { background: #f0f0f0 !important; }
</style>