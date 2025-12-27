<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\GreenterService;

class EnviarFacturas extends BaseCommand
{
    protected $group       = 'Facturacion';
    protected $name        = 'facturacion:enviar-facturas';
    protected $description = 'Envia facturas individuales pendientes a SUNAT.';

    protected $greenter;

    public function run(array $params)
    {
        CLI::write('Iniciando envio de facturas individuales...', 'green');
        
        $datesToProcess = [];
        
        // If param provided, use it
        $paramDate = array_shift($params);
        if ($paramDate) {
            $datesToProcess[] = $paramDate;
        } else {
            // SUNAT limit: Up to 3 days after emission.
            // Logic: Process from 3 days ago up to yesterday (skip today to allow corrections)
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
                CLI::write("\nProcesando grupo: $group", 'yellow');
                try {
                    $db = \Config\Database::connect($group);
                    $db->initialize();
                    if (!$db->connID) {
                         CLI::error("No se pudo conectar a $group");
                         continue;
                    }
                    
                    $this->procesarFacturas($db, $group, $fecha);
                    
                    $db->close();
                } catch (\Throwable $e) {
                    CLI::error("Error en $group: " . $e->getMessage());
                }
            }
        }
        
        CLI::write("\nProceso finalizado.", 'green');
    }

    private function procesarFacturas($db, $groupName, $fecha)
    {
        $cia = '25';
        
        // Convert date from YYYY-MM-DD to DD/MM/YYYY for SQL Server
        $fechaSQL = date('d/m/Y', strtotime($fecha));
        
        // Get pending invoices (headers) - based on reference query
        $sqlHeaders = "SELECT 
                        LTRIM(RTRIM(CONVERT(VARCHAR, ALL_NUMSER))) as Serie,
                        LTRIM(RTRIM(CONVERT(VARCHAR, ALL_NUMFAC))) as Numero,
                        ALL_FECHA_DIA as Fecha,
                        ALL_CODCLIE as CodigoCliente,
                        ALL_NETO as Total,
                        ALL_RUC as RucCliente
                    FROM ALLOG 
                    WHERE ALL_CODCIA = ? 
                      AND ALL_FECHA_DIA = ? 
                      AND ALL_TIPMOV = '10' 
                      AND ALL_FBG = 'F'
                      AND ALL_SIGNO_CAJA = 1
                      AND ALL_FLAG_EXT <> 'E'
                      AND ALL_ESTADO_FE IS NULL
                      AND ALL_DOC_ELECTRONICO = 'A'
                      AND (ALL_CODTRA <> 1111 AND ALL_CODTRA <> 1122)
                    ORDER BY ALL_NUMFAC";
        
        $headers = $db->query($sqlHeaders, [$cia, $fechaSQL])->getResultArray();
        
        if (empty($headers)) {
            CLI::write(" - No hay facturas pendientes en $groupName", 'light_gray');
            return;
        }
        
        CLI::write(" - Encontradas " . count($headers) . " facturas pendientes", 'cyan');
        
        foreach ($headers as $header) {
            $this->enviarFactura($db, $header, $groupName);
        }
    }

    private function enviarFactura($db, $header, $groupName)
    {
        $cia = '25';
        $serie = $header['Serie'];
        $numero = $header['Numero'];
        
        // Format serie like FA01
        $serieFormateada = 'FA' . substr(str_pad($serie, 3, '0', STR_PAD_LEFT), -2);
        $numeroFormateado = str_pad($numero, 8, '0', STR_PAD_LEFT);
        
        CLI::write("  Procesando: $serieFormateada-$numeroFormateado", 'yellow');
        
        try {
            // Get client info
            // CLI_RUC_ESPOSO = RUC for Facturas
            // CLI_RUC_ESPOSA = DNI for Boletas
            $sqlCliente = "SELECT cli_ruc_esposo, cli_nombre 
                          FROM CLIENTES 
                          WHERE cli_codcia = ? AND cli_codclie = ?";
            $resultCliente = $db->query($sqlCliente, [$cia, $header['CodigoCliente']]);
            
            if (!$resultCliente) {
                CLI::error("    Error en query de cliente: " . $db->error());
                return;
            }
            
            $cliente = $resultCliente->getRow();
            
            if (!$cliente) {
                CLI::error("    Cliente no encontrado");
                return;
            }
            
            // Get invoice details (products) - based on reference query
            $sqlDetails = "SELECT 
                            ROUND((FAR_CANTIDAD/FAR_EQUIV), 4) as Cantidad,
                            LTRIM(RTRIM(CONVERT(VARCHAR, FAR_CODART))) as Codigo,
                            RTRIM(ART_NOMBRE) as Descripcion,
                            CAST(FAR_PRECIO AS DECIMAL(16,2)) as PrecioUnitario,
                            CAST(FAR_SUBTOTAL AS DECIMAL(16,2)) as BaseImponible
                          FROM facart AS T1
                          INNER JOIN ARTI AS T2 ON (T1.FAR_CODART = T2.ART_KEY)
                          WHERE far_codcia = ? 
                            AND far_numser = ? 
                            AND far_numfac = ? 
                            AND far_tipmov = '10' 
                            AND far_fbg = 'F'
                          ORDER BY far_numsec";
            
            $details = $db->query($sqlDetails, [$cia, $serie, $numero])->getResultArray();
            
            if (empty($details)) {
                CLI::error("    No se encontraron detalles");
                return;
            }
            
            // Calculate IGV per line (0 for exonerated, or from FAR_IMPTO if available)
            $totalBase = 0;
            foreach ($details as &$detail) {
                $detail['IGV'] = 0; // Exonerated operation
                $detail['PrecioUnitario'] = floatval($detail['PrecioUnitario']);
                $detail['BaseImponible'] = floatval($detail['BaseImponible']);
                $detail['Cantidad'] = floatval($detail['Cantidad']);
                $totalBase += $detail['BaseImponible'];
            }
            
            // Prepare header data for Greenter
            // Handle empty client document (use generic values for clients without RUC/DNI)
            $clienteDoc = trim($cliente->cli_ruc_esposo ?? '');
            if (empty($clienteDoc)) {
                // Use DNI type with '-' for generic/unidentified clients
                $clienteTipo = '1'; // DNI
                $clienteDoc = '-'; // Generic/unidentified
                CLI::write("    ADVERTENCIA: Cliente sin RUC/DNI, usando valores genéricos", 'yellow');
            } else {
                $clienteTipo = (strlen($clienteDoc) == 11) ? '6' : '1';
            }
            
            $headerData = [
                'Serie' => $serieFormateada,
                'Numero' => $numeroFormateado,
                'Fecha' => $header['Fecha'],
                'ClienteTipoDoc' => $clienteTipo,
                'ClienteNumDoc' => $clienteDoc,
                'ClienteNombre' => $cliente->cli_nombre ?? 'CLIENTE GENERICO',
                'ClienteDireccion' => '', // Address not available in CLIENTES table
                'BaseImponible' => $totalBase, // Sum of line items
                'IGV' => 0, // Exonerated
                'Total' => $totalBase // For exonerated, total = base
            ];
            
            // Send to SUNAT
            $result = $this->greenter->enviarFactura($headerData, $details);
            
            if (!$result->isSuccess()) {
                $error = $result->getError();
                CLI::error("    Error: " . $error->getCode() . " - " . $error->getMessage());
                return;
            }
            
            // Success - save CDR
            $cdrZip = $result->getCdrZip();
            $cdrResponse = $result->getCdrResponse();
            
            // Create directory structure: writable/facturas/YYYY/MM/
            $year = date('Y', strtotime($header['Fecha']));
            $month = date('m', strtotime($header['Fecha']));
            $cdrDir = WRITEPATH . "facturas/$year/$month";
            
            if (!is_dir($cdrDir)) {
                mkdir($cdrDir, 0755, true);
            }
            
            // Save CDR ZIP
            $cdrFileName = "$serieFormateada-$numeroFormateado.zip";
            file_put_contents("$cdrDir/$cdrFileName", $cdrZip);
            
            // Update status in database
            $sqlUpdate = "UPDATE ALLOG SET ALL_ESTADO_FE = 1 
                         WHERE ALL_CODCIA = ? AND ALL_NUMSER = ? AND ALL_NUMFAC = ? 
                         AND ALL_TIPMOV = '10' AND ALL_FBG = 'F'";
            $db->query($sqlUpdate, [$cia, $serie, $numero]);
            
            CLI::write("    ✓ Enviado exitosamente. CDR guardado.", 'green');
            
        } catch (\Exception $e) {
            $msg = "Error al enviar $serieFormateada-$numeroFormateado: " . $e->getMessage();
            CLI::error("    " . $msg);
            
            // Log error to DB for Monitor visibility
            // We use 'ER' as type for Error, Status 0 (Error)
            $this->registrarIncidencia($db, 'ER', "$serieFormateada-$numeroFormateado", '', 0, substr($e->getMessage(), 0, 250));
        }
    }

    private function registrarIncidencia($db, $tipo, $numero, $ticket, $estado, $obs)
    {
        // Use direct query to ensure compatibility if SP changes
        // Or reuse usp_InsertDeclaracionDocumentoElectronico if available
        // Let's rely on the table directly if SP is strict, but SP seemed generic.
        // Param 1: Id OUTPUT (we can ignore output in CI4 simple query or handle it)
        // CI4 doesn't support OUTPUT params easily in query().
        // Better direct insert? 
        // Table: DeclaracionDocumentoElectronico
        // Cols based on Select: Fecha, TipoDocumento, NroOficial, Ticket, CodigoEstado, Observacion, ...
        
        try {
            // Using standard Insert
            $sql = "INSERT INTO DeclaracionDocumentoElectronico 
                    (Fecha, TipoDocumento, NroOficial, Ticket, CodigoEstado, Observacion)
                    VALUES (GETDATE(), ?, ?, ?, ?, ?)";
            $db->query($sql, [$tipo, $numero, $ticket, $estado, $obs]);
        } catch (\Throwable $t) {
            // Silent fail for log
        }
    }
}
