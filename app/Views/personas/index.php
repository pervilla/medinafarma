<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Clientes</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Clientes</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid"> 
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <!-- /.col -->
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Cliente</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="busqueda" placeholder="Cliente (Use '%' para caracter o palabra desconocida)">
                                    </div>
                                </div>
                                <!-- /.form-group -->
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                        <div class="custom-control custom-radio">
                                        <input class="custom-control-input custom-control-input-danger" type="radio" id="RadioCli" name="RadioClientes" checked="" value="C">
                                        <label for="RadioCli" class="custom-control-label">Clientes</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                        <input class="custom-control-input custom-control-input-danger custom-control-input-outline" type="radio" id="RadioPro" name="RadioClientes" value="P">
                                        <label for="RadioPro" class="custom-control-label">Proveedores</label>
                                        </div>
                                </div>
                            </div>
                            <div class="col-md-2">       
                                <button type="button"  id="buscar" name="buscar" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Buscar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Centro</h3>
                        <div class="card-tools">
                            <a href="#" class="btn btn-tool btn-sm">
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="#" class="btn btn-tool btn-sm">
                                <i class="fas fa-bars"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="productos_centro" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>CODCIA</th>
                                    <th>RUC</th>
                                    <th>DNI</th>
                                    <th>cp</th>
                                    <th>NOMBRE</th>
                                    <th>GERENTE</th>
                                    <th>123</th>
                                    <th>DIRECCION</th>
                                    <th>Numero</th>
                                    <th>Zona</th>
                                    <th>SubZona</th>
                                    <th>Estado</th>
                                    <th>Moneda</th>
                                    <th>TipoCli</th>
                                    <th>ZonaNew</th>
                                    <th>TELEFONO</th>
                                    <th></th>
                                    
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col-md-6 -->
            <div class="col-lg-6">
            <div class="card card-danger">
              <div class="card-header">
                <h3 class="card-title"><i class="far fa-user"></i></h3>
              </div>
              <div class="card-body">
                <!-- Date dd/mm/yyyy -->
                <div class="form-group">
                    <label>Sunat:</label>
                    <div class="row">
                        <div class="col-3">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                                </div>
                                <input type="numeric" class="form-control" id="CLI_CODCLIE" name='CLI_CODCLIE' placeholder="CODIGO CLIENTE" disabled>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-id-badge"></i></span>
                                </div>
                                <input type="numeric" class="form-control" id="CLI_RUC_ESPOSO" name='CLI_RUC_ESPOSO' placeholder="R.U.C. o D.N.I.">
                            </div>
                        </div> 
                        <div class="col-3">       
                                <button type="button"  id="importar" name="importar" class="btn btn-primary btn-block"><i class="fas fa-cloud-download-alt"></i> Importar</button>
                        </div>
                    </div>
                </div>                
                <!-- /.form group -->
                
                <!-- Date mm/dd/yyyy -->
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="far fa-user"></i></span>
                    </div>
                    <input type="text" class="form-control" id="CLI_NOMBRE" name='CLI_NOMBRE' placeholder="RAZON SOCIAL" readonly>
                  </div>
                  <!-- /.input group -->
                </div>
                <!-- /.form group -->
                <!-- /.form group -->
                <!-- Date mm/dd/yyyy -->
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                        </div>
                        <input type="text" id='CLI_FECHA_NAC' name='CLI_FECHA_NAC' class="form-control" readonly
                            data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask=""
                            inputmode="numeric" placeholder="FECHA NACIMIENTO !Solo para Pacientes!">
                    </div>
                </div>
                <!-- /.form group -->

                <!-- phone mask -->
                <div class="form-group">  
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="fa fa-home"></i></span>
                    </div>
                    <input type="text" class="form-control" id="CLI_CASA_DIREC" name='CLI_CASA_DIREC' placeholder="DIRECCIÓN" readonly>
                  </div>
                  <!-- /.input group -->
                </div>
                <!-- /.form group -->
                <!-- phone mask -->
                <div class="form-group">  
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="fa fa-phone"></i></span>
                    </div>
                    <input type="text" class="form-control" id="CLI_TELEF1" name='CLI_TELEF1' placeholder="TELEFONO" readonly>
                    <input type="hidden" id="estado" name='estado'>
                  </div>
                  <!-- /.input group -->
                </div>
                <!-- /.form group -->               

                <div class="form-group">
                    <label></label>
                    <div class="row">
                        <div class="col-12 btn-group">
                        <button type="button"  id="nuevo" name="nuevo" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo</button>
                        
                        <button type="button"  id="editar" name="editar" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</button>
                        <button type="button"  id="cancelar" name="cancelar" class="btn btn-danger"><i class="fas fa-times-circle"></i> Cancelar</button>
                        <button type="button"  id="guardar" name="guardar" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>

                        </div> 
                        
                    </div>
                </div>          

              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

            </div>
            <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->

    </div>
    <!-- /.container-fluid -->
</div>
<!-- /.content -->

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<!-- Toast -->
<link rel="stylesheet" href="../../plugins/toastr/toastr.min.css">
<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<!-- Toast -->
<script src="../../plugins/toastr/toastr.min.js"></script>
<!-- InputMask -->
<script src="../../plugins/inputmask/jquery.inputmask.min.js"></script>
<script>
$(document).ready(function () {
    $("#nuevo").show();
    $("#editar").hide();
    $("#guardar").hide();
    $("#cancelar").hide();
    $("#importar").hide();
    $('#CLI_FECHA_NAC').inputmask('dd/mm/yyyy', {
        'placeholder': 'dd/mm/yyyy'
    });
    
    // Validación en tiempo real del documento
    $('#CLI_RUC_ESPOSO').on('input', function() {
        var documento = $(this).val().replace(/\D/g, ''); // Solo números
        $(this).val(documento);
        
        var longitud = documento.length;
        var $importBtn = $('#importar');
        
        if (longitud === 8) {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $importBtn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt"></i> Importar DNI');
        } else if (longitud === 11) {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $importBtn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt"></i> Importar RUC');
        } else {
            $(this).removeClass('is-valid is-invalid');
            $importBtn.prop('disabled', true).html('<i class="fas fa-cloud-download-alt"></i> Importar');
        }
    });
    
    // Validación del nombre
    $('#CLI_NOMBRE').on('input', function() {
        var nombre = $(this).val().trim();
        if (nombre.length >= 3) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else if (nombre.length > 0) {
            $(this).removeClass('is-valid').addClass('is-invalid');
        } else {
            $(this).removeClass('is-valid is-invalid');
        }
    });    

        var dtable = $('#productos_centro').DataTable({
            ajax: {
                url: "<?= site_url('personas/get_personas') ?>",
                type: "POST",
                dataSrc: '',
                data: { 
                    busqueda: function () { return $("input#busqueda").val(); },
                    tipoCli: function () { return $('input:radio[name=RadioClientes]:checked').val(); }
                }
            },
            columns: [
                {data: 'CLI_CODCLIE', width: "10%",className: 'dt-body-right'}, //0
                {data: 'CLI_CODCIA'},
                {data: 'CLI_RUC_ESPOSO'},                //2
                {data: 'CLI_RUC_ESPOSA'},                //3
                {data: 'CLI_CP'},
                {data: 'CLI_NOMBRE'},//5
                {data: 'CLI_NOMBRE_ESPOSA'},
                {data: 'CLI_123'},
                {data: 'CLI_CASA_DIREC'},//8
                {data: 'CLI_CASA_NUM'},
                {data: 'CLI_CASA_ZONA'},
                {data: 'CLI_CASA_SUBZONA'},
                {data: 'CLI_ESTADO'},
                {data: 'CLI_MONEDA'},
                {data: 'CLI_TIPOCLI'},
                {data: 'CLI_ZONA_NEW'},
                {data: 'CLI_TELEF1'},                
                {defaultContent: "<button class='btn btn-block btn-outline-primary btn-xs'>Ver</button>"}      
            ],
            fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {  
                if (aData.StockGen > 0) {
                    $(nRow).addClass('bg-success');
                }
            return nRow;
            },
            searching: false,
            paging: true,
            responsive: true, lengthChange: false, autoWidth: false, dom: 'Bfrtip',
            columnDefs: [
            {targets: [ 1 ],visible: false,searchable: false},
            {targets: [ 4 ],visible: false,searchable: false},
            {targets: [ 6 ],visible: false,searchable: false},
            {targets: [ 7 ],visible: false,searchable: false},
            {targets: [ 9 ],visible: false,searchable: false},
            {targets: [ 10 ],visible: false,searchable: false},
            {targets: [ 11 ],visible: false,searchable: false},
            {targets: [ 12 ],visible: false,searchable: false},
            {targets: [ 13 ],visible: false,searchable: false},
            {targets: [ 14 ],visible: false,searchable: false},
            {targets: [ 15 ],visible: false,searchable: false}
        ]
        });
        $('#productos_centro tbody').on('click', 'button', function () {
            var data = dtable.row($(this).parents('tr')).data();
            $("input#CLI_CODCLIE").val(data['CLI_CODCLIE']);
            $("input#CLI_RUC_ESPOSO").val(data['CLI_RUC_ESPOSO']?data['CLI_RUC_ESPOSO']:data['CLI_RUC_ESPOSA']).attr('readonly', true);
            $("input#CLI_NOMBRE").val(data['CLI_NOMBRE']).attr('readonly', true);
            $("input#CLI_CASA_DIREC").val(data['CLI_CASA_DIREC']).attr('readonly', true);
            $("input#CLI_TELEF1").val(data['CLI_TELEF1']).attr('readonly', true);
            $("input#CLI_FECHA_NAC").val($.trim(data['EDAD']) !== ''?new Date(data['CLI_FECHA_NAC']):'').attr('readonly', true);           

            $("#nuevo").show();
            $("#editar").show();
            $("#guardar").hide();
            $("#cancelar").hide();
            $("#importar").hide();
        });
$('#busqueda').keydown(function(event){ 
    var keyCode = (event.keyCode ? event.keyCode : event.which);   
    if (keyCode == 13) {
        $('#buscar').trigger('click');
    }
});
$("#buscar").click(function () {
    dtable.ajax.reload();
    
});

$("#nuevo").click(function () {
    $("#nuevo").hide();
    $("#editar").hide();
    $("#guardar").show();
    $("#cancelar").show();
    $("#importar").show();
    $("input#CLI_CODCLIE").val('');
    $("input#CLI_RUC_ESPOSO").val('').removeAttr('readonly');
    $("input#CLI_NOMBRE").val('').removeAttr('readonly');
    $("input#CLI_CASA_DIREC").val('').removeAttr('readonly');
    $("input#CLI_TELEF1").val('').removeAttr('readonly');
    $("input#CLI_FECHA_NAC").val('').removeAttr('readonly');
    $("input#estado").val('nuevo');
});

$("#editar").click(function () {
    $("#nuevo").hide();
    $("#editar").hide();
    $("#guardar").show();
    $("#cancelar").show();
    $("#importar").hide();
    $("input#CLI_RUC_ESPOSO").removeAttr('readonly');
    $("input#CLI_NOMBRE").removeAttr('readonly');
    $("input#CLI_CASA_DIREC").removeAttr('readonly');
    $("input#CLI_TELEF1").removeAttr('readonly');
    $("input#CLI_FECHA_NAC").removeAttr('readonly');
    $("input#estado").val('editar');
});

$("#cancelar").click(function () {
    $("#nuevo").show();
    $("#editar").hide();
    $("#guardar").hide();
    $("#cancelar").hide();
    $("#importar").hide();
    $("input#CLI_RUC_ESPOSO").attr('readonly', true);
    $("input#CLI_NOMBRE").attr('readonly', true);
    $("input#CLI_CASA_DIREC").attr('readonly', true);
    $("input#CLI_TELEF1").attr('readonly', true);
    $("input#CLI_FECHA_NAC").attr('readonly', true);
    $("input#estado").val('editar');
});

$('.toastrDefaultSuccess').click(function() {
      toastr.success('Lorem ipsum dolor sit amet, consetetur sadipscing elitr.')
    });

$("#guardar").click(function () {
    // Validaciones básicas
    if ($("#CLI_NOMBRE").val().trim() === "") {
        toastr.error("El nombre es obligatorio");
        $("#CLI_NOMBRE").focus();
        return;
    }
    
    // Mostrar loading
    $("#guardar").prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.post("<?= site_url('personas/save_persona') ?>", {
        cod: $("input#CLI_CODCLIE").val(),
        ruc: $("input#CLI_RUC_ESPOSO").val(),
        nom: $("input#CLI_NOMBRE").val(),
        dir: $("input#CLI_CASA_DIREC").val(),
        tel: $("input#CLI_TELEF1").val(),
        nac: $("input#CLI_FECHA_NAC").val(),
        est: $("input#estado").val(),
        tps: $("input[name=RadioClientes]:checked").val()
    }, function (response) {
        if (response.status === 'success') {
            toastr.success(response.message);
            dtable.ajax.reload();
            $("#cancelar").trigger("click"); // Limpiar formulario
        } else {
            toastr.error(response.message || 'Error al guardar');
        }
    }, 'json').fail(function() {
        toastr.error('Error de conexión');
    }).always(function() {
        // Restaurar botón
        $("#guardar").prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
    });
});

$("#importar").click(function () {
    var documento = $("input#CLI_RUC_ESPOSO").val().trim();
    
    if (!documento) {
        toastr.error('Ingrese un documento');
        return;
    }
    
    // Mostrar loading
    $("#importar").prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Consultando...');
    
    $.post("<?= site_url('personas/get_persona_sunat') ?>", {
        ruc: documento
    }, function (response) {
        if (response.status === 'success') {
            // Datos nuevos de API
            $("input#CLI_RUC_ESPOSO").val(response.data.documento);
            $("input#CLI_NOMBRE").val(response.data.nombre);
            $("input#CLI_CASA_DIREC").val(response.data.direccion);
            $("input#CLI_TELEF1").val(response.data.telefono || '');
            $("input#CLI_CODCLIE").val(response.data.codigo);
            $("input#CLI_FECHA_NAC").val(response.data.fecha_nacimiento || '');
            
            toastr.success('Datos obtenidos. Complete información faltante y presione Guardar');
            
        } else if (response.status === 'exists') {
            // Cliente ya existe
            $("input#CLI_RUC_ESPOSO").val(response.data.documento);
            $("input#CLI_NOMBRE").val(response.data.nombre);
            $("input#CLI_CASA_DIREC").val(response.data.direccion);
            $("input#CLI_TELEF1").val(response.data.telefono);
            $("input#CLI_CODCLIE").val(response.data.codigo);
            $("input#CLI_FECHA_NAC").val(response.data.fecha_nacimiento || '');
            
            // Cambiar a modo solo lectura
            $("#nuevo").show();
            $("#editar").show();
            $("#guardar").hide();
            $("#cancelar").hide();
            $("#importar").hide();
            $("input#CLI_RUC_ESPOSO").attr('readonly', true);
            $("input#CLI_NOMBRE").attr('readonly', true);
            $("input#CLI_CASA_DIREC").attr('readonly', true);
            $("input#CLI_TELEF1").attr('readonly', true);
            $("input#CLI_FECHA_NAC").attr('readonly', true);
            
            toastr.warning('El documento ya está registrado');
            
        } else {
            // Error
            toastr.error(response.message || 'Error al consultar el documento');
            $("input#CLI_RUC_ESPOSO").focus();
        }
    }, 'json').fail(function() {
        toastr.error('Error de conexión');
    }).always(function() {
        // Restaurar botón
        $("#importar").prop('disabled', false).html('<i class="fas fa-cloud-download-alt"></i> Importar');
    });
});



});
</script>
<?= $this->endSection(); ?>