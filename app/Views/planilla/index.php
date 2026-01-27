<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Planillas de Pago</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Planillas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Historial de Planillas</h3>
                        <div class="card-tools">
                            <a href="<?= site_url('planilla/create') ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Nueva Planilla
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Año/Mes</th>
                                    <th>Fechas</th>
                                    <th>Estado</th>
                                    <th>Creado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($planillas)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No hay planillas registradas.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($planillas as $p): ?>
                                    <tr>
                                        <td><?= $p->id ?></td>
                                        <td><?= $p->anio ?> - <?= str_pad($p->mes, 2, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <small>Inicio: <?= $p->fecha_inicio ?></small><br>
                                            <small>Corte: <?= $p->fecha_corte ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $p->estado == 'PAGADA' ? 'success' : ($p->estado == 'PROCESADA' ? 'info' : 'secondary') ?>">
                                                <?= $p->estado ?>
                                            </span>
                                        </td>
                                        <td><?= $p->created_at ?></td>
                                        <td>
                                            <a href="<?= site_url('planilla/edit/'.$p->id) ?>" class="btn btn-sm btn-info" title="Editar / Ver Detalle"><i class="fas fa-edit"></i></a>
                                            <a href="<?= site_url('planilla/export/'.$p->id) ?>" class="btn btn-sm btn-success" title="Exportar a Excel">
                                                <i class="fas fa-file-excel"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $p->id ?>" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('.btn-delete').click(function() {
        if(!confirm('¿Está seguro de eliminar esta planilla?')) return;
        var id = $(this).data('id');
        $.get("<?= site_url('planilla/delete/') ?>" + id, function(res){
            if(res.success){
                location.reload();
            } else {
                alert('Error al eliminar');
            }
        });
    });
</script>

<?= $this->endSection(); ?>
