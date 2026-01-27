<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Description of CajaModel
 *
 * @author José Luis
 */
class CajaMovimientosModel extends Model {

    var $table = 'caja_movimientos';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->dbpm = \Config\Database::connect('pmeza');
        $this->dbjj = \Config\Database::connect('juanjuicillo');
    }

    public function get_movimientos($idcaja,$server) {
        $sql = 'SELECT CM.*, RTRIM(VEM.VEM_NOMBRE) AS VEM_NOMBRE ';
        $sql .= 'FROM dbo.CAJA_MOVIMIENTOS AS CM ';
        $sql .= 'LEFT JOIN dbo.VEMAEST AS VEM ON (CM.CMV_CODVEN = VEM.VEM_CODVEN AND VEM.VEM_CODCIA = 25) ';
        $sql .= 'WHERE CM.CMV_CAJA = ' . $idcaja;
        if($server==2){
            $query =  $this->dbjj->query($sql);
        }elseif($server==3){
            $query =  $this->dbpm->query($sql);
        }else{
            $query =  $this->db->query($sql);
        }
        return $query->getResult();
    }

    public function get_movimiento($idmovimiento,$server) {
        $sql = 'SELECT * ';
        $sql .= 'FROM dbo.CAJA_MOVIMIENTOS AS CM ';
        $sql .= 'INNER JOIN dbo.CAJAS AS CA ON(CA.CAJ_NRO=CM.CMV_CAJA)  ';
        $sql .= 'INNER JOIN dbo.VEMAEST AS VEN ON (CA.CAJ_CODVEN=VEN.VEM_CODVEN AND VEN.VEM_CODCIA=25) ';
        $sql .= 'WHERE ';
        $sql .= 'CMV_NRO = ' . $idmovimiento;
        if($server==2){
            $query =  $this->dbjj->query($sql);
        }elseif($server==3){
            $query =  $this->dbpm->query($sql);
        }else{
            $query =  $this->db->query($sql);
        }
        return $query->getRow();
    }
    public function get_caja_datos($idcaja,$server) {
        $sql = 'SELECT * ';
        $sql .= 'FROM dbo.CAJAS AS CA ';
        $sql .= 'INNER JOIN dbo.VEMAEST AS VEN ON (CA.CAJ_CODVEN=VEN.VEM_CODVEN AND VEN.VEM_CODCIA=25) ';
        $sql .= 'WHERE ';
        $sql .= 'CA.CAJ_NRO = ' . $idcaja;
        
        if($server==2){
            $query =  $this->dbjj->query($sql);
        }elseif($server==3){
            $query =  $this->dbpm->query($sql);
        }else{
            $query =  $this->db->query($sql);
        }
        return $query->getRow();
    }
    public function crear_movimiento($data,$server) {
        if($server==2){
            return $query =  $this->dbjj->table('CAJA_MOVIMIENTOS')->insert($data);
        }elseif($server==3){
            return $query =  $this->dbpm->table('CAJA_MOVIMIENTOS')->insert($data);
        }else{
            return $query =  $this->db->table('CAJA_MOVIMIENTOS')->insert($data);
        }       
    }

    public function get_creditos($fechaInicio, $fechaFin) {
        // Ensure dates are in YYYYMMDD format for SQL Server
        $fechaInicio = date('Ymd', strtotime($fechaInicio));
        $fechaFin = date('Ymd', strtotime($fechaFin));

        $buildQuery = function($serverPrefix = '') use ($fechaInicio, $fechaFin) {
            $dbName = $serverPrefix ? $serverPrefix . '.[BDATOS].[dbo].' : '';
            
            $sql = 'SELECT CMV_CODVEN, VEM_NOMBRE, SUM(CMV_MONTO) AS DEUDA ';
            $sql .= ' FROM ' . $dbName . 'CAJAS AS CAJ ';
            $sql .= ' INNER JOIN ' . $dbName . 'CAJA_MOVIMIENTOS AS MOV ON CAJ.CAJ_NRO = MOV.CMV_CAJA ';
            $sql .= ' INNER JOIN ' . $dbName . 'VEMAEST AS VEN ON (CMV_CODVEN=VEM_CODVEN AND VEM_CODCIA=25) ';
            $sql .= ' WHERE CMV_TIPO IN(6,7) ';
            $sql .= " AND CAJ_FECHA BETWEEN '$fechaInicio' AND '$fechaFin' ";
            $sql .= ' GROUP BY CMV_CODVEN, VEM_NOMBRE ';
            return $sql;
        };

        // We need to sum the results of the UNION, because an employee might have credits in multiple servers.
        // So we wrap the UNION in an outer SELECT SUM
        
        $sqlLocal = $buildQuery('');
        $sqlSrv2 = $buildQuery('[SERVER02]');
        $sqlSrv3 = $buildQuery('[SERVER03]');

        $unionSql = "($sqlLocal) UNION ALL ($sqlSrv2) UNION ALL ($sqlSrv3)";

        $finalSql = "SELECT CMV_CODVEN, VEM_NOMBRE, SUM(DEUDA) AS DEUDA FROM ($unionSql) AS T GROUP BY CMV_CODVEN, VEM_NOMBRE ORDER BY CMV_CODVEN, VEM_NOMBRE";

        $query = $this->db->query($finalSql);
        return $query->getResult();
    }

    public function get_creditos_empleado($mes, $anio, $empleado) {
        $buildQuery = function($serverPrefix = '') use ($mes, $anio, $empleado) {
            $dbName = $serverPrefix ? $serverPrefix . '.[BDATOS].[dbo].' : '';

            $sql = 'SELECT CAJ_FECHA,CMV_TIPO,RTRIM(TAB_NOMLARGO) TAB_NOMLARGO,CMV_DESCRIPCION,CMV_MONTO ';
            $sql .= ' FROM ' . $dbName . 'CAJAS AS CAJ ';
            $sql .= ' INNER JOIN ' . $dbName . 'CAJA_MOVIMIENTOS AS MOV ON CAJ.CAJ_NRO = MOV.CMV_CAJA ';
            $sql .= ' INNER JOIN ' . $dbName . 'TABLAS AS TAB ON(CMV_TIPO=TAB_NUMTAB AND TAB_TIPREG=100) ';
            $sql .= ' WHERE CMV_TIPO IN(6,7) ';
            $sql .= ' AND MONTH(CAJ_FECHA) = ' . $mes;
            $sql .= ' AND YEAR(CAJ_FECHA) = ' . $anio;
            $sql .= ' AND CMV_CODVEN = ' . $empleado;
            return $sql;
        };

        $sql = $buildQuery('') . ' UNION ALL ' . $buildQuery('[SERVER02]') . ' UNION ALL ' . $buildQuery('[SERVER03]');
        
        $query = $this->db->query($sql);
        return $query->getResult();
    }
public function delete_movimiento($id,$server){
    if($server==2){
        $builder =  $this->dbjj->table('CAJA_MOVIMIENTOS');
    }elseif($server==3){
        $builder =  $this->dbpm->table('CAJA_MOVIMIENTOS');
    }else{
        $builder =  $this->db->table('CAJA_MOVIMIENTOS');
    } 
return $builder->delete(['CMV_NRO' => $id]);
}
}
