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
                $jenis_biaya3 = "support";
                $datapb = site_url("datapb/$jenis_biaya2/$id_transaksi");
                $pjum = site_url("listpjum/$jenis_biaya1/$id_transaksi");
                $pb = site_url("listpb/$jenis_biaya2/$id_transaksi");
                $support = site_url("support/$id_transaksi");
                $gssupport = site_url("gsselesaisupport/$jenis_biaya3/$id_transaksi");
            ?>
            <li class="nav-item active"><a class="nav-link" href="<?php echo $dashboard?>"><i class="fa-solid fa-house-chimney"></i><span> Dashboard</span></a></li>
            
            <li class="nav-item active"><a class="nav-link" href="<?php echo site_url("transaksi")?>"><i class="fa-solid fa-id-badge"></i><span> ID Transaksi</span></a></li>

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
                <li class="nav-item active"><a class="nav-link" href="javascript:void(0)" data-toggle="modal" data-target="#logoutModal"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout</a></li>
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
                                                    <th class="text-center" scope="row" style="color: #5a5c69">ID Transaksi</th>
                                                    <th class="text-center" scope="row" style="color: #5a5c69">Berangkat dari Kota</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <th class="text-center" scope="row"><a href="<?php echo site_url("detailtransaksi/$id_transaksi")?>"><?php echo $id['id_transaksi']; echo("/"); echo("Perjalanan Dinas Luar Negeri"); echo("/"); echo $id['jumlah_personil']; echo("/"); foreach ($neg as $neg) : echo $neg['negara_tujuan']; endforeach ?></a></th>
                                                        <?php foreach ($kota as $kota) { ?>
                                                            <?php if(!empty($kota)){ ?>
                                                                <th class="text-center" style="color: #5a5c69"><?php echo $kota['kota'];?></th>
                                                            <?php } else { ?>
                                                                <th class="text-center" scope="row" style="color: #5a5c69"></th>
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
                                    <div class="dropdown mb-3">
                                        <form action="" method="POST" enctype="multipart/form-data">
                                            <a class="btn btn-secondary" href="<?php echo $dashboard ?>">Kembali</a>
                                            <?php if ($submit == 3) { ?>
                                                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-file-arrow-down"></i>
                                                    Download Format Excel
                                                </button>
                                                <div class="dropdown-menu animated--fade-in" aria-labelledby="dropdownMenuButton" style="">
                                                    <!-- <a class="dropdown-item" href="<php echo base_url()?>/formatExcel/Format Biaya Support Perjalanan Dinas Luar Negeri.xlsx"><i class="fa-solid fa-file-download"></i> Download Format Excel (.xlsx)</a> -->
                                                    <a class="dropdown-item" href="<?php echo site_url("supportxls") ?>"><i class="fa-solid fa-file-download"></i> Download Format Excel (.xls)</a>
                                                </div>
                                                <a class="btn btn-info" href="javascript:void(0)" data-toggle="modal" data-target="#uploadExcel"><i class="fa-solid fa-file-upload"></i> Upload File Excel</a>
                                            <?php  } else { ?>

                                            <?php } ?>
                                            
                                            <?php if ($submit == 3) { ?>
                                                <a onclick="return confirm('Apakah data PB untuk kategori Dibelikan GS perlu dilakukan revisi?')"><input type="submit" class="btn btn-warning" name="submit" value="Revisi Data PB" id="submit"></a>
                                                <a class="btn btn-success" onclick="return confirm('Apakah Biaya Support sudah lengkap dan benar?')" href="<?php echo $gssupport ?>">Selesai</a>
                                            <?php  } else { ?>
                                                
                                            <?php } ?>
                                        </form>
                                    </div>
                                    <div style="overflow-x:auto;">
                                        <table id="myTable" class="table table-bordered mb-4">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" style="color: #5a5c69;">Baris Excel</th>
                                                    <th class="text-center" style="color: #5a5c69;">Tanggal</th>
                                                    <th class="text-center" style="color: #5a5c69;">Kategori</th>
                                                    <th class="text-center" style="color: #5a5c69;">Jumlah Personil</th>
                                                    <th class="text-center" style="color: #5a5c69;">Biaya</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($kategori as $k => $kat) {
                                                    $id_kategori = $kat['id_kategori'];
                                                    $id_transaksi = $kat['id_transaksi'];
                                                    $hapus = site_url("treasurypb/$id_kategori/$id_transaksi/$jenis_biaya/?aksi=hapus&id_kategori=$id_kategori");
                                                ?>
                                                    <tr>
                                                        <th class="text-center" scope="row" style="color: #5a5c69"><?php echo $kat['baris']?></th>
                                                        <th class="text-center" scope="row" style="color: #5a5c69"><?php echo tanggal_indo($kat['tanggal']) ?></th>
                                                        <th class="text-center" scope="row" style="color: #5a5c69"><?php echo $kat['kategori']?></th>
                                                        <th class="text-center" scope="row" style="color: #5a5c69"><?php echo $kat['jumlah_personil']?></th>
                                                        <?php foreach ($biaya as $b => $bia) { ?>
                                                            <?php if($kat['id_kategori'] == $bia['id_kategori']) {
                                                                $id_biaya = $bia['id_biaya'];
                                                                $id_kategori = $bia['id_kategori'];
                                                                $jenis_biaya = 'support';
                                                            ?>
                                                                <?php if(!($bia['biaya'] == 0)){ ?>
                                                                    <?php if($submit_pb == 3){ ?>
                                                                        <th class="text-right" scope="row">
                                                                            <a href="<?php echo site_url("editbiayasupport/$id_biaya/$id_kategori/$id_transaksi/$jenis_biaya") ?>" style="color: #5a5c69"><abbr title="Klik untuk melakukan edit biaya" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?></abbr></a>
                                                                        </th>
                                                                    <?php } else { ?>
                                                                        <th class="text-right" scope="row" style="color: #5a5c69">
                                                                            <?php echo $bia['simbol']; echo number_format($bia['biaya'], 2, ',', '.');?>
                                                                        </th>
                                                                    <?php } ?>
                                                                <?php } else { ?>
                                                                    <th class="text-right" scope="row" style="color: #5a5c69"></th>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                            <tr>
                                                <th colspan ="4" style="color: #5a5c69">Total Biaya</th>
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
                                                        <th class="text-right" scope="row" style="color: #5a5c69"></th>
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
                <form action="<?php echo site_url("importsupport/$id_transaksi") ?>" method="POST" enctype="multipart/form-data"><!-- enctype="multipart/form-data" -->
                    <div class="modal-body">
                        <input type="file" class="form-control" name="file_excel_support" accept=".xls,.xlsx" required>
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