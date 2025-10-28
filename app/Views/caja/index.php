<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header">
    
</div>
<!-- /.content-header -->
<?php $session = session(); ?>
<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Ventas Diarias</h3>
                        </div>
                    </div>
                    <div class="card-body">
                                                <!-- /.d-flex -->

                        <div class="position-relative mb-4">
                            <canvas id="visitors-chart" height="200"></canvas>
                        </div>

                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2">
                                <i class="fas fa-square text-primary"></i> Centro
                            </span>

                            <span>
                                <i class="fas fa-square text-gray"></i> Peñameza
                            </span>
                        </div>
                    </div>
                </div>
                <!-- /.card -->                
            </div>
            <!-- /.col-md-6 -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Ventas Diarias</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        

                        <div class="position-relative mb-4">
                            <canvas id="sales-chart" height="200"></canvas>
                        </div>

                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2">
                                <i class="fas fa-square text-primary"></i> Centro
                            </span>

                            <span>
                                <i class="fas fa-square text-gray"></i> Peñameza
                            </span>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col-md-6 -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 id="card_cajas_diario" class="card-title"></h3>
                        <div class="card-tools">
                            
                            <div class="input-group input-group-sm mb-12">
                            <div class="btn-group mr-2" role="group" aria-label="First group">
                                <button type="button" id="caja_cnt" class="btn btn-<?=$color=='success'?$color:'default';?> btn-sm"><i class="fa fa-inbox"> </i> Cja Centro</button>
                                <button type="button" id="caja_pmz" class="btn btn-<?=$color=='info'?$color:'default';?> btn-sm"><i class="fa fa-inbox"> </i> Cja PMeza</button>
                                <button type="button" id="caja_jjc" class="btn btn-<?=$color=='danger'?$color:'default';?> btn-sm"><i class="fa fa-inbox"> </i> Cja Juanjuicillo</button>
                            </div>

                                <div class="input-group-prepend">
                                <button type="button" class="btn btn-danger">Mes</button>
                                </div>
                                <?php
$mes_ar=array("01"=>"ENE","02"=>"FEB","03"=>"MAR","04"=>"ABR","05"=>"MAY","06"=>"JUN","07"=>"JUL","08"=>"AGO","09"=>"SEP","10"=>"OCT","11"=>"NOV","12"=>"DIC",);
?> 
                                <select id="mes" name="mes" class="form-control">
                                <option value="<?=date('m', strtotime('-4 month'))?>" data-anio="<?=date('Y', strtotime('-4 month'))?>" <?= $session->get('mes_caja')==date('m', strtotime('-4 month'))?'selected="selected"':'';?> ><?=date('Y', strtotime('-4 month'))?>-<?=$mes_ar[date('m', strtotime('-4 month'))]?></option>
                                    <option value="<?=date('m', strtotime('-3 month'))?>" data-anio="<?=date('Y', strtotime('-3 month'))?>" <?= $session->get('mes_caja')==date('m', strtotime('-3 month'))?'selected="selected"':'';?> ><?=date('Y', strtotime('-3 month'))?>-<?=$mes_ar[date('m', strtotime('-3 month'))]?></option>
                                    <option value="<?=date('m', strtotime('-2 month'))?>" data-anio="<?=date('Y', strtotime('-2 month'))?>" <?= $session->get('mes_caja')==date('m', strtotime('-2 month'))?'selected="selected"':'';?> ><?=date('Y', strtotime('-2 month'))?>-<?=$mes_ar[date('m', strtotime('-2 month'))]?></option>
                                    <option value="<?=date('m', strtotime('-1 month'))?>" data-anio="<?=date('Y', strtotime('-1 month'))?>" <?= $session->get('mes_caja')==date('m', strtotime('-1 month'))?'selected="selected"':'';?> ><?=date('Y', strtotime('-1 month'))?>-<?=$mes_ar[date('m', strtotime('-1 month'))]?></option>
                                    <option value="<?=date('m')?>" data-anio="<?=date('Y')?>" <?= $session->get('mes_caja')==date('m')?'selected="selected"':'';?> ><?=date('Y')?>-<?=$mes_ar[date('m')]?></option>

                                </select>

                               
                                <div class="input-group-append">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>    
             
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                    <table id="cajas_diario" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Día</th>
                                    <th>Caja</th>
                                    <th>Responsable</th>
                                    <th>Efectivo</th>
                                    <th>Movimientos</th>
                                    <th>Total Caja</th>
                                    <th>Total Sistema</th>
                                    <th>Diferencia</th>
                                    <th>Ver</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventas as $venta) { 
                                    $dif = round(($venta->TOT_MOVIM+$venta->TOT_EFECTIVO)-$venta->TOT_VENTAS,2);
                                    ?>
                                    <tr>                      
                                        <td><small class="text-success mr-1"><i class="fas fa-calendar-day"></i><?= date("d", strtotime($venta->ALL_FECHA_DIA)); ?></small></td>
                                        <td><?= $venta->ALL_CAJA_NRO; ?></td>
                                        <td><?= $venta->VEM_NOMBRE; ?></td>
                                        <td><?= $venta->TOT_EFECTIVO; ?></td>
                                        <td><?= $venta->TOT_MOVIM; ?></td>
                                        <td><?= $venta->TOT_EFECTIVO+$venta->TOT_MOVIM; ?></td>
                                        <td><?= $venta->TOT_VENTAS; ?></td>
                                        <td <?= $dif<0?'style="color: red !important;"':''?>><?= $dif ?></td>
                                        <td>
                                            <a class="openPopup" data-href="<?= site_url('caja/ver_caja') .'/'.$venta->ALL_CAJA_NRO.'/'.$caja?>/" class="openPopup"><i class='fas fa-eye'></i></a> 
                                            <a href="<?=site_url('caja/imprimircaja').'/'.$venta->ALL_CAJA_NRO.'/'.$session->get('caja')?>" class="text-muted"> <i class="fas fa-print"></i></a>
                                        </td>
                                    </tr>
                                <?php }  ?>                         
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</div>
<!-- /.content -->

    <!-- Modal cAJA -->
    <div class="modal fade" id="myCaja" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Reporte de Caja</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>                    
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        
        </div>
    </div>

<?= $this->endSection(); ?>


<?= $this->section('footer'); ?>
<!-- OPTIONAL SCRIPTS -->
<script src="../../plugins/chart.js/Chart.min.js"></script>
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">


<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

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
    $(function () {
        'use strict';

        var ticksStyle = {
            fontColor: '#495057',
            fontStyle: 'bold'
        };

        var mode = 'index';
        var intersect = true;

        var $salesChart = $('#sales-chart');
        var salesChart = new Chart($salesChart, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($dias); ?>,
                datasets: [
                    {
                        backgroundColor: '#007bff',
                        borderColor: '#007bff',
                        data: <?php echo json_encode($centro); ?>
                    },
                    {
                        backgroundColor: '#ced4da',
                        borderColor: '#ced4da',
                        data: <?php echo json_encode($pmeza); ?>
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    mode: mode,
                    intersect: intersect
                },
                hover: {
                    mode: mode,
                    intersect: intersect
                },
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                            // display: false,
                            gridLines: {
                                display: true,
                                lineWidth: '4px',
                                color: 'rgba(0, 0, 0, .2)',
                                zeroLineColor: 'transparent'
                            },
                            ticks: $.extend({
                                beginAtZero: true,

                                // Include a dollar sign in the ticks
                                callback: function (value, index, values) {
                                    if (value >= 1000) {
                                        value /= 1000;
                                        value += 'k';
                                    }
                                    return 'S/.' + value;
                                }
                            }, ticksStyle)
                        }],
                    xAxes: [{
                            display: true,
                            gridLines: {
                                display: false
                            },
                            ticks: ticksStyle
                        }]
                }
            }
        });

        var $visitorsChart = $('#visitors-chart');
        var visitorsChart = new Chart($visitorsChart, {
            data: {
                labels: <?php echo json_encode($dias); ?>,
                datasets: [{
                        type: 'line',
                        data: <?php echo json_encode($centro); ?>,
                        backgroundColor: 'transparent',
                        borderColor: '#007bff',
                        pointBorderColor: '#007bff',
                        pointBackgroundColor: '#007bff',
                        fill: false
                                // pointHoverBackgroundColor: '#007bff',
                                // pointHoverBorderColor    : '#007bff'
                    },
                    {
                        type: 'line',
                        data: <?php echo json_encode($pmeza); ?>,
                        backgroundColor: 'tansparent',
                        borderColor: '#ced4da',
                        pointBorderColor: '#ced4da',
                        pointBackgroundColor: '#ced4da',
                        fill: false
                                // pointHoverBackgroundColor: '#ced4da',
                                // pointHoverBorderColor    : '#ced4da'
                    }]
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    mode: mode,
                    intersect: intersect
                },
                hover: {
                    mode: mode,
                    intersect: intersect
                },
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                            // display: false,
                            gridLines: {
                                display: true,
                                lineWidth: '4px',
                                color: 'rgba(0, 0, 0, .2)',
                                zeroLineColor: 'transparent'
                            },
                            ticks: $.extend({
                                beginAtZero: true,
                                suggestedMax: 200
                            }, ticksStyle)
                        }],
                    xAxes: [{
                            display: true,
                            gridLines: {
                                display: false
                            },
                            ticks: ticksStyle
                        }]
                }
            }
        });
    });
$(function () {

    $("#cajas_diario").DataTable({
            responsive: true, lengthChange: false, autoWidth: false, dom: 'Bfrtip',
            buttons: [
                { 
                extend: 'copy', 
                text:'<i class="fas fa-file-alt"></i> Copiar' ,
                className:"btn btn-sm"         
            },
            { 
                extend: 'csv', 
                text:'<i class="fas fa-file-csv"></i> CSV' ,
                className:"btn btn-sm"            
            },
            { 
                extend: 'excel', 
                text:'<i class="fas fa-file-excel"></i> Excel'  ,
                className:"btn btn-sm"           
            },
            { 
                extend: 'pdf', 
                text:'<i class="fas fa-file-pdf"></i> Pdf'  ,
                className:"btn btn-sm"           
            },
            { 
                extend: 'print', 
                text:'<i class="fas fa-print"></i> Imprimir'  ,
                className:"btn btn-sm"           
            }],
            fixedColumns: {
                leftColumns: 0,
                rightColumns: 3
            },
            order: [[1, "desc"]]
        }).buttons().container().appendTo('#card_cajas_diario');
});
$('.openPopup').on('click', function() {
        var dataURL = $(this).attr('data-href');
        $('.modal-body').load(dataURL, function() {
            $('#myCaja').modal({
                show: true
            });
        });
    });

$("#caja_cnt").click(function(e) {
    set_caja(e,1);
});
$("#caja_pmz").click(function(e) {
    set_caja(e,3);
});
$("#caja_jjc").click(function(e) {
    set_caja(e,2);
});
$("#mes").change(function() {
    $.ajax({
        type: "POST",
        url: "<?= site_url('caja/set_mes') ?>",
        data: { 
			mes:this.value,
            anio:$("#mes").find(':selected').data('anio')
        },
        success: function(result) {
            location.reload();
    		return false;
        },
        error: function(result) {
            alert('error');
        }
    });
});
function set_caja(e,x) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "<?= site_url('caja/set_caja') ?>",
        data: { 
			caja:x ,opci: 'caja'
        },
        success: function(result) {
            location.reload();
    		return false;
        },
        error: function(result) {
            alert('error');
        }
    });
}
</script>



<?= $this->endSection(); ?>