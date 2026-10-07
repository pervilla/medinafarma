<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-chart-line text-primary mr-2"></i> <?= esc($titulo) ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= site_url('cmCitas') ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-calendar-alt mr-1"></i> Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="get" action="<?= site_url('cmCitas/balance') ?>" class="row align-items-end">
                    <div class="col-md-2">
                        <label class="small">Tipo de Balance</label>
                        <select name="tipo" class="form-control form-control-sm">
                            <option value="dia" <?= $tipo == 'dia' ? 'selected' : '' ?>>Diario</option>
                            <option value="campania" <?= $tipo == 'campania' ? 'selected' : '' ?>>Por Campaña</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small">Desde</label>
                        <input type="date" name="desde" class="form-control form-control-sm" value="<?= esc($desde) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small">Hasta</label>
                        <input type="date" name="hasta" class="form-control form-control-sm" value="<?= esc($hasta) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small">Local</label>
                        <select name="local" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <?php foreach ($locales as $k => $v): ?>
                                <option value="<?= $k ?>" <?= $local == $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small">Campaña</label>
                        <select name="horario_id" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            <?php foreach ($campanias as $c): ?>
                                <option value="<?= $c->id ?>" <?= intval($horario_id) == $c->id ? 'selected' : '' ?>>
                                    <?= $c->fecha_especifica ? date('d/m/Y', strtotime($c->fecha_especifica)) : 'Recurrente' ?> - <?= esc(substr($c->medico, 0, 25)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-search mr-1"></i> Consultar</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-block mt-1" onclick="window.print()"><i class="fas fa-print mr-1"></i> Imprimir</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resumen -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="info-box"><div class="info-box-icon bg-success"><i class="fas fa-hand-holding-usd"></i></div>
                    <div class="info-box-content"><span class="info-box-text">Total Cobrado</span><span class="info-box-number">S/ <?= number_format($gran_total, 2) ?></span></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box"><div class="info-box-icon bg-info"><i class="fas fa-receipt"></i></div>
                    <div class="info-box-content"><span class="info-box-text">N° de Pagos</span><span class="info-box-number"><?= $gran_n ?></span></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card"><div class="card-header py-2"><h6 class="mb-0">Totales por Forma de Pago</h6></div>
                    <div class="card-body py-2">
                        <?php if (empty($formas)): ?>
                            <span class="text-muted">Sin cobros en el rango.</span>
                        <?php else: foreach ($formas as $f => $d): ?>
                            <span class="badge badge-light border mr-1 mb-1" style="font-size:.85rem;">
                                <?= esc($f) ?>: S/ <?= number_format($d['total'], 2) ?> (<?= $d['n'] ?>)
                            </span>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla agrupada -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><?= $tipo == 'campania' ? 'Resumen por Campaña' : 'Resumen por Día' ?></h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light"><tr><th><?= $tipo == 'campania' ? 'Campaña' : 'Fecha' ?></th><th>N° Pagos</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                        <?php if (!empty($grupos)): foreach ($grupos as $g): ?>
                        <tr><td><?= esc($g['label']) ?></td><td><?= $g['n'] ?></td><td class="text-right">S/ <?= number_format($g['total'], 2) ?></td></tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="3" class="text-center text-muted">Sin resultados</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detalle -->
        <div class="card">
            <div class="card-header"><h3 class="card-title">Detalle de Cobros</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr><th>Ticket</th><th>Fecha</th><th>Paciente</th><th>Campaña / Médico</th><th>Forma</th><th>N° Operación</th><th>Local</th><th class="text-right">Monto</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($detalle)): foreach ($detalle as $p): ?>
                        <tr>
                            <td><?= esc($p->ticket_nro) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($p->fecha_pago)) ?></td>
                            <td><?= esc($p->CLI_NOMBRE) ?></td>
                            <td><?= $p->fecha_especifica ? date('d/m/Y', strtotime($p->fecha_especifica)) : '-' ?> - <?= esc($p->medico ?: '-') ?></td>
                            <td><span class="badge badge-info"><?= esc($p->forma_pago) ?></span></td>
                            <td><?= esc($p->nro_operacion ?: '-') ?></td>
                            <td><?= esc($locales[trim($p->local_pago ?? '')] ?? trim($p->local_pago ?? '')) ?></td>
                            <td class="text-right">S/ <?= number_format($p->monto, 2) ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="8" class="text-center text-muted">Sin cobros en el rango seleccionado</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Comprobantes Emitidos -->
        <div class="card">
            <div class="card-header bg-primary text-white"><h3 class="card-title mb-0"><i class="fas fa-file-invoice-dollar mr-2"></i> Comprobantes Emitidos</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr><th>N° Comprobante</th><th>Fecha</th><th>Paciente</th><th>Campaña / Médico</th><th>SUNAT</th><th>Local</th><th class="text-right">Monto</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($comprobantes)): foreach ($comprobantes as $c): ?>
                        <tr>
                            <td><?= esc($c->tipo_documento) ?>-<?= esc($c->serie) ?>-<?= str_pad($c->correlativo, 8, '0', STR_PAD_LEFT) ?></td>
                            <td><?= date('d/m/Y', strtotime($c->fecha_emision)) ?></td>
                            <td><?= esc($c->CLI_NOMBRE) ?></td>
                            <td><?= $c->fecha_especifica ? date('d/m/Y', strtotime($c->fecha_especifica)) : '-' ?> - <?= esc($c->medico ?: '-') ?></td>
                            <td><span class="badge badge-<?= $c->estado_sunat == 2 ? 'success' : ($c->estado_sunat == 3 ? 'danger' : ($c->estado_sunat == 1 ? 'info' : 'warning')) ?>"><?= esc($c->sunat_nombre) ?></span></td>
                            <td><?= esc($locales[trim($c->local_id ?? '')] ?? trim($c->local_id ?? '')) ?></td>
                            <td class="text-right">S/ <?= number_format($c->monto, 2) ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center text-muted">Sin comprobantes en el rango seleccionado</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot><tr class="font-weight-bold bg-light"><td colspan="6" class="text-right">TOTAL EMITIDOS:</td><td class="text-right">S/ <?= number_format($total_emitidos, 2) ?></td></tr></tfoot>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>