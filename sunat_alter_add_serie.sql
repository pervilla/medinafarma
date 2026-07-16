USE [BDATOS]
GO

/****** Agregar columna serie a sunat_resumenes ******/
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'[dbo].[sunat_resumenes]') AND name = 'serie')
BEGIN
    ALTER TABLE [dbo].[sunat_resumenes] ADD [serie] [varchar](10) NULL;
    PRINT 'Columna serie agregada correctamente.';
END
ELSE
    PRINT 'La columna serie ya existe.';
GO
