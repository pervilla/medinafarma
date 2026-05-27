-- ==========================================================
-- FASE 6: PRECIOS POR SERVICIO MÉDICO EN HORARIOS
-- ==========================================================

-- 1. Agregar columna de artículo de servicio al horario
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('CM_MEDICOS_HORARIOS') AND name = 'cod_art_servicio')
    ALTER TABLE CM_MEDICOS_HORARIOS ADD cod_art_servicio INT NULL;
GO

-- 2. Mapear especialidad del médico → ART_KEY del servicio
-- Usamos la especialidad del médico para buscar el artículo correspondiente
UPDATE H
SET cod_art_servicio = A.ART_KEY
FROM CM_MEDICOS_HORARIOS H
INNER JOIN CM_MEDICOS M ON M.id = H.medico_id
CROSS APPLY (
    SELECT TOP 1 ART_KEY
    FROM ARTI
    WHERE ART_FAMILIA = 594 
      AND ART_SITUACION = 0
      AND (
          ART_NOMBRE COLLATE Modern_Spanish_CI_AS LIKE '%' + M.especialidad + '%'
          OR (M.especialidad COLLATE Modern_Spanish_CI_AS LIKE '%MEDICINA GENERAL%' AND ART_NOMBRE COLLATE Modern_Spanish_CI_AS LIKE '%CONSULTA MEDICA GENERAL%')
      )
    ORDER BY 
        CASE WHEN ART_NOMBRE COLLATE Modern_Spanish_CI_AS LIKE '%' + M.especialidad + '%' THEN 0 ELSE 1 END,
        ART_KEY
) A
WHERE H.cod_art_servicio IS NULL;

-- Fallback: si no encontró por especialidad, asignar CONSULTA MEDICA GENERAL
UPDATE H
SET cod_art_servicio = (SELECT TOP 1 ART_KEY FROM ARTI WHERE ART_NOMBRE LIKE '%CONSULTA MEDICA GENERAL%' AND ART_FAMILIA = 594)
FROM CM_MEDICOS_HORARIOS H
WHERE H.cod_art_servicio IS NULL;

-- 3. Verificación
SELECT H.id, H.fecha_especifica,
       (M.nombres + ' ' + M.apellidos) AS medico,
       M.especialidad,
       H.cod_art_servicio,
       A.ART_NOMBRE AS servicio,
       P.PRE_PRE1 AS precio
FROM CM_MEDICOS_HORARIOS H
INNER JOIN CM_MEDICOS M ON M.id = H.medico_id
LEFT JOIN ARTI A ON A.ART_KEY = H.cod_art_servicio
LEFT JOIN PRECIOS P ON P.PRE_CODART = H.cod_art_servicio AND P.PRE_FLAG_UNIDAD = 'A' AND P.PRE_CODCIA = 25
WHERE H.estado = 1
ORDER BY H.fecha_especifica;
