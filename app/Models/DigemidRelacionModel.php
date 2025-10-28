<?php

namespace App\Models;

use CodeIgniter\Model;

class DigemidRelacionModel extends Model
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function buscarProductosDigemid($termino)
    {
        $sql = "
            SELECT TOP 20 
                Cod_Prod, 
                Nom_Prod, 
                Concent, 
                Nom_Form_Farm, 
                Presentac,
                Nom_Titular
            FROM PRECIOS_DIGEMID 
            WHERE Nom_Prod LIKE ? 
            AND Situacion = 'ACT'
            ORDER BY Nom_Prod
        ";
        
        $query = $this->db->query($sql, ["%{$termino}%"]);
        return $query->getResult();
    }

    public function buscarArticulos($termino)
    {
        $sql = "
            SELECT TOP 20 
                ART_KEY, 
                ART_NOMBRE
            FROM ARTI 
            WHERE ART_NOMBRE LIKE ? 
            AND ART_SITUACION = 0 
            AND ART_CODCIA = '25'
            ORDER BY ART_NOMBRE
        ";
        
        $query = $this->db->query($sql, ["%{$termino}%"]);
        return $query->getResult();
    }

    public function crearRelacion($codProd, $preCodeart)
    {
        try {
            // Verificar si ya existe la relación
            $existe = $this->db->query("SELECT COUNT(*) as total FROM PRECIOS_DET_DIGEMID_MEDINA WHERE Cod_Prod = ?", [$codProd])->getRow();
            
            if ($existe->total > 0) {
                // Actualizar
                $sql = "UPDATE PRECIOS_DET_DIGEMID_MEDINA SET PRE_CODART = ? WHERE Cod_Prod = ?";
                $this->db->query($sql, [$preCodeart, $codProd]);
            } else {
                // Insertar
                $sql = "INSERT INTO PRECIOS_DET_DIGEMID_MEDINA (PRE_CODART, Cod_Prod) VALUES (?, ?)";
                $this->db->query($sql, [$preCodeart, $codProd]);
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function eliminarRelacion($codProd)
    {
        try {
            $sql = "DELETE FROM PRECIOS_DET_DIGEMID_MEDINA WHERE Cod_Prod = ?";
            $this->db->query($sql, [$codProd]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function obtenerRelacionados()
    {
        $sql = "
            SELECT 
                pd.Cod_Prod,
                pd.PRE_CODART,
                dig.Nom_Prod,
                dig.Concent,
                dig.Nom_Form_Farm,
                art.ART_NOMBRE
            FROM PRECIOS_DET_DIGEMID_MEDINA pd
            INNER JOIN PRECIOS_DIGEMID dig ON dig.Cod_Prod = pd.Cod_Prod
            INNER JOIN ARTI art ON art.ART_KEY = pd.PRE_CODART AND art.ART_CODCIA = '25'
            ORDER BY dig.Nom_Prod
        ";
        
        $query = $this->db->query($sql);
        return $query->getResult();
    }

    public function obtenerSinRelacionarLimitado()
    {
        $sql = "
            SELECT TOP 50
                dig.Cod_Prod,
                dig.Nom_Prod,
                dig.Concent,
                dig.Nom_Form_Farm,
                dig.Presentac,
                dig.Nom_Titular
            FROM PRECIOS_DIGEMID dig
            LEFT JOIN PRECIOS_DET_DIGEMID_MEDINA pd ON pd.Cod_Prod = dig.Cod_Prod
            WHERE pd.Cod_Prod IS NULL 
            AND dig.Situacion = 'ACT'
            ORDER BY dig.Nom_Prod
        ";
        
        $query = $this->db->query($sql);
        return $query->getResult();
    }
}