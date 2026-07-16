<?= $this->extend('templates/admin_template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Módulo SUNAT - Resúmenes Diarios</h1>
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
                            <?php foreach($empresas as $ruc => $empresa): ?>
                                <option value="<?= $ruc ?>"><?= $empresa['nombre_comercial'] ?> (<?= $ruc ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Serie (Solo Inversiones San Martin)</label>
                        <select class="form-control" id="serie" name="serie">
                            <option value="10">Serie 10 (192.168.101.201)</option>
                            <option value="11">Serie 11 (192.168.101.200)</option>
                            <option value="B001">B001 (MARYMED - 192.168.101.202)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Fecha de Comprobantes</label>
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
                <h5 class="mb-0">Comprobantes (Boletas)</h5>
                <button class="btn btn-success" onclick="generarResumen()" id="btn-generar" disabled>Generar y Enviar Resumen Diario</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="tabla-comprobantes">
                        <thead>
                            <tr>
                                <th>Serie</th>
                                <th>Número</th>
                                <th>Hora</th>
                                <th>Cliente</th>
                                <th>Nº Doc.</th>
                                <th>Total (Exonerado)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="4" class="text-center">Realiza una consulta para ver los datos</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Enviar CSRF token en todas las peticiones AJAX
    $.ajaxSetup({
        beforeSend: function(xhr) {
            var cookie = document.cookie.split('; ').find(row => row.startsWith('csrf_cookie_name='));
            if (cookie) {
                xhr.setRequestHeader('X-CSRF-TOKEN', cookie.split('=')[1]);
            }
        }
    });

    $(document).ready(function() {
        // Auto-llenar desde parámetros URL (viene del historial → Reenviar)
        const params = new URLSearchParams(window.location.search);
        if (params.has('empresa_ruc')) $('#empresa_ruc').val(params.get('empresa_ruc'));
        if (params.has('serie')) $('#serie').val(params.get('serie'));
        if (params.has('fecha')) $('#fecha').val(params.get('fecha'));
        if (params.has('empresa_ruc')) consultar();
    });

    function consultar() {
        const formData = $('#form-consulta').serialize();
        $('#btn-generar').prop('disabled', true);
        $('#tabla-comprobantes tbody').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
        
        $.ajax({
            url: '<?= base_url('SunatController/api_pendientes') ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                let tbody = $('#tabla-comprobantes tbody');
                tbody.empty();
                
                if (response.data && response.data.length > 0) {
                    let countBoletas = 0;
                    response.data.forEach(c => {
                        if (c.tipo_fbg === 'B') {
                            countBoletas++;
                            tbody.append(`
                                <tr>
                                    <td>${c.serie}</td>
                                    <td>${c.numero}</td>
                                    <td>${c.hora}</td>
                                    <td>${c.cliente_nombre}</td>
                                    <td>${c.cliente_doc}</td>
                                    <td>S/ ${parseFloat(c.total).toFixed(2)}</td>
                                </tr>
                            `);
                        }
                    });
                    
                    if (response.ya_existe) {
                        $('#btn-generar').prop('disabled', true);
                        if (countBoletas > 0) {
                            tbody.append('<tr><td colspan="6" class="text-center text-info"><i class="fas fa-info-circle"></i> Ya existe un resumen PENDIENTE o ACEPTADO para esta fecha/serie. Para reenviar ve al Historial.</td></tr>');
                        }
                    } else if (countBoletas > 0) {
                        $('#btn-generar').prop('disabled', false);
                    } else {
                        tbody.append('<tr><td colspan="6" class="text-center text-warning">No hay boletas (solo facturas) para esta fecha y sede.</td></tr>');
                    }
                } else {
                    tbody.append('<tr><td colspan="4" class="text-center text-danger">No se encontraron registros o error de conexión con base de datos de origen.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                alert("Error " + xhr.status + ": " + error + "\n\n" + (xhr.responseText || ''));
                $('#tabla-comprobantes tbody').html('<tr><td colspan="4" class="text-center text-danger">Error al consultar.</td></tr>');
            }
        });
    }

    function generarResumen() {
        if (!confirm('¿Estás seguro de enviar el resumen diario a SUNAT (PRODUCCIÓN)?')) return;
        
        const formData = $('#form-consulta').serialize();
        $('#btn-generar').prop('disabled', true).text('Enviando...');
        
        $.ajax({
            url: '<?= base_url('SunatController/generar_resumen') ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    alert('✅ ' + response.message + '\nTicket: ' + response.ticket);
                    $('#btn-generar').prop('disabled', true).text('✓ Resumen Enviado');
                } else if (response.status === 'warning') {
                    alert('⚠️ ' + response.message);
                    $('#btn-generar').prop('disabled', true).text('✓ Ya Enviado');
                } else {
                    alert('❌ Error: ' + response.message);
                    $('#btn-generar').prop('disabled', false).text('Generar y Enviar Resumen Diario');
                }
            },
            error: function(xhr, status, error) {
                alert("Error " + xhr.status + ": " + error + "\n\nRespuesta: " + (xhr.responseText || 'sin respuesta'));
                $('#btn-generar').prop('disabled', false).text('Generar y Enviar Resumen Diario');
            }
        });
    }
</script>

<?= $this->endSection() ?>
