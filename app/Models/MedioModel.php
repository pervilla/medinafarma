<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Description of CitasModel
 *
 * @author José Luis
 */
class MedioModel extends Model {

    var $table = 'medio_publicidad';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    
    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    public function get_medios(){
        $sql = 'SELECT * ';
        $sql.= 'FROM dbo.MEDIO_PUBLICIDAD ';
        $query =  $this->db->query($sql); 
        return $query->getResult();
    }

}