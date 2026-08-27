-- ==========================================================
-- SQL_CM_FASE9_PagosComprobantes.sql
-- FASE 9: PAGOS Y COMPROBANTES DE CONSULTORIO (v2)
-- 
-- Nuevo flujo:
--  1. Al pagar una cita (reserva o pendiente) -> SOLO se marca pagado
--     y se registra en CM_PAGOS (ticket de constancia para el cajero).
--     NO se genera comprobante SUNAT.
--  2. El dia de la consulta, la asistente emite el/los comprobantes
--     (Boleta/Factura) sobre los servicios pendientes de la cita.
--  3. Un comando separado envia esos comprobantes a SUNAT.
--
-- v2: Soportar VARIOS comprobantes por cita y VARIOS items por
--     comprobante. Se agrega CM_COMPROBANTE_DETALLE y la columna
--     facturado en CM_CITAS_SERVICIOS. Para Facturas se guardan
--     datos del cliente (razon social + RUC) en CM_COMPROBANTES.
-- ==========================================================
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- ==========================================================
-- 1. TABLA CM_PAGOS
-- Registro de cada pago/deposito de una cita de consultorio
-- ==========================================================
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_PAGOS]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[CM_PAGOS](
        [id] [int] IDENTITY(1,1) NOT NULL,
        [cita_id] [int] NOT NULL,               -- FK a CM_CITAS
        [monto] [decimal](11,2) NOT NULL,
        [forma_pago] [varchar](30) NOT NULL DEFAULT 'EFECTIVO',
        [local_pago] [char](2) NULL,            -- '01', '02', '03'
        [fecha_pago] [datetime] NOT NULL DEFAULT GETDATE(),
        [usuario_cajero] [varchar](60) NULL,
        [ticket_nro] [varchar](20) NULL,        -- Ticket de constancia (TKT-000123)
        [estado] [tinyint] NOT NULL DEFAULT 1,  -- 1=Pagado, 2=Comprobante emitido, 3=Anulado
        [created_at] [datetime] NOT NULL DEFAULT GETDATE(),
     CONSTRAINT [PK_CM_PAGOS] PRIMARY KEY CLUSTERED ([id] ASC)
    )
END
GO

IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[FK_CM_PAGOS_CITAS]') AND parent_object_id = OBJECT_ID(N'[dbo].[CM_PAGOS]'))
ALTER TABLE [dbo].[CM_PAGOS]  WITH CHECK ADD  CONSTRAINT [FK_CM_PAGOS_CITAS] FOREIGN KEY([cita_id])
REFERENCES [dbo].[CM_CITAS] ([id])
GO

-- ==========================================================
-- 2. TABLA CM_COMPROBANTES
-- Comprobante emitido el dia de la consulta por la asistente
-- v2: soporta varios por cita. Guarda datos del cliente
--     (necesarios para Facturas con RUC).
-- ==========================================================
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_COMPROBANTES]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[CM_COMPROBANTES](
        [id] [int] IDENTITY(1,1) NOT NULL,
        [cita_id] [int] NOT NULL,               -- FK a CM_CITAS
        [pago_id] [int] NULL,                   -- FK a CM_PAGOS
        [tipo_documento] [char](1) NOT NULL,    -- 'B' Boleta, 'F' Factura, 'G' Guia
        [serie] [varchar](4) NOT NULL,          -- Serie (ej. BC11, o numerica 0091)
        [correlativo] [int] NOT NULL,           -- Numeracion correlativa
        [monto] [decimal](11,2) NOT NULL,
        [fecha_emision] [datetime] NOT NULL DEFAULT GETDATE(),
        [local_id] [char](2) NULL,              -- Local donde se emitio
        [usuario_asistente] [varchar](60) NULL,
        [cliente_nombre] [varchar](120) NULL,   -- Razon social / nombre para el comprobante
        [cliente_tipo_doc] [varchar](1) NULL,   -- '1' DNI, '6' RUC
        [cliente_num_doc] [varchar](11) NULL,   -- Numero de documento (DNI o RUC)
        [estado_sunat] [tinyint] NOT NULL DEFAULT 0, -- 0=Pendiente,1=Enviado,2=Aceptado,3=Rechazado,4=Anulado
        [cdr_ticket] [varchar](50) NULL,
        [fecha_envio] [datetime] NULL,
        [observaciones] [varchar](250) NULL,
        [created_at] [datetime] NOT NULL DEFAULT GETDATE(),
     CONSTRAINT [PK_CM_COMPROBANTES] PRIMARY KEY CLUSTERED ([id] ASC)
    )
END
GO

IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[FK_CM_COMPROBANTES_CITAS]') AND parent_object_id = OBJECT_ID(N'[dbo].[CM_COMPROBANTES]'))
ALTER TABLE [dbo].[CM_COMPROBANTES]  WITH CHECK ADD  CONSTRAINT [FK_CM_COMPROBANTES_CITAS] FOREIGN KEY([cita_id])
REFERENCES [dbo].[CM_CITAS] ([id])
GO

IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[FK_CM_COMPROBANTES_PAGOS]') AND parent_object_id = OBJECT_ID(N'[dbo].[CM_COMPROBANTES]'))
ALTER TABLE [dbo].[CM_COMPROBANTES]  WITH CHECK ADD  CONSTRAINT [FK_CM_COMPROBANTES_PAGOS] FOREIGN KEY([pago_id])
REFERENCES [dbo].[CM_PAGOS] ([id])
GO

-- ==========================================================
-- 3. TABLA CM_COMPROBANTE_DETALLE
-- Items de cada comprobante (1 comprobante -> N items)
-- ==========================================================
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_COMPROBANTE_DETALLE]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[CM_COMPROBANTE_DETALLE](
        [id] [int] IDENTITY(1,1) NOT NULL,
        [comprobante_id] [int] NOT NULL,        -- FK a CM_COMPROBANTES
        [cita_servicio_id] [int] NULL,          -- FK a CM_CITAS_SERVICIOS (item original)
        [art_key] [int] NULL,                   -- Articulo en ARTI
        [descripcion] [varchar](200) NOT NULL,  -- Descripcion del item
        [cantidad] [decimal](13,4) NOT NULL DEFAULT 1,
        [precio] [decimal](11,2) NOT NULL,
        [subtotal] [decimal](11,2) NOT NULL,
     CONSTRAINT [PK_CM_COMPROBANTE_DETALLE] PRIMARY KEY CLUSTERED ([id] ASC)
    )
END
GO

IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[FK_CM_COMPROBANTE_DETALLE_COMP]') AND parent_object_id = OBJECT_ID(N'[dbo].[CM_COMPROBANTE_DETALLE]'))
ALTER TABLE [dbo].[CM_COMPROBANTE_DETALLE]  WITH CHECK ADD  CONSTRAINT [FK_CM_COMPROBANTE_DETALLE_COMP] FOREIGN KEY([comprobante_id])
REFERENCES [dbo].[CM_COMPROBANTES] ([id])
GO

IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[FK_CM_COMPROBANTE_DETALLE_SERV]') AND parent_object_id = OBJECT_ID(N'[dbo].[CM_COMPROBANTE_DETALLE]'))
ALTER TABLE [dbo].[CM_COMPROBANTE_DETALLE]  WITH CHECK ADD  CONSTRAINT [FK_CM_COMPROBANTE_DETALLE_SERV] FOREIGN KEY([cita_servicio_id])
REFERENCES [dbo].[CM_CITAS_SERVICIOS] ([id])
GO

-- ==========================================================
-- 4. COLUMNA facturado EN CM_CITAS_SERVICIOS
-- Marca que un servicio ya fue incluido en un comprobante
-- ==========================================================
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'[dbo].[CM_CITAS_SERVICIOS]') AND name = 'facturado')
ALTER TABLE [dbo].[CM_CITAS_SERVICIOS] ADD [facturado] [tinyint] NOT NULL DEFAULT 0
GO

-- ==========================================================
-- 5. DATOS EJEMPLO EN CM_SERIE_DOCUMENTOS
-- (Los registros deben existir por local + tipo_documento)
-- ==========================================================
-- INSERT INTO CM_SERIE_DOCUMENTOS (local_id, tipo_documento, prefijo, serie_actual, tipo_servicio, estado) VALUES
--   (1, '03', 'BC11', 0, 'CONSULTORIO', 1),  -- Local 01 Boleta
--   (1, '01', 'FC11', 0, 'CONSULTORIO', 1),  -- Local 01 Factura
--   (2, '03', 'BC20', 0, 'CONSULTORIO', 1),  -- Local 02 Boleta
--   (2, '01', 'FC20', 0, 'CONSULTORIO', 1),  -- Local 02 Factura
--   (3, '03', 'BC22', 0, 'CONSULTORIO', 1),  -- Local 03 Boleta
--   (3, '01', 'FC22', 0, 'CONSULTORIO', 1);  -- Local 03 Factura
GO
