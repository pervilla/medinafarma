<?= $this->extend('templates/admin_template') ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-robot"></i> Matching Automático con IA</h1>
                </div>
                <div class="col-sm-6">
                    <a href="<?= site_url('digemidrelacion') ?>" class="btn btn-secondary float-right">
                        <i class="fas fa-arrow-left"></i> Volver a Manual
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= number_format($stats['total_digemid']) ?></h3>
                            <p>Total Productos DIGEMID</p>
                        </div>
                        <div class="icon"><i class="fas fa-database"></i></div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= number_format($stats['matched']) ?></h3>
                            <p>Emparejados (<?= $stats['match_rate'] ?>%)</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= number_format($stats['unmatched']) ?></h3>
                            <p>Sin Emparejar</p>
                        </div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?= number_format($stats['pending_suggestions'] ?? 0) ?></h3>
                            <p>Sugerencias Pendientes</p>
                        </div>
                        <div class="icon"><i class="fas fa-lightbulb"></i></div>
                    </div>
                </div>
            </div>

            <!-- Processing Panel -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-cogs"></i> Procesamiento Automático</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Tamaño del Lote</label>
                                <select id="batchSize" class="form-control">
                                    <option value="10">10 productos</option>
                                    <option value="25">25 productos</option>
                                    <option value="50" selected>50 productos</option>
                                    <option value="100">100 productos</option>
                                </select>
                            </div>
                            
                            <button id="btnProcessBatch" class="btn btn-primary btn-block">
                                <i class="fas fa-play"></i> Iniciar Procesamiento Local
                            </button>
                            
                            <button id="btnProcessCloudflare" class="btn btn-info btn-block mt-2">
                                <i class="fas fa-cloud"></i> Procesar con Cloudflare
                            </button>
                            
                            <div id="processingStatus" class="mt-3"></div>
                            
                            <div class="progress mt-3" style="display:none;" id="progressBar">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-tasks"></i> Acciones Rápidas</h3>
                        </div>
                        <div class="card-body">
                            <a href="<?= site_url('aimatching/review') ?>" class="btn btn-success btn-block btn-lg">
                                <i class="fas fa-clipboard-check"></i> Revisar Sugerencias
                                <?php if ($stats['pending_suggestions'] > 0): ?>
                                    <span class="badge badge-light"><?= $stats['pending_suggestions'] ?></span>
                                <?php endif; ?>
                            </a>
                            
                            <button id="btnBatchApprove" class="btn btn-warning btn-block mt-2">
                                <i class="fas fa-magic"></i> Aprobar Alta Confianza (>85%)
                            </button>
                            
                            <button id="btnBatchReject" class="btn btn-danger btn-block mt-2">
                                <i class="fas fa-times-circle"></i> Rechazar Todas Pendientes
                            </button>
                            
                            <hr>
                            
                            <div class="info-box bg-light">
                                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Score Promedio Aprobados</span>
                                    <span class="info-box-number">
                                        <?= number_format(($stats['avg_approved_score'] ?? 0) * 100, 1) ?>%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Algorithm Info -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Información del Algoritmo</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h5>Normalización de Texto</h5>
                                    <ul>
                                        <li>Conversión a minúsculas</li>
                                        <li>Eliminación de acentos</li>
                                        <li>Aplicación de sinónimos farmacéuticos</li>
                                        <li>Extracción de concentraciones</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h5>Cálculo de Similitud</h5>
                                    <ul>
                                        <li>Distancia Levenshtein (50%)</li>
                                        <li>Índice Jaccard (palabras)</li>
                                        <li>Matching de concentración (30%)</li>
                                        <li>Forma farmacéutica (20%)</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h5>Umbrales de Confianza</h5>
                                    <ul>
                                        <li><strong>≥85%:</strong> Auto-aprobación</li>
                                        <li><strong>70-84%:</strong> Sugerencia</li>
                                        <li><strong>50-69%:</strong> Revisión manual</li>
                                        <li><strong>&lt;50%:</strong> Descartado</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<script>
$(document).ready(function() {
    
    $('#btnProcessBatch').click(function() {
        const batchSize = $('#batchSize').val();
        const btn = $(this);
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        $('#progressBar').show().find('.progress-bar').css('width', '50%');
        
        $.post('<?= site_url('aimatching/processBatch') ?>', {batch_size: batchSize}, function(response) {
            if (response.success) {
                $('#processingStatus').html(`
                    <div class="alert alert-success">
                        <i class="fas fa-check"></i> ${response.message}
                    </div>
                `);
                setTimeout(() => location.reload(), 2000);
            } else {
                $('#processingStatus').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-times"></i> Error: ${response.message}
                    </div>
                `);
            }
        }, 'json').always(function() {
            btn.prop('disabled', false).html('<i class="fas fa-play"></i> Iniciar Procesamiento Local');
            $('#progressBar').hide();
        });
    });
    
    $('#btnProcessCloudflare').click(function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando con Cloudflare...');
        
        $.post('<?= site_url('aimatching/processWithCloudflare') ?>', {}, function(response) {
            if (response.success) {
                $('#processingStatus').html(`
                    <div class="alert alert-success">
                        <i class="fas fa-cloud"></i> ${response.message}
                    </div>
                `);
                setTimeout(() => location.reload(), 2000);
            } else {
                $('#processingStatus').html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> ${response.message}
                    </div>
                `);
            }
        }, 'json').always(function() {
            btn.prop('disabled', false).html('<i class="fas fa-cloud"></i> Procesar con Cloudflare');
        });
    });
    
    $('#btnBatchApprove').click(function() {
        if (!confirm('¿Aprobar automáticamente todas las sugerencias con confianza ≥85%?')) return;
        
        const btn = $(this);
        btn.prop('disabled', true);
        
        $.post('<?= site_url('aimatching/batchApprove') ?>', {}, function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            }
        }, 'json').always(function() {
            btn.prop('disabled', false);
        });
    });
    
    $('#btnBatchReject').click(function() {
        if (!confirm('¿Rechazar TODAS las sugerencias pendientes? Esto permitirá procesar un nuevo lote.')) return;
        
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Rechazando...');
        
        $.post('<?= site_url('aimatching/batchReject') ?>', {}, function(response) {
            if (response.success) {
                alert(response.message + '. Ahora puedes procesar un nuevo lote.');
                location.reload();
            }
        }, 'json').always(function() {
            btn.prop('disabled', false).html('<i class="fas fa-times-circle"></i> Rechazar Todas Pendientes');
        });
    });
});
</script>
<?= $this->endSection() ?>
