<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
    }
    .stat-box {
        padding: 20px;
        border-radius: 12px;
        transition: transform 0.3s;
    }
    .stat-box:hover {
        transform: translateY(-5px);
    }
    .score-badge {
        font-weight: 700;
        border-radius: 50px;
        padding: 5px 15px;
    }
    .table-suggestion tr {
        transition: background 0.2s;
    }
    .table-suggestion tr:hover {
        background-color: rgba(66, 133, 244, 0.05) !important;
    }
    .btn-apply {
        border-radius: 50px;
        padding: 5px 20px;
        font-weight: 600;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-magic mr-2 text-primary"></i> Panel de Control de Subgrupos</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Pestañas de Navegación -->
        <ul class="nav nav-pills mb-4 glass-card p-2" id="pills-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="pills-sugerencias-tab" data-toggle="pill" href="#pills-sugerencias" role="tab">
                    <i class="fas fa-magic mr-1"></i> Sugerencias de IA
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pills-maestros-tab" data-toggle="pill" href="#pills-maestros" role="tab">
                    <i class="fas fa-database mr-1"></i> Gestión de Maestros (Principios Activos)
                </a>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <!-- TAB 1: SUGERENCIAS -->
            <div class="tab-pane fade show active" id="pills-sugerencias" role="tabpanel">
                <!-- Stats Row -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="glass-card stat-box bg-white">
                            <h5 class="text-muted mb-1">Sin Subgrupo</h5>
                            <h2 class="text-danger font-weight-bold"><?= number_format($sin_subgrupo) ?></h2>
                            <div class="progress progress-xs mt-2">
                                <div class="progress-bar bg-danger" style="width: <?= min(100, ($sin_subgrupo/1000)*100) ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card stat-box bg-white">
                            <h5 class="text-muted mb-1">Subgrupos Activos</h5>
                            <h2 class="text-primary font-weight-bold"><?= number_format($total_subgrupos) ?></h2>
                            <small class="text-info"><i class="fas fa-tag"></i> Principios Activos Definidos</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card stat-box bg-gradient-primary text-white">
                            <h5 class="text-white-50 mb-1">IA Sugerencias</h5>
                            <h2 class="font-weight-bold">Activa</h2>
                            <small>Algoritmo Jaccard + Sinónimos</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card glass-card">
                            <div class="card-header border-0 bg-transparent">
                                <h3 class="card-title font-weight-bold">Sugerencias de Categorización</h3>
                                <div class="card-tools">
                                    <button id="refreshSuggestions" class="btn btn-sm btn-outline-primary mr-2">
                                        <i class="fas fa-sync-alt mr-1"></i> Refrescar
                                    </button>
                                    <button id="autoApply" class="btn btn-sm btn-primary">
                                        <i class="fas fa-bolt mr-1"></i> Auto-Aplicar Top Coincidencias
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-suggestion mb-0" id="tableSuggestions">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Producto Sin Categoría</th>
                                                <th>Subgrupo Sugerido (IA)</th>
                                                <th class="text-center">Confianza</th>
                                                <th class="text-right">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: GESTIÓN DE MAESTROS -->
            <div class="tab-pane fade" id="pills-maestros" role="tabpanel">
                <div class="card glass-card">
                    <div class="card-header border-0 bg-transparent">
                        <h3 class="card-title font-weight-bold">Edición de Principios Activos (TABLAS 129)</h3>
                        <div class="card-tools">
                            <button onclick="loadMaster()" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-sync"></i> Recargar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            <i class="fas fa-info-circle"></i> Edita los nombres para que sean genéricos puros. 
                            <strong>Ejemplo:</strong> Cambia "ACETILCISTEINA AMPOLLA" por "ACETILCISTEINA". Esto mejorará los aciertos de la IA.
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="tableMaster">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre Actual (TAB_NOMLARGO)</th>
                                        <th width="200" class="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<script>
    $(document).ready(function() {
        loadSuggestions();
        
        $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            if (e.target.id === 'pills-maestros-tab') loadMaster();
        });

        function loadSuggestions() {
            $('#tableSuggestions tbody').html('<tr><td colspan="4" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i><p class="text-muted">Analizando productos...</p></td></tr>');
            $.get("<?= site_url('aimatching/get_subgrupo_suggestions') ?>", function(data) {
                let html = '';
                if (data.length === 0) {
                    html = '<tr><td colspan="4" class="text-center py-5 text-success"><i class="fas fa-check-circle fa-2x mb-2"></i><br>¡Excelente! Todos los productos están categorizados.</td></tr>';
                } else {
                    data.forEach(item => {
                        let scoreColor = item.score > 80 ? 'success' : (item.score > 50 ? 'warning' : 'secondary');
                        let btnDisabled = item.suggested_id ? '' : 'disabled';
                        html += `
                        <tr id="row-${item.art_key}">
                            <td><strong>${item.art_nombre}</strong><br><small class="text-muted">ID: ${item.art_key}</small></td>
                            <td><span class="${item.suggested_id ? 'text-primary font-weight-bold' : 'text-muted'}"><i class="fas fa-link mr-1"></i> ${item.suggested_name}</span></td>
                            <td class="text-center"><span class="badge badge-${scoreColor} score-badge">${item.score}%</span></td>
                            <td class="text-right">
                                <button class="btn btn-sm btn-success btn-apply" onclick="applySubgrupo('${item.art_key}', '${item.suggested_id}')" ${btnDisabled}><i class="fas fa-check mr-1"></i> Aplicar</button>
                                <button class="btn btn-sm btn-link text-muted" onclick="$(this).closest('tr').fadeOut()">Saltar</button>
                            </td>
                        </tr>`;
                    });
                }
                $('#tableSuggestions tbody').html(html);
            });
        }

        window.loadMaster = function() {
            $('#tableMaster tbody').html('<tr><td colspan="3" class="text-center py-4"><i class="fas fa-sync fa-spin"></i> Cargando maestros...</td></tr>');
            $.get("<?= site_url('aimatching/get_subgroups_master') ?>", function(data) {
                let html = '';
                data.forEach(item => {
                    html += `
                    <tr>
                        <td>${item.TAB_NUMTAB}</td>
                        <td>
                            <input type="text" class="form-control form-control-sm" id="master-input-${item.TAB_NUMTAB}" value="${item.TAB_NOMLARGO}">
                        </td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-primary" onclick="updateMaster('${item.TAB_NUMTAB}')">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="suggestClean('${item.TAB_NUMTAB}')" title="Sugerencia Limpieza">
                                <i class="fas fa-broom"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                $('#tableMaster tbody').html(html);
            });
        }

        window.updateMaster = function(id) {
            let newName = $(`#master-input-${id}`).val();
            $.post("<?= site_url('aimatching/update_subgroup_master') ?>", { id: id, name: newName }, function(res) {
                if (res.success) toastr.success('Maestro actualizado');
                else toastr.error('Error al actualizar');
            });
        }

        window.suggestClean = function(id) {
            let input = $(`#master-input-${id}`);
            let val = input.val();
            // Lógica simple de limpieza (remover formas comunes)
            let cleaned = val.replace(/AMPOLLA|INYECTABLE|TABLETA|CAPSULA|JARABE|INY|TAB|CAP|AMP/gi, '').trim();
            input.val(cleaned).addClass('is-valid');
        }

        window.applySubgrupo = function(artKey, subId) {
            let $row = $(`#row-${artKey}`);
            $row.css('opacity', '0.5');
            $.post("<?= site_url('aimatching/apply_subgrupo') ?>", { art_key: artKey, subgru_id: subId }, function(res) {
                if (res.success) {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        if ($('#tableSuggestions tbody tr').length === 0) loadSuggestions();
                    });
                    toastr.success('Producto categorizado');
                } else {
                    $row.css('opacity', '1');
                    toastr.error('Error');
                }
            });
        };

        $("#refreshSuggestions").click(loadSuggestions);
    });
</script>
<?= $this->endSection(); ?>
