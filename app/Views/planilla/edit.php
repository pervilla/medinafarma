<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Editar Planilla - <?= $planilla->anio ?>-<?= str_pad($planilla->mes, 2, '0', STR_PAD_LEFT) ?></h1>
            </div>
            <div class="col-sm-6">
                 <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('planilla') ?>">Planillas</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Info Panel -->
        <div class="card card-info card-outline">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><strong>Estado:</strong> <span class="badge badge-warning"><?= $planilla->estado ?></span></div>
                    <div class="col-md-3"><strong>Fecha Corte:</strong> <?= $planilla->fecha_corte ?></div>
                    <div class="col-md-6 text-right">
                        <button class="btn btn-primary" id="btn-actualizar">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detalle de Empleados</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-sm text-sm" id="tabla-planilla">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th width="80">Días</th>
                            <th width="100">Básico</th>
                            <th width="100">Asig. Fam.</th>
                            <th width="100">Comisión</th>
                            <th>Bruto</th>
                            <th>AFP/ONP</th>
                            <th width="100">Desc. AFP</th>
                            <th width="100">Adelantos</th>
                            <th width="100">Faltantes</th>
                            <th>Total Neto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($detalles as $det): ?>
                        <tr data-id="<?= $det->id ?>">
                            <td><?= $det->nombre ?></td>
                            <td><input type="number" class="form-control form-control-sm text-center inp-dias" value="<?= $det->dias_trabajados ?>"></td>
                            <td><input type="number" class="form-control form-control-sm text-right inp-basico" value="<?= $det->sueldo_basico ?>" step="0.01"></td>
                            <td><input type="number" class="form-control form-control-sm text-right inp-asig" value="<?= $det->asignacion_familiar ?>" step="0.01"></td>
                            <td><input type="number" class="form-control form-control-sm text-right inp-comision" value="<?= $det->comision_ventas ?>" step="0.01" readonly style="background-color: #f4f6f9;"></td>
                            <td class="text-right font-weight-bold val-bruto">0.00</td>
                            <td><?= $det->afp_nombre ?></td>
                            <td><input type="number" class="form-control form-control-sm text-right inp-afp" value="<?= $det->afp_monto ?>" step="0.01"></td>
                            <td><input type="number" class="form-control form-control-sm text-right inp-adelantos text-danger" value="<?= $det->adelantos + $det->creditos ?>" step="0.01"></td>
                            <td><input type="number" class="form-control form-control-sm text-right inp-faltantes text-danger" value="<?= $det->faltantes ?>" step="0.01"></td>
                            <td class="text-right font-weight-bold bg-light val-neto">0.00</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="10" class="text-right">Total Planilla:</th>
                            <th id="total-planilla">0.00</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<script>
$(document).ready(function(){
    // Initial Calc
    recalculateAll();

    // Event Listeners
    $('#tabla-planilla').on('input', 'input', function(){
        recalculateRow($(this).closest('tr'));
        recalculateTotal();
    });

    function recalculateRow(row) {
        var basico = parseFloat(row.find('.inp-basico').val()) || 0;
        var asig = parseFloat(row.find('.inp-asig').val()) || 0;
        var comision = parseFloat(row.find('.inp-comision').val()) || 0; // Readonly but part of calc
        
        var bruto = basico + asig + comision;
        row.find('.val-bruto').text(bruto.toFixed(2));

        var afp = parseFloat(row.find('.inp-afp').val()) || 0;
        var adelantos = parseFloat(row.find('.inp-adelantos').val()) || 0;
        var faltantes = parseFloat(row.find('.inp-faltantes').val()) || 0;

        var neto = bruto - afp - adelantos - faltantes;
        row.find('.val-neto').text(neto.toFixed(2));
    }

    function recalculateAll() {
        $('#tabla-planilla tbody tr').each(function(){
            recalculateRow($(this));
        });
        recalculateTotal();
    }

    function recalculateTotal() {
        var total = 0;
        $('#tabla-planilla tbody tr').each(function(){
            total += parseFloat($(this).find('.val-neto').text());
        });
        $('#total-planilla').text(total.toFixed(2));
    }

    $('#btn-actualizar').click(function(){
        if(!confirm('¿Desea guardar los cambios realizados?')) return;

        var detalles = [];
        $('#tabla-planilla tbody tr').each(function(){
            var row = $(this);
            detalles.push({
                id: row.data('id'),
                dias_trabajados: row.find('.inp-dias').val(),
                sueldo_basico: row.find('.inp-basico').val(),
                asignacion_familiar: row.find('.inp-asig').val(),
                comision_ventas: row.find('.inp-comision').val(),
                afp_monto: row.find('.inp-afp').val(),
                adelantos: row.find('.inp-adelantos').val(),
                creditos: 0, // Merged into adelantos in View for simplicity
                faltantes: row.find('.inp-faltantes').val(),
                total_neto: row.find('.val-neto').text()
            });
        });

        var payload = {
            id: <?= $planilla->id ?>,
            header: {
                estado: '<?= $planilla->estado ?>' // Maintain state for now
            },
            detalles: detalles
        };

        $.ajax({
            url: "<?= site_url('planilla/update') ?>",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(payload),
            success: function(res){
                if(res.success){
                    alert('Cambios guardados correctamente');
                    location.reload();
                } else {
                    alert('Error al guardar: ' + (res.message || 'Desconocido'));
                }
            },
            error: function(xhr){
                alert('Error de servidor: ' + xhr.responseText);
            }
        });
    });

});
</script>
<?= $this->endSection(); ?>
