<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<!-- Content Header (Page header) -->
<div class="content-header">

</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Campañas</h3>
                        <div class="card-tools">
                            <span class="input-group-append">
                                <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#modal-primary"><i class="fa fa-plus"></i> Nueva Campaña</button>
                            </span>

                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="table_citas" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Médico</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Ver Campaña</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($campanias as $campania) {
                                    if (date('d/m/Y', strtotime($campania->CAM_FEC_INI)) != '31/12/1969') {
                                        $date =  date('d/m/Y', strtotime($campania->CAM_FEC_INI)) . ' ' . date('h:i A', strtotime($campania->CAM_HOR_INI));
                                    } else {
                                        $date = 'Sin Fecha';
                                    };
                                    if (date('d/m/Y', strtotime($campania->CAM_FEC_FIN)) != '31/12/1969') {
                                        $date2 =  date('d/m/Y', strtotime($campania->CAM_FEC_FIN)) . ' ' . date('h:i A', strtotime($campania->CAM_HOR_FIN));
                                    } else {
                                        $date2 = 'Sin Fecha';
                                    };

                                    $firstDate = new DateTime("now");
                                    $secondDate = new DateTime($campania->CAM_FEC_INI);

                                    if ($secondDate >= $firstDate or  $secondDate == '31/12/1969') {
                                        $css = "even bg-success";
                                    } else {
                                        $css = '';
                                    }
                                ?>
                                    <tr class="<?= $css ?>">
                                        <td><?= $campania->CAM_DESCRIP ?></td>
                                        <td><?= $campania->CLI_NOMBRE ?></td>
                                        <td><?= $date ?></td>
                                        <td><?= $date2 ?></td>
                                        <td><a class='btn btn-block bg-gradient-primary btn-sm cs_pointer' href='<?= site_url('consultorio/confirmados') . "/" . $campania->CAM_CODCAMP ?>' target='_blank'><i class='fas fa-eye'></i> Ver</a></td>
                                    </tr>
                                <?php } ?>

                            </tbody>
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
<div class="modal fade" id="modal-primary">
    <div class="modal-dialog">
        <div class="modal-content bg-success">
            <form role="form" action="set_campania" id="set_campania" method="post">
                <div class="modal-header">
                    <h4 class="modal-title">Nueva Campaña</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="card-body">
                        <div class="form-group row">
                            <label for="cmvtipo" class="col-sm-3 col-form-label">Campaña</label>
                            <div class="col-sm-9">
                                <select id="idcliente" name="idcliente" class="form-control">
                                    <option value="" selected="selected">SELECCIONE</option>
                                    <?php foreach ($medicos as $medico) { ?>
                                        <option value="<?= $medico->CLI_CODCLIE ?>"><?= $medico->CLI_NOMBRE ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="cmvtipo" class="col-sm-3 col-form-label">Descripción</label>
                            <div class="col-sm-9">
                                <select id="idcampania" name="idcampania" class="form-control">
                                    <option value="" selected="selected">SELECCIONE</option>
                                    <?php foreach ($tipocampa as $tipo) : ?>
                                        <option value="<?= $tipo->CAMP_ID ?>"><?= $tipo->CAMP_DESCRIPCION ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="checkbox" class="col-sm-3 col-form-label">Sin Fecha</label>
                            <div class="col-sm-9">
                                <input type="checkbox" name="my-checkbox" id="my-checkbox" class="form-control switch conf-switch-state" checked data-bootstrap-switch data-off-color="danger" data-on-color="primary" data-on-text="CON FECHA" data-off-text="SIN FECHA">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Inicio:</label>
                            <div class="col-sm-9 input-group date" id="reservationdatetime" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" id="reservationdatetimeval" name="reservationdatetimeval" data-target="#reservationdatetime">
                                <div class="input-group-append" data-target="#reservationdatetime" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Fin:</label>
                            <div class="col-sm-9 input-group date" id="reservationdatetimeend" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" id="reservationdatetimeendval" name="reservationdatetimeendval" data-target="#reservationdatetimeend">
                                <div class="input-group-append" data-target="#reservationdatetimeend" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cerrar</button>
                    <button type="button" id="set_campania2" value="button" class="btn btn-outline-light">Guardar</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<?=$this->endSection(); ?>

<?=$this->section('footer'); ?>
<!-- Select2 -->
<link rel="stylesheet" href="../../plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
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


<script>
    $(document).ready(function() {
        //Date and time picker
        $('#reservationdatetime').datetimepicker({
            icons: {
                time: 'far fa-clock'
            }
        });
        $('#reservationdatetimeend').datetimepicker({
            icons: {
                time: 'far fa-clock'
            }
        });

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        })
        $('input[data-bootstrap-switch]').on('switchChange.bootstrapSwitch', function(e, data) {
            if (data) {
                $("#reservationdatetimeval").removeAttr('disabled')
                $("#reservationdatetimeendval").removeAttr('disabled')
            } else {
                $("#reservationdatetimeval").attr('disabled', 'disabled')
                $("#reservationdatetimeendval").attr('disabled', 'disabled')
            }
        });
        $("#set_campania2").click(function() {
            var check = $('.bootstrap-switch-on');
            if (check.length > 0) {
                calend = 1
            } else {
                calend = 0
            }
            $.ajax({
                type: "POST",
                url: "<?= site_url('consultorio/set_campania') ?>",
                data: {
                    idcampania:$("select#idcampania option:checked").val(),
                    descrip: $("select#idcampania option:checked").text(),
                    fechaini: $("#reservationdatetimeval").val(),
                    fechafin: $("#reservationdatetimeendval").val(),
                    cliente: $("select#idcliente option:checked").val(),
                    calendario: calend
                }
            }).done(function() {
                location.reload();
            })
        });


    });
</script>
<?= $this->endSection(); ?>