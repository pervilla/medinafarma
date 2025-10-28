-- Cambio para obtener solo fecha sin hora
-- En lugar de:
-- DECLARE @FechaHoy DATETIME = GETDATE()

-- Usar:
DECLARE @FechaHoy DATETIME = CONVERT(DATE, GETDATE())

-- O alternativamente:
DECLARE @FechaHoy DATETIME = DATEADD(dd, 0, DATEDIFF(dd, 0, GETDATE()))

-- La línea completa en el stored procedure sería:
ALTER PROCEDURE SP_GenerarComprobanteConsultorio
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
    -- CAMBIO AQUÍ: Solo fecha sin hora
    DECLARE @FechaHoy DATETIME = CONVERT(DATE, GETDATE())
    DECLARE @ServiciosCount INT = 0
    
    -- El resto del stored procedure permanece igual...
END