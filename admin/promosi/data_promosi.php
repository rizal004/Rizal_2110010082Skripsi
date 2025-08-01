<?php
include "inc/koneksi.php";
?>

<section class="content-header">
    <h1 style="text-align:center;">Data Promosi</h1>
    
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <a href="?page=MyApp/add_promosi" class="btn btn-primary">
                <i class="glyphicon glyphicon-plus"></i> Tambah Promosi
            </a>
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Jenis Promosi</th>
                            <th>Deskripsi</th>
                            <th>Lokasi</th>
                            <th>Tanggal</th>
                            <th>Harga</th>
                            <th>Kontak</th>
                            <th>Bukti </th>
                            <th>Tanggal Upload</th>
                            <th>Kelola</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $sql = $koneksi->query("SELECT * FROM tb_promosi ORDER BY tanggal_upload DESC");
                        while ($data = $sql->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($data['judul_promosi']); ?></td>
                            <td><?= htmlspecialchars($data['jenis_promosi']); ?></td>
                            <td><?= htmlspecialchars($data['deskripsi']); ?></td>
                            <td><?= htmlspecialchars($data['lokasi']); ?></td>
                            <td>
                                <?= date('d-m-Y', strtotime($data['tanggal_mulai'])) . " s/d " . date('d-m-Y', strtotime($data['tanggal_selesai'])); ?>
                            </td>
                            <td>Rp<?= number_format($data['harga'], 0, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($data['kontak']); ?></td>
                            <td>
                                <?php if(!empty($data['gambar'])) { ?>
                                    <img src="uploads/promosi/<?= htmlspecialchars($data['gambar']); ?>" width="60">
                                <?php } else { echo '<span style="color:#bbb;">-</span>'; } ?>
                            </td>
                            <td><?= date('d-m-Y H:i', strtotime($data['tanggal_upload'])); ?></td>
                            <td>
                                <a href="?page=MyApp/edit_promosi&id=<?= htmlspecialchars($data['id_promosi']); ?>"
                                   class="btn btn-success btn-sm" title="Ubah">
                                    <i class="glyphicon glyphicon-edit"></i>
                                </a>
                                <a href="?page=MyApp/del_promosi&id=<?= htmlspecialchars($data['id_promosi']); ?>"
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
