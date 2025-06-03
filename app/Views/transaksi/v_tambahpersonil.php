<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Report Perjalanan Dinas Luar Negeri</title>
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url()?>/konimex.png">
    
    <link rel="stylesheet" href="<?php echo base_url('admin/css/jquery.dataTables.min.css')?>">

    <link href="<?php echo base_url('admin/css/select2.min.css')?>" rel="stylesheet"/>

    <script src="<?php echo base_url('admin/js/jquery-3.5.1.min.js')?>"></script>

    <!-- Custom fonts for this template-->
    <link href="<?php echo base_url('admin/vendor/fontawesome-free/css/all.min.css')?>" rel="stylesheet" type="text/css">

    <script src="<?php echo base_url('admin/js/all.js')?>" crossorigin="anonymous"></script>

    <!-- Custom styles for this template-->
    <link href="<?php echo base_url('admin/css/sb-admin-2.min.css')?>" rel="stylesheet">
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

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Nav Item - User Information -->
                <li class="nav-item active"><a class="nav-link" href="javascript:void(0)" data-toggle="modal" data-target="#logoutId"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout</a></li>
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
                        <div class="col-xl-12 col-lg-12">
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
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <div class="col-xl-12 col-lg-12">
                                            Nama Lengkap dan NIK<a style="color: #e74a3b">*</a>
                                        </div>
                                        <div class="row col-xl-12 col-lg-12 mb-3">
                                            <div class="col-lg-9 pt-1 pb-1">
                                                <select required class="select_nama" name="niknm[]" multiple="multiple" style="width: 100%;"></select>
                                            </div>
                                        </div>
                                        <script>
                                            $(document).ready(function() {
                                                var namalist =
                                                [<?php foreach ($nama as $nama) : ?>"<?php echo $nama['niknm']?> - <?php echo $nama['nik']?>",<?php endforeach ?>]

                                                $(".select_nama").select2({
                                                    data:namalist,
                                                    placeholder: "",
                                                    allowClear: true
                                                    // tags: true,
                                                    // tokenSeparators: [',', ' '],
                                                });
                                            });
                                        </script>
                                        <div class="col-xl-12 col-lg-12">
                                            Bagian
                                        </div>
                                        <div class="row col-xl-12 col-lg-12 mb-3">
                                            <div class="col-lg-9 pt-1 pb-1">
                                                <select required class="select_bagian" name="strorgnm" style="width: 100%;"></select>
                                            </div>
                                        </div>
                                        <script>
                                            $(document).ready(function() {
                                                var bagianlist =
                                                [<?php foreach ($bag as $bag) : ?>"<?php echo $bag['strorgnm']?>",<?php endforeach ?>]

                                                $(".select_bagian").select2({
                                                    data:bagianlist,
                                                    // tags: true,
                                                    // tokenSeparators: [',', ' '],
                                                });
                                            });
                                        </script>
                                        <div class="col-xl-12 col-lg-12">
                                            Berangkat dari Kota mana?
                                        </div>
                                        <div class="row col-xl-12 col-lg-12 mb-2">
                                            <div class="col-lg-9 pt-1 pb-1">
                                                <select required class="select_kot" name="kota" style="width: 100%;"></select>
                                            </div>
                                        </div>
                                        <script>
                                            $(document).ready(function() {
                                                var kotlist =
                                                [<?php foreach ($kot as $kot) : ?>"<?php echo $kot['kota']?>",<?php endforeach ?>]

                                                $(".select_kot").select2({
                                                    data:kotlist,
                                                    // tags: true,
                                                    // tokenSeparators: [',', ' '],
                                                });
                                            });
                                        </script>
                                        <div class="col-xl-12 col-lg-12 mb-2">
                                            <a style="color: #e74a3b">*Dapat memilih lebih dari satu</a>
                                        </div>
                                        <div class="col-xl-6 col-lg-6">
                                            <input type="submit" class="btn btn-success" name="submit" value="Simpan Data" id="submit">
                                            <a href="<?php echo site_url('transaksi')?>" class="btn btn-secondary"> Kembali</a>
                                        </div>
                                    </form>                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <footer id="footer" class="footer text-center">
                <div class="copyright">
                    Copyright &copy; <strong><span>MIS 2023</span></strong>.
                </div>
            </footer><!-- End Footer -->
        </div>
    </div>
    <!-- Scroll to Top Button-->
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
    <div class="modal fade" id="logoutId" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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

    <script src="<?php echo base_url('admin/js/jquery-3.7.0.js')?>"></script>

    <script src="<?php echo base_url('admin/js/select2.min.js')?>"></script>

    <script src="<?php echo base_url('admin/js/jquery.dataTables.min.js')?>"></script>
    
    <script src="<?php echo base_url('admin/vendor/bootstrap/js/bootstrap.bundle.min.js')?>"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?php echo base_url('admin/js/sb-admin-2.js')?>"></script>
</body>
</html>