<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-list text-primary mr-2"></i> <?= esc($titulo) ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <label class="small text-muted mb-0 mr-1">Local:</label>
                <select id="select_local" class="form-control form-control-sm d-inline-block mr-2" style="width: 140px;">
                    <option value="1" <?= intval($local_pago)==1 ? 'selected' : '' ?>>Centro</option>
                    <option value="2" <?= intval($local_pago)==2 ? 'selected' : '' ?>>Juanjuicillo</option>
                    <option value="3" <?= intval($local_pago)==3 ? 'selected' : '' ?>>Peñameza</option>
                    <option value="4" <?= intval($local_pago)==4 ? 'selected' : '' ?>>Consultorio</option>
                </select>
                <a href="<?= site_url('cmCitas') ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-calendar-alt mr-1"></i> Dashboard</a>
                <a href="<?= site_url('cmCitas/balance') ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-chart-line mr-1"></i> Balance</a>
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
                        <div class="form-group mb-0"><label class="small">Inscripción Desde</label>
                            <input type="date" id="filtro_desde" class="form-control form-control-sm" value="<?= $filtros['fecha_desde'] ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0"><label class="small">Inscripción Hasta</label>
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
                    <button type="button" class="btn btn-success btn-sm" id="btnNuevaCita"><i class="fas fa-plus mr-1"></i> Nueva Cita</button>
                </div>
            </div>
            <div class="card-body p-0">
                <table id="tabla_citas" class="table table-bordered table-hover table-sm w-100">
                    <thead class="thead-light">
                        <tr>
                            <th>N°</th>
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
            
            <div class="card card-outline card-info mb-3">
                <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-id-card mr-1"></i> Datos Personales</h6></div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-5">
                            <label class="small mb-0">Nombre</label>
                            <span id="vp_nombre_txt" class="d-block font-weight-bold"></span>
                            <input class="form-control form-control-sm d-none" id="vp_nombre_inp">
                        </div>
                        <div class="col-2">
                            <label class="small mb-0">DNI</label>
                            <span id="vp_dni_txt" class="d-block"></span>
                            <input class="form-control form-control-sm d-none" id="vp_dni_inp" maxlength="11">
                        </div>
                        <div class="col-2">
                            <label class="small mb-0">Edad</label>
                            <span id="vp_edad" class="d-block"></span>
                        </div>
                        <div class="col-3">
                            <label class="small mb-0">Total</label>
                            <span id="vp_total" class="d-block font-weight-bold text-success"></span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-3">
                            <label class="small mb-0">Teléfono</label>
                            <span id="vp_tel_txt" class="d-block"></span>
                            <input class="form-control form-control-sm d-none" id="vp_tel_inp">
                        </div>
                        <div class="col-4">
                            <label class="small mb-0">Fec. Nacimiento</label>
                            <span id="vp_fnac_txt" class="d-block"></span>
                            <input type="date" class="form-control form-control-sm d-none" id="vp_fnac_inp">
                        </div>
                        <div class="col-5">
                            <label class="small mb-0">Campaña / Médico</label>
                            <span id="vp_campana" class="d-block small"></span>
                            <span id="vp_medico" class="d-block small text-muted"></span>
                        </div>
                    </div>
                    <div id="vp_servicios" class="mt-2 small"></div>
                </div>
            </div>

            <div class="card card-outline card-success mb-3">
                <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-notes-medical mr-1"></i> Ficha Clínica</h6></div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-3">
                            <label class="small mb-0">Tipo de Sangre</label>
                            <span id="vp_sangre_txt" class="d-block"></span>
                            <select class="form-control form-control-sm d-none" id="vp_sangre_inp">
                                <option value="">Desconocido</option>
                                <option value="O+">O+</option><option value="O-">O-</option>
                                <option value="A+">A+</option><option value="A-">A-</option>
                                <option value="B+">B+</option><option value="B-">B-</option>
                                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="small mb-0">Contacto Emergencia</label>
                            <span id="vp_contacto_txt" class="d-block"></span>
                            <input class="form-control form-control-sm d-none" id="vp_contacto_inp">
                        </div>
                        <div class="col-3">
                            <label class="small mb-0">Tel. Emergencia</label>
                            <span id="vp_tel_emergencia_txt" class="d-block"></span>
                            <input class="form-control form-control-sm d-none" id="vp_tel_emergencia_inp">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <label class="small mb-0">Alergias Conocidas</label>
                            <span id="vp_alergias_txt" class="d-block"></span>
                            <input class="form-control form-control-sm d-none" id="vp_alergias_inp" placeholder="Ej. Penicilina, Ibuprofeno...">
                        </div>
                        <div class="col-6">
                            <label class="small mb-0">Enfermedades Crónicas</label>
                            <span id="vp_enfermedades_txt" class="d-block"></span>
                            <input class="form-control form-control-sm d-none" id="vp_enfermedades_inp" placeholder="Ej. Hipertensión, Diabetes...">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <label class="small mb-0">Observaciones Médicas</label>
                            <span id="vp_observaciones_txt" class="d-block"></span>
                            <textarea class="form-control form-control-sm d-none" id="vp_observaciones_inp" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn btn-success d-none" id="btnGuardarPaciente" type="button"><i class="fas fa-save"></i> Guardar Cambios</button>
            
            <hr>
            <h6><i class="fas fa-history mr-1"></i> Historial de Citas</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered"><thead class="thead-light"><tr><th>#</th><th>Fecha</th><th>Médico</th><th>Total</th><th>Estado</th></tr></thead><tbody id="vp_historial"></tbody></table>
            </div>
        </div>
    </div></div>
</div>

<!-- Modal Agregar Servicio / Procedimiento -->
<div class="modal fade" id="modalServicio" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="fas fa-syringe mr-2"></i> Agregar Servicio y Cobrar</h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="sv_cita_id">
            <div class="form-group"><label>Servicio / Procedimiento</label>
                <select class="form-control" id="sv_servicio" style="width:100%;"></select></div>
            <div class="form-group"><label>Precio (S/)</label>
                <input type="number" class="form-control" id="sv_precio" step="0.01" min="0.01" required></div>
            <div class="form-group"><label>Observación</label>
                <input type="text" class="form-control" id="sv_obs" placeholder="Ej: Vacuna antigripal"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnGuardarServicio"><i class="fas fa-cash-register mr-1"></i> Cobrar Servicio</button>
        </div>
    </div></div>
</div>

<!-- Modal Emitir Comprobante -->
<div class="modal fade" id="modalEmitirComprobante" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="fas fa-file-invoice-dollar mr-2"></i> Emitir Comprobante</h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="ec_cita_id">
            <div class="form-group">
                <label><strong>Servicios a facturar:</strong></label>
                <div id="ec_servicios" class="border rounded p-2" style="max-height:180px;overflow-y:auto;"></div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group"><label>Tipo de Comprobante</label>
                        <select id="ec_tipo" class="form-control">
                            <option value="B">Boleta</option>
                            <option value="F">Factura</option>
                            <option value="G">Guía</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
                    <div id="ec_factura_box" class="d-none">
                        <div class="row">
                            <div class="col-md-6">
                                <label>RUC (11 dígitos)</label>
                                <div class="input-group">
                                    <input type="text" id="ec_ruc" class="form-control" maxlength="11" placeholder="20600000000">
                                    <div class="input-group-append"><button class="btn btn-outline-secondary" type="button" id="ec_buscar_ruc"><i class="fas fa-search"></i></button></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Razón Social</label>
                                <input type="text" id="ec_razon" class="form-control" placeholder="Nombre o empresa">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success" id="btnEmitirComprobanteListado"><i class="fas fa-file-invoice-dollar mr-1"></i> Emitir</button>
        </div>
    </div></div>
</div>

<!-- Modal Ver Tickets / Pagos -->
<div class="modal fade" id="modalTickets" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header bg-info text-white">
            <h5 class="modal-title"><i class="fas fa-receipt mr-2"></i> Pagos / Tickets de la Cita</h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <table class="table table-sm table-bordered">
                <thead class="thead-light"><tr><th>Ticket</th><th>Fecha</th><th>Concepto</th><th>Forma Pago</th><th>Monto</th><th>Comprobante</th><th>Imprimir</th></tr></thead>
                <tbody id="tickets_body"><tr><td colspan="7" class="text-center text-muted">Cargando...</td></tr></tbody>
            </table>
        </div>
    </div></div>
</div>

<!-- Modal Reservar Cita (misma funcionalidad que el dashboard) -->
<div class="modal fade" id="modalReservar" tabindex="-1" role="dialog">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="fas fa-ticket-alt mr-2"></i> Reservar Cupo</h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formReserva">
                <div class="form-group">
                    <label>1. Seleccionar Campaña</label>
                    <select class="form-control" id="reserva_horario" style="width: 100%;">
                        <option value="">-- Seleccionar --</option>
                        <?php foreach($horarios as $h): ?>
                        <option value="<?= $h->id ?>"><?= $h->fecha_especifica ? date('d/m/Y', strtotime($h->fecha_especifica)) : '' ?> - <?= esc(substr($h->medico, 0, 30)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>2. Seleccionar Paciente</label>
                    <select class="form-control" id="reserva_paciente_id" name="paciente_id" style="width: 100%;"></select>
                    <button class="btn btn-outline-success btn-sm btn-block mt-1" type="button" onclick="$('#modalNuevoPaciente').modal('show')">
                        <i class="fas fa-user-plus mr-1"></i> Registrar Nuevo Paciente
                    </button>
                </div>
                <div class="form-group border-top pt-3">
                    <label>3. Tipo de Comprobante</label>
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
                    <label>4. Servicios Adicionales (opcional)</label>
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
    </div></div>
</div>

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

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<link rel="stylesheet" href="<?= base_url('plugins/select2/css/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>">
<script src="<?= base_url('plugins/select2/js/select2.full.min.js') ?>"></script>
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
    // Imprime el ticket en la ticketera (paciente + deposito si aplica)
    function imprimirTicketTermico(pago_id) {
        if (!pago_id) return;
        Swal.fire({ title: 'Enviando a la ticketera...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $.post("<?= site_url('CmCitas/imprimir_ticket_termico') ?>", { pago_id: pago_id }, function(res) {
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Ticket enviado', text: res.msg, timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'No se pudo imprimir', text: res.msg });
            }
        });
    }

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
            { data: 'nro', render: function(d, t, r) {
                if (r.estado == 3) return '<span class="text-muted">-</span>';
                return '<span class="badge badge-dark" style="font-size:1rem;">' + (d || '-') + '</span>';
            }},
            { data: 'CLI_NOMBRE', render: function(d, t, r) {
                let badge = '';
                if (r.estado == 0) badge = ' <span class="badge badge-warning float-right">SIN PAGO</span>';
                if (r.estado == 4) badge = ' <span class="badge badge-info float-right">EXÁMENES</span>';
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
                    btn += '<a href="<?= site_url('cmHistoria/triaje/') ?>'+r.id+'" class="btn btn-warning btn-xs mr-1" title="Triaje"><i class="fas fa-heartbeat"></i></a>';
                    btn += '<a href="<?= site_url('CmHistoria/imprimir_triaje/') ?>'+r.id+'" target="_blank" class="btn btn-secondary btn-xs mr-1" title="Imprimir Triaje"><i class="fas fa-print"></i></a>';
                    btn += '<a href="<?= site_url('cmHistoria/ver/') ?>'+r.id+'" class="btn btn-outline-info btn-xs mr-1" title="Ver Historia"><i class="fas fa-file-medical"></i></a>';
                    btn += '<button class="btn btn-info btn-xs atender-ajax mr-1" data-cita="'+r.id+'" title="Atender"><i class="fas fa-check"></i></button>';
                }
                if (r.estado == 4) {
                    btn += '<a href="<?= site_url('cmHistoria/ver/') ?>'+r.id+'" class="btn btn-outline-info btn-xs mr-1" title="Ver Historia"><i class="fas fa-file-medical"></i></a>';
                    btn += '<button class="btn btn-info btn-xs atender-ajax mr-1" data-cita="'+r.id+'" title="Cerrar atención (exámenes completados)"><i class="fas fa-check"></i></button>';
                }
                if (r.estado == 2) {
                    btn += '<a href="<?= site_url('cmHistoria/ver/') ?>'+r.id+'" class="btn btn-outline-info btn-xs mr-1" title="Ver Historia"><i class="fas fa-file-medical"></i></a>';
                }
                if (r.estado == 1 || r.estado == 4) {
                    btn += '<button class="btn btn-primary btn-xs btn-servicio mr-1" data-cita="'+r.id+'" title="Agregar Servicio y Cobrar"><i class="fas fa-syringe"></i></button>';
                }
                if (r.estado == 1 || r.estado == 2 || r.estado == 4) {
                    btn += '<button class="btn btn-success btn-xs btn-comprobante mr-1" data-cita="'+r.id+'" title="Emitir Comprobante"><i class="fas fa-file-invoice-dollar"></i></button>';
                    btn += '<button class="btn btn-outline-secondary btn-xs btn-ticket mr-1" data-cita="'+r.id+'" title="Ver/Imprimir Tickets"><i class="fas fa-receipt"></i></button>';
                }
                if (r.estado == 0) {
                    btn += '<button class="btn btn-danger btn-xs anular-ajax" data-cita="'+r.id+'" title="Anular reserva sin pago"><i class="fas fa-times"></i></button>';
                }
                return btn;
            }},
        ],
        order: [],
        columnDefs: [
            { orderable: false, targets: [0] }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json' },
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print']
    });

    $('#btnFiltrar').click(function() { tabla.ajax.reload(); });

    // Cambio de local
    $('#select_local').change(function() {
        let local = $(this).val();
        $.post("<?= site_url('caja/set_caja') ?>", { caja: local, opci: 'caja' }, function() {
            location.reload();
        });
    });

    // Cobrar ajax: cobra y emite el comprobante automáticamente
        $(document).on('click', '.cobrar-ajax', function() {
        let cita_id = $(this).data('cita');
        let btn = $(this);
        let formaVal = 'EFECTIVO', nroOpVal = '';
        Swal.fire({
            title: '¿Cobrar la consulta?',
            html:
                '<div class="text-left">Forma de Pago:</div>' +
                '<select id="swal_forma" class="form-control mt-1">' +
                '<option value="EFECTIVO">EFECTIVO</option>' +
                '<option value="YAPE">YAPE</option>' +
                '<option value="PLIN">PLIN</option>' +
                '<option value="TARJETA">TARJETA</option>' +
                '<option value="TRANSFERENCIA">TRANSFERENCIA</option>' +
                '</select>' +
                '<div id="swal_nroop_box" class="mt-2 d-none">' +
                '<div class="text-left">N° Operación (obligatorio en YAPE):</div>' +
                '<input type="text" id="swal_nroop" class="form-control mt-1" maxlength="30" placeholder="Ej: 1234567890">' +
                '</div>' +
                '<hr class="my-2">' +
                '<div class="text-left">Tipo de comprobante:</div>' +
                '<select id="swal_tipo_comp" class="form-control mt-1">' +
                '<option value="B">Boleta</option><option value="F">Factura</option><option value="G">Guía</option></select>' +
                '<div id="swal_fact_box" class="d-none">' +
                '<div class="text-left mt-2">RUC (11 dígitos):</div>' +
                '<div class="input-group">' +
                '<input type="text" id="swal_ruc" class="form-control" maxlength="11" placeholder="20600000000">' +
                '<div class="input-group-append"><button type="button" id="swal_buscar_ruc" class="btn btn-outline-secondary"><i class="fas fa-search"></i></button></div>' +
                '</div>' +
                '<div class="text-left mt-2">Razón Social:</div>' +
                '<input type="text" id="swal_razon" class="form-control" placeholder="Nombre o empresa">' +
                '</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-cash-register"></i> Cobrar',
            cancelButtonText: 'Cancelar',
            didOpen: function() {
                $('#swal_forma').on('change', function() {
                    formaVal = $(this).val();
                    if (formaVal === 'YAPE') $('#swal_nroop_box').removeClass('d-none');
                    else { $('#swal_nroop_box').addClass('d-none'); nroOpVal = ''; $('#swal_nroop').val(''); }
                });
                $('#swal_tipo_comp').on('change', function() {
                    if ($(this).val() == 'F') $('#swal_fact_box').removeClass('d-none');
                    else $('#swal_fact_box').addClass('d-none');
                });
                $('#swal_buscar_ruc').on('click', function() {
                    let ruc = $('#swal_ruc').val().replace(/\D/g, '');
                    if (ruc.length != 11) { Swal.fire({ icon: 'warning', title: 'RUC debe tener 11 dígitos' }); return; }
                    $.post("<?= site_url('personas/get_persona_sunat') ?>", { ruc: ruc }, function(res) {
                        if (res.status === 'exists' || res.status === 'success') {
                            $('#swal_razon').val(res.data.nombre || '');
                        } else {
                            $('#swal_razon').val(''); Swal.fire({ icon: 'info', title: res.message || 'RUC no encontrado' });
                        }
                    });
                });
            }
        }).then((r) => {
            if (!r.isConfirmed) return;
            formaVal = $('#swal_forma').val();
            nroOpVal = ($('#swal_nroop').val() || '').trim();
            if (formaVal === 'YAPE' && !nroOpVal) {
                Swal.fire({ icon: 'warning', title: 'Para pagos con YAPE es obligatorio el N° de operación' });
                return;
            }
            let tipo = $('#swal_tipo_comp').val();
            let data = { cita_id: cita_id, tipo_comprobante: tipo, forma_pago: formaVal };
            if (nroOpVal) data.nro_operacion = nroOpVal;
            if (tipo == 'F') {
                data.cliente_num_doc = $('#swal_ruc').val();
                data.cliente_nombre = $('#swal_razon').val();
            }
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $.post("<?= site_url('CmCitas/cobrar_pendiente') ?>", data, function(res) {
                if (res.status === 'success') {
                    if (res.comprobante && res.comprobante.ref) {
                        Swal.fire({
                            icon: 'success', title: 'Pago y comprobante registrados',
                            html: 'Ticket: <strong>'+res.ticket.nro+'</strong><br>Comprobante: <strong>'+res.comprobante.ref+'</strong><br>Monto: S/ '+parseFloat(res.ticket.monto).toFixed(2),
                            showCancelButton: true, confirmButtonColor: '#28a745',
                            confirmButtonText: '<i class="fas fa-print"></i> Imprimir Comprobante',
                            cancelButtonText: 'Cerrar'
                        }).then((r) => {
                            if (r.isConfirmed) {
                                $.post("<?= site_url('CmCitas/imprimir_comprobante') ?>", { comprobante_id: res.comprobante.id }, function(){});
                            }
                            tabla.ajax.reload();
                        });
                    } else {
                        let msg = 'Ticket: <strong>'+res.ticket.nro+'</strong><br>Monto: S/ '+parseFloat(res.ticket.monto).toFixed(2);
                        if (res.comp_error) msg += '<br><small class="text-danger">Comprobante: '+res.comp_error+'</small>';
                        Swal.fire({
                            icon: 'success', title: 'Pago registrado',
                            html: msg,
                            showCancelButton: true, confirmButtonColor: '#28a745',
                            confirmButtonText: '<i class="fas fa-print"></i> Imprimir Ticket',
                            cancelButtonText: '<i class="fas fa-file-invoice-dollar"></i> Emitir Comprobante'
                        }).then((r) => {
                            if (r.isConfirmed) {
                                imprimirTicketTermico(res.ticket.pago_id);
                            } else if (r.dismiss === Swal.DismissReason.cancel) {
                                abrirEmitirComprobante(cita_id);
                            }
                            tabla.ajax.reload();
                        });
                    }
                } else {
                    Swal.fire({ icon: 'error', title: res.msg });
                }
                btn.prop('disabled', false).html('<i class="fas fa-cash-register"></i>');
            });
        });
    });
    // Atender / Cerrar pendiente
    $(document).on('click', '.atender-ajax', function() {
        let cita_id = $(this).data('cita');
        let titulo = $(this).attr('title') === 'Cerrar atención (exámenes completados)' ? '¿Cerrar atención? Los exámenes pendientes han terminado.' : '¿Marcar como atendido?';
        Swal.fire({ title: titulo, icon: 'question', showCancelButton: true, confirmButtonText: 'Sí' }).then((r) => {
            if (!r.isConfirmed) return;
            $.post("<?= site_url('CmCitas/cambiar_estado') ?>", { cita_id: cita_id, estado: '2' }, function(res) {
                Swal.fire({ icon: res.status === 'success' ? 'success' : 'error', title: res.msg }); tabla.ajax.reload();
            });
        });
    });

    // Anular ajax (solo reservas sin pago)
    $(document).on('click', '.anular-ajax', function() {
        let cita_id = $(this).data('cita');
        Swal.fire({ title: '¿Anular esta reserva sin pago?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, anular', cancelButtonText: 'Cancelar', confirmButtonColor: '#dc3545' }).then((r) => {
            if (!r.isConfirmed) return;
            $.post("<?= site_url('CmCitas/cambiar_estado') ?>", { cita_id: cita_id, estado: '3' }, function(res) {
                Swal.fire({ icon: res.status === 'success' ? 'success' : 'error', title: res.msg }); tabla.ajax.reload();
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
            $('#vp_sangre_txt').text(c.tipo_sangre ? c.tipo_sangre : 'No registrado');
            $('#vp_sangre_inp').val(c.tipo_sangre || '');
            $('#vp_contacto_txt').text(c.contacto_emergencia || 'No registrado');
            $('#vp_contacto_inp').val(c.contacto_emergencia || '');
            $('#vp_tel_emergencia_txt').text(c.telefono_emergencia || 'No registrado');
            $('#vp_tel_emergencia_inp').val(c.telefono_emergencia || '');
            $('#vp_alergias_txt').text(c.alergias || 'Ninguna');
            $('#vp_alergias_inp').val(c.alergias || '');
            $('#vp_enfermedades_txt').text(c.enfermedades_cronicas || 'Ninguna');
            $('#vp_enfermedades_inp').val(c.enfermedades_cronicas || '');
            $('#vp_observaciones_txt').text(c.observaciones_medicas || 'Ninguna');
            $('#vp_observaciones_inp').val(c.observaciones_medicas || '');
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
            fecha_nac: $('#vp_fnac_inp').val(),
            tipo_sangre: $('#vp_sangre_inp').val(),
            contacto_emergencia: $('#vp_contacto_inp').val(),
            telefono_emergencia: $('#vp_tel_emergencia_inp').val(),
            alergias: $('#vp_alergias_inp').val(),
            enfermedades_cronicas: $('#vp_enfermedades_inp').val(),
            observaciones_medicas: $('#vp_observaciones_inp').val()
        }, function(res) {
            Swal.fire({ icon: res.status === 'success' ? 'success' : 'error', title: res.msg });;
            if (res.status === 'success') {
                $('#btnEditarPaciente').trigger('click'); // volver a modo lectura
                tabla.ajax.reload();
            }
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');
        });
    });

    // === AGREGAR SERVICIO / PROCEDIMIENTO ===
    $('#sv_servicio').select2({
        theme: 'bootstrap4', placeholder: 'Buscar servicio...', dropdownParent: $('#modalServicio'),
        ajax: {
            url: "<?= site_url('CmCitas/get_servicios_disponibles') ?>", type: "post", dataType: 'json', delay: 250,
            processResults: function(r) {
                let results = [];
                (r||[]).forEach(function(item){ results.push({id:item.ART_KEY, text:item.ART_NOMBRE+' (S/ '+parseFloat(item.precio).toFixed(2)+')', precio:item.precio}); });
                return {results:results};
            },
            cache: true
        }
    }).on('select2:select', function(e) { $('#sv_precio').val(e.params.data.precio||''); });

    $(document).on('click', '.btn-servicio', function() {
        let cita_id = $(this).data('cita');
        $('#sv_cita_id').val(cita_id);
        $('#sv_precio').val('');
        $('#sv_obs').val('');
        if ($('#sv_servicio').hasClass('select2-hidden-accessible')) { $('#sv_servicio').val(null).trigger('change'); }
        $('#modalServicio').modal('show');
    });

    $('#btnGuardarServicio').click(function() {
        let cita_id = $('#sv_cita_id').val(), art_key = $('#sv_servicio').val();
        let precio = $('#sv_precio').val(), obs = $('#sv_obs').val();
        if (!art_key || !precio || parseFloat(precio) <= 0) { Swal.fire({ icon: 'warning', title: 'Selecciona servicio y precio' }); return; }
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmCitas/cobrar_procedimiento') ?>", { cita_id: cita_id, art_key: art_key, precio: precio, observacion: obs }, function(res) {
            if (res.status === 'success') {
                $('#modalServicio').modal('hide');
                if (res.ticket && res.ticket.nro) {
                    Swal.fire({
                        icon: 'success', title: 'Pago registrado',
                        html: 'Ticket: <strong>'+res.ticket.nro+'</strong><br>S/ '+parseFloat(res.ticket.monto).toFixed(2),
                        showCancelButton: true, confirmButtonColor: '#28a745',
                        confirmButtonText: '<i class="fas fa-print"></i> Imprimir Ticket',
                        cancelButtonText: '<i class="fas fa-file-invoice-dollar"></i> Emitir Comprobante'
                    }).then((r) => {
                        if (r.isConfirmed) {
                            imprimirTicketTermico(res.ticket.pago_id);
                        } else if (r.dismiss === Swal.DismissReason.cancel) {
                            abrirEmitirComprobante(cita_id);
                        }
                        tabla.ajax.reload();
                    });
                } else { Swal.fire({ icon: 'success', title: res.msg }); tabla.ajax.reload(); }
            } else { Swal.fire({ icon: 'error', title: res.msg }); }
            btn.prop('disabled', false).html('<i class="fas fa-cash-register mr-1"></i> Cobrar Servicio');
        });
    });

    // === EMITIR COMPROBANTE ===
    function abrirEmitirComprobante(cita_id) {
        $('#ec_cita_id').val(cita_id);
        $('#ec_ruc').val(''); $('#ec_razon').val('');
        $('#ec_factura_box').addClass('d-none');
        $('#ec_tipo').val('B');
        $.post("<?= site_url('CmCitas/get_servicios_pendientes_comprobante') ?>", { cita_id: cita_id }, function(servicios) {
            if (!servicios.length) {
                Swal.fire({ icon: 'info', title: 'No hay servicios pendientes de facturar' });
                return;
            }
            let html = '';
            servicios.forEach(function(s, i) {
                html += '<div class="custom-control custom-checkbox">' +
                    '<input class="custom-control-input ec-item" type="checkbox" id="ecitm_'+i+'" value="'+s.id+'" checked>' +
                    '<label class="custom-control-label" for="ecitm_'+i+'">'+s.descripcion+' - S/ '+parseFloat(s.subtotal).toFixed(2)+'</label></div>';
            });
            $('#ec_servicios').html(html);
            $('#modalEmitirComprobante').modal('show');
        });
    }

    $(document).on('click', '.btn-comprobante', function() {
        abrirEmitirComprobante($(this).data('cita'));
    });

    $('#ec_tipo').on('change', function() {
        if ($(this).val() == 'F') $('#ec_factura_box').removeClass('d-none');
        else $('#ec_factura_box').addClass('d-none');
    });

    $('#ec_buscar_ruc').click(function() {
        let ruc = $('#ec_ruc').val().replace(/\D/g, '');
        if (ruc.length != 11) { Swal.fire({ icon: 'warning', title: 'RUC debe tener 11 dígitos' }); return; }
        let b = $(this); b.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        // Reutiliza el modulo /personas: busca en CLIENTES y si no existe consulta Factiliza
        $.post("<?= site_url('personas/get_persona_sunat') ?>", { ruc: ruc }, function(res) {
            if (res.status === 'exists') {
                $('#ec_razon').val(res.data.nombre || '');
                Swal.fire({ icon: 'info', title: 'RUC ya registrado en el directorio', timer: 1500, showConfirmButton: false });
            } else if (res.status === 'success') {
                $('#ec_razon').val(res.data.nombre || '');
                // Crear el cliente en CLIENTES (reutilizando save_persona)
                $.post("<?= site_url('personas/save_persona') ?>", {
                    cod: res.data.codigo || '', ruc: ruc, nom: res.data.nombre || '',
                    dir: res.data.direccion || '', tel: res.data.telefono || '',
                    est: 'nuevo', nac: res.data.fecha_nacimiento || '', his: '', tps: 'C'
                }, function(save) {
                    if (save && save.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Cliente creado: ' + res.data.nombre, timer: 2000, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'info', title: (save && save.message) || 'RUC importado, ya puede emitir', timer: 2000, showConfirmButton: false });
                    }
                });
            } else {
                $('#ec_razon').val('');
                Swal.fire({ icon: 'error', title: res.message || 'RUC no encontrado' });
            }
            b.prop('disabled', false).html('<i class="fas fa-search"></i>');
        });
    });

    $('#btnEmitirComprobanteListado').click(function() {
        let cita_id = $('#ec_cita_id').val();
        let tipo_doc = $('#ec_tipo').val();
        let selected = [];
        $('.ec-item:checked').each(function() { selected.push($(this).val()); });
        if (!selected.length) { Swal.fire({ icon: 'warning', title: 'Selecciona al menos un servicio' }); return; }
        let data = { cita_id: cita_id, tipo_documento: tipo_doc, servicios_ids: selected };
        if (tipo_doc == 'F') {
            data.cliente_num_doc = $('#ec_ruc').val();
            data.cliente_nombre = $('#ec_razon').val();
        }
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmCitas/emitir_comprobante') ?>", data, function(res) {
            if (res.status === 'success') {
                $('#modalEmitirComprobante').modal('hide');
                Swal.fire({ icon: 'success', title: res.msg, timer: 2500, showConfirmButton: false }).then(function() { tabla.ajax.reload(); });
            } else { Swal.fire({ icon: 'error', title: res.msg }); }
            btn.prop('disabled', false).html('<i class="fas fa-file-invoice-dollar mr-1"></i> Emitir');
        });
    });

    // === VER / IMPRIMIR TICKETS ===
    $(document).on('click', '.btn-ticket', function() {
        let cita_id = $(this).data('cita');
        $('#tickets_body').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i></td></tr>');
        $('#modalTickets').modal('show');
        $.post("<?= site_url('CmCitas/get_pagos_cita') ?>", { cita_id: cita_id }, function(pagos) {
            if (!pagos.length) {
                $('#tickets_body').html('<tr><td colspan="7" class="text-center text-muted">Sin pagos registrados</td></tr>');
                return;
            }
            let html = '';
            pagos.forEach(function(p) {
                let compCell = '<span class="text-muted">-</span>';
                let imprCell = '<button class="btn btn-outline-secondary btn-xs btn-imprimir-pago" data-pago="'+p.id+'" title="Imprimir ticket"><i class="fas fa-receipt"></i></button>';
                if (p.comprobantes && p.comprobantes.length > 0) {
                    let compHtml = '';
                    p.comprobantes.forEach(function(c) {
                        let t = c.tipo_documento == 'F' ? 'FA' : (c.tipo_documento == 'G' ? 'GU' : 'BO');
                        compHtml += '<span class="badge badge-info">'+t+'-'+c.serie+'-'+String(c.correlativo).padStart(8,'0')+'</span> ';
                    });
                    compCell = compHtml;
                    imprCell = '<button class="btn btn-success btn-xs btn-imprimir-comp" data-comp="'+p.comprobantes[0].id+'" title="Imprimir comprobante"><i class="fas fa-file-invoice-dollar"></i></button>';
                }
                let formaCell = p.forma_pago || '';
                if (p.nro_operacion) formaCell += '<br><small class="text-muted">Op. ' + p.nro_operacion + '</small>';
                html += '<tr><td><strong>'+p.ticket_nro+'</strong></td><td>'+p.fecha_pago.substring(0,10)+'</td><td>'+p.concepto+'</td>' +
                    '<td>'+formaCell+'</td><td>S/ '+parseFloat(p.monto).toFixed(2)+'</td>' +
                    '<td>'+compCell+'</td>' +
                    '<td>'+imprCell+'</td></tr>';
            });
            $('#tickets_body').html(html);
        });
    });

    $(document).on('click', '.btn-imprimir-pago', function() {
        imprimirTicketTermico($(this).data('pago'));
    });

    $(document).on('click', '.btn-imprimir-comp', function() {
        let comp_id = $(this).data('comp');
        Swal.fire({ title: 'Enviando a la ticketera...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $.post("<?= site_url('CmCitas/imprimir_comprobante') ?>", { comprobante_id: comp_id }, function(res) {
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', title: res.msg, timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: res.msg });
            }
        });
    });

    // === NUEVA CITA / RESERVAR (igual que el dashboard) ===
    function notify(msg, type) {
        let icon = type === 'error' ? 'error' : (type === 'warning' ? 'warning' : (type === 'info' ? 'info' : 'success'));
        Swal.fire({ icon: icon, title: msg, toast: false, timer: 3000, showConfirmButton: true });
    }

    $('#btnNuevaCita').click(function() {
        let hf = $('#filtro_horario').val();
        if (hf) $('#reserva_horario').val(hf);
        else $('#reserva_horario').val($('#reserva_horario option:not(:first)').first().val());
        $('#modalReservar').modal('show');
    });

    $('#modalReservar').on('hidden.bs.modal', function() {
        $('#reserva_paciente_id').val(null).trigger('change');
        $('#servicios_extra').val(null).trigger('change');
        $('#reserva_horario').val('');
    });

    // === MODAL NUEVO PACIENTE ===
    $('#np_dni').on('input', function() {
        let v = $(this).val().replace(/\D/g, ''); $(this).val(v);
        let ok = v.length === 8 || v.length === 11;
        $('#np_buscar').prop('disabled', !ok);
        $('#np_importar, #np_completar').addClass('d-none');
    });

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
                if (res.datos_completos) notify('Encontrado en BD local.', 'success');
                else { $('#np_completar').removeClass('d-none'); notify('Faltan datos. Use "Completar desde Factiliza".', 'warning'); }
            } else if (res.status === 'not_found') {
                $('#np_importar').removeClass('d-none');
                notify('No encontrado en BD local.', 'info');
            } else { notify(res.msg, 'info'); }
            btn.prop('disabled', false).html('<i class="fas fa-search mr-1"></i> Buscar');
        });
    });

    $('#np_importar').click(function() {
        let dni = $('#np_dni').val().trim();
        if (!dni || ![8,11].includes(dni.length)) { notify('Ingrese DNI válido (8 u 11 dígitos)', 'warning'); return; }
        let btn = $(this); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post("<?= site_url('CmPacientes/importar_desde_dni') ?>", { dni: dni, forzar_api: '1' }, function(res) {
            if (res.status === 'exists' || res.status === 'success') {
                $('#np_nombre').val(res.nombre || '');
                $('#np_codigo').val(res.cliente_id || '');
                $('#np_direccion').val(res.direccion || '');
                $('#np_fecha_nac').val(res.fecha_nac || '');
                $('#np_telefono').val(res.telefono || '');
                notify(res.msg || 'Datos importados.', 'success');
            } else { notify(res.msg, 'info'); }
            btn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-1"></i> Importar');
        });
    });

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

    // Select2 servicios adicionales
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

    // Select2 paciente
    $('#reserva_paciente_id').select2({
        theme: 'bootstrap4', placeholder: 'Escribe el nombre o DNI del paciente...', dropdownParent: $('#modalReservar'),
        ajax: {
            url: "<?= site_url('CmPacientes/get_pacientes') ?>", type: "post", dataType: 'json', delay: 250,
            data: function(p) { return { busqueda: p.term }; },
            processResults: function(r) {
                let results = [];
                (r||[]).forEach(function(item){
                    results.push({ id: item.id, text: item.CLI_NOMBRE + ' (DNI: ' + (item.DNI||'S/N') + ')', cliente_id: item.cliente_id });
                });
                return {results:results};
            },
            cache: true
        }
    });

    $("#btnSoloReservar").click(function() {
        let pid = $("#reserva_paciente_id").val(), hid = $("#reserva_horario").val();
        let sel = $('#reserva_paciente_id').select2('data')[0];
        if(!hid) { notify('Selecciona una campaña.', 'warning'); return; }
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
        let pid = $("#reserva_paciente_id").val(), hid = $("#reserva_horario").val();
        let tc = $("input[name='tipo_comprobante']:checked").val(), extra = $("#servicios_extra").val() || [];
        let sel = $('#reserva_paciente_id').select2('data')[0];
        if(!hid) { notify('Selecciona una campaña.', 'warning'); return; }
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
                        if (r.isConfirmed) imprimirTicketTermico(res.ticket.pago_id);
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
<?= $this->endSection(); ?>
