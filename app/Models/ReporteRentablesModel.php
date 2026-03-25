<?php

namespace App\Models;

use CodeIgniter\Model;

class ReporteRentablesModel extends Model
{
    protected $db;
    protected $dbpm;
    protected $dbjj;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect('default', false);
        $this->dbpm = \Config\Database::connect('pmeza', false);
        $this->dbjj = \Config\Database::connect('juanjuicillo', false);
    }

    /**
     * Checks if all 3 database servers are available.
     * Returns true if all are online, false otherwise.
     */
    public function check_servers_availability()
    {
        $groups = [
            'Local' => 'default',
            'Juanjuicillo' => 'juanjuicillo',
            'Pmeza' => 'pmeza'
        ];

        $unavailable = [];
        $dbConfig = config('Database');

        foreach ($groups as $name => $group) {
            if (!isset($dbConfig->$group)) {
                $unavailable[] = $name;
                continue;
            }

            $config = $dbConfig->$group;
            $host = $config['hostname'];
            $port = $config['port'] ?? 1433;

            // Limpiar hostname si tiene instancia (ej: host\instance)
            $hostOnly = explode('\\', $host)[0];

            // Timeout de 1 segundo para no congelar la app
            $connection = @fsockopen($hostOnly, $port, $errno, $errstr, 1);

            if ($connection) {
                fclose($connection);
            } else {
                log_message('error', "Server $name ($group) at {$hostOnly}:{$port} unavailable. Error: {$errstr}");
                $unavailable[] = $name;
            }
        }

        if (!empty($unavailable)) {
            return [
                'success' => false,
                'message' => 'Los siguientes servidores no están disponibles: ' . implode(', ', $unavailable)
            ];
        }

        return ['success' => true];
    }

    /**
     * Gets profitable product sales aggregated by employee in a date range across all 3 servers.
     */
    public function get_ventas_rentables_empleado($fechaInicio, $fechaFin)
    {
        // 1. Get profitable families from planilla_reglas_comision
        // Usamos la conexión default (Local) para obtener las reglas de rentabilidad
        $queryReglas = $this->db->query("SELECT referencia_id FROM planilla_reglas_comision WHERE tipo = 'FAMILIA'");
        $reglas = $queryReglas->getResultArray();
        
        if (empty($reglas)) {
            return []; // No hay familias rentables configuradas
        }

        $familias_rentables = array_column($reglas, 'referencia_id');
        $familias_in = implode(',', $familias_rentables);

        // Ensure dates are in YYYYMMDD format for SQL Server
        $fechaInicioStr = date('Ymd', strtotime($fechaInicio));
        $fechaFinStr = date('Ymd', strtotime($fechaFin));

        // Helper: Query para ventas RENTABLES (solo familias configuradas)
        $buildQueryRentable = function () use ($fechaInicioStr, $fechaFinStr, $familias_in) {
            $sql = "SELECT 
                        T1.FAR_CODVEN, 
                        T2.VEM_NOMBRE, 
                        SUM(T1.FAR_CANTIDAD/T1.FAR_EQUIV*T1.FAR_PRECIO) as TOTAL_VENTA_RENTABLE
                    FROM FACART T1 
                    INNER JOIN VEMAEST T2 ON (T1.FAR_CODVEN = T2.VEM_CODVEN AND T1.FAR_CODCIA = T2.VEM_CODCIA) 
                    INNER JOIN ARTI T3 ON (T1.FAR_CODART = T3.ART_KEY AND T1.FAR_CODCIA = T3.ART_CODCIA) 
                    INNER JOIN CLIENTES T5 ON (T1.FAR_CODCLIE = T5.CLI_CODCLIE AND T1.FAR_CODCIA = T5.CLI_CODCIA AND T1.FAR_CP = T5.CLI_CP) 
                    WHERE T1.FAR_FECHA BETWEEN '$fechaInicioStr' AND '$fechaFinStr' 
                    AND T1.FAR_ESTADO <> 'E' 
                    AND T1.FAR_ESTADO2 <> 'L' 
                    AND T5.CLI_LETRA <> '1' 
                    AND T1.FAR_TIPMOV = 10 
                    AND T3.ART_FAMILIA IN ($familias_in)
                    GROUP BY T1.FAR_CODVEN, T2.VEM_NOMBRE";
            return $sql;
        };

        // Helper: Query para ventas BRUTAS (todos los productos)
        $buildQueryBruto = function () use ($fechaInicioStr, $fechaFinStr) {
            $sql = "SELECT 
                        T1.FAR_CODVEN, 
                        T2.VEM_NOMBRE, 
                        SUM(T1.FAR_CANTIDAD/T1.FAR_EQUIV*T1.FAR_PRECIO) as TOTAL_BRUTO
                    FROM FACART T1 
                    INNER JOIN VEMAEST T2 ON (T1.FAR_CODVEN = T2.VEM_CODVEN AND T1.FAR_CODCIA = T2.VEM_CODCIA) 
                    INNER JOIN CLIENTES T5 ON (T1.FAR_CODCLIE = T5.CLI_CODCLIE AND T1.FAR_CODCIA = T5.CLI_CODCIA AND T1.FAR_CP = T5.CLI_CP) 
                    WHERE T1.FAR_FECHA BETWEEN '$fechaInicioStr' AND '$fechaFinStr' 
                    AND T1.FAR_ESTADO <> 'E' 
                    AND T1.FAR_ESTADO2 <> 'L' 
                    AND T5.CLI_LETRA <> '1' 
                    AND T1.FAR_TIPMOV = 10 
                    GROUP BY T1.FAR_CODVEN, T2.VEM_NOMBRE";
            return $sql;
        };

        // En lugar de hacer un UNION (que requiere Linked Servers funcionando 100%),
        // hacemos consultas separadas a cada base de datos y combinamos el resultado en PHP.
        // Esto es mucho más seguro para la estabilidad de la aplicación y evita bloqueos por Linked Servers.

        $servers = [
            $this->db,    // Local
            $this->dbjj,  // Juanjuicillo
            $this->dbpm   // Pmeza
        ];

        $resultadosAgrupados = [];

        foreach ($servers as $conexion) {
            try {
                // 1. Ventas Rentables
                $sqlRentable = $buildQueryRentable();
                $queryRent = $conexion->query($sqlRentable);
                $resultadosRent = $queryRent->getResult();

                foreach ($resultadosRent as $row) {
                    $codven = $row->FAR_CODVEN;
                    if (!isset($resultadosAgrupados[$codven])) {
                        $resultadosAgrupados[$codven] = [
                            'vem_codven' => $codven,
                            'nombre' => trim($row->VEM_NOMBRE),
                            'total_ventas' => 0,
                            'total_bruto' => 0
                        ];
                    }
                    $resultadosAgrupados[$codven]['total_ventas'] += (float)$row->TOTAL_VENTA_RENTABLE;
                }

                // 2. Ventas Brutas (todos los productos)
                $sqlBruto = $buildQueryBruto();
                $queryBruto = $conexion->query($sqlBruto);
                $resultadosBruto = $queryBruto->getResult();

                foreach ($resultadosBruto as $row) {
                    $codven = $row->FAR_CODVEN;
                    if (!isset($resultadosAgrupados[$codven])) {
                        $resultadosAgrupados[$codven] = [
                            'vem_codven' => $codven,
                            'nombre' => trim($row->VEM_NOMBRE),
                            'total_ventas' => 0,
                            'total_bruto' => 0
                        ];
                    }
                    $resultadosAgrupados[$codven]['total_bruto'] += (float)$row->TOTAL_BRUTO;
                }
            } catch (\Throwable $th) {
                log_message('error', 'Error extrayendo ventas rentables: ' . $th->getMessage());
                throw $th;
            }
        }

        // Convertir asociativo a indexado y ordenar por ventas rentables (descendente)
        $ventas_final = array_values($resultadosAgrupados);
        
        usort($ventas_final, function($a, $b) {
            return $b['total_ventas'] <=> $a['total_ventas'];
        });

        // Calcular % de rentables sobre bruto
        foreach ($ventas_final as &$venta) {
            $venta['total_ventas_fmt'] = number_format($venta['total_ventas'], 2, '.', ',');
            $venta['total_bruto_fmt'] = number_format($venta['total_bruto'], 2, '.', ',');
            $venta['pct_rentable'] = $venta['total_bruto'] > 0 
                ? round(($venta['total_ventas'] / $venta['total_bruto']) * 100, 2) 
                : 0;
        }

        return $ventas_final;
    }
}
