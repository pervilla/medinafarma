<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?= $titulo ?> <small>Dashboard de Flujo de Caja</small></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Operaciones</a></li>
              <li class="breadcrumb-item active">Flujo de Caja</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        
        <!-- Filtros -->
        <div class="row">
          <div class="col-md-12">
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Filtros</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <form id="filtroForm" method="get" action="<?= site_url('flujocaja') ?>">
                  <div class="row">
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>Fecha Desde</label>
                        <input type="date" class="form-control" name="fecha_desde" value="<?= $fechaDesde ?>">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>Fecha Hasta</label>
                        <input type="date" class="form-control" name="fecha_hasta" value="<?= $fechaHasta ?>">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>Local</label>
                        <select class="form-control" name="local">
                          <option value="1" <?= $local == 1 ? 'selected' : '' ?>>Local 1 (Central)</option>
                          <option value="2" <?= $local == 2 ? 'selected' : '' ?>>Local 2 (Juanjuicillo)</option>
                          <option value="3" <?= $local == 3 ? 'selected' : '' ?>>Local 3 (Peñameza)</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>Tipo de Reporte</label>
                        <select class="form-control" name="tipo_reporte" id="tipoReporte">
                          <option value="diario" <?= $tipoReporte == 'diario' ? 'selected' : '' ?>>Diario</option>
                          <option value="semanal" <?= $tipoReporte == 'semanal' ? 'selected' : '' ?>>Semanal</option>
                          <option value="mensual" <?= $tipoReporte == 'mensual' ? 'selected' : '' ?>>Mensual</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                      </button>
                      <button type="button" class="btn btn-default" onclick="resetFiltros()">
                        <i class="fas fa-undo"></i> Limpiar
                      </button>
                      <a href="<?= site_url('flujocaja/exportarExcel') ?>?<?= $_SERVER['QUERY_STRING'] ?>" class="btn btn-success float-right">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                      </a>
                      <a href="<?= site_url('flujocaja/reporte') ?>?<?= $_SERVER['QUERY_STRING'] ?>" class="btn btn-info float-right mr-2">
                        <i class="fas fa-file-alt"></i> Ver Reporte Detallado
                      </a>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Tarjetas de Resumen -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <sup style="font-size: 20px">S/.</sup><h3><?= number_format($totalIngresos, 2) ?></h3>
                <p>Total Ingresos</p>
              </div>
              <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
              </div>
              <a href="#ingresosSection" class="small-box-footer">Más información <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <sup style="font-size: 20px">S/.</sup><h3><?= number_format($totalEgresos, 2) ?></h3>
                <p>Total Gastos</p>
              </div>
              <div class="icon">
                <i class="fas fa-shopping-cart"></i>
              </div>
              <a href="#egresosSection" class="small-box-footer">Más información <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <sup style="font-size: 20px">S/.</sup><h3><?= number_format($totalPagosProveedores, 2) ?></h3>
                <p>Pagos a Proveedores</p>
              </div>
              <div class="icon">
                <i class="fas fa-truck"></i>
              </div>
              <a href="#proveedoresSection" class="small-box-footer">Más información <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box <?= $flujoNeto >= 0 ? 'bg-info' : 'bg-secondary' ?>">
              <div class="inner">
                <sup style="font-size: 20px">S/.</sup><h3><?= number_format($flujoNeto, 2) ?></h3>
                <p>Flujo Neto</p>
              </div>
              <div class="icon">
                <i class="fas fa-chart-line"></i>
              </div>
              <a href="#graficoSection" class="small-box-footer">Más información <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>

        <!-- Gráfico de Flujo de Caja -->
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-bar mr-1"></i>
                  Flujo de Caja por Período
                </h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body" id="graficoSection">
                <div class="chart">
                  <canvas id="flujoCajaChart" height="100"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Distribución de Egresos -->
        <div class="row">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-pie mr-1"></i>
                  Distribución de Gastos
                </h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="chart">
                  <canvas id="egresosChart" height="250"></canvas>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-balance-scale mr-1"></i>
                  Comparativo Ingresos vs Egresos
                </h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="chart">
                  <canvas id="comparativoChart" height="250"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tablas Detalladas -->
        <div class="row">
          <!-- Ingresos -->
          <div class="col-md-12">
            <div class="card" id="ingresosSection">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-money-bill-wave mr-1"></i>
                  Ingresos (Ventas)
                </h3>
                <div class="card-tools">
                  <span class="badge badge-success"><?= count($ingresos) ?> registros</span>
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Descripción</th>
                      <th class="text-right">Monto (S/.)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($ingresos)): ?>
                      <tr>
                        <td colspan="3" class="text-center">No hay ingresos en el período seleccionado</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($ingresos as $ingreso): ?>
                        <tr>
                          <td><?= date('d/m/Y', strtotime($ingreso['fecha'])) ?></td>
                          <td><?= $ingreso['descripcion'] ?></td>
                          <td class="text-right text-success"><?= number_format($ingreso['monto'], 2) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                  <tfoot>
                    <tr class="bg-light">
                      <th colspan="2" class="text-right">Total Ingresos:</th>
                      <th class="text-right text-success">S/. <?= number_format($totalIngresos, 2) ?></th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
          
          <!-- Egresos -->
          <div class="col-md-6">
            <div class="card" id="egresosSection">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-shopping-cart mr-1"></i>
                  Gastos Operativos
                </h3>
                <div class="card-tools">
                  <span class="badge badge-danger"><?= count($egresos) ?> registros</span>
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body table-responsive p-0" style="max-height: 300px;">
                <table class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Tipo</th>
                      <th class="text-right">Monto (S/.)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($egresos)): ?>
                      <tr>
                        <td colspan="3" class="text-center">No hay gastos en el período</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($egresos as $egreso): ?>
                        <tr>
                          <td><?= date('d/m/Y', strtotime($egreso['fecha'])) ?></td>
                          <td><?= $egreso['tipo'] ?></td>
                          <td class="text-right text-danger"><?= number_format($egreso['monto'], 2) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                  <tfoot>
                    <tr class="bg-light">
                      <th colspan="2" class="text-right">Total Gastos:</th>
                      <th class="text-right text-danger">S/. <?= number_format($totalEgresos, 2) ?></th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
          
          <!-- Pagos a Proveedores -->
          <div class="col-md-6">
            <div class="card" id="proveedoresSection">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-truck mr-1"></i>
                  Pagos a Proveedores
                </h3>
                <div class="card-tools">
                  <span class="badge badge-warning"><?= count($pagosProveedores) ?> registros</span>
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body table-responsive p-0" style="max-height: 300px;">
                <table class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Proveedor</th>
                      <th class="text-right">Monto (S/.)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($pagosProveedores)): ?>
                      <tr>
                        <td colspan="3" class="text-center">No hay pagos a proveedores</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($pagosProveedores as $pago): ?>
                        <tr>
                          <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                          <td><?= strlen($pago['descripcion']) > 30 ? substr($pago['descripcion'], 0, 30) . '...' : $pago['descripcion'] ?></td>
                          <td class="text-right text-warning"><?= number_format($pago['monto'], 2) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                  <tfoot>
                    <tr class="bg-light">
                      <th colspan="2" class="text-right">Total Pagos:</th>
                      <th class="text-right text-warning">S/. <?= number_format($totalPagosProveedores, 2) ?></th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Datos para gráficos desde PHP
const datosGrafico = <?= json_encode($datosGrafico) ?>;
const totalIngresos = <?= $totalIngresos ?>;
const totalEgresos = <?= $totalEgresos ?>;
const totalPagosProveedores = <?= $totalPagosProveedores ?>;
const totalEgresosGeneral = <?= $totalEgresosGeneral ?>;
const flujoNeto = <?= $flujoNeto ?>;

// Función para resetear filtros
function resetFiltros() {
    const today = new Date().toISOString().split('T')[0];
    const firstDay = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
    
    document.querySelector('input[name="fecha_desde"]').value = firstDay;
    document.querySelector('input[name="fecha_hasta"]').value = today;
    document.querySelector('select[name="local"]').value = '1';
    document.querySelector('select[name="tipo_reporte"]').value = 'diario';
    
    document.getElementById('filtroForm').submit();
}

// Gráfico de Flujo de Caja
document.addEventListener('DOMContentLoaded', function() {
    // Preparar datos para gráfico principal
    const periodos = datosGrafico.map(item => item.periodo);
    const ingresosData = datosGrafico.map(item => item.ingresos);
    const egresosData = datosGrafico.map(item => item.egresos);
    const pagosProveedoresData = datosGrafico.map(item => item.pagos_proveedores);
    const flujoNetoData = datosGrafico.map(item => item.ingresos - (item.egresos + item.pagos_proveedores));
    
    // Gráfico principal: Flujo de Caja por Período
    const ctxFlujo = document.getElementById('flujoCajaChart').getContext('2d');
    const flujoCajaChart = new Chart(ctxFlujo, {
        type: 'bar',
        data: {
            labels: periodos,
            datasets: [
                {
                    label: 'Ingresos',
                    data: ingresosData,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Gastos Operativos',
                    data: egresosData,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Pagos a Proveedores',
                    data: pagosProveedoresData,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Flujo Neto',
                    data: flujoNetoData,
                    type: 'line',
                    fill: false,
                    borderColor: 'rgba(0, 123, 255, 1)',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 2,
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Flujo de Caja por Período'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/. ' + value.toLocaleString('es-PE');
                        }
                    }
                }
            }
        }
    });
    
    // Gráfico de Distribución de Gastos
    const ctxEgresos = document.getElementById('egresosChart').getContext('2d');
    const egresosChart = new Chart(ctxEgresos, {
        type: 'pie',
        data: {
            labels: ['Gastos Operativos', 'Pagos a Proveedores'],
            datasets: [{
                data: [totalEgresos, totalPagosProveedores],
                backgroundColor: [
                    'rgba(220, 53, 69, 0.9)',
                    'rgba(255, 193, 7, 0.9)'
                ],
                borderColor: [
                    'rgba(220, 53, 69, 1)',
                    'rgba(255, 193, 7, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Distribución de Egresos'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'S/. ' + context.raw.toLocaleString('es-PE');
                            return label;
                        }
                    }
                }
            }
        }
    });
    
    // Gráfico Comparativo Ingresos vs Egresos
    const ctxComparativo = document.getElementById('comparativoChart').getContext('2d');
    const comparativoChart = new Chart(ctxComparativo, {
        type: 'bar',
        data: {
            labels: ['Ingresos', 'Egresos Totales', 'Flujo Neto'],
            datasets: [{
                label: 'Monto (S/.)',
                data: [totalIngresos, totalEgresosGeneral, flujoNeto],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',
                    'rgba(220, 53, 69, 0.7)',
                    flujoNeto >= 0 ? 'rgba(0, 123, 255, 0.7)' : 'rgba(108, 117, 125, 0.7)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(220, 53, 69, 1)',
                    flujoNeto >= 0 ? 'rgba(0, 123, 255, 1)' : 'rgba(108, 117, 125, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Comparativo General del Período'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/. ' + value.toLocaleString('es-PE');
                        }
                    }
                }
            }
        }
    });
    
    // Actualizar gráfico cuando cambie el tipo de reporte
    document.getElementById('tipoReporte').addEventListener('change', function() {
        // Se podría implementar actualización AJAX aquí
        // Por ahora, se recarga la página con el nuevo filtro
    });
});
</script>
<?= $this->endSection(); ?>