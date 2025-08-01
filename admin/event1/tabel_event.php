<?php  
// File: tabel_event.php (Listing Data Event dengan Kolom Gambar)  
include "inc/koneksi.php";  
?>  

<section class="content-header">   
    <h1 style="text-align:center;">Data Event Wisata</h1> 
</section>  

<section class="content">   
    <div class="box box-primary">     
        <div class="box-header with-border">       
            <a href="?page=MyApp/add_event" class="btn btn-primary">         
                <i class="glyphicon glyphicon-plus"></i> Tambah Event       
            </a>       
            <a href="?page=MyApp/data_event" class="btn btn-warning" style="margin-left:7px;">                 
                <i class="fa fa-arrow-left"></i> Kembali             
            </a>     
        </div>     
        <div class="box-body">       
            <div class="table-responsive">         
                <table id="example1" class="table table-bordered table-striped">           
                    <thead>             
                        <tr>               
                            <th>No</th>               
                            <th>Nama Event</th>               
                            <th>Kategori</th>               
                            <th>Tanggal</th>               
                            <th>Lokasi</th>               
                            <th>Harga Tiket</th>               
                            <th>Jam Operasional</th>
                            <th>Gambar</th>               
                            <th>Tgl Upload</th>               
                            <th>Kelola</th>             
                        </tr>           
                    </thead>           
                    <tbody>             
                        <?php             
                        $no = 1;             
                        $sql = $koneksi->query("SELECT * FROM tb_event ORDER BY tanggal_upload DESC");             
                        while ($data = $sql->fetch_assoc()) {                 
                            $lokasi = htmlspecialchars("{$data['alamat']}, {$data['kecamatan']}, {$data['kabupaten']}, {$data['provinsi']}");                 
                            $tglEvent = date('d M Y', strtotime($data['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($data['tanggal_selesai']));                 
                            $harga = htmlspecialchars($data['harga_tiket']);                 
                            $jam = htmlspecialchars($data['jam_operasional']);                 
                            $tglUp = date('d-m-Y H:i', strtotime($data['tanggal_upload']));
                            
                            // Cek apakah ada gambar
                            $gambar = $data['gambar'];
                            $linkGambar = '';
                            if (!empty($gambar)) {
                                $linkGambar = '<button type="button" class="btn btn-info btn-xs" 
                                               onclick="showImages(\'' . $data['id_event'] . '\', \'' . htmlspecialchars($data['nama_event']) . '\', \'' . $gambar . '\')">
                                               <i class="glyphicon glyphicon-picture"></i> Lihat Gambar
                                               </button>';
                            } else {
                                $linkGambar = '<span class="text-muted">Tidak ada gambar</span>';
                            }
                        ?>             
                        <tr>               
                            <td><?= $no++; ?></td>               
                            <td><?= htmlspecialchars($data['nama_event']); ?></td>               
                            <td><?= htmlspecialchars($data['kategori']); ?></td>               
                            <td><?= $tglEvent; ?></td>               
                            <td><?= $lokasi; ?></td>               
                            <td><?= $harga; ?></td>               
                            <td><?= $jam; ?></td>
                            <td><?= $linkGambar; ?></td>               
                            <td><?= $tglUp; ?></td>               
                            <td>                 
                                <a href="?page=MyApp/edit_event&id=<?= $data['id_event']; ?>"                    
                                   class="btn btn-success btn-sm" title="Ubah">
                                   <i class="glyphicon glyphicon-edit"></i>
                                </a>                 
                                <a href="?page=MyApp/del_event&id=<?= $data['id_event']; ?>"                    
                                   onclick="return confirm('Yakin hapus event ini?')"                    
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

<!-- Modal untuk melihat gambar event -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modalTitle">Gambar Event</h4>
            </div>
            <div class="modal-body">
                <div id="imageGallery" class="row"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk melihat gambar full size -->
<div class="modal fade" id="fullImageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Preview Gambar</h4>
            </div>
            <div class="modal-body text-center">
                <img id="fullImage" src="" class="img-responsive" style="max-width: 100%; height: auto;">
            </div>
        </div>
    </div>
</div>

<script>
function showImages(eventId, eventName, gambarString) {
    // Set judul modal
    document.getElementById('modalTitle').textContent = 'Gambar Event: ' + eventName;
    
    // Parse gambar
    var gambarArray = gambarString.split(',');
    var gallery = document.getElementById('imageGallery');
    gallery.innerHTML = '';
    
    if (gambarString.trim() === '') {
        gallery.innerHTML = '<div class="col-md-12"><div class="alert alert-info text-center">Tidak ada gambar untuk event ini.</div></div>';
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
                         onclick="showFullImage('${gambar.trim()}')">
                        <img src="uploads/${gambar.trim()}" 
                             alt="Gambar Event" 
                             class="img-responsive" 
                             style="width: 100%; height: 150px; object-fit: cover; border-radius: 4px;"
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2VlZSIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1zaXplPSIxMiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iIGZpbGw9IiM5OTkiPkdhbWJhciBUaWRhayBEaXRlbXVrYW48L3RleHQ+PC9zdmc+'">
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
    $('#imageModal').modal('show');
}

function showFullImage(imageName) {
    document.getElementById('fullImage').src = 'uploads/' + imageName;
    $('#fullImageModal').modal('show');
}
</script>

<style>
.thumbnail:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

#imageModal .modal-dialog {
    width: 90%;
    max-width: 1000px;
}

@media (max-width: 768px) {
    #imageModal .modal-dialog {
        width: 95%;
        margin: 10px auto;
    }
}
</style>