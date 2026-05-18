<?= $this->extend('templates/admin_template') ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-link mr-2"></i>Gestión de Relaciones DIGEMID</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Stats Row -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="stat-total-digemid"><?= number_format($stats->total_digemid) ?></h3>
                            <p>Productos DIGEMID (ACT)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-pills"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="stat-total-relacionados"><?= number_format($stats->total_relacionados) ?></h3>
                            <p>Productos Relacionados</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="stat-sin-relacionar"><?= number_format($stats->total_digemid - $stats->total_relacionados) ?></h3>
                            <p>Sin Relacionar</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-unlink"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="stat-huerfanas"><?= number_format($huerfanas) ?></h3>
                            <p>Relaciones Huérfanas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-ghost"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline card-tabs">
                        <div class="card-header p-0 pt-1 border-bottom-0">
                            <ul class="nav nav-tabs" id="digemid-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-vincular-link" data-toggle="pill" href="#tab-vincular" role="tab"><i class="fas fa-plus-circle mr-1"></i> Nueva Vinculación</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-relacionados-link" data-toggle="pill" href="#tab-relacionados" role="tab"><i class="fas fa-list mr-1"></i> Relacionados</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-huerfanas-link" data-toggle="pill" href="#tab-huerfanas" role="tab"><i class="fas fa-ghost mr-1"></i> Huérfanas</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-sin-relacion-link" data-toggle="pill" href="#tab-sin-relacion" role="tab"><i class="fas fa-eye-slash mr-1"></i> Sin Relacionar</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- TAB: VINCULAR -->
                                <div class="tab-pane fade show active" id="tab-vincular" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card card-secondary">
                                                <div class="card-header">
                                                    <h3 class="card-title">1. Seleccionar Producto DIGEMID</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="input-group mb-3">
                                                        <input type="text" id="search-digemid" class="form-control" placeholder="Nombre del producto...">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                        </div>
                                                    </div>
                                                    <div id="results-digemid" class="list-container" style="max-height: 400px; overflow-y: auto;">
                                                        <p class="text-muted text-center py-4">Inicie la búsqueda escribiendo el nombre...</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card card-secondary">
                                                <div class="card-header">
                                                    <h3 class="card-title">2. Seleccionar Artículo Medinafarma</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="input-group mb-3">
                                                        <input type="text" id="search-medina" class="form-control" placeholder="Nombre del artículo interno...">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                        </div>
                                                    </div>
                                                    <div id="results-medina" class="list-container" style="max-height: 400px; overflow-y: auto;">
                                                        <p class="text-muted text-center py-4">Inicie la búsqueda escribiendo el nombre...</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12 text-center">
                                            <div id="selection-summary" class="alert alert-info d-none">
                                                <span id="summary-text"></span>
                                            </div>
                                            <button id="btn-create-relation" class="btn btn-success btn-lg px-5" disabled>
                                                <i class="fas fa-link mr-2"></i> Crear Vinculación
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB: RELACIONADOS -->
                                <div class="tab-pane fade" id="tab-relacionados" role="tabpanel">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <input type="text" id="search-list-relacionados" class="form-control" placeholder="Buscar en relacionados...">
                                                <div class="input-group-append">
                                                    <button class="btn btn-default" type="button" id="btn-search-relacionados"><i class="fas fa-search"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped table-bordered text-sm" id="table-relacionados">
                                            <thead class="bg-dark text-white">
                                                <tr>
                                                    <th>Cod. Prod</th>
                                                    <th>Producto DIGEMID</th>
                                                    <th>Artículo Medinafarma</th>
                                                    <th class="text-center">Estado</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <div id="pagination-relacionados" class="mt-3"></div>
                                </div>

                                <!-- TAB: HUERFANAS -->
                                <div class="tab-pane fade" id="tab-huerfanas" role="tabpanel">
                                    <div class="alert alert-warning">
                                        <h5><i class="icon fas fa-exclamation-triangle"></i> Relaciones Huérfanas</h5>
                                        Son vínculos que existen en la tabla de relaciones pero cuyo Código DIGEMID ya no existe en el catálogo actualizado. Se recomienda eliminarlas para evitar inconsistencias.
                                    </div>
                                    <div class="mb-3">
                                        <button class="btn btn-danger" id="btn-delete-all-huerfanas">
                                            <i class="fas fa-trash-alt mr-1"></i> Eliminar Todas las Huérfanas
                                        </button>
                                        <button class="btn btn-default float-right" id="btn-refresh-huerfanas">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered text-sm" id="table-huerfanas">
                                            <thead class="bg-gray">
                                                <tr>
                                                    <th>Cod. Prod Huérfano</th>
                                                    <th>Artículo Medinafarma Vinculado</th>
                                                    <th>Observación</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- TAB: SIN RELACIONAR -->
                                <div class="tab-pane fade" id="tab-sin-relacion" role="tabpanel">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <input type="text" id="search-list-sin-relacion" class="form-control" placeholder="Buscar artículo sin vínculo...">
                                                <div class="input-group-append">
                                                    <button class="btn btn-default" type="button" id="btn-search-sin-relacion"><i class="fas fa-search"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-center">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="filter-stock-only">
                                                <label class="custom-control-label" for="filter-stock-only">Solo con Stock > 0</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped table-bordered text-sm" id="table-sin-relacion">
                                            <thead class="bg-dark text-white">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Artículo Medinafarma</th>
                                                    <th>Laboratorio</th>
                                                    <th class="text-center">Stock</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <div id="pagination-sin-relacion" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Vincular Rápido -->
<div class="modal fade" id="modal-quick-link" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-link mr-2"></i> Vincular Artículo</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    Estás vinculando el artículo interno: <br>
                    <h4 id="quick-link-medina-name" class="mb-0 font-weight-bold"></h4>
                    <input type="hidden" id="quick-link-medina-id">
                </div>

                <div class="form-group">
                    <label>Buscar en Catálogo DIGEMID:</label>
                    <div class="input-group">
                        <input type="text" id="search-quick-digemid" class="form-control" placeholder="Escriba nombre o código DIGEMID...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>

                <div id="results-quick-digemid" class="list-container" style="max-height: 350px; overflow-y: auto;">
                    <p class="text-muted text-center py-4">Ingrese al menos 3 caracteres para buscar...</p>
                </div>
            </div>
            <div class="modal-footer">
                <div id="quick-link-summary" class="mr-auto d-none text-sm">
                    <i class="fas fa-arrow-right"></i> Seleccionado: <b id="quick-link-digemid-name"></b>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn-save-quick-link" class="btn btn-success" disabled>
                    <i class="fas fa-save mr-1"></i> Guardar Vínculo
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .list-container .item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background 0.2s;
    }
    .list-container .item:hover {
        background-color: #f4f6f9;
    }
    .list-container .item.active {
        background-color: #007bff;
        color: white;
    }
    .list-container .item.active .text-muted {
        color: #e0e0e0 !important;
    }
    .list-container .item:last-child {
        border-bottom: none;
    }
    .badge-status {
        width: 10px;
        height: 10px;
        display: inline-block;
        border-radius: 50%;
        margin-right: 5px;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<script>
    let selectedDigemid = null;
    let selectedMedina = null;
    
    // Pagination state
    let currentPageRelacionados = 1;
    let currentPageHuerfanas = 1;
    let currentPageSinRelacion = 1;

    $(document).ready(function() {
        // Tab events
        $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
            const target = $(e.target).attr("href");
            if (target === '#tab-relacionados') loadRelacionados(currentPageRelacionados);
            if (target === '#tab-huerfanas') loadHuerfanas(currentPageHuerfanas);
            if (target === '#tab-sin-relacion') loadSinRelacionar(currentPageSinRelacion);
        });

        // Search Digemid for Linking
        let digemidTimer;
        $('#search-digemid').on('keyup', function() {
            clearTimeout(digemidTimer);
            const val = $(this).val();
            if (val.length < 3) return;
            digemidTimer = setTimeout(() => searchDigemid(val), 500);
        });

        // Search Medina for Linking
        let medinaTimer;
        $('#search-medina').on('keyup', function() {
            clearTimeout(medinaTimer);
            const val = $(this).val();
            if (val.length < 3) return;
            medinaTimer = setTimeout(() => searchMedina(val), 500);
        });

        // Create Relation
        $('#btn-create-relation').click(createRelation);

        // Relacionados search and pagination
        $('#btn-search-relacionados').click(() => loadRelacionados(1));
        $('#search-list-relacionados').keypress(function(e) { if(e.which == 13) loadRelacionados(1); });

        // Sin Relacionar search and pagination
        $('#btn-search-sin-relacion').click(() => loadSinRelacionar(1));
        $('#search-list-sin-relacion').keypress(function(e) { if(e.which == 13) loadSinRelacionar(1); });

        // Refresh Huerfanas
        $('#btn-refresh-huerfanas').click(loadHuerfanas);

        // Delete all huerfanas
        $('#btn-delete-all-huerfanas').click(deleteAllHuerfanas);
    });

    // --- Vinculacion Logic ---

    function searchDigemid(query) {
        $('#results-digemid').html('<p class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Buscando...</p>');
        $.post('<?= site_url('digemidrelacion/buscar') ?>', { termino: query }, function(data) {
            let html = '';
            if (data.length === 0) {
                html = '<p class="text-center py-4">No se encontraron resultados.</p>';
            } else {
                data.forEach(item => {
                    const badge = item.ya_relacionado == 1 ? '<span class="badge badge-success float-right">Vinculado</span>' : '';
                    html += `<div class="item" data-id="${item.Cod_Prod}" data-name="${item.Nom_Prod.replace(/'/g, "&apos;")}" data-fraccion="${item.Fracciones}">
                        ${badge}
                        <strong>${item.Nom_Prod}</strong><br>
                        <small class="text-muted">${item.Concent} | ${item.Nom_Form_Farm} | ${item.Presentac}</small><br>
                        <small class="text-info font-weight-bold">Fracción DIGEMID: ${item.Fracciones}</small><br>
                        <small class="text-xs text-muted">${item.Nom_Titular}</small>
                    </div>`;
                });
            }
            $('#results-digemid').html(html);

            $('#results-digemid .item').click(function() {
                $('#results-digemid .item').removeClass('active');
                $(this).addClass('active');
                selectedDigemid = { 
                    id: $(this).data('id'), 
                    name: $(this).data('name'),
                    fraccion: $(this).data('fraccion')
                };
                updateSelectionSummary();
            });
        });
    }

    function searchMedina(query) {
        $('#results-medina').html('<p class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Buscando...</p>');
        $.post('<?= site_url('digemidrelacion/buscarArticulos') ?>', { termino: query }, function(data) {
            let html = '';
            if (data.length === 0) {
                html = '<p class="text-center py-4">No se encontraron resultados.</p>';
            } else {
                data.forEach(item => {
                    html += `<div class="item" data-id="${item.ART_KEY}" data-name="${item.ART_NOMBRE.replace(/'/g, "&apos;")}" data-fraccion="${item.fraccion || 'N/A'}">
                        <strong>${item.ART_NOMBRE}</strong><br>
                        <small class="text-muted">ID: ${item.ART_KEY}</small><br>
                        <small class="text-success font-weight-bold">Fracción Medina: ${item.fraccion || '?'}</small>
                    </div>`;
                });
            }
            $('#results-medina').html(html);

            $('#results-medina .item').click(function() {
                $('#results-medina .item').removeClass('active');
                $(this).addClass('active');
                selectedMedina = { 
                    id: $(this).data('id'), 
                    name: $(this).data('name'),
                    fraccion: $(this).data('fraccion')
                };
                updateSelectionSummary();
            });
        });
    }

    function updateSelectionSummary() {
        if (selectedDigemid && selectedMedina) {
            const warning = (selectedDigemid.fraccion != selectedMedina.fraccion) 
                ? `<br><span class="text-danger small"><i class="fas fa-exclamation-triangle"></i> Las fracciones no coinciden (${selectedDigemid.fraccion} vs ${selectedMedina.fraccion})</span>`
                : `<br><span class="text-success small"><i class="fas fa-check-double"></i> Fracciones coinciden (${selectedDigemid.fraccion})</span>`;

            $('#summary-text').html(`Vinculando <b>${selectedDigemid.name}</b> con <b>${selectedMedina.name}</b> ${warning}`);
            $('#selection-summary').removeClass('d-none');
            $('#btn-create-relation').prop('disabled', false);
        } else {
            $('#selection-summary').addClass('d-none');
            $('#btn-create-relation').prop('disabled', true);
        }
    }

    function createRelation() {
        if (!selectedDigemid || !selectedMedina) return;
        
        const btn = $('#btn-create-relation');
        const originalHtml = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Vinculando...').prop('disabled', true);

        $.post('<?= site_url('digemidrelacion/relacionar') ?>', {
            cod_prod: selectedDigemid.id,
            pre_codeart: selectedMedina.id
        }, function(response) {
            if (response.success) {
                toastr.success('Vínculo creado correctamente');
                // Reset linking state
                selectedDigemid = null;
                selectedMedina = null;
                $('#search-digemid, #search-medina').val('');
                $('#results-digemid, #results-medina').html('<p class="text-muted text-center py-4">Inicie la búsqueda escribiendo el nombre...</p>');
                updateSelectionSummary();
                refreshStats();
            } else {
                toastr.error('Error al crear el vínculo: ' + (response.message || 'Error desconocido'));
            }
        }).fail(function(xhr) {
            console.error("Error en vinculación:", xhr.responseText);
            toastr.error('Error de servidor al procesar la vinculación');
        }).always(function() {
            btn.html(originalHtml).prop('disabled', false);
        });
    }

    // --- Tab Relacionados ---

    function loadRelacionados(page = 1) {
        currentPageRelacionados = page;
        const query = $('#search-list-relacionados').val();
        const tbody = $('#table-relacionados tbody');
        tbody.html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando datos...</td></tr>');

        $.post('<?= site_url('digemidrelacion/relacionados') ?>', { 
            busqueda: query, 
            pagina: page 
        }, function(res) {
            let html = '';
            if (res.datos.length === 0) {
                html = '<tr><td colspan="5" class="text-center">No se encontraron vínculos.</td></tr>';
            } else {
                res.datos.forEach(row => {
                    const statusClass = row.ESTADO == 1 ? 'bg-success' : 'bg-danger';
                    const statusText = row.ESTADO == 1 ? 'Activo' : 'Inactivo';
                    const escapedName = row.Nom_Prod.replace(/'/g, "&apos;");
                    html += `<tr>
                        <td>${row.Cod_Prod}</td>
                        <td>
                            <b>${row.Nom_Prod}</b><br>
                            <small class="text-muted">${row.Concent} | ${row.Nom_Form_Farm}</small><br>
                            <small class="text-info"><b>Frac:</b> ${row.Fracciones} | <b>Titular:</b> ${row.Nom_Titular}</small>
                        </td>
                        <td>
                            <b>${row.ART_NOMBRE}</b><br>
                            <small class="text-success"><b>Lab:</b> ${row.Laboratorio || 'N/A'}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge ${statusClass}">${statusText}</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-danger btn-delete-rel" data-id="${row.Cod_Prod}" data-name="${escapedName}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
            }
            tbody.html(html);
            renderPagination('pagination-relacionados', res, loadRelacionados);
            
            // Event listener para botones de eliminar
            $('.btn-delete-rel').click(function() {
                deleteRelation($(this).data('id'), $(this).data('name'));
            });
        });
    }

    // --- Tab Huérfanas ---

    function loadHuerfanas(page = 1) {
        currentPageHuerfanas = page;
        const tbody = $('#table-huerfanas tbody');
        tbody.html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Analizando relaciones...</td></tr>');

        $.get('<?= site_url('digemidrelacion/huerfanas') ?>', function(res) {
            let html = '';
            if (res.datos.length === 0) {
                html = '<tr><td colspan="4" class="text-center">¡Genial! No se encontraron relaciones huérfanas.</td></tr>';
                $('#btn-delete-all-huerfanas').hide();
            } else {
                $('#btn-delete-all-huerfanas').show().text(`Eliminar ${res.total} Huérfanas`);
                res.datos.forEach(row => {
                    const escapedName = (row.ART_NOMBRE || 'Huérfana').replace(/'/g, "&apos;");
                    html += `<tr>
                        <td><code>${row.Cod_Prod}</code></td>
                        <td>${row.ART_NOMBRE || 'Articulo no encontrado'}</td>
                        <td><small class="text-danger">${row.OBSERVACION || 'Sin info en DIGEMID'}</small></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-danger btn-delete-huerfana" data-id="${row.Cod_Prod}" data-name="${escapedName}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
            }
            tbody.html(html);
            
            $('.btn-delete-huerfana').click(function() {
                deleteRelation($(this).data('id'), $(this).data('name'));
            });
        });
    }

    function deleteAllHuerfanas() {
        if (!confirm('¿Está seguro de eliminar TODAS las relaciones huérfanas? Esta acción no se puede deshacer.')) return;

        $.get('<?= site_url('digemidrelacion/eliminarHuerfanas') ?>', function(res) {
            if (res.success) {
                toastr.success(`Se eliminaron ${res.eliminadas} registros huérfanos.`);
                loadHuerfanas();
                refreshStats();
            } else {
                toastr.error('Ocurrió un error al procesar la limpieza.');
            }
        });
    }

    // --- Tab Sin Relacionar ---

    function loadSinRelacionar(page = 1) {
        currentPageSinRelacion = page;
        const query = $('#search-list-sin-relacion').val();
        const soloConStock = $('#filter-stock-only').is(':checked');
        const tbody = $('#table-sin-relacion tbody');
        tbody.html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');

        $.post('<?= site_url('digemidrelacion/sinRelacionar') ?>', { 
            busqueda: query, 
            pagina: page,
            solo_con_stock: soloConStock
        }, function(res) {
            let html = '';
            if (res.datos.length === 0) {
                html = '<tr><td colspan="5" class="text-center">No hay productos pendientes.</td></tr>';
            } else {
                res.datos.forEach(row => {
                    const escapedName = row.Nom_Prod.replace(/'/g, "&apos;");
                    html += `<tr>
                        <td>${row.Cod_Prod}</td>
                        <td>
                            <b>${row.Nom_Prod}</b><br>
                            <small class="text-muted">Frac: ${row.Fracciones || 'N/A'}</small>
                        </td>
                        <td><small>${row.Nom_Titular || 'N/A'}</small></td>
                        <td class="text-center">
                            <span class="badge ${row.Stock > 0 ? 'badge-success' : 'badge-secondary'}">${row.Stock}</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary btn-vincular-directo" data-id="${row.Cod_Prod}" data-name="${escapedName}">
                                <i class="fas fa-link"></i> Vincular
                            </button>
                        </td>
                    </tr>`;
                });
            }
            tbody.html(html);
            renderPagination('pagination-sin-relacion', res, loadSinRelacionar);
            
            $('.btn-vincular-directo').off('click').on('click', function() {
                const rowId = $(this).data('id');
                const rowName = $(this).data('name');
                const rowLab = $(this).closest('tr').find('td:eq(2) small').text(); // Obtener lab de la celda
                openQuickLinkModal(rowId, rowName, rowLab);
            });
        });
    }

    // --- Quick Link Modal Logic ---
    let quickSelectedDigemid = null;

    function openQuickLinkModal(medinaId, medinaName, medinaLab) {
        $('#quick-link-medina-id').val(medinaId);
        $('#quick-link-medina-name').html(`${medinaName}<br><small class="text-success">Lab: ${medinaLab || 'N/A'}</small>`);
        $('#search-quick-digemid').val(medinaName);
        $('#modal-quick-link').modal('show');
        
        quickSelectedDigemid = null;
        $('#quick-link-summary').addClass('d-none');
        $('#btn-save-quick-link').prop('disabled', true);
        
        searchQuickDigemid(medinaName);
    }

    function searchQuickDigemid(query) {
        $('#results-quick-digemid').html('<p class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Buscando...</p>');
        $.post('<?= site_url('digemidrelacion/buscar') ?>', { termino: query }, function(data) {
            let html = '';
            if (data.length === 0) {
                html = '<p class="text-center py-4">No se encontraron resultados.</p>';
            } else {
                data.forEach(item => {
                    const badge = item.ya_relacionado == 1 ? '<span class="badge badge-success float-right">Vinculado</span>' : '';
                    html += `<div class="item" data-id="${item.Cod_Prod}" data-name="${item.Nom_Prod.replace(/'/g, "&apos;")}">
                        ${badge}
                        <strong>${item.Nom_Prod}</strong><br>
                        <small class="text-muted">${item.Concent} | ${item.Nom_Form_Farm}</small><br>
                        <small class="text-info font-weight-bold">Frac: ${item.Fracciones} | Titular: ${item.Nom_Titular}</small>
                    </div>`;
                });
            }
            $('#results-quick-digemid').html(html);

            $('#results-quick-digemid .item').click(function() {
                $('#results-quick-digemid .item').removeClass('active');
                $(this).addClass('active');
                quickSelectedDigemid = { id: $(this).data('id'), name: $(this).data('name') };
                
                $('#quick-link-digemid-name').text(quickSelectedDigemid.name);
                $('#quick-link-summary').removeClass('d-none');
                $('#btn-save-quick-link').prop('disabled', false);
            });
        });
    }

    let quickTimer;
    $('#search-quick-digemid').on('keyup', function() {
        clearTimeout(quickTimer);
        const val = $(this).val();
        if (val.length < 3) return;
        quickTimer = setTimeout(() => searchQuickDigemid(val), 500);
    });

    $('#btn-save-quick-link').click(function() {
        const medinaId = $('#quick-link-medina-id').val();
        const digemidId = quickSelectedDigemid.id;
        const btn = $(this);
        
        btn.html('<i class="fas fa-spinner fa-spin"></i> Guardando...').prop('disabled', true);

        $.post('<?= site_url('digemidrelacion/relacionar') ?>', {
            cod_prod: digemidId,
            pre_codeart: medinaId
        }, function(response) {
            if (response.success) {
                toastr.success('Vínculo creado correctamente');
                $('#modal-quick-link').modal('hide');
                loadSinRelacionar(currentPageSinRelacion); // Refrescar lista de fondo
                refreshStats();
            } else {
                toastr.error('Error: ' + (response.message || 'No se pudo guardar'));
            }
        }).fail(() => toastr.error('Error de servidor'))
          .always(() => btn.html('<i class="fas fa-save mr-1"></i> Guardar Vínculo').prop('disabled', false));
    });

    $('.btn-vincular-directo').off('click').on('click', function() {
        openQuickLinkModal($(this).data('id'), $(this).data('name'));
    });

    $('#btn-search-sin-relacion').click(() => loadSinRelacionar(1));
    $('#filter-stock-only').change(() => loadSinRelacionar(1));

    // --- Helper Functions ---

    function deleteRelation(id, name) {
        if (!confirm(`¿Está seguro de eliminar el vínculo de ${name}?`)) return;

        $.post('<?= site_url('digemidrelacion/eliminar') ?>', { cod_prod: id }, function(res) {
            if (res.success) {
                toastr.success('Vínculo eliminado');
                const activeTabLink = $('#digemid-tabs .nav-link.active');
                const activeTabId = activeTabLink.attr('href');
                
                if (activeTabId === '#tab-relacionados') loadRelacionados(currentPageRelacionados);
                if (activeTabId === '#tab-huerfanas') loadHuerfanas(currentPageHuerfanas);
                refreshStats();
            } else {
                toastr.error('No se pudo eliminar el vínculo');
            }
        });
    }

    function goToVinculacion(id, name) {
        $('#tab-vincular-link').tab('show');
        $('#search-digemid').val(name);
        searchDigemid(name);
    }

    function refreshStats() {
        $.get('<?= site_url('digemidrelacion/estadisticas') ?>', function(stats) {
            $('#stat-total-digemid').text(stats.total_digemid.toLocaleString());
            $('#stat-total-relacionados').text(stats.total_relacionados.toLocaleString());
            $('#stat-sin-relacionar').text(stats.sin_relacionar.toLocaleString());
            $('#stat-huerfanas').text(stats.huerfanas.toLocaleString());
        });
    }

    function renderPagination(containerId, res, callback) {
        let html = '<ul class="pagination pagination-sm m-0 float-right">';
        
        // Prev
        html += `<li class="page-item ${res.pagina == 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="event.preventDefault(); ${res.pagina > 1 ? callback.name + '(' + (res.pagina - 1) + ')' : ''}">&laquo;</a></li>`;
        
        // Pages (max 5)
        let start = Math.max(1, res.pagina - 2);
        let end = Math.min(res.totalPags, start + 4);
        if (end - start < 4) start = Math.max(1, end - 4);

        for (let i = start; i <= end; i++) {
            html += `<li class="page-item ${i == res.pagina ? 'active' : ''}"><a class="page-link" href="#" onclick="event.preventDefault(); ${callback.name}(${i})">${i}</a></li>`;
        }

        // Next
        html += `<li class="page-item ${res.pagina == res.totalPags ? 'disabled' : ''}"><a class="page-link" href="#" onclick="event.preventDefault(); ${res.pagina < res.totalPags ? callback.name + '(' + (res.pagina + 1) + ')' : ''}">&raquo;</a></li>`;
        
        html += '</ul>';
        html += `<span class="text-muted text-sm">Mostrando ${((res.pagina-1)*res.porPagina)+1} a ${Math.min(res.pagina*res.porPagina, res.total)} de ${res.total} registros</span>`;
        
        $(`#${containerId}`).html(html);
    }
</script>
<?= $this->endSection() ?>