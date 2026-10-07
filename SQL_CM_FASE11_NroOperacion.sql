-- ==========================================================
-- SQL_CM_FASE11_NroOperacion.sql
-- FASE 11: registrar N° de operación (YAPE) en los pagos
--
-- Agrega la columna CM_PAGOS.nro_operacion para guardar el
-- número de operación cuando el pago se hace por YAPE/Plin.
-- Idempotente: se puede ejecutar varias veces sin error.
-- ==========================================================
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT * FROM sys.columns
               WHERE object_id = OBJECT_ID(N'[dbo].[CM_PAGOS]') AND name = 'nro_operacion')
BEGIN
    ALTER TABLE [dbo].[CM_PAGOS] ADD [nro_operacion] [varchar](50) NULL;
    PRINT 'Columna nro_operacion agregada a CM_PAGOS';
END
ELSE
BEGIN
    PRINT 'La columna nro_operacion ya existe en CM_PAGOS';
END
GO
