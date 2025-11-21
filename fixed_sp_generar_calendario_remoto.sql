SET ANSI_NULLS ON
SET QUOTED_IDENTIFIER ON
GO

ALTER PROCEDURE [dbo].[sp_generar_calendario_remoto]
    @servidor_destino INT,               -- 1=principal, 2=server02, 3=server03
    @anio INT = 2026,                    -- Año a generar
    @codigo_cia VARCHAR(10) = '25',      -- Compañía
    @mensaje_salida VARCHAR(500) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @FechaInicio DATE;
    DECLARE @FechaFin DATE;
    DECLARE @TablaDestino NVARCHAR(200);
    DECLARE @sql NVARCHAR(MAX);

    -- Fechas (Usando formato ISO YYYYMMDD que es seguro)
    SET @FechaInicio = CAST(CAST(@anio AS VARCHAR(4)) + '0101' AS DATE);
    SET @FechaFin    = CAST(CAST(@anio AS VARCHAR(4)) + '1231' AS DATE);

    -- Determinar tabla en servidor destino
    SET @TablaDestino = CASE @servidor_destino
                           WHEN 1 THEN '[BDATOS].[dbo].CALENDARIO'
                           WHEN 2 THEN '[SERVER02].[BDATOS].[dbo].CALENDARIO'
                           WHEN 3 THEN '[SERVER03].[BDATOS].[dbo].CALENDARIO'
                           ELSE NULL
                        END;

    IF @TablaDestino IS NULL
    BEGIN
        SET @mensaje_salida = '0_Error: Servidor destino inválido (1-3)';
        RETURN;
    END

    BEGIN TRY
        --------------------------------------------------------------------------
        -- 1. ELIMINAR CALENDARIO DEL AÑO SI YA EXISTE EN EL DESTINO
        --------------------------------------------------------------------------
        SET @sql = N'
            DELETE FROM ' + @TablaDestino + '
            WHERE YEAR(CAL_FECHA) = ' + CAST(@anio AS NVARCHAR(4));

        EXEC (@sql);

        --------------------------------------------------------------------------
        -- 2. INSERTAR DÍAS DEL AÑO USANDO CTE (Lógica probada)
        --------------------------------------------------------------------------
        -- Se usa CONVERT(..., 112) para pasar las fechas en formato YYYYMMDD al SQL dinámico
        SET @sql = N'
            ;WITH Numeros AS (
                SELECT 0 AS Num
                UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
                UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 
                UNION ALL SELECT 9
            ),
            Centenas AS (
                SELECT (a.Num * 100 + b.Num * 10 + c.Num) AS Numero
                FROM Numeros a
                CROSS JOIN Numeros b
                CROSS JOIN Numeros c
            )
            INSERT INTO ' + @TablaDestino + ' (
                CAL_CODCIA, CAL_FECHA, CAL_LABORABLE,
                CAL_INDICE, CAL_TIPO_CAMBIO, CAL_TC_MERCA,
                CAL_TC_INGRE, CAL_TC_SALID
            )
            SELECT 
                ''' + @codigo_cia + ''',
                DATEADD(DAY, Numero, ''' + CONVERT(VARCHAR(8), @FechaInicio, 112) + '''),
                CASE 
                    WHEN DATEPART(WEEKDAY, DATEADD(DAY, Numero, ''' + CONVERT(VARCHAR(8), @FechaInicio, 112) + ''')) IN (1, 7) THEN ''N''
                    ELSE ''S''
                END,
                0, 0, 0, 0, 0
            FROM Centenas
            WHERE Numero <= DATEDIFF(DAY, ''' + CONVERT(VARCHAR(8), @FechaInicio, 112) + ''', ''' + CONVERT(VARCHAR(8), @FechaFin, 112) + ''')
            ORDER BY Numero
        ';

        EXEC (@sql);

        --------------------------------------------------------------------------
        -- Final OK
        --------------------------------------------------------------------------
        SET @mensaje_salida = '1_Calendario ' + CAST(@anio AS VARCHAR(4)) +
                              ' generado correctamente en servidor destino: ' +
                              CAST(@servidor_destino AS VARCHAR);

    END TRY
    BEGIN CATCH
        SET @mensaje_salida = '0_Error: ' + ERROR_MESSAGE() + 
                              ' Linea: ' + CAST(ERROR_LINE() AS VARCHAR);
    END CATCH

END
GO
