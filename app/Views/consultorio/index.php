<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header"></div>
<!-- /.content-header -->
<!-- Main content -->
<?php
//echo $CIT_CODCAMP;
?>
<div class="content">
    

    <div class="container-fluid">
        <div class="row">
            <?php foreach ($campanias as $campania) {                                                                                 
                if(date('d/m/Y', strtotime($campania->CAM_FEC_INI))!='31/12/1969'){
                    $date =  date('d/m/Y', strtotime($campania->CAM_FEC_INI));
                    $sinfecha = '';
                }else{
                    $date = 'Sin Fecha'; 
                    $sinfecha = '<div class="ribbon-wrapper ribbon-lg"><div class="ribbon bg-warning text-lg">'.$date.'</div></div>';
                }; ?>             
                <div class="col-md-4">
                    <div class="card card-widget widget-user shadow">
                    <?=$sinfecha?>
                        <div class="widget-user-header text-white" style="background: #007bff 35%; background-image: url(../dist/img/shape_2.svg); background-size: cover; background-position: top right; background-repeat: no-repeat;">  
                            <h6 class="widget-user-username"><?=$campania->CAM_DESCRIP.' - '.$date ?></h6>                            
                            <h5 class="widget-user-desc"><?=$campania->CLI_NOMBRE ?></h5>
                        </div>
                        <div class="widget-user-image">
                            <img class="img-circle elevation-2" src="../dist/img/<?=$campania->CAM_CODMED ?>.jpg" alt="<?=$campania->CLI_NOMBRE ?>">
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm-4 border-right">
                                    <div class="description-block">
                                        <h5 class="description-header"><?=$campania->INSCRITOS ?></h5>
                                        <span class="description-text">INSCRITOS</span>
                                    </div>
                                </div>
                                <div class="col-sm-4 border-right">
                                    <div class="description-block">
                                        <h5 class="description-header"><?=$campania->CONFIRMADOS ?></h5>
                                        <span class="description-text">CONFIRMADOS</span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="description-block">
                                        <h5 class="description-header"><?=$campania->ATENDIDOS ?></h5>
                                        <span class="description-text">ATENDIDOS</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 btn-group">
                                        <button type="button" id="inscribir_cita" name="inscribir_cita"  
                                        data-id='<?=$campania->CAM_CODCAMP ?>'
                                        data-toggle="modal" data-target="#modal-registo-paciente" class="btn btn-success"
                                            style="color: var(--white); --fa-animation-duration:2s;"><i class="fas fa-marker"></i>
                                            Inscribir</button>
                                        <a href="<?= site_url('consultorio/pacientes/'.$campania->CAM_CODCAMP) ?>" class="btn btn-secondary " role="button" aria-disabled="true"><i
                                                class="fas fa-address-book"></i> Pacientes</a>                                                                             
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?> 
        </div>
    </div>
    <!-- /.container-fluid -->
</div>
<!-- /.content -->
<!-- Modal -->
<div class="modal fade" id="modal-registo-paciente">
    <div class="modal-dialog">
        <div class="modal-content bg-success">
            <div class="modal-header">
                <h4 class="modal-title">Nueva Cita</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <form id="formcita">
                        <div class="form-group row">
                            <label for="cmvtipo" class="col-sm-2 col-form-label">Campaña</label>
                            <div class="col-sm-10">
                                <select id="new_camp" name="new_camp" class="form-control">
                                    <option value="0" selected="selected">SELECCIONE</option>
                                    <?php foreach ($campanias as $campania) {                                                                                 
                           $date =  date("d/m/Y", strtotime($campania->CAM_FEC_INI)); ?>
                                    <option value="<?=$campania->CAM_CODCAMP?>"><?=$campania->CAM_DESCRIP.' - '.$date ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" id="DV_CIT_ORD_NEW">
                            <label for="cmvvend" class="col-sm-2 col-form-label">Orden</label>
                            <div class="col-sm-10">
                                <input type="numeric" class="form-control" id="CIT_ORD_NEW" name="CIT_ORD_NEW"
                                    placeholder="ORDEN" />
                            </div>
                        </div>
                        <div class="form-group row" id="DV_b_cliente">
                            <label for="cmvvend" class="col-sm-2 col-form-label">Paciente</label>
                            <div class="col-sm-8">
                                <select id="b_cliente" name="b_cliente" class="form-control"
                                    style="width: 100%;"></select>
                            </div>
                            <div class="col-sm-2">
                                <button onclick="agregarPaciente()" type="button" class="form-control btn btn-block btn-primary btn-sm">Nuevo</button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="cvmmotivo3" class="col-sm-2 col-form-label">Pago</label>
                            <div class="col-sm-1">
                                <div
                                    class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                    <input type="checkbox" class="custom-control-input" id="customSwitch3"
                                        name="customSwitch3" />
                                    <label class="custom-control-label" for="customSwitch3"></label>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="input-group mb-3">
                                    <select id="ALL_NUMSER" name="ALL_NUMSER" class="form-control" readonly="true">
                                        <option value="0" selected="selected">SERIE</option>
                                        <option value="10">BO10</option>
                                        <option value="11">BO11</option>
                                        <option value="12">BO12</option>
                                    </select>
                                    <input type="text" class="form-control" id="ALL_NUMFAC" name="ALL_NUMFAC"
                                        placeholder="NUMERO" readonly="true" />
                                    <input type="text" class="form-control" id="ALL_MONTO" name="ALL_MONTO"
                                        placeholder="MONTO" readonly="true" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="cmvMEDIO" class="col-sm-2 col-form-label">Medio</label>
                            <div class="col-sm-10">
                                <select id="CIT_MEDIO" name="CIT_MEDIO" class="form-control">
                                    <option value="0" selected="selected">SELECCIONE ¿Porqué Medio se Enteró?</option>
                                    <?php foreach ($medios as $medio) { ?>
                                    <option value="<?=$medio->PUB_CODMEDIO?>"><?=$medio->PUB_DESCRIPCION ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cerrar</button>
                <button type="button" id="guardarcita" name="guardarcita" class="btn btn-outline-light">Guardar</button>
            </div>
        </div>
    </div>
    <!-- /.modal-dialog -->
    <!-- /.modal-content -->
</div>
<!-- /.modal -->
<div class="modal fade" id="modal-default" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">DATOS DEL PACIENTE</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                                </div>
                                <input type="numeric" class="form-control" id="CLI_CODCLIE" name="CLI_CODCLIE"
                                    placeholder="CODIGO CLIENTE" disabled />
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.form group -->
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-id-badge"></i></span>
                                </div>
                                <input type="numeric" class="form-control" id="CLI_RUC_ESPOSO" name="CLI_RUC_ESPOSO"
                                    placeholder="R.U.C. o D.N.I." />
                            </div>
                        </div>
                        <div class="col-6">
                            <button type="button" id="importarp" name="importarp" class="btn btn-primary"><i
                                    class="fas fa-cloud-download-alt"></i> Importar</button>
                        </div>
                    </div>
                </div>
                <!-- Date mm/dd/yyyy -->
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="far fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" id="CLI_NOMBRE" name="CLI_NOMBRE"
                            placeholder="RAZON SOCIAL" />
                    </div>
                    <!-- /.input group -->
                </div>
                <!-- /.form group -->
                <!-- Date mm/dd/yyyy -->
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                        </div>
                        <input type="text" id='CLI_FECHA_NAC' name='CLI_FECHA_NAC' class="form-control"
                            data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask=""
                            inputmode="numeric" placeholder="FECHA NACIMIENTO">
                    </div>
                </div>
                <!-- /.form group -->

                <!-- phone mask -->
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-home"></i></span>
                        </div>
                        <input type="text" class="form-control" id="CLI_CASA_DIREC" name="CLI_CASA_DIREC"
                            placeholder="DIRECCIÓN" />
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
                        <input type="text" class="form-control" id="CLI_TELEF1" name="CLI_TELEF1"
                            placeholder="TELEFONO" />
                        <input type="hidden" id="estado" name="estado" />
                    </div>
                    <!-- /.input group -->
                </div>
                <!-- /.form group -->
                <div class="form-group">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                        <input type="checkbox" class="custom-control-input" id="historia" name="historia">
                        <label class="custom-control-label" for="historia">¿Tiene Historia?</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="form-group">
                    <div class="row">
                        <div class="col-12 btn-group">

                            <button type="button" id="seleccionarp" name="seleccionarp" class="btn btn-info"
                                style="color: var(--white); --fa-animation-duration:2s;"><i class="fas fa-plus"></i>
                                Seleccionar</button>
                            <button type="button" id="nuevo" name="nuevo" class="btn btn-primary"><i
                                    class="fas fa-plus"></i> Nuevo</button>
                            <button type="button" id="editar" name="editar" class="btn btn-warning"><i
                                    class="fas fa-edit"></i> Editar</button>
                            <button type="button" id="cancelar" name="cancelar" class="btn btn-danger"
                                data-dismiss="modal"><i class="fas fa-times-circle"></i> Cancelar</button>
                            <button type="button" id="guardar" name="guardar" class="btn btn-success"><i
                                    class="fas fa-save"></i> Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('footer'); ?>
<!-- Select2 -->
<link rel="stylesheet" href="../../plugins/select2/css/select2.min.css" />
<link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css" />
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
<link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css" />
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
<!-- Select2 -->
<script src="../../plugins/select2/js/select2.full.min.js"></script>
<script src="../../plugins/bootstrap-switch/js/bootstrap-switch.js"></script>
<!-- InputMask -->
<script src="../../plugins/inputmask/jquery.inputmask.min.js"></script>

<script>
$(document).ready(function() {
    $('input[name="my-checkbox"]').bootstrapSwitch("state", true, true);
    //Datemask dd/mm/yyyy
    $('#CLI_FECHA_NAC').inputmask('dd/mm/yyyy', {
        'placeholder': 'dd/mm/yyyy'
    })
    
    // Validación en tiempo real del documento en modal
    $('#CLI_RUC_ESPOSO').on('input', function() {
        var documento = $(this).val().replace(/\D/g, ''); // Solo números
        $(this).val(documento);
        
        var longitud = documento.length;
        var $importBtn = $('#importarp');
        
        if (longitud === 8) {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $importBtn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt"></i> Importar DNI');
        } else if (longitud === 11) {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $importBtn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt"></i> Importar RUC');
        } else if (longitud > 0) {
            $(this).removeClass('is-valid').addClass('is-invalid');
            $importBtn.prop('disabled', true).html('<i class="fas fa-exclamation-triangle"></i> Documento inválido');
        } else {
            $(this).removeClass('is-valid is-invalid');
            $importBtn.prop('disabled', true).html('<i class="fas fa-cloud-download-alt"></i> Importar');
        }
    });
    
    // Validación del nombre en modal
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
    
    // Validación de la dirección en modal
    $('#CLI_CASA_DIREC').on('input', function() {
        var direccion = $(this).val().trim();
        if (direccion.length >= 5) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else if (direccion.length > 0) {
            $(this).removeClass('is-valid').addClass('is-invalid');
        } else {
            $(this).removeClass('is-valid is-invalid');
        }
    });
    $('#modal-registo-paciente').on('shown.bs.modal', function(e) {
        var sel = $(e.relatedTarget).data('id');

        $('#new_camp').val(sel).change();

    })
    $("#b_cliente").select2({
        ajax: {
            url: "<?= site_url('consultorio/get_personas2') ?>",
            dataType: "json",
            processResults: function(data) {
                return {
                    results: data,
                };
            },
        },
        width: "100%",
        placeholder: "Seleccione el Paciente",
        language: {
            noResults: function() {
                return '<div class="form-group row"><label for="inputEmail3" class="col-sm-8 col-form-label">No existe ese paciente.</label><div class="col-sm-4"><button onclick="agregarPaciente()" type="button" class="form-control btn btn-block btn-primary btn-sm">Nuevo</button></div></div>';
            },
        },
        escapeMarkup: function(markup) {
            return markup;
        },
    });
    $("#new_camp").change(function() {
        $.post(
            "<?= site_url('consultorio/get_orden') ?>", {
                camp: $(this).val(),
            },
            function(htmlexterno) {
                $("#CIT_ORD_NEW").val(htmlexterno);
            }
        );
    });
    $("#customSwitch3").change(function() {
        if (this.checked) {
            $("#ALL_NUMSER").removeAttr("readonly");
            $("#ALL_NUMFAC").removeAttr("readonly")
        } else {
            $("#ALL_NUMSER").attr("readonly", "readonly");
            $("#ALL_NUMFAC").attr("readonly", "readonly");
        }
    });
    $("#ALL_NUMFAC").blur(function() {
        $(this).css("background-color", "#FFFFCC");
        $.post(
            "<?= site_url('consultorio/get_monto') ?>", {
                numer: $(this).val(),
                serie: $("#ALL_NUMSER").val(),
            },
            function(htmlexterno) {
                $("#ALL_MONTO").val(htmlexterno);
            }
        );
    });
    ///// GUARDAR PACIENTE /////
    $("#guardar").click(function() {
        // Validaciones básicas
        if ($("#CLI_NOMBRE").val().trim() === "") {
            alert("El nombre es obligatorio");
            $("#CLI_NOMBRE").focus();
            return;
        }
        
        $.post(
            "<?= site_url('personas/save_persona') ?>", {
                cod: $("input#CLI_CODCLIE").val(),
                ruc: $("input#CLI_RUC_ESPOSO").val(),
                nom: $("input#CLI_NOMBRE").val(),
                dir: $("input#CLI_CASA_DIREC").val(),
                tel: $("input#CLI_TELEF1").val(),
                nac: $("input#CLI_FECHA_NAC").val(),
                his: $("input[name=historia]:checked").val(),
                est: $("input#estado").val(),
                tps: 'C'
            },
            function(response) {
                if (response.status === 'success') {
                    var data = {
                        id: response.data.codigo,
                        text: response.data.nombre
                    };
                    var newOption = new Option(data.text, data.id, false, false);
                    $('#b_cliente').empty();
                    $('#b_cliente').append(newOption).trigger('change');
                    $("#guardar").blur();
                    $("#modal-default").modal("hide");
                } else {
                    alert(response.message || 'Error al guardar');
                }
            },
            'json'
        ).fail(function() {
            alert('Error de conexión');
        });
    });
    ///// GUARDAR CITA /////
    $("#guardarcita").click(function() {
        console.log($("select#new_camp option:checked").val());
        console.log($("select#b_cliente option:checked").val());
        if ($("select#new_camp option:checked").val() > 0 && $("select#b_cliente option:checked")
        .val() !== undefined) {
            $.post(
                "<?= site_url('consultorio/save_cita') ?>", {
                    cam: $("select#new_camp option:checked").val(),
                    ord: $("input#CIT_ORD_NEW").val(),
                    cli: $("select#b_cliente").val(),
                    pag: $("input[name=customSwitch3]:checked").val(),
                    ser: $("select#ALL_NUMSER option:checked").val(),
                    fac: $("input#ALL_NUMFAC").val(),
                    mon: $("input#ALL_MONTO").val(),
                    med: $("select#CIT_MEDIO option:checked").val(),
                },
                function(htmlexterno) {
                    var datos = eval(htmlexterno);
                    var data = {
                        id: $("input#CLI_CODCLIE").val(),
                        text: $("input#CLI_NOMBRE").val()
                    };
                    var newOption = new Option(data.text, data.id, false, false);
                    $("select#new_camp").val(0),
                        $("input#CIT_ORD_NEW").val(''),
                        $('select#b_cliente').val(null).trigger('change'),
                        $("select#ALL_NUMSER").val(0),
                        $("input#ALL_NUMFAC").val(''),
                        $("input#ALL_MONTO").val(''),
                        $("select#CIT_MEDIO").val(0),
                        $("#modal-registo-paciente").modal("hide");
                    location.reload();
                }
            );
        } else {
            alert("Rellene todo el formulario");
        }

    });
    $("#importarp").click(function() {
        var documento = $("input#CLI_RUC_ESPOSO").val().trim();
        
        if (!documento) {
            alert('Ingrese un documento');
            return;
        }
        
        $("#importarp").prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Consultando...');
        
        $.post(
            "<?= site_url('personas/get_persona_sunat') ?>", {
                ruc: documento
            },
            function(response) {
                console.log('Respuesta recibida:', response);
                
                if (response.status === 'success') {
                    $("input#CLI_RUC_ESPOSO").val(response.data.documento);
                    $("input#CLI_NOMBRE").val(response.data.nombre);
                    $("input#CLI_CASA_DIREC").val(response.data.direccion);
                    $("input#CLI_TELEF1").val(response.data.telefono || '');
                    $("input#CLI_CODCLIE").val(response.data.codigo);
                    $("input#CLI_FECHA_NAC").val(response.data.fecha_nacimiento || '');
                    $("input#estado").val("nuevo");
                    
                    alert('Datos obtenidos correctamente. Complete información faltante y presione Guardar');
                    
                } else if (response.status === 'exists') {
                    $("input#CLI_RUC_ESPOSO").val(response.data.documento);
                    $("input#CLI_NOMBRE").val(response.data.nombre);
                    $("input#CLI_CASA_DIREC").val(response.data.direccion);
                    $("input#CLI_TELEF1").val(response.data.telefono);
                    $("input#CLI_CODCLIE").val(response.data.codigo);
                    $("input#CLI_FECHA_NAC").val(response.data.fecha_nacimiento || '');
                    $("input#estado").val("existe");
                    
                    $("#nuevo").show();
                    $("#editar").show();
                    $("#seleccionarp").show();
                    $("#guardar").hide();
                    $("#cancelar").show();
                    $("#importarp").hide();
                    
                    alert("El documento " + response.data.documento + " ya está registrado!");
                    
                } else {
                    console.log('Error en respuesta:', response);
                    alert(response.message || 'Error al consultar el documento');
                    $("input#CLI_RUC_ESPOSO").focus();
                }
            },
            'json'
        ).fail(function(xhr, status, error) {
            console.log('Error AJAX:', xhr.responseText);
            alert('Error de conexión: ' + error);
        }).always(function() {
            $("#importarp").prop('disabled', false).html('<i class="fas fa-cloud-download-alt"></i> Importar');
        });
    });

    $("#nuevo").click(function() {
        $("#nuevo").hide();
        $("#editar").hide();
        $("#seleccionarp").hide();
        $("#guardar").show();
        $("#cancelar").show();
        $("#importarp").show();
        $("input#CLI_CODCLIE").val("");
        $("input#CLI_RUC_ESPOSO").val("");
        $("input#CLI_NOMBRE").val("");
        $("input#CLI_CASA_DIREC").val("");
        $("input#CLI_TELEF1").val("");
        $("input#estado").val("nuevo");
    });

    $("#editar").click(function() {
        $("#nuevo").hide();
        $("#editar").hide();
        $("#seleccionarp").hide();
        $("#guardar").show();
        $("#cancelar").show();
        $("#importarp").show();
        $("input#estado").val("editar");
    });
    $("#seleccionarp").click(function() {
        var data = {
            id: $("input#CLI_CODCLIE").val(),
            text: $("input#CLI_NOMBRE").val()
        };
        var newOption = new Option(data.text, data.id, false, false);
        $('#b_cliente').empty();
        $('#b_cliente').append(newOption).trigger('change');
        $("#modal-default").modal("hide");
    })
});

function agregarPaciente() {
    $("#modal-default").modal("show");
    $("#nuevo").hide();
    $("#editar").hide();
    $("#seleccionarp").hide();
    $("#guardar").show();
    $("#cancelar").show();
    $("#importarp").show().prop('disabled', true).html('<i class="fas fa-cloud-download-alt"></i> Importar');
    $("input#CLI_CODCLIE").val("");
    $("input#CLI_RUC_ESPOSO").val("").removeClass('is-valid is-invalid');
    $("input#CLI_NOMBRE").val("").removeClass('is-valid is-invalid');
    $("input#CLI_CASA_DIREC").val("").removeClass('is-valid is-invalid');
    $("input#CLI_TELEF1").val("");
    $("input#CLI_FECHA_NAC").val("");
    $("input#estado").val("nuevo");
    $(".select2-container").siblings('select').select2('close');
    $("input#CLI_RUC_ESPOSO").focus(function() {
        $(this).select();
    });
}
</script>
<?= $this->endSection(); ?>