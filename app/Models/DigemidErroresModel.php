<?php

namespace App\Models;

use CodeIgniter\Model;

class DigemidErroresModel extends Model
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function marcarError($codProd, $observacion)
    {
        try {
            $sql = "UPDATE PRECIOS_DET_DIGEMID_MEDINA SET ESTADO = 0, OBSERVACION = ? WHERE Cod_Prod = ?";
            $this->db->query($sql, [$observacion, $codProd]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function obtenerErrores()
    {
        $sql = "
            SELECT 
                pd.Cod_Prod,
                pd.PRE_CODART,
                pd.OBSERVACION,
                pd.ESTADO,
                dig.Nom_Prod,
                dig.Concent,
                dig.Nom_Form_Farm,
                art.ART_NOMBRE
            FROM PRECIOS_DET_DIGEMID_MEDINA pd
            INNER JOIN PRECIOS_DIGEMID dig ON dig.Cod_Prod = pd.Cod_Prod
            LEFT JOIN ARTI art ON art.ART_KEY = pd.PRE_CODART AND art.ART_CODCIA = '25'
            WHERE pd.ESTADO = 0 OR pd.OBSERVACION IS NOT NULL
            ORDER BY dig.Nom_Prod
        ";
        
        $query = $this->db->query($sql);
        return $query->getResult();
    }

    public function obtenerSinRelacionar()
    {
        // Redirigir a la función limitada para evitar problemas de memoria
        return $this->obtenerSinRelacionarLimitado();
    }

    public function obtenerSinRelacionarLimitado()
    {
        $sql = "
            SELECT TOP 100
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

    public function actualizarEstado($codProd, $estado, $observacion = null)
    {
        try {
            $sql = "UPDATE PRECIOS_DET_DIGEMID_MEDINA SET ESTADO = ?, OBSERVACION = ? WHERE Cod_Prod = ?";
            $this->db->query($sql, [$estado, $observacion, $codProd]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}