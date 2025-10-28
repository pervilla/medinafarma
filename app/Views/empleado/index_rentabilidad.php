<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header"></div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Rentabilidad de Vendedores</h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm mb-12">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-danger">Mes/Año</button>
                                </div>
                                <!-- /btn-group -->
                                <select class="form-control" id="mes" name="mes">
                                    <?php
                                    $i = 1;
                                    $mes = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
                                    while ($i <= 12) {
                                        $sel = date('n') == $i ? "selected='selected'" : "" ?>
                                        <?= "<option value='$i' $sel>" . $mes[$i] . "</option>"; ?>
                                    <?php $i++;
                                    } ?>
                                </select>
                                <select class="form-control" id="anio" name="anio">
                                    <?php $i = 2021;
                                    while ($i <= date('Y')) {
                                        $sel = date('Y') == $i ? "selected='selected'" : "" ?>
                                        <?= "<option value='$i' $sel>" . date("y", strtotime($i)) . "</option>"; ?>
                                    <?php $i++;
                                    } ?>
                                </select>
                                <span class="input-group-append">
                                    <button type="button" id="buscar" name="buscar" class="btn btn-success"><i class="fa fa-search"> </i> Ver</button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="table_rentabilidad" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Vendedor</th>
                                    <th>Ventas C</th>
                                    <th>Costo C</th>
                                    <th>Ventas PM</th>
                                    <th>Costo PM</th>
                                    <th>Ventas JCILLO</th>
                                    <th>Costo JCILLO</th>
                                    <th>Total</th>
                                    <th>Costo</th>
                                    <th>Rentabilidad</th>
                                    <th>% Rentable</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-lg-6">
                <canvas id="pieChart"></canvas>
            </div>
            <div class="col-lg-6">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</div>
<!-- /.content -->


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
    $(document).ready(function() {

        var dtable = $('#table_rentabilidad').DataTable({
            ajax: {
                url: "<?= site_url('empleado/get_rentabilidad') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    mes: function() {
                        return $("select#mes option:checked").val();
                    },
                    anio: function() {
                        return $("select#anio option:checked").val();
                    }
                }
            },
            columns: [{data: 'VEM_NOMBRE', className: 'all'},         // Prioridad alta
        {data: 'COMISION', className: 'priority-5'},
        {data: 'COSTO', className: 'all'},              // Prioridad alta
        {data: 'COMISION3', className: 'priority-4'},
        {data: 'COSTO3', className: 'priority-4'},
        {data: 'COMISION2', className: 'priority-4'},
        {data: 'COSTO2', className: 'priority-4'},
        {data: 'TOTAL', className: 'all'},              // Prioridad alta
        {data: 'TOTALCOST', className: 'priority-3'},
        {data: 'RENTABILIDAD', className: 'all'},       // Prioridad alta
        {data: 'PORCENTAJE', className: 'all'}          // Prioridad alta
    ],
            order: [
                [10, 'desc']
            ],
            rowGroup: {
                dataSrc: 5
            },
            searching: false,
            paging: true,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            dom: 'Bfrtip'
        });
        var pieChart;
     var lineChart;

     function updateCharts(data) {
         var labels = data.map(item => item.VEM_NOMBRE.split(' ')[0]);
         var rentabilities = data.map(item => parseFloat(item.RENTABILIDAD));
         var percentages = data.map(item => parseFloat(item.PORCIENTO));
         
         let total = rentabilities.reduce((acc, val) => acc + val, 0);

// Convertir cada valor a porcentaje
let percentages2 = rentabilities.map(value => (value / total * 100).toFixed(2));

console.log(percentages2);

         console.log(rentabilities);
         console.log(percentages);
         // Actualizar Pie Chart
         if (pieChart) pieChart.destroy();
         var ctxPie = document.getElementById('pieChart').getContext('2d');
         pieChart = new Chart(ctxPie, {
             type: 'pie',
             data: {
                 labels: labels,
                 datasets: [{
                     label: 'Rentabilidad %',
                     data: percentages2,
                     backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                        '#9966FF', '#FF9F40', '#8DFF63', '#FF8C94', 
                        '#66FF66', '#3399FF', '#FF66B2','#C0C0C0'
                    ],
                     hoverOffset: 8
                 }]
             },
             options: {
                 responsive: true,
                 plugins: {
                     legend: {
                         position: 'bottom',
                         labels: {
                             padding: 15,
                             font: {
                                 size: 14
                             }
                         }
                     },
                     tooltip: {
                         callbacks: {
                             label: function(context) {
                                 return `${context.label}: ${context.raw}%`;
                             }
                         }
                     }
                 }
             }
         });

         // Actualizar Line Chart
         if (lineChart) lineChart.destroy();
         var ctxLine = document.getElementById('lineChart').getContext('2d');
         lineChart = new Chart(ctxLine, {
             type: 'line',
             data: {
                 labels: labels,
                 datasets: [{
                     label: 'Rentabilidad',
                     data: rentabilities,
                     borderColor: '#36A2EB',
                     backgroundColor: 'rgba(54, 162, 235, 0.2)',
                     fill: true
                 }]
             },
             options: {
                 scales: {
                     y: {
                         beginAtZero: true
                     }
                 }
             }
         });
     }

         // Escucha el evento `xhr` para ejecutar `updateCharts` después de cargar los datos
    $('#table_rentabilidad').on('xhr.dt', function (e, settings, json) {
        // Ejecuta `updateCharts` pasando los datos cargados (json)
        updateCharts(json);
    });

    // Ya no es necesario usar el botón "buscar" para ejecutar `updateCharts`
    $("#buscar").click(function () {
        dtable.ajax.reload();
    }); 
    });
</script>
<?= $this->endSection(); ?>