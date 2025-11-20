<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use CodeIgniter\Model;
/**
 * Description of Allog_model
 *
 * @author José Luis
 */
class AllogModel  extends Model {
 
    var $table = 'allog';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->dbpm = \Config\Database::connect('pmeza');
        $this->dbjj = \Config\Database::connect('juanjuicillo');
    }
 
    public function get_operaciones($dia,$mes,$anio,$fecha1,$fecha2,$mov,$numero) {
       $sql = 'SELECT CONVERT(VARCHAR(10),ALL_FECHA_PRO, 103) ALL_FECHA_PRO,T1.ALL_NUMSER,ALL_NUMFAC,LTRIM(RTRIM(CLI_NOMBRE)) CLI_NOMBRE,ALL_IMPORTE_AMORT ';
        $sql.= 'FROM ALLOG T1 ';
        $sql.= 'INNER JOIN CLIENTES T2 ON (T1.ALL_CODCLIE= T2.CLI_CODCLIE AND T1.ALL_CODCIA = T2.CLI_CODCIA) ';
        $sql.= 'WHERE ALL_CODCIA in (\'25\') AND  ';
        $sql.= 'ALL_TIPMOV = '.$mov.' AND ';
        $sql.= 'ALL_FECHA_PRO >= \''.$fecha1.'\'  AND ';
        $sql.= 'ALL_FECHA_PRO <= \''.$fecha2.'\'  AND ';
        $sql.= $numero?'ALL_NUMFAC = \''.$numero.'\'  AND ':'';        
        $sql.= "ALL_FLAG_EXT  <> 'E' AND ";
        $sql.= "T2.CLI_CP = 'P' AND ";
        $sql.= "T1.ALL_CODCLIE <> 0 ";
        $sql.= 'ORDER BY ALL_FECHA_PRO,ALL_NUMSER,ALL_NUMFAC ';
        //echo $sql; die();
        $query =  $this->db->query($sql);
        return $query->getResult();
    }
    public function get_operaciones2($fecha1,$fecha2,$mov,$numero) {
       $sql = "SELECT CONVERT(VARCHAR(26),ALL_FECHA_PRO,23) ALL_FECHA_PRO,T1.ALL_NUMOPER,T1.ALL_NUMSER,ALL_NUMFAC,RTRIM(ALL_CONCEPTO) ALL_CONCEPTO,ALL_IMPORTE_AMORT,rtrim(ALL_CTAG1) ALL_CTAG1 ";
        $sql.= 'FROM ALLOG T1 ';
        $sql.= 'WHERE ALL_CODCIA in (\'25\') AND  ';
        $sql.= 'ALL_TIPMOV = '.$mov.' AND ';
        $sql.= 'ALL_FECHA_PRO >= \''.$fecha1.'\'  AND ';
        $sql.= 'ALL_FECHA_PRO <= \''.$fecha2.'\'  AND ';
        $sql.= $numero?'ALL_NUMFAC = \''.$numero.'\'  AND ':'';  
        $sql.= "ALL_FLAG_EXT  <> 'E' ";        
        $sql.= 'ORDER BY ALL_FECHA_PRO,ALL_NUMOPER,ALL_NUMSER,ALL_NUMFAC ';
        //echo $sql;
        $query =  $this->db->query($sql);
     
        return $query->getResult();
    }
    public function get_lista_ventas($fecha1,$fecha2,$mov,$numero,$server) {
        $sql = "SELECT CONVERT(VARCHAR(26),ALL_FECHA_PRO,23) ALL_FECHA_PRO,ALL_HORA,T1.ALL_NUMOPER,RTRIM(T1.ALL_NUMSER) ALL_NUMSER,";
        $sql.= "ALL_NUMFAC,ALL_FBG,ALL_CODVEN, ";
        $sql.= "RTRIM(ALL_CONCEPTO) ALL_CONCEPTO,ALL_IMPORTE_AMORT,rtrim(ALL_CTAG1) ALL_CTAG1 ";
         $sql.= 'FROM ALLOG T1 ';
         $sql.= 'WHERE ALL_CODCIA in (\'25\') AND  ';
         $sql.= 'ALL_TIPMOV = '.$mov.' AND ';
         $sql.= 'ALL_FECHA_PRO >= \''.$fecha1.'\'  AND ';
         $sql.= 'ALL_FECHA_PRO <= \''.$fecha2.'\'  AND ';
         $sql.= $numero?'ALL_NUMFAC = \''.$numero.'\'  AND ':'';  
         $sql.= "ALL_FLAG_EXT  <> 'E' ";        
         $sql.= 'ORDER BY ALL_FECHA_PRO,ALL_NUMOPER,ALL_NUMSER,ALL_NUMFAC ';
        //echo $sql;
         if($server==2){
            $query =  $this->dbjj->query($sql);
        }elseif($server==3){
            $query =  $this->dbpm->query($sql);
        }else{
            $query =  $this->db->query($sql);
        }
         return $query->getResult();
     }
    public function get_ventas($dia,$mes,$anio){
        $sql = 'SELECT SUM(ALL_IMPORTE_AMORT) VENTAS ';
        $sql.= 'FROM dbo.ALLOG ';
        $sql.= 'WHERE ';
        $sql.= empty($dia)?'':'DAY(ALL_FECHA_DIA) = \''.$dia.'\'  AND ';
        $sql.= empty($mes)?'':'MONTH(ALL_FECHA_DIA) = \''.$mes.'\'  AND ';
        $sql.= empty($anio)?'':'YEAR(ALL_FECHA_DIA) = \''.$anio.'\'  AND ';
        $sql.= 'ALL_TIPMOV = 10 AND ';
        $sql.= 'ALL_SIGNO_CAJA = 1 AND ';
        $sql.= "ALL_FLAG_EXT  <> 'E' AND ";
        $sql.= "(ALL_CODTRA <> 1111 OR ALL_CODTRA <> 1122) ";
        //echo $sql;
        $query =  $this->db->query($sql);
        return $query->getRow();
    }
    public function get_ventas_dia($dia,$mes,$anio){
        $sql = 'SELECT DAY(ALL_FECHA_DIA) DIA,ALL_CAJA_NRO, SUM(ALL_IMPORTE_AMORT) VENTAS ';
        $sql.= 'FROM dbo.ALLOG ';
        $sql.= 'WHERE ';
        $sql.= empty($dia)?'':'DAY(ALL_FECHA_DIA) = \''.$dia.'\'  AND ';
        $sql.= empty($mes)?'':'MONTH(ALL_FECHA_DIA) = \''.$mes.'\'  AND ';
        $sql.= empty($anio)?'':'YEAR(ALL_FECHA_DIA) = \''.$anio.'\'  AND ';
        $sql.= 'ALL_TIPMOV = 10 AND ';
        $sql.= 'ALL_SIGNO_CAJA = 1 AND ';
        $sql.= "ALL_FLAG_EXT  <> 'E' AND ";
        $sql.= "(ALL_CODTRA <> 1111 OR ALL_CODTRA <> 1122) ";
        $sql.= 'GROUP BY DAY(ALL_FECHA_DIA),ALL_CAJA_NRO ';
        $sql.= 'ORDER BY DAY(ALL_FECHA_DIA),ALL_CAJA_NRO ';
        //echo $sql;
        $query =  $this->db->query($sql);
        return $query->getResult();
    }
    public function get_nro_oper($fecha,$docum,$server){
        $sql = "SELECT ALL_NUMOPER FROM DBO.ALLOG WHERE ALL_FECHA_DIA = '$fecha' AND ALL_NUMFAC = '$docum' and ALL_CODTRA <> 1111";
        if($server==2){
            $query =  $this->dbjj->query($sql);
        }elseif($server==3){
            $query =  $this->dbpm->query($sql);
        }else{
            $query =  $this->db->query($sql);
        }
        return $query->getRow();
    }
    public function get_nro_doc($fecha,$server){
        $sql = "SELECT TOP 1 rtrim(ALL_NUMSER) ALL_NUMSER,ALL_NUMFAC FROM DBO.ALLOG "; 
        $sql.= "WHERE ALL_TIPMOV = 10 AND ALL_FECHA_DIA = '$fecha' ";
        $sql.= "AND ALL_FLAG_EXT <> 'E' ";
        $sql.= "AND ALL_CODCLIE <> 0 ";
        $sql.= "ORDER BY ALL_NUMOPER DESC ";
        if($server==2){
            $query =  $this->dbjj->query($sql);
        }elseif($server==3){
            $query =  $this->dbpm->query($sql);
        }else{
            $query =  $this->db->query($sql);
        }
        return $query->getRow();
    }
    public function set_cierre_caja($fecha,$oper2,$oper,$caja,$server){
        $sql = 'UPDATE DBO.ALLOG SET ALL_CAJA_NRO = '.$caja;
        $sql.= ' WHERE ALL_FECHA_DIA = \''.$fecha.'\' ' ;
        $sql.= ' AND ALL_NUMOPER >= '.$oper;
        $sql.= " AND ALL_NUMOPER <= ".$oper2;
        $sql.= ' AND ALL_TIPMOV = 10 AND ';
        $sql.= ' ALL_SIGNO_CAJA = 1 AND ';
        $sql.= " ALL_FLAG_EXT  <> 'E' AND ";
        $sql.= " (ALL_CODTRA <> 1111 OR ALL_CODTRA <> 1122) ";
        //echo $sql; die();
        if($server==2){
            return $query =  $this->dbjj->simpleQuery($sql);
        }elseif($server==3){
            return $query =  $this->dbpm->simpleQuery($sql);
        }else{
            return $query =  $this->db->simpleQuery($sql);
        }

    }
    
    public function set_guia_migrada($fecha,$numser,$numfac,$value){
        $sql = 'UPDATE DBO.ALLOG SET ALL_CTAG1 = '.$value;
        $sql.= ' WHERE ALL_FECHA_PRO = \''.$fecha.'\' ' ;
        $sql.= " AND ALL_NUMSER = ".$numser;
        $sql.= ' AND ALL_NUMFAC = '.$numfac;

        return $this->db->simpleQuery($sql);
        
    }

    public function get_pagos_consulta($server,$fecha01,$fecha02){
        switch($server){case 1:$local='CTO'; break; case 2:$local='JCL'; break;case 3:$local='PMZ'; break;}
        $sql = "SELECT ";
        $sql.= "CONVERT(VARCHAR(26),ALL_FECHA_PRO,23) ALL_FECHA_PRO, ";
        $sql.= "ART.ART_NOMBRE,far_cliente, ";
        $sql.= "'$local - ' + CASE T2.FAR_FBG WHEN 'B' THEN 'B0' WHEN 'F' THEN 'F0' ELSE 'G0' END + ";
        $sql.= "RTRIM(T1.ALL_NUMSER) +'-'+ CAST(ALL_NUMFAC AS VARCHAR(6)) AS COMPROBANTE, ";
        $sql.= "FAR_PRECIO,RTRIM(VM.VEM_NOMBRE) VEN ";
        $sql.= "FROM ALLOG T1 ";
        $sql.= "LEFT JOIN FACART T2 ON(T2.FAR_CODCIA=T1.ALL_CODCIA AND T2.FAR_FBG=T1.ALL_FBG  ";
        $sql.= "         AND T2.FAR_NUMFAC=T1.ALL_NUMFAC AND T2.FAR_NUMSER=T1.ALL_NUMSER  ";
        $sql.= "         AND T2.FAR_TIPMOV=T1.ALL_TIPMOV) ";
        $sql.= "LEFT JOIN VEMAEST AS VM ON(VM.VEM_CODVEN=T1.ALL_CODVEN) ";
        $sql.= "INNER JOIN ARTI ART ON(ART.ART_KEY=T2.FAR_CODART) ";
        $sql.= "INNER JOIN TABLAS TB ON(TB.TAB_TIPREG=121 AND TB.TAB_NUMTAB=ART.ART_GRUPOP AND TB.TAB_CODCIA='25') ";
        $sql.= "WHERE ALL_CODCIA in ('25') AND  ";
        $sql.= "ALL_TIPMOV = 10 AND  ";
        $sql.= "ALL_FECHA_PRO >= '".$fecha01."'  AND ";
        $sql.= "ALL_FECHA_PRO <= '".$fecha02."'  AND ";
        $sql.= "TB.TAB_NUMTAB=566 AND ";
        $sql.= "ALL_FLAG_EXT  <> 'E'  ";
        $sql.= "ORDER BY ALL_FECHA_PRO,ALL_NUMOPER,ALL_NUMSER,ALL_NUMFAC ";

//echo $sql; die();
        if($server==2){
            $query =  $this->dbjj->query($sql);
        }elseif($server==3){
            $query =  $this->dbpm->query($sql);
        }else{
            $query =  $this->db->query($sql);
        }
        return $query->getResult();
    }


    public function actualizar_personas($CLIENTE1,$CLIENTE2){
        $sql = 'UPDATE DBO.ALLOG SET ALL_CODCLIE = '.$CLIENTE1;
        $sql.= ' WHERE ALL_CODCLIE = '.$CLIENTE2;
        return $this->db->simpleQuery($sql);
    }

    public function get_comprobantes_dia($fecha, $server){
        $sql = "SELECT TOP 10 ";
        $sql.= "CASE ";
        $sql.= "WHEN ALL_FBG = 'B' THEN 'BO' + RTRIM(ALL_NUMSER) + '-' + CONVERT(VARCHAR, ALL_NUMFAC) ";
        $sql.= "WHEN ALL_FBG = 'G' THEN 'GI' + RTRIM(ALL_NUMSER) + '-' + CONVERT(VARCHAR, ALL_NUMFAC) ";
        $sql.= "WHEN ALL_FBG = 'F' THEN 'FA' + RTRIM(ALL_NUMSER) + '-' + CONVERT(VARCHAR, ALL_NUMFAC) ";
        $sql.= "ELSE ALL_FBG + ALL_NUMSER + '-' + CONVERT(VARCHAR, ALL_NUMFAC) ";
        $sql.= "END AS COMPROBANTE, ";
        $sql.= "ALL_NETO ";
        $sql.= "FROM dbo.ALLOG ";
        $sql.= "WHERE ALL_CODCIA = 25 ";
        $sql.= "AND ALL_TIPMOV = 10 ";
        $sql.= "AND ALL_FECHA_DIA = '$fecha' ";
        $sql.= "AND ALL_FLAG_EXT <> 'E' ";
        $sql.= "AND ALL_CODCLIE <> 0 ";
        $sql.= "ORDER BY ALL_NUMOPER DESC";
        
        if($server==2){
            $query = $this->dbjj->query($sql);
        }elseif($server==3){
            $query = $this->dbpm->query($sql);
        }else{
            $query = $this->db->query($sql);
        }
        return $query->getResult();
    }
}
