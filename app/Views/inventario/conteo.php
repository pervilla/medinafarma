<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0">
                                <i class="fas fa-clipboard-check"></i> Conteo de Inventario
                            </h4>
                            <small>Responsable: <?= isset($responsable->VEM_NOMBRE) ? $responsable->VEM_NOMBRE : 'No asignado' ?></small>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="btn-group">
                                <select class="form-control form-control-sm" id="selector-responsable" style="width: 200px; display: inline-block;">
                                    <option value="">Seleccionar responsable...</option>
                                </select>
                                <button type="button" class="btn btn-light btn-sm" id="btn-buscar" data-toggle="modal" data-target="#buscarModal">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="<?= site_url('inventario/dashboard') ?>" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-3">
        <div class="col-md-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Asignados</span>
                    <span class="info-box-number" id="total-productos"><?= count($productos) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Contados</span>
                    <span class="info-box-number" id="productos-contados">
                        <?= count(array_filter($productos, function($p) { return isset($p->ind_estado) && $p->ind_estado == 'contado'; })) ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendientes</span>
                    <span class="info-box-number" id="productos-pendientes">
                        <?= count(array_filter($productos, function($p) { return !isset($p->ind_estado) || $p->ind_estado == 'pendiente'; })) ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-percentage"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Progreso</span>
                    <span class="info-box-number" id="porcentaje-progreso">
                        <?= count($productos) > 0 ? round((count(array_filter($productos, function($p) { return isset($p->ind_estado) && $p->ind_estado == 'contado'; })) * 100) / count($productos), 1) : 0 ?>%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de progreso -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             id="barra-progreso" 
                             style="width: <?= count($productos) > 0 ? (count(array_filter($productos, function($p) { return isset($p->ind_estado) && $p->ind_estado == 'contado'; })) * 100) / count($productos) : 0 ?>%">
                            <span id="texto-progreso">
                                <?= count(array_filter($productos, function($p) { return isset($p->ind_estado) && $p->ind_estado == 'contado'; })) ?> de <?= count($productos) ?> productos
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de productos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-list"></i> Productos Asignados
                    </h5>
                    <div class="card-tools">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" id="filtro-todos">Todos</button>
                            <button type="button" class="btn btn-outline-warning" id="filtro-pendientes">Pendientes</button>
                            <button type="button" class="btn btn-outline-success" id="filtro-contados">Contados</button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tabla-productos" class="table table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Stock Sistema</th>
                                    <th>Stock Físico</th>
                                    <th>Diferencia</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($productos)): ?>
                            <?php foreach ($productos as $producto): ?>
                                <tr class="producto-row" data-estado="<?= isset($producto->ind_estado) ? $producto->ind_estado : 'pendiente' ?>">
                                    <td><strong><?= $producto->art_key ?></strong></td>
                                    <td>
                                        <div class="producto-info">
                                            <strong><?= $producto->ART_NOMBRE ?></strong>
                                            <br><small class="text-muted"><?= isset($producto->FAMILIA) ? $producto->FAMILIA : '' ?></small>
                                            <br><small class="text-info">Unidad: <?= isset($producto->PRE_UNIDAD) ? $producto->PRE_UNIDAD : 'UND' ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= isset($producto->STOCK_DISPLAY) ? $producto->STOCK_DISPLAY : $producto->ARM_STOCK ?>
                                        </span>
                                        <br><small class="text-muted"><?= isset($producto->PRE_UNIDAD) ? $producto->PRE_UNIDAD : 'UND' ?></small>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               class="form-control form-control-sm stock-fisico-input" 
                                               value="<?= isset($producto->ind_stock_fisico) ? $producto->ind_stock_fisico : '' ?>"
                                               data-producto-id="<?= $producto->art_key ?>"
                                               data-equiv="<?= isset($producto->PRE_EQUIV) ? $producto->PRE_EQUIV : 1 ?>"
                                               placeholder="En unidades"
                                               min="0"
                                               step="1">
                                        <small class="text-muted">Contar en unidades individuales</small>
                                    </td>
                                    <td>
                                        <span class="diferencia-badge badge 
                                            <?= (isset($producto->ind_diferencia) && $producto->ind_diferencia == 0) ? 'badge-success' : 
                                                ((isset($producto->ind_diferencia) && $producto->ind_diferencia > 0) ? 'badge-primary' : 'badge-danger') ?>">
                                            <?= isset($producto->ind_diferencia_display) ? $producto->ind_diferencia_display : '0' ?>
                                        </span>
                                        <br><small class="text-muted"><?= isset($producto->PRE_UNIDAD) ? $producto->PRE_UNIDAD : 'UND' ?></small>
                                    </td>
                                    <td>
                                        <span class="estado-badge badge 
                                            <?= (isset($producto->ind_estado) && $producto->ind_estado == 'contado') ? 'badge-success' : 'badge-warning' ?>">
                                            <?= isset($producto->ind_estado) ? ucfirst($producto->ind_estado) : 'Pendiente' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-primary btn-contar" 
                                                    data-producto-id="<?= $producto->art_key ?>"
                                                    data-ind-id="<?= isset($producto->Row) ? $producto->Row : $producto->art_key ?>">
                                                <i class="fas fa-save"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-info btn-observaciones" 
                                                    data-producto-id="<?= $producto->art_key ?>"
                                                    data-observaciones="<?= isset($producto->ind_observaciones) ? $producto->ind_observaciones : '' ?>">
                                                <i class="fas fa-comment"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No hay productos asignados</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buscar Producto -->
<div class="modal fade" id="buscarModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-search"></i> Buscar Producto</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="busqueda-input" 
                           placeholder="Ingrese código o nombre del producto...">
                </div>
                <div id="resultados-busqueda"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Observaciones -->
<div class="modal fade" id="observacionesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-comment"></i> Observaciones</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="observaciones-texto">Comentarios sobre el producto:</label>
                    <textarea class="form-control" id="observaciones-texto" rows="3" 
                              placeholder="Ej: Producto dañado, ubicación incorrecta, etc."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="guardar-observaciones">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="id-local" value="<?= $id_local ?>">
<input type="hidden" id="id-inv" value="<?= $id_inv ?>">
<input type="hidden" id="id-ven" value="<?= $id_ven ?>">
<input type="hidden" id="producto-actual" value="">

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
    // Cargar responsables disponibles
    cargarResponsables();
    
    let tablaProductos = $('#tabla-productos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        pageLength: 25,
        order: [[5, 'asc']], // Ordenar por estado
        columnDefs: [
            { orderable: false, targets: [6] }
        ]
    });
    
    // Filtros
    $('#filtro-todos').click(function() {
        tablaProductos.column(5).search('').draw();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
    
    $('#filtro-pendientes').click(function() {
        tablaProductos.column(5).search('Pendiente').draw();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
    
    $('#filtro-contados').click(function() {
        tablaProductos.column(5).search('Contado').draw();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
    
    // Auto-actualizar al cambiar stock físico
    $(document).on('change', '.stock-fisico-input', function() {
        const fila = $(this).closest('tr');
        const stockFisico = $(this).val();
        const productoId = fila.find('.btn-contar').data('producto-id');
        
        if (stockFisico !== '' && productoId) {
            actualizarConteo(fila, productoId, stockFisico, false);
        }
    });
    
    // Contar producto (botón)
    $('.btn-contar').click(function() {
        const productoId = $(this).data('producto-id');
        const fila = $(this).closest('tr');
        const stockFisico = fila.find('.stock-fisico-input').val();
        
        if (!stockFisico && stockFisico !== '0') {
            alert('Ingrese la cantidad física del producto');
            return;
        }
        
        actualizarConteo(fila, productoId, stockFisico, true);
    });
    
    function actualizarConteo(fila, productoId, stockFisico, mostrarAlerta) {
        $.post("<?= site_url('inventario/actualizar_conteo') ?>", {
            id_local: $('#id-local').val(),
            art_key: productoId,
            inv_id: $('#id-inv').val(),
            vem_codven: $('#id-ven').val(),
            stock_fisico: stockFisico,
            observaciones: ''
        }).done(function(response) {
            if (response.success) {
                if (mostrarAlerta) {
                    alert('Conteo actualizado correctamente');
                }
                
                // Actualizar la fila
                const equiv = parseInt(fila.find('.stock-fisico-input').data('equiv')) || 1;
                const stockSistemaText = fila.find('td:eq(2) .badge').text();
                let stockSistemaUnidades;
                
                // Convertir stock del sistema a unidades
                if (stockSistemaText.includes('/')) {
                    const partes = stockSistemaText.split('/');
                    stockSistemaUnidades = (parseInt(partes[0]) * equiv) + parseInt(partes[1]);
                } else {
                    stockSistemaUnidades = parseInt(stockSistemaText) * (equiv > 1 ? equiv : 1);
                }
                
                const diferenciaUnidades = parseInt(stockFisico) - stockSistemaUnidades;
                let diferenciaDisplay;
                
                if (equiv > 1) {
                    const cajas = Math.floor(Math.abs(diferenciaUnidades) / equiv);
                    const unidades = Math.abs(diferenciaUnidades) % equiv;
                    const signo = diferenciaUnidades < 0 ? '-' : '';
                    diferenciaDisplay = signo + cajas + '/' + unidades;
                } else {
                    diferenciaDisplay = diferenciaUnidades.toString();
                }
                
                fila.find('.diferencia-badge')
                    .text(diferenciaDisplay)
                    .removeClass('badge-success badge-primary badge-danger')
                    .addClass(diferenciaUnidades == 0 ? 'badge-success' : (diferenciaUnidades > 0 ? 'badge-primary' : 'badge-danger'));
                
                fila.find('.estado-badge')
                    .text('Contado')
                    .removeClass('badge-warning badge-secondary')
                    .addClass('badge-success');
                
                fila.attr('data-estado', 'contado');
                
                actualizarEstadisticas();
            }
        }).fail(function() {
            if (mostrarAlerta) {
                alert('Error al actualizar el conteo');
            }
        });
    }
    
    // Observaciones
    $('.btn-observaciones').click(function() {
        $('#producto-actual').val($(this).data('producto-id'));
        $('#observaciones-texto').val($(this).data('observaciones'));
        $('#observacionesModal').modal('show');
    });
    
    $('#guardar-observaciones').click(function() {
        const productoId = $('#producto-actual').val();
        const observaciones = $('#observaciones-texto').val();
        
        // Aquí guardarías las observaciones
        alert('Observaciones guardadas');
        $('#observacionesModal').modal('hide');
    });
    
    // Buscar producto
    $('#busqueda-input').on('input', function() {
        const busqueda = $(this).val();
        if (busqueda.length >= 3) {
            $.post("<?= site_url('inventario/buscar_producto') ?>", {
                id_local: $('#id-local').val(),
                id_inv: $('#id-inv').val(),
                id_ven: $('#id-ven').val(),
                busqueda: busqueda
            }).done(function(productos) {
                mostrarResultadosBusqueda(productos);
            });
        }
    });
    
    function mostrarResultadosBusqueda(productos) {
        let html = '<div class="list-group">';
        productos.forEach(function(producto) {
            html += `<div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${producto.art_key}</strong> - ${producto.ART_NOMBRE}
                                <br><small class="text-muted">Stock: ${producto.ARM_STOCK}</small>
                            </div>
                            <button class="btn btn-sm btn-primary ir-producto" data-codigo="${producto.art_key}">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>`;
        });
        html += '</div>';
        $('#resultados-busqueda').html(html);
    }
    
    // Ir a producto desde búsqueda
    $(document).on('click', '.ir-producto', function() {
        const codigo = $(this).data('codigo');
        const fila = $(`tr:contains(${codigo})`).first();
        
        if (fila.length) {
            $('#buscarModal').modal('hide');
            $('html, body').animate({
                scrollTop: fila.offset().top - 100
            }, 500);
            fila.addClass('table-warning');
            setTimeout(() => fila.removeClass('table-warning'), 3000);
        }
    });
    
    function actualizarEstadisticas() {
        const total = $('.producto-row').length;
        const contados = $('.producto-row[data-estado="contado"]').length;
        const pendientes = total - contados;
        const porcentaje = total > 0 ? (contados * 100 / total).toFixed(1) : 0;
        
        $('#productos-contados').text(contados);
        $('#productos-pendientes').text(pendientes);
        $('#porcentaje-progreso').text(porcentaje + '%');
        
        $('#barra-progreso').css('width', porcentaje + '%');
        $('#texto-progreso').text(`${contados} de ${total} productos`);
    }
    
    // Cambiar responsable
    $('#selector-responsable').change(function() {
        const nuevoVendedor = $(this).val();
        if (nuevoVendedor) {
            const url = `<?= site_url('inventario/conteo') ?>/${$('#id-local').val()}/${$('#id-inv').val()}/${nuevoVendedor}`;
            window.location.href = url;
        }
    });
    
    function cargarResponsables() {
        $.post("<?= site_url('inventario/listar_responsables') ?>", {
            inv_id: $('#id-inv').val()
        }).done(function(responsables) {
            const selector = $('#selector-responsable');
            selector.empty().append('<option value="">Seleccionar responsable...</option>');
            
            if (responsables && responsables.length > 0) {
                responsables.forEach(function(resp) {
                    const selected = resp.vem_codven == $('#id-ven').val() ? 'selected' : '';
                    selector.append(`<option value="${resp.vem_codven}" ${selected}>${resp.VEM_NOMBRE}</option>`);
                });
            }
        });
    }
    
    // Auto-guardar deshabilitado temporalmente
    // setInterval(function() {
    //     $('.producto-row[data-estado="pendiente"]').each(function() {
    //         const fila = $(this);
    //         const stockFisico = fila.find('.stock-fisico-input').val();
    //         const indId = fila.find('.btn-contar').data('ind-id');
    //         
    //         if (stockFisico && stockFisico !== '' && indId) {
    //             $.post("<?= site_url('inventario/actualizar_conteo') ?>", {
    //                 id_local: $('#id-local').val(),
    //                 ind_id: indId,
    //                 stock_fisico: stockFisico,
    //                 observaciones: ''
    //             });
    //         }
    //     });
    // }, 30000);
});
</script>
<?= $this->endSection(); ?>