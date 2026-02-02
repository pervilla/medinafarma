<?php

namespace App\Models;

use CodeIgniter\Model;

class EgresoModel extends Model
{
    protected $table = 'EGRESOS';
    protected $primaryKey = 'EGR_ID';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'EGR_FECHA',
        'EGR_LOCAL',
        'EGR_CUENTA_ID',
        'EGR_DESCRIPCION',
        'EGR_MONTO',
        'EGR_COMPROBANTE_TIPO',
        'EGR_COMPROBANTE_SERIE',
        'EGR_COMPROBANTE_NUMERO',
        'EGR_FORMA_PAGO',
        'EGR_RESPONSABLE',
        'EGR_ESTADO',
        'EGR_FECHA_VCTO',
        'EGR_INTERESES',
        'EGR_TIPO_EGRESO',
        'EGR_FACTURA_REF',
        'EGR_PROVEEDOR_COD',
        'EGR_CAJA_MOV_ID',
        'EGR_USUARIO',
        'EGR_FECHA_REGISTRO',
        'EGR_OBSERVACIONES'
    ];
    
    protected $useTimestamps = false;
    
    // Validation rules
    protected $validationRules = [
        'EGR_FECHA' => 'required|valid_date',
        'EGR_LOCAL' => 'required|integer|in_list[1,2,3]',
        'EGR_CUENTA_ID' => 'required|integer',
        'EGR_DESCRIPCION' => 'required|max_length[255]',
        'EGR_MONTO' => 'required|decimal',
        'EGR_ESTADO' => 'permit_empty|in_list[pagado,pendiente,anulado]',
        'EGR_TIPO_EGRESO' => 'permit_empty|in_list[NORMAL,INTERES_MORA,LETRA]'
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    /**
     * Registra un egreso de interés moratorio relacionado con un pago a proveedor
     */
    public function registrarInteresMora($data)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Obtener cuenta de intereses por mora
            $planCuentaModel = new PlanCuentaModel();
            $cuentaInteresesId = $planCuentaModel->getCuentaInteresesMora();
            
            if (!$cuentaInteresesId) {
                throw new \Exception('No se encontró la cuenta de intereses por mora');
            }
            
            $egresoData = [
                'EGR_FECHA' => $data['fecha_pago'],
                'EGR_LOCAL' => $data['local'],
                'EGR_CUENTA_ID' => $cuentaInteresesId,
                'EGR_DESCRIPCION' => $data['descripcion'],
                'EGR_MONTO' => $data['monto_interes'],
                'EGR_COMPROBANTE_TIPO' => $data['comprobante_tipo'] ?? null,
                'EGR_COMPROBANTE_SERIE' => $data['comprobante_serie'] ?? null,
                'EGR_COMPROBANTE_NUMERO' => $data['comprobante_numero'] ?? null,
                'EGR_FORMA_PAGO' => $data['forma_pago'],
                'EGR_RESPONSABLE' => $data['responsable'] ?? null,
                'EGR_ESTADO' => 'pagado',
                'EGR_TIPO_EGRESO' => 'INTERES_MORA',
                'EGR_FACTURA_REF' => $data['factura_ref'],
                'EGR_PROVEEDOR_COD' => $data['proveedor_cod'],
                'EGR_USUARIO' => $data['usuario'],
                'EGR_FECHA_REGISTRO' => date('d-m-Y H:i:s'),
                'EGR_OBSERVACIONES' => $data['observaciones'] ?? null
            ];
            
            $egresoId = $this->insert($egresoData);
            
            // Registrar movimiento en caja si aplica
            if ($data['registrar_caja'] ?? true) {
                $cajaMovModel = new CajaMovimientosModel();
                $movimientoId = $cajaMovModel->registrarMovimiento([
                    'CM_FECHA' => $data['fecha_pago'],
                    'CM_CAJA_ID' => $data['local'],
                    'CM_MOTIVO' => 'INTERES_MORA',
                    'CM_MONTO' => -$data['monto_interes'], // Negativo porque es egreso
                    'CM_DESCRIPCION' => $data['descripcion'],
                    'CM_USUARIO' => $data['usuario'],
                    'CM_REFERENCIA' => "EGR-{$egresoId}",
                    'CM_ESTADO' => 'CONFIRMADO'
                ], $data['local']); // Servidor = local
                
                // Actualizar referencia en egreso
                $this->update($egresoId, ['EGR_CAJA_MOV_ID' => $movimientoId]);
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === FALSE) {
                throw new \Exception('Error al registrar el interés en la base de datos');
            }
            
            return $egresoId;
            
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }
    
    /**
     * Registra un egreso normal (gasto operativo)
     */
    public function registrarEgresoNormal($data)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        $egresoData = null; // Definir antes del try para acceso en catch
        
        try {
            $egresoData = [
                'EGR_FECHA' => $data['fecha'],
                'EGR_LOCAL' => $data['local'],
                'EGR_CUENTA_ID' => $data['cuenta_id'],
                'EGR_DESCRIPCION' => $data['descripcion'],
                'EGR_MONTO' => $data['monto'],
                'EGR_COMPROBANTE_TIPO' => $data['comprobante_tipo'] ?? null,
                'EGR_COMPROBANTE_SERIE' => $data['comprobante_serie'] ?? null,
                'EGR_COMPROBANTE_NUMERO' => $data['comprobante_numero'] ?? null,
                'EGR_FORMA_PAGO' => $data['forma_pago'] ?? 'EFECTIVO',
                'EGR_RESPONSABLE' => $data['responsable'] ?? null,
                'EGR_ESTADO' => $data['estado'] ?? 'pagado',
                'EGR_TIPO_EGRESO' => 'NORMAL',
                'EGR_FACTURA_REF' => $data['factura_ref'] ?? null,
                'EGR_PROVEEDOR_COD' => $data['proveedor_cod'] ?? null,
                'EGR_CAJA_MOV_ID' => $data['caja_mov_id'] ?? null,
                'EGR_USUARIO' => $data['usuario'],
                'EGR_FECHA_REGISTRO' => date('d-m-Y H:i:s'),
                'EGR_OBSERVACIONES' => $data['observaciones'] ?? null
            ];
            
            $insertResult = $this->insert($egresoData);
            log_message('error', 'Last query: ' . $this->db->getLastQuery());
            if (!$insertResult) {
                $error = $this->db->error();
                log_message('error', 'Error details: ' . json_encode($error));
                throw new \Exception('Error al insertar egreso: ' . ($error['message'] ?? 'Error desconocido'));
            }
            $egresoId = $this->db->insertID();
            
            // Registrar movimiento en caja si el estado es pagado
            if (($data['estado'] ?? 'pagado') === 'pagado' && ($data['registrar_caja'] ?? true)) {
                $cajaMovModel = new CajaMovimientosModel();
                $movimientoId = $cajaMovModel->registrarMovimiento([
                    'CM_FECHA' => $data['fecha'],
                    'CM_CAJA_ID' => $data['local'],
                    'CM_MOTIVO' => 'NORMAL',
                    'CM_MONTO' => -$data['monto'], // Negativo porque es egreso
                    'CM_DESCRIPCION' => $data['descripcion'],
                    'CM_USUARIO' => $data['usuario'],
                    'CM_REFERENCIA' => "EGR-{$egresoId}",
                    'CM_ESTADO' => 'CONFIRMADO'
                ], $data['local']); // Servidor = local
                
                // Actualizar referencia en egreso
                $this->update($egresoId, ['EGR_CAJA_MOV_ID' => $movimientoId]);
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === FALSE) {
                $error = $db->error();
                log_message('error', 'Error de base de datos al registrar egreso: ' . json_encode($error));
                throw new \Exception('Error al registrar el egreso en la base de datos: ' . ($error['message'] ?? 'Error desconocido'));
            }
            
            return $egresoId;
            
        } catch (\Exception $e) {
            $db->transRollback();
            // Log detallado del error
            log_message('error', 'Error en registrarEgresoNormal: ' . $e->getMessage());
            log_message('error', 'Datos del egreso: ' . json_encode($egresoData));
            // Relanzar excepción con mensaje más claro
            throw new \Exception('Error al registrar el egreso: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene egresos con filtros
     */
    public function getEgresos($filters = [])
    {
        $builder = $this->builder();
        $builder->select('EGRESOS.*, PLAN_CUENTAS.PC_CODIGO, PLAN_CUENTAS.PC_NOMBRE as cuenta_nombre');
        $builder->join('PLAN_CUENTAS', 'PLAN_CUENTAS.PC_ID = EGRESOS.EGR_CUENTA_ID', 'left');
        
        // Aplicar filtros
        if (!empty($filters['fecha_desde'])) {
            $builder->where('EGR_FECHA >=', $filters['fecha_desde']);
        }
        if (!empty($filters['fecha_hasta'])) {
            $builder->where('EGR_FECHA <=', $filters['fecha_hasta']);
        }
        if (!empty($filters['local'])) {
            $builder->where('EGR_LOCAL', $filters['local']);
        }
        if (!empty($filters['cuenta_id'])) {
            $builder->where('EGR_CUENTA_ID', $filters['cuenta_id']);
        }
        if (!empty($filters['tipo_egreso'])) {
            $builder->where('EGR_TIPO_EGRESO', $filters['tipo_egreso']);
        }
        if (!empty($filters['estado'])) {
            $builder->where('EGR_ESTADO', $filters['estado']);
        }
        if (!empty($filters['proveedor_cod'])) {
            $builder->where('EGR_PROVEEDOR_COD', $filters['proveedor_cod']);
        }
        
        $builder->orderBy('EGR_FECHA', 'DESC');
        $builder->orderBy('EGR_ID', 'DESC');
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Obtiene intereses moratorios pagados en un período
     */
    public function getInteresesMoratorios($fechaDesde, $fechaHasta, $proveedorId = null)
    {
        $builder = $this->builder();
        $builder->select('EGRESOS.*, PLAN_CUENTAS.PC_CODIGO, PLAN_CUENTAS.PC_NOMBRE as cuenta_nombre');
        $builder->join('PLAN_CUENTAS', 'PLAN_CUENTAS.PC_ID = EGRESOS.EGR_CUENTA_ID', 'left');
        
        $builder->where('EGR_TIPO_EGRESO', 'INTERES_MORA');
        $builder->where('EGR_FECHA >=', $fechaDesde);
        $builder->where('EGR_FECHA <=', $fechaHasta);
        
        if ($proveedorId) {
            $builder->where('EGR_PROVEEDOR_COD', $proveedorId);
        }
        
        $builder->orderBy('EGR_FECHA', 'DESC');
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Obtiene total de egresos por cuenta en un período
     */
    public function getTotalPorCuenta($fechaDesde, $fechaHasta)
    {
        $builder = $this->builder();
        $builder->select('EGR_CUENTA_ID, PLAN_CUENTAS.PC_CODIGO, PLAN_CUENTAS.PC_NOMBRE, SUM(EGR_MONTO) as total');
        $builder->join('PLAN_CUENTAS', 'PLAN_CUENTAS.PC_ID = EGRESOS.EGR_CUENTA_ID', 'left');
        
        $builder->where('EGR_FECHA >=', $fechaDesde);
        $builder->where('EGR_FECHA <=', $fechaHasta);
        $builder->where('EGR_ESTADO', 'pagado');
        
        $builder->groupBy('EGR_CUENTA_ID, PLAN_CUENTAS.PC_CODIGO, PLAN_CUENTAS.PC_NOMBRE');
        $builder->orderBy('PLAN_CUENTAS.PC_CODIGO', 'ASC');
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Obtiene egresos por factura de referencia
     */
    public function getEgresosPorFactura($facturaRef)
    {
        return $this->where('EGR_FACTURA_REF', $facturaRef)
                   ->where('EGR_TIPO_EGRESO', 'INTERES_MORA')
                   ->findAll();
    }
    
    /**
     * Valida si ya se registró interés para una factura en una fecha específica
     */
    public function existeInteresRegistrado($facturaRef, $fechaPago)
    {
        return $this->where('EGR_FACTURA_REF', $facturaRef)
                   ->where('EGR_TIPO_EGRESO', 'INTERES_MORA')
                   ->where('EGR_FECHA', $fechaPago)
                   ->countAllResults() > 0;
    }
    
    /**
     * Calcula total de intereses pagados a un proveedor
     */
    public function getTotalInteresesProveedor($proveedorCod, $fechaDesde = null, $fechaHasta = null)
    {
        $builder = $this->builder();
        $builder->select('SUM(EGR_MONTO) as total');
        $builder->where('EGR_PROVEEDOR_COD', $proveedorCod);
        $builder->where('EGR_TIPO_EGRESO', 'INTERES_MORA');
        $builder->where('EGR_ESTADO', 'pagado');
        
        if ($fechaDesde) {
            $builder->where('EGR_FECHA >=', $fechaDesde);
        }
        if ($fechaHasta) {
            $builder->where('EGR_FECHA <=', $fechaHasta);
        }
        
        $result = $builder->get()->getRowArray();
        return $result['total'] ?? 0;
    }
    
    /**
     * Actualiza la cuenta de gasto de múltiples egresos a la vez
     */
    public function actualizarCuentaMasivo($ids, $cuentaId)
    {
        if (empty($ids)) return false;
        
        return $this->builder()
            ->whereIn('EGR_ID', $ids)
            ->update(['EGR_CUENTA_ID' => $cuentaId]);
    }

    /**
     * Obtiene egresos por rango de fechas para flujo de caja
     */
    public function obtenerEgresosPorRango($fechaDesde, $fechaHasta, $local = null)
    {
        $builder = $this->builder();
        $builder->select('EGRESOS.*, PLAN_CUENTAS.PC_CODIGO, PLAN_CUENTAS.PC_NOMBRE as cuenta_nombre');
        $builder->join('PLAN_CUENTAS', 'PLAN_CUENTAS.PC_ID = EGRESOS.EGR_CUENTA_ID', 'left');
        
        $builder->where('EGR_FECHA >=', $fechaDesde);
        $builder->where('EGR_FECHA <=', $fechaHasta);
        $builder->where('EGR_ESTADO', 'pagado'); // Solo egresos pagados
        
        if ($local) {
            $builder->where('EGR_LOCAL', $local);
        }
        
        $builder->orderBy('EGR_FECHA', 'ASC');
        $builder->orderBy('EGR_ID', 'ASC');
        
        return $builder->get()->getResultArray();
    }
}