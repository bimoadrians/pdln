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

                    <!-- <li class="nav-item active">
                        <a class="nav-link collapsed" href="javascript:void(0)" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                            <i class="fa-solid fa-credit-card"></i>
                            <span>PJUM</span>
                        </a>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item" href="<?php echo $pjum?>">Data
                                    PJUM</a>
                            </div>
                        </div>
                    </li> -->
                <?php } else { ?>

                <?php } ?>

                <?php if ($role == 'treasury' && $submit_pjum == 1) { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo $pjum?>"><i class="fa-solid fa-credit-card"></i><span> PJUM</span></a>
                    </li>

                    <!-- <li class="nav-item active">
                        <a class="nav-link collapsed" href="javascript:void(0)" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                            <i class="fa-solid fa-credit-card"></i>
                            <span>PJUM</span>
                        </a>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item" href="<?php echo $pjum?>">Data
                                    PJUM</a>
                            </div>
                        </div>
                    </li> -->
                <?php } else { ?>

                <?php } ?>

                <?php if ($role == 'gs' && $submit_pjum > 1) { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo $pjum?>"><i class="fa-solid fa-credit-card"></i><span> PJUM</span></a>
                    </li>

                    <!-- <li class="nav-item active">
                        <a class="nav-link collapsed" href="javascript:void(0)" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                            <i class="fa-solid fa-credit-card"></i>
                            <span>PJUM</span>
                        </a>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item" href="<?php echo $pjum?>">Data
                                    PJUM</a>
                            </div>
                        </div>
                    </li> -->
                <?php } else { ?>

                <?php } ?>

            <?php } if (empty($cekpb)) { ?>

            <?php } else { ?>
                <!-- Nav Item - Utilities Collapse Menu -->
                <?php if ($role == 'admin' || $role == 'user' || $role == 'treasury' && $submit_pb == 0) { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo $pb?>"><i class="fa-solid fa-sack-dollar"></i><span> PB</span></a>
                    </li>

                    <!-- <li class="nav-item active">
                        <a class="nav-link collapsed" href="javascript:void(0)" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
                            <i class="fa-solid fa-sack-dollar"></i>
                            <span>PB</span>
                        </a>
                        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item" href="<?php echo $pb?>">Data
                                    PB</a>
                            </div>
                        </div>
                    </li> -->
                <?php } else { ?>

                <?php } ?>
                
                <!-- Nav Item - Utilities Collapse Menu -->
                <?php if ($role == 'treasury' && $submit_pb == 1) { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo $pb?>"><i class="fa-solid fa-sack-dollar"></i><span> PB</span></a>
                    </li>

                    <!-- <li class="nav-item active">
                        <a class="nav-link collapsed" href="javascript:void(0)" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
                            <i class="fa-solid fa-sack-dollar"></i>
                            <span>PB</span>
                        </a>
                        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item" href="<?php echo $pb?>">Data
                                    PB</a>
                            </div>
                        </div>
                    </li> -->
                <?php } else { ?>

                <?php } ?>

                <!-- Nav Item - Utilities Collapse Menu -->
                <?php if ($role == 'gs' && $submit_pb > 1) { ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo $pb?>"><i class="fa-solid fa-sack-dollar"></i><span> PB</span></a>
                    </li>

                    <!-- <li class="nav-item active">
                        <a class="nav-link collapsed" href="javascript:void(0)" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
                            <i class="fa-solid fa-sack-dollar"></i>
                            <span>PB</span>
                        </a>
                        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item" href="<?php echo $pb?>">Data
                                    PB</a>
                            </div>
                        </div>
                    </li> -->
                <?php } else { ?>

                <?php } ?>

                <?php if ($role == 'gs' && $solo != 'Surakarta' && $submit_pb > 1) { ?>
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
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">
                                <?php echo($header)?>
                            </h1>
                        </div>
                    </form>

                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 large"><?php echo session()->get('akun_email')?></span>
                        </li>
                    </ul>
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
                                                        <th class="text-center">ID Transaksi</th>
                                                        <?php if ($role == 'treasury' && $submit == 1 && $valas_kurs != 'IDR') { ?>
                                                            <th rowspan ="2" class="text-center" style="vertical-align: middle;">Kurs</th>
                                                        <?php } else { ?>
                                                    
                                                        <?php } ?>
                                                        <?php if ($role == 'gs' && $submit == 2) { ?>
                                                            <th class="text-center" style="vertical-align: middle;">Berangkat dari Kota</th>
                                                        <?php } else { ?>
                                                    
                                                        <?php } ?>
                                                        <th class="text-center">No PB</th>
                                                        <?php if ($role != 'gs') { ?>
                                                            <th class="text-center">Tanggal Pembuatan No PB</th>
                                                        <?php } ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <th class="text-center" scope="row"><a href="<?php echo site_url("detailtransaksi/$id_transaksi")?>"><?php echo $id['id_transaksi']; echo("/"); echo("Perjalanan Dinas Luar Negeri"); echo("/"); echo $id['jumlah_personil']; echo("/"); foreach ($neg as $neg) : echo $neg['negara_tujuan']; endforeach ?></a></th>
                                                        <?php if ($role == 'treasury' && $submit == 1 && $valas_kurs != 'IDR') { ?>
                                                            <?php if(empty($kurs)) { ?>
                                                                <th class="text-center" scope="row">
                                                                    <a href="javascript:void(0)" class="btn btn-success" data-toggle="modal" data-target="#kurs"><abbr title="Klik untuk menambahkan nilai Kurs Tengah BI" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none">Input Kurs Tengah BI</abbr></a>
                                                                </th>
                                                                <!-- <th class="text-center" scope="row" style="color: #5a5c69"></th> -->
                                                            <?php } else { ?>
                                                                <?php foreach ($kurs as $kur) { ?>
                                                                    <th class="text-center" scope="row">
                                                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#kurs<?php echo $kur['id_kurs']; ?>"><abbr title="Klik untuk edit nilai Kurs Tengah BI" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo number_format($kur['kurs'], 2, ',', '.'); ?></abbr></a>
                                                                    </th>
                                                                    <!-- <th class="text-center" scope="row" style="color: #5a5c69">
                                                                        <?php echo number_format($kur['kurs'], 2, ',', '.'); ?>
                                                                    </th> -->
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                    
                                                        <?php } ?>
                                                        <?php if ($role == 'gs' && $submit == 2) { ?>
                                                            <?php foreach ($kota as $kota) { ?>
                                                                <?php if(!empty($kota)){ ?>
                                                                    <th class="text-center"><?php echo $kota['kota'];?></th>
                                                                <?php } else { ?>
                                                                    <th class="text-center" scope="row"></th>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                    
                                                        <?php } ?>

                                                        <?php foreach ($nomor as $nomor) { ?>
                                                            <?php if($nomor['nomor'] == null) { ?>
                                                                <th class="text-center" scope="row"></th>
                                                            <?php } else if($nomor['nomor'] != null) { ?>
                                                                <th class="text-center"><?php echo $nomor['nomor'];?></th>
                                                            <?php } ?>

                                                            <?php foreach ($valas1 as $key => $value) { ?>
                                                                <?php if($nomor['id_pb'] == $value['id_pb']){ ?>
                                                                    <?php if($role == 'admin' && $nomor['tanggal'] == null || $role == 'user' && $nomor['tanggal'] == null || $role == 'treasury' && $nomor['tanggal'] == null && $submit == 0) { ?>
                                                                        <th class="text-center" scope="row">
                                                                            <a href="javascript:void(0)" class="btn btn-success" data-toggle="modal" data-target="#tanggal<?php echo $nomor['id_pb']; echo $value['id_pb']; ?>"><abbr title="Klik untuk menambahkan tanggal pembuatan no PB" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none">Input Tanggal Pembuatan No PB</abbr></a>
                                                                        </th>
                                                                    <?php } else if($role == 'treasury' && $nomor['tanggal'] == null && $submit == 1) { ?>
                                                                        <th class="text-center" scope="row"></th>
                                                                    <?php } else if($role == 'admin' && $nomor['tanggal'] != null && $submit == 0 && $submit_pjum == 0 || $role == 'user' && $nomor['tanggal'] != null && $submit == 0 && $submit_pjum == 0 || $role == 'treasury' && $nomor['tanggal'] != null && $submit == 0) { ?>
                                                                        <th class="text-center" scope="row">
                                                                            <a href="javascript:void(0)" data-toggle="modal" data-target="#tanggal<?php echo $nomor['id_pb']; echo $value['id_pb']; ?>"><abbr title="Klik untuk menambahkan tanggal pembuatan no PB" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo tanggal_indo1($nomor['tanggal']); ?></abbr></a>
                                                                        </th>
                                                                    <?php } else if($role == 'admin' && $nomor['tanggal'] != null && $submit == 0 && $submit_pjum != 0 || $role == 'user' && $nomor['tanggal'] != null && $submit == 0 && $submit_pjum != 0) { ?>
                                                                        <th class="text-center" style="color: #5a5c69" scope="row"><?php echo tanggal_indo1($nomor['tanggal']); ?></th>
                                                                    <?php } else if($role == 'admin' && $nomor['tanggal'] != null && $submit != 0 || $role == 'user' && $nomor['tanggal'] != null && $submit != 0) { ?>
                                                                        <th class="text-center" style="color: #5a5c69" scope="row"><?php echo tanggal_indo1($nomor['tanggal']); ?></th>
                                                                    <?php } else if($role == 'treasury' && $nomor['tanggal'] != null && $submit == 1) { ?>
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
                                    <?php if ($role == 'admin') { ?>
                                        <div class="dropdown mb-3">
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <a class="btn btn-secondary" href="<?php echo $pb ?>">Kembali</a>
                                                <?php if($kirim == 1) { ?>
                                                    <p class="mt-3">Keterangan :</p>
                                                    <li style="text-align : left;">
                                                        Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #e74a3b; font-weight: bold;"> MERAH</span> artinya biaya perlu dilakukan revisi
                                                    </li>
                                                    <li style="text-align : left;">
                                                        Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #1cc88a; font-weight: bold;"> HIJAU</span> artinya biaya telah dilakukan revisi
                                                    </li>
                                                    <li style="text-align : left;">
                                                        Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #4e73df; font-weight: bold;"> BIRU</span> artinya biaya telah diubah diluar revisi dari Bagian Treasury
                                                    </li>
                                                <?php } ?>
                                                <!-- <a class="btn btn-info" href="<php echo site_url("exportpb/$jenis_biaya/$id_transaksi/$id_pb") ?>"><i class="fa-solid fa-file-download"></i> Export Excel</a> -->
                                                <?php if ($submit == 0) { ?>
                                                    <!-- <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-file-arrow-down"></i>
                                                        Download Format Excel
                                                    </button> -->
                                                    <!-- <div class="dropdown-menu animated--fade-in" aria-labelledby="dropdownMenuButton" style=""> -->
                                                        <!-- "<php echo base_url('Format PB Perjalanan Dinas Luar Negeri.xlsx')?>" -->
                                                        <!-- <a class="dropdown-item" href="<?php echo base_url()?>/formatExcel/Format PB Perjalanan Dinas Luar Negeri.xlsx"><i class="fa-solid fa-file-download"></i> Download Format Excel (.xlsx)</a>
                                                        <a class="dropdown-item" href="<?php echo base_url()?>/formatExcel/Format PB Perjalanan Dinas Luar Negeri.xls"><i class="fa-solid fa-file-download"></i> Download Format Excel (.xls)</a> -->
                                                    <!-- </div> -->
                                                    <!-- <a class="btn btn-success" href="javascript:void(0)" data-toggle="modal" data-target="#uploadExcel"><i class="fa-solid fa-file-upload"></i> Upload File Excel</a> -->
                                                <?php  } else { ?>

                                                <?php } ?>
                                            </form>
                                        </div>
                                    <?php } else if ($role == 'user') { ?>
                                        <div class="dropdown mb-3">
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <a class="btn btn-secondary" href="<?php echo $pb ?>">Kembali</a>
                                                <?php if($kirim == 1) { ?>
                                                    <p class="mt-3">Keterangan :</p>
                                                    <li style="text-align : left;">
                                                        Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #e74a3b; font-weight: bold;"> MERAH</span> artinya biaya perlu dilakukan revisi
                                                    </li>
                                                    <li style="text-align : left;">
                                                        Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #1cc88a; font-weight: bold;"> HIJAU</span> artinya biaya telah dilakukan revisi
                                                    </li>
                                                    <li style="text-align : left;">
                                                    Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #4e73df; font-weight: bold;"> BIRU</span> artinya biaya telah diubah diluar revisi dari Bagian Treasury
                                                    </li>
                                                <?php } ?>
                                                <!-- <a class="btn btn-info" href="<php echo site_url("exportpb/$jenis_biaya/$id_transaksi/$id_pb") ?>"><i class="fa-solid fa-file-download"></i> Export Excel</a> -->
                                                <?php if ($submit == 0) { ?>
                                                    <!-- <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-file-arrow-down"></i>
                                                        Download Format Excel
                                                    </button> -->
                                                    <!-- <div class="dropdown-menu animated--fade-in" aria-labelledby="dropdownMenuButton" style=""> -->
                                                        <!-- "<php echo base_url('Format PB Perjalanan Dinas Luar Negeri.xlsx')?>" -->
                                                        <!-- <a class="dropdown-item" href="<?php echo base_url()?>/formatExcel/Format PB Perjalanan Dinas Luar Negeri.xlsx"><i class="fa-solid fa-file-download"></i> Download Format Excel (.xlsx)</a>
                                                        <a class="dropdown-item" href="<?php echo base_url()?>/formatExcel/Format PB Perjalanan Dinas Luar Negeri.xls"><i class="fa-solid fa-file-download"></i> Download Format Excel (.xls)</a> -->
                                                    <!-- </div> -->
                                                    <!-- <a class="btn btn-success" href="javascript:void(0)" data-toggle="modal" data-target="#uploadExcel"><i class="fa-solid fa-file-upload"></i> Upload File Excel</a> -->
                                                <?php  } else { ?>

                                                <?php } ?>
                                            </form>
                                        </div>
                                    <?php } else if ($role == 'treasury') { ?>
                                        <div class="dropdown mb-3">
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <a class="btn btn-secondary" href="<?php echo $pb ?>">Kembali</a>
                                                <p class="mt-3">Keterangan :</p>
                                                <li style="text-align : left;">
                                                    Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #e74a3b; font-weight: bold;"> MERAH</span> artinya biaya perlu dilakukan revisi
                                                </li>
                                                <li style="text-align : left;">
                                                    Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #1cc88a; font-weight: bold;"> HIJAU</span> artinya biaya telah dilakukan revisi
                                                </li>
                                                <li style="text-align : left;">
                                                Jika <span style="color: #5a5c69; font-weight: bold;"> BIAYA</span> berwarna <span style="color: #4e73df; font-weight: bold;"> BIRU</span> artinya biaya telah diubah diluar revisi dari Bagian Treasury
                                                </li>
                                                <!-- <a class="btn btn-info" href="<?php echo site_url("exportpb/$jenis_biaya/$id_transaksi/$id_pb") ?>"><i class="fa-solid fa-file-download"></i> Export Excel</a> -->
                                                <?php if ($submit == 1) { ?>
                                                    <!-- <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-file-arrow-down"></i>
                                                        Download Format Excel
                                                    </button> -->
                                                    <!-- <div class="dropdown-menu animated--fade-in" aria-labelledby="dropdownMenuButton" style=""> -->
                                                        <!-- "<php echo base_url('Format PB Perjalanan Dinas Luar Negeri.xlsx')?>" -->
                                                        <!-- <a class="dropdown-item" href="<?php echo base_url()?>/formatExcel/Format PB Perjalanan Dinas Luar Negeri.xlsx"><i class="fa-solid fa-file-download"></i> Download Format Excel (.xlsx)</a>
                                                        <a class="dropdown-item" href="<?php echo base_url()?>/formatExcel/Format PB Perjalanan Dinas Luar Negeri.xls"><i class="fa-solid fa-file-download"></i> Download Format Excel (.xls)</a> -->
                                                    <!-- </div> -->
                                                    <!-- <a class="btn btn-success" href="javascript:void(0)" data-toggle="modal" data-target="#uploadExcel"><i class="fa-solid fa-file-upload"></i> Upload File Excel</a> -->
                                                <?php  } else { ?>

                                                <?php } ?>
                                            </form>
                                        </div>
                                    <?php } else if ($role == 'gs') { ?>
                                        <div class="dropdown mb-3">
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <a class="btn btn-secondary" href="<?php echo $pb ?>">Kembali</a>
                                                <!-- <a class="btn btn-info" href="<php echo site_url("exportpb/$jenis_biaya/$id_transaksi/$id_pb") ?>"><i class="fa-solid fa-file-download"></i> Export Excel</a> -->
                                                <?php if ($submit == 2) {
                                                ?>
                                                    <!-- <a class="btn btn-success" href="javascript:void(0)" data-toggle="modal" data-target="#uploadExcel"><i class="fa-solid fa-file-upload"></i> Upload File Excel</a> -->
                                                <?php  } else { ?>

                                                <?php } ?>
                                            </form>
                                        </div>
                                    <?php } ?>
                                    <table id="myTable" class="table table-bordered mb-4">
                                        <thead>
                                            <tr>
                                                <th rowspan ="2" class="col-1 text-center" style="vertical-align: middle;">Baris Excel</th>
                                                <th rowspan ="2" class="col-2 text-center" style="vertical-align: middle;">Tanggal</th>
                                                <th rowspan ="2" class="col-1 text-center" style="vertical-align: middle;">Kategori</th>
                                                <th rowspan ="2" class="col-1 text-center" style="vertical-align: middle;">Status</th>
                                                <th rowspan ="2" class="col-1 text-center" style="vertical-align: middle;">Ref</th>
                                                <th rowspan ="2" class="col-1 text-center" style="vertical-align: middle;">Note</th>
                                                <th rowspan ="2" class="col-1 text-center" style="vertical-align: middle;">Jumlah Personil</th>
                                                <th colspan ="<?php echo $index?>" class="col-1 text-center">Valas</th>
                                                <?php if ($role == 'gs') { ?>
                                                    
                                                <?php } else { ?>
                                                    <th rowspan ="2" colspan ="1" class="col-1 text-center" style="vertical-align: middle;">Note Treasury</th>
                                                <?php } ?>
                                                
                                                <?php if ($role == 'treasury' && $submit == 1) { ?>
                                                    <th rowspan ="2" colspan ="1" class="col-1 text-center" style="vertical-align: middle;">Aksi</th>
                                                <?php } else { ?>
                                                    
                                                <?php } ?>
                                            </tr>
                                            <tr>
                                                <?php foreach ($kode_valas as $kode_valas) { ?>
                                                    <?php if(!empty($kode_valas['kode_valas'])){ ?>
                                                        <th class="text-center"><?php echo $kode_valas['kode_valas']?></th>
                                                    <?php } else { ?>
                                                        <th class="text-right" scope="row"></th>
                                                    <?php } ?>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($kategori as $k => $kat) {
                                                $id_kategori = $kat['id_kategori'];
                                                $id_transaksi = $kat['id_transaksi'];
                                                $id_pb = $kat['id_pb'];
                                                $jenis_biaya = "pb";

                                                $tambah = site_url("treasurypb/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb");
                                                $hapus = site_url("treasurypb/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb/?aksi=hapus&id_kategori=$id_kategori");
                                            ?>
                                                <tr>
                                                    <th class="text-center" scope="row"><?php echo $kat['baris']?></th>
                                                    <th class="text-center" scope="row"><?php echo tanggal_indo($kat['tanggal']) ?></th>
                                                    <th class="text-center" scope="row"><?php echo $kat['kategori']?></th>
                                                    <th class="text-center" scope="row"><?php echo $kat['status']?></th>
                                                    <th class="text-center" scope="row"><?php echo $kat['ref']?></th>
                                                    <th class="text-center" scope="row"><?php echo $kat['note']?></th>
                                                    <th class="text-center" scope="row"><?php echo $kat['jumlah_personil']?></th>
                                                    <?php foreach ($biaya as $b => $bia) { ?>
                                                        <?php if($kat['id_kategori'] == $bia['id_kategori']){ ?>
                                                            <?php if(!($bia['biaya'] == 0)) { ?>
                                                                <?php if($role == 'admin' && $submit == 0 && $submit_pjum == 0 || $role == 'user' && $submit == 0 && $submit_pjum == 0 || $role == 'treasury' && $submit == 0) { ?>
                                                                    <!-- normal belum dikirim belum diedit -->
                                                                    <?php if ($kat['treasury'] == null && $kat['edited_by'] == null && $kirim == 0) { ?>
                                                                        <th class="text-right" scope="row">
                                                                            <a href="javascript:void(0)" style="color: #5a5c69" data-toggle="modal" data-target="#biaya<?php echo $bia['id_biaya']; echo $kat['id_kategori']; ?>"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?></abbr></a>
                                                                        </th>
                                                                    <!-- normal belum dikirim sudah diedit -->
                                                                    <?php } else if ($kat['treasury'] == null && $kat['edited_by'] != null && $kirim == 0) { ?>
                                                                        <th class="text-right" scope="row">
                                                                            <a href="javascript:void(0)" style="color: #5a5c69" data-toggle="modal" data-target="#biaya<?php echo $bia['id_biaya']; echo $kat['id_kategori']; ?>"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?></abbr></a>
                                                                        </th>
                                                                    <!-- normal sudah dikirim -->
                                                                    <?php } else if ($kat['treasury'] == null && $kat['edited_by'] == null && $kirim == 1) { ?>
                                                                        <th class="text-right" scope="row">
                                                                            <a href="javascript:void(0)" style="color: #5a5c69" data-toggle="modal" data-target="#biaya<?php echo $bia['id_biaya']; echo $kat['id_kategori']; ?>"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?></abbr></a>
                                                                        </th>
                                                                    <!-- telah diubah -->
                                                                    <?php } else if ($kat['treasury'] == null && $kat['edited_by'] != null && $kirim == 1) { ?>
                                                                        <th class="text-right" scope="row">
                                                                            <a href="javascript:void(0)" style="color: #4e73df" data-toggle="modal" data-target="#biaya<?php echo $bia['id_biaya']; echo $kat['id_kategori']; ?>"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?> (Telah diubah)</abbr></a>
                                                                        </th>
                                                                    <!-- perlu revisi -->
                                                                    <?php } else if ($kat['treasury'] != null && $kat['edited_by'] == null && $kirim == 1) { ?>
                                                                        <th class="text-right" scope="row">
                                                                            <a href="javascript:void(0)" style="color: #e74a3b" data-toggle="modal" data-target="#biaya<?php echo $bia['id_biaya']; echo $kat['id_kategori']; ?>"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?> (Perlu revisi)</abbr></a>
                                                                        </th>
                                                                    <!-- telah direvisi -->
                                                                    <?php } else if ($kat['treasury'] != null && $kat['edited_by'] != null && $kirim == 1) { ?>
                                                                        <th class="text-right" scope="row">
                                                                            <a href="javascript:void(0)" style="color: #1cc88a" data-toggle="modal" data-target="#biaya<?php echo $bia['id_biaya']; echo $kat['id_kategori']; ?>"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?> (Telah direvisi)</abbr></a>
                                                                        </th>
                                                                    <?php } ?>
                                                                <?php } else if($role == 'gs' && $submit == 2) { ?>
                                                                    <th class="text-right" scope="row">
                                                                        <a href="javascript:void(0)" style="color: #5a5c69" data-toggle="modal" data-target="#biaya<?php echo $bia['id_biaya']; echo $kat['id_kategori']; ?>"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?></abbr></a>
                                                                    </th>
                                                                <?php } else { ?>
                                                                    <th class="text-right" scope="row" style="color: #5a5c69">
                                                                        <?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?>
                                                                    </th>
                                                                <?php } ?>
                                                            <?php } else { ?>
                                                                <?php if($role == 'gs' && $submit == 2) { ?>
                                                                    <th class="text-right" scope="row">
                                                                        <a href="javascript:void(0)" style="color: #5a5c69" data-toggle="modal" data-target="#biaya<?php echo $bia['id_biaya']; echo $kat['id_kategori']; ?>"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?></abbr></a>
                                                                    </th>
                                                                <?php } else { ?>
                                                                    <th class="text-right" scope="row"></th>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <?php if ($role == 'gs') { ?>
                                                    
                                                    <?php } else { ?>
                                                        <th class="text-center" scope="row"><?php echo $kat['treasury']?></th>
                                                    <?php } ?>
                                                    
                                                    <?php if ($role == 'treasury' && $submit == 1) { ?>
                                                        <th class="text-center" scope="row">
                                                            <a href="<?php echo $tambah?>" class="btn btn-success btn-block">Tambah Note</a>
                                                            <a href="<?php echo $tambah?>" class="btn btn-primary btn-block">Edit Note</a>
                                                            <a href="<?php echo $hapus?>" onclick="return confirm('Yakin akan menghapus Note Treasury?')" class="btn btn-danger btn-block">Hapus Note</a>
                                                        </th>
                                                    <?php } else { ?>

                                                    <?php } ?>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                        <tr>
                                            <th colspan ="7">Total Biaya</th>
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
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

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

    <?php foreach ($nomor1 as $nomor) { ?>
        <?php foreach ($valas1 as $key => $value) {
            if ($nomor['id_pb'] == $value['id_pb']) {
                $id_valas = $value['id_valas'];
                $kode_valas = $value['kode_valas'];
                $id_transaksi = session()->get('id_transaksi');
            }
        ?>
            <div class="modal fade" id="tanggal<?php echo $nomor['id_pb']; echo $value['id_pb']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Input Tanggal Pembuatan No PB</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <form action="<?php echo site_url("tanggalpb/$id_pb_tanggal/$id_transaksi/$jenis_biaya") ?>" method="post" enctype="multipart/form-data">
                            <div class="modal-body">
                                Tanggal Pembuatan No PB
                                <input required class="form-control" type="date" name="tanggal" id="input_tanggal" value="<?php echo(isset($tanggal1)) ? $tanggal1 : $nomor['tanggal'] ?>">
                                <input name="nomor" type="hidden" value="<?php echo(isset($nomo)) ? $nomo : $nomor['nomor'] ?>">
                                <input name="id_valas" type="hidden" value="<?php echo(isset($vala1)) ? $vala1 : $value['id_valas']  ?>">
                                <input name="kode_valas" type="hidden" value="<?php echo(isset($vala)) ? $vala : $value['kode_valas']  ?>">
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Kembali</button>
                                <button class="btn btn-success" type="submit" name="submit">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

    <?php foreach ($kategori as $b => $kat) { 
        $id_kategori = $kat['id_kategori'];
    ?>
        <?php foreach ($biaya1 as $b => $bia) { 
            $id_biaya = $bia['id_biaya'];
            $id_valas = $bia['id_valas'];
            $kode_valas = $bia['kode_valas'];
        ?>
            <?php if(empty($kurs)) { ?>
                <?php foreach ($nomor1 as $nomor) { ?>
                    <div class="modal fade" id="kurs" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Input Nilai Kurs Tengah BI</h5>
                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <form action="<?php echo site_url("kurspb/$id_transaksi/$jenis_biaya/$id_pb") ?>" method="POST" enctype="multipart/form-data"><!-- enctype="multipart/form-data" -->
                                    <div class="modal-body">
                                        <div class="row col-xl-12 col-lg-12">
                                            Tanggal Kurs
                                            <input required class="form-control" type="date" name="tanggal" id="input_tanggal" value="<?php echo(isset($tanggal1)) ? $tanggal1 : $nomor['tanggal']  ?>">
                                        </div>
                                        <div class="row col-xl-12 col-lg-12 mt-3">
                                            Kurs Tengah BI
                                            <input required type="text" autocomplete="off" class="form-control" name="kurs" id="currency-field" data-type="currency">
                                            <input name="id_valas" type="hidden" value="<?php echo(isset($vala1)) ? $vala1 : $id_valas  ?>">
                                            <input name="kode_valas" type="hidden" value="<?php echo(isset($vala)) ? $vala : $kode_valas  ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Kembali</button>
                                        <button class="btn btn-success" type="submit" name="submit">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else {?>
                <?php foreach ($kurs as $k => $kur) { 
                    $id_kurs = $kur['id_kurs'];
                ?>
                    <div class="modal fade" id="kurs<?php echo $kur['id_kurs']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Edit Nilai Kurs Tengah BI</h5>
                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <form action="<?php echo site_url("editkurspb/$id_transaksi/$jenis_biaya/$id_pb/$id_kurs") ?>" method="POST" enctype="multipart/form-data"><!-- enctype="multipart/form-data" -->
                                    <div class="modal-body">
                                        <div class="row col-xl-12 col-lg-12">
                                            Tanggal Kurs
                                            <input required class="form-control" type="date" name="tanggal" id="input_tanggal" value="<?php echo(isset($tanggal1)) ? $tanggal1 : $kur['tanggal'] ?>">
                                        </div>
                                        <div class="row col-xl-12 col-lg-12 mt-3">
                                            Kurs Tengah BI
                                            <input required type="text" autocomplete="off" class="form-control" name="kurs" id="currency-field" data-type="currency">
                                            <input name="id_valas" type="hidden" value="<?php echo(isset($vala1)) ? $vala1 : $id_valas  ?>">
                                            <input name="kode_valas" type="hidden" value="<?php echo(isset($vala)) ? $vala : $kode_valas  ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Kembali</button>
                                        <button class="btn btn-success" type="submit" name="submit">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>

            <div class="modal fade" id="biaya<?php echo $bia['id_biaya']; echo $kat['id_kategori']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Edit Biaya PB</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <form action="<?php echo site_url("editbiayapb/$id_biaya/$id_kategori/$id_transaksi/$jenis_biaya/$id_pb") ?>" method="post" enctype="multipart/form-data">
                            <div class="modal-body">
                                Biaya
                                <input type="text" autocomplete="off" class="form-control" name="biaya" id="currency-field" value="<?php echo $bia['simbol'] ?><?php echo(isset($biaya2)) ? $biaya2 : number_format($bia['biaya'], 2, ',', '.')  ?>" data-type="currency" placeholder="<?php echo $bia['simbol'] ?>">
                            </div>
                            <?php if($role == 'treasury') { ?>
                                <script>
                                    $("input[data-type='currency']").on({
                                        keyup: function() {
                                        formatCurrency($(this));
                                        },
                                        blur: function() { 
                                        formatCurrency($(this), "blur");
                                        }
                                    });

                                    function formatNumber(n) {
                                    // format number 1000000 to 1,234,567
                                    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                                    }

                                    function formatCurrency(input, blur) {
                                    // appends $ to value, validates decimal side
                                    // and puts cursor back in right position.
                                    
                                    // get input value
                                    var input_val = input.val();
                                    
                                    // don't validate empty input
                                    if (input_val === "") { return; }
                                    
                                    // original length
                                    var original_len = input_val.length;

                                    // initial caret position 
                                    var caret_pos = input.prop("selectionStart");
                                        
                                    // check for decimal
                                    if (input_val.indexOf(",") >= 0) {

                                        // get position of first decimal
                                        // this prevents multiple decimals from
                                        // being entered
                                        var decimal_pos = input_val.indexOf(",");

                                        // split number by decimal point
                                        var left_side = input_val.substring(0, decimal_pos);
                                        var right_side = input_val.substring(decimal_pos);

                                        // add commas to left side of number
                                        left_side = formatNumber(left_side);

                                        // validate right side
                                        right_side = formatNumber(right_side);
                                        
                                        // On blur make sure 2 numbers after decimal
                                        if (blur === "blur") {
                                        right_side += "00";
                                        }
                                        
                                        // Limit decimal to only 2 digits
                                        right_side = right_side.substring(0, 2);

                                        // join number by .
                                        input_val = left_side + "," + right_side;
                                        // if (<?php echo $role == 'treasury'; ?>) {
                                        //     input_val = left_side + "." + right_side;
                                        // } else {
                                        //     input_val = "<?php echo $bia['simbol'] ?>" + left_side + "." + right_side;
                                        // }

                                    } 
                                    else {
                                        // no decimal entered
                                        // add commas to number
                                        // remove all non-digits
                                        input_val = formatNumber(input_val);
                                        // if (<?php echo $role == 'treasury'; ?>) {
                                        //     input_val = input_val;
                                        // } else {
                                        //     input_val = "<?php echo $bia['simbol'] ?>" + input_val;
                                        // }
                                        
                                        // final formatting
                                        if (blur === "blur") {
                                        input_val += "";
                                        }
                                    }
                                    
                                    // send updated string to input
                                    input.val(input_val);

                                    // put caret back in the right position
                                    var updated_len = input_val.length;
                                    caret_pos = updated_len - original_len + caret_pos;
                                    input[0].setSelectionRange(caret_pos, caret_pos);
                                    }
                                </script>
                            <?php } else {?>
                                <script>
                                    $("input[data-type='currency']").on({
                                        keyup: function() {
                                        formatCurrency($(this));
                                        },
                                        blur: function() { 
                                        formatCurrency($(this), "blur");
                                        }
                                    });

                                    function formatNumber(n) {
                                    // format number 1000000 to 1,234,567
                                    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                                    }

                                    function formatCurrency(input, blur) {
                                    // appends $ to value, validates decimal side
                                    // and puts cursor back in right position.
                                    
                                    // get input value
                                    var input_val = input.val();
                                    
                                    // don't validate empty input
                                    if (input_val === "") { return; }
                                    
                                    // original length
                                    var original_len = input_val.length;

                                    // initial caret position 
                                    var caret_pos = input.prop("selectionStart");
                                        
                                    // check for decimal
                                    if (input_val.indexOf(",") >= 0) {

                                        // get position of first decimal
                                        // this prevents multiple decimals from
                                        // being entered
                                        var decimal_pos = input_val.indexOf(",");

                                        // split number by decimal point
                                        var left_side = input_val.substring(0, decimal_pos);
                                        var right_side = input_val.substring(decimal_pos);

                                        // add commas to left side of number
                                        left_side = formatNumber(left_side);

                                        // validate right side
                                        right_side = formatNumber(right_side);
                                        
                                        // On blur make sure 2 numbers after decimal
                                        if (blur === "blur") {
                                        right_side += "00";
                                        }
                                        
                                        // Limit decimal to only 2 digits
                                        right_side = right_side.substring(0, 2);

                                        // join number by .
                                        input_val = "<?php echo $bia['simbol'] ?>" + left_side + "," + right_side;

                                    } 
                                    else {
                                        // no decimal entered
                                        // add commas to number
                                        // remove all non-digits
                                        input_val = formatNumber(input_val);
                                        input_val = "<?php echo $bia['simbol'] ?>" + input_val;
                                        
                                        // final formatting
                                        if (blur === "blur") {
                                        input_val += "";
                                        }
                                    }
                                    
                                    // send updated string to input
                                    input.val(input_val);

                                    // put caret back in the right position
                                    var updated_len = input_val.length;
                                    caret_pos = updated_len - original_len + caret_pos;
                                    input[0].setSelectionRange(caret_pos, caret_pos);
                                    }
                                </script>
                            <?php } ?>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Kembali</button>
                                <button class="btn btn-success" type="submit" name="submit">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

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