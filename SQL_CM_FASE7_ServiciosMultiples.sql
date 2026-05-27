-- ==========================================================
-- FASE 7: TABLA CM_CITAS_SERVICIOS (MULTI-SERVICIO POR CITA)
-- ==========================================================
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_CITAS_SERVICIOS]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[CM_CITAS_SERVICIOS](
        [id] [int] IDENTITY(1,1) NOT NULL,
        [cita_id] [int] NOT NULL,
        [art_key] [int] NOT NULL,
        [precio] [decimal](11,2) NOT NULL DEFAULT 0,
        [cantidad] [int] NOT NULL DEFAULT 1,
        [observaciones] [varchar](250) NULL,
        [created_at] [datetime] NOT NULL DEFAULT GETDATE(),
     CONSTRAINT [PK_CM_CITAS_SERVICIOS] PRIMARY KEY CLUSTERED ([id] ASC)
    )
    ALTER TABLE [dbo].[CM_CITAS_SERVICIOS] WITH CHECK ADD CONSTRAINT [FK_CM_CITAS_SERVICIOS_CITA] 
        FOREIGN KEY([cita_id]) REFERENCES [dbo].[CM_CITAS] ([id])
END
GO
PRINT 'Tabla CM_CITAS_SERVICIOS creada';
GO
