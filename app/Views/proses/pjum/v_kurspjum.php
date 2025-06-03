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
                                    $datapjum = site_url("datapjum/$jenis_biaya/$id_transaksi/$id_pjum");
                                    ?>
                                    <div class="row">
                                        <div class="col-xl-12 col-lg-12" style="overflow-x:auto;">
                                            <table class="table table-bordered mb-4">
                                                <thead>
                                                    <tr>
                                                        <th rowspan ="2" class="text-center" style="vertical-align: middle;">ID Transaksi</th>
                                                        <th rowspan ="2" class="text-center" style="vertical-align: middle;">No PJUM</th>
                                                        <th colspan ="<?php echo $index; ?>" class="text-center">PUM</th>
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
                                                    <tr>
                                                        <th class="text-center" scope="row"><a href="<?php echo site_url("detailtransaksi/$id_transaksi")?>"><?php echo $id['id_transaksi']; echo("/"); echo("Perjalanan Dinas Luar Negeri"); echo("/"); echo $id['jumlah_personil']; echo("/"); foreach ($neg as $neg) : echo $neg['negara_tujuan']; endforeach ?></a></th>
                                                        <?php foreach ($nomor as $nomor) { ?>
                                                            <?php if(!empty($nomor)){ ?>
                                                                <th class="text-center"><?php echo $nomor['nomor'];?></th>
                                                            <?php } else { ?>
                                                                <th class="text-right" scope="row"></th>
                                                            <?php } ?>
                                                        <?php } ?>
                                                        <?php foreach ($pum as $p => $pu) { ?>
                                                            <?php if(!($pu['pum'] == null)){ ?>
                                                                <th class="text-center" scope="row">
                                                                    <?php echo $pu['simbol']; echo number_format($pu['pum'], 2, ',', '.');?>
                                                                </th>
                                                            <?php } else { ?>
                                                                <th class="text-center" scope="row"></th>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                Tanggal Kurs
                                                <input required class="form-control col-xl-4 col-lg-4 mt-3 mb-3" type="date" name="tanggal" id="input_tanggal" value="<?php 
                                                    foreach ($kurs as $k => $kur) {
                                                        echo(isset($tanggal1)) ? $tanggal1 : $kur['tanggal'];
                                                    }
                                                ?>">

                                                Kurs Tengah BI
                                                <input required type="text" autocomplete="off" class="form-control col-xl-4 col-lg-4 mt-3 mb-3" name="kurs" id="currency-field" data-type="currency" value="<?php 
                                                foreach ($kurs as $k => $kur) {
                                                    echo(isset($kur1)) ? $kur1 : number_format($kur['kurs'], 2, ',', '.');
                                                }
                                                ?>">
                                                <?php foreach ($biaya as $b => $bia) { 
                                                    $id_biaya = $bia['id_biaya'];
                                                    $id_valas = $bia['id_valas'];
                                                    $kode_valas = $bia['kode_valas'];
                                                ?>
                                                    <input name="id_valas" type="hidden" value="<?php echo(isset($vala1)) ? $vala1 : $id_valas  ?>">
                                                    <input name="kode_valas" type="hidden" value="<?php echo(isset($vala)) ? $vala : $kode_valas  ?>">
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

                                                        } 
                                                        else {
                                                            // no decimal entered
                                                            // add commas to number
                                                            // remove all non-digits
                                                            input_val = formatNumber(input_val);
                                                            input_val = input_val;
                                                            
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

                                                <input type="submit" class="btn btn-success" name="submit" value="Simpan Data" id="submit">
                                                <a href= "<?php echo $datapjum ?>" class="btn btn-secondary"> Kembali</a>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="row justify-content-md-center"> -->
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