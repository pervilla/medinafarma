<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use CodeIgniter\Model;
/**
 * Description of Tablas_model
 *
 * @author José Luis
 */
class TablasModel extends Model {
    var $table = 'tablas';     
    protected $db;
    protected $dbpm;
    protected $dbjj;
    public function __construct() {
        parent::__construct();
        $db = \Config\Database::connect();
    }
    public function get_by_tipreg($id,$art) {
      $sql = 'select * from TABLAS where ';
      $sql.= 'TAB_TIPREG ='.$id;
      $sql.= empty($art)?"":' AND TAB_CODART ='.$art;
      $sql.= " AND RTRIM(TAB_NOMLARGO) <> ''";
      $sql.= ' ORDER BY TAB_NOMLARGO ';
      $query =  $this->db->query($sql);
      return $query->getResult();
    }
}
