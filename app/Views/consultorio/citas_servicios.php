<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<!-- Content Header (Page header) -->
<div class="content-header"></div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <!-- Filtros y controles principales -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Campaña</label>
                                    <div class="col-sm-9">
                                        <select id="filtro_campania" name="filtro_campania" class="form-control">
                                            <option value="">TODAS</option>
                                            <?php foreach ($campanias as $campania) {                                                                                 
                                                $date =  date("d/m/Y", strtotime($campania->CAM_FEC_INI)); ?>
                                                <option value="<?=$campania->CAM_CODCAMP?>" <?=$campanias_cod==$campania->CAM_CODCAMP?'selected=selected':''?>>
                                                    <?=$campania->CAM_DESCRIP.' - '.$date ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Estado</label>
                                    <select id="filtro_estado" name="filtro_estado" class="form-control">
                                        <option value="">TODOS</option>
                                        <option value="0">INSCRITO</option>
                                        <option value="1">CONFIRMADO</option>
                                        <option value="2">ATENDIDO</option>
                                        <option value="3">EN DIAGNÓSTICO</option>
                                        <option value="4">CANCELADO</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Fecha Inicio</label>
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Fecha Fin</label>
                                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <label>&nbsp;</label>
                                <button type="button" id="btn_buscar" class="btn btn-primary btn-block">
                                    <i class="fa fa-search"></i> Buscar
                                </button>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#modal-nueva-cita">
                                    <i class="fa fa-plus"></i> Nueva Cita
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de citas -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Gestión de Citas - Servicios y Pagos</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="tabla_citas" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Fecha/Hora</th>
                                    <th>Paciente</th>
                                    <th>DNI</th>
                                    <th>Teléfono</th>
                                    <th>Edad</th>
                                    <th>Servicios</th>
                                    <th>Total</th>
                                    <th>Pagado</th>
                                    <th>Saldo</th>
                                    <th>Estado Pago</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Cita -->
<div class="modal fade" id="modal-nueva-cita">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-success">
            <div class="modal-header">
                <h4 class="modal-title">Nueva Cita</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-nueva-cita">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Campaña</label>
                                <select id="nueva_cita_campania" name="campania_id" class="form-control" required>
                                    <option value="">SELECCIONE</option>
                                    <?php foreach ($campanias as $campania) {                                                                                 
                                        $date =  date("d/m/Y", strtotime($campania->CAM_FEC_INI)); ?>
                                        <option value="<?=$campania->CAM_CODCAMP?>" <?=$campanias_cod==$campania->CAM_CODCAMP?'selected=selected':''?>>
                                            <?=$campania->CAM_DESCRIP.' - '.$date ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Medio</label>
                                <select id="nueva_cita_medio" name="medio_id" class="form-control" required>
                                    <option value="">SELECCIONE</option>
                                    <?php foreach ($medios as $medio) { ?>
                                        <option value="<?=$medio->PUB_CODMEDIO?>"><?=$medio->PUB_DESCRIPCION ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Paciente</label>
                                <select id="nueva_cita_paciente" name="cliente_id" class="form-control" style="width: 100%;" required></select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-block" onclick="agregarPaciente()">
                                    <i class="fa fa-plus"></i> Nuevo
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Local Origen</label>
                                <select id="nueva_cita_local" name="local_origen" class="form-control" required>
                                    <option value="01">LOCAL 01</option>
                                    <option value="02">LOCAL 02</option>
                                    <option value="03">LOCAL 03</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha</label>
                                <input type="date" id="nueva_cita_fecha" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hora</label>
                                <input type="time" id="nueva_cita_hora" name="hora" class="form-control" value="<?= date('H:i') ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea id="nueva_cita_observaciones" name="observaciones" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cerrar</button>
                <button type="button" id="btn-guardar-cita" class="btn btn-outline-light">Guardar Cita</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gestión de Servicios y Pagos -->
<div class="modal fade" id="modal-gestion-cita" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Gestión de Cita - <span id="titulo_paciente"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Info de la cita -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Información del Paciente</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>DNI:</strong> <span id="info_dni"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Teléfono:</strong> <span id="info_telefono"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Edad:</strong> <span id="info_edad"></span> años
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Estado:</strong> <span id="info_estado" class="badge"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs para servicios y pagos -->
                <ul class="nav nav-tabs" id="tabs-cita" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-servicios" data-toggle="pill" href="#panel-servicios" role="tab">
                            <i class="fas fa-notes-medical"></i> Servicios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-pagos" data-toggle="pill" href="#panel-pagos" role="tab">
                            <i class="fas fa-credit-card"></i> Pagos
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="content-tabs">
                    <!-- Panel Servicios -->
                    <div class="tab-pane fade show active" id="panel-servicios" role="tabpanel">
                        <div class="row mt-3">
                            <div class="col-md-8">
                                <button type="button" class="btn btn-success mb-3" id="btn-agregar-servicio">
                                    <i class="fa fa-plus"></i> Agregar Servicio
                                </button>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-primary mb-3" id="btn-pagar-servicios">
                                    <i class="fa fa-credit-card"></i> Pagar
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="tabla-servicios" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Servicio</th>
                                                <th>Cantidad</th>
                                                <th>Precio</th>
                                                <th>Subtotal</th>
                                                <th>Estado Pago</th>
                                                <th>Observaciones</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr class="bg-light">
                                                <th colspan="3">TOTAL SERVICIOS:</th>
                                                <th id="total-servicios">S/. 0.00</th>
                                                <th colspan="3"></th>
                                            </tr>
                                            <tr class="bg-warning">
                                                <th colspan="3">PENDIENTE DE PAGO:</th>
                                                <th id="total-pendiente">S/. 0.00</th>
                                                <th colspan="3"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Pagos -->
                    <div class="tab-pane fade" id="panel-pagos" role="tabpanel">
                        <div class="row mt-3">
                            <div class="col-md-8">
                                <button type="button" class="btn btn-primary mb-3" id="btn-registrar-pago">
                                    <i class="fa fa-money-bill-wave"></i> Registrar Pago
                                </button>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Saldo Pendiente</span>
                                        <span class="info-box-number" id="saldo-pendiente">S/. 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="tabla-pagos" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Monto</th>
                                                <th>Forma Pago</th>
                                                <th>Referencia</th>
                                                <th>Local</th>
                                                <th>Comprobante</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr class="bg-light">
                                                <th>TOTAL PAGADO:</th>
                                                <th id="total-pagado">S/. 0.00</th>
                                                <th colspan="6"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <select id="cambiar-estado" class="form-control d-inline-block w-auto">
                        <option value="0">INSCRITO</option>
                        <option value="1">CONFIRMADO</option>
                        <option value="2">ATENDIDO</option>
                        <option value="3">EN DIAGNÓSTICO</option>
                        <option value="4">CANCELADO</option>
                    </select>
                    <button type="button" class="btn btn-warning" id="btn-cambiar-estado">
                        <i class="fa fa-edit"></i> Cambiar Estado
                    </button>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Servicio -->
<div class="modal fade" id="modal-agregar-servicio">
    <div class="modal-dialog">
        <div class="modal-content bg-success">
            <div class="modal-header">
                <h4 class="modal-title">Agregar Servicio</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-agregar-servicio">
                    <div class="form-group">
                        <label>Buscar Servicio</label>
                        <input type="text" id="buscar-servicio" class="form-control" placeholder="Escriba para buscar servicios...">
                        <div id="resultados-servicio" class="mt-2" style="max-height: 200px; overflow-y: auto;"></div>
                    </div>
                    <div class="form-group">
                        <label>Servicio Seleccionado</label>
                        <input type="text" id="servicio-seleccionado" class="form-control" readonly>
                        <input type="hidden" id="servicio-id" name="articulo_id">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cantidad</label>
                                <input type="number" id="servicio-cantidad" name="cantidad" class="form-control" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Precio</label>
                                <input type="number" id="servicio-precio" name="precio" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea id="servicio-observaciones" name="observaciones" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cerrar</button>
                <button type="button" id="btn-confirmar-servicio" class="btn btn-outline-light">Agregar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Pago -->
<div class="modal fade" id="modal-registrar-pago">
    <div class="modal-dialog">
        <div class="modal-content bg-info">
            <div class="modal-header">
                <h4 class="modal-title">Registrar Pago</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-registrar-pago">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Monto</label>
                                <input type="number" id="pago-monto" name="monto" class="form-control" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Forma de Pago</label>
                                <select id="pago-forma" name="forma_pago" class="form-control" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="EFECTIVO">EFECTIVO</option>
                                    <option value="TARJETA">TARJETA</option>
                                    <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                                    <option value="YAPE">YAPE</option>
                                    <option value="PLIN">PLIN</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Referencia</label>
                                <input type="text" id="pago-referencia" name="referencia" class="form-control" placeholder="Número de operación, etc.">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Local de Pago</label>
                                <select id="pago-local" name="local_pago" class="form-control" required>
                                    <option value="01">LOCAL 01</option>
                                    <option value="02">LOCAL 02</option>
                                    <option value="03">LOCAL 03</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cerrar</button>
                <button type="button" id="btn-confirmar-pago" class="btn btn-outline-light">Registrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gestión de Paciente -->
<div class="modal fade" id="modal-paciente">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">DATOS DEL PACIENTE</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-paciente">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-6">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="CLI_CODCLIE" name="CLI_CODCLIE" placeholder="CODIGO CLIENTE" disabled />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="row">
                            <div class="col-6">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-id-badge"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="CLI_RUC_ESPOSO" name="CLI_RUC_ESPOSO" placeholder="R.U.C. o D.N.I." autocomplete="off"/>
                                </div>
                            </div>
                            <div class="col-6">
                                <button type="button" id="importarp" name="importarp" class="btn btn-primary">
                                    <i class="fas fa-cloud-download-alt"></i> Importar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-user"></i></span>
                            </div>
                            <input type="text" class="form-control" id="CLI_NOMBRE" name="CLI_NOMBRE" placeholder="NOMBRE Y APELLIDOS O RAZON SOCIAL" />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="date" id="CLI_FECHA_NAC" name="CLI_FECHA_NAC" class="form-control" placeholder="FECHA NACIMIENTO">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-home"></i></span>
                            </div>
                            <input type="text" class="form-control" id="CLI_CASA_DIREC" name="CLI_CASA_DIREC" placeholder="DIRECCIÓN" />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-phone"></i></span>
                            </div>
                            <input type="text" class="form-control" id="CLI_TELEF1" name="CLI_TELEF1" placeholder="TELEFONO" autocomplete="off"/>
                            <input type="hidden" id="estado_paciente" name="estado" />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                            <input type="checkbox" class="custom-control-input" id="historia" name="historia">
                            <label class="custom-control-label" for="historia">¿Tiene Historia?</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="btn-group w-100">
                    <button type="button" id="seleccionarp" name="seleccionarp" class="btn btn-info">
                        <i class="fas fa-check"></i> Seleccionar
                    </button>
                    <button type="button" id="nuevo_paciente" name="nuevo_paciente" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo
                    </button>
                    <button type="button" id="editar_paciente" name="editar_paciente" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button type="button" id="cancelar_paciente" name="cancelar_paciente" class="btn btn-danger" data-dismiss="modal">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </button>
                    <button type="button" id="guardar_paciente" name="guardar_paciente" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<!-- Librerías necesarias -->
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">

<!-- Select2 CSS -->  
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Variables globales
let citaActual = null;
let tablaCitas = null;
let tablaServicios = null;
let tablaPagos = null;

$(document).ready(function() {
    // Inicializar componentes
    inicializarComponentes();
    
    // Event listeners
    configurarEventos();
    
    // Cargar datos iniciales
    cargarCitas();
});

function inicializarComponentes() {
    // Inicializar Select2 para búsqueda de pacientes
    $('#nueva_cita_paciente').select2({
        placeholder: 'Buscar paciente...',
        minimumInputLength: 3,
        ajax: {
            url: '/consultorio/get_personas2',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    term: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function(item) {
                        // Si los datos ya vienen con id y text, usarlos directamente
                        if (item.id && item.text) {
                            return {
                                id: item.id,
                                text: item.text
                            };
                        }
                        // Si vienen con formato original, convertir
                        return {
                            id: item.CLI_CODCLIE,
                            text: item.CLI_NOMBRE + ' - ' + item.CLI_RUC_ESPOSA
                        };
                    })
                };
            }
        }
    });

    // Inicializar DataTable principal
    tablaCitas = $('#tabla_citas').DataTable({
        "processing": true,
        "serverSide": false,
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "columns": [
            {"data": "CIT_ORD"},
            {
                "data": null,
                "render": function(data, type, row) {
                    return row.CIT_FECHA + '<br><small>' + row.CIT_HORA + '</small>';
                }
            },
            {"data": "CLI_NOMBRE"},
            {"data": "CLI_RUC_ESPOSA"},
            {"data": "CLI_TELEF1"},
            {"data": "EDAD"},
            {"data": "CANT_SERVICIOS"},
            {
                "data": "CIT_TOTAL",
                "render": function(data, type, row) {
                    return 'S/. ' + parseFloat(data || 0).toFixed(2);
                }
            },
            {
                "data": "TOTAL_PAGADO", 
                "render": function(data, type, row) {
                    return 'S/. ' + parseFloat(data || 0).toFixed(2);
                }
            },
            {
                "data": "CIT_SALDO",
                "render": function(data, type, row) {
                    return 'S/. ' + parseFloat(data || 0).toFixed(2);
                }
            },
            {
                "data": "ESTADO_PAGO",
                "render": function(data, type, row) {
                    let clase = '';
                    switch(data) {
                        case 'PAGADO': clase = 'badge-success'; break;
                        case 'PARCIAL': clase = 'badge-warning'; break;
                        default: clase = 'badge-danger';
                    }
                    return '<span class="badge ' + clase + '">' + data + '</span>';
                }
            },
            {
                "data": "CIT_ESTADO",
                "render": function(data, type, row) {
                    let estados = {
                        0: {text: 'INSCRITO', class: 'badge-secondary'},
                        1: {text: 'CONFIRMADO', class: 'badge-primary'},
                        2: {text: 'ATENDIDO', class: 'badge-success'},
                        3: {text: 'EN DIAGNÓSTICO', class: 'badge-info'},
                        4: {text: 'CANCELADO', class: 'badge-danger'}
                    };
                    let estado = estados[data] || estados[0];
                    return '<span class="badge ' + estado.class + '">' + estado.text + '</span>';
                }
            },
            {
                "data": null,
                "orderable": false,
                "render": function(data, type, row) {
                    return `
                        <button type="button" class="btn btn-sm btn-info btn-gestionar" 
                                onclick="abrirGestionCita(${row.CIT_CODCIT})">
                            <i class="fa fa-edit"></i> Gestionar
                        </button>
                    `;
                }
            }
        ]
    });
}

function configurarEventos() {
    // Botón buscar
    $('#btn_buscar').click(function() {
        cargarCitas();
    });
    
    // Guardar nueva cita
    $('#btn-guardar-cita').click(function() {
        guardarNuevaCita();
    });
    
    // Búsqueda de servicios
    $('#buscar-servicio').on('input', function() {
        let busqueda = $(this).val();
        if(busqueda.length >= 3) {
            buscarServicios(busqueda);
        } else {
            $('#resultados-servicio').empty();
        }
    });
    
    // Confirmar agregar servicio
    $('#btn-confirmar-servicio').click(function() {
        agregarServicioACita();
    });
    
    // Registrar pago
    $('#btn-confirmar-pago').click(function() {
        registrarPago();
    });
    
    // Cambiar estado
    $('#btn-cambiar-estado').click(function() {
        cambiarEstadoCita();
    });
    
    // Procesar pago directo
    $('#btn-pagar-servicios').click(function() {
        procesarPagoDirecto();
    });
    
    // Botones de los modales
    $('#btn-agregar-servicio').click(function() {
        $('#modal-agregar-servicio').modal('show');
    });
    
    $('#btn-registrar-pago').click(function() {
        $('#modal-registrar-pago').modal('show');
    });
    
    // Botón pagar servicios (nueva lógica)
    $('#btn-pagar-servicios').click(function() {
        procesarPagoDirecto();
    });
    
    // Gestión de pacientes
    $('#seleccionarp').click(function() {
        seleccionarPaciente();
    });
    
    $('#nuevo_paciente').click(function() {
        prepararNuevoPaciente();
    });
    
    $('#editar_paciente').click(function() {
        prepararEditarPaciente();
    });
    
    $('#guardar_paciente').click(function() {
        guardarPaciente();
    });
    
    $('#importarp').click(function() {
        importarPaciente();
    });
    
    // Validaciones en tiempo real
    $('#CLI_RUC_ESPOSO').on('input', function() {
        validarRucDni($(this));
    });
    
    $('#CLI_NOMBRE').on('input', function() {
        validarNombre($(this));
    });
}

function cargarCitas() {
    let datos = {
        campania: $('#filtro_campania').val(),
        estado: $('#filtro_estado').val(),
        fecha_inicio: $('#fecha_inicio').val(),
        fecha_fin: $('#fecha_fin').val()
    };
    
    $.ajax({
        url: '/citas/getCitasConServicios',
        type: 'POST',
        data: datos,
        success: function(response) {
            if(response.success) {
                tablaCitas.clear().rows.add(response.data).draw();
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar las citas', 'error');
        }
    });
}

function guardarNuevaCita() {
    if(!$('#form-nueva-cita')[0].checkValidity()) {
        $('#form-nueva-cita')[0].reportValidity();
        return;
    }
    
    let datos = {
        cliente_id: $('#nueva_cita_paciente').val(),
        campania_id: $('#nueva_cita_campania').val(),
        medio_id: $('#nueva_cita_medio').val(),
        fecha: $('#nueva_cita_fecha').val(),
        hora: $('#nueva_cita_hora').val(),
        observaciones: $('#nueva_cita_observaciones').val(),
        local_origen: $('#nueva_cita_local').val()
    };
    
    $.ajax({
        url: '/citas/crearCita',
        type: 'POST',
        data: datos,
        success: function(response) {
            if(response.success) {
                $('#modal-nueva-cita').modal('hide');
                $('#form-nueva-cita')[0].reset();
                $('#nueva_cita_paciente').val(null).trigger('change');
                cargarCitas();
                Swal.fire('Éxito', response.message, 'success');
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al crear la cita', 'error');
        }
    });
}

function abrirGestionCita(citaId) {
    citaActual = citaId;
    
    $.ajax({
        url: '/citas/getCitaDetalle',
        type: 'POST',
        data: {cita_id: citaId},
        success: function(response) {
            if(response.success) {
                // Llenar información
                $('#titulo_paciente').text(response.cita.CLI_NOMBRE);
                $('#info_dni').text(response.cita.CLI_RUC_ESPOSA);
                $('#info_telefono').text(response.cita.CLI_TELEF1);
                $('#info_edad').text(response.cita.EDAD);
                
                let estados = {
                    0: {text: 'INSCRITO', class: 'badge-secondary'},
                    1: {text: 'CONFIRMADO', class: 'badge-primary'}, 
                    2: {text: 'ATENDIDO', class: 'badge-success'},
                    3: {text: 'EN DIAGNÓSTICO', class: 'badge-info'},
                    4: {text: 'CANCELADO', class: 'badge-danger'}
                };
                let estado = estados[response.cita.CIT_ESTADO] || estados[0];
                $('#info_estado').removeClass().addClass('badge ' + estado.class).text(estado.text);
                $('#cambiar-estado').val(response.cita.CIT_ESTADO);
                
                // Cargar servicios
                cargarServicios(response.servicios);
                
                // Cargar pagos
                cargarPagos(response.pagos);
                
                // Actualizar totales
                $('#total-servicios').text('S/. ' + parseFloat(response.totales.total_servicios || 0).toFixed(2));
                $('#total-pendiente').text('S/. ' + parseFloat(response.totales.total_pendiente || 0).toFixed(2));
                $('#total-pagado').text('S/. ' + parseFloat(response.totales.total_pagado || 0).toFixed(2));
                $('#saldo-pendiente').text('S/. ' + parseFloat(response.totales.saldo || 0).toFixed(2));
                
                $('#modal-gestion-cita').modal('show');
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar los detalles de la cita', 'error');
        }
    });
}

function cargarServicios(servicios) {
    let tbody = $('#tabla-servicios tbody');
    tbody.empty();
    
    let totalPendiente = 0;
    
    servicios.forEach(function(servicio) {
        let estadoPago = servicio.CNS_PAGADO == 1 ? 'PAGADO' : 'PENDIENTE';
        let badgeClass = servicio.CNS_PAGADO == 1 ? 'badge-success' : 'badge-warning';
        let botonEliminar = servicio.CNS_PAGADO == 1 ? '' : `
            <button type="button" class="btn btn-sm btn-danger" 
                    onclick="eliminarServicio(${servicio.CNS_CODCNS})">
                <i class="fa fa-trash"></i>
            </button>
        `;
        
        // Sumar al total pendiente si no está pagado
        if (servicio.CNS_PAGADO != 1) {
            totalPendiente += parseFloat(servicio.SUBTOTAL);
        }
        
        tbody.append(`
            <tr ${servicio.CNS_PAGADO == 1 ? 'class="table-success"' : ''}>
                <td>${servicio.ART_NOMBRE}</td>
                <td>${servicio.CNS_CANTIDAD}</td>
                <td>S/. ${parseFloat(servicio.CNS_PRECIO).toFixed(2)}</td>
                <td>S/. ${parseFloat(servicio.SUBTOTAL).toFixed(2)}</td>
                <td><span class="badge ${badgeClass}">${estadoPago}</span></td>
                <td>${servicio.CNS_OBSERVACIONES || ''}</td>
                <td>
                    ${botonEliminar}
                </td>
            </tr>
        `);
    });
    
    // Actualizar total pendiente
    $('#total-pendiente').text('S/. ' + totalPendiente.toFixed(2));
}

function cargarPagos(pagos) {
    let tbody = $('#tabla-pagos tbody');
    tbody.empty();
    
    pagos.forEach(function(pago) {
        let comprobante = '';
        if(pago.CTP_GENERO_COMPROBANTE) {
            comprobante = `${pago.CTP_TIPO_COMPROBANTE}${pago.CTP_SERIE}-${pago.CTP_NUMERO}`;
        }
        
        tbody.append(`
            <tr>
                <td>${new Date(pago.CTP_FECHA).toLocaleDateString()}</td>
                <td>S/. ${parseFloat(pago.CTP_MONTO).toFixed(2)}</td>
                <td>${pago.CTP_FORMA_PAGO}</td>
                <td>${pago.CTP_REFERENCIA || ''}</td>
                <td>LOCAL ${pago.CTP_LOCAL_PAGO}</td>
                <td>${comprobante}</td>
                <td>
                    <span class="badge ${pago.CTP_ESTADO == 1 ? 'badge-success' : 'badge-danger'}">
                        ${pago.ESTADO_DESC}
                    </span>
                </td>
                <td>
                    ${pago.CTP_ESTADO == 1 ? `
                        <button type="button" class="btn btn-sm btn-warning" 
                                onclick="anularPago(${pago.CTP_CODCTP})">
                            <i class="fa fa-ban"></i>
                        </button>
                        ${!pago.CTP_GENERO_COMPROBANTE ? `
                            <button type="button" class="btn btn-sm btn-info" 
                                    onclick="generarComprobante(${pago.CTP_CODCTP})">
                                <i class="fa fa-file-invoice"></i>
                            </button>
                        ` : ''}
                    ` : ''}
                </td>
            </tr>
        `);
    });
}

function buscarServicios(busqueda) {
    $.ajax({
        url: '/citas/buscarServicios',
        type: 'POST',
        data: {busqueda: busqueda},
        success: function(response) {
            if(response.success) {
                let resultados = $('#resultados-servicio');
                resultados.empty();
                
                response.data.forEach(function(servicio) {
                    resultados.append(`
                        <div class="resultado-servicio p-2 border mb-1" style="cursor: pointer;" 
                             onclick="seleccionarServicio(${servicio.ART_KEY}, '${servicio.ART_NOMBRE}', ${servicio.PRECIO})">
                            <strong>${servicio.ART_NOMBRE}</strong><br>
                            <small>Precio: S/. ${parseFloat(servicio.PRECIO).toFixed(2)} | Unidad: ${servicio.UNIDAD || 'UND'}</small>
                        </div>
                    `);
                });
            }
        }
    });
}

function seleccionarServicio(id, nombre, precio) {
    $('#servicio-id').val(id);
    $('#servicio-seleccionado').val(nombre);
    $('#servicio-precio').val(precio);
    $('#resultados-servicio').empty();
    $('#buscar-servicio').val('');
}

function agregarServicioACita() {
    if(!$('#form-agregar-servicio')[0].checkValidity()) {
        $('#form-agregar-servicio')[0].reportValidity();
        return;
    }
    
    let datos = {
        cita_id: citaActual,
        articulo_id: $('#servicio-id').val(),
        cantidad: $('#servicio-cantidad').val(),
        precio: $('#servicio-precio').val(),
        observaciones: $('#servicio-observaciones').val()
    };
    
    $.ajax({
        url: '/citas/agregarServicio',
        type: 'POST',
        data: datos,
        success: function(response) {
            if(response.success) {
                $('#modal-agregar-servicio').modal('hide');
                $('#form-agregar-servicio')[0].reset();
                abrirGestionCita(citaActual); // Recargar datos
                Swal.fire('Éxito', response.message, 'success');
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al agregar el servicio', 'error');
        }
    });
}

function eliminarServicio(servicioId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Se eliminará este servicio de la cita',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/citas/eliminarServicio',
                type: 'POST',
                data: {
                    servicio_id: servicioId,
                    cita_id: citaActual
                },
                success: function(response) {
                    if(response.success) {
                        abrirGestionCita(citaActual); // Recargar datos
                        Swal.fire('Eliminado', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al eliminar el servicio', 'error');
                }
            });
        }
    });
}

function registrarPago() {
    if(!$('#form-registrar-pago')[0].checkValidity()) {
        $('#form-registrar-pago')[0].reportValidity();
        return;
    }
    
    let datos = {
        cita_id: citaActual,
        monto: $('#pago-monto').val(),
        forma_pago: $('#pago-forma').val(),
        referencia: $('#pago-referencia').val(),
        local_pago: $('#pago-local').val()
    };
    
    $.ajax({
        url: '/citas/registrarPago',
        type: 'POST',
        data: datos,
        success: function(response) {
            if(response.success) {
                $('#modal-registrar-pago').modal('hide');
                $('#form-registrar-pago')[0].reset();
                abrirGestionCita(citaActual); // Recargar datos
                cargarCitas(); // Actualizar tabla principal
                Swal.fire('Éxito', response.message, 'success');
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al registrar el pago', 'error');
        }
    });
}

function anularPago(pagoId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Se anulará este pago',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/citas/anularPago',
                type: 'POST',
                data: {
                    pago_id: pagoId,
                    cita_id: citaActual
                },
                success: function(response) {
                    if(response.success) {
                        abrirGestionCita(citaActual); // Recargar datos
                        cargarCitas(); // Actualizar tabla principal
                        Swal.fire('Anulado', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al anular el pago', 'error');
                }
            });
        }
    });
}

function generarComprobante(pagoId) {
    Swal.fire({
        title: 'Generar Comprobante',
        html: `
            <div class="form-group">
                <label>Tipo de Comprobante</label>
                <select id="tipo-comprobante" class="form-control">
                    <option value="B">BOLETA</option>
                    <option value="F">FACTURA</option>
                </select>
            </div>
            <div class="form-group">
                <label>Local</label>
                <select id="local-comprobante" class="form-control">
                    <option value="01">LOCAL 01</option>
                    <option value="02">LOCAL 02</option>
                    <option value="03">LOCAL 03</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Generar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            return {
                tipo: $('#tipo-comprobante').val(),
                local: $('#local-comprobante').val()
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/citas/generarComprobante',
                type: 'POST',
                data: {
                    cita_id: citaActual,
                    pago_id: pagoId,
                    tipo_comprobante: result.value.tipo,
                    local_pago: result.value.local
                },
                success: function(response) {
                    if(response.success) {
                        abrirGestionCita(citaActual); // Recargar datos
                        Swal.fire(
                            'Comprobante Generado',
                            `${response.comprobante.tipo}${response.comprobante.serie}-${response.comprobante.numero}<br>Total: S/. ${response.comprobante.total}`,
                            'success'
                        );
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al generar el comprobante', 'error');
                }
            });
        }
    });
}

function cambiarEstadoCita() {
    let nuevoEstado = $('#cambiar-estado').val();
    
    $.ajax({
        url: '/citas/cambiarEstadoCita',
        type: 'POST',
        data: {
            cita_id: citaActual,
            estado: nuevoEstado
        },
        success: function(response) {
            if(response.success) {
                abrirGestionCita(citaActual); // Recargar datos
                cargarCitas(); // Actualizar tabla principal
                Swal.fire('Éxito', response.message, 'success');
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cambiar el estado', 'error');
        }
    });
}

// ========================================
// NUEVA FUNCIÓN: PROCESAR PAGO DIRECTO
// ========================================

function procesarPagoDirecto() {
    // Verificar que hay servicios pendientes
    let totalPendiente = parseFloat($('#total-pendiente').text().replace('S/. ', '').replace(',', ''));
    
    if (totalPendiente <= 0) {
        Swal.fire('Error', 'No hay servicios pendientes de pago', 'error');
        return;
    }
    
    // Modal para seleccionar opciones de pago
    Swal.fire({
        title: 'Procesar Pago',
        html: `
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="alert alert-info">
                        <strong>Total a Pagar: S/. ${totalPendiente.toFixed(2)}</strong>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Forma de Pago</label>
                <select id="pago-directo-forma" class="form-control">
                    <option value="EFECTIVO">EFECTIVO</option>
                    <option value="TARJETA">TARJETA</option>
                    <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                    <option value="YAPE">YAPE</option>
                    <option value="PLIN">PLIN</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tipo de Comprobante</label>
                <select id="pago-directo-tipo" class="form-control">
                    <option value="B">BOLETA</option>
                    <option value="F">FACTURA</option>
                </select>
            </div>
            <div class="form-group">
                <label>Local</label>
                <select id="pago-directo-local" class="form-control">
                    <option value="01">LOCAL 01</option>
                    <option value="02">LOCAL 02</option>
                    <option value="03">LOCAL 03</option>
                </select>
            </div>
            <div class="form-group">
                <label>Referencia (Opcional)</label>
                <input type="text" id="pago-directo-referencia" class="form-control" placeholder="Número de operación, etc.">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Procesar Pago',
        cancelButtonText: 'Cancelar',
        width: '500px',
        preConfirm: () => {
            return {
                forma_pago: $('#pago-directo-forma').val(),
                tipo_comprobante: $('#pago-directo-tipo').val(),
                local_pago: $('#pago-directo-local').val(),
                referencia: $('#pago-directo-referencia').val()
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Procesar el pago directo
            $.ajax({
                url: '/citas/procesarPago',
                type: 'POST',
                data: {
                    cita_id: citaActual,
                    forma_pago: result.value.forma_pago,
                    tipo_comprobante: result.value.tipo_comprobante,
                    local_pago: result.value.local_pago,
                    referencia: result.value.referencia
                },
                success: function(response) {
                    if(response.success) {
                        abrirGestionCita(citaActual); // Recargar datos
                        cargarCitas(); // Actualizar tabla principal
                        
                        Swal.fire({
                            title: 'Pago Procesado',
                            html: `
                                <div class="alert alert-success">
                                    <strong>Comprobante Generado:</strong><br>
                                    ${response.comprobante.tipo}${response.comprobante.serie}-${response.comprobante.numero}<br>
                                    <strong>Total: S/. ${response.comprobante.total}</strong>
                                </div>
                            `,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        });
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al procesar el pago', 'error');
                }
            });
        }
    });
}

// ========================================
// FUNCIONES PARA GESTIÓN DE PACIENTES
// ========================================

function agregarPaciente() {
    $('#modal-paciente').modal('show');
    limpiarFormularioPaciente();
    prepararNuevoPaciente();
}

function limpiarFormularioPaciente() {
    $('#form-paciente')[0].reset();
    $('#CLI_CODCLIE').val('');
    $('#estado_paciente').val('nuevo');
    $('#CLI_RUC_ESPOSO, #CLI_NOMBRE, #CLI_CASA_DIREC, #CLI_TELEF1').removeClass('is-valid is-invalid');
}

function prepararNuevoPaciente() {
    $('#nuevo_paciente').hide();
    $('#editar_paciente').hide();
    $('#seleccionarp').hide();
    $('#guardar_paciente').show();
    $('#cancelar_paciente').show();
    $('#importarp').show();
    $('#estado_paciente').val('nuevo');
}

function prepararEditarPaciente() {
    $('#nuevo_paciente').hide();
    $('#editar_paciente').hide();
    $('#seleccionarp').hide();
    $('#guardar_paciente').show();
    $('#cancelar_paciente').show();
    $('#importarp').show();
    $('#estado_paciente').val('editar');
}

function seleccionarPaciente() {
    let clienteId = $('#CLI_CODCLIE').val();
    let clienteNombre = $('#CLI_NOMBRE').val();
    
    if (clienteId && clienteNombre) {
        // Agregar a Select2
        let newOption = new Option(clienteNombre, clienteId, false, true);
        $('#nueva_cita_paciente').append(newOption).trigger('change');
        
        $('#modal-paciente').modal('hide');
        Swal.fire('Éxito', 'Paciente seleccionado correctamente', 'success');
    } else {
        Swal.fire('Error', 'Debe completar los datos del paciente', 'error');
    }
}

function guardarPaciente() {
    // Validaciones básicas
    if ($('#CLI_NOMBRE').val().trim() === '') {
        Swal.fire('Error', 'El nombre es obligatorio', 'error');
        $('#CLI_NOMBRE').focus();
        return;
    }
    
    if ($('#CLI_RUC_ESPOSO').val().trim() === '') {
        Swal.fire('Error', 'El DNI/RUC es obligatorio', 'error');
        $('#CLI_RUC_ESPOSO').focus();
        return;
    }
    
    let datos = {
        cod: $('#CLI_CODCLIE').val(),
        ruc: $('#CLI_RUC_ESPOSO').val(),
        nom: $('#CLI_NOMBRE').val(),
        dir: $('#CLI_CASA_DIREC').val(),
        tel: $('#CLI_TELEF1').val(),
        nac: $('#CLI_FECHA_NAC').val(),
        his: $('#historia').is(':checked') ? 1 : 0,
        est: $('#estado_paciente').val(),
        tps: 'C'
    };
    
    $.ajax({
        url: '/personas/save_persona',
        type: 'POST',
        data: datos,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let data = {
                    id: response.data.codigo,
                    text: response.data.nombre
                };
                
                // Si es nuevo, agregarlo al Select2
                if ($('#estado_paciente').val() === 'nuevo') {
                    let newOption = new Option(data.text, data.id, false, true);
                    $('#nueva_cita_paciente').append(newOption).trigger('change');
                }
                
                $('#modal-paciente').modal('hide');
                Swal.fire('Éxito', 'Paciente guardado correctamente', 'success');
            } else {
                Swal.fire('Error', response.message || 'Error al guardar', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error de conexión', 'error');
        }
    });
}

function importarPaciente() {
    let ruc = $('#CLI_RUC_ESPOSO').val().trim();
    
    if (ruc === '') {
        Swal.fire('Error', 'Ingrese un DNI o RUC para importar', 'error');
        $('#CLI_RUC_ESPOSO').focus();
        return;
    }
    
    $('#importarp').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importando...');
    
    $.ajax({
        url: '/personas/get_persona_sunat',
        type: 'POST',
        data: {
            ruc: ruc,
            tipo: 2
        },
        success: function(response) {
            let datos = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (datos[0] == null) {
                Swal.fire('Error', 'DNI o RUC no válido o no registrado', 'error');
                $('#CLI_RUC_ESPOSO').focus();
            } else if (datos[0] === 'nada') {
                Swal.fire('Info', 'DNI o RUC no se encuentra, regístrelo manualmente.', 'info');
                $('#CLI_RUC_ESPOSO').focus();
            } else {
                $('#CLI_RUC_ESPOSO').val(datos[0]);
                $('#CLI_NOMBRE').val(datos[1]);
                $('#CLI_CASA_DIREC').val(datos[2]);
                $('#CLI_TELEF1').val(datos[3]);
                $('#CLI_CODCLIE').val(datos[4]);
                
                if (datos[5] === 'nuevo') {
                    Swal.fire('Info', 'Complete dirección y teléfono. Luego presione Guardar', 'info');
                    prepararNuevoPaciente();
                } else {
                    $('#nuevo_paciente').show();
                    $('#editar_paciente').show();
                    $('#seleccionarp').show();
                    $('#guardar_paciente').hide();
                    $('#cancelar_paciente').show();
                    $('#importarp').hide();
                    Swal.fire('Info', 'El DNI: ' + datos[0] + ' ya está registrado!', 'info');
                }
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al consultar los datos', 'error');
        },
        complete: function() {
            $('#importarp').prop('disabled', false).html('<i class="fas fa-cloud-download-alt"></i> Importar');
        }
    });
}

function validarRucDni($input) {
    let valor = $input.val().trim();
    let $importBtn = $('#importarp');
    
    if (valor.length === 8 || valor.length === 11) {
        $input.removeClass('is-invalid').addClass('is-valid');
        $importBtn.prop('disabled', false);
    } else if (valor.length > 0) {
        $input.removeClass('is-valid').addClass('is-invalid');
        $importBtn.prop('disabled', true);
    } else {
        $input.removeClass('is-valid is-invalid');
        $importBtn.prop('disabled', true);
    }
}

function validarNombre($input) {
    let nombre = $input.val().trim();
    
    if (nombre.length >= 3) {
        $input.removeClass('is-invalid').addClass('is-valid');
    } else if (nombre.length > 0) {
        $input.removeClass('is-valid').addClass('is-invalid');
    } else {
        $input.removeClass('is-valid is-invalid');
    }
}
</script>

<?= $this->endSection(); ?>
