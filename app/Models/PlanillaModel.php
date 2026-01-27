<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanillaModel extends Model
{
    protected $table      = 'planillas';
    protected $primaryKey = 'id';
    protected $allowedFields = ['anio', 'mes', 'fecha_inicio', 'fecha_corte', 'estado', 'usuario_id', 'created_at', 'updated_at'];
    protected $useTimestamps = false;
    protected $returnType = 'object';
}
