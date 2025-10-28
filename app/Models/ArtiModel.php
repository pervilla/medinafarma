<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use CodeIgniter\Model;
/**
 * Description of ArtiModel
 *
 * @author José Luis
 */
class ArtiModel extends Model {
 
    var $table = 'arti';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->dbpm = \Config\Database::connect('pmeza');
        $this->dbjj = \Config\Database::connect('juanjuicillo');
    }
    public function get_operacion($mov,$tipo,$ser,$fac,$fecha,$server) {
       $sql = 'SELECT ';
        $sql.= 'FAR_CODART,FAR_TIPMOV,';
        $sql.= 'RTRIM(ART_NOMBRE)ART_NOMBRE, ';
        $sql.= 'RTRIM(FAR_DESCRI)FAR_DESCRI, ';
        $sql.= 'FAR_PRECIO,FAR_EQUIV, ';
        $sql.= 'CAST(FAR_CANTIDAD/FAR_EQUIV AS DECIMAL(6,2)) FAR_CANTIDAD, ';
        $sql.= 'FAR_SUBTOTAL, ';
        $sql.= "CAST(PRE_PRE1 AS DECIMAL(6,2)) PRE_PRE1, ";
        $sql.= "ISNULL(ROUND((PRE_PRE1*100/NULLIF(FAR_PRECIO,0))-100,2,0),0) MARGEN ";
        $sql.= "FROM FACART AS T1 ";
        $sql.= "INNER JOIN ARTI AS T2 ON(T1.FAR_CODART=T2.ART_KEY) ";
        $sql.= "INNER JOIN PRECIOS AS T3 ON(T1.FAR_CODART=T3.PRE_CODART AND T1.FAR_EQUIV=T3.PRE_EQUIV) ";
        $sql.= "WHERE ";
        $sql.= "FAR_TIPMOV = $mov AND ";
        $sql.= "FAR_CODCIA = 25 AND ";
        $sql.= "FAR_NUMSER = $ser AND ";
        $sql.= "FAR_NUMFAC =$fac AND ";
        $sql.= "FAR_FECHA = '$fecha' ";
        $sql.= 'ORDER BY FAR_NUMSEC ';
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
    public function get_arti($key){
        $sql = "SELECT ART_KEY,ART_CODCIA,ART_NOMBRE,ART_COSTO,ART_TIPO,ART_ESTADO,ART_CALIDAD,ART_UNIDAD,";
        $sql.= "ART_EX_IGV,ART_DECIMALES,ART_FAMILIA,ART_ALTERNO,ART_CP,ART_FLAG_STOCK,ART_FLAG_CAMBIO,";
        $sql.= "ART_FECHAHORA,ART_GRUPOP,ART_CODREL ";
        $sql.= "FROM dbo.ARTI ";
        $sql.= "WHERE ART_KEY > ".$key;
        $query =  $this->db->query($sql);
        return $query->getResult();
    }
    public function get_arti_act($key){
        $sql = "SELECT COUNT(ART_KEY) ART_KEY FROM DBO.ARTI WHERE ART_KEY > $key;";
        $query =  $this->db->query($sql);
        $row   = $query->getRow();
        return $row->ART_KEY;
       
    }

}
