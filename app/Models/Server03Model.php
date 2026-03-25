<?php

namespace App\Models;

use CodeIgniter\Model;

class Server03Model extends Model {

    var $table = 'facart';
    protected $db;
    protected $dbpm;
    protected $dbjj;

    public function __construct() {
        parent::__construct();
        $this->dbpm = \Config\Database::connect('pmeza');
        $this->dbjj = \Config\Database::connect('juanjuicillo');
    }
    private function isServerAvailable($group)
    {
        $cache = \Config\Services::cache();
        $cacheKey = "db_avail_{$group}";
        
        $cachedStatus = $cache->get($cacheKey);
        if ($cachedStatus !== null) {
            return (bool)$cachedStatus;
        }

        $dbConfig = config('Database');
        if (!isset($dbConfig->$group)) {
            return false;
        }
        
        $config = $dbConfig->$group;
        $host = $config['hostname'];
        $port = $config['port'] ?? 1433;

        // Limpiar hostname si tiene instancia (ej: host\instance)
        $hostOnly = explode('\\', $host)[0];

        $connection = @fsockopen($hostOnly, $port, $errno, $errstr, 1);

        if ($connection) {
            fclose($connection);
            $cache->save($cacheKey, 1, 300); // Cache success for 5 min
            return true;
        } else {
            log_message('error', "Server group '{$group}' at {$hostOnly}:{$port} unavailable. Error: {$errstr}");
            $cache->save($cacheKey, 0, 60); // Cache failure for 1 min
            return false;
        }
    }
    
    private function getDbByServer($server)
    {
        $group = ($server == 3) ? 'pmeza' : (($server == 2) ? 'juanjuicillo' : 'default');
        if ($this->isServerAvailable($group)) {
            if ($group === 'pmeza') return $this->dbpm;
            if ($group === 'juanjuicillo') return $this->dbjj;
            return $this->db;
        }
        return null;
    }

    public function get_max_numfac($server) {
        $sql = 'SELECT ';
        $sql .= '(SELECT MAX(ALL_NUMFAC) FROM DBO.allog WHERE ALL_TIPMOV = 6 AND ALL_CODCIA = 25 AND ALL_NUMSER = 1) FAR_NUMFAC, ';
        $sql .= '(SELECT MAX(ALL_NUMOPER) FROM DBO.ALLOG WHERE ALL_FECHA_DIA=CONVERT (date, GETDATE())) FAR_NUMOPER';
        
        $db = $this->getDbByServer($server);
        if (!$db) return null;
        
        $query = $db->query($sql);
        return $query->getRow();
    }
    public function get_max_numart($server) {
        $sql = 'SELECT MAX(ART_KEY) ART_KEY FROM DBO.ARTI';
        
        $db = $this->getDbByServer($server);
        if (!$db) return null;
        
        $query = $db->query($sql);
        $row   = $query->getRow();
        return $row ? $row->ART_KEY : null;
    }

    public function get_comisiones_simple($mes, $anio,$server) {
        $sql = 'SELECT T2.VEM_CODVEN,T2.VEM_NOMBRE, ROUND(sum(T1.FAR_CANTIDAD/T1.FAR_EQUIV*T1.FAR_PRECIO*0.8474576271186441),2,0) as COMISION ';
        $sql .= 'FROM FACART T1 ';
        $sql .= 'INNER JOIN VEMAEST T2 ON (T1.FAR_CODVEN = T2.VEM_CODVEN AND T1.FAR_CODCIA = T2.VEM_CODCIA) ';
        $sql .= 'WHERE MONTH(T1.FAR_FECHA) = ' . $mes . ' AND ';
        $sql .= 'YEAR(T1.FAR_FECHA) = ' . $anio . ' AND ';
        $sql .= "T1.FAR_ESTADO <> 'E' AND ";
        $sql .= "T1.FAR_ESTADO2 <> 'L' AND ";
        $sql .= 'T1.FAR_TIPMOV = 10 AND ';
        $sql .= "T1.FAR_CODART NOT IN(SELECT art_key FROM dbo.ARTI WHERE ART_GRUPOP=566) ";
        $sql .= 'GROUP BY T2.VEM_CODVEN,T2.VEM_NOMBRE ';
        $sql .= 'ORDER BY T2.VEM_CODVEN,T2.VEM_NOMBRE ';
        
        $db = $this->getDbByServer($server);
        if (!$db) return [];
        
        $query = $db->query($sql);
        return $query->getResult();
    }

    public function get_ventas($dia, $mes, $anio) {
        $sql = 'SELECT SUM(ALL_IMPORTE_AMORT) VENTAS ';
        $sql .= 'FROM dbo.ALLOG ';
        $sql .= 'WHERE ';
        $sql .= empty($dia) ? '' : 'DAY(ALL_FECHA_DIA) = \'' . $dia . '\'  AND ';
        $sql .= empty($mes) ? '' : 'MONTH(ALL_FECHA_DIA) = \'' . $mes . '\'  AND ';
        $sql .= empty($anio) ? '' : 'YEAR(ALL_FECHA_DIA) = \'' . $anio . '\'  AND ';
        $sql .= 'ALL_TIPMOV = 10 AND ';
        $sql .= 'ALL_SIGNO_CAJA = 1 AND ';
        $sql .= "ALL_FLAG_EXT  <> 'E' AND ";
        $sql .= "(ALL_CODTRA <> 1111 OR ALL_CODTRA <> 1122) ";
        
        $db = $this->getDbByServer(3); // Assuming pmeza is server 3
        if (!$db) return (object)['VENTAS' => 0];

        $query = $db->query($sql);
        return $query->getRow();
    }

    public function get_rentabilidad_empleado($mes, $anio, $server) {
        $sql = 'SELECT T2.VEM_CODVEN,T2.VEM_NOMBRE, ROUND(sum(T1.FAR_CANTIDAD/T1.FAR_EQUIV*T1.FAR_PRECIO*0.8474576271186441),2,0) as VENTA, ';
        $sql .= 'ROUND(sum(T1.FAR_CANTIDAD/T1.FAR_EQUIV*T1.FAR_PRECIO),2,0) as NETO,';
        $sql .= 'ROUND(sum(T1.FAR_CANTIDAD/T1.FAR_EQUIV*T1.FAR_COSPRO),2,0) as COSTO ';
        $sql .= 'FROM FACART T1 ';
        $sql .= 'INNER JOIN VEMAEST T2 ON (T1.FAR_CODVEN = T2.VEM_CODVEN AND T1.FAR_CODCIA = T2.VEM_CODCIA) ';
        $sql .= 'WHERE MONTH(T1.FAR_FECHA) = ' . $mes . ' AND ';
        $sql .= 'YEAR(T1.FAR_FECHA) = ' . $anio . ' AND ';
        $sql .= "T1.FAR_ESTADO <> 'E' AND ";
        $sql .= "T1.FAR_ESTADO2 <> 'L' AND ";
        $sql .= 'T1.FAR_TIPMOV = 10 AND ';
        $sql .= "T1.FAR_CODART NOT IN(SELECT art_key FROM dbo.ARTI WHERE ART_GRUPOP=566) ";
        $sql .= 'GROUP BY T2.VEM_CODVEN,T2.VEM_NOMBRE ';
        $sql .= 'ORDER BY T2.VEM_CODVEN,T2.VEM_NOMBRE ';
        
        $db = $this->getDbByServer($server);
        if (!$db) return [];

        $query = $db->query($sql);
        return $query->getResult();
    }
    public function crear_guia_allog($data,$server) {
        $db = $this->getDbByServer($server);
        if (!$db) return false;
        return $db->table('ALLOG')->insert($data);
    }

    public function crear_guia_facart($data,$server) {
        $db = $this->getDbByServer($server);
        if (!$db) return false;
        return $db->table('FACART')->insertBatch($data);
    }

    public function crear_prod_arti($data,$server) {
        $db = $this->getDbByServer($server);
        if (!$db) return false;
        return $db->table('ARTI')->insertBatch($data);
    }
    public function crear_prod_articulo($data,$server) {
        $db = $this->getDbByServer($server);
        if (!$db) return false;
        return $db->table('ARTICULO')->insertBatch($data);
    }
    public function crear_prod_precios($data,$server) {
        $db = $this->getDbByServer($server);
        if (!$db) return false;
        return $db->table('PRECIOS')->insertBatch($data);
    }
    public function crear_prod_lote($data,$server) {
        $db = $this->getDbByServer($server);
        if (!$db) return false;
        return $db->table('LOTE')->insertBatch($data);
    }

    public function actualiza_stock($data,$server) {
        $sp = "sp_actualiza_stock ?,?,?,?,?,? ";
        $db = $this->getDbByServer($server);
        if (!$db) return false;
        $query = $db->query($sp,[$data['TIPMOV'],$data['CODCIA'],$data['NUMSER'],$data['NUMFAC'],$data['FECHA'],$data['NUMOPER']]);
        return $query;
    }

    public function get_articulos_selec($artkey, $artsbg, $local)
{
    $sql = "SELECT T1.ARM_CODART, art_familia, ";
    $sql .= "RTRIM(LTRIM(T2.ART_NOMBRE)) ART_NOMBRE, AVG(T1.ARM_STOCK) AS StockGen, ";
    $sql .= "CASE WHEN MAX(PRE_EQUIV) = 1 THEN CAST(FLOOR(AVG(T1.ARM_STOCK)) AS varchar)
            ELSE CAST((FLOOR(AVG(T1.ARM_STOCK)/MAX(PRE_EQUIV))) AS varchar)
            + '/' + CAST(FLOOR(AVG(T1.ARM_STOCK)%MAX(PRE_EQUIV)) AS VARCHAR) END AS STOCK ";
    $sql .= "FROM dbo.ARTICULO AS T1 ";
    $sql .= "INNER JOIN DBO.ARTI AS T2 ON (T1.ARM_CODART = T2.ART_KEY AND T1.ARM_CODCIA = T2.ART_CODCIA) ";
    $sql .= "LEFT JOIN PRECIOS AS T3 ON (T3.PRE_CODART = T1.ARM_CODART AND T3.PRE_CODCIA = '25') ";
    $sql .= "WHERE ";
    $sql .= empty($artsbg) ? "T1.ARM_CODART = $artkey " : "T2.ART_SUBGRU = $artsbg AND T1.ARM_STOCK > 0 ";
    $sql .= "GROUP BY T1.ARM_CODART, T2.ART_NOMBRE, art_familia ";
    $sql .= "ORDER BY T1.ARM_CODART, T2.ART_NOMBRE";

    try {
        $query = null;

        switch ($local) {
            case 3:
                if ($this->isServerAvailable('pmeza')) {
                    $query = $this->dbpm->query($sql);
                } else {
                    return [];
                }
                break;

            case 2:
                if ($this->isServerAvailable('juanjuicillo')) {
                    $query = $this->dbjj->query($sql);
                } else {
                    return [];
                }
                break;

            case 1:
                if ($this->isServerAvailable('default')) {
                    $query = $this->db->query($sql);
                } else {
                    return [];
                }
                break;

            default:
                log_message('error', 'Invalid local value.');
                return [];
        }

        if (!$query) {
            throw new \Exception('Query failed: ' . $this->db->error());
        }

        return $query->getResult();

    } catch (\Throwable $e) {
        log_message('error', 'Database connection or query failed: ' . $e->getMessage());
        return [];
    }
}



    public function update_precios_precio($server,$PRE_CODCIA,$PRE_CODART,$PRE_SECUENCIA,$PRE_PRE1,$PRE_PRE2,$PRE_POR1,$PRE_POR2){
        $sql = 'UPDATE DBO.PRECIOS SET ';
        $sql.= ' PRE_PRE1='.$PRE_PRE1.',';
        $sql.= ' PRE_PRE2='.$PRE_PRE2.',';
        $sql.= ' PRE_POR1='.$PRE_POR1.',';
        $sql.= ' PRE_POR2='.$PRE_POR2;
        $sql.= ' WHERE ';
        $sql.= ' PRE_CODCIA='.$PRE_CODCIA.' AND ';
        $sql.= ' PRE_CODART='.$PRE_CODART.' AND ';
        $sql.= ' PRE_SECUENCIA='.$PRE_SECUENCIA;
        //echo $sql; die();
        if($server==2){
            return $query =  $this->dbjj->simpleQuery($sql);
        }elseif($server==3){
            return $query =  $this->dbpm->simpleQuery($sql);
        }else{
            return $query =  $this->db->simpleQuery($sql);
        }

    }

    public function update_precios_articulo($server,$ARM_CODCIA,$ARM_CODART,$ARM_COSPRO){
        $sql = 'UPDATE DBO.ARTICULO ';
        $sql.= ' SET ARM_COSPRO='.$ARM_COSPRO;
        $sql.= ' WHERE ';
        $sql.= ' ARM_CODCIA='.$ARM_CODCIA.' AND ';
        $sql.= ' ARM_CODART='.$ARM_CODART;

        //echo $sql; die();
        if($server==2){
            return $query =  $this->dbjj->simpleQuery($sql);
        }elseif($server==3){
            return $query =  $this->dbpm->simpleQuery($sql);
        }else{
            return $query =  $this->db->simpleQuery($sql);
        }

    }

    public function get_monto($serie,$numero){
        $sql = 'SELECT ALL_IMPORTE_AMORT ';
        $sql.= 'FROM dbo.ALLOG ';
        $sql.= ' WHERE ALL_FECHA_PRO >= DATEADD(MM, -2,GETDATE()) ' ;
        $sql.= " AND ALL_NUMSER = ".$serie;
        $sql.= ' AND ALL_NUMFAC = '.$numero;
        $sql.= ' AND ALL_TIPMOV = 10 ';
        $sql.= ' AND ALL_SIGNO_CAJA = 1 ';
        $sql.= " AND ALL_FLAG_EXT  <> 'E' ";
        $sql.= " AND (ALL_CODTRA <> 1111 OR ALL_CODTRA <> 1122) ";
        if($serie==10){
            $query =  $this->dbjj->query($sql);
            $ret = $query->getRow();
        }elseif($serie==12){
            $query =  $this->dbpm->query($sql);
            $ret = $query->getRow();
        }else{
            $query =  $this->db->query($sql);
            $ret = $query->getRow();
        }
        return number_format($ret->ALL_IMPORTE_AMORT, 2);
    }



}
