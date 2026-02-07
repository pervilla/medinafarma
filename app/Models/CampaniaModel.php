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
        $sql = "SELECT 
                    T1.CAM_CODCAMP,
                    T1.CAM_CODMED,
                    T2.CLI_NOMBRE,
                    T1.CAM_DESCRIP,
                    T1.CAM_FEC_INI,
                    T1.CAM_FEC_FIN,
                    T1.CAM_HOR_INI,
                    T1.CAM_HOR_FIN,
                    SUM(CASE WHEN T3.CIT_ESTADO >= 0 THEN 1 ELSE 0 END) AS INSCRITOS,
                    SUM(CASE WHEN T3.CIT_ESTADO = 1 THEN 1 ELSE 0 END) AS CONFIRMADOS,
                    SUM(CASE WHEN T3.CIT_ESTADO = 2 THEN 1 ELSE 0 END) AS ATENDIDOS
                FROM dbo.CAMPANIA AS T1 
                INNER JOIN dbo.CLIENTES AS T2 ON (T1.CAM_CODMED = T2.CLI_CODCLIE AND T2.CLI_CP = 'M')
                LEFT JOIN dbo.CITAS AS T3 ON (T3.CIT_CODCAMP = T1.CAM_CODCAMP)
                WHERE 1 = 1 ";

        if (is_null($camp)) {
             $sql .= is_null($all) ? " AND (T1.CAM_FEC_FIN >= GETDATE() or T1.CAM_FEC_FIN = '1969-12-31') " : ' ';
        } else {
            $sql .= " AND T1.CAM_CODCAMP = " . $this->db->escape($camp);
        }

        $sql .= " GROUP BY 
                    T1.CAM_CODCAMP,
                    T1.CAM_CODMED,
                    T2.CLI_NOMBRE,
                    T1.CAM_DESCRIP,
                    T1.CAM_FEC_INI,
                    T1.CAM_FEC_FIN,
                    T1.CAM_HOR_INI,
                    T1.CAM_HOR_FIN
                ORDER BY 
                    -- Primero, mostrar las campañas activas (fecha actual entre inicio y fin)
                    CASE 
                        WHEN T1.CAM_FEC_INI <= GETDATE() AND T1.CAM_FEC_FIN >= GETDATE() 
                        THEN 1 
                        WHEN T1.CAM_FEC_INI = '1969-12-31'  -- Campañas sin fecha definida
                        THEN 3
                        WHEN T1.CAM_FEC_INI > GETDATE()     -- Campañas futuras
                        THEN 2
                        ELSE 4                              -- Campañas pasadas
                    END,
                    -- Para campañas activas, ordenar por fecha de inicio más reciente
                    CASE 
                        WHEN T1.CAM_FEC_INI <= GETDATE() AND T1.CAM_FEC_FIN >= GETDATE() 
                        THEN T1.CAM_FEC_INI 
                        ELSE NULL 
                    END DESC,
                    -- Para campañas sin fecha, ordenar por código
                    CASE 
                        WHEN T1.CAM_FEC_INI = '1969-12-31' 
                        THEN T1.CAM_CODCAMP 
                        ELSE NULL 
                    END DESC,
                    T1.CAM_FEC_INI DESC,
                    T1.CAM_CODCAMP";

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
    public function update_campania($id, $data)
    {
        return $this->db->table('CAMPANIA')->where('CAM_CODCAMP', $id)->update($data);
    }

    public function get_campania_by_id($id)
    {
        return $this->db->table('CAMPANIA')->where('CAM_CODCAMP', $id)->get()->getRow();
    }
}
