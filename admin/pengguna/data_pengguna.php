<?php
// inc/koneksi.php
include "inc/koneksi.php";
?>

<section class="content-header">
    <h1 style="text-align:center;">Data Pengguna</h1>
    <ol class="breadcrumb">
        <li>
            <a href="index.php">
                <i class="fa fa-home"></i>
                <b>Si Tabsis</b>
            </a>
        </li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <a href="?page=MyApp/add_pengguna" class="btn btn-primary">
                <i class="glyphicon glyphicon-plus"></i> Tambah Data
            </a>
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Alamat</th>
                            <th>No HP</th>
                            <th>Level</th>
                            <th>Kelola</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $sql = $koneksi->query("SELECT * FROM tb_pengguna ORDER BY id_pengguna DESC");
                        while ($data = $sql->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($data['nama_pengguna']); ?></td>
                            <td><?= htmlspecialchars($data['username']); ?></td>
                            <td><?= htmlspecialchars($data['alamat']); ?></td>
                            <td><?= htmlspecialchars($data['no_hp']); ?></td>
                            <td><?= htmlspecialchars($data['level']); ?></td>
                            <td>
                                <a href="?page=MyApp/edit_pengguna&kode=<?= htmlspecialchars($data['id_pengguna']); ?>"
                                   class="btn btn-success btn-sm" title="Ubah">
                                    <i class="glyphicon glyphicon-edit"></i>
                                </a>
                                <a href="?page=MyApp/del_pengguna&kode=<?= htmlspecialchars($data['id_pengguna']); ?>"
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
