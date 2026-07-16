USE [BDATOS]
GO

/****** Table: dbo.sunat_resumenes ******/
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[sunat_resumenes]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[sunat_resumenes](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[empresa_ruc] [varchar](11) NOT NULL,
	[serie] [varchar](10) NULL,
	[fecha_generacion] [date] NOT NULL,
	[fecha_resumen] [date] NOT NULL,
	[correlativo] [int] NOT NULL,
	[ticket] [varchar](50) NULL,
	[estado_sunat] [varchar](50) NULL,
	[xml_path] [varchar](255) NULL,
	[cdr_path] [varchar](255) NULL,
	[mensaje_sunat] [varchar](1000) NULL,
	[created_at] [datetime] DEFAULT GETDATE(),
	[updated_at] [datetime] DEFAULT GETDATE(),
 CONSTRAINT [PK_sunat_resumenes] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO

/****** Table: dbo.sunat_comprobantes ******/
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[sunat_comprobantes]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[sunat_comprobantes](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[empresa_ruc] [varchar](11) NOT NULL,
	[tipo_doc] [varchar](2) NOT NULL,
	[serie] [varchar](4) NOT NULL,
	[correlativo] [int] NOT NULL,
	[fecha_emision] [date] NOT NULL,
	[xml_path] [varchar](255) NULL,
	[estado_sunat] [varchar](50) NULL,
	[cdr_path] [varchar](255) NULL,
	[mensaje_sunat] [varchar](1000) NULL,
	[hash_cpe] [varchar](255) NULL,
	[created_at] [datetime] DEFAULT GETDATE(),
	[updated_at] [datetime] DEFAULT GETDATE(),
 CONSTRAINT [PK_sunat_comprobantes] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO

/****** Stored Procedure: dbo.sp_insertar_sunat_resumen ******/
IF EXISTS (SELECT * FROM sys.objects WHERE type = 'P' AND name = 'sp_insertar_sunat_resumen')
DROP PROCEDURE [dbo].[sp_insertar_sunat_resumen]
GO
CREATE PROCEDURE [dbo].[sp_insertar_sunat_resumen]
	@empresa_ruc VARCHAR(11),
	@fecha_generacion DATE,
	@fecha_resumen DATE,
	@correlativo INT,
	@ticket VARCHAR(50),
	@estado_sunat VARCHAR(50),
	@xml_path VARCHAR(255),
	@mensaje_sunat VARCHAR(1000)
AS
BEGIN
	INSERT INTO [dbo].[sunat_resumenes] (empresa_ruc, fecha_generacion, fecha_resumen, correlativo, ticket, estado_sunat, xml_path, mensaje_sunat)
	VALUES (@empresa_ruc, @fecha_generacion, @fecha_resumen, @correlativo, @ticket, @estado_sunat, @xml_path, @mensaje_sunat);
	SELECT SCOPE_IDENTITY() as id;
END
GO

/****** Stored Procedure: dbo.sp_actualizar_sunat_resumen ******/
IF EXISTS (SELECT * FROM sys.objects WHERE type = 'P' AND name = 'sp_actualizar_sunat_resumen')
DROP PROCEDURE [dbo].[sp_actualizar_sunat_resumen]
GO
CREATE PROCEDURE [dbo].[sp_actualizar_sunat_resumen]
	@ticket VARCHAR(50),
	@estado_sunat VARCHAR(50),
	@cdr_path VARCHAR(255),
	@mensaje_sunat VARCHAR(1000)
AS
BEGIN
	UPDATE [dbo].[sunat_resumenes] 
	SET estado_sunat = @estado_sunat, cdr_path = @cdr_path, mensaje_sunat = @mensaje_sunat, updated_at = GETDATE()
	WHERE ticket = @ticket;
END
GO

/****** Stored Procedure: dbo.sp_insertar_sunat_comprobante ******/
IF EXISTS (SELECT * FROM sys.objects WHERE type = 'P' AND name = 'sp_insertar_sunat_comprobante')
DROP PROCEDURE [dbo].[sp_insertar_sunat_comprobante]
GO
CREATE PROCEDURE [dbo].[sp_insertar_sunat_comprobante]
	@empresa_ruc VARCHAR(11),
	@tipo_doc VARCHAR(2),
	@serie VARCHAR(4),
	@correlativo INT,
	@fecha_emision DATE,
	@xml_path VARCHAR(255),
	@estado_sunat VARCHAR(50),
	@hash_cpe VARCHAR(255),
	@cdr_path VARCHAR(255),
	@mensaje_sunat VARCHAR(1000)
AS
BEGIN
	INSERT INTO [dbo].[sunat_comprobantes] (empresa_ruc, tipo_doc, serie, correlativo, fecha_emision, xml_path, estado_sunat, hash_cpe, cdr_path, mensaje_sunat)
	VALUES (@empresa_ruc, @tipo_doc, @serie, @correlativo, @fecha_emision, @xml_path, @estado_sunat, @hash_cpe, @cdr_path, @mensaje_sunat);
	SELECT SCOPE_IDENTITY() as id;
END
GO
