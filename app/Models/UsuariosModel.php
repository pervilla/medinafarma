<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use CodeIgniter\Model;
/**
 * Description of Usuarios_model
 *
 * @author José Luis
 */
class UsuariosModel extends Model {
    var $table = 'Usuarios';   
    protected $db;
    protected $dbpm;
    protected $dbjj;  
    public function __construct() {
        parent::__construct();
        $db = \Config\Database::connect();
    }
    public function get_by_tipreg($id,$art) {
      $sql = 'select * from Usuarios where TAB_TIPREG ='.$id.' and TAB_CODART ='.$art;
      $query =  $this->db->query($sql);
      return $query->getResult();
    }
    public function get_usuario($user,$pwd){
        $sql = "select USU_NOMBRE from Usuarios where USU_KEY ='".$user."'";
        $sql.= empty($pwd)?"":" and USU_CLAVE ='".$pwd."'";
        $query =  $this->db->query($sql);
        $ret = $query->getRow();
        return $ret->USU_NOMBRE;
    }

}