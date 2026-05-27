<?php

namespace App\Models;

use CodeIgniter\Model;

class CmPacientesModel extends Model
{
    protected $table            = 'CM_PACIENTES';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'cliente_id',
        'tipo_sangre',
        'alergias',
        'enfermedades_cronicas',
        'contacto_emergencia',
        'telefono_emergencia',
        'observaciones_medicas',
        'consentimiento_datos',
        'fecha_registro',
        'estado'
    ];

    /**
     * Obtiene el paciente junto con los datos maestros del cliente
     */
    public function getPacienteCompleto($id)
    {
        return $this->select('CM_PACIENTES.*, CLIENTES.CLI_NOMBRE, CLIENTES.CLI_RUC_ESPOSO as RUC, CLIENTES.CLI_RUC_ESPOSA as DNI')
                    ->join('CLIENTES', 'CLIENTES.CLI_CODCLIE = CM_PACIENTES.cliente_id')
                    ->where('CM_PACIENTES.id', $id)
                    ->first();
    }
}
