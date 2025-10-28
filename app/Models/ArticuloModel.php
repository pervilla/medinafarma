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
        // Validar entrada: si está vacía, devolver un resultado vacío
        if (empty(trim($busqueda))) {
            return []; // Devuelve un array vacío
        }

        // Convertir la búsqueda a mayúsculas y reemplazar caracteres especiales
        $busqueda = strtoupper($busqueda);
        $busqueda = str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
            ['A', 'E', 'I', 'O', 'U', 'Ñ'],
            $busqueda
        );

        // Limpiar caracteres no alfanuméricos pero conservar "Ñ" y "ñ"
        $busqueda = preg_replace('/[^A-Za-z0-9 ñÑ]/', '', $busqueda);

        // Dividir la búsqueda en palabras y construir condiciones
        $palabras = explode(' ', $busqueda);
        $condiciones = [];
        $params = [];
        foreach ($palabras as $palabra) {
            $palabra = trim($palabra); // Eliminar espacios en cada palabra
            if ($palabra !== '') {    // Ignorar palabras vacías
                $condiciones[] = "T2.ART_NOMBRE LIKE ?";
                $params[] = "%$palabra%"; // Parámetro para la condición
            }
        }

        // Si no hay palabras válidas después del procesamiento, devolver vacío
        if (empty($condiciones)) {
            return [];
        }

        // Unir condiciones con "AND"
        $condiciones_str = implode(' AND ', $condiciones);

        // Construcción de la consulta SQL
        $sql = "SELECT TOP 100 
                RTRIM(LTRIM(T4.TAB_NOMLARGO)) AS TAB_NOMLARGO,
                T1.ARM_CODART,
                T2.ART_SUBGRU,
                RTRIM(LTRIM(TABLAS2.TAB_CONTABLE2)) AS CNTLD,
                RTRIM(LTRIM(T2.ART_NOMBRE)) AS ART_NOMBRE,
                AVG(T1.ARM_STOCK) AS StockGen,
                MAX(PRE_EQUIV) AS CANT, 
                MAX(PRE_PRE1) AS PRE_CAJA,  
                MIN(PRE_PRE1) AS PRE_UND,
                MAX(PRE_PRE2) AS PRE_CAJA_2,  
                MIN(PRE_PRE2) AS PRE_UND_2,
                (AVG(T1.ARM_STOCK) % MAX(PRE_EQUIV)) AS ART_UNID,
                FLOOR(AVG(T1.ARM_STOCK) / MAX(PRE_EQUIV)) AS ART_PQT,
                CASE 
                    WHEN MAX(PRE_EQUIV) = 1 
                        THEN CAST(FLOOR(AVG(T1.ARM_STOCK)) AS VARCHAR)
                    ELSE CAST(FLOOR(AVG(T1.ARM_STOCK) / MAX(PRE_EQUIV)) AS VARCHAR) 
                         + '/' + CAST(FLOOR(AVG(T1.ARM_STOCK) % MAX(PRE_EQUIV)) AS VARCHAR) 
                END AS STOCK,
                STUFF((SELECT ',' + RTRIM(LTRIM(b.PRE_UNIDAD)) 
                       FROM PRECIOS b
                       WHERE b.PRE_CODART = T1.ARM_CODART AND b.PRE_CODCIA = '25'
                       FOR XML PATH('')), 1, 1, '') AS UNIDADES
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
            ORDER BY T4.TAB_NOMLARGO, T2.ART_NOMBRE";

        // Depuración de SQL y parámetros
        log_message('debug', "SQL Query: $sql");
        log_message('debug', 'Parameters: ' . json_encode($params));

        // Ejecutar la consulta con parámetros enlazados
        $query = $this->db->query($sql, $params);

        // Verificar si la consulta falló
        if (!$query) {
            log_message('error', 'Database query failed: ' . $this->db->error());
            return [];
        }

        // Retornar los resultados
        return $query->getResult();
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
}
