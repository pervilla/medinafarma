<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Description of CarteraModel
 *
 * @author José Luis
 */
class CarteraModel extends Model
{

    var $table = 'cartera';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    public function getDocumentos($proveedor = null, $inicio = null, $fin = null, $estado = null)
{
    $builder = $this->db->table('CARTERA CRT');
    $builder->select("
        CRT.CAR_SERDOC,
        CRT.CAR_NUMDOC,
        CRT.CAR_NUMSER_C,
        CRT.CAR_NUMFAC_C,
        CRT.CAR_TIPDOC,
        CRT.CAR_IMP_INI,
        CRT.CAR_IMPORTE,
        CASE WHEN CRT.CAR_IMPORTE <> 0 THEN 'SIN PAGAR' ELSE 'PAGADO' END AS ESTADO,
        CONVERT(varchar, CRT.CAR_FECHA_INGR, 103) CAR_FECHA_INGR,
        CONVERT(varchar, CRT.CAR_FECHA_VCTO, 103) CAR_FECHA_VCTO,
        CLI.CLI_NOMBRE,
        ALG.ALL_IMPORTE_AMORT AS PAGO_MONTO,
        ALG.ALL_FECHA_DIA AS PAGO_FECHA,
        TRA.TAB_NOMLARGO AS PAGO_FORMA,
        BAN.TAB_NOMLARGO AS PAGO_BANCO,
        ALG.ALL_NUMOPER AS PAGO_NUMERO_OPERACION
    ");
    $builder->join('CLIENTES CLI', '
        CRT.CAR_CODCLIE = CLI.CLI_CODCLIE AND 
        CRT.CAR_CODCIA = CLI.CLI_CODCIA AND 
        CRT.CAR_CP = CLI.CLI_CP
    ');
    $builder->join('ALLOG ALG', '
        CRT.CAR_CODCIA = ALG.ALL_CODCIA AND
        CRT.CAR_CP = ALG.ALL_CP AND
        CRT.CAR_TIPDOC = ALG.ALL_TIPDOC AND
        CRT.CAR_NUMDOC = ALG.ALL_NUMDOC
    ', 'left');
    $builder->join('TABLAS TRA', 'ALG.ALL_CODTRA = TRA.TAB_NUMTAB AND TRA.TAB_TIPREG = 502', 'left');
    $builder->join('TABLAS BAN', 'ALG.ALL_CODBAN = BAN.TAB_NUMTAB AND BAN.TAB_TIPREG = 504', 'left');

    $builder->where('CLI.CLI_CP', 'P');
    $builder->whereIn('CRT.CAR_TIPDOC', ['FA', 'LE', 'LR', 'PT', 'CH']);
    
    if ($proveedor) {
        $builder->where('CLI.CLI_CODCLIE', $proveedor);
    }

    // Caso: Solo documentos PAGADOS en un rango de fechas
    if ($estado === 'PAGADO' && $inicio && $fin) {
        $builder->where("CONVERT(DATE, ALG.ALL_FECHA_DIA, 103) BETWEEN CONVERT(DATE, '$inicio', 103) AND CONVERT(DATE, '$fin', 103)");
        $builder->where("CRT.CAR_IMPORTE", 0);
    }

    // Caso: Documentos SIN PAGAR o con situación especial
    if ($estado === 'PENDIENTE' || $estado === '') {
        $builder->groupStart();
            $builder->where("CRT.CAR_IMPORTE >", 0);
            $builder->orWhere("CRT.CAR_SITUACION", "E");
        $builder->groupEnd();

        if ($fin) {
            $builder->where("CONVERT(DATE, CRT.CAR_FECHA_INGR, 103) <= CONVERT(DATE, '$fin', 103)");
        }
    }

    $builder->orderBy('CLI.CLI_NOMBRE ASC, CRT.CAR_FECHA_VCTO ASC');

    $query = $builder->get();
    return $query->getResultArray();
}

    public function getDocumentoById($id)
    {
        return $this->db->table('CARTERA')->where('CAR_NUMDOC', $id)->get()->getRowArray();
    }
}
