-- Stored Procedure para generar comprobante de consultorio
-- SQL Server 2008 R2
CREATE PROCEDURE SP_GenerarComprobanteConsultorio
    @CitaId INT,
    @LocalPago VARCHAR(2),
    @TipoComprobante CHAR(1) = 'B',
    @FormaPago VARCHAR(20) = 'EFECTIVO',
    @Referencia VARCHAR(50) = '',
    @NumFac INT OUTPUT,
    @NumSer INT OUTPUT,
    @Total DECIMAL(11,2) OUTPUT,
    @Resultado VARCHAR(500) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @CodCia CHAR(2) = '25'
    DECLARE @TipMov INT = 10
    DECLARE @NumOper INT
    DECLARE @CodClie INT
    DECLARE @Subtotal DECIMAL(11,2) = 0
    DECLARE @IGV DECIMAL(11,2) = 0
    DECLARE @FechaHoy DATETIME = GETDATE()
    DECLARE @Error VARCHAR(500) = ''
    
    -- Mapeo de locales a series
    SET @NumSer = CASE @LocalPago 
        WHEN '01' THEN 21
        WHEN '02' THEN 20  
        WHEN '03' THEN 22
        ELSE 21
    END
    
    BEGIN TRY
        BEGIN TRANSACTION
        
        -- Obtener información de la cita
        SELECT @CodClie = CIT_CODCLIE 
        FROM CITAS 
        WHERE CIT_CODCIT = @CitaId
        
        IF @CodClie IS NULL
        BEGIN
            SET @Resultado = 'ERROR: Cita no encontrada'
            ROLLBACK TRANSACTION
            RETURN
        END
        
        -- Calcular subtotal
        SELECT @Subtotal = SUM(CNS_CANTIDAD * CNS_PRECIO)
        FROM CITAS_SERVICIOS 
        WHERE CNS_CODCIT = @CitaId
        
        IF @Subtotal IS NULL OR @Subtotal = 0
        BEGIN
            SET @Resultado = 'ERROR: No hay servicios para generar comprobante'
            ROLLBACK TRANSACTION
            RETURN
        END
        
        SET @Total = @Subtotal + @IGV
        
        -- Obtener siguiente número de factura
        SELECT @NumFac = ISNULL(MAX(FAR_NUMFAC), 0) + 1 
        FROM FACART 
        WHERE FAR_CODCIA = @CodCia AND FAR_NUMSER = @NumSer AND FAR_FBG = @TipoComprobante
        
        -- Obtener siguiente número de operación
        SELECT @NumOper = ISNULL(MAX(ALL_NUMOPER), 0) + 1 
        FROM ALLOG 
        WHERE CONVERT(DATE, ALL_FECHA_DIA) = CONVERT(DATE, @FechaHoy)
        
        -- Insertar en ALLOG
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
            @CodCia, @FechaHoy, @NumOper, 2401, 'N', @CodClie, 
            0, 0, 0, 'i_c', 0, 0, 
            'OPER03    ', 0, 0, @TipoComprobante, 'C', '  ', 0, 
            0, 0, NULL, 0, '0  ', @NumSer, @NumFac, 
            @FechaHoy, @Subtotal, @Total, 0, @IGV, 0, 'S', 
            ' ', 'S', 0, 0, 0, 
            NULL, NULL, 0, NULL, 0, 
            1, NULL, 0, 1, 0, @TipMov, 
            '0  ', 0, 0, NULL, 0, NULL, 
            @FechaHoy, NULL, 'SERVICIOS DE CONSULTORIO', NULL, @FechaHoy, NULL, 
            @FechaHoy, ' ', 0, 0, NULL, NULL, 0, 
            @FechaHoy, @FechaHoy, 0, 0, '            ', 0, 0, 
            1, NULL, ' ', 0, 1
        )
        
        -- Insertar servicios en FACART
        DECLARE @NumSec INT = 1
        DECLARE @CodArt INT, @Cantidad DECIMAL(13,4), @Precio DECIMAL(13,4), @SubtotalServicio DECIMAL(11,2)
        
        DECLARE servicios_cursor CURSOR FOR
        SELECT CNS_CODART, CNS_CANTIDAD, CNS_PRECIO, (CNS_CANTIDAD * CNS_PRECIO) as SUBTOTAL
        FROM CITAS_SERVICIOS 
        WHERE CNS_CODCIT = @CitaId
        
        OPEN servicios_cursor
        FETCH NEXT FROM servicios_cursor INTO @CodArt, @Cantidad, @Precio, @SubtotalServicio
        
        WHILE @@FETCH_STATUS = 0
        BEGIN
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
                @TipMov, @CodCia, @NumSer, @TipoComprobante, @NumFac, @NumSec, @FechaHoy, 
                @NumOper, @CodClie, @CodArt, ' ', 'N', 0, 
                0, -1, @Precio, 94, 0.0488, 0, 
                0, 0, 0, @SubtotalServicio, 1, 0, 
                0, '  ', '0  ', 0, 0, 'C', 
                0, 0, NULL, NULL, 
                1, '            ', 'B', '          ', '', 'Clientes Varios', 
                0, 9, 0, 0, @FechaHoy, 20, 
                @Cantidad, 0, 'SERVICIO CONSULTORIO', '3 ', 0, 0, 
                0, 'UND            ', 0, 0, 0, 'A', 0, 
                '1', NULL, 'SERVICIO CONSULTORIO', 0, 0, 0, 
                0, 'OPER03    ', 'S', ' ', 0.0488, ' ', 
                CONVERT(VARCHAR(12), @FechaHoy, 108), 0, @Cantidad, 1, 'FA', 'N', 
                '          ', 'A', @NumOper, '', 0, @FechaHoy, 
                @FechaHoy, @SubtotalServicio, NULL, NULL, ''
            )
            
            SET @NumSec = @NumSec + 1
            FETCH NEXT FROM servicios_cursor INTO @CodArt, @Cantidad, @Precio, @SubtotalServicio
        END
        
        CLOSE servicios_cursor
        DEALLOCATE servicios_cursor
        
        -- Insertar en CITAS_PAGOS
        INSERT INTO CITAS_PAGOS (
            CIT_CODCIT, CTP_FECHA, CTP_MONTO, CTP_FORMA_PAGO, CTP_REFERENCIA, 
            CTP_LOCAL_PAGO, CTP_ESTADO, CTP_GENERO_COMPROBANTE, CTP_TIPO_COMPROBANTE, 
            CTP_SERIE, CTP_NUMERO, CTP_CODCIA
        ) VALUES (
            @CitaId, @FechaHoy, @Total, @FormaPago, @Referencia, 
            @LocalPago, 1, 1, @TipoComprobante, 
            @NumSer, @NumFac, @CodCia
        )
        
        COMMIT TRANSACTION
        SET @Resultado = 'SUCCESS'
        
    END TRY
    BEGIN CATCH
        ROLLBACK TRANSACTION
        SET @Resultado = 'ERROR: ' + ERROR_MESSAGE()
    END CATCH
END