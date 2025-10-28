<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<?php $session = session(); echo "med numero:".$session->get('mes_caja');?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">                		
                <a id="caja_cnt" class="btn btn-app bg-<?=$color=='success'?$color:'disabled';?>">
                <i class="fas fa-inbox"></i>Caja Centro
                </a>
                <a id="caja_pmz" class="btn btn-app bg-<?=$color=='info'?$color:'disabled';?>">
                <i class="fas fa-inbox"></i>Caja PMeza
                </a>
                <a id="caja_jjc" class="btn btn-app bg-<?=$color=='danger'?$color:'disabled';?>">
                <i class="fas fa-inbox"></i>Caja Juanjuicillo
                </a>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-<?=$color;?>">
                    <div class="card-header">
                        <h3 class="card-title">Movimiento Diario</h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm mb-12">
                                <div class="input-group-prepend">
                                <button type="button" class="btn btn-danger">Mes</button>
                                </div>
                                <select id="mes" name="mes" class="form-control">
                                    <option value="01" <?= $session->get('mes_caja')=='01'?'selected="selected"':'';?> >Enero</option>
                                    <option value="02" <?= $session->get('mes_caja')=='02'?'selected="selected"':'';?> >Febrero</option>
                                    <option value="03" <?= $session->get('mes_caja')=='03'?'selected="selected"':'';?> >Marzo</option>
                                    <option value="04" <?= $session->get('mes_caja')=='04'?'selected="selected"':'';?> >Abril</option>
                                    <option value="05" <?= $session->get('mes_caja')=='05'?'selected="selected"':'';?> >Mayo</option>
                                    <option value="06" <?= $session->get('mes_caja')=='06'?'selected="selected"':'';?> >Junio</option>
                                    <option value="07" <?= $session->get('mes_caja')=='07'?'selected="selected"':'';?> >Julio</option>
                                    <option value="08" <?= $session->get('mes_caja')=='08'?'selected="selected"':'';?> >Agosto</option>
                                    <option value="09" <?= $session->get('mes_caja')=='09'?'selected="selected"':'';?> >Septiembre</option>
                                    <option value="10" <?= $session->get('mes_caja')=='10'?'selected="selected"':'';?> >Octubre</option>
                                    <option value="11" <?= $session->get('mes_caja')=='11'?'selected="selected"':'';?> >Noviembre</option>
                                    <option value="12" <?= $session->get('mes_caja')=='12'?'selected="selected"':'';?> >Diciembre</option>
                                </select>

                               
                                <div class="input-group-append">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>    
             
                            </div>
                        </div>
                    </div>                    
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table  id="cajas_diario" class="table">
                            <thead>
                                <tr>
                                    <th style="width: 10px">DIA</th>
                                    <?php foreach ($empleados as $empleado) { $emple = explode(" ", $empleado->VEM_NOMBRE); ?>
                                        <th><?= $empleado->VEM_CODVEN . ' - ' . $emple[0]; ?></th>
                                    <?php } ?>
                                    <th style="width: 40px">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimientos as $mov) { 
                                    echo "<tr>";
                                    foreach($mov as $value){
                                        echo "<td>$value</td>";
                                    }
                                    echo "</tr>";
                                     } ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>            
        </div>

    </div><!-- /.container-fluid -->
</section>

<!-- /.content -->
<?= $this->endSection(); ?>
<?= $this->section('footer'); ?>
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">


<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script>

</script>
<?= $this->endSection(); ?>

