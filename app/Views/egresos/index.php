<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $titulo ?? 'Registro de Gastos y Egresos' ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Egresos</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <?php if (session('success')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <i class="icon fas fa-check"></i> <?= session('success') ?>
            </div>
        <?php endif; ?>
        
        <?php if (session('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <i class="icon fas fa-ban"></i> <?= session('error') ?>
            </div>
        <?php endif; ?>
        
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
                        <form id="form-filtros" method="get" action="<?= site_url('egresos') ?>">
                            <div class="row">
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
                                            <option value="">Todos</option>
                                            <option value="1" <?= ($filtros['local'] ?? '') == '1' ? 'selected' : '' ?>>Local 1</option>
                                            <option value="2" <?= ($filtros['local'] ?? '') == '2' ? 'selected' : '' ?>>Local 2</option>
                                            <option value="3" <?= ($filtros['local'] ?? '') == '3' ? 'selected' : '' ?>>Local 3</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="cuenta_id">Cuenta</label>
                                        <select name="cuenta_id" id="cuenta_id" class="form-control select2" style="width: 100%;">
                                            <option value="">Todas las cuentas</option>
                                            <?php foreach ($cuentas as $id => $nombre): ?>
                                                <option value="<?= $id ?>" <?= ($filtros['cuenta_id'] ?? '') == $id ? 'selected' : '' ?>>
                                                    <?= esc($nombre) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="estado">Estado</label>
                                        <select name="estado" id="estado" class="form-control">
                                            <option value="">Todos</option>
                                            <option value="pagado" <?= ($filtros['estado'] ?? '') == 'pagado' ? 'selected' : '' ?>>Pagado</option>
                                            <option value="pendiente" <?= ($filtros['estado'] ?? '') == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Barra de acciones masivas -->
                <div id="bulk-actions-bar" class="card card-outline card-info mb-3" style="display: none;">
                    <div class="card-body p-2">
                        <div class="row align-items-center">
                            <div class="col-md-auto ml-2">
                                <i class="fas fa-tasks text-info"></i>
                                <span id="selected-count" class="badge badge-info mx-1">0</span> seleccionados
                            </div>
                            <div class="col-md-4">
                                <select id="bulk-cuenta-id" class="form-control form-control-sm select2">
                                    <option value="">Seleccionar cuenta para asignar masivamente...</option>
                                    <?php foreach ($cuentas as $id => $nombre): ?>
                                        <option value="<?= $id ?>"><?= esc($nombre) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-auto">
                                <button type="button" id="btn-bulk-apply" class="btn btn-sm btn-info">
                                    <i class="fas fa-check-circle"></i> Aplicar a seleccionados
                                </button>
                            </div>
                            <div class="col-md-auto ml-auto mr-2">
                                <button type="button" class="btn btn-tool" onclick="$('.egreso-checkbox, #select-all').prop('checked', false); updateBulkBar();">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Listado de Egresos</h3>
                        <div class="card-tools">
                            <a href="<?= site_url('egresos/crear') ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Nuevo Gasto
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tabla-egresos" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="30px" class="text-center"><input type="checkbox" id="select-all"></th>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Local</th>
                                    <th>Cuenta</th>
                                    <th>Descripción</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Forma Pago</th>
                                    <th>Comprobante</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($egresos as $egreso): ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="egreso-checkbox" value="<?= $egreso['EGR_ID'] ?>">
                                    </td>
                                    <td><?= $egreso['EGR_ID'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($egreso['EGR_FECHA'])) ?></td>
                                    <td>Local <?= $egreso['EGR_LOCAL'] ?></td>
                                    <td><?= esc($egreso['PC_CODIGO'] ?? '') . ' - ' . esc($egreso['cuenta_nombre'] ?? '') ?></td>
                                    <td><?= esc($egreso['EGR_DESCRIPCION']) ?></td>
                                    <td class="text-right">S/. <?= number_format($egreso['EGR_MONTO'], 2) ?></td>
                                    <td>
                                        <?php if ($egreso['EGR_ESTADO'] == 'pagado'): ?>
                                            <span class="badge badge-success">Pagado</span>
                                        <?php elseif ($egreso['EGR_ESTADO'] == 'pendiente'): ?>
                                            <span class="badge badge-warning">Pendiente</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?= $egreso['EGR_ESTADO'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $egreso['EGR_FORMA_PAGO'] ?></td>
                                    <td>
                                        <?php if ($egreso['EGR_COMPROBANTE_TIPO'] && $egreso['EGR_COMPROBANTE_NUMERO']): ?>
                                            <?= $egreso['EGR_COMPROBANTE_TIPO'] ?> 
                                            <?= $egreso['EGR_COMPROBANTE_SERIE'] ? $egreso['EGR_COMPROBANTE_SERIE'] . '-' : '' ?>
                                            <?= $egreso['EGR_COMPROBANTE_NUMERO'] ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin comprobante</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('egresos/editar/' . $egreso['EGR_ID']) ?>" class="btn btn-primary btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (empty($egreso['EGR_CAJA_MOV_ID'])): ?>
                                            <a href="<?= site_url('egresos/eliminar/' . $egreso['EGR_ID']) ?>" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este egreso?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th colspan="5" class="text-right">Total:</th>
                                    <th class="text-right">S/. <?= number_format($total_egresos ?? 0, 2) ?></th>
                                    <th colspan="4"></th>
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
<!-- Select2 -->
<link rel="stylesheet" href="<?= site_url('plugins/select2/css/select2.min.css') ?>">
<link rel="stylesheet" href="<?= site_url('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>">
<!-- DataTables -->
<link rel="stylesheet" href="<?= site_url('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= site_url('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">
<!-- Toastr -->
<link rel="stylesheet" href="<?= site_url('plugins/toastr/toastr.min.css') ?>">

<!-- DataTables  & Plugins -->
<script src="<?= site_url('plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>
<!-- Select2 -->
<script src="<?= site_url('plugins/select2/js/select2.full.min.js') ?>"></script>
<!-- Toastr -->
<script src="<?= site_url('plugins/toastr/toastr.min.js') ?>"></script>

<script>
    $(function () {
        // Inicializar Select2
        $('.select2').select2({
            theme: 'bootstrap4'
        });
        
        // Inicializar DataTable
        var table = $('#tabla-egresos').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "order": [[1, 'desc']],
            "columnDefs": [
                { "orderable": false, "targets": 0 },
                { "searchable": false, "targets": 0 }
            ]
        });
        
        // Botón para limpiar filtros
        $('.btn-limpiar').on('click', function() {
            $('#form-filtros')[0].reset();
            $('#form-filtros').submit();
        });

        // Lógica de selección masiva
        window.updateBulkBar = function() {
            // Usamos table.$() para encontrar checkboxes inclusive en otras páginas
            const selectedCount = table.$('.egreso-checkbox:checked').length;
            $('#selected-count').text(selectedCount);
            
            if (selectedCount > 0) {
                $('#bulk-actions-bar').slideDown();
            } else {
                $('#bulk-actions-bar').slideUp();
                $('#select-all').prop('checked', false);
            }
        };

        // Seleccionar todos (usando el API de DataTable para afectar a todas las páginas)
        $(document).on('click', '#select-all', function() {
            const isChecked = this.checked;
            table.$('.egreso-checkbox').prop('checked', isChecked);
            updateBulkBar();
        });

        $(document).on('change', '.egreso-checkbox', function() {
            const allChecked = table.$('.egreso-checkbox:checked').length === table.$('.egreso-checkbox').length;
            $('#select-all').prop('checked', allChecked);
            updateBulkBar();
        });

        // Aplicar actualización masiva
        $('#btn-bulk-apply').on('click', function() {
            const ids = table.$('.egreso-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            const cuentaId = $('#bulk-cuenta-id').val();

            if (!cuentaId) {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('Por favor seleccione una cuenta para asignar');
                } else {
                    alert('Por favor seleccione una cuenta para asignar');
                }
                return;
            }

            if (confirm('¿Está seguro de asignar la cuenta seleccionada a los ' + ids.length + ' registros seleccionados?')) {
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

                $.ajax({
                    url: '<?= site_url('egresos/actualizarCuentasMasivo') ?>',
                    method: 'POST',
                    data: {
                        ids: ids,
                        cuenta_id: cuentaId,
                        "<?= csrf_token() ?>": "<?= csrf_hash() ?>"
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message);
                            } else {
                                alert(response.message);
                            }
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message);
                            } else {
                                alert(response.message);
                            }
                            btn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Aplicar a seleccionados');
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Error al procesar la solicitud masiva');
                        } else {
                            alert('Error al procesar la solicitud masiva');
                        }
                        btn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Aplicar a seleccionados');
                    }
                });
            }
        });
    });
</script>
<?= $this->endSection(); ?>