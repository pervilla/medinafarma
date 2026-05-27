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
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Datos paciente + triaje -->
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fas fa-user mr-2"></i> <?= esc($cita->CLI_NOMBRE) ?></h5></div>
                    <div class="card-body">
                        <small><strong>DNI:</strong> <?= $cita->DNI ?: '-' ?> | <strong>Edad:</strong> <?= $cita->edad ?> a</small><br>
                        <small><strong>Médico:</strong> <?= esc($cita->medico) ?> | <?= $cita->fecha_especifica ? date('d/m/Y', strtotime($cita->fecha_especifica)) : '-' ?></small>
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
                            <tr><td>Saturación O2</td><td><strong><?= $historia->saturacion ? $historia->saturacion.'%' : '-' ?></strong></td></tr>
                            <tr><td>FC</td><td><strong><?= $historia->frec_cardiaca ?: '-' ?></strong></td></tr>
                            <tr><td>FR</td><td><strong><?= $historia->frec_respiratoria ?: '-' ?></strong></td></tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Atención médica -->
            <div class="col-md-8">
                <!-- Examen clínico, plan, indicaciones -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white"><h5 class="mb-0">Examen Clínico y Plan de Trabajo</h5></div>
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
                            <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar Atención</button>
                        </form>
                    </div>
                </div>
                
                <!-- Diagnósticos CIE-10 -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0">Diagnósticos CIE-10</h5></div>
                    <div class="card-body">
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Código</th><th>Descripción</th><th>Tipo</th><th>Caso</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach($diagnosticos as $d): ?>
                                <tr>
                                    <td><?= esc($d->cie_codigo) ?></td>
                                    <td><?= esc($d->cie_descripcion) ?></td>
                                    <td><span class="badge badge-info"><?= esc($d->tipo) ?></span></td>
                                    <td><span class="badge badge-secondary"><?= esc($d->caso) ?></span></td>
                                    <td><a href="<?= site_url('cmHistoria/eliminar_diagnostico/' . $d->id) ?>" class="btn btn-xs btn-danger" onclick="return confirm('¿Eliminar?')"><i class="fas fa-times"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <form method="post" action="<?= site_url('cmHistoria/guardar_diagnostico') ?>" class="form-inline mt-2">
                            <input type="hidden" name="historia_id" value="<?= $historia->id ?>">
                            <input name="cie_codigo" class="form-control form-control-sm mr-1" placeholder="Código CIE-10" required>
                            <input name="cie_descripcion" class="form-control form-control-sm mr-1" placeholder="Descripción" style="width: 250px;">
                            <select name="tipo" class="form-control form-control-sm mr-1"><option value="">Tipo</option><option>DEFINITIVO</option><option>PRESUNTIVO</option></select>
                            <select name="caso" class="form-control form-control-sm mr-1"><option value="">Caso</option><option>NUEVO</option><option>REPETIDO</option></select>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i></button>
                        </form>
                    </div>
                </div>
                
                <!-- Recetas -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white"><h5 class="mb-0">Recetas</h5></div>
                    <div class="card-body">
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Artículo</th><th>Cant</th><th>Días</th><th>Indicaciones</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach($recetas as $r): ?>
                                <tr>
                                    <td><?= esc($r->nombre_articulo) ?></td>
                                    <td><?= $r->cantidad ?></td>
                                    <td><?= $r->dias ?></td>
                                    <td><?= esc($r->indicaciones) ?></td>
                                    <td><a href="<?= site_url('cmHistoria/eliminar_receta/' . $r->id) ?>" class="btn btn-xs btn-danger" onclick="return confirm('¿Eliminar?')"><i class="fas fa-times"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <form method="post" action="<?= site_url('cmHistoria/guardar_receta') ?>" class="form-inline mt-2">
                            <input type="hidden" name="historia_id" value="<?= $historia->id ?>">
                            <input name="nombre_articulo" class="form-control form-control-sm mr-1" placeholder="Artículo/Medicamento" style="width: 200px;" required>
                            <input name="cantidad" type="number" class="form-control form-control-sm mr-1" placeholder="Cant" value="1" style="width: 70px;">
                            <input name="dias" type="number" class="form-control form-control-sm mr-1" placeholder="Días" value="1" style="width: 70px;">
                            <input name="indicaciones" class="form-control form-control-sm mr-1" placeholder="Indicaciones" style="width: 200px;">
                            <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-plus"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
