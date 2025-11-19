<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<style>
.metric-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}
.metric-card:hover {
    transform: translateY(-5px);
}
.metric-value {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 5px;
}
.metric-label {
    font-size: 0.9rem;
    opacity: 0.9;
}
.table-container {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.filter-section {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
}
.btn-modern {
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
    border: none;
    transition: all 0.3s ease;
}
.chart-container {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    position: relative;
    height: 600px;
}
.chart-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}
.chart-wrapper {
    position: relative;
    height: calc(100% - 40px);
    width: 100%;
}
</style>

<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">📊 Dashboard de Rentabilidad</h1>
        <p class="text-muted">Análisis de ventas y rentabilidad por vendedor</p>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- Filtros -->
        <div class="row">
            <div class="col-12">
                <div class="filter-section">
                    <h4><i class="fas fa-filter"></i> Filtros de Consulta</h4>
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Mes</label>
                            <select class="form-control" id="mes" name="mes">
                                <?php
                                $i = 1;
                                $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                while ($i <= 12) {
                                    $sel = date('n') == $i ? "selected='selected'" : "" ?>
                                    <?= "<option value='$i' $sel>" . $meses[$i] . "</option>"; ?>
                                <?php $i++; } ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Año</label>
                            <select class="form-control" id="anio" name="anio">
                                <?php $i = 2021;
                                while ($i <= date('Y')) {
                                    $sel = date('Y') == $i ? "selected='selected'" : "" ?>
                                    <?= "<option value='$i' $sel>" . $i . "</option>"; ?>
                                <?php $i++; } ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="buscar" class="btn btn-light btn-modern">
                                <i class="fas fa-search"></i> Consultar Datos
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Métricas Principales -->
        <div class="row" id="metricas-principales" style="display: none;">
            <div class="col-md-3">
                <div class="metric-card text-center">
                    <div class="metric-value" id="total-ventas">$0</div>
                    <div class="metric-label">💰 Total Ventas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card text-center" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <div class="metric-value" id="total-rentabilidad">$0</div>
                    <div class="metric-label">📈 Rentabilidad Total</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card text-center" style="background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);">
                    <div class="metric-value" id="promedio-rentabilidad">0%</div>
                    <div class="metric-label">📊 % Promedio</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card text-center" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="metric-value" id="total-vendedores">0</div>
                    <div class="metric-label">👥 Vendedores</div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row" id="graficos-section" style="display: none;">
            <div class="col-lg-6">
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fas fa-chart-pie"></i> Distribución de Rentabilidad
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fas fa-chart-bar"></i> Comparativa de Ventas vs Rentabilidad
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="graficos-section2" style="display: none;">
            <div class="col-lg-6">
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fas fa-chart-line"></i> Porcentaje de Rentabilidad por Vendedor
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fas fa-trophy"></i> Top 5 Vendedores por Rentabilidad
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="horizontalBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-table"></i> Detalle de Rentabilidad por Vendedor</h4>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="table_rentabilidad" class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Vendedor</th>
                                    <th>Ventas C</th>
                                    <th>Costo C</th>
                                    <th>Ventas PM</th>
                                    <th>Costo PM</th>
                                    <th>Ventas JCILLO</th>
                                    <th>Costo JCILLO</th>
                                    <th>Total Ventas</th>
                                    <th>Costo Total</th>
                                    <th>Rentabilidad</th>
                                    <th>% Rentable</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Variables globales para los gráficos
    let pieChart, barChart, lineChart, horizontalBarChart;

    function formatCurrency(value) {
        if (!value || isNaN(value)) return 'S/.0';
        return 'S/.' + parseFloat(value).toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    var dtable = $('#table_rentabilidad').DataTable({
        ajax: {
            url: "<?= site_url('empleado/get_rentabilidad') ?>",
            type: "POST",
            dataSrc: '',
            data: {
                mes: function() { return $('#mes').val(); },
                anio: function() { return $('#anio').val(); }
            }
        },
        columns: [
            {data: 'VEM_NOMBRE', render: function(data) { return data.trim(); }},
            {data: 'COMISION', type: 'num', render: function(data) { return formatCurrency(data); }},
            {data: 'COSTO', type: 'num', render: function(data) { return formatCurrency(data); }},
            {data: 'COMISION3', type: 'num', render: function(data) { return formatCurrency(data); }},
            {data: 'COSTO3', type: 'num', render: function(data) { return formatCurrency(data); }},
            {data: 'COMISION2', type: 'num', render: function(data) { return formatCurrency(data); }},
            {data: 'COSTO2', type: 'num', render: function(data) { return formatCurrency(data); }},
            {
                data: 'TOTAL', 
                type: 'num',
                render: function(data, type, row) { 
                    if (type === 'sort' || type === 'type') {
                        return parseFloat(data) || 0;
                    }
                    return '<strong>' + formatCurrency(data) + '</strong>'; 
                }
            },
            {data: 'TOTALCOST', type: 'num', render: function(data) { return formatCurrency(data); }},
            {
                data: 'RENTABILIDAD',
                type: 'num',
                render: function(data, type, row) { 
                    if (type === 'sort' || type === 'type') {
                        return parseFloat(data) || 0;
                    }
                    return '<span class="badge badge-success">' + formatCurrency(data) + '</span>'; 
                }
            },
            {data: 'PORCENTAJE'}
        ],
        order: [[7, 'desc']],
        pageLength: 25,
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        }
    });

    function updateMetrics(data) {
        if (!data || data.length === 0) {
            $('#metricas-principales').hide();
            $('#graficos-section').hide();
            $('#graficos-section2').hide();
            return;
        }
        
        let totalVentas = 0, totalRentabilidad = 0, promedioRentabilidad = 0;
        
        data.forEach(item => {
            totalVentas += parseFloat(item.TOTAL) || 0;
            totalRentabilidad += parseFloat(item.RENTABILIDAD) || 0;
            promedioRentabilidad += parseFloat(item.PORCIENTO) || 0;
        });
        
        promedioRentabilidad = promedioRentabilidad / data.length;
        
        $('#total-ventas').text(formatCurrency(totalVentas));
        $('#total-rentabilidad').text(formatCurrency(totalRentabilidad));
        $('#promedio-rentabilidad').text(promedioRentabilidad.toFixed(1) + '%');
        $('#total-vendedores').text(data.length);
        
        $('#metricas-principales').show();
        $('#graficos-section').show();
        $('#graficos-section2').show();
    }

    function updateCharts(data) {
        if (!data || data.length === 0) return;

        // Preparar datos
        const labels = data.map(item => item.VEM_NOMBRE.trim().split(' ')[0]);
        const rentabilidades = data.map(item => parseFloat(item.RENTABILIDAD));
        const totalVentas = data.map(item => parseFloat(item.TOTAL));
        const porcentajes = data.map(item => parseFloat(item.PORCIENTO));
        
        // Calcular porcentajes para el pie chart
        const totalRentabilidad = rentabilidades.reduce((acc, val) => acc + val, 0);
        const porcentajesDistribucion = rentabilidades.map(value => 
            ((value / totalRentabilidad) * 100).toFixed(2)
        );

        // Colores
        const backgroundColors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
            '#9966FF', '#FF9F40', '#8DFF63', '#FF8C94', 
            '#66FF66', '#3399FF', '#FF66B2', '#C0C0C0'
        ];

        // Configuración común
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2
        };

        // 1. Gráfico de Pie - Distribución de Rentabilidad
        if (pieChart) {
            pieChart.destroy();
            pieChart = null;
        }
        const ctxPie = document.getElementById('pieChart');
        if (ctxPie) {
            pieChart = new Chart(ctxPie.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Distribución',
                        data: porcentajesDistribucion,
                        backgroundColor: backgroundColors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 10,
                                font: { size: 11 }
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
        }

        // 2. Gráfico de Barras - Ventas vs Rentabilidad
        if (barChart) {
            barChart.destroy();
            barChart = null;
        }
        const ctxBar = document.getElementById('barChart');
        if (ctxBar) {
            barChart = new Chart(ctxBar.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Total Ventas',
                            data: totalVentas,
                            backgroundColor: '#36A2EB',
                            borderColor: '#2589d4',
                            borderWidth: 1
                        },
                        {
                            label: 'Rentabilidad',
                            data: rentabilidades,
                            backgroundColor: '#4BC0C0',
                            borderColor: '#3aa8a8',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'S/.' + value.toLocaleString('es-PE');
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': S/.' + 
                                           context.parsed.y.toLocaleString('es-PE');
                                }
                            }
                        }
                    }
                }
            });
        }

        // 3. Gráfico de Línea - Porcentaje de Rentabilidad
        if (lineChart) {
            lineChart.destroy();
            lineChart = null;
        }
        const ctxLine = document.getElementById('lineChart');
        if (ctxLine) {
            lineChart = new Chart(ctxLine.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '% Rentabilidad',
                        data: porcentajes,
                        borderColor: '#FF6384',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#FF6384',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y.toFixed(2) + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        // 4. Gráfico Horizontal - Top 5
        const top5Data = data
            .sort((a, b) => parseFloat(b.RENTABILIDAD) - parseFloat(a.RENTABILIDAD))
            .slice(0, 5);
        
        const top5Labels = top5Data.map(item => item.VEM_NOMBRE.trim());
        const top5Values = top5Data.map(item => parseFloat(item.RENTABILIDAD));

        if (horizontalBarChart) {
            horizontalBarChart.destroy();
            horizontalBarChart = null;
        }
        const ctxHorizontal = document.getElementById('horizontalBarChart');
        if (ctxHorizontal) {
            horizontalBarChart = new Chart(ctxHorizontal.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: top5Labels,
                    datasets: [{
                        label: 'Rentabilidad',
                        data: top5Values,
                        backgroundColor: [
                            '#FFD700',
                            '#C0C0C0', 
                            '#CD7F32',
                            '#4BC0C0',
                            '#9966FF'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    ...commonOptions,
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'S/.' + value.toLocaleString('es-PE');
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'S/.' + context.parsed.x.toLocaleString('es-PE');
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    dtable.on('xhr.dt', function(e, settings, json) {
        updateMetrics(json);
        updateCharts(json);
    });

    $('#buscar').click(function() {
        dtable.ajax.reload();
    });

    // Cargar datos iniciales
    dtable.ajax.reload();
});
</script>
<?= $this->endSection(); ?>