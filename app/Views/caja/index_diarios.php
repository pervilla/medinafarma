<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Cajas</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Cajas</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Movimiento Diario</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table  id="cajas_diario" class="table">
                            <thead>
                                <tr>
                                    <th style="width: 10px">DIA</th>
                                    <th>CAJA</th>
                                    <th>RESPONSABLE</th>
                                    <th style="width: 40px">ESTADO</th>
                                </tr>
                            </thead>
                            <tbody>
                                



                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">CAJA <?= $caja[0]->CAJ_NRO; ?></h3>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->

                    <?php if (count($caja) == 1) { ?> 

                        <form role="form" action="cerracaja" method="post">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-2">
                                        <!-- text input -->
                                        <div class="form-group">
                                            <label>Caja Nro</label>
                                            <input type="text" class="form-control" disabled="disabled" value="<?= $caja[0]->CAJ_NRO; ?>">
                                            <input type="hidden" id="CAJ_NRO" name="CAJ_NRO" value="<?= $caja[0]->CAJ_NRO; ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Fecha</label>
                                            <input type="text" class="form-control" disabled="disabled" value="<?= $caja[0]->CAJ_FECHA; ?>">
                                            <input type="hidden" name="CAJ_FECHA" value="<?= $caja[0]->CAJ_FECHA; ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Responsable</label>
                                            <select class="form-control" name="VEM_CODVEN" disabled="disabled">

                                                <?php foreach ($empleados as $empleado) { ?>
                                                    <option value="<?= $empleado->VEM_CODVEN; ?>" <?= $empleado->VEM_CODVEN == $caja[0]->CAJ_CODVEN ? 'selected="selected"' : ''; ?>><?= $empleado->VEM_CODVEN . ' - ' . $empleado->VEM_NOMBRE; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="card card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">Movimientos de Caja</h3>
                                        </div>
                                        <!-- /.card-header -->
                                        <div class="card-body">
                                            <div class="form-group row">
                                                <table id="cajas_diario_detalle" class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>TIPO</th>
                                                            <th>DESCRIPCION</th>
                                                            <th>MONTO</th>     
                                                            <th style="width: 10px"></th>                         
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        //var_export($movimientos);
                                                        //var_export($motivo_gasto);
                                                         foreach ($movimientos as $val) { ?>
                                                        <!-- array ( 0 => (object) array( 'CMV_NRO' => 322, 'CMV_CAJA' => 313, 'CMV_TIPO' => 1, 'CMV_CODVEN' => 18, 'CMV_DESCRIPCION' => 'DEPOSITO', 'CMV_MONTO' => '100.00', ), ) -->
                                                        <tr>                      
                                                            <td><?=$motivo_gasto[$val->CMV_TIPO] ?></td>
                                                            <td><?=$val->CMV_DESCRIPCION ?></td>
                                                            <td><?=$val->CMV_MONTO ?></td>  
                                                            <td><a href="#" class="nav-link" title="Eliminar"><span class="float-right badge bg-danger"><i class="fas fa-trash" onclick="quitar_mov(<?=$val->CMV_NRO ?>)"></i></span></a></td>
                                                            </tr>
                                                        <?php } ?>  
                                                    </tbody>
                                                </table>
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-primary">
                                                    Agregar
                                                </button>
                                            </div>                                            
                                        </div>
                                        <!-- /.card-body -->
                                    </div>
                                </div>
                                <div id="cerrar_caja"  style="display: none">
                                    <div class="col-sm-12 row">
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control" placeholder="Serie" name="CAJ_NUMSER" id="CAJ_NUMSER">
                                            <input type="number" class="form-control" placeholder="Nro Fac/Bol/Guia" name="CAJ_NUMFAC" id="CAJ_NUMFAC">
                                            <input type="number" min="0" step=".01" class="form-control" placeholder="Monto Efectivo" name="CAJ_EFECTIVO" id="CAJ_EFECTIVO">
                                            <span class="input-group-append">
                                                <button type="submit" class="btn btn-success btn-flat">Cerrar Caja</button>
                                                <button type="button" id="btn_cancelar" class="btn btn-danger btn-flat">Cancelar</button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer">
                                    <button type="button" id="btn_cerrar" class="btn btn-primary">Cerrar</button>
                                </div>
                            </div>
                        </form>

                    <?php } else { ?> 
                        <form role="form" action="abrircaja" method="post">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Responsable</label>
                                    <select id="VEM_CODVEN" name="VEM_CODVEN" class="form-control">

                                        <?php foreach ($empleados as $empleado) { ?>
                                            <option value="<?= $empleado->VEM_CODVEN; ?>" <?= $empleado->VEM_CODVEN == $caja[0]->CAJ_CODVEN ? 'selected="selected"' : ''; ?>><?= $empleado->VEM_CODVEN . ' - ' . $empleado->VEM_NOMBRE; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Date:</label>
                                    <div class="input-group date" id="reservationdate" data-target-input="nearest">
                                        <input type="text" id="CAJ_FECHA2" name="CAJ_FECHA2" class="form-control datetimepicker-input" data-target="#reservationdate">
                                        <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /.card-body -->

                            <div class="card-footer">
                                <button type="submit" id="btn_nuevo" class="btn btn-success">Abrir Caja</button>
                            </div>
                        </form>
                    <?php } ?> 
                </div>

            </div>
            <!-- /.col -->
        </div>

    </div><!-- /.container-fluid -->
</section>

<!-- Modal -->
<div class="modal fade" id="modal-primary">
    <div class="modal-dialog">
        <div class="modal-content bg-primary">
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
                                    <option value="<?= $empleado->VEM_CODVEN; ?>" <?= $empleado->VEM_CODVEN == $caja[0]->CAJ_CODVEN ? 'selected="selected"' : ''; ?>><?= $empleado->VEM_CODVEN . ' - ' . trim($empleado->VEM_NOMBRE); ?></option>
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
                <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cerrar</button>
                <button type="button" id="movimientos" class="btn btn-outline-light">Guardar</button>
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


<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script>
    $(function () {
        $("#cajas_diario").DataTable({
            "responsive": true,
            "autoWidth": false,
            "order": [[1, "desc"]],
            "searching": false,
            "paging": false
        });
    });
    $("#btn_nuevo").click(function () {
        $(this).slideUp();
    });
    $("#btn_cerrar").click(function () {
        $('#cerrar_caja').slideDown();
        $(this).slideUp();
        $.post("get_nro_doc", {}, function (htmlexterno) {
            var obj = jQuery.parseJSON(htmlexterno);
            $("#CAJ_NUMSER").val(obj.ALL_NUMSER);
            $("#CAJ_NUMFAC").val(obj.ALL_NUMFAC);
            $("#CAJ_EFECTIVO").focus();
        });
    });
    $("#btn_cancelar").click(function () {
        $('#cerrar_caja').slideUp();
        $('#btn_cerrar').slideDown();
    });
    //Date picker
    $('#reservationdate').datetimepicker({
        format: 'LL'
    });
    $("#movimientos").click(function () {
        $.post("agregar_movimiento", {
            cmv_tipo: $( "select#CMV_TIPO option:checked" ).val(),
            cmv_caja: $("input#CAJ_NRO").val(),
            cmv_codven: $( "select#CMV_CODVEN option:checked" ).val(),
            cmv_descri: $("#CMV_DESCRI").val(),
            cvm_monto:$("#CMV_MONTO").val()
        }, function (htmlexterno) {
            $("#cajas_diario_detalle > tbody").html(htmlexterno);
        });
    });

    $("#CMV_TIPO" ).change(function() {
        var opt = $(this).find("option:selected").attr('value'); 
        if(opt == 6 || opt == 7){
            $('#DV_CMV_CODVEN').show();
        }else{
            $('#DV_CMV_CODVEN').hide();
            var selId = document.getElementById("CMV_CODVEN");
            selId.value = <?=($caja[0]->CAJ_CODVEN)?($caja[0]->CAJ_CODVEN):0; ?>; 
        }
        $('#CMV_MONTO').removeAttr('value');
    });

    function quitar_mov(x) {
        $.post("eliminar_movimiento", {
            cmv_nro: x
        }, function (htmlexterno) {
            location.reload();
        });
    }

</script>
<?= $this->endSection(); ?>

