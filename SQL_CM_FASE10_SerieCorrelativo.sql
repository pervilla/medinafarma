-- ==========================================================
-- SQL_CM_FASE10_SerieCorrelativo.sql
-- FASE 10: separar SERIE y CORRELATIVO en el consultorio
--
-- Problema: CM_SERIE_DOCUMENTOS.serie_actual se sembro como el
-- NUMERO DE SERIE (21 / 20 / 22 por local) pero el codigo lo usaba
-- como correlativo (serie_actual + 1), destruyendo la serie y
-- emitiendo comprobantes con serie vacia.
--
-- Ahora:
--   prefijo            = serie SUNAT del comprobante (B001, F001, ...)
--   correlativo_actual = ultimo correlativo emitido de esa serie
--   serie_actual       = queda solo como referencia historica
--
-- IMPORTANTE: las series deben estar declaradas en SUNAT antes de
-- emitir. Revisar los prefijos del backfill antes de usarlos en
-- produccion.
-- ==========================================================
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- 1. Correlativo independiente de la serie
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'[dbo].[CM_SERIE_DOCUMENTOS]') AND name = 'correlativo_actual')
ALTER TABLE [dbo].[CM_SERIE_DOCUMENTOS] ADD [correlativo_actual] [int] NOT NULL DEFAULT 0
GO

-- 2. Backfill del prefijo cuando esta vacio: B/F/T + numero de serie a 3 digitos
--    (03 Boleta -> B0xx, 01 Factura -> F0xx, 09 Guia -> T0xx)
UPDATE CM_SERIE_DOCUMENTOS
SET prefijo = CASE tipo_documento
                  WHEN '01' THEN 'F'
                  WHEN '09' THEN 'T'
                  ELSE 'B'
              END + RIGHT('000' + CAST(serie_actual AS VARCHAR(3)), 3)
WHERE tipo_servicio = 'CONSULTORIO' AND ISNULL(LTRIM(RTRIM(prefijo)), '') = ''
GO

-- 3. Arrancar el correlativo despues del ultimo comprobante ya emitido de esa serie
UPDATE S
SET correlativo_actual = ISNULL(C.max_corr, 0)
FROM CM_SERIE_DOCUMENTOS S
OUTER APPLY (
    SELECT MAX(correlativo) AS max_corr
    FROM CM_COMPROBANTES
    WHERE serie = S.prefijo
) C
WHERE S.tipo_servicio = 'CONSULTORIO' AND S.correlativo_actual = 0
GO

-- ==========================================================
-- 4. Unicidad: evita tickets y correlativos duplicados si dos
--    cajas emiten al mismo tiempo
-- ==========================================================
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'UX_CM_PAGOS_TICKET' AND object_id = OBJECT_ID(N'[dbo].[CM_PAGOS]'))
CREATE UNIQUE INDEX [UX_CM_PAGOS_TICKET] ON [dbo].[CM_PAGOS]([ticket_nro]) WHERE [ticket_nro] IS NOT NULL
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'UX_CM_COMPROBANTES_SERIE_CORR' AND object_id = OBJECT_ID(N'[dbo].[CM_COMPROBANTES]'))
CREATE UNIQUE INDEX [UX_CM_COMPROBANTES_SERIE_CORR] ON [dbo].[CM_COMPROBANTES]([tipo_documento], [serie], [correlativo])
GO
