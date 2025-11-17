<?= $this->extend('templates/admin_template') ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-clipboard-check"></i> Revisar Sugerencias de Matching</h1>
                </div>
                <div class="col-sm-6">
                    <a href="<?= site_url('aimatching') ?>" class="btn btn-secondary float-right">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (empty($suggestions)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay sugerencias pendientes de revisión.
                    <a href="<?= site_url('aimatching') ?>" class="alert-link">Procesar nuevos productos</a>
                </div>
            <?php else: ?>
                <?php foreach ($suggestions as $suggestion): ?>
                    <div class="card suggestion-card" data-id="<?= $suggestion['id'] ?>">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="mb-0">
                                        <span class="badge badge-<?= $suggestion['score'] >= 0.85 ? 'success' : ($suggestion['score'] >= 0.70 ? 'warning' : 'info') ?>">
                                            Score: <?= number_format($suggestion['score'] * 100, 1) ?>%
                                        </span>
                                    </h5>
                                </div>
                                <div class="col-md-4 text-right">
                                    <button class="btn btn-success btn-sm btn-approve" data-id="<?= $suggestion['id'] ?>">
                                        <i class="fas fa-check"></i> Aprobar
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-reject" data-id="<?= $suggestion['id'] ?>">
                                        <i class="fas fa-times"></i> Rechazar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary"><i class="fas fa-pills"></i> Producto DIGEMID</h6>
                                    <p class="mb-1"><strong><?= esc($suggestion['Nom_Prod']) ?></strong></p>
                                    <p class="mb-0 text-muted">
                                        <small>
                                            <?= esc($suggestion['Concent']) ?> - 
                                            <?= esc($suggestion['Nom_Form_Farm']) ?>
                                        </small>
                                    </p>
                                    <p class="mb-0 text-muted">
                                        <small>Código: <?= esc($suggestion['cod_prod']) ?></small>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-success"><i class="fas fa-box"></i> Artículo Establecimiento</h6>
                                    <p class="mb-1"><strong><?= esc($suggestion['ART_NOMBRE']) ?></strong></p>
                                    <p class="mb-0 text-muted">
                                        <small>Código: <?= esc($suggestion['art_key']) ?></small>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if (!empty($suggestion['metadata'])): ?>
                                <?php $metadata = json_decode($suggestion['metadata'], true); ?>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button class="btn btn-sm btn-link" type="button" data-toggle="collapse" 
                                                data-target="#details-<?= $suggestion['id'] ?>">
                                            <i class="fas fa-info-circle"></i> Ver detalles del análisis
                                        </button>
                                        <div class="collapse" id="details-<?= $suggestion['id'] ?>">
                                            <div class="card card-body bg-light mt-2">
                                                <small>
                                                    <strong>Algoritmo:</strong> <?= esc($metadata['algorithm'] ?? 'N/A') ?><br>
                                                    <?php if (isset($metadata['details'])): ?>
                                                        <strong>Producto normalizado:</strong> <?= esc($metadata['details']['product_normalized'] ?? '') ?><br>
                                                        <strong>Artículo normalizado:</strong> <?= esc($metadata['details']['article_normalized'] ?? '') ?><br>
                                                        <strong>Concentración coincide:</strong> 
                                                        <?= ($metadata['details']['concentration_match'] ?? false) ? 'Sí' : 'No' ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Rechazar Sugerencia</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectId">
                <div class="form-group">
                    <label>Motivo del rechazo (opcional)</label>
                    <textarea id="rejectReason" class="form-control" rows="3" 
                              placeholder="Ej: Productos diferentes, concentración incorrecta..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmReject" class="btn btn-danger">Confirmar Rechazo</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<script>
$(document).ready(function() {
    
    $('.btn-approve').click(function() {
        const id = $(this).data('id');
        const card = $(`.suggestion-card[data-id="${id}"]`);
        
        if (!confirm('¿Aprobar esta sugerencia y crear la relación?')) return;
        
        $.post('<?= site_url('aimatching/approve') ?>', {id: id}, function(response) {
            if (response.success) {
                card.fadeOut(300, function() {
                    $(this).remove();
                    if ($('.suggestion-card').length === 0) {
                        location.reload();
                    }
                });
                toastr.success(response.message);
            } else {
                toastr.error(response.message);
            }
        }, 'json');
    });
    
    $('.btn-reject').click(function() {
        const id = $(this).data('id');
        $('#rejectId').val(id);
        $('#rejectReason').val('');
        $('#rejectModal').modal('show');
    });
    
    $('#btnConfirmReject').click(function() {
        const id = $('#rejectId').val();
        const reason = $('#rejectReason').val();
        const card = $(`.suggestion-card[data-id="${id}"]`);
        
        $.post('<?= site_url('aimatching/reject') ?>', {id: id, reason: reason}, function(response) {
            if (response.success) {
                $('#rejectModal').modal('hide');
                card.fadeOut(300, function() {
                    $(this).remove();
                    if ($('.suggestion-card').length === 0) {
                        location.reload();
                    }
                });
                toastr.success(response.message);
            } else {
                toastr.error(response.message);
            }
        }, 'json');
    });
});
</script>
<?= $this->endSection() ?>
