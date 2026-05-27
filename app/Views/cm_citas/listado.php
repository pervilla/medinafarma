<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-list text-primary mr-2"></i> <?= esc($titulo) ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= site_url('cmCitas') ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-calendar-alt mr-1"></i> Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group mb-0"><label class="small">Campaña</label>
                            <select id="filtro_horario" class="form-control form-control-sm">
                                <option value="">Todas</option>
                                <?php foreach($horarios as $h): ?>
                                    <option value="<?= $h->id ?>" <?= ($filtros['horario_id'] == $h->id) ? 'selected' : '' ?>><?= $h->fecha_especifica ? date('d/m/Y', strtotime($h->fecha_especifica)) : '' ?> - <?= esc(substr($h->medico, 0, 30)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0"><label class="small">Estado</label>
                            <select id="filtro_estado" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="0" <?= $filtros['estado'] === '0' ? 'selected' : '' ?>>Inscrito</option>
                                <option value="1" <?= $filtros['estado'] === '1' ? 'selected' : '' ?>>Confirmado</option>
                                <option value="2" <?= $filtros['estado'] === '2' ? 'selected' : '' ?>>Atendido</option>
                                <option value="3" <?= $filtros['estado'] === '3' ? 'selected' : '' ?>>Anulado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0"><label class="small">Desde</label>
                            <input type="date" id="filtro_desde" class="form-control form-control-sm" value="<?= $filtros['fecha_desde'] ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0"><label class="small">Hasta</label>
                            <input type="date" id="filtro_hasta" class="form-control form-control-sm" value="<?= $filtros['fecha_hasta'] ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm btn-block" id="btnFiltrar"><i class="fas fa-search mr-1"></i> Buscar</button>
                    </div>
                    <div class="col-md-1">
                        <a href="<?= site_url('cmCitas/listado') ?>" class="btn btn-outline-secondary btn-sm btn-block">Limpiar</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Listado de Citas</h3>
                <div class="card-tools">
                    <a href="<?= site_url('cmCitas') ?>" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Nueva Cita</a>
                </div>
            </div>
            <div class="card-body p-0">
                <table id="tabla_citas" class="table table-bordered table-hover table-sm w-100">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Paciente</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Edad</th>
                            <th>Campaña/Médico</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Paciente -->
<div class="modal fade" id="modalVerPaciente" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header bg-info text-white">
            <h5 class="modal-title"><i class="fas fa-user-injured mr-2"></i> Detalle del Paciente</h5>
            <div class="ml-auto mr-2">
                <button class="btn btn-sm btn-light" id="btnEditarPaciente" type="button"><i class="fas fa-edit"></i> Editar</button>
            </div>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="vp_cita_id">
            <input type="hidden" id="vp_paciente_id">
            <div class="row mb-2">
                <div class="col-6"><strong>Nombre:</strong> <span id="vp_nombre_txt"></span><input class="form-control form-control-sm d-none" id="vp_nombre_inp"></div>
                <div class="col-3"><strong>DNI:</strong> <span id="vp_dni_txt"></span><input class="form-control form-control-sm d-none" id="vp_dni_inp" maxlength="11"></div>
                <div class="col-3"><strong>Edad:</strong> <span id="vp_edad"></span></div>
            </div>
            <div class="row mb-2">
                <div class="col-3"><strong>Teléfono:</strong> <span id="vp_tel_txt"></span><input class="form-control form-control-sm d-none" id="vp_tel_inp"></div>
                <div class="col-4"><strong>Fec. Nac:</strong> <span id="vp_fnac_txt"></span><input type="date" class="form-control form-control-sm d-none" id="vp_fnac_inp"></div>
                <div class="col-5 text-right"><strong>Total:</strong> <span id="vp_total"></span></div>
            </div>
            <div class="row mb-2"><div class="col-6"><strong>Campaña:</strong> <span id="vp_campana"></span></div><div class="col-6"><strong>Médico:</strong> <span id="vp_medico"></span></div></div>
            <div id="vp_servicios" class="mb-3"></div>
            <button class="btn btn-success btn-sm d-none" id="btnGuardarPaciente" type="button"><i class="fas fa-save"></i> Guardar Cambios</button>
            <hr><h6>Historial de Citas</h6>
            <table class="table table-sm table-bordered"><thead><tr><th>#</th><th>Fecha</th><th>Médico</th><th>Total</th><th>Estado</th></tr></thead><tbody id="vp_historial"></tbody></table>
        </div>
    </div></div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<link rel="stylesheet" href="<?= base_url('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') ?>">
<script src="<?= base_url('plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= base_url('plugins/datatables-buttons/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?= base_url('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('plugins/jszip/jszip.min.js') ?>"></script>
<script src="<?= base_url('plugins/pdfmake/pdfmake.min.js') ?>"></script>
<script src="<?= base_url('plugins/pdfmake/vfs_fonts.js') ?>"></script>
<script src="<?= base_url('plugins/datatables-buttons/js/buttons.html5.min.js') ?>"></script>
<script src="<?= base_url('plugins/datatables-buttons/js/buttons.print.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    var tabla = $('#tabla_citas').DataTable({
        ajax: {
            url: "<?= site_url('CmCitas/listado_data') ?>",
            type: "POST",
            dataSrc: '',
            data: function(d) {
                d.horario_id = $('#filtro_horario').val();
                d.estado = $('#filtro_estado').val();
                d.fecha_desde = $('#filtro_desde').val();
                d.fecha_hasta = $('#filtro_hasta').val();
            }
        },
        columns: [
            { data: 'id' },
            { data: 'CLI_NOMBRE', render: function(d, t, r) {
                let badge = '';
                if (r.estado == 0) badge = ' <span class="badge badge-warning float-right">PENDIENTE</span>';
                return '<a href="#" class="ver-paciente" data-cita="'+r.id+'"><i class="fas fa-user-injured"></i> ' + d + '</a>' + badge;
            }},
            { data: 'DNI', render: d => d || '-' },
            { data: 'CLI_TELEF1', render: d => d || '-' },
            { data: null, render: function(d, t, r) { return r.edad || '-'; }},
            { data: null, render: function(d, t, r) { return (r.fecha_especifica ? r.fecha_especifica.substring(0,10) : '') + '<br><small class="text-muted">' + (r.medico || '') + '</small>'; }},
            { data: 'estado_nombre', render: function(d, t, r) {
                var badge = r.estado == 1 ? 'success' : (r.estado == 2 ? 'info' : (r.estado == 3 ? 'secondary' : 'warning'));
                return '<span class="badge badge-'+badge+'">'+d+'</span>';
            }},
            { data: 'total', render: d => 'S/ ' + parseFloat(d || 0).toFixed(2) },
            { data: null, render: function(d, t, r) {
                var btn = '';
                if (r.estado == 0) {
                    btn += '<button class="btn btn-success btn-xs cobrar-ajax mr-1" data-cita="'+r.id+'" title="Cobrar"><i class="fas fa-cash-register"></i></button>';
                }
                if (r.estado == 1) {
                    btn += '<button class="btn btn-info btn-xs atender-ajax mr-1" data-cita="'+r.id+'" title="Atender"><i class="fas fa-check"></i></button>';
                }
                if (r.estado != 3) {
                    btn += '<button class="btn btn-danger btn-xs anular-ajax" data-cita="'+r.id+'" title="Anular"><i class="fas fa-times"></i></button>';
                }
                return btn;
            }},
        ],
        order: [[0, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json' },
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print']
    });

    $('#btnFiltrar').click(function() { tabla.ajax.reload(); });

    // Cobrar ajax
    $(document).on('click', '.cobrar-ajax', function() {
        let cita_id = $(this).data('cita');
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmCitas/cobrar_pendiente') ?>", { cita_id: cita_id }, function(res) {
            Swal.fire({ icon: res.status === 'success' ? 'success' : 'error', title: res.msg });;
            tabla.ajax.reload();
        });
    });

    // Atender ajax
    $(document).on('click', '.atender-ajax', function() {
        let cita_id = $(this).data('cita');
        Swal.fire({ title: '¿Marcar como atendido?', icon: 'question', showCancelButton: true, confirmButtonText: 'Sí' }).then((r) => {
            if (!r.isConfirmed) return;
            $.post("<?= site_url('CmCitas/cambiar_estado') ?>", { cita_id: cita_id, estado: '2' }, function(res) {
                Swal.fire({ icon: 'info', title: res.msg }); tabla.ajax.reload();
            });
        });
    });

    // Anular ajax
    $(document).on('click', '.anular-ajax', function() {
        let cita_id = $(this).data('cita');
        Swal.fire({ title: '¿Anular cita?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, anular', confirmButtonColor: '#dc3545' }).then((r) => {
            if (!r.isConfirmed) return;
            $.post("<?= site_url('CmCitas/cambiar_estado') ?>", { cita_id: cita_id, estado: '3' }, function(res) {
                Swal.fire({ icon: 'info', title: res.msg }); tabla.ajax.reload();
            });
        });
    });

    // Ver paciente
    $(document).on('click', '.ver-paciente', function(e) {
        e.preventDefault();
        let cita_id = $(this).data('cita');
        $.post("<?= site_url('CmCitas/ver_paciente') ?>", { cita_id: cita_id }, function(res) {
            if (res.status !== 'success') return;
            let c = res.cita;
            $('#vp_cita_id').val(c.id);
            $('#vp_paciente_id').val(c.paciente_id);
            $('#vp_nombre_txt').text(c.CLI_NOMBRE);
            $('#vp_nombre_inp').val(c.CLI_NOMBRE);
            $('#vp_dni_txt').text(c.DNI || '-');
            $('#vp_dni_inp').val(c.DNI || '');
            $('#vp_tel_txt').text(c.CLI_TELEF1 || '-');
            $('#vp_tel_inp').val(c.CLI_TELEF1 ? c.CLI_TELEF1.trim() : '');
            $('#vp_fnac_txt').text(c.CLI_FECHA_NAC ? c.CLI_FECHA_NAC.substring(0,10) : '-');
            $('#vp_fnac_inp').val(c.CLI_FECHA_NAC ? c.CLI_FECHA_NAC.substring(0,10) : '');
            $('#vp_edad').text(c.edad ? c.edad + ' años' : '-');
            $('#vp_total').text('S/ ' + parseFloat(c.total || 0).toFixed(2));
            $('#vp_campana').text(c.fecha_especifica ? c.fecha_especifica.substring(0,10) : '-');
            $('#vp_medico').text(c.medico || '-');
            $('#vp_servicios').html(c.servicios_extra ? '<strong>Servicios extra:</strong> '+c.servicios_extra : '');
            let hhtml = '';
            if (res.historial.length > 0) {
                res.historial.forEach(function(h) {
                    hhtml += '<tr><td>'+h.id+'</td><td>'+(h.fecha_especifica?h.fecha_especifica.substring(0,10):'-')+'</td><td>'+h.medico+'</td><td>S/'+parseFloat(h.total||0).toFixed(2)+'</td><td>'+h.estado_nombre+'</td></tr>';
                });
            } else {
                hhtml = '<tr><td colspan="5" class="text-muted">Sin historial previo</td></tr>';
            }
            $('#vp_historial').html(hhtml);
            // Reset edit mode
            $('#btnEditarPaciente').show().text('Editar');
            $('.d-none[id$="_inp"]').addClass('d-none');
            $('[id$="_txt"]').removeClass('d-none');
            $('#btnGuardarPaciente').addClass('d-none');
            $('#modalVerPaciente').modal('show');
        });
    });

    // Toggle edición
    $('#btnEditarPaciente').click(function() {
        if ($(this).text().trim() === 'Editar') {
            $(this).text('Cancelar').removeClass('btn-light').addClass('btn-warning');
            $('[id$="_txt"]').addClass('d-none');
            $('.d-none[id$="_inp"]').removeClass('d-none');
            $('#btnGuardarPaciente').removeClass('d-none');
        } else {
            $(this).text('Editar').removeClass('btn-warning').addClass('btn-light');
            $('[id$="_inp"]').addClass('d-none');
            $('[id$="_txt"]').removeClass('d-none');
            $('#btnGuardarPaciente').addClass('d-none');
        }
    });

    // Guardar cambios paciente
    $('#btnGuardarPaciente').click(function() {
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmCitas/actualizar_paciente') ?>", {
            cita_id: $('#vp_cita_id').val(),
            paciente_id: $('#vp_paciente_id').val(),
            nombre: $('#vp_nombre_inp').val(),
            dni: $('#vp_dni_inp').val(),
            telefono: $('#vp_tel_inp').val(),
            fecha_nac: $('#vp_fnac_inp').val()
        }, function(res) {
            Swal.fire({ icon: res.status === 'success' ? 'success' : 'error', title: res.msg });;
            if (res.status === 'success') {
                $('#btnEditarPaciente').trigger('click'); // volver a modo lectura
                tabla.ajax.reload();
            }
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');
        });
    });
});
</script>
<?= $this->endSection(); ?>
