<?php /** @var CodeIgniter\View\View $this*/ ?>
<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<?php $session = session(); ?>
<!-- Content Header (Page header) -->
<section class="content-header">
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="sticky-top mb-3">
                    <div class="card shadow">
                        <div class="card-header">
                            <h4 class="card-title">Locales</h4>
                        </div>
                        <div class="card-body">
                        <a id="caja_cnt" class="btn btn-app bg-<?=$color=='success'?$color:'disabled';?>"><i class="fas fa-inbox"></i>Caja Centro</a>
                        <a id="caja_pmz" class="btn btn-app bg-<?=$color=='info'?$color:'disabled';?>"><i class="fas fa-inbox"></i>Caja PMeza</a>
                        <a id="caja_jjc" class="btn btn-app bg-<?=$color=='danger'?$color:'disabled';?>"><i class="fas fa-inbox"></i>Caja Juanjuicillo</a>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-header border-0">
                            <h3 class="card-title">Cajas del Día</h3>                       
                        </div>
                        <div class="card-body table-responsive p-0">
                        <table id="cajas_diario" class="table table-striped table-valign-middle">
                            <thead>
                            <tr>
                                <th>CAJA</th>
                                <th>RESPONSABLE</th>
                                <th style="width:40px">ESTADO</th>  
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        </div>
                    </div>                    
                </div>
            </div>

            <div class="col-md-9">                
                <div id="caja_apertura" class="card card-<?=$color;?> shadow">
                    <div class="card-header">
                        <h3 class="card-title"><marquee><?php                                        
                            switch ($session->get('caja')) {
                                case '1':
                                    $txtcaja = "CAJA CENTRO";
                                break;
                                case '2':
                                    $txtcaja = "CAJA JUANJUICILLO";
                                break;
                                case '3':
                                    $txtcaja = "CAJA PEÑAMEZA";
                                break;
                                default:
                                    $txtcaja = "NO CAJA SELECCIONADA";
                                break;
                                }
                                echo $txtcaja;
                            ?>
                        </marquee></h3>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="LOCAL" name="LOCAL" value="<?=$session->get('caja')?>">
                        <div class="row">
                            <div class="col-sm-2">
                                <!-- text input -->
                                <div class="form-group">
                                    <label>Caja Nro</label>
                                    <input type="text" id="CAJ_NRO" class="form-control" readonly value="0">                                
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="text" id="CAJ_FECHA" class="form-control" readonly value="<?=date('d-m-Y')?>">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Responsable</label>
                                    <select class="form-control" name="VEM_CODVEN" id="VEM_CODVEN"> <!-- disabled="disabled" -->
                                        <?php foreach ($empleados as $empleado) { ?>
                                            <option value="<?= $empleado->VEM_CODVEN; ?>"><?= $empleado->VEM_CODVEN . ' - ' . $empleado->VEM_NOMBRE; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>                       
                    </div>
                    <div class="card-footer">
                        <button type="button" id="btn_abrir" class="btn btn-<?=$color;?>">Abrir Caja</button>
                        <button type="button" id="btn_editar" class="btn btn-<?=$color;?>" style="display:none" data-toggle="modal" data-target="#modal-modifica-caja">Editar Caja</button> 
                        <button type="button" id="btn_cerrar" class="btn btn-<?=$color;?> float-right" style="display:none">Cerrar Caja</button>
                    </div>
                </div>
                <div id="caja_movimientos" class="card card-<?=$color;?> shadow" style="display:none">
                    <div class="card-header">
                        <h3 class="card-title">MOVIMIENTOS DE CAJA</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table id="table_movimientos" class="table table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th>TIPO</th>
                                    <th>DESCRIPCION</th>
                                    <th>MONTO</th>     
                                    <th style="width: 10px"></th>                         
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>                                          
                    </div>
                    <div class="card-footer" style="display: flex; align-items: center;">
                        <button type="button" class="btn btn-<?=$color;?>" data-toggle="modal" style="margin-right: 10px;" data-target="#modal-movimiento">Agregar Movimientos de Caja</button>
                        <div id="contenedor-inventario"></div>
                    </div>
                </div>
                <div id="caja_cerrar" class="card card-<?=$color;?> shadow" style="display: none">
                    <div class="card-header">
                        <h3 class="card-title">CERRAR CAJA</h3>
                    </div>
                    <div class="card-body">                            
                        <div class="col-sm-12 row">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-caret-right"></i> Serie:</span></div>
                                <input type="number" class="form-control" placeholder="Serie" name="CAJ_NUMSER" id="CAJ_NUMSER">                            
                                <input type="text" class="form-control" value="Nro Fac/Bol/Guia:" readonly>
                                <input type="number" class="form-control" placeholder="Nro Fac/Bol/Guia" name="CAJ_NUMFAC" id="CAJ_NUMFAC">
                                <input type="text" class="form-control" value="Monto Efectivo:" readonly>
                                <input type="number" min="0" step=".01" class="form-control" placeholder="Monto Efectivo" name="CAJ_EFECTIVO" id="CAJ_EFECTIVO">
                                <button type="button" id="btn_cerrar_caja" class="btn btn-success btn-flat">Cerrar Caja</button>
                                <span class="input-group-append"><button type="button" id="btn_cancelar" class="btn btn-danger btn-flat">Cancelar</button></span>
                            </div>
                        </div>
                        <div id='tiempo_ap' class="col-sm-8 row" style="display: none">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-caret-right"></i> Serie:</span></div>
                                <input type="number" class="form-control" placeholder="Serie" name="CAJ_NUMSER2o" id="CAJ_NUMSER2o">                            
                                <input type="text" class="form-control" value="Nro Fac/Bol/Guia:" readonly>
                                <input type="number" class="form-control" placeholder="Nro Fac/Bol/Guia" name="CAJ_NUMFA2o" id="CAJ_NUMFA2o">
                                <button type="button" id="btn_agrega_venta" class="btn btn-success btn-flat">Agregar Nuevas Ventas</button>
                                <span class="input-group-append"><button type="button" id="btn_cancelar_ap" class="btn btn-danger btn-flat">Cancelar</button></span>
                            </div>
                        </div>
                        <div id='tiempo_inf' class="col-sm-8 row" style="display: none">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><span class="input-group-text bg-warning clock"> </span></div>
                                <button type="button" id="btn_comprueba_venta" class="btn btn-success btn-flat">Buscar más Ventas</button>
                                <span class="input-group-append"><button type="button" id="btn_cancelar_inf" class="btn btn-danger btn-flat">Cancelar</button></span>
                            </div>
                        </div>
                    </div>
                    <div class="overlay">
                        <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                    </div>
                </div> 
            </div>
        </div>
    </div>
</section>

<!-- Modal MOVIMIENTOS -->
<div class="modal fade" id="modal-movimiento">
    <div class="modal-dialog">
        <div class="modal-content bg-<?=$color;?>">
            <div class="modal-header">
                <h4 class="modal-title">Movimiento de Caja</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="form-group row">
                        <label for="cmvtipo" class="col-sm-2 col-form-label">Motivo</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="CMV_TIPO">
                                <?php foreach ($motivo_gasto as $clave => $valor) { ?>
                                    <option value="<?= $clave; ?>"><?= $valor; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row" id="DV_CMV_CODVEN">
                        <label for="cmvvend" class="col-sm-2 col-form-label">Trabaj</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="CMV_CODVEN" name="CMV_CODVEN">
                                <?php foreach ($empleados as $empleado) { ?>
                                    <option value="<?= $empleado->VEM_CODVEN; ?>"><?= $empleado->VEM_CODVEN . ' - ' . trim($empleado->VEM_NOMBRE); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="cvmmotivo3" class="col-sm-2 col-form-label">Motivo</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="CMV_DESCRI" placeholder="Motivo" style="text-transform:uppercase">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="cvmmonto3" class="col-sm-2 col-form-label">Monto</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="CMV_MONTO" placeholder="0.00">
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" id="movimientos" class="btn btn-primary">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- Modal MODIFICA CAJA -->
<div class="modal fade" id="modal-modifica-caja">
    <div class="modal-dialog">
        <div class="modal-content bg-<?=$color;?>">
            <div class="modal-header">
                <h4 class="modal-title">Modificar Caja</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="form-group row" id="DV_CMV_CODVEN2">
                        <label for="cmvvend" class="col-sm-2 col-form-label">Trabaj</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="CMV_CODVEN2" name="CMV_CODVEN2">
                            <?php foreach ($empleados as $empleado) {  ?>
                                <option value="<?= $empleado->VEM_CODVEN; ?>"><?= $empleado->VEM_CODVEN . ' - ' . $empleado->VEM_NOMBRE; ?></option>
                            <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" id="modificaCaja" class="btn btn-primary">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- /.content -->
<?= $this->endSection(); ?>
<?= $this->section('footer'); ?>
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/jquery-confirm/css/jquery-confirm.css">
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/jquery-confirm/js/jquery-confirm.js"></script>
<script>
    $(document).ready(function() {
        let timeout;
        // Function to reset the timer
        const resetTimer = function () {
            clearTimeout(timeout); // Clear the previous timer
            timeout = setTimeout(function () {
                location.reload(); // Reload the page
            }, 300000); // 5 minutes = 300,000 milliseconds
        };
        // Reset timer on any of these events
        $(document).on('mousemove keydown scroll click', resetTimer);

        // Initialize the timer when the page loads
        resetTimer();
        var d = new Date();
        var tiempo = { hora: 0, minuto: 0, segundo: 0 };
        var running_time = null;
        var dtable = $("#cajas_diario").DataTable({
        ajax: {
            url: "<?= site_url('caja/get_cajas_dia') ?>",
            type: "POST",
            dataSrc: "",
            data: {
                loc: function() {
                    return $("#LOCAL").val();
                },
                anio:d.getFullYear(),
                mes:d.getMonth()+1,
                dia:d.getDate(),
            },
        },
        columns: [
            {data: "CAJ_NRO"},
            {data: "VEM_NOMBRE"},
            {data: "CAJ_ESTADO",
                render: function(data, type, row, meta) {
                    if (row.CAJ_ESTADO==0) {
                        var rpt = '<a href="<?=site_url('caja/imprimircaja')?>/'+row.CAJ_NRO+'/'+<?=$session->get('caja')?>+'" class="btn btn-block bg-primary btn-sm"> <i class="fas fa-print"></i></a>';   
                    } else {
                        var rpt = "<button id='verCaja' class='btn btn-block bg-primary btn-sm'><i class='fas fa-eye'></i> Ver</button>";
                    }
                    return rpt
                }            
            }
        ],
        fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {  
                if (aData.CAJ_ESTADO == 1) {
                    $(nRow).addClass('bg-success');
                    vercajita(aData);
                } 
                if (aData.CAJ_ESTADO == 2){
                    $(nRow).addClass('bg-secondary');
                }
            return nRow;
            },
        ordering: false,
        searching: false,
        paging: false,
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        oLanguage: {"sInfo" : " "}
    });
    var dtableMovimiento = $('#table_movimientos').DataTable({
        ajax: {
            url: "<?= site_url('caja/listar_movimientos') ?>",
            type: "POST",
            dataSrc: '',
            data: {
                nro_caja: function() {
                    return $("#CAJ_NRO").val();
                }
            }
        },
        processing: true,
        columns: [
            {data: 'CMV_TIPO',render:function(data, type, row, meta){
                var motivos = <?php echo json_encode($motivo_gasto); ?>;
                return motivos[row.CMV_TIPO];
            }},
            {data: 'CMV_DESCRIPCION'},
            {data: 'CMV_MONTO'},
            {data: 'CMV_NRO',
                render: function(data, type, row, meta) {
                    rpt = '';
                    if (row.CMV_TIPO==10) {
                        var rpt = '<a href="<?=site_url('comprobante/deposito')?>/'+<?=$session->get('caja')?>+'/'+row.CMV_NRO+'" class="btn btn-outline-primary btn-sm"> <i class="fas fa-print"></i></a>';   
                    }
                    rpta= '<div class="btn-group" role="group" aria-label="Basic example">'+"<button type='button' id='delmov' class='btn btn-outline-danger btn-block'><i class='fa fa-trash'></i></button>"+rpt+"</div>";
                    return rpta;
                }
            },
        ],
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        paging: false,
        ordering: false,
        info: false,
        bFilter: false,
        searching: false,

    });
    $("#verCaja").click(function () {
        alert('ohhhh');
    });
    function vercajita(dato){
        $('#caja_cerrar').slideUp();
        $('#caja_movimientos').slideDown();
        $("#btn_abrir").slideUp();
        $("#btn_editar").slideDown();
        $("#btn_cerrar").slideDown();
        $("#VEM_CODVEN").attr("disabled", true);
        $("#CAJ_NRO").val(dato.CAJ_NRO);
        $("#btn_cerrar").text('Cerrar Caja de '+dato.VEM_NOMBRE);
        $("#VEM_CODVEN option[value='"+dato.CAJ_CODVEN+"']").attr("selected", "selected");
        $("#CMV_CODVEN option[value='"+dato.CAJ_CODVEN+"']").attr("selected", "selected");
        $("#CMV_CODVEN2 option[value='"+dato.CAJ_CODVEN+"']").attr("selected", "selected");
        dtableMovimiento.ajax.reload();
        // Generar botón de inventario bebidas
        const cajaSession = '<?= $session->get("caja") ?>';
        const nroCaja = dato.CAJ_NRO;
        const baseUrl = '<?= base_url() ?>';
        const url = `${baseUrl}/comprobante/inventario_bebidas/${cajaSession}/${nroCaja}`;
        const botonHTML = `<a href="${url}" class="btn btn-warning" target="_blank" title="Imprimir inventario de bebidas para esta caja">
                <i class="fa fa-shipping-fast"></i> Inventario Bebidas
            </a>`;
        $("#contenedor-inventario").html(botonHTML);
    }
    $("#btn_nuevo").click(function () {
        $(this).slideUp();
    });
    $("#btn_cerrar").click(function () {
        $('.overlay').show();
        $('#caja_cerrar').slideDown();
        $('#caja_movimientos').slideUp();
        $('#tiempo_ap').hide();
        $('#tiempo_inf').hide();
        tiempo = { hora: 0, minuto: 0, segundo: 0 };
        running_time = null;
        hora();
        $(this).slideUp();
        $.post("get_nro_doc", {}, function (htmlexterno) {
            var obj = jQuery.parseJSON(htmlexterno);
            $("#CAJ_NUMSER").val(obj.ALL_NUMSER);
            $("#CAJ_NUMFAC").val(obj.ALL_NUMFAC);
            $('.overlay').hide();
            $("#CAJ_EFECTIVO").focus();
        });
    });

    $("#btn_comprueba_venta").click(function () {
        $('.overlay').show();
        $.post("get_nro_doc", {}, function (htmlexterno) {
            var obj = jQuery.parseJSON(htmlexterno);
            $("#CAJ_NUMSER2o").val(obj.ALL_NUMSER);
            $("#CAJ_NUMFA2o").val(obj.ALL_NUMFAC);
        });
        $('.overlay').hide();
        $('#tiempo_ap').show();
    });
    $("#btn_agrega_venta").click(function () {
     
        $("#CAJ_NUMSER").val($("#CAJ_NUMSER2o").val());
        $("#CAJ_NUMFAC").val($("#CAJ_NUMFA2o").val());
        
        $('#tiempo_ap').hide();
        $('#tiempo_inf').hide();
        clearInterval(running_time);
        tiempo = {
        hora: 0,
        minuto: 0,
        segundo: 0
    };
    });

    $("#btn_cerrar_caja").click(function () {        
    if($("#CAJ_EFECTIVO").val()>0){
        $.confirm({
            title: 'Cerrar Caja',
            icon: 'fa fa-warning',
            content: '<b>¿Vas a cerrar esta caja.?</b></br><blockquote class="quote-success"><h5>Atención!</h5><p>“Si no pusiste bien el monto u olvidaste de agregar un movimiento...</br>ANÓTALO en OBSERVACIONES.”</br><b>** No intentes arreglarlo…</br>Simplemente no puedes!!! **</b></br>Gracias.</p></blockquote>',
            type: 'green',
            buttons: {   
                ok: {
                    text: "Si quiero cerrar la caja",
                    btnClass: 'btn-primary',
                    keys: ['enter'],
                    action: function(){
                        $.post("cerrar_caja2", {
                            local:  $("#LOCAL").val(),
                            CAJ_NRO:$("#CAJ_NRO").val(),
                            CAJ_NUMSER:$("#CAJ_NUMSER").val(),
                            CAJ_NUMFAC:$("#CAJ_NUMFAC").val(),
                            CAJ_EFECTIVO:$("#CAJ_EFECTIVO").val(),
                            CAJ_FECHA:$("#CAJ_FECHA").val()
                        }, function (htmlexterno) {
                            location.reload(true);
                        });
                    }
                },
                cancel: function(){
                    $.alert({
                        title: 'Gracias',
                        content: 'Errar es humano, perdonar es divino, rectificar es de sabios.',
                        type: 'green',
                    });
                }
            }
        });


    }else{
        $.alert({
            title: 'Error',
            content: 'Ingresa el monto de tu caja <i class="fa-regular fa-face-angry"></i>',
            type: 'red',
        });
    }
    });
    $("#btn_abrir").click(function () {
        $.post("abrircaja2", {
            local:  $("#LOCAL").val(),
            resp: $("#VEM_CODVEN").val()
        }, function (htmlexterno) {
            var obj = jQuery.parseJSON(htmlexterno);
            dtable.ajax.reload();
            vercajita(resp);
        });
    });
    $("#modificaCaja").click(function () {
        $.post("editar_caja2", {
            local: $("#LOCAL").val(),
            caja: $("#CAJ_NRO").val(),
            resp: $("#CMV_CODVEN2").val()
        }, function (htmlexterno) {
            location.reload(true);
        });
    });
    $("#btn_cancelar").click(function () {
        $('#caja_cerrar').slideUp();
        $('#caja_movimientos').slideDown();
        $('#btn_cerrar').slideDown();
        $('#tiempo_ap').hide();
        $('#tiempo_inf').hide();
        clearInterval(running_time);
    });
    $("#btn_cancelar_ap").click(function () {
        $('#caja_cerrar').slideUp();
        $('#caja_movimientos').slideDown();
        $('#btn_cerrar').slideDown();
        $('#tiempo_ap').hide();
        $('#tiempo_inf').hide();
        clearInterval(running_time);
    });
    $("#btn_cancelar_inf").click(function () {
        $('#caja_cerrar').slideUp();
        $('#caja_movimientos').slideDown();
        $('#btn_cerrar').slideDown();
        $('#tiempo_ap').hide();
        $('#tiempo_inf').hide();
        clearInterval(running_time);
    });
    $("#movimientos").click(function () {
        $.post("agregar_movimiento", {
            cmv_tipo: $( "select#CMV_TIPO option:checked" ).val(),
            cmv_caja: $("input#CAJ_NRO").val(),
            cmv_codven: $( "select#CMV_CODVEN option:checked" ).val(),
            cmv_descri: $("#CMV_DESCRI").val(),
            cvm_monto:$("#CMV_MONTO").val()
        }, function (htmlexterno) {
            dtableMovimiento.ajax.reload();
            $('#modal-movimiento').modal('hide');
        });
    });
    $("#CMV_TIPO" ).change(function() {
        var opt = $(this).find("option:selected").attr('value'); 
        if(opt == 6 || opt == 7){
            $('#DV_CMV_CODVEN').show();
        }else{
            $('#DV_CMV_CODVEN').hide();
        }
        $('#CMV_MONTO').removeAttr('value');
    });
    $('#modal-movimiento').on('shown.bs.modal', function(e) {
        var tipo = $("#CMV_TIPO" ).find("option:selected").attr('value');
        $('#CMV_MONTO').val('');
        $('#CMV_DESCRI').val('');
    });
    $('#table_movimientos tbody').on('click', '#delmov', function(event) {
        var data = dtableMovimiento.row($(this).parents('tr')).data();
        var motivos = <?php echo json_encode($motivo_gasto); ?>;
        if (confirm('Está seguro de ELIMINAR esté Movimiento:'+$.trim(motivos[data['CMV_TIPO']]))) {            
            $.post("<?= site_url('caja/eliminar_movimiento') ?>", {
                    cmv_nro: data['CMV_NRO'],                    
                    local: $("#LOCAL").val()
                },
                function(htmlexterno) {
                    var datos = eval(htmlexterno);
                    dtable.ajax.reload();
                }
            );
        }
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
                caja:x ,opci: 'caja'
            },
            success: function(result) {
                location.reload();
                return false;
            },
            error: function(result) {
                alert('error');
            }
        });
    }


    function hora(){
        running_time = setInterval(function(){
            // Segundos
            tiempo.segundo++;
            if(tiempo.segundo >= 60){
                tiempo.segundo = 0;
                tiempo.minuto++;
            }
            // Minutos
            if(tiempo.minuto >= 60){
                tiempo.minuto = 0;
                tiempo.hora++;
            }
            var hor = tiempo.hora < 10 ? '0' + tiempo.hora : tiempo.hora;
            var min = tiempo.minuto < 10 ? '0' + tiempo.minuto : tiempo.minuto;
            var seg = tiempo.segundo < 10 ? '0' + tiempo.segundo : tiempo.segundo;
            if(hor>0||min>1){
                $('#tiempo_inf').show();
                $(".clock").html('A transcurrido &nbsp;<i class="fas fa-clock"> </i>&nbsp; ' + hor + ':' + min + ':' + seg + ', pueden haber mas ventas:');
            }
        }, 1000);
    }
});
<?php 
if($session->get('item')){ ?>
    $(document).Toasts('create', {
        class: 'bg-danger',
        title: 'Atención',
        body: '<?=$session->get('item')?>',
        animation:	true,
        autohide: true,
        delay:	5500
      })
<?php } ?>
</script>
<?= $this->endSection(); ?>

