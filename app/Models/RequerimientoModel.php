<?php

namespace App\Models;

use CodeIgniter\Model;

class RequerimientoModel extends Model
{
    protected $db;
    protected $dbpm;
    protected $dbjj;
    protected $DBGroup          = 'default';
    protected $table            = 'requerimientos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
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

    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->dbpm = \Config\Database::connect('pmeza');
        $this->dbjj = \Config\Database::connect('juanjuicillo');
    }
    public function listar($local){
        $sql = 'SELECT REQ_LOCAL,REQ_CODART,LTRIM(RTRIM(T2.ART_NOMBRE)) ART_NOMBRE,T3.ARM_STOCK, ';
        $sql.= 'REQ_CANT,REQ_EQUIV,REQ_FECHA,REQ_CODVEN, ';
        $sql.= "REQ_UNIDADES = Stuff((SELECT DISTINCT  ',' + RTRIM(PRE_UNIDAD)+ '|' +CONVERT(varchar, CONVERT(integer, PRE_EQUIV)) AS [text()]
                FROM PRECIOS a where a.PRE_CODART = T2.ART_KEY FOR XML PATH ('')),1,1,'') ";
        $sql.= 'FROM dbo.REQUERIMIENTO as T1 ';
        $sql.= 'INNER JOIN DBO.ARTI AS T2 ON(T1.REQ_CODART=T2.ART_KEY) ';
        $sql.= 'inner join dbo.ARTICULO as T3 on(T1.REQ_CODART=T3.ARM_CODART) ';
        $sql.= 'WHERE 1=1 ';
        $sql.= empty($local)?'and REQ_LOCAL = 0':'and REQ_LOCAL = '.$local ;
        $query =  $this->db->query($sql);
        return $query->getResult();                                                             
    }
    public function agregar($local){
        return $query =  $this->db->table('REQUERIMIENTO')->insert($local);    
    }
    public function existe($local){
        $builder = $this->db->table('REQUERIMIENTO')->selectCount('REQ_LOCAL')->where($local);
        $query   = $builder->get()->getResult();         
        return $query[0]->REQ_LOCAL;    
    }
    public function get_Stock($lc,$id){
        $result = $this->db->table('REQUERIMIENTO')->select(['REQ_CANT','REQ_EQUIV'])->where('REQ_LOCAL', $lc)->where('REQ_CODART', $id);	
        return $result->get()->getRow();
    }
    public function actualizar($ids,$rows){
        $result = $this->db->table('REQUERIMIENTO')->update($rows,$ids);
        return $result;
    }
    public function crea_guia_requerimiento($local) {
        $a="begin
        DECLARE @mensaje varchar(255)        
        EXEC   [dbo].[sp_crea_guias_salida]
              @LOCAL = N'{$local}',
              @mensaje = @mensaje OUTPUT;        
        SELECT @mensaje as N'mensaje'
         end";
        $single_blog = $this->db->query($a)->getRow();

        return $single_blog;
    }
    public function crea_guia_requerimiento_ING($local) {
        $a="begin
        DECLARE @mensaje varchar(255)        
        EXEC   [dbo].[sp_crea_guias_entrada]
              @LOCAL = N'{$local}',
              @mensaje = @mensaje OUTPUT;        
        SELECT @mensaje as N'mensaje'
         end";
        $single_blog = $this->db->query($a)->getRow();

        return $single_blog;
    }
}
