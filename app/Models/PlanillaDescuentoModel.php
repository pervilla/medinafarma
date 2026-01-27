<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanillaDescuentoModel extends Model
{
    protected $table      = 'planilla_descuentos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['vem_codven', 'tipo', 'monto', 'fecha', 'observacion', 'estado', 'planilla_id', 'created_at', 'updated_at'];
    protected $useTimestamps = false; // Manually handling dates for SQL Server safety
    protected $returnType = 'object';
}
