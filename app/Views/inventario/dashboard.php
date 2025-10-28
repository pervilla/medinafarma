<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Dashboard de Inventarios</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#nuevoInventario">
                            <i class="fas fa-plus"></i> Nuevo Inventario
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php foreach ($inventarios as $inv): ?>
        <div class="col-lg-6 col-12 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-box"></i> <?= $inv->inv_descripcion ?>
                        <small class="text-muted">(ID: <?= $inv->inv_id ?>)</small>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="info-box mb-2">
                                <span class="info-box-icon bg-info"><i class="fas fa-cubes"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Items</span>
                                    <span class="info-box-number"><?= number_format($inv->inv_total_items) ?></span>
                                </div>
                            </div>
                            
                            <div class="info-box mb-2">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Items Contados</span>
                                    <span class="info-box-number"><?= number_format($inv->items_contados) ?></span>
                                </div>
                            </div>
                            
                            <div class="info-box mb-2">
                                <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Responsables</span>
                                    <span class="info-box-number"><?= $inv->total_responsables ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="progress-group">
                                <span class="progress-text">Progreso General</span>
                                <span class="float-right"><b><?= $inv->items_contados ?></b>/<?= $inv->inv_total_items ?></span>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-primary" style="width: <?= $inv->porcentaje_avance ?>%"></div>
                                </div>
                                <span class="progress-text text-center"><?= $inv->porcentaje_avance ?>%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <button type="button" class="btn btn-outline-primary btn-gestionar" 
                                        data-inv-id="<?= $inv->inv_id ?>" data-total="<?= $inv->inv_total_items ?>">
                                    <i class="fas fa-cog"></i> Gestionar
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-success dropdown-toggle" data-toggle="dropdown">
                                        <i class="fas fa-list"></i> Ver Detalle
                                    </button>
                                    <div class="dropdown-menu" id="dropdown-responsables-<?= $inv->inv_id ?>">
                                        <a class="dropdown-item" href="<?= site_url('inventario/lista/1/' . $inv->inv_id) ?>">
                                            <i class="fas fa-table"></i> Vista Tabla
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <h6 class="dropdown-header">Responsables:</h6>
                                        <!-- Se cargarán dinámicamente -->
                                    </div>
                                </div>
                                <?php if ($inv->inv_estado == 1): ?>
                                    <button type="button" class="btn btn-outline-warning btn-cerrar" data-inv-id="<?= $inv->inv_id ?>">
                                        <i class="fas fa-lock"></i> Cerrar
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-generar-guias" data-inv-id="<?= $inv->inv_id ?>">
                                        <i class="fas fa-file-export"></i> Guías
                                    </button>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Cerrado</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small><i class="fas fa-calendar"></i> Creado: <?= date('d/m/Y H:i', strtotime($inv->inv_fecha)) ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Gestión de Inventario -->
<div class="modal fade" id="gestionModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title"><i class="fas fa-cog"></i> Gestión de Inventario</h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Responsables Asignados</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="table_responsables" class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Responsable</th>
                                                <th>Proporción</th>
                                                <th>Items Asignados</th>
                                                <th>Progreso</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#nuevoResponsable">
                                    <i class="fas fa-plus"></i> Agregar Responsable
                                </button>
                                <button type="button" class="btn btn-warning btn-sm" id="distribuirProductos">
                                    <i class="fas fa-random"></i> Distribuir Productos
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Resumen</h5>
                            </div>
                            <div class="card-body">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-cubes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Items</span>
                                        <span class="info-box-number" id="total-items">0</span>
                                    </div>
                                </div>
                                
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Items Asignados</span>
                                        <span class="info-box-number" id="items-asignados">0</span>
                                    </div>
                                </div>
                                
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Responsables</span>
                                        <span class="info-box-number" id="total-responsables">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Inventario -->
<div class="modal fade" id="nuevoInventario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-nuevo-inventario" action="<?= site_url('inventario/crear_inventario') ?>" method="post">
                <div class="modal-header bg-success text-white">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Nuevo Inventario</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="idlocal">Local</label>
                        <select id="idlocal" name="idlocal" class="form-control" required>
                            <option value="">Seleccione un local</option>
                            <option value="1">LOCAL CENTRAL</option>
                            <option value="2">JUANJUICILLO</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="inv_descripcion">Descripción</label>
                        <input type="text" id="inv_descripcion" name="inv_descripcion" class="form-control" 
                               placeholder="Ej: Inventario Mensual Enero 2024" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Crear Inventario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nuevo Responsable -->
<div class="modal fade" id="nuevoResponsable" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title"><i class="fas fa-user-plus"></i> Agregar Responsable</h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="vem_codven">Empleado</label>
                    <select class="form-control" id="vem_codven" name="vem_codven" required>
                        <option value="">Seleccione un empleado</option>
                        <?php foreach ($empleados as $empleado): ?>
                            <option value="<?= $empleado->VEM_CODVEN ?>">
                                <?= $empleado->VEM_CODVEN . ' - ' . trim($empleado->VEM_NOMBRE) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="proporcion">Proporción</label>
                    <input type="number" class="form-control" id="proporcion" name="proporcion" 
                           min="1" value="1" placeholder="Ej: 2 (significa 2 partes del total)">
                    <small class="form-text text-muted">
                        La proporción determina cuántas partes del inventario se asignarán a este responsable
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-agregar-responsable">
                    <i class="fas fa-save"></i> Agregar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Generar Guías -->
<div class="modal fade" id="guiasModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title"><i class="fas fa-file-export"></i> Generar Guías de Ajuste</h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Las guías se generarán automáticamente según las diferencias encontradas en el inventario.
                </div>
                
                <div class="form-group">
                    <label>Tipo de guías a generar:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_guia" id="tipo_ingreso" value="ingreso">
                        <label class="form-check-label" for="tipo_ingreso">
                            <i class="fas fa-arrow-up text-success"></i> Solo Guía de Ingreso (Sobrantes)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_guia" id="tipo_salida" value="salida">
                        <label class="form-check-label" for="tipo_salida">
                            <i class="fas fa-arrow-down text-danger"></i> Solo Guía de Salida (Faltantes)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_guia" id="tipo_ambas" value="ambas" checked>
                        <label class="form-check-label" for="tipo_ambas">
                            <i class="fas fa-exchange-alt text-primary"></i> Ambas Guías (Recomendado)
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-generar-guias">
                    <i class="fas fa-cog"></i> Generar Guías
                </button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="current-inv-id" value="">
<input type="hidden" id="current-total-items" value="">

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<link rel="stylesheet" href="<?= site_url('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= site_url('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">

<script src="<?= site_url('plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>

<script>
$(document).ready(function() {
    let currentInvId = 0;
    let responsablesTable;
    
    // Gestionar inventario
    $('.btn-gestionar').click(function() {
        currentInvId = $(this).data('inv-id');
        $('#current-inv-id').val(currentInvId);
        $('#current-total-items').val($(this).data('total'));
        $('#total-items').text($(this).data('total'));
        
        cargarResponsables();
        cargarDropdownResponsables(currentInvId);
        $('#gestionModal').modal('show');
    });
    
    function cargarDropdownResponsables(invId) {
        $.post("<?= site_url('inventario/listar_responsables') ?>", {
            inv_id: invId
        }).done(function(responsables) {
            const dropdown = $(`#dropdown-responsables-${invId}`);
            
            // Limpiar responsables anteriores
            dropdown.find('.responsable-item').remove();
            
            if (responsables && responsables.length > 0) {
                responsables.forEach(function(resp) {
                    dropdown.append(`
                        <a class="dropdown-item responsable-item" href="<?= site_url('inventario/conteo/1/') ?>${invId}/${resp.vem_codven}">
                            <i class="fas fa-mobile-alt"></i> ${resp.VEM_NOMBRE}
                        </a>
                    `);
                });
            } else {
                dropdown.append('<span class="dropdown-item-text responsable-item text-muted">Sin responsables asignados</span>');
            }
        });
    }
    
    function cargarResponsables() {
        if (responsablesTable) {
            responsablesTable.destroy();
        }
        
        responsablesTable = $('#table_responsables').DataTable({
            ajax: {
                url: "<?= site_url('inventario/listar_responsables') ?>",
                type: "POST",
                data: {inv_id: currentInvId},
                dataSrc: function(json) {
                    return json || [];
                }
            },
            columns: [
                {data: 'VEM_NOMBRE', defaultContent: ''},
                {
                    data: 'inr_proporcion',
                    defaultContent: '1',
                    render: function(data, type, row) {
                        return `<input type="number" class="form-control form-control-sm proporcion-input" 
                                value="${data || 1}" data-id="${row.inr_id || ''}" min="1" style="width:80px;">`;
                    }
                },
                {data: 'inr_cantidad', defaultContent: '0'},
                {
                    data: null,
                    defaultContent: '',
                    render: function(data, type, row) {
                        return `<div class="progress" style="height: 20px;">
                                    <div class="progress-bar" style="width: 0%">0%</div>
                                </div>`;
                    }
                },
                {
                    data: null,
                    defaultContent: '',
                    render: function(data, type, row) {
                        return `<button class="btn btn-sm btn-danger eliminar-responsable" data-id="${row.inr_id || ''}">
                                    <i class="fas fa-trash"></i>
                                </button>`;
                    }
                }
            ],
            paging: false,
            searching: false,
            info: false,
            processing: true
        });
    }
    
    // Actualizar proporción
    $('#table_responsables').on('change', '.proporcion-input', function() {
        const nuevaProporcion = $(this).val();
        const responsableId = $(this).data('id');
        
        $.post("<?= site_url('inventario/actualizaDistribucion') ?>", {
            inv_id: currentInvId,
            inv_cv: responsableId,
            inv_vl: nuevaProporcion,
            inv_ti: $('#current-total-items').val(),
            inv_lc: 1
        }).done(function() {
            alert('Proporción actualizada correctamente');
            responsablesTable.ajax.reload();
        });
    });
    
    // Agregar responsable
    $('#btn-agregar-responsable').click(function() {
        const empleadoId = $('#vem_codven').val();
        const proporcion = $('#proporcion').val();
        
        if (!empleadoId || !proporcion) {
            alert('Complete todos los campos');
            return;
        }
        
        $.post("<?= site_url('inventario/agregar_responsables') ?>", {
            invlc: 1,
            idinv: currentInvId,
            vem_codven: empleadoId
        }).done(function() {
            $('#nuevoResponsable').modal('hide');
            alert('Responsable agregado correctamente');
            responsablesTable.ajax.reload();
        });
    });
    
    // Distribuir productos automáticamente
    $('#distribuirProductos').click(function() {
        if (confirm('¿Está seguro de redistribuir todos los productos? Esta acción sobrescribirá las asignaciones actuales.')) {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Distribuyendo...');
            
            $.post("<?= site_url('inventario/distribuir_automatico') ?>", {
                id_local: 1,
                inv_id: currentInvId
            }).done(function(response) {
                if (response.success) {
                    alert('Productos distribuidos correctamente');
                    responsablesTable.ajax.reload();
                } else {
                    alert('Error al distribuir productos');
                }
            }).fail(function() {
                alert('Error de conexión');
            }).always(function() {
                btn.prop('disabled', false).html('<i class="fas fa-random"></i> Distribuir Productos');
            });
        }
    });
    
    // Cerrar inventario
    $('.btn-cerrar').click(function() {
        const invId = $(this).data('inv-id');
        
        if (confirm('¿Está seguro de cerrar este inventario? Esta acción no se puede deshacer.')) {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cerrando...');
            
            $.post("<?= site_url('inventario/cerrar_inventario') ?>", {
                id_local: 1,
                inv_id: invId
            }).done(function(response) {
                if (response.success) {
                    alert('Inventario cerrado correctamente');
                    location.reload();
                } else {
                    alert('Error al cerrar inventario: ' + response.message);
                }
            }).fail(function() {
                alert('Error de conexión');
            }).always(function() {
                btn.prop('disabled', false).html('<i class="fas fa-lock"></i> Cerrar');
            });
        }
    });
    
    // Generar guías
    $('.btn-generar-guias').click(function() {
        currentInvId = $(this).data('inv-id');
        $('#guiasModal').modal('show');
    });
    
    $('#btn-generar-guias').click(function() {
        const tipoGuia = $('input[name="tipo_guia"]:checked').val();
        const btn = $(this);
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generando...');
        
        $.post("<?= site_url('inventario/generar_guias') ?>", {
            id_local: 1,
            inv_id: currentInvId,
            tipo: tipoGuia
        }).done(function(response) {
            if (response.success) {
                let mensaje = 'Guías generadas correctamente:\n';
                if (response.guias.ingreso) {
                    mensaje += '\n• ' + response.guias.ingreso;
                }
                if (response.guias.salida) {
                    mensaje += '\n• ' + response.guias.salida;
                }
                alert(mensaje);
                $('#guiasModal').modal('hide');
            } else {
                alert('Error al generar guías: ' + response.message);
            }
        }).fail(function() {
            alert('Error de conexión');
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fas fa-cog"></i> Generar Guías');
        });
    });
});
</script>
<?= $this->endSection(); ?>