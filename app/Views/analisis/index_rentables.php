<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<style>
.filter-section {
    background: linear-gradient(135deg, #1f4037 0%, #99f2c8 100%);
    color: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.btn-modern {
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
    border: none;
    transition: all 0.3s ease;
}
.table-container {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.chart-container {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
</style>

<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">📊 Reporte de Productos Rentables</h1>
        <p class="text-muted">Ventas consolidadas de productos rentables (Local, JCL, PMZ)</p>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- Error Alert -->
        <div class="alert alert-danger" id="error-alert" style="display: none;">
            <h5><i class="icon fas fa-ban"></i> Error de Conexión!</h5>
            <span id="error-message"></span>
        </div>

        <!-- Filtros -->
        <div class="row">
            <div class="col-12">
                <div class="filter-section">
                    <h4><i class="fas fa-filter"></i> Filtros de Fecha</h4>
                    <form id="form-filtros" method="POST" action="<?= site_url('reportes/export_rentables_excel') ?>">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= date('Y-m-01') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6 d-flex gap-2">
                                <button type="button" id="btn-buscar" class="btn btn-light btn-modern mr-2">
                                    <i class="fas fa-search text-primary"></i> Consultar
                                </button>
                                <button type="submit" class="btn btn-success btn-modern">
                                    <i class="fas fa-file-excel"></i> Exportar a Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row" id="datos-section" style="display: none;">
            <!-- Tabla -->
            <div class="col-lg-6">
                <div class="table-container">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-table"></i> Detalle por Empleado</h4>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="table_rentables" class="table table-striped table-hover w-100">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nº</th>
                                    <th>Vendedor</th>
                                    <th>Total Bruto (S/)</th>
                                    <th>Venta Rentable (S/)</th>
                                    <th>% Rentable</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right">TOTAL GENERAL:</th>
                                    <th id="total-bruto">0.00</th>
                                    <th id="total-rentable">0.00</th>
                                    <th id="total-pct">0%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Grafico -->
            <div class="col-lg-6">
                <div class="chart-container">
                    <h4 class="mb-3"><i class="fas fa-chart-bar"></i> Top Ventas Rentables</h4>
                    <canvas id="barChart" style="min-height: 400px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
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
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let barChart = null;
    let dtable = null;

    function formatCurrency(value) {
        if (!value || isNaN(value)) return '0.00';
        return parseFloat(value).toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function initTable(data) {
        if (dtable) {
            dtable.clear();
            dtable.rows.add(data);
            dtable.draw();
        } else {
            dtable = $('#table_rentables').DataTable({
                data: data,
                columns: [
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {data: 'nombre'},
                    {
                        data: 'total_bruto', 
                        type: 'num',
                        render: function(data, type, row) { 
                            if (type === 'display') {
                                return 'S/.' + formatCurrency(data);
                            }
                            return data;
                        }
                    },
                    {
                        data: 'total_ventas', 
                        type: 'num',
                        render: function(data, type, row) { 
                            if (type === 'display') {
                                return '<strong>S/.' + formatCurrency(data) + '</strong>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'pct_rentable',
                        type: 'num',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                let color = data >= 50 ? 'success' : (data >= 30 ? 'warning' : 'danger');
                                return '<span class="badge badge-' + color + '">' + data + '%</span>';
                            }
                            return data;
                        }
                    }
                ],
                order: [[3, 'desc']],
                pageLength: 20,
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                },
                footerCallback: function (row, data, start, end, display) {
                    let api = this.api();
                    
                    let totalBruto = api.column(2).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);

                    let totalRentable = api.column(3).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);

                    let pct = totalBruto > 0 ? ((totalRentable / totalBruto) * 100).toFixed(2) : 0;

                    $(api.column(2).footer()).html('S/. ' + formatCurrency(totalBruto));
                    $(api.column(3).footer()).html('S/. ' + formatCurrency(totalRentable));
                    $(api.column(4).footer()).html(pct + '%');
                }
            });
        }
    }

    function initChart(data) {
        // Tomamos los 10 mejores
        const topData = [...data].sort((a, b) => b.total_ventas - a.total_ventas).slice(0, 10);
        const labels = topData.map(item => item.nombre.trim());
        const valuesRentable = topData.map(item => item.total_ventas);
        const valuesBruto = topData.map(item => item.total_bruto);

        if (barChart) {
            barChart.destroy();
        }

        const ctx = document.getElementById('barChart').getContext('2d');
        barChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Bruto (S/)',
                        data: valuesBruto,
                        backgroundColor: 'rgba(201, 203, 207, 0.5)',
                        borderColor: 'rgba(201, 203, 207, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Venta Rentable (S/)',
                        data: valuesRentable,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'S/.' + context.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });
    }

    function loadData() {
        $('#btn-buscar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cargando...');
        $('#error-alert').hide();
        
        $.ajax({
            url: "<?= site_url('reportes/get_data_rentables') ?>",
            type: "POST",
            dataType: "json",
            data: {
                fecha_inicio: $('#fecha_inicio').val(),
                fecha_fin: $('#fecha_fin').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#datos-section').show();
                    initTable(response.data);
                    initChart(response.data);
                }
            },
            error: function(xhr) {
                $('#datos-section').hide();
                let msg = "Error al conectar con los servidores.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                
                $('#error-message').text(msg);
                $('#error-alert').fadeIn();

                // Alert popup using SweetAlert2
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Disponibilidad',
                    text: msg
                });
            },
            complete: function() {
                $('#btn-buscar').prop('disabled', false).html('<i class="fas fa-search text-primary"></i> Consultar');
            }
        });
    }

    $('#btn-buscar').click(function() {
        if (!$('#fecha_inicio').val() || !$('#fecha_fin').val()) {
            Swal.fire('Atención', 'Seleccione el rango de fechas', 'warning');
            return;
        }
        loadData();
    });

    // Validar exportación antes de enviar el form
    $('#form-filtros').submit(function(e) {
        if (!$('#fecha_inicio').val() || !$('#fecha_fin').val()) {
            e.preventDefault();
            Swal.fire('Atención', 'Seleccione el rango de fechas', 'warning');
            return false;
        }
        return true;
    });

    // Carga inicial automática
    loadData();
});
</script>
<?= $this->endSection(); ?>
