<?php

namespace App\Models;

use CodeIgniter\Model;

class SunatModel extends Model {

    protected $db_default;
    protected $db_juanjuicillo;
    protected $db_marymed;
    protected $lastSql = '';

    public function __construct() {
        parent::__construct();
        $this->db_default = \Config\Database::connect('default'); // 192.168.101.200 (Serie 11)
        $this->db_juanjuicillo = \Config\Database::connect('juanjuicillo'); // 192.168.101.201 (Serie 10)
        $this->db_marymed = \Config\Database::connect('marymed'); // 192.168.101.200 BD:MARYMED
    }

    private function toSqlServerDate($dateString) {
        $datetime = \DateTime::createFromFormat('Y-m-d', $dateString);
        if ($datetime !== false) {
            return $datetime->format('d/m/Y');
        }
        return $dateString;
    }

    // ==========================================
    // INVERSIONES SAN MARTIN (BDATOS)
    // ==========================================

    public function getComprobantesSanMartin($fecha, $serie) {
        $fechaSql = $this->toSqlServerDate($fecha);
        $db = ($serie == '10') ? $this->db_juanjuicillo : $this->db_default;

        $sql = "SELECT 
                    CONVERT(VARCHAR(10), ALL_FECHA_DIA, 126) as fecha_emision,
                    convert(VARCHAR(8), all_hora, 108) as hora,
                    case when ALL_FBG = 'B' and ALL_CODCLIE = 1 then 0
                         when ALL_FBG = 'B' and ALL_CODCLIE > 1 then 1
                         when ALL_FBG = 'F' then 6
                         Else 0 end as tipo_doc_usu,
                    case when ALL_FBG = 'B' and ALL_CODCLIE = 1 then '00000000'
                         WHEN ALL_FBG = 'B' AND ALL_CODCLIE > 1 THEN 
                            CASE 
                                WHEN RTRIM(ISNULL(T3.CLI_RUC_ESPOSA, '')) = '' THEN RTRIM(ALL_RUC)
                                ELSE RTRIM(T3.CLI_RUC_ESPOSA)
                            END
                         when ALL_FBG = 'F' then RTRIM(ALL_RUC)
                         Else '00000000' end as cliente_doc,
                    CASE WHEN LEN(RTRIM(T3.CLI_NOMBRE)) > 0 
                         THEN UPPER(RTRIM(T3.CLI_NOMBRE))
                         ELSE 'SIN DATOS' END AS cliente_nombre,
                    case when ALL_FBG = 'B' then '03'
                         when ALL_FBG = 'F' then '01'
                         Else '00' end as tipo_doc,
                    ALL_FBG as tipo_fbg,
                    RTRIM(ALL_NUMSER) AS serie,
                    RTRIM(ALL_NUMFAC) AS numero,
                    CAST(ALL_NETO AS DECIMAL(16,2)) AS total
                FROM ALLOG AS T1
                INNER JOIN VEMAEST AS T2 ON (T1.ALL_CODVEN = T2.VEM_CODVEN AND T1.ALL_CODCIA = T2.VEM_CODCIA)
                INNER JOIN CLIENTES AS T3 ON (T1.ALL_CODCLIE = T3.CLI_CODCLIE AND T1.ALL_CODCIA = T3.CLI_CODCIA AND T3.CLI_CP = 'C')
                WHERE 
                    ALL_FECHA_DIA = CONVERT(smalldatetime, '$fechaSql', 103)
                    AND ALL_NUMSER = '$serie'
                    AND ALL_TIPMOV = 10 
                    AND ALL_SIGNO_CAJA = 1
                    AND all_flag_ext <> 'E' 
                    AND (ALL_CODTRA <> 1111 OR ALL_CODTRA <> 1122)
                ORDER BY serie, numero";

        $this->lastSql = $sql;

        log_message('debug', 'SUNAT SQL: ' . $sql);
        $query = $db->query($sql);
        if (!$query) {
            log_message('error', 'SUNAT SQL ERROR: ' . json_encode($db->error()));
            return [];
        }
        $result = $query->getResult();
        log_message('debug', 'SUNAT RESULT COUNT: ' . count($result));
        return $result;
    }

    public function getDetallesSanMartin($serie, $numero) {
        $db = ($serie == '10') ? $this->db_juanjuicillo : $this->db_default;

        $sql = "SELECT 
                    ROUND((FAR_CANTIDAD / FAR_EQUIV), 4) AS cantidad, 
                    FAR_CODART AS cod_producto, 
                    RTRIM(ART_NOMBRE) AS nombre_producto,
                    CAST(FAR_PRECIO AS DECIMAL(16,2)) AS precio_unitario, 
                    CAST(FAR_SUBTOTAL AS DECIMAL(16,2)) AS total_item
                FROM FACART AS T1
                INNER JOIN ARTI AS T2 ON (T1.FAR_CODART = T2.ART_KEY)
                WHERE 
                    FAR_TIPMOV = '10' AND 
                    FAR_CODCIA = '25' AND 
                    FAR_FBG = 'F' AND 
                    FAR_NUMSER = '$serie' AND
                    FAR_NUMFAC = '$numero'";
        
        $query = $db->query($sql);
        return $query ? $query->getResult() : [];
    }

    public function getFacturasSanMartin($fecha, $serie) {
        $fechaSql = $this->toSqlServerDate($fecha);
        $db = ($serie == '10') ? $this->db_juanjuicillo : $this->db_default;

        $sql = "SELECT 
                    CONVERT(VARCHAR(10), ALL_FECHA_DIA, 126) as fecha_emision,
                    convert(VARCHAR(8), all_hora, 108) as hora,
                    '01' as tipo_doc,
                    'F' as tipo_fbg,
                    RTRIM(ALL_NUMSER) AS serie,
                    RTRIM(ALL_NUMFAC) AS numero,
                    CAST(ALL_NETO AS DECIMAL(16,2)) AS total,
                    RTRIM(ALL_RUC) AS cliente_ruc,
                    CASE WHEN LEN(RTRIM(T3.CLI_NOMBRE)) > 0 
                         THEN UPPER(RTRIM(T3.CLI_NOMBRE))
                         ELSE 'SIN DATOS' END AS cliente_nombre,
                    RTRIM(T3.CLI_CASA_DIREC) AS cliente_direccion
                FROM ALLOG AS T1
                INNER JOIN VEMAEST AS T2 ON (T1.ALL_CODVEN = T2.VEM_CODVEN AND T1.ALL_CODCIA = T2.VEM_CODCIA)
                INNER JOIN CLIENTES AS T3 ON (T1.ALL_CODCLIE = T3.CLI_CODCLIE AND T1.ALL_CODCIA = T3.CLI_CODCIA AND T3.CLI_CP = 'C')
                WHERE 
                    ALL_FECHA_DIA = CONVERT(smalldatetime, '$fechaSql', 103)
                    AND ALL_NUMSER = '$serie'
                    AND ALL_FBG = 'F'
                    AND ALL_TIPMOV = 10 
                    AND ALL_SIGNO_CAJA = 1
                    AND all_flag_ext <> 'E' 
                    AND (ALL_CODTRA <> 1111 OR ALL_CODTRA <> 1122)
                ORDER BY serie, numero";

        $this->lastSql = $sql;
        $query = $db->query($sql);
        return $query ? $query->getResult() : [];
    }

    public function getLastSql() {
        return $this->lastSql;
    }

    // ==========================================
    // MARIA DORISEVIA MEDINA ROJAS (MARYMED)
    // ==========================================

    public function getBoletasMarymed($fecha) {
        $fechaSql = $this->toSqlServerDate($fecha);
        $db = $this->db_marymed;

        $sql = "SELECT 
                    CONVERT(VARCHAR(10), f.FECHA, 126) as fecha_emision,
                    '' as hora,
                    f.SERIE as serie,
                    f.NUMERO as numero,
                    '03' as tipo_doc,
                    'B' as tipo_fbg,
                    CAST(f.TOTAL AS DECIMAL(16,2)) as total,
                    CAST(f.NETO AS DECIMAL(16,2)) as neto,
                    CAST(f.IMPUESTO AS DECIMAL(16,2)) as impuesto,
                    f.MONEDA as moneda,
                    '' as cliente_doc,
                    'CLIENTE VARIOS' as cliente_nombre
                FROM factura f
                WHERE f.TIPO = '1'
                  AND CONVERT(date, f.FECHA) = CONVERT(date, '$fechaSql', 103)
                ORDER BY f.FECHA, f.SERIE, f.NUMERO";
        
        try {
            $query = $db->query($sql);
            return $query ? $query->getResult() : [];
        } catch (\Throwable $e) {
            log_message('error', 'Error en getBoletasMarymed: ' . $e->getMessage());
            return [];
        }
    }

    public function getDetallesMarymed($serie, $numero, $fecha) {
        $db = $this->db_marymed;
        $fechaSql = $this->toSqlServerDate($fecha);

        $sql = "SELECT 
                    d.PRODUCTO as cod_producto,
                    d.DESCRIPCIO as nombre_producto,
                    d.CANTIDAD as cantidad,
                    CAST(d.PRECIO AS DECIMAL(16,2)) as precio_unitario,
                    CAST(d.TOTAL AS DECIMAL(16,2)) as total_item
                FROM detalle d
                WHERE d.TIPO = '1'
                  AND d.SERIE = '$serie'
                  AND d.NUMERO = '$numero'
                  AND CONVERT(date, d.FECHA) = CONVERT(date, '$fechaSql', 103)
                ORDER BY d.LINEA";
        
        try {
            $query = $db->query($sql);
            return $query ? $query->getResult() : [];
        } catch (\Throwable $e) {
            log_message('error', 'Error en getDetallesMarymed: ' . $e->getMessage());
            return [];
        }
    }
}
