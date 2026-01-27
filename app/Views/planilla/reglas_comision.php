<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<div class="content-header">
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Configuración de Comisiones</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        
        <!-- Global Settings -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Configuración Global</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                         <div class="form-group">
                            <label>Porcentaje Comisión por Defecto (Para familias sin regla)</label>
                            <div class="input-group">
                                <input type="number" id="default-pct" class="form-control" step="0.01" value="<?= $defaultRule ? $defaultRule->porcentaje : 0 ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                    <button class="btn btn-success" id="btn-save-default">Guardar</button>
                                </div>
                            </div>
                            <small class="text-muted">Se aplica a cualquier artículo que pertenezca a una familia sin regla específica.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Descuento Global (Impuestos/Renta)</label>
                            <div class="input-group">
                                <input type="number" id="global-desc" class="form-control" step="0.01" value="<?= $globalDiscount ? $globalDiscount->porcentaje : 0 ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                    <button class="btn btn-success" id="btn-save-global">Guardar</button>
                                </div>
                            </div>
                             <small class="text-muted">Este porcentaje se DESCUENTA del total de comisiones generado.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Family Rules -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Reglas por Familia de Productos</h3>
                <div class="card-tools">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add-rule">
                        <i class="fa fa-plus"></i> Agregar Regla
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="tabla-reglas" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Familia</th>
                            <th>Porcentaje</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rules as $rule): ?>
                        <tr>
                            <td><?= $rule->descripcion ?></td>
                            <td><?= $rule->porcentaje ?> %</td>
                            <td>
                                <button class="btn btn-danger btn-xs btn-delete" data-id="<?= $rule->id ?>"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<!-- Modal Add Rule -->
<div class="modal fade" id="modal-add-rule">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Agregar Regla por Familia</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="selected-fam-id">
                <input type="hidden" id="selected-fam-name">
                
                <div class="form-group">
                    <label>Buscar Familia</label>
                    <select id="select-familia" class="form-control" style="width: 100%;">
                        <option value="">Cargando familias...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Porcentaje (%)</label>
                    <input type="number" class="form-control" id="rule-pct" step="0.01">
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-rule">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<script>
$(document).ready(function(){
    // Load Families
    $.get("<?= site_url('planilla/get_familias') ?>", function(data){
        var opts = '<option value="">-- Seleccione Familia --</option>';
        data.forEach(function(f){
            opts += `<option value="${f.id}" data-name="${f.nombre}">${f.nombre}</option>`;
        });
        $('#select-familia').html(opts);
    });

    $('#btn-save-default').click(function(){
        saveSpecialRule('DEFAULT', $('#default-pct').val(), 'Default Rule');
    });

    $('#btn-save-global').click(function(){
        saveSpecialRule('GLOBAL_DISCOUNT', $('#global-desc').val(), 'Descuento Global');
    });

    $('#btn-save-rule').click(function(){
        var famId = $('#select-familia').val();
         var famName = $('#select-familia option:selected').data('name');
        var pct = $('#rule-pct').val();

        if(!famId || !pct){
            alert('Seleccione familia e ingrese porcentaje'); return;
        }

        $.post("<?= site_url('planilla/save_rule') ?>", {
            tipo: 'FAMILIA',
            referencia_id: famId,
            porcentaje: pct,
            descripcion: famName
        }, function(res){
            if(res.success){
                location.reload();
            } else {
                alert('Error: ' + res.message);
            }
        });
    });

    $('.btn-delete').click(function(){
        if(!confirm('¿Eliminar regla?')) return;
        var id = $(this).data('id');
        $.post("<?= site_url('planilla/delete_rule') ?>", {id: id}, function(res){
            if(res.success) location.reload();
            else alert('Error al eliminar');
        });
    });

    function saveSpecialRule(type, pct, desc){
         $.post("<?= site_url('planilla/save_rule') ?>", {
            tipo: type,
            porcentaje: pct,
            descripcion: desc
        }, function(res){
            if(res.success){
                alert('Guardado correctamente');
                // Optional: location.reload() if we want to refresh values, but inputs are already there.
            } else {
                alert('Error: ' + res.message);
            }
        });
    }
});
</script>
<?= $this->endSection(); ?>
