<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-file-medical text-info mr-2"></i> Historia Clínica</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= site_url('cmHistoria/atencion/' . $cita->id) ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-edit mr-1"></i> Editar Atención</a>
                <a href="<?= site_url('cmCitas/listado') ?>" class="btn btn-outline-secondary btn-sm ml-1"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4><?= esc($cita->CLI_NOMBRE) ?></h4>
                        <p>DNI: <?= $cita->DNI ?: '-' ?> | Edad: <?= $cita->edad ?> años | Fecha: <?= $cita->fecha_especifica ? date('d/m/Y', strtotime($cita->fecha_especifica)) : '-' ?> | Médico: <?= esc($cita->medico) ?></p>
                        
                        <?php if($historia): ?>
                        <hr><h5>Signos Vitales</h5>
                        <div class="row">
                            <div class="col-3"><strong>Presión:</strong> <?= $historia->presion_arterial ?: '-' ?></div>
                            <div class="col-3"><strong>T°:</strong> <?= $historia->temperatura ? $historia->temperatura.' °C' : '-' ?></div>
                            <div class="col-3"><strong>Peso:</strong> <?= $historia->peso ? $historia->peso.' kg' : '-' ?></div>
                            <div class="col-3"><strong>Talla:</strong> <?= $historia->talla ? $historia->talla.' cm' : '-' ?></div>
                            <div class="col-3"><strong>Sat O2:</strong> <?= $historia->saturacion ? $historia->saturacion.'%' : '-' ?></div>
                            <div class="col-3"><strong>FC:</strong> <?= $historia->frec_cardiaca ?: '-' ?></div>
                            <div class="col-3"><strong>FR:</strong> <?= $historia->frec_respiratoria ?: '-' ?></div>
                        </div>
                        
                        <hr><h5>Examen Clínico</h5>
                        <p><?= nl2br(esc($historia->examen_clinico ?: 'Sin registro')) ?></p>
                        <h5>Plan de Trabajo</h5>
                        <p><?= nl2br(esc($historia->plan_trabajo ?: 'Sin registro')) ?></p>
                        <h5>Indicaciones</h5>
                        <p><?= nl2br(esc($historia->indicaciones ?: 'Sin registro')) ?></p>
                        <?php endif; ?>
                        
                        <?php if(!empty($diagnosticos)): ?>
                        <hr><h5>Diagnósticos CIE-10</h5>
                        <table class="table table-sm table-bordered"><thead><tr><th>Código</th><th>Descripción</th><th>Tipo</th><th>Caso</th></tr></thead>
                            <tbody>
                                <?php foreach($diagnosticos as $d): ?>
                                <tr><td><?= esc($d->cie_codigo) ?></td><td><?= esc($d->cie_descripcion) ?></td><td><?= esc($d->tipo) ?></td><td><?= esc($d->caso) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody></table>
                        <?php endif; ?>
                        
                        <?php if(!empty($recetas)): ?>
                        <hr><h5>Recetas</h5>
                        <table class="table table-sm table-bordered"><thead><tr><th>Artículo</th><th>Cant</th><th>Días</th><th>Indicaciones</th></tr></thead>
                            <tbody>
                                <?php foreach($recetas as $r): ?>
                                <tr><td><?= esc($r->nombre_articulo) ?></td><td><?= $r->cantidad ?></td><td><?= $r->dias ?></td><td><?= esc($r->indicaciones) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody></table>
                        <?php endif; ?>
                        
                        <?php if(!$historia && empty($diagnosticos) && empty($recetas)): ?>
                        <div class="alert alert-info">No hay historia clínica registrada para esta cita.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
