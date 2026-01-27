<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanillaReglaComisionModel extends Model
{
    protected $table      = 'planilla_reglas_comision';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tipo', 'referencia_id', 'nombre_producto', 'porcentaje', 'descripcion', 'created_at', 'updated_at'];
    protected $useTimestamps = false;
    protected $returnType = 'object';
}
