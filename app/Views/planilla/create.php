<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Generar Nueva Planilla</h1>
            </div>
            <div class="col-sm-6">
                 <!-- Config Button triggers Modal -->
                 <button class="btn btn-secondary float-sm-right ml-2" data-toggle="modal" data-target="#modal-config">
                    <i class="fas fa-cogs"></i> Config. Empleados
                </button>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Control Panel -->
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">Parámetros</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Año</label>
                            <input type="number" class="form-control" id="anio" value="<?= date('Y') ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                         <div class="form-group">
                            <label>Mes</label>
                            <select class="form-control" id="mes">
                                <?php for($i=1; $i<=12; $i++): ?>
                                    <option value="<?= $i ?>" <?= $i == date('n') ? 'selected' : '' ?>>
                                        <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" value="<?= date('Y-m-01') ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Fecha Corte</label>
                            <input type="date" class="form-control" id="fecha_corte" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                         <div class="form-group">
                            <button class="btn btn-success" id="btn-generar">
                                <i class="fas fa-sync"></i> Generar / Calcular
                            </button>
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Result Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detalle de Planilla (Vista Previa)</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-sm text-sm" id="tabla-planilla">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Días</th>
                            <th>Básico</th>
                            <th>Asig. Fam.</th>
                            <th>Comisión</th>
                            <th>Extras/Fer.</th>
                            <th>Bruto</th>
                            <th>AFP/ONP</th>
                            <th>Desc. AFP</th>
                            <th>Adelantos</th>
                            <th>Otros Desc.</th>
                            <th>Total Neto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Content loaded via JS -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="10" class="text-right">Total Planilla:</th>
                            <th id="total-planilla">0.00</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary float-right" id="btn-guardar" disabled>
                    <i class="fas fa-save"></i> Guardar Planilla
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Config Empleados -->
<div class="modal fade" id="modal-config">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Configuración de Empleados</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tabla-config">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Sueldo Básico</th>
                                <th>Tipo Comisión</th>
                                <th>Monto Fijo</th>
                                <th>AFP</th>
                                <th>Asig. Familiar</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-save-config">Guardar Configuración</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<script>
$(document).ready(function(){
    var currentData = [];
    var afpList = <?= json_encode($afps) ?>;

    // Load Config Data into Modal
    $('#modal-config').on('show.bs.modal', function (e) {
        $.get("<?= site_url('planilla/get_empleados_config') ?>", function(resp){
            var tbody = $('#tabla-config tbody');
            tbody.empty();
            resp.empleados.forEach(function(emp){
                var cfg = emp.config || {};
                var basico = cfg.sueldo_basico || 0;
                var tipo = cfg.tipo_comision || 'NINGUNO';
                var afpId = cfg.afp_id || '';
                var asig = cfg.asignacion_familiar == 1 ? 'checked' : '';
                var fijo = cfg.comision_fijo_monto || 0;

                var afpOptions = '<option value="">-- Seleccionar --</option>';
                resp.afps.forEach(function(afp){
                    var sel = (afp.id == afpId) ? 'selected' : '';
                    afpOptions += `<option value="${afp.id}" ${sel}>${afp.nombre} (${afp.porcentaje}%)</option>`;
                });

                var tr = `
                    <tr data-id="${emp.VEM_CODVEN}">
                        <td>${emp.VEM_NOMBRE}</td>
                        <td><input type="number" class="form-control form-control-sm cfg-basico" value="${basico}"></td>
                        <td>
                            <select class="form-control form-control-sm cfg-tipo">
                                <option value="NINGUNO" ${tipo=='NINGUNO'?'selected':''}>NINGUNO</option>
                                <option value="VENTAS" ${tipo=='VENTAS'?'selected':''}>VENTAS</option>
                                <option value="FIJO" ${tipo=='FIJO'?'selected':''}>FIJO</option>
                                <option value="MIXTO" ${tipo=='MIXTO'?'selected':''}>VENTAS + FIJO</option>
                            </select>
                        </td>
                        <td><input type="number" class="form-control form-control-sm cfg-fijo" value="${fijo}" placeholder="Monto Fijo"></td>
                        <td><select class="form-control form-control-sm cfg-afp">${afpOptions}</select></td>
                        <td class="text-center"><input type="checkbox" class="cfg-asig" ${asig}></td>
                    </tr>
                `;
                tbody.append(tr);
            });
        });
    });

    // Save Config
    $('#btn-save-config').click(function(){
        var updates = [];
        $('#tabla-config tbody tr').each(function(){
            var row = $(this);
            updates.push({
                vem_codven: row.data('id'),
                sueldo_basico: row.find('.cfg-basico').val(),
                tipo_comision: row.find('.cfg-tipo').val(),
                comision_fijo_monto: row.find('.cfg-fijo').val(),
                afp_id: row.find('.cfg-afp').val(),
                asignacion_familiar: row.find('.cfg-asig').is(':checked') ? 1 : 0
            });
        });

        $.ajax({
            url: "<?= site_url('planilla/save_config') ?>",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(updates),
            success: function(res){
                if(res.success){
                    alert('Configuración guardada correctamente');
                    $('#modal-config').modal('hide');
                }
            }
        });
    });

    // Generate Planner Data
    $('#btn-generar').click(function(){
        var anio = $('#anio').val();
        var mes = $('#mes').val();
        var inicio = $('#fecha_inicio').val();
        var corte = $('#fecha_corte').val();
        
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Procesando...').prop('disabled', true);

        $.get("<?= site_url('planilla/generate_data') ?>", {
            anio: anio, 
            mes: mes, 
            fecha_inicio: inicio,
            fecha_corte: corte
        }, function(data){
            currentData = data;
            renderTable();
            $('#btn-guardar').prop('disabled', false);
            $btn.html(originalText).prop('disabled', false);
        }).fail(function(){
            alert('Error al generar datos. Revise el log o intente nuevamente.');
            $btn.html(originalText).prop('disabled', false);
        });
    });

    function renderTable(){
        var tbody = $('#tabla-planilla tbody');
        tbody.empty();
        var total = 0;

        currentData.forEach(function(row, index){
            var bruto = parseFloat(row.sueldo_basico) + parseFloat(row.asignacion_familiar) + parseFloat(row.comision_ventas);
            // Recalculate logic could be here if we allowed editing, for now just display
            
            var tr = `
                <tr>
                    <td>${row.nombre}</td>
                    <td>${row.dias_trabajados}</td>
                    <td>${formatMoney(row.sueldo_basico)}</td>
                    <td>${formatMoney(row.asignacion_familiar)}</td>
                    <td class="text-primary" title="${row.comision_info}">${formatMoney(row.comision_ventas)}</td>
                    <td class="text-primary" title="${row.extras_info}">${formatMoney(row.extras_monto)}</td>
                    <td class="font-weight-bold">${formatMoney(bruto)}</td>
                    <td>${row.afp_nombre}</td>
                    <td>${formatMoney(row.afp_monto)}</td>
                    <td class="text-danger">${formatMoney(row.adelantos)}</td>
                    <td class="text-danger">${formatMoney(row.faltantes)}</td>
                    <td class="font-weight-bold bg-light">${formatMoney(row.total_neto)}</td>
                </tr>
            `;
            tbody.append(tr);
            total += parseFloat(row.total_neto);
        });
        $('#total-planilla').text(formatMoney(total));
    }

    function formatMoney(amount){
        return parseFloat(amount).toFixed(2);
    }

    // Save Planilla
    $('#btn-guardar').click(function(){
        if(!confirm('¿Está seguro de guardar la planilla? Se generará un borrador.')) return;

        var payload = {
            header: {
                anio: $('#anio').val(),
                mes: $('#mes').val(),
                fecha_corte: $('#fecha_corte').val(),
                fecha_inicio: $('#anio').val() + '-' + $('#mes').val() + '-01' // Approx
            },
            detalles: currentData
        };

        $.ajax({
            url: "<?= site_url('planilla/store') ?>",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(payload),
            success: function(res){
                if(res.success){
                    alert('Planilla guardada con éxito (ID: ' + res.id + ')');
                    window.location.href = "<?= site_url('planilla') ?>";
                }
            }
        });
    });

});
</script>
<?= $this->endSection(); ?>
