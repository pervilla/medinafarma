-- ==========================================================
-- MIGRAR CAMPANIAS EXISTENTES A CM_MEDICOS + CM_MEDICOS_HORARIOS
-- ==========================================================
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Paso 1: Insertar médicos desde CLIENTES (CLI_CP = 'M')
-- Solo los médicos que tengan campañas activas
INSERT INTO CM_MEDICOS (nombres, apellidos, dni, cmp, especialidad, telefono, estado)
SELECT 
    CASE 
        WHEN CHARINDEX(' ', LTRIM(RTRIM(C.CLI_NOMBRE))) > 0 
        THEN LEFT(LTRIM(RTRIM(C.CLI_NOMBRE)), CHARINDEX(' ', LTRIM(RTRIM(C.CLI_NOMBRE))) - 1)
        ELSE LTRIM(RTRIM(C.CLI_NOMBRE))
    END AS nombres,
    CASE 
        WHEN CHARINDEX(' ', LTRIM(RTRIM(C.CLI_NOMBRE))) > 0 
        THEN SUBSTRING(LTRIM(RTRIM(C.CLI_NOMBRE)), CHARINDEX(' ', LTRIM(RTRIM(C.CLI_NOMBRE))) + 1, 100)
        ELSE ''
    END AS apellidos,
    C.CLI_RUC_ESPOSO AS dni,
    C.CLI_AUTO1 AS cmp,
    C.CLI_AUTO2 AS especialidad,
    C.CLI_TELEF1 AS telefono,
    1 AS estado
FROM CLIENTES C
INNER JOIN CAMPANIA CA ON C.CLI_CODCLIE = CA.CAM_CODMED
WHERE C.CLI_CP = 'M'
  AND CA.CAM_FEC_FIN >= CAST(GETDATE() AS DATE)
  AND NOT EXISTS (
      SELECT 1 FROM CM_MEDICOS M WHERE M.dni COLLATE SQL_Latin1_General_CP1_CI_AS = C.CLI_RUC_ESPOSO
  )
GROUP BY C.CLI_NOMBRE, C.CLI_RUC_ESPOSO, C.CLI_AUTO1, C.CLI_AUTO2, C.CLI_TELEF1;

GO

-- Paso 2: Insertar horarios desde campañas activas
INSERT INTO CM_MEDICOS_HORARIOS (medico_id, local_id, dia_semana, fecha_especifica,
                                 hora_inicio, hora_fin, cupos_totales, cupos_ocupados,
                                 tiempo_por_atencion_minutos, estado)
SELECT 
    M.id AS medico_id,
    1 AS local_id,
    DATEPART(WEEKDAY, CA.CAM_FEC_INI) AS dia_semana,
    CA.CAM_FEC_INI AS fecha_especifica,
    CONVERT(VARCHAR(5), CA.CAM_HOR_INI, 108) AS hora_inicio,
    CONVERT(VARCHAR(5), CA.CAM_HOR_FIN, 108) AS hora_fin,
    -- Calcular cupos: duración en minutos / 30 min por consulta
    CASE 
        WHEN DATEDIFF(MINUTE, CA.CAM_HOR_INI, CA.CAM_HOR_FIN) > 0 
        THEN DATEDIFF(MINUTE, CA.CAM_HOR_INI, CA.CAM_HOR_FIN) / 30
        ELSE 10
    END AS cupos_totales,
    0 AS cupos_ocupados,
    30 AS tiempo_por_atencion_minutos,
    1 AS estado
FROM CAMPANIA CA
INNER JOIN CLIENTES C ON C.CLI_CODCLIE = CA.CAM_CODMED AND C.CLI_CP = 'M'
INNER JOIN CM_MEDICOS M ON M.dni COLLATE SQL_Latin1_General_CP1_CI_AS = C.CLI_RUC_ESPOSO
WHERE CA.CAM_FEC_FIN >= CAST(GETDATE() AS DATE)
  AND NOT EXISTS (
      SELECT 1 FROM CM_MEDICOS_HORARIOS H 
      WHERE H.medico_id = M.id 
        AND H.fecha_especifica = CA.CAM_FEC_INI
        AND H.hora_inicio = CONVERT(VARCHAR(5), CA.CAM_HOR_INI, 108)
  );

GO

-- ==========================================================
-- VERIFICACIÓN
-- ==========================================================
SELECT '=== Médicos migrados ===' as info;
SELECT id, (nombres + ' ' + apellidos) as nombre, dni, especialidad FROM CM_MEDICOS WHERE estado = 1;

SELECT '=== Horarios migrados ===' as info;
SELECT h.id, (m.nombres + ' ' + m.apellidos) as medico,
       h.fecha_especifica, h.hora_inicio, h.hora_fin,
       h.cupos_totales, h.cupos_ocupados
FROM CM_MEDICOS_HORARIOS h
JOIN CM_MEDICOS m ON m.id = h.medico_id
WHERE h.estado = 1
ORDER BY h.fecha_especifica, h.hora_inicio;
