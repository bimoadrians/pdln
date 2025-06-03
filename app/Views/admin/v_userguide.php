<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>User Guide</title>
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url()?>/konimex.png">

    <script src="<?php echo base_url('admin')?>/js/jquery-3.5.1.min.js"></script>

    <link href="<?php echo base_url('admin')?>/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    
    <script src="<?php echo base_url('admin')?>/js/all.js" crossorigin="anonymous"></script>

    <!-- Custom styles for this template-->
    <link href="<?php echo base_url('admin')?>/css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body class="bg-gradient-white">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="col-lg-12">
                            <div class="p-5">
                                <div class="text-center mb-4">                                    
                                    <img class="img-fluid logo-dark mb-4" src="<?php echo base_url()?>/konimex.png" alt="Logo">
                                    <h1>User Guide</h1>
                                </div>
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
                                <div class="text-center">                                    
                                    <a class="btn btn-lg btn-block btn-primary mb-3" href="<?php echo site_url("user") ?>"><i class="fa-solid fa-file-download"></i> Download User Guide (User Bagian)</a>
                                    <a class="btn btn-lg btn-block btn-primary mb-3" href="<?php echo site_url("treasury") ?>"><i class="fa-solid fa-file-download"></i> Download User Guide (Treasury)</a>
                                    <a class="btn btn-lg btn-block btn-primary mb-3" href="<?php echo site_url("gs") ?>"><i class="fa-solid fa-file-download"></i> Download User Guide (General Service)</a>
                                    <a class="btn btn-success" href="<?php echo site_url("")?>">Kembali</a>
                                </div>
                            </div>
                        </div>
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

<!-- Bootstrap core JavaScript-->
<script src="<?php echo base_url('admin')?>/js/jquery-3.7.0.js"></script>

<script src="<?php echo base_url('admin')?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="<?php echo base_url('admin')?>/vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="<?php echo base_url('admin')?>/js/sb-admin-2.js"></script>

</body>

</html>