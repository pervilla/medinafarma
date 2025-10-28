<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;
class EnfermedadesModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'enfermedades';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected $db;
    protected $dbpm;
    protected $dbjj;

    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->dbpm = \Config\Database::connect('pmeza');
        $this->dbjj = \Config\Database::connect('juanjuicillo');
    }

    public function get_enfermedades(){
        $sql = 'SELECT * ';
        $sql.= "FROM dbo.enfermedades ";
        $query =  $this->db->query($sql);
        return $query->getResult();
    }
    public function get_sistema(){
        $sql = 'SELECT * ';
        $sql.= "FROM dbo.enfermedades_sistema ";
        $query =  $this->db->query($sql);
        return $query->getResult();
    }

    public function get_exist_pa($data){       
        $builder = $this->db->table('TRATAMIENTO')
                    ->where($data)
                    ->selectCount('ID_ENFERMEDAD')
                    ->get()
                    ->getRow();
        return $builder;
    }
    public function get_medicamentos($id_enfermedad){
        $sql = 'SELECT T1.CODART, RTRIM(T2.ART_NOMBRE) ART_NOMBRE,RTRIM(T3.DESCRIPCION) DESCRIPCION,RTRIM(T4.TAB_NOMLARGO) TAB_NOMLARGO ';
        $sql.= "FROM DBO.TRATAMIENTO AS T1 ";
        $sql.= "INNER JOIN DBO.ARTI AS T2 ON(T1.TAB_NUMTAB=T2.ART_SUBGRU AND T2.ART_CODCIA=25) ";        
        $sql.= "LEFT JOIN dbo.POSOLOGIA AS T3 ON(T2.ART_KEY=T3.CODART) ";
        $sql.= "INNER JOIN DBO.TABLAS AS T4 ON(T1.TAB_NUMTAB=T4.TAB_NUMTAB AND T4.TAB_TIPREG=129) ";
        $sql.= "WHERE T1.ID_ENFERMEDAD = ".$id_enfermedad;

        //echo $sql;
        $query =  $this->db->query($sql);
        return $query->getResult();
    }

    public function insertar($data){     
        $this->db->table('enfermedades')->insert($data);     
        $insert_id = $this->db->insertID();   
        return $insert_id;
    }
    public function insertar_pa($data){     
        return $this->db->table('TRATAMIENTO')->insert($data);        
    }

    public function insertar_keys($data){
        return $this->db->table('enfermedades_keywords')->insertBatch($data); 
    }




}
