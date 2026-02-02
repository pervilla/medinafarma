<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?= $titulo ?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= site_url('flujocaja') ?>">Flujo de Caja</a></li>
              <li class="breadcrumb-item active">Reporte Detallado</li>
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
                <h3 class="card-title">Filtros del Reporte</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <form method="get" action="<?= site_url('flujocaja/reporte') ?>">
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
                        <label>Tipo de Movimiento</label>
                        <select class="form-control" name="tipo_movimiento">
                          <option value="todos" <?= $tipoMovimiento == 'todos' ? 'selected' : '' ?>>Todos los movimientos</option>
                          <option value="ingresos" <?= $tipoMovimiento == 'ingresos' ? 'selected' : '' ?>>Solo Ingresos</option>
                          <option value="egresos" <?= $tipoMovimiento == 'egresos' ? 'selected' : '' ?>>Solo Egresos</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Generar Reporte
                      </button>
                      <a href="<?= site_url('flujocaja') ?>" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                      </a>
                      <a href="<?= site_url('flujocaja/exportarExcel') ?>?<?= $_SERVER['QUERY_STRING'] ?>" class="btn btn-success float-right">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                      </a>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Resumen del Reporte -->
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-info-circle mr-1"></i>
                  Resumen del Período
                </h3>
                <div class="card-tools">
                  <span class="badge badge-info"><?= count($movimientos) ?> movimientos</span>
                </div>
              </div>
              <div class="card-body">
                <?php
                // Calcular totales desde los movimientos filtrados
                $totalIngresos = 0;
                $totalEgresos = 0;
                $totalPagosProveedores = 0;
                
                foreach ($movimientos as $mov) {
                    if ($mov['tipo'] === 'VENTAS') {
                        $totalIngresos += $mov['monto'];
                    } elseif ($mov['tipo'] === 'PAGO_PROVEEDOR') {
                        $totalPagosProveedores += $mov['monto'];
                    } else {
                        $totalEgresos += $mov['monto'];
                    }
                }
                
                $totalEgresosGeneral = $totalEgresos + $totalPagosProveedores;
                $flujoNeto = $totalIngresos - $totalEgresosGeneral;
                ?>
                
                <div class="row">
                  <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                      <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
                      <div class="info-box-content">
                        <span class="info-box-text">Total Ingresos</span>
                        <span class="info-box-number">S/. <?= number_format($totalIngresos, 2) ?></span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                      <span class="info-box-icon bg-danger"><i class="fas fa-shopping-cart"></i></span>
                      <div class="info-box-content">
                        <span class="info-box-text">Total Gastos</span>
                        <span class="info-box-number">S/. <?= number_format($totalEgresos, 2) ?></span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                      <span class="info-box-icon bg-warning"><i class="fas fa-truck"></i></span>
                      <div class="info-box-content">
                        <span class="info-box-text">Pagos Proveedores</span>
                        <span class="info-box-number">S/. <?= number_format($totalPagosProveedores, 2) ?></span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                      <span class="info-box-icon <?= $flujoNeto >= 0 ? 'bg-info' : 'bg-secondary' ?>">
                        <i class="fas fa-chart-line"></i>
                      </span>
                      <div class="info-box-content">
                        <span class="info-box-text">Flujo Neto</span>
                        <span class="info-box-number">S/. <?= number_format($flujoNeto, 2) ?></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabla Detallada -->
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-list mr-1"></i>
                  Movimientos Detallados
                </h3>
                <div class="card-tools">
                  <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" id="searchInput" class="form-control float-right" placeholder="Buscar...">
                    <div class="input-group-append">
                      <button type="button" class="btn btn-default">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body table-responsive p-0">
                <table class="table table-hover" id="movimientosTable">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Tipo</th>
                      <th>Descripción</th>
                      <th class="text-right">Monto (S/.)</th>
                      <th>Local</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($movimientos)): ?>
                      <tr>
                        <td colspan="5" class="text-center">No hay movimientos en el período seleccionado</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($movimientos as $mov): ?>
                        <?php
                        // Determinar clase CSS según tipo
                        $claseFila = '';
                        $claseMonto = '';
                        $icono = '';
                        
                        switch ($mov['tipo']) {
                            case 'VENTAS':
                                $claseMonto = 'text-success';
                                $icono = '<i class="fas fa-money-bill-wave text-success mr-1"></i>';
                                break;
                            case 'PAGO_PROVEEDOR':
                                $claseMonto = 'text-warning';
                                $icono = '<i class="fas fa-truck text-warning mr-1"></i>';
                                break;
                            case 'INTERES_MORA':
                                $claseMonto = 'text-danger';
                                $icono = '<i class="fas fa-percentage text-danger mr-1"></i>';
                                break;
                            case 'NORMAL':
                            case 'LETRA':
                                $claseMonto = 'text-danger';
                                $icono = '<i class="fas fa-shopping-cart text-danger mr-1"></i>';
                                break;
                            default:
                                $claseMonto = 'text-secondary';
                                $icono = '<i class="fas fa-exchange-alt text-secondary mr-1"></i>';
                        }
                        
                        // Determinar local
                        $nombreLocal = 'Local ' . ($mov['local'] ?? '1');
                        if ($mov['local'] == 2) $nombreLocal = 'Juanjuicillo';
                        if ($mov['local'] == 3) $nombreLocal = 'Peñameza';
                        ?>
                        <tr>
                          <td><?= date('d/m/Y', strtotime($mov['fecha'])) ?></td>
                          <td><?= $icono ?> <?= $mov['tipo'] ?></td>
                          <td><?= htmlspecialchars($mov['descripcion']) ?></td>
                          <td class="text-right <?= $claseMonto ?>">
                            <?= $mov['tipo'] === 'VENTAS' ? '+' : '-' ?>
                            <?= number_format($mov['monto'], 2) ?>
                          </td>
                          <td><span class="badge badge-info"><?= $nombreLocal ?></span></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                  <tfoot>
                    <tr class="bg-light">
                      <th colspan="3" class="text-right">Total Ingresos:</th>
                      <th class="text-right text-success">S/. <?= number_format($totalIngresos, 2) ?></th>
                      <th></th>
                    </tr>
                    <tr class="bg-light">
                      <th colspan="3" class="text-right">Total Gastos:</th>
                      <th class="text-right text-danger">S/. <?= number_format($totalEgresos, 2) ?></th>
                      <th></th>
                    </tr>
                    <tr class="bg-light">
                      <th colspan="3" class="text-right">Total Pagos Proveedores:</th>
                      <th class="text-right text-warning">S/. <?= number_format($totalPagosProveedores, 2) ?></th>
                      <th></th>
                    </tr>
                    <tr class="bg-dark">
                      <th colspan="3" class="text-right">Flujo Neto:</th>
                      <th class="text-right <?= $flujoNeto >= 0 ? 'text-info' : 'text-secondary' ?>">
                        S/. <?= number_format($flujoNeto, 2) ?>
                      </th>
                      <th></th>
                    </tr>
                  </tfoot>
                </table>
              </div>
              <div class="card-footer clearfix">
                <div class="float-right">
                  <small class="text-muted">
                    Mostrando <?= count($movimientos) ?> movimientos del <?= date('d/m/Y', strtotime($fechaDesde)) ?> al <?= date('d/m/Y', strtotime($fechaHasta)) ?>
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<script>
// Búsqueda en tabla
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('movimientosTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;
            
            // Buscar en todas las celdas excepto la última (local)
            for (let j = 0; j < cells.length - 1; j++) {
                const cellText = cells[j].textContent || cells[j].innerText;
                if (cellText.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            
            if (found) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    // Ordenar por fecha al hacer clic en encabezado
    const headers = table.getElementsByTagName('th');
    headers[0].addEventListener('click', function() {
        sortTable(0);
    });
});

// Función simple para ordenar tabla
function sortTable(columnIndex) {
    const table = document.getElementById('movimientosTable');
    const tbody = table.getElementsByTagName('tbody')[0];
    const rows = Array.from(tbody.getElementsByTagName('tr'));
    
    // Determinar si estamos ordenando por fecha o texto
    const isDateColumn = columnIndex === 0;
    const isNumericColumn = columnIndex === 3;
    
    // Determinar orden actual
    const currentOrder = table.getAttribute('data-sort-order') || 'asc';
    const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
    table.setAttribute('data-sort-order', newOrder);
    
    rows.sort(function(a, b) {
        const aCell = a.getElementsByTagName('td')[columnIndex];
        const bCell = b.getElementsByTagName('td')[columnIndex];
        
        let aValue = aCell.textContent || aCell.innerText;
        let bValue = bCell.textContent || bCell.innerText;
        
        if (isDateColumn) {
            // Convertir fecha dd/mm/yyyy a yyyymmdd para comparación
            aValue = aValue.split('/').reverse().join('');
            bValue = bValue.split('/').reverse().join('');
        } else if (isNumericColumn) {
            // Convertir montos a números
            aValue = parseFloat(aValue.replace(/[^0-9.-]+/g, ''));
            bValue = parseFloat(bValue.replace(/[^0-9.-]+/g, ''));
        }
        
        if (newOrder === 'asc') {
            return aValue > bValue ? 1 : -1;
        } else {
            return aValue < bValue ? 1 : -1;
        }
    });
    
    // Reconstruir tabla
    rows.forEach(function(row) {
        tbody.appendChild(row);
    });
}
</script>
<?= $this->endSection(); ?>