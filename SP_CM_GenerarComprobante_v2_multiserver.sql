-- ==========================================================
-- SP_CM_GenerarComprobante v2 - MULTISERVIDOR
-- Genera comprobantes (Boleta/Factura/Guia) para Consultorio
-- Inserta en el servidor local correspondiente segun @LocalPago
-- Usa CM_SERIE_DOCUMENTOS como contador correlativo centralizado
-- Si el servidor local no esta disponible, fallback al principal
-- ==========================================================
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SP_CM_GenerarComprobante]') AND type in (N'P', N'PC'))
    DROP PROCEDURE [dbo].[SP_CM_GenerarComprobante]
GO

CREATE PROCEDURE [dbo].[SP_CM_GenerarComprobante]
    @HorarioId INT,
    @PacienteId INT,
    @ClienteId INT,
    @LocalPago VARCHAR(2),    -- '01' Centro (principal), '02' Juanjuicillo, '03' Pena
    @TipoComprobante CHAR(1) = 'B',  -- 'B' Boleta, 'F' Factura, 'G' Guia
    @FormaPago VARCHAR(20) = 'EFECTIVO',
    @MontoTotal DECIMAL(11,2),
    @CodArtServicio INT = 0,
    @CitaId INT = 0,          -- Si > 0, actualiza CM_CITAS dentro de la transaccion
    @NumFac INT OUTPUT,       -- Correlativo generado
    @NumSer INT OUTPUT,       -- Serie usada
    @Resultado VARCHAR(500) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT OFF;

    DECLARE @CodCia CHAR(2) = '25'
    DECLARE @TipMov INT = 10
    DECLARE @NumOper INT
    DECLARE @Subtotal DECIMAL(11,2) = 0
    DECLARE @IGV DECIMAL(11,2) = 0
    DECLARE @FechaHoy DATETIME = CONVERT(DATE, GETDATE())
    DECLARE @HoraAllog DATETIME = GETDATE()
    DECLARE @HoraActual VARCHAR(12) = CONVERT(VARCHAR(12), GETDATE(), 108)
    DECLARE @ServidorDestino VARCHAR(50) = ''
    DECLARE @DbPrefix VARCHAR(200) = ''
    DECLARE @Sql NVARCHAR(MAX)
    DECLARE @Dummy INT
    DECLARE @RemoteAvailable INT = 0
    DECLARE @EsRemoto INT = 0
    DECLARE @TipoDoc VARCHAR(2)           -- '01' Factura, '03' Boleta, '09' Guia
    DECLARE @SerieConfigId INT            -- ID en CM_SERIE_DOCUMENTOS
    DECLARE @Prefijo VARCHAR(4)           -- Ej. BC11, FC11
    DECLARE @RefComp VARCHAR(100)         -- Referencia B-91-1234

    SET @Subtotal = @MontoTotal
    SET @IGV = 0

    -- Mapear FBG a tipo_documento de CM_SERIE_DOCUMENTOS
    SET @TipoDoc = CASE @TipoComprobante 
                    WHEN 'F' THEN '01' 
                    WHEN 'G' THEN '09' 
                    ELSE '03' 
                   END

    -- Buscar configuracion de series en CM_SERIE_DOCUMENTOS
    SELECT @SerieConfigId = id, @Prefijo = prefijo, @NumSer = serie_actual
    FROM CM_SERIE_DOCUMENTOS 
    WHERE local_id = CAST(@LocalPago AS INT) 
      AND tipo_documento = @TipoDoc
      AND tipo_servicio = 'CONSULTORIO' 
      AND estado = 1

    -- Si no hay registro en CM_SERIE_DOCUMENTOS, usar valores por defecto
    IF @SerieConfigId IS NULL
    BEGIN
        SET @NumSer = CASE @LocalPago 
            WHEN '01' THEN 21
            WHEN '02' THEN 20  
            WHEN '03' THEN 22
            ELSE 21
        END
        SET @Prefijo = NULL
    END

    -- Si no se especifico articulo, buscar el primero de la familia Consultorio
    IF @CodArtServicio = 0
        SELECT TOP 1 @CodArtServicio = ART_KEY FROM ARTI WHERE ART_FAMILIA = 594 AND ART_SITUACION = 0

    IF @CodArtServicio IS NULL OR @CodArtServicio = 0
    BEGIN
        SET @Resultado = 'ERROR: No se encontro un articulo de servicio medico (ART_FAMILIA=594)'
        RETURN
    END

    -- Determinar servidor destino
    IF @LocalPago = '02'
        SET @ServidorDestino = 'SERVER02'
    ELSE IF @LocalPago = '03'
        SET @ServidorDestino = 'SERVER03'
    ELSE
        SET @ServidorDestino = ''

    -- =========================================================================
    -- VERIFICAR CONEXION AL SERVIDOR REMOTO
    -- =========================================================================
    IF @ServidorDestino <> ''
    BEGIN
        BEGIN TRY
            SET @Sql = 'SELECT @d = 1 FROM [' + @ServidorDestino + '].[master].[sys].[databases] WHERE 1=0'
            EXEC sp_executesql @Sql, N'@d INT OUTPUT', @d = @Dummy OUTPUT
            SET @RemoteAvailable = 1
        END TRY
        BEGIN CATCH
            SET @RemoteAvailable = 0
        END CATCH
    END

    -- =========================================================================
    -- CASO REMOTO: insertar en el linked server
    -- =========================================================================
    IF @RemoteAvailable = 1
    BEGIN
        SET @DbPrefix = '[' + @ServidorDestino + '].[BDATOS].'
        SET @EsRemoto = 1

        BEGIN TRY
            -- Leer el ultimo correlativo del remoto para evitar colisiones
            SET @Sql = '
                SELECT @nf = ISNULL(MAX(FAR_NUMFAC), 0) + 1 
                FROM ' + @DbPrefix + '[dbo].[FACART] 
                WHERE FAR_CODCIA = @cc AND FAR_NUMSER = @ns AND FAR_FBG = @fbg'
            EXEC sp_executesql @Sql, N'@nf INT OUTPUT, @cc CHAR(2), @ns INT, @fbg CHAR(1)', 
                @nf = @NumFac OUTPUT, @cc = @CodCia, @ns = @NumSer, @fbg = @TipoComprobante

            -- Si tenemos CM_SERIE_DOCUMENTOS y su correlativo es mayor, usamos ese
            IF @SerieConfigId IS NOT NULL AND @SerieConfigId > 0
            BEGIN
                DECLARE @NextCorr INT = @NumSer + 1  -- serie_actual es el ultimo usado, mas 1 es el siguiente
                IF @NextCorr > @NumFac
                    SET @NumFac = @NextCorr
            END

            -- Obtener siguiente numero de operacion en el remoto
            SET @Sql = '
                SELECT @no = ISNULL(MAX(ALL_NUMOPER), 0) + 1 
                FROM ' + @DbPrefix + '[dbo].[ALLOG] 
                WHERE CONVERT(DATE, ALL_FECHA_DIA) = CONVERT(DATE, GETDATE())'
            EXEC sp_executesql @Sql, N'@no INT OUTPUT', @no = @NumOper OUTPUT

            -- Insertar ALLOG en el remoto
            SET @Sql = '
            INSERT INTO ' + @DbPrefix + '[dbo].[ALLOG] (
                ALL_CODCIA, ALL_FECHA_DIA, ALL_NUMOPER, ALL_CODTRA, ALL_FLAG_EXT, ALL_CODCLIE, 
                ALL_CODART, ALL_IMPORTE_AMORT, ALL_IMPORTE, ALL_CHESER, ALL_SECUENCIA, ALL_IMPORTE_DOLL, 
                ALL_CODUSU, ALL_PRECIO, ALL_CODVEN, ALL_FBG, ALL_CP, ALL_TIPDOC, ALL_CANTIDAD, 
                ALL_NUMGUIA, ALL_CODBAN, ALL_AUTOCON, ALL_CHENUM, ALL_CHESEC, ALL_NUMSER, ALL_NUMFAC, 
                ALL_FECHA_VCTO, ALL_NETO, ALL_BRUTO, ALL_GASTOS, ALL_IMPTO, ALL_DESCTO, ALL_MONEDA_CAJA, 
                ALL_MONEDA_CCM, ALL_MONEDA_CLI, ALL_NUMDOC, ALL_LIMCRE_ANT, ALL_LIMCRE_ACT, 
                ALL_TIPO_BLOQ_ANT, ALL_TIPO_BLOQ_ACT, ALL_CANT_CHEQ, ALL_NUM_INI, ALL_NUM_OPER2, 
                ALL_SIGNO_ARM, ALL_CODTRA_EXT, ALL_SIGNO_CCM, ALL_SIGNO_CAR, ALL_SIGNO_CAJA, ALL_TIPMOV, 
                ALL_NUMSER_C, ALL_NUMFAC_C, ALL_SERDOC, ALL_TIPO_CAMBIO, ALL_FLETE, ALL_SUBTRA, 
                ALL_HORA, ALL_FACART, ALL_CONCEPTO, ALL_NUMOPER2, ALL_FECHA_ANT, ALL_SITUACION, 
                ALL_FECHA_SUNAT, ALL_FLAG_SO, ALL_IMPG1, ALL_IMPG2, ALL_CTAG1, ALL_CTAG2, ALL_CODSUNAT, 
                ALL_FECHA_PRO, ALL_FECHA_CAN, ALL_SERIE_REC, ALL_NUM_RECIBO, ALL_RUC, ALL_CC, ALL_ZONACC, 
                ALL_CODCAJ, ALL_ESTADO_FE, ALL_DOC_ELECTRONICO, ALL_CAJA_NRO, ALL_CAJA_ESTADO
            ) VALUES (
                @cc, @fh, @no, 2401, ''N'', @cli, 
                0, @mt, @mt, ''i_c'', 0, 0, 
                ''OPER03    '', 0, 0, @fbg, ''C'', ''  '', 0, 
                0, 0, NULL, 0, ''0  '', CAST(@ns AS CHAR(3)), @nf, 
                @fh, @sub, @mt, 0, @igv, 0, ''S'', 
                '' '', ''S'', 0, 0, 0, 
                NULL, NULL, 0, NULL, 0, 
                1, NULL, 0, 1, 0, @tm, 
                ''0  '', 0, 0, NULL, 0, NULL, 
                @ha, NULL, ''SERVICIOS DE CONSULTORIO'', NULL, @fh, NULL, 
                @fh, '' '', 0, 0, NULL, NULL, 0, 
                @fh, @fh, 0, 0, ''            '', 0, 0, 
                1, NULL, '' '', 0, 1
            )'
            EXEC sp_executesql @Sql, 
                N'@cc CHAR(2), @fh DATETIME, @no INT, @cli INT, @mt DECIMAL(11,2), @fbg CHAR(1), @ns INT, @nf INT, @sub DECIMAL(11,2), @igv DECIMAL(11,2), @ha VARCHAR(12), @tm INT',
                @cc = @CodCia, @fh = @FechaHoy, @no = @NumOper, @cli = @ClienteId, @mt = @MontoTotal, 
                @fbg = @TipoComprobante, @ns = @NumSer, @nf = @NumFac, @sub = @Subtotal, @igv = @IGV, 
                @ha = @HoraActual, @tm = @TipMov

            -- Insertar FACART en el remoto
            SET @Sql = '
            INSERT INTO ' + @DbPrefix + '[dbo].[FACART] (
                FAR_TIPMOV, FAR_CODCIA, FAR_NUMSER, FAR_FBG, FAR_NUMFAC, FAR_NUMSEC, FAR_FECHA, 
                FAR_NUMOPER, FAR_CODCLIE, FAR_CODART, FAR_TRANSITO, FAR_ESTADO, FAR_NUMGUIA, 
                FAR_DIAS, FAR_SIGNO_ARM, FAR_PRECIO, FAR_STOCK, FAR_COSPRO, FAR_IMPTO, 
                FAR_TOT_DESCTO, FAR_DESCTO, FAR_GASTOS, FAR_BRUTO, FAR_EQUIV, FAR_PORDESCTO1, 
                FAR_TIPO_CAMBIO, FAR_OTRA_CIA, FAR_NUMSER_C, FAR_NUMFAC_C, FAR_NUMDOC, FAR_CP, 
                FAR_LIMCRE_ANT, FAR_LIMCRE_ACT, FAR_TIPO_BLOQ_ANT1, FAR_TIPO_BLOQ_ANT2, 
                FAR_KEY_DIRCLI, FAR_RUC, FAR_TIPO_BLOQ_ACT1, FAR_DOCCLI, FAR_DIRCLI, FAR_CLIENTE, 
                FAR_PRECIO_NETO, FAR_CODVEN, FAR_UNIDADES, FAR_LITRO, FAR_FECHA_COMPRA, FAR_NUM_LOTE, 
                FAR_CANTIDAD, FAR_SIGNO_LOT, FAR_CONCEPTO, FAR_COD_SUNAT, FAR_FLETE, FAR_CODART_REF, 
                FAR_JABAS, FAR_DESCRI, FAR_MORTAL, FAR_PESO, FAR_TOT_FLETE, FAR_EX_IGV, FAR_SIGNO_CAR, 
                FAR_NUM_PRECIO, FAR_FACTURACION_IGV, FAR_SUBTRA, FAR_PEDSER, FAR_PEDFAC, FAR_PEDSEC, 
                FAR_ORDEN_UNIDADES, FAR_CODUSU, FAR_MONEDA, FAR_COSTEO, FAR_COSPRO_ANT, FAR_COSTEO_REAL, 
                FAR_HORA, FAR_SERGUIA, FAR_CANTIDAD_P, FAR_TURNO, FAR_TIPDOC, FAR_ESTADO2, 
                FAR_PORDESCTOS, FAR_FLAG_SO, FAR_NUMOPER2, FAR_OC, FAR_COSPRO_SUP, FAR_FECHA_PRO, 
                FAR_FECHA_CAN, FAR_SUBTOTAL, FAR_CODLOT, FAR_ESTADO_FE, FAR_DOC_ELECTRONICO
            ) VALUES (
                @tm, @cc, CAST(@ns AS CHAR(3)), @fbg, @nf, 1, @fh, 
                @no, @cli, @art, '' '', ''N'', 0, 
                0, -1, @mt, 0, 0.0488, 0, 
                0, 0, 0, @mt, 1, 0, 
                0, ''  '', ''0  '', 0, 0, ''C'', 
                0, 0, NULL, NULL, 
                1, ''            '', ''B'', ''          '', '''', ''Paciente Clinico'', 
                0, 9, 0, 0, @fh, 20, 
                1, 0, ''SERVICIO CONSULTORIO'', ''3 '', 0, 0, 
                0, ''UND            '', 0, 0, 0, ''A'', 0, 
                ''1'', NULL, ''SERVICIO CONSULTORIO'', 0, 0, 0, 
                0, ''OPER03    '', ''S'', '' '', 0.0488, '' '', 
                @ha, 0, 1, 1, ''FA'', ''N'',
                ''          '', ''A'', @no, '''', 0, @fh, 
                @fh, @mt, NULL, NULL, ''''
            )'
            EXEC sp_executesql @Sql,
                N'@tm INT, @cc CHAR(2), @ns INT, @fbg CHAR(1), @nf INT, @fh DATETIME, @no INT, @cli INT, @art INT, @mt DECIMAL(11,2), @ha VARCHAR(12)',
                @tm = @TipMov, @cc = @CodCia, @ns = @NumSer, @fbg = @TipoComprobante, @nf = @NumFac, 
                @fh = @FechaHoy, @no = @NumOper, @cli = @ClienteId, @art = @CodArtServicio, 
                @mt = @MontoTotal, @ha = @HoraActual

            -- Actualizar CM_SERIE_DOCUMENTOS en el servidor PRINCIPAL
            IF @SerieConfigId IS NOT NULL AND @SerieConfigId > 0
                UPDATE CM_SERIE_DOCUMENTOS SET serie_actual = @NumFac WHERE id = @SerieConfigId

            SET @Resultado = 'SUCCESS-REMOTO-' + @ServidorDestino
        END TRY
        BEGIN CATCH
            SET @RemoteAvailable = 0
            SET @EsRemoto = 0
            SET @DbPrefix = ''
        END CATCH
    END

    -- =========================================================================
    -- CASO LOCAL / FALLBACK: insertar en el servidor principal
    -- =========================================================================
    IF @RemoteAvailable = 0
    BEGIN
        BEGIN TRY
            BEGIN TRANSACTION

            -- Obtener correlativo: desde CM_SERIE_DOCUMENTOS o MAX+1
            IF @SerieConfigId IS NOT NULL AND @SerieConfigId > 0
            BEGIN
                SET @NumFac = @NumSer + 1  -- serie_actual es el ultimo, +1 = siguiente
            END
            ELSE
            BEGIN
                SELECT @NumFac = ISNULL(MAX(FAR_NUMFAC), 0) + 1 
                FROM FACART 
                WHERE FAR_CODCIA = @CodCia AND FAR_NUMSER = @NumSer AND FAR_FBG = @TipoComprobante
            END
            
            SELECT @NumOper = ISNULL(MAX(ALL_NUMOPER), 0) + 1 
            FROM ALLOG 
            WHERE CONVERT(DATE, ALL_FECHA_DIA) = CONVERT(DATE, GETDATE())

            INSERT INTO ALLOG (
                ALL_CODCIA, ALL_FECHA_DIA, ALL_NUMOPER, ALL_CODTRA, ALL_FLAG_EXT, ALL_CODCLIE, 
                ALL_CODART, ALL_IMPORTE_AMORT, ALL_IMPORTE, ALL_CHESER, ALL_SECUENCIA, ALL_IMPORTE_DOLL, 
                ALL_CODUSU, ALL_PRECIO, ALL_CODVEN, ALL_FBG, ALL_CP, ALL_TIPDOC, ALL_CANTIDAD, 
                ALL_NUMGUIA, ALL_CODBAN, ALL_AUTOCON, ALL_CHENUM, ALL_CHESEC, ALL_NUMSER, ALL_NUMFAC, 
                ALL_FECHA_VCTO, ALL_NETO, ALL_BRUTO, ALL_GASTOS, ALL_IMPTO, ALL_DESCTO, ALL_MONEDA_CAJA, 
                ALL_MONEDA_CCM, ALL_MONEDA_CLI, ALL_NUMDOC, ALL_LIMCRE_ANT, ALL_LIMCRE_ACT, 
                ALL_TIPO_BLOQ_ANT, ALL_TIPO_BLOQ_ACT, ALL_CANT_CHEQ, ALL_NUM_INI, ALL_NUM_OPER2, 
                ALL_SIGNO_ARM, ALL_CODTRA_EXT, ALL_SIGNO_CCM, ALL_SIGNO_CAR, ALL_SIGNO_CAJA, ALL_TIPMOV, 
                ALL_NUMSER_C, ALL_NUMFAC_C, ALL_SERDOC, ALL_TIPO_CAMBIO, ALL_FLETE, ALL_SUBTRA, 
                ALL_HORA, ALL_FACART, ALL_CONCEPTO, ALL_NUMOPER2, ALL_FECHA_ANT, ALL_SITUACION, 
                ALL_FECHA_SUNAT, ALL_FLAG_SO, ALL_IMPG1, ALL_IMPG2, ALL_CTAG1, ALL_CTAG2, ALL_CODSUNAT, 
                ALL_FECHA_PRO, ALL_FECHA_CAN, ALL_SERIE_REC, ALL_NUM_RECIBO, ALL_RUC, ALL_CC, ALL_ZONACC, 
                ALL_CODCAJ, ALL_ESTADO_FE, ALL_DOC_ELECTRONICO, ALL_CAJA_NRO, ALL_CAJA_ESTADO
            ) VALUES (
                @CodCia, @FechaHoy, @NumOper, 2401, 'N', @ClienteId, 
                0, @MontoTotal, @MontoTotal, 'i_c', 0, 0, 
                'OPER03    ', 0, 0, @TipoComprobante, 'C', '  ', 0, 
                0, 0, NULL, 0, '0  ', CAST(@NumSer AS CHAR(3)), @NumFac, 
                @FechaHoy, @Subtotal, @MontoTotal, 0, @IGV, 0, 'S', 
                ' ', 'S', 0, 0, 0, 
                NULL, NULL, 0, NULL, 0, 
                1, NULL, 0, 1, 0, @TipMov, 
                '0  ', 0, 0, NULL, 0, NULL, 
                @HoraAllog, NULL, 'SERVICIOS DE CONSULTORIO', NULL, @FechaHoy, NULL, 
                @FechaHoy, ' ', 0, 0, NULL, NULL, 0, 
                @FechaHoy, @FechaHoy, 0, 0, '            ', 0, 0, 
                1, NULL, ' ', 0, 1
            )

            IF @@ROWCOUNT = 0
            BEGIN
                SET @Resultado = 'ERROR: No se pudo insertar en ALLOG'
                ROLLBACK TRANSACTION
                RETURN
            END

            INSERT INTO FACART (
                FAR_TIPMOV, FAR_CODCIA, FAR_NUMSER, FAR_FBG, FAR_NUMFAC, FAR_NUMSEC, FAR_FECHA, 
                FAR_NUMOPER, FAR_CODCLIE, FAR_CODART, FAR_TRANSITO, FAR_ESTADO, FAR_NUMGUIA, 
                FAR_DIAS, FAR_SIGNO_ARM, FAR_PRECIO, FAR_STOCK, FAR_COSPRO, FAR_IMPTO, 
                FAR_TOT_DESCTO, FAR_DESCTO, FAR_GASTOS, FAR_BRUTO, FAR_EQUIV, FAR_PORDESCTO1, 
                FAR_TIPO_CAMBIO, FAR_OTRA_CIA, FAR_NUMSER_C, FAR_NUMFAC_C, FAR_NUMDOC, FAR_CP, 
                FAR_LIMCRE_ANT, FAR_LIMCRE_ACT, FAR_TIPO_BLOQ_ANT1, FAR_TIPO_BLOQ_ANT2, 
                FAR_KEY_DIRCLI, FAR_RUC, FAR_TIPO_BLOQ_ACT1, FAR_DOCCLI, FAR_DIRCLI, FAR_CLIENTE, 
                FAR_PRECIO_NETO, FAR_CODVEN, FAR_UNIDADES, FAR_LITRO, FAR_FECHA_COMPRA, FAR_NUM_LOTE, 
                FAR_CANTIDAD, FAR_SIGNO_LOT, FAR_CONCEPTO, FAR_COD_SUNAT, FAR_FLETE, FAR_CODART_REF, 
                FAR_JABAS, FAR_DESCRI, FAR_MORTAL, FAR_PESO, FAR_TOT_FLETE, FAR_EX_IGV, FAR_SIGNO_CAR, 
                FAR_NUM_PRECIO, FAR_FACTURACION_IGV, FAR_SUBTRA, FAR_PEDSER, FAR_PEDFAC, FAR_PEDSEC, 
                FAR_ORDEN_UNIDADES, FAR_CODUSU, FAR_MONEDA, FAR_COSTEO, FAR_COSPRO_ANT, FAR_COSTEO_REAL, 
                FAR_HORA, FAR_SERGUIA, FAR_CANTIDAD_P, FAR_TURNO, FAR_TIPDOC, FAR_ESTADO2, 
                FAR_PORDESCTOS, FAR_FLAG_SO, FAR_NUMOPER2, FAR_OC, FAR_COSPRO_SUP, FAR_FECHA_PRO, 
                FAR_FECHA_CAN, FAR_SUBTOTAL, FAR_CODLOT, FAR_ESTADO_FE, FAR_DOC_ELECTRONICO
            ) VALUES (
                @TipMov, @CodCia, CAST(@NumSer AS CHAR(3)), @TipoComprobante, @NumFac, 1, @FechaHoy, 
                @NumOper, @ClienteId, @CodArtServicio, ' ', 'N', 0, 
                0, -1, @MontoTotal, 0, 0.0488, 0, 
                0, 0, 0, @MontoTotal, 1, 0, 
                0, '  ', '0  ', 0, 0, 'C', 
                0, 0, NULL, NULL, 
                1, '            ', 'B', '          ', '', 'Paciente Clinico', 
                0, 9, 0, 0, @FechaHoy, 20, 
                1, 0, 'SERVICIO CONSULTORIO', '3 ', 0, 0, 
                0, 'UND            ', 0, 0, 0, 'A', 0, 
                '1', NULL, 'SERVICIO CONSULTORIO', 0, 0, 0, 
                0, 'OPER03    ', 'S', ' ', 0.0488, ' ', 
                @HoraActual, 0, 1, 1, 'FA', 'N',
                '          ', 'A', @NumOper, '', 0, @FechaHoy, 
                @FechaHoy, @MontoTotal, NULL, NULL, ''
            )

            -- Actualizar CM_CITAS
            IF @CitaId > 0
            BEGIN
                SET @RefComp = @TipoComprobante + '-' + CAST(@NumSer AS VARCHAR(5)) + '-' + CAST(@NumFac AS VARCHAR(10))
                UPDATE CM_CITAS 
                SET estado = 1, total = @MontoTotal, saldo = 0, 
                    observaciones = 'Pagado: ' + @RefComp,
                    updated_at = GETDATE() 
                WHERE id = @CitaId AND estado IN (0, 1)
                IF @@ROWCOUNT = 0
                BEGIN
                    SET @Resultado = 'ERROR: Cita no encontrada o ya pagada (ID: ' + CAST(@CitaId AS VARCHAR(10)) + ')'
                    ROLLBACK TRANSACTION
                    RETURN
                END
            END

            -- INCREMENTAR contador en CM_SERIE_DOCUMENTOS
            IF @SerieConfigId IS NOT NULL AND @SerieConfigId > 0
                UPDATE CM_SERIE_DOCUMENTOS SET serie_actual = @NumFac WHERE id = @SerieConfigId

            COMMIT TRANSACTION

            IF @EsRemoto = 1
                SET @Resultado = 'SUCCESS-FALLBACK-' + @ServidorDestino
            ELSE
                SET @Resultado = 'SUCCESS-LOCAL'
        END TRY
        BEGIN CATCH
            IF @@TRANCOUNT > 0
                ROLLBACK TRANSACTION
            SET @Resultado = 'ERROR: ' + ERROR_MESSAGE()
        END CATCH
    END
END
GO
