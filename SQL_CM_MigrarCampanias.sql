-- ==========================================================
-- MIGRAR CAMPANIAS EXISTENTES A CM_MEDICOS + CM_MEDICOS_HORARIOS
-- ==========================================================
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- ==========================================================
-- RESET DE DATOS DE PRUEBA
-- Borra toda la data del módulo Consultorio y reinicia los
-- IDENTITY para empezar desde 1.
-- NOTA: se usa DELETE (no TRUNCATE) porque las tablas tienen
-- FOREIGN KEY. Luego DBCC CHECKIDENT reinicia los correlativos.
-- ==========================================================
SET NOCOUNT ON;

IF OBJECT_ID('CM_COMPROBANTE_DETALLE', 'U') IS NOT NULL DELETE FROM CM_COMPROBANTE_DETALLE;
IF OBJECT_ID('CM_COMPROBANTES', 'U') IS NOT NULL DELETE FROM CM_COMPROBANTES;
IF OBJECT_ID('CM_PAGOS', 'U') IS NOT NULL DELETE FROM CM_PAGOS;
IF OBJECT_ID('CM_CITAS_SERVICIOS', 'U') IS NOT NULL DELETE FROM CM_CITAS_SERVICIOS;
IF OBJECT_ID('CM_HISTORIA_DIAGNOSTICO', 'U') IS NOT NULL DELETE FROM CM_HISTORIA_DIAGNOSTICO;
IF OBJECT_ID('CM_HISTORIA_RECETA', 'U') IS NOT NULL DELETE FROM CM_HISTORIA_RECETA;
IF OBJECT_ID('CM_HISTORIA', 'U') IS NOT NULL DELETE FROM CM_HISTORIA;
IF OBJECT_ID('CM_CITAS', 'U') IS NOT NULL DELETE FROM CM_CITAS;
IF OBJECT_ID('CM_PACIENTE_RESPONSABLE', 'U') IS NOT NULL DELETE FROM CM_PACIENTE_RESPONSABLE;
IF OBJECT_ID('CM_PACIENTES', 'U') IS NOT NULL DELETE FROM CM_PACIENTES;
IF OBJECT_ID('CM_MEDICOS_HORARIOS', 'U') IS NOT NULL DELETE FROM CM_MEDICOS_HORARIOS;
IF OBJECT_ID('CM_MEDICOS', 'U') IS NOT NULL DELETE FROM CM_MEDICOS;

DBCC CHECKIDENT('CM_COMPROBANTE_DETALLE', RESEED, 0);
DBCC CHECKIDENT('CM_COMPROBANTES', RESEED, 0);
DBCC CHECKIDENT('CM_PAGOS', RESEED, 0);
DBCC CHECKIDENT('CM_CITAS_SERVICIOS', RESEED, 0);
DBCC CHECKIDENT('CM_HISTORIA_DIAGNOSTICO', RESEED, 0);
DBCC CHECKIDENT('CM_HISTORIA_RECETA', RESEED, 0);
DBCC CHECKIDENT('CM_HISTORIA', RESEED, 0);
DBCC CHECKIDENT('CM_CITAS', RESEED, 0);
DBCC CHECKIDENT('CM_PACIENTE_RESPONSABLE', RESEED, 0);
DBCC CHECKIDENT('CM_PACIENTES', RESEED, 0);
DBCC CHECKIDENT('CM_MEDICOS_HORARIOS', RESEED, 0);
DBCC CHECKIDENT('CM_MEDICOS', RESEED, 0);

-- Resetear correlativos de series (mantiene la configuración de series)
IF OBJECT_ID('CM_SERIE_DOCUMENTOS', 'U') IS NOT NULL
    UPDATE CM_SERIE_DOCUMENTOS SET correlativo_actual = 0 WHERE tipo_servicio = 'CONSULTORIO';

PRINT 'Datos de prueba borrados correctamente.';
GO

-- ==========================================================
-- 0. Columna cliente_id en CM_MEDICOS (para matchear directo
--    con CAMPANIA.CAM_CODMED, evitando el matching por DNI)
-- ==========================================================
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'[dbo].[CM_MEDICOS]') AND name = 'cliente_id')
    ALTER TABLE [dbo].[CM_MEDICOS] ADD [cliente_id] [int] NULL
GO

-- Paso 1: Insertar médicos desde CLIENTES (CLI_CP = 'M')
-- Solo los médicos que tengan campañas activas
INSERT INTO CM_MEDICOS (nombres, apellidos, dni, cliente_id, cmp, especialidad, telefono, estado)
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
    C.CLI_RUC_ESPOSA AS dni,
    C.CLI_CODCLIE AS cliente_id,
    C.CLI_AUTO1 AS cmp,
    C.CLI_AUTO2 AS especialidad,
    C.CLI_TELEF1 AS telefono,
    1 AS estado
FROM CLIENTES C
INNER JOIN CAMPANIA CA ON C.CLI_CODCLIE = CA.CAM_CODMED
WHERE C.CLI_CP = 'M'
  AND CA.CAM_FEC_FIN >= CAST(GETDATE() AS DATE)
  AND NOT EXISTS (
      SELECT 1 FROM CM_MEDICOS M WHERE M.cliente_id = C.CLI_CODCLIE
  )
GROUP BY C.CLI_NOMBRE, C.CLI_RUC_ESPOSA, C.CLI_CODCLIE, C.CLI_AUTO1, C.CLI_AUTO2, C.CLI_TELEF1;

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
INNER JOIN CM_MEDICOS M ON M.cliente_id = CA.CAM_CODMED
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
