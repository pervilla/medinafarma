<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $titulo ?? 'Editar Egreso' ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('egresos') ?>">Egresos</a></li>
                    <li class="breadcrumb-item active">Editar Egreso #<?= $egreso['EGR_ID'] ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <?php if (session('errors')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Errores de validación</h5>
                <ul>
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (session('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <i class="icon fas fa-ban"></i> <?= session('error') ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Datos del Egreso</h3>
                    </div>
                    <form method="post" action="<?= site_url('egresos/actualizar/' . $egreso['EGR_ID']) ?>">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha">Fecha <span class="text-danger">*</span></label>
                                        <input type="date" name="fecha" id="fecha" class="form-control" value="<?= old('fecha', $egreso['EGR_FECHA']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="local">Local <span class="text-danger">*</span></label>
                                        <select name="local" id="local" class="form-control" required>
                                            <?php foreach ($locales as $value => $nombre): ?>
                                                <option value="<?= $value ?>" <?= old('local', $egreso['EGR_LOCAL']) == $value ? 'selected' : '' ?>><?= esc($nombre) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="cuenta_id">Cuenta Contable <span class="text-danger">*</span></label>
                                        <select name="cuenta_id" id="cuenta_id" class="form-control select2" style="width: 100%;" required>
                                            <?php foreach ($cuentas as $id => $nombre): ?>
                                                <option value="<?= $id ?>" <?= old('cuenta_id', $egreso['EGR_CUENTA_ID']) == $id ? 'selected' : '' ?>><?= esc($nombre) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="monto">Monto (S/.) <span class="text-danger">*</span></label>
                                        <input type="number" name="monto" id="monto" class="form-control" step="0.01" min="0.01" value="<?= old('monto', $egreso['EGR_MONTO']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="descripcion">Descripción <span class="text-danger">*</span></label>
                                        <input type="text" name="descripcion" id="descripcion" class="form-control" value="<?= old('descripcion', $egreso['EGR_DESCRIPCION']) ?>" required maxlength="255">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="forma_pago">Forma de Pago <span class="text-danger">*</span></label>
                                        <select name="forma_pago" id="forma_pago" class="form-control" required>
                                            <?php foreach ($formas_pago as $value => $nombre): ?>
                                                <option value="<?= $value ?>" <?= old('forma_pago', $egreso['EGR_FORMA_PAGO']) == $value ? 'selected' : '' ?>><?= esc($nombre) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="estado">Estado <span class="text-danger">*</span></label>
                                        <select name="estado" id="estado" class="form-control" required>
                                            <?php foreach ($estados as $value => $nombre): ?>
                                                <option value="<?= $value ?>" <?= old('estado', $egreso['EGR_ESTADO']) == $value ? 'selected' : '' ?>><?= esc($nombre) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="comprobante_tipo">Tipo Comprobante</label>
                                        <select name="comprobante_tipo" id="comprobante_tipo" class="form-control">
                                            <option value="">Sin comprobante</option>
                                            <?php foreach ($comprobantes_tipo as $value => $nombre): ?>
                                                <option value="<?= $value ?>" <?= old('comprobante_tipo', $egreso['EGR_COMPROBANTE_TIPO']) == $value ? 'selected' : '' ?>><?= esc($nombre) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="comprobante_serie">Serie</label>
                                        <input type="text" name="comprobante_serie" id="comprobante_serie" class="form-control" value="<?= old('comprobante_serie', $egreso['EGR_COMPROBANTE_SERIE']) ?>" maxlength="10">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="comprobante_numero">Número</label>
                                        <input type="text" name="comprobante_numero" id="comprobante_numero" class="form-control" value="<?= old('comprobante_numero', $egreso['EGR_COMPROBANTE_NUMERO']) ?>" maxlength="20">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="responsable">Responsable</label>
                                        <input type="text" name="responsable" id="responsable" class="form-control" value="<?= old('responsable', $egreso['EGR_RESPONSABLE']) ?>" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="observaciones">Observaciones</label>
                                        <textarea name="observaciones" id="observaciones" class="form-control" rows="2"><?= old('observaciones', $egreso['EGR_OBSERVACIONES']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($egreso['EGR_CAJA_MOV_ID'])): ?>
                                <div class="alert alert-info">
                                    <i class="icon fas fa-info-circle"></i> Este egreso tiene un movimiento de caja asociado (ID: <?= $egreso['EGR_CAJA_MOV_ID'] ?>).
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Egreso
                            </button>
                            <a href="<?= site_url('egresos') ?>" class="btn btn-default">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            
                            <?php if (empty($egreso['EGR_CAJA_MOV_ID'])): ?>
                                <a href="<?= site_url('egresos/eliminar/' . $egreso['EGR_ID']) ?>" class="btn btn-danger float-right" onclick="return confirm('¿Está seguro de eliminar este egreso?');">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    $(function () {
        // Inicializar Select2
        $('.select2').select2();
        
        // Cambiar estado para mostrar/ocultar campos de comprobante
        $('#comprobante_tipo').change(function() {
            if ($(this).val() === '') {
                $('#comprobante_serie, #comprobante_numero').prop('disabled', true).val('');
            } else {
                $('#comprobante_serie, #comprobante_numero').prop('disabled', false);
            }
        }).trigger('change');
    });
</script>
<?= $this->endSection(); ?>