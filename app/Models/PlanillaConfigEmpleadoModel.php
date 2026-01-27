<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanillaConfigEmpleadoModel extends Model
{
    protected $table      = 'planilla_config_empleados';
    protected $primaryKey = 'vem_codven';
    protected $useAutoIncrement = false;
    protected $allowedFields = ['vem_codven', 'sueldo_basico', 'tipo_comision', 'afp_id', 'asignacion_familiar', 'comision_fijo_monto'];
    protected $returnType = 'object';
}
