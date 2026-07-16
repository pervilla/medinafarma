<?= $this->extend('templates/admin_template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-check-double text-success"></i> Comprobantes Enviados SUNAT</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Facturas y comprobantes enviados (últimos 200)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Empresa</th>
                                <th>Doc</th>
                                <th>Serie</th>
                                <th>Número</th>
                                <th>Fecha Emisión</th>
                                <th>Estado</th>
                                <th>Mensaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($comprobantes)): ?>
                                <?php foreach ($comprobantes as $c): ?>
                                    <tr>
                                        <td><?= $c->id ?></td>
                                        <td>
                                            <?php $emp = $empresas[$c->empresa_ruc] ?? null; ?>
                                            <?= $emp ? '<small>'.$emp['nombre_comercial'].'</small>' : $c->empresa_ruc ?>
                                        </td>
                                        <td><span class="badge badge-secondary"><?= $c->tipo_doc == '01' ? 'Factura' : $c->tipo_doc ?></span></td>
                                        <td><?= $c->serie ?></td>
                                        <td><?= $c->correlativo ?></td>
                                        <td><?= $c->fecha_emision ?></td>
                                        <td>
                                            <?php
                                                $badge = ['ACEPTADA'=>'badge-success','RECHAZADA'=>'badge-danger','PENDIENTE'=>'badge-warning','EXCEPCION'=>'badge-secondary','ERROR'=>'badge-danger'];
                                                $cls = $badge[$c->estado_sunat] ?? 'badge-secondary';
                                            ?>
                                            <span class="badge <?= $cls ?>"><?= $c->estado_sunat ?></span>
                                        </td>
                                        <td><small class="text-muted"><?= htmlspecialchars($c->mensaje_sunat ?? '') ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">No hay comprobantes enviados aún.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
