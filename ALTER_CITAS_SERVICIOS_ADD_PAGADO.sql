-- Script para agregar columna CNS_PAGADO a la tabla CITAS_SERVICIOS
-- Ejecutar en SQL Server Management Studio

-- Verificar si la columna ya existe
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'[dbo].[CITAS_SERVICIOS]') AND name = 'CNS_PAGADO')
BEGIN
    -- Agregar la columna CNS_PAGADO
    ALTER TABLE CITAS_SERVICIOS 
    ADD CNS_PAGADO BIT NULL DEFAULT 0;
    
    PRINT 'Columna CNS_PAGADO agregada exitosamente a CITAS_SERVICIOS';
    
    -- Actualizar registros existentes como no pagados
    UPDATE CITAS_SERVICIOS 
    SET CNS_PAGADO = 0 
    WHERE CNS_PAGADO IS NULL;
    
    PRINT 'Registros existentes marcados como no pagados';
END
ELSE
BEGIN
    PRINT 'La columna CNS_PAGADO ya existe en CITAS_SERVICIOS';
END

-- Verificar el resultado
SELECT TOP 5 * FROM CITAS_SERVICIOS ORDER BY CNS_CODCNS DESC;