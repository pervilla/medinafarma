<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para gestión de pagos de citas
 * Tabla CITAS_PAGOS
 */
class CitaPagoModel extends Model
{
    protected $table = 'CITAS_PAGOS';
    protected $primaryKey = 'CTP_CODCTP';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'CIT_CODCIT',
        'CTP_FECHA',
        'CTP_MONTO',
        'CTP_FORMA_PAGO',
        'CTP_REFERENCIA',
        'CTP_LOCAL_PAGO',
        'CTP_ESTADO',
        'CTP_GENERO_COMPROBANTE',
        'CTP_TIPO_COMPROBANTE',
        'CTP_SERIE',
        'CTP_NUMERO',
        'CTP_CODCIA'
    ];

    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Generar comprobante directo usando stored procedure v3 (evita pagos duplicados)
     */
    public function generarComprobanteDirecto($citaId, $localPago, $tipoComprobante = 'B', $formaPago = 'EFECTIVO', $referencia = '')
    {
        try {
            log_message('info', 'CitaPagoModel: Ejecutando SP_GenerarComprobanteConsultorio_v3 para cita: ' . $citaId);
            
            // Ejecutar stored procedure v3 con parámetros OUTPUT
            $sql = "DECLARE @NumFac INT, @NumSer INT, @Total DECIMAL(11,2), @Resultado VARCHAR(500);
                    EXEC SP_GenerarComprobanteConsultorio_v3 
                        @CitaId = ?,
                        @LocalPago = ?,
                        @TipoComprobante = ?,
                        @FormaPago = ?,
                        @Referencia = ?,
                        @NumFac = @NumFac OUTPUT,
                        @NumSer = @NumSer OUTPUT,
                        @Total = @Total OUTPUT,
                        @Resultado = @Resultado OUTPUT;
                    SELECT @NumFac as NumFac, @NumSer as NumSer, @Total as Total, @Resultado as Resultado;";
            
            log_message('info', 'CitaPagoModel: SQL: ' . $sql);
            log_message('info', 'CitaPagoModel: Parámetros: ' . json_encode([(int)$citaId, $localPago, $tipoComprobante, $formaPago, $referencia]));
            
            $query = $this->db->query($sql, [
                (int)$citaId,
                $localPago,
                $tipoComprobante,
                $formaPago,
                $referencia
            ]);
            
            $result = $query->getRow();
            
            if (!$result) {
                throw new \Exception('No se pudo obtener resultado del stored procedure');
            }
            
            log_message('info', 'CitaPagoModel: Resultado SP completo: ' . json_encode($result));
            
            if ($result->Resultado === 'SUCCESS') {
                return [
                    'success' => true,
                    'numfac' => (int)$result->NumFac,
                    'serie' => (int)$result->NumSer,
                    'tipo' => $tipoComprobante,
                    'total' => (float)$result->Total
                ];
            } else {
                throw new \Exception($result->Resultado);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'CitaPagoModel: Error en SP - ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function getCitaInfo($citaId)
    {
        $sql = "SELECT CIT_CODCIT, CIT_CODCLIE, CIT_CODCAMP 
                FROM CITAS 
                WHERE CIT_CODCIT = ?";

        try {
            log_message('error', 'DEBUG: getCitaInfo - Ejecutando consulta para cita: ' . $citaId);
            $query = $this->db->query($sql, [$citaId]);
            $result = $query->getRow();
            log_message('error', 'DEBUG: getCitaInfo - Resultado: ' . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'DEBUG: getCitaInfo - Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getServiciosCita($citaId)
    {
        $sql = "SELECT 
                    cs.CNS_CODART,
                    cs.CNS_CANTIDAD,
                    cs.CNS_PRECIO,
                    RTRIM(LTRIM(art.ART_NOMBRE)) as ART_NOMBRE
                FROM CITAS_SERVICIOS cs
                INNER JOIN ARTI art ON cs.CNS_CODART = art.ART_KEY
                WHERE cs.CNS_CODCIT = ?";

        try {
            log_message('error', 'DEBUG: getServiciosCita - Ejecutando consulta para cita: ' . $citaId);
            $query = $this->db->query($sql, [$citaId]);
            $result = $query->getResult();
            log_message('error', 'DEBUG: getServiciosCita - Encontrados ' . count($result) . ' servicios');

            // Verificar si los artículos existen en ARTI para evitar errores en el trigger
            foreach ($result as $servicio) {
                $sqlVerify = "SELECT ART_FLAG_STOCK FROM ARTI WHERE ART_CODCIA = '25' AND ART_KEY = ?";
                $queryVerify = $this->db->query($sqlVerify, [$servicio->CNS_CODART]);
                $artInfo = $queryVerify->getRow();
                log_message('error', 'DEBUG: Artículo ' . $servicio->CNS_CODART . ' - FLAG_STOCK: ' . ($artInfo ? $artInfo->ART_FLAG_STOCK : 'NO EXISTE'));
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'DEBUG: getServiciosCita - Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPagosByCita($citaId)
    {
        $sql = "SELECT 
                    CTP_CODCTP,
                    CIT_CODCIT,
                    CTP_FECHA,
                    CTP_MONTO,
                    CTP_FORMA_PAGO,
                    CTP_REFERENCIA,
                    CTP_LOCAL_PAGO,
                    CTP_ESTADO,
                    CTP_GENERO_COMPROBANTE,
                    CTP_TIPO_COMPROBANTE,
                    CTP_SERIE,
                    CTP_NUMERO,
                    CTP_CODCIA,
                    CASE 
                        WHEN CTP_ESTADO = 1 THEN 'PAGADO'
                        WHEN CTP_ESTADO = 2 THEN 'ANULADO'
                        ELSE 'PENDIENTE'
                    END AS ESTADO_DESC
                FROM CITAS_PAGOS
                WHERE CIT_CODCIT = ?
                ORDER BY CTP_FECHA DESC";

        $query = $this->db->query($sql, [$citaId]);
        return $query->getResult();
    }

    public function getTotalPagado($citaId)
    {
        $sql = "SELECT SUM(CTP_MONTO) as TOTAL_PAGADO
                FROM CITAS_PAGOS 
                WHERE CIT_CODCIT = ? 
                  AND CTP_ESTADO = 1";

        $query = $this->db->query($sql, [$citaId]);
        $result = $query->getRow();
        return $result ? $result->TOTAL_PAGADO : 0;
    }

    public function procesarPagoCompleto($citaId, $localPago, $tipoComprobante = 'B', $formaPago = 'EFECTIVO', $referencia = '')
    {
        log_message('error', 'DEBUG: CitaPagoModel: ===== INICIANDO procesarPagoCompleto con SP v3 =====');
        log_message('error', 'DEBUG: CitaPagoModel: Parámetros - Cita: ' . $citaId . ', Local: ' . $localPago . ', Tipo: ' . $tipoComprobante . ', FormaPago: ' . $formaPago);

        try {
            // El stored procedure v3 maneja todo el proceso incluyendo CITAS_PAGOS y marca servicios como pagados
            $resultado = $this->generarComprobanteDirecto($citaId, $localPago, $tipoComprobante, $formaPago, $referencia);
            log_message('error', 'DEBUG: CitaPagoModel: Resultado SP v3: ' . json_encode($resultado));
            return $resultado;
        } catch (\Exception $e) {
            log_message('error', 'CitaPagoModel: Error en SP v3: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function registrarPago($data)
    {
        if (empty($data['CTP_FECHA'])) {
            $data['CTP_FECHA'] = date('Y-m-d H:i:s');
        }
        return $this->db->table($this->table)->insert($data);
    }

    public function generarComprobante($citaId, $localPago, $tipoComprobante = 'B')
    {
        return $this->generarComprobanteDirecto($citaId, $localPago, $tipoComprobante);
    }

    public function actualizarPago($pagoId, $data)
    {
        return $this->db->table($this->table)->where('CTP_CODCTP', $pagoId)->update($data);
    }

    public function anularPago($pagoId)
    {
        return $this->db->table($this->table)->where('CTP_CODCTP', $pagoId)->update(['CTP_ESTADO' => 2]);
    }

    public function getReportePagos($fechaInicio, $fechaFin, $local = null)
    {
        $sql = "SELECT cp.CTP_FECHA, cp.CTP_MONTO, cp.CTP_FORMA_PAGO, cp.CTP_LOCAL_PAGO, cp.CTP_SERIE, cp.CTP_NUMERO, cp.CTP_TIPO_COMPROBANTE, c.CIT_CODCLIE, cli.CLI_NOMBRE FROM CITAS_PAGOS cp INNER JOIN CITAS c ON cp.CIT_CODCIT = c.CIT_CODCIT LEFT JOIN CLIENTES cli ON c.CIT_CODCLIE = cli.CLI_CODCLIE WHERE cp.CTP_FECHA BETWEEN ? AND ? AND cp.CTP_ESTADO = 1";
        $params = [$fechaInicio, $fechaFin];
        if ($local) {
            $sql .= " AND cp.CTP_LOCAL_PAGO = ?";
            $params[] = $local;
        }
        $sql .= " ORDER BY cp.CTP_FECHA DESC";
        $query = $this->db->query($sql, $params);
        return $query->getResult();
    }
}
