<?php

namespace App\Models;

use CodeIgniter\Model;

class CaracuModel extends Model
{
    protected $table = 'CARACU';
    protected $primaryKey = 'CAA_NUMFAC';
    protected $useAutoIncrement = false; // La tabla puede no tener autoincrement
    
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    // Definir campos permitidos (ajustar según estructura real)
    protected $allowedFields = [
        'CAA_CP',
        'CAA_CODCLIE',
        'CAA_CODCIA',
        'CAA_TIPDOC',
        'CAA_FECHA',
        'CAA_NUM_OPER',
        'CAA_SERDOC',
        'CAA_NUMDOC',
        'CAA_IMPORTE',
        'CAA_SALDO',
        'CAA_SALDO_CAR',
        'CAA_NUMFAC',
        'CAA_SIGNO_CAR',
        'CAA_CODTRA',
        'CAA_CODUSU',
        'CAA_HORA',
        'CAA_NUMSER',
        'CAA_NUMSER_C',
        'CAA_NUMFAC_C',
        'CAA_FECHA_COBRO',
        'CAA_CONCEPTO'
    ];
    
    protected $useTimestamps = false;
    
    protected $dbjj;
    protected $dbpm;
    
    public function __construct()
    {
        parent::__construct();
        $this->dbjj = \Config\Database::connect('juanjuicillo');
        $this->dbpm = \Config\Database::connect('pmeza');
    }
    
    /**
     * Registrar movimiento en CARACU (historial de cartera)
     */
    public function registrarMovimiento($data)
    {
        // Asignar valores por defecto (basado en datos VB6)
        $defaults = [
            'CAA_HORA' => date('d/m/Y H:i:s'),
            'CAA_FECHA_COBRO' => date('d/m/Y') . ' 00:00',
            'CAA_SIGNO_CCM' => 0,
            'CAA_SIGNO_CAJA' => -1,
            'CAA_ESTADO' => 'N',
            'CAA_FLAG_SO' => 'N',
            'CAA_TIPO_CAMBIO' => 1.0,
            'CAA_NUMSER' => ' ',
            'CAA_NUMSER_C' => ' ',
            'CAA_NUMFAC_C' => 0,
            'CAA_CONCEPTO' => 'null'
        ];
        
        $insertData = array_merge($defaults, $data);
        
        // Determinar conexión de base de datos según CAA_CP
        $server = 1; // Por defecto local
        if (isset($insertData['CAA_CP']) && $insertData['CAA_CP'] == '2') {
            $server = 2;
        } elseif (isset($insertData['CAA_CP']) && $insertData['CAA_CP'] == '3') {
            $server = 3;
        }
        
        if ($server == 2) {
            $db = $this->dbjj;
        } elseif ($server == 3) {
            $db = $this->dbpm;
        } else {
            $db = $this->db;
        }
        
        error_log("CARACU registrarMovimiento: server=$server, CAA_CP=" . ($insertData['CAA_CP'] ?? 'null'));
        
        // Log para depuración
        log_message('debug', 'CARACU insert data: ' . print_r($insertData, true));
        
        // Usar query builder directamente para evitar problemas con clave primaria
        return $db->table($this->table)->insert($insertData);
    }
    
    /**
     * Obtener movimientos por factura
     */
    public function getMovimientosByFactura($numfac, $codclie = null)
    {
        $builder = $this->builder();
        $builder->where('CAA_NUMFAC', $numfac);
        
        if ($codclie) {
            $builder->where('CAA_CODCLIE', $codclie);
        }
        
        $builder->orderBy('CAA_FECHA', 'DESC');
        $builder->orderBy('CAA_HORA', 'DESC');
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Verificar si ya existe un pago registrado para la factura en una fecha
     */
    public function existePagoRegistrado($numfac, $fecha, $numOperacion = null)
    {
        $builder = $this->builder();
        $builder->where('CAA_NUMFAC', $numfac);
        $builder->where('CAA_FECHA', $fecha);
        $builder->where('CAA_CODTRA', 5360);
        
        if ($numOperacion) {
            $builder->where('CAA_NUM_OPER', $numOperacion);
        }
        
        return $builder->countAllResults() > 0;
    }
    
    /**
     * Obtener pagos a proveedores por rango de fechas para flujo de caja
     */
    public function obtenerPagosProveedoresPorRango($fechaDesde, $fechaHasta, $local = null)
    {
        // Convertir fechas a formato SQL Server (dd/mm/YYYY)
        $fechaDesdeSql = $this->convertToSqlServerDate($fechaDesde);
        $fechaHastaSql = $this->convertToSqlServerDate($fechaHasta);
        
        // Determinar conexión de base de datos según local
        if ($local == 2) {
            $db = $this->dbjj;
        } elseif ($local == 3) {
            $db = $this->dbpm;
        } else {
            $db = $this->db;
        }
        
        // Para pagos a proveedores, necesitamos unir con CLIENTES para obtener nombre del proveedor
        $builder = $db->table($this->table);
        
        // Seleccionar campos relevantes
        $builder->select('
            CARACU.CAA_FECHA,
            CARACU.CAA_CODCLIE,
            CARACU.CAA_IMPORTE,
            CARACU.CAA_NUMFAC,
            CARACU.CAA_SERDOC,
            CARACU.CAA_NUMDOC,
            CARACU.CAA_CODTRA,
            CLIENTES.CLI_NOMBRE
        ');
        
        // Unir con CLIENTES para obtener nombre del proveedor
        $builder->join('CLIENTES', 'CLIENTES.CLI_CODCLIE = CARACU.CAA_CODCLIE AND CLIENTES.CLI_CODCIA = CARACU.CAA_CODCIA AND CLIENTES.CLI_CP = CARACU.CAA_CP', 'left');
        
        // Filtrar por transacción de pago a proveedores (5360)
        $builder->where('CARACU.CAA_CODTRA', 5360);
        $builder->where('CARACU.CAA_FECHA >=', $fechaDesdeSql);
        $builder->where('CARACU.CAA_FECHA <=', $fechaHastaSql);
        
        // Nota: El campo de local ya se maneja mediante la conexión de base de datos
        
        $builder->orderBy('CARACU.CAA_FECHA', 'ASC');
        $builder->orderBy('CARACU.CAA_NUMFAC', 'ASC');
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Convertir fecha a formato SQL Server (dd/mm/YYYY)
     */
    private function convertToSqlServerDate($dateString)
    {
        if (empty($dateString)) {
            return '';
        }
        
        // Si ya está en formato dd/mm/yyyy, devolver tal cual
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}/', $dateString)) {
            return $dateString;
        }
        
        // Intentar convertir de Y-m-d a dd/mm/Y
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            return date('d/m/Y', $timestamp);
        }
        
        return $dateString; // Devolver original si no se puede convertir
    }
}