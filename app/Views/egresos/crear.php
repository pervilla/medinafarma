<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $titulo ?? 'Registrar Nuevo Gasto' ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('egresos') ?>">Egresos</a></li>
                    <li class="breadcrumb-item active">Nuevo Gasto</li>
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
                        <h3 class="card-title">Datos del Gasto</h3>
                    </div>
                    <form method="post" action="<?= site_url('egresos/guardar') ?>">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha">Fecha <span class="text-danger">*</span></label>
                                        <input type="date" name="fecha" id="fecha" class="form-control" value="<?= old('fecha', date('Y-m-d')) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="local">Local <span class="text-danger">*</span></label>
                                        <select name="local" id="local" class="form-control" required>
                                            <?php foreach ($locales as $value => $nombre): ?>
                                                <option value="<?= $value ?>" <?= old('local', session('caja') ?? 1) == $value ? 'selected' : '' ?>><?= esc($nombre) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="cuenta_id">Cuenta Contable <span class="text-danger">*</span></label>
                                        <select name="cuenta_id" id="cuenta_id" class="form-control select2" style="width: 100%;" required>
                                            <?php foreach ($cuentas as $id => $nombre): ?>
                                                <option value="<?= $id ?>" <?= old('cuenta_id') == $id ? 'selected' : '' ?>><?= esc($nombre) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="monto">Monto (S/.) <span class="text-danger">*</span></label>
                                        <input type="number" name="monto" id="monto" class="form-control" step="0.01" min="0.01" value="<?= old('monto') ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="descripcion">Descripción <span class="text-danger">*</span></label>
                                        <input type="text" name="descripcion" id="descripcion" class="form-control" value="<?= old('descripcion') ?>" required maxlength="255">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="forma_pago">Forma de Pago <span class="text-danger">*</span></label>
                                        <select name="forma_pago" id="forma_pago" class="form-control" required>
                                            <?php foreach ($formas_pago as $value => $nombre): ?>
                                                <option value="<?= $value ?>" <?= old('forma_pago', 'EFECTIVO') == $value ? 'selected' : '' ?>><?= esc($nombre) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="estado">Estado</label>
                                        <select name="estado" id="estado" class="form-control">
                                            <option value="pagado" <?= old('estado', 'pagado') == 'pagado' ? 'selected' : '' ?>>Pagado</option>
                                            <option value="pendiente" <?= old('estado') == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        </select>
                                        <small class="text-muted">Si selecciona "Pendiente", no se registrará movimiento en caja.</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="comprobante_tipo">Tipo Comprobante</label>
                                        <select name="comprobante_tipo" id="comprobante_tipo" class="form-control">
                                            <option value="">Sin comprobante</option>
                                            <?php foreach ($comprobantes_tipo as $value => $nombre): ?>
                                                <option value="<?= $value ?>" <?= old('comprobante_tipo') == $value ? 'selected' : '' ?>><?= esc($nombre) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="comprobante_serie">Serie</label>
                                        <input type="text" name="comprobante_serie" id="comprobante_serie" class="form-control" value="<?= old('comprobante_serie') ?>" maxlength="10">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="comprobante_numero">Número</label>
                                        <input type="text" name="comprobante_numero" id="comprobante_numero" class="form-control" value="<?= old('comprobante_numero') ?>" maxlength="20">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="responsable">Responsable</label>
                                        <input type="text" name="responsable" id="responsable" class="form-control" value="<?= old('responsable') ?>" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="observaciones">Observaciones</label>
                                        <textarea name="observaciones" id="observaciones" class="form-control" rows="2"><?= old('observaciones') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Registrar Gasto
                            </button>
                            <a href="<?= site_url('egresos') ?>" class="btn btn-default">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
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