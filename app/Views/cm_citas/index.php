<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<style>
    .campaign-card { border-radius: 12px; transition: all 0.3s ease; border-top: 4px solid #007bff; }
    .campaign-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .doctor-avatar { width: 60px; height: 60px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #6c757d; }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-calendar-alt text-primary mr-2"></i> <?= esc($titulo) ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= site_url('cmMedicos') ?>" class="btn btn-outline-primary btn-sm mr-1"><i class="fas fa-user-md mr-1"></i> Médicos</a>
                <a href="<?= site_url('cmHorarios') ?>" class="btn btn-outline-primary btn-sm mr-1"><i class="fas fa-calendar-alt mr-1"></i> Horarios</a>
                <a href="<?= site_url('cmPacientes') ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-users mr-1"></i> Pacientes</a>
                <a href="<?= site_url('cmCitas/reporte') ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-chart-bar mr-1"></i> Reporte</a>
                <a href="<?= site_url('cmCitas/listado') ?>" class="btn btn-outline-secondary btn-sm ml-1"><i class="fas fa-list mr-1"></i> Ver Todas</a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <h5 class="mb-4 text-muted">Campañas Médicas y Horarios Activos</h5>
        <div class="row">
            <?php if(empty($campanias)): ?>
                <div class="col-12"><div class="alert alert-light text-center py-5"><h4>No hay Campañas programadas</h4></div></div>
            <?php else: ?>
                <?php foreach($campanias as $c): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="card campaign-card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge badge-primary px-3 py-2"><?= $c->fecha_especifica ? date('d/m/Y', strtotime($c->fecha_especifica)) : 'Recurrente' ?></span>
                                <?php $libres = $c->cupos_totales - $c->cupos_ocupados; ?>
                                <span class="badge <?= $libres > 0 ? 'badge-success' : 'badge-danger' ?>"><?= $libres ?> cupos</span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="doctor-avatar mr-3"><i class="fas fa-user-md text-primary"></i></div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold">Dr(a). <?= esc($c->apellidos) ?></h5>
                                    <p class="text-muted mb-0 small"><?= esc($c->especialidad ?: 'General') ?> â€¢ <?= esc($c->servicio_nombre ?: '') ?></p>
                                </div>
                            </div>
                            <div class="row text-center border-top border-bottom py-2 mb-3 bg-light">
                                <div class="col-4 border-right"><small class="text-muted">Inicio</small><strong><?= substr($c->hora_inicio, 0, 5) ?></strong></div>
                                <div class="col-4 border-right"><small class="text-muted">Fin</small><strong><?= substr($c->hora_fin, 0, 5) ?></strong></div>
                                <div class="col-4"><small class="text-muted">Inscritos</small>
                                    <a href="#" class="ver-inscritos" data-horario="<?= $c->id ?>"><strong><?= $c->pacientes_inscritos ?></strong></a>
                                </div>
                            </div>
                            <button class="btn btn-success btn-block" onclick="abrirReserva(<?= $c->id ?>, <?= $c->precio ?? 0 ?>, '<?= esc($c->apellidos ?: '') ?>')">
                                <i class="fas fa-ticket-alt mr-1"></i> Reservar Cita
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Reservar Cita -->
<div class="modal fade" id="modalReservar" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-ticket-alt mr-2"></i> Reservar Cupo</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formReserva">
                    <input type="hidden" name="horario_id" id="reserva_horario_id">
                    <div class="form-group">
                        <label>1. Seleccionar Paciente</label>
                        <select class="form-control" id="reserva_paciente_id" name="paciente_id" style="width: 100%;"></select>
                        <button class="btn btn-outline-success btn-sm btn-block mt-1" type="button" onclick="$('#modalNuevoPaciente').modal('show')">
                            <i class="fas fa-user-plus mr-1"></i> Registrar Nuevo Paciente
                        </button>
                    </div>
                    <div class="form-group border-top pt-3">
                        <label>2. Tipo de Comprobante</label>
                        <div class="d-flex mt-2">
                            <div class="custom-control custom-radio mr-4">
                                <input class="custom-control-input" type="radio" id="pagoBol" name="tipo_comprobante" value="03" checked>
                                <label for="pagoBol" class="custom-control-label">Boleta <small class="text-muted">(Serie <?= esc($series['03'] ?: 'sin configurar') ?>)</small></label>
                            </div>
                            <div class="custom-control custom-radio mr-4">
                                <input class="custom-control-input" type="radio" id="pagoFac" name="tipo_comprobante" value="01">
                                <label for="pagoFac" class="custom-control-label">Factura <small class="text-muted">(Serie <?= esc($series['01'] ?: 'sin configurar') ?>)</small></label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="pagoGuia" name="tipo_comprobante" value="09">
                                <label for="pagoGuia" class="custom-control-label">Guía <small class="text-muted">(Serie <?= esc($series['09'] ?: 'sin configurar') ?>)</small></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group border-top pt-3">
                        <label>3. Servicios Adicionales (opcional)</label>
                        <select class="form-control" id="servicios_extra" name="servicios_extra[]" multiple style="width: 100%;"></select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-outline-primary" id="btnSoloReservar">
                    <i class="fas fa-calendar-check mr-1"></i> Solo Reservar
                </button>
                <button type="button" class="btn btn-success" id="btnCobrarYReservar">
                    <i class="fas fa-cash-register mr-1"></i> Cobrar y Reservar
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<link rel="stylesheet" href="<?= base_url('plugins/select2/css/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>">
<script src="<?= base_url('plugins/select2/js/select2.full.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function notify(msg, type) {
    if (typeof Swal !== 'undefined') {
        let icon = type === 'error' ? 'error' : (type === 'warning' ? 'warning' : (type === 'info' ? 'info' : 'success'));
        Swal.fire({ icon: icon, title: msg, toast: false, timer: 3000, showConfirmButton: true });
    } else {
        alert(msg);
    }
}

function abrirReserva(horario_id, precio, medico) {
    $("#reserva_horario_id").val(horario_id);
    $("#btnCobrarYReservar").html('<i class="fas fa-cash-register mr-1"></i> Cobrar S/ ' + parseFloat(precio).toFixed(2) + ' y Reservar');
    $("#modalReservar .modal-title").html('<i class="fas fa-ticket-alt mr-2"></i> Reservar Cupo - Dr(a). ' + medico);
    $("#modalReservar").modal('show');
}

function cargarInscritos(horario_id) {
    $('#tabla-inscritos-body').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $.post("<?= site_url('CmCitas/get_pacientes_inscritos') ?>", { horario_id: horario_id }, function(data) {
        let html = '';
        if (data.length > 0) {
            data.forEach(function(p, i) {
                let eb = p.estado == 1 ? 'badge-success' : (p.estado == 2 ? 'badge-info' : (p.estado == 4 ? 'badge-warning' : 'badge-warning'));
                let accion = '';
                if (p.estado == 0) {
                    accion = '<button class="btn btn-sm btn-success cobrar-pendiente mr-1" data-cita="'+p.id+'"><i class="fas fa-cash-register"></i> Cobrar</button>' +
                             '<button class="btn btn-sm btn-danger anular-cita" data-cita="'+p.id+'"><i class="fas fa-times"></i></button>';
                } else if (p.estado == 1) {
                    accion = '<button class="btn btn-sm btn-info atender-cita mr-1" data-cita="'+p.id+'"><i class="fas fa-check-circle"></i> Atender</button>' +
                             '<button class="btn btn-sm btn-outline-info procedimiento-cita mr-1" data-cita="'+p.id+'"><i class="fas fa-syringe"></i> Proced.</button>';
                } else if (p.estado == 2 || p.estado == 4) {
                    accion = '<button class="btn btn-sm btn-outline-info procedimiento-cita" data-cita="'+p.id+'"><i class="fas fa-syringe"></i> Proced.</button>';
                }
                html += '<tr><td>'+(i+1)+'</td><td><strong>'+p.CLI_NOMBRE+'</strong></td><td>'+(p.DNI||'-')+'</td><td>'+(p.CLI_TELEF1||'-')+'</td>' +
                        '<td><span class="badge '+eb+'">'+p.estado_nombre+'</span></td>' +
                        '<td>'+(p.estado==0?'S/ '+parseFloat(p.saldo||0).toFixed(2):'-')+'</td><td>'+accion+'</td></tr>';
            });
        } else {
            html = '<tr><td colspan="7" class="text-center text-muted">No hay pacientes inscritos</td></tr>';
        }
        $('#tabla-inscritos-body').html(html);
    });
}

$(document).ready(function() {
    $('#modalReservar').on('hidden.bs.modal', function() {
        $('#reserva_paciente_id').val(null).trigger('change');
        $('#servicios_extra').val(null).trigger('change');
        $('#reserva_horario_id').val('');
    });

    // === MODAL NUEVO PACIENTE ===
    $('#np_dni').on('input', function() {
        let v = $(this).val().replace(/\D/g, ''); $(this).val(v);
        let ok = v.length === 8 || v.length === 11;
        $('#np_buscar').prop('disabled', !ok);
        $('#np_importar, #np_completar').addClass('d-none');
    });

    // 1. Buscar en BD local
    $('#np_buscar').click(function() {
        let dni = $('#np_dni').val().trim();
        if (!dni || ![8,11].includes(dni.length)) { notify('DNI inválido', 'warning'); return; }
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $('#np_importar, #np_completar').addClass('d-none');
        $.post("<?= site_url('CmPacientes/importar_desde_dni') ?>", { dni: dni }, function(res) {
            if (res.status === 'exists') {
                $('#np_nombre').val(res.nombre || '');
                $('#np_codigo').val(res.cliente_id || '');
                $('#np_direccion').val(res.direccion || '');
                $('#np_fecha_nac').val(res.fecha_nac || '');
                $('#np_telefono').val(res.telefono || '');
                if (res.datos_completos) {
                    notify('Encontrado en BD local.', 'success');
                } else {
                    $('#np_completar').removeClass('d-none');
                    notify('Faltan datos. Use "Completar desde Factiliza".', 'warning');
                }
            } else if (res.status === 'not_found') {
                $('#np_importar').removeClass('d-none');
                notify('No encontrado en BD local.', 'info');
            } else {
                notify(res.msg, 'info');
            }
            btn.prop('disabled', false).html('<i class="fas fa-search mr-1"></i> Buscar');
        });
    });

    // 2. Importar nuevo desde Factiliza
    $('#np_importar').click(function() {
        let dni = $('#np_dni').val().trim();
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmPacientes/importar_desde_dni') ?>", { dni: dni, forzar_api: '1' }, function(res) {
            if (res.status === 'success') {
                $('#np_nombre').val(res.nombre || '');
                $('#np_codigo').val(res.cliente_id || '');
                $('#np_direccion').val(res.direccion || '');
                $('#np_fecha_nac').val(res.fecha_nac || '');
                $('#np_importar').addClass('d-none');
                notify(res.msg, 'success');
            } else { notify(res.msg, 'info'); }
            btn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-1"></i> Importar desde Factiliza');
        });
    });

    // 3. Completar datos de paciente existente desde API
    $('#np_completar').click(function() {
        let dni = $('#np_dni').val().trim();
        let cliente_id = $('#np_codigo').val();
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmPacientes/completar_desde_api') ?>", { dni: dni, cliente_id: cliente_id }, function(res) {
            if (res.status === 'success') {
                $('#np_direccion').val(res.direccion || $('#np_direccion').val());
                $('#np_fecha_nac').val(res.fecha_nac || $('#np_fecha_nac').val());
                $('#np_completar').addClass('d-none');
                notify(res.msg, 'success');
            } else { notify(res.msg, 'info'); }
            btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Completar desde Factiliza');
        });
    });

    $('#np_importar').click(function() {
        let dni = $('#np_dni').val().trim();
        if (!dni || ![8,11].includes(dni.length)) { notify('Ingrese DNI válido (8 u 11 dígitos)', 'warning'); return; }
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmPacientes/importar_desde_dni') ?>", { dni: dni, forzar_api: '1' }, function(res) {
            if (res.status === 'exists') {
                $('#np_nombre').val(res.nombre || '');
                $('#np_codigo').val(res.cliente_id || '');
                $('#np_direccion').val(res.direccion || '');
                $('#np_fecha_nac').val(res.fecha_nac || '');
                $('#np_telefono').val(res.telefono || '');
                if (!res.datos_completos) {
                    notify('Datos incompletos. Use Completar desde Factiliza.', 'warning');
                    btn.data('forzar', '1');
                } else {
                    notify('Datos completos.', 'success');
                    btn.data('forzar', '0');
                }
            } else if (res.status === 'success') {
                $('#np_nombre').val(res.nombre || '');
                $('#np_codigo').val(res.cliente_id || '');
                $('#np_direccion').val(res.direccion || '');
                $('#np_fecha_nac').val(res.fecha_nac || '');
                notify(res.msg, 'success');
                btn.data('forzar', '0');
            } else { notify(res.msg, 'info'); }
            btn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-1"></i> Importar');
        });
    });

    $('#np_guardar').click(function() {
        let nombre = $('#np_nombre').val().trim();
        if (nombre.length < 3) { notify('El nombre es obligatorio', 'warning'); return; }
        let dni = $('#np_dni').val().trim().replace(/\D/g, '');
        let codigo = $('#np_codigo').val();
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmPacientes/guardar_desde_modal') ?>", {
            codigo: codigo, dni: dni, nombre: nombre,
            telefono: $('#np_telefono').val(), direccion: $('#np_direccion').val(),
            fecha_nac: $('#np_fecha_nac').val()
        }, function(res) {
            if (res.status === 'success') {
                let opt = new Option(res.nombre, res.paciente_id, true, true);
                $('#reserva_paciente_id').empty().append(opt).trigger('change');
                $('#modalNuevoPaciente').modal('hide');
                $('#np_nombre, #np_dni, #np_telefono, #np_direccion, #np_fecha_nac').val('');
                $('#np_codigo').val('');
            } else { notify(res.msg, 'info'); }
            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Guardar y Seleccionar');
        });
    });

    $('#servicios_extra').select2({
        theme: 'bootstrap4', placeholder: 'Agregar servicios adicionales...', dropdownParent: $('#modalReservar'),
        ajax: {
            url: "<?= site_url('CmCitas/get_servicios_disponibles') ?>", type: "post", dataType: 'json', delay: 250,
            processResults: function(r) {
                let results = [];
                (r||[]).forEach(function(item){ results.push({id:item.ART_KEY, text:item.ART_NOMBRE+' (S/ '+parseFloat(item.precio).toFixed(2)+')'}); });
                return {results:results};
            },
            cache: true
        }
    });

    $('#reserva_paciente_id').select2({
        theme: 'bootstrap4', placeholder: 'Escribe el nombre o DNI del paciente...', dropdownParent: $('#modalReservar'),
        ajax: {
            url: "<?= site_url('CmPacientes/get_pacientes') ?>", type: "post", dataType: 'json', delay: 250,
            data: function(p) { return { busqueda: p.term }; },
            processResults: function(r) {
                let results = [];
                (r||[]).forEach(function(item){
                    results.push({
                        id: item.id,
                        text: item.CLI_NOMBRE + ' (DNI: ' + (item.DNI||'S/N') + ')',
                        cliente_id: item.cliente_id
                    });
                });
                return {results:results};
            },
            cache: true
        }
    });

    $(document).on('click', '.ver-inscritos', function(e) {
        e.preventDefault();
        let horario_id = $(this).data('horario');
        $('#modalInscritos').modal('show');
        cargarInscritos(horario_id);
        if (!$('#inscritos_servicios_extra').hasClass('select2-hidden-accessible')) {
            $('#inscritos_servicios_extra').select2({
                theme: 'bootstrap4', placeholder: 'Servicios adicionales...', dropdownParent: $('#modalInscritos'),
                ajax: {
                    url: "<?= site_url('CmCitas/get_servicios_disponibles') ?>", type: "post", dataType: 'json', delay: 250,
                    processResults: function(r) {
                        let results = [];
                        (r||[]).forEach(function(item){ results.push({id:item.ART_KEY, text:item.ART_NOMBRE+' (S/ '+parseFloat(item.precio).toFixed(2)+')'}); });
                        return {results:results};
                    },
                    cache: true
                }
            });
        }
    });

    $(document).on('click', '.cobrar-pendiente', function() {
        let cita_id = $(this).data('cita');
        let extra = $("#inscritos_servicios_extra").val() || [];
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmCitas/cobrar_pendiente') ?>", {cita_id:cita_id, servicios_extra:extra}, function(res) {
            if (res.status === 'success') {
                if (res.ticket && res.ticket.nro) {
                    Swal.fire({
                        icon: 'success', title: 'Pago registrado',
                        html: 'Ticket: <strong>'+res.ticket.nro+'</strong><br>Monto: S/ '+parseFloat(res.ticket.monto).toFixed(2),
                        showCancelButton: true, confirmButtonText: '<i class="fas fa-print"></i> Imprimir Ticket', cancelButtonText: 'Cerrar'
                    }).then((r) => {
                        if (r.isConfirmed) window.open("<?= site_url('cmCitas/ticket/') ?>"+res.ticket.pago_id, '_blank');
                        cargarInscritos($('.ver-inscritos').last().data('horario'));
                    });
                } else {
                    notify(res.msg, 'success'); cargarInscritos($('.ver-inscritos').last().data('horario'));
                }
            }
            else { notify(res.msg, 'error'); btn.prop('disabled', false).html('<i class="fas fa-cash-register"></i> Cobrar'); }
        });
    });

    $(document).on('click', '.atender-cita', function() {
        let cita_id = $(this).data('cita');
        Swal.fire({ title: '¿Marcar como atendido?', icon: 'question', showCancelButton: true, confirmButtonText: 'Sí' }).then((r) => {
            if (!r.isConfirmed) return;
            $.post("<?= site_url('CmCitas/cambiar_estado') ?>", {cita_id:cita_id, estado:'2'}, function(res) {
                notify(res.msg, 'info'); cargarInscritos($('.ver-inscritos').last().data('horario'));
            });
        });
    });

    $(document).on('click', '.anular-cita', function() {
        let cita_id = $(this).data('cita');
        Swal.fire({ title: '¿Anular esta reserva sin pago?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, anular', cancelButtonText: 'Cancelar', confirmButtonColor: '#dc3545' }).then((r) => {
            if (!r.isConfirmed) return;
            $.post("<?= site_url('CmCitas/cambiar_estado') ?>", {cita_id:cita_id, estado:'3'}, function(res) {
                notify(res.msg, res.status === 'success' ? 'success' : 'error'); cargarInscritos($('.ver-inscritos').last().data('horario'));
            });
        });
    });

    $(document).on('click', '.procedimiento-cita', function() {
        let cita_id = $(this).data('cita');
        $('#proc_cita_id').val(cita_id); $('#proc_precio').val(''); $('#proc_obs').val('');
        if ($('#proc_servicio').hasClass('select2-hidden-accessible')) { $('#proc_servicio').val(null).trigger('change'); }
        else {
            $('#proc_servicio').select2({
                theme: 'bootstrap4', placeholder: 'Buscar servicio...', dropdownParent: $('#modalProcedimiento'),
                ajax: {
                    url: "<?= site_url('CmCitas/get_servicios_disponibles') ?>", type: "post", dataType: 'json', delay: 250,
                    processResults: function(r) {
                        let results = [];
                        (r||[]).forEach(function(item){ results.push({id:item.ART_KEY, text:item.ART_NOMBRE+' (S/ '+parseFloat(item.precio).toFixed(2)+')', precio:item.precio}); });
                        return {results:results};
                    },
                    cache: true
                }
            }).on('select2:select', function(e) { $('#proc_precio').val(e.params.data.precio||''); });
        }
        $('#modalProcedimiento').modal('show');
    });

    $('#btnCobrarProcedimiento').click(function() {
        let cita_id = $('#proc_cita_id').val(), art_key = $('#proc_servicio').val();
        let precio = $('#proc_precio').val(), obs = $('#proc_obs').val();
        if (!art_key || !precio || parseFloat(precio) <= 0) { notify('Selecciona un servicio y define el precio', 'warning'); return; }
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmCitas/cobrar_procedimiento') ?>", {cita_id:cita_id, art_key:art_key, precio:precio, observacion:obs}, function(res) {
            if (res.status === 'success') {
                $('#modalProcedimiento').modal('hide');
                if (res.ticket && res.ticket.nro) {
                    Swal.fire({
                        icon: 'success', title: 'Pago registrado',
                        html: 'Ticket: <strong>'+res.ticket.nro+'</strong><br>Monto: S/ '+parseFloat(res.ticket.monto).toFixed(2),
                        showCancelButton: true, confirmButtonText: '<i class="fas fa-print"></i> Imprimir Ticket', cancelButtonText: 'Cerrar'
                    }).then((r) => {
                        if (r.isConfirmed) window.open("<?= site_url('cmCitas/ticket/') ?>"+res.ticket.pago_id, '_blank');
                        cargarInscritos($('.ver-inscritos').last().data('horario'));
                    });
                } else {
                    notify(res.msg, 'success'); cargarInscritos($('.ver-inscritos').last().data('horario'));
                }
            }
            else { notify(res.msg, 'error'); }
            btn.prop('disabled', false).html('<i class="fas fa-cash-register mr-1"></i> Cobrar Procedimiento');
        });
    });

    $("#btnSoloReservar").click(function() {
        let pid = $("#reserva_paciente_id").val(), hid = $("#reserva_horario_id").val();
        let sel = $('#reserva_paciente_id').select2('data')[0];
        if(!pid) { notify('Selecciona un paciente.', 'warning'); return; }
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmCitas/reservar_cita') ?>", {
            paciente_id: pid, horario_id: hid, pagar_ahora:'0', cliente_id: sel.cliente_id || ''
        }, function(res) {
            if(res.status==='success') { notify(res.msg, 'success'); $("#modalReservar").modal('hide'); location.reload(); }
            else { notify(res.msg, 'error'); }
            btn.prop('disabled', false).html('<i class="fas fa-calendar-check mr-1"></i> Solo Reservar');
        });
    });

    $("#btnCobrarYReservar").click(function() {
        let pid = $("#reserva_paciente_id").val(), hid = $("#reserva_horario_id").val();
        let tc = $("input[name='tipo_comprobante']:checked").val(), extra = $("#servicios_extra").val() || [];
        let sel = $('#reserva_paciente_id').select2('data')[0];
        if(!pid) { notify('Selecciona un paciente.', 'warning'); return; }
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmCitas/reservar_cita') ?>", {
            paciente_id: pid, horario_id: hid, pagar_ahora:'1', tipo_comprobante:tc, servicios_extra:extra,
            cliente_id: sel.cliente_id || ''
        }, function(res) {
            if(res.status==='success') {
                $("#modalReservar").modal('hide');
                if (res.ticket && res.ticket.nro) {
                    Swal.fire({
                        icon: 'success', title: 'Pago registrado',
                        html: 'Ticket: <strong>'+res.ticket.nro+'</strong><br>Monto: S/ '+parseFloat(res.ticket.monto).toFixed(2),
                        showCancelButton: true, confirmButtonText: '<i class="fas fa-print"></i> Imprimir Ticket', cancelButtonText: 'Cerrar'
                    }).then((r) => {
                        if (r.isConfirmed) window.open("<?= site_url('cmCitas/ticket/') ?>"+res.ticket.pago_id, '_blank');
                        location.reload();
                    });
                } else {
                    notify(res.msg, 'success'); location.reload();
                }
            }
            else { notify(res.msg, 'error'); }
            btn.prop('disabled', false).html('<i class="fas fa-cash-register mr-1"></i> Cobrar y Reservar');
        });
    });
});
</script>

<!-- Modal Nuevo Paciente -->
<div class="modal fade" id="modalNuevoPaciente" tabindex="-1" role="dialog">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i> Registrar Nuevo Paciente</h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="np_estado" value="nuevo">
            <input type="hidden" id="np_codigo">
            <div class="form-group row">
                <div class="col-8"><div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-id-badge"></i></span></div>
                    <input type="text" class="form-control" id="np_dni" placeholder="DNI o RUC (opcional)" maxlength="11">
                </div></div>
                <div class="col-4">
                    <button type="button" id="np_buscar" class="btn btn-primary btn-block"><i class="fas fa-search mr-1"></i> Buscar</button>
                </div>
            </div>
            <div class="text-center mb-2">
                <button type="button" id="np_importar" class="btn btn-info btn-sm d-none"><i class="fas fa-cloud-download-alt mr-1"></i> Importar desde Factiliza</button>
                <button type="button" id="np_completar" class="btn btn-warning btn-sm d-none"><i class="fas fa-sync-alt mr-1"></i> Completar datos desde Factiliza</button>
            </div>
            <div class="form-group"><div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="far fa-user"></i></span></div>
                <input type="text" class="form-control" id="np_nombre" placeholder="Nombre completo *" required>
            </div></div>
            <div class="row">
                <div class="col-6"><div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="far fa-calendar-alt"></i></span></div>
                    <input type="date" class="form-control" id="np_fecha_nac">
                </div></div>
                <div class="col-6"><div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-phone"></i></span></div>
                    <input type="text" class="form-control" id="np_telefono" placeholder="Teléfono">
                </div></div>
            </div>
            <div class="form-group mt-2"><div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-home"></i></span></div>
                <input type="text" class="form-control" id="np_direccion" placeholder="Dirección">
            </div></div>
            <small class="text-muted">* El DNI es opcional. Puede registrarse sin documento.</small>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success" id="np_guardar"><i class="fas fa-save mr-1"></i> Guardar y Seleccionar</button>
        </div>
    </div></div>
</div>

<!-- Modal Ver Inscritos -->
<div class="modal fade" id="modalInscritos" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header bg-info text-white">
            <h5 class="modal-title"><i class="fas fa-users mr-2"></i> Pacientes Inscritos</h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <select class="form-control mb-3" id="inscritos_servicios_extra" multiple style="width: 100%;" data-placeholder="Agregar servicios adicionales..."></select>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-light"><tr><th>#</th><th>Paciente</th><th>DNI</th><th>Teléfono</th><th>Estado</th><th>Saldo</th><th>Acción</th></tr></thead>
                    <tbody id="tabla-inscritos-body"><tr><td colspan="7" class="text-center text-muted">Cargando...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div></div>
</div>

<!-- Modal Procedimiento -->
<div class="modal fade" id="modalProcedimiento" tabindex="-1" role="dialog">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-info text-white">
            <h5 class="modal-title"><i class="fas fa-syringe mr-2"></i> Cobrar Procedimiento Adicional</h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="proc_cita_id">
            <div class="form-group"><label>Servicio / Procedimiento</label>
                <select class="form-control" id="proc_servicio" style="width: 100%;"></select></div>
            <div class="form-group"><label>Precio (S/)</label>
                <input type="number" class="form-control" id="proc_precio" step="0.01" min="0.01" required></div>
            <div class="form-group"><label>Observación</label>
                <input type="text" class="form-control" id="proc_obs" placeholder="Ej: Lunar grande"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-info" id="btnCobrarProcedimiento">
                <i class="fas fa-cash-register mr-1"></i> Cobrar Procedimiento
            </button>
        </div>
    </div></div>
</div>

<?= $this->endSection(); ?>


