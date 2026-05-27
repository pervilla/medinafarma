-- ==========================================================
-- FASE 1: SCRIPT DE CREACIÓN DE TABLAS - MÓDULO CONSULTORIO
-- Base de Datos: MedinaFarma
-- Compatible con: SQL Server 2008 R2
-- ==========================================================

-- 1. TABLA CM_SERIE_DOCUMENTOS
-- Para separar las series de facturación del consultorio (ej. BC11, FC11)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_SERIE_DOCUMENTOS]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[CM_SERIE_DOCUMENTOS](
        [id] [int] IDENTITY(1,1) NOT NULL,
        [local_id] [int] NOT NULL, -- FK a locales
        [tipo_documento] [varchar](2) NOT NULL, -- '01' Factura, '03' Boleta
        [prefijo] [varchar](4) NOT NULL, -- Ej. BC11, FC11
        [serie_actual] [int] NOT NULL DEFAULT 1,
        [tipo_servicio] [varchar](20) NOT NULL, -- 'FARMACIA', 'CONSULTORIO'
        [estado] [tinyint] NOT NULL DEFAULT 1,
     CONSTRAINT [PK_CM_SERIE_DOCUMENTOS] PRIMARY KEY CLUSTERED ([id] ASC)
    )
END
GO

-- 2. TABLA CM_PACIENTES
-- Relación 1:1 o 1:N con CLIENTES (El paciente clínico)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_PACIENTES]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[CM_PACIENTES](
        [id] [int] IDENTITY(1,1) NOT NULL,
        [cliente_id] [int] NOT NULL, -- FK a CLIENTES.CLI_CODCLIE
        [tipo_sangre] [varchar](5) NULL,
        [alergias] [varchar](500) NULL,
        [enfermedades_cronicas] [varchar](500) NULL,
        [contacto_emergencia] [varchar](150) NULL,
        [telefono_emergencia] [varchar](20) NULL,
        [observaciones_medicas] [varchar](max) NULL,
        [consentimiento_datos] [tinyint] NOT NULL DEFAULT 0, -- 1=Aceptó Ley 29733
        [fecha_registro] [datetime] NOT NULL DEFAULT GETDATE(),
        [estado] [tinyint] NOT NULL DEFAULT 1,
     CONSTRAINT [PK_CM_PACIENTES] PRIMARY KEY CLUSTERED ([id] ASC)
    )
END
GO

-- 3. TABLA CM_PACIENTE_RESPONSABLE
-- Relaciona quién es el titular de pago/apoderado del paciente en caso de menores o dependientes
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_PACIENTE_RESPONSABLE]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[CM_PACIENTE_RESPONSABLE](
        [id] [int] IDENTITY(1,1) NOT NULL,
        [paciente_id] [int] NOT NULL, -- FK a CM_PACIENTES.id
        [cliente_id] [int] NOT NULL,  -- FK a CLIENTES.CLI_CODCLIE (El Titular del pago/facturación)
        [parentesco] [varchar](50) NOT NULL, -- PADRE, MADRE, APODERADO, EMPRESA, TITULAR
        [titular_facturacion] [tinyint] NOT NULL DEFAULT 1, -- 1=Sí, a este se le emite la boleta
        [telefono] [varchar](20) NULL,
        [observaciones] [varchar](250) NULL,
        [fecha_registro] [datetime] NOT NULL DEFAULT GETDATE(),
        [estado] [tinyint] NOT NULL DEFAULT 1,
     CONSTRAINT [PK_CM_PACIENTE_RESPONSABLE] PRIMARY KEY CLUSTERED ([id] ASC)
    )
END
GO

-- 4. TABLA CM_MEDICOS
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_MEDICOS]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[CM_MEDICOS](
        [id] [int] IDENTITY(1,1) NOT NULL,
        [nombres] [varchar](100) NOT NULL,
        [apellidos] [varchar](100) NOT NULL,
        [dni] [varchar](15) NULL,
        [cmp] [varchar](20) NULL, -- Colegio Médico del Perú
        [rne] [varchar](20) NULL, -- Registro Nacional de Especialista
        [especialidad] [varchar](100) NULL,
        [telefono] [varchar](20) NULL,
        [estado] [tinyint] NOT NULL DEFAULT 1,
     CONSTRAINT [PK_CM_MEDICOS] PRIMARY KEY CLUSTERED ([id] ASC)
    )
END
GO

-- 5. TABLA CM_MEDICOS_HORARIOS
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_MEDICOS_HORARIOS]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[CM_MEDICOS_HORARIOS](
        [id] [int] IDENTITY(1,1) NOT NULL,
        [medico_id] [int] NOT NULL, -- FK a CM_MEDICOS
        [local_id] [int] NOT NULL,  -- FK a Locales
        [dia_semana] [tinyint] NOT NULL, -- 1=Lunes, 7=Domingo
        [fecha_especifica] [date] NULL, -- Si es nulo, es horario regular
        [hora_inicio] [time](0) NOT NULL,
        [hora_fin] [time](0) NOT NULL,
        [cupos_totales] [int] NOT NULL DEFAULT 0,
        [cupos_ocupados] [int] NOT NULL DEFAULT 0,
        [tiempo_por_atencion_minutos] [int] NOT NULL DEFAULT 15,
        [estado] [tinyint] NOT NULL DEFAULT 1,
     CONSTRAINT [PK_CM_MEDICOS_HORARIOS] PRIMARY KEY CLUSTERED ([id] ASC)
    )
END
GO

-- ==========================================================
-- AGREGAR FOREIGN KEYS (Claves Foráneas)
-- ==========================================================
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[FK_CM_PACIENTES_CLIENTES]') AND parent_object_id = OBJECT_ID(N'[dbo].[CM_PACIENTES]'))
ALTER TABLE [dbo].[CM_PACIENTES]  WITH CHECK ADD  CONSTRAINT [FK_CM_PACIENTES_CLIENTES] FOREIGN KEY([cliente_id])
REFERENCES [dbo].[CLIENTES] ([CLI_CODCLIE]) 
GO

IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[FK_CM_PACIENTE_RESP_PACIENTES]') AND parent_object_id = OBJECT_ID(N'[dbo].[CM_PACIENTE_RESPONSABLE]'))
ALTER TABLE [dbo].[CM_PACIENTE_RESPONSABLE]  WITH CHECK ADD  CONSTRAINT [FK_CM_PACIENTE_RESP_PACIENTES] FOREIGN KEY([paciente_id])
REFERENCES [dbo].[CM_PACIENTES] ([id])
GO

IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[FK_CM_PACIENTE_RESP_CLIENTES]') AND parent_object_id = OBJECT_ID(N'[dbo].[CM_PACIENTE_RESPONSABLE]'))
ALTER TABLE [dbo].[CM_PACIENTE_RESPONSABLE]  WITH CHECK ADD  CONSTRAINT [FK_CM_PACIENTE_RESP_CLIENTES] FOREIGN KEY([cliente_id])
REFERENCES [dbo].[CLIENTES] ([CLI_CODCLIE])
GO

IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[FK_CM_MEDICOS_HORARIOS_MEDICOS]') AND parent_object_id = OBJECT_ID(N'[dbo].[CM_MEDICOS_HORARIOS]'))
ALTER TABLE [dbo].[CM_MEDICOS_HORARIOS]  WITH CHECK ADD  CONSTRAINT [FK_CM_MEDICOS_HORARIOS_MEDICOS] FOREIGN KEY([medico_id])
REFERENCES [dbo].[CM_MEDICOS] ([id])
GO
