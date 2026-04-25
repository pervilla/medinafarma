<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Medinafarma</title>

  <!-- Google Font: Source Sans Pro 
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">-->
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= site_url('plugins/fontawesome-free/css/all.min.css') ?>">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="<?= site_url('plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= site_url('dist/css/adminlte.min.css') ?>">
</head>
<body class="hold-transition login-page">
<div class="login-box">
	<div class="login-logo">
		<a href="../../index2.html"><b>MEDINA</b>FARMA</a>
	</div>
	<!-- /.login-logo -->
	<div class="card">
	<?php 
		$session = session();
		if(empty($session->get('user_id'))){
	?> 
		<div class="card-body login-card-body">
			<p class="login-box-msg">Identifíquese, para iniciar sesión</p>
			<form id="form-login">
				<div class="input-group mb-3">
					<input type="text" class="form-control" id="usuario" placeholder="Usuario" enterkeyhint="next">
					<div class="input-group-append" id="btn-search-user" style="cursor: pointer;">
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
					<input type="password" class="form-control" id="password" placeholder="Password" enterkeyhint="go">
					<div class="input-group-append">
						<div class="input-group-text">
							<span class="fas fa-lock"></span>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-8">
						<div class="icheck-primary">
				  <input type="checkbox" id="remember" name="remember" value="1">
				  <label for="remember">
					Mantener sesión activa
				  </label>
				</div>
					</div>
					<!-- /.col -->
					<div class="col-4">
						<button type="submit" id="login" name="login" class="btn btn-primary btn-block">Iniciar</button>
					</div>
					<!-- /.col -->
				</div>
			</form>
		</div>
		<?php } else {?>
		<div class="card-body login-card-body">
			<p class="login-box-msg">Cerrar sesión</p>  
			<p class="login-box-msg"><?= $session->get('user_id')?$session->get('user_name'):""; ?></p>
			<div class="row">
				<div class="col-6">
				<button id="salir_session" type="button" class="btn btn-primary btn-block">Salir</button>
				</div>
				<div class="col-6">
					<a href="<?= site_url('dashboard') ?>" class="btn btn-success btn-block">Home</a>
				</div>
			</div>                                    
		</div>
		<?php } ?>
		<!-- /.card-body -->
								
		
	</div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="<?= site_url('plugins/jquery/jquery.min.js') ?>"></script>
<!-- Bootstrap 4 -->
<script src="<?= site_url('plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<!-- AdminLTE App -->
<script src="<?= site_url('dist/js/adminlte.js') ?>"></script>
<script>
    function buscarUsuario() {
        var usuario = $("#usuario").val();
        if (usuario.length > 0) {
            $.post("<?= site_url('login/user') ?>", {user: usuario}, function (result) {
                if(result){
                    $("#desc_usuario").val(result);
                    $("#password").focus();
                }else{
                    $("#usuario").focus();
                }                
            });
        }
    }

    $('#usuario').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            event.preventDefault();
            buscarUsuario();
        }
    });

    $('#btn-search-user').click(function() {
        buscarUsuario();
    });

    $('#password').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            // El submit del form se encargará del login
        }
    });

    $("#form-login").submit(function (event) {
        event.preventDefault();
        var password = $("#password").val(); 
        var usuario = $("#usuario").val();
        var remember = $("#remember").is(":checked") ? 1 : 0;
        
        if (!usuario || !password) {
            return;
        }

        $.post("<?= site_url('login/auth') ?>", {pwd: password, user: usuario, remember: remember}, function (result) {
            if(result){
                $('#modal-login').modal('hide');
                window.location.href = '<?= site_url('dashboard') ?>';
            }else{
                $("#password").focus();
            }                
        });
    });


$("#salir_session").click(function(){
  $.post("<?= site_url('login/close') ?>", function(data, status){
    alert("Data: " + data + "\nStatus: " + status);
    location.reload();
  });
});



</script>
</body>
</html>
