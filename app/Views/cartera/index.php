<?php

/** @var CodeIgniter\View\View $this*/ ?>
<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header"></div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Reporte de Facturas por Proveedor</h3>
                    </div>
                    <div class="card-body">

                        <!-- Filtros -->
                        <div class="form-inline mb-3">
                            <div class="form-group mr-2">
                                <label>Proveedor:</label>
                                <select id="b_cliente" name="b_cliente" class="form-control select2 w-100" data-placeholder="Buscar Proveedor" data-allow-clear="1"></select>
                            </div>

                            <div class="form-group mr-2">
                                <label>Rango de Fechas:</label>
                                <input type="text" id="reportrange" name="reportrange" class="form-control form-control-sm" readonly style="background-color: #fff;">
                            </div>

                            <div class="form-group mr-2">
                                <label>Estado:</label>
                                <select id="estado" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <option value="PAGADO">Pagado</option>
                                    <option value="SIN PAGAR">Sin Pagar</option>
                                </select>
                            </div>

                            <button type="button" id="btn-buscar" class="btn btn-primary">Buscar</button>
                        </div>

                        <!-- Tabla -->
                        <table id="tabla-documentos" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Documento</th>
                                    <th>Tipo</th>
                                    <th>Fecha Ingreso</th>
                                    <th>Vencimiento</th>
                                    <th>Monto</th>
                                    <th>Pago Realizado</th>
                                    <th>Fecha Pago</th>
                                    <th>Forma de Pago</th>
                                    <th>Banco</th>
                                    <th>Número Operación</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                        </table>

                        <div class="mt-3">
                            <a href="#" id="exportarExcel" class="btn btn-success"><i class="fa fa-file-excel"></i> Exportar a Excel</a>
                            <a href="#" id="exportarPdf" class="btn btn-danger"><i class="fa fa-file-pdf"></i> Exportar a PDF</a>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</div>
<!-- /.content -->

<?= $this->endSection(); ?>


<?= $this->section('footer'); ?>
<!-- Select2 -->
<link rel="stylesheet" href="../../plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

<!-- Daterangepicker -->
<link rel="stylesheet" type="text/css" href="../../plugins/daterangepicker/daterangepicker.css" />


<!-- Moment (para daterangepicker) -->
<script type="text/javascript" src="../../plugins/moment/moment.min.js"></script>
<script type="text/javascript" src="../../plugins/daterangepicker/daterangepicker.js"></script>

<!-- Select2 -->
<script src="../../plugins/select2/js/select2.full.min.js"></script>

<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>

<!-- Exportar a Excel y PDF -->
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>

<script>
    $(function () {
    // Cargar Select2 para proveedores
    $('#b_cliente').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: "Buscar Proveedor",
        allowClear: true,
        ajax: {
            url: "<?= site_url('personas/get_proveedores') ?>",
            dataType: "json",
            processResults: function(data) {
                return { results: data };
            },
        }
    });

    // Daterangepicker
    let start = moment().startOf('month');
    let end = moment().endOf('month');

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            customRangeLabel: 'Rango personalizado'
        },
        ranges: {
            'Hoy': [moment(), moment()],
            'Esta semana': [moment().startOf('week'), moment().endOf('week')],
            'Este mes': [moment().startOf('month'), moment().endOf('month')],
            'Últimos 30 días': [moment().subtract(29, 'days'), moment()]
        }
    }, function(start, end) {
        $('#reportrange').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
    });

    // DataTable
    const tabla = $('#tabla-documentos').DataTable({
        processing: true,
        serverSide: false,
        order: [[0, 'asc']], // Ordenar por proveedor
        ajax: {
            url: "<?= site_url('cartera/get_documentos') ?>",
            type: "POST",
            data: function(d) {
                d.proveedor = $('#b_cliente').val();
                d.fecha_inicio =  moment($('#reportrange').data('daterangepicker').startDate).format('DD/MM/YYYY');
                d.fecha_fin = moment($('#reportrange').data('daterangepicker').endDate).format('DD/MM/YYYY');
                d.estado = $('#estado').val();
            }
        },
        columns: [
            { data: "CLI_NOMBRE" },
            { data: "CAR_NUMDOC", render: (data, type, row) => row.CAR_SERDOC + '-' + data },
            { data: "CAR_TIPDOC" },
            { data: "CAR_FECHA_INGR" },
            { data: "CAR_FECHA_VCTO" },
            { data: "CAR_IMP_INI", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "PAGO_MONTO", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "PAGO_FECHA" },
            { data: "PAGO_FORMA" },
            { data: "PAGO_BANCO" },
            { data: "PAGO_NUMERO_OPERACION" },
            { data: "ESTADO" },
            { data: null, render: function(data) {
                return `<a href="<?= site_url('cartera/imprimir/') ?>${data.CAR_NUMDOC}" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-print"></i></a>`;
            }}
        ],
        drawCallback: function(settings) {
            var api = this.api();
            var last = null;

            // Agrupación por proveedor
            api.rows({ page: 'current' }).every(function(rowIdx, tableLoop, rowLoop) {
                var data = this.data();
                if (last !== data.CLI_NOMBRE) {
                    $(this.node()).before(
                        '<tr class="group"><td colspan="12">' + data.CLI_NOMBRE + '</td></tr>'
                    );
                    last = data.CLI_NOMBRE;
                }
            });
        }
    });

    // Botón buscar
    $('#btn-buscar').on('click', function() {
        tabla.ajax.reload();
    });
    $('#exportarExcel').on('click', function() {
    const proveedor = $('#b_cliente').val();
    const inicio = moment($('#reportrange').data('daterangepicker').startDate).format('DD/MM/YYYY');
    const fin = $('#reportrange').data('daterangepicker').endDate.format('DD/MM/YYYY');
    const estado = $('#estado').val();

    window.location.href = "<?= site_url('cartera/exportar/excel') ?>" +
        "?proveedor=" + encodeURIComponent(proveedor) +
        "&fecha_inicio=" + encodeURIComponent(inicio) +
        "&fecha_fin=" + encodeURIComponent(fin) +
        "&estado=" + encodeURIComponent(estado);
});

$('#exportarPdf').on('click', function() {
    const proveedor = $('#b_cliente').val();
    const inicio = moment($('#reportrange').data('daterangepicker').startDate).format('DD/MM/YYYY');
    const fin = $('#reportrange').data('daterangepicker').endDate.format('DD/MM/YYYY');
    const estado = $('#estado').val();

    window.open("<?= site_url('cartera/exportar/pdf') ?>" +
        "?proveedor=" + encodeURIComponent(proveedor) +
        "&fecha_inicio=" + encodeURIComponent(inicio) +
        "&fecha_fin=" + encodeURIComponent(fin) +
        "&estado=" + encodeURIComponent(estado));
});

});
</script>
<?= $this->endSection(); ?>