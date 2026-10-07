-- Series de comprobantes del consultorio.
-- prefijo = serie SUNAT (B001/F001/T001), correlativo_actual = ultimo numero emitido.
-- Ajustar los prefijos a las series realmente declaradas en SUNAT.

-- Crear tabla si no existe
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_SERIE_DOCUMENTOS]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[CM_SERIE_DOCUMENTOS](
        [id] [int] IDENTITY(1,1) NOT NULL,
        [local_id] [int] NOT NULL,
        [tipo_documento] [varchar](2) NOT NULL, -- '01' Factura, '03' Boleta, '09' Guia
        [tipo_servicio] [varchar](20) NOT NULL, -- 'CONSULTORIO'
        [prefijo] [varchar](4) NULL,            -- Serie SUNAT del comprobante (B001, F001, T001)
        [serie_actual] [int] NOT NULL,          -- Numero de serie (referencia historica)
        [correlativo_actual] [int] NOT NULL DEFAULT ((0)), -- Ultimo correlativo emitido
        [estado] [bit] NOT NULL DEFAULT ((1)),
        CONSTRAINT [PK_CM_SERIE_DOCUMENTOS] PRIMARY KEY CLUSTERED ([id] ASC)
    )
END
GO

-- Insertar configuracion para Local 01 (Centro) -> Serie 21
IF NOT EXISTS (SELECT 1 FROM CM_SERIE_DOCUMENTOS WHERE local_id = 1 AND tipo_documento = '03' AND tipo_servicio = 'CONSULTORIO')
    INSERT INTO CM_SERIE_DOCUMENTOS (local_id, tipo_documento, tipo_servicio, prefijo, serie_actual, correlativo_actual, estado) VALUES (1, '03', 'CONSULTORIO', 'B021', 21, 0, 1);

IF NOT EXISTS (SELECT 1 FROM CM_SERIE_DOCUMENTOS WHERE local_id = 1 AND tipo_documento = '01' AND tipo_servicio = 'CONSULTORIO')
    INSERT INTO CM_SERIE_DOCUMENTOS (local_id, tipo_documento, tipo_servicio, prefijo, serie_actual, correlativo_actual, estado) VALUES (1, '01', 'CONSULTORIO', 'F021', 21, 0, 1);

IF NOT EXISTS (SELECT 1 FROM CM_SERIE_DOCUMENTOS WHERE local_id = 1 AND tipo_documento = '09' AND tipo_servicio = 'CONSULTORIO')
    INSERT INTO CM_SERIE_DOCUMENTOS (local_id, tipo_documento, tipo_servicio, prefijo, serie_actual, correlativo_actual, estado) VALUES (1, '09', 'CONSULTORIO', 'T021', 21, 0, 1);

-- Insertar configuracion para Local 02 (Juanjuicillo) -> Serie 20
IF NOT EXISTS (SELECT 1 FROM CM_SERIE_DOCUMENTOS WHERE local_id = 2 AND tipo_documento = '03' AND tipo_servicio = 'CONSULTORIO')
    INSERT INTO CM_SERIE_DOCUMENTOS (local_id, tipo_documento, tipo_servicio, prefijo, serie_actual, correlativo_actual, estado) VALUES (2, '03', 'CONSULTORIO', 'B020', 20, 0, 1);

IF NOT EXISTS (SELECT 1 FROM CM_SERIE_DOCUMENTOS WHERE local_id = 2 AND tipo_documento = '01' AND tipo_servicio = 'CONSULTORIO')
    INSERT INTO CM_SERIE_DOCUMENTOS (local_id, tipo_documento, tipo_servicio, prefijo, serie_actual, correlativo_actual, estado) VALUES (2, '01', 'CONSULTORIO', 'F020', 20, 0, 1);

IF NOT EXISTS (SELECT 1 FROM CM_SERIE_DOCUMENTOS WHERE local_id = 2 AND tipo_documento = '09' AND tipo_servicio = 'CONSULTORIO')
    INSERT INTO CM_SERIE_DOCUMENTOS (local_id, tipo_documento, tipo_servicio, prefijo, serie_actual, correlativo_actual, estado) VALUES (2, '09', 'CONSULTORIO', 'T020', 20, 0, 1);

-- Insertar configuracion para Local 03 (Pena) -> Serie 22
IF NOT EXISTS (SELECT 1 FROM CM_SERIE_DOCUMENTOS WHERE local_id = 3 AND tipo_documento = '03' AND tipo_servicio = 'CONSULTORIO')
    INSERT INTO CM_SERIE_DOCUMENTOS (local_id, tipo_documento, tipo_servicio, prefijo, serie_actual, correlativo_actual, estado) VALUES (3, '03', 'CONSULTORIO', 'B022', 22, 0, 1);

IF NOT EXISTS (SELECT 1 FROM CM_SERIE_DOCUMENTOS WHERE local_id = 3 AND tipo_documento = '01' AND tipo_servicio = 'CONSULTORIO')
    INSERT INTO CM_SERIE_DOCUMENTOS (local_id, tipo_documento, tipo_servicio, prefijo, serie_actual, correlativo_actual, estado) VALUES (3, '01', 'CONSULTORIO', 'F022', 22, 0, 1);

IF NOT EXISTS (SELECT 1 FROM CM_SERIE_DOCUMENTOS WHERE local_id = 3 AND tipo_documento = '09' AND tipo_servicio = 'CONSULTORIO')
    INSERT INTO CM_SERIE_DOCUMENTOS (local_id, tipo_documento, tipo_servicio, prefijo, serie_actual, correlativo_actual, estado) VALUES (3, '09', 'CONSULTORIO', 'T022', 22, 0, 1);
GO
