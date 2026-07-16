<?= $this->extend('templates/admin_template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-history text-primary"></i> Historial de Resúmenes SUNAT</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= base_url('sunat') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Nuevo Resumen
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Resúmenes enviados a SUNAT (últimos 200)</h5>
                <div>
                    <button class="btn btn-sm btn-warning" onclick="ejecutarCron()" title="Ejecutar cron manualmente">
                        <i class="fas fa-play"></i> Ejecutar Cron
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Empresa</th>
                                <th>Serie</th>
                                <th>Fecha Comprobantes</th>
                                <th>Fecha Envío</th>
                                <th>Ticket SUNAT</th>
                                <th>Estado</th>
                                <th>Mensaje</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($resumenes)): ?>
                                <?php foreach ($resumenes as $r): ?>
                                    <tr>
                                        <td><?= $r->id ?></td>
                                        <td>
                                            <?php 
                                                $emp = $empresas[$r->empresa_ruc] ?? null;
                                                echo $emp ? '<small>' . $emp['nombre_comercial'] . '</small><br><code>' . $r->empresa_ruc . '</code>' : $r->empresa_ruc;
                                            ?>
                                        </td>
                                        <td><span class="badge badge-secondary"><?= $r->serie ?? 'N/A' ?></span></td>
                                        <td><?= $r->fecha_generacion ?></td>
                                        <td><?= $r->fecha_resumen ?></td>
                                        <td>
                                            <?php if (!empty($r->ticket)): ?>
                                                <code><?= $r->ticket ?></code>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                                $badge = [
                                                    'PENDIENTE' => 'badge-warning',
                                                    'ACEPTADA'  => 'badge-success',
                                                    'RECHAZADA' => 'badge-danger',
                                                    'EXCEPCION' => 'badge-secondary',
                                                    'ERROR'     => 'badge-danger',
                                                ];
                                                $cls = $badge[$r->estado_sunat] ?? 'badge-secondary';
                                            ?>
                                            <span class="badge <?= $cls ?>"><?= $r->estado_sunat ?></span>
                                        </td>
                                        <td><small class="text-muted"><?= htmlspecialchars($r->mensaje_sunat ?? '') ?></small></td>
                                        <td>
                                            <?php if ($r->estado_sunat === 'PENDIENTE' && !empty($r->ticket)): ?>
                                                <button class="btn btn-xs btn-info" 
                                                        onclick="consultarTicket('<?= $r->ticket ?>', '<?= $r->empresa_ruc ?>', this)"
                                                        title="Consultar estado en SUNAT">
                                                    <i class="fas fa-search"></i> Consultar
                                                </button>
                                            <?php elseif (in_array($r->estado_sunat, ['RECHAZADA', 'ERROR', 'EXCEPCION'])): ?>
                                                <a href="<?= base_url('sunat') ?>?empresa_ruc=<?= $r->empresa_ruc ?>&serie=<?= $r->serie ?? '' ?>&fecha=<?= $r->fecha_generacion ?>" 
                                                   class="btn btn-xs btn-warning" title="Reenviar resumen">
                                                    <i class="fas fa-redo"></i> Reenviar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        No hay resúmenes registrados aún.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function ejecutarCron() {
    const dias = prompt('¿Cuántos días hacia atrás procesar? (3 = últimos 3 días hasta ayer)', '3');
    if (!dias) return;
    if (!confirm(`¿Ejecutar cron para los últimos ${dias} días?`)) return;
    const btn = document.querySelector('.btn-warning');
    $(btn).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Ejecutando...');
    $.ajax({
        url: '<?= base_url('SunatController/cron_manual') ?>',
        type: 'POST',
        data: { dias: dias },
        success: function(resp) {
            if (resp.log) {
                let msg = resp.log.join('\n');
                if (msg.length > 2000) msg = msg.substring(0, 2000) + '\n... (ver logs completos)';
                alert('✅ Cron ejecutado.\n\n' + msg);
            }
            location.reload();
        },
        error: function() { alert('Error al ejecutar cron'); location.reload(); }
    });
}

function consultarTicket(ticket, empresa_ruc, btn) {
    $(btn).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    $.ajax({
        url: '<?= base_url('SunatController/consultar_ticket') ?>',
        type: 'POST',
        data: { ticket: ticket, empresa_ruc: empresa_ruc },
        timeout: 60000, // 60 segundos de timeout
        success: function(response) {
            if (response.status === 'success') {
                alert('✅ Estado: ' + response.estado + '\n' + response.descripcion);
                location.reload();
            } else {
                let msg = '❌ ' + (response.message || 'Error desconocido');
                if (response.code) msg += '\nCódigo: ' + response.code;
                if (response.code === '98') msg += '\n⏳ Ticket aún en proceso en SUNAT. Vuelve a consultar más tarde.';
                alert(msg);
                $(btn).prop('disabled', false).html('<i class="fas fa-search"></i> Consultar');
            }
        },
        error: function(xhr, status, error) {
            if (status === 'timeout') {
                alert('⏱ La consulta a SUNAT tardó demasiado. Intenta de nuevo más tarde.');
            } else {
                alert('❌ Error de conexión: ' + (error || 'sin respuesta del servidor'));
            }
            $(btn).prop('disabled', false).html('<i class="fas fa-search"></i> Consultar');
        }
    });
}
</script>

<?= $this->endSection() ?>
