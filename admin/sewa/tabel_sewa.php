<?php
// File: tabel_motor.php (Listing Data Motor/Mobil) - DIPERBAIKI
include "inc/koneksi.php";
?>

<section class="content-header">
    <h1 style="text-align:center;">Data Motor & Mobil</h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <a href="?page=MyApp/add_sewa" class="btn btn-primary">
                <i class="glyphicon glyphicon-plus"></i> Tambah
            </a>
            <a href="?page=MyApp/data_sewa" class="btn btn-warning" style="margin-left:7px;">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
        
        <div class="box-body">
            <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kendaraan</th>
                            <th>Jenis</th>
                            <th>Merk</th>
                            <th>Tahun</th>
                            <th>Warna</th>
                            <th>Harga Sewa/Hari</th>
                            <th>Lokasi</th>
                            <th>Kontak</th>
                            <th>Fasilitas</th>
                            <th>Gambar</th>
                            <th>Tgl Upload</th>
                            <th>Kelola</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $sql = $koneksi->query("SELECT * FROM tb_motor ORDER BY nama_motor ASC");
                        while ($data = $sql->fetch_assoc()) {
                            // Gabung lokasi
                            $lokasi = htmlspecialchars("{$data['kecamatan']}, {$data['kabupaten']}");
                            
                            // Format harga
                            $harga = number_format($data['harga_sewa'], 0, ',', '.');
                            
                            // Tanggal upload
                            $tglUp = isset($data['tanggal_upload']) ? date('d-m-Y H:i', strtotime($data['tanggal_upload'])) : '-';
                            
                            // Gambar - DIPERBAIKI: ambil gambar pertama jika ada multiple
                            $gambarArray = !empty($data['gambar']) ? explode(',', $data['gambar']) : [];
                            $gambarPertama = !empty($gambarArray[0]) ? trim($gambarArray[0]) : '';
                            
                            // Kontak info
                            $namaKontak = isset($data['nama_kontak']) ? htmlspecialchars($data['nama_kontak']) : '-';
                            $noTelepon = isset($data['no_telepon']) ? htmlspecialchars($data['no_telepon']) : '-';
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($data['nama_motor']); ?></td>
                            <td>
                                <span class="label <?= $data['jenis_kendaraan'] == 'Motor' ? 'label-info' : 'label-success'; ?>">
                                    <?= htmlspecialchars($data['jenis_kendaraan']); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($data['merk']); ?></td>
                            <td><?= htmlspecialchars($data['tahun']); ?></td>
                            <td><?= htmlspecialchars($data['warna']); ?></td>
                            <td>Rp <?= $harga; ?></td>
                            <td style="max-width: 200px; word-wrap: break-word;"><?= $lokasi; ?></td>
                            <td style="max-width: 150px;">
                                <div class="contact-info">
                                    <strong><?= $namaKontak; ?></strong><br>
                                    <?php if ($noTelepon !== '-'): ?>
                                        <a href="https://wa.me/<?= str_replace(['+', '-', ' ', '(', ')'], '', $noTelepon); ?>" 
                                           target="_blank" 
                                           class="btn btn-success btn-xs" 
                                           title="Chat WhatsApp">
                                            <i class="fa fa-whatsapp"></i> <?= $noTelepon; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No Contact</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $fasilitas = htmlspecialchars($data['fasilitas']);
                                echo strlen($fasilitas) > 50 ? substr($fasilitas, 0, 50) . '...' : $fasilitas;
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($gambarPertama)): ?>
                                    <?php 
                                    // Cek path mana yang ada
                                    $pathUploads = "uploads/" . $gambarPertama;
                                    $pathGambar = "gambar/" . $gambarPertama;
                                    
                                    if (file_exists($pathUploads)) {
                                        $imagePath = $pathUploads;
                                    } elseif (file_exists($pathGambar)) {
                                        $imagePath = $pathGambar;
                                    } else {
                                        $imagePath = false;
                                    }
                                    ?>
                                    
                                    <?php if ($imagePath): ?>
                                        <img src="<?= $imagePath; ?>" 
                                             alt="<?= htmlspecialchars($data['nama_motor']); ?>" 
                                             style="width: 60px; height: 40px; object-fit: cover; cursor: pointer;" 
                                             class="img-thumbnail"
                                             onclick="showImageModal('<?= $imagePath; ?>', '<?= htmlspecialchars($data['nama_motor']); ?>')">
                                    <?php else: ?>
                                        <span class="text-muted">File Not Found</span>
                                        <br><small class="text-danger"><?= $gambarPertama; ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $tglUp; ?></td>
                            <td>
                                <a href="?page=MyApp/edit_sewa&id=<?= $data['id_motor']; ?>" 
                                   class="btn btn-success btn-sm" title="Ubah">
                                    <i class="glyphicon glyphicon-edit"></i>
                                </a>
                                <a href="?page=MyApp/del_sewa&id=<?= $data['id_motor']; ?>" 
                                   onclick="return confirm('Yakin hapus kendaraan ini?')" 
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

<!-- Modal untuk preview gambar -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="imageModalTitle">Preview Gambar</h4>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" style="max-width: 100%; height: auto;">
            </div>
        </div>
    </div>
</div>

<style>
.label-info {
    background-color: #5bc0de;
}
.label-success {
    background-color: #5cb85c;
}
.img-thumbnail:hover {
    opacity: 0.8;
}
.contact-info {
    font-size: 12px;
    line-height: 1.3;
}
.contact-info strong {
    color: #333;
    font-size: 13px;
}
.btn-xs {
    padding: 1px 5px;
    font-size: 11px;
    line-height: 1.2;
}
.fa-whatsapp {
    color: #25D366;
}
</style>

<script>
function showImageModal(imagePath, title) {
    document.getElementById('modalImage').src = imagePath;
    document.getElementById('imageModalTitle').textContent = title;
    $('#imageModal').modal('show');
}
</script>