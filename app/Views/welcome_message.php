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
					
				</div>
				<!-- /.col -->
				<div class="col-4">
					<button type="button"  id="login" name="login" class="btn btn-primary btn-block">Iniciar</button>
				</div>
				<!-- /.col -->
			</div>                                    
		</div>
		<?php } else {?>
		<div class="card-body login-card-body">
			<p class="login-box-msg">Cerrar sesión</p>  
			<p class="login-box-msg"><?= $session->get('user_id')?$session->get('user_name'):""; ?></p>
			<div class="row">
				<div class="col-12">
				<button id="salir_session" type="button" class="btn btn-primary btn-block">Salir</button>
				</div>
			</div>                                    
		</div>
		<?php } ?>
		<!-- /.card-body -->
								
		<div class="card-body login-card-body">
			
			<a id="caja_cnt" class="btn btn-app bg-success">
			  <span class="badge bg-purple">891</span>
			  <i class="fas fa-inbox"></i>Caja Centro
			</a>
			<a id="caja_pmz" class="btn btn-app bg-info">
			  <span class="badge bg-teal">67</span>
			  <i class="fas fa-inbox"></i>Caja PMeza
			</a>
			<a id="caja_jjc" class="btn btn-app bg-danger">
			  <span class="badge bg-info">12</span>
			  <i class="fas fa-inbox"></i>Juanjuicillo
			</a>
			<a href="<?= site_url('productos') ?>" class="btn btn-app bg-secondary">
			  <span class="badge bg-success"></span>
			  <i class="fas fa-barcode"></i> Products
			</a>
			<a href="<?= site_url('personas') ?>" class="btn btn-app bg-warning">
			  <span class="badge bg-danger"></span>
			  <i class="fas fa-heart"></i> Clientes
			</a>
			<a href="<?= site_url('dashboard') ?>" class="btn btn-app bg-info">
			<span class="badge bg-danger"></span>
			  <i class="fas fa-inbox"></i> Inicio
			</a>
		</div>
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
		$('#login').trigger('click');
    }
});

$("#login").click(function () {
	var password = $("#password").val(); var usuario = $("#usuario").val();
	$.post("<?= site_url('login/auth') ?>", {pwd: password,user: usuario}, function (result) {
		if(result){
			$('#modal-login').modal('hide');
			$(location).attr('href', '<?= site_url('dashboard') ?>')
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

$("#caja_cnt").click(function(e) {
    set_caja(e,1);
});
$("#caja_pmz").click(function(e) {
    set_caja(e,3);
});
$("#caja_jjc").click(function(e) {
    set_caja(e,2);
});
function set_caja(e,x) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "<?= site_url('caja/set_caja') ?>",
        data: { 
			caja:x ,opci: 'login'
        },
        success: function(result) {
            window.location.href = "<?= site_url('caja/dia') ?>";
    		return false;
        },
        error: function(result) {
            alert('error');
        }
    });
}

</script>
</body>
</html>
