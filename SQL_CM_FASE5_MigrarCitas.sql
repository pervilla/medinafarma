-- ==========================================================
-- FASE 5: CREAR CM_CITAS + MIGRAR CITAS DE CAMPAÑAS ACTIVAS
-- ==========================================================
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- ==========================================================
-- 1. CREAR TABLA CM_CITAS
-- ==========================================================
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_CITAS]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[CM_CITAS](
        [id] [int] IDENTITY(1,1) NOT NULL,
        [paciente_id] [int] NOT NULL,           -- FK a CM_PACIENTES
        [horario_id] [int] NOT NULL,            -- FK a CM_MEDICOS_HORARIOS
        [cliente_id] [int] NOT NULL,            -- FK a CLIENTES (quién paga)
        [estado] [tinyint] NOT NULL DEFAULT 0,  -- 0=inscrito, 1=confirmado, 2=atendido, 3=anulado
        [orden] [int] NOT NULL DEFAULT 0,
        [orden_atencion] [int] NOT NULL DEFAULT 0,
        [medio_id] [int] NULL,
        [total] [decimal](11,2) NOT NULL DEFAULT 0,
        [saldo] [decimal](11,2) NOT NULL DEFAULT 0,
        [fecha] [datetime] NULL,
        [hora] [varchar](8) NULL,
        [observaciones] [varchar](500) NULL,
        [local_origen] [char](2) NULL,
        [created_at] [datetime] NOT NULL DEFAULT GETDATE(),
        [updated_at] [datetime] NULL,
     CONSTRAINT [PK_CM_CITAS] PRIMARY KEY CLUSTERED ([id] ASC)
    )

    -- Foreign Keys
    ALTER TABLE [dbo].[CM_CITAS] WITH CHECK ADD CONSTRAINT [FK_CM_CITAS_PACIENTES] 
        FOREIGN KEY([paciente_id]) REFERENCES [dbo].[CM_PACIENTES] ([id])
    ALTER TABLE [dbo].[CM_CITAS] WITH CHECK ADD CONSTRAINT [FK_CM_CITAS_HORARIOS] 
        FOREIGN KEY([horario_id]) REFERENCES [dbo].[CM_MEDICOS_HORARIOS] ([id])
END
GO

-- ==========================================================
-- 2. MIGRAR PACIENTES DESDE CLIENTES A CM_PACIENTES
--    (solo clientes CLI_CP='C' con CITAS en campañas activas)
-- ==========================================================
INSERT INTO CM_PACIENTES (cliente_id, fecha_registro, estado)
SELECT C.CLI_CODCLIE, GETDATE(), 1
FROM CLIENTES C
INNER JOIN CITAS CT ON C.CLI_CODCLIE = CT.CIT_CODCLIE
INNER JOIN CAMPANIA CA ON CT.CIT_CODCAMP = CA.CAM_CODCAMP
WHERE C.CLI_CP = 'C'
  AND CA.CAM_FEC_FIN >= CAST(GETDATE() AS DATE)
  AND NOT EXISTS (
      SELECT 1 FROM CM_PACIENTES P WHERE P.cliente_id = C.CLI_CODCLIE
  )
GROUP BY C.CLI_CODCLIE;

PRINT 'Pacientes migrados: ' + CAST(@@ROWCOUNT AS VARCHAR);

GO

-- ==========================================================
-- 3. MIGRAR TITULARES A CM_PACIENTE_RESPONSABLE
--    (cada paciente es su propio titular si no tiene otro)
-- ==========================================================
INSERT INTO CM_PACIENTE_RESPONSABLE (paciente_id, cliente_id, parentesco, titular_facturacion, fecha_registro, estado)
SELECT P.id, P.cliente_id, 'TITULAR', 1, GETDATE(), 1
FROM CM_PACIENTES P
WHERE NOT EXISTS (
    SELECT 1 FROM CM_PACIENTE_RESPONSABLE PR WHERE PR.paciente_id = P.id
);

PRINT 'Responsables migrados: ' + CAST(@@ROWCOUNT AS VARCHAR);

GO

-- ==========================================================
-- 4. MIGRAR CITAS DE CAMPAÑAS ACTIVAS
--    Mapea CITAS → CM_CITAS, vinculando horario por fecha_inicio y médico
-- ==========================================================
INSERT INTO CM_CITAS (paciente_id, horario_id, cliente_id, estado, orden, orden_atencion,
                      medio_id, total, saldo, fecha, hora, observaciones, local_origen,
                      created_at, updated_at)
SELECT 
    P.id AS paciente_id,
    H.id AS horario_id,
    CT.CIT_CODCLIE AS cliente_id,
    CT.CIT_ESTADO,
    ISNULL(CT.CIT_ORD, 0),
    ISNULL(CT.CIT_ORD_ATENCION, 0),
    CT.CIT_CODMEDIO,
    ISNULL(CT.CIT_TOTAL, 0),
    ISNULL(CT.CIT_SALDO, 0),
    CT.CIT_FECHA,
    CT.CIT_HORA,
    CT.CIT_OBSERVACIONES,
    CT.CIT_LOCAL_ORIGEN,
    GETDATE(),
    NULL
FROM CITAS CT
INNER JOIN CLIENTES C ON CT.CIT_CODCLIE = C.CLI_CODCLIE AND C.CLI_CP = 'C'
INNER JOIN CAMPANIA CA ON CT.CIT_CODCAMP = CA.CAM_CODCAMP
INNER JOIN CM_MEDICOS M ON M.cliente_id = CA.CAM_CODMED
INNER JOIN CM_MEDICOS_HORARIOS H ON H.medico_id = M.id 
    AND H.fecha_especifica = CA.CAM_FEC_INI
    AND H.hora_inicio = CONVERT(TIME(0), CA.CAM_HOR_INI, 108)
INNER JOIN CM_PACIENTES P ON P.cliente_id = CT.CIT_CODCLIE
WHERE CA.CAM_FEC_FIN >= CAST(GETDATE() AS DATE)
  AND CT.CIT_ESTADO IN (0, 1, 2)  -- Solo activos (inscrito, confirmado, atendido)
  AND NOT EXISTS (
      SELECT 1 FROM CM_CITAS NCC 
      WHERE NCC.paciente_id = P.id 
        AND NCC.horario_id = H.id
  );

PRINT 'Citas migradas: ' + CAST(@@ROWCOUNT AS VARCHAR);

GO

-- ==========================================================
-- 5. ACTUALIZAR CUPOS OCUPADOS EN CM_MEDICOS_HORARIOS
-- ==========================================================
UPDATE H
SET cupos_ocupados = (
    SELECT COUNT(*) FROM CM_CITAS CC 
    WHERE CC.horario_id = H.id 
      AND CC.estado IN (0, 1, 2)
)
FROM CM_MEDICOS_HORARIOS H;

PRINT 'Cupos actualizados en: ' + CAST(@@ROWCOUNT AS VARCHAR) + ' horarios';

GO

-- ==========================================================
-- VERIFICACIÓN
-- ==========================================================
SELECT '=== Resumen de migración ===' AS info;

SELECT 'CM_PACIENTES' AS tabla, COUNT(*) AS registros FROM CM_PACIENTES
UNION ALL
SELECT 'CM_PACIENTE_RESPONSABLE', COUNT(*) FROM CM_PACIENTE_RESPONSABLE
UNION ALL
SELECT 'CM_CITAS', COUNT(*) FROM CM_CITAS
UNION ALL
SELECT 'CM_MEDICOS_HORARIOS (cupos_ocupados)', SUM(cupos_ocupados) FROM CM_MEDICOS_HORARIOS;

SELECT '=== Citas migradas por campaña ===' AS info;
SELECT 
    (M.nombres + ' ' + M.apellidos) AS medico,
    H.fecha_especifica,
    H.hora_inicio,
    COUNT(CC.id) AS citas,
    H.cupos_ocupados
FROM CM_CITAS CC
INNER JOIN CM_MEDICOS_HORARIOS H ON CC.horario_id = H.id
INNER JOIN CM_MEDICOS M ON H.medico_id = M.id
GROUP BY M.nombres, M.apellidos, H.fecha_especifica, H.hora_inicio, H.cupos_ocupados
ORDER BY H.fecha_especifica, H.hora_inicio;
