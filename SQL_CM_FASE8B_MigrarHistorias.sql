-- ==========================================================
-- FASE 8B: MIGRAR HISTORIAS ANTIGUAS A NUEVAS TABLAS CM
-- ==========================================================
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Mapeo: HISTORIA.HIS_CODCIT → CM_CITAS.id
-- Creamos tabla temporal para el mapeo
DECLARE @mapeo TABLE (his_codcit INT, his_codhis INT, cm_cita_id INT)

INSERT INTO @mapeo (his_codcit, his_codhis, cm_cita_id)
SELECT H.HIS_CODCIT, H.HIS_CODHIS, CC.id
FROM HISTORIA H
INNER JOIN CITAS CT ON H.HIS_CODCIT = CT.CIT_CODCIT
INNER JOIN CM_PACIENTES P ON P.cliente_id = CT.CIT_CODCLIE
INNER JOIN CAMPANIA CA ON CT.CIT_CODCAMP = CA.CAM_CODCAMP
INNER JOIN CLIENTES CM ON CA.CAM_CODMED = CM.CLI_CODCLIE AND CM.CLI_CP = 'M'
INNER JOIN CM_MEDICOS M ON M.dni COLLATE SQL_Latin1_General_CP1_CI_AS = CM.CLI_RUC_ESPOSO
INNER JOIN CM_MEDICOS_HORARIOS HOR ON HOR.medico_id = M.id 
    AND HOR.fecha_especifica = CAST(CA.CAM_FEC_INI AS DATE)
    AND HOR.hora_inicio = CONVERT(TIME(0), CA.CAM_HOR_INI, 108)
INNER JOIN CM_CITAS CC ON CC.paciente_id = P.id AND CC.horario_id = HOR.id
WHERE NOT EXISTS (SELECT 1 FROM CM_HISTORIA CH WHERE CH.cita_id = CC.id);

-- 1. Migrar HISTORIA → CM_HISTORIA
INSERT INTO CM_HISTORIA (cita_id, paciente_id, presion_arterial, temperatura, peso, talla, 
                          saturacion, frec_cardiaca, frec_respiratoria, examen_clinico, 
                          plan_trabajo, indicaciones, estado, created_at)
SELECT 
    M.cm_cita_id,
    P.id AS paciente_id,
    H.HIS_PREART_MM,
    H.HIS_TEMPERATURA,
    H.HIS_PESO,
    H.HIS_TALLA,
    H.HIS_SATURACION,
    H.HIS_FRE_CARD,
    H.HIS_FRE_RESP,
    H.HIS_EXA_CLI,
    H.HIS_PLAN_TRAB,
    H.HIS_INDICACIONES,
    ISNULL(H.HIS_ESTADO, 1),
    GETDATE()
FROM @mapeo M
INNER JOIN HISTORIA H ON H.HIS_CODHIS = M.his_codhis
INNER JOIN CM_CITAS CC ON CC.id = M.cm_cita_id
INNER JOIN CM_PACIENTES P ON P.id = CC.paciente_id;

PRINT 'Historias migradas: ' + CAST(@@ROWCOUNT AS VARCHAR);

-- 2. Migrar HISTORIA_DIAGNOSTICO → CM_HISTORIA_DIAGNOSTICO
INSERT INTO CM_HISTORIA_DIAGNOSTICO (historia_id, cie_codigo, cie_descripcion, tipo, caso, alta)
SELECT 
    CH.id,
    HD.HISD_CIE_CODIGO,
    HD.HISD_CIE_DESCRIPCION,
    CASE HD.HISD_TIPO WHEN 1 THEN 'DEFINITIVO' WHEN 2 THEN 'PRESUNTIVO' ELSE NULL END,
    CASE HD.HISD_CASO WHEN 1 THEN 'NUEVO' WHEN 2 THEN 'REPETIDO' ELSE NULL END,
    CASE HD.HISD_ALTA WHEN 1 THEN 'SI' WHEN 2 THEN 'NO' ELSE NULL END
FROM @mapeo M
INNER JOIN HISTORIA_DIAGNOSTICO HD ON HD.HISD_CODHIS = M.his_codhis
INNER JOIN CM_HISTORIA CH ON CH.cita_id = M.cm_cita_id;

PRINT 'Diagnósticos migrados: ' + CAST(@@ROWCOUNT AS VARCHAR);

-- 3. Migrar HISTORIA_RECETA → CM_HISTORIA_RECETA
INSERT INTO CM_HISTORIA_RECETA (historia_id, art_key, nombre_articulo, cantidad, dias, indicaciones)
SELECT 
    CH.id,
    HR.HISR_CODART,
    HR.HISR_NOMART,
    HR.HISR_CANT,
    HR.HISR_DIAS,
    HR.HISR_INDICACIONES
FROM @mapeo M
INNER JOIN HISTORIA_RECETA HR ON HR.HISR_CODHIS = M.his_codhis
INNER JOIN CM_HISTORIA CH ON CH.cita_id = M.cm_cita_id;

PRINT 'Recetas migradas: ' + CAST(@@ROWCOUNT AS VARCHAR);

GO

-- ==========================================================
-- VERIFICACIÓN
-- ==========================================================
SELECT 'CM_HISTORIA' AS tabla, COUNT(*) AS registros FROM CM_HISTORIA
UNION ALL
SELECT 'CM_HISTORIA_DIAGNOSTICO', COUNT(*) FROM CM_HISTORIA_DIAGNOSTICO
UNION ALL
SELECT 'CM_HISTORIA_RECETA', COUNT(*) FROM CM_HISTORIA_RECETA;
