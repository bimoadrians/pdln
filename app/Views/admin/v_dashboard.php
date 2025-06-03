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
                                        <table class="table table-bordered mb-4">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" scope="col">ID Transaksi</th>
                                                    <?php if ($role == 'gs') { ?>
                                                        <th class="text-center" scope="col">Berangkat dari Kota</th>
                                                    <?php } else { ?>
                                                
                                                    <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($id_transaksi as $id) {
                                                $id_transaksi = $id['id_transaksi'];
                                                $support = site_url("support/$id_transaksi");
                                                $jenis_biaya1 = "pjum";
                                                $jenis_biaya2 = "pb";
                                                $pjum = site_url("listpjum/$jenis_biaya1/$id_transaksi");
                                                $pb = site_url("listpb/$jenis_biaya2/$id_transaksi");
                                                ?>
                                                <tr>
                                                    <th class="text-center" scope="row"><a href="<?php echo site_url("detailtransaksi/$id_transaksi")?>"><?php echo $id['id_transaksi']; echo("/"); echo("Perjalanan Dinas Luar Negeri"); echo("/"); echo $id['jumlah_personil']; echo("/"); foreach ($neg as $neg) : echo $neg['negara_tujuan']; endforeach ?></a></th>
                                                    <?php if ($role == 'gs') { ?>
                                                        <?php foreach ($kota1 as $kota1) { ?>
                                                            <?php if(!empty($kota1)){ ?>
                                                                <th class="text-center"><?php echo $kota1['kota'];?></th>
                                                            <?php } else { ?>
                                                                <th class="text-right" scope="row"></th>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                
                                                    <?php } ?>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                        <?php
                                        foreach ($nopb as $nb1 => $nopb1) {
                                            $created_by = $nopb1['created_by'];
                                        }
                                        if ($role == 'gs' && $submit_pjum == 4 && $submit_pb == 4) { ?>
                                            <a class="btn btn-primary" href="<?php echo site_url("exportlaporan/$id_transaksi") ?>"><i class="fas fa-download fa-sm text-white-50"></i> GENERATE REPORT</a>
                                        <!-- <php } else if ($role == 'admin' && $submit_pjum == 0 && $submit_pb == 0 || $role == 'user' && $submit_pjum == 0 && $submit_pb == 0 || $role == 'admin' && $submit_pjum == 1 && $submit_pb == 0 || $role == 'user' && $submit_pjum == 1 && $submit_pb == 0 || $role == 'admin' && $submit_pjum == 4 && $submit_pb == 0 || $role == 'user' && $submit_pjum == 4 && $submit_pb == 0 || $role == 'admin' && $submit_pjum == 0 && $submit_pb == 1 || $role == 'user' && $submit_pjum == 0 && $submit_pb == 1 || $role == 'admin' && $submit_pjum == 0 && $submit_pb == 2 || $role == 'user' && $submit_pjum == 0 && $submit_pb == 2 || $role == 'admin' && $submit_pjum == 0 && $submit_pb == 3 || $role == 'user' && $submit_pjum == 0 && $submit_pb == 3 || $role == 'admin' && $submit_pjum == 0 && $submit_pb == 4 || $role == 'user' && $submit_pjum == 0 && $submit_pb == 4) { ?> -->

                                        <?php } else if ($role == 'admin' && $submit_pjum == 0 || $role == 'admin' && $submit_pb == 0 || $role == 'user' && $submit_pjum == 0 || $role == 'user' && $submit_pb == 0) { ?>
                                            <?php if ($role == 'admin' && $submit_pjum == 0 && $submit_pb == 0 && $kirim_pjum == 0 && $kirim_pb == 0 || $role == 'user' && $submit_pjum == 0 && $submit_pb == 0 && $kirim_pjum == 0 && $kirim_pb == 0) { ?>
                                                <div class="btn-group">
                                                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-file-arrow-down"></i>
                                                        Download Format Excel
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                        <li><a class="dropdown-item" href="<?php echo site_url("biayaxls") ?>"><i class="fa-solid fa-file-download"></i> Download Format Excel (.xls)</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo site_url("biayaxlsx") ?>"><i class="fa-solid fa-file-download"></i> Download Format Excel (.xlsx)</a></li>
                                                    </ul>
                                                </div>
                                            <?php } ?>

                                            <div class="btn-group">
                                                <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-file-arrow-down"></i>
                                                    Export File Excel
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                    <li><a class="dropdown-item" href="<?php echo site_url("exportbiaya/$id_transaksi") ?>"><i class="fa-solid fa-file-download"></i> Export File Excel</a></li>
                                                    <?php if ($role == 'admin' || $role == 'user') { ?>
                                                        <li><a class="dropdown-item" href="<?php echo site_url("exporterp/$id_transaksi") ?>"><i class="fa-solid fa-file-download"></i> Export File Excel (Untuk ERP)</a></li>
                                                        <!-- <li><a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#report"><i class="fa-solid fa-file-upload"></i> Report Pengeluaran</a></li> -->
                                                    <?php } ?>
                                                </ul>
                                            </div>

                                            <a class="btn btn-success" href="javascript:void(0)" data-toggle="modal" data-target="#uploadExcel"><i class="fa-solid fa-file-upload"></i> Upload File Excel</a>

                                        <?php } else if ($role == 'treasury' && empty($biayapb) || $role == 'treasury' && $submit_pb == 0 || $role == 'treasury' && $submit_pb == 1) { ?>
                                            <a class="btn btn-warning" href="<?php echo site_url("exportbiaya/$id_transaksi") ?>"><i class="fa-solid fa-file-download"></i> Export File Excel</a>
                                            <a class="btn btn-success" href="javascript:void(0)" data-toggle="modal" data-target="#uploadExcel"><i class="fa-solid fa-file-upload"></i> Upload File Excel</a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php if (empty($cek)) { ?>

                            <?php } else if (empty($cekpjum)) { ?>

                            <?php } else { ?>
                                <!-- Earnings (Monthly) Card Example -->
                                <?php if ($role == 'admin' || $role == 'user') { ?>
                                    <div class="col-xl-6 col-md-6 mb-4">
                                        <div class="card border-left-danger shadow h-100 py-2">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="h2 mb-0 font-weight-bold text-gray-800"><i class="fa-solid fa-credit-card"></i> <a href="<?php echo $pjum?>">PJUM</a></div>
                                                    </div>
                                                    <a class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" href="<?php echo $pjum?>">
                                                        <i class="fa-solid fa-circle-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if ($role == 'treasury' && $submit_pjum == 1) { ?>
                                    <div class="col-xl-6 col-md-6 mb-4">
                                        <div class="card border-left-danger shadow h-100 py-2">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="h2 mb-0 font-weight-bold text-gray-800"><i class="fa-solid fa-credit-card"></i> <a href="<?php echo $pjum?>">PJUM</a></div>
                                                    </div>
                                                    <a class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" href="<?php echo $pjum?>">
                                                        <i class="fa-solid fa-circle-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } elseif ($role == 'gs' && $submit_pjum > 1) { ?>
                                    <div class="col-xl-12 col-md-6 mb-4">
                                        <div class="card border-left-primary shadow h-100 py-2">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="h2 mb-0 font-weight-bold text-gray-800"><i class="fa-solid fa-credit-card"></i> <a href="<?php echo $pjum?>">PJUM</a></div>
                                                    </div>
                                                    <a class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" href="<?php echo $pjum?>">
                                                        <i class="fa-solid fa-circle-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                                    
                            <?php if (empty($cekpb)) { ?>

                            <?php } else { ?>
                                <!-- Earnings (Monthly) Card Example -->
                                <?php
                                foreach ($nopb as $nb => $nopb) {
                                    $created_by = $nopb['created_by'];
                                }
                                
                                if ($role == 'admin'|| $role == 'user'|| $role == 'treasury' && $submit_pb == 1 || $role == 'treasury' && $submit_pb == 0 && $created_by == '05080') { ?>
                                    <div class="col-xl-6 col-md-6 mb-4">
                                        <div class="card border-left-primary shadow h-100 py-2">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="h2 mb-0 font-weight-bold text-gray-800"><i class="fa-solid fa-sack-dollar"></i> <a href="<?php echo $pb?>">PB</a></div>
                                                    </div>
                                                    <a class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" href="<?php echo $pb?>">
                                                        <i class="fa-solid fa-circle-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if ($role == 'gs' && $submit_pb > 1) { ?>
                                    <div class="col-xl-12 col-md-6 mb-4">
                                        <div class="card border-left-primary shadow h-100 py-2">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="h2 mb-0 font-weight-bold text-gray-800"><i class="fa-solid fa-sack-dollar"></i> <a href="<?php echo $pb?>">PB</a></div>
                                                    </div>
                                                    <a class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" href="<?php echo $pb?>">
                                                        <i class="fa-solid fa-circle-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>

                        <div class="row">
                            <!-- Earnings (Monthly) Card Example -->
                            <?php if ($role == 'gs' && $solo != 'Surakarta' && $submit_pb > 2) { ?>
                                <div class="col-xl-12 col-md-6 mb-4">
                                    <div class="card border-left-danger shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="h2 mb-0 font-weight-bold text-gray-800"><i class="fa-solid fa-square-caret-down"></i> <a href="<?php echo $support?>">Biaya Support</a></div>
                                                </div>
                                                <a class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" href="<?php echo $support?>">
                                                    <i class="fa-solid fa-circle-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>

                            <?php } ?>
                        </div>
                    </div>
                    <!-- /.container-fluid -->