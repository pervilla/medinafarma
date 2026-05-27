<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-calendar-alt text-primary mr-2"></i> <?= esc($titulo) ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <button class="btn btn-primary" onclick="$('#modalHorario').modal('show'); $('#formHorario')[0].reset(); $('#horario_id').val('');">
                    <i class="fas fa-plus mr-1"></i> Nuevo Horario
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
                            <th>Médico</th>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th>Cupos</th>
                            <th>Servicio</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($horarios as $h): ?>
                        <tr>
                            <td><?= $h->id ?></td>
                            <td><strong><?= esc($h->medico) ?></strong></td>
                            <td><?= $h->fecha_especifica ? date('d/m/Y', strtotime($h->fecha_especifica)) : 'Recurrente' ?></td>
                            <td><?= substr($h->hora_inicio, 0, 5) ?> - <?= substr($h->hora_fin, 0, 5) ?></td>
                            <td><?= $h->cupos_ocupados ?>/<?= $h->cupos_totales ?></td>
                            <td><?= esc($h->servicio ?? '-') ?></td>
                            <td>S/ <?= number_format($h->precio ?? 0, 2) ?></td>
                            <td>
                                <?php if ($h->estado == 1): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editarHorario(<?= $h->id ?>)"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($horarios)): ?>
                        <tr><td colspan="9" class="text-center text-muted">No hay horarios programados</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Horario -->
<div class="modal fade" id="modalHorario" tabindex="-1">
    <div class="modal-dialog">
        <form id="formHorario" method="post" action="<?= site_url('CmHorarios/guardar') ?>">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Nuevo Horario</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="horario_id">
                    
                    <div class="form-group">
                        <label>Médico</label>
                        <select name="medico_id" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($medicos as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= esc($m['apellidos'] . ', ' . $m['nombres']) ?> - <?= esc($m['especialidad'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Fecha específica</label>
                        <input type="date" name="fecha_especifica" class="form-control">
                        <small class="text-muted">Dejar vacío para horario recurrente</small>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Hora inicio</label>
                                <input type="time" name="hora_inicio" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Hora fin</label>
                                <input type="time" name="hora_fin" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Cupos totales</label>
                                <input type="number" name="cupos_totales" class="form-control" value="10" min="1">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Min. por consulta</label>
                                <input type="number" name="tiempo_por_atencion_minutos" class="form-control" value="15" min="5">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Servicio (artículo de consulta)</label>
                        <select name="cod_art_servicio" class="form-control">
                            <option value="">Seleccione...</option>
                            <?php foreach ($servicios as $s): ?>
                                <option value="<?= $s->ART_KEY ?>"><?= esc($s->ART_NOMBRE) ?> - S/ <?= number_format($s->precio, 2) ?></option>
                            <?php endforeach; ?>
                        </select>
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

<script>
function editarHorario(id) {
    $.post("<?= site_url('CmHorarios/get_horario') ?>", { id: id }, function(h) {
        $('#horario_id').val(h.id);
        $('[name="medico_id"]').val(h.medico_id);
        $('[name="fecha_especifica"]').val(h.fecha_especifica ? h.fecha_especifica.substring(0,10) : '');
        $('[name="hora_inicio"]').val(h.hora_inicio ? h.hora_inicio.substring(0,5) : '');
        $('[name="hora_fin"]').val(h.hora_fin ? h.hora_fin.substring(0,5) : '');
        $('[name="cupos_totales"]').val(h.cupos_totales);
        $('[name="tiempo_por_atencion_minutos"]').val(h.tiempo_por_atencion_minutos);
        $('[name="cod_art_servicio"]').val(h.cod_art_servicio);
        $('[name="estado"]').val(h.estado);
        $('.modal-title').text('Editar Horario');
        $('#modalHorario').modal('show');
    });
}
</script>

<?= $this->endSection(); ?>
