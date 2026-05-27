<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-stethoscope text-success mr-2"></i> <?= esc($titulo) ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= site_url('cmHistoria/triaje/' . $cita->id) ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-heartbeat mr-1"></i> Triaje</a>
                <a href="<?= site_url('cmHistoria/ver/' . $cita->id) ?>" class="btn btn-outline-info btn-sm ml-1"><i class="fas fa-file-medical mr-1"></i> Ver Historia</a>
                <a href="<?= site_url('cmCitas/listado') ?>" class="btn btn-outline-secondary btn-sm ml-1"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Columna izquierda: Datos paciente + signos vitales -->
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fas fa-user mr-2"></i> <?= esc($cita->CLI_NOMBRE) ?></h5></div>
                    <div class="card-body">
                        <p class="mb-1"><strong>DNI:</strong> <?= $cita->DNI ?: '-' ?></p>
                        <p class="mb-1"><strong>Edad:</strong> <?= $cita->edad ?> años</p>
                        <p class="mb-1"><strong>Médico:</strong> <?= esc($cita->medico) ?></p>
                        <p class="mb-0"><strong>Fecha:</strong> <?= $cita->fecha_especifica ? date('d/m/Y', strtotime($cita->fecha_especifica)) : '-' ?></p>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header bg-warning"><h6 class="mb-0">Signos Vitales</h6></div>
                    <div class="card-body p-2">
                        <table class="table table-sm mb-0">
                            <tr><td>Presión</td><td><strong><?= $historia->presion_arterial ?: '-' ?></strong></td></tr>
                            <tr><td>Temperatura</td><td><strong><?= $historia->temperatura ? $historia->temperatura.' °C' : '-' ?></strong></td></tr>
                            <tr><td>Peso</td><td><strong><?= $historia->peso ? $historia->peso.' kg' : '-' ?></strong></td></tr>
                            <tr><td>Talla</td><td><strong><?= $historia->talla ? $historia->talla.' cm' : '-' ?></strong></td></tr>
                            <tr><td>Saturación</td><td><strong><?= $historia->saturacion ? $historia->saturacion.'%' : '-' ?></strong></td></tr>
                            <tr><td>FC</td><td><strong><?= $historia->frec_cardiaca ?: '-' ?></strong></td></tr>
                            <tr><td>FR</td><td><strong><?= $historia->frec_respiratoria ?: '-' ?></strong></td></tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Columna derecha: Atención -->
            <div class="col-md-9">
                <!-- Examen clínico -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Examen Clínico y Plan de Trabajo</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= site_url('cmHistoria/guardar_atencion') ?>">
                            <input type="hidden" name="historia_id" value="<?= $historia->id ?>">
                            <input type="hidden" name="cita_id" value="<?= $cita->id ?>">
                            <div class="form-group"><label>Examen Clínico</label>
                                <textarea name="examen_clinico" class="form-control" rows="3"><?= esc($historia->examen_clinico ?? '') ?></textarea></div>
                            <div class="form-group"><label>Plan de Trabajo</label>
                                <textarea name="plan_trabajo" class="form-control" rows="3"><?= esc($historia->plan_trabajo ?? '') ?></textarea></div>
                            <div class="form-group"><label>Indicaciones</label>
                                <textarea name="indicaciones" class="form-control" rows="2"><?= esc($historia->indicaciones ?? '') ?></textarea></div>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar y Marcar Atendido</button>
                        </form>
                    </div>
                </div>
                
                <!-- Diagnósticos CIE-10 -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Diagnósticos CIE-10</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-5">
                                <select class="form-control form-control-sm" id="cie_search" style="width:100%" placeholder="Buscar código o descripción CIE-10..."></select>
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control form-control-sm" id="cie_desc_manual" placeholder="O escriba la descripción manual">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary btn-sm btn-block" id="btnAddDiagnostico"><i class="fas fa-plus"></i> Agregar</button>
                            </div>
                        </div>
                        <table class="table table-sm table-bordered" id="tabla_diag">
                            <thead><tr><th>Código</th><th>Descripción</th><th>Tipo</th><th>Caso</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach($diagnosticos as $d): ?>
                                <tr>
                                    <td><?= esc($d->cie_codigo) ?></td>
                                    <td><?= esc($d->cie_descripcion) ?></td>
                                    <td><span class="badge badge-info"><?= esc($d->tipo) ?></span></td>
                                    <td><span class="badge badge-secondary"><?= esc($d->caso) ?></span></td>
                                    <td><a href="<?= site_url('cmHistoria/eliminar_diagnostico/' . $d->id) ?>" class="btn btn-xs btn-danger"><i class="fas fa-times"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Recetas -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Recetas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-7">
                                <input type="text" class="form-control form-control-sm" id="receta_nombre" placeholder="Nombre del medicamento/artículo...">
                            </div>
                            <div class="col-md-1">
                                <input type="number" class="form-control form-control-sm" id="receta_cant" value="1" min="1">
                            </div>
                            <div class="col-md-1">
                                <input type="number" class="form-control form-control-sm" id="receta_dias" value="1" min="1">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control form-control-sm" id="receta_ind" placeholder="Indicaciones">
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-info btn-sm btn-block" id="btnAddReceta"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <table class="table table-sm table-bordered" id="tabla_receta">
                            <thead><tr><th>Artículo</th><th>Cant</th><th>Días</th><th>Indicaciones</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach($recetas as $r): ?>
                                <tr>
                                    <td><?= esc($r->nombre_articulo) ?></td>
                                    <td><?= $r->cantidad ?></td>
                                    <td><?= $r->dias ?></td>
                                    <td><?= esc($r->indicaciones) ?></td>
                                    <td><a href="<?= site_url('cmHistoria/eliminar_receta/' . $r->id) ?>" class="btn btn-xs btn-danger"><i class="fas fa-times"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<link href="<?= base_url('plugins/select2/css/select2.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>" rel="stylesheet">
<script src="<?= base_url('plugins/select2/js/select2.full.min.js') ?>"></script>

<script>
$(function() {
    // CIE-10 Search via Select2 (reusing old endpoint)
    $('#cie_search').select2({
        theme: 'bootstrap4',
        placeholder: 'Buscar CIE-10 por código o descripción...',
        ajax: {
            url: "<?= site_url('consultorio/get_cie_nombre') ?>",
            type: "post",
            dataType: 'json',
            delay: 250,
            data: function(p) { return { term: p.term }; },
            processResults: function(data) {
                return { results: (data||[]).map(function(d) { return { id: d.id, text: d.text, desc: d.text }; }) };
            }
        }
    }).on('select2:select', function(e) {
        $('#cie_desc_manual').val(e.params.data.desc || '');
    });

    // Agregar diagnóstico
    $('#btnAddDiagnostico').click(function() {
        let codigo = $('#cie_search').val();
        let desc = $('#cie_desc_manual').val() || $('#cie_search').select2('data')[0]?.text || '';
        if (!codigo && !desc) { alert('Seleccione un diagnóstico'); return; }
        $.post("<?= site_url('cmHistoria/guardar_diagnostico') ?>", {
            historia_id: <?= $historia->id ?>,
            cie_codigo: codigo || '',
            cie_descripcion: desc,
            tipo: 'DEFINITIVO',
            caso: 'NUEVO'
        }, function() { location.reload(); });
    });

    // Agregar receta
    $('#btnAddReceta').click(function() {
        let nombre = $('#receta_nombre').val();
        if (!nombre) { alert('Ingrese el nombre del artículo'); return; }
        $.post("<?= site_url('cmHistoria/guardar_receta') ?>", {
            historia_id: <?= $historia->id ?>,
            nombre_articulo: nombre,
            cantidad: $('#receta_cant').val()||1,
            dias: $('#receta_dias').val()||1,
            indicaciones: $('#receta_ind').val()
        }, function() { location.reload(); });
    });
});
</script>
<?= $this->endSection(); ?>
