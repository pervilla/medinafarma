<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-heartbeat text-danger mr-2"></i> <?= esc($titulo) ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= site_url('cmHistoria/atencion/' . $cita->id) ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-stethoscope mr-1"></i> Ir a Atención</a>
                <a href="<?= site_url('cmCitas/listado') ?>" class="btn btn-outline-secondary btn-sm ml-1"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Datos del paciente -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fas fa-user mr-2"></i> Paciente</h5></div>
                    <div class="card-body">
                        <strong><?= esc($cita->CLI_NOMBRE) ?></strong><br>
                        <small>DNI: <?= esc($cita->DNI ?: '-') ?> | Edad: <?= $cita->edad ?> años</small><br>
                        <small>Médico: <?= esc($cita->medico) ?></small><br>
                        <small>Fecha: <?= $cita->fecha_especifica ? date('d/m/Y', strtotime($cita->fecha_especifica)) : '-' ?></small>
                    </div>
                </div>
            </div>
            
            <!-- Formulario Triaje -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning"><h5 class="mb-0"><i class="fas fa-notes-medical mr-2"></i> Signos Vitales (Triaje)</h5></div>
                    <div class="card-body">
                        <form method="post" action="<?= site_url('cmHistoria/guardar_triaje') ?>">
                            <input type="hidden" name="historia_id" value="<?= $historia->id ?>">
                            <input type="hidden" name="cita_id" value="<?= $cita->id ?>">
                            <div class="row">
                                <div class="col-md-3"><div class="form-group">
                                    <label>Presión Arterial</label>
                                    <input name="presion_arterial" class="form-control" placeholder="120/80" value="<?= esc($historia->presion_arterial ?? '') ?>">
                                </div></div>
                                <div class="col-md-3"><div class="form-group">
                                    <label>Temperatura (°C)</label>
                                    <input name="temperatura" type="number" step="0.1" class="form-control" placeholder="36.5" value="<?= $historia->temperatura ?? '' ?>">
                                </div></div>
                                <div class="col-md-3"><div class="form-group">
                                    <label>Peso (kg)</label>
                                    <input name="peso" type="number" step="0.1" class="form-control" placeholder="70" value="<?= $historia->peso ?? '' ?>">
                                </div></div>
                                <div class="col-md-3"><div class="form-group">
                                    <label>Talla (cm)</label>
                                    <input name="talla" type="number" step="0.1" class="form-control" placeholder="165" value="<?= $historia->talla ?? '' ?>">
                                </div></div>
                            </div>
                            <div class="row">
                                <div class="col-md-3"><div class="form-group">
                                    <label>Saturación O2 (%)</label>
                                    <input name="saturacion" type="number" class="form-control" placeholder="98" value="<?= $historia->saturacion ?? '' ?>">
                                </div></div>
                                <div class="col-md-3"><div class="form-group">
                                    <label>Frec. Cardíaca</label>
                                    <input name="frec_cardiaca" type="number" class="form-control" placeholder="72" value="<?= $historia->frec_cardiaca ?? '' ?>">
                                </div></div>
                                <div class="col-md-3"><div class="form-group">
                                    <label>Frec. Respiratoria</label>
                                    <input name="frec_respiratoria" type="number" class="form-control" placeholder="16" value="<?= $historia->frec_respiratoria ?? '' ?>">
                                </div></div>
                                <div class="col-md-3 d-flex align-items-end"><div class="form-group w-100">
                                    <button type="submit" class="btn btn-warning btn-block"><i class="fas fa-save mr-1"></i> Guardar y Continuar</button>
                                </div></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
