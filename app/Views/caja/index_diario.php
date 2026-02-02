<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<?php $session = session(); ?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">                		
                <a id="caja_cnt" class="btn btn-app bg-<?=$color=='success'?$color:'disabled';?>">
                <i class="fas fa-inbox"></i>Caja Centro
                </a>
                <a id="caja_pmz" class="btn btn-app bg-<?=$color=='info'?$color:'disabled';?>">
                <i class="fas fa-inbox"></i>Caja PMeza
                </a>
                <a id="caja_jjc" class="btn btn-app bg-<?=$color=='danger'?$color:'disabled';?>">
                <i class="fas fa-inbox"></i>Caja Juanjuicillo
                </a>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>
<?php
$mes_ar=array("01"=>"ENE","02"=>"FEB","03"=>"MAR","04"=>"ABR","05"=>"MAY","06"=>"JUN","07"=>"JUL","08"=>"AGO","09"=>"SEP","10"=>"OCT","11"=>"NOV","12"=>"DIC",);
?> 
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-<?=$color;?>">
                <div class="card-header">
                        <h3 class="card-title">MOVIMIENTO DE CAJAS</h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm mb-12">
                                <div class="input-group-prepend">
                                <button type="button" class="btn btn-danger">Mes</button>
                                </div>
                                <select id="mes" name="mes" class="form-control">
                                    <option value="<?=date('m', strtotime('-4 month'))?>" data-anio="<?=date('Y', strtotime('-4 month'))?>" <?= $session->get('mes_caja')==date('m', strtotime('-4 month'))?'selected="selected"':'';?> ><?=date('Y', strtotime('-4 month'))?>-<?=$mes_ar[date('m', strtotime('-4 month'))]?></option>
                                    <option value="<?=date('m', strtotime('-3 month'))?>" data-anio="<?=date('Y', strtotime('-3 month'))?>" <?= $session->get('mes_caja')==date('m', strtotime('-3 month'))?'selected="selected"':'';?> ><?=date('Y', strtotime('-3 month'))?>-<?=$mes_ar[date('m', strtotime('-3 month'))]?></option>
                                    <option value="<?=date('m', strtotime('-2 month'))?>" data-anio="<?=date('Y', strtotime('-2 month'))?>" <?= $session->get('mes_caja')==date('m', strtotime('-2 month'))?'selected="selected"':'';?> ><?=date('Y', strtotime('-2 month'))?>-<?=$mes_ar[date('m', strtotime('-2 month'))]?></option>
                                    <option value="<?=date('m', strtotime('-1 month'))?>" data-anio="<?=date('Y', strtotime('-1 month'))?>" <?= $session->get('mes_caja')==date('m', strtotime('-1 month'))?'selected="selected"':'';?> ><?=date('Y', strtotime('-1 month'))?>-<?=$mes_ar[date('m', strtotime('-1 month'))]?></option>
                                    <option value="<?=date('m')?>" data-anio="<?=date('Y')?>" <?= $session->get('mes_caja')==date('m')?'selected="selected"':'';?> ><?=date('Y')?>-<?=$mes_ar[date('m')]?></option>

                                </select>
          
                                <div class="input-group-append">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>    
             
                            </div>
                        </div>
                    </div>                   
                    <!-- /.card-header -->
                    <?php $session = session(); ?>

                    <div class="card-body table-responsive p-0">
                        <table id="cajas_diario" class="table table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th style="width: 10px">DIA</th>
                                    <th>CAJA</th>
                                    <th>RESPONSABLE</th>
                                    <th style="width: 40px">ESTADO</th>
                                    <th style="width: 180px"></th>
                                </tr>
                            </thead>
                            <tbody><?php foreach ($ventas as $venta) { ?>
                                <tr>                      
                                    <td><small class="text-success mr-1"><i class="fas fa-calendar-day"></i><?= $venta->DIA; ?></small></td>
                                    <td><?= $venta->CAJ_NRO; ?></td>
                                    <td><?= trim($venta->VEM_NOMBRE)?></td>
                                    <td><?= $venta->CAJ_ESTADO == 0 ? '<small class="text-danger mr-1">CERRADO</small>' : '<small class="text-success mr-1">ABIERTO</small>'; ?></td>
                                    <td><?= $venta->CAJ_ESTADO == 0 ? '<a href="'. site_url('caja/imprimircaja').'/'.$venta->CAJ_NRO.'/'.$session->get('caja').'" class="text-muted"> <i class="fas fa-print"></i></a>' : ''; ?> &nbsp;
                                    <?php if($session->get('user_id')=='ADMIN'){ ?>
                                        <a class="openPopup" data-href="<?= site_url('caja/ver_caja') .'/'.$venta->CAJ_NRO.'/'.$ncaja?>/"><i class='fas fa-eye'></i></a> &nbsp;
                                        <?= $venta->CAJ_ESTADO == 0 ? '<a href="'. site_url('caja/editarcaja').'/'.$venta->CAJ_NRO.'/'.$venta->CAJ_NRO.'" class="text-muted"><i class="fas fa-lock text-maroon"></i></a>' : '<a href="'. site_url('caja/bloqueacaja').'/'.$venta->CAJ_NRO.'/'.$venta->CAJ_NRO.'" " class="text-muted"><i class="fa fa-unlock text-success"></i></a>'; ?>&nbsp;
                                        <?= $venta->CAJ_ESTADO == 0 ? '<a class="editarcaja" href="#" data-href="'.$venta->CAJ_NRO.'/'.$ncaja.'"><i class="fa fa-edit"></i></a>':"";  ?>&nbsp;
                                        <?= $venta->CAJ_ESTADO == 0 ? '<a class="eliminarcaja" href="#" data-href="'. site_url('caja/eliminarcaja').'/'.$venta->CAJ_NRO.'/'.$ncaja.'" class="text-muted"><i class="fa fa-trash"></i></a>' : ''; ?> &nbsp;
                                    <?php } ?></td>
                                </tr>
                                <?php } ?>  



                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-6">
                <div class="card card-<?=$color;?>">
                    <div class="card-header">
                        <h3 class="card-title">CAJA</h3>
                        <input type="hidden" id="LOCAL" name="LOCAL" value="<?=$session->get('caja')?>">
                        <div class="card-tools"></div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-2">
                                <!-- text input -->
                                <div class="form-group">
                                    <label>Caja Nro</label>
                                    <input type="text" class="form-control" id="CAJ_NRO" name="CAJ_NRO" value='0' readonly>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="text" class="form-control" id="CAJ_FECHA" readonly>
                                    
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Responsable</label>
                                    <select class="form-control" name="VEM_CODVEN" disabled="disabled">
                                        <?php foreach ($empleados as $empleado) { ?>
                                            <option value="<?= $empleado->VEM_CODVEN; ?>"><?= $empleado->VEM_CODVEN . ' - ' . $empleado->VEM_NOMBRE; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="card card-<?=$color;?>">
                                <div class="card-header">
                                    <h3 class="card-title">Movimientos de Caja</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body p-0">
                                    <table id="cajas_diario_movimientos" class="table table-striped table-valign-middle">
                                        <thead>
                                        <tr>
                                            <th>TIPO</th>
                                            <th>DESCRIPCION</th>
                                            <th>MONTO</th>     
                                            <th style="width: 10px"></th> 
                                            <th style="width: 10px"></th> 
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                         <tr>
                                             <th><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-movimiento">Agregar</button></th>
                                             <th></th>
                                             <th></th>     
                                             <th></th> 
                                             <th></th> 
                                         </tr>
                                    </table>
                                    
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <div id="cerrar_caja"  style="display: true">
                            <div class="col-sm-12 row">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend"><span class="input-group-text"> Serie:</span></div>
                                    <input type="text" class="form-control" id="CAJ_NUMSER" readonly>                            
                                    <input type="text" class="form-control" value="Nro Fac/Bol/Guia:" readonly>
                                    <input type="number" class="form-control" id="CAJ_NUMFAC" readonly>
                                    <input type="text" class="form-control" value="Monto Efectivo:" readonly>
                                    <span class="input-group-append"><input type="number" min="0" step=".01" class="form-control" id="CAJ_EFECTIVO"></span>
                                </div>

                                
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                        <button type="button" id="btn_cancelar" class="btn btn-danger">Cancelar</button>
                            <button type="button" id="btn_cerrar" class="btn btn-<?=$color;?>">Guardar</button>
                        </div>
                    </div>
                        
                </div>
            </div>
                                    </table>
                                    
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <div id="cerrar_caja"  style="display: true">
                            <div class="col-sm-12 row">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend"><span class="input-group-text"> Serie:</span></div>
                                    <input type="text" class="form-control" id="CAJ_NUMSER" readonly>                            
                                    <input type="text" class="form-control" value="Nro Fac/Bol/Guia:" readonly>
                                    <input type="number" class="form-control" id="CAJ_NUMFAC" readonly>
                                    <input type="text" class="form-control" value="Monto Efectivo:" readonly>
                                    <span class="input-group-append"><input type="number" min="0" step=".01" class="form-control" id="CAJ_EFECTIVO"></span>
                                </div>

                                
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                        <button type="button" id="btn_cancelar" class="btn btn-danger">Cancelar</button>
                            <button type="button" id="btn_cerrar" class="btn btn-<?=$color;?>">Guardar</button>
                        </div>
                    </div>
                        
                </div>
            </div>
            <!-- /.col -->
        </div>

    </div><!-- /.container-fluid -->
</section>

<!-- Modal -->
<div class="modal fade" id="modal-movimiento">
    <div class="modal-dialog">
        <div class="modal-content bg-<?=$color;?>">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-cash-register"></i> Movimiento de Caja</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="CMV_NRO" value="0">
                <div class="card-body">
                    <div class="form-group row">
                        <label for="CMV_TIPO" class="col-sm-3 col-form-label">Motivo</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="CMV_TIPO">
                                <?php foreach ($motivo_gasto as $clave => $valor) { ?>
                                    <option value="<?= $clave; ?>"><?= $valor; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row" id="DV_CMV_CODVEN" style="display:none;">
                        <label for="CMV_CODVEN" class="col-sm-3 col-form-label"><i class="fas fa-user"></i> Empleado</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="CMV_CODVEN" name="CMV_CODVEN">
                                <option value="">Seleccionar empleado...</option>
                                <?php foreach ($empleados as $empleado) { ?>
                                    <option value="<?= $empleado->VEM_CODVEN; ?>"><?= $empleado->VEM_CODVEN . ' - ' . trim($empleado->VEM_NOMBRE); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row" id="DV_CMV_COMPROBANTE" style="display:none;">
                        <label for="CMV_COMPROBANTE" class="col-sm-3 col-form-label"><i class="fas fa-receipt"></i> Comproban.</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="CMV_COMPROBANTE">
                                <option value="">Seleccionar comprobante...</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="CMV_DESCRI" class="col-sm-3 col-form-label" id="LBL_MOTIVO">Descripción</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="CMV_DESCRI" placeholder="Motivo" style="text-transform:uppercase">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="CMV_MONTO" class="col-sm-3 col-form-label">Monto</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">S/.</span>
                                </div>
                                <input type="number" class="form-control" id="CMV_MONTO" placeholder="0.00" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cerrar</button>
                <button type="button" id="movimientos" class="btn btn-outline-light">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- /.modal -->
<!-- Modal cAJA -->
<div class="modal fade" id="myCaja" role="dialog">
    <div class="modal-dialog">        
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Reporte de Caja</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>                    
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>    
    </div>
</div>
<!-- Modal cAJA -->

<!-- /.content -->
<?= $this->endSection(); ?>
<?= $this->section('footer'); ?>
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() { 
        var nro_caja = 0;
        var motivos = <?php echo json_encode($motivo_gasto); ?>;
        var exportables = [11, 12, 13]; // Tipos de gastos exportables a egresos
        
        var dtable = $('#cajas_diario_movimientos').DataTable({
            ajax: {
                url: "<?= site_url('caja/listar_movimientos') ?>",
                type: "POST",
                dataSrc: "",
                data: function(d) {
                    d.nro_caja = nro_caja;
                    d.local = '<?=$ncaja?>';
                }
            },
            columns: [
                { data: 'CMV_TIPO',
                    render: function(data, type, row, meta) {  
                        return motivos[row.CMV_TIPO] || row.CMV_TIPO;
                    }
                },
                { data: 'CMV_DESCRIPCION',
                    render: function(data, type, row, meta) {
                        if((row.CMV_TIPO == 6 || row.CMV_TIPO == 7) && row.VEM_NOMBRE){
                            return '<strong>' + row.VEM_NOMBRE + '</strong><br>' + data;
                        }
                        return data;
                    }
                },
                { data: 'CMV_MONTO' },
                { data: 'CMV_MONTO',
                    render: function(data, type, row, meta) {        
                        var rpt = "<div class='btn-group'>";
                        rpt += "<button id='editMovim' class='btn btn-sm bg-warning' title='Editar'><i class='fas fa-edit'></i></button>";
                        rpt += "<button id='eliminaMovim' class='btn btn-sm bg-primary' title='Eliminar'><i class='fas fa-trash'></i></button>";
                        rpt += "</div>";
                        return rpt;
                    }
                },
                { data: 'CMV_NRO',
                    render: function(data, type, row, meta) {
                        if (exportables.includes(parseInt(row.CMV_TIPO))) {
                            var yaExportado = row.CMV_EGRESO_ID && row.CMV_EGRESO_ID > 0;
                            if (yaExportado) {
                                return '<button class="btn btn-block bg-success btn-sm" disabled title="Ya exportado"><i class="fas fa-check"></i></button>';
                            } else {
                                return '<button class="btn btn-block bg-info btn-sm exportar-btn" data-cmv-nro="' + row.CMV_NRO + '" title="Exportar a Egresos"><i class="fas fa-external-link-alt"></i></button>';
                            }
                        }
                        return '';
                    }
                },
            ],
            paging: false,
            ordering: false,
            info: false,
            searching: false,
        });

        var btable = $("#cajas_diario").DataTable({
            responsive: true,
            autoWidth: false,
            order: [[1, "desc"]],
            searching: false,
            paging: false,
            info: false,
            bFilter: false,            
        });

        // Eventos de Caja
        $("#btn_cerrar").click(function () {
            $.post("editar_caja3", {
                local: $("#LOCAL").val(),
                caja: $("#CAJ_NRO").val(),
                efec: $("#CAJ_EFECTIVO").val()
            }, function (htmlexterno) {
                dtable.clear().draw();
                $(document).Toasts('create', {
                    class: 'bg-success',
                    title: 'Caja',
                    body: 'Se guardó correctamente',
                    position: 'bottomRight',
                    icon: 'far fa-check-circle fa-lg',
                    autohide: true,
                    delay: 2500
                });
            });
        });

        $("#btn_cancelar").click(function () {
            dtable.clear().draw();
            $('#CAJ_NRO').val('');
            $('#CAJ_NUMSER').val('');
            $('#CAJ_NUMFAC').val('');
            $('#CAJ_EFECTIVO').val('');
            $('#CAJ_FECHA').val('');
        });

        // Lógica de Movimientos (Agregar / Editar)
        $("#movimientos").click(function () {
            var nro = $("#CMV_NRO").val();
            var url = nro > 0 ? "<?= site_url('caja/actualizar_movimiento') ?>" : "agregar_movimiento";
            
            var postData = {
                cmv_nro: nro,
                cmv_tipo: $("#CMV_TIPO").val(),
                cmv_caja: $("#CAJ_NRO").val(),
                cmv_codven: $("#CMV_CODVEN").val(),
                cmv_descri: $("#CMV_DESCRI").val(),
                cvm_monto: $("#CMV_MONTO").val()
            };

            $.post(url, postData, function (response) {
                if (nro > 0) {
                    if (response.success) {
                        $('#modal-movimiento').modal('hide');
                        dtable.ajax.reload();
                    } else {
                        alert(response.message || 'Error al actualizar');
                    }
                } else {
                    // Legacy agregar_movimiento returns HTML, we just reload
                    dtable.ajax.reload();
                    $('#modal-movimiento').modal('hide');
                }
            }, nro > 0 ? 'json' : '');
        });

        $('#cajas_diario_movimientos tbody').on('click', '#editMovim', function() {
            var data = dtable.row($(this).parents('tr')).data();
            $("#CMV_NRO").val(data.CMV_NRO);
            $("#CMV_TIPO").val(data.CMV_TIPO).trigger('change');
            $("#CMV_CODVEN").val(data.CMV_CODVEN);
            $("#CMV_DESCRI").val(data.CMV_DESCRIPCION);
            $("#CMV_MONTO").val(data.CMV_MONTO);
            $("#modal-movimiento").modal('show');
        });

        $('#cajas_diario_movimientos tbody').on('click', '#eliminaMovim', function() {
            var data = dtable.row($(this).parents('tr')).data();
            if (confirm('¿Eliminar Movimiento?')) {            
                $.post("<?= site_url('caja/eliminar_movimiento') ?>", {
                    local: $("#LOCAL").val(),
                    cmv_nro: data.CMV_NRO
                }, function() {
                    dtable.ajax.reload();
                });
            }
        });

        $('#cajas_diario_movimientos tbody').on('click', '.exportar-btn', function() {
            var cmv_nro = $(this).data('cmv-nro');
            var $btn = $(this);
            if (confirm('¿Exportar este movimiento a egresos?')) {
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                $.post("<?= site_url('caja/exportarAEgresos') ?>", { cmv_nro: cmv_nro }, function(response) {
                    if (response.success) {
                        $(document).Toasts('create', {
                            class: 'bg-success',
                            title: 'Éxito',
                            body: response.message,
                            position: 'bottomRight',
                            autohide: true,
                            delay: 3000
                        });
                        dtable.ajax.reload();
                    } else {
                        alert(response.message);
                        $btn.prop('disabled', false).html('<i class="fas fa-external-link-alt"></i>');
                    }
                }, 'json');
            }
        });

        // Lógica de Tipo y Campos Dinámicos
        $("#CMV_TIPO").change(function() {
            var opt = $(this).val(); 
            $('#DV_CMV_CODVEN').hide();
            $('#DV_CMV_COMPROBANTE').hide();
            $('#CMV_MONTO').removeAttr('readonly');
            $('#CMV_DESCRI').removeAttr('readonly');
            $('#LBL_MOTIVO').text('Descripción');
            
            if(opt == 6) { // ADELANTO
                $('#DV_CMV_CODVEN').show();
            } else if(opt == 7) { // CREDITO
                $('#DV_CMV_CODVEN').show();
                $('#DV_CMV_COMPROBANTE').show();
                $('#LBL_MOTIVO').text('Comprobante');
                $('#CMV_DESCRI').attr('readonly', true);
                cargarComprobantes();
            }
        });

        $("#CMV_COMPROBANTE").change(function() {
            var selectedOption = $(this).find('option:selected');
            var comprobante = selectedOption.text();
            var monto = selectedOption.data('monto');
            if(comprobante && !comprobante.includes('Seleccionar')) {
                $('#CMV_DESCRI').val(comprobante);
                $('#CMV_MONTO').val(monto);
            }
        });

        function cargarComprobantes() {
            var select = $('#CMV_COMPROBANTE');
            select.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
            $.post("<?= site_url('caja/get_comprobantes') ?>", {}, function(data) {
                select.empty().append('<option value="">Seleccionar comprobante...</option>').prop('disabled', false);
                if(data && data.length > 0) {
                    $.each(data, function(index, item) {
                        select.append('<option value="' + item.COMPROBANTE + '" data-monto="' + item.ALL_NETO + '">' + 
                                     item.COMPROBANTE + ' - S/. ' + parseFloat(item.ALL_NETO).toFixed(2) + '</option>');
                    });
                }
            }, 'json');
        }

        $('#modal-movimiento').on('shown.bs.modal', function() {
            if ($("#CMV_NRO").val() == 0) {
                $('#CMV_MONTO').val('');
                $('#CMV_DESCRI').val('');
                $('#CMV_TIPO').trigger('change');
            }
        });

        $('#modal-movimiento').on('hidden.bs.modal', function() {
            $("#CMV_NRO").val(0);
        });

        // Otros Modales y Scripts
        $('.openPopup').on('click', function() {
            var dataURL = $(this).attr('data-href');
            $('#myCaja').find('.modal-body').load(dataURL, function() {
                $('#myCaja').modal('show');
            });
        });

        $('.eliminarcaja').on('click', function() {
            var dataURL = $(this).attr('data-href');
            if (confirm('¿Está seguro de eliminar esta caja?')) {    
                $.get(dataURL, function(data) { alert("Respuesta: " + data); });
            }
        });

        $('.editarcaja').on('click', function() {
            var dataURL = $(this).attr('data-href');
            var strx = dataURL.split('/');
            nro_caja = strx[0];
            dtable.clear().draw();
            $.post("get_cajas_dia", {
                anio:'', mes:'', dia:'', loc:strx[1], caj:strx[0]
            }, function (rpta) {
                $('#CAJ_NRO').val(rpta[0].CAJ_NRO);
                $('#CAJ_NUMSER').val(rpta[0].CAJ_NUMSER);
                $('#CAJ_NUMFAC').val(rpta[0].CAJ_NUMFAC);
                $('#CAJ_EFECTIVO').val(rpta[0].CAJ_EFECTIVO);
                $('#CAJ_FECHA').val(rpta[0].CAJ_FECHA);
            }, "json");     
            dtable.ajax.reload();
        });

        $("#caja_cnt").click(function(e) { set_caja(e,1); });
        $("#caja_pmz").click(function(e) { set_caja(e,3); });
        $("#caja_jjc").click(function(e) { set_caja(e,2); });
        
        function set_caja(e,x) {
            e.preventDefault();
            $.post("<?= site_url('caja/set_caja') ?>", { caja:x ,opci: 'caja' }, function() {
                location.reload();
            });
        }   

        $("#mes").change(function() {
            $.post("<?= site_url('caja/set_mes') ?>", { 
                mes:this.value, anio:$(this).find(':selected').data('anio') 
            }, function() { location.reload(); });
        });
    });

    <?php if($session->get('item')){ ?>
        $(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Atención',
            body: '<?=$session->get('item')?>',
            autohide: true,
            delay: 5500
        });
    <?php } ?>
</script>
<?= $this->endSection(); ?>
