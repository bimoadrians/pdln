</div>
<!-- End of Main Content -->

<!-- Footer -->
<!-- <footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; 2009-<?php echo "" . date("Y ");?><a style="color:red;" href="https://konimex.com/">Konimex.</a> All rights reserved</span>
        </div>
    </div>
</footer> -->
<!-- End of Footer -->

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
                <form action="<?php echo site_url("importbiaya/$id") ?>" method="POST" enctype="multipart/form-data"><!-- enctype="multipart/form-data" -->
                    <div class="modal-body">
                        <input type="file" class="form-control" name="file_excel_all" accept=".xls,.xlsx" required>
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

<script src="<?php echo base_url('admin')?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="<?php echo base_url('admin')?>/vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="<?php echo base_url('admin')?>/js/sb-admin-2.js"></script>

</body>

</html>