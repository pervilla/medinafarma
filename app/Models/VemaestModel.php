<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Description of VemaestModel
 *
 * @author José Luis
 */
class VemaestModel extends Model {

    var $table = 'tablas';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function get_empleado($emp) {
        $sql = 'select VEM_CODVEN, VEM_NOMBRE from VEMAEST where';
        $sql.= " VEM_SISTEMA <> 'A' AND VEM_CODCIA = '25' AND VEM_CODVEN <> '0' ";
        $sql.= empty($emp)?'':'AND VEM_CODVEN = ' . $emp ;
        $query = $this->db->query($sql);
        return $query->getResult();
    }

}
