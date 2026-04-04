<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<section class="content-header pt-3 pb-1">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-file-excel mr-2"></i>Registro de Ventas</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Inicio</a></li>
                    <li class="breadcrumb-item">Reportes</li>
                    <li class="breadcrumb-item active">Reg. Ventas</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-ban"></i> <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="card card-primary card-outline">
            <div class="card-header" style="background: #17375e; color: #fff;">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Parámetros del Reporte</h3>
            </div>

            <form action="<?= base_url('reportes/regventa/generar') ?>" method="post" id="frm_regventa" target="_blank">
                <?= csrf_field() ?>
                <div class="card-body">

                    <div class="row">
                        <!-- Sede / Servidor -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Sede / Servidor <span class="text-danger">*</span></label>
                                <select name="server" id="server_select" class="form-control">
                                    <option value="1" <?= $server == 1 ? 'selected' : '' ?>>LOCAL (Centro)</option>
                                    <option value="3" <?= $server == 3 ? 'selected' : '' ?>>P. MEZA</option>
                                    <option value="2" <?= $server == 2 ? 'selected' : '' ?>>JUANJUICILLO</option>
                                </select>
                            </div>
                        </div>
                        <!-- Rango de Fechas -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rango de Fechas <span class="text-danger">*</span></label>
                                <div id="reportrange" class="form-control" style="cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down float-right mt-1"></i>
                                </div>
                                <input type="hidden" name="fecha1" id="fecha1" value="<?= $fecha1 ?>">
                                <input type="hidden" name="fecha2" id="fecha2" value="<?= $fecha2 ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Serie (opcional)</label>
                                <input type="text" name="serie" id="serie"
                                       class="form-control text-uppercase"
                                       maxlength="4" placeholder="Ej: 0001">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Tipo de Documento <span class="text-danger">*</span></label>
                                <div class="d-flex flex-wrap">
                                    <div class="custom-control custom-checkbox mr-4">
                                        <input class="custom-control-input" type="checkbox" name="tipo_f" id="tipo_f" value="1" checked>
                                        <label class="custom-control-label" for="tipo_f">
                                            <span class="badge badge-primary">F</span> Facturas
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox mr-4">
                                        <input class="custom-control-input" type="checkbox" name="tipo_b" id="tipo_b" value="1" checked>
                                        <label class="custom-control-label" for="tipo_b">
                                            <span class="badge badge-success">B</span> Boletas
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox mr-4">
                                        <input class="custom-control-input" type="checkbox" name="tipo_n" id="tipo_n" value="1">
                                        <label class="custom-control-label" for="tipo_n">
                                            <span class="badge badge-warning">N</span> N/Crédito
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" name="tipo_d" id="tipo_d" value="1">
                                        <label class="custom-control-label" for="tipo_d">
                                            <span class="badge badge-danger">D</span> N/Débito
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vendedor(es) <small class="text-muted">(opcional, todos si está vacío)</small></label>
                                <select name="vendedores[]" id="vendedores" class="form-control select2"
                                        multiple data-placeholder="-- Todos los vendedores --">
                                    <?php foreach ($vendedores as $v): ?>
                                        <option value="<?= htmlspecialchars($v['VEM_CODVEN']) ?>">
                                            <?= htmlspecialchars($v['VEM_CODVEN'] . ' - ' . $v['VEM_NOMBRE']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cliente(s) <small class="text-muted">(opcional, todos si está vacío)</small></label>
                                <select name="clientes[]" id="clientes" class="form-control select2"
                                        multiple data-placeholder="-- Todos los clientes --">
                                    <?php foreach ($clientes as $c): ?>
                                        <option value="<?= htmlspecialchars($c['CLI_CODCLIE']) ?>">
                                            <?= htmlspecialchars($c['CLI_CODCLIE'] . ' - ' . $c['CLI_NOMBRE']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success" id="btn_generar">
                        <i class="fas fa-file-excel mr-1"></i> Generar Excel
                    </button>
                    <button type="reset" class="btn btn-secondary ml-2">
                        <i class="fas fa-undo mr-1"></i> Limpiar
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<!-- Select2 -->
<link rel="stylesheet" href="<?= base_url('plugins/select2/css/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>">
<script src="<?= base_url('plugins/select2/js/select2.full.min.js') ?>"></script>
<!-- Daterange picker -->
<link rel="stylesheet" href="<?= base_url('plugins/daterangepicker/daterangepicker.css') ?>">
<script src="<?= base_url('plugins/moment/moment.min.js') ?>"></script>
<script src="<?= base_url('plugins/daterangepicker/daterangepicker.js') ?>"></script>

<script>
$(function () {
    // Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Daterangepicker (Report Range)
    var start = moment("<?= $fecha1 ?>", "DD/MM/YYYY");
    var end = moment("<?= $fecha2 ?>", "DD/MM/YYYY");

    function cb(start, end) {
        $('#reportrange span').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
        $('#fecha1').val(start.format('DD/MM/YYYY'));
        $('#fecha2').val(end.format('DD/MM/YYYY'));
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
           'Hoy': [moment(), moment()],
           'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
           'Últimos 30 Días': [moment().subtract(29, 'days'), moment()],
           'Este Mes': [moment().startOf('month'), moment().endOf('month')],
           'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            "format": "DD/MM/YYYY",
            "separator": " - ",
            "applyLabel": "Aplicar",
            "cancelLabel": "Cancelar",
            "fromLabel": "Desde",
            "toLabel": "Hasta",
            "customRangeLabel": "Personalizado",
            "daysOfWeek": ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
            "monthNames": ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            "firstDay": 1
        }
    }, cb);

    cb(start, end);

    // Cambio de servidor: recargar página para actualizar combos de vendedores/clientes
    $('#server_select').on('change', function() {
        var server = $(this).val();
        var f1 = $('#fecha1').val();
        var f2 = $('#fecha2').val();
        window.location.href = "<?= base_url('reportes/regventa') ?>?server=" + server + "&fecha1=" + f1 + "&fecha2=" + f2;
    });

    // Validación al enviar
    $('#frm_regventa').on('submit', function (e) {
        var tipos = $('input[name^="tipo_"]:checked').length;
        if (tipos === 0) {
            e.preventDefault();
            alert('Debe seleccionar al menos un tipo de documento.');
            return false;
        }
        
        var btn = $('#btn_generar');
        var originalHtml = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Generando...')
           .attr('disabled', true);
           
        // El formulario se abre en una nueva pestaña (target="_blank"), 
        // así que reactivamos el botón después de un tiempo corto
        setTimeout(function () {
            btn.html(originalHtml).attr('disabled', false);
        }, 5000);
    });
});
</script>
<?= $this->endSection(); ?>
