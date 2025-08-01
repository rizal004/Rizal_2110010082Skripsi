<?php 
// inc/koneksi.php 
include "inc/koneksi.php"; 
?>  

<section class="content-header">     
    <h1 style="text-align:center;">Data Kuliner</h1>     
    <ol class="breadcrumb">         
        <li>             
            <a href="index.php">                 
                <i class="fa fa-home"></i>                 
                <b>Si Kuliner</b>             
            </a>         
        </li>     
    </ol> 
</section>  

<section class="content">     
    <div class="box box-primary">         
        <div class="box-header with-border">             
            <a href="?page=MyApp/add_kuliner" class="btn btn-primary">                 
                <i class="glyphicon glyphicon-plus"></i> Tambah Data             
            </a>             
            <a href="?page=MyApp/data_kuliner" class="btn btn-warning" style="margin-left:7px;">                 
                <i class="fa fa-arrow-left"></i> Kembali             
            </a>         
        </div>         
        <div class="box-body">             
            <div class="table-responsive">                 
                <table id="example1" class="table table-bordered table-striped">                     
                    <thead>                         
                        <tr>                             
                            <th>No</th>                             
                            <th>Nama Kuliner</th>                             
                            <th>Lokasi</th>                             
                            <th>Menu</th>                             
                            <th>Spesial Menu</th>                             
                            <th>Harga Range</th>                             
                            <th>Jam Operasional</th>
                            <th>Gambar</th>                             
                            <th>Tanggal Upload</th>                             
                            <th>Kelola</th>                         
                        </tr>                     
                    </thead>                     
                    <tbody>                         
                        <?php                         
                        $no = 1;                         
                        $sql = $koneksi->query("SELECT * FROM tb_kuliner ORDER BY tanggal_upload DESC");                         
                        while ($data = $sql->fetch_assoc()) {
                            // Format lokasi
                            $lokasi = htmlspecialchars("{$data['alamat']}, {$data['kecamatan']}, {$data['kabupaten']}, {$data['provinsi']}");
                            
                            // Format menu & special
                            $menu = nl2br(htmlspecialchars(str_replace(',', ', ', $data['menu'])));
                            $special = htmlspecialchars($data['special_menu']);
                            
                            // Format harga_range
                            $parts = explode(' - ', $data['harga_range']);
                            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                                $hargaText = 'Rp'.number_format($parts[0],0,',','.'). ' – Rp'.number_format($parts[1],0,',','.');
                            } else {
                                $hargaText = htmlspecialchars($data['harga_range']);
                            }
                            
                            // Cek apakah ada gambar
                            $gambar = $data['gambar'];
                            $linkGambar = '';
                            if (!empty($gambar)) {
                                $linkGambar = '<button type="button" class="btn btn-info btn-xs" 
                                               onclick="showKulinerImages(\'' . $data['id_kuliner'] . '\', \'' . htmlspecialchars($data['nama_kuliner']) . '\', \'' . $gambar . '\')">
                                               <i class="glyphicon glyphicon-picture"></i> Lihat Gambar
                                               </button>';
                            } else {
                                $linkGambar = '<span class="text-muted">Tidak ada gambar</span>';
                            }
                            
                            // Tanggal
                            $tgl = date('d-m-Y H:i', strtotime($data['tanggal_upload']));
                        ?>                         
                        <tr>                             
                            <td><?= $no++; ?></td>                             
                            <td><?= htmlspecialchars($data['nama_kuliner']); ?></td>                             
                            <td><?= $lokasi; ?></td>                             
                            <td><?= $menu; ?></td>                             
                            <td><?= $special; ?></td>                             
                            <td><?= $hargaText; ?></td>                             
                            <td><?= htmlspecialchars($data['jam_operasional']); ?></td>
                            <td><?= $linkGambar; ?></td>                             
                            <td><?= $tgl; ?></td>                             
                            <td>                                 
                                <a href="?page=MyApp/edit_kuliner&id=<?= htmlspecialchars($data['id_kuliner']); ?>"                                    
                                   class="btn btn-success btn-sm" title="Ubah">                                     
                                    <i class="glyphicon glyphicon-edit"></i>                                 
                                </a>                                 
                                <a href="?page=MyApp/del_kuliner&id=<?= htmlspecialchars($data['id_kuliner']); ?>"                                    
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

<!-- Modal untuk melihat gambar kuliner -->
<div class="modal fade" id="kulinerImageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="kulinerModalTitle">Gambar Kuliner</h4>
            </div>
            <div class="modal-body">
                <div id="kulinerImageGallery" class="row"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk melihat gambar full size -->
<div class="modal fade" id="kulinerFullImageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Preview Gambar</h4>
            </div>
            <div class="modal-body text-center">
                <img id="kulinerFullImage" src="" class="img-responsive" style="max-width: 100%; height: auto;">
            </div>
        </div>
    </div>
</div>

<script>
function showKulinerImages(kulinerId, kulinerName, gambarString) {
    // Set judul modal
    document.getElementById('kulinerModalTitle').textContent = 'Gambar Kuliner: ' + kulinerName;
    
    // Parse gambar
    var gambarArray = gambarString.split(',');
    var gallery = document.getElementById('kulinerImageGallery');
    gallery.innerHTML = '';
    
    if (gambarString.trim() === '') {
        gallery.innerHTML = '<div class="col-md-12"><div class="alert alert-info text-center">Tidak ada gambar untuk kuliner ini.</div></div>';
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
                         onclick="showKulinerFullImage('${gambar.trim()}')">
                        <img src="uploads/${gambar.trim()}" 
                             alt="Gambar Kuliner" 
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
    $('#kulinerImageModal').modal('show');
}

function showKulinerFullImage(imageName) {
    document.getElementById('kulinerFullImage').src = 'uploads/' + imageName;
    $('#kulinerFullImageModal').modal('show');
}
</script>

<style>
.thumbnail:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

#kulinerImageModal .modal-dialog {
    width: 90%;
    max-width: 1000px;
}

@media (max-width: 768px) {
    #kulinerImageModal .modal-dialog {
        width: 95%;
        margin: 10px auto;
    }
}
</style>