<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para gestión de servicios de citas
 * Tabla CITAS_SERVICIOS
 */
class CitaServicioModel extends Model
{
    protected $table = 'CITAS_SERVICIOS';
    protected $primaryKey = 'CNS_CODCNS';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'CNS_CODCIT', 
        'CNS_CODART', 
        'CNS_CANTIDAD', 
        'CNS_PRECIO', 
        'CNS_ESTADO', 
        'CNS_OBSERVACIONES',
        'CNS_PAGADO'
    ];

    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Obtener servicios de una cita
     */
    public function getServiciosByCita($citaId)
    {
        $sql = "SELECT 
                    cs.CNS_CODCNS,
                    cs.CNS_CODCIT,
                    cs.CNS_CODART,
                    cs.CNS_CANTIDAD,
                    cs.CNS_PRECIO,
                    cs.CNS_ESTADO,
                    cs.CNS_OBSERVACIONES,
                    ISNULL(cs.CNS_PAGADO, 0) as CNS_PAGADO,
                    RTRIM(LTRIM(art.ART_NOMBRE)) as ART_NOMBRE,
                    (cs.CNS_CANTIDAD * cs.CNS_PRECIO) as SUBTOTAL,
                    CASE WHEN ISNULL(cs.CNS_PAGADO, 0) = 1 THEN 'PAGADO' ELSE 'PENDIENTE' END as ESTADO_PAGO
                FROM CITAS_SERVICIOS cs
                INNER JOIN ARTI art ON cs.CNS_CODART = art.ART_KEY
                WHERE cs.CNS_CODCIT = ?
                ORDER BY cs.CNS_CODCNS";
        
        $query = $this->db->query($sql, [$citaId]);
        return $query->getResult();
    }

    /**
     * Agregar servicio a una cita
     */
    public function agregarServicio($data)
    {
        return $this->db->table($this->table)->insert($data);
    }

    /**
     * Actualizar servicio
     */
    public function actualizarServicio($servicioId, $data)
    {
        return $this->db->table($this->table)
                       ->where('CNS_CODCNS', $servicioId)
                       ->update($data);
    }

    /**
     * Eliminar servicio
     */
    public function eliminarServicio($servicioId)
    {
        return $this->db->table($this->table)
                       ->where('CNS_CODCNS', $servicioId)
                       ->delete();
    }

    /**
     * Obtener total de servicios de una cita
     */
    public function getTotalServicios($citaId)
    {
        $sql = "SELECT SUM(CNS_CANTIDAD * CNS_PRECIO) as TOTAL
                FROM CITAS_SERVICIOS 
                WHERE CNS_CODCIT = ?";
        
        $query = $this->db->query($sql, [$citaId]);
        $result = $query->getRow();
        return $result ? $result->TOTAL : 0;
    }

    /**
     * Obtener total de servicios pendientes de pago
     */
    public function getTotalServiciosPendientes($citaId)
    {
        $sql = "SELECT SUM(CNS_CANTIDAD * CNS_PRECIO) as TOTAL
                FROM CITAS_SERVICIOS 
                WHERE CNS_CODCIT = ? AND ISNULL(CNS_PAGADO, 0) = 0";
        
        $query = $this->db->query($sql, [$citaId]);
        $result = $query->getRow();
        return $result ? $result->TOTAL : 0;
    }

    /**
     * Marcar servicios como pagados
     */
    public function marcarServiciosComoPagados($citaId)
    {
        return $this->db->table($this->table)
                       ->where('CNS_CODCIT', $citaId)
                       ->where('ISNULL(CNS_PAGADO, 0)', 0)
                       ->update(['CNS_PAGADO' => 1]);
    }

    /**
     * Buscar servicios disponibles (solo de consultorio)
     */
    public function buscarServicios($busqueda = '')
    {
        // Si no hay búsqueda, devolver vacío
        if (empty(trim($busqueda))) {
            return [];
        }

        // Limpiar búsqueda
        $busqueda = strtoupper($busqueda);
        $busqueda = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'], ['A', 'E', 'I', 'O', 'U', 'Ñ'], $busqueda);
        $busqueda = preg_replace('/[^A-Za-z0-9 ñÑ]/', '', $busqueda);

        // Dividir en palabras
        $palabras = explode(' ', $busqueda);
        $condiciones = [];
        $params = [];
        
        foreach ($palabras as $palabra) {
            $palabra = trim($palabra);
            if ($palabra !== '') {
                $condiciones[] = "art.ART_NOMBRE LIKE ?";
                $params[] = "%$palabra%";
            }
        }

        if (empty($condiciones)) {
            return [];
        }

        $condiciones_str = implode(' AND ', $condiciones);

        $sql = "SELECT TOP 50
                    art.ART_KEY,
                    RTRIM(LTRIM(art.ART_NOMBRE)) as ART_NOMBRE,
                    ISNULL(pre.PRE_PRE1, 0) as PRECIO,
                    RTRIM(LTRIM(pre.PRE_UNIDAD)) as UNIDAD
                FROM ARTI art
                LEFT JOIN PRECIOS pre ON art.ART_KEY = pre.PRE_CODART AND pre.PRE_CODCIA = '25'
                WHERE $condiciones_str
                  AND art.ART_FAMILIA = 594 -- SERVICIOS DE CONSULTORIO
                  AND art.ART_SITUACION = 0 -- ACTIVOS
                  AND art.ART_CODCIA = '25'
                ORDER BY art.ART_NOMBRE";

        $query = $this->db->query($sql, $params);
        return $query->getResult();
    }

    /**
     * Obtener información detallada de un servicio
     */
    public function getServicioDetalle($articuloId)
    {
        $sql = "SELECT 
                    art.ART_KEY,
                    RTRIM(LTRIM(art.ART_NOMBRE)) as ART_NOMBRE,
                    ISNULL(pre.PRE_PRE1, 0) as PRECIO,
                    RTRIM(LTRIM(pre.PRE_UNIDAD)) as UNIDAD,
                    RTRIM(LTRIM(fam.TAB_NOMLARGO)) as FAMILIA
                FROM ARTI art
                LEFT JOIN PRECIOS pre ON art.ART_KEY = pre.PRE_CODART AND pre.PRE_CODCIA = '25'
                LEFT JOIN TABLAS fam ON art.ART_FAMILIA = fam.TAB_NUMTAB 
                                    AND fam.TAB_CODCIA = 25 
                                    AND fam.TAB_TIPREG = 122
                WHERE art.ART_KEY = ? 
                  AND art.ART_CODCIA = '25'";

        $query = $this->db->query($sql, [$articuloId]);
        return $query->getRow();
    }
}
