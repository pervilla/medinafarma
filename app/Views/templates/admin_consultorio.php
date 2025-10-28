<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Consultorio Medinafarma</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Google Font: Source Sans Pro 
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">-->
        <!-- Font Awesome -->
        <link rel="stylesheet" href="<?= site_url('plugins/fontawesome-free/css/all.min.css') ?>">
        <!-- Ionicons 
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">-->
        <!-- Tempusdominus Bbootstrap 4 -->
        <link rel="stylesheet" href="<?= site_url('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') ?>">
        <!-- iCheck -->
        <link rel="stylesheet" href="<?= site_url('plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
        <!-- JQVMap -->
        <link rel="stylesheet" href="<?= site_url('plugins/jqvmap/jqvmap.min.css') ?>">
        <!-- Theme style -->
        <link rel="stylesheet" href="<?= site_url('dist/css/adminlte.min.css') ?>">
        <!-- overlayScrollbars -->
        <link rel="stylesheet" href="<?= site_url('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') ?>">
        <!-- Daterange picker -->
        <link rel="stylesheet" href="<?= site_url('plugins/daterangepicker/daterangepicker.css') ?>">
        <!-- summernote -->
        <link rel="stylesheet" href="<?= site_url('plugins/summernote/summernote-bs4.css') ?>">
    </head>
    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
            <!-- Preloader -->
            <div class="preloader flex-column justify-content-center align-items-center">
                <img class="animation__shake" src="<?= site_url('dist/img/AdminLTELogo.png') ?>" alt="AdminLTELogo" height="60" width="60">
            </div>
            <?php $session = session(); ?>
            <!-- Modal -->
            <div class="modal fade" id="modal-login">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="login-box2">
                            <!-- /.login-logo -->
                            <div class="card card-outline card-primary">
                                <div class="card-header text-center">
                                    <a href="../../index2.html" class="h1"><b>MEDINA</b>FARMA</a>
                                </div>
                                <?php if(empty($session->get('user_id'))){?> 
                                <div class="card-body">
                                    <p class="login-box-msg">Identifíquese, para iniciar sesión</p>                                    
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="usuario" placeholder="Usuario">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="fas fa-users"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="desc_usuario" placeholder="Descripción">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="fas fa-user"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="input-group mb-3">
                                            <input type="password" class="form-control" id="password" placeholder="Password">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="fas fa-lock"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-8">
                                                <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
                                            </div>
                                            <!-- /.col -->
                                            <div class="col-4">
                                                <button type="button" class="btn btn-primary btn-block">Iniciar</button>
                                            </div>
                                            <!-- /.col -->
                                        </div>                                    
                                </div>
                                <?php } else {?>
                                    <div class="card-body">
                                    <p class="login-box-msg">Cerrar sesión</p>  
                                    <p><?= $session->get('user_id')?$session->get('user_name'):""; ?></p>
                                        <div class="row">
                                        <div class="col-4">
                                        <button id="salir_session" type="button" class="btn btn-primary btn-block">Salir</button>
                                        </div>
                                        </div>                                    
                                </div>
                                <?php } ?>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>

                    </div>

                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->

        <!-- Content Wrapper. Contains page content -->
        <div class="wrapper">
            <?= $this->renderSection('content'); ?>
        </div>
        

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="<?= site_url('plugins/jquery/jquery.min.js') ?>"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="<?= site_url('plugins/jquery-ui/jquery-ui.min.js') ?>"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 4 -->
    <script src="<?= site_url('plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <!-- ChartJS -->
    <script src="<?= site_url('plugins/chart.js/Chart.min.js') ?>"></script>
    <!-- Sparkline -->
    <script src="<?= site_url('plugins/sparklines/sparkline.js') ?>"></script>
    <!-- JQVMap -->
    <script src="<?= site_url('plugins/jqvmap/jquery.vmap.min.js') ?>"></script>
    <script src="<?= site_url('plugins/jqvmap/maps/jquery.vmap.usa.js') ?>"></script>
    <!-- jQuery Knob Chart -->
    <script src="<?= site_url('plugins/jquery-knob/jquery.knob.min.js') ?>"></script>
    <!-- daterangepicker -->
    <script src="<?= site_url('plugins/moment/moment.min.js') ?>"></script>
    <script src="<?= site_url('plugins/moment/moment-with-locales.js') ?>"></script>
    <script src="<?= site_url('plugins/daterangepicker/daterangepicker.js') ?>"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="<?= site_url('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') ?>"></script>
    <!-- Summernote -->
    <script src="<?= site_url('plugins/summernote/summernote-bs4.min.js') ?>"></script>
    <!-- overlayScrollbars -->
    <script src="<?= site_url('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?= site_url('dist/js/adminlte.js') ?>"></script>
<script>
    $('#usuario').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
        var usuario = $("#usuario").val();
        $.post("<?= site_url('login/user') ?>", {user: usuario}, function (result) {
                if(result){
                    $("#desc_usuario").val(result);
                    $("#password").focus();
                }else{
                    $("#usuario").focus();
                }                
            });
    }
});
$('#password').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
        var password = $("#password").val(); var usuario = $("#usuario").val();
        $.post("<?= site_url('login/auth') ?>", {pwd: password,user: usuario}, function (result) {
                if(result){
                    $('#modal-login').modal('hide');
                    location.reload();
                }else{
                    $("#password").focus();
                }                
            });
    }
});
$("#salir_session").click(function(){
  $.post("<?= site_url('login/close') ?>", function(data, status){
    alert("Data: " + data + "\nStatus: " + status);
    location.reload();
  });
});


</script>

    <?= $this->renderSection('footer'); ?>

</body>
</html>
