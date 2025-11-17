-- ============================================
-- SQL Scripts para AI Matching Module
-- Base de datos: SQL Server 2008 R2
-- ============================================

-- 1. CREAR TABLAS (Alternativa a migración)
-- ============================================

-- Tabla de sugerencias de matching
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='digemid_matching_suggestions' AND xtype='U')
BEGIN
    CREATE TABLE digemid_matching_suggestions (
        id INT IDENTITY(1,1) PRIMARY KEY,
        cod_prod INT NOT NULL,
        art_key NUMERIC(8,0) NOT NULL,
        score DECIMAL(5,4) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        validated_by INT NULL,
        validated_at DATETIME NULL,
        metadata NVARCHAR(MAX) NULL,
        created_at DATETIME NOT NULL DEFAULT GETDATE(),
        CONSTRAINT FK_suggestions_digemid FOREIGN KEY (cod_prod) 
            REFERENCES PRECIOS_DIGEMID(Cod_Prod)
    )
    
    CREATE INDEX idx_suggestions_status ON digemid_matching_suggestions(status, score DESC)
    CREATE INDEX idx_suggestions_created ON digemid_matching_suggestions(created_at DESC)
END
GO

-- Tabla de historial de aprendizaje
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='digemid_matching_history' AND xtype='U')
BEGIN
    CREATE TABLE digemid_matching_history (
        id INT IDENTITY(1,1) PRIMARY KEY,
        cod_prod INT NOT NULL,
        art_key NUMERIC(8,0) NOT NULL,
        score DECIMAL(5,4) NOT NULL,
        action VARCHAR(20) NOT NULL,
        user_id INT NULL,
        created_at DATETIME NOT NULL DEFAULT GETDATE()
    )
    
    CREATE INDEX idx_history_action ON digemid_matching_history(action, created_at DESC)
    CREATE INDEX idx_history_product ON digemid_matching_history(cod_prod)
END
GO

-- 2. CONSULTAS ÚTILES
-- ============================================

-- Ver estadísticas generales
SELECT 
    COUNT(DISTINCT d.Cod_Prod) as total_digemid,
    COUNT(DISTINCT r.Cod_Prod) as matched,
    COUNT(DISTINCT d.Cod_Prod) - COUNT(DISTINCT r.Cod_Prod) as unmatched,
    COUNT(DISTINCT CASE WHEN s.status = 'pending' THEN s.cod_prod END) as pending_suggestions,
    CAST(COUNT(DISTINCT r.Cod_Prod) * 100.0 / COUNT(DISTINCT d.Cod_Prod) AS DECIMAL(5,2)) as match_rate
FROM PRECIOS_DIGEMID d
LEFT JOIN PRECIOS_DET_DIGEMID_MEDINA r ON d.Cod_Prod = r.Cod_Prod AND r.ESTADO = 1
LEFT JOIN digemid_matching_suggestions s ON d.Cod_Prod = s.cod_prod
GO

-- Ver productos sin emparejar (top 50)
SELECT TOP 50
    d.Cod_Prod,
    d.Nom_Prod,
    d.Concent,
    d.Nom_Form_Farm,
    d.Presentac
FROM PRECIOS_DIGEMID d
LEFT JOIN PRECIOS_DET_DIGEMID_MEDINA r ON d.Cod_Prod = r.Cod_Prod AND r.ESTADO = 1
WHERE r.Cod_Prod IS NULL
ORDER BY d.Nom_Prod
GO

-- Ver sugerencias pendientes con score alto
SELECT TOP 20
    s.id,
    s.cod_prod,
    s.art_key,
    s.score,
    d.Nom_Prod as producto_digemid,
    a.ART_NOMBRE as articulo_establecimiento,
    s.created_at
FROM digemid_matching_suggestions s
INNER JOIN PRECIOS_DIGEMID d ON s.cod_prod = d.Cod_Prod
INNER JOIN ARTI a ON s.art_key = a.ART_KEY
WHERE s.status = 'pending'
ORDER BY s.score DESC
GO

-- Ver historial de decisiones
SELECT TOP 100
    h.cod_prod,
    d.Nom_Prod,
    h.art_key,
    a.ART_NOMBRE,
    h.score,
    h.action,
    h.created_at
FROM digemid_matching_history h
INNER JOIN PRECIOS_DIGEMID d ON h.cod_prod = d.Cod_Prod
INNER JOIN ARTI a ON h.art_key = a.ART_KEY
ORDER BY h.created_at DESC
GO

-- Score promedio por acción
SELECT 
    action,
    COUNT(*) as total,
    AVG(score) as avg_score,
    MIN(score) as min_score,
    MAX(score) as max_score
FROM digemid_matching_history
GROUP BY action
GO

-- 3. MANTENIMIENTO
-- ============================================

-- Limpiar sugerencias rechazadas antiguas (más de 30 días)
DELETE FROM digemid_matching_suggestions
WHERE status = 'rejected' 
AND validated_at < DATEADD(day, -30, GETDATE())
GO

-- Limpiar sugerencias duplicadas (mantener la de mayor score)
WITH CTE AS (
    SELECT 
        id,
        ROW_NUMBER() OVER (PARTITION BY cod_prod, art_key ORDER BY score DESC) as rn
    FROM digemid_matching_suggestions
    WHERE status = 'pending'
)
DELETE FROM CTE WHERE rn > 1
GO

-- Reindexar tablas
ALTER INDEX ALL ON digemid_matching_suggestions REBUILD
ALTER INDEX ALL ON digemid_matching_history REBUILD
GO

-- 4. REPORTES
-- ============================================

-- Productos con múltiples sugerencias
SELECT 
    s.cod_prod,
    d.Nom_Prod,
    COUNT(*) as num_sugerencias,
    MAX(s.score) as mejor_score
FROM digemid_matching_suggestions s
INNER JOIN PRECIOS_DIGEMID d ON s.cod_prod = d.Cod_Prod
WHERE s.status = 'pending'
GROUP BY s.cod_prod, d.Nom_Prod
HAVING COUNT(*) > 1
ORDER BY num_sugerencias DESC
GO

-- Tasa de aprobación por rango de score
SELECT 
    CASE 
        WHEN score >= 0.85 THEN '85-100%'
        WHEN score >= 0.70 THEN '70-84%'
        WHEN score >= 0.50 THEN '50-69%'
        ELSE '<50%'
    END as score_range,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    CAST(SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) AS DECIMAL(5,2)) as approval_rate
FROM digemid_matching_suggestions
WHERE status IN ('approved', 'rejected')
GROUP BY 
    CASE 
        WHEN score >= 0.85 THEN '85-100%'
        WHEN score >= 0.70 THEN '70-84%'
        WHEN score >= 0.50 THEN '50-69%'
        ELSE '<50%'
    END
ORDER BY MIN(score) DESC
GO

-- Productos más difíciles de emparejar (sin sugerencias)
SELECT TOP 20
    d.Cod_Prod,
    d.Nom_Prod,
    d.Concent,
    d.Nom_Form_Farm
FROM PRECIOS_DIGEMID d
LEFT JOIN PRECIOS_DET_DIGEMID_MEDINA r ON d.Cod_Prod = r.Cod_Prod AND r.ESTADO = 1
LEFT JOIN digemid_matching_suggestions s ON d.Cod_Prod = s.cod_prod
WHERE r.Cod_Prod IS NULL
AND s.cod_prod IS NULL
ORDER BY d.Nom_Prod
GO

-- 5. TESTING
-- ============================================

-- Insertar sugerencia de prueba
INSERT INTO digemid_matching_suggestions (cod_prod, art_key, score, status, metadata, created_at)
VALUES ('TEST001', 'ART001', 0.95, 'pending', '{"test": true}', GETDATE())
GO

-- Verificar inserción
SELECT * FROM digemid_matching_suggestions WHERE cod_prod = 'TEST001'
GO

-- Limpiar prueba
DELETE FROM digemid_matching_suggestions WHERE cod_prod = 'TEST001'
GO

-- 6. BACKUP
-- ============================================

-- Backup de sugerencias pendientes
SELECT * 
INTO digemid_matching_suggestions_backup
FROM digemid_matching_suggestions
WHERE status = 'pending'
GO

-- Backup de historial
SELECT * 
INTO digemid_matching_history_backup
FROM digemid_matching_history
WHERE created_at >= DATEADD(month, -3, GETDATE())
GO
