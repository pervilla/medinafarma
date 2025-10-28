<?php

/** @var CodeIgniter\View\View $this*/ ?>
<?= $this->extend('templates/admin_template') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="content-header">
    </div>
    <div id="statusMessage" style="display: none;" class="alert alert-info"></div>

    <div class="row">
        <div class="col-12">
            <div id="caja_documentos" class="card shadow">
                <div class="card-header">
                    <div class="row align-items-center w-100 mx-0">
                        <!-- Título -->
                        <div class="col-6">
                            <h3 class="card-title mb-0">FACTURA</h3>
                        </div>
                        <!-- Grupo de herramientas -->
                        <div class="col-6">
                            <div class="card-tools">
                                <div class="input-group input-group-sm">
                                    <!-- Botones Izquierda -->
                                    <div class="input-group-prepend">
                                        <button class="btn btn-primary btn-sm" type="button" data-toggle="modal" data-target="#importarSireModal">Importar Sire</button>
                                        <button class="btn btn-primary btn-sm" type="button" data-toggle="modal" data-target="#uploadModal">Upload</button>
                                        <button class="btn btn-dark btn-sm" type="button"><i class="fa fa-qrcode"></i></button>
                                    </div>

                                    <!-- Select Proveedor -->
                                    <select id="b_cliente" name="b_cliente" class="form-control form-control-sm" data-placeholder="Buscar Proveedor" data-allow-clear="1"></select>

                                    <!-- Rango de Fechas -->
                                    <div id="reportrange" class="form-control form-control-sm">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>

                                    <!-- Botón Buscar -->
                                    <div class="input-group-append">
                                        <button id="bListarDoc" class="btn btn-danger btn-sm" type="button">Buscar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="table_documentos" class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>RUC</th>
                                <th>NOMBRE</th><th>TIPO</th>
                                <th>NRO_FACTURA</th>
                                <th>FECHA</th>
                                <th>TOTAL</th>
                                <th>GUIA</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div id="caja_detalle" class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">DETALLE FACTURA</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <div class="input-group-prepend">
                                <button class="btn btn-default">
                                    <b>S/.</b>
                                </button>
                            </div>
                            <input type="text" id="table_total" name="table_total" class="form-control float-right" placeholder="Total">
                            <div class="input-group-append">
                                <button class="btn btn-default">
                                    <i class="fa fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-file"></i></span>
                                </div>
                                <input type="text" class="form-control" id="fac_cod" name="fac_cod" placeholder="Codigo" aria-label="Codigo">
                                <input type="text" class="form-control" id="fac_cli" name="fac_cli" placeholder="Cliente" aria-label="Cliente">
                                <input type="text" class="form-control" id="fac_ser" name="fac_ser" placeholder="Serie" aria-label="Serie">
                                <input type="text" class="form-control" id="fac_num" name="fac_num" placeholder="Numero" aria-label="Numero">
                                <input type="text" class="form-control" id="fac_tot" name="fac_tot" placeholder="Total" aria-label="Total">

                                <div class="input-group-append">
                                    <button class="btn btn-default">
                                        <i class="fa fa-cart-plus"></i>
                                    </button>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn bg-primary" type="button" id="btn_promedio">Promedio</button>
                                    <button class="btn bg-info" type="button" id="btn_descuento">Desc.B-A</button>
                                    <button class="btn btn-warning" type="button" id="btn_excluir">Excluir A</button>
                                    <button class="btn btn-secondary" type="button" id="btn_flete">A.Flete</button>
                                    <button class="btn btn-danger" type="button" id="btn_eliminar">Elim.Item</button>
                                    <button class="btn btn-success btn-outline" type="button" id="btn_crear_compra">Ingresar Compra</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">

                    <table id="table_detalle" class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>COD.PROV</th>
                                <th>DESCRIPCION FACTURA</th>
                                <th>ACT</th>
                                <th>DESCRIPCION MEDINA</th>
                                <th>C.MED</th>
                                <th>FAR_EQUIV</th>
                                <th>CANT</th>
                                <th>PU</th>
                                <th>CoAn</th>
                                <th>TOTAL</th>
                                <th>A</th>
                                <th>B</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <div class="card-footer"></div>
            </div>
        </div>
    </div>

</div>


<div class="modal fade" id="importarSireModal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-primary">
            <div class="overlay" style="display: none;">
                <i class="fas fa-2x fa-sync fa-spin"></i>
            </div>
            <div class="modal-header">
                <h4 class="modal-title">Importar Comprobantes</br>desde Sire</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-file"></i></span>
                    </div>
                    <input type="text" class="form-control col-1" id="codEstado" name="codEstado" value="0" disabled>
                    <input type="text" class="form-control col-1" id="codDocIde" name="codDocIde" value="6" disabled>
                    <select class="form-control col-2" id="tipoDoc" name="tipoDoc">
                        <option value="01">01 - FACTURA</option>
                        <option value="F7">F7 - NOTA DE CREDITO</option>
                    </select>
                    <div id="reportrange3" class="form-control">
                        <i class="fa fa-calendar"></i>&nbsp;
                        <span></span> <i class="fa fa-caret-down"></i>
                    </div>
                    <div class="input-group-append">
                        <button class="btn btn-danger" id="bimpsire" type="button">Importar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

<div class="modal fade" id="uploadModal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-primary">
            <div class="overlay" style="display: none;">
                <i class="fas fa-2x fa-sync fa-spin"></i>
            </div>
            <div class="modal-header">
                <h4 class="modal-title">Importar Factura/Boleta de SUNAT</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">

                <form id="uploadForm" action="<?= site_url('importar/procesarFactura') ?>" method="post" enctype="multipart/form-data">
                    <input type="file" name="factura_html" required>
                    <button type="submit">Procesar Factura</button>
                </form>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="modal-producto" data-keyboard="false" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="input-group input-group-sm mb-12">
                    <div class="input-group-prepend">
                        <button type="button" class="btn btn-primary">Producto </button>
                    </div>
                    <input type="text" class="form-control" id="busqueda" autocomplete="off" placeholder="Producto (Use '%' para caracter o palabra desconocida)">

                    <span class="input-group-append">
                        <button type="button" id="buscar" name="buscar" class="btn btn-primary"><i class="fa fa-search"> </i> Buscar</button>
                    </span>
                </div>
                <input type="hidden" id="imp_ruc_f" name="imp_ruc_f">
                <input type="hidden" id="imp_cod" name="imp_cod">

            </div>
            <div style="margin-left:5px;margin-right:5px;" class="card-body p-0">
                <table id="productos_centro" class="display compact nowrap dataTable no-footer dtr-inline collapsed table-bordered table-striped" style="width: 100%; background-color:white;">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Productos</th>
                            <th>Und</th>
                            <th>Pqt</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-equivalencia" data-keyboard="false" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="input-group input-group-sm mb-12">
                    <div class="input-group-prepend">
                        <button type="button" class="btn btn-primary">Equivalencia </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <label class="input-group-text">EQUIV.</label>
                            </div>
                            <select class="custom-select" id="inputGroupEquiv">
                                <option selected>Seleccione</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <label class="input-group-text">PRECIO</label>
                            </div>
                            <input type="text" class="form-control" id="txt_precio_ini" readonly>
                            <label class="input-group-text form-control text-center">÷</label>
                            <input type="text" class="form-control" id="txt_factor">
                            <label class="input-group-text form-control text-center">=</label>
                            <div class="input-group-append">
                                <input type="text" class="form-control" id="txt_precio_fin" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-light" id="bequiv">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('footer'); ?>
<!-- Select2 -->
<link rel="stylesheet" href="../../plugins/select2/css/select2.min.css" />
<link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css" />
<!-- DataTables <link rel="stylesheet" href="../../plugins/datatables/jquery.dataTables.min.css">-->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

<link rel="stylesheet" href="<?= site_url('../../plugins/datatables-keytable/css/keyTable.dataTables.min.css') ?>">
<link rel="stylesheet" href="../../plugins/jquery-confirm/css/jquery-confirm.css">

<link rel="stylesheet" href="../../plugins/sweetalert2/sweetalert2.min.css">

<!-- Select2 -->
<script src="../../plugins/select2/js/select2.full.min.js"></script>
<script src="../../plugins/bootstrap-switch/js/bootstrap-switch.js"></script>
<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<script src="<?= site_url('../../plugins/datatables-keytable/js/dataTables.keyTable.min.js') ?>"></script>
<script src="../../plugins/jquery-confirm/js/jquery-confirm.js"></script>
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>
<script>
    var idfact = 0;
    var idart = 0;
    var canti = 0;
    var equiv = 1;
    var idscmb = [];
    var idcmbb = 0;
    $(document).on('keydown', function(event) {
        if (event.key == "Escape" && $('#modal-producto').is(':visible')) {
            $("#busqueda").val('');
            $("#busqueda").focus();
        }
    });
    $(document).ready(function() {
        // Interceptar el envío del formulario
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault(); // Evitar el envío normal del formulario

            // Mostrar el overlay de carga
            $('.overlay').show();

            // Crear un FormData para enviar el archivo
            var formData = new FormData(this);

            // Enviar el formulario mediante AJAX
            $.ajax({
                url: $(this).attr('action'), // URL del formulario
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Ocultar el overlay de carga
                    $('.overlay').hide();

                    // Convertir la respuesta a JSON (si es necesario)
                    var data = JSON.parse(response);

                    // Verificar si la respuesta es exitosa
                    if (data.status === 200) {
                        // Recargar la tabla DataTable
                        if (typeof dtablefac !== 'undefined' && dtablefac.ajax.reload) {
                            dtablefac.ajax.reload(null, false); // Recargar sin resetear la paginación
                        }

                        // Cerrar el modal
                        $('#uploadModal').modal('hide');

                        // Mostrar un mensaje de éxito (opcional)
                        alert('Factura procesada con éxito.');
                    } else {
                        // Mostrar un mensaje de error
                        alert('Error: ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    // Ocultar el overlay de carga
                    $('.overlay').hide();

                    // Mostrar un mensaje de error
                    alert('Error al procesar la factura: ' + error);
                }
            });
        });
    });
    $(document).ready(function() {
        // Función para inicializar un daterangepicker
        function initializeDateRangePicker(selector, startDate, endDate, callback) {
            $(selector).daterangepicker({
                startDate: startDate,
                endDate: endDate,
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 Días': [moment().subtract(29, 'days'), moment()],
                    'Este Mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    "format": "DD/MM/YYYY",
                    "separator": " - ",
                    "applyLabel": "Aplicar",
                    "cancelLabel": "Cancelar",
                    "fromLabel": "Desde",
                    "toLabel": "Hasta",
                    "customRangeLabel": "Personalizado",
                    "weekLabel": "W",
                    "daysOfWeek": ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
                    "monthNames": ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
                    "firstDay": 1
                }
            }, callback);

            // Ejecutar el callback inicial para mostrar las fechas seleccionadas
            callback(startDate, endDate);
        }

        // Callback para actualizar el texto del rango de fechas
        function updateDateRangeText(start, end, targetSelector) {
            $(targetSelector).html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
        }

        // Fechas iniciales
        var start = moment().subtract(15, 'days');
        var end = moment();

        // Inicializar el primer calendario
        initializeDateRangePicker('#reportrange', start, end, function(start, end) {
            updateDateRangeText(start, end, '#reportrange span');
        });

        // Inicializar un segundo calendario (ejemplo)
        initializeDateRangePicker('#reportrange2', start, end, function(start, end) {
            updateDateRangeText(start, end, '#reportrange2 span');
        });
        // Inicializar un segundo calendario (ejemplo)
        initializeDateRangePicker('#reportrange3', start, end, function(start, end) {
            updateDateRangeText(start, end, '#reportrange3 span');
        });

        // Evento click del botón #bimpsire
        $(document).ready(function() {
            $('#bimpsire').on('click', function() {
                var $btn = $(this); // Guardar referencia al botón
                var codEstado = $('#codEstado').val().trim();
                var codDocIde = $('#codDocIde').val().trim();
                var tipoDoc = $('#tipoDoc').val().trim();

                // Obtener fechas del daterangepicker
                var dateRangePicker = $('#reportrange3').data('daterangepicker');
                if (!dateRangePicker) {
                    Swal.fire("Error", "No se pudo obtener el rango de fechas.", "error");
                    return;
                }

                var fechaInicio = moment(dateRangePicker.startDate).format('DD/MM/YYYY');
                var fechaFin = moment(dateRangePicker.endDate).format('DD/MM/YYYY');

                // Validar campos vacíos
                if (!codEstado || !codDocIde || !tipoDoc || !fechaInicio || !fechaFin) {
                    Swal.fire("Atención", "Por favor, complete todos los campos.", "warning");
                    return;
                }

                // Deshabilitar botón y mostrar mensaje de carga
                $btn.prop('disabled', true).text('Procesando...');

                // Enviar solicitud AJAX
                $.ajax({
                    url: 'importar/listaComprasSire',
                    type: 'POST',
                    data: {
                        codEstado: codEstado,
                        codDocIde: codDocIde,
                        tipoDoc: tipoDoc,
                        fecEmisionIni: fechaInicio,
                        fecEmisionFin: fechaFin
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: "Procesando...",
                            text: "Por favor, espere mientras se importan los datos.",
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        tablePrinFact.ajax.reload();
                        $('#importarSireModal').modal('hide');
                        Swal.fire("Éxito", "Datos procesados correctamente.", "success");
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        Swal.fire("Error", "Hubo un problema al procesar la solicitud. Inténtelo de nuevo.", "error");
                    },
                    complete: function() {
                        // Habilitar botón nuevamente
                        $btn.prop('disabled', false).text('Importar desde SUNAT');
                    }
                });
            });
        });
        $('#b_cliente').select2({
            theme: 'bootstrap4',
            width: $('#b_cliente').data('width') ? $('#b_cliente').data('width') : $('#b_cliente').hasClass('w-100') ? '100%' : 'style',
            placeholder: "Buscar Proveedor",
            allowClear: Boolean($('#b_cliente').data('allow-clear')),
            closeOnSelect: !$('#b_cliente').attr('multiple'),
            ajax: {
                url: "<?= site_url('personas/get_proveedores') ?>",
                dataType: "json",
                processResults: function(data) {
                    return {
                        results: data,
                    };
                },
            },

            escapeMarkup: function(markup) {
                return markup;
            },
        });
        $('#b_cliente').addClass('form-control col-2');
        $("#btn_uploadfactura").click(function() {
            $.confirm({
                title: 'Subir factura',
                icon: 'fa fa-warning',
                type: 'green',
                content: '' +
                    '<form action="" class="formName">' +
                    '<div class="form-group">' +
                    '<label>Subir una factura</label>' +
                    '<input type="file" placeholder="Factura" class="facturaup form-control" required />' +
                    '</div>' +
                    '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-blue',
                        action: function() {
                            var facturaup = this.$content.find('.facturaup').val();
                            if (!facturaup) {
                                $.alert('Agregar un archivo');
                                return false;
                            }
                            $.post('importar/agregar_factura_up', {
                                idfact: idfact,
                                vflete: flete
                            }, function(htmlexterno) {
                                dtablefac.ajax.reload();
                            });
                        }
                    },
                    cancel: function() {
                        //close
                    },
                },
                onContentReady: function() {
                    // bind to events
                    var jc = this;
                    this.$content.find('form').on('submit', function(e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });
        });
        var tablePrinFact = $('#table_documentos').DataTable({
            ajax: {
                url: "<?= site_url('importar/listarDocumentos') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    cliente: function() {
                        return $("select#b_cliente option:checked").val();
                    },
                    startDate: function() {
                        return moment($('#reportrange').data('daterangepicker').startDate).format('DD/MM/YYYY');
                    },
                    endDate: function() {
                        return moment($('#reportrange').data('daterangepicker').endDate).format('DD/MM/YYYY');
                    }
                }
            },
            columns: [{
                    data: 'ID'
                },
                {
                    data: 'RUC'
                },
                {
                    data: 'desRazonSocialEmis'
                },
                {
                    data: 'codCpe',
                    render: function(data, type, row) {
                        const tipos = {
                            '01': 'Factura',
                            '02': 'Boleta',
                            '03': 'Boleta de Venta',
                            '07': 'Nota de Crédito',
                            '08': 'Nota de Débito',
                            '09': 'Guía de Remisión',
                            '20': 'Comprobante de Retención'
                        };
                        return tipos[data] || 'Otro Comprobante';
                    }
                },                {
                    data: 'NRO_FACTURA'
                },
                {
                    data: 'FECHA'
                },
                {
                    data: 'TOTAL'
                },
                {
                    data: 'NRO_GUIA'
                },

                {
                    data: 'NRO_GUIA',
                    render: function(data, type, row, meta) {
                        var rpt = '<div class="btn-group btn-group-sm" role="group" aria-label="Acciones">';
                        rpt = rpt + '<button type="button" class="btn ' + (row.ESTADO === 10 ? 'btn-primary btn-imp-comp' : 'btn-secondary') + '"><i class="nav-icon fas fa-arrow-circle-down"></i> Importar</button>';
                        rpt = rpt + '<button type="button" class="btn ' + (row.TOTAL === null ? 'btn-secondary' : 'btn-info btn-ver-comp') + '"><i class="nav-icon fas fa-eye"></i> Ver</button>';
                        rpt = rpt + '<button type="button" class="btn btn-warning btn-visor-comp"><i class="nav-icon fas fa-file-alt"></i> Visor</button>';
                        rpt = rpt + '<button type="button" class="btn ' + (row.NRO_GUIA === null ? 'btn-secondary' : 'btn-danger btn-del-comp') + '"><i class="nav-icon fas fa-trash"></i> Eliminar</button>';
                        rpt = rpt + "</div>";
                        return rpt
                    }
                },

            ],
            fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                if (aData.ESTADO == 1) {
                    $(nRow).addClass('bg-success');
                }
                if (aData.ESTADO == 0) {
                    $(nRow).addClass('bg-warning');
                }
                return nRow;
            },
            order: [
                [0, 'desc']
            ],
            searching: false,
            paging: true,
            orderable: false,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            select: true
        });
        $('#table_documentos tbody').on('click', '.btn-imp-comp', function(event) {
            var data = tablePrinFact.row($(this).parents('tr')).data();

            $.confirm({
                title: 'Importar Detalle Comprobante',
                icon: 'fa fa-warning',
                content: '<b>¿Vas a importar el detalle del comprobante desde la Sunat?</b>',
                type: 'blue',
                buttons: {
                    ok: {
                        text: "Si, Importar",
                        btnClass: 'btn-primary',
                        keys: ['enter'],
                        action: function() {
                            // Mostrar mensaje de "Procesando..."
                            $.alert({
                                title: 'Procesando...',
                                content: 'Por favor, espera mientras se importa el comprobante.',
                                type: 'orange',
                                icon: 'fa fa-spinner fa-spin',
                                closeIcon: false,
                                buttons: {}
                            });

                            // Enviar la petición al servidor
                            $.post('importar/importaComprobanteSunat', {
                                ruc: data['RUC'],
                                nro: data['NRO_FACTURA'],
                                cod: data['codCpe']
                            }, function(response) {
                                // Cerrar mensaje de "Procesando..."
                                $('.jconfirm').remove();

                                if (response.status === 200) {
                                    $.alert({
                                        title: 'Éxito',
                                        content: response.message,
                                        type: 'green',
                                        icon: 'fa fa-check',
                                        buttons: {
                                            ok: function() {
                                                // Recargar la tabla después de la importación
                                                tablePrinFact.ajax.reload();
                                            }
                                        }
                                    });
                                } else {
                                    $.alert({
                                        title: 'Error',
                                        content: response.message || 'Ocurrió un error al importar el comprobante.',
                                        type: 'red',
                                        icon: 'fa fa-exclamation-triangle'
                                    });
                                }
                            }).fail(function() {
                                // Manejo de error si falla la petición AJAX
                                $('.jconfirm').remove();
                                $.alert({
                                    title: 'Error',
                                    content: 'No se pudo conectar con el servidor. Inténtalo de nuevo.',
                                    type: 'red',
                                    icon: 'fa fa-exclamation-triangle'
                                });
                            });
                        }
                    },
                    cancel: function() {}
                }
            });
        });

        $('#table_documentos tbody').on('click', '.btn-ver-comp', function(event) {

            var data = tablePrinFact.row($(this).parents('tr')).data();
            idfact = data['ID'];
            dtablefac.ajax.reload();
            $("#imp_ruc_f").val(data['CLI_CODCLIE']);
            $("#fac_cod").val(data['CLI_CODCLIE']);
            $("#fac_cli").val(data['CLI_NOMBRE']);
            $("#fac_ser").val(data['ALL_NUMSER']);
            $("#fac_num").val(data['ALL_NUMFACT']);
            $("#fac_tot").val(data['TOTAL']);
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
            } else {
                tablePrinFact.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        });

        $('#table_documentos tbody').on('click', '.btn-visor-comp', function(event) {
            var data = tablePrinFact.row($(this).parents('tr')).data();
            data['codCpe']=data['codCpe']=='07'?'F7':data['codCpe'];
            var ruc = data['RUC'];
            var tipoDoc = data['codCpe'] || '01';
            var nroFactura = data['NRO_FACTURA'];
            
            var url = '<?= site_url('importar/visorComprobante') ?>?ruc=' + ruc + 
                      '&tipoDoc=' + tipoDoc + '&nroFactura=' + nroFactura;
            
            // Abrir en nueva ventana
            window.open(url, '_blank', 'width=1000,height=700,scrollbars=yes,resizable=yes');
        });

        var dtablefac = $('#table_detalle').DataTable({
            ajax: {
                url: "<?= site_url('importar/listarDetalleDocumentos') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    id: function() {
                        return idfact;
                    }
                }
            },
            columns: [{
                    data: 'ID'
                },
                {
                    data: 'COD_PROD'
                },
                {
                    data: 'DES_PROD'
                },
                {
                    data: 'TOTAL_SIST',
                    render: function(data, type, row, meta) {
                        return '<a class="openPopup" id="verProd"><i class="fas fa-eye"></i></a>';
                    }
                },
                {
                    data: 'ART_NOMBRE'
                },
                {
                    data: 'ART_KEY'
                },
                {
                    data: 'FAR_EQUIV',
                    render: function(data, type, row, meta) {
                        return '<a class="openPopup" id="verCant"><i class="fa fa-cubes"></i> ' + data + '</a>';
                    }
                },
                {
                    data: 'CANTIDAD',
                    render: function(data, type, row, meta) {
                        var cantidad = Math.round(data * 1000) / 1000;
                        return cantidad;
                    }
                },
                {
                    data: 'PRECIO',
                    render: $.fn.dataTable.render.number(',', '.', 2, 'S/. ')
                },
                {
                    data: 'ARM_COSPRO',
                    render: function(data, type, row, meta) {
                        return (Math.round(data * row.FAR_EQUIV * 1000) / 1000);
                    }

                },
                {
                    data: 'TOTAL_SIST',
                    render: function(data, type, row, meta) {
                        return "<input type='text' id='TOT_" + row.ID + "' class='form-control form-control-sm rowTotal' style='width:60px;' value='" +
                            Math.round(data * 1000) / 1000 + "'>"
                    }
                },
                {
                    data: 'ID',
                    sortable: false,
                    render: function(data, type, row, meta) {
                        return '<input class="check_a" type="checkbox" name="group1" value="' + data + '"/>';
                    }
                },
                {
                    data: 'ID',
                    sortable: false,
                    render: function(data, type, row, meta) {
                        return '<input class="check_b" type="checkbox" name="group2" value="' + data + '"/>';
                    }
                }
            ],
            footerCallback: function(row, data, start, end, display) {
                let api = this.api();
                let intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i :
                        0;
                };
                total = api
                    .column(10)
                    .data()
                    .reduce((a, b) => intVal(a) + intVal(b), 0);
                $("#table_total").val(total);
            },
            fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var min = aData.ARM_COSPRO * aData.FAR_EQUIV * 0.85;
                var max = aData.ARM_COSPRO * aData.FAR_EQUIV * 1.15;
                
                // Agregar alertas para producto inactivo o equivalencia inexistente
                var alertas = '';
                if (aData.ART_SITUACION == 1) {
                    alertas += '<i class="fas fa-exclamation-triangle text-danger" title="Producto Inactivo"></i> ';
                }
                if (aData.EQUIV_EXISTE == 0 && aData.ART_KEY) {
                    alertas += '<i class="fas fa-question-circle text-warning" title="Equivalencia no existe en PRECIOS"></i> ';
                }
                if (alertas) {
                    $(nRow).find('td:eq(3)').prepend(alertas);
                }
                
                if (aData.ESTADO == 0) {
                    $(nRow).addClass('bg-warning');
                } else if (max > aData.PRECIO && aData.PRECIO > min) {
                    $(nRow).addClass('bg-light');
                } else {
                    $(nRow).addClass('bg-danger');
                }

                return nRow;
            },
            searching: false,
            paging: false,
            responsive: true,
            lengthChange: false,
            autoWidth: false

        });
        $('#table_detalle tbody').on('click', '.check_a', function() {
            var ids = [];
            $('input[name="group1"]:checked').each(function() {
                ids.push(this.value);
                idscmb = ids;
            });
        });

        $('#table_detalle tbody').on('click', '.check_b', function() {
            $('input[name="group2"]').not(this).prop('checked', false);
            idcmbb = this.value;
            var rowData = dtablefac.row($(this).parents('tr')).data();
            canti = rowData.CANTIDAD;
        });

        var dtable = $('#productos_centro').DataTable({
            ajax: {
                url: "<?= site_url('productos/get_productos') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    busqueda: function() {
                        return $("input#busqueda").val();
                    }
                }
            },
            columns: [{
                    data: 'ARM_CODART',
                    orderable: false
                },
                {
                    data: 'ART_NOMBRE'
                },
                {
                    data: 'ART_PQT',
                    render: function(data, type, row, meta) {
                        return (Math.round(data * 1000) / 1000);
                    },
                    orderable: false
                },
                {
                    data: 'ART_UNID',
                    render: function(data, type, row, meta) {
                        return (Math.round(data * 1000) / 1000);
                    },
                    orderable: false
                },
                {
                    data: 'ARM_CODART',
                    render: function(data, type, row, meta) {
                        return "<button id='selprod' class='btn btn-block bg-gradient-primary btn-xs'><i class='fas fa-pills'></i> Seleccionar</button>"
                    }
                }

            ],
            fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                if (aData.StockGen > 0) {
                    $(nRow).addClass('bg-success');
                }
                return nRow;
            },
            searching: false,
            paging: true,
            orderable: false,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            select: true,
            ordering: false,
            oLanguage: {
                "sInfo": " "
            },
            keys: true,
            //dom: 'Bfrt<"col-md-6 inline"i> <"col-md-6 inline"p>',

        });
        dtable.on('key', function(e, datatable, key, cell, originalEvent) {
            if (key == 13) { // Si se presiona Enter
                var rowData = datatable.row(cell.index().row).data();

                $.ajax({
                    url: "<?= site_url('importar/update_producto') ?>",
                    type: "POST",
                    dataType: "json", // Asegura que esperamos JSON
                    data: {
                        cli: $("#imp_ruc_f").val(),
                        cod: $.trim($("#imp_cod").val()),
                        art: rowData['ARM_CODART']
                    },
                    success: function(response) {
                        // Manejo basado en el JSON recibido
                        let color = response.status == 200 ? 'bg-success' : 'bg-danger';

                        $(document).Toasts('create', {
                            class: color,
                            title: 'Producto',
                            body: response.message,
                            position: 'bottomRight',
                            icon: 'far fa-check-circle fa-lg',
                            animation: true,
                            autohide: true,
                            delay: 2500
                        });

                        if (response.status == 200) {
                            dtablefac.ajax.reload();
                            $('#modal-producto').modal('hide');
                        }
                    },
                    error: function(xhr, status, error) {
                        $(document).Toasts('create', {
                            class: 'bg-danger',
                            title: 'Error',
                            body: 'Error en la conexión con el servidor',
                            position: 'bottomRight',
                            icon: 'fas fa-exclamation-circle',
                            autohide: true,
                            delay: 2500
                        });
                    }
                });
            }

            if (key == 27) { // Si se presiona Escape
                $("#busqueda").focus();
            }
        });
        $('#productos_centro tbody').on('click', '#selprod', function(event) {
            var rowData = dtable.row($(this).parents('tr')).data();
            $('#modal-producto').modal('hide');
            $.post(
                "<?= site_url('importar/update_producto') ?>", {
                    cli: $("#imp_ruc_f").val(),
                    cod: $.trim($("#imp_cod").val()),
                    art: rowData.ARM_CODART
                },
                function(rpta) {
                    const myArray = rpta.split("|");
                    color = myArray[0] == 0 ? 'bg-danger' : 'bg-success';
                    $(document).Toasts('create', {
                        class: color,
                        title: 'Producto',
                        body: myArray[1],
                        position: 'bottomRight',
                        icon: 'far fa-check-circle fa-lg',
                        animation: true,
                        autohide: true,
                        delay: 2500
                    });
                    dtablefac.ajax.reload();
                    $('#modal-producto').modal('hide');
                }
            );
        });
        $('#busqueda').keydown(function(event) {
            var keyCode = (event.keyCode ? event.keyCode : event.which);
            console.log(keyCode);
            if (keyCode == 13) {
                $('#buscar').trigger('click');
            } else if (keyCode == 40) {
                dtable.cell().focus();
                $('#busqueda').blur();
            }
        });

        $('#fac_tot').keydown(function(event) {
            var keyCode = (event.keyCode ? event.keyCode : event.which);
            console.log(this.value);
            if (keyCode == 13) {
                $.confirm({
                    title: 'Cambiar Monto',
                    icon: 'fa fa-warning',
                    content: '<b>¿Vas a Cambiar el Monto de la Factura.?</b>',
                    type: 'blue',
                    buttons: {
                        ok: {
                            text: "Si cambiar",
                            btnClass: 'btn-primary',
                            keys: ['enter'],
                            action: function() {
                                $.post('importar/cambiar_monto', {
                                    idfact: idfact,
                                    total: $('#fac_tot').val()
                                }, function(htmlexterno) {
                                    dtablefac.ajax.reload();
                                });
                            }
                        },
                        cancel: function() {}
                    }
                });

            }
        });

        $("#buscar").click(function() {
            dtable.ajax.reload();
        });

        $("#bListarDoc").click(function() {
            tablePrinFact.ajax.reload();
        });


        $("#btn_promedio").click(function() {
            $.confirm({
                title: 'Promediar Costos',
                icon: 'fa fa-warning',
                content: '<b>¿Vas a promediar el precio de venta.?</b>',
                type: 'blue',
                buttons: {
                    ok: {
                        text: "Si promediar",
                        btnClass: 'btn-primary',
                        keys: ['enter'],
                        action: function() {
                            $.post('importar/promediar_costos', {
                                idfact: idfact,
                                ids: idscmb
                            }, function(htmlexterno) {
                                dtablefac.ajax.reload();
                            });
                        }
                    },
                    cancel: function() {}
                }
            });
        });
        $("#btn_flete").click(function() {

            $dtotal = $('#table_total').val();
            $stotal = $('#fac_tot').val();
            $fle_su = ($stotal - $dtotal);

            $.confirm({
                title: 'Agregar Flete',
                icon: 'fa fa-warning',
                type: 'red',
                content: '' +
                    '<form action="" class="formName">' +
                    '<div class="form-group">' +
                    '<label>Agregar el total del flete</label>' +
                    '<input type="number" placeholder="Flete" class="flete form-control" value="' + $fle_su + '" required />' +
                    '</div>' +
                    '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-blue',
                        action: function() {
                            var flete = this.$content.find('.flete').val();
                            if (!flete) {
                                $.alert('Agregar un monto');
                                return false;
                            }
                            $.post('importar/agregar_flete', {
                                idfact: idfact,
                                vflete: flete
                            }, function(htmlexterno) {
                                dtablefac.ajax.reload();
                            });
                        }
                    },
                    cancel: function() {
                        //close
                    },
                },
                onContentReady: function() {
                    // bind to events
                    var jc = this;
                    this.$content.find('form').on('submit', function(e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });
        });
        $("#btn_eliminar").click(function() {
            $.confirm({
                title: 'Eliminar Items',
                icon: 'fa fa-warning',
                content: '<b>¿Vas a ELIMINAR items de la factura?</b>',
                type: 'red',
                buttons: {
                    ok: {
                        text: "Si Eliminar",
                        btnClass: 'btn-danger',
                        keys: ['enter'],
                        action: function() {
                            $.post('importar/eliminar_items', {
                                idfact: idfact,
                                ids: idscmb
                            }, function(htmlexterno) {
                                dtablefac.ajax.reload();
                            });
                        }
                    },
                    cancel: function() {}
                }
            });
        });
        $("#btn_excluir").click(function() {
            $.confirm({
                title: 'Excluir Productos',
                icon: 'fa fa-warning',
                content: '<b>¿Vas a excluir estos productos.?</b>',
                type: 'orange',
                buttons: {
                    ok: {
                        text: "Si excluir",
                        btnClass: 'btn-warning',
                        keys: ['enter'],
                        action: function() {
                            $.post('importar/excluir_productos', {
                                idfact: idfact,
                                ids: idscmb
                            }, function(htmlexterno) {
                                dtablefac.ajax.reload();
                            });
                        }
                    },
                    cancel: function() {}
                }
            });
        });

        $("#btn_descuento").click(function() {
            if (idcmbb > 0 && idscmb.length === 1) {
                $.confirm({
                    title: 'Descontar B a A',
                    icon: 'fa fa-warning',
                    content: '<b>Vas a asignar el último costo del sistema a "B" y le vas a descontar a "A".</b>',
                    type: 'purple',
                    buttons: {
                        ok: {
                            text: "Sí quiero descontar",
                            btnClass: 'btn-info',
                            keys: ['enter'],
                            action: function() {
                                $.post('importar/desc_promocion', {
                                    idfact,
                                    ids: idscmb[0],
                                    idcmbb,
                                    canti
                                }, function(response) {
                                    dtablefac.ajax.reload();
                                });
                            }
                        },
                        cancel: function() {}
                    }
                });
            } else {
                $.alert({
                    title: 'Error',
                    content: 'Debes seleccionar un solo elemento en "A" y "B" debe tener un valor mayor a 0.',
                    type: 'red',
                });
            }
        });
        $("#bequiv").click(function() {
            $.post('importar/actualizar_equiv', {
                idfact: idfact,
                codclie: $("#fac_cod").val(),
                artkey: idart,
                equiv: $("#inputGroupEquiv").find(":selected").val(),
                factr: $("#txt_factor").val()
            }, function(htmlexterno) {
                dtablefac.ajax.reload();
                $('#modal-equivalencia').modal('hide');
            });
        })
        $("#btn_crear_compra").click(function() {
            if (idfact > 0) {
                $.confirm({
                    title: 'Cerrar Caja',
                    icon: 'fa fa-warning',
                    content: '<b>¿Vas a ingresar una compra.?</b></br><blockquote class="quote-success"><h5>Atención! Revisa bien antes de hacerlo</h5><p></b></br>Gracias.</p></blockquote>',
                    type: 'green',
                    buttons: {
                        ok: {
                            text: "Si quiero hacer la compra",
                            btnClass: 'btn-primary',
                            keys: ['enter'],
                            action: function() {
                                $.post('importar/crea_compra', {
                                    idfact: idfact,
                                    codclie: $("#fac_cod").val()
                                }, function(htmlexterno) {
                                    tablePrinFact.ajax.reload();
                                });
                            }
                        },
                        cancel: function() {
                            $.alert({
                                title: 'Gracias',
                                content: 'Errar es humano, perdonar es divino, rectificar es de sabios.',
                                type: 'green',
                            });
                        }
                    }
                });
            } else {
                $.alert({
                    title: 'Error',
                    content: 'No es una factura Valida <i class="fa-regular fa-face-angry"></i>',
                    type: 'red',
                });
            }
        });
        $('#table_detalle tbody').on('click', '#verProd', function(event) {
            var data = dtablefac.row($(this).parents('tr')).data();
            //SI NO TIENE DATOS 
            $("#modal-producto").modal("show");
            $("input#imp_cod").val(data['COD_PROD']);
            $("input#busqueda").val(data['DES_PROD']);
            $('#busqueda').trigger('focus');
        });
        $('#table_detalle tbody').on('click', '#verCant', function(event) {
            var data = dtablefac.row($(this).parents('tr')).data();
            idart = data['ART_KEY'];
            equiv = data['FAR_EQUIV'];
            $.getJSON("<?= site_url('productos/get_equiv') ?>", {
                keyval: idart
            }, function(data) {
                $('#inputGroupEquiv option').remove();
                $.each(data, function(val, text) {
                    var equivo = text.PRE_EQUIV > 1 ? ' x ' + text.PRE_EQUIV : '';
                    var selected = text.PRE_EQUIV == equiv ? true : false;
                    console.log(selected);
                    $('#inputGroupEquiv').append(new Option(text.PRE_UNIDAD + equivo, text.PRE_EQUIV, false, selected));
                });
            });
            $("#modal-equivalencia").modal("show");
            $("input#txt_precio_ini").val(data['PRECIO']);
            $("input#txt_factor").val(1);
            $("input#txt_precio_fin").val(data['PRECIO']);
        });
        $('#table_detalle tbody').on('change', '.rowTotal', function() {
            /* input de cantidad */
            var data = dtablefac.row($(this).parents('tr')).data();
            vl = $(this).val();
            if (vl >= 0) {
                $.post("<?= site_url('importar/actualizaProd') ?>", {
                        id: data.ID,
                        idfact: data.IDFACT,
                        cantidad: data.CANTIDAD,
                        total: vl
                    }, /* und-val-idprod-local */
                    function(rpta) {
                        color = 'bg-success';
                        $(document).Toasts('create', {
                            class: color,
                            title: 'Producto',
                            body: 'Actualizado',
                            position: 'bottomRight',
                            icon: 'far fa-check-circle fa-lg',
                            animation: true,
                            autohide: true,
                            delay: 2500
                        });
                        dtablefac.ajax.reload();
                    }
                );
            } else {
                $.alert({
                    title: 'Error',
                    content: '<i class="fa-solid fa-face-sad-tear"></i> Monto mayor a cero.',
                    type: 'red',
                });
                $(this).val('0');
            }
        });
    });
</script>



<?= $this->endSection(); ?>