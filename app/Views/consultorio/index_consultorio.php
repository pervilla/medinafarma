<?= $this->extend('templates/admin_consultorio'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header"></div>
<!-- /.content-header -->
<!-- Main content -->
<?php
//echo $CIT_CODCAMP;
?>
<div class="content">
<input type='hidden' id='campanias_cod' value='<?=$campanias_cod?>'>
<?php if(!$campanias_cod){ ?>
    <div class="container-fluid">
        <div class="row">
        <?php foreach ($campanias as $campania) {                                                                                 
            if(date('d/m/Y', strtotime($campania->CAM_FEC_INI))!='31/12/1969'){
                $date =  date('d/m/Y', strtotime($campania->CAM_FEC_INI));
            }else{
                $date = 'Sin Fecha'; 
            }; ?>             
            <div class="col-md-2">
                <div class="card card-widget widget-user shadow">
                <div class="widget-user-header text-white" style="background: #007bff 35%; background-image: url(../dist/img/shape_2.svg); background-size: cover; background-position: top right; background-repeat: no-repeat;">  
                        <h3 class="widget-user-username"><?=$campania->CAM_DESCRIP ?></h3>                            
                    </div>
                    <div class="widget-user-image">
                        <img class="img-circle elevation-2" src="../dist/img/<?=$campania->CAM_CODMED ?>.jpg" alt="<?=$campania->CLI_NOMBRE ?>">
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-sm-12 border-right">
                                <div class="description-block">
                                    <h5 class="description-header"><?=$campania->CLI_NOMBRE ?></h5>
                                    <span class="description-text">Fecha: <?=$date ?></span>
                                </div>
                            </div>                                
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-12 btn-group">                                        
                                    <a href="<?= site_url('consultorio/confirmados/'.$campania->CAM_CODCAMP) ?>" class="btn btn-secondary " role="button" aria-disabled="true"><i
                                            class="fas fa-address-book"></i> Pacientes</a>                                                                             
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div> 
    </div>
    <?php } else { ?>
    <!-- /.container-fluid -->
    <div class="container-fluid">        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?php foreach ($campanias as $campania) { echo $campania->CAM_DESCRIP; }?></h3>
                        <div class="card-tools">
                        <a href="<?= site_url('consultorio/confirmados') ?>" class="btn btn-secondary " role="button" aria-disabled="true"><i class="fas fa-address-book"></i> Todas las Campañas</a>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="table_citas" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Paciente</th>
                                    <th>DNI</th>
                                    <th>Telefono</th>
                                    <th>Edad</th>                                   
                                    <th>Estado</th>                                    
                                    <th>Atención</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card -->
            </div>

        </div>
        <!-- /.row -->
    </div>
    <?php }  ?>
    <!-- /.container-fluid -->
</div>
<!-- /.content -->

<?= $this->endSection(); ?>
<?= $this->section('footer'); ?>
<!-- Select2 -->
<link rel="stylesheet" href="../../plugins/select2/css/select2.min.css" />
<link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css" />
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
<link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css" />
<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<script>
$(document).ready(function() {

    var dtable = $("#table_citas").DataTable({
        ajax: {
            url: "<?= site_url('consultorio/get_citas') ?>",
            type: "POST",
            dataSrc: "",
            data: {
                camp: function() {
                    return $("#campanias_cod").val();
                },
                esta:1
            },
        },
        columns: [
            {data: "CIT_ORD_ATENCION"},
            {data: "CLI_NOMBRE",
                render: function(data, type, row, meta) {
                if (row.CIT_ESTADO == 1 || row.CIT_ESTADO == 2) {
                    var rpt = "<i class='fas fa-user-injured'></i> "+row.CLI_NOMBRE;
                } else{
                    var rpt = "<i class='fas fa-user-injured'></i> "+row.CLI_NOMBRE;
                }                     
                    return rpt
                }},
            {data: "CLI_RUC_ESPOSA"},
            {data: "CLI_TELEF1"},
            {data: "EDAD",
                render: function(data, type, row, meta) {
                    if (row.CLI_FECHA_NAC!='') {
                        var today = new Date();
                        var birthDate = new Date(row.CLI_FECHA_NAC);
                        var age = today.getFullYear() - birthDate.getFullYear();
                        var m = today.getMonth() - birthDate.getMonth();
                        var da = today.getDate() - birthDate.getDate();
                        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }
                        if(m<0){
                            m +=12;
                        }
                        if(da<0){
                            da +=30;
                        }
                        var age = age+" años "+ Math.abs(m) + " meses ";  
                    } else {
                        var age = "Sin Fecha";
                    }
                    return age
                }            
            },
            {data: 'CIT_ESTADO',
                render: function(data, type, row, meta) {
                    if (row.CIT_ESTADO==0) {
                        var rpt = "Sin Confirmar";   
                    } else if( row.CIT_ESTADO==1) {
                        var rpt = "Confirmado";
                    }else {
                        var rpt = row.CIT_ESTADO==2?"Atendido":"En Espera";
                    }
                    return rpt
                }
            },
            {
                data: 'CIT_CODCIT',
                render: function(data, type, row, meta) {
                    if (row.CIT_ESTADO==1 ){
                        var url = "<?= site_url('consultorio/atencion') ?>/" + row.CIT_CODCAMP +
                        '/' + row.CIT_CODCIT + '/' + row.CIT_CODCLIE;
                    var rpt = "<a href='"+url+"' class='btn btn-dark btn-block btn-sm'><i class='fas fa-user-md'></i> Atención </a>";
                    }else if (row.CIT_ESTADO==2){
                        var url = "<?= site_url('consultorio/atencion') ?>/" + row.CIT_CODCAMP +
                        '/' + row.CIT_CODCIT + '/' + row.CIT_CODCLIE;
                    var rpt = "<a href='"+url+"' class='btn btn-block btn-outline-dark btn-sm'><i class='fas fa-user-md'></i> Finalizado </a>";
                    }else if (row.CIT_ESTADO==3){
                        var url = "<?= site_url('consultorio/atencion') ?>/" + row.CIT_CODCAMP +
                        '/' + row.CIT_CODCIT + '/' + row.CIT_CODCLIE;
                    var rpt = "<a href='"+url+"' class='btn btn-dark btn-block btn-sm'><i class='fas fa-user-md'></i> Finalizar Atención </a>";
                    }else {
                        var rpt = "";
                    }
                    return rpt;
                }
            },
        ],
        fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {  
                if (aData.CIT_ESTADO == 1) {
                    $(nRow).addClass('bg-success');
                } 
                if (aData.CIT_ESTADO == 2){
                    $(nRow).addClass('bg-secondary');
                }
                if (aData.CIT_ESTADO == 3){
                    $(nRow).addClass('bg-warning');
                }                
            return nRow;
            },
        order: [
            [0, "desc"]
        ],
        rowGroup: {
            dataSrc: 4,
        },
        searching: false,
        paging: false,
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        dom: "Bfrtip",
    });
   
});


</script>
<?= $this->endSection(); ?>