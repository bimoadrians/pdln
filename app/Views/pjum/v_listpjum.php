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
                $treasury = site_url("treasuryselesaipjum/$jenis_biaya1/$id_transaksi");
                $gs = site_url("gsselesaipjum/$jenis_biaya1/$id_transaksi");
                $support = site_url("support/$id_transaksi");
                $pjum = site_url("listpjum/$jenis_biaya1/$id_transaksi");
                $pb = site_url("listpb/$jenis_biaya2/$id_transaksi");
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
                                                        <th class="text-center" style="color: #5a5c69;">ID Transaksi</th>
                                                        <?php if ($role == 'gs' && $submit == 2) { ?>
                                                            <th class="text-center" style="color: #5a5c69;">Berangkat dari Kota</th>
                                                        <?php } else { ?>
                                                    
                                                        <?php } ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <th class="text-center" scope="row" style="color: #5a5c69;"><a href="<?php echo site_url("detailtransaksi/$id_transaksi")?>"><?php echo $id['id_transaksi']; echo("/"); echo("Perjalanan Dinas Luar Negeri"); echo("/"); echo $id['jumlah_personil']; echo("/"); foreach ($neg as $neg) : echo $neg['negara_tujuan']; endforeach ?></a></th>
                                                        <?php if ($role == 'gs' && $submit == 2) { ?>
                                                            <?php foreach ($kota as $kota) { ?>
                                                                <?php if(!empty($kota)){ ?>
                                                                    <th class="text-center" style="color: #5a5c69;"><?php echo $kota['kota'];?></th>
                                                                <?php } else { ?>
                                                                    <th class="text-center" style="color: #5a5c69;"></th>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                    
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
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <a class="btn btn-secondary mb-3" href="<?php echo $dashboard ?>">Kembali</a>
                                        <?php if ($role == 'admin' || $role == 'user') { ?>
                                            <?php if ($submit == 0) { ?>
                                                <!-- <a class="btn btn-primary mb-3" href="javascript:void(0)" data-toggle="modal" data-target="#nopjum">Tambah Data Nomor PJUM</a> -->
                                                <a onclick="return confirm('Apakah data PJUM sudah lengkap dan benar?')"><input type="submit" class="btn btn-success mb-3" name="submit" value="Kirim Data PJUM ke Treasury" id="submit"></a>
                                            <?php  } else { ?>
                                                    
                                            <?php } ?>
                                        <?php } if ($role == 'treasury') { ?>
                                            <?php if ($submit == 1) { ?>
                                                <a onclick="return confirm('Apakah data PJUM perlu dilakukan revisi oleh User bagian?')"><input type="submit" class="btn btn-warning mb-3" name="submit" value="Revisi Data PJUM" id="submit"></a>
                                                <?php if ($submit_pb == 0 && !empty($cekdatapb)) { ?>
                                                    
                                                <?php } else { ?>
                                                    <a class="btn btn-success mb-3" onclick="return confirm('Apakah data PJUM sudah lengkap dan benar?')" href="<?php echo $treasury ?>">Kirim Data PJUM ke GS</a>
                                                <?php } ?>
                                            <?php } else { ?>
                                                    
                                            <?php } ?>
                                        <?php } if ($role == 'gs') { ?>
                                            <?php if ($submit == 2) { ?>
                                                <!-- <a onclick="return confirm('Apakah data PJUM perlu dilakukan revisi oleh bagian Treasury?')"><input type="submit" class="btn btn-warning mb-3" name="submit" value="Revisi Data PJUM" id="submit"></a> -->
                                                <a class="btn btn-success mb-3" onclick="return confirm('Apakah data PJUM sudah lengkap dan benar?')" href="<?php echo $gs ?>">Selesai</a>
                                            <?php  } else { ?>
                                                    
                                            <?php } ?>
                                        <?php } ?>
                                    </form>
                                    <div style="overflow-x:auto;">
                                        <table id="myTable" class="table table-bordered mb-4">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" style="color: #5a5c69;">Nomor PJUM</th>
                                                    <th class="text-center" style="color: #5a5c69;">Valas</th>
                                                    <th class="text-center" style="color: #5a5c69;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($nomor as $nom) { 
                                                    $id_pjum = $nom['id_pjum'];
                                                    $link_pilih = site_url("datapjum/$jenis_biaya1/$id_transaksi/$id_pjum");
                                                    $link_delete = site_url("listpjum/$jenis_biaya1/$id_transaksi/?aksi=hapus&id_pjum=$id_pjum");
                                                ?>
                                                    <tr>
                                                        <?php foreach ($valas as $key => $value) { ?>
                                                            <?php if($nom['id_pjum'] == $value['id_pjum']){ ?>
                                                                <?php if($role == 'admin' && $submit == 0 || $role == 'user' && $submit == 0){ ?>
                                                                    <th class="text-center" scope="row" style="color: #5a5c69;">
                                                                        <a href="javascript:void(0)" style="color: #5a5c69" data-toggle="modal" data-target="#editnopjum<?php echo $nom['id_pjum']; echo $value['id_pjum']; ?>"><abbr title="Klik untuk melakukan edit No PJUM" style="text-decoration:none;-webkit-text-decoration:none;text-decoration:none;cursor:pointer;border-bottom:0;-webkit-text-decoration-skip-ink:none;text-decoration-skip-ink:none"><?php echo $nom['nomor'];?></abbr></a>
                                                                    </th>
                                                                <?php } else { ?>
                                                                    <th class="text-center" scope="row" style="color: #5a5c69;">
                                                                        <?php echo $nom['nomor'];?>
                                                                    </th>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                        
                                                        <?php foreach ($valas as $key => $value) { ?>
                                                            <?php if($nom['id_pjum'] == $value['id_pjum']){ ?>
                                                                <th class="text-center" scope="row" style="color: #5a5c69;">
                                                                    <?php echo $value['kode_valas'];?>
                                                                </th>
                                                            <?php } ?>
                                                        <?php } ?>

                                                        <th class="text-center" scope="row" style="color: #5a5c69;">
                                                            <a href="<?php echo $link_pilih?>" class="btn btn-success">Pilih</a>
                                                            
                                                            <?php if ($role == 'admin' && $submit == 0 || $role == 'user' && $submit == 0) { // && $kirim_pjum == 0 ?>
                                                                <a href="<?php echo $link_delete?>" onclick="return confirm('Yakin akan menghapus data PJUM?')" class="btn btn-danger">Hapus</a>
                                                            <?php  } else { ?>
                                                                    
                                                            <?php } ?>

                                                            <?php foreach ($kategori as $key => $value) { ?>
                                                                <?php if($value['id_pjum'] == $nom['id_pjum']) { ?>
                                                                    <?php if($value['treasury'] != null && $value['edited_by'] == null && $role == 'admin' && $submit == 0 || $value['treasury'] != null && $value['edited_by'] == null && $role == 'user' && $submit == 0 || $value['treasury'] != null && $value['edited_by'] == null && $role == 'treasury' && $submit == 1) { ?>
                                                                        <p style="color: #e74a3b">(Perlu revisi)</p>
                                                                        <?php break; ?>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                            <?php } ?>
                                                            
                                                            <?php foreach ($kategori as $key => $value) { ?>
                                                                <?php if($value['id_pjum'] == $nom['id_pjum']) { ?>
                                                                    <?php if($value['treasury'] != null && $value['edited_by'] != null && $role == 'admin' && $submit == 0 || $value['treasury'] != null && $value['edited_by'] != null && $role == 'user' && $submit == 0 || $value['treasury'] != null && $value['edited_by'] != null && $role == 'treasury' && $submit == 1) { ?>
                                                                        <p style="color: #1cc88a">(Telah direvisi)</p>
                                                                        <?php break; ?>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                            <?php } ?>
                                                                    
                                                            <?php foreach ($kategori as $key => $value) { ?>
                                                                <?php if($value['id_pjum'] == $nom['id_pjum']) { ?>
                                                                    <?php if($value['treasury'] == null && $value['edited_by'] != null && $role == 'admin' && $submit == 0 || $value['treasury'] == null && $value['edited_by'] != null && $role == 'user' && $submit == 0 || $value['treasury'] == null && $value['edited_by'] != null && $role == 'treasury' && $submit == 1) { ?>
                                                                        <p style="color: #4e73df">(Telah diubah)</p>
                                                                        <?php break; ?>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        </th>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
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

    <!-- <div class="modal fade" id="nopjum" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">No PJUM</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                    <input required type="text" autocomplete="off" class="form-control" id="nomor" name="nomor" placeholder="Masukkan no PJUM">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Kembali</button>
                        <button class="btn btn-success" type="submit" name="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div> -->

    <?php foreach ($nomor as $nom) { 
        $id_pjum = $nom['id_pjum'];
    ?>
    <?php foreach ($valas as $key => $value) {
        if ($nom['id_pjum'] == $value['id_pjum']) {
            $id_valas = $value['id_valas'];
            $kode_valas = $value['kode_valas'];
        }
    ?>
            <div class="modal fade" id="editnopjum<?php echo $nom['id_pjum']; echo $value['id_pjum']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Edit No PJUM</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <form action="<?php echo site_url("editnopjum/$id_pjum/$id_transaksi/$jenis_biaya") ?>" method="post" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="row col-xl-12 col-lg-12">
                                    Tanggal Pembuatan No PJUM
                                    <input required class="form-control" type="date" name="tanggal" id="input_tanggal" value="<?php echo(isset($tanggal1)) ? $tanggal1 : $nom['tanggal'] ?>">
                                </div>
                                <div class="row col-xl-12 col-lg-12 mt-3">
                                    No PJUM
                                    <input required type="text" autocomplete="off" class="form-control" id="nomor" name="nomor" value="<?php echo(isset($nomor1)) ? $nomor1 : $nom['nomor'] ?>">
                                    <input name="id_valas" type="hidden" value="<?php echo(isset($vala1)) ? $vala1 : $value['id_valas'] ?>">
                                    <input name="kode_valas" type="hidden" value="<?php echo(isset($vala)) ? $vala : $value['kode_valas'] ?>">
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