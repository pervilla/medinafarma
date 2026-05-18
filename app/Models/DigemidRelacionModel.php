<?php

namespace App\Models;

use CodeIgniter\Model;

class DigemidRelacionModel extends Model
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ─────────────────────────────────────────
    //  BÚSQUEDAS
    // ─────────────────────────────────────────

    public function buscarProductosDigemid($termino)
    {
        $palabras = explode(' ', trim($termino));
        $where = "d.Situacion = 'ACT'";
        $params = [];

        foreach ($palabras as $p) {
            if (empty($p)) continue;
            $where .= " AND (d.Nom_Prod LIKE ? OR d.Nom_Titular LIKE ? OR d.Cod_Prod LIKE ?)";
            $params[] = "%$p%";
            $params[] = "%$p%";
            $params[] = "%$p%";
        }

        $sql = "
            SELECT TOP 50 
                d.Cod_Prod, 
                d.Nom_Prod, 
                d.Concent, 
                Nom_Form_Farm, 
                Presentac,
                Nom_Titular,
                Fracciones,
                CASE WHEN pd.Cod_Prod IS NOT NULL THEN 1 ELSE 0 END as ya_relacionado,
                pd.PRE_CODART,
                art.ART_NOMBRE as art_relacionado
            FROM PRECIOS_DIGEMID d
            LEFT JOIN PRECIOS_DET_DIGEMID_MEDINA pd ON pd.Cod_Prod = d.Cod_Prod
            LEFT JOIN ARTI art ON art.ART_KEY = pd.PRE_CODART AND art.ART_CODCIA = '25'
            WHERE {$where}
            ORDER BY d.Nom_Prod
        ";
        $query = $this->db->query($sql, $params);
        return $query->getResult();
    }

    public function buscarArticulos($termino)
    {
        $palabras = explode(' ', trim($termino));
        $where = "a.ART_SITUACION = 0 AND a.ART_CODCIA = '25'";
        $params = [];

        foreach ($palabras as $p) {
            if (empty($p)) continue;
            $where .= " AND (a.ART_NOMBRE LIKE ? OR lab.TAB_NOMLARGO LIKE ? OR a.ART_KEY LIKE ?)";
            $params[] = "%$p%";
            $params[] = "%$p%";
            $params[] = "%$p%";
        }

        $sql = "
            SELECT TOP 50 
                a.ART_KEY, 
                a.ART_NOMBRE,
                p.PRE_EQUIV as fraccion,
                lab.TAB_NOMLARGO as laboratorio
            FROM ARTI a
            LEFT JOIN PRECIOS p ON p.PRE_CODART = a.ART_KEY AND p.PRE_CODCIA = '25' AND p.PRE_FLAG_UNIDAD = 'A'
            LEFT JOIN TABLAS lab ON a.ART_FAMILIA = lab.TAB_NUMTAB AND lab.TAB_CODCIA = '25' AND lab.TAB_TIPREG = 122
            WHERE {$where}
            ORDER BY a.ART_NOMBRE
        ";
        $query = $this->db->query($sql, $params);
        return $query->getResult();
    }

    // ─────────────────────────────────────────
    //  CREAR / ELIMINAR RELACIÓN
    // ─────────────────────────────────────────

    public function crearRelacion($codProd, $preCodeart)
    {
        try {
            $existe = $this->db->query(
                "SELECT COUNT(*) as total FROM PRECIOS_DET_DIGEMID_MEDINA WHERE Cod_Prod = ?",
                [$codProd]
            )->getRow();

            if ($existe->total > 0) {
                $sql = "UPDATE PRECIOS_DET_DIGEMID_MEDINA SET PRE_CODART = ?, ESTADO = 1, OBSERVACION = NULL WHERE Cod_Prod = ?";
                $this->db->query($sql, [$preCodeart, $codProd]);
            } else {
                $sql = "INSERT INTO PRECIOS_DET_DIGEMID_MEDINA (PRE_CODART, Cod_Prod, ESTADO) VALUES (?, ?, 1)";
                $this->db->query($sql, [$preCodeart, $codProd]);
            }
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error en crearRelacion: ' . $e->getMessage());
            return false;
        }
    }

    public function eliminarRelacion($codProd)
    {
        try {
            $this->db->query("DELETE FROM PRECIOS_DET_DIGEMID_MEDINA WHERE Cod_Prod = ?", [$codProd]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ─────────────────────────────────────────
    //  LISTAR RELACIONADOS (con búsqueda)
    // ─────────────────────────────────────────

    public function obtenerRelacionados($busqueda = '', $pagina = 1, $porPagina = 50)
    {
        $inicio = ($pagina - 1) * $porPagina + 1;
        $fin = $pagina * $porPagina;
        $where = $busqueda ? "AND (dig.Nom_Prod LIKE ? OR art.ART_NOMBRE LIKE ?)" : "";
        $params = $busqueda ? ["%{$busqueda}%", "%{$busqueda}%"] : [];

        $sql = "
            SELECT * FROM (
                SELECT 
                    pd.Cod_Prod,
                    pd.PRE_CODART,
                    pd.ESTADO,
                    pd.OBSERVACION,
                    dig.Nom_Prod,
                    dig.Concent,
                    dig.Nom_Form_Farm,
                    dig.Presentac,
                    dig.Fracciones,
                    dig.Nom_Titular,
                    art.ART_NOMBRE,
                    art.ART_KEY,
                    lab.TAB_NOMLARGO as Laboratorio,
                    ROW_NUMBER() OVER (ORDER BY dig.Nom_Prod) as row_num
                FROM PRECIOS_DET_DIGEMID_MEDINA pd
                INNER JOIN PRECIOS_DIGEMID dig ON dig.Cod_Prod = pd.Cod_Prod
                INNER JOIN ARTI art ON art.ART_KEY = pd.PRE_CODART AND art.ART_CODCIA = '25'
                LEFT JOIN TABLAS lab ON art.ART_FAMILIA = lab.TAB_NUMTAB AND lab.TAB_CODCIA = '25' AND lab.TAB_TIPREG = 122
                WHERE 1=1 {$where}
            ) as t
            WHERE t.row_num BETWEEN {$inicio} AND {$fin}
        ";
        $query = $this->db->query($sql, $params);
        return $query->getResult();
    }

    public function contarRelacionados($busqueda = '')
    {
        $where = $busqueda ? "AND (dig.Nom_Prod LIKE ? OR art.ART_NOMBRE LIKE ?)" : "";
        $params = $busqueda ? ["%{$busqueda}%", "%{$busqueda}%"] : [];
        $sql = "
            SELECT COUNT(*) as total
            FROM PRECIOS_DET_DIGEMID_MEDINA pd
            INNER JOIN PRECIOS_DIGEMID dig ON dig.Cod_Prod = pd.Cod_Prod
            INNER JOIN ARTI art ON art.ART_KEY = pd.PRE_CODART AND art.ART_CODCIA = '25'
            WHERE 1=1 {$where}
        ";
        return $this->db->query($sql, $params)->getRow()->total;
    }

    // ─────────────────────────────────────────
    //  RELACIONES HUÉRFANAS
    //  (registros en DET que ya no existen en PRECIOS_DIGEMID)
    // ─────────────────────────────────────────

    public function obtenerHuerfanas()
    {
        $sql = "
            SELECT 
                det.Cod_Prod,
                det.PRE_CODART,
                det.ESTADO,
                det.OBSERVACION,
                art.ART_NOMBRE
            FROM dbo.PRECIOS_DET_DIGEMID_MEDINA det
            LEFT JOIN ARTI art ON art.ART_KEY = det.PRE_CODART AND art.ART_CODCIA = '25'
            WHERE NOT EXISTS (
                SELECT 1 
                FROM dbo.PRECIOS_DIGEMID pri
                WHERE pri.Cod_Prod = det.Cod_Prod
            )
            ORDER BY det.Cod_Prod
        ";
        $query = $this->db->query($sql);
        return $query->getResult();
    }

    public function contarHuerfanas()
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM dbo.PRECIOS_DET_DIGEMID_MEDINA det
            WHERE NOT EXISTS (
                SELECT 1 FROM dbo.PRECIOS_DIGEMID pri
                WHERE pri.Cod_Prod = det.Cod_Prod
            )
        ";
        return $this->db->query($sql)->getRow()->total;
    }

    public function eliminarHuerfanas()
    {
        try {
            $sql = "
                DELETE FROM dbo.PRECIOS_DET_DIGEMID_MEDINA
                WHERE NOT EXISTS (
                    SELECT 1 FROM dbo.PRECIOS_DIGEMID pri
                    WHERE pri.Cod_Prod = dbo.PRECIOS_DET_DIGEMID_MEDINA.Cod_Prod
                )
            ";
            $this->db->query($sql);
            return $this->db->affectedRows();
        } catch (\Exception $e) {
            return -1;
        }
    }
    
    // ─────────────────────────────────────────
    //  SIN RELACIONAR (Artículos internos sin vínculo)
    // ─────────────────────────────────────────

    public function obtenerSinRelacionar($busqueda = '', $pagina = 1, $porPagina = 50, $soloConStock = false)
    {
        $inicio = ($pagina - 1) * $porPagina + 1;
        $fin = $pagina * $porPagina;
        
        $where = "";
        $params = [];
        if (!empty($busqueda)) {
            $where .= " AND a.ART_NOMBRE LIKE ?";
            $params[] = "%{$busqueda}%";
        }
        
        if ($soloConStock) {
            $where .= " AND s.ARM_STOCK > 0";
        }

        $sql = "
            SELECT * FROM (
                SELECT
                    a.ART_KEY as Cod_Prod,
                    a.ART_NOMBRE as Nom_Prod,
                    lab.TAB_NOMLARGO as Nom_Titular,
                    s.ARM_STOCK as Stock,
                    p.PRE_EQUIV as Fracciones,
                    ROW_NUMBER() OVER (ORDER BY s.ARM_STOCK DESC, a.ART_NOMBRE ASC) as row_num
                FROM ARTI a
                INNER JOIN ARTICULO s ON s.ARM_CODART = a.ART_KEY AND s.ARM_CODCIA = a.ART_CODCIA
                LEFT JOIN PRECIOS p ON p.PRE_CODART = a.ART_KEY AND p.PRE_CODCIA = a.ART_CODCIA AND p.PRE_FLAG_UNIDAD = 'A'
                LEFT JOIN TABLAS lab ON a.ART_FAMILIA = lab.TAB_NUMTAB AND lab.TAB_CODCIA = a.ART_CODCIA AND lab.TAB_TIPREG = 122
                WHERE a.ART_SITUACION = 0
                AND NOT EXISTS (
                    SELECT 1 FROM PRECIOS_DET_DIGEMID_MEDINA det 
                    WHERE det.PRE_CODART = a.ART_KEY AND det.ESTADO = 1
                )
                {$where}
            ) as t
            WHERE t.row_num BETWEEN {$inicio} AND {$fin}
        ";
        $query = $this->db->query($sql, $params);
        return $query->getResult();
    }

    public function contarSinRelacionar($busqueda = '', $soloConStock = false)
    {
        $where = "";
        $params = [];
        if (!empty($busqueda)) {
            $where .= " AND a.ART_NOMBRE LIKE ?";
            $params[] = "%{$busqueda}%";
        }
        
        if ($soloConStock) {
            $where .= " AND s.ARM_STOCK > 0";
        }

        $sql = "
            SELECT COUNT(*) as total
            FROM ARTI a
            INNER JOIN ARTICULO s ON s.ARM_CODART = a.ART_KEY AND s.ARM_CODCIA = a.ART_CODCIA
            WHERE a.ART_SITUACION = 0
            AND NOT EXISTS (
                SELECT 1 FROM PRECIOS_DET_DIGEMID_MEDINA det 
                WHERE det.PRE_CODART = a.ART_KEY AND det.ESTADO = 1
            )
            {$where}
        ";
        return $this->db->query($sql, $params)->getRow()->total;
    }

    // ─────────────────────────────────────────
    //  ESTADÍSTICAS GENERALES
    // ─────────────────────────────────────────

    public function obtenerEstadisticas()
    {
        $sql = "
            SELECT
                (SELECT COUNT(*) FROM PRECIOS_DIGEMID WHERE Situacion = 'ACT') as total_digemid,
                (SELECT COUNT(*) FROM PRECIOS_DET_DIGEMID_MEDINA pd
                    INNER JOIN PRECIOS_DIGEMID d ON d.Cod_Prod = pd.Cod_Prod) as total_relacionados,
                (SELECT COUNT(*) FROM PRECIOS_DET_DIGEMID_MEDINA det
                    WHERE NOT EXISTS (
                        SELECT 1 FROM PRECIOS_DIGEMID pri WHERE pri.Cod_Prod = det.Cod_Prod
                    )) as total_huerfanas
        ";
        return $this->db->query($sql)->getRow();
    }

    // Compatibilidad con código anterior
    public function obtenerSinRelacionarLimitado()
    {
        return $this->obtenerSinRelacionar('', 1, 50);
    }
}
