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
class LoteModel extends Model {

    var $table = 'lote';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    
    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function set_Saldo($key,$cant) {        
        $sql = 'UPDATE DBO.LOTE SET ';
        $sql .= ' LOT_SALDOS = LOT_SALDOS + ' . $cant;
        $sql .= " WHERE LOT_CODART = " . $key;
        return $this->db->simpleQuery($sql);
    }
    public function get_lotes($key) {
        $sql = 'SELECT LOT_CODCIA,LOT_CODART,LOT_NROLOTE,LOT_CODCLIE,LOT_FECHA_vcto,0 as LOT_SALDOS, LOT_CODUSU  ';
        $sql .= "FROM dbo.LOTE ";
        $sql .= "WHERE LOT_CODART > " . $key;
        $query = $this->db->query($sql);
        return $query->getResult();
    }



}
