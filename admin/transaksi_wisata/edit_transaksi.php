<?php
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$id_pengguna_sess = isset($_SESSION['ses_id']) ? intval($_SESSION['ses_id']) : 0;
$id_transaksi = isset($_GET['id']) ? $_GET['id'] : '';

// Ambil data transaksi
$sql = $koneksi->query("SELECT t.*, w.nama_wisata, w.harga_tiket, p.nama_pengguna 
                        FROM tb_transaksi t 
                        LEFT JOIN tb_wisata w ON t.id_wisata = w.id_wisata 
                        LEFT JOIN tb_pengguna p ON t.id_pengguna = p.id_pengguna
                        WHERE t.id_transaksi='$id_transaksi' LIMIT 1");
$data = $sql ? $sql->fetch_assoc() : null;

if (!$data || intval($data['id_pengguna']) !== $id_pengguna_sess) {
    echo "<script>alert('Transaksi tidak ditemukan/akses tidak sah!');window.location='?page=MyApp/data_transaksi';</script>";
    exit;
}

// Default harga tiket
$harga_tiket_default = $data['harga_tiket'];

// Proses update
if (isset($_POST['update'])) {
    $id_wisata_baru = mysqli_real_escape_string($koneksi, $_POST['id_wisata']);
    $jumlah_tiket   = intval($_POST['jumlah_tiket']);

    // Ambil harga tiket baru
    $qry_wisata = $koneksi->query("SELECT nama_wisata, harga_tiket FROM tb_wisata WHERE id_wisata='$id_wisata_baru'");
    $wisata_baru = $qry_wisata ? $qry_wisata->fetch_assoc() : null;
    $harga_baru  = $wisata_baru ? floatval($wisata_baru['harga_tiket']) : 0;
    $total       = $harga_baru * $jumlah_tiket;
    $update_bukti = '';

    // Cek upload bukti baru
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        if (!empty($data['bukti_pembayaran']) && file_exists("uploads/bukti/" . $data['bukti_pembayaran'])) {
            unlink("uploads/bukti/" . $data['bukti_pembayaran']);
        }
        $fileTmp  = $_FILES['bukti_pembayaran']['tmp_name'];
        $fileName = $id_transaksi . '_' . basename($_FILES['bukti_pembayaran']['name']);
        $target   = "uploads/bukti/" . $fileName;
        if (move_uploaded_file($fileTmp, $target)) {
            $update_bukti = ", bukti_pembayaran='$fileName'";
        }
    }

    // Update transaksi
    $q = $koneksi->query("UPDATE tb_transaksi SET 
            id_wisata='$id_wisata_baru',
            jumlah_tiket=$jumlah_tiket,
            total_bayar=$total
            $update_bukti
        WHERE id_transaksi='$id_transaksi'
    ");
    if ($q) {
        echo "<script>alert('Transaksi berhasil diupdate!');window.location='?page=MyApp/data_transaksi';</script>";
    } else {
        echo "<script>alert('Gagal update transaksi!');</script>";
    }

    // Refresh data
    $sql = $koneksi->query("SELECT t.*, w.nama_wisata, w.harga_tiket, p.nama_pengguna 
                        FROM tb_transaksi t 
                        LEFT JOIN tb_wisata w ON t.id_wisata = w.id_wisata 
                        LEFT JOIN tb_pengguna p ON t.id_pengguna = p.id_pengguna
                        WHERE t.id_transaksi='$id_transaksi' LIMIT 1");
    $data = $sql ? $sql->fetch_assoc() : null;
    $harga_tiket_default = $data['harga_tiket'];
}
?>

<section class="content-header">
    <h1 style="text-align:center;">Edit Transaksi</h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">Form Edit Transaksi</h3></div>
        <form action="" method="POST" enctype="multipart/form-data" id="formEditTransaksi">
            <div class="box-body">
                <div class="form-group">
                    <label>Nama Pemesan</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_pengguna']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Wisata</label>
                    <select name="id_wisata" id="id_wisata" class="form-control" required>
                        <option value="">-- Pilih Wisata --</option>
                        <?php
                        $wisatas = $koneksi->query("SELECT id_wisata, nama_wisata, harga_tiket FROM tb_wisata");
                        while ($w = $wisatas->fetch_assoc()) {
                            $selected = ($w['id_wisata'] == $data['id_wisata']) ? 'selected' : '';
                            echo '<option value="'.htmlspecialchars($w['id_wisata']).'" data-harga="'.floatval($w['harga_tiket']).'" '.$selected.'>'
                                .htmlspecialchars($w['nama_wisata']).' - Rp'.number_format($w['harga_tiket'],0,',','.').'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Harga Tiket</label>
                    <input type="text" id="harga_tiket" class="form-control" value="Rp<?= number_format($harga_tiket_default,0,',','.'); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Jumlah Tiket</label>
                    <input type="number" name="jumlah_tiket" id="jumlah_tiket" class="form-control" min="1" value="<?= $data['jumlah_tiket']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Total Bayar</label>
                    <input type="text" id="total_bayar" class="form-control" value="Rp<?= number_format($data['total_bayar'],0,',','.'); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Bukti Pembayaran</label>
                    <input type="file" name="bukti_pembayaran" class="form-control" accept="image/*">
                    <?php
                    if (!empty($data['bukti_pembayaran']) && file_exists('uploads/bukti/' . $data['bukti_pembayaran'])) {
                        echo "<br><b>Bukti lama:</b><br><img src='uploads/bukti/".htmlspecialchars($data['bukti_pembayaran'])."' width='120'>";
                    }
                    ?>
                </div>
                <div class="alert alert-info">
                    <strong>Note:</strong> Silakan transfer ke rekening berikut kemudian unggah bukti pembayaran:
                    <ul>
                        <li>Dana: 0812-3456-7890</li>
                        <li>Bank BRI: 1234-5678-9012-3456</li>
                        <li>Bank Mandiri: 0987-6543-2109-8765</li>
                    </ul>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" name="update" class="btn btn-success">Update Transaksi</button>
                <a href="?page=MyApp/transaksi_wisata" class="btn btn-default">Batal</a>
            </div>
        </form>
    </div>
</section>

<script>
// Update harga tiket & total bayar saat wisata/jumlah diubah
document.addEventListener('DOMContentLoaded', function() {
    function updateHargaDanTotal() {
        let selectWisata = document.getElementById('id_wisata');
        let selected = selectWisata.options[selectWisata.selectedIndex];
        let harga = parseFloat(selected.getAttribute('data-harga')) || 0;
        let jumlah = parseInt(document.getElementById('jumlah_tiket').value) || 1;
        document.getElementById('harga_tiket').value = 'Rp' + harga.toLocaleString('id-ID');
        document.getElementById('total_bayar').value = 'Rp' + (harga * jumlah).toLocaleString('id-ID');
    }
    document.getElementById('id_wisata').addEventListener('change', updateHargaDanTotal);
    document.getElementById('jumlah_tiket').addEventListener('input', updateHargaDanTotal);
});
</script>
