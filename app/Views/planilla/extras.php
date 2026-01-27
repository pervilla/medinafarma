<?= $this->extend('templates/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Horas Extras y Feriados</h1>
            </div>
            <div class="col-sm-6">
                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#modal-add">
                    <i class="fa fa-plus"></i> Nuevo Registro
                </button>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Listado de Pendientes/Procesados</h3>
                <div class="card-tools">
                   <select id="filtro-mes" class="form-control form-control-sm d-inline-block" style="width: 120px;">
                       <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 10)) ?></option>
                       <?php endfor; ?>
                   </select>
                   <select id="filtro-anio" class="form-control form-control-sm d-inline-block" style="width: 100px;">
                       <?php for($y=date('Y'); $y>=2024; $y--): ?>
                        <option value="<?= $y ?>"><?= $y ?></option>
                       <?php endfor; ?>
                   </select>
                   <button class="btn btn-sm btn-default" id="btn-filtrar"><i class="fa fa-filter"></i> Filtrar</button>
                </div>
            </div>
            <div class="card-body">
                <table id="tabla-extras" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Empleado</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Observación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loaded by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal Add/Edit -->
<div class="modal fade" id="modal-add">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registrar Hora Extra / Feriado</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-extra">
                    <input type="hidden" id="extra-id">
                    <div class="form-group">
                        <label>Empleado</label>
                        <select class="form-control" id="extra-empleado" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach($empleados as $emp): ?>
                                <option value="<?= $emp->VEM_CODVEN ?>"><?= $emp->VEM_NOMBRE ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipo</label>
                        <select class="form-control" id="extra-tipo">
                            <option value="HORA_EXTRA_25">Hora Extra 25%</option>
                            <option value="HORA_EXTRA_35">Hora Extra 35%</option>
                            <option value="HORA_EXTRA_100">Hora Extra Simple (100%)</option>
                            <option value="FERIADO">Feriado Laborado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cantidad (Horas o Días)</label>
                        <input type="number" class="form-control" id="extra-cantidad" step="0.5" required>
                        <small class="text-muted">Para H.E ingrese horas, para Feriados ingrese días (ej. 1)</small>
                    </div>
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" class="form-control" id="extra-fecha" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Observación</label>
                        <textarea class="form-control" id="extra-obs" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<script>
$(document).ready(function(){
    loadExtras();

    $('#btn-filtrar').click(function(){ loadExtras(); });

    $('#btn-save').click(function(){
        var data = {
            id: $('#extra-id').val(),
            vem_codven: $('#extra-empleado').val(),
            tipo: $('#extra-tipo').val(),
            cantidad: $('#extra-cantidad').val(),
            fecha: $('#extra-fecha').val(),
            observacion: $('#extra-obs').val()
        };
        
        if(!data.vem_codven || !data.cantidad || !data.fecha){
            alert('Complete los campos obligatorios'); return;
        }

        $.post("<?= site_url('planilla/save_extra') ?>", data, function(res){
            if(res.success){
                $('#modal-add').modal('hide');
                resetForm();
                loadExtras();
            } else {
                alert('Error: ' + res.message);
            }
        });
    });

    $(document).on('click', '.btn-delete', function(){
       if(!confirm('¿Eliminar registro?')) return;
       var id = $(this).data('id');
       $.post("<?= site_url('planilla/delete_extra') ?>", {id: id}, function(res){
           if(res.success) loadExtras();
       });
    });
});

function loadExtras(){
    var mes = $('#filtro-mes').val();
    var anio = $('#filtro-anio').val();
    
    $.get("<?= site_url('planilla/get_extras') ?>", {mes: mes, anio: anio}, function(res){
        var html = '';
        res.data.forEach(function(d){
            var statusBadge = d.estado === 'PENDIENTE' ? '<span class="badge badge-warning">Pendiente</span>' : '<span class="badge badge-success">Procesado</span>';
            var deleteBtn = d.estado === 'PENDIENTE' ? `<button class="btn btn-xs btn-danger btn-delete" data-id="${d.id}"><i class="fa fa-trash"></i></button>` : '';
            var cantSuffix = d.tipo === 'FERIADO' ? ' Días' : ' Hrs';

            html += `<tr>
                <td>${d.fecha}</td>
                <td>${d.nombre_empleado || d.vem_codven}</td>
                <td>${d.tipo}</td>
                <td>${d.cantidad}${cantSuffix}</td>
                <td>${d.observacion || ''}</td>
                <td>${statusBadge}</td>
                <td>${deleteBtn}</td>
            </tr>`;
        });
        $('#tabla-extras tbody').html(html);
    });
}

function resetForm(){
    $('#extra-id').val('');
    $('#extra-empleado').val('');
    $('#extra-cantidad').val('');
    $('#extra-obs').val('');
}
</script>
<?= $this->endSection() ?>
