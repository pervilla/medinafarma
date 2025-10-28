<?php

namespace App\Models;

use CodeIgniter\Model;

class DigemidModel extends Model
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function obtenerPrecios($codEstab)
    {
        $sql = "";
        
        if ($codEstab == '0060004') {
            // Query para CENTRO
            $sql = "
                SELECT 
                '0060004' AS CodEstab,
                CONVERT(VARCHAR, T1.Cod_Prod, 1) AS CodProd,
                CONVERT(VARCHAR, CAST(MAX(PRE_PRE1) AS MONEY), 1) AS Precio1,
                CONVERT(VARCHAR, CAST(MIN(PRE_PRE1) AS MONEY), 1) AS Precio2
                FROM dbo.PRECIOS_DIGEMID as T1
                LEFT JOIN PRECIOS_DET_DIGEMID_MEDINA AS T4 ON(T4.Cod_Prod=T1.Cod_Prod)
                INNER JOIN ARTI AS T2 ON(T2.ART_KEY = T4.PRE_CODART AND T2.ART_CODCIA = '25') 
                INNER JOIN ARTICULO T5 ON(T5.ARM_CODART=T4.PRE_CODART AND T5.ARM_CODCIA = '25')
                LEFT JOIN PRECIOS AS T3 ON(T3.PRE_CODART = T4.PRE_CODART AND T3.PRE_CODCIA = '25')
                WHERE T5.ARM_STOCK > 0 AND T4.ESTADO = 1
                GROUP BY T1.Cod_Prod, T3.PRE_CODART, T2.ART_NOMBRE, T1.Nom_Prod, Concent,	
                Nom_Form_Farm, Presentac, Nom_Titular, Fracciones
            ";
        } elseif ($codEstab == '0042586') {
            // Query para JUANJUICILLO
            $sql = "
                SELECT 
                '0042586' AS CodEstab,
                CONVERT(VARCHAR, T1.Cod_Prod, 1) AS CodProd,
                CONVERT(VARCHAR, CAST(MAX(PRE_PRE1) AS MONEY), 1) AS Precio1,
                CONVERT(VARCHAR, CAST(MIN(PRE_PRE1) AS MONEY), 1) AS Precio2
                FROM dbo.PRECIOS_DIGEMID as T1
                LEFT JOIN PRECIOS_DET_DIGEMID_MEDINA AS T4 ON(T4.Cod_Prod=T1.Cod_Prod)
                INNER JOIN [SERVER02].[BDATOS].[dbo].ARTI AS T2 ON(T2.ART_KEY = T4.PRE_CODART AND T2.ART_CODCIA = '25') 
                INNER JOIN [SERVER02].[BDATOS].[dbo].ARTICULO T5 ON(T5.ARM_CODART=T4.PRE_CODART AND T5.ARM_CODCIA = '25')
                LEFT JOIN [SERVER02].[BDATOS].[dbo].PRECIOS AS T3 ON(T3.PRE_CODART = T4.PRE_CODART AND T3.PRE_CODCIA = '25')
                WHERE T5.ARM_STOCK > 0 AND T4.ESTADO = 1
                GROUP BY T1.Cod_Prod, T3.PRE_CODART, T2.ART_NOMBRE, T1.Nom_Prod, Concent,	
                Nom_Form_Farm, Presentac, Nom_Titular, Fracciones
            ";
        }
        
        if (!empty($sql)) {
            $query = $this->db->query($sql);
            return $query->getResult();
        }
        
        return [];
    }
}