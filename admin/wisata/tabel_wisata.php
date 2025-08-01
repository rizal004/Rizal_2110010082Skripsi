<?php
// inc/koneksi.php
include "inc/koneksi.php";
?>

<section class="content-header">
    <h1 style="text-align:center;">Data Wisata</h1>
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
        <div class="box-header with-border">
            <a href="?page=MyApp/add_wisata" class="btn btn-primary">
                <i class="glyphicon glyphicon-plus"></i> Tambah Data
            </a>
            <a href="?page=MyApp/data_wisata" class="btn btn-warning" style="margin-left:7px;">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kategori</th>
                            <th>Nama Wisata</th>
                            <th>Lokasi</th>
                            <th>Harga Tiket</th>
                            <th>Estimasi Biaya</th>
                            <th>Jam Operasional</th>
                            <th>Gambar</th>
                            <th>Tanggal Upload</th>
                            <th>Kelola</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $sql = $koneksi->query("SELECT * FROM tb_wisata ORDER BY tanggal_upload DESC");
                        while ($data = $sql->fetch_assoc()) {
                            // Cek apakah ada gambar
                            $gambar = $data['gambar'];
                            $linkGambar = '';
                            if (!empty($gambar)) {
                                $linkGambar = '<button type="button" class="btn btn-info btn-xs" 
                                               onclick="showWisataImages(\'' . $data['id_wisata'] . '\', \'' . htmlspecialchars($data['nama_wisata']) . '\', \'' . $gambar . '\')">
                                               <i class="glyphicon glyphicon-picture"></i> Lihat Gambar
                                               </button>';
                            } else {
                                $linkGambar = '<span class="text-muted">Tidak ada gambar</span>';
                            }
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($data['kategori']); ?></td>
                            <td><?= htmlspecialchars($data['nama_wisata']); ?></td>
                            <td>
                                <?= htmlspecialchars(str_replace('negara, ', '', $data['alamat'])); ?>,
                                <?= htmlspecialchars($data['kecamatan']); ?>,
                                <?= htmlspecialchars($data['kabupaten']); ?>
                            </td>
                            <td>
                                <?= is_numeric($data['harga_tiket']) && $data['harga_tiket'] > 0 
                                    ? 'Rp' . number_format($data['harga_tiket'], 0, ',', '.') 
                                    : htmlspecialchars($data['harga_tiket']) ?>
                            </td>
                            <td>
                                <?= !empty($data['estimasi_biaya']) ? htmlspecialchars($data['estimasi_biaya']) : '<span style="color:#bbb;">-</span>'; ?>
                            </td>
                            <td><?= htmlspecialchars($data['jam_operasional']); ?></td>
                            <td><?= $linkGambar; ?></td>
                            <td><?= date('d-m-Y H:i', strtotime($data['tanggal_upload'])); ?></td>
                            <td>
                                <a href="?page=MyApp/edit_wisata&id=<?= htmlspecialchars($data['id_wisata']); ?>"
                                   class="btn btn-success btn-sm" title="Ubah">
                                    <i class="glyphicon glyphicon-edit"></i>
                                </a>
                                <a href="?page=MyApp/del_wisata&id=<?= htmlspecialchars($data['id_wisata']); ?>"
                                   onclick="return confirm('Yakin hapus data ini?')"
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

<!-- Modal untuk melihat gambar wisata -->
<div class="modal fade" id="wisataImageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="wisataModalTitle">Gambar Wisata</h4>
            </div>
            <div class="modal-body">
                <div id="wisataImageGallery" class="row"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk melihat gambar full size -->
<div class="modal fade" id="wisataFullImageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Preview Gambar</h4>
            </div>
            <div class="modal-body text-center">
                <img id="wisataFullImage" src="" class="img-responsive" style="max-width: 100%; height: auto;">
            </div>
        </div>
    </div>
</div>

<script>
function showWisataImages(wisataId, wisataName, gambarString) {
    // Set judul modal
    document.getElementById('wisataModalTitle').textContent = 'Gambar Wisata: ' + wisataName;
    
    // Parse gambar
    var gambarArray = gambarString.split(',');
    var gallery = document.getElementById('wisataImageGallery');
    gallery.innerHTML = '';
    
    if (gambarString.trim() === '') {
        gallery.innerHTML = '<div class="col-md-12"><div class="alert alert-info text-center">Tidak ada gambar untuk wisata ini.</div></div>';
    } else {
        gambarArray.forEach(function(gambar, index) {
            if (gambar.trim() !== '') {
                var col = document.createElement('div');
                col.className = 'col-md-4 col-sm-6 col-xs-12';
                col.style.marginBottom = '15px';
                
                col.innerHTML = `
                    <div class="thumbnail" style="cursor: pointer; transition: transform 0.2s;" 
                         onmouseover="this.style.transform='scale(1.05)'"
                         onmouseout="this.style.transform='scale(1)'"
                         onclick="showWisataFullImage('${gambar.trim()}')">
                        <img src="uploads/${gambar.trim()}" 
                             alt="Gambar Wisata" 
                             class="img-responsive" 
                             style="width: 100%; height: 150px; object-fit: cover; border-radius: 4px;"
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2VlZSIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1zaXplPSIxMiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iIGZpbGw9IiM5OTkiPkdhbWJhciBUaWRhayBEaXRlbXVrYW48L3RleHQ+PC9zdmc+'; this.parentNode.innerHTML='<div class=\'alert alert-warning text-center\'><small>Gambar tidak dapat dimuat</small></div>'">
                        <div class="caption">
                            <p class="text-center"><small>Gambar ${index + 1}</small></p>
                        </div>
                    </div>
                `;
                
                gallery.appendChild(col);
            }
        });
    }
    
    // Tampilkan modal
    $('#wisataImageModal').modal('show');
}

function showWisataFullImage(imageName) {
    document.getElementById('wisataFullImage').src = 'uploads/' + imageName;
    $('#wisataFullImageModal').modal('show');
}
</script>

<style>
.thumbnail:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

#wisataImageModal .modal-dialog {
    width: 90%;
    max-width: 1000px;
}

@media (max-width: 768px) {
    #wisataImageModal .modal-dialog {
        width: 95%;
        margin: 10px auto;
    }
}
</style>