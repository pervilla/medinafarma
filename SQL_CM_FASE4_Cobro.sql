-- ==========================================================
-- FASE 4: SCRIPT PARA GENERAR COMPROBANTES DE CONSULTORIO
-- Base de Datos: MedinaFarma
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
    @ClienteId INT, -- Quien paga (Titular)
    @LocalPago VARCHAR(2), -- '01' Centro, '02' Juanjuicillo, '03' Peña
    @TipoComprobante CHAR(1) = 'B', -- 'B' Boleta, 'F' Factura
    @FormaPago VARCHAR(20) = 'EFECTIVO',
    @MontoTotal DECIMAL(11,2),
    @CodArtServicio INT = 0, -- ID del articulo "Consulta Medica" en tu BD (Si es 0, usamos uno genérico)
    @NumFac INT OUTPUT,
    @NumSer INT OUTPUT,
    @Resultado VARCHAR(500) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @CodCia CHAR(2) = '25'
    DECLARE @TipMov INT = 10
    DECLARE @NumOper INT
    DECLARE @Subtotal DECIMAL(11,2) = 0
    DECLARE @IGV DECIMAL(11,2) = 0
    
    DECLARE @FechaHoy DATETIME = CONVERT(DATE, GETDATE())
    DECLARE @HoraAllog DATETIME = GETDATE()
    DECLARE @HoraActual VARCHAR(12) = CONVERT(VARCHAR(12), GETDATE(), 108)
    
    -- Si no se especificó artículo, buscar el primero de la familia Consultorio (ART_FAMILIA=594)
    IF @CodArtServicio = 0
        SELECT TOP 1 @CodArtServicio = ART_KEY FROM ARTI WHERE ART_FAMILIA = 594 AND ART_SITUACION = 0
    
    IF @CodArtServicio IS NULL OR @CodArtServicio = 0
    BEGIN
        SET @Resultado = 'ERROR: No se encontró un artículo de servicio médico (ART_FAMILIA=594)'
        RETURN
    END
    
    SET @NumFac = 0
    SET @NumSer = 0
    SET @Resultado = ''
    
    -- Mapeo de series para el Consultorio. 
    -- Para evitar conflictos con Farmacia, usaremos Series + 70 (Ej: 91, 90, 92)
    -- Ajusta esto si ya tienes un mapeo específico para consultorio.
    SET @NumSer = CASE @LocalPago 
        WHEN '01' THEN 91
        WHEN '02' THEN 90  
        WHEN '03' THEN 92
        ELSE 91
    END
    
    -- Cálculo simple (Asumiendo que el servicio es Exonerado/Inafecto como suele ser salud. Si tiene IGV, ajusta aquí)
    SET @Subtotal = @MontoTotal
    SET @IGV = 0
    
    BEGIN TRY
        BEGIN TRANSACTION
        
        -- Obtener siguiente número de factura
        SELECT @NumFac = ISNULL(MAX(FAR_NUMFAC), 0) + 1 
        FROM FACART 
        WHERE FAR_CODCIA = @CodCia AND FAR_NUMSER = @NumSer AND FAR_FBG = @TipoComprobante
        
        -- Obtener siguiente número de operación
        SELECT @NumOper = ISNULL(MAX(ALL_NUMOPER), 0) + 1 
        FROM ALLOG 
        WHERE CONVERT(DATE, ALL_FECHA_DIA) = CONVERT(DATE, GETDATE())
        
        -- 1. Insertar Cabecera en ALLOG
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
        
        -- 2. Insertar Detalle en FACART (Un solo item de servicio médico)
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
            1, '            ', 'B', '          ', '', 'Paciente Clínico', 
            0, 9, 0, 0, @FechaHoy, 20, 
            1, 0, 'SERVICIO CONSULTORIO', '3 ', 0, 0, 
            0, 'UND            ', 0, 0, 0, 'A', 0, 
            '1', NULL, 'SERVICIO CONSULTORIO', 0, 0, 0, 
            0, 'OPER03    ', 'S', ' ', 0.0488, ' ', 
            @HoraActual, 0, 1, 1, 'FA', 'N',
            '          ', 'A', @NumOper, '', 0, @FechaHoy, 
            @FechaHoy, @MontoTotal, NULL, NULL, ''
        )

        -- 3. Registrar que la cita ha sido pagada (Opcional, en una nueva tabla CM_ATENCIONES)
        -- Si en el futuro usamos CM_MEDICOS_HORARIOS_RESERVAS, aquí iría el INSERT.
        
        COMMIT TRANSACTION
        SET @Resultado = 'SUCCESS'
        
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION
            
        SET @Resultado = 'ERROR: ' + ERROR_MESSAGE()
    END CATCH
END
GO
