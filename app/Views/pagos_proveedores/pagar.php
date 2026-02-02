<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $titulo ?? 'Registrar Pago a Proveedor' ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('pagosproveedores') ?>">Cuentas por Pagar</a></li>
                    <li class="breadcrumb-item active">Registrar Pago</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Datos de la Factura</h3>
                    </div>
                    <form id="form-pago" method="post" action="<?= site_url('pagosproveedores/procesarpago') ?>">
                        <?= csrf_field() ?>
                        <div class="card-body">
                            <!-- Información de la factura -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Factura N°</label>
                                        <input type="text" class="form-control" value="<?= esc($factura['car_NUMFAC']) ?>" readonly>
                                        <input type="hidden" name="numfac" value="<?= esc($factura['car_NUMFAC']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Proveedor</label>
                                        <input type="text" class="form-control" value="<?= esc($proveedor['cli_nombre'] ?? 'N/A') ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Fecha Vencimiento</label>
                                        <input type="text" class="form-control" value="<?= esc($factura['car_fecha_vcto']) ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Saldo Pendiente</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">S/</span>
                                            </div>
                                            <input type="text" id="saldo_pendiente" class="form-control text-right" value="<?= number_format($factura['car_importe'], 2) ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Días de Mora</label>
                                        <input type="text" class="form-control text-center" value="<?= $dias_mora > 0 ? $dias_mora . ' días' : 'Al día' ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Montos del pago -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="monto_capital">Monto a Pagar (Capital) *</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">S/</span>
                                            </div>
                                            <input type="number" step="0.01" min="0.01" max="<?= $factura['car_importe'] ?>" 
                                                   name="monto_capital" id="monto_capital" class="form-control text-right" 
                                                   value="<?= number_format($factura['car_importe'], 2, '.', '') ?>" required>
                                        </div>
                                        <small class="form-text text-muted">Monto máximo: S/ <?= number_format($factura['car_importe'], 2) ?></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="monto_interes">Interés Moratorio Calculado</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">S/</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" 
                                                   name="monto_interes" id="monto_interes" class="form-control text-right" 
                                                   value="<?= number_format($interes_calculado, 2, '.', '') ?>">
                                        </div>
                                        <small class="form-text text-muted">
                                            <?php if ($dias_mora > 0): ?>
                                                Tasa: 5% anual | Días mora: <?= $dias_mora ?> | Interés calculado automáticamente
                                            <?php else: ?>
                                                No hay días de mora
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Opciones de interés -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="pagar_interes" id="pagar_interes" value="1" <?= $interes_calculado > 0 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="pagar_interes">Incluir interés moratorio en el pago</label>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Información del pago -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="forma_pago">Forma de Pago *</label>
                                        <select name="forma_pago" id="forma_pago" class="form-control" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($formas_pago as $valor => $etiqueta): ?>
                                                <option value="<?= $valor ?>"><?= $etiqueta ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="local">Local *</label>
                                        <select name="local" id="local" class="form-control" required>
                                            <?php foreach ($locales as $id => $nombre): ?>
                                                <option value="<?= $id ?>"><?= $nombre ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="fecha_pago">Fecha de Pago *</label>
                                        <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" 
                                               value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="observaciones">Observaciones</label>
                                        <textarea name="observaciones" id="observaciones" class="form-control" rows="2" placeholder="Notas adicionales sobre este pago..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <h5><i class="icon fas fa-info-circle"></i> Resumen del Pago</h5>
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Capital:</strong></td>
                                                <td class="text-right">S/ <span id="resumen_capital"><?= number_format($factura['car_importe'], 2) ?></span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Interés Moratorio:</strong></td>
                                                <td class="text-right">S/ <span id="resumen_interes"><?= number_format($interes_calculado, 2) ?></span></td>
                                            </tr>
                                            <tr class="table-active">
                                                <td><strong>Total a Pagar:</strong></td>
                                                <td class="text-right"><strong>S/ <span id="resumen_total"><?= number_format($total_a_pagar, 2) ?></span></strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Validación VB6 -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="callout callout-warning">
                                        <h5><i class="fas fa-exclamation-triangle"></i> Validaciones del Sistema VB6</h5>
                                        <p class="mb-0">Este pago replicará la transacción LK_CODTRA=5360 del sistema VB6, afectando las tablas CARTERA, CARACU y ALLOG. Se aplicarán las mismas validaciones de negocio (saldo no negativo, importes válidos, etc.).</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Confirmar Pago</button>
                            <a href="<?= site_url('pagosproveedores') ?>" class="btn btn-default">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<script>
    $(function () {
        // Calcular totales en tiempo real
        function calcularTotales() {
            var capital = parseFloat($('#monto_capital').val()) || 0;
            var interes = parseFloat($('#monto_interes').val()) || 0;
            var pagarInteres = $('#pagar_interes').is(':checked');
            
            // Si no se marca pagar interés, interés = 0
            if (!pagarInteres) {
                interes = 0;
                $('#monto_interes').val(0);
            }
            
            var total = capital + interes;
            
            $('#resumen_capital').text(capital.toFixed(2));
            $('#resumen_interes').text(interes.toFixed(2));
            $('#resumen_total').text(total.toFixed(2));
        }
        
        // Actualizar al cambiar montos
        $('#monto_capital, #monto_interes, #pagar_interes').on('input change', calcularTotales);
        
        // Validar que el capital no exceda el saldo pendiente
        $('#monto_capital').on('change', function() {
            var max = parseFloat('<?= $factura['car_importe'] ?>');
            var valor = parseFloat($(this).val()) || 0;
            
            if (valor > max) {
                alert('El monto a pagar no puede exceder el saldo pendiente (S/ ' + max.toFixed(2) + ')');
                $(this).val(max.toFixed(2));
                calcularTotales();
            }
            
            if (valor <= 0) {
                alert('El monto debe ser mayor a cero');
                $(this).val(max.toFixed(2));
                calcularTotales();
            }
        });
        
        // Calcular interés en tiempo real si cambia fecha de pago
        $('#fecha_pago').on('change', function() {
            var fechaPago = $(this).val();
            var fechaVcto = '<?= $factura['car_fecha_vcto'] ?>';
            var saldo = parseFloat($('#monto_capital').val()) || parseFloat('<?= $factura['car_importe'] ?>');
            
            if (!fechaPago || !fechaVcto) return;
            
            // Llamar a API para calcular interés
            $.get('<?= site_url('pagosproveedores/calcularinteres') ?>', {
                saldo: saldo,
                fecha_vcto: fechaVcto,
                fecha_pago: fechaPago
            }, function(data) {
                if (data.error) {
                    console.error(data.error);
                    return;
                }
                
                // Actualizar campos
                $('#monto_interes').val(data.interes_calculado);
                $('input[name="dias_mora"]').val(data.dias_mora);
                
                // Actualizar resumen
                calcularTotales();
                
                // Mostrar información actualizada
                var info = 'Días mora: ' + data.dias_mora + ' | Tasa: ' + data.tasa_anual;
                $('#monto_interes').next('.form-text').html(info);
            }, 'json');
        });
        
        // Inicializar valores
        calcularTotales();
        $('#forma_pago').val('EFECTIVO');
        $('#local').val('<?= session()->get('caja') ?? 1 ?>');
    });
</script>
<?= $this->endSection(); ?>