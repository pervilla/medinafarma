-- ==========================================================
-- LIMPIEZA: Duplicado de CLI_CODCLIE=3316
--
-- Caso: RAMIREZ PERDOMO, FREDY ROLAN (CLI_CP='C') y
--       DISTRIBUIDORA DROGUERIA TRUJILLO SAC (CLI_CP='P')
--       comparten el CLI_CODCLIE=3316.
--
-- Decision: RAMIREZ (CLI_CP='C') conserva 3316.
--           La DROGUERIA (CLI_CP='P') pasa a un codigo nuevo.
-- ==========================================================
SET NOCOUNT ON;

-- 1. DETECTAR TODOS LOS DUPLICADOS (por si hay mas)
SELECT CLI_CODCLIE, COUNT(*) AS Cantidad
FROM CLIENTES
WHERE CLI_CODCIA = 25
GROUP BY CLI_CODCLIE
HAVING COUNT(*) > 1
ORDER BY CLI_CODCLIE;

-- 2. NUEVO CODIGO DISPONIBLE (mayor a todos, clientes y proveedores)
DECLARE @nuevo INT = (SELECT MAX(CLI_CODCLIE) + 1 FROM CLIENTES WHERE CLI_CODCIA = 25);
SELECT @nuevo AS NuevoCodigoParaProveedor;

-- 3. REASIGNAR LA DROGUERIA a un codigo unico
UPDATE CLIENTES
SET CLI_CODCLIE = @nuevo
WHERE CLI_CODCLIE = 3316 AND CLI_CP = 'P' AND CLI_RUC_ESPOSO = '20606441259';

-- 4. ACTUALIZAR SU DIRECCION EN DIRCLI (si existe la fila con CODCLI=3316 y CP='P')
UPDATE DIRCLI SET CODCLI = @nuevo WHERE CODCLI = 3316 AND CP = 'P';

-- 5. VERIFICAR QUE YA NO EXISTE EL DUPLICADO
SELECT CLI_CODCLIE, CLI_CP, CLI_NOMBRE, CLI_RUC_ESPOSO, CLI_RUC_ESPOSA
FROM CLIENTES
WHERE CLI_CODCLIE IN (3316, @nuevo)
ORDER BY CLI_CODCLIE, CLI_CP;

-- NOTA: Si la DROGUERIA tuviera comprobantes en ALLOG/FACART con CLI_CODCLIE=3316,
--       esos registros quedarian apuntando a RAMIREZ (ambiguedad preexistente).
--       Verificar si hay referencias antes de ejecutar:
--       SELECT COUNT(*) FROM ALLOG WHERE ALL_CODCLIE = 3316 AND ALL_CODCIA = 25
--       SELECT COUNT(*) FROM FACART WHERE FAR_CODCLIE = 3316 AND FAR_CODCIA = 25
GO
