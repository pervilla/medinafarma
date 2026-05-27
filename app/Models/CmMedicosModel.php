<?php

namespace App\Models;

use CodeIgniter\Model;

class CmMedicosModel extends Model
{
    protected $table            = 'CM_MEDICOS';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombres',
        'apellidos',
        'dni',
        'cmp',
        'rne',
        'especialidad',
        'telefono',
        'estado'
    ];
}
