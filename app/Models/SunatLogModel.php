<?php

namespace App\Models;

use CodeIgniter\Model;

class SunatLogModel extends Model {

    protected $db;

    public function __construct() {
        parent::__construct();
        // Log tables are always in the default DB (BDATOS 192.168.101.200)
        $this->db = \Config\Database::connect('default');
    }

    public function insertResumen($data) {
        // Quitar 'serie' de los datos si la columna no existe aún
        if (isset($data['serie'])) {
            $tieneSerie = false;
            try {
                $row = $this->db->query("SELECT COL_LENGTH('sunat_resumenes', 'serie') AS col_exists")->getRow();
                $tieneSerie = ($row && $row->col_exists !== null);
            } catch (\Exception $e) {}
            if (!$tieneSerie) {
                unset($data['serie']);
            }
        }
        $builder = $this->db->table('sunat_resumenes');
        $builder->insert($data);
        return $this->db->insertID();
    }

    public function yaExisteResumen($empresa_ruc, $serie, $fecha_generacion) {
        // Verificar si la columna 'serie' existe en la tabla (puede no existir si no se ejecutó el ALTER)
        $tieneSerie = false;
        try {
            $row = $this->db->query("SELECT COL_LENGTH('sunat_resumenes', 'serie') AS col_exists")->getRow();
            $tieneSerie = ($row && $row->col_exists !== null);
        } catch (\Exception $e) {
            $tieneSerie = false;
        }

        $builder = $this->db->table('sunat_resumenes');
        $builder->where('empresa_ruc', $empresa_ruc);
        $builder->where('fecha_generacion', $fecha_generacion);
        $builder->whereIn('estado_sunat', ['PENDIENTE', 'ACEPTADA']);
        if ($tieneSerie && !empty($serie)) {
            $builder->where('serie', $serie);
        }
        return $builder->countAllResults() > 0;
    }

    public function getResumenesHistorial($limit = 100) {
        // Usar TOP en vez de OFFSET/FETCH (compatible con SQL Server 2008+)
        $sql = "SELECT TOP ? * FROM dbo.sunat_resumenes ORDER BY fecha_generacion DESC, id DESC";
        return $this->db->query($sql, [(int)$limit])->getResult();
    }

    public function updateResumen($ticket, $data) {
        $builder = $this->db->table('sunat_resumenes');
        $builder->where('ticket', $ticket);
        return $builder->update($data);
    }

    public function getResumenes($empresa_ruc, $limit = 50) {
        $builder = $this->db->table('sunat_resumenes');
        $builder->where('empresa_ruc', $empresa_ruc);
        $builder->orderBy('fecha_generacion', 'DESC');
        $builder->limit($limit);
        return $builder->get()->getResult();
    }

    public function getResumenPendientePorTicket($ticket) {
        $builder = $this->db->table('sunat_resumenes');
        $builder->where('ticket', $ticket);
        return $builder->get()->getRow();
    }

    public function getComprobantesHistorial($limit = 200) {
        $sql = "SELECT TOP ? * FROM dbo.sunat_comprobantes ORDER BY fecha_emision DESC, id DESC";
        return $this->db->query($sql, [(int)$limit])->getResult();
    }

    public function insertComprobante($data) {
        $builder = $this->db->table('sunat_comprobantes');
        $builder->insert($data);
        return $this->db->insertID();
    }

    public function updateComprobante($empresa_ruc, $tipo_doc, $serie, $correlativo, $data) {
        $builder = $this->db->table('sunat_comprobantes');
        $builder->where('empresa_ruc', $empresa_ruc);
        $builder->where('tipo_doc', $tipo_doc);
        $builder->where('serie', $serie);
        $builder->where('correlativo', $correlativo);
        return $builder->update($data);
    }

    public function getComprobantes($empresa_ruc, $limit = 50) {
        $builder = $this->db->table('sunat_comprobantes');
        $builder->where('empresa_ruc', $empresa_ruc);
        $builder->orderBy('fecha_emision', 'DESC');
        $builder->limit($limit);
        return $builder->get()->getResult();
    }
}
