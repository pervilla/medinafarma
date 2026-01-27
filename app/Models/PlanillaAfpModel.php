<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanillaAfpModel extends Model
{
    protected $table      = 'planilla_afps';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'porcentaje'];
    protected $returnType = 'object';
}
