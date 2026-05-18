<?php

namespace App\Controllers;

use Config\Database;

class DebugDigemid extends BaseController
{
    public function index()
    {
        $db = Database::connect();
        $busqueda = 'MEDICORT';
        
        $sql = "
            SELECT 
                a.ART_KEY, 
                a.ART_NOMBRE, 
                a.ART_SITUACION, 
                a.ART_CODCIA,
                s.ARM_STOCK,
                det.Cod_Prod as Relacionado_CodProd,
                det.ESTADO as Relacionado_Estado
            FROM ARTI a
            INNER JOIN ARTICULO s ON s.ARM_CODART = a.ART_KEY AND s.ARM_CODCIA = '25'
            LEFT JOIN PRECIOS_DET_DIGEMID_MEDINA det ON det.PRE_CODART = a.ART_KEY
            WHERE a.ART_NOMBRE LIKE '%$busqueda%'
            AND a.ART_CODCIA = '25'
        ";
        
        $query = $db->query($sql);
        $results = $query->getResult();
        
        echo "<pre>";
        print_r($results);
        echo "</pre>";
    }
}
