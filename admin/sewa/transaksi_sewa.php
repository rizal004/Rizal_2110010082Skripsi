<?php
// inc/koneksi.php
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$ses_id    = isset($_SESSION['ses_id']) ? intval($_SESSION['ses_id']) : 0;
$ses_level = isset($_SESSION['ses_level']) ? strtolower($_SESSION['ses_level']) : '';

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Transaksi Sewa</title>
    
    <style>
        .content-header h1 {
            color: black;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            font-weight: 700;
        }
        .breadcrumb {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .breadcrumb a {
            color: #fff;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            color: #f8f9fa;
        }
        .box {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 1600px;
            overflow: hidden;
        }
        .box-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 20px;
            border-bottom: none;
        }
        .box-header .btn {
            margin-top: 5px;
        }
        .box-body {
            padding: 30px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-weight: 600;
            border: none;
            padding: 15px 10px;
        }
        .table-striped > tbody > tr:nth-child(odd) > td {
            background-color: #f8f9ff;
        }
        .table td {
            padding: 12px 10px;
            vertical-align: middle;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .btn-sm {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 6px;
            font-size: 12px;
        }
        .btn-warning {
            background: #ffc107;
            border: none;
        }
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-1px);
        }
        .btn-success {
            background: #28a745;
            border: none;
        }
        .btn-success:hover {
            background: #218838;
            transform: translateY(-1px);
        }
        .btn-info {
            background: #17a2b8;
            border: none;
        }
        .btn-info:hover {
            background: #138496;
            transform: translateY(-1px);
        }
        .btn-danger {
            background: #dc3545;
            border: none;
        }
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        .btn-default {
            background: #6c757d;
            border: none;
            color: white;
        }
        .btn-default:hover {
            background: #545b62;
            color: white;
            transform: translateY(-1px);
        }
        .btn-complete {
            background: #6c757d;
            border: none;
            color: white;
        }
        .btn-complete:hover {
            background: #545b62;
            color: white;
            transform: translateY(-1px);
        }
        .text-center {
            text-align: center;
        }
        .text-danger {
            color: #dc3545;
        }
        .rating-stars {
            color: #ffc107;
            font-size: 14px;
        }
        .testimoni-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
        }
        .testimoni-cell:hover {
            white-space: normal;
            word-wrap: break-word;
        }
        .rating-badge {
            background: #ffc107;
            color: #212529;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        /* Payment proof styles - updated for text-only display */
        .payment-status {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 5px;
        }
        .payment-available {
            background: #d4edda;
            color: #155724;
        }
        .payment-missing {
            background: #f8d7da;
            color: #721c24;
        }
        .payment-cell {
            text-align: center;
            min-width: 120px;
        }
        
        /* Modal styles */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            border-bottom: none;
        }
        .modal-body {
            padding: 30px;
        }
        .rating-display {
            text-align: center;
            margin-bottom: 20px;
        }
        .rating-display .stars {
            font-size: 24px;
            color: #ffc107;
            margin-bottom: 10px;
        }
        .testimoni-text {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            font-style: italic;
            line-height: 1.6;
        }
        .payment-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .payment-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .box {
                margin: 10px;
            }
            .box-body {
                padding: 15px;
                overflow-x: auto;
            }
            .table {
                font-size: 12px;
            }
            .btn-sm {
                padding: 3px 6px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <section class="content-header">
            <h1><i class="fa fa-list-alt"></i> Data Transaksi Sewa Kendaraan</h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-home"></i> <b>Sistem Sewa</b></a></li>
                <li class="active">Transaksi Sewa</li>
            </ol>
        </section>

        <section class="content">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <?php if ($ses_level === 'admin' || $ses_level === 'administrator'): ?>
                        <a href="?page=MyApp/add_transaksi_sewa" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Tambah Transaksi Sewa
                        </a>
                    <?php endif; ?>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kendaraan</th>
                                    <th>Nama Penyewa</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Lama Sewa</th>
                                    <th>Total Bayar</th>
                                    <th>Alamat Pengantaran</th>
                                    <th>No HP</th>
                                    <th>Status</th>
                                    <th>Bukti Pembayaran</th>
                                    <th>Rating & Testimoni</th>
                                    <th>Kelola</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $sql = ($ses_level === 'administrator' || $ses_level === 'admin')
                                ? $koneksi->query("SELECT t.id_transaksi, t.id_pengguna, m.nama_motor, p.nama_pengguna AS nama_penyewa,
                                                            t.tanggal_mulai, t.tanggal_selesai, t.lama_sewa, t.total_bayar, t.status, t.bukti_pembayaran,
                                                            m.merk, m.jenis_kendaraan, t.tanggal, t.rating, t.testimoni, t.alamat_pengantaran, t.no_hp
                                                    FROM tb_transaksi_sewa t
                                                    LEFT JOIN tb_motor m ON t.id_motor = m.id_motor
                                                    LEFT JOIN tb_pengguna p ON t.id_pengguna = p.id_pengguna
                                                    ORDER BY t.tanggal DESC")
                                : $koneksi->query("SELECT t.id_transaksi, t.id_pengguna, m.nama_motor, p.nama_pengguna AS nama_penyewa,
                                                            t.tanggal_mulai, t.tanggal_selesai, t.lama_sewa, t.total_bayar, t.status, t.bukti_pembayaran,
                                                            m.merk, m.jenis_kendaraan, t.tanggal, t.rating, t.testimoni, t.alamat_pengantaran, t.no_hp
                                                    FROM tb_transaksi_sewa t
                                                    LEFT JOIN tb_motor m ON t.id_motor = m.id_motor
                                                    LEFT JOIN tb_pengguna p ON t.id_pengguna = p.id_pengguna
                                                    WHERE t.id_pengguna = $ses_id
                                                    ORDER BY t.tanggal DESC");

                                if (!$sql) {
                                    echo '<tr><td colspan="12" class="text-center text-danger">SQL Error: ' . htmlspecialchars($koneksi->error) . '</td></tr>';
                                } elseif ($sql->num_rows === 0) {
                                    echo '<tr><td colspan="12" class="text-center">Tidak ada data transaksi sewa</td></tr>';
                                } else {
                                    while ($data = $sql->fetch_assoc()) {
                                        echo '<tr>';
                                        echo '<td>' . $no++ . '</td>';
                                        echo '<td>' . htmlspecialchars($data['nama_motor']) . '<br><small class="text-muted">' . htmlspecialchars($data['merk'] . ' - ' . $data['jenis_kendaraan']) . '</small></td>';
                                        echo '<td>' . htmlspecialchars($data['nama_penyewa']) . '</td>';
                                        echo '<td>' . date('d-m-Y', strtotime($data['tanggal_mulai'])) . '</td>';
                                        echo '<td>' . date('d-m-Y', strtotime($data['tanggal_selesai'])) . '</td>';
                                        echo '<td><span class="badge" style="background:#007bff;color:#fff;">' . htmlspecialchars($data['lama_sewa']) . ' hari</span></td>';
                                        echo '<td><strong>Rp' . number_format($data['total_bayar'],0,',','.') . '</strong></td>';
                                        echo '<td class="alamat-cell">';
                                        if (!empty($data['alamat_pengantaran'])) {
                                            $alamat_preview = strlen($data['alamat_pengantaran']) > 30 ? substr($data['alamat_pengantaran'], 0, 30) . '...' : $data['alamat_pengantaran'];
                                            echo '<div class="contact-info" title="' . htmlspecialchars($data['alamat_pengantaran']) . '">';
                                            echo '<i class="fa fa-map-marker"></i> ' . htmlspecialchars($alamat_preview);
                                            echo '</div>';
                                        } else {
                                            echo '<small class="text-muted">Tidak ada alamat</small>';
                                        }
                                        echo '</td>';

                                        // NO HP COLUMN
                                        echo '<td>';
                                        if (!empty($data['no_hp'])) {
                                            echo '<div class="contact-info">';
                                            echo '<i class="fa fa-phone"></i> ' . htmlspecialchars($data['no_hp']);
                                            echo '</div>';
                                        } else {
                                            echo '<small class="text-muted">Tidak ada no HP</small>';
                                        }
                                        echo '</td>';
                                        // STATUS BADGE
                                        echo '<td>';
                                        if ($data['status'] === 'pending') {
                                            echo '<span class="badge" style="background:#ffc107;color:#212529;"><i class="fa fa-clock"></i> Pending</span>';
                                        } elseif ($data['status'] === 'approved') {
                                            echo '<span class="badge" style="background:#28a745;color:#fff;"><i class="fa fa-check"></i> Approved</span>';
                                        } elseif ($data['status'] === 'cancelled') {
                                            echo '<span class="badge" style="background:#dc3545;color:#fff;"><i class="fa fa-times"></i> Dibatalkan</span>';
                                        } elseif ($data['status'] === 'selesai') {
                                            echo '<span class="badge" style="background:#6c757d;color:#fff;"><i class="fa fa-flag-checkered"></i> Selesai</span>';
                                        } else {
                                            echo '<span class="badge" style="background:#17a2b8;color:#fff;">' . htmlspecialchars($data['status']) . '</span>';
                                        }
                                        echo '</td>';

                                        // BUKTI PEMBAYARAN COLUMN - MODIFIED (No image preview, only detail button)
                                        echo '<td class="payment-cell">';
                                        if (!empty($data['bukti_pembayaran']) && file_exists("uploads/bukti/" . $data['bukti_pembayaran'])) {
                                            echo '<div class="payment-status payment-available">✓ Tersedia</div>';
                                            echo '<br>';
                                            echo '<button class="btn btn-info btn-sm" ';
                                            echo 'onclick="showPaymentProof(\'' . htmlspecialchars($data['id_transaksi'], ENT_QUOTES) . '\', \'';
                                            echo htmlspecialchars($data['nama_penyewa'], ENT_QUOTES) . '\', \'';
                                            echo htmlspecialchars($data['bukti_pembayaran'], ENT_QUOTES) . '\', \'';
                                            echo number_format($data['total_bayar'],0,',','.') . '\')" title="Lihat Bukti Pembayaran">';
                                            echo '<i class="fa fa-eye"></i> Lihat Detail';
                                            echo '</button>';
                                        } else {
                                            echo '<div class="payment-status payment-missing">✗ Tidak Ada</div>';
                                            echo '<br>';
                                            echo '<small class="text-muted">Bukti belum diupload</small>';
                                        }
                                        echo '</td>';

                                        // RATING & TESTIMONI COLUMN
                                        echo '<td>';
                                        if ($data['status'] === 'selesai' && ($data['rating'] > 0 || !empty($data['testimoni']))) {
                                            // Display rating
                                            if ($data['rating'] > 0) {
                                                echo '<div class="rating-display" style="margin-bottom: 8px;">';
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $data['rating']) {
                                                        echo '<i class="fa fa-star" style="color: #ffc107;"></i>';
                                                    } else {
                                                        echo '<i class="fa fa-star" style="color: #ddd;"></i>';
                                                    }
                                                }
                                                echo '<br><small class="rating-badge">' . $data['rating'] . '/5</small>';
                                                echo '</div>';
                                            }
                                            
                                            // Display testimoni preview
                                            if (!empty($data['testimoni'])) {
                                                $testimoni_preview = strlen($data['testimoni']) > 30 ? substr($data['testimoni'], 0, 30) . '...' : $data['testimoni'];
                                                echo '<div class="testimoni-preview" style="font-size: 11px; color: #6c757d; font-style: italic;">';
                                                echo '"' . htmlspecialchars($testimoni_preview) . '"';
                                                echo '</div>';
                                                
                                                // Button to view full testimoni
                                                echo '<button class="btn btn-link btn-sm" style="padding: 2px 5px; font-size: 10px;" onclick="showTestimoni(\'' . 
                                                     htmlspecialchars($data['id_transaksi'], ENT_QUOTES) . '\', \'' . 
                                                     htmlspecialchars($data['nama_penyewa'], ENT_QUOTES) . '\', ' . 
                                                     $data['rating'] . ', \'' . 
                                                     htmlspecialchars($data['testimoni'], ENT_QUOTES) . '\')">';
                                                echo '<i class="fa fa-eye"></i> Lihat Detail';
                                                echo '</button>';
                                            }
                                        } else {
                                            echo '<small class="text-muted">Belum ada rating</small>';
                                        }
                                        echo '</td>';

                                        // KOLOM KELOLA
                                        echo '<td>';

                                        // Edit & Batal: hanya pengguna, status pending, milik sendiri
                                        if (
                                            $ses_level === 'pengguna'
                                            && $data['status'] === 'pending'
                                            && intval($data['id_pengguna']) === $ses_id
                                        ) {
                                            echo '<a href="?page=MyApp/edit_transaksi_sewa&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-warning btn-sm" title="Edit Transaksi">'
                                                . '<i class="fa fa-edit"></i></a> ';
                                            echo '<a href="?page=MyApp/batal_transaksi_sewa&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-default btn-sm" title="Batalkan Transaksi" '
                                                . 'onclick="return confirm(\'Batalkan transaksi sewa ini?\');">'
                                                . '<i class="fa fa-times"></i></a> ';
                                        }

                                        // Approve: admin, status pending
                                        if ($data['status'] === 'pending' && ($ses_level === 'administrator' || $ses_level === 'admin')) {
                                            echo '<a href="?page=MyApp/approve_transaksi_sewa&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-success btn-sm" title="Approve Transaksi" '
                                                . 'onclick="return confirm(\'Approve transaksi sewa ini?\');">'
                                                . '<i class="fa fa-check"></i></a> ';
                                        }

                                        // Selesaikan Sewa: admin, status approved
                                        if ($data['status'] === 'approved' && ($ses_level === 'administrator' || $ses_level === 'admin')) {
                                            echo '<a href="?page=MyApp/selesai_transaksi_sewa&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-complete btn-sm" title="Selesaikan Sewa" '
                                                . 'onclick="return confirm(\'Tandai sewa ini sebagai selesai?\');">'
                                                . '<i class="fa fa-flag-checkered"></i></a> ';
                                        }

                                        // Selesaikan Sewa: pengguna, status approved, milik sendiri
                                        if (
                                            $ses_level === 'pengguna'
                                            && $data['status'] === 'approved'
                                            && intval($data['id_pengguna']) === $ses_id
                                        ) {
                                            echo '<a href="?page=MyApp/selesai_transaksi_sewa&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-complete btn-sm" title="Selesaikan Sewa" '
                                                . 'onclick="return confirm(\'Tandai sewa ini sebagai selesai? Pastikan kendaraan sudah dikembalikan.\');">'
                                                . '<i class="fa fa-flag-checkered"></i> Selesai</a> ';
                                        }

                                        // Cetak Kontrak: approved atau selesai
                                        if (
                                            ($data['status'] === 'approved' || $data['status'] === 'selesai')
                                            && (
                                                ($ses_level === 'administrator' || $ses_level === 'admin') ||
                                                ($ses_level === 'pengguna' && intval($data['id_pengguna']) === $ses_id)
                                            )
                                        ) {
                                            echo '<a href="?page=MyApp/cetak_transaksi_sewa&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-info btn-sm" title="Cetak Kontrak Sewa" >'
                                                . '<i class="fa fa-print"></i></a> ';
                                        }

                                        // Hapus: hanya admin/administrator
                                        if ($ses_level === 'administrator' || $ses_level === 'admin') {
                                            echo '<a href="?page=MyApp/del_transaksi_sewa&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-danger btn-sm" title="Hapus Transaksi" '
                                                . 'onclick="return confirm(\'Yakin hapus transaksi sewa ini?\');">'
                                                . '<i class="fa fa-trash"></i></a> ';
                                        }

                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal untuk menampilkan testimoni lengkap -->
    <div class="modal fade" id="testimoniModal" tabindex="-1" role="dialog" aria-labelledby="testimoniModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="testimoniModalLabel">
                        <i class="fa fa-star"></i> Rating & Testimoni
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="rating-display">
                        <h5 id="modal-customer-name"></h5>
                        <div id="modal-transaksi-id" style="font-size: 12px; color: #6c757d; margin-bottom: 15px;"></div>
                        <div class="stars" id="modal-rating-stars"></div>
                        <div id="modal-rating-text" style="font-size: 14px; color: #6c757d;"></div>
                    </div>
                    <div class="testimoni-text" id="modal-testimoni-text"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk menampilkan bukti pembayaran -->
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="paymentModalLabel">
                        <i class="fa fa-receipt"></i> Bukti Pembayaran
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="payment-info">
                        <h5 id="payment-customer-name"></h5>
                        <div id="payment-transaksi-id" style="font-size: 12px; color: #6c757d; margin-bottom: 10px;"></div>
                        <div id="payment-total" style="font-size: 16px; font-weight: bold; color: #28a745;"></div>
                    </div>
                    <div class="text-center">
                        <img id="payment-image" src="" alt="Bukti Pembayaran" class="payment-image">
                    </div>
                    <div style="margin-top: 15px;">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> 
                            Klik pada gambar untuk memperbesar tampilan
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    
    <script>
        $(document).ready(function() {
            $('#example1').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                },
                "responsive": true,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                "order": [[ 0, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": [11] }
                ]
            });

            // Auto refresh setiap 30 detik untuk update status
            setTimeout(function() {
                location.reload();
            }, 30000);
        });

        // Function untuk menampilkan modal testimoni
        function showTestimoni(idTransaksi, namaPenyewa, rating, testimoni) {
            $('#modal-customer-name').text(namaPenyewa);
            $('#modal-transaksi-id').text('ID Transaksi: ' + idTransaksi);
            
            // Generate stars
            var starsHtml = '';
            var ratingText = '';
            for (var i = 1; i <= 5; i++) {
                if (i <= rating) {
                    starsHtml += '<i class="fa fa-star" style="color: #ffc107;"></i>';
                } else {
                    starsHtml += '<i class="fa fa-star" style="color: #ddd;"></i>';
                }
            }
            
            // Rating text
            switch(rating) {
                case 1: ratingText = 'Sangat Tidak Puas'; break;
                case 2: ratingText = 'Tidak Puas'; break;
                case 3: ratingText = 'Cukup Puas'; break;
                case 4: ratingText = 'Puas'; break;
                case 5: ratingText = 'Sangat Puas'; break;
                default: ratingText = '';
            }
            
            $('#modal-rating-stars').html(starsHtml);
            $('#modal-rating-text').text(rating + '/5 - ' + ratingText);
            $('#modal-testimoni-text').text(testimoni);
            
            $('#testimoniModal').modal('show');
        }

        // Function untuk menampilkan modal bukti pembayaran
        function showPaymentProof(idTransaksi, namaPenyewa, buktiFile, totalBayar) {
            $('#payment-customer-name').text(namaPenyewa);
            $('#payment-transaksi-id').text('ID Transaksi: ' + idTransaksi);
            $('#payment-total').text('Total Pembayaran: Rp' + totalBayar);
            $('#payment-image').attr('src', 'uploads/bukti/' + buktiFile);
            $('#payment-image').attr('alt', 'Bukti Pembayaran - ' + idTransaksi);
            
            
            $('#paymentModal').modal('show');
        }


        // Function untuk memperbesar gambar bukti pembayaran
        $(document).on('click', '#payment-image', function() {
            var src = $(this).attr('src');
            var modal = '<div class="modal fade" id="imageModal" tabindex="-1" role="dialog">' +
                       '<div class="modal-dialog modal-lg" role="document">' +
                       '<div class="modal-content">' +
                       '<div class="modal-header">' +
                       '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
                       '<span aria-hidden="true">&times;</span>' +
                       '</button>' +
                       '<h4 class="modal-title">Bukti Pembayaran - Tampilan Penuh</h4>' +
                       '</div>' +
                       '<div class="modal-body text-center">' +
                       '<img src="' + src + '" style="max-width: 100%; height: auto;" alt="Bukti Pembayaran">' +
                       '</div>' +
                       '<div class="modal-footer">' +
                       '<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>' +
                       '</div>' +
                       '</div>' +
                       '</div>' +
                       '</div>';
            
            $('body').append(modal);
            $('#imageModal').modal('show');
            
            // Remove modal after it's hidden
            $('#imageModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        });

        // Function untuk format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }

        // Function untuk validasi file upload (jika diperlukan di halaman lain)
        function validateImageFile(input) {
            if (input.files && input.files[0]) {
                var file = input.files[0];
                var fileSize = file.size / 1024 / 1024; // Convert to MB
                var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                
                if (!allowedTypes.includes(file.type)) {
                    alert('Hanya file gambar (JPEG, JPG, PNG, GIF) yang diperbolehkan!');
                    input.value = '';
                    return false;
                }
                
                if (fileSize > 5) {
                    alert('Ukuran file tidak boleh lebih dari 5MB!');
                    input.value = '';
                    return false;
                }
                
                return true;
            }
            return false;
        }

        // Function untuk preview image sebelum upload
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#' + previewId).attr('src', e.target.result);
                    $('#' + previewId).show();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });

        // Confirm before form submission for critical actions
        function confirmAction(message, form) {
            if (confirm(message)) {
                form.submit();
                return true;
            }
            return false;
        }

        // Print functionality
        function printContract(transactionId) {
            var printWindow = window.open('?page=MyApp/cetak_transaksi_sewa&id=' + transactionId, '_blank');
            printWindow.onload = function() {
                printWindow.print();
            };
        }

        // Update status indicators with animation
        function updateStatusBadge(element, newStatus) {
            $(element).fadeOut(300, function() {
                var badgeClass = '';
                var icon = '';
                var text = '';
                
                switch(newStatus) {
                    case 'pending':
                        badgeClass = 'badge';
                        $(element).css({'background': '#ffc107', 'color': '#212529'});
                        icon = '<i class="fa fa-clock"></i>';
                        text = 'Pending';
                        break;
                    case 'approved':
                        badgeClass = 'badge';
                        $(element).css({'background': '#28a745', 'color': '#fff'});
                        icon = '<i class="fa fa-check"></i>';
                        text = 'Approved';
                        break;
                    case 'cancelled':
                        badgeClass = 'badge';
                        $(element).css({'background': '#dc3545', 'color': '#fff'});
                        icon = '<i class="fa fa-times"></i>';
                        text = 'Dibatalkan';
                        break;
                    case 'selesai':
                        badgeClass = 'badge';
                        $(element).css({'background': '#6c757d', 'color': '#fff'});
                        icon = '<i class="fa fa-flag-checkered"></i>';
                        text = 'Selesai';
                        break;
                }
                
                $(element).html(icon + ' ' + text);
                $(element).fadeIn(300);
            });
        }

        // Export table to Excel (optional feature)
        function exportToExcel() {
            var table = document.getElementById('example1');
            var wb = XLSX.utils.table_to_book(table, {sheet: "Transaksi Sewa"});
            var filename = 'Data_Transaksi_Sewa_' + new Date().toISOString().slice(0,10) + '.xlsx';
            XLSX.writeFile(wb, filename);
        }

        // Search and filter functionality enhancement
        function quickSearch(searchTerm) {
            var table = $('#example1').DataTable();
            table.search(searchTerm).draw();
        }

        // Mobile responsive table scroll
        $(window).resize(function() {
            if ($(window).width() < 768) {
                $('.table-responsive').css('overflow-x', 'auto');
            }
        });

    </script>
</body>
</html>