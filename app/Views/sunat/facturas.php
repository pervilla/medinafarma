<?= $this->extend('templates/admin_template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-file-invoice text-warning"></i> Envío de Facturas SUNAT</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <div class="card mb-4">
            <div class="card-body">
                <form id="form-consulta" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Empresa</label>
                        <select class="form-control" id="empresa_ruc" name="empresa_ruc">
                            <?php foreach($empresas as $ruc => $emp): ?>
                                <?php if (in_array('facturas', $emp['tipo'] ?? [])): ?>
                                    <option value="<?= $ruc ?>"><?= $emp['nombre_comercial'] ?> (<?= $ruc ?>)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Serie</label>
                        <select class="form-control" id="serie" name="serie">
                            <option value="10">FA10 (192.168.101.201)</option>
                            <option value="11">FA11 (192.168.101.200)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Fecha de Emisión</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" onclick="consultar()">Consultar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Facturas Pendientes</h5>
                <div>
                    <button class="btn btn-sm btn-outline-info mr-1" onclick="seleccionarTodas()"><i class="fas fa-check-double"></i> Todo</button>
                    <button class="btn btn-success" onclick="enviarSeleccionadas()" id="btn-enviar" disabled>
                        <i class="fas fa-paper-plane"></i> Enviar Seleccionadas
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-sm mb-0" id="tabla-facturas">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:40px"><input type="checkbox" id="check-todas" onchange="toggleTodas(this)"></th>
                                <th>Serie</th>
                                <th>Número</th>
                                <th>Hora</th>
                                <th>Cliente RUC</th>
                                <th>Cliente</th>
                                <th>Total (Exonerado)</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="9" class="text-center py-4 text-muted">Realiza una consulta para ver las facturas</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Detalle -->
        <div class="modal fade" id="modal-detalle">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalle de Factura</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" id="modal-detalle-body">
                        <div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    let facturasData = [];

    function consultar() {
        const formData = $('#form-consulta').serialize();
        $('#btn-enviar').prop('disabled', true);
        $('#tabla-facturas tbody').html('<tr><td colspan="9" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
        
        $.ajax({
            url: '<?= base_url('SunatController/api_facturas_pendientes') ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                let tbody = $('#tabla-facturas tbody');
                tbody.empty();
                facturasData = response.data || [];
                
                if (facturasData.length > 0) {
                    let pendientes = 0;
                    facturasData.forEach((f, i) => {
                        const yaEnviada = f.enviada;
                        const estadoBadge = yaEnviada 
                            ? (f.estado_envio === 'ACEPTADA' ? 'badge-success' : 'badge-danger')
                            : 'badge-secondary';
                        const estadoTexto = yaEnviada ? (f.estado_envio || 'ENVIADA') : 'PENDIENTE';
                        if (!yaEnviada) pendientes++;
                        
                        tbody.append(`
                            <tr class="${yaEnviada ? 'table-success' : ''}">
                                <td><input type="checkbox" class="check-factura" data-index="${i}" ${yaEnviada ? 'disabled' : ''}></td>
                                <td>FA${f.serie}</td>
                                <td>${f.numero}</td>
                                <td>${f.hora || '-'}</td>
                                <td><code>${f.cliente_ruc || 'N/A'}</code></td>
                                <td>${f.cliente_nombre}</td>
                                <td>S/ ${parseFloat(f.total).toFixed(2)}</td>
                                <td><span class="badge ${estadoBadge}">${estadoTexto}</span></td>
                                <td>
                                    ${yaEnviada 
                                        ? '<span class="text-muted"><i class="fas fa-check"></i> Enviada</span>'
                                        : `<button class="btn btn-xs btn-info" onclick="verDetalle('${f.serie}','${f.numero}')" title="Ver detalle"><i class="fas fa-list"></i></button>
                                           <button class="btn btn-xs btn-success" onclick="enviarIndividual(${i})" title="Enviar"><i class="fas fa-paper-plane"></i></button>`
                                    }
                                </td>
                            </tr>
                        `);
                    });
                    
                    if (pendientes > 0) {
                        $('#btn-enviar').prop('disabled', false);
                    }
                } else {
                    tbody.append('<tr><td colspan="9" class="text-center text-danger">No se encontraron facturas para esta fecha y sede.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                alert('Error de conexión: ' + error);
                $('#tabla-facturas tbody').html('<tr><td colspan="9" class="text-center text-danger">Error al obtener datos.</td></tr>');
            }
        });
    }

    function toggleTodas(source) {
        $('.check-factura:not(:disabled)').prop('checked', $(source).prop('checked'));
    }

    function seleccionarTodas() {
        $('.check-factura:not(:disabled)').prop('checked', true);
        $('#check-todas').prop('checked', true);
    }

    function enviarIndividual(idx) {
        const f = facturasData[idx];
        if (!confirm(`¿Enviar factura FA${f.serie}-${f.numero} a SUNAT (PRODUCCIÓN)?`)) return;

        const btn = event.target;
        $(btn).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '<?= base_url('SunatController/enviar_factura') ?>',
            type: 'POST',
            data: {
                fecha: $('#fecha').val(),
                serie: f.serie,
                numero: f.numero
            },
            success: function(response) {
                if (response.status === 'success') {
                    alert('✅ Factura ACEPTADA\n' + (response.descripcion || ''));
                } else {
                    alert('❌ Error: ' + (response.message || 'Error desconocido'));
                }
                consultar();
            },
            error: function() {
                alert('Error de conexión al enviar.');
                consultar();
            }
        });
    }

    function enviarSeleccionadas() {
        const indices = [];
        $('.check-factura:checked').each(function() {
            indices.push(parseInt($(this).data('index')));
        });
        
        if (indices.length === 0) {
            alert('Selecciona al menos una factura.');
            return;
        }
        
        if (!confirm(`¿Enviar ${indices.length} factura(s) a SUNAT (PRODUCCIÓN)?`)) return;
        
        $('#btn-enviar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
        
        let enviadas = 0, exitos = 0, errores = 0;
        const total = indices.length;
        
        function enviarSiguiente() {
            if (enviadas >= total) {
                const msg = `Proceso completado.\nEnviadas: ${total}\nAceptadas: ${exitos}\nRechazadas/Error: ${errores}`;
                alert(msg);
                consultar();
                return;
            }
            
            const idx = indices[enviadas];
            const f = facturasData[idx];
            enviadas++;
            
            $('#btn-enviar').text(`Enviando ${enviadas}/${total}...`);
            
            $.ajax({
                url: '<?= base_url('SunatController/enviar_factura') ?>',
                type: 'POST',
                data: {
                    fecha: $('#fecha').val(),
                    serie: f.serie,
                    numero: f.numero
                },
                success: function(response) {
                    if (response.status === 'success') exitos++;
                    else errores++;
                    enviarSiguiente();
                },
                error: function() {
                    errores++;
                    enviarSiguiente();
                }
            });
        }
        
        enviarSiguiente();
    }

    function verDetalle(serie, numero) {
        $('#modal-detalle').modal('show');
        $('#modal-detalle-body').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
        
        $.ajax({
            url: '<?= base_url('SunatController/api_factura_detalles') ?>',
            type: 'POST',
            data: { serie: serie, numero: numero },
            success: function(response) {
                if (response.data && response.data.length > 0) {
                    let html = `<table class="table table-sm"><thead><tr><th>Cant.</th><th>Producto</th><th>P.Unit.</th><th>Total</th></tr></thead><tbody>`;
                    response.data.forEach(d => {
                        html += `<tr><td>${parseFloat(d.cantidad).toFixed(4)}</td><td>${d.nombre_producto}</td><td>S/ ${parseFloat(d.precio_unitario).toFixed(2)}</td><td>S/ ${parseFloat(d.total_item).toFixed(2)}</td></tr>`;
                    });
                    html += '</tbody></table>';
                    $('#modal-detalle-body').html(html);
                } else {
                    $('#modal-detalle-body').html('<p class="text-muted text-center">Sin detalle disponible</p>');
                }
            },
            error: function() {
                $('#modal-detalle-body').html('<p class="text-danger text-center">Error al cargar detalle</p>');
            }
        });
    }
</script>

<?= $this->endSection() ?>
