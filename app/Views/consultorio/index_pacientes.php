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
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <!-- /.col -->
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Campaña</label>
                                    <div class="col-sm-9">
                                        <select id="camp" name="camp" class="form-control">
                                            <option value="0" selected="selected">SELECCIONE</option>
                                            <?php foreach ($campanias as $campania) {                                                                                 
                                    $date =  date("d/m/Y", strtotime($campania->CAM_FEC_INI)); ?>
                                            <option value="<?=$campania->CAM_CODCAMP?>" <?=$campanias_cod==$campania->CAM_CODCAMP?'selected=selected':''?>>
                                                <?=$campania->CAM_DESCRIP.' - '.$date ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <!-- /.form-group -->
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="buscar" name="buscar" class="btn btn-primary btn-block"><i
                                        class="fa fa-search"></i> Buscar</button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-success btn-block" data-toggle="modal"
                                    data-target="#modal-registo-paciente"><i class="fa fa-plus"></i> Nueva Cita</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
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
                        <table id="table_citas" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Paciente</th>
                                    <th>DNI</th>
                                    <th>Telefono</th>
                                    <th>Edad</th>
                                    <th>Pago</th>
                                    <th>Documento</th>
                                    <th>Estado</th>
                                    <th>Triaje</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card -->
            </div>

        </div>
        <!-- /.row -->
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
                                <option value="0">SELECCIONE</option>
                                            <?php foreach ($campanias as $campania) {                                                                                 
                                    $date =  date("d/m/Y", strtotime($campania->CAM_FEC_INI)); ?>
                                            <option value="<?=$campania->CAM_CODCAMP?>" <?=$campanias_cod==$campania->CAM_CODCAMP?'selected=selected':''?>>
                                                <?=$campania->CAM_DESCRIP.' - '.$date ?></option>
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
                                    <input type="number" class="form-control" id="ALL_NUMFAC" name="ALL_NUMFAC"
                                        placeholder="NUMERO" readonly="true" autocomplete="off"/>
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
<div class="modal fade" id="modal-paciente" style="display: none;" aria-hidden="true">
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
                                <input type="number" class="form-control" id="CLI_RUC_ESPOSO" name="CLI_RUC_ESPOSO"
                                    placeholder="R.U.C. o D.N.I." autocomplete="off"/>
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
                            placeholder="NOMBRE Y APELLIDOS O RAZON SOCIAL" />
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
                            placeholder="TELEFONO" autocomplete="off"/>
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

<!-- Modal -->
<div class="modal fade" id="modal-boleta">
    <div class="modal-dialog">
        <div class="modal-content bg-info">
            <div class="modal-body">
                <div class="card-body">                    
                        <div class="form-group row">
                            <label for="cvmmotivo4" class="col-sm-2 col-form-label">Pago</label>
                            <div class="col-sm-10">
                                <div class="input-group mb-10">
                                    <select id="CIT_SERIE" name="CIT_SERIE" class="form-control">
                                        <option value="0" selected="selected">SERIE</option>
                                        <option value="10">BO10</option>
                                        <option value="11">BO11</option>
                                        <option value="12">BO12</option>
                                    </select>
                                    <input type="number" class="form-control" id="CIT_NUMERO" name="CIT_NUMERO"
                                        placeholder="NUMERO BOLETA O FACTURA" autocomplete="off"/>
                                    <input type="text" class="form-control" id="CIT_MONTO" name="CIT_MONTO"
                                        placeholder="MONTO" readonly="true" />
                                    <input type="hidden" id="CIT_CODCIT" name="CIT_CODCIT" />
                                </div>
                            </div>
                        </div> 
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cerrar</button>
                <button type="button" id="actualizacita" name="actualizacita" class="btn btn-outline-light">Guardar</button>
            </div>
        </div>
    </div>
    <!-- /.modal-dialog -->
    <!-- /.modal-content -->
</div>
<!-- /.modal -->

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
    var dtable = $("#table_citas").DataTable({
        ajax: {
            url: "<?= site_url('consultorio/get_citas') ?>",
            type: "POST",
            dataSrc: "",
            data: {
                camp: function() {
                    return $("select#camp option:checked").val();
                },
                esta:'<?=$estado?>'
            },
        },
        columns: [
            {data: "CIT_ORD"},
            {data: "CLI_NOMBRE",
                render: function(data, type, row, meta) {
                var badge = row.ATENCIONES == 0?'<span class="float-right badge bg-danger">NUEVO</span>':'';    
                if (row.CIT_ESTADO == 1 || row.CIT_ESTADO == 2) {
                    var rpt = "<a href='#' id='verPac' class='text-light'><i class='fas fa-user-injured'></i> "+row.CLI_NOMBRE+ badge+"</a>";
                } else{
                    var rpt = "<a href='#' id='verPac' class='d-block'><i class='fas fa-user-injured'></i> "+row.CLI_NOMBRE+ badge+"</a>";
                }
                
                     
                    return rpt 
                }},
            {data: "CLI_RUC_ESPOSA"},
            {data: "CLI_TELEF1"},
            {data: "EDAD",
                render: function(data, type, row, meta) {
                    if (row.CLI_FECHA_NAC!='') {
                        var today = new Date();
                        var birthDate = new Date(row.CLI_FECHA_NAC);
                        var age = today.getFullYear() - birthDate.getFullYear();
                        var m = today.getMonth() - birthDate.getMonth();
                        var da = today.getDate() - birthDate.getDate();
                        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }
                        if(m<0){
                            m +=12;
                        }
                        if(da<0){
                            da +=30;
                        }
                        var age = age+" años "+ Math.abs(m) + " meses ";  
                    } else {
                        var age = "Sin Fecha";
                    }
                    return age
                }            
            },
            {data: "CIT_PAGO"},
            {data: "CIT_NUMERO",
                render: function(data, type, row, meta) {
                    if (row.CIT_NUMERO==0) {
                        var rpt = "<button id='editCita' class='btn btn-info btn-block btn-sm'><i class='fas fa-money-bill-alt'></i> Registrar Pago</button>";   
                    } else {
                        var rpt = row.CIT_NUMERO;
                    }
                    return rpt
                }},
            {data: 'CIT_ESTADO',
                render: function(data, type, row, meta) {
                    if (row.CIT_ESTADO==0) {
                        var rpt = "<button id='confirPac' class='btn btn-block bg-primary btn-sm'><i class='fas fa-check-square'></i> Confirmar</button>";   
                    } else if( row.CIT_ESTADO==1) {
                        var rpt = "Confirmado";
                    }else {
                        var rpt = row.CIT_ESTADO==2?"Atendido":"En Espera";
                    }
                    return rpt
                    return rpt
                }
            },
            {
                data: 'CIT_CODCIT',
                render: function(data, type, row, meta) {
                    if (row.CIT_ESTADO==1){
                        var url = "<?= site_url('consultorio/triaje') ?>/" + row.CIT_CODCAMP +
                        '/' + row.CIT_CODCIT + '/' + row.CIT_CODCLIE;
                    var rpt = "<div class='btn-group' role='group' aria-label='Basic example'><a href='"+url+"' class='btn btn-danger btn-sm'><i class='fas fa-user-md'></i>Triaje </a> <a class='btn bg-primary btn-sm cs_pointer' href='<?=site_url('consultorio/historia')?>/"+row.CIT_CODCAMP+'/'+row.CIT_CODCIT+'/'+row.CIT_CODCLIE+"' target='_blank'><i class='fas fa-print'></i></a></div>"

                    }else {
                        var rpt = "";
                    }
                    return rpt;
                }
            },

            {
                data: 'CIT_CODCIT',
                render: function(data, type, row, meta) {
                    if (row.CIT_ESTADO==0){
                        var rpt = "<button id='eliminarCit' class='btn btn-block bg-danger btn-sm'><i class='fa fa-trash'></i></button>";   
                    }else {
                        var rpt = "";
                    }
                    return rpt;
                }
            }

        ],
        fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {  
            if (aData.CIT_ESTADO == 1) {
                    $(nRow).addClass('bg-success');
                } 
                if (aData.CIT_ESTADO == 2){
                    $(nRow).addClass('bg-secondary');
                }
                if (aData.CIT_ESTADO == 3){
                    $(nRow).addClass('bg-warning');
                }  
            return nRow;
            },
        order: [
            [0, "desc"]
        ],
        rowGroup: {
            dataSrc: 4,
        },
        searching: false,
        paging: false,
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        dom: "Bfrtip",
    });

    $("#buscar").click(function() {
        dtable.ajax.reload();
    });
    $("#camp").change(function() {
        dtable.ajax.reload();
    });
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
    $('#modal-registo-paciente').on('shown.bs.modal', function(e) {
        var campania = $('#camp').val();
        $('#new_camp').val(campania);
        $.post(
            "<?= site_url('consultorio/get_orden') ?>", {
                camp: campania
            },
            function(htmlexterno) {
                $("#CIT_ORD_NEW").val(htmlexterno);
                $('#new_camp').val(campania)
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
    $("#CIT_NUMERO").blur(function() {
        $(this).css("background-color", "#FFFFCC");
        if ($("#CIT_SERIE").is(":empty") && $("#CIT_NUMERO").is(":empty")) {
           alert('ingrese serie y numero de boleta');
        }else{
            $.post(
                "<?= site_url('consultorio/get_monto') ?>", {
                    numer: $(this).val(),
                    serie: $("#CIT_SERIE").val(),
                },
                function(htmlexterno) {
                    $("#CIT_MONTO").val(htmlexterno);
                }
            ); 
        }
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
                    if($("input#estado").val()=='editar'){
                        dtable.ajax.reload();
                    }else{
                        var newOption = new Option(data.text, data.id, false, false);
                        $('#b_cliente').empty();
                        $('#b_cliente').append(newOption).trigger('change');
                    }
                    $("#guardar").blur();
                    $("#modal-paciente").modal("hide");
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
                    dtable.ajax.reload();
                }
            );
        } else {
            alert("Rellene todo el formulario");
        }

    });

    ///// EDITAR CITA /////
    $("#actualizacita").click(function() {
        if ($("#CIT_SERIE").is(":empty") && $("#CIT_NUMERO").is(":empty")) {
            alert("Rellene todo el formulario");
        } else {            
            $.post(
                "<?= site_url('consultorio/editar_cita') ?>", {
                    cit: $("#CIT_CODCIT").val(),
                    ser: $("#CIT_SERIE").val(),
                    nro: parseInt($("#CIT_NUMERO").val()),
                    mon: $("#CIT_MONTO").val()
                },
                function(htmlexterno) {
                    var datos = eval(htmlexterno);
                    dtable.ajax.reload();
                    $("#modal-boleta").modal("hide");
                }
            );
        }
    });

    ///// CONFIRMAR CITA /////
    $('#table_citas tbody').on('click', '#confirPac', function(event) {
        //CLI_RUC_ESPOSA
        //EDAD

        var data = dtable.row($(this).parents('tr')).data();

        if ($.trim(data['CLI_RUC_ESPOSA']) !== '' && $.trim(data['EDAD']) !== ''){
            //SI TIENE DATOS NECESARIOS CONFIRMAMOS CITA
            if (confirm($.trim(data['CLI_NOMBRE']) + ' Confirma su Cita! ')) {            
                $.post("<?= site_url('consultorio/confirma_cita') ?>", {
                        cit: data.CIT_CODCIT,
                        est: 1
                    },
                    function(htmlexterno) {
                        var datos = eval(htmlexterno);
                        dtable.ajax.reload();
                    }
                );
            }
        }else{
            //SI NO TIENE DATOS 
            $("#modal-paciente").modal("show");
            $("#nuevo").hide();
            $("#editar").hide();
            $("#seleccionarp").hide();
            $("#guardar").show();
            $("#cancelar").show();
            $("#importar").hide();
            $("input#CLI_CODCLIE").val(data['CLI_CODCLIE']);
            $("input#CLI_RUC_ESPOSO").val($.trim(data['CLI_RUC_ESPOSO']) !== ''?$.trim(data['CLI_RUC_ESPOSO']):$.trim(data['CLI_RUC_ESPOSA']));
            $("input#CLI_NOMBRE").val(data['CLI_NOMBRE']);
            $("input#CLI_CASA_DIREC").val(data['CLI_CASA_DIREC']);
            $("input#CLI_TELEF1").val(data['CLI_TELEF1']);
            $("input#CLI_FECHA_NAC").val($.trim(data['EDAD']) !== ''?new Date(data['CLI_FECHA_NAC']):'');
            
            $("input#estado").val("editar");
  
        }

    });
///// ELIMINAR CITA /////
$('#table_citas tbody').on('click', '#eliminarCit', function(event) {
        var data = dtable.row($(this).parents('tr')).data();
            //SI TIENE DATOS NECESARIOS CONFIRMAMOS CITA
            if (confirm($.trim(data['CLI_NOMBRE']) + ' Esta seguro de ELIMINAR está Cita! ')) {            
                $.post("<?= site_url('consultorio/eliminar_cita') ?>", {
                        cit: data.CIT_CODCIT,
                        est: 1
                    },
                    function(htmlexterno) {
                        var datos = eval(htmlexterno);
                        dtable.ajax.reload();
                    }
                );
            }
    });

///// EDITAR PACIENTE /////
$('#table_citas tbody').on('click', '#verPac', function(event) {
        var data = dtable.row($(this).parents('tr')).data();
            //SI NO TIENE DATOS 
            $("#modal-paciente").modal("show");
            $("#nuevo").hide();
            $("#editar").hide();
            $("#seleccionarp").hide();
            $("#guardar").show();
            $("#cancelar").show();
            $("#importar").hide();
            $("input#CLI_CODCLIE").val(data['CLI_CODCLIE']);
            $("input#CLI_RUC_ESPOSO").val($.trim(data['CLI_RUC_ESPOSO']) !== ''?$.trim(data['CLI_RUC_ESPOSO']):$.trim(data['CLI_RUC_ESPOSA']));
            $("input#CLI_NOMBRE").val(data['CLI_NOMBRE']);
            $("input#CLI_CASA_DIREC").val(data['CLI_CASA_DIREC']);
            $("input#CLI_TELEF1").val(data['CLI_TELEF1']);
            $("input#CLI_FECHA_NAC").val($.trim(data['EDAD']) !== ''?new Date(data['CLI_FECHA_NAC']):'');            
            $("input#estado").val("editar"); 
    });

///// EDITAR CITA /////
$('#table_citas tbody').on('click', '#editCita', function(event) {
    var data = dtable.row($(this).parents('tr')).data();
    $("#modal-boleta").modal("show");
    $('#CIT_CODCIT').val(data['CIT_CODCIT']);
    $('#CIT_SERIE').val(data['CIT_SERIE']);
    $('#CIT_NUMERO').val(data['CIT_NUMERO']); 
});
    
///// IMPORTAR PERSONAS /////
    $("#importarp").click(function() {
        $.post(
            "<?= site_url('personas/get_persona_sunat') ?>", {
                ruc: $("input#CLI_RUC_ESPOSO").val(),
                tipo: 2,
            },
            function(htmlexterno) {
                var datos = eval(htmlexterno);
                if (datos[0] == null) {
                    alert("DNI o RUC no válido o no registrado");
                    $("input#CLI_RUC_ESPOSO").focus();
                }else if (datos[0]=='nada') {
                    alert('DNI o RUC no se encuentra, registrelo manualmente.');
                    $("input#CLI_RUC_ESPOSO").focus();
                } else {
                    $("input#CLI_RUC_ESPOSO").val(datos[0]);
                    $("input#CLI_NOMBRE").val(datos[1]);
                    $("input#CLI_CASA_DIREC").val(datos[2]);
                    $("input#CLI_TELEF1").val(datos[3]);
                    $("input#CLI_CODCLIE").val(datos[4]);

                    if (datos[5] == "nuevo") {
                        toastr.success("Ingresa dirección y teléfono. Luego presione Guardar");
                    } else {
                        $("#nuevo").show();
                        $("#editar").show();
                        $("#seleccionarp").show();
                        $("#guardar").hide();
                        $("#cancelar").show();
                        $("#importarp").hide();
                        alert("El DNI:" + datos[0] + " ya esta registrado!");
                    }
                }
            }
        );
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
        $("#modal-paciente").modal("hide");
    })
});

function agregarPaciente() {
    $("#modal-paciente").modal("show");
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