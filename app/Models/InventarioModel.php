<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarioModel extends Model
{
    protected $table            = 'inventarios';
    protected $primaryKey       = 'inv_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];
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
    public function get_inventarios($server)
    {
        $sql = "SELECT * ";
        $sql .= "FROM INVENTARIOS ";
        $sql .= "WHERE 1=1 ";
        $sql .= "ORDER BY inv_id DESC ";
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
    public function get_producto($server,$inv, $vendedor,$fila) 
    {
        $sql = <<<EOD
        SELECT * FROM (
            SELECT ROW_NUMBER() OVER(ORDER BY T4.TAB_NOMLARGO,T2.ART_NOMBRE) AS Row, 
            T1.art_key, RTRIM(LTRIM(T2.ART_NOMBRE)) AS ART_NOMBRE,T6.PRE_EQUIV,
            RTRIM(LTRIM(T6.PRE_UNIDAD)) PRE_UNIDAD,T2.ART_FAMILIA,RTRIM(LTRIM(T4.TAB_NOMLARGO)) as FAMILIA,
            T2.ART_SUBGRU,RTRIM(LTRIM(T5.TAB_NOMLARGO)) AS PRIN_ACT,T3.ARM_STOCK,
            CASE WHEN T6.PRE_EQUIV = 1 THEN CAST(FLOOR(T3.ARM_STOCK) AS varchar)
                ELSE CAST((FLOOR(T3.ARM_STOCK/T6.PRE_EQUIV)) AS varchar)
                + '/' + CAST(FLOOR(T3.ARM_STOCK%T6.PRE_EQUIV) AS VARCHAR) end as STOCK
            FROM INVENTARIO_DETALLE as T1
            INNER JOIN ARTI as T2 ON(T1.art_key=T2.ART_KEY and T2.ART_CODCIA=25)
            INNER JOIN ARTICULO AS T3 ON(T1.art_key=T3.ARM_CODART AND T3.ARM_CODCIA=25)
            INNER JOIN TABLAS AS T4 ON (T4.TAB_CODCIA=25 AND T4.TAB_TIPREG = 122 AND T2.ART_FAMILIA = T4.TAB_NUMTAB)
            LEFT JOIN TABLAS AS T5 ON T2.ART_CODCIA = T5.TAB_CODCIA AND T5.TAB_TIPREG = 129 AND T2.ART_SUBGRU = T5.TAB_NUMTAB
            INNER JOIN PRECIOS AS T6 ON (T6.PRE_CODCIA=25 AND T1.art_key = T6.PRE_CODART)
            
            WHERE T6.PRE_FLAG_UNIDAD = 'A'
            AND T1.vem_codven = $vendedor
            AND T1.inv_id = $inv
            
        ) as result
        WHERE (Row=$fila)
        EOD;
        if ($server == 2) {
            $query =  $this->dbjj->query($sql);
        } elseif ($server == 3) {
            $query =  $this->dbpm->query($sql);
        } else {
            $query =  $this->db->query($sql);
        }
        return $query->getRow();
    }
    public function get_productos($server,$inv, $vendedor,$fila) 
    {
        $sql = <<<EOD
            SELECT ROW_NUMBER() OVER(ORDER BY T4.TAB_NOMLARGO,T2.ART_NOMBRE) AS Row, 
            T1.art_key, RTRIM(LTRIM(T2.ART_NOMBRE)) AS ART_NOMBRE,T6.PRE_EQUIV,
            RTRIM(LTRIM(T6.PRE_UNIDAD)) PRE_UNIDAD,T2.ART_FAMILIA,RTRIM(LTRIM(T4.TAB_NOMLARGO)) as FAMILIA,
            T2.ART_SUBGRU,RTRIM(LTRIM(T5.TAB_NOMLARGO)) AS PRIN_ACT,T3.ARM_STOCK,
            -- Stock en unidades de venta (cajas/paquetes)
            CASE WHEN T6.PRE_EQUIV = 1 THEN CAST(T3.ARM_STOCK AS varchar)
                ELSE CAST(FLOOR(T3.ARM_STOCK/T6.PRE_EQUIV) AS varchar)
                + '/' + CAST(T3.ARM_STOCK%T6.PRE_EQUIV AS VARCHAR) end as STOCK_DISPLAY,
            -- Stock físico en unidades de venta
            CASE WHEN T1.ind_stock_fisico IS NULL THEN NULL
                WHEN T6.PRE_EQUIV = 1 THEN T1.ind_stock_fisico
                ELSE FLOOR(T1.ind_stock_fisico/T6.PRE_EQUIV) end as ind_stock_fisico_display,
            -- Stock físico en unidades para input
            CASE WHEN T1.ind_stock_fisico IS NULL THEN ''
                ELSE CAST(T1.ind_stock_fisico AS varchar) end as ind_stock_fisico,
            -- Diferencia en formato CAJA/UNIDAD
            CASE WHEN T1.ind_diferencia IS NULL THEN '0'
                WHEN T6.PRE_EQUIV = 1 THEN CAST(T1.ind_diferencia AS varchar)
                ELSE CAST(FLOOR(T1.ind_diferencia/T6.PRE_EQUIV) AS varchar)
                + '/' + CAST(ABS(T1.ind_diferencia%T6.PRE_EQUIV) AS VARCHAR) end as ind_diferencia_display,
            -- Diferencia numérica para cálculos
            CASE WHEN T1.ind_diferencia IS NULL THEN 0
                ELSE T1.ind_diferencia end as ind_diferencia,
            T1.ind_estado, T1.ind_observaciones
            FROM INVENTARIO_DETALLE as T1
            INNER JOIN ARTI as T2 ON(T1.art_key=T2.ART_KEY and T2.ART_CODCIA=25)
            INNER JOIN ARTICULO AS T3 ON(T1.art_key=T3.ARM_CODART AND T3.ARM_CODCIA=25)
            INNER JOIN TABLAS AS T4 ON (T4.TAB_CODCIA=25 AND T4.TAB_TIPREG = 122 AND T2.ART_FAMILIA = T4.TAB_NUMTAB)
            LEFT JOIN TABLAS AS T5 ON T2.ART_CODCIA = T5.TAB_CODCIA AND T5.TAB_TIPREG = 129 AND T2.ART_SUBGRU = T5.TAB_NUMTAB
            INNER JOIN PRECIOS AS T6 ON (T6.PRE_CODCIA=25 AND T1.art_key = T6.PRE_CODART)            
            WHERE T6.PRE_FLAG_UNIDAD = 'A'
            AND T1.vem_codven = $vendedor
            AND T1.inv_id = $inv
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
    public function get_responsables($server, $inv)
    {
        $sin = $inv?"AND T1.inv_id = $inv ":" ";
        $sql = "SELECT 
                    T1.vem_codven,
                    VEM_NOMBRE,
                    T1.inr_proporcion,
                    T1.inr_id,
                    T1.inr_id,
                    T1.inv_id,
                    COUNT(T3.ind_id) inr_cantidad 
                FROM INVENTARIO_RESPONSABLES AS T1 
                    LEFT JOIN VEMAEST AS T2 ON T1.vem_codven = T2.vem_codven 
                    inner join INVENTARIO_DETALLE as T3 ON T3.inv_id = T1.inv_id
                WHERE 1=1 $sin 
                    group by T1.vem_codven,	VEM_NOMBRE,T1.inr_proporcion,T1.inr_id,T1.inr_id,T1.inv_id
                    ORDER BY T1.vem_codven ASC ";
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

    public function get_responsables_avn($server, $inv)
    {
        $sql = "SELECT T2.inv_id,T3.VEM_CODVEN,T3.VEM_NOMBRE,COUNT(T2.arm_stock) AS tregistros,COUNT(T2.ind_diferencia) as tavance ";
        $sql .= "FROM INVENTARIOS AS T1 ";
        $sql .= "INNER JOIN INVENTARIO_DETALLE AS T2 ON T1.inv_id=T2.inv_id ";        
        $sql .= "INNER JOIN VEMAEST AS T3 ON T2.vem_codven=T3.VEM_CODVEN ";
        $sql .= "WHERE inv_estado IS NOT NULL ";
        $sql .= $inv?"AND T1.inv_id = $inv ":" ";
        $sql .= "GROUP BY T2.inv_id,T3.VEM_CODVEN,T3.VEM_NOMBRE ";
        $sql .= "ORDER BY T2.inv_id,T3.VEM_CODVEN,T3.VEM_NOMBRE ";
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
    public function actualizaDistribucion($server,$inv,$ven,$treg,$prop)
    {
        $sql = <<<EOD
        DECLARE @var1 float; --Nueva Proporcion
        DECLARE @var2 INT;   --Id Inventario
        DECLARE @var3 INT;   --Cod Vendedor
        DECLARE @var4 INT;   --Tot Registros
        DECLARE @var5 INT;   --Tot Partes
        DECLARE @var6 INT;   --Una Parte
        DECLARE @var7 INT;   --Minimo Id Detalle
        DECLARE @var8 INT;   --Rango
        SELECT @var1 = $prop;
        SELECT @var2 = $inv;
        SELECT @var3 = $ven;
        SELECT @var4 = $treg;

        UPDATE dbo.INVENTARIO_RESPONSABLES
        SET inr_proporcion = @var1
        WHERE inv_id = @var2 AND vem_codven = @var3;

        SELECT @var5 = (SELECT sum(inr_proporcion) FROM dbo.INVENTARIO_RESPONSABLES
        WHERE inv_id = @var2);

        SELECT @var6 = CEILING(@var4/@var5); 

        SELECT @var7 = (SELECT MIN(ind_id) FROM dbo.INVENTARIO_DETALLE WHERE inv_id = @var2);

        DECLARE @Vendedor AS INT
        DECLARE @Proporci AS INT
        DECLARE InvResp CURSOR FOR SELECT vem_codven,inr_proporcion FROM dbo.INVENTARIO_RESPONSABLES WHERE inv_id = @var2
        OPEN InvResp
        FETCH NEXT FROM InvResp INTO @Vendedor,@Proporci
        WHILE @@fetch_status = 0

        BEGIN
        SELECT @var8 = @var7+(@Proporci*@var6)
        UPDATE dbo.INVENTARIO_DETALLE
        SET vem_codven = @Vendedor
        WHERE ind_id BETWEEN @var7 and @var8
        
        SELECT @var7 = @var8             
            FETCH NEXT FROM InvResp INTO @Vendedor,@Proporci
        END
        CLOSE InvResp
        DEALLOCATE InvResp
        EOD;
        if ($server == 2) {
            $query =  $this->dbjj->query($sql);
        } elseif ($server == 3) {
            $query =  $this->dbpm->query($sql);
        } else {
            $query =  $this->db->query($sql);
        }
        return $query->getResult();
    }

    public function crear_inventario($server, $data)
    {
        $sql = <<<EOD
        DECLARE @var1 VARCHAR(30);
        DECLARE @var2 INT;
        DECLARE @var3 INT;
        SELECT @var1 = '$data';
        SELECT @var2 = $server;
        
        INSERT INTO dbo.INVENTARIOS(inv_descripcion,inv_local) VALUES(@var1,@var2);
        SELECT @var3 = (SELECT @@IDENTITY AS 'Identity');
         
        INSERT INTO dbo.INVENTARIO_DETALLE (inv_id, art_key, arm_stock)
        SELECT @var3,
        ARTI.ART_KEY, 
        ARTICULO.ARM_STOCK
        FROM dbo.ARTI ARTI 
        INNER JOIN dbo.ARTICULO ARTICULO ON (ARTI.ART_KEY = ARTICULO.ARM_CODART AND ARTI.ART_CODCIA = ARTICULO.ARM_CODCIA) 
        WHERE
        ARTI.ART_SITUACION <> '1' AND 
        ARTI.ART_FAMILIA NOT IN (594,442) AND 
        ARTICULO.ARM_STOCK <> 0 
        ORDER BY ARTI.ART_FAMILIA ASC,ARTI.ART_NOMBRE ASC;
        
        UPDATE DBO.INVENTARIOS 
        SET inv_total_items = (SELECT COUNT(inv_id) FROM dbo.INVENTARIO_DETALLE WHERE inv_id = @var3 )
        WHERE inv_id = @var3;
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

    public function crear_responsable($server, $data)
    {
        if ($server == 2) {
            return $query =  $this->dbjj->table('INVENTARIO_RESPONSABLES')->insert($data);
        } elseif ($server == 3) {
            return $query =  $this->dbpm->table('INVENTARIO_RESPONSABLES')->insert($data);
        } elseif ($server == 1) {
            return $query =  $this->db->table('INVENTARIO_RESPONSABLES')->insert($data);
        }
    }
    
    public function get_responsable_info($server, $vem_codven)
    {
        $sql = "SELECT VEM_NOMBRE FROM VEMAEST WHERE VEM_CODVEN = $vem_codven";
        if ($server == 2) {
            $query = $this->dbjj->query($sql);
        } elseif ($server == 3) {
            $query = $this->dbpm->query($sql);
        } else {
            $query = $this->db->query($sql);
        }
        return $query->getRow();
    }
    
    public function actualizar_conteo($server, $art_key, $stock_fisico, $observaciones, $inv_id, $vem_codven)
    {
        $sql = "UPDATE INVENTARIO_DETALLE SET 
                ind_stock_fisico = $stock_fisico,
                ind_diferencia = ($stock_fisico - arm_stock),
                ind_observaciones = '$observaciones',
                ind_estado = 'contado',
                updated_at = GETDATE()
                WHERE art_key = $art_key AND inv_id = $inv_id AND vem_codven = $vem_codven";
        
        if ($server == 2) {
            return $this->dbjj->query($sql);
        } elseif ($server == 3) {
            return $this->dbpm->query($sql);
        } else {
            return $this->db->query($sql);
        }
    }
    
    public function distribuir_productos_automatico($server, $inv_id)
    {
        $sql = <<<EOD
        DECLARE @inv_id INT = $inv_id;
        DECLARE @total_productos INT;
        DECLARE @total_responsables INT;
        DECLARE @productos_por_responsable INT;
        
        -- Obtener totales
        SELECT @total_productos = COUNT(*) FROM INVENTARIO_DETALLE WHERE inv_id = @inv_id;
        SELECT @total_responsables = COUNT(*) FROM INVENTARIO_RESPONSABLES WHERE inv_id = @inv_id;
        
        IF @total_responsables > 0
        BEGIN
            SET @productos_por_responsable = @total_productos / @total_responsables;
            
            -- Distribuir productos de forma aleatoria
            WITH ProductosAleatorios AS (
                SELECT ind_id, art_key, 
                       ROW_NUMBER() OVER (ORDER BY NEWID()) as rn
                FROM INVENTARIO_DETALLE 
                WHERE inv_id = @inv_id
            ),
            ResponsablesConOrden AS (
                SELECT vem_codven, 
                       ROW_NUMBER() OVER (ORDER BY inr_id) as responsable_orden
                FROM INVENTARIO_RESPONSABLES 
                WHERE inv_id = @inv_id
            )
            UPDATE ID
            SET vem_codven = R.vem_codven
            FROM INVENTARIO_DETALLE ID
            INNER JOIN ProductosAleatorios PA ON ID.ind_id = PA.ind_id
            INNER JOIN ResponsablesConOrden R ON 
                R.responsable_orden = ((PA.rn - 1) % @total_responsables) + 1
            WHERE ID.inv_id = @inv_id;
        END
        EOD;
        
        if ($server == 2) {
            return $this->dbjj->query($sql);
        } elseif ($server == 3) {
            return $this->dbpm->query($sql);
        } else {
            return $this->db->query($sql);
        }
    }
    
    public function buscar_producto($server, $inv, $vendedor, $busqueda)
    {
        $sql = "SELECT T1.ind_id, T1.art_key, RTRIM(LTRIM(T2.ART_NOMBRE)) AS ART_NOMBRE,
                T6.PRE_EQUIV, RTRIM(LTRIM(T6.PRE_UNIDAD)) PRE_UNIDAD,
                T3.ARM_STOCK, T1.ind_stock_fisico, T1.ind_observaciones, T1.ind_estado
                FROM INVENTARIO_DETALLE as T1
                INNER JOIN ARTI as T2 ON(T1.art_key=T2.ART_KEY and T2.ART_CODCIA=25)
                INNER JOIN ARTICULO AS T3 ON(T1.art_key=T3.ARM_CODART AND T3.ARM_CODCIA=25)
                INNER JOIN PRECIOS AS T6 ON (T6.PRE_CODCIA=25 AND T1.art_key = T6.PRE_CODART)
                WHERE T6.PRE_FLAG_UNIDAD = 'A'
                AND T1.vem_codven = $vendedor
                AND T1.inv_id = $inv
                AND (T2.ART_NOMBRE LIKE '%$busqueda%' OR T1.art_key LIKE '%$busqueda%')";
        
        if ($server == 2) {
            $query = $this->dbjj->query($sql);
        } elseif ($server == 3) {
            $query = $this->dbpm->query($sql);
        } else {
            $query = $this->db->query($sql);
        }
        return $query->getResult();
    }
    
    public function get_inventarios_dashboard($server)
    {
        $sql = "SELECT I.inv_id, I.inv_descripcion, I.inv_fecha, I.inv_total_items, I.inv_estado,
                COUNT(DISTINCT IR.vem_codven) as total_responsables,
                COUNT(CASE WHEN ID.ind_estado = 'contado' THEN 1 END) as items_contados,
                CAST(COUNT(CASE WHEN ID.ind_estado = 'contado' THEN 1 END) * 100.0 / I.inv_total_items AS DECIMAL(5,2)) as porcentaje_avance
                FROM INVENTARIOS I
                LEFT JOIN INVENTARIO_RESPONSABLES IR ON I.inv_id = IR.inv_id
                LEFT JOIN INVENTARIO_DETALLE ID ON I.inv_id = ID.inv_id
                WHERE I.inv_estado IS NOT NULL
                GROUP BY I.inv_id, I.inv_descripcion, I.inv_fecha, I.inv_total_items, I.inv_estado
                ORDER BY I.inv_id DESC";
        
        if ($server == 2) {
            $query = $this->dbjj->query($sql);
        } elseif ($server == 3) {
            $query = $this->dbpm->query($sql);
        } else {
            $query = $this->db->query($sql);
        }
        return $query->getResult();
    }
    
    public function cerrar_inventario($server, $inv_id)
    {
        $sql = "UPDATE INVENTARIOS SET inv_estado = 0 WHERE inv_id = $inv_id";
        
        if ($server == 2) {
            return $this->dbjj->query($sql);
        } elseif ($server == 3) {
            return $this->dbpm->query($sql);
        } else {
            return $this->db->query($sql);
        }
    }
    
    public function generar_guia_ingreso($server, $inv_id)
    {
        $sql = <<<EOD
        DECLARE @new_numfac INT;
        DECLARE @new_numoper INT;
        DECLARE @fec_hoy VARCHAR(10) = CONVERT(VARCHAR(10), GETDATE(), 120);
        
        -- Obtener próximos números
        SELECT @new_numfac = ISNULL(MAX(ALL_NUMFAC), 0) + 1 FROM ALLOG WHERE ALL_TIPMOV = 6 AND ALL_CODCIA = '25';
        SELECT @new_numoper = ISNULL(MAX(ALL_NUMOPER), 0) + 1 FROM ALLOG;
        
        -- Insertar en ALLOG
        INSERT INTO ALLOG (
            ALL_TIPMOV, ALL_CODCIA, ALL_NUMSER, ALL_NUMFAC, ALL_FECHA_DIA,
            ALL_NUMOPER, ALL_SIGNO_ARM, ALL_IMPORTE_AMORT, ALL_NETO, ALL_BRUTO,
            ALL_CONCEPTO, ALL_SUBTRA, ALL_CODUSU, ALL_NUMOPER2, ALL_FECHA_PRO,
            ALL_FECHA_SUNAT, ALL_FECHA_CAN, ALL_CODTRA, ALL_CODTRA_EXT, ALL_CHESER,
            ALL_CODSUNAT, ALL_CANTIDAD, ALL_AUTOCON, ALL_FECHA_VCTO, ALL_NUMSER_C, ALL_NUMFAC_C
        )
        SELECT 
            6, '25', '1', @new_numfac, @fec_hoy,
            @new_numoper, 1, SUM(A.ARM_STOCK * P.PRE_PRE1), SUM(A.ARM_STOCK * P.PRE_PRE1), SUM(A.ARM_STOCK * P.PRE_PRE1),
            'AJUSTE INVENTARIO - SOBRANTE INV-' + CAST($inv_id AS VARCHAR),
            'INVENTARIO', 'ADMIN', @new_numoper, @fec_hoy, @fec_hoy, @fec_hoy,
            2403, 2403, 'i_c', 3, SUM(ID.ind_diferencia),
            'AJUSTE INVENTARIO - SOBRANTE INV-' + CAST($inv_id AS VARCHAR),
            @fec_hoy, 0, 0
        FROM INVENTARIO_DETALLE ID
        INNER JOIN ARTICULO A ON ID.art_key = A.ARM_CODART AND A.ARM_CODCIA = 25
        INNER JOIN PRECIOS P ON ID.art_key = P.PRE_CODART AND P.PRE_CODCIA = 25 AND P.PRE_FLAG_UNIDAD = 'A'
        WHERE ID.inv_id = $inv_id AND ID.ind_diferencia > 0;
        
        -- Insertar en FACART
        INSERT INTO FACART (
            FAR_TIPMOV, FAR_CODCIA, FAR_NUMSER, FAR_NUMFAC, FAR_NUMSEC, FAR_FECHA,
            FAR_NUMOPER, FAR_CODART, FAR_PRECIO, FAR_BRUTO, FAR_EQUIV, FAR_CANTIDAD,
            FAR_CONCEPTO, FAR_DESCRI, FAR_SUBTRA, FAR_CODUSU, FAR_FECHA_PRO, FAR_FECHA_CAN,
            FAR_SIGNO_ARM, FAR_ESTADO, FAR_EX_IGV, FAR_COD_SUNAT
        )
        SELECT 
            6, '25', '1', @new_numfac, ROW_NUMBER() OVER(ORDER BY ID.art_key), @fec_hoy,
            @new_numoper, ID.art_key, P.PRE_PRE1, ID.ind_diferencia * P.PRE_PRE1, P.PRE_EQUIV, ID.ind_diferencia,
            'AJUSTE INVENTARIO - SOBRANTE INV-' + CAST($inv_id AS VARCHAR),
            AR.ART_NOMBRE, 'INVENTARIO', 'ADMIN', @fec_hoy, @fec_hoy,
            1, 'N', 'A', 3
        FROM INVENTARIO_DETALLE ID
        INNER JOIN ARTI AR ON ID.art_key = AR.ART_KEY AND AR.ART_CODCIA = 25
        INNER JOIN PRECIOS P ON ID.art_key = P.PRE_CODART AND P.PRE_CODCIA = 25 AND P.PRE_FLAG_UNIDAD = 'A'
        WHERE ID.inv_id = $inv_id AND ID.ind_diferencia > 0;
        
        -- Actualizar stock
        EXEC sp_actualiza_stock 6, '25', '1', @new_numfac, @fec_hoy, @new_numoper;
        
        SELECT @new_numfac as numfac, @new_numoper as numoper;
        EOD;
        
        if ($server == 2) {
            $query = $this->dbjj->query($sql);
        } elseif ($server == 3) {
            $query = $this->dbpm->query($sql);
        } else {
            $query = $this->db->query($sql);
        }
        return $query->getRow();
    }
    
    public function generar_guia_salida($server, $inv_id)
    {
        $sql = <<<EOD
        DECLARE @new_numfac INT;
        DECLARE @new_numoper INT;
        DECLARE @fec_hoy VARCHAR(10) = CONVERT(VARCHAR(10), GETDATE(), 120);
        
        -- Obtener próximos números
        SELECT @new_numfac = ISNULL(MAX(ALL_NUMFAC), 0) + 1 FROM ALLOG WHERE ALL_TIPMOV = 5 AND ALL_CODCIA = '25';
        SELECT @new_numoper = ISNULL(MAX(ALL_NUMOPER), 0) + 1 FROM ALLOG;
        
        -- Insertar en ALLOG
        INSERT INTO ALLOG (
            ALL_TIPMOV, ALL_CODCIA, ALL_NUMSER, ALL_NUMFAC, ALL_FECHA_DIA,
            ALL_NUMOPER, ALL_SIGNO_ARM, ALL_IMPORTE_AMORT, ALL_NETO, ALL_BRUTO,
            ALL_CONCEPTO, ALL_SUBTRA, ALL_CODUSU, ALL_NUMOPER2, ALL_FECHA_PRO,
            ALL_FECHA_SUNAT, ALL_FECHA_CAN, ALL_CODTRA, ALL_CODTRA_EXT, ALL_CHESER,
            ALL_CODSUNAT, ALL_CANTIDAD, ALL_AUTOCON, ALL_FECHA_VCTO, ALL_NUMSER_C, ALL_NUMFAC_C
        )
        SELECT 
            5, '25', '1', @new_numfac, @fec_hoy,
            @new_numoper, -1, SUM(A.ARM_STOCK * P.PRE_PRE1), SUM(A.ARM_STOCK * P.PRE_PRE1), SUM(A.ARM_STOCK * P.PRE_PRE1),
            'AJUSTE INVENTARIO - FALTANTE INV-' + CAST($inv_id AS VARCHAR),
            'INVENTARIO', 'ADMIN', @new_numoper, @fec_hoy, @fec_hoy, @fec_hoy,
            2403, 2403, 's_c', 3, SUM(ABS(ID.ind_diferencia)),
            'AJUSTE INVENTARIO - FALTANTE INV-' + CAST($inv_id AS VARCHAR),
            @fec_hoy, 0, 0
        FROM INVENTARIO_DETALLE ID
        INNER JOIN ARTICULO A ON ID.art_key = A.ARM_CODART AND A.ARM_CODCIA = 25
        INNER JOIN PRECIOS P ON ID.art_key = P.PRE_CODART AND P.PRE_CODCIA = 25 AND P.PRE_FLAG_UNIDAD = 'A'
        WHERE ID.inv_id = $inv_id AND ID.ind_diferencia < 0;
        
        -- Insertar en FACART
        INSERT INTO FACART (
            FAR_TIPMOV, FAR_CODCIA, FAR_NUMSER, FAR_NUMFAC, FAR_NUMSEC, FAR_FECHA,
            FAR_NUMOPER, FAR_CODART, FAR_PRECIO, FAR_BRUTO, FAR_EQUIV, FAR_CANTIDAD,
            FAR_CONCEPTO, FAR_DESCRI, FAR_SUBTRA, FAR_CODUSU, FAR_FECHA_PRO, FAR_FECHA_CAN,
            FAR_SIGNO_ARM, FAR_ESTADO, FAR_EX_IGV, FAR_COD_SUNAT
        )
        SELECT 
            5, '25', '1', @new_numfac, ROW_NUMBER() OVER(ORDER BY ID.art_key), @fec_hoy,
            @new_numoper, ID.art_key, P.PRE_PRE1, ABS(ID.ind_diferencia) * P.PRE_PRE1, P.PRE_EQUIV, ABS(ID.ind_diferencia),
            'AJUSTE INVENTARIO - FALTANTE INV-' + CAST($inv_id AS VARCHAR),
            AR.ART_NOMBRE, 'INVENTARIO', 'ADMIN', @fec_hoy, @fec_hoy,
            -1, 'N', 'A', 3
        FROM INVENTARIO_DETALLE ID
        INNER JOIN ARTI AR ON ID.art_key = AR.ART_KEY AND AR.ART_CODCIA = 25
        INNER JOIN PRECIOS P ON ID.art_key = P.PRE_CODART AND P.PRE_CODCIA = 25 AND P.PRE_FLAG_UNIDAD = 'A'
        WHERE ID.inv_id = $inv_id AND ID.ind_diferencia < 0;
        
        -- Actualizar stock (para salidas usamos el mismo SP pero con TIPMOV 5)
        UPDATE ARTICULO SET 
            ARM_STOCK = ARM_STOCK + ID.ind_diferencia,
            ARM_SALIDAS = ARM_SALIDAS + ABS(ID.ind_diferencia)
        FROM ARTICULO A
        INNER JOIN INVENTARIO_DETALLE ID ON A.ARM_CODART = ID.art_key
        WHERE A.ARM_CODCIA = 25 AND ID.inv_id = $inv_id AND ID.ind_diferencia < 0;
        
        SELECT @new_numfac as numfac, @new_numoper as numoper;
        EOD;
        
        if ($server == 2) {
            $query = $this->dbjj->query($sql);
        } elseif ($server == 3) {
            $query = $this->dbpm->query($sql);
        } else {
            $query = $this->db->query($sql);
        }
        return $query->getRow();
    }
}
