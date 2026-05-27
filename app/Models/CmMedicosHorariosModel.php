<?php

namespace App\Models;

use CodeIgniter\Model;

class CmMedicosHorariosModel extends Model
{
    protected $table            = 'CM_MEDICOS_HORARIOS';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'medico_id',
        'local_id',
        'dia_semana',
        'fecha_especifica',
        'hora_inicio',
        'hora_fin',
        'cupos_totales',
        'cupos_ocupados',
        'tiempo_por_atencion_minutos',
        'cod_art_servicio',
        'estado'
    ];
}
