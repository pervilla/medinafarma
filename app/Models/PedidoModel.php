<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Description of PedidoModel
 *
 * @author José Luis
 */
class PedidoModel extends Model {

    var $table = 'pedido';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    
    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    public function get_pedido(){
        $sql = 'SELECT P.*, A.ART_NOMBRE ';
        $sql.= 'FROM dbo.PEDIDO_DETALLE P ';
        $sql.= 'INNER JOIN DBO.ARTI A ON A.ART_KEY = P.cod_prod ';
        $query =  $this->db->query($sql); 
        return $query->getResult();
    }

}