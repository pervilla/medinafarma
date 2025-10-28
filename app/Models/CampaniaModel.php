<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Description of CampaniaModel
 *
 * @author José Luis
 */
class CampaniaModel extends Model
{

    var $table = 'campania';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    public function get_campanias($camp = null, $all = null)
    {
        $sql = 'SELECT T1.CAM_CODCAMP,T1.CAM_CODMED,T2.CLI_NOMBRE,T1.CAM_DESCRIP,T1.CAM_FEC_INI,T1.CAM_FEC_FIN,T1.CAM_HOR_INI,T1.CAM_HOR_FIN, ';
        $sql .= 'SUM(CASE WHEN T3.CIT_ESTADO >= 0 THEN 1 ELSE 0 END) AS INSCRITOS, ';
        $sql .= 'SUM(CASE WHEN T3.CIT_ESTADO = 1 THEN 1 ELSE 0 END) AS CONFIRMADOS, ';
        $sql .= 'SUM(CASE WHEN T3.CIT_ESTADO = 2 THEN 1 ELSE 0 END) AS ATENDIDOS ';
        $sql .= 'FROM dbo.CAMPANIA AS T1 ';
        $sql .= "INNER JOIN dbo.CLIENTES AS T2 ON(T1.CAM_CODMED=T2.CLI_CODCLIE AND CLI_CP='M') ";
        $sql .= 'LEFT JOIN dbo.CITAS AS T3 ON(T3.CIT_CODCAMP=T1.CAM_CODCAMP) ';
        $sql .= 'WHERE ';
        $sql .= is_null($all) ? '(CAM_FEC_FIN >= \'' . date('d/m/Y') . '\' or ' . "CAM_FEC_FIN = '31/12/1969') " : ' 1=1 ';
        $sql .= is_null($camp) ? ' ' : 'and T1.CAM_CODCAMP =' . $camp . ' ';
        $sql .= 'GROUP BY T1.CAM_CODCAMP,T1.CAM_CODMED,T2.CLI_NOMBRE,T1.CAM_DESCRIP,T1.CAM_FEC_INI,T1.CAM_FEC_FIN,T1.CAM_HOR_INI,T1.CAM_HOR_FIN ';
        $sql .= 'ORDER BY T1.CAM_FEC_INI desc, T1.CAM_CODCAMP ,T1.CAM_CODMED,T2.CLI_NOMBRE,T1.CAM_DESCRIP,T1.CAM_FEC_FIN,T1.CAM_HOR_INI,T1.CAM_HOR_FIN ';
        //echo $sql; die();
        $query =  $this->db->query($sql);
        return $query->getResult();
    }
    public function crear_campania($data)
    {
        //var_export($data); die();
        return $query =  $this->db->table('CAMPANIA')->insert($data);
    }

    public function get_tipos_campanias(){
        $sql = 'SELECT * ';
        $sql .= 'FROM dbo.CAMPANIA_TIPO';
        $query =  $this->db->query($sql);
        return $query->getResult();
    }
}
