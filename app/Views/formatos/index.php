<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Módulo de Formatos</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="row">

            <div class="col-md-6 col-lg-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-temperature-half mr-1"></i>
                            Registro de Temperatura y Humedad
                        </h3>
                    </div>
                    <form action="<?= site_url('formatos/temperatura') ?>" method="get" target="_blank">
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Formato mensual de control de temperatura y humedad. Se genera con los
                                tres turnos de medición por día (9-10, 15-16 y 21-22 h).
                            </p>
                            <div class="form-group">
                                <label for="area">Ambiente</label>
                                <select class="form-control" id="area" name="area" required>
                                    <?php foreach ($ambientes as $ambiente): ?>
                                        <option value="<?= $ambiente ?>"><?= $ambiente ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="cod_inst">Cód. Inst.</label>
                                <input type="text" class="form-control" id="cod_inst" name="cod_inst"
                                       placeholder="Ej. BMF-07" autocomplete="off">
                            </div>
                            <div class="row">
                                <div class="col-7">
                                    <div class="form-group">
                                        <label for="mes">Mes</label>
                                        <select class="form-control" id="mes" name="mes" required>
                                            <?php foreach ($meses as $num => $nombre): ?>
                                                <option value="<?= $num ?>" <?= $num == $mes_actual ? 'selected' : '' ?>>
                                                    <?= $nombre ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <div class="form-group">
                                        <label for="anio">Año</label>
                                        <select class="form-control" id="anio" name="anio" required>
                                            <?php for ($y = $anio_actual - 2; $y <= $anio_actual + 2; $y++): ?>
                                                <option value="<?= $y ?>" <?= $y == $anio_actual ? 'selected' : '' ?>>
                                                    <?= $y ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-pdf mr-1"></i> Generar PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-broom mr-1"></i>
                            Registro de Control de Limpieza
                        </h3>
                    </div>
                    <form action="<?= site_url('formatos/limpieza') ?>" method="get" target="_blank">
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Formato mensual F-BMF-19 con las 12 actividades de limpieza y su
                                frecuencia (diaria, semanal y mensual).
                            </p>
                            <div class="row">
                                <div class="col-7">
                                    <div class="form-group">
                                        <label for="mes_limpieza">Mes</label>
                                        <select class="form-control" id="mes_limpieza" name="mes" required>
                                            <?php foreach ($meses as $num => $nombre): ?>
                                                <option value="<?= $num ?>" <?= $num == $mes_actual ? 'selected' : '' ?>>
                                                    <?= $nombre ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <div class="form-group">
                                        <label for="anio_limpieza">Año</label>
                                        <select class="form-control" id="anio_limpieza" name="anio" required>
                                            <?php for ($y = $anio_actual - 2; $y <= $anio_actual + 2; $y++): ?>
                                                <option value="<?= $y ?>" <?= $y == $anio_actual ? 'selected' : '' ?>>
                                                    <?= $y ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-file-pdf mr-1"></i> Generar PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <form action="<?= site_url('formatos/asistencia') ?>" method="post" target="_blank">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-clock mr-1"></i>
                                Firma de Asistencia
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Formato mensual de asistencia con registro de hora y firma de ingreso
                                y salida. Se imprimen 2 trabajadores por hoja.
                            </p>
                            <div class="form-group">
                                <label for="local">Área / Local</label>
                                <select class="form-control" id="local" name="local" required>
                                    <?php foreach ($locales as $local): ?>
                                        <option value="<?= $local ?>"><?= $local ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-7">
                                    <div class="form-group">
                                        <label for="mes_asistencia">Mes</label>
                                        <select class="form-control" id="mes_asistencia" name="mes" required>
                                            <?php foreach ($meses as $num => $nombre): ?>
                                                <option value="<?= $num ?>" <?= $num == $mes_actual ? 'selected' : '' ?>>
                                                    <?= $nombre ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <div class="form-group">
                                        <label for="anio_asistencia">Año</label>
                                        <select class="form-control" id="anio_asistencia" name="anio" required>
                                            <?php for ($y = $anio_actual - 2; $y <= $anio_actual + 2; $y++): ?>
                                                <option value="<?= $y ?>" <?= $y == $anio_actual ? 'selected' : '' ?>>
                                                    <?= $y ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalPersonal">
                                <i class="fas fa-users mr-1"></i> Seleccionar personal
                            </button>
                            <span class="ml-2 text-muted"><span id="lbl_seleccionados">0</span> seleccionado(s)</span>
                        </div>
                    </div>

                    <div class="modal fade" id="modalPersonal">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-users mr-1"></i> Seleccionar personal</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="buscar_empleado"
                                               placeholder="Buscar por código o nombre..." autocomplete="off">
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="check_todos">
                                        <label class="custom-control-label font-weight-bold" for="check_todos">
                                            Seleccionar todos
                                        </label>
                                    </div>
                                    <div id="lista_empleados" style="max-height: 380px; overflow-y: auto;">
                                        <?php foreach ($empleados as $emp): ?>
                                            <div class="custom-control custom-checkbox empleado-item">
                                                <input type="checkbox" class="custom-control-input empleado-check"
                                                       id="emp_<?= $emp->VEM_CODVEN ?>" name="empleados[]"
                                                       value="<?= $emp->VEM_CODVEN ?>">
                                                <label class="custom-control-label" for="emp_<?= $emp->VEM_CODVEN ?>">
                                                    <?= $emp->VEM_CODVEN ?> - <?= esc(trim($emp->VEM_NOMBRE)) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (empty($empleados)): ?>
                                            <p class="text-muted mb-0">No hay empleados disponibles.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <span class="text-muted"><span id="contador_empleados">0</span> seleccionado(s)</span>
                                    <div>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-file-pdf mr-1"></i> Generar PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<script>
    function actualizarContadorEmpleados() {
        var total = $('.empleado-check').length;
        var n = $('.empleado-check:checked').length;
        $('#contador_empleados, #lbl_seleccionados').text(n);
        $('#check_todos').prop('checked', total > 0 && n === total);
    }

    $(document).on('change', '.empleado-check', actualizarContadorEmpleados);

    $('#check_todos').on('change', function () {
        var marcar = $(this).prop('checked');
        $('.empleado-item:visible .empleado-check').prop('checked', marcar);
        actualizarContadorEmpleados();
    });

    $('#buscar_empleado').on('keyup', function () {
        var texto = $(this).val().toLowerCase();
        $('.empleado-item').each(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(texto) > -1);
        });
    });

    $('#modalPersonal').on('shown.bs.modal', function () {
        actualizarContadorEmpleados();
        $('#buscar_empleado').trigger('focus');
    });
</script>
<?= $this->endSection(); ?>
