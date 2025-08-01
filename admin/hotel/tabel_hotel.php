<?php   
// File: tabel_hotel.php (Listing Data Hotel)   
include "inc/koneksi.php";   
?>    

<section class="content-header">
  <h1 style="text-align:center;">Data Hotel</h1>
</section>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <a href="?page=MyApp/add_hotel" class="btn btn-primary">
        <i class="glyphicon glyphicon-plus"></i> Tambah Hotel
      </a>
      <a href="?page=MyApp/data_hotel" class="btn btn-warning" style="margin-left:7px;">
        <i class="fa fa-arrow-left"></i> Kembali
      </a>
    </div>
    <div class="box-body">
      <div class="table-responsive">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Hotel</th>
              <th>Lokasi</th>
              <th>Harga Hotel</th>
              <th>Kontak</th>
              <th>Fasilitas</th>
              <th>Deskripsi</th>
              <th>Gambar</th>
              <th>Tgl Upload</th>
              <th>Kelola</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            $sql = $koneksi->query("SELECT * FROM tb_hotel ORDER BY nama_hotel ASC");
            while ($data = $sql->fetch_assoc()) {
                // Gabung lokasi
                $lokasi = htmlspecialchars("{$data['alamat']}, {$data['kecamatan']}, {$data['kabupaten']}, {$data['provinsi']}");
                
                // Cek apakah ada gambar
                $gambar = $data['gambar'];
                $linkGambar = '';
                if (!empty($gambar)) {
                    $linkGambar = '<button type="button" class="btn btn-info btn-xs" 
                                   onclick="showHotelImages(\'' . $data['id_hotel'] . '\', \'' . htmlspecialchars($data['nama_hotel']) . '\', \'' . $gambar . '\')">
                                   <i class="glyphicon glyphicon-picture"></i> Lihat Gambar
                                   </button>';
                } else {
                    $linkGambar = '<span class="text-muted">Tidak ada gambar</span>';
                }
                
                // Tanggal upload
                $tglUp = isset($data['tanggal_upload']) ? date('d-m-Y H:i', strtotime($data['tanggal_upload'])) : '-';
            ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= htmlspecialchars($data['nama_hotel']); ?></td>
              <td><?= $lokasi; ?></td>
              <td><?= htmlspecialchars($data['harga_hotel']); ?></td>
              <td><?= htmlspecialchars($data['kontak']); ?></td>
              <td><?= htmlspecialchars($data['fasilitas']); ?></td>
              <td><?= htmlspecialchars($data['deskripsi']); ?></td>
              <td><?= $linkGambar; ?></td>
              <td><?= $tglUp; ?></td>
              <td>
                <a href="?page=MyApp/edit_hotel&id=<?= $data['id_hotel']; ?>" class="btn btn-success btn-sm" title="Ubah"><i class="glyphicon glyphicon-edit"></i></a>
                <a href="?page=MyApp/del_hotel&id=<?= $data['id_hotel']; ?>" onclick="return confirm('Yakin hapus hotel ini?')" class="btn btn-danger btn-sm" title="Hapus"><i class="glyphicon glyphicon-trash"></i></a>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<!-- Modal untuk melihat gambar hotel -->
<div class="modal fade" id="hotelImageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="hotelModalTitle">Gambar Hotel</h4>
            </div>
            <div class="modal-body">
                <div id="hotelImageGallery" class="row"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk melihat gambar full size -->
<div class="modal fade" id="hotelFullImageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Preview Gambar</h4>
            </div>
            <div class="modal-body text-center">
                <img id="hotelFullImage" src="" class="img-responsive" style="max-width: 100%; height: auto;">
            </div>
        </div>
    </div>
</div>

<script>
function showHotelImages(hotelId, hotelName, gambarString) {
    // Set judul modal
    document.getElementById('hotelModalTitle').textContent = 'Gambar Hotel: ' + hotelName;
    
    // Parse gambar
    var gambarArray = gambarString.split(',');
    var gallery = document.getElementById('hotelImageGallery');
    gallery.innerHTML = '';
    
    if (gambarString.trim() === '') {
        gallery.innerHTML = '<div class="col-md-12"><div class="alert alert-info text-center">Tidak ada gambar untuk hotel ini.</div></div>';
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
                         onclick="showHotelFullImage('${gambar.trim()}')">
                        <img src="uploads/${gambar.trim()}" 
                             alt="Gambar Hotel" 
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
    $('#hotelImageModal').modal('show');
}

function showHotelFullImage(imageName) {
    document.getElementById('hotelFullImage').src = 'uploads/' + imageName;
    $('#hotelFullImageModal').modal('show');
}
</script>

<style>
.thumbnail:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

#hotelImageModal .modal-dialog {
    width: 90%;
    max-width: 1000px;
}

@media (max-width: 768px) {
    #hotelImageModal .modal-dialog {
        width: 95%;
        margin: 10px auto;
    }
}
</style>