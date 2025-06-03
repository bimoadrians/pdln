<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Report Perjalanan Dinas Luar Negeri</title>
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url()?>/konimex.png">

    <link rel="stylesheet" href="<?php echo base_url('admin')?>/css/jquery.dataTables.min.css">

    <script src="<?php echo base_url('admin')?>/js/jquery-3.5.1.min.js"></script>

    <!-- Custom fonts for this template-->
    <link href="<?php echo base_url('admin')?>/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    
    <script src="<?php echo base_url('admin')?>/js/all.js" crossorigin="anonymous"></script>

    <!-- Custom styles for this template-->
    <link href="<?php echo base_url('admin')?>/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="javascript:void(0)">
                <img class="img-profile rounded-circle" src="<?php echo base_url()?>/konimex.png" alt="Logo" width="35" height="35">
                <div class="sidebar-brand-icon rotate-n-15">
                </div>
                <div class="sidebar-brand-text mx-3">Perjalanan Dinas LN</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <?php
                $id_transaksi = session()->get('id_transaksi');
                $dashboard = site_url("dashboard/$id_transaksi");
                $jenis_biaya1 = "pjum";
                $jenis_biaya2 = "pb";
                $treasury = site_url("treasuryselesaipb/$jenis_biaya2/$id_transaksi");
                $gs = site_url("gsselesaipb/$jenis_biaya2/$id_transaksi");
                $support = site_url("support/$id_transaksi");
                $pjum = site_url("listpjum/$jenis_biaya1/$id_transaksi");
                $pb = site_url("listpb/$jenis_biaya2/$id_transaksi");
            ?>
            <li class="nav-item active"><a class="nav-link" href="<?php echo $dashboard?>">
                    <i class="fa-solid fa-house-chimney"></i>
                    <span>Dashboard</span></a></li>
            <li class="nav-item active"><a class="nav-link" href="<?php echo site_url('transaksi')?>">
                    <i class="fa-solid fa-id-badge"></i>
                    <span>ID Transaksi</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">
                
            <!-- Heading -->
            <div class="sidebar-heading">
                Menu
            </div>

            <?php if (empty($cek)) { ?>

            <?php } else if (empty($cekpjum)) { ?>

            <?php } else { ?>
                <!-- Nav Item - Pages Collapse Menu -->
                <?php if ($role == 'admin' || $role == 'user') { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo $pjum?>"><i class="fa-solid fa-credit-card"></i><span> PJUM</span></a>
                    </li>
                <?php } else { ?>

                <?php } ?>

                <?php if ($role == 'treasury' && $submit_pjum == 1) { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo $pjum?>"><i class="fa-solid fa-credit-card"></i><span> PJUM</span></a>
                    </li>
                <?php } else { ?>

                <?php } ?>

                <?php if ($role == 'gs' && $submit_pjum > 1) { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo $pjum?>"><i class="fa-solid fa-credit-card"></i><span> PJUM</span></a>
                    </li>
                <?php } else { ?>

                <?php } ?>

            <?php } if (empty($cekpb)) { ?>

            <?php } else { ?>
                <!-- Nav Item - Utilities Collapse Menu -->
                <?php 
                foreach ($nopb as $nb => $nopb) {
                    $created_by = $nopb['created_by'];
                }

                if ($role == 'admin' && $created_by != '05080' || $role == 'user' && $created_by != '05080') { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo $pb?>"><i class="fa-solid fa-sack-dollar"></i><span> PB</span></a>
                    </li>
                <?php } else { ?>

                <?php } ?>
                
                <!-- Nav Item - Utilities Collapse Menu -->
                <?php if ($role == 'treasury' && $submit_pb == 1 || $role == 'treasury' && $submit_pb == 0 && $created_by == '05080') { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo $pb?>"><i class="fa-solid fa-sack-dollar"></i><span> PB</span></a>
                    </li>
                <?php } else { ?>

                <?php } ?>
                
                <!-- Nav Item - Utilities Collapse Menu -->
                <?php if ($role == 'gs' && $submit_pb > 1) { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo $pb?>"><i class="fa-solid fa-sack-dollar"></i><span> PB</span></a>
                    </li>
                <?php } else { ?>

                <?php } ?>

                <?php if ($role == 'gs' && $solo != 'Surakarta' && $submit_pb > 2) { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo $support?>">
                            <i class="fa-solid fa-square-caret-down"></i>
                            <span>Biaya Support</span>
                        </a>
                    </li>
                <?php } else { ?>
                                                    
                <?php } ?>
            <?php } ?>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">

                <!-- Nav Item - User Information -->
                <li class="nav-item active"><a class="nav-link" href="javascript:void(0)" data-toggle="modal"
                        data-target="#logoutModal">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Logout</a>
                </li>
            </ul>

            <hr class="sidebar-divider">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <form class="">
                        <div style="white-space: nowrap">
                            <h1 class="h4 mb-0 text-gray-800">
                                <?php echo($header)?>
                            </h1>
                        </div>
                        <span class="text-gray-600 large"><?php echo session()->get('akun_email')?></span>
                    </form>

                    <!-- <ul class="navbar-nav ml-auto"> -->
                        <!-- Nav Item - User Information -->
                        <!-- <li class="nav-item dropdown no-arrow">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 large"><?php echo session()->get('akun_email')?></span>
                        </li>
                    </ul> -->
                </nav>
                <!-- End of Topbar -->
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Content Row -->
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 mb-4">
                            <div class="card shadow">
                                <div class="card-body">
                                    <?php
                                    $session = \Config\Services::session();
                                    if($session->getFlashdata('warning')) {
                                    ?>
                                        <div class="alert alert-warning">
                                            <ul>
                                                <?php
                                                foreach($session->getFlashdata('warning') as $val) {
                                                ?>
                                                    <li><?php echo $val ?></li>
                                                <?php
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    <?php
                                    }
                                    if($session->getFlashdata('success')) {
                                    ?>
                                        <div class="alert alert-success"><?php echo $session->getFlashdata('success')?></div>
                                    <?php
                                    }
                                    ?>
                                    <?php
                                    $id_transaksi = session()->get('id_transaksi');
                                    $role = session()->get('akun_role');
                                    $jenis_biaya = session()->get('jenis_biaya');
                                    ?>
                                    <div class="row">
                                        <div class="col-xl-12 col-lg-12">
                                            <table class="table table-bordered mb-4">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" style="vertical-align: middle; color: #5a5c69;">ID Transaksi</th>
                                                        <?php if ($role == 'treasury' && $submit == 1 && $valas_kurs != 'IDR') { ?>
                                                            <th class="text-center" style="vertical-align: middle; color: #5a5c69;">Kurs</th>
                                                        <?php } else if ($role == 'treasury' && $submit == 0 && $valas_kurs != 'IDR') { ?>
                                                            <?php
                                                            foreach ($nomor as $nomor_head) {
                                                                $tanggal_pb = $nomor_head['tanggal'];
                                                            }
                                                            if(!empty($tanggal_pb)) { ?>
                                                                <th class="text-center" style="vertical-align: middle; color: #5a5c69;">Kurs</th>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                    
                                                        <?php } ?>
                                                        <?php if ($role == 'gs' && $submit == 2) { ?>
                                                            <th class="text-center" style="vertical-align: middle; color: #5a5c69;">Berangkat dari Kota</th>
                                                        <?php } else { ?>
                                                    
                                                        <?php } ?>
                                                        <th class="text-center" style="vertical-align: middle; color: #5a5c69;">No PB</th>
                                                        <?php if ($role != 'gs') { ?>
                                                            <th class="text-center" style="vertical-align: middle; color: #5a5c69;">Tanggal Pembuatan No PB</th>
                                                        <?php } ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <th class="text-center" scope="row" style="color: #5a5c69;"><a href="<?php echo site_url("detailtransaksi/$id_transaksi")?>"><?php echo $id['id_transaksi']; echo("/"); echo("Perjalanan Dinas Luar Negeri"); echo("/"); echo $id['jumlah_personil']; echo("/"); foreach ($neg as $neg) : echo $neg['negara_tujuan']; endforeach ?></a></th>
                                                        <?php if ($role == 'treasury' && $submit == 1 && $valas_kurs != 'IDR') { ?>
                                                            <?php if(empty($kurs)) { ?>
                                                                <th class="text-center" scope="row" style="color: #5a5c69;">
                                                                    <a href="<?php echo site_url("kurspb/$id_transaksi/$jenis_biaya/$id_pb") ?>" class="btn btn-success"><abbr title="Klik untuk menambahkan nilai Kurs Tengah BI" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none">Input Kurs Tengah BI</abbr></a>
                                                                </th>
                                                            <?php } else { ?>
                                                                <?php foreach ($kurs as $kur) {
                                                                    $id_kurs = $kur['id_kurs'];
                                                                    $jenis_biaya = "pb";
                                                                ?>
                                                                    <th class="text-center" scope="row" style="color: #5a5c69;">
                                                                        <a href="<?php echo site_url("editkurspb/$id_transaksi/$jenis_biaya/$id_pb/$id_kurs") ?>"><abbr title="Klik untuk edit nilai Kurs Tengah BI" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo number_format($kur['kurs'], 2, ',', '.'); ?></abbr></a>
                                                                    </th>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } else if ($role == 'treasury' && $submit == 0 && $valas_kurs != 'IDR') { ?>
                                                            <?php
                                                            foreach ($nomor as $nomor_kurs) {
                                                                $tanggal_pb = $nomor_kurs['tanggal'];
                                                            }

                                                            foreach ($kurs as $kur1) {
                                                                $id_kurs = $kur1['id_kurs'];
                                                                $jenis_biaya = "pb";
                                                            }
                                                            if(!empty($tanggal_pb)) { ?>
                                                                <?php if(!empty($id_kurs)) { ?>
                                                                    <th class="text-center" scope="row" style="color: #5a5c69;">
                                                                        <a href="<?php echo site_url("editkurspb/$id_transaksi/$jenis_biaya/$id_pb/$id_kurs") ?>"><abbr title="Klik untuk edit nilai Kurs Tengah BI" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo number_format($kur1['kurs'], 2, ',', '.'); ?></abbr></a>
                                                                    </th>
                                                                <?php } else { ?>
                                                                    <th class="text-center" scope="row" style="color: #5a5c69;">
                                                                        <a href="<?php echo site_url("kurspb/$id_transaksi/$jenis_biaya/$id_pb") ?>" class="btn btn-success"><abbr title="Klik untuk menambahkan nilai Kurs Tengah BI" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none">Input Kurs Tengah BI</abbr></a>
                                                                    </th>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                    
                                                        <?php } ?>
                                                        <?php if ($role == 'gs' && $submit == 2) { ?>
                                                            <?php foreach ($kota as $kota) { ?>
                                                                <?php if(!empty($kota)){ ?>
                                                                    <th class="text-center" scope="row" style="color: #5a5c69;"><?php echo $kota['kota'];?></th>
                                                                <?php } else { ?>
                                                                    <th class="text-center" scope="row" style="color: #5a5c69;"></th>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                    
                                                        <?php } ?>

                                                        <?php foreach ($nomor as $nomor) { ?>
                                                            <?php if($nomor['nomor'] == null) { ?>
                                                                <th class="text-center" scope="row" style="color: #5a5c69;"></th>
                                                            <?php } else if($nomor['nomor'] != null) { ?>
                                                                <th class="text-center" scope="row" style="color: #5a5c69;"><?php echo $nomor['nomor'];?></th>
                                                            <?php } ?>

                                                            <?php foreach ($valas as $key => $value) { ?>
                                                                <?php if($nomor['id_pb'] == $value['id_pb']){ ?>
                                                                    <?php if($role == 'admin' && $nomor['tanggal'] == null && $submit == 0 || $role == 'user' && $nomor['tanggal'] == null && $submit == 0 || $role == 'treasury' && $nomor['tanggal'] == null && $submit == 0 && $submit_pjum == 1 || $role == 'treasury' && $nomor['tanggal'] == null && $submit == 0 && $submit_pjum == 4) { ?>
                                                                        <th class="text-center" scope="row" style="color: #5a5c69;">
                                                                            <a href="<?php echo site_url("tanggalpb/$id_pb_tanggal/$id_transaksi/$jenis_biaya") ?>" class="btn btn-success"><abbr title="Klik untuk menambahkan tanggal pembuatan no PB" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none">Input Tanggal Pembuatan No PB</abbr></a>
                                                                        </th>
                                                                    <?php } else if($role == 'treasury' && $nomor['tanggal'] == null && $submit == 1) { ?>
                                                                        <th class="text-center" scope="row" style="color: #5a5c69;"></th>
                                                                    <?php } else if($role == 'admin' && $nomor['tanggal'] != null && $submit == 0 || $role == 'user' && $nomor['tanggal'] != null && $submit == 0 || $role == 'treasury' && $nomor['tanggal'] != null && $submit == 0 && $submit_pjum == 1 || $role == 'treasury' && $nomor['tanggal'] != null && $submit == 0 && $submit_pjum == 4) { ?>
                                                                        <th class="text-center" scope="row" style="color: #5a5c69;">
                                                                            <a href="<?php echo site_url("tanggalpb/$id_pb_tanggal/$id_transaksi/$jenis_biaya") ?>"><abbr title="Klik untuk menambahkan tanggal pembuatan no PB" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo tanggal_indo1($nomor['tanggal']); ?></abbr></a>
                                                                        </th>
                                                                    <?php } else if($role == 'gs') { ?>
                                                                        
                                                                    <?php } else { ?>
                                                                        <th class="text-center" style="color: #5a5c69" scope="row"><?php echo tanggal_indo1($nomor['tanggal']); ?></th>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 mb-4">
                            <div class="card shadow">
                                <div class="card-body">
                                    <?php if ($role == 'admin' || $role == 'user') { ?>
                                        <div class="dropdown mb-3">
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <a class="btn btn-secondary" href="<?php echo $pb ?>">Kembali</a>
                                                <?php if($kirim == 1) { ?>
                                                    <p class="mt-3">Keterangan :</p>
                                                    <div class="col-xl-12 col-lg-12">
                                                        <li style="text-align : left;">
                                                            Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #e74a3b; font-weight: bold;"> MERAH</span> artinya biaya perlu dilakukan revisi
                                                        </li>
                                                        <li style="text-align : left;">
                                                            Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #1cc88a; font-weight: bold;"> HIJAU</span> artinya biaya telah dilakukan revisi
                                                        </li>
                                                        <li style="text-align : left;">
                                                            Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #4e73df; font-weight: bold;"> BIRU</span> artinya biaya telah diubah diluar revisi dari Bagian Treasury
                                                        </li>
                                                    </div>
                                                <?php } ?>
                                            </form>
                                        </div>
                                    <?php } else if ($role == 'treasury') { ?>
                                        <div class="dropdown mb-3">
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <a class="btn btn-secondary" href="<?php echo $pb ?>">Kembali</a>
                                                <p class="mt-3">Keterangan :</p>
                                                <div class="col-xl-12 col-lg-12">
                                                    <li style="text-align : left;">
                                                        Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #e74a3b; font-weight: bold;"> MERAH</span> artinya biaya perlu dilakukan revisi
                                                    </li>
                                                    <li style="text-align : left;">
                                                        Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #1cc88a; font-weight: bold;"> HIJAU</span> artinya biaya telah dilakukan revisi
                                                    </li>
                                                    <li style="text-align : left;">
                                                        Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #4e73df; font-weight: bold;"> BIRU</span> artinya biaya telah diubah diluar revisi dari Bagian Treasury
                                                    </li>
                                                </div>
                                            </form>
                                        </div>
                                    <?php } else if ($role == 'gs') { ?>
                                        <div class="dropdown mb-3">
                                            <a class="btn btn-secondary" href="<?php echo $pb ?>">Kembali</a>
                                        </div>
                                    <?php } ?>
                                    <div style="overflow-x:auto;">
                                        <table id="myTable" class="table table-bordered mb-4">
                                            <thead>
                                                <tr>
                                                    <th rowspan ="2" class="text-center" style="vertical-align: middle; color: #5a5c69;">Baris Excel</th>
                                                    <th rowspan ="2" class="text-center" style="vertical-align: middle; color: #5a5c69;">Tanggal</th>
                                                    <th rowspan ="2" class="text-center" style="vertical-align: middle; color: #5a5c69;">Kategori</th>
                                                    <th rowspan ="2" class="text-center" style="vertical-align: middle; color: #5a5c69;">Status</th>
                                                    <th rowspan ="2" class="text-center" style="vertical-align: middle; color: #5a5c69;">Ref</th>
                                                    <th rowspan ="2" class="text-center" style="vertical-align: middle; color: #5a5c69;">Note</th>
                                                    <th rowspan ="2" class="text-center" style="vertical-align: middle; color: #5a5c69;">Jumlah Personil</th>
                                                    <th colspan ="<?php echo $index?>" class="text-center" style="color: #5a5c69;">Valas</th>
                                                    <?php if ($role == 'gs' || $role == 'treasury' && $submit == 0) { ?>
                                                        
                                                    <?php } else { ?>
                                                        <th rowspan ="2" class="text-center" style="vertical-align: middle; color: #5a5c69;">Note Treasury</th>
                                                    <?php } ?>
                                                    
                                                    <?php if ($role == 'treasury' && $submit == 1) { ?>
                                                        <th rowspan ="2" class="text-center" style="vertical-align: middle; color: #5a5c69;">Aksi</th>
                                                    <?php } else { ?>
                                                        
                                                    <?php } ?>
                                                </tr>
                                                <tr>
                                                    <?php foreach ($kode_valas as $kode_valas) { ?>
                                                        <?php if(!empty($kode_valas['kode_valas'])){ ?>
                                                            <th class="text-center" style="color: #5a5c69;"><?php echo $kode_valas['kode_valas']?></th>
                                                        <?php } else { ?>
                                                            <th class="text-center" style="color: #5a5c69;"></th>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($kategori as $k => $kat) {
                                                    $id_kategori = $kat['id_kategori'];
                                                    $id_pb = $kat['id_pb'];
                                                    $jenis_biaya = "pb";

                                                    $tambah = site_url("treasurypb/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb");
                                                    $hapus = site_url("treasurypb/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb/?aksi=hapus&id_kategori=$id_kategori");
                                                ?>
                                                    <tr>
                                                        <th class="text-center" scope="row" style="color: #5a5c69"><?php echo $kat['baris']?></th>
                                                        <th class="text-center" scope="row" style="color: #5a5c69"><?php echo tanggal_indo($kat['tanggal']) ?></th>
                                                        <th class="text-center" scope="row" style="color: #5a5c69"><?php echo $kat['kategori']?></th>
                                                        <th class="text-center" scope="row" style="color: #5a5c69"><?php echo $kat['status']?></th>
                                                        <th class="text-center" scope="row" style="color: #5a5c69"><?php echo $kat['ref']?></th>
                                                        <th class="text-center" scope="row" style="color: #5a5c69"><?php echo $kat['note']?></th>
                                                        <th class="text-center" scope="row" style="color: #5a5c69"><?php echo $kat['jumlah_personil']?></th>
                                                        <?php foreach ($biaya as $b => $bia) { ?>
                                                            <?php if($kat['id_kategori'] == $bia['id_kategori']){
                                                                $id_biaya = $bia['id_biaya'];
                                                                $id_kategori = $bia['id_kategori'];
                                                            ?>
                                                                <?php if(!($bia['biaya'] == 0)) { ?>
                                                                    <?php if($role == 'admin' && $submit == 0 || $role == 'user' && $submit == 0 || $role == 'treasury' && $submit == 0 && $submit_pjum == 1 || $role == 'treasury' && $submit == 0 && $submit_pjum == 4) { ?>
                                                                        <!-- normal belum dikirim belum diedit -->
                                                                        <?php if ($kat['treasury'] == null && $kat['edited_by'] == null && $kirim == 0) { ?>
                                                                            <th class="text-right" scope="row">
                                                                                <a href="<?php echo site_url("editbiayapb/$id_biaya/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb_tanggal") ?>" style="color: #5a5c69"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?></abbr></a>
                                                                            </th>
                                                                        <!-- normal belum dikirim sudah diedit -->
                                                                        <?php } else if ($kat['treasury'] == null && $kat['edited_by'] != null && $kirim == 0) { ?>
                                                                            <th class="text-right" scope="row">
                                                                                <a href="<?php echo site_url("editbiayapb/$id_biaya/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb_tanggal") ?>" style="color: #5a5c69"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?></abbr></a>
                                                                            </th>
                                                                        <!-- normal sudah dikirim -->
                                                                        <?php } else if ($kat['treasury'] == null && $kat['edited_by'] == null && $kirim == 1) { ?>
                                                                            <th class="text-right" scope="row">
                                                                                <a href="<?php echo site_url("editbiayapb/$id_biaya/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb_tanggal") ?>" style="color: #5a5c69"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?></abbr></a>
                                                                            </th>
                                                                        <!-- telah diubah -->
                                                                        <?php } else if ($kat['treasury'] == null && $kat['edited_by'] != null && $kirim == 1) { ?>
                                                                            <th class="text-right" scope="row">
                                                                                <a href="<?php echo site_url("editbiayapb/$id_biaya/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb_tanggal") ?>" style="color: #4e73df"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?> (Telah diubah)</abbr></a>
                                                                            </th>
                                                                        <!-- perlu revisi -->
                                                                        <?php } else if ($kat['treasury'] != null && $kat['edited_by'] == null && $kirim == 1) { ?>
                                                                            <th class="text-right" scope="row">
                                                                                <a href="<?php echo site_url("editbiayapb/$id_biaya/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb_tanggal") ?>" style="color: #e74a3b"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?> (Perlu revisi)</abbr></a>
                                                                            </th>
                                                                        <!-- telah direvisi -->
                                                                        <?php } else if ($kat['treasury'] != null && $kat['edited_by'] != null && $kirim == 1) { ?>
                                                                            <th class="text-right" scope="row">
                                                                                <a href="<?php echo site_url("editbiayapb/$id_biaya/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb_tanggal") ?>" style="color: #1cc88a"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?> (Telah direvisi)</abbr></a>
                                                                            </th>
                                                                        <?php } ?>
                                                                    <?php } else if ($role == 'treasury' && $submit == 1) { ?>
                                                                        <!-- normal -->
                                                                        <?php if ($kat['treasury'] == null && $kat['edited_by'] == null) { ?>
                                                                            <th class="text-right" scope="row" style="color: #5a5c69">
                                                                                <?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?>
                                                                            </th>
                                                                        <!-- telah diubah -->
                                                                        <?php } else if ($kat['treasury'] == null && $kat['edited_by'] != null) { ?>
                                                                            <th class="text-right" scope="row" style="color: #4e73df">
                                                                                <?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?> (Telah diubah)
                                                                            </th>
                                                                        <!-- perlu revisi -->
                                                                        <?php } else if ($kat['treasury'] != null && $kat['edited_by'] == null) { ?>
                                                                            <th class="text-right" scope="row" style="color: #e74a3b">
                                                                                <?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?> (Perlu revisi)
                                                                            </th>
                                                                        <!-- telah direvisi -->
                                                                        <?php } else if ($kat['treasury'] != null && $kat['edited_by'] != null) { ?>
                                                                            <th class="text-right" scope="row" style="color: #1cc88a">
                                                                                <?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?> (Telah direvisi)
                                                                            </th>
                                                                        <?php } ?>
                                                                    <?php } else if($role == 'gs' && $submit == 2 && $kat['status'] == 'Dibelikan GS') { ?>
                                                                        <th class="text-right" scope="row">
                                                                            <a href="<?php echo site_url("editbiayapb/$id_biaya/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb_tanggal") ?>" style="color: #5a5c69"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?></abbr></a>
                                                                        </th>
                                                                    <?php } else { ?>
                                                                        <th class="text-right" scope="row" style="color: #5a5c69">
                                                                            <?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?>
                                                                        </th>
                                                                    <?php } ?>
                                                                <?php } else { ?>
                                                                    <?php if($role == 'gs' && $submit == 2 && $kat['status'] == 'Dibelikan GS') { ?>
                                                                        <th class="text-right" scope="row">
                                                                            <a href="<?php echo site_url("editbiayapb/$id_biaya/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb_tanggal") ?>" style="color: #5a5c69"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?></abbr></a>
                                                                        </th>
                                                                    <?php } else { ?>
                                                                        <th class="text-center" scope="row" style="color: #5a5c69">
                                                                    <?php } ?>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                        <?php if ($role == 'gs' || $role == 'treasury' && $submit == 0) { ?>
                                                        
                                                        <?php } else { ?>
                                                            <th class="text-center" scope="row" style="color: #5a5c69"><?php echo $kat['treasury']?></th>
                                                        <?php } ?>
                                                        
                                                        <?php if ($role == 'treasury' && $submit == 1) { ?>
                                                            <th class="text-center" scope="row" style="color: #5a5c69">
                                                                <?php if ($kat['treasury'] == null) { ?>
                                                                    <a href="<?php echo $tambah?>" class="btn btn-success btn-block">Tambah Note</a>
                                                                <?php } if ($kat['treasury'] != null) { ?>
                                                                    <a href="<?php echo $tambah?>" class="btn btn-primary btn-block">Edit Note</a>
                                                                    <a href="<?php echo $hapus?>" onclick="return confirm('Yakin akan menghapus Note Treasury?')" class="btn btn-danger btn-block">Hapus Note</a>
                                                                <?php } ?>
                                                            </th>
                                                        <?php } else { ?>

                                                        <?php } ?>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                            <tr>
                                                <th colspan ="7" style="color: #5a5c69">Total Biaya</th>
                                                <?php foreach ($total as $total) {
                                                $biaya = implode("", $total);
                                                $array = array(0,1,2,3,4,5,6,7,8,9,'.');
                                                $simbol = str_replace($array,'', $biaya);
                                                $bia = floatval($biaya);
                                                ?>
                                                    <?php if(!empty($bia)){ ?>
                                                        <th class="text-right" scope="row" style="color: #5a5c69">
                                                            <?php echo $simbol; echo number_format($bia, 2, ',', '.');?>
                                                        </th>
                                                    <?php } else { ?>
                                                        <th class="text-right" scope="row"></th>
                                                    <?php } ?>
                                                <?php } ?>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            <footer id="footer" class="footer text-center">
                <div class="copyright">
                    Copyright &copy; <strong><span>MIS 2023</span></strong>.
                </div>
            </footer><!-- End Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#top">
        <i class="fas fa-angle-up"></i>
    </a>

    <style>
        body {
        position: relative;
        }
        .section {
        height: 100vh;
        background: #dedede;
        margin-bottom: 20px;
        font-size: 100px;
        }

        .scroll-container {
        position: absolute;
        top: 0;
        right:0;
        height: 100%;
        }

        // to hide the button when page first loaded
        .scroll-container:before {
        content: '';
        display: block;
        height: 100vh;
        pointer-events: none;
        }

        // fixed to right bottom of page
        .scroll-container a {
        position: sticky;
        top: 88vh;
        cursor: pointer;
        font-size: 20px;
        }
    </style>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Logout?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Pilih "Logout" jika anda ingin keluar dari session ini</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batalkan</button>
                    <a class="btn btn-success" href="<?php echo site_url('logout')?>/">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Upload Excel</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="<?php echo site_url("importpb/$jenis_biaya/$id_transaksi/$id_pb") ?>" method="POST" enctype="multipart/form-data"><!-- enctype="multipart/form-data" -->
                    <div class="modal-body">
                        <input type="file" class="form-control" name="file_excel_pb" accept=".xls,.xlsx" required>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Kembali</button>
                        <button class="btn btn-success" type="submit" name="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?php echo base_url('admin')?>/js/jquery-3.7.0.js"></script>
    <script src="<?php echo base_url('admin')?>/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function(){
        $('#myTable').DataTable({
            "pageLength":-1,
            "lengthMenu": [ [1, 5, 10, 50, 100, -1], [1, 5, 10, 50, 100, "All"] ],
        });
    });
    </script>

    <script src="<?php echo base_url('admin')?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?php echo base_url('admin')?>/js/sb-admin-2.js"></script>
</body>
</html>