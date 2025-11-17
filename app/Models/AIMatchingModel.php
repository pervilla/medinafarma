<?php

namespace App\Models;

use CodeIgniter\Model;

class AIMatchingModel extends Model
{
    protected $table = 'digemid_matching_suggestions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'cod_prod', 'art_key', 'score', 'status', 'validated_by', 
        'validated_at', 'metadata', 'created_at'
    ];
    protected $useTimestamps = false;
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    
    // Get unmatched DIGEMID products
    public function getUnmatchedDigemidProducts($limit = 50)
    {
        return $this->db->query("
            SELECT TOP {$limit} 
                d.Cod_Prod,
                RTRIM(d.Nom_Prod) + ' ' + 
                ISNULL(d.Concent, '') + ' ' + 
                ISNULL(d.Nom_Form_Farm, '') + ' ' + 
                ISNULL(d.Presentac, '') + ' ' + 
                ISNULL(d.Nom_Titular, '') AS Nom_Prod_Full,
                d.Nom_Prod,
                d.Concent,
                d.Nom_Form_Farm,
                d.Presentac,
                d.Nom_Titular
            FROM PRECIOS_DIGEMID d
            LEFT JOIN PRECIOS_DET_DIGEMID_MEDINA r ON d.Cod_Prod = r.Cod_Prod AND r.ESTADO = 1
            LEFT JOIN digemid_matching_suggestions s ON d.Cod_Prod = s.cod_prod
            WHERE r.Cod_Prod IS NULL
            AND (s.cod_prod IS NULL OR s.status = 'pending')
            ORDER BY d.Nom_Prod
        ")->getResultArray();
    }
    
    // Get all establishment articles
    public function getEstablishmentArticles()
    {
        return $this->db->query("
            SELECT 
                A.ART_KEY,
                RTRIM(A.ART_NOMBRE) + ' ' + ISNULL(T.TAB_NOMLARGO, '') AS ART_NOMBRE_Full,
                A.ART_NOMBRE,
                T.TAB_NOMLARGO AS Laboratorio
            FROM ARTI AS A
            LEFT JOIN TABLAS AS T ON A.ART_CODCIA = T.TAB_CODCIA 
                AND A.ART_FAMILIA = T.TAB_NUMTAB 
                AND T.TAB_TIPREG = 122
            ORDER BY A.ART_NOMBRE
        ")->getResultArray();
    }
    
    // Save matching suggestion
    public function saveSuggestion($data)
    {
        $sql = "INSERT INTO digemid_matching_suggestions 
                (cod_prod, art_key, score, status, metadata, created_at)
                VALUES (?, ?, ?, 'pending', ?, GETDATE())";
        
        return $this->db->query($sql, [
            $data['cod_prod'],
            $data['art_key'],
            $data['score'],
            json_encode($data['metadata'] ?? [])
        ]);
    }
    
    // Get pending suggestions
    public function getPendingSuggestions($limit = 100)
    {
        return $this->db->query("
            SELECT TOP {$limit}
                s.id,
                s.cod_prod,
                s.art_key,
                s.score,
                s.metadata,
                d.Nom_Prod,
                d.Concent,
                d.Nom_Form_Farm,
                a.ART_NOMBRE
            FROM digemid_matching_suggestions s
            INNER JOIN PRECIOS_DIGEMID d ON s.cod_prod = d.Cod_Prod
            INNER JOIN ARTI a ON s.art_key = a.ART_KEY
            WHERE s.status = 'pending'
            ORDER BY s.score DESC
        ")->getResultArray();
    }
    
    // Approve suggestion and create relation
    public function approveSuggestion($id, $userId)
    {
        $suggestion = $this->find($id);
        if (!$suggestion) return false;
        
        $this->db->transStart();
        
        // Create relation
        $this->db->query("
            INSERT INTO PRECIOS_DET_DIGEMID_MEDINA (Cod_Prod, PRE_CODART, ESTADO, OBSERVACION)
            VALUES (?, ?, 1, 'Auto-matched by AI')
        ", [$suggestion['cod_prod'], $suggestion['art_key']]);
        
        // Update suggestion status
        $this->db->query("
            UPDATE digemid_matching_suggestions 
            SET status = 'approved', validated_by = ?, validated_at = GETDATE()
            WHERE id = ?
        ", [$userId, $id]);
        
        // Save to learning history
        $this->saveToLearningHistory($suggestion, 'approved', $userId);
        
        $this->db->transComplete();
        
        return $this->db->transStatus();
    }
    
    // Reject suggestion
    public function rejectSuggestion($id, $userId, $reason = '')
    {
        $suggestion = $this->find($id);
        if (!$suggestion) return false;
        
        $this->db->query("
            UPDATE digemid_matching_suggestions 
            SET status = 'rejected', validated_by = ?, validated_at = GETDATE()
            WHERE id = ?
        ", [$userId, $id]);
        
        $this->saveToLearningHistory($suggestion, 'rejected', $userId);
        
        return true;
    }
    
    // Save to learning history for algorithm improvement
    private function saveToLearningHistory($suggestion, $action, $userId)
    {
        $this->db->query("
            INSERT INTO digemid_matching_history 
            (cod_prod, art_key, score, action, user_id, created_at)
            VALUES (?, ?, ?, ?, ?, GETDATE())
        ", [
            $suggestion['cod_prod'],
            $suggestion['art_key'],
            $suggestion['score'],
            $action,
            $userId
        ]);
    }
    
    // Get matching statistics
    public function getMatchingStats()
    {
        try {
            $stats = $this->db->query("
                SELECT 
                    COUNT(DISTINCT d.Cod_Prod) as total_digemid,
                    COUNT(DISTINCT r.Cod_Prod) as matched,
                    COUNT(DISTINCT CASE WHEN s.status = 'pending' THEN s.cod_prod END) as pending_suggestions,
                    AVG(CASE WHEN s.status = 'approved' THEN s.score END) as avg_approved_score
                FROM PRECIOS_DIGEMID d
                LEFT JOIN PRECIOS_DET_DIGEMID_MEDINA r ON d.Cod_Prod = r.Cod_Prod AND r.ESTADO = 1
                LEFT JOIN digemid_matching_suggestions s ON d.Cod_Prod = s.cod_prod
            ")->getRowArray();
            
            $stats['unmatched'] = $stats['total_digemid'] - $stats['matched'];
            $stats['match_rate'] = $stats['total_digemid'] > 0 
                ? round(($stats['matched'] / $stats['total_digemid']) * 100, 2) 
                : 0;
            
            return $stats;
        } catch (\Exception $e) {
            return [
                'total_digemid' => 0,
                'matched' => 0,
                'unmatched' => 0,
                'pending_suggestions' => 0,
                'avg_approved_score' => 0,
                'match_rate' => 0
            ];
        }
    }
    
    // Clear old rejected suggestions
    public function clearOldRejections($days = 30)
    {
        return $this->db->query("
            DELETE FROM digemid_matching_suggestions
            WHERE status = 'rejected' 
            AND validated_at < DATEADD(day, -{$days}, GETDATE())
        ");
    }
}
