<?php
//Mulai Sesion
session_start();
if (isset($_SESSION["ses_username"]) == "") {
	header("location: login.php");
} else {
	$data_id = $_SESSION["ses_id"];
	$data_nama = $_SESSION["ses_nama"];
	$data_user = $_SESSION["ses_username"];
	$data_level = $_SESSION["ses_level"];
}

//KONEKSI DB
include "inc/koneksi.php";
?>
<style>
/* BODY & BACKGROUND */
body {
    background: linear-gradient(120deg, #2272ff 0%, #66aaff 100%) !important;
}

/* MAIN HEADER */
.main-header {
    background-color: #1363c6 !important;
    border-bottom: 4px solid #1e90ff;
}
.main-header .logo {
    background:rgb(23, 110, 217) !important;
    color: #fff !important;
}
.main-header .navbar {
    background:rgb(23, 110, 217) !important;
    border: none;
}
.main-header .logo-lg b {
    color: #fff;
}

/* SIDEBAR */
.main-sidebar {
    background: linear-gradient(150deg, #0a2e5c 60%, #1877f2 100%) !important;
    color: whitesmoke;
}
.sidebar-menu > li.header {
    color: #fff;
    background: #1363c6;
    letter-spacing: 2px;
}
.sidebar-menu > li > a {
    color: #fff;
    background: transparent !important;
    transition: background 0.3s;
    border-radius: 8px;
    margin: 3px 5px;
}
.sidebar-menu > li.active > a,
.sidebar-menu > li > a:hover {
    background: #1e90ff !important;
    color: #fff !important;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(30, 144, 255, 0.2);
}
.sidebar-menu .treeview-menu > li > a {
    background: #1657a3 !important;
    color:rgb(231, 242, 254) !important;
    border-radius: 6px;
    margin: 2px 12px;
}
.sidebar-menu .treeview-menu > li > a:hover {
    background: #1e90ff !important;
    color: #fff !important;
}
.user-panel {
    background: #1a3762;
    border-radius: 12px;
    margin-bottom: 10px;
    padding: 12px 5px 8px 5px;
    color: #fff;
}
.user-panel img {
    border: 2px solid #1e90ff;
    box-shadow: 0 2px 6px rgba(30,144,255,0.15);
}

/* KONTEN UTAMA */
.content-wrapper, .right-side {
    background: rgba(255,255,255,0.9) !important;
    border-radius: 18px 18px 0 0;
    min-height: 90vh;
    box-shadow: 0 8px 24px 0 rgba(30,144,255,0.10);
    padding-bottom: 24px;
}

/* PANEL, BOX, TABLE */
.box-primary, .box {
    border-top: 4px solid #1e90ff !important;
    border-radius: 12px;
    box-shadow: 0 4px 20px 0 rgba(30,144,255,0.05);
}
.box-header.with-border {
    background: #eaf4ff;
    border-radius: 12px 12px 0 0;
    color: #1363c6;
}

/* BUTTONS */
.btn-primary, .btn-info, .btn-success, .btn-warning, .btn-danger {
    border-radius: 24px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(30,144,255,0.08);
    letter-spacing: 1px;
}
.btn-primary {
    background: linear-gradient(90deg, #2272ff 0%, #66aaff 100%);
    border: none;
}

/* TABLES */
.table-striped>tbody>tr:nth-of-type(odd) {
    background-color: #e3f0ff;
}
.table > thead > tr {
    background: #1363c6;
    color: #fff;
}

/* DATATABLES SEARCH & PAGINATION */
.dataTables_filter input,
.dataTables_length select {
    border-radius: 18px;
    border: 1.5px solid #1e90ff;
    background: #eaf4ff;
}
.dataTables_paginate .paginate_button {
    background: #eaf4ff !important;
    color: #1363c6 !important;
    border-radius: 14px !important;
    margin: 0 2px;
}
.dataTables_paginate .paginate_button.current,
.dataTables_paginate .paginate_button:hover {
    background: #2272ff !important;
    color: #fff !important;
}

</style>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>SI Wisata</title>
	<link rel="icon" href="dist/img/sma.png">
	<!-- Tell the browser to be responsive to screen width -->
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<!-- Bootstrap 3.3.6 -->
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
	<!-- Ionicons -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
	<!-- DataTables -->
	<link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="plugins/select2/select2.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="dist/css/AdminLTE.min.css">
	<!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
	<link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
</head>

<body class="hold-transition skin-blue sidebar-mini">
	<!-- Site wrapper -->
	<div class="wrapper">

		<header class="main-header">
			<!-- Logo -->
			<a href="index.php" class="logo">
				<span class="logo-lg">
					<img src="dist/img/sma.png" width="37px">
					<b>Si Wisata</b>
				</span>
			</a>
			<!-- Header Navbar: style can be found in header.less -->
			<nav class="navbar navbar-static-top">
				<!-- Sidebar toggle button-->
				<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					
				</a>

				<div class="navbar-custom-menu">
					<ul class="nav navbar-nav">
						<!-- Messages: style can be found in dropdown.less-->
						<li class="dropdown messages-menu">
							<a class="dropdown-toggle">
								<span>
									<b>
										Sistem Informasi Tempat Wisata Kalimantan Tengah
									</b>
								</span>
							</a>
						</li>
					</ul>
				</div>
			</nav>
		</header>

		<!-- =============================================== -->

		<!-- Left side column. contains the sidebar -->
		<aside class="main-sidebar">
			<!-- sidebar: style can be found in sidebar.less -->
			<section class="sidebar">
				<!-- Sidebar user panel -->
				</<b>
				<div class="user-panel">
					<div class="pull-left image">
						<img src="dist/img/avatar.png" class="img-circle" alt="User Image">
					</div>
					<div class="pull-left info">
						<p>
							<?php echo $data_nama; ?>
						</p>
						<span class="label label-warning">
							<?php echo $data_level; ?>
						</span>
					</div>
				</div>
				</br>
				<!-- /.search form -->
				<!-- sidebar menu: : style can be found in sidebar.less -->
				<ul class="sidebar-menu">
					<li class="header">MAIN NAVIGATION</li>

					<!-- Level  -->
					<?php
					if ($data_level == "Administrator") {
					?>

						<li class="treeview">
							<a href="?page=admin">
								<i class="fa fa-dashboard"></i>
								<span>Dashboard</span>
								</span>
							</a>
						</li>
						
						<li class="treeview">
							<a href="?page=MyApp/data_pengguna">
								<i class="fa fa-user"></i> <!-- Ikon untuk Informasi Wisata -->
								<span>Pengguna Sistem</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/data_promosi">
								<i class="fa fa-bullhorn"></i> <!-- Ikon untuk Oleh Oleh -->
								<span>Promosi</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/data_wisata">
								<i class="fa fa-globe"></i> <!-- Ikon untuk Informasi Wisata -->
								<span>Wisata</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/data_kuliner">
								<i class="fa fa-cutlery"></i> <!-- Ikon untuk Kuliner -->
								<span>Kuliner</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/data_oleh2">
								<i class="fa fa-gift"></i> <!-- Ikon untuk Oleh Oleh -->
								<span>Oleh Oleh</span>
							</a>
						</li>
						
						<li class="treeview">
							<a href="?page=MyApp/data_event">
								<i class="fa fa-calendar"></i> <!-- Ikon untuk Event/Festival -->
								<span>Event atau Festival</span>
							</a>
						</li>
						
						<li class="treeview">
							<a href="?page=MyApp/data_hotel">
								<i class="fa fa-bed"></i> <!-- Ikon untuk Event/Festival -->
								<span>Hotel</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/data_sewa">
								<i class="fa fa-car" ></i> <i class="fa fa-motorcycle"></i> <!-- Ikon untuk Event/Festival -->
								<span>Sewa Mobil/Motor</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/transaksi_wisata">
								<i class="fa fa-ticket" ></i> 
								<span>Tiket</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/data_kritik_wisata">
								<i class="fa fa-comment"></i> <!-- Ikon untuk Event/Festival -->
								<span>Saran Kritik</span>
							</a>
						</li>
						

						<li class="treeview">
							<a href="#">
								<i class="fa fa-print"></i>
								<span>Laporan</span>
									<i class="fa fa-angle-left pull-right"></i>
								</span>
							</a>
							<ul class="treeview-menu">
								<li>
									<a href="?page=MyApp/cetak_promosi">
										<i class="fa fa-file"></i>Laporan promosi</a>
								</li>
								<li>
									<a href="?page=MyApp/cetak_wisata">
										<i class="fa fa-file"></i>Laporan Wisata</a>
								</li>
								
								
								<li>
									<a href="?page=MyApp/cetak_kuliner">
										<i class="fa fa-file"></i>Laporan kuliner</a>
								</li>
								
								<li>
									<a href="?page=MyApp/cetak_oleh2">
										<i class="fa fa-file"></i>Laporan Oleh-oleh</a>
								</li>
								
								<li>
									<a href="?page=MyApp/cetak_event">
										<i class="fa fa-file"></i>Laporan Event</a>
								</li>
								<li>
									<a href="?page=MyApp/cetak_hotel">
										<i class="fa fa-file"></i>Laporan Hotel</a>
								</li>
								
								<li>
									<a href="?page=MyApp/cetak_sewa">
										<i class="fa fa-file"></i>Laporan Sewa Kendaraan</a>
								</li>
								<li>
									<a href="?page=MyApp/laporan_tiket">
										<i class="fa fa-file"></i>Laporan Pemesanan Tiket</a>
								</li>
								<li>
									<a href="?page=MyApp/cetak_kritik_wisata">
										<i class="fa fa-file"></i>Laporan kritik Saran</a>
								</li>

								
							</ul>
						</li>

						<li class="header">SETTING</li>

						

					<?php
					} elseif ($data_level == "pengguna") {
					?>

							<li class="treeview">
							<a href="?page=pengguna">
								<i class="fa fa-dashboard"></i>
								<span>Dashboard</span>
								</span>
							</a>
						</li>
						
					
						<li class="treeview">
							<a href="?page=MyApp/data_wisata">
								<i class="fa fa-globe"></i> <!-- Ikon untuk Informasi Wisata -->
								<span>Wisata</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/data_kuliner">
								<i class="fa fa-cutlery"></i> <!-- Ikon untuk Kuliner -->
								<span>Kuliner</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/data_event">
								<i class="fa fa-calendar"></i> <!-- Ikon untuk Event/Festival -->
								<span>Event atau Festival</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/data_oleh2">
								<i class="fa fa-gift"></i> <!-- Ikon untuk Oleh Oleh -->
								<span>Oleh Oleh</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/data_hotel">
								<i class="fa fa-bed"></i> <!-- Ikon untuk Event/Festival -->
								<span>Hotel</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/data_sewa">
								<i class="fa fa-car" ></i> <i class="fa fa-motorcycle"></i> <!-- Ikon untuk Event/Festival -->
								<span>Sewa Mobil/Motor</span>
							</a>
						</li>

						<li class="treeview">
							<a href="?page=MyApp/transaksi_wisata">
								<i class="fa fa-ticket" ></i> 
								<span>Tiket</span>
							</a>
						</li>

						<li class="header">SETTING</li>

						

					<?php
					}
					?>

					<li>
						<a href="logout.php" onclick="return confirm('Anda yakin keluar dari aplikasi ?')">
							<i class="fa fa-sign-out"></i>
							<span>Logout</span>
						</a>
					</li>


			</section>
			<!-- /.sidebar -->
		</aside>

		<!-- =============================================== -->

		<!-- Content Wrapper. Contains page content -->
		<div class="content-wrapper">
			<!-- Content Header (Page header) -->
			<!-- Main content -->
			<section class="content">
				<?php
				if (isset($_GET['page'])) {
					$hal = $_GET['page'];

					switch ($hal) {
							//Klik Halaman Home Pengguna
						case 'admin':
							include "home/admin.php";
							break;
						case 'pengguna':
							include "home/pengguna.php";
							break;
						case 'visimisi':
								include "home/visimisi.php";
								break;
						case 'struktur':
							include "home/struktur.php";
							break;
	

							//Pengguna
						case 'MyApp/data_pengguna':
							include "admin/pengguna/data_pengguna.php";
							break;
						case 'MyApp/add_pengguna':
							include "admin/pengguna/add_pengguna.php";
							break;
						case 'MyApp/edit_pengguna':
							include "admin/pengguna/edit_pengguna.php";
							break;
						case 'MyApp/del_pengguna':
							include "admin/pengguna/del_pengguna.php";
							break;
						case 'MyApp/cetak_pengguna':
							include "admin/pengguna/cetak_pengguna.php";
							break;
						

							//wisata
						case 'MyApp/data_wisata':
							include "admin/wisata/data_wisata.php";
							break;
						case 'MyApp/detail_wisata':
							include "admin/wisata/detail_wisata.php";
							break;
						case 'MyApp/kritik_saran':
								include "admin/wisata/kritik_saran.php";
								break;
						case 'MyApp/add_wisata':
							include "admin/wisata/add_wisata.php";
							break;
						case 'MyApp/edit_wisata':
							include "admin/wisata/edit_wisata.php";
							break;
						case 'MyApp/del_wisata':
							include "admin/wisata/del_wisata.php";
							break;
						case 'MyApp/tabel_wisata':
							include "admin/wisata/tabel_wisata.php";
							break;
						case 'MyApp/cetak_wisata':
							include "admin/wisata/cetak_wisata.php";
							break;
						case 'MyApp/cetak_kritik_wisata':
							include "admin/wisata/cetak_kritik_wisata.php";
							break;
						case 'MyApp/data_kritik_wisata':
							include "admin/wisata/data_kritik_wisata.php";
							break;
						case 'MyApp/del_kritik_wisata':
							include "admin/wisata/del_kritik_wisata.php";
							break;
						

						//transaksi_wisata	
						case 'MyApp/transaksi_wisata':
							include "admin/transaksi_wisata/transaksi_wisata.php";
							break;
						case 'MyApp/add_transaksi':
							include "admin/transaksi_wisata/add_transaksi.php";
							break;
						case 'MyApp/edit_transaksi':
							include "admin/transaksi_wisata/edit_transaksi.php";
							break;
						case 'MyApp/batal_transaksi':
							include "admin/transaksi_wisata/batal_transaksi.php";
							break;
						case 'MyApp/reject_transaksi':
							include "admin/transaksi_wisata/reject_transaksi.php";
							break;
						case 'MyApp/del_transaksi':
							include "admin/transaksi_wisata/del_transaksi.php";
							break;
						case 'MyApp/cetak_tiket':
							include "admin/transaksi_wisata/cetak_tiket.php";
							break;
						case 'MyApp/approve_transaksi':
							include "admin/transaksi_wisata/approve_transaksi.php";
							break;
						case 'MyApp/laporan_tiket':
							include "admin/transaksi_wisata/laporan_tiket.php";
							break;


						//kuliner
						case 'MyApp/data_kuliner':
							include "admin/kuliner/data_kuliner.php";
							break;
						case 'MyApp/add_kuliner':
							include "admin/kuliner/add_kuliner.php";
							break;
						case 'MyApp/tabel_kuliner':
							include "admin/kuliner/tabel_kuliner.php";
							break;
						case 'MyApp/detail_kuliner':
							include "admin/kuliner/detail_kuliner.php";
							break;
						case 'MyApp/del_kuliner':
							include "admin/kuliner/del_kuliner.php";
							break;
						case 'MyApp/edit_kuliner':
							include "admin/kuliner/edit_kuliner.php";
							break;
						case 'MyApp/cetak_kuliner':
							include "admin/kuliner/cetak_kuliner.php";
							break;
						case 'MyApp/cetak_kritik_kuliner':
							include "admin/kuliner/cetak_kritik_kuliner.php";
							break;
						case 'MyApp/data_kritik_kuliner':
							include "admin/kuliner/data_kritik_kuliner.php";
							break;
						case 'MyApp/del_kritik_kuliner':
							include "admin/kuliner/del_kritik_kuliner.php";
							break;


						//hotel
						case 'MyApp/data_hotel':
							include "admin/hotel/data_hotel.php";
							break;
						case 'MyApp/add_hotel':
							include "admin/hotel/add_hotel.php";
							break;
						case 'MyApp/tabel_hotel':
							include "admin/hotel/tabel_hotel.php";
							break;
						case 'MyApp/detail_hotel':
							include "admin/hotel/detail_hotel.php";
							break;
						case 'MyApp/del_hotel':
							include "admin/hotel/del_hotel.php";
							break;
						case 'MyApp/edit_hotel':
							include "admin/hotel/edit_hotel.php";
							break;
						case 'MyApp/cetak_hotel':
							include "admin/hotel/cetak_hotel.php";
							break;
						case 'MyApp/cetak_kritik_hotel':
							include "admin/hotel/cetak_kritik_hotel.php";
							break;
						case 'MyApp/data_kritik_hotel':
							include "admin/hotel/data_kritik_hotel.php";
							break;
						case 'MyApp/del_kritik_hotel':
							include "admin/hotel/del_kritik_hotel.php";
							break;
							

								//oleh2
						case 'MyApp/data_oleh2':
							include "admin/oleh2/data_oleh2.php";
							break;
						case 'MyApp/add_oleh2':
							include "admin/oleh2/add_oleh2.php";
							break;
						case 'MyApp/edit_oleh2':
							include "admin/oleh2/edit_oleh2.php";
							break;
						case 'MyApp/del_oleh2':
							include "admin/oleh2/del_oleh2.php";
							break;
						case 'MyApp/tabel_oleh2':
							include "admin/oleh2/tabel_oleh2.php";
							break;
						case 'MyApp/detail_oleh2':
							include "admin/oleh2/detail_oleh2.php";
							break;
						case 'MyApp/cetak_oleh2':
							include "admin/oleh2/cetak_oleh2.php";
							break;
							case 'MyApp/cetak_kritik_oleh2':
							include "admin/oleh2/cetak_kritik_oleh2.php";
							break;
						case 'MyApp/data_kritik_oleh2':
							include "admin/oleh2/data_kritik_oleh2.php";
							break;
						case 'MyApp/del_kritik_oleh2':
							include "admin/oleh2/del_kritik_oleh2.php";
							break;

								//event
						case 'MyApp/data_event':
							include "admin/event1/data_event.php";
							break;
						case 'MyApp/add_event':
							include "admin/event1/add_event.php";
							break;
						case 'MyApp/del_event':
							include "admin/event1/del_event.php";
							break;
						case 'MyApp/edit_event':
							include "admin/event1/edit_event.php";
							break;
						case 'MyApp/detail_event':
							include "admin/event1/detail_event.php";
							break;
						case 'MyApp/tabel_event':
							include "admin/event1/tabel_event.php";
							break;
						case 'MyApp/map_event':
							include "admin/event1/map_event.php";
							break;
						case 'MyApp/cetak_event':
							include "admin/event1/cetak_event.php";
							break;
						case 'MyApp/cetak_kritik_event':
							include "admin/event1/cetak_kritik_event.php";
							break;
						case 'MyApp/data_kritik_event':
							include "admin/event1/data_kritik_event.php";
							break;
						case 'MyApp/del_kritik_event':
							include "admin/event1/del_kritik_event.php";
							break;

							//Promosi 
						case 'MyApp/data_promosi':
							include "admin/promosi/data_promosi.php";
							break;
						case 'MyApp/add_promosi':
							include "admin/promosi/add_promosi.php";
							break;
						case 'MyApp/del_promosi':
							include "admin/promosi/del_promosi.php";
							break;
						case 'MyApp/edit_promosi':
							include "admin/promosi/edit_promosi.php";
							break;
						case 'MyApp/cetak_promosi':
							include "admin/promosi/cetak_promosi.php";
							break;

							//sewa
						case 'MyApp/data_sewa':
							include "admin/sewa/data_sewa.php";
							break;
						case 'MyApp/add_sewa':
							include "admin/sewa/add_sewa.php";
							break;
						case 'MyApp/tabel_sewa':
							include "admin/sewa/tabel_sewa.php";
							break;
						case 'MyApp/detail_sewa':
							include "admin/sewa/detail_sewa.php";
							break;
						case 'MyApp/del_sewa':
							include "admin/sewa/del_sewa.php";
							break;
						case 'MyApp/edit_sewa':
							include "admin/sewa/edit_sewa.php";
							break;
						case 'MyApp/transaksi_sewa':
							include "admin/sewa/transaksi_sewa.php";
							break;
						case 'MyApp/cetak_sewa':
							include "admin/sewa/cetak_sewa.php";
							break;
						case 'MyApp/cetak_kritik_sewa':
							include "admin/sewa/cetak_kritik_sewa.php";
							break;
						case 'MyApp/data_kritik_sewa':
							include "admin/sewa/data_kritik_sewa.php";
							break;
						case 'MyApp/del_kritik_sewa':
							include "admin/sewa/del_kritik_sewa.php";
							break;

						
						case 'MyApp/add_transaksi_sewa':
							include "admin/transaksi_sewa/add_transaksi_sewa.php";
							break;
						case 'MyApp/edit_transaksi_sewa':
							include "admin/transaksi_sewa/edit_transaksi_sewa.php";
							break;
						case 'MyApp/batal_transaksi_sewa':
							include "admin/transaksi_sewa/batal_transaksi_sewa.php";
							break;
						case 'MyApp/del_transaksi_sewa':
							include "admin/transaksi_sewa/del_transaksi_sewa.php";
							break;
						case 'MyApp/cetak_transaksi_sewa':
							include "admin/transaksi_sewa/cetak_transaksi_sewa.php";
							break;
						case 'MyApp/approve_transaksi_sewa':
							include "admin/transaksi_sewa/approve_transaksi_sewa.php";
							break;
						case 'MyApp/selesai_transaksi_sewa':
							include "admin/transaksi_sewa/selesai_transaksi_sewa.php";
							break;
						case 'MyApp/detail_transaksi_sewa':
							include "admin/transaksi_sewa/detail_transaksi_sewa.php";
							break;
						case 'MyApp/laporan_sewa':
							include "admin/transaksi_sewa/laporan_sewa.php";
							break;
			
							//default
						default:
							echo "<center><br><br><br><br><br><br><br><br><br>
				  <h1> Halaman tidak ditemukan !</h1></center>";
							break;
					}
				} else {
					// Auto Halaman Home Pengguna
					if ($data_level == "Administrator") {
						include "home/admin.php";
					} elseif ($data_level == "pengguna") {
						include "home/pengguna.php";
					}
				}
				?>



			</section>
			<!-- /.content -->
		</div>

		<!-- /.content-wrapper 

		<footer class="main-footer">
			<div class="pull-right hidden-xs">
			</div>
			<strong>Copyright &copy;
				<a href="https://www.facebook.com/">Muhammad Ivan Setiawan</a>.</strong> All rights reserved.
		</footer>
		<div class="control-sidebar-bg"></div>
		-->

		<!-- ./wrapper -->

		<!-- jQuery 2.2.3 -->
		<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
			 
		<!--Bootstrap 3.3.6 -->
			
		<script src = "bootstrap/js/bootstrap.min.js"></script>
		

		<script src="plugins/select2/select2.full.min.js"></script>
		<!-- DataTables -->
		<script src="plugins/datatables/jquery.dataTables.min.js"></script>
		<script src="plugins/datatables/dataTables.bootstrap.min.js"></script>

		<!-- AdminLTE App -->
		<script src="dist/js/app.min.js"></script>
		<!-- AdminLTE for demo purposes -->
		<script src="dist/js/demo.js"></script>
		<!-- page script -->


		<script>
			$(function() {
				$("#example1").DataTable({
					columnDefs: [{
						"defaultContent": "-",
						"targets": "_all"
					}]
				});
				$('#example2').DataTable({
					"paging": true,
					"lengthChange": false,
					"searching": false,
					"ordering": true,
					"info": true,
					"autoWidth": false
				});
			});
		</script>

		<script>
			$(function() {
				//Initialize Select2 Elements
				$(".select2").select2();
			});
		</script>
</body>

</html>
