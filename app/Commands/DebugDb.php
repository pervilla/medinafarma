<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DebugDb extends BaseCommand
{
    protected $group       = 'Facturacion';
    protected $name        = 'debug:db';
    protected $description = 'Muestra los ultimos registros de ALLOG para verificar datos.';

    public function run(array $params)
    {
        $db = \Config\Database::connect('juanjuicillo');
        
        CLI::write("Buscando resumenes pendientes en juanjuicillo para 2025-12-15:", 'yellow');
        
        // Use d/m/Y
        $fecha = '15/12/2025';
        
        $sql = "SELECT 
                    ALL_FECHA_DIA, ALL_NUMSER, ALL_NUMFAC, ALL_ESTADO_FE, ALL_DOC_ELECTRONICO
                FROM ALLOG 
                WHERE ALL_CODCIA='25' AND ALL_FECHA_DIA = ? 
                  AND ALL_TIPMOV='10' AND ALL_FBG IN ('B', 'F')
                  AND ALL_ESTADO_FE IS NULL
                  AND ALL_DOC_ELECTRONICO='A'
                  AND ALL_CODTRA IN (2401, 2402)";
        
        $results = $db->query($sql, [$fecha])->getResultArray();
        
        if (empty($results)) {
            CLI::write("No se encontraron pendientes. Buscando totales...", 'red');
            
            // Check if they exist but processed
            $sqlProcessed = "SELECT COUNT(*) as Cantidad FROM ALLOG WHERE ALL_CODCIA='25' AND ALL_FECHA_DIA = '15/12/2025' AND ALL_TIPMOV='10' AND ALL_FBG IN ('B', 'F') AND ALL_CODTRA IN (2401, 2402)";
            $processed = $db->query($sqlProcessed)->getRow();
            CLI::write("Total items (procesados o no): " . $processed->Cantidad, 'cyan');
            
            // Check status of these items
            $sqlStatus = "SELECT TOP 5 ALL_NUMFAC, ALL_ESTADO_FE FROM ALLOG WHERE ALL_CODCIA='25' AND ALL_FECHA_DIA = '15/12/2025' AND ALL_TIPMOV='10' AND ALL_FBG IN ('B', 'F') AND ALL_CODTRA IN (2401, 2402)";
            $statuses = $db->query($sqlStatus)->getResultArray();
            foreach ($statuses as $st) {
                 CLI::write("  Fac: {$st['ALL_NUMFAC']} - Estado: " . var_export($st['ALL_ESTADO_FE'], true), 'light_gray');
            }
        } else {
            CLI::write("Pendientes: " . count($results), 'green');
        }
    }
}
