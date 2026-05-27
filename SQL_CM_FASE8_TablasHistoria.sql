-- ==========================================================
-- FASE 8: TABLAS CM_HISTORIA, CM_DIAGNOSTICO, CM_RECETA
-- ==========================================================
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- 1. CM_HISTORIA (Triaje + Atención)
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_HISTORIA]') AND type = 'U')
BEGIN
    CREATE TABLE CM_HISTORIA (
        id INT IDENTITY(1,1) NOT NULL,
        cita_id INT NOT NULL,
        paciente_id INT NOT NULL,
        
        -- Triaje (signos vitales)
        presion_arterial VARCHAR(10) NULL,
        temperatura DECIMAL(4,1) NULL,
        peso DECIMAL(5,2) NULL,
        talla DECIMAL(5,2) NULL,
        saturacion INT NULL,
        frec_cardiaca INT NULL,
        frec_respiratoria INT NULL,
        
        -- Atención médica
        examen_clinico VARCHAR(MAX) NULL,
        plan_trabajo VARCHAR(MAX) NULL,
        indicaciones VARCHAR(MAX) NULL,
        
        -- Estado
        estado TINYINT NOT NULL DEFAULT 0, -- 0=triaje, 1=atendido
        created_at DATETIME NOT NULL DEFAULT GETDATE(),
        updated_at DATETIME NULL,
        
        CONSTRAINT PK_CM_HISTORIA PRIMARY KEY (id),
        CONSTRAINT FK_CM_HISTORIA_CITA FOREIGN KEY (cita_id) REFERENCES CM_CITAS(id),
        CONSTRAINT FK_CM_HISTORIA_PACIENTE FOREIGN KEY (paciente_id) REFERENCES CM_PACIENTES(id)
    )
END
GO

-- 2. CM_HISTORIA_DIAGNOSTICO (Diagnósticos CIE-10)
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_HISTORIA_DIAGNOSTICO]') AND type = 'U')
BEGIN
    CREATE TABLE CM_HISTORIA_DIAGNOSTICO (
        id INT IDENTITY(1,1) NOT NULL,
        historia_id INT NOT NULL,
        cie_codigo VARCHAR(10) NOT NULL,
        cie_descripcion VARCHAR(500) NULL,
        tipo VARCHAR(20) NULL, -- DEFINITIVO, PRESUNTIVO
        caso VARCHAR(20) NULL, -- NUEVO, REPETIDO
        alta VARCHAR(5) NULL, -- SI, NO
        
        CONSTRAINT PK_CM_HISTORIA_DIAGNOSTICO PRIMARY KEY (id),
        CONSTRAINT FK_CM_HDIAG_HISTORIA FOREIGN KEY (historia_id) REFERENCES CM_HISTORIA(id)
    )
END
GO

-- 3. CM_HISTORIA_RECETA (Prescripciones)
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[CM_HISTORIA_RECETA]') AND type = 'U')
BEGIN
    CREATE TABLE CM_HISTORIA_RECETA (
        id INT IDENTITY(1,1) NOT NULL,
        historia_id INT NOT NULL,
        art_key INT NULL,
        nombre_articulo VARCHAR(200) NULL,
        cantidad INT NOT NULL DEFAULT 1,
        dias INT NOT NULL DEFAULT 1,
        indicaciones VARCHAR(500) NULL,
        
        CONSTRAINT PK_CM_HISTORIA_RECETA PRIMARY KEY (id),
        CONSTRAINT FK_CM_HRECETA_HISTORIA FOREIGN KEY (historia_id) REFERENCES CM_HISTORIA(id)
    )
END
GO

PRINT 'FASE 8: Tablas creadas correctamente';
GO
