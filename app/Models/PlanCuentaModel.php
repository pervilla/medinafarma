<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanCuentaModel extends Model
{
    protected $table = 'PLAN_CUENTAS';
    protected $primaryKey = 'PC_ID';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'PC_CODIGO',
        'PC_NOMBRE',
        'PC_TIPO',
        'PC_PADRE',
        'PC_ACTIVO',
        'PC_DESCRIPCION'
    ];
    
    protected $useTimestamps = false;
    
    // Validation rules
    protected $validationRules = [
        'PC_CODIGO' => 'required|max_length[20]',
        'PC_NOMBRE' => 'required|max_length[100]',
        'PC_TIPO' => 'required|in_list[I,E]',
        'PC_PADRE' => 'permit_empty|integer',
        'PC_ACTIVO' => 'permit_empty|in_list[0,1]'
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    /**
     * Obtiene todas las cuentas activas ordenadas jerárquicamente
     */
    public function getCuentasActivas($tipo = null)
    {
        $builder = $this->builder();
        $builder->where('PC_ACTIVO', 1);
        
        if ($tipo) {
            $builder->where('PC_TIPO', $tipo);
        }
        
        $builder->orderBy('PC_CODIGO', 'ASC');
        return $builder->get()->getResultArray();
    }
    
    /**
     * Obtiene cuentas en formato jerárquico para select
     */
    public function getCuentasJerarquicas($tipo = 'E')
    {
        $cuentas = $this->getCuentasActivas($tipo);
        return $this->buildJerarquia($cuentas);
    }
    
    /**
     * Construye array jerárquico a partir de lista plana
     */
    private function buildJerarquia($cuentas, $padreId = null, $nivel = 0)
    {
        $result = [];
        
        foreach ($cuentas as $cuenta) {
            if ($cuenta['PC_PADRE'] == $padreId) {
                $cuenta['nivel'] = $nivel;
                $cuenta['hijos'] = $this->buildJerarquia($cuentas, $cuenta['PC_ID'], $nivel + 1);
                $result[] = $cuenta;
            }
        }
        
        return $result;
    }
    
    /**
     * Obtiene cuenta por código
     */
    public function getCuentaByCodigo($codigo)
    {
        return $this->where('PC_CODIGO', $codigo)->first();
    }
    
    /**
     * Obtiene ID de la cuenta de intereses por mora
     */
    public function getCuentaInteresesMora()
    {
        $cuenta = $this->where('PC_CODIGO', '6.2.1')->first();
        return $cuenta ? $cuenta['PC_ID'] : null;
    }
    
    /**
     * Obtiene opciones para dropdown con indentación
     */
    public function getOpcionesDropdown($tipo = 'E', $incluirVacio = true)
    {
        $cuentas = $this->getCuentasJerarquicas($tipo);
        $opciones = [];
        
        if ($incluirVacio) {
            $opciones[''] = '-- Seleccionar --';
        }
        
        $this->addOpcionesJerarquicas($cuentas, $opciones);
        return $opciones;
    }
    
private function addOpcionesJerarquicas($cuentas, &$opciones, $prefijo = '')
{
    foreach ($cuentas as $cuenta) {
        // Usar un carácter de espacio que no necesite escape
        $indent = str_repeat("\xC2\xA0\xC2\xA0\xC2\xA0", $cuenta['nivel']); // UTF-8 para non-breaking space
        $opciones[$cuenta['PC_ID']] = $indent . $cuenta['PC_CODIGO'] . ' - ' . $cuenta['PC_NOMBRE'];
        
        if (!empty($cuenta['hijos'])) {
            $this->addOpcionesJerarquicas($cuenta['hijos'], $opciones, $prefijo . "\xC2\xA0\xC2\xA0\xC2\xA0");
        }
    }
}
    
    /**
     * Verifica si una cuenta tiene movimientos (egresos asociados)
     */
    public function tieneMovimientos($cuentaId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('EGRESOS');
        $builder->where('EGR_CUENTA_ID', $cuentaId);
        $builder->limit(1);
        return $builder->countAllResults() > 0;
    }
    
    /**
     * Obtiene el código siguiente para una nueva subcuenta
     */
    public function getSiguienteCodigo($padreId = null)
    {
        if ($padreId) {
            $padre = $this->find($padreId);
            if (!$padre) {
                return '1';
            }
            
            // Buscar hermanos con mismo prefijo
            $codigoPadre = $padre['PC_CODIGO'];
            $builder = $this->builder();
            $builder->like('PC_CODIGO', $codigoPadre . '.', 'after');
            $builder->orderBy('PC_CODIGO', 'DESC');
            $ultimo = $builder->get()->getRowArray();
            
            if ($ultimo) {
                $partes = explode('.', $ultimo['PC_CODIGO']);
                $ultimoNumero = end($partes);
                $nuevoNumero = intval($ultimoNumero) + 1;
                return $codigoPadre . '.' . $nuevoNumero;
            } else {
                return $codigoPadre . '.1';
            }
        } else {
            // Cuenta raíz
            $builder = $this->builder();
            $builder->where('PC_PADRE IS NULL');
            $builder->orderBy('PC_CODIGO', 'DESC');
            $ultimo = $builder->get()->getRowArray();
            
            if ($ultimo) {
                $nuevoNumero = intval($ultimo['PC_CODIGO']) + 1;
                return (string) $nuevoNumero;
            } else {
                return '1';
            }
        }
    }
}