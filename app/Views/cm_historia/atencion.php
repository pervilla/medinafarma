<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-stethoscope text-success mr-2"></i> <?= esc($titulo) ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <?php
                    $citaBadge = $cita->estado == 4 ? 'badge-warning' : ($cita->estado == 2 ? 'badge-success' : 'badge-info');
                    $citaLabel = $cita->estado == 4 ? 'Pendiente (exámenes)' : ($cita->estado == 2 ? 'Atendido' : 'En atención');
                ?>
                <span class="badge <?= $citaBadge ?> mr-2"><?= $citaLabel ?></span>
                <a href="<?= site_url('cmHistoria/triaje/' . $cita->id) ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-heartbeat mr-1"></i> Triaje</a>
                <a href="<?= site_url('cmHistoria/ver/' . $cita->id) ?>" class="btn btn-outline-info btn-sm ml-1"><i class="fas fa-file-medical mr-1"></i> Ver Historia</a>
                <a href="<?= site_url('cmHistoria/receta/' . $cita->id) ?>" class="btn btn-outline-secondary btn-sm ml-1" target="_blank"><i class="fas fa-print mr-1"></i> Imprimir Receta</a>
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
                            <div class="btn-group">
                                <button type="submit" name="resultado_atencion" value="guardar" class="btn btn-warning"><i class="fas fa-save mr-1"></i> Guardar</button>
                                <?php if ($cita->estado != 4): ?>
                                <button type="submit" name="resultado_atencion" value="pendiente" class="btn btn-info"><i class="fas fa-clock mr-1"></i> Dejar en Pendiente</button>
                                <?php endif; ?>
                                <button type="submit" name="resultado_atencion" value="atendido" class="btn btn-success"><i class="fas fa-check-circle mr-1"></i> <?= $cita->estado == 4 ? 'Cerrar (Atendido)' : 'Finalizar Atención' ?></button>
                            </div>
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
                            <div class="col-md-3">
                                <select class="form-control form-control-sm" id="cie_search" style="width:100%"></select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm" id="cie_desc_manual" placeholder="Descripción">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control form-control-sm" id="cie_tipo">
                                    <option value="DEFINITIVO">Definitivo</option>
                                    <option value="PRESUNTIVO">Presuntivo</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control form-control-sm" id="cie_caso">
                                    <option value="NUEVO">Nuevo</option>
                                    <option value="REPETIDO">Repetido</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <select class="form-control form-control-sm" id="cie_alta">
                                    <option value="">-</option>
                                    <option value="SI">Sí</option>
                                    <option value="NO">No</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-primary btn-sm btn-block" id="btnAddDiagnostico"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <table class="table table-sm table-bordered" id="tabla_diag">
                            <thead><tr><th>Código</th><th>Descripción</th><th>Tipo</th><th>Caso</th><th>Alta</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach($diagnosticos as $d): ?>
                                <tr data-diag-id="<?= $d->id ?>">
                                    <td><?= esc($d->cie_codigo) ?></td>
                                    <td><?= esc($d->cie_descripcion) ?></td>
                                    <td>
                                        <select class="form-control form-control-sm diag-tipo" data-id="<?= $d->id ?>">
                                            <option value="DEFINITIVO" <?= $d->tipo == 'DEFINITIVO' ? 'selected' : '' ?>>Definitivo</option>
                                            <option value="PRESUNTIVO" <?= $d->tipo == 'PRESUNTIVO' ? 'selected' : '' ?>>Presuntivo</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm diag-caso" data-id="<?= $d->id ?>">
                                            <option value="NUEVO" <?= $d->caso == 'NUEVO' ? 'selected' : '' ?>>Nuevo</option>
                                            <option value="REPETIDO" <?= $d->caso == 'REPETIDO' ? 'selected' : '' ?>>Repetido</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm diag-alta" data-id="<?= $d->id ?>">
                                            <option value="" <?= !$d->alta ? 'selected' : '' ?>>-</option>
                                            <option value="SI" <?= $d->alta == 'SI' ? 'selected' : '' ?>>Sí</option>
                                            <option value="NO" <?= $d->alta == 'NO' ? 'selected' : '' ?>>No</option>
                                        </select>
                                    </td>
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
                            <div class="col-md-5">
                                <select class="form-control form-control-sm" id="receta_buscar" style="width:100%"></select>
                            </div>
                            <div class="col-md-1">
                                <input type="number" class="form-control form-control-sm" id="receta_cant" value="1" min="1" placeholder="Cant">
                            </div>
                            <div class="col-md-1">
                                <input type="number" class="form-control form-control-sm" id="receta_dias" value="1" min="1" placeholder="Días">
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control form-control-sm" id="receta_ind" placeholder="Indicaciones (ej: 1 tab c/8h)">
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
            tipo: $('#cie_tipo').val(),
            caso: $('#cie_caso').val(),
            alta: $('#cie_alta').val()
        }, function() { location.reload(); });
    });

    // Editar tipo/caso/alta inline
    $(document).on('change', '.diag-tipo, .diag-caso, .diag-alta', function() {
        let id = $(this).data('id');
        let field = $(this).hasClass('diag-tipo') ? 'tipo' : ($(this).hasClass('diag-caso') ? 'caso' : 'alta');
        let val = $(this).val();
        $.post("<?= site_url('cmHistoria/actualizar_diagnostico') ?>", {
            id: id, field: field, value: val
        });
    });

    // Inicializar buscador de recetas
    $('#receta_buscar').select2({
        theme: 'bootstrap4',
        placeholder: 'Buscar medicamento o artículo...',
        ajax: {
            url: "<?= site_url('cmHistoria/buscar_articulo') ?>",
            type: "post", dataType: 'json', delay: 250,
            data: function(p) { return { term: p.term }; },
            processResults: function(data) {
                return { results: (data||[]).map(function(d) {
                    return { id: d.ART_KEY, text: d.ART_NOMBRE, art_key: d.ART_KEY, nombre: d.ART_NOMBRE };
                })};
            }
        }
    });

    // Agregar receta
    $('#btnAddReceta').click(function() {
        let sel = $('#receta_buscar').select2('data')[0];
        let nombre = sel ? sel.nombre : $('#receta_buscar').val();
        if (!nombre || nombre.length < 2) { Swal.fire({icon:'warning', title:'Busque o escriba un artículo'}); return; }
        $.post("<?= site_url('cmHistoria/guardar_receta') ?>", {
            historia_id: <?= $historia->id ?>,
            nombre_articulo: nombre,
            art_key: sel ? sel.art_key : 0,
            cantidad: $('#receta_cant').val()||1,
            dias: $('#receta_dias').val()||1,
            indicaciones: $('#receta_ind').val()
        }, function() { location.reload(); });
    });

});
</script>
<?= $this->endSection(); ?>
