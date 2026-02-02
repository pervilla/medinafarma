<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $titulo ?? 'Reporte de Intereses Moratorios' ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('pagosproveedores') ?>">Cuentas por Pagar</a></li>
                    <li class="breadcrumb-item active">Reporte de Intereses</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Filtros</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="form-filtros" method="get" action="<?= site_url('pagosproveedores/reporteintereses') ?>">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha_desde">Fecha Desde</label>
                                        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="<?= $filtros['fecha_desde'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha_hasta">Fecha Hasta</label>
                                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="<?= $filtros['fecha_hasta'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="proveedor">Proveedor</label>
                                        <select name="proveedor" id="proveedor" class="form-control select2" style="width: 100%;">
                                            <option value="">Todos los proveedores</option>
                                            <?php foreach ($proveedores as $prov): ?>
                                                <option value="<?= $prov['cli_codclie'] ?>" <?= ($filtros['proveedor'] ?? '') == $prov['cli_codclie'] ? 'selected' : '' ?>>
                                                    <?= esc($prov['cli_nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                                        <a href="<?= site_url('pagosproveedores/exportarexcel?tipo=intereses&' . http_build_query($filtros)) ?>" class="btn btn-success ml-2" title="Exportar a Excel">
                                            <i class="fas fa-file-excel"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Intereses Moratorios Pagados</h3>
                        <div class="card-tools">
                            <div class="badge badge-danger">
                                Total: S/ <?= number_format($total_intereses, 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="tabla-intereses" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha Pago</th>
                                    <th>Factura</th>
                                    <th>Proveedor</th>
                                    <th>Local</th>
                                    <th>Descripción</th>
                                    <th>Monto Interés</th>
                                    <th>Forma Pago</th>
                                    <th>Usuario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($intereses as $interes): ?>
                                    <tr>
                                        <td><?= esc($interes['EGR_FECHA'] ?? '') ?></td>
                                        <td><?= esc($interes['EGR_FACTURA_REF'] ?? '') ?></td>
                                        <td>
                                            <?php if (!empty($interes['proveedor_nombre'])): ?>
                                                <?= esc($interes['proveedor_nombre']) ?>
                                            <?php else: ?>
                                                Cód. <?= esc($interes['EGR_PROVEEDOR_COD'] ?? '') ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $localesMap = [1 => 'Centro', 2 => 'Juanjuicillo', 3 => 'Peñameza'];
                                            echo $localesMap[$interes['EGR_LOCAL'] ?? 1] ?? 'N/A';
                                            ?>
                                        </td>
                                        <td><?= esc($interes['EGR_DESCRIPCION'] ?? '') ?></td>
                                        <td class="text-right">S/ <?= number_format($interes['EGR_MONTO'] ?? 0, 2) ?></td>
                                        <td class="text-center">
                                            <?php
                                            $formasPago = [
                                                'EFECTIVO' => 'Efectivo',
                                                'TRANSFERENCIA' => 'Transferencia',
                                                'TARJETA' => 'Tarjeta'
                                            ];
                                            echo $formasPago[$interes['EGR_FORMA_PAGO'] ?? 'EFECTIVO'] ?? $interes['EGR_FORMA_PAGO'];
                                            ?>
                                        </td>
                                        <td><?= esc($interes['EGR_USUARIO'] ?? '') ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info" title="Ver detalle" onclick="verDetalle(<?= $interes['EGR_ID'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (session()->get('rol') === 'ADMIN'): ?>
                                                <a href="#" class="btn btn-sm btn-warning" title="Editar" onclick="editarInteres(<?= $interes['EGR_ID'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">Total:</th>
                                    <th class="text-right">S/ <?= number_format($total_intereses, 2) ?></th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<!-- Modal para ver detalle -->
<div class="modal fade" id="modal-detalle">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detalle del Interés Moratorio</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal-detalle-body">
                Cargando...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<!-- DataTables -->
<link rel="stylesheet" href="<?= site_url('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= site_url('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= site_url('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') ?>">

<!-- DataTables -->
<script src="<?= site_url('plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-buttons/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') ?>"></script>
<script src="<?= site_url('plugins/jszip/jszip.min.js') ?>"></script>
<script src="<?= site_url('plugins/pdfmake/pdfmake.min.js') ?>"></script>
<script src="<?= site_url('plugins/pdfmake/vfs_fonts.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-buttons/js/buttons.html5.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-buttons/js/buttons.print.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-buttons/js/buttons.colVis.min.js') ?>"></script>

<!-- Select2 -->
<link rel="stylesheet" href="<?= site_url('plugins/select2/css/select2.min.css') ?>">
<link rel="stylesheet" href="<?= site_url('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>">
<script src="<?= site_url('plugins/select2/js/select2.full.min.js') ?>"></script>

<script>
    $(function () {
        // Inicializar Select2
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        // Inicializar DataTable
        $('#tabla-intereses').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "order": [[0, 'desc']], // Ordenar por fecha descendente
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "buttons": [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i class="fas fa-columns"></i> Columnas',
                    className: 'btn btn-secondary btn-sm'
                }
            ],
            "dom": 'Bfrtip',
            "columnDefs": [
                { "orderable": false, "targets": [8] } // Deshabilitar orden en columna Acciones
            ]
        });
    });
    
    function verDetalle(egresoId) {
        $('#modal-detalle-body').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i> Cargando...</div>');
        $('#modal-detalle').modal('show');
        
        $.get('<?= site_url('pagosproveedores/detalleinteres') ?>/' + egresoId, function(data) {
            $('#modal-detalle-body').html(data);
        }).fail(function() {
            $('#modal-detalle-body').html('<div class="alert alert-danger">Error al cargar los detalles.</div>');
        });
    }
    
    function editarInteres(egresoId) {
        if (confirm('¿Está seguro de editar este registro de interés? Esta operación debe realizarse con cuidado.')) {
            window.location.href = '<?= site_url('pagosproveedores/editarinteres') ?>/' + egresoId;
        }
    }
</script>
<?= $this->endSection(); ?>