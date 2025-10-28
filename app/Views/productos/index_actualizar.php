<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Loading Overlay -->
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Profile</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active">User Profile</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-3">
        <!-- Profile Image -->
        <div class="card card-info card-outline">
          <div class="card-body box-profile p-0">
            <div class="small-box bg-success mb-0">
              <div class="inner">
                <h3>SINCRONIZAR</h3>

                <p>Todas las Base de Datos</p>
              </div>
              <div class="icon">
                <i class="fa fa-database"></i>
              </div>
              <a id="sincronizar" href="#" class="small-box-footer">Clic Aquí <i class="fas fa-arrow-circle-right"></i></a>

            </div>
          </div>

          <!-- /.card-body -->
          <div id='overlay_actualizar' class="overlay">
            <div class="spinner-border" role="status">
              <span class="sr-only">Loading...</span>
            </div>
          </div>
        </div>
        <!-- /.card -->
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
        <!-- Profile Image -->
        <div class="card card-info card-outline">
          <div class="card-body box-profile">
            <div class="text-center">
              <img class="profile-user-img img-fluid img-circle"
                src="../../dist/img/logomedina.png"
                alt="User profile picture">
            </div>
            <h3 class="profile-username text-center">Peñameza</h3>
            <p class="text-muted text-center">Estado de Productos</p>
            <ul class="list-group list-group-unbordered mb-3">
              <li class="list-group-item">
                <b>Ultimo Producto</b> <a id="local_pm" class="float-right">0</a>
              </li>
              <li class="list-group-item">
                <b>Sin Actualizar</b> <a id="local_pm_count" class="float-right">0</a>
              </li>
            </ul>
          </div>

          <!-- /.card-body -->
          <div id='overlay_pm' class="overlay">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
          </div>
        </div>
        <!-- /.card -->
      </div>
      <!-- /.col -->
      <div class="col-md-3">
        <!-- Profile Image -->
        <div class="card card-danger card-outline">
          <div class="card-body box-profile">
            <div class="text-center">
              <img class="profile-user-img img-fluid img-circle"
                src="../../dist/img/juanjuicillo.jpg"
                alt="User profile picture">
            </div>
            <h3 class="profile-username text-center">Juanjuicillo</h3>
            <p class="text-muted text-center">Estado de Productos</p>
            <ul class="list-group list-group-unbordered mb-3">
              <li class="list-group-item">
                <b>Ultimo Producto</b> <a id="local_jj" class="float-right"></a>
              </li>
              <li class="list-group-item">
                <b>Sin Actualizar </b> <a id="local_jj_count" class="float-right"></a>
              </li>
            </ul>
          </div>
          <!-- /.card-body -->
          <div id='overlay_jj' class="overlay">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
          </div>
        </div>
        <!-- /.card -->
      </div>
    </div>
    <div class="row">
      <!-- /.col -->
      <div class="col-md-6">
        <!-- Profile Image -->
        <div class="card card-warning card-outline">
          <div class="card-body box-profile">
            <div class="resultados text-center">

            </div>
          </div>

        </div>
        <!-- /.card -->
      </div>
      <!-- /.col -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
<!-- Toast Container para mensajes -->
<div class="toasts-top-right fixed" id="toast-container">
</div>
<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">

<!-- En el head -->
<link rel="stylesheet" href="../../plugins/sweetalert2/sweetalert2.min.css">

<!-- Al final del body -->
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
  $(document).ready(function() {
    obtener_max_key(2);
    obtener_max_key(3);
    $('#overlay_actualizar').hide();

    function obtener_max_key(server) {
      var loc = server == 3 ? 'pm' : 'jj';
      $.ajax({
        type: "POST",
        url: "<?= site_url('productos/crea_productos') ?>",
        data: {
          serve: server
        },
        dataType: 'text',
        beforeSend: function() {
          $('#overlay_' + loc).show();
        },
        success: function(data) {
          var rpta = data.split("|");
          $('a#local_' + loc + '_count').html('<i class="fa fa-eye"></i>' + rpta[1]);
          $('a#local_' + loc).text(rpta[0]);
        },
        complete: function() {
          $('#overlay_' + loc).hide();
        },
      });
    }


    $("#local_pm_count").click(function() {
      ver_arti(3)
    });
    $("#local_jj_count").click(function() {
      ver_arti(2)
    });

    function ver_arti(server) {
      var loc = server == 3 ? 'pm' : 'jj';
      var key = $('a#local_' + loc).text();
      var cadena = '';
      $.ajax({
        type: "POST",
        url: "<?= site_url('productos/ver_arti') ?>",
        data: {
          serve: server,
          keyval: key
        },
        dataType: 'text',
        beforeSend: function() {
          $('#overlay_' + loc).show();
        },
        success: function(data) {
          $.each(eval(data), function(i, item) {
            cadena = cadena + '' + item.ART_KEY + '-' + item.ART_NOMBRE + '</br>';
          });
          $('.resultados').html(cadena);
        },
        complete: function() {
          $('#overlay_' + loc).hide();
        },
      });

    }

    $("#sincronizar").click(function() {
      // Mostrar diálogo de confirmación
      Swal.fire({
        title: '¿Desea sincronizar los datos?',
        text: "Este proceso puede tomar varios minutos",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, sincronizar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          sincronizarDatos();
        }
      });
    });

    function formatMessage(mensaje) {
      // Dividir el mensaje en secciones
      let sections = mensaje.split('SERVER');
      let formattedMessage = '';

      // Formatear la primera sección (conexiones)
      if (sections[0]) {
        formattedMessage += sections[0].replace(/\. /g, '<br>');
      }

      // Formatear las secciones de cada servidor
      for (let i = 1; i < sections.length; i++) {
        if (sections[i]) {
          formattedMessage += '<br>SERVER' + sections[i].replace(/\. /g, '<br>');
        }
      }

      return formattedMessage;
    }

    function sincronizarDatos() {
      $.ajax({
        type: "POST",
        url: "<?= site_url('productos/actualiza_precios') ?>",
        data: {
          serve: 0
        },
        dataType: 'json',
        beforeSend: function() {
          $('#overlay_actualizar').show();

        },
        success: function(response) {
          if (response.success) {
            $('#overlay_actualizar').hide();

            Swal.fire({
              title: 'Sincronización Completada',
              html: formatMessage(response.mensaje),
              icon: 'success',
              confirmButtonText: 'Aceptar'
            });
          } else {
            Swal.fire({
              title: 'Error en la sincronización',
              html: formatMessage(response.mensaje),
              icon: "error",
              confirmButtonText: 'Aceptar'
            });
          }
        },
        error: function(xhr, status, error) {
          showToast('error', 'Error', 'Ocurrió un error durante la sincronización');
          console.error("Error en la solicitud:", error);
        },
        complete: function() {
          $('#overlay_actualizar').hide();
        }
      });
    }

    function showToast(type, title, message) {
      $(document).Toasts('create', {
        class: type === 'success' ? 'bg-success' : 'bg-danger',
        title: title,
        subtitle: 'Ahora',
        body: message,
        autohide: true,
        delay: 15000,
        icon: type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle',
      });
    }







  });
</script>
<?= $this->endSection(); ?>