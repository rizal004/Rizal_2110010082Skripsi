<?php 
// inc/koneksi.php 
include "inc/koneksi.php"; 
?>  

<section class="content-header">     
    <h1 style="text-align:center;">Data Kritik & Saran Hotel</h1>     
    <ol class="breadcrumb">         
        <li>             
            <a href="index.php">                 
                <i class="fa fa-home"></i>                 
                <b>Si Wisata</b>             
            </a>         
        </li>     
    </ol> 
</section>  

<section class="content">     
    <div class="box box-primary">         
        <div class="box-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Kategori Kritik dan Saran</label>                     
                        <select name="kategori" id="kategori" class="form-control" onchange="redirectToCategory(this.value)">                         
                            <option value="">-- Pilih Kategori --</option>                         
                            <option value="wisata">Kritik dan Saran Wisata</option>                         
                            <option value="kuliner">Kritik dan Saran Kuliner</option>                         
                            <option value="oleh2">Kritik dan Saran Oleh-oleh</option>                         
                            <option value="event">Kritik dan Saran Event</option>                         
                            <option value="hotel" selected>Kritik dan Saran Hotel</option>                     
                        </select>                 
                    </div>
                </div>
            </div>
        </div>         
        <div class="box-body">             
            <div class="table-responsive">                 
                <table id="example1" class="table table-bordered table-striped">                     
                    <thead>                         
                        <tr>                             
                            <th>No</th>                             
                            <th>Nama Hotel</th>                             
                            <th>Nama Pengguna</th>                             
                            <th>Rating</th>                             
                            <th>Komentar</th>                             
                            <th>Tanggal</th>                             
                            <th>Kelola</th>                         
                        </tr>                     
                    </thead>                     
                    <tbody>                         
                        <?php                         
                        $no = 1;                         
                        $sql = $koneksi->query("SELECT 
                            ks.id_kritik_saran_hotel,
                            ks.id_hotel,
                            ks.id_pengguna,
                            ks.rating,
                            ks.komentar,
                            ks.tanggal,
                            h.nama_hotel,
                            p.nama_pengguna
                            FROM tb_kritik_saran_hotel ks
                            LEFT JOIN tb_hotel h ON ks.id_hotel = h.id_hotel
                            LEFT JOIN tb_pengguna p ON ks.id_pengguna = p.id_pengguna
                            WHERE ks.id_hotel IS NOT NULL
                            ORDER BY ks.tanggal DESC");                         
                        while ($data = $sql->fetch_assoc()) {
                            // Format rating dengan bintang
                            $rating = intval($data['rating']);
                            $stars = '';
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    $stars .= '<i class="fa fa-star text-warning"></i>';
                                } else {
                                    $stars .= '<i class="fa fa-star-o text-muted"></i>';
                                }
                            }
                            $stars .= ' (' . $rating . '/5)';
                            
                            // Batasi komentar jika terlalu panjang
                            $komentar = htmlspecialchars($data['komentar']);
                            $komentar_pendek = strlen($komentar) > 100 ? substr($komentar, 0, 100) . '...' : $komentar;
                        ?>                         
                        <tr>                             
                            <td><?= $no++; ?></td>                             
                            <td><?= htmlspecialchars($data['nama_hotel'] ?? 'Hotel Tidak Ditemukan'); ?></td>                             
                            <td><?= htmlspecialchars($data['nama_pengguna'] ?? 'Pengguna Tidak Ditemukan'); ?></td>                             
                            <td><?= $stars; ?></td>                             
                            <td>
                                <span class="komentar-pendek"><?= $komentar_pendek; ?></span>
                                <?php if (strlen($komentar) > 100) { ?>
                                    <br><button type="button" class="btn btn-xs btn-info" 
                                            onclick="showFullComment('<?= str_replace(["'", '"'], ["&#39;", "&quot;"], $komentar); ?>', '<?= str_replace(["'", '"'], ["&#39;", "&quot;"], $data['nama_pengguna'] ?? 'Pengguna Tidak Ditemukan'); ?>')">
                                        <i class="fa fa-eye"></i> Lihat Selengkapnya
                                    </button>
                                <?php } ?>
                            </td>                             
                            <td><?= date('d-m-Y H:i', strtotime($data['tanggal'])); ?></td>                             
                            <td>                                 
                                <a href="?page=MyApp/edit_kritik_hotel&id=<?= htmlspecialchars($data['id_kritik_saran_hotel']); ?>"                                    
                                   class="btn btn-success btn-sm" title="Ubah">                                     
                                    <i class="glyphicon glyphicon-edit"></i>                                 
                                </a>                                 
                                <a href="?page=MyApp/del_kritik_hotel&id=<?= htmlspecialchars($data['id_kritik_saran_hotel']); ?>"                                    
                                   onclick="return confirm('Yakin hapus kritik dan saran ini?')"                                    
                                   class="btn btn-danger btn-sm" title="Hapus">                                     
                                    <i class="glyphicon glyphicon-trash"></i>                                 
                                </a>                             
                            </td>                         
                        </tr>                         
                        <?php } ?>                     
                    </tbody>                 
                </table>             
            </div>         
        </div>     
    </div> 
</section>

<!-- Modal untuk melihat komentar lengkap -->
<div class="modal fade" id="commentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="commentModalTitle">Komentar Lengkap</h4>
            </div>
            <div class="modal-body">
                <div id="fullCommentText" style="line-height: 1.6; padding: 15px; background-color: #f9f9f9; border-radius: 5px; border-left: 4px solid #3c8dbc;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function showFullComment(fullComment, userName) {
    try {
        document.getElementById('commentModalTitle').textContent = 'Komentar dari: ' + userName;
        document.getElementById('fullCommentText').innerHTML = fullComment.replace(/\n/g, '<br>');
        $('#commentModal').modal('show');
    } catch (error) {
        console.error('Error showing comment:', error);
        alert('Terjadi kesalahan saat menampilkan komentar');
    }
}

// Inisialisasi DataTable
$(document).ready(function() {
    $('#example1').DataTable({
        "responsive": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        },
        "order": [[ 5, "desc" ]], // Urutkan berdasarkan tanggal terbaru
        "pageLength": 25,
        "columnDefs": [
            { "orderable": false, "targets": [6] }, // Kolom Kelola tidak bisa diurutkan
            { "width": "5%", "targets": [0] }, // No
            { "width": "20%", "targets": [1] }, // Nama Hotel
            { "width": "15%", "targets": [2] }, // Nama Pengguna
            { "width": "10%", "targets": [3] }, // Rating
            { "width": "30%", "targets": [4] }, // Komentar
            { "width": "12%", "targets": [5] }, // Tanggal
            { "width": "8%", "targets": [6] }   // Kelola
        ]
    });
});

// Fungsi untuk redirect ke kategori yang dipilih
function redirectToCategory(kategori) {
    if (kategori === '' || kategori === null) {
        return;
    }
    
    console.log('Redirecting to category:', kategori); // Debug log
    
    var pages = {
        'wisata': '?page=MyApp/data_kritik_wisata',
        'kuliner': '?page=MyApp/data_kritik_kuliner',
        'oleh2': '?page=MyApp/data_kritik_oleh2',
        'event': '?page=MyApp/data_kritik_event',
        'hotel': '?page=MyApp/data_kritik_hotel'
    };
    
    if (pages[kategori]) {
        console.log('Redirecting to:', pages[kategori]); // Debug log
        window.location.href = pages[kategori];
    } else {
        console.error('Invalid category:', kategori); // Debug log
        alert('Kategori tidak valid: ' + kategori);
    }
}

// Debug function untuk memastikan event listener bekerja
function testRedirect() {
    console.log('Test redirect function called');
    var select = document.getElementById('kategori');
    if (select) {
        console.log('Select element found, current value:', select.value);
        redirectToCategory(select.value);
    } else {
        console.error('Select element not found');
    }
}

// Pastikan DOM sudah loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - Hotel Page');
    var select = document.getElementById('kategori');
    if (select) {
        console.log('Select element found on DOM load');
        // Tambahkan event listener sebagai backup
        select.addEventListener('change', function() {
            console.log('Change event triggered:', this.value);
            redirectToCategory(this.value);
        });
    } else {
        console.error('Select element not found on DOM load');
    }
});
</script>

<style>
.fa-star, .fa-star-o {
    font-size: 14px;
}

.komentar-pendek {
    display: inline-block;
    max-width: 100%;
    word-wrap: break-word;
}

#commentModal .modal-dialog {
    width: 80%;
    max-width: 800px;
}

@media (max-width: 768px) {
    #commentModal .modal-dialog {
        width: 95%;
        margin: 10px auto;
    }
    
    .table-responsive {
        font-size: 12px;
    }
    
    .btn-sm {
        padding: 2px 6px;
        font-size: 11px;
    }
}

/* Styling untuk rating stars */
.text-warning {
    color: #f39c12 !important;
}

.text-muted {
    color: #777 !important;
}

/* Hover effect untuk baris tabel */
.table tbody tr:hover {
    background-color: #f5f5f5;
}

/* Highlight untuk halaman hotel */
.content-header h1 {
    color: #8e44ad;
}

.box-primary {
    border-top-color: #8e44ad;
}
</style>