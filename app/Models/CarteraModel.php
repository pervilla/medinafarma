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

    $builder->where('CLI.CLI_CP', 'P'); // Solo proveedores
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
    
    /**
     * Obtiene facturas pendientes de pago de proveedores con información para cálculo de intereses
     */
    public function getFacturasPendientesProveedores($proveedor = null, $fechaDesde = null, $fechaHasta = null, $local = 1, $estado = null)
    {
        $builder = $this->db->table('CARTERA CRT');
        $builder->select("
            CRT.CAR_NUMDOC as numfac,
            CRT.CAR_NUMDOC as car_NUMFAC,
            CRT.CAR_CODCLIE as codclie,
            CLI.CLI_NOMBRE as proveedor_nombre,
            CRT.CAR_TIPDOC as tipdoc,
            CRT.CAR_SERDOC as serdoc,
            CRT.CAR_NUMDOC as numdoc,
            CRT.CAR_IMPORTE as saldo_pendiente,
            CRT.CAR_IMPORTE as car_importe,
            CRT.CAR_IMP_INI as importe_inicial,
            CRT.CAR_FECHA_VCTO as fecha_vencimiento,
            CRT.CAR_FECHA_VCTO as car_fecha_vcto,
            CRT.CAR_FECHA_INGR as fecha_emision,
            CRT.CAR_SITUACION as situacion,
            CRT.CAR_CP as cp,
            CRT.CAR_CODCIA as codcia,
            DATEDIFF(day, CRT.CAR_FECHA_VCTO, GETDATE()) as dias_mora,
            CASE 
                WHEN CRT.CAR_IMPORTE > 0 AND CRT.CAR_FECHA_VCTO < GETDATE() THEN 'VENCIDA'
                WHEN CRT.CAR_IMPORTE > 0 AND CRT.CAR_FECHA_VCTO >= GETDATE() THEN 'POR_VENCER'
                ELSE 'PAGADA'
            END as estado_factura
        ");
        $builder->join('CLIENTES CLI', 'CRT.CAR_CODCLIE = CLI.CLI_CODCLIE AND CRT.CAR_CODCIA = CLI.CLI_CODCIA AND CRT.CAR_CP = CLI.CLI_CP');
        
        // Filtros básicos
        $builder->where('CLI.CLI_CP', 'P'); // Solo proveedores
        $builder->whereIn('CRT.CAR_TIPDOC', ['FA', 'LE', 'LR', 'PT', 'CH']); // Tipos de documento válidos
        $builder->where('CRT.CAR_IMPORTE >', 0); // Solo pendientes de pago
        
        // Filtrar por proveedor
        if (!empty($proveedor)) {
            $builder->where('CRT.CAR_CODCLIE', $proveedor);
        }
        
        // Filtrar por rango de fechas de vencimiento
        if (!empty($fechaDesde)) {
            $builder->where('CRT.CAR_FECHA_VCTO >=', $fechaDesde);
        }
        if (!empty($fechaHasta)) {
            $builder->where('CRT.CAR_FECHA_VCTO <=', $fechaHasta);
        }
        
        // Filtrar por local (asumimos que CAR_CP representa el local 1,2,3)
        // Comentado por posibles problemas de tipo de datos
        // if (!empty($local)) {
        //     $builder->where('CRT.CAR_CP', $local);
        // }
        
        // Filtrar por estado
        if ($estado === 'vencidas') {
            $builder->where('CRT.CAR_FECHA_VCTO <', date('Y-m-d'));
        } elseif ($estado === 'por_vencer') {
            $builder->where('CRT.CAR_FECHA_VCTO >=', date('Y-m-d'));
        }
        
        $builder->orderBy('CRT.CAR_FECHA_VCTO', 'ASC');
        $builder->orderBy('CLI.CLI_NOMBRE', 'ASC');
        
        $query = $builder->get();
        return $query->getResultArray();
    }
    
    /**
     * Obtiene una factura específica por número
     */
    public function getFacturaByNumero($numfac, $tipo = 'P')
    {
        $builder = $this->db->table('CARTERA CRT');
        $builder->select("
            CRT.CAR_NUMDOC as numfac,
            CRT.CAR_NUMDOC as car_NUMDOC,
            CRT.CAR_NUMFAC as car_NUMFAC,
            CRT.CAR_CODCLIE as codclie,
            CRT.CAR_CODCLIE as car_CODCLIE,
            CLI.CLI_NOMBRE as proveedor_nombre,
            CRT.CAR_TIPDOC as tipdoc,
            CRT.CAR_TIPDOC as CAR_TIPDOC,
            CRT.CAR_SERDOC as serdoc,
            CRT.CAR_SERDOC as car_SERDOC,
            CRT.CAR_NUMDOC as numdoc,
            CRT.CAR_IMPORTE as saldo_pendiente,
            CRT.CAR_IMPORTE as car_importe,
            CRT.CAR_IMP_INI as importe_inicial,
            CRT.CAR_FECHA_VCTO as fecha_vencimiento,
            CRT.CAR_FECHA_VCTO as car_fecha_vcto,
            CRT.CAR_FECHA_INGR as fecha_emision,
            CRT.CAR_SITUACION as situacion,
            CRT.CAR_CP as cp,
            CRT.CAR_CODCIA as codcia,
            CRT.CAR_CODCIA as CAR_CODCIA,
            DATEDIFF(day, CRT.CAR_FECHA_VCTO, GETDATE()) as dias_mora
        ");
        $builder->join('CLIENTES CLI', 'CRT.CAR_CODCLIE = CLI.CLI_CODCLIE AND CRT.CAR_CODCIA = CLI.CLI_CODCIA AND CRT.CAR_CP = CLI.CLI_CP');
        
        $builder->where('CRT.CAR_NUMDOC', $numfac);
        $builder->where('CLI.CLI_CP', $tipo); // 'P' para proveedores
        
        $query = $builder->get();
        return $query->getRowArray();
    }
    
    /**
     * Actualizar saldo de una factura en CARTERA
     */
    public function actualizarSaldoFactura($numfac, $nuevoSaldo, $codclie, $codcia = 25, $cp = null)
    {
        $builder = $this->db->table('CARTERA');
        $builder->where('CAR_NUMDOC', $numfac);
        $builder->where('CAR_CODCLIE', $codclie);
        $builder->where('CAR_CODCIA', $codcia);
        if ($cp !== null) {
            $builder->where('CAR_CP', $cp);
        }
        
        $data = [
            'CAR_IMPORTE' => $nuevoSaldo,
            'CAR_SITUACION' => $nuevoSaldo > 0 ? 'P' : 'C' // P = Pendiente, C = Cancelado
        ];
        
        return $builder->update($data);
    }
    
    /**
     * Obtener saldo actual de una factura
     */
    public function getSaldoFactura($numfac, $codclie, $codcia = 25, $cp = null)
    {
        $builder = $this->db->table('CARTERA');
        $builder->select('CAR_IMPORTE');
        $builder->where('CAR_NUMDOC', $numfac);
        $builder->where('CAR_CODCLIE', $codclie);
        $builder->where('CAR_CODCIA', $codcia);
        if ($cp !== null) {
            $builder->where('CAR_CP', $cp);
        }
        
        $query = $builder->get();
        $result = $query->getRowArray();
        return $result ? $result['CAR_IMPORTE'] : 0;
    }
}
