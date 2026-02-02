<?php

namespace App\Models;

use CodeIgniter\Model;

class FlujoCajaModel extends Model
{
    protected $table = 'CAJA_MOVIMIENTOS'; // Tabla principal, pero usaremos múltiples fuentes
    protected $primaryKey = 'CM_ID';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = []; // No usamos inserción directa en este modelo
    
    protected $useTimestamps = false;
    
    // Conexiones a bases de datos por local
    private $dbLocal1;
    private $dbLocal2;
    private $dbLocal3;
    
    public function __construct()
    {
        parent::__construct();
        $this->dbLocal1 = \Config\Database::connect();
        $this->dbLocal2 = \Config\Database::connect('juanjuicillo');
        $this->dbLocal3 = \Config\Database::connect('pmeza');
    }
    
    /**
     * Obtiene conexión a base de datos según local
     */
    private function getDbByLocal($local)
    {
        switch ($local) {
            case 1:
                return $this->dbLocal1;
            case 2:
                return $this->dbLocal2;
            case 3:
                return $this->dbLocal3;
            default:
                return $this->dbLocal1;
        }
    }
    
    /**
     * Obtiene flujo de caja consolidado por período
     */
    public function obtenerFlujoCaja($fechaDesde, $fechaHasta, $local = 1, $agrupacion = 'diario')
    {
        $db = $this->getDbByLocal($local);
        
        // Obtener ingresos (ventas)
        $ingresos = $this->obtenerIngresos($fechaDesde, $fechaHasta, $local, $db);
        
        // Obtener egresos (gastos)
        $egresos = $this->obtenerEgresos($fechaDesde, $fechaHasta, $local, $db);
        
        // Obtener pagos a proveedores
        $pagosProveedores = $this->obtenerPagosProveedores($fechaDesde, $fechaHasta, $local, $db);
        
        // Consolidar por período según agrupación
        $consolidado = $this->consolidarPorPeriodo($ingresos, $egresos, $pagosProveedores, $agrupacion);
        
        return [
            'consolidado' => $consolidado,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'pagosProveedores' => $pagosProveedores
        ];
    }
    
    /**
     * Obtiene ingresos (ventas) desde CAJAS y ALLOG
     */
    private function obtenerIngresos($fechaDesde, $fechaHasta, $local, $db)
    {
        // Consulta para obtener ventas diarias
        $sql = "
            SELECT 
                CONVERT(DATE, CAJ_FECHA) as fecha,
                SUM(CAJ_EFECTIVO) as monto,
                COUNT(*) as cantidad_ventas
            FROM CAJAS
            WHERE 
                CAJ_FECHA >= ? AND 
                CAJ_FECHA <= ? AND
                CAJ_ESTADO = 'C'
            GROUP BY CONVERT(DATE, CAJ_FECHA)
            ORDER BY CONVERT(DATE, CAJ_FECHA)
        ";
        
        $query = $db->query($sql, [$fechaDesde, $fechaHasta]);
        $ventas = $query->getResultArray();
        
        // Formatear resultado
        $resultado = [];
        foreach ($ventas as $venta) {
            $resultado[] = [
                'fecha' => $venta['fecha'],
                'tipo' => 'VENTAS',
                'descripcion' => 'Ventas del día',
                'monto' => floatval($venta['monto']),
                'cantidad' => intval($venta['cantidad_ventas']),
                'local' => $local
            ];
        }
        
        return $resultado;
    }
    
    /**
     * Obtiene egresos desde EGRESOS table
     */
    private function obtenerEgresos($fechaDesde, $fechaHasta, $local, $db)
    {
        $egresoModel = new EgresoModel();
        
        // Usar el método que ya existe en EgresoModel
        $egresos = $egresoModel->obtenerEgresosPorRango($fechaDesde, $fechaHasta, $local);
        
        // Formatear resultado
        $resultado = [];
        foreach ($egresos as $egreso) {
            $resultado[] = [
                'fecha' => $egreso['EGR_FECHA'],
                'tipo' => $egreso['EGR_TIPO_EGRESO'],
                'descripcion' => $egreso['EGR_DESCRIPCION'],
                'monto' => floatval($egreso['EGR_MONTO']),
                'cuenta' => $egreso['PC_CODIGO'] . ' - ' . $egreso['cuenta_nombre'],
                'local' => $local
            ];
        }
        
        return $resultado;
    }
    
    /**
     * Obtiene pagos a proveedores desde CARACU
     */
    private function obtenerPagosProveedores($fechaDesde, $fechaHasta, $local, $db)
    {
        $caracuModel = new CaracuModel();
        
        // Usar el método que ya existe en CaracuModel
        $pagos = $caracuModel->obtenerPagosProveedoresPorRango($fechaDesde, $fechaHasta, $local);
        
        // Formatear resultado
        $resultado = [];
        foreach ($pagos as $pago) {
            $resultado[] = [
                'fecha' => $pago['CAA_FECHA'],
                'tipo' => 'PAGO_PROVEEDOR',
                'descripcion' => 'Pago a proveedor: ' . $pago['CLI_NOMBRE'],
                'monto' => floatval($pago['CAA_IMPORTE']),
                'proveedor' => $pago['CLI_NOMBRE'],
                'factura' => $pago['CAA_NUMFAC'],
                'local' => $local
            ];
        }
        
        return $resultado;
    }
    
    /**
     * Obtiene movimientos de caja desde CAJA_MOVIMIENTOS
     */
    public function obtenerMovimientosCaja($fechaDesde, $fechaHasta, $local = 1)
    {
        $db = $this->getDbByLocal($local);
        
        $sql = "
            SELECT 
                CM_FECHA as fecha,
                CM_MOTIVO as motivo,
                CM_DESCRIPCION as descripcion,
                CM_MONTO as monto,
                CM_USUARIO as usuario,
                CM_REFERENCIA as referencia,
                CM_ESTADO as estado
            FROM CAJA_MOVIMIENTOS
            WHERE 
                CM_FECHA >= ? AND 
                CM_FECHA <= ? AND
                CM_ESTADO = 'CONFIRMADO'
            ORDER BY CM_FECHA, CM_ID
        ";
        
        $query = $db->query($sql, [$fechaDesde, $fechaHasta]);
        return $query->getResultArray();
    }
    
    /**
     * Consolidar datos por período (diario, semanal, mensual)
     */
    private function consolidarPorPeriodo($ingresos, $egresos, $pagosProveedores, $agrupacion)
    {
        $consolidado = [];
        
        // Combinar todos los movimientos
        $movimientos = array_merge($ingresos, $egresos, $pagosProveedores);
        
        foreach ($movimientos as $mov) {
            $fecha = $mov['fecha'];
            
            // Determinar período según agrupación
            switch ($agrupacion) {
                case 'diario':
                    $periodo = $fecha;
                    $etiqueta = date('d/m/Y', strtotime($fecha));
                    break;
                case 'semanal':
                    $semana = date('W', strtotime($fecha));
                    $anio = date('Y', strtotime($fecha));
                    $periodo = "{$anio}-W{$semana}";
                    $etiqueta = "Semana {$semana} de {$anio}";
                    break;
                case 'mensual':
                    $periodo = date('Y-m', strtotime($fecha));
                    $etiqueta = date('F Y', strtotime($fecha));
                    break;
                default:
                    $periodo = $fecha;
                    $etiqueta = date('d/m/Y', strtotime($fecha));
            }
            
            if (!isset($consolidado[$periodo])) {
                $consolidado[$periodo] = [
                    'periodo' => $periodo,
                    'etiqueta' => $etiqueta,
                    'ingresos' => 0,
                    'egresos' => 0,
                    'pagos_proveedores' => 0,
                    'flujo_neto' => 0,
                    'cantidad_movimientos' => 0
                ];
            }
            
            // Acumular según tipo
            if ($mov['tipo'] === 'VENTAS') {
                $consolidado[$periodo]['ingresos'] += $mov['monto'];
            } elseif ($mov['tipo'] === 'PAGO_PROVEEDOR') {
                $consolidado[$periodo]['pagos_proveedores'] += $mov['monto'];
            } else {
                $consolidado[$periodo]['egresos'] += $mov['monto'];
            }
            
            $consolidado[$periodo]['cantidad_movimientos']++;
        }
        
        // Calcular flujo neto para cada período
        foreach ($consolidado as &$periodo) {
            $totalEgresos = $periodo['egresos'] + $periodo['pagos_proveedores'];
            $periodo['flujo_neto'] = $periodo['ingresos'] - $totalEgresos;
        }
        
        // Ordenar por período
        ksort($consolidado);
        
        return array_values($consolidado);
    }
    
    /**
     * Obtiene estadísticas resumidas de flujo de caja
     */
    public function obtenerEstadisticas($fechaDesde, $fechaHasta, $local = 1)
    {
        $flujo = $this->obtenerFlujoCaja($fechaDesde, $fechaHasta, $local, 'diario');
        
        $consolidado = $flujo['consolidado'];
        
        if (empty($consolidado)) {
            return [
                'total_ingresos' => 0,
                'total_egresos' => 0,
                'total_pagos_proveedores' => 0,
                'flujo_neto_total' => 0,
                'promedio_diario_ingresos' => 0,
                'promedio_diario_egresos' => 0,
                'dias_positivos' => 0,
                'dias_negativos' => 0,
                'mayor_ingreso' => 0,
                'mayor_egreso' => 0
            ];
        }
        
        // Calcular totales
        $totalIngresos = array_sum(array_column($consolidado, 'ingresos'));
        $totalEgresos = array_sum(array_column($consolidado, 'egresos'));
        $totalPagosProveedores = array_sum(array_column($consolidado, 'pagos_proveedores'));
        $flujoNetoTotal = $totalIngresos - ($totalEgresos + $totalPagosProveedores);
        
        // Calcular promedios diarios
        $dias = count($consolidado);
        $promedioDiarioIngresos = $dias > 0 ? $totalIngresos / $dias : 0;
        $promedioDiarioEgresos = $dias > 0 ? ($totalEgresos + $totalPagosProveedores) / $dias : 0;
        
        // Contar días con flujo positivo/negativo
        $diasPositivos = 0;
        $diasNegativos = 0;
        $mayorIngreso = 0;
        $mayorEgreso = 0;
        
        foreach ($consolidado as $dia) {
            if ($dia['flujo_neto'] > 0) {
                $diasPositivos++;
            } elseif ($dia['flujo_neto'] < 0) {
                $diasNegativos++;
            }
            
            if ($dia['ingresos'] > $mayorIngreso) {
                $mayorIngreso = $dia['ingresos'];
            }
            
            $totalEgresosDia = $dia['egresos'] + $dia['pagos_proveedores'];
            if ($totalEgresosDia > $mayorEgreso) {
                $mayorEgreso = $totalEgresosDia;
            }
        }
        
        return [
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'total_pagos_proveedores' => $totalPagosProveedores,
            'flujo_neto_total' => $flujoNetoTotal,
            'promedio_diario_ingresos' => $promedioDiarioIngresos,
            'promedio_diario_egresos' => $promedioDiarioEgresos,
            'dias_positivos' => $diasPositivos,
            'dias_negativos' => $diasNegativos,
            'mayor_ingreso' => $mayorIngreso,
            'mayor_egreso' => $mayorEgreso,
            'total_dias' => $dias
        ];
    }
    
    /**
     * Proyecta flujo de caja futuro basado en patrones históricos
     */
    public function proyectarFlujo($diasProyeccion = 30, $local = 1)
    {
        // Obtener datos históricos de los últimos 90 días
        $fechaHasta = date('Y-m-d');
        $fechaDesde = date('Y-m-d', strtotime("-90 days"));
        
        $flujo = $this->obtenerFlujoCaja($fechaDesde, $fechaHasta, $local, 'diario');
        $consolidado = $flujo['consolidado'];
        
        if (empty($consolidado)) {
            return [];
        }
        
        // Calcular promedios por día de la semana
        $promediosDiaSemana = [
            0 => ['ingresos' => 0, 'egresos' => 0, 'count' => 0], // Domingo
            1 => ['ingresos' => 0, 'egresos' => 0, 'count' => 0], // Lunes
            2 => ['ingresos' => 0, 'egresos' => 0, 'count' => 0], // Martes
            3 => ['ingresos' => 0, 'egresos' => 0, 'count' => 0], // Miércoles
            4 => ['ingresos' => 0, 'egresos' => 0, 'count' => 0], // Jueves
            5 => ['ingresos' => 0, 'egresos' => 0, 'count' => 0], // Viernes
            6 => ['ingresos' => 0, 'egresos' => 0, 'count' => 0]  // Sábado
        ];
        
        foreach ($consolidado as $dia) {
            $fecha = date_create($dia['periodo']);
            $diaSemana = date_format($fecha, 'w'); // 0 = Domingo, 6 = Sábado
            
            $promediosDiaSemana[$diaSemana]['ingresos'] += $dia['ingresos'];
            $promediosDiaSemana[$diaSemana]['egresos'] += ($dia['egresos'] + $dia['pagos_proveedores']);
            $promediosDiaSemana[$diaSemana]['count']++;
        }
        
        // Calcular promedios
        for ($i = 0; $i < 7; $i++) {
            if ($promediosDiaSemana[$i]['count'] > 0) {
                $promediosDiaSemana[$i]['ingresos'] /= $promediosDiaSemana[$i]['count'];
                $promediosDiaSemana[$i]['egresos'] /= $promediosDiaSemana[$i]['count'];
            }
        }
        
        // Generar proyección
        $proyeccion = [];
        $fechaActual = date('Y-m-d');
        
        for ($i = 1; $i <= $diasProyeccion; $i++) {
            $fechaProyectada = date('Y-m-d', strtotime("+{$i} days", strtotime($fechaActual)));
            $diaSemana = date('w', strtotime($fechaProyectada));
            
            $ingresosProyectados = $promediosDiaSemana[$diaSemana]['ingresos'];
            $egresosProyectados = $promediosDiaSemana[$diaSemana]['egresos'];
            $flujoNetoProyectado = $ingresosProyectados - $egresosProyectados;
            
            $proyeccion[] = [
                'fecha' => $fechaProyectada,
                'dia_semana' => $this->getNombreDiaSemana($diaSemana),
                'ingresos_proyectados' => $ingresosProyectados,
                'egresos_proyectados' => $egresosProyectados,
                'flujo_neto_proyectado' => $flujoNetoProyectado
            ];
        }
        
        return $proyeccion;
    }
    
    /**
     * Obtiene nombre del día de la semana en español
     */
    private function getNombreDiaSemana($numeroDia)
    {
        $dias = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];
        
        return $dias[$numeroDia] ?? 'Desconocido';
    }
}