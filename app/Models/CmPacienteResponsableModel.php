<?php

namespace App\Models;

use CodeIgniter\Model;

class CmPacienteResponsableModel extends Model
{
    protected $table            = 'CM_PACIENTE_RESPONSABLE';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'paciente_id',
        'cliente_id',
        'parentesco',
        'titular_facturacion',
        'telefono',
        'observaciones',
        'fecha_registro',
        'estado'
    ];
}
