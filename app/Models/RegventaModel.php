<?php

namespace App\Models;

use CodeIgniter\Model;

class RegventaModel extends Model
{
    protected $db;
    protected $dbpm;
    protected $dbjj;

    public function __construct()
    {
        parent::__construct();
        // Conexiones para P. Meza y Juanjuicillo (deben estar en app/Config/Database.php)
        try {
            $this->dbpm = \Config\Database::connect('pmeza', false);
            $this->dbjj = \Config\Database::connect('juanjuicillo', false);
        } catch (\Exception $e) {
            log_message('error', 'Error conectando a bases de datos remotas en RegventaModel: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene la conexión a usar según el servidor seleccionado
     * 1: Local (Default)
     * 2: Juanjuicillo (dbjj)
     * 3: P. Meza (dbpm)
     */
    private function get_connection($server = 1)
    {
        if ($server == 3) return $this->dbpm;
        if ($server == 2) return $this->dbjj;
        return $this->db;
    }

    /**
     * Obtiene facturas y boletas
     */
    public function get_facturas_boletas($params, $server = 1)
    {
        $db = $this->get_connection($server);
        $builder = $db->table('FACART f');
        $builder->select('f.FAR_CODCIA, f.FAR_NUMSER, f.FAR_NUMFAC, f.FAR_NUMSEC, f.FAR_FBG, f.FAR_TIPMOV, f.FAR_FECHA_COMPRA, f.FAR_FECHA, f.FAR_BRUTO, f.FAR_TOT_DESCTO, f.FAR_IMPTO, f.FAR_SUBTOTAL, f.FAR_EX_IGV, f.FAR_ESTADO, f.FAR_MONEDA, f.FAR_CANTIDAD, f.FAR_COSPRO, f.FAR_NUM_LOTE, f.FAR_SIGNO_CAR');
        $builder->select('c.CLI_RUC_ESPOSA, c.CLI_RUC_ESPOSO, c.CLI_NOMBRE, c.CLI_CODCLIE as CLI_CODCLIE2');
        $builder->join('CLIENTES c', 'f.FAR_CODCIA = c.CLI_CODCIA AND f.FAR_CODCLIE = c.CLI_CODCLIE AND f.FAR_CP = c.CLI_CP', 'left');

        $this->_apply_cias_filter($builder, $params['codcias']);

        $builder->where('f.FAR_TIPMOV', 10);
        $builder->where('f.FAR_ESTADO2 !=', 'L');
        $builder->where('f.FAR_FECHA_COMPRA >=', $params['fecha1']);
        $builder->where('f.FAR_FECHA_COMPRA <=', $params['fecha2']);

        $tipos = [];
        if (!empty($params['TD_F'])) $tipos[] = 'F';
        if (!empty($params['TD_B'])) $tipos[] = 'B';
        if (!empty($tipos)) {
            $builder->whereIn('f.FAR_FBG', $tipos);
        }

        if (!empty($params['serie'])) {
            $builder->where('f.FAR_NUMSER', $params['serie']);
        }

        if (!empty($params['codvendedores'])) {
            $builder->whereIn('f.FAR_CODVEN', $params['codvendedores']);
        }

        if (!empty($params['codclientes'])) {
            $builder->whereIn('f.FAR_CODCLIE', $params['codclientes']);
        }

        $builder->orderBy('f.FAR_FECHA_COMPRA, f.FAR_FBG DESC, f.FAR_NUMSER, f.FAR_NUMFAC, f.FAR_NUMSEC');

        return $builder->get()->getResultArray();
    }

    /**
     * Obtiene Notas de Crédito
     */
    public function get_notas_credito($params, $server = 1)
    {
        $db = $this->get_connection($server);
        $builder = $db->table('FACART f');
        $builder->select('f.FAR_CODCIA, f.FAR_NUMSER, f.FAR_NUMFAC, f.FAR_NUMSEC, f.FAR_FBG, f.FAR_TIPMOV, f.FAR_FECHA_COMPRA, f.FAR_FECHA, f.FAR_BRUTO, f.FAR_TOT_DESCTO, f.FAR_IMPTO, f.FAR_SUBTOTAL, f.FAR_EX_IGV, f.FAR_ESTADO, f.FAR_MONEDA, f.FAR_CANTIDAD, f.FAR_COSPRO, f.FAR_NUM_LOTE, f.FAR_SIGNO_CAR');
        $builder->select('c.CLI_RUC_ESPOSA, c.CLI_RUC_ESPOSO, c.CLI_NOMBRE, c.CLI_CODCLIE as CLI_CODCLIE2');
        $builder->join('CLIENTES c', 'f.FAR_CODCIA = c.CLI_CODCIA AND f.FAR_CODCLIE = c.CLI_CODCLIE AND f.FAR_CP = c.CLI_CP', 'left');

        $this->_apply_cias_filter($builder, $params['codcias']);

        $builder->where('f.FAR_TIPMOV', 97);
        $builder->where('f.FAR_ESTADO2 !=', 'L');
        // Cambiar FAR_FECHA por FAR_FECHA_COMPRA para coincidir con Visual Basic
        $builder->where('f.FAR_FECHA_COMPRA >=', $params['fecha1']);
        $builder->where('f.FAR_FECHA_COMPRA <=', $params['fecha2']);
        $builder->where('f.FAR_CP', 'C');

        if (!empty($params['codvendedores'])) {
            $builder->whereIn('f.FAR_CODVEN', $params['codvendedores']);
        }
        if (!empty($params['codclientes'])) {
            $builder->whereIn('f.FAR_CODCLIE', $params['codclientes']);
        }

        $builder->orderBy('f.FAR_TIPMOV, f.FAR_FBG DESC, f.FAR_NUMSER, f.FAR_NUMFAC');

        return $builder->get()->getResultArray();
    }

    /**
     * Obtiene Notas de Débito
     */
    public function get_notas_debito($params, $server = 1)
    {
        $db = $this->get_connection($server);
        $builder = $db->table('FACART f');
        $builder->select('f.FAR_CODCIA, f.FAR_NUMSER, f.FAR_NUMFAC, f.FAR_NUMSEC, f.FAR_FBG, f.FAR_TIPMOV, f.FAR_FECHA_COMPRA, f.FAR_FECHA, f.FAR_BRUTO, f.FAR_TOT_DESCTO, f.FAR_IMPTO, f.FAR_SUBTOTAL, f.FAR_EX_IGV, f.FAR_ESTADO, f.FAR_MONEDA, f.FAR_CANTIDAD, f.FAR_COSPRO, f.FAR_NUM_LOTE, f.FAR_SIGNO_CAR');
        $builder->select('c.CLI_RUC_ESPOSA, c.CLI_RUC_ESPOSO, c.CLI_NOMBRE, c.CLI_CODCLIE as CLI_CODCLIE2');
        $builder->join('CLIENTES c', 'f.FAR_CODCIA = c.CLI_CODCIA AND f.FAR_CODCLIE = c.CLI_CODCLIE AND f.FAR_CP = c.CLI_CP', 'left');

        $this->_apply_cias_filter($builder, $params['codcias']);

        $builder->where('f.FAR_TIPMOV', 98);
        $builder->where('f.FAR_ESTADO2 !=', 'L');
        // Cambiar FAR_FECHA por FAR_FECHA_COMPRA para coincidir con Visual Basic
        $builder->where('f.FAR_FECHA_COMPRA >=', $params['fecha1']);
        $builder->where('f.FAR_FECHA_COMPRA <=', $params['fecha2']);
        $builder->where('f.FAR_CP', 'C');

        if (!empty($params['codvendedores'])) {
            $builder->whereIn('f.FAR_CODVEN', $params['codvendedores']);
        }
        if (!empty($params['codclientes'])) {
            $builder->whereIn('f.FAR_CODCLIE', $params['codclientes']);
        }

        $builder->orderBy('f.FAR_TIPMOV, f.FAR_FBG DESC, f.FAR_FECHA_COMPRA, f.FAR_NUMSER, f.FAR_NUMFAC');

        return $builder->get()->getResultArray();
    }

    /**
     * Obtiene tipo de cambio
     */
    public function get_tipo_cambio($fecha, $codcia, $server = 1)
    {
        $db = $this->get_connection($server);
        $builder = $db->table('CALENDARIO');
        $builder->select('CAL_TIPO_CAMBIO');
        $builder->where('CAL_CODCIA', $codcia);
        $builder->where('CAL_FECHA', $fecha);
        $row = $builder->get()->getRowArray();

        if (empty($row) || empty($row['CAL_TIPO_CAMBIO'])) {
            return 0;
        }
        return (float) $row['CAL_TIPO_CAMBIO'];
    }

    /**
     * Obtiene lista de vendedores
     */
    public function get_vendedores($codcia, $server = 1)
    {
        $db = $this->get_connection($server);
        $builder = $db->table('VEMAEST');
        $builder->select('VEM_CODVEN, VEM_NOMBRE');
        $builder->where('VEM_CODCIA', $codcia);
        $builder->orderBy('VEM_NOMBRE');
        return $builder->get()->getResultArray();
    }

    /**
     * Obtiene lista de clientes
     */
    public function get_clientes($codcia, $server = 1)
    {
        $db = $this->get_connection($server);
        $builder = $db->table('CLIENTES');
        $builder->select('CLI_CODCLIE, CLI_NOMBRE');
        $builder->where('CLI_CODCIA', $codcia);
        $builder->where('CLI_CP', 'C');
        $builder->orderBy('CLI_NOMBRE');
        return $builder->get()->getResultArray();
    }

    private function _apply_cias_filter($builder, $codcias)
    {
        if (!empty($codcias) && is_array($codcias)) {
            $builder->whereIn('f.FAR_CODCIA', $codcias);
        }
    }
}
