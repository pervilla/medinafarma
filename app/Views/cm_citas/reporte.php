<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-chart-bar text-primary mr-2"></i> <?= esc($titulo) ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= site_url('cmCitas') ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-calendar-alt mr-1"></i> Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card mb-3">
            <div class="card-body">
                <form method="get" class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small">Mes</label>
                        <select name="mes" class="form-control form-control-sm">
                            <?php foreach (['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'] as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $mes == $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small">Año</label>
                        <input type="number" name="anio" class="form-control form-control-sm" value="<?= $anio ?>" min="2020" max="2100">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-search mr-1"></i> Consultar</button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-block" onclick="window.print()"><i class="fas fa-print mr-1"></i> Imprimir</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fas fa-cash-register mr-2"></i> Pagos Realizados</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light"><tr><th>Ticket</th><th>Fecha</th><th>Paciente</th><th>Médico</th><th>Local</th><th>Estado</th><th>Monto</th></tr></thead>
                            <tbody>
                                <?php if (!empty($pagos)): foreach ($pagos as $p): ?>
                                <tr>
                                    <td><?= esc($p->ticket_nro) ?></td>
                                    <td><?= date('d/m/Y', strtotime($p->fecha_pago)) ?></td>
                                    <td><?= esc($p->CLI_NOMBRE) ?></td>
                                    <td><?= esc($p->medico ?: '-') ?></td>
                                    <td><?= esc($p->local_pago) ?></td>
                                    <td><span class="badge badge-<?= $p->estado == 2 ? 'info' : 'success' ?>"><?= esc($p->estado_nombre) ?></span></td>
                                    <td class="text-right">S/ <?= number_format($p->monto, 2) ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="7" class="text-center text-muted">Sin pagos este mes</td></tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot><tr class="font-weight-bold bg-light"><td colspan="6" class="text-right">TOTAL PAGOS:</td><td class="text-right">S/ <?= number_format($total_pagos, 2) ?></td></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-file-invoice-dollar mr-2"></i> Comprobantes Emitidos</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light"><tr><th>N° Comprobante</th><th>Fecha</th><th>Paciente</th><th>SUNAT</th><th>Monto</th></tr></thead>
                            <tbody>
                                <?php if (!empty($comprobantes)): foreach ($comprobantes as $c): ?>
                                <tr>
                                    <td><?= $c->tipo_documento ?>-<?= esc($c->serie) ?>-<?= str_pad($c->correlativo, 8, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= date('d/m/Y', strtotime($c->fecha_emision)) ?></td>
                                    <td><?= esc($c->CLI_NOMBRE) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $c->estado_sunat == 2 ? 'success' : ($c->estado_sunat == 3 ? 'danger' : ($c->estado_sunat == 1 ? 'info' : 'warning')) ?>"><?= esc($c->sunat_nombre) ?></span>
                                    </td>
                                    <td class="text-right">S/ <?= number_format($c->monto, 2) ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="5" class="text-center text-muted">Sin comprobantes este mes</td></tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot><tr class="font-weight-bold bg-light"><td colspan="4" class="text-right">TOTAL EMITIDOS:</td><td class="text-right">S/ <?= number_format($total_emitidos, 2) ?></td></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
