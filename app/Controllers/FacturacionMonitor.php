<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class FacturacionMonitor extends BaseController
{
    public function index()
    {
        $db = Database::connect();
        
        // Retrieve summaries and incidents
        // We filter out 'ER' from the main summaries list if we want to separate them, or keep them.
        // Let's create a separate list for "Incidencias" (Type ER)
        
        $sqlIncidencias = "SELECT TOP 50 * FROM DeclaracionDocumentoElectronico WHERE TipoDocumento = 'ER' ORDER BY Fecha DESC, iD DESC";
        $incidencias = $db->query($sqlIncidencias)->getResultArray();
        
        // Summaries exclude invoices errors to keep it clean? Or show all logs?
        // Let's show RC/RA in summaries.
        $sqlSummaries = "SELECT TOP 100 * FROM DeclaracionDocumentoElectronico WHERE TipoDocumento IN ('RC', 'RA') ORDER BY Fecha DESC, iD DESC";
        $summaries = $db->query($sqlSummaries)->getResultArray();
        
        // Retrieve individual invoices from ALLOG
        $sqlInvoices = "SELECT TOP 100
                            CONVERT(VARCHAR(10), ALL_FECHA_DIA, 103) as Fecha,
                            CASE WHEN ALL_FBG='F' THEN 'FA' ELSE 'BO' END + 
                                SUBSTRING(LTRIM(RTRIM(CONVERT(VARCHAR, ALL_NUMSER))), LEN(LTRIM(RTRIM(CONVERT(VARCHAR, ALL_NUMSER))))-1, 2) as Serie,
                            LTRIM(RTRIM(CONVERT(VARCHAR, ALL_NUMFAC))) as Numero,
                            CASE WHEN ALL_FBG='F' THEN '01' ELSE '03' END as TipoDoc,
                            ALL_NETO as Total,
                            ALL_ESTADO_FE as Estado,
                            ALL_CODCIA as CodCia,
                            ALL_NUMSER as NumSerRaw,
                            ALL_NUMFAC as NumFacRaw
                        FROM ALLOG
                        WHERE ALL_TIPMOV='10' 
                          AND ALL_FBG = 'F'
                          AND ALL_DOC_ELECTRONICO='A'
                          AND ALL_ESTADO_FE IS NOT NULL
                        ORDER BY ALL_FECHA_DIA DESC, ALL_NUMFAC DESC";
        
        $invoices = $db->query($sqlInvoices)->getResultArray();
        
        $data = [
            'summaries' => $summaries,
            'invoices' => $invoices,
            'incidencias' => $incidencias,
            'title' => 'Monitor de Facturación Electrónica'
        ];

        return view('facturacion/monitor', $data);
    }
}
