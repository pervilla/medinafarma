<?php

/**
 * @var CodeIgniter\View\View $this
 */
?>
<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="content-header">
    <?php var_export($producto); 
    $pag_next = intval($producto->Row)<=$to_reg?intval($producto->Row) + 1:$to_reg; 
    $pag_prev = intval($producto->Row)>1?intval($producto->Row) - 1:1; 
    $url_next = site_url('inventario/producto/' . $id_loc . '/' . $id_inv . '/' . $id_ven . '/' . $to_reg. '/' . $pag_next );
    $url_prev = site_url('inventario/producto/' . $id_loc . '/' . $id_inv . '/' . $id_ven . '/' . $to_reg. '/' . $pag_prev );
    $caja = $producto->PRE_EQUIV > 1?"Caja de $producto->PRE_EQUIV unidades":"Unidad";
    
    ?>
    </div>
    <div class="row">
        <div class="col col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-barcode text-primary"></i> <?= $producto->art_key ?></h3>
                    <div class="card-tools">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Small button group">                            
                            <a href="<?= $url_prev ?>" class="btn btn-secondary">Atrás</a>  
                            <a href="<?= $url_next ?>" class="btn btn-secondary">Siguiente</a>                            
                        </div>
                    </div>

                </div>
                <div class="card-body">
                    <h5 class="card-title font-weight-bold"><?= $producto->ART_NOMBRE ?></h5>
                    <p class="card-text text-secondary"><?= $producto->FAMILIA ?></p>
                    <p class="card-text text-primary"><i class="fa fa-cube"></i> <?= $caja ?></p>
                    <form class="needs-validation" novalidate>
                        <div class="form-row">
                            <div class="col">
                                <label for="nb_totalsist">Stock: Sistema</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-outline-primary bg-primary"><?= $producto->STOCK ?></span>
                                    </div>
                                    <input type="number" class="form-control" id="nb_totalsist" placeholder="Stock Sistema" value="<?= intval($producto->ARM_STOCK) ?>" readonly>
                                </div>

                                
                            </div>
                            <div class="col">
                                <label for="nb_totalfis">Físico</label>
                                <input type="number" class="form-control" id="nb_totalfis" placeholder="Físico" value="">
                            </div>
                            <div class="col">
                                <label for="nb_diferencia">Diferencia</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-outline-primary bg-primary" id="nb_diferenciaPrepend">@</span>
                                    </div>
                                    <input type="number" class="form-control" id="nb_diferencia" placeholder="Diferencia" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col">
                                <label for="princAct">Principio Activo</label>
                                <select class="form-control" id="princAct">
                                    <option><?= $producto->PRIN_ACT ?></option>
                                </select>
                            </div>                                                                                                                                                                                                             
                        </div>
                        <div class="form-row">
                            <div class="col">
                                <label>Próximo Vencimiento</label>
                                <div class="attachment-block">
                                    <div class="form-row">
                                        <div class="col">
                                            <label for="SelectMes">Mes</label>
                                            <select class="form-control" id="SelectMes">
                                            <?php for($i=1;$i<=12;$i++) : ?>                                                
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label for="SelectAnio">Año</label>
                                            <select class="form-control" id="SelectAnio">
                                            <?php $anio = date("Y"); for($i=$anio;$i<=$anio+6;$i++) : ?>                                                
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label for="nb_totalven">Cant/Unid</label>
                                            <input type="number" class="form-control" id="nb_totalven" placeholder="A vencer" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>

                        <button class="btn btn-primary" type="submit">Submit form</button>
                    </form>
                </div>
                <div class="card-footer text-muted">
                    2 days ago
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.modal -->

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<?= $this->endSection(); ?>