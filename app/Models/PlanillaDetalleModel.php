<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanillaDetalleModel extends Model
{
    protected $table      = 'planilla_detalles';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'planilla_id', 'vem_codven', 'dias_trabajados', 'sueldo_basico', 
        'asignacion_familiar', 'comision_ventas', 'afp_monto', 
        'adelantos', 'creditos', 'faltantes', 'total_neto'
    ];
    protected $returnType = 'object';
}
