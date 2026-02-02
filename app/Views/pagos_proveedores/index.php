<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $titulo ?? 'Cuentas por Pagar' ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Cuentas por Pagar</li>
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
                        <form id="form-filtros" method="get" action="<?= site_url('pagosproveedores') ?>">
                            <div class="row">
                                <div class="col-md-3">
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
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="fecha_desde">Fecha Desde</label>
                                        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="<?= $filtros['fecha_desde'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="fecha_hasta">Fecha Hasta</label>
                                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="<?= $filtros['fecha_hasta'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="local">Local</label>
                                        <select name="local" id="local" class="form-control">
                                            <option value="1" <?= ($filtros['local'] ?? 1) == 1 ? 'selected' : '' ?>>Centro</option>
                                            <option value="2" <?= ($filtros['local'] ?? 1) == 2 ? 'selected' : '' ?>>Juanjuicillo</option>
                                            <option value="3" <?= ($filtros['local'] ?? 1) == 3 ? 'selected' : '' ?>>Peñameza</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="estado">Estado</label>
                                        <select name="estado" id="estado" class="form-control">
                                            <option value="">Todas</option>
                                            <option value="vencidas" <?= ($filtros['estado'] ?? '') == 'vencidas' ? 'selected' : '' ?>>Vencidas</option>
                                            <option value="por_vencer" <?= ($filtros['estado'] ?? '') == 'por_vencer' ? 'selected' : '' ?>>Por Vencer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
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
                        <h3 class="card-title">Facturas Pendientes de Pago</h3>
                        <div class="card-tools">
                            <a href="<?= site_url('pagosproveedores/reporteintereses') ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-chart-bar"></i> Reporte de Intereses
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="tabla-facturas" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Factura</th>
                                    <th>Proveedor</th>
                                    <th>Fecha Vcto.</th>
                                    <th>Saldo Pendiente</th>
                                    <th>Días Mora</th>
                                    <th>Interés Proyectado</th>
                                    <th>Total a Pagar</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($facturas as $factura): ?>
                                    <tr>
                                        <td><?= esc($factura['car_NUMFAC']) ?></td>
                                        <td><?= esc($factura['proveedor_nombre'] ?? 'N/A') ?></td>
                                        <td><?= esc($factura['car_fecha_vcto']) ?></td>
                                        <td class="text-right">S/ <?= number_format($factura['car_importe'] ?? 0, 2) ?></td>
                                        <td class="text-center">
                                            <?php if (($factura['dias_mora'] ?? 0) > 0): ?>
                                                <span class="badge badge-danger"><?= $factura['dias_mora'] ?> días</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Al día</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">S/ <?= number_format($factura['interes_proyectado'] ?? 0, 2) ?></td>
                                        <td class="text-right">S/ <?= number_format($factura['total_a_pagar'] ?? 0, 2) ?></td>
                                        <td class="text-center">
                                            <a href="<?= site_url('pagosproveedores/pagar/' . $factura['car_NUMFAC']) ?>" class="btn btn-sm btn-primary" title="Registrar Pago">
                                                <i class="fas fa-money-bill-wave"></i> Pagar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Totales:</th>
                                    <th class="text-right">S/ <?= number_format(array_sum(array_column($facturas, 'car_importe')), 2) ?></th>
                                    <th></th>
                                    <th class="text-right">S/ <?= number_format(array_sum(array_column($facturas, 'interes_proyectado')), 2) ?></th>
                                    <th class="text-right">S/ <?= number_format(array_sum(array_column($facturas, 'total_a_pagar')), 2) ?></th>
                                    <th></th>
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
        $('#tabla-facturas').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "order": [[2, 'asc']], // Ordenar por fecha de vencimiento
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "buttons": [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn btn-info btn-sm'
                }
            ],
            "dom": 'Bfrtip',
            "columnDefs": [
                { "orderable": false, "targets": [7] } // Deshabilitar orden en columna Acciones
            ]
        });
    });
</script>
<?= $this->endSection(); ?>