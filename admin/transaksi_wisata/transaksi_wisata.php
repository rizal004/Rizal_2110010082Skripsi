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
    <title>Data Transaksi Wisata</title>
    
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
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
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
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1565c0 0%, #1e88e5 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,136,229,0.3);
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .table thead th {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            color: #fff;
            font-weight: 600;
            border: none;
            padding: 15px 10px;
        }
        .table-striped > tbody > tr:nth-child(odd) > td {
            background-color: #f0f8ff;
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
        /* Style khusus untuk tombol reject */
        .btn-reject {
            background: #ff6b35;
            border: none;
            color: white;
        }
        .btn-reject:hover {
            background: #e55a30;
            color: white;
            transform: translateY(-1px);
        }
        .text-center {
            text-align: center;
        }
        .text-danger {
            color: #dc3545;
        }
        .ticket-badge {
            background: #1e88e5;
            color: #fff;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        /* Bukti Pembayaran Link Styles */
        .bukti-link {
            color: #1e88e5;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border: 1px solid #1e88e5;
            border-radius: 20px;
            background: rgba(30, 136, 229, 0.1);
            transition: all 0.3s;
            display: inline-block;
        }
        .bukti-link:hover {
            background: #1e88e5;
            color: #fff;
            text-decoration: none;
            transform: scale(1.05);
        }
        .bukti-link i {
            margin-right: 5px;
        }
        .no-bukti {
            color: #6c757d;
            font-style: italic;
            font-size: 12px;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            max-height: 80%;
            border-radius: 10px;
            position: relative;
        }
        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            z-index: 1001;
            line-height: 1;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .close:hover,
        .close:focus {
            color: #fff;
            background: rgba(255,255,255,0.2);
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
            .bukti-link {
                font-size: 10px;
                padding: 4px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <section class="content-header">
            <h1><i class="fa fa-map-marker"></i> Data Tiket</h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-home"></i> <b>Si Wisata</b></a></li>
                <li class="active">Transaksi Wisata</li>
            </ol>
        </section>

        <section class="content">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <?php if ($ses_level === 'admin' || $ses_level === 'administrator'): ?>
                        <a href="?page=MyApp/add_transaksi" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Tambah Transaksi Wisata
                        </a>
                    <?php endif; ?>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>ID Transaksi</th>
                                    <th>Nama Wisata</th>
                                    <th>Nama Pemesan</th>
                                    <th>Jumlah Tiket</th>
                                    <th>Total Bayar</th>
                                    <th>Bukti Pembayaran</th>
                                    <th>Status</th>
                                    <th>Kelola</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $sql = ($ses_level === 'administrator' || $ses_level === 'admin')
                                    ? $koneksi->query("SELECT t.id_transaksi, t.id_pengguna, w.nama_wisata, p.nama_pengguna AS nama_pemesan,
                                                                t.tanggal, t.jumlah_tiket, t.total_bayar, t.status, t.bukti_pembayaran
                                                         FROM tb_transaksi t
                                                         LEFT JOIN tb_wisata w ON t.id_wisata = w.id_wisata
                                                         LEFT JOIN tb_pengguna p ON t.id_pengguna = p.id_pengguna
                                                         ORDER BY t.tanggal DESC")
                                    : $koneksi->query("SELECT t.id_transaksi, t.id_pengguna, w.nama_wisata, p.nama_pengguna AS nama_pemesan,
                                                                t.tanggal, t.jumlah_tiket, t.total_bayar, t.status, t.bukti_pembayaran
                                                         FROM tb_transaksi t
                                                         LEFT JOIN tb_wisata w ON t.id_wisata = w.id_wisata
                                                         LEFT JOIN tb_pengguna p ON t.id_pengguna = p.id_pengguna
                                                         WHERE t.id_pengguna = $ses_id
                                                         ORDER BY t.tanggal DESC");

                                if (!$sql) {
                                    echo '<tr><td colspan="10" class="text-center text-danger">SQL Error: ' . htmlspecialchars($koneksi->error) . '</td></tr>';
                                } elseif ($sql->num_rows === 0) {
                                    echo '<tr><td colspan="10" class="text-center">Tidak ada data transaksi wisata</td></tr>';
                                } else {
                                    while ($data = $sql->fetch_assoc()) {
                                        echo '<tr>';
                                        echo '<td>' . $no++ . '</td>';
                                        echo '<td><strong>' . htmlspecialchars($data['id_transaksi']) . '</strong></td>';
                                        echo '<td>' . htmlspecialchars($data['nama_wisata']) . '</td>';
                                        echo '<td>' . htmlspecialchars($data['nama_pemesan']) . '</td>';
                            
                                        echo '<td><span class="ticket-badge">' . htmlspecialchars($data['jumlah_tiket']) . ' tiket</span></td>';
                                        echo '<td><strong>Rp' . number_format($data['total_bayar'],0,',','.') . '</strong></td>';
                                        
                                        // BUKTI PEMBAYARAN COLUMN
                                        echo '<td class="text-center">';
                                        if (!empty($data['bukti_pembayaran']) && file_exists("uploads/bukti/" . $data['bukti_pembayaran'])) {
                                            echo '<a href="javascript:void(0)" 
                                                      class="bukti-link" 
                                                      onclick="showBukti(\'' . htmlspecialchars($data['bukti_pembayaran']) . '\')" 
                                                      title="Klik untuk melihat bukti pembayaran">
                                                      <i class="fa fa-eye"></i>Lihat Bukti
                                                  </a>';
                                        } else {
                                            echo '<span class="no-bukti">Tidak ada bukti</span>';
                                        }
                                        echo '</td>';
                                        
                                        // STATUS BADGE - Ditambahkan status ditolak
                                        echo '<td>';
                                        if ($data['status'] === 'pending') {
                                            echo '<span class="badge" style="background:#ffc107;color:#212529;"><i class="fa fa-clock-o"></i> Pending</span>';
                                        } elseif ($data['status'] === 'approved') {
                                            echo '<span class="badge" style="background:#28a745;color:#fff;"><i class="fa fa-check"></i> Approved</span>';
                                        } elseif ($data['status'] === 'cancelled') {
                                            echo '<span class="badge" style="background:#dc3545;color:#fff;"><i class="fa fa-times"></i> Dibatalkan</span>';
                                        } elseif ($data['status'] === 'ditolak' || $data['status'] === 'rejected') {
                                            echo '<span class="badge" style="background:#ff6b35;color:#fff;"><i class="fa fa-ban"></i> Ditolak</span>';
                                        } elseif ($data['status'] === 'completed') {
                                            echo '<span class="badge" style="background:#17a2b8;color:#fff;"><i class="fa fa-flag-checkered"></i> Selesai</span>';
                                        } else {
                                            echo '<span class="badge" style="background:#6c757d;color:#fff;">' . htmlspecialchars($data['status']) . '</span>';
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
                                            echo '<a href="?page=MyApp/edit_transaksi&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-warning btn-sm" title="Edit Transaksi">'
                                                . '<i class="fa fa-edit"></i></a> ';
                                            echo '<a href="?page=MyApp/batal_transaksi&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-default btn-sm" title="Batalkan" '
                                                . 'onclick="return confirm(\'Batalkan pemesanan wisata ini?\');">'
                                                . '<i class="fa fa-times"></i></a> ';
                                        }

                                        // Approve & Reject: admin, status pending
                                        if ($data['status'] === 'pending' && ($ses_level === 'administrator' || $ses_level === 'admin')) {
                                            echo '<a href="?page=MyApp/approve_transaksi&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-success btn-sm" title="Approve Transaksi" '
                                                . 'onclick="return confirm(\'Approve transaksi wisata ini?\');">'
                                                . '<i class="fa fa-check"></i></a> ';
                                            
                                            echo '<a href="?page=MyApp/reject_transaksi&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-reject btn-sm" title="Tolak Transaksi" '
                                                . 'onclick="return confirm(\'Yakin ingin menolak transaksi wisata ini?\');">'
                                                . '<i class="fa fa-ban"></i></a> ';
                                        }

                                        // Cetak Tiket: approved
                                        if (
                                            $data['status'] === 'approved'
                                            && (
                                                ($ses_level === 'administrator' || $ses_level === 'admin') ||
                                                ($ses_level === 'pengguna' && intval($data['id_pengguna']) === $ses_id)
                                            )
                                        ) {
                                            echo '<a href="?page=MyApp/cetak_tiket&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-info btn-sm" title="Cetak Tiket Wisata">'
                                                . '<i class="fa fa-print"></i></a> ';
                                        }

                                        // Hapus: hanya admin/administrator
                                        if ($ses_level === 'administrator' || $ses_level === 'admin') {
                                            echo '<a href="?page=MyApp/del_transaksi&id=' . urlencode($data['id_transaksi']) . '" '
                                                . 'class="btn btn-danger btn-sm" title="Hapus Transaksi" '
                                                . 'onclick="return confirm(\'Yakin hapus transaksi wisata ini?\');">'
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

    <!-- Modal untuk menampilkan bukti pembayaran -->
    <div id="buktiModal" class="modal" onclick="closeBukti()">
        <span class="close" onclick="closeBukti()" title="Tutup">&times;</span>
        <img class="modal-content" id="buktiImage" onclick="event.stopPropagation()">
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
                    { "orderable": false, "targets": [7, 8] }, // Bukti pembayaran dan kelola tidak bisa di-sort
                    { "width": "120px", "targets": [6] } // Set width untuk kolom bukti pembayaran
                ]
            });

            // Auto refresh setiap 30 detik untuk update status
            setTimeout(function() {
                location.reload();
            }, 30000);
        });

        // Function untuk menampilkan bukti pembayaran dalam modal
        function showBukti(filename) {
            const modal = document.getElementById("buktiModal");
            const modalImg = document.getElementById("buktiImage");
            
            modal.style.display = "flex";
            modalImg.src = "uploads/bukti/" + filename;
            
            // Tambahkan event listener untuk ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeBukti();
                }
            });
        }

        // Function untuk menutup modal
        function closeBukti() {
            const modal = document.getElementById("buktiModal");
            modal.style.display = "none";
            
            // Hapus event listener ESC
            document.removeEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeBukti();
                }
            });
        }

        // Tutup modal ketika klik di background (bukan gambar)
        document.getElementById("buktiModal").addEventListener('click', function(e) {
            if (e.target === this) {
                closeBukti();
            }
        });

        // Konfirmasi sebelum aksi
        function confirmAction(message, url) {
            if (confirm(message)) {
                window.location.href = url;
            }
        }
    </script>
</body>
</html>