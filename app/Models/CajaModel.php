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
class CajaModel  extends Model{
    var $table = 'caja';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->dbpm = \Config\Database::connect('pmeza');
        $this->dbjj = \Config\Database::connect('juanjuicillo');
    }
    public function get_ventas_dia($dia,$mes,$anio,$cajanro,$server){
        $sql = 'SELECT CAJ_NRO,CAJ_CODVEN,VEM_NOMBRE,DAY(CAJ_FECHA) DIA,CAJ_EFECTIVO, CAJ_ESTADO,CAJ_NUMSER,CAJ_NUMFAC,CAJ_FECHA ';
        $sql.= 'FROM dbo.CAJAS ';
        $sql.= 'INNER JOIN dbo.VEMAEST ON(CAJ_CODVEN=VEM_CODVEN) ';
        $sql.= 'WHERE 1=1 ';
        $sql.= empty($dia)?'':'and DAY(CAJ_FECHA) = \''.$dia.'\' ';
        $sql.= empty($mes)?'':'and MONTH(CAJ_FECHA) = \''.$mes.'\' ';
        $sql.= empty($anio)?'':'and YEAR(CAJ_FECHA) = \''.$anio.'\' ';
        $sql.= empty($cajanro)?'':'and CAJ_NRO = '.$cajanro;
        //echo $sql; die();
        if($server==2){
            $query =  $this->dbjj->query($sql);
        }elseif($server==3){
            $query =  $this->dbpm->query($sql);
        }elseif($server==1){
            $query =  $this->db->query($sql);
        }
        
        return $query->getResult();
    }
    public function get_ventas_dia_det($dia,$mes,$anio,$nrocaja,$server,$isadmin){
        $sql = 'SELECT ALL_FECHA_DIA,ALL_CAJA_NRO,';
        $sql.= '(SELECT VEM_NOMBRE FROM CAJAS INNER JOIN VEMAEST ON CAJ_CODVEN=VEM_CODVEN WHERE CAJ_NRO = ALL_CAJA_NRO) VEM_NOMBRE,';
        $sql.= 'SUM(ALL_IMPORTE_AMORT) AS TOT_VENTAS,';
        $sql.= '(SELECT SUM(CAJA_MOVIMIENTOS.CMV_MONTO) FROM DBO.CAJA_MOVIMIENTOS WHERE CAJA_MOVIMIENTOS.CMV_CAJA = ALLOG.ALL_CAJA_NRO) AS TOT_MOVIM,';
        $sql.= '(SELECT CAJAS.CAJ_EFECTIVO FROM DBO.CAJAS WHERE CAJAS.CAJ_NRO = ALLOG.ALL_CAJA_NRO) AS TOT_EFECTIVO ';
        $sql.= 'FROM DBO.ALLOG ';
        $sql.= 'WHERE ';
        $sql.= empty($dia)?'':'DAY(ALL_FECHA_DIA) = \''.$dia.'\'  AND ';
        $sql.= empty($mes)?'':'MONTH(ALL_FECHA_DIA) = \''.$mes.'\'  AND ';
        $sql.= empty($anio)?'':'YEAR(ALL_FECHA_DIA) = \''.$anio.'\' AND  ';
        $sql.= empty($nrocaja)?'':'ALL_CAJA_NRO = '.$nrocaja.' AND  ';
        $sql.= 'ALL_TIPMOV = 10 AND ';
        $sql.= 'ALL_SIGNO_CAJA = 1 AND ';
        $sql.= "all_flag_ext <> 'E' AND ";
        $sql.= $isadmin?'':'ALL_CAJA_NRO > 0 AND ';
        $sql.= '(ALL_CODTRA <> 1111 OR ALL_CODTRA <> 1122) ';
        $sql.= 'GROUP BY ALL_FECHA_DIA,ALL_CAJA_NRO ';
        $sql.= 'ORDER BY ALL_FECHA_DIA,ALL_CAJA_NRO;';
//echo $sql; die();
        if($server==2){
            $query =  $this->dbjj->query($sql);
        }elseif($server==3){
            $query =  $this->dbpm->query($sql);
        }elseif($server==1){
            $query =  $this->db->query($sql);
        }
        return $query->getResult();
    }
    public function get_caja($dia,$mes,$anio,$estado,$caja,$cajanro,$server){
        $sql = 'SELECT TOP 1 CAJ_NRO,CAJ_CODVEN,CAJ_FECHA,CAJ_NUMOPER, ';
        $sql.= 'CAJ_NUMSER, CAJ_NUMFAC, CAJ_EFECTIVO, CAJ_ESTADO ';
        $sql.= 'FROM dbo.CAJAS ';
        $sql.= 'WHERE ';
        $sql.= '1 = 1 ';
        $sql.= empty($estado)?'':' AND CAJ_ESTADO = '.$estado;
        $sql.= empty($caja)?'':' AND CAJ_NRO < '.$caja;
        $sql.= empty($cajanro)?'':' AND CAJ_NRO = '.$cajanro;
        $sql.= empty($dia)?'':' AND DAY(CAJ_FECHA) = \''.$dia.'\' ';
        $sql.= empty($mes)?'':' AND MONTH(CAJ_FECHA) = \''.$mes.'\' ';
        $sql.= empty($anio)?'':' AND YEAR(CAJ_FECHA) = \''.$anio.'\' ';
        
        $sql.= ' ORDER BY CAJ_NRO DESC ';
        //echo $sql; die();
        if($server==2){
            $query =  $this->dbjj->query($sql);
        }elseif($server==3){           
            $query =  $this->dbpm->query($sql);
        }elseif($server==1){
            $query =  $this->db->query($sql);
        }
        return $query->getResult();
    }
    public function get_caja_anterior($fecha,$nrooper,$server){
        $sql = 'SELECT TOP 1 CAJ_NRO,CAJ_CODVEN,CAJ_FECHA,CAJ_NUMOPER, ';
        $sql.= 'CAJ_NUMSER, CAJ_NUMFAC, CAJ_EFECTIVO, CAJ_ESTADO ';
        $sql.= 'FROM dbo.CAJAS ';
        $sql.= 'WHERE ';
        $sql.= empty($fecha)?'':" CAJ_FECHA = '".$fecha."' ";
        $sql.= empty($nrooper)?'':" AND CAJ_NUMOPER < ".$nrooper;        
        $sql.= ' ORDER BY CAJ_NUMOPER DESC ';

        if($server==2){
            $query =  $this->dbjj->query($sql);
        }elseif($server==3){           
            $query =  $this->dbpm->query($sql);
        }elseif($server==1){
            $query =  $this->db->query($sql);
        }
        return $query->getRow();
    }


    public function verificar_caja_abierta($fecha, $server){
        $sql = "SELECT COUNT(*) as total FROM dbo.CAJAS WHERE CAJ_FECHA = '".$fecha."' AND CAJ_ESTADO = 1";
        
        if($server==2){
            $query = $this->dbjj->query($sql);
        }elseif($server==3){
            $query = $this->dbpm->query($sql);
        }elseif($server==1){
            $query = $this->db->query($sql);
        }
        
        $result = $query->getRow();
        return $result->total > 0;
    }

    public function crear_caja($data,$server){
       
        if($server==2){
            return $query =  $this->dbjj->table('CAJAS')->insert($data);
        }elseif($server==3){
            
            return $query =  $this->dbpm->table('CAJAS')->insert($data);
        }elseif($server==1){
            return $query =  $this->db->table('CAJAS')->insert($data);
        }
    }
    public function cerrar_caja($oper,$nroser,$nrofac,$efec,$caja,$server){
        $sql = 'UPDATE DBO.CAJAS SET ';
        $sql.= ' CAJ_NUMOPER = '.$oper ;
        $sql.= ',CAJ_NUMSER = '.$nroser;
        $sql.= ",CAJ_NUMFAC = ".$nrofac;
        $sql.= ',CAJ_EFECTIVO = '.$efec;
        $sql.= ',CAJ_ESTADO = 0';
        $sql.= " WHERE CAJ_NRO = ".$caja;
        if($server==2){
            return $query =  $this->dbjj->simpleQuery($sql);
        }elseif($server==3){
            return $query =  $this->dbpm->simpleQuery($sql);
        }elseif($server==1){
            return $query =  $this->db->simpleQuery($sql);
        }
    }
    public function editar_caja($caja,$server){
        $sql = 'UPDATE DBO.CAJAS SET ';
        $sql.= ' CAJ_ESTADO = 1';
        $sql.= " WHERE CAJ_NRO = ".$caja;
        //echo $sql; die();
        if($server==2){
            return $query =  $this->dbjj->simpleQuery($sql);
        }elseif($server==3){
            return $query =  $this->dbpm->simpleQuery($sql);
        }elseif($server==1){
            return $query =  $this->db->simpleQuery($sql);
        }
    }
    public function bloquea_caja($caja,$server){
        $sql = 'UPDATE DBO.CAJAS SET ';
        $sql.= ' CAJ_ESTADO = 0';
        $sql.= " WHERE CAJ_NRO = ".$caja;
        //echo $sql; die();
        if($server==2){
            return $query =  $this->dbjj->simpleQuery($sql);
        }elseif($server==3){
            return $query =  $this->dbpm->simpleQuery($sql);
        }elseif($server==1){
            return $query =  $this->db->simpleQuery($sql);
        }
    }
    public function editar_caja2($resp,$caja,$server){
        $sql = 'UPDATE DBO.CAJAS SET ';
        $sql.= ' CAJ_CODVEN = '.$resp;
        $sql.= " WHERE CAJ_NRO = ".$caja;
        //echo $sql; die();
        if($server==2){
            return $query =  $this->dbjj->simpleQuery($sql);
        }elseif($server==3){
            return $query =  $this->dbpm->simpleQuery($sql);
        }elseif($server==1){
            return $query =  $this->db->simpleQuery($sql);
        }
    }
    public function editar_caja3($efec,$caja,$server){
        $sql = 'UPDATE DBO.CAJAS SET ';
        $sql.= ' CAJ_EFECTIVO = '.$efec;
        $sql.= " WHERE CAJ_NRO = ".$caja;
        //echo $sql; die();
        if($server==2){
            return $query =  $this->dbjj->simpleQuery($sql);
        }elseif($server==3){
            return $query =  $this->dbpm->simpleQuery($sql);
        }elseif($server==1){
            return $query =  $this->db->simpleQuery($sql);
        }
    }
    public function eliminar_caja($id,$server){
        if($server==2){
            $builder =  $this->dbjj->table('CAJAS');
        }elseif($server==3){
            $builder =  $this->dbpm->table('CAJAS');
        }else{
            $builder =  $this->db->table('CAJAS');
        } 
    return $builder->delete(['CAJ_NRO' => $id]);
    }
}
