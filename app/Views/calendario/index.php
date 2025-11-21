<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Administración de Calendario</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <?php if (session()->getFlashdata('mensaje')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-check"></i> Éxito!</h5>
                <?= session()->getFlashdata('mensaje') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Generar Calendario -->
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Generar Calendario Anual</h3>
                    </div>
                    <form action="<?= site_url('calendario/generar') ?>" method="post">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="anio">Año</label>
                                <select class="form-control" id="anio" name="anio">
                                    <?php 
                                    $currentYear = date('Y');
                                    for($i = 0; $i < 5; $i++): 
                                        $y = $currentYear + $i;
                                    ?>
                                        <option value="<?= $y ?>"><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="server">Servidor Destino</label>
                                <select class="form-control" id="server" name="server">
                                    <option value="1">Principal</option>
                                    <option value="2">Server02 (Juanjuicillo)</option>
                                    <option value="3">Server03 (Peñameza)</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Generar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cerrar Día -->
            <div class="col-md-6">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Cerrar / Habilitar Día</h3>
                    </div>
                    <form action="<?= site_url('calendario/cerrar') ?>" method="post" onsubmit="return confirm('¿Está seguro de realizar esta acción? Esto cerrará los días anteriores y habilitará el día actual.');">
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="icon fas fa-info"></i> Esta opción cerrará todos los días anteriores al día actual y habilitará el día de hoy como activo. La fecha se toma automáticamente del servidor.
                            </div>
                            <div class="form-group">
                                <label for="server_cerrar">Servidor Destino</label>
                                <select class="form-control" id="server_cerrar" name="server" required>
                                    <option value="">-- Seleccione --</option>
                                    <option value="1">Principal</option>
                                    <option value="2">Server02 (Juanjuicillo)</option>
                                    <option value="3">Server03 (Peñameza)</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-warning">Procesar Cierre</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
