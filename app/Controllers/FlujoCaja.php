<?php

namespace App\Controllers;

use App\Models\CajaModel;
use App\Models\CajaMovimientosModel;
use App\Models\EgresoModel;
use App\Models\CarteraModel;
use App\Models\CaracuModel;
use App\Models\PlanCuentaModel;

class FlujoCaja extends BaseController
{
    /**
     * Dashboard principal de flujo de caja
     */
    public function index()
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('ver_flujo_caja')) {
            return redirect()->to('/')->with('error', 'No tiene permisos para acceder a esta sección');
        }
        
        $data = [
            'menu' => ['p' => 30, 'i' => 38], // Operaciones -> Flujo de Caja
            'titulo' => 'Flujo de Caja'
        ];
        
        // Obtener filtros por defecto
        $fechaDesde = $this->request->getGet('fecha_desde') ?? date('Y-m-01');
        $fechaHasta = $this->request->getGet('fecha_hasta') ?? date('Y-m-d');
        $local = $this->request->getGet('local') ?? $session->get('caja') ?? 1;
        $tipoReporte = $this->request->getGet('tipo_reporte') ?? 'diario'; // diario, semanal, mensual
        
        // Cargar modelos
        $cajaModel = new CajaModel();
        $egresoModel = new EgresoModel();
        $cajaMovimientosModel = new CajaMovimientosModel();
        $carteraModel = new CarteraModel();
        
        // Obtener datos de ingresos (ventas)
        $ingresos = $this->obtenerIngresos($fechaDesde, $fechaHasta, $local);
        
        // Obtener datos de egresos
        $egresos = $this->obtenerEgresos($fechaDesde, $fechaHasta, $local);
        
        // Obtener pagos a proveedores
        $pagosProveedores = $this->obtenerPagosProveedores($fechaDesde, $fechaHasta, $local);
        
        // Calcular totales
        $totalIngresos = array_sum(array_column($ingresos, 'monto'));
        $totalEgresos = array_sum(array_column($egresos, 'monto'));
        $totalPagosProveedores = array_sum(array_column($pagosProveedores, 'monto'));
        $totalEgresosGeneral = $totalEgresos + $totalPagosProveedores;
        $flujoNeto = $totalIngresos - $totalEgresosGeneral;
        
        // Preparar datos para gráficos
        $datosGrafico = $this->prepararDatosGrafico($ingresos, $egresos, $pagosProveedores, $tipoReporte);
        
        // Pasar datos a la vista
        $data['fechaDesde'] = $fechaDesde;
        $data['fechaHasta'] = $fechaHasta;
        $data['local'] = $local;
        $data['tipoReporte'] = $tipoReporte;
        $data['ingresos'] = $ingresos;
        $data['egresos'] = $egresos;
        $data['pagosProveedores'] = $pagosProveedores;
        $data['totalIngresos'] = $totalIngresos;
        $data['totalEgresos'] = $totalEgresos;
        $data['totalPagosProveedores'] = $totalPagosProveedores;
        $data['totalEgresosGeneral'] = $totalEgresosGeneral;
        $data['flujoNeto'] = $flujoNeto;
        $data['datosGrafico'] = $datosGrafico;
        
        return view('flujo_caja/index', $data);
    }
    
    /**
     * Obtener datos de ingresos (ventas) desde las cajas
     */
    private function obtenerIngresos($fechaDesde, $fechaHasta, $local)
    {
        $cajaModel = new CajaModel();
        
        // Convertir fechas a componentes
        $desde = date_create($fechaDesde);
        $hasta = date_create($fechaHasta);
        
        $ingresos = [];
        
        // Para cada día en el rango
        $interval = date_diff($desde, $hasta);
        $dias = $interval->days + 1;
        
        for ($i = 0; $i < $dias; $i++) {
            $fecha = date('Y-m-d', strtotime($fechaDesde . " +{$i} days"));
            $dia = date('d', strtotime($fecha));
            $mes = date('m', strtotime($fecha));
            $anio = date('Y', strtotime($fecha));
            
            // Obtener ventas del día
            $ventas = $cajaModel->get_ventas_dia($dia, $mes, $anio, null, $local);
            
            $totalVentas = 0;
            foreach ($ventas as $venta) {
                $totalVentas += $venta->CAJ_EFECTIVO;
            }
            
            if ($totalVentas > 0) {
                $ingresos[] = [
                    'fecha' => $fecha,
                    'tipo' => 'VENTAS',
                    'descripcion' => 'Ventas del día',
                    'monto' => $totalVentas
                ];
            }
        }
        
        return $ingresos;
    }
    
    /**
     * Obtener datos de egresos (gastos manuales)
     */
    private function obtenerEgresos($fechaDesde, $fechaHasta, $local)
    {
        $egresoModel = new EgresoModel();
        
        // Obtener egresos en el rango de fechas
        $egresos = $egresoModel->obtenerEgresosPorRango($fechaDesde, $fechaHasta, $local);
        
        $resultado = [];
        foreach ($egresos as $egreso) {
            $resultado[] = [
                'fecha' => $egreso['EGR_FECHA'],
                'tipo' => $egreso['EGR_TIPO_EGRESO'],
                'descripcion' => $egreso['EGR_DESCRIPCION'],
                'monto' => $egreso['EGR_MONTO']
            ];
        }
        
        return $resultado;
    }
    
    /**
     * Obtener pagos a proveedores
     */
    private function obtenerPagosProveedores($fechaDesde, $fechaHasta, $local)
    {
        $caracuModel = new CaracuModel();
        
        // Obtener pagos a proveedores en el rango de fechas
        $pagos = $caracuModel->obtenerPagosProveedoresPorRango($fechaDesde, $fechaHasta, $local);
        
        $resultado = [];
        foreach ($pagos as $pago) {
            $resultado[] = [
                'fecha' => $pago['CAA_FECHA'],
                'tipo' => 'PAGO_PROVEEDOR',
                'descripcion' => 'Pago a proveedor: ' . $pago['CLI_NOMBRE'],
                'monto' => $pago['CAA_IMPORTE']
            ];
        }
        
        return $resultado;
    }
    
    /**
     * Preparar datos para gráficos
     */
    private function prepararDatosGrafico($ingresos, $egresos, $pagosProveedores, $tipoReporte)
    {
        $datos = [];
        
        // Combinar todos los movimientos
        $movimientos = array_merge($ingresos, $egresos, $pagosProveedores);
        
        // Agrupar por período según tipo de reporte
        foreach ($movimientos as $movimiento) {
            $fecha = $movimiento['fecha'];
            
            switch ($tipoReporte) {
                case 'diario':
                    $periodo = $fecha;
                    break;
                case 'semanal':
                    $semana = date('W', strtotime($fecha));
                    $anio = date('Y', strtotime($fecha));
                    $periodo = "Semana {$semana}-{$anio}";
                    break;
                case 'mensual':
                    $periodo = date('Y-m', strtotime($fecha));
                    break;
                default:
                    $periodo = $fecha;
            }
            
            if (!isset($datos[$periodo])) {
                $datos[$periodo] = [
                    'periodo' => $periodo,
                    'ingresos' => 0,
                    'egresos' => 0,
                    'pagos_proveedores' => 0
                ];
            }
            
            // Clasificar por tipo
            if ($movimiento['tipo'] === 'VENTAS') {
                $datos[$periodo]['ingresos'] += $movimiento['monto'];
            } elseif ($movimiento['tipo'] === 'PAGO_PROVEEDOR') {
                $datos[$periodo]['pagos_proveedores'] += $movimiento['monto'];
            } else {
                $datos[$periodo]['egresos'] += $movimiento['monto'];
            }
        }
        
        // Ordenar por período
        ksort($datos);
        
        return array_values($datos);
    }
    
    /**
     * Endpoint para obtener datos de flujo de caja en formato JSON
     */
    public function obtenerDatos()
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('ver_flujo_caja')) {
            return $this->response->setJSON(['error' => 'No tiene permisos']);
        }
        
        $fechaDesde = $this->request->getGet('fecha_desde') ?? date('Y-m-01');
        $fechaHasta = $this->request->getGet('fecha_hasta') ?? date('Y-m-d');
        $local = $this->request->getGet('local') ?? $session->get('caja') ?? 1;
        $tipoReporte = $this->request->getGet('tipo_reporte') ?? 'diario';
        
        // Obtener datos
        $ingresos = $this->obtenerIngresos($fechaDesde, $fechaHasta, $local);
        $egresos = $this->obtenerEgresos($fechaDesde, $fechaHasta, $local);
        $pagosProveedores = $this->obtenerPagosProveedores($fechaDesde, $fechaHasta, $local);
        
        // Preparar datos para gráficos
        $datosGrafico = $this->prepararDatosGrafico($ingresos, $egresos, $pagosProveedores, $tipoReporte);
        
        return $this->response->setJSON([
            'success' => true,
            'datos' => $datosGrafico,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'pagosProveedores' => $pagosProveedores
        ]);
    }
    
    /**
     * Reporte detallado de flujo de caja
     */
    public function reporte()
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('ver_flujo_caja')) {
            return redirect()->to('/')->with('error', 'No tiene permisos para acceder a esta sección');
        }
        
        $data = [
            'menu' => ['p' => 30, 'i' => 38],
            'titulo' => 'Reporte Detallado de Flujo de Caja'
        ];
        
        // Obtener filtros
        $fechaDesde = $this->request->getGet('fecha_desde') ?? date('Y-m-01');
        $fechaHasta = $this->request->getGet('fecha_hasta') ?? date('Y-m-d');
        $local = $this->request->getGet('local') ?? $session->get('caja') ?? 1;
        $tipoMovimiento = $this->request->getGet('tipo_movimiento'); // ingresos, egresos, todos
        
        // Obtener datos
        $ingresos = $this->obtenerIngresos($fechaDesde, $fechaHasta, $local);
        $egresos = $this->obtenerEgresos($fechaDesde, $fechaHasta, $local);
        $pagosProveedores = $this->obtenerPagosProveedores($fechaDesde, $fechaHasta, $local);
        
        // Combinar todos los movimientos
        $movimientos = array_merge($ingresos, $egresos, $pagosProveedores);
        
        // Filtrar por tipo si se especifica
        if ($tipoMovimiento === 'ingresos') {
            $movimientos = array_filter($movimientos, function($mov) {
                return $mov['tipo'] === 'VENTAS';
            });
        } elseif ($tipoMovimiento === 'egresos') {
            $movimientos = array_filter($movimientos, function($mov) {
                return $mov['tipo'] !== 'VENTAS';
            });
        }
        
        // Ordenar por fecha
        usort($movimientos, function($a, $b) {
            return strcmp($a['fecha'], $b['fecha']);
        });
        
        $data['fechaDesde'] = $fechaDesde;
        $data['fechaHasta'] = $fechaHasta;
        $data['local'] = $local;
        $data['tipoMovimiento'] = $tipoMovimiento;
        $data['movimientos'] = $movimientos;
        
        return view('flujo_caja/reporte', $data);
    }
    
    /**
     * Exportar reporte a Excel
     */
    public function exportarExcel()
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('ver_flujo_caja')) {
            return redirect()->to('/')->with('error', 'No tiene permisos');
        }
        
        $fechaDesde = $this->request->getGet('fecha_desde') ?? date('Y-m-01');
        $fechaHasta = $this->request->getGet('fecha_hasta') ?? date('Y-m-d');
        $local = $this->request->getGet('local') ?? $session->get('caja') ?? 1;
        
        // Obtener datos
        $ingresos = $this->obtenerIngresos($fechaDesde, $fechaHasta, $local);
        $egresos = $this->obtenerEgresos($fechaDesde, $fechaHasta, $local);
        $pagosProveedores = $this->obtenerPagosProveedores($fechaDesde, $fechaHasta, $local);
        
        // Combinar y ordenar
        $movimientos = array_merge($ingresos, $egresos, $pagosProveedores);
        usort($movimientos, function($a, $b) {
            return strcmp($a['fecha'], $b['fecha']);
        });
        
        // Crear archivo Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Encabezados
        $sheet->setCellValue('A1', 'Fecha');
        $sheet->setCellValue('B1', 'Tipo');
        $sheet->setCellValue('C1', 'Descripción');
        $sheet->setCellValue('D1', 'Monto (S/.)');
        
        // Datos
        $row = 2;
        foreach ($movimientos as $mov) {
            $sheet->setCellValue('A' . $row, $mov['fecha']);
            $sheet->setCellValue('B' . $row, $mov['tipo']);
            $sheet->setCellValue('C' . $row, $mov['descripcion']);
            $sheet->setCellValue('D' . $row, $mov['monto']);
            $row++;
        }
        
        // Autoajustar columnas
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Configurar respuesta
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $filename = 'flujo_caja_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Verificar permisos del usuario
     */
    private function tienePermiso($permiso)
    {
        $session = session();
        $rol = $session->get('rol');
        
        // Mapeo de roles a permisos
        $permisos = [
            'ADMIN' => ['ver_flujo_caja', 'ver_facturas', 'ver_egresos'],
            'CONTADOR' => ['ver_flujo_caja', 'ver_facturas', 'ver_egresos'],
            'CAJERO' => ['ver_flujo_caja'],
            'VENDEDOR' => []
        ];
        
        return in_array($permiso, $permisos[$rol] ?? []);
    }
}