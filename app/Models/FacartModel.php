<?php

namespace App\Models;

use CodeIgniter\Model;
use PDO;

class FacartModel extends Model
{

    var $table = 'facart';
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

    public function get_all_facart()
    {
        $query = $this->db->query('SELECT * FROM facart');
        return $query->getResult();
    }

    public function get_by_id($id)
    {
        $sql = 'SELECT * FROM facart WHERE FAR_CODCIA =' . $id;
        $query = $this->db->query($sql);
        return $query->getRow();
    }

    public function get_max_numfac()
    {
        $sql = 'SELECT ';
        $sql .= '(SELECT MAX(FAR_NUMFAC) FROM DBO.FACART WHERE FAR_TIPMOV = 6 AND FAR_CODCIA = 25 AND FAR_NUMSER = 1) FAR_NUMFAC, ';
        $sql .= '(SELECT MAX(FAR_NUMOPER) FROM DBO.FACART WHERE FAR_FECHA = CONVERT (date, GETDATE())) FAR_NUMOPER ';
        $query = $this->db->query($sql);
        return $query->getRow();
    }

    public function get_comisiones($mes, $anio)
    {
        $sql = 'SELECT 
            T2.VEM_CODVEN, 
            T2.VEM_NOMBRE, 
            T2.VEM_META,
            COALESCE(T3.COMISION, 0) as COMISION
        FROM VEMAEST T2
        LEFT JOIN (
            SELECT 
                FAR_CODVEN,
                FAR_CODCIA,
                ROUND(SUM(FAR_CANTIDAD/FAR_EQUIV*FAR_PRECIO*0.8474576271186441), 2) as COMISION
            FROM FACART 
            WHERE MONTH(FAR_FECHA) = ' . $mes . ' 
                AND YEAR(FAR_FECHA) = ' . $anio . ' 
                AND FAR_ESTADO <> \'E\' 
                AND FAR_ESTADO2 <> \'L\'
                AND FAR_TIPMOV = 10
                AND FAR_CODART NOT IN (SELECT art_key FROM dbo.ARTI WHERE ART_GRUPOP = 566)
            GROUP BY FAR_CODVEN, FAR_CODCIA
        ) T3 ON (T2.VEM_CODVEN = T3.FAR_CODVEN AND T2.VEM_CODCIA = T3.FAR_CODCIA)
        WHERE T2.VEM_DESACTIVO <> \'A\' 
            AND T2.VEM_CODVEN <> 0 
            AND T2.VEM_CODCIA = 25
        ORDER BY T2.VEM_CODVEN, T2.VEM_NOMBRE, T2.VEM_META';

        //echo $sql;
        $query = $this->db->query($sql);
        return $query->getResult();
    }
    public function get_comisiones_empleado($mes, $anio)
    {
        $sql = 'SELECT T2.VEM_CODVEN, T2.VEM_NOMBRE,T2.VEM_META,';
        $sql .= 'ROUND(sum(T1.FAR_CANTIDAD/T1.FAR_EQUIV*T1.FAR_PRECIO*0.8474576271186441),2,0) as COMISION '; /* MENOS 18% IGV */
        $sql .= 'FROM FACART T1 ';
        $sql .= 'INNER JOIN VEMAEST T2 ON (T1.FAR_CODVEN = T2.VEM_CODVEN AND T1.FAR_CODCIA = T2.VEM_CODCIA) ';
        $sql .= 'WHERE MONTH(T1.FAR_FECHA) = ' . $mes . ' AND ';
        $sql .= 'YEAR(T1.FAR_FECHA) = ' . $anio . ' AND ';
        $sql .= "T1.FAR_ESTADO <> 'E' AND ";
        $sql .= "T1.FAR_ESTADO2 <> 'L' AND ";
        $sql .= 'T1.FAR_TIPMOV = 10 ';
        $sql .= 'GROUP BY T2.VEM_CODVEN,T2.VEM_NOMBRE,T2.VEM_META ';
        $sql .= 'ORDER BY T2.VEM_CODVEN,T2.VEM_NOMBRE,T2.VEM_META ';
        $query =  $this->db->query($sql);
        return $query->getResult();
    }
    public function get_rentabilidad_empleado($mes, $anio)
    {
        $sql = 'SELECT 
            T2.VEM_CODVEN, 
            T2.VEM_NOMBRE, 
            T2.VEM_META,
            COALESCE(T3.VENTA, 0) as VENTA,
            COALESCE(T3.NETO, 0) as NETO,
            COALESCE(T3.COSTO, 0) as COSTO
        FROM VEMAEST T2
        LEFT JOIN (
            SELECT 
                FAR_CODVEN,
                FAR_CODCIA,
                ROUND(SUM(FAR_CANTIDAD/FAR_EQUIV*FAR_PRECIO*0.8474576271186441), 2) as VENTA,
                ROUND(SUM(FAR_CANTIDAD/FAR_EQUIV*FAR_PRECIO), 2) as NETO,
                ROUND(SUM(FAR_CANTIDAD/FAR_EQUIV*FAR_COSPRO), 2) as COSTO
            FROM FACART 
            WHERE MONTH(FAR_FECHA) = ' . $mes . ' 
                AND YEAR(FAR_FECHA) = ' . $anio . ' 
                AND FAR_ESTADO <> \'E\' 
                AND FAR_ESTADO2 <> \'L\'
                AND FAR_TIPMOV = 10
                AND FAR_CODART NOT IN (SELECT art_key FROM dbo.ARTI WHERE ART_GRUPOP = 566)
            GROUP BY FAR_CODVEN, FAR_CODCIA
        ) T3 ON (T2.VEM_CODVEN = T3.FAR_CODVEN AND T2.VEM_CODCIA = T3.FAR_CODCIA)
        WHERE T2.VEM_DESACTIVO <> \'A\' 
            AND T2.VEM_CODVEN <> 0 
            AND T2.VEM_CODCIA = 25
        ORDER BY T2.VEM_CODVEN, T2.VEM_NOMBRE, T2.VEM_META';

        //echo $sql;
        $query = $this->db->query($sql);
        return $query->getResult();
    }
    public function get_guia($fecha, $serie, $factura)
    {
        $sql = 'SELECT FAR_NUMSEC,FAR_FECHA,FAR_CODART,FAR_PRECIO,FAR_COSPRO,FAR_BRUTO, ';
        $sql .= 'FAR_EQUIV,FAR_FECHA_COMPRA,FAR_NUM_LOTE,FAR_CANTIDAD,FAR_DESCRI,FAR_PESO, ';
        $sql .= 'FAR_COSPRO_ANT,FAR_HORA,FAR_CANTIDAD_P,FAR_TURNO,FAR_SUBTOTAL,FAR_CODLOT ';
        $sql .= 'FROM FACART WHERE ';
        $sql .= 'FAR_FECHA = \'' . $fecha . '\' AND ';
        $sql .= 'FAR_NUMSER = ' . $serie . ' AND ';
        $sql .= 'FAR_NUMFAC = ' . $factura;
        $query = $this->db->query($sql);
        return $query->getResult();
    }
    public function get_comprobante($numser, $numfac, $tipmov, $fecha, $server)
    {
        $sql = 'SELECT ';
        $sql .= 'FACART.FAR_FBG,FACART."FAR_NUMSER", FACART."FAR_NUMFAC", FACART."FAR_NUMSEC", FACART."FAR_FECHA", FACART."FAR_DIAS", FACART."FAR_PRECIO", FACART."FAR_IMPTO", FACART."FAR_CODCLIE",  ';
        $sql .= "CASE when FACART.FAR_ESTADO='E' THEN '*** ANULADO ***' ELSE '' END AS FAR_ESTADO,";
        $sql .= 'FACART."FAR_BRUTO", FACART."FAR_EQUIV", FACART."FAR_PORDESCTO1", FACART."FAR_PRECIO_NETO", FACART."FAR_DESCRI",  ';
        $sql .= 'FACART."FAR_MONEDA", FACART."FAR_HORA", FACART."FAR_CANTIDAD_P", FACART."FAR_PORDESCTOS", ';
        $sql .= 'FACART.FAR_CLIENTE,';
        $sql .= 'ARTI."ART_KEY", ARTI."ART_NOMBRE", ARTI."ART_EX_IGV", ARTI."ART_ALTERNO", ';
        $sql .= 'CLIENTES."CLI_NOMBRE", CLIENTES."CLI_CASA_DIREC", CLIENTES."CLI_CASA_NUM", CLIENTES."CLI_RUC_ESPOSO", ';
        $sql .= 'CLIENTES.CLI_RUC_ESPOSO,CLIENTES.CLI_RUC_ESPOSA,';
        $sql .= 'VEMAEST."VEM_NOMBRE", ';
        $sql .= 'TAB_COPIAS."COP_SEC", TAB_COPIAS."COP_DESCRIP", TAB_COPIAS."COP_CANT_NC", ';
        $sql .= 'TABLAS."TAB_TIPREG", TABLAS."TAB_NOMCORTO" ';
        $sql .= 'FROM "BDATOS"."dbo"."FACART" FACART  ';
        $sql .= 'INNER JOIN "BDATOS"."dbo"."CLIENTES" CLIENTES ON FACART."FAR_CODCLIE" = CLIENTES."CLI_CODCLIE" AND FACART."FAR_CODCIA" = CLIENTES."CLI_CODCIA" AND FACART."FAR_CP" = CLIENTES."CLI_CP" ';
        $sql .= 'INNER JOIN "BDATOS"."dbo"."VEMAEST" VEMAEST ON FACART."FAR_CODVEN" = VEMAEST."VEM_CODVEN" AND FACART."FAR_CODCIA" = VEMAEST."VEM_CODCIA" ';
        $sql .= 'INNER JOIN "BDATOS"."dbo"."TAB_COPIAS" TAB_COPIAS ON FACART."FAR_CODCIA" = TAB_COPIAS."COP_CIA" ';
        $sql .= 'INNER JOIN "BDATOS"."dbo"."ARTI" ARTI ON FACART."FAR_CODART" = ARTI."ART_KEY" AND FACART."FAR_CODCIA" = ARTI."ART_CODCIA" ';
        $sql .= 'INNER JOIN "BDATOS"."dbo"."TABLAS" TABLAS ON ARTI."ART_CODCIA" = TABLAS."TAB_CODCIA" AND ARTI."ART_FAMILIA" = TABLAS."TAB_NUMTAB" ';
        $sql .= 'WHERE ';
        $sql .= "FACART.FAR_FECHA ='$fecha' AND ";
        $sql .= "FACART.FAR_TIPMOV = $tipmov AND ";
        $sql .= "FACART.FAR_NUMSER = '$numser'  and ";
        $sql .= "FACART.FAR_NUMFAC = $numfac AND ";
        $sql .= 'TAB_COPIAS."COP_CANT_NC" = 1 AND ';
        $sql .= 'TABLAS."TAB_TIPREG" = 122  ';
        $sql .= 'ORDER BY ';
        $sql .= 'TAB_COPIAS."COP_SEC" ASC, ';
        $sql .= 'FACART."FAR_NUMSER" ASC, ';
        $sql .= 'FACART."FAR_NUMFAC" ASC, ';
        $sql .= 'TABLAS."TAB_NOMCORTO" ASC, ';
        $sql .= 'FACART."FAR_NUMSEC" ASC ';
        //echo $sql; die();
        if ($server == 2) {
            $query =  $this->dbjj->query($sql);
        } elseif ($server == 3) {
            $query =  $this->dbpm->query($sql);
        } elseif ($server == 1) {
            $query =  $this->db->query($sql);
        }

        return $query->getResult();
    }
    public function get_compras($CODCLIE, $CODART)
    {
        $sql = 'SELECT DISTINCT A.ART_KEY, A.ART_NOMBRE, L.FAR_EQUIV, ';
        $sql .= "CASE WHEN L.FAR_EQUIV = 1 THEN CAST(FLOOR(AR.ARM_STOCK) AS varchar)
                ELSE CAST((FLOOR(AR.ARM_STOCK/L.FAR_EQUIV)) AS varchar)
                + '/' + CAST(FLOOR(AR.ARM_STOCK%L.FAR_EQUIV) AS VARCHAR) end as STOCK, ";
        $sql .= 'AR.ARM_STOCK, T3.PRE_UNIDAD,T3.PRE_PRE1, ';
        $sql .= '(SELECT TOP 1 FAR_COSPRO*FAR_EQUIV from dbo.FACART WHERE FAR_TIPMOV=L.FAR_TIPMOV AND FAR_CODCIA=L.FAR_CODCIA AND FAR_CODCLIE = L.FAR_CODCLIE AND FAR_CODART=L.FAR_CODART and FAR_ESTADO=L.FAR_ESTADO order by FAR_FECHA desc) as COSTO,  ';
        $sql .= empty($CODART) ? "" : '(SELECT TOP 1 FAR_FECHA from dbo.FACART WHERE FAR_TIPMOV=L.FAR_TIPMOV AND FAR_CODCIA=L.FAR_CODCIA AND FAR_CODCLIE = L.FAR_CODCLIE AND FAR_CODART=L.FAR_CODART and FAR_ESTADO=L.FAR_ESTADO order by FAR_FECHA desc) as FAR_FECHA,  ';
        $sql .= 'C.CLI_NOMBRE ';
        $sql .= 'FROM dbo.arti as A ';
        $sql .= 'INNER JOIN dbo.FACART as L on(A.ART_KEY=L.FAR_CODART AND A.ART_CODCIA=L.FAR_CODCIA) ';
        $sql .= "INNER JOIN dbo.CLIENTES AS C ON(C.CLI_CODCLIE = L.FAR_CODCLIE AND C.CLI_CP='P') ";
        $sql .= 'INNER JOIN dbo.PRECIOS AS T3 ON(L.FAR_CODART=T3.PRE_CODART AND L.FAR_EQUIV=T3.PRE_EQUIV) ';
        $sql .= 'INNER JOIN dbo.ARTICULO AS AR ON(AR.ARM_CODART=A.ART_KEY AND AR.ARM_CODCIA=A.ART_CODCIA) ';
        $sql .= 'WHERE FAR_TIPMOV=20 ';
        $sql .= empty($CODCLIE) ? "" : "AND L.FAR_CODCLIE = $CODCLIE ";
        $sql .= empty($CODART) ? "" : "AND FAR_CODART = $CODART ";
        $sql .= 'AND FAR_FECHA >= Dateadd(yy,-1,Getdate()); ';
        //echo $sql;
        $query =  $this->db->query($sql);
        return $query->getResult();
    }
    public function get_ing_sal($articulo)
    {
        $sql = <<<EOD
        select DATEPART(YEAR, FAR_FECHA) as ANIO, DATEPART(MONTH, FAR_FECHA) as MES, 
        SUM (CASE
            WHEN FAR_TIPMOV = 5 THEN FAR_CANTIDAD
            ELSE 0
        END) AS SALIDAS,
        SUM (CASE
            WHEN FAR_TIPMOV = 6 THEN FAR_CANTIDAD
            ELSE 0
        END) AS INGRESOS,
        SUM (CASE
            WHEN FAR_TIPMOV = 10 THEN FAR_CANTIDAD
            ELSE 0
        END) AS VENTAS,
        SUM (CASE
            WHEN FAR_TIPMOV = 20 THEN FAR_CANTIDAD
            ELSE 0
        END) AS COMPRAS
        FROM dbo.FACART 
        where 
        FAR_CODART=$articulo
        AND FAR_ESTADO <> 'E' 
        AND FAR_ESTADO2 <> 'L' 
        and FAR_FECHA >= Dateadd(yy,-1,Getdate())
        group by DATEPART(YEAR, FAR_FECHA), DATEPART(MONTH,FAR_FECHA)
        order by DATEPART(YEAR, FAR_FECHA), DATEPART(MONTH,FAR_FECHA)
        EOD;
        $query =  $this->db->query($sql);
        return $query->getResult();
    }
    public function actualizar_personas($CLIENTE1, $CLIENTE2)
    {
        $sql = 'UPDATE DBO.FACART SET FAR_CODCLIE = ' . $CLIENTE1;
        $sql .= ' WHERE FAR_CODCLIE = ' . $CLIENTE2;
        return $this->db->simpleQuery($sql);
    }

    public function migrar_guia($servidor_origen, $servidor_destino, $fecha_origen, $serie_origen, $factura_origen)
    {
        try {
            // Validación de servidores iguales
            if ($servidor_origen == $servidor_destino) {
                return [
                    'success' => false,
                    'message' => 'Error: Origen y destino no pueden ser iguales'
                ];
            }

            // Determinar qué conexión usar según el servidor destino
            $db = $this->db; // Por defecto principal (servidor 1)

            // Preparar la llamada al stored procedure
            $sql = "DECLARE @mensaje VARCHAR(500);
                EXEC [dbo].[sp_migrar_guia_optimizado] 
                    @servidor_origen = ?, 
                    @servidor_destino = ?, 
                    @fecha_origen = ?, 
                    @serie_origen = ?, 
                    @factura_origen = ?, 
                    @mensaje_salida = @mensaje OUTPUT;
                SELECT @mensaje AS mensaje_salida;";

            // Ejecutar la consulta
            $query = $db->query($sql, [
                $servidor_origen,
                $servidor_destino,
                $fecha_origen,
                $serie_origen,
                $factura_origen
            ]);

            // Obtener el resultado
            $result = $query->getRow();
            $mensaje_salida = $result->mensaje_salida ?? '';

            // Procesar el resultado
            if (strpos($mensaje_salida, '1_') === 0) {
                $nueva_factura = substr($mensaje_salida, 2);
                return [
                    'success' => true,
                    'nueva_factura' => $nueva_factura,
                    'message' => 'Migración exitosa'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $mensaje_salida
                ];
            }
        } catch (\Throwable $th) {
            log_message('error', 'Error en migrar_guia: ' . $th->getMessage());
            return [
                'success' => false,
                'message' => 'Error en el servidor: ' . $th->getMessage()
            ];
        }
    }
    public function eliminar_item_facart($fecha, $numero_factura, $codigo_articulo, $server)
    {
        try {
            // Preparar los parámetros para el stored procedure
            $params = [
                ':fecha'         => $fecha,
                ':numero_factura' => $numero_factura,
                ':codigo_articulo' => $codigo_articulo
            ];

            // Construir la llamada al stored procedure
            $sql = "EXEC sp_eliminar_item_facart 
                    @FAR_FECHA = ?, 
                    @FAR_NUMFAC = ?, 
                    @FAR_CODART = ?";

            // Ejecutar el stored procedure en la base de datos correspondiente
            if ($server == 2) {
                $query = $this->dbjj->query($sql, array_values($params));
            } elseif ($server == 3) {
                $query = $this->dbpm->query($sql, array_values($params));
            } else {
                $query = $this->db->query($sql, array_values($params));
            }

            // Verificar si la operación fue exitosa
            if ($query) {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            log_message('error', 'Error en eliminar_item_facart: ' . $th->getMessage());
            return false;
        }
    }
}
