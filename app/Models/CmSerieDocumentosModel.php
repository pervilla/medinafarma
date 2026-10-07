<?php

namespace App\Models;

use CodeIgniter\Model;

class CmSerieDocumentosModel extends Model
{
    protected $table            = 'CM_SERIE_DOCUMENTOS';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'local_id',
        'tipo_documento',
        'prefijo',
        'serie_actual',
        'correlativo_actual',
        'tipo_servicio',
        'estado'
    ];
}
