-- ==========================================================
-- SP AUXILIAR: Agregar un servicio a un comprobante existente
-- ==========================================================
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SP_CM_AgregarServicioAFactura]') AND type in (N'P', N'PC'))
    DROP PROCEDURE [dbo].[SP_CM_AgregarServicioAFactura]
GO

CREATE PROCEDURE [dbo].[SP_CM_AgregarServicioAFactura]
    @NumSer INT,
    @NumFac INT,
    @NumOper INT,
    @CodCia CHAR(2) = '25',
    @TipMov INT = 10,
    @TipoComprobante CHAR(1) = 'B',
    @ClienteId INT,
    @CodArt INT,
    @Precio DECIMAL(11,2),
    @FechaHoy DATETIME = NULL,
    @NumSec INT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    
    IF @FechaHoy IS NULL SET @FechaHoy = CONVERT(DATE, GETDATE());
    IF @NumSec IS NULL 
        SELECT @NumSec = ISNULL(MAX(FAR_NUMSEC), 0) + 1 FROM FACART 
        WHERE FAR_CODCIA = @CodCia AND FAR_NUMSER = @NumSer AND FAR_NUMFAC = @NumFac;
    
    DECLARE @HoraActual VARCHAR(12) = CONVERT(VARCHAR(12), GETDATE(), 108);
    
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
        @TipMov, @CodCia, CAST(@NumSer AS CHAR(3)), @TipoComprobante, @NumFac, @NumSec, @FechaHoy, 
        @NumOper, @ClienteId, @CodArt, ' ', 'N', 0, 
        0, -1, @Precio, 0, 0.0488, 0, 
        0, 0, 0, @Precio, 1, 0, 
        0, '  ', '0  ', 0, 0, 'C', 
        0, 0, NULL, NULL, 
        1, '            ', 'B', '          ', '', 'Paciente Clínico', 
        0, 9, 0, 0, @FechaHoy, 20, 
        1, 0, 'SERV. ADICIONAL CONSULTORIO', '3 ', 0, 0, 
        0, 'UND            ', 0, 0, 0, 'A', 0, 
        '1', NULL, 'SERV. ADICIONAL CONSULTORIO', 0, 0, 0, 
        0, 'OPER03    ', 'S', ' ', 0.0488, ' ', 
        @HoraActual, 0, 1, 1, 'FA', 'N', 
        '          ', 'A', @NumOper, '', 0, @FechaHoy, 
        @FechaHoy, @Precio, NULL, NULL, ''
    );
END
GO
