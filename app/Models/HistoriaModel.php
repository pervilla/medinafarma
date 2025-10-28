<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Description of HistoriaModel
 *
 * @author José Luis
 */
class HistoriaModel extends Model {

    var $table = 'historia';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    
    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    public function get_historia($hist){
        $sql = 'SELECT * ';
        $sql.= 'FROM dbo.HISTORIA AS T1 ';
        $sql.= "INNER JOIN dbo.CAMPANIA AS T2 ON(T1.HIS_CODCAMP=T2.CAM_CODCAMP ) ";
        $sql.= 'WHERE ';
        $sql.= 'HIS_CODHIS = '.$hist;
        $query =  $this->db->query($sql);       
        
        return $query->getResult();
    }
    public function get_historias($cli){
        $sql = 'SELECT HIS_CODHIS,HIS_CODCIT,HIS_CODCLI,HIS_CODCAMP,CAM_DESCRIP,CAM_FEC_INI ';
        $sql.= 'FROM dbo.HISTORIA as t1 ';
        $sql.= "INNER JOIN dbo.CAMPANIA AS T2 ON(T1.HIS_CODCAMP=T2.CAM_CODCAMP ) ";
        $sql.= 'WHERE ';
        $sql.= 'HIS_CODCLI = '.$cli;
        $sql.= ' ORDER BY HIS_CODHIS DESC';
        $query =  $this->db->query($sql);       
        
        return $query->getResult();
    }
    public function get_cie_descripcion($cie,$dsc){
        $sql = 'SELECT top 100 HISC_CIE_CODIGO as id, HISC_CIE_DESCRIPCION as text ';
        $sql.= 'FROM dbo.HISTORIA_CIE10 ';
        $sql.= 'WHERE 1=1 ';
        $sql.= empty($cie)?"":"AND HISC_CIE_CODIGO = '$cie' ";
        $sql.= empty($dsc)?"":"AND HISC_CIE_DESCRIPCION like '%$dsc%' ";
        $query =  $this->db->query($sql);       
        
        return $query->getResult();
    }
    public function get_nro_historia($cita,$cli,$camp){
        $sql = ' SELECT HIS_CODHIS FROM dbo.HISTORIA ';
        $sql.= "WHERE HIS_CODCIT = $cita ";
        $sql.= "AND HIS_CODCLI = $cli ";
        $sql.= "AND HIS_CODCAMP = $camp ";




        //echo $sql; die();
        $query =  $this->db->query($sql);


        if(is_null($query->getRow())){
            return false;
            
        }else{
            return $query->getRow()->HIS_CODHIS;
        }
        
    }

    public function set_historia($data){ 
        $Historia =  $this->db->table('historia')->insert($data);
        return $Historia;
    }

    public function upd_historia($id,$update_rows){
		$result = $this->db->table('HISTORIA')->update($update_rows,"HIS_CODHIS = $id");	
		return $result;
    }
    public function get_historia_diag($id){
        $sql = 'SELECT * ';
        $sql.= 'FROM dbo.HISTORIA_DIAGNOSTICO ';
        $sql.= 'WHERE ';
        $sql.= "HISD_CODHIS =".$id;
        $query =  $this->db->query($sql);       
        
        return $query->getResult();
    }    
    public function set_historia_diag($data){ 
        $Historia =  $this->db->table('HISTORIA_DIAGNOSTICO')->insert($data);
        return $Historia;
    }
    public function upd_historia_diag($id,$cd,$update_rows){
		$result = $this->db->table('HISTORIA_DIAGNOSTICO')->update($update_rows, ['HISD_CODHIS' => $id,'HISD_CIE_CODIGO'=>$cd]);	
		return $result;
    }
    public function set_historia_receta($data){ 
        $Historia =  $this->db->table('HISTORIA_RECETA')->insert($data);
        return $Historia;
    }
    public function upd_historia_receta($update_rows){
		$result = $this->db->table('HISTORIA_RECETA')->update($update_rows, ['HISR_CODHIS' => $update_rows['HISR_CODHIS'],'HISR_CODART' => $update_rows['HISR_CODART']]);	
		return $result;
    }
    public function delete_historia_receta($his,$car){
        $builder =  $this->db->table('HISTORIA_RECETA');
        return $builder->delete(['HISR_CODHIS' => $his,'HISR_CODART' => $car]);
    }
    public function delete_cie($his,$cie){
        $builder =  $this->db->table('HISTORIA_DIAGNOSTICO');
        return $builder->delete(['HISD_CODHIS' => $his,'HISD_CIE_CODIGO' => $cie]);
    }
    

    public function get_historia_receta($id){
        $sql = 'SELECT * ';
        $sql.= 'FROM dbo.HISTORIA_RECETA ';
        $sql.= 'WHERE ';
        $sql.= "HISR_CODHIS =".$id;
        $query =  $this->db->query($sql);
        return $query->getResult();
    }  
    public function actualizar_personas($CLIENTE1,$CLIENTE2){
        $sql = 'UPDATE DBO.HISTORIA SET HIS_CODCLI = '.$CLIENTE1;
        $sql.= ' WHERE HIS_CODCLI = '.$CLIENTE2;
        return $this->db->simpleQuery($sql);
    }
}