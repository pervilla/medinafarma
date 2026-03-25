<?php
/** @var CodeIgniter\View\View $this*/ ?>
<?= $this->extend('templates/admin_template') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="content-header">
        <h4><i class="fas fa-clipboard-check"></i> F-BMF-10 Registro de Recepción de Productos</h4>
    </div>

    <!-- SECCION 1: SELECCION DE FACTURAS -->
    <div class="row">
        <div class="col-12">
            <div id="caja_documentos" class="card shadow">
                <div class="card-header">
                    <div class="row align-items-center w-100 mx-0">
                        <div class="col-3">
                            <h3 class="card-title mb-0"><i class="fas fa-file-invoice"></i> Facturas con Compra Ingresada</h3>
                        </div>
                        <div class="col-9">
                            <div class="card-tools">
                                <div class="input-group input-group-sm">
                                    <!-- Select Proveedor -->
                                    <select id="b_cliente" name="b_cliente" class="form-control form-control-sm" data-placeholder="Buscar Proveedor" data-allow-clear="1"></select>

                                    <!-- Rango de Fechas -->
                                    <div id="reportrange" class="form-control form-control-sm">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>

                                    <!-- Botones -->
                                    <div class="input-group-append">
                                        <button id="bListarDoc" class="btn btn-info btn-sm" type="button"><i class="fas fa-search"></i> Buscar</button>
                                        <button id="btnGenerarReporte" class="btn btn-success btn-sm" type="button" disabled><i class="fas fa-file-pdf"></i> Generar Reporte</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="table_documentos" class="table table-striped table-valign-middle table-sm">
                        <thead>
                            <tr>
                                <th style="width:30px;"><input type="checkbox" id="checkAll"></th>
                                <th>ID</th>
                                <th>RUC</th>
                                <th>PROVEEDOR</th>
                                <th>NRO FACTURA</th>
                                <th>FECHA</th>
                                <th>TOTAL</th>
                                <th>ESTADO</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <small class="text-muted">Seleccione facturas del mismo proveedor para generar el reporte. Las facturas ya incluidas en un reporte aparecen deshabilitadas.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCION 2: REPORTES GENERADOS -->
    <div class="row">
        <div class="col-12">
            <div id="caja_reportes" class="card shadow">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list-alt"></i> Reportes Generados</h3>
                    <div class="card-tools">
                        <button id="btnRefreshReportes" class="btn btn-sm btn-default"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="table_reportes" class="table table-striped table-valign-middle table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>PROVEEDOR</th>
                                <th>RUC</th>
                                <th>FACTURAS</th>
                                <th>FECHA RECEPCIÓN</th>
                                <th>GENERADO</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('footer'); ?>
<!-- Select2 -->
<link rel="stylesheet" href="../../plugins/select2/css/select2.min.css" />
<link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css" />
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/sweetalert2/sweetalert2.min.css">

<!-- Select2 -->
<script src="../../plugins/select2/js/select2.full.min.js"></script>
<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<!-- pdfmake -->
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<!-- SweetAlert2 -->
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
    var selectedFacturas = [];
    var selectedProveedor = null;

    $(document).ready(function() {

        // ===== DATERANGEPICKER =====
        var start = moment().subtract(30, 'days');
        var end = moment();

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Hoy': [moment(), moment()],
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
                "daysOfWeek": ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
                "monthNames": ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
                "firstDay": 1
            }
        }, function(s, e) {
            $('#reportrange span').html(s.format('DD/MM/YYYY') + ' - ' + e.format('DD/MM/YYYY'));
        });
        $('#reportrange span').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));

        // ===== SELECT2 PROVEEDOR =====
        $('#b_cliente').select2({
            theme: 'bootstrap4',
            width: '250px',
            placeholder: "Buscar Proveedor",
            allowClear: true,
            ajax: {
                url: "<?= site_url('personas/get_proveedores') ?>",
                dataType: "json",
                processResults: function(data) {
                    return { results: data };
                },
            },
            escapeMarkup: function(markup) { return markup; },
        });

        // ===== DATATABLE FACTURAS =====
        var tableFact = $('#table_documentos').DataTable({
            ajax: {
                url: "<?= site_url('recepcionProductos/listarDocumentos') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    cliente: function() { return $("select#b_cliente option:checked").val(); },
                    startDate: function() { return moment($('#reportrange').data('daterangepicker').startDate).format('DD/MM/YYYY'); },
                    endDate: function() { return moment($('#reportrange').data('daterangepicker').endDate).format('DD/MM/YYYY'); }
                }
            },
            columns: [
                {
                    data: 'ID',
                    orderable: false,
                    render: function(data, type, row) {
                        if (row.EN_REPORTE == 1) {
                            return '<input type="checkbox" class="chk-factura" disabled title="Ya incluida en reporte #' + row.ID_REPORTE + '">';
                        }
                        return '<input type="checkbox" class="chk-factura" value="' + data + '" data-ruc="' + row.RUC + '" data-nrofact="' + row.NRO_FACTURA + '" data-codclie="' + (row.CLI_CODCLIE || '') + '" data-nombre="' + (row.CLI_NOMBRE || row.desRazonSocialEmis || '') + '">';
                    }
                },
                { data: 'ID' },
                { data: 'RUC' },
                {
                    data: 'CLI_NOMBRE',
                    render: function(data, type, row) {
                        return data || row.desRazonSocialEmis || '';
                    }
                },
                { data: 'NRO_FACTURA' },
                { data: 'FECHA' },
                {
                    data: 'TOTAL',
                    render: $.fn.dataTable.render.number(',', '.', 2, 'S/. ')
                },
                {
                    data: 'EN_REPORTE',
                    render: function(data, type, row) {
                        if (data == 1) {
                            return '<span class="badge badge-secondary"><i class="fas fa-lock"></i> En Reporte #' + row.ID_REPORTE + '</span>';
                        }
                        return '<span class="badge badge-success"><i class="fas fa-check"></i> Disponible</span>';
                    }
                }
            ],
            fnRowCallback: function(nRow, aData) {
                if (aData.EN_REPORTE == 1) {
                    $(nRow).addClass('text-muted').css('opacity', '0.6');
                }
                return nRow;
            },
            order: [[1, 'desc']],
            searching: false,
            paging: true,
            pageLength: 25,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            language: {
                emptyTable: "No hay facturas para mostrar. Use los filtros y presione Buscar.",
                info: "Mostrando _START_ a _END_ de _TOTAL_ facturas",
                paginate: { previous: "Anterior", next: "Siguiente" }
            }
        });

        // ===== CHECKBOX LOGICA =====
        // Seleccionar/deseleccionar todos
        $('#checkAll').on('change', function() {
            var checked = this.checked;
            $('#table_documentos tbody .chk-factura:not(:disabled)').each(function() {
                // Solo marcar los del mismo proveedor si ya hay uno seleccionado
                if (selectedProveedor && $(this).data('ruc') !== selectedProveedor) return;
                this.checked = checked;
            });
            updateSelectedFacturas();
        });

        // Controlar selección individual
        $('#table_documentos tbody').on('change', '.chk-factura', function() {
            updateSelectedFacturas();
        });

        function updateSelectedFacturas() {
            selectedFacturas = [];
            selectedProveedor = null;
            var proveedorNombre = '';
            var proveedorCodclie = '';

            $('#table_documentos tbody .chk-factura:checked').each(function() {
                var ruc = $(this).data('ruc');
                var nroFact = $(this).data('nrofact');
                var id = $(this).val();
                var codclie = $(this).data('codclie');
                var nombre = $(this).data('nombre');

                if (!selectedProveedor) {
                    selectedProveedor = ruc;
                    proveedorNombre = nombre;
                    proveedorCodclie = codclie;
                }

                if (ruc === selectedProveedor) {
                    selectedFacturas.push({
                        id: id,
                        nro_factura: nroFact,
                        ruc: ruc,
                        nombre: nombre,
                        codclie: codclie
                    });
                } else {
                    // Deseleccionar facturas de otro proveedor
                    $(this).prop('checked', false);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Proveedor diferente',
                        text: 'Solo puede seleccionar facturas del mismo proveedor.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });

            // Deshabilitar checkboxes de otros proveedores
            if (selectedProveedor) {
                $('#table_documentos tbody .chk-factura:not(:disabled)').each(function() {
                    if ($(this).data('ruc') !== selectedProveedor && !$(this).is(':checked')) {
                        $(this).prop('disabled', true);
                    }
                });
            } else {
                // Rehabilitar todos si no hay selección
                $('#table_documentos tbody .chk-factura').each(function() {
                    if (!$(this).attr('title')) { // No rehabilitar los que están en reporte
                        $(this).prop('disabled', false);
                    }
                });
            }

            // Habilitar/deshabilitar botón generar
            $('#btnGenerarReporte').prop('disabled', selectedFacturas.length === 0);
        }

        // ===== BUSCAR =====
        $("#bListarDoc").click(function() {
            selectedFacturas = [];
            selectedProveedor = null;
            $('#checkAll').prop('checked', false);
            $('#btnGenerarReporte').prop('disabled', true);
            tableFact.ajax.reload();
        });

        // ===== GENERAR REPORTE =====
        $("#btnGenerarReporte").click(function() {
            if (selectedFacturas.length === 0) {
                Swal.fire('Atención', 'Seleccione al menos una factura.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Generar Reporte de Recepción',
                html: '<p>Se generará el reporte F-BMF-10 con <b>' + selectedFacturas.length + '</b> factura(s) del proveedor <b>' + selectedFacturas[0].nombre + '</b>.</p><p>Estas facturas quedarán marcadas y no podrán usarse en futuros reportes.</p>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Generar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "<?= site_url('recepcionProductos/generarReporte') ?>",
                        type: 'POST',
                        data: {
                            facturas: selectedFacturas,
                            proveedor: {
                                codclie: selectedFacturas[0].codclie,
                                ruc: selectedFacturas[0].ruc,
                                nombre: selectedFacturas[0].nombre
                            }
                        },
                        beforeSend: function() {
                            Swal.fire({ title: 'Generando...', text: 'Por favor espere.', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                        },
                        success: function(response) {
                            if (response.status === 200) {
                                Swal.fire('Éxito', response.message, 'success');
                                generarPDF(response.data);
                                tableFact.ajax.reload();
                                tableReportes.ajax.reload();
                                selectedFacturas = [];
                                selectedProveedor = null;
                                $('#checkAll').prop('checked', false);
                                $('#btnGenerarReporte').prop('disabled', true);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Error al conectar con el servidor.', 'error');
                        }
                    });
                }
            });
        });

        // ===== DATATABLE REPORTES GENERADOS =====
        var tableReportes = $('#table_reportes').DataTable({
            ajax: {
                url: "<?= site_url('recepcionProductos/listarReportes') ?>",
                type: "POST",
                dataSrc: ''
            },
            columns: [
                { data: 'ID' },
                { data: 'RAZON_SOCIAL' },
                { data: 'RUC' },
                {
                    data: 'FACTURAS',
                    render: function(data) {
                        if (!data) return '';
                        return '<small>' + data + '</small>';
                    }
                },
                { data: 'FECHA_RECEPCION' },
                {
                    data: 'FECHA_GENERACION',
                    render: function(data) {
                        if (!data) return '';
                        return moment(data).format('DD/MM/YYYY HH:mm');
                    }
                },
                {
                    data: 'ESTADO',
                    render: function(data) {
                        if (data == 1) return '<span class="badge badge-success">Activo</span>';
                        return '<span class="badge badge-danger">Anulado</span>';
                    }
                },
                {
                    data: 'ID',
                    orderable: false,
                    render: function(data, type, row) {
                        var btns = '<div class="btn-group btn-group-sm">';
                        btns += '<button class="btn btn-info btn-ver-reporte" data-id="' + data + '"><i class="fas fa-file-pdf"></i> Ver PDF</button>';
                        if (row.ESTADO == 1) {
                            btns += '<button class="btn btn-danger btn-anular-reporte" data-id="' + data + '"><i class="fas fa-times"></i> Anular</button>';
                        }
                        btns += '</div>';
                        return btns;
                    }
                }
            ],
            order: [[0, 'desc']],
            searching: false,
            paging: true,
            pageLength: 10,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            language: {
                emptyTable: "No hay reportes generados.",
                info: "Mostrando _START_ a _END_ de _TOTAL_ reportes",
                paginate: { previous: "Anterior", next: "Siguiente" }
            }
        });

        $('#btnRefreshReportes').click(function() {
            tableReportes.ajax.reload();
        });

        // ===== VER PDF DE REPORTE EXISTENTE =====
        $('#table_reportes tbody').on('click', '.btn-ver-reporte', function() {
            var idReporte = $(this).data('id');
            $.ajax({
                url: "<?= site_url('recepcionProductos/verReporte') ?>",
                type: 'POST',
                data: { id: idReporte },
                beforeSend: function() {
                    Swal.fire({ title: 'Cargando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                },
                success: function(response) {
                    Swal.close();
                    if (response.status === 200) {
                        generarPDF(response.data);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al conectar con el servidor.', 'error');
                }
            });
        });

        // ===== ANULAR REPORTE =====
        $('#table_reportes tbody').on('click', '.btn-anular-reporte', function() {
            var idReporte = $(this).data('id');
            Swal.fire({
                title: 'Anular Reporte #' + idReporte,
                text: '¿Está seguro? Las facturas asociadas quedarán disponibles para futuros reportes.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("<?= site_url('recepcionProductos/anularReporte') ?>", { id: idReporte }, function(response) {
                        if (response.status === 200) {
                            Swal.fire('Éxito', response.message, 'success');
                            tableReportes.ajax.reload();
                            tableFact.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    });
                }
            });
        });

    }); // end document.ready

    // =============================================
    // GENERADOR PDF CON PDFMAKE - F-BMF-10 (MEJORADO)
    // =============================================
    function generarPDF(data) {
        var reporte = data.reporte;
        var productos = data.productos;
        var facturas = data.facturas || [];

        // Preparar datos generales
        var proveedorNombre = reporte.RAZON_SOCIAL || '';
        var fechaRecepcion = reporte.FECHA_RECEPCION;
        var fechaParts = fechaRecepcion.split('-');
        var fechaFormateada = fechaParts[2] + '/' + fechaParts[1] + '/' + fechaParts[0];
        
        // Unir números de factura para la cabecera
        var nrosFactura = facturas.map(function(f) { return f.NRO_FACTURA; }).join(', ');
        if (!nrosFactura && productos.length > 0) {
            // Fallback si no hay detalles de factura específicos en el objeto facturas
            var facts = [];
            productos.forEach(function(p) { if(p.NRO_FACTURA && facts.indexOf(p.NRO_FACTURA) === -1) facts.push(p.NRO_FACTURA); });
            nrosFactura = facts.join(', ');
        }

        // Función auxiliar para checkboxes
        function getCheckbox() {
            return {
                canvas: [{ type: 'rect', x: 0, y: 0, w: 8, h: 8, lineColor: '#000000' }],
                width: 10, height: 10, margin: [0, 1, 0, 0]
            };
        }

        function getCheckedBox() {
            return {
                canvas: [
                    { type: 'rect', x: 0, y: 0, w: 8, h: 8, lineColor: '#000000' },
                    { type: 'line', x1: 2, y1: 4, x2: 4, y2: 6, lineWidth: 1 },
                    { type: 'line', x1: 4, y1: 6, x2: 7, y2: 2, lineWidth: 1 }
                ],
                width: 10, height: 10, margin: [0, 1, 0, 0]
            };
        }

        // Construir filas de la tabla
        var bodyTable = [];

        // Encabezados (3 filas)
        bodyTable.push([
            { text: 'Item', fontSize: 7, bold: true, alignment: 'center', rowSpan: 3, fillColor: '#EEEEEE' },
            { text: 'Nombre de Producto/Concentración', fontSize: 7, bold: true, alignment: 'center', rowSpan: 3, fillColor: '#EEEEEE' },
            { text: 'Forma Farmacéutica', fontSize: 7, bold: true, alignment: 'center', rowSpan: 3, fillColor: '#EEEEEE' },
            { text: 'Presentación', fontSize: 7, bold: true, alignment: 'center', rowSpan: 3, fillColor: '#EEEEEE' },
            { text: 'Lote', fontSize:7, bold: true, alignment: 'center', rowSpan: 3, fillColor: '#EEEEEE' },
            { text: 'Fecha Vcto.', fontSize: 7, bold: true, alignment: 'center', rowSpan: 3, fillColor: '#EEEEEE' },
            { text: 'Registro Sanitario / N.S.O. (1)', fontSize: 7, bold: true, alignment: 'center', rowSpan: 3, fillColor: '#EEEEEE' },
            { text: 'Condición Almacento. (2)', fontSize: 5, bold: true, alignment: 'center', colSpan: 2, fillColor: '#EEEEEE' },
            {},
            { text: 'Cantidad Solicitada', fontSize: 6, bold: true, alignment: 'center', rowSpan: 2, fillColor: '#EEEEEE' },
            { text: 'Cantidad Recibida', fontSize: 6, bold: true, alignment: 'center', rowSpan: 2, fillColor: '#EEEEEE' },
            { text: 'CONFORMIDAD DEL ANÁLISIS ORGANOLÉPTICO (3)', fontSize: 6, bold: true, alignment: 'center', colSpan: 4, fillColor: '#EEEEEE' },
            {},
            {},
            {}
        ]);

        bodyTable.push([
            {}, {}, {}, {}, {}, {}, {},
            { text: 'T° Amb', fontSize: 5, bold: false, alignment: 'center', fillColor: '#EEEEEE' },
            { text: 'Refrig.', fontSize: 5, bold: false, alignment: 'center', fillColor: '#EEEEEE' },
            {}, {},
            { text: 'Material Embalaje', fontSize: 5, bold: true, alignment: 'center', rowSpan: 2, fillColor: '#EEEEEE' },
            { text: 'Identificación de Product', fontSize: 5, bold: true, alignment: 'center', rowSpan: 2, fillColor: '#EEEEEE' },
            { text: 'Envase Mediato Inmediat', fontSize: 5, bold: true, alignment: 'center', rowSpan: 2, fillColor: '#EEEEEE' },
            { text: 'Cierre/Sello de Envase', fontSize: 5, bold: true, alignment: 'center', rowSpan: 2, fillColor: '#EEEEEE' }
        ]);

        bodyTable.push([
            {}, {}, {}, {}, {}, {}, {},
            { stack: [getCheckbox()], alignment: 'center' },
            { stack: [getCheckbox()], alignment: 'center' },
            {}, {}, {}, {}, {}, {}
        ]);

        // Filas de datos
        productos.forEach(function(prod, idx) {
            var nombreProducto = prod.ART_NOMBRE || prod.DES_PROD || '';
            
            bodyTable.push([
                { text: (idx + 1).toString(), style: 'tdCell', alignment: 'center' },
                { text: nombreProducto, style: 'tdCell', alignment: 'left' },
                { text: '', style: 'tdCell' }, // Forma Farmac.
                { text: '', style: 'tdCell' }, // Presentación
                { text: (prod.LOTE || ''), style: 'tdCell', alignment: 'center', fontSize: (prod.LOTE && prod.LOTE.length > 7 ? 5 : 7) }, // Lote
                { text: (prod.VENCIMIENTO || ''), style: 'tdCell', alignment: 'center' }, // F. Venc.
                { text: '', style: 'tdCell', alignment: 'center' }, // Reg. Sanitario
                { stack: [getCheckbox()], alignment: 'center' }, // T. Amb
                { stack: [getCheckbox()], alignment: 'center' }, // Refrig
                { text: (prod.CANTIDAD || ''), style: 'tdCell', alignment: 'center' }, // Cant. Solicitada
                { text: (prod.CANTIDAD || ''), style: 'tdCell', alignment: 'center' }, // Cant. Recibida
                { stack: [getCheckbox()], alignment: 'center' }, // Mat. Embalaje
                { stack: [getCheckbox()], alignment: 'center' }, // Identificacion
                { stack: [getCheckbox()], alignment: 'center' }, // Envase
                { stack: [getCheckbox()], alignment: 'center' }  // Cierre
            ]);
        });

        // Rellenar hasta mínimo de filas
        var minRows = 20;
        var startIdx = productos.length;
        for (var i = startIdx; i < minRows; i++) {
            bodyTable.push([
                { text: (i + 1).toString(), style: 'tdCell', alignment: 'center' },
                { text: '', style: 'tdCell' },
                { text: '', style: 'tdCell' },
                { text: '', style: 'tdCell' },
                { text: '', style: 'tdCell' },
                { text: '', style: 'tdCell' },
                { text: '', style: 'tdCell' },
                { stack: [getCheckbox()], alignment: 'center' },
                { stack: [getCheckbox()], alignment: 'center' },
                { text: '', style: 'tdCell' },
                { text: '', style: 'tdCell' },
                { stack: [getCheckbox()], alignment: 'center' },
                { stack: [getCheckbox()], alignment: 'center' },
                { stack: [getCheckbox()], alignment: 'center' },
                { stack: [getCheckbox()], alignment: 'center' }
            ]);
        }

        var docDefinition = {
            pageSize: 'A4',
            pageOrientation: 'landscape',
            pageMargins: [25, 70, 25, 120],

            header: function(currentPage, pageCount) {
                return {
                    margin: [25, 15, 25, 0],
                    columns: [
                        {
                            width: 100,
                            stack: [
                                { text: 'MEDINAFARMA', bold: true, fontSize: 11, color: '#CC0000' }
                            ]
                        },
                        {
                            width: '*',
                            stack: [
                                { text: 'Registro de Recepción de Productos y/o Dispositivos', bold: true, fontSize: 13, alignment: 'center' }
                            ]
                        },
                        {
                            width: 80,
                            table: {
                                widths: ['*'],
                                body: [[{ text: 'F-BMF-10', bold: true, fontSize: 10, alignment: 'center' }]]
                            },
                            layout: { hLineWidth: function() { return 1; }, vLineWidth: function() { return 1; } }
                        }
                    ]
                };
            },

            footer: function(currentPage, pageCount) {
                return {
                    margin: [25, 5, 25, 0],
                    stack: [
                        {
                            columns: [
                                {
                                    width: '60%',
                                    table: {
                                        widths: ['*'],
                                        body: [
                                            [{ text: 'Características básica de envases para dar conformidad:', bold: true, fontSize: 7, border: [true, true, true, false] }],
                                            [{ text: '1. De Vidrio: No deben estar vacios o imcompletos, no presentar manchas, cuerpos extraños en el interior, grietas, el cierre debe ser hermético y banda de seguridad intacta si tuviera.', fontSize: 6, border: [true, false, true, false] }],
                                            [{ text: '2. De Plástico: No deben estar vacios o imcompletos, no deben presentar aberturas, grietas o hendiduras que afecten el producto y su apariencia. En caso de tener banda de seguridad, debe estar intacta.', fontSize: 6, border: [true, false, true, false] }],
                                            [{ text: '3. De Aluminio: No deben presentar perforaciones, grietas, roturas y deformaciones. El cierre debe ser hermético.', fontSize: 6, border: [true, false, true, false] }],
                                            [{ text: '4. De Blister termosellado o folios: No debe estar roto, vació y/o mal sellados. No debe presentar perforaciones.', fontSize: 6, border: [true, false, true, true] }]
                                        ]
                                    },
                                    layout: { 
                                        hLineWidth: function() { return 0.5; }, 
                                        vLineWidth: function() { return 0.5; },
                                        hLineColor: function() { return '#333333'; },
                                        vLineColor: function() { return '#333333'; }
                                    }
                                },
                                {
                                    width: '40%',
                                    margin: [10, 40, 0, 0], // Margen superior alto para alinear con base del cuadro
                                    columns: [
                                        {
                                            width: '50%',
                                            stack: [
                                                { text: '________________________________', alignment: 'center', fontSize: 7 },
                                                { text: 'Encargado de Recepción', alignment: 'center', fontSize: 7, bold: true }
                                            ]
                                        },
                                        {
                                            width: '50%',
                                            stack: [
                                                { text: '________________________________', alignment: 'center', fontSize: 7 },
                                                { text: 'V°B° Director Técnico', alignment: 'center', fontSize: 7, bold: true }
                                            ]
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            columns: [
                                { text: 'Versión: 01', fontSize: 7, margin: [0, 5, 0, 0] },
                                { text: 'Página ' + currentPage + ' de ' + pageCount, fontSize: 7, alignment: 'right', margin: [0, 5, 0, 0] }
                            ]
                        }
                    ]
                };
            },

            content: [
                // Datos Generales en Cabecera
                {
                    columns: [
                        { text: 'Proveedor:', fontSize: 9, width: 50 },
                        { text: proveedorNombre, fontSize: 9, width: 250, decoration: 'underline' },
                        { text: 'Fecha Recepción:', fontSize: 9, width: 80 },
                        { text: fechaFormateada, fontSize: 9, width: 70, decoration: 'underline', alignment: 'center' },
                        { text: 'Fecha Revisión:__________________', fontSize: 9, width: 140 },
                        { text: '', fontSize: 9, width: '*', decoration: 'underline' }
                    ],
                    margin: [0, -30, 0, 5]
                },
                {
                    columns: [
                        { text: 'Factura/Guía:', fontSize: 9, width: 65 },
                        { text: nrosFactura, fontSize: 9, width: '*', decoration: 'underline' }
                    ],
                    margin: [0, 0, 0, 10]
                },

                // Tabla
                {
                    table: {
                        headerRows: 3,
                        widths: [18, '*', 45, 45, 35, 35, 50, 15, 15, 30, 30, 20, 20, 20, 20],
                        body: bodyTable
                    },
                    layout: {
                        hLineWidth: function() { return 0.5; },
                        vLineWidth: function() { return 0.5; },
                        hLineColor: function() { return '#000000'; },
                        vLineColor: function() { return '#000000'; }
                    }
                },

                // Leyendas
                {
                    margin: [0, 5, 0, 0],
                    columns: [
                        { text: 'Temperatura de Recepción: _____°C    Observaciones:_________________________________________________________________________________________________', fontSize: 8, width: '80%' },
                        {
                            width: '20%',
                            stack: [
                                { text: '(1) *N.S.O.: Notificación sanitaria obligatoria', fontSize: 7 },
                                { text: '(2) T° Amb. 15°C - 25°C        Refrig. 2°C - 8°C', fontSize: 7, margin: [0, 2, 0, 2] },
                                { 
                                    columns: [
                                        { text: '(3) Conforme ', width: 'auto' },
                                        { stack: [getCheckedBox()], width: 12, margin: [2, 1, 0, 0] },
                                        { text: '    No conforme     x', width: '*' }
                                    ],
                                    fontSize: 7 
                                }
                            ]
                        }
                    ]
                }
            ],

            styles: {
                tdCell: {
                    fontSize: 7,
                    margin: [0, 1, 0, 1]
                }
            },

            defaultStyle: {
                font: 'Roboto'
            }
        };

        pdfMake.createPdf(docDefinition).open();
    }
</script>

<?= $this->endSection(); ?>
