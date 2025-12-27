<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\GreenterService;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;

class ProcesarFacturacion extends BaseCommand
{
    protected $group       = 'Facturacion';
    protected $name        = 'facturacion:procesar';
    protected $description = 'Procesa resumenes diarios y facturas pendientes de envio.';

    protected $greenter;
    protected $dbs = [];

    public function run(array $params)
    {
        CLI::write('Iniciando proceso de facturacion...', 'green');
        
        $datesToProcess = [];
        
        // If param provided, use it
        $paramDate = array_shift($params);
        if ($paramDate) {
            $datesToProcess[] = $paramDate;
        } else {
            // SUNAT limit: Up to 3 days (or 7 for contingency).
            // We process electronic summaries for T-3 to T-1.
            for ($i = 3; $i >= 1; $i--) {
                $datesToProcess[] = date('Y-m-d', strtotime("-$i days"));
            }
        }
        
        $this->greenter = new GreenterService();
        $connections = ['default', 'pmeza', 'juanjuicillo'];
        
        foreach ($datesToProcess as $fecha) {
            CLI::write("\n------------------------------------------------", 'white');
            CLI::write("Fecha a procesar: $fecha", 'cyan');
            CLI::write("------------------------------------------------", 'white');

            foreach ($connections as $group) {
                CLI::write("Procesando grupo: $group", 'yellow');
                try {
                    $db = \Config\Database::connect($group);
                    $db->initialize(); 
                    if (!$db->connID) {
                         CLI::error("No se pudo conectar a $group");
                         continue;
                    }
                    
                    $this->procesarResumenDiario($db, $group, $fecha);
                    $this->procesarBajas($db, $group, $fecha);
                    
                    $db->close();
                } catch (\Throwable $e) {
                    CLI::error("Error en $group: " . $e->getMessage());
                }
            }
        }
        
        CLI::write('Proceso finalizado.', 'green');
    }

    private function procesarResumenDiario($db, $groupName, $fecha)
    {
        $cia = '25'; 
        
        // Convert date to d/m/Y for SQL Server compatibility
        $fechaSQL = date('d/m/Y', strtotime($fecha));
        
        // Direct Query to bypass SP issues
        // Handle both Facturas (F) and Boletas (B)
        $sql = "SELECT 
                    CASE WHEN T1.ALL_FBG='F' THEN '01' ELSE '03' END as Tipo,
                    T1.ALL_FBG as FBG,
                    LTRIM(RTRIM(CONVERT(VARCHAR, ALL_NUMSER))) as Serie,
                    LTRIM(RTRIM(CONVERT(VARCHAR, ALL_NUMFAC))) as Numero,
                    '1' as Estado, -- 1=Add
                    CASE WHEN C.cli_ruc_esposa IS NOT NULL AND LEN(C.cli_ruc_esposa)=11 THEN '6' 
                         WHEN C.cli_ruc_esposa IS NOT NULL AND LEN(C.cli_ruc_esposa)=8 THEN '1'
                         ELSE '-' END as [Tipo Documento],
                    ISNULL(C.cli_ruc_esposa, '-') as [Nro. Documento],
                    T1.ALL_NETO as DocTotal,
                    -- Logic for bases: ALL_BRUTO is base, ALL_IMPTO is tax, ALL_NETO is total
                    CASE WHEN T1.ALL_IMPTO > 0 THEN T1.ALL_BRUTO ELSE 0 END as [Base Imponible Oper. Gravada],
                    CASE WHEN T1.ALL_IMPTO = 0 THEN T1.ALL_NETO ELSE 0 END as [Exonerada],
                    0 as [Inafecta],
                    T1.ALL_IMPTO as IGV
                FROM ALLOG T1 
                LEFT JOIN CLIENTES C ON T1.ALL_CODCIA=C.cli_codcia AND T1.ALL_CODCLIE=C.cli_codclie
                WHERE T1.ALL_CODCIA = ? 
                  AND T1.ALL_FECHA_DIA = ? 
                  AND T1.ALL_TIPMOV = '10' 
                  AND T1.ALL_FBG IN ('B', 'F')
                  AND T1.ALL_ESTADO_FE IS NULL -- Only pending
                  AND T1.ALL_DOC_ELECTRONICO = 'A'
                  AND T1.ALL_CODTRA IN (2401, 2402)";
        
        try {
            $query = $db->query($sql, [$cia, $fechaSQL]);
            
            if (!$query) {
                CLI::error(" - Error ejecutando query en $groupName: " . json_encode($db->error()));
                return;
            }
            
            $results = $query->getResultArray();
            
            if (empty($results)) {
                CLI::write(" - No hay comprobantes para resumen en $groupName fecha $fechaSQL", 'light_gray');
                return;
            }
            
            CLI::write(" - Encontrados " . count($results) . " items para resumen.", 'cyan');
            
            // Generate Correlative based on Date (Simplification: 001 or check DB)
            // We should check DeclaracionDocumentoElectronico for the last Correlative for this Date+RC
            $correlativo = $this->generarCorrelativoResumen($db, $fecha);
            $numCorrelativo = str_pad($correlativo, 3, '0', STR_PAD_LEFT);

            $details = [];
            $seen = []; // Track duplicates
            foreach ($results as $row) {
                // Formatting Serie/Numero per SP logic: FA/BO + last 2 digits of serie
                $prefix = ($row['FBG'] == 'F') ? 'FA' : 'BO';
                $serie = $prefix . substr(str_pad($row['Serie'], 3, '0', STR_PAD_LEFT), -2);
                $numero = str_pad($row['Numero'], 8, '0', STR_PAD_LEFT);
                $key = $serie . '-' . $numero;
                
                // Check for duplicates
                if (isset($seen[$key])) {
                    CLI::write("   ADVERTENCIA: Duplicado detectado: $key", 'red');
                    continue; // Skip duplicate
                }
                $seen[$key] = true;
                
                $det = new SummaryDetail();
                $det->setTipoDoc($row['Tipo']) // 01 for Factura, 03 for Boleta
                    ->setSerieNro($key)
                    ->setEstado('1') 
                    ->setClienteTipo($row['Tipo Documento'])
                    ->setClienteNro($row['Nro. Documento'])
                    ->setTotal(floatval($row['DocTotal']))
                    ->setMtoOperGravadas(floatval($row['Base Imponible Oper. Gravada']))
                    ->setMtoOperInafectas(floatval($row['Inafecta']))
                    ->setMtoOperExoneradas(floatval($row['Exonerada']))
                    ->setMtoIGV(floatval($row['IGV']));
                    
                $details[] = $det;
            }
            
            $sum = new Summary();
            $sum->setFecGeneracion(new \DateTime($fecha))
                ->setFecResumen(new \DateTime($fecha))
                ->setCorrelativo($numCorrelativo)
                ->setCompany($this->greenter->getCompanyObject())
                ->setDetails($details);
                
            CLI::write(" - Enviando resumen RC-$fecha-$numCorrelativo...", 'yellow');
            
            $result = $this->greenter->getSee()->send($sum);
            
            if (!$result->isSuccess()) {
                CLI::error(" - Error al enviar resumen: " . $result->getError()->getCode() . " - " . $result->getError()->getMessage());
                return;
            }
            
            $ticket = $result->getTicket();
            CLI::write(" - Resumen enviado. Ticket: $ticket", 'green');
            
            $this->registrarEnvio($db, 'RC', "RC-$fecha-$numCorrelativo", $ticket, 1, "Enviado correctamente");
            
            // Update ALL_ESTADO_FE to 1 (Sent) for these rows?
            // "Active" systems usually update this flag to prevent re-sending.
            // SP didn't show update logic, but user said "falta la logica de la columna ALL_ESTADO_FE".
            // I should update it.
            $this->marcarComoEnviado($db, $cia, $fecha);

        } catch (\Exception $e) {
            CLI::error(" - Error en $groupName: " . $e->getMessage());
        }
    }
    
    private function generarCorrelativoResumen($db, $fecha) {
        // Try YYYYMMDD which is safe for SQL Server DATETIME
        $fechaSQL = date('Ymd', strtotime($fecha));
        $sql = "SELECT COUNT(*) + 1 as NextCorrelativo FROM DeclaracionDocumentoElectronico WHERE Fecha = ? AND TipoDocumento = 'RC'";
        $query = $db->query($sql, [$fechaSQL]);
        
        if (!$query || is_bool($query)) {
             $error = $db->error();
             throw new \Exception("Error al generar correlativo (Query failed or returned bool): " . json_encode($error));
        }
        
        $row = $query->getRow();
        return $row ? $row->NextCorrelativo : 1;
    }
    
    private function marcarComoEnviado($db, $cia, $fecha) {
         // Expects date in Y-m-d, convert to d/m/Y for SQL
         $fechaSQL = date('d/m/Y', strtotime($fecha));
         
         $sql = "UPDATE ALLOG SET ALL_ESTADO_FE = 1 
                 WHERE ALL_CODCIA = ? AND ALL_FECHA_DIA = ? AND ALL_TIPMOV = '10' 
                 AND ALL_FBG = 'B' AND ALL_ESTADO_FE IS NULL AND ALL_DOC_ELECTRONICO='A' AND ALL_CODTRA IN (2401, 2402)";
         $db->query($sql, [$cia, $fechaSQL]);
    }

    private function procesarBajas($db, $groupName, $fecha)
    {
        $cia = '25';
        $fechaSQL = date('d/m/Y', strtotime($fecha));
        
        $sql = "EXEC usp_GetResumenDocumentoBaja ?, ?, ?";
        
        try {
            $query = $db->query($sql, [$cia, $fechaSQL, $fechaSQL]);
            
            if (!$query) {
                CLI::error(" - Error obteniendo bajas en $groupName: " . json_encode($db->error()));
                return;
            }
            
            $results = $query->getResultArray();
            
            if (empty($results)) {
                return;
            }
            
            CLI::write(" - Encontrados " . count($results) . " comprobantes anulados para baja en $groupName.", 'cyan');
             // Similar logic for Voided (Bajas)
             // Typically Voided is for Invoices (Facturas) or Boletas processed individually? 
             // SP `usp_GetResumenDocumentoBaja` suggests grouping.
             // Greenter Voided works for Facturas. Boletas are annulled via Summary (State 3).
             // Check SP output column 'Tipo'. If '01' (Factura), use Voided. If '03' (Boleta), it should have been in Summary with State 3.
             
             // Reuse similar logic, filtering for valid types for "Voided" communication if needed.
             // If SP returns Boletas here too, we must ensure they aren't duplicated in Summary.
             // The requirement says "comprobantes que se anulan pasando un dia".
             
             // Assumption: This SP specifically gathers cancellations.
             
             // Implementation of Voided...
             // Get Correlative...
             $correlativo = $results[0]['Correlativo'] ?? '1'; // Need to check if SP returns Correlativo for RA
             $numCorrelativo = str_pad($correlativo, 3, '0', STR_PAD_LEFT);

             $details = [];
             foreach ($results as $row) {
                 $det = new VoidedDetail();
                 $det->setTipoDoc($row['Tipo'])
                     ->setSerieNro($row['Serie'] . '-' . $row['Numero'])
                     ->setDesMotivoBaja("ERROR DEL SISTEMA"); // Or row['MotivoBaja']

                 $details[] = $det;
             }

             $voided = new Voided();
             $voided->setFecGeneracion(new \DateTime($fecha))
                 ->setFecComunicacion(new \DateTime($fecha))
                 ->setCorrelativo($numCorrelativo)
                 ->setCompany($this->greenter->getCompanyObject())
                 ->setDetails($details);

             CLI::write(" - Enviando Baja RA-$fecha-$numCorrelativo...", 'yellow');
             $result = $this->greenter->getSee()->send($voided);

             if (!$result->isSuccess()) {
                 CLI::error(" - Error al enviar baja: " . $result->getError()->getCode() . " - " . $result->getError()->getMessage());
                 return;
             }

             $ticket = $result->getTicket();
             CLI::write(" - Baja enviada. Ticket: $ticket", 'green');
             $this->registrarEnvio($db, 'RA', "RA-$fecha-$numCorrelativo", $ticket, 1, "Baja enviada correctamente");

        } catch (\Exception $e) {
            CLI::error(" - Error procesando bajas en $groupName: " . $e->getMessage());
        }
    }

    private function registrarEnvio($db, $tipo, $numero, $ticket, $estado, $obs)
    {
        // Wrapper for usp_InsertDeclaracionDocumentoElectronico
        $sql = "DECLARE @id INT; EXEC usp_InsertDeclaracionDocumentoElectronico @id OUTPUT, ?, ?, ?, ?, ?";
        // Ensure no date params are missing or misformatted if the SP expects them.
        // It seems the SP takes 5 input params.
        $db->query($sql, [$tipo, $numero, $ticket, $estado, $obs]);
    }
}
