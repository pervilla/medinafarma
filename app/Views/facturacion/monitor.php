<?= $this->extend('templates/admin_template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Monitor de Facturación Automática</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Incidencias / Errores Recientes -->
        <?php if (!empty($incidencias)): ?>
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-circle"></i> Incidencias / Errores Recientes</h3>
                <div class="card-tools">
                   <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                   <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="card-body table-responsive p-0" style="max-height: 200px;">
                <table class="table table-sm text-nowrap">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Documento</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incidencias as $err): ?>
                            <tr>
                                <td><?= $err['Fecha'] ?></td>
                                <td><b><?= $err['NroOficial'] ?></b></td>
                                <td class="text-danger"><small><?= $err['Observacion'] ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Facturas Individuales -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-invoice"></i> Facturas Individuales Enviadas</h3>
                <div class="card-tools">
                   <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                     <a href="<?= base_url('facturacion/monitor') ?>" class="btn btn-tool">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0" style="max-height: 400px;">
                <table class="table table-hover table-sm text-nowrap table-head-fixed">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Serie-Número</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($invoices)): ?>
                            <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><?= $inv['Fecha'] ?></td>
                                    <td>
                                        <?php if ($inv['TipoDoc'] == '01'): ?>
                                            <span class="badge badge-primary">Factura</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Boleta</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= $inv['Serie'] ?>-<?= str_pad($inv['Numero'], 8, '0', STR_PAD_LEFT) ?></strong></td>
                                    <td>S/ <?= number_format($inv['Total'], 2) ?></td>
                                    <td>
                                        <?php if ($inv['Estado'] == 1): ?>
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Enviado</span>
                                        <?php elseif ($inv['Estado'] == 2): ?>
                                            <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Observado</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><i class="fas fa-times"></i> Error</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($inv['Estado'] != 1): ?>
                                            <button class="btn btn-xs btn-warning" onclick="reenviarFactura('<?= $inv['CodCia'] ?>', '<?= $inv['NumSerRaw'] ?>', '<?= $inv['NumFacRaw'] ?>')">
                                                <i class="fas fa-redo"></i> Reenviar
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay facturas enviadas recientemente.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Resúmenes Diarios -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> Resúmenes Diarios (RC/RA)</h3>
                <div class="card-tools">
                   <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                     <a href="<?= base_url('facturacion/monitor') ?>" class="btn btn-tool">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0" style="max-height: 400px;">
                <table class="table table-hover table-sm text-nowrap table-head-fixed">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Tipo Doc</th>
                            <th>Nro Oficial</th>
                            <th>Ticket</th>
                            <th>Estado</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($summaries)): ?>
                            <?php foreach ($summaries as $item): ?>
                                <tr>
                                    <td><?= $item['iD'] ?></td>
                                    <td><?= $item['Fecha'] ?></td>
                                    <td>
                                        <?php 
                                            switch($item['TipoDocumento']) {
                                                case 'RC': echo '<span class="badge badge-info">Resumen (RC)</span>'; break;
                                                case 'RA': echo '<span class="badge badge-warning">Baja (RA)</span>'; break;
                                                default: echo $item['TipoDocumento'];
                                            }
                                        ?>
                                    </td>
                                    <td><?= $item['NroOficial'] ?></td>
                                    <td><?= $item['Ticket'] ?></td>
                                    <td>
                                        <?php if ($item['CodigoEstado'] == 1): ?>
                                            <span class="badge badge-success">Enviado</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Error</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $item['Observacion'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay registros recientes.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
function reenviarFactura(codCia, numSer, numFac) {
    if (!confirm('¿Está seguro de reenviar esta factura a SUNAT?')) {
        return;
    }
    
    // TODO: Implement AJAX call to resend endpoint
    alert('Funcionalidad de reenvío en desarrollo. Por ahora use: php spark facturacion:enviar-facturas [fecha]');
}
</script>

<?= $this->endSection() ?>
