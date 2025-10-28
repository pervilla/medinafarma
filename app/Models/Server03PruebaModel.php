<?php

namespace App\Models;

use CodeIgniter\Model;

class Server03PruebaModel extends Model {

    var $table = 'facart';
    protected $dbpmpr;
    protected $db;
    protected $dbpm;
    protected $dbjj;

    public function __construct() {
        parent::__construct();
        $this->dbpmpr = \Config\Database::connect('pmezaPrueba');
    }

    public function get_max_numfac() {
        $sql = 'SELECT ';
        $sql .= '(SELECT MAX(ALL_NUMFAC) FROM DBO.allog WHERE ALL_TIPMOV = 6 AND ALL_CODCIA = 25 AND ALL_NUMSER = 1) FAR_NUMFAC, ';
        $sql .= '(SELECT MAX(ALL_NUMOPER) FROM DBO.ALLOG WHERE ALL_FECHA_DIA=CONVERT (date, GETDATE())) FAR_NUMOPER';
        $query = $this->dbpmpr->query($sql);
        return $query->getRow();
    }

    public function get_comisiones($mes, $anio) {
        $sql = 'SELECT T2.VEM_CODVEN, T2.VEM_NOMBRE,T2.VEM_META, ROUND(sum(T1.FAR_CANTIDAD/T1.FAR_EQUIV*T1.FAR_PRECIO*0.8474576271186441),2,0) as COMISION ';
        $sql .= 'FROM FACART T1 ';
        $sql .= 'INNER JOIN VEMAEST T2 ON (T1.FAR_CODVEN = T2.VEM_CODVEN AND T1.FAR_CODCIA = T2.VEM_CODCIA) ';
        $sql .= 'WHERE MONTH(T1.FAR_FECHA) = ' . $mes . ' AND ';
        $sql .= 'YEAR(T1.FAR_FECHA) = ' . $anio . ' AND ';
        $sql .= "T1.FAR_ESTADO <> 'E' AND ";
        $sql .= "T1.FAR_ESTADO2 <> 'L' AND ";
        $sql .= 'T1.FAR_TIPMOV = 10 ';
        $sql .= 'GROUP BY T2.VEM_CODVEN,T2.VEM_NOMBRE,T2.VEM_META ';
        $sql .= 'ORDER BY T2.VEM_CODVEN,T2.VEM_NOMBRE,T2.VEM_META ';
        $query = $this->dbpmpr->query($sql);
        return $query->getResult();
    }

    public function get_comisiones_simple($mes, $anio) {
        $sql = 'SELECT T2.VEM_CODVEN,T2.VEM_NOMBRE, ROUND(sum(T1.FAR_CANTIDAD/T1.FAR_EQUIV*T1.FAR_PRECIO*0.8474576271186441),2,0) as COMISION ';
        $sql .= 'FROM FACART T1 ';
        $sql .= 'INNER JOIN VEMAEST T2 ON (T1.FAR_CODVEN = T2.VEM_CODVEN AND T1.FAR_CODCIA = T2.VEM_CODCIA) ';
        $sql .= 'WHERE MONTH(T1.FAR_FECHA) = ' . $mes . ' AND ';
        $sql .= 'YEAR(T1.FAR_FECHA) = ' . $anio . ' AND ';
        $sql .= "T1.FAR_ESTADO <> 'E' AND ";
        $sql .= "T1.FAR_ESTADO2 <> 'L' AND ";
        $sql .= 'T1.FAR_TIPMOV = 10 AND ';
        $sql .= "T1.FAR_CODART NOT IN('2334626','2348875','2348982','2349347','2375982','2349454') ";
        $sql .= 'GROUP BY T2.VEM_CODVEN,T2.VEM_NOMBRE ';
        $sql .= 'ORDER BY T2.VEM_CODVEN,T2.VEM_NOMBRE ';
        $query = $this->dbpmpr->query($sql);
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
        $query = $this->dbpmpr->query($sql);
        return $query->getRow();
    }

    public function get_rentabilidad_empleado($mes, $anio) {
        $sql = 'SELECT T2.VEM_CODVEN,T2.VEM_NOMBRE, ROUND(sum(T1.FAR_CANTIDAD/T1.FAR_EQUIV*T1.FAR_PRECIO),2,0) as VENTA, ';
        $sql .= 'ROUND(sum(T1.FAR_CANTIDAD/T1.FAR_EQUIV*T1.FAR_COSPRO),2,0) as COSTO ';
        $sql .= 'FROM FACART T1 ';
        $sql .= 'INNER JOIN VEMAEST T2 ON (T1.FAR_CODVEN = T2.VEM_CODVEN AND T1.FAR_CODCIA = T2.VEM_CODCIA) ';
        $sql .= 'WHERE MONTH(T1.FAR_FECHA) = ' . $mes . ' AND ';
        $sql .= 'YEAR(T1.FAR_FECHA) = ' . $anio . ' AND ';
        $sql .= "T1.FAR_ESTADO <> 'E' AND ";
        $sql .= "T1.FAR_ESTADO2 <> 'L' AND ";
        $sql .= 'T1.FAR_TIPMOV = 10 ';
        $sql .= 'GROUP BY T2.VEM_CODVEN,T2.VEM_NOMBRE ';
        $sql .= 'ORDER BY T2.VEM_CODVEN,T2.VEM_NOMBRE ';
        $query = $this->dbpmpr->query($sql);
        return $query->getResult();
    }

    public function crear_guia_allog($data) {
        return $this->dbpmpr->table('ALLOG')->insert($data);
    }

    public function crear_guia_facart($data) {
        return $this->dbpmpr->table('FACART')->insertBatch($data);
    }

    public function actualiza_stock($data) {
        $sp = "sp_actualiza_stock ?,?,?,?,?,? ";
        $query = $this->dbpmpr->query($sp,[$data['TIPMOV'],$data['CODCIA'],$data['NUMSER'],$data['NUMFAC'],$data['FECHA'],$data['NUMOPER']]);
        return $query;
    }

}
