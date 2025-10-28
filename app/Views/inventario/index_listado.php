<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid">
    <!-- Header con información del responsable -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                <i class="fas fa-clipboard-list"></i> Inventario - Lista de Productos
                            </h4>
                            <p class="mb-0">
                                <i class="fas fa-user"></i> Responsable: <strong>Vendedor <?= $id_ven ?></strong> |
                                <i class="fas fa-cubes"></i> Total productos: <strong><?= $to_reg ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="btn-group">
                                <a href="<?= site_url('inventario/conteo/' . $id_loc . '/' . $id_inv . '/' . $id_ven) ?>" 
                                   class="btn btn-light btn-sm">
                                    <i class="fas fa-mobile-alt"></i> Modo Conteo
                                </a>
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

    <!-- Estadísticas rápidas -->
    <div class="row mb-3">
        <div class="col-md-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-asignados"><?= $to_reg ?></h3>
                    <p>Total Asignados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="total-contados">0</h3>
                    <p>Contados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="total-pendientes"><?= $to_reg ?></h3>
                    <p>Pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3 id="porcentaje-avance">0%</h3>
                    <p>Progreso</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de productos -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Productos Asignados
                    </h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 300px;">
                            <input type="text" class="form-control" placeholder="Buscar producto..." id="buscar-producto">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <input id="id_loc" type="hidden" value="<?=$id_loc?>">
                    <input id="id_inv" type="hidden" value="<?=$id_inv?>">
                    <input id="id_ven" type="hidden" value="<?=$id_ven?>">
                    
                    <table id="tabla_productos" class="table table-striped table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th width="60">#</th>
                                <th width="100">Código</th>
                                <th>Producto</th>
                                <th width="100">Stock Sistema</th>
                                <th width="120">Stock Físico</th>
                                <th width="100">Diferencia</th>
                                <th width="100">Estado</th>
                                <th width="120">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     id="barra-progreso" style="width: 0%">
                                    <span id="texto-progreso">0 de <?= $to_reg ?> productos</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-primary" id="exportar-excel">
                                    <i class="fas fa-file-excel"></i> Exportar
                                </button>
                                <button type="button" class="btn btn-outline-success" id="guardar-todo">
                                    <i class="fas fa-save"></i> Guardar Todo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<link rel="stylesheet" href="<?= site_url('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= site_url('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= site_url('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') ?>">

<script src="<?= site_url('plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-buttons/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') ?>"></script>
<script src="<?= site_url('plugins/jszip/jszip.min.js') ?>"></script>
<script src="<?= site_url('plugins/datatables-buttons/js/buttons.html5.min.js') ?>"></script>

<script>
$(document).ready(function() {
    let dtableProductos = $('#tabla_productos').DataTable({
        ajax: {
            url: "<?= site_url('inventario/lista_productos') ?>",
            type: "POST",
            dataSrc: '',
            data: {
                idlocal: () => $("#id_loc").val(),
                id_inv: () => $("#id_inv").val(),
                id_ven: () => $("#id_ven").val()
            }
        },
        processing: true,
        columns: [
            { data: 'Row', className: 'text-center' },
            { 
                data: 'art_key',
                render: function(data) {
                    return `<strong>${data}</strong>`;
                }
            },
            { 
                data: 'ART_NOMBRE',
                render: function(data, type, row) {
                    return `<div>
                                <strong>${data}</strong>
                                <br><small class="text-muted">${row.FAMILIA || ''}</small>
                            </div>`;
                }
            },
            { 
                data: 'STOCK',
                className: 'text-center',
                render: function(data) {
                    return `<span class="badge badge-info">${data}</span>`;
                }
            },
            {
                data: 'ind_stock_fisico',
                className: 'text-center',
                render: function(data, type, row) {
                    return `<input type="number" class="form-control form-control-sm stock-input" 
                                   value="${data || ''}" data-row="${row.Row}" 
                                   placeholder="0" min="0" style="width: 80px; margin: 0 auto;">`;
                }
            },
            {
                data: 'ind_diferencia',
                className: 'text-center',
                render: function(data) {
                    const valor = data || 0;
                    const clase = valor == 0 ? 'success' : (valor > 0 ? 'primary' : 'danger');
                    return `<span class="badge badge-${clase}">${valor}</span>`;
                }
            },
            {
                data: null,
                className: 'text-center',
                render: function(data, type, row) {
                    const estado = row.ind_stock_fisico ? 'contado' : 'pendiente';
                    const clase = estado == 'contado' ? 'success' : 'warning';
                    return `<span class="badge badge-${clase}">${estado.toUpperCase()}</span>`;
                }
            },
            {
                data: 'Row',
                className: 'text-center',
                render: function(data, type, row) {
                    return `<div class="btn-group btn-group-sm">
                                <button class="btn btn-primary btn-sm guardar-item" data-row="${data}" title="Guardar">
                                    <i class="fas fa-save"></i>
                                </button>
                                <a href="<?= site_url('inventario/producto/' . $id_loc . '/' . $id_inv . '/' . $id_ven . '/' . $to_reg . '/') ?>${data}" 
                                   class="btn btn-info btn-sm" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>`;
                }
            }
        ],
        responsive: true,
        pageLength: 50,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        drawCallback: function() {
            actualizarEstadisticas();
        }
    });
    
    // Buscar productos
    $('#buscar-producto').on('keyup', function() {
        dtableProductos.search(this.value).draw();
    });
    
    // Guardar item individual
    $('#tabla_productos').on('click', '.guardar-item', function() {
        const fila = $(this).closest('tr');
        const stockFisico = fila.find('.stock-input').val();
        const row = $(this).data('row');
        
        if (!stockFisico && stockFisico !== '0') {
            alert('Ingrese la cantidad física');
            return;
        }
        
        // Simular guardado
        alert('Producto guardado correctamente');
        actualizarEstadisticas();
    });
    
    // Guardar todo
    $('#guardar-todo').click(function() {
        const productosConStock = $('.stock-input').filter(function() {
            return $(this).val() !== '';
        }).length;
        
        if (productosConStock === 0) {
            alert('No hay productos con stock físico ingresado');
            return;
        }
        
        if (confirm(`¿Guardar ${productosConStock} productos con stock físico?`)) {
            // Simular guardado masivo
            alert(`${productosConStock} productos guardados correctamente`);
            actualizarEstadisticas();
        }
    });
    
    function actualizarEstadisticas() {
        const total = <?= $to_reg ?>;
        const contados = $('.stock-input').filter(function() {
            return $(this).val() !== '';
        }).length;
        const pendientes = total - contados;
        const porcentaje = total > 0 ? ((contados * 100) / total).toFixed(1) : 0;
        
        $('#total-contados').text(contados);
        $('#total-pendientes').text(pendientes);
        $('#porcentaje-avance').text(porcentaje + '%');
        
        $('#barra-progreso').css('width', porcentaje + '%');
        $('#texto-progreso').text(`${contados} de ${total} productos`);
    }
    
    // Actualizar estadísticas al cargar
    setTimeout(actualizarEstadisticas, 1000);
});
</script>
<?= $this->endSection(); ?>