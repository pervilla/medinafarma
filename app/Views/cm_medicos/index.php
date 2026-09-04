<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-user-md text-primary mr-2"></i> <?= esc($titulo) ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <button class="btn btn-primary" onclick="$('#modalMedico').modal('show'); $('#formMedico')[0].reset(); $('#medico_id').val(''); $('#modalTitulo').text('Nuevo Médico');">
                    <i class="fas fa-plus mr-1"></i> Nuevo Médico
                </button>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Apellidos, Nombres</th>
                            <th>DNI</th>
                            <th>CMP</th>
                            <th>Especialidad</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicos as $m): ?>
                        <tr>
                            <td><?= $m['id'] ?></td>
                             <td>
                                <?php 
                                $photo_url = null;
                                if (!empty($m['cliente_id'])) {
                                    $file_path = FCPATH . 'dist/img/' . $m['cliente_id'] . '.jpg';
                                    if (file_exists($file_path)) {
                                        $photo_url = base_url('dist/img/' . $m['cliente_id'] . '.jpg');
                                    }
                                }
                                ?>
                                <?php if ($photo_url): ?>
                                    <img src="<?= $photo_url ?>" class="img-circle elevation-1 mr-2" style="width: 32px; height: 32px; object-fit: cover;" alt="Dr. <?= esc($m['apellidos']) ?>">
                                <?php else: ?>
                                    <div class="d-inline-flex align-items-center justify-content-center bg-light img-circle mr-2" style="width: 32px; height: 32px; border: 1px solid #dee2e6;">
                                        <i class="fas fa-user-md text-muted" style="font-size: 14px;"></i>
                                    </div>
                                <?php endif; ?>
                                <strong><?= esc($m['apellidos']) ?></strong>, <?= esc($m['nombres']) ?>
                             </td>
                            <td><?= esc($m['dni'] ?? '-') ?></td>
                            <td><?= esc($m['cmp'] ?? '-') ?></td>
                            <td><span class="badge badge-info"><?= esc($m['especialidad'] ?? 'General') ?></span></td>
                            <td><?= esc($m['telefono'] ?? '-') ?></td>
                            <td>
                                <?php if ($m['estado'] == 1): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editarMedico(<?= $m['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($medicos)): ?>
                        <tr><td colspan="8" class="text-center text-muted">No hay médicos registrados</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Médico -->
<div class="modal fade" id="modalMedico" tabindex="-1">
    <div class="modal-dialog">
        <form id="formMedico" method="post" action="<?= site_url('CmMedicos/guardar') ?>">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitulo">Nuevo Médico</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="medico_id">
                    <div class="form-group">
                        <label>Nombres</label>
                        <input type="text" name="nombres" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Apellidos</label>
                        <input type="text" name="apellidos" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>DNI</label>
                                <input type="text" name="dni" class="form-control">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>CMP</label>
                                <input type="text" name="cmp" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Especialidad</label>
                        <input type="text" name="especialidad" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<script>
    function editarMedico(id) {
        $.post("<?= site_url('CmMedicos/get_medico') ?>", { id: id }, function(m) {
            $('#medico_id').val(m.id);
            $('[name="nombres"]').val(m.nombres);
            $('[name="apellidos"]').val(m.apellidos);
            $('[name="dni"]').val(m.dni);
            $('[name="cmp"]').val(m.cmp);
            $('[name="especialidad"]').val(m.especialidad);
            $('[name="telefono"]').val(m.telefono);
            $('[name="estado"]').val(m.estado);
            $('#modalTitulo').text('Editar Médico');
            $('#modalMedico').modal('show');
        });
    }
</script>
<?= $this->endSection(); ?>
