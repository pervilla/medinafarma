<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<style>
    .search-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .search-input-group {
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        border-radius: 50px;
        overflow: hidden;
        background: white;
        display: flex;
        align-items: center;
        padding: 5px 15px;
    }
    .search-input-group input {
        border: none !important;
        box-shadow: none !important;
        font-size: 1.2rem;
        padding: 10px 15px;
    }
    .search-input-group .btn-search {
        border-radius: 50px;
        padding: 10px 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }
    .card-header {
        border-radius: 12px 12px 0 0 !important;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.9rem;
    }
    .table thead th {
        border-top: none;
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .bg-transit {
        background-color: #fff3cd !important;
        border-left: 4px solid #ffc107;
    }
    .selected {
        background-color: #e8f0fe !important;
        border-left: 4px solid #4285f4;
    }
    .badge-stock {
        font-size: 0.9rem;
        padding: 5px 10px;
        border-radius: 6px;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Localizador de Productos</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Buscador Principal -->
        <div class="search-container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="search-input-group">
                        <i class="fas fa-search text-muted ml-2"></i>
                        <input type="text" class="form-control" id="busqueda" placeholder="Escribe el nombre del producto o principio activo..." autocomplete="off">
                        <button type="button" id="buscar" class="btn btn-primary btn-search shadow-sm">
                            <i class="fa fa-bolt mr-1"></i> Buscar
                        </button>
                    </div>
                    <small class="text-muted mt-2 d-block text-center">
                        <i class="fas fa-info-circle mr-1"></i> Tip: Puedes buscar por sinónimos o marcas genéricas. Presiona ENTER para buscar.
                    </small>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Panel Izquierdo: Resultados Principales -->
            <div class="col-lg-8">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-pills mr-2"></i> Stock en Sede Actual</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="productos_centro" class="table table-hover table-striped mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre del Producto</th>
                                        <th>Principio Activo</th>
                                        <th class="text-center">Stock (C/U)</th>
                                        <th>P. Unit.</th>
                                        <th>P. Empaque</th>
                                        <th>Laboratorio</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- NUEVA SECCIÓN: Productos en Tránsito -->
                <div class="card card-outline card-warning mt-4">
                    <div class="card-header">
                        <h3 class="card-title text-dark"><i class="fas fa-truck-loading mr-2"></i> Próximos Ingresos (En Tránsito)</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="productos_transito" class="table table-sm mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cant.</th>
                                        <th>Factura</th>
                                        <th>Fecha Compra</th>
                                        <th>Proveedor</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-light">
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">Realiza una búsqueda para ver ingresos pendientes...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Derecho: Detalles e Información Extra -->
            <div class="col-lg-4">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-map-marker-alt mr-2"></i> Stock Otros Locales</h3>
                    </div>
                    <div class="card-body p-0">
                        <table id="productos_medina3" class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Sede</th>
                                    <th>Producto</th>
                                    <th class="text-center">Stock</th>
                                    <th width="40"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="card card-info mt-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i> Sugerencias / Equivalentes</h3>
                    </div>
                    <div class="card-body p-0">
                        <table id="productos_equival" class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Sede</th>
                                    <th>Producto</th>
                                    <th class="text-center">Stock</th>
                                    <th width="40"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales (se mantienen pero se pueden pulir después) -->
    <div class="modal fade" id="modal-overlay">
        <div class="modal-dialog modal-lg shadow-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title">Ficha Detallada del Producto</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row">
                        <div class="col-sm-8"><label>Nombre:</label><input class="form-control mb-2" disabled id="nombreProducto" type="text"></div>
                        <div class="col-sm-4"><label>Precio Unitario:</label><input class="form-control mb-2" disabled name="precio2" type="text"></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8"><label>Presentación:</label><input class="form-control mb-2" disabled id="presentacion" type="text"></div>
                        <div class="col-sm-4"><label>Precio Empaque:</label><input class="form-control mb-2" disabled name="precioV" type="text"></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><label>Reg. Sanitario:</label><input class="form-control" disabled id="registroSanitario" type="text"></div>
                        <div class="col-sm-4"><label>Concentración:</label><input class="form-control" disabled id="Concent" type="text"></div>
                        <div class="col-sm-4"><label>Forma Farm.:</label><input class="form-control" disabled id="Presentac" type="text"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicialización de Datatable Principal
        var dtable = $('#productos_centro').DataTable({
            ajax: {
                url: "<?= site_url('productos/get_productos') ?>",
                type: "POST",
                dataSrc: '',
                data: function(d) {
                    d.busqueda = $("#busqueda").val() || 'A';
                }
            },
            columns: [
                { data: 'ARM_CODART', className: 'text-muted small' },
                { 
                    data: 'ART_NOMBRE',
                    render: function(data, type, row) {
                        let icon = row.CNTLD == 'C' ? "<span class='text-danger mr-1' title='Controlado'><i class='fas fa-copyright'></i></span>" : "";
                        return `<strong>${icon}${data}</strong>`;
                    }
                },
                { 
                    data: 'Nom_IFA',
                    render: function(data) {
                        return data ? `<small class="text-primary"><i>${data}</i></small>` : `<small class="text-muted">No relacionado</small>`;
                    }
                },
                { 
                    data: 'STOCK', 
                    className: 'text-center',
                    render: function(data) {
                        return `<span class="badge badge-stock ${data.includes('/') && data.split('/')[0] > 0 ? 'bg-success' : (data > 0 ? 'bg-success' : 'bg-secondary')}">${data}</span>`;
                    }
                },
                { 
                    data: 'PRE_UND',
                    render: data => `S/. ${parseFloat(data).toFixed(2)}`
                },
                { 
                    data: 'PRE_CAJA',
                    render: data => `S/. ${parseFloat(data).toFixed(2)}`
                },
                { data: 'TAB_NOMLARGO', className: 'small' },
                { 
                    data: 'ARM_CODART',
                    render: function(data) {
                        return `<button class="btn btn-xs btn-outline-info" data-toggle="modal" data-target="#modal-overlay" data-id="${data}"><i class="fa fa-info-circle"></i></button>`;
                    }
                }
            ],
            language: {
                emptyTable: "No se encontraron productos",
                loadingRecords: "Buscando productos...",
                processing: "Procesando..."
            },
            searching: false,
            paging: true,
            responsive: true,
            order: [[1, 'asc']]
        });

        // Función para buscar en tránsito
        function buscarTransito(query) {
            if (!query) return;
            
            $("#productos_transito tbody").html('<tr><td colspan="5" class="text-center py-2"><i class="fas fa-sync fa-spin"></i> Consultando tránsito...</td></tr>');
            
            $.post("<?= site_url('productos/get_transito') ?>", { busqueda: query }, function(data) {
                let html = '';
                if (data && data.length > 0) {
                    data.forEach(item => {
                        html += `<tr class="bg-transit">
                            <td>${item.ART_NOMBRE}</td>
                            <td>${item.CANTIDAD}</td>
                            <td>${item.NRO_FACTURA}</td>
                            <td>${item.FECHA_DOC}</td>
                            <td><small>${item.PROVEEDOR}</small></td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center text-muted py-2">No hay ingresos próximos para esta búsqueda.</td></tr>';
                }
                $("#productos_transito tbody").html(html);
            });
        }

        // Eventos de Búsqueda
        $("#buscar").click(function() {
            let query = $("#busqueda").val();
            dtable.ajax.reload();
            buscarTransito(query);
        });

        $('#busqueda').on('keypress', function(e) {
            if (e.which == 13) $("#buscar").trigger('click');
        });

        // Click en fila para cargar stock externo y equivalentes
        $('#productos_centro tbody').on('click', 'tr', function() {
            let data = dtable.row(this).data();
            if (!data) return;

            $(this).addClass('selected').siblings().removeClass('selected');
            
            // Limpiar paneles
            $("#productos_medina3 tbody, #productos_equival tbody").html('<tr><td colspan="4" class="text-center"><i class="fas fa-sync fa-spin"></i></td></tr>');

            // Cargar Stock Locales
            [2, 3].forEach(localId => {
                $.post("<?= site_url('productos/get_stock') ?>", { artkey: data.ARM_CODART, local: localId }, function(htm) {
                    if (localId == 2) $("#productos_medina3 tbody").html(''); // Limpiar en la primera respuesta
                    $("#productos_medina3 tbody").append(htm);
                });
            });

            // Cargar Equivalentes (Smart: Prioriza Principio Activo)
            let equivParams = data.Nom_IFA ? { ifa: data.Nom_IFA, local: 1 } : { artkey: '', artsbg: data.ART_SUBGRU, local: 1 };
            $.post("<?= site_url('productos/get_stock') ?>", equivParams, function(htm) {
                $("#productos_equival tbody").html(htm);
            });
        });

        // Manejo de Modal de Detalles
        $('#modal-overlay').on('shown.bs.modal', function(e) {
            let id = $(e.relatedTarget).data('id');
            $.post("<?= site_url('productos/get_precios_digemid') ?>", { artkey: id }, function(res) {
                let json = JSON.parse(res);
                if (json && json[0]) {
                    let v = json[0];
                    $("#nombreProducto").val(v.Nom_Prod);
                    $("#presentacion").val(v.Presentac);
                    $("#registroSanitario").val(v.Num_RegSan);
                    $("#nombreTitular").val(v.Nom_Titular);
                    $("#Concent").val(v.Concent);
                    $("#Presentac").val(v.Presentac);
                }
            });
        });

        // Limpiar búsqueda con ESC
        $(document).keydown(function(e) {
            if (e.key === "Escape") {
                $("#busqueda").val('').focus();
            }
        });
    });
</script>

<?= $this->endSection(); ?>
