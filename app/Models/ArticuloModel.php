<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Description of ArticuloModel
 *
 * @author José Luis
 */
class ArticuloModel extends Model
{

    var $table = 'articulo';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->dbpm = \Config\Database::connect('pmeza');
        $this->dbjj = \Config\Database::connect('juanjuicillo');
    }
    public function get_articulos($key)
    {
        $sql = 'SELECT ARM_CODART, ARM_CODCIA, 0 as ARM_STOCK, 0 as ARM_INGRESOS, ';
        $sql .= '0 as ARM_SALIDAS, ARM_STOCK_INI, ARM_COSPRO, ARM_STOCK2, ARM_COSTO_ULT,  ';
        $sql .= 'ARM_FECHA_ULT, 0 as ARM_SALDO_S, ARM_SALDO_S2, 0 as ARM_SALDO_N, ';
        $sql .= 'ARM_SALDO_N2, ARM_STOCK_T, ARM_AJUSTA  ';
        $sql .= "FROM dbo.ARTICULO ";
        $sql .= "WHERE ARM_CODART > " . $key;
        $query =  $this->db->query($sql);
        return $query->getResult();
    }

    public function set_Stock($key, $cant)
    {
        $sql = 'UPDATE DBO.ARTICULO SET ';
        $sql .= ' ARM_STOCK = ARM_STOCK + ' . $cant;
        $sql .= ',ARM_INGRESOS = ARM_INGRESOS + ' . $cant;
        $sql .= ",ARM_SALDO_N = ARM_SALDO_N + " . $cant;
        $sql .= " WHERE ARM_CODART = " . $key;
        return $this->db->simpleQuery($sql);
    }
    public function get_Stock($id)
    {
        $result = $this->db->table('ARTICULO')->select('ARM_STOCK')->where('ARM_CODART', $id);
        return $result->get()->getRow();
    }
    public function get_articulos_det($busqueda)
    {
        $condiciones = [];
        $params = [];

        // Limpiar y preparar términos de búsqueda
        $busqueda = trim($busqueda);
        if (empty($busqueda)) return [];

        // Dividir por espacios para búsqueda multi-palabra
        $terminos = explode(' ', $busqueda);
        $search_parts = [];
        foreach ($terminos as $t) {
            $t = trim($t);
            if (strlen($t) >= 3) {
                $search_parts[] = $t;
                $condiciones[] = "(T2.ART_NOMBRE LIKE ? OR EXISTS (
                    SELECT 1 FROM PRECIOS_DET_DIGEMID_MEDINA R 
                    INNER JOIN PRECIOS_DIGEMID D ON R.Cod_Prod = D.Cod_Prod 
                    WHERE R.PRE_CODART = T1.ARM_CODART AND R.ESTADO = 1 AND D.Nom_IFA LIKE ?
                ))";
                $params[] = "%$t%";
                $params[] = "%$t%";
            }
        }

        // Si no hay términos de 3 letras, intentar solo por nombre
        if (empty($condiciones) && !empty($busqueda)) {
             $condiciones[] = "T2.ART_NOMBRE LIKE ?";
             $params[] = "%" . trim($busqueda) . "%";
        }

        if (empty($condiciones)) return [];

        $condiciones_str = implode(' AND ', $condiciones);

        $sql = "SELECT 
                RTRIM(LTRIM(T4.TAB_NOMLARGO)) AS TAB_NOMLARGO, 
                T1.ARM_CODART, 
                T2.ART_SUBGRU, 
                RTRIM(LTRIM(TABLAS2.TAB_CONTABLE2)) AS CNTLD, 
                RTRIM(LTRIM(T2.ART_NOMBRE)) AS ART_NOMBRE, 
                MAX(T3.PRE_EQUIV) AS EQUIVALENCIA,
                MAX(T3.PRE_EQUIV) AS CANT, 
                MAX(T3.PRE_PRE1) AS PRE_CAJA,  
                MIN(T3.PRE_PRE1) AS PRE_UND,
                MAX(T3.PRE_PRE2) AS PRE_CAJA_2,  
                MIN(T3.PRE_PRE2) AS PRE_UND_2,
                AVG(T1.ARM_STOCK) AS StockGen,
                (AVG(T1.ARM_STOCK) % NULLIF(MAX(T3.PRE_EQUIV),0)) AS ART_UNID,
                FLOOR(AVG(T1.ARM_STOCK) / NULLIF(MAX(T3.PRE_EQUIV),0)) AS ART_PQT,
                CASE 
                    WHEN MAX(T3.PRE_EQUIV) = 1 
                        THEN CAST(FLOOR(AVG(T1.ARM_STOCK)) AS VARCHAR)
                    ELSE CAST(FLOOR(AVG(T1.ARM_STOCK) / NULLIF(MAX(T3.PRE_EQUIV),0)) AS VARCHAR) 
                         + '/' + CAST(FLOOR(AVG(T1.ARM_STOCK) % NULLIF(MAX(T3.PRE_EQUIV),0)) AS VARCHAR) 
                END AS STOCK,
                STUFF((SELECT ',' + RTRIM(LTRIM(b.PRE_UNIDAD)) 
                       FROM PRECIOS b
                       WHERE b.PRE_CODART = T1.ARM_CODART AND b.PRE_CODCIA = '25'
                       FOR XML PATH('')), 1, 1, '') AS UNIDADES,
                (SELECT TOP 1 D.Nom_IFA 
                 FROM PRECIOS_DET_DIGEMID_MEDINA R 
                 INNER JOIN PRECIOS_DIGEMID D ON R.Cod_Prod = D.Cod_Prod 
                 WHERE R.PRE_CODART = T1.ARM_CODART AND R.ESTADO = 1) AS Nom_IFA
            FROM dbo.ARTICULO AS T1
            INNER JOIN DBO.ARTI AS T2 
                ON T1.ARM_CODART = T2.ART_KEY AND T1.ARM_CODCIA = T2.ART_CODCIA
            INNER JOIN TABLAS AS T4 
                ON T2.ART_FAMILIA = T4.TAB_NUMTAB 
                   AND T4.TAB_CODCIA = 25 
                   AND T4.TAB_TIPREG = 122
            LEFT JOIN PRECIOS AS T3 
                ON T3.PRE_CODART = T2.ART_KEY AND T3.PRE_CODCIA = '25'
            LEFT JOIN dbo.TABLAS TABLAS2 
                ON T2.ART_CODCIA = TABLAS2.TAB_CODCIA 
                   AND TABLAS2.TAB_TIPREG = 129 
                   AND T2.ART_SUBGRU = TABLAS2.TAB_NUMTAB
            WHERE $condiciones_str
              AND T2.ART_SITUACION = 0
              AND T4.TAB_NUMTAB NOT IN (442)
            GROUP BY 
                T4.TAB_NOMLARGO, 
                T1.ARM_CODART, 
                T2.ART_SUBGRU, 
                TABLAS2.TAB_CONTABLE2, 
                T2.ART_NOMBRE
            ORDER BY 
                CASE WHEN T2.ART_NOMBRE LIKE ? THEN 0 ELSE 1 END,
                T2.ART_NOMBRE ASC";

        // Parámetro adicional para el ORDER BY (relevancia al inicio)
        $params[] = trim($busqueda) . "%";

        $query = $this->db->query($sql, $params);
        return $query ? $query->getResult() : [];
    }

    /**
     * Obtiene productos en facturas pendientes (en tránsito)
     */
    public function get_productos_transito($busqueda)
    {
        if (empty(trim($busqueda))) return [];

        $palabras = explode(' ', strtoupper(trim($busqueda)));
        $condiciones = [];
        $params = [];
        foreach ($palabras as $palabra) {
            if (trim($palabra) !== '') {
                $condiciones[] = "d.DES_PROD LIKE ?";
                $params[] = "%$palabra%";
            }
        }

        $condiciones_str = implode(' AND ', $condiciones);

        $sql = "SELECT 
                    RTRIM(d.DES_PROD) AS ART_NOMBRE, 
                    d.CANTIDAD, 
                    d.PRECIO, 
                    RTRIM(f.NRO_FACTURA) AS NRO_FACTURA, 
                    CONVERT(VARCHAR, f.FECHA, 103) AS FECHA_DOC,
                    RTRIM(c.CLI_NOMBRE) AS PROVEEDOR
                FROM IMPORT_FACT_DET d
                INNER JOIN IMPORT_FACT f ON d.IDFACT = f.ID
                LEFT JOIN clientes c ON f.RUC = c.CLI_RUC_ESPOSO AND c.cli_cp = 'P'
                WHERE f.ESTADO = 0 
                AND ($condiciones_str)
                ORDER BY f.FECHA ASC";

        $query = $this->db->query($sql, $params);
        return $query ? $query->getResult() : [];
    }

    public function get_stock_articulos($stock, $server, $order = "laboratorio", $unidad = "caja", $familias = [])
    {
        try {
            // Convertimos el arreglo de familias a una cadena separada por comas
            $familiasStr = !empty($familias) ? implode(',', $familias) : null;
    
            // Construcción dinámica de llamada al SP
            $params = [
                ':stock'     => $stock,
                ':unidad'    => $unidad,
                ':order'     => $order,
                ':familias'  => $familiasStr
            ];

            $sql = "EXEC sp_get_stock_articulos 
                        @stock = ?, 
                        @unidad = ?, 
                        @order = ?, 
                        @familias = ?";

            if ($server == 2) {
                $query = $this->dbjj->query($sql, array_values($params));
            } elseif ($server == 3) {
                $query = $this->dbpm->query($sql, array_values($params));
            } else {
                $query = $this->db->query($sql, array_values($params));
            }
    
            return $query->getResult();
    
        } catch (\Throwable $th) {
            log_message('error', 'Error en get_stock_articulos: ' . $th->getMessage());
            return [];
        }
    }
    public function get_stock_articulos_bkp($stock, $server, $order = "laboratorio", $unidad = "caja")
    {
        $sql = "SELECT ARTI.ART_KEY, RTRIM(LTRIM(ARTI.ART_NOMBRE)) ART_NOMBRE, ARTI.ART_SITUACION, ";
        $sql .= "RTRIM(LTRIM(PRECIOS.PRE_UNIDAD)) PRE_UNIDAD, PRECIOS.PRE_EQUIV,PRECIOS.PRE_PRE1, PRECIOS.PRE_FLAG_UNIDAD, ";
        $sql .= "ARTICULO.ARM_STOCK, ";
        $sql .= "CASE WHEN PRECIOS.PRE_EQUIV = 1 THEN CAST(FLOOR(ARTICULO.ARM_STOCK) AS varchar)
                ELSE CAST((FLOOR(ARTICULO.ARM_STOCK/PRECIOS.PRE_EQUIV)) AS varchar)
                + '/' + CAST(FLOOR(ARTICULO.ARM_STOCK%PRECIOS.PRE_EQUIV) AS VARCHAR) end as STOCK,";
        $sql .= "TABLAS.TAB_TIPREG, RTRIM(LTRIM(TABLAS.TAB_NOMLARGO)) TAB_NOMLARGO, RTRIM(LTRIM(TABLAS2.TAB_CONTABLE2)) AS CNTLD ";
        $sql .= "FROM dbo.ARTI ARTI  ";
        $sql .= "INNER JOIN dbo.ARTICULO ARTICULO ON (ARTI.ART_KEY = ARTICULO.ARM_CODART AND ARTI.ART_CODCIA = ARTICULO.ARM_CODCIA) ";
        $sql .= "INNER JOIN dbo.TABLAS TABLAS ON (ARTI.ART_CODCIA = TABLAS.TAB_CODCIA AND ARTI.ART_FAMILIA = TABLAS.TAB_NUMTAB) ";
        $sql .= "INNER JOIN dbo.PRECIOS PRECIOS ON (ARTI.ART_CODCIA = PRECIOS.PRE_CODCIA AND ARTI.ART_KEY = PRECIOS.PRE_CODART) ";
        $sql .= 'LEFT JOIN dbo.TABLAS TABLAS2 ON (ARTI.ART_CODCIA = TABLAS2.TAB_CODCIA AND TABLAS2.TAB_TIPREG = 129 AND ARTI.ART_SUBGRU = TABLAS2.TAB_NUMTAB) ';
        $sql .= "WHERE ";
        if ($unidad == "caja") {
            $sql .= "PRECIOS.PRE_FLAG_UNIDAD = 'A' ";
        } elseif ($unidad == "und") {
            $sql .= "PRECIOS.PRE_EQUIV = 1 ";
        }

        $sql .= "AND ARTI.ART_SITUACION <> '1' ";
        $sql .= "AND TABLAS.TAB_TIPREG = 122 ";
        $sql .= "AND TABLAS.TAB_NUMTAB NOT IN (594,442) ";
        $sql .= $stock == 1 ? "AND ARTICULO.ARM_STOCK <> 0 " : '';
        if ($order == "laboratorio") {
            $sql .= "ORDER BY TABLAS.TAB_NOMLARGO ASC,ARTI.ART_NOMBRE ASC ";
        } elseif ($order == "articulo") {
            $sql .= "ORDER BY ARTI.ART_NOMBRE ASC";
        }

        // echo $sql; die();
        if ($server == 2) {
            $query =  $this->dbjj->query($sql);
        } elseif ($server == 3) {
            $query =  $this->dbpm->query($sql);
        } else {
            $query =  $this->db->query($sql);
        }
        return $query->getResult();
    }
    public function get_mov_controlados($server, $fecha01, $fecha02, $factu)
    {
        $sql = <<<EOD
        SELECT FAR_TIPMOV,
        CASE  WHEN FAR_TIPMOV = 5 THEN 'G.SALID' WHEN FAR_TIPMOV = 6 THEN 'G.INGRE' WHEN FAR_TIPMOV = 10 THEN 'VENTA' WHEN FAR_TIPMOV = 20 THEN 'COMPRA' END as TIPO,
        RTRIM(LTRIM(FACART."FAR_NUMSER")) FAR_NUMSER, FACART."FAR_NUMFAC",      
        CASE when FACART."FAR_NUMSER_C"=0 THEN '' ELSE cast(FACART."FAR_NUMSER_C" as varchar) + ' - ' + cast(FACART."FAR_NUMFAC_C" as varchar) END AS FACTURA_PROV,
        ISNULL(RTRIM(LTRIM(CLIENTES."CLI_NOMBRE")),'') AS CLIENTE,
        CONVERT (VARCHAR(10),FACART."FAR_FECHA", 23) FAR_FECHA,
        ARTI."ART_KEY",
        RTRIM(LTRIM(ARTI."ART_NOMBRE")) ART_NOMBRE,
        CASE WHEN FACART."FAR_EQUIV" = 1 THEN CAST(FLOOR(FACART."FAR_STOCK") AS varchar)
        ELSE CAST((FLOOR(FACART.FAR_STOCK/FACART."FAR_EQUIV")) AS varchar)
         + '/' + CAST(FLOOR(FACART.FAR_STOCK%FACART."FAR_EQUIV") AS VARCHAR) end as STOCK,
        FACART."FAR_CANTIDAD"/FACART."FAR_EQUIV" as CANTIDAD,
        RTRIM(LTRIM(FACART."FAR_DESCRI")) FAR_DESCRI,
        FACART."FAR_PRECIO", 
        RTRIM(LTRIM(TABLAS."TAB_NOMLARGO")) AS LABORATORIO,
        RTRIM(LTRIM(TABLAS2."TAB_NOMLARGO")) AS DCI
        FROM "BDATOS"."dbo"."FACART" FACART 
        INNER JOIN "BDATOS"."dbo"."ARTI" ARTI ON FACART."FAR_CODART" = ARTI."ART_KEY" AND FACART."FAR_CODCIA" = ARTI."ART_CODCIA"
        INNER JOIN "BDATOS"."dbo"."TABLAS" TABLAS ON ARTI."ART_CODCIA" = TABLAS."TAB_CODCIA" AND TABLAS."TAB_TIPREG" = 122 AND ARTI.ART_FAMILIA = TABLAS."TAB_NUMTAB" 
        LEFT JOIN "BDATOS"."dbo"."TABLAS" TABLAS2 ON ARTI."ART_CODCIA" = TABLAS2."TAB_CODCIA" AND TABLAS2."TAB_TIPREG" = 129 AND ARTI.ART_SUBGRU = TABLAS2."TAB_NUMTAB" 
        LEFT OUTER JOIN "BDATOS"."dbo"."CLIENTES" CLIENTES ON FACART."FAR_CODCLIE" = CLIENTES."CLI_CODCLIE" AND FACART."FAR_CODCIA" = CLIENTES."CLI_CODCIA" AND FACART."FAR_CP" = CLIENTES."CLI_CP"
        WHERE
        FACART.FAR_FECHA BETWEEN '$fecha01' AND '$fecha02'
        AND FACART.FAR_ESTADO<>'E'
        AND ARTI.ART_SUBGRU IN (SELECT TAB_NUMTAB FROM TABLAS WHERE "TAB_CONTABLE2" LIKE 'C%')         
        ORDER BY
        FAR_TIPMOV ASC,
        FACART."FAR_NUMSER" ASC,
        FACART."FAR_NUMFAC" ASC,
        FACART."FAR_NUMSEC" ASC
        EOD;

        //echo $sql; die();
        if ($server == 2) {
            $query =  $this->dbjj->query($sql);
        } elseif ($server == 3) {
            $query =  $this->dbpm->query($sql);
        } else {
            $query =  $this->db->query($sql);
        }

        return $query->getResult();
    }

    public function get_precios($server)
    {
        $sql = "SELECT PRE_CODCIA,PRE_CODART,PRE_SECUENCIA, ";
        $sql .= "PRE_PRE1,PRE_PRE2,PRE_POR1,PRE_POR2,ARM_COSPRO ";
        $sql .= "FROM ARTICULO ";
        $sql .= "INNER JOIN PRECIOS ON(ARM_CODART=PRE_CODART AND ARM_CODCIA=PRE_CODCIA) ";
        $sql .= "WHERE ARM_CODCIA='25' ";
        $sql .= "AND ARM_FECHA_ULT > DATEADD(month, -1, GETDATE()) ";
        //$sql.= "AND PRE_CODART<>0 ";
        //$sql.= "AND ARM_STOCK>0 ";
        $sql .= "ORDER BY PRE_CODART,PRE_SECUENCIA ";
        //echo $sql;
        if ($server == 2) {
            $query =  $this->dbjj->query($sql);
        } elseif ($server == 3) {
            $query =  $this->dbpm->query($sql);
        } else {
            $query =  $this->db->query($sql);
        }
        return $query->getResult();
    }
    public function set_costo($key, $costo)
    {
        $sql = 'UPDATE DBO.ARTICULO SET ';
        $sql .= ' ARM_COSPRO  = ' . $costo;
        $sql .= " WHERE ARM_CODART = " . $key;
        //echo $sql;
        return $this->db->simpleQuery($sql);
    }
    public function ejecuta_actualizacion_productos()
    {
        $sp = "EXEC [dbo].[sp_actualiza_productos]"; // Simplifica la llamada

        try {
            $query = $this->db->query($sp);
            $result = $query->getRow();

            if ($result && isset($result->mensaje)) {
                return [
                    'success' => true,
                    'message' => $result->mensaje,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo obtener el mensaje del procedimiento.',
                ];
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error al ejecutar el procedimiento almacenado: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al ejecutar el procedimiento almacenado.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function get_articulos_por_ifa($ifa, $server)
    {
        if (empty($ifa)) return [];

        // 1. Obtener todos los ART_KEY que comparten este principio activo (Nom_IFA)
        $sqlKeys = "SELECT DISTINCT REL.PRE_CODART 
                    FROM PRECIOS_DET_DIGEMID_MEDINA REL
                    INNER JOIN PRECIOS_DIGEMID DIG ON DIG.Cod_Prod = REL.Cod_Prod
                    WHERE DIG.Nom_IFA = ? AND REL.ESTADO = 1";
        
        $keysQuery = $this->db->query($sqlKeys, [$ifa]);
        $results = $keysQuery->getResult();
        
        if (empty($results)) return [];
        
        $artKeys = [];
        foreach ($results as $row) {
            $artKeys[] = $row->PRE_CODART;
        }
        $keysList = implode(',', $artKeys);
        
        // 2. Consultar el stock de esos productos en el servidor destino
        $sql = "SELECT T1.ARM_CODART, art_familia, 
                RTRIM(LTRIM(T2.ART_NOMBRE)) ART_NOMBRE, AVG(T1.ARM_STOCK) AS StockGen, 
                CASE WHEN MAX(PRE_EQUIV) = 1 THEN CAST(FLOOR(AVG(T1.ARM_STOCK)) AS varchar)
                ELSE CAST((FLOOR(AVG(T1.ARM_STOCK)/MAX(PRE_EQUIV))) AS varchar)
                + '/' + CAST(FLOOR(AVG(T1.ARM_STOCK)%MAX(PRE_EQUIV)) AS VARCHAR) END AS STOCK 
                FROM dbo.ARTICULO AS T1 
                INNER JOIN DBO.ARTI AS T2 ON (T1.ARM_CODART = T2.ART_KEY AND T1.ARM_CODCIA = T2.ART_CODCIA) 
                LEFT JOIN PRECIOS AS T3 ON (T3.PRE_CODART = T1.ARM_CODART AND T3.PRE_CODCIA = '25') 
                WHERE T1.ARM_CODART IN ($keysList)
                GROUP BY T1.ARM_CODART, T2.ART_NOMBRE, art_familia 
                ORDER BY T1.ARM_CODART, T2.ART_NOMBRE";

        if ($server == 2) {
            $query = $this->dbjj->query($sql);
        } elseif ($server == 3) {
            $query = $this->dbpm->query($sql);
        } else {
            $query = $this->db->query($sql);
        }
        
        return $query ? $query->getResult() : [];
    }
}
