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

    <link href="<?php echo base_url('admin/css/select2.min.css')?>" rel="stylesheet" />

    <script src="<?php echo base_url('admin')?>/js/jquery-3.5.1.min.js"></script>

    <!-- Custom fonts for this template-->
    <link href="<?php echo base_url('admin')?>/vendor/fontawesome-free/css/all.min.css" rel="stylesheet"
        type="text/css">

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
                <img class="img-profile rounded-circle" src="<?php echo base_url()?>/konimex.png" alt="Logo" width="35"
                    height="35">
                <div class="sidebar-brand-icon rotate-n-15"></div>
                <div class="sidebar-brand-text mx-3">Perjalanan Dinas LN</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <?php if ($role == 'admin' || $role == 'user') { ?>
                <?php if (empty($submit)) { ?>
                    
                <?php } else if ($submit['submit_pjum'] == 4 && $submit['submit_pb'] == 4) { ?>
                    <li class="nav-item active"><a class="nav-link" href="javascript:void(0)" data-toggle="modal" data-target="#report"><i class="fa-solid fa-file-excel"></i> Cetak Laporan Biaya</a></li>
                <?php } else { ?>

                <?php } ?>
            <?php } ?>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Nav Item - User Information -->
                <li class="nav-item active"><a class="nav-link" href="javascript:void(0)" data-toggle="modal"
                        data-target="#logoutId"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Logout</a></li>
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
                <div class="col-xl-12 col-lg-12 mb-4">
                    <div class="card shadow">
                        <div class="card-body">
                            <?php if ($role == 'treasury') { ?>

                            <?php } else if ($role == 'gs') { ?>

                            <?php } else { ?>
                                <a class="btn btn-primary mb-3" href="tambahdataid">+ Tambah ID Transaksi</a>
                            <?php } ?>

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
                            <div style="overflow-x:auto;">
                                <table id="myTable" class="table table-bordered mb-4">
                                    <thead>
                                        <tr class="align-text-center">
                                            <th class="text-center" style="color: #5a5c69;">No</th>
                                            <th class="text-center" style="color: #5a5c69;">ID Transaksi</th>
                                            <th class="text-center" style="color: #5a5c69;">Tanggal Keberangkatan</th>
                                            <th class="text-center" style="color: #5a5c69;">Tanggal Pulang</th>
                                            <th class="text-center" style="color: #5a5c69;">Jumlah Personil</th>
                                            <th class="text-center" style="color: #5a5c69;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1?>
                                        <?php foreach ($id_t as $id) {
                                            $id_transaksi = $id['id_transaksi'];
                                            $login = $id['login'];
                                            $login_by = $id['login_by'];
                                            $submit_pjum = $id['submit_pjum'];
                                            $submit_pb = $id['submit_pb'];
                                            $kirim_pjum = $id['kirim_pjum'];
                                            $kirim_pb = $id['kirim_pb'];
                                            $nik = session()->get('akun_nik');
                                            $link_pilih = site_url("islogin/$id_transaksi");
                                            $link_pilih1 = site_url("dashboard/$id_transaksi");
                                            $link_delete = site_url("transaksi/?aksi=hapus&id_transaksi=$id_transaksi");
                                            ?>
                                        <tr>
                                            <th class="text-center" scope="row" style="color: #5a5c69;">
                                                <?php echo $i++; ?>
                                            </th>
                                            <th class="text-center" scope="row" style="color: #5a5c69;">
                                                <a href="<?php echo site_url("detailtransaksi/$id_transaksi")?>"><?php echo $id['id_transaksi']; echo("/"); echo("Perjalanan Dinas Luar Negeri"); echo("/"); echo $id['jumlah_personil']?></a>
                                            </th>
                                            <th class="text-center" scope="row" style="color: #5a5c69;">
                                                <?php echo tanggal_indo($id['tanggal_berangkat'])?>
                                            </th>
                                            <th class="text-center" scope="row" style="color: #5a5c69;">
                                                <?php echo tanggal_indo($id['tanggal_pulang'])?>
                                            </th>
                                            <th class="text-center" scope="row" style="color: #5a5c69;">
                                                <?php echo $id['jumlah_personil']?>
                                            </th>
                                            <th class="text-center" scope="row" style="color: #5a5c69;">
                                                <?php if ($role == 'admin' && $login == 0 || $role == 'user' && $login == 0) { ?>
                                                <a href="<?php echo $link_pilih?>"
                                                    class="btn btn-success btn-block">Pilih</a>
                                                <?php if ($role == 'user' && $submit_pjum == 0 && $submit_pb == 0 && $kirim_pjum == 0 && $kirim_pb == 0) { ?>
                                                <a href="<?php echo $link_delete?>"
                                                    onclick="return confirm('Yakin akan menghapus data?')"
                                                    class="btn btn-danger btn-block">Hapus</a>
                                                <?php } else { ?>

                                                <?php } ?>
                                                <?php } else if ($role == 'treasury' || $role == 'gs') { ?>
                                                <a href="<?php echo $link_pilih1?>"
                                                    class="btn btn-success btn-block">Pilih</a>
                                                <?php } else { ?>
                                                    Sedang diedit oleh <?php echo $login_by?>
                                                <?php } ?>
                                            </th>
                                        </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer id="footer" class="footer text-center">
                <div class="copyright">
                    Copyright &copy; <strong><span>MIS 2023</span></strong>.
                </div>
            </footer><!-- End Footer -->
        </div>
    </div>
    
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

    <div class="modal fade" id="report" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Cetak Laporan Biaya</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <h6 class="modal-title" id="exampleModalLabel">Silahkan Pilih Filter</h6>
                        <div class="row col-xl-14 col-lg-14 mt-3">
                            <div class="form-group col-lg-6">
                                Tanggal Awal
                                <?php if(!empty($date_min['tanggal_berangkat']) && !empty($date_max['tanggal_pulang'])) { ?>
                                    <input required type="date" min="<?php echo $date_min['tanggal_berangkat'] ?>" max="<?php echo $date_max['tanggal_pulang'] ?>" class="form-control" name="tanggal_awal" id="input_tanggal_awal">
                                <?php } else { ?>

                                <?php } ?>
                            </div>
                            <div class="form-group col-lg-6">
                                Tanggal Akhir
                                <?php if(!empty($date_min['tanggal_berangkat']) && !empty($date_max['tanggal_pulang'])) { ?>
                                    <input required type="date" min="<?php echo $date_min['tanggal_berangkat'] ?>" max="<?php echo $date_max['tanggal_pulang'] ?>" class="form-control" name="tanggal_akhir" id="input_tanggal_akhir">
                                <?php } else { ?>

                                <?php } ?>
                            </div>
                        </div>
                        <div class="mb-1">
                            Bagian<a style="color: #e74a3b">*</a>
                        </div>
                        <select class="select_bagian" name="strorgnm[]" multiple="multiple"
                            style="width: 100%;"></select>
                        <script>
                        $(document).ready(function() {
                            var bagianlist = [
                                <?php foreach ($bag as $bag) : ?> "<?php echo $bag['strorgnm']?>",
                                <?php endforeach ?>
                            ]

                            $(".select_bagian").select2({
                                // tags:["Semua"],
                                data: bagianlist,
                                placeholder: "Semua",
                                allowClear: true
                                // tags: true,
                                // tokenSeparators: [',', ' '],
                            });
                        });
                        </script>
                        <div class="mb-1 mt-3">
                            Negara<a style="color: #e74a3b">*</a>
                        </div>
                        <select class="select_negara" name="negara[]" multiple="multiple" style="width: 100%;"></select>
                        <script>
                        $(document).ready(function() {
                            var negaralist = [
                                <?php foreach ($neg as $neg) : ?> "<?php echo $neg['negara_tujuan']?>",
                                <?php endforeach?>
                            ]

                            $(".select_negara").select2({
                                data: negaralist,
                                placeholder: "Semua",
                                allowClear: true
                                // tags: true,
                                // tokenSeparators: [',', ' '],
                            });
                        });
                        </script>
                        <div class="mb-1 mt-3">
                            Kategori<a style="color: #e74a3b">*</a>
                        </div>
                        <select class="select_kategori" name="kategori[]" multiple="multiple"
                            style="width: 100%;"></select>
                        <script>
                        $(document).ready(function() {
                            var kategori = ['Tiket Pesawat', 'Bagasi Pesawat', 'Porter Pesawat', 'Hotel', 'Makan dan Minum',
                                'Transportasi', 'Laundry', 'Lain-lain'
                            ]

                            $(".select_kategori").select2({
                                data: kategori,
                                placeholder: "Semua",
                                allowClear: true
                                // tags: true,
                                // tokenSeparators: [',', ' '],
                            });
                        });
                        </script>
                        <div class="mb-1 mt-3" style="color: #e74a3b">
                            *Dapat memilih lebih dari satu
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Batalkan</button>
                        <button class="btn btn-success" type="submit" name="submit">Cetak Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo base_url('admin')?>/js/jquery-3.7.0.js"></script>

    <script src="<?php echo base_url('admin/js/select2.min.js')?>"></script>

    <script src="<?php echo base_url('admin')?>/js/jquery.dataTables.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#myTable').DataTable({
            "pageLength": -1,
            "lengthMenu": [
                [1, 5, 10, 50, 100, -1],
                [1, 5, 10, 50, 100, "All"]
            ],
        });
    });
    </script>

    <script src="<?php echo base_url('admin')?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?php echo base_url('admin')?>/js/sb-admin-2.js"></script>
</body>

</html>