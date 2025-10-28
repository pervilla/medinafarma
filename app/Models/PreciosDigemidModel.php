<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use CodeIgniter\Model;

/**
 * Description of PreciosDigemidModel
 *
 * @author José Luis
 */
class PreciosDigemidModel extends Model {

    var $table = 'precios_digemid';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    
    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function get_precios_digemid($key) {
        $sql = 'SELECT * ';
        $sql .= "FROM dbo.PRECIOS_DIGEMID as T1 ";
        $sql .= "LEFT JOIN PRECIOS_DET_DIGEMID_MEDINA AS T4 ON(T4.Cod_Prod=T1.Cod_Prod) ";
        $sql .= "WHERE T4.PRE_CODART = '" . $key . "'; ";
        $query = $this->db->query($sql);
        return $query->getResult();

    }
}