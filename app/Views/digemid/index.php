<?= $this->extend('templates/admin_template') ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Generar Archivo DIGEMID</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Parámetros de Generación</h3>
                        </div>
                        <div class="card-body">
                            <form id="formDigemid">
                                <div class="form-group">
                                    <label>Establecimiento</label>
                                    <select class="form-control" name="cod_estab" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="0060004">Centro (0060004)</option>
                                        <option value="0042586">Juan Juicillo (0042586)</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Mes</label>
                                    <select class="form-control" name="mes" required>
                                        <option value="">Seleccionar...</option>
                                        <?php for($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= sprintf('%02d', $i) ?>"><?= sprintf('%02d', $i) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Año</label>
                                    <select class="form-control" name="anio" required>
                                        <option value="">Seleccionar...</option>
                                        <?php for($i = date('Y'); $i >= 2020; $i--): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-file-archive"></i> Generar ZIP
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Información</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Formato del archivo:</strong> RUC_CODIGOLOCAL_MES_AÑO</p>
                            <p><strong>Ejemplo:</strong> 20450337839_0042586_08_2025.zip</p>
                            <p><strong>Contenido:</strong> Archivo CSV con estructura:</p>
                            <code>CodEstab;CodProd;Precio 1;Precio 2</code>
                            
                            <div id="resultado" class="mt-3" style="display: none;">
                                <div class="alert alert-success">
                                    <h5><i class="icon fas fa-check"></i> Archivo generado!</h5>
                                    <p id="info-archivo"></p>
                                    <a id="btn-descargar" href="#" class="btn btn-success">
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
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
    $('#formDigemid').on('submit', function(e) {
        e.preventDefault();
        
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generando...');
        
        $.ajax({
            url: '<?= site_url('digemid/generar') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#info-archivo').html(
                        '<strong>Archivo:</strong> ' + response.archivo + '<br>' +
                        '<strong>Registros:</strong> ' + response.registros
                    );
                    $('#btn-descargar').attr('href', '<?= site_url('digemid/descargar/') ?>' + response.archivo);
                    $('#resultado').show();
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function() {
                alert('Error al generar el archivo');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-file-archive"></i> Generar ZIP');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>