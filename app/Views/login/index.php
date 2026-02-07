<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MEDINAFARMA | Log in</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= base_url('plugins/fontawesome-free/css/all.min.css') ?>">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="<?= base_url('plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= base_url('dist/css/adminlte.min.css') ?>">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="#"><b>MEDINA</b>FARMA</a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Identifíquese, para iniciar sesión</p>

      <?php $error = session()->getFlashdata('error'); ?>
      <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <?= $error ?>
        </div>
      <?php endif; ?>

      <form action="<?= base_url('login/auth') ?>" method="post" id="loginForm">
        <div class="input-group mb-3">
          <input type="text" class="form-control" id="usuario" name="user" placeholder="Usuario" required autofocus>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-users"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" class="form-control" id="desc_usuario" placeholder="Descripción" readonly>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" id="password" name="pwd" placeholder="Password" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="form-check px-4">
              <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
              <label class="form-check-label" for="remember">
                PC de Confianza
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Iniciar</button>
          </div>
          <!-- /.col -->
        </div>
      </form>
      
    </div>
    <!-- /.card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="<?= base_url('plugins/jquery/jquery.min.js') ?>"></script>
<!-- Bootstrap 4 -->
<script src="<?= base_url('plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<!-- AdminLTE App -->
<script src="<?= base_url('dist/js/adminlte.min.js') ?>"></script>

<script>
    $('#usuario').on('blur keypress', function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(event.type === 'blur' || keycode == '13'){
            var usuario = $("#usuario").val();
            if(usuario.length > 0) {
                $.post("<?= site_url('login/user') ?>", {user: usuario}, function (result) {
                    if(result){
                        $("#desc_usuario").val(result);
                        if(keycode == '13') {
                            $("#password").focus();
                        }
                    }else{
                        // Don't clear if user just tabbed out potentially, but here maybe safer not to interfere too much
                        if(keycode == '13') $("#usuario").focus();
                    }
                });
            }
        }
        if(keycode == '13') {
            event.preventDefault(); // Prevent form submit on enter in user field
        }
    });

    $('#password').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            $('#loginForm').submit();
        }
    });
</script>
</body>
</html>