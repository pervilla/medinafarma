# ANÁLISIS COMPLETO - LK_CODTRA = 1111 (ANULACIÓN DE DOCUMENTOS)

## DESCRIPCIÓN GENERAL

**LK_CODTRA = 1111** es la transacción de **ANULACIÓN/EXTORNO** de documentos en el sistema VB6. Permite anular:
- Compras (FAR_TIPMOV = 20)
- Ventas (FAR_TIPMOV = 10)
- Guías de ingreso (FAR_TIPMOV = 6)
- Guías de salida (FAR_TIPMOV = 5)
- Otros movimientos de almacén

La particularidad de esta transacción es que **revierte todas las operaciones** del documento original, creando registros espejo pero con signos invertidos.

---

## FLUJO GENERAL DE ANULACIÓN

```
┌─────────────────────────────────────────────────┐
│  1. USUARIO SELECCIONA DOCUMENTO A ANULAR       │
│  ----------------------------------------       │
│  • Busca en Grid_all (historial ALLOG)         │
│  • Selecciona documento por número de operación │
│  • Sistema carga datos del documento original   │
└────────────┬────────────────────────────────────┘
             │
             v
┌─────────────────────────────────────────────────┐
│  2. SISTEMA LEE DOCUMENTO ORIGINAL              │
│  ----------------------------------------       │
│  • Lee ALLOG por ALL_NUMOPER                    │
│  • Obtiene ww_codtra_ext (tipo doc original)   │
│  • Lee FACART (detalle de productos)            │
│  • Lee CARTERA (datos financieros)              │
│  • Lee CARACU (historial)                       │
└────────────┬────────────────────────────────────┘
             │
             v
┌─────────────────────────────────────────────────┐
│  3. VALIDACIONES                                │
│  ----------------------------------------       │
│  • Documento no debe estar ya anulado           │
│  • Usuario debe tener permisos                  │
│  • Fecha de anulación puede ser diferente       │
└────────────┬────────────────────────────────────┘
             │
             v
┌─────────────────────────────────────────────────┐
│  4. ANULACIÓN - REVIERTE OPERACIONES            │
│  ----------------------------------------       │
│  A. ALMACÉN (ARM_STOCK)                         │
│     - Invierte signo: si doc sumó, ahora resta  │
│     - arm_stock += cantidad * (-pub_signo_arm)  │
│                                                 │
│  B. FACART (Detalle)                            │
│     - Inserta nuevos registros con:             │
│       • FAR_ESTADO = 'E' (Extornado)            │
│       • FAR_ESTADO2 = 'E'                       │
│       • Mismos datos pero marca anulado         │
│                                                 │
│  C. CARTERA                                     │
│     - Marca CAR_SITUACION = 'E'                 │
│     - Revierte saldo si es necesario            │
│                                                 │
│  D. CARACU (Historial)                          │
│     - Inserta registro con:                     │
│       • CAA_ESTADO = 'E'                        │
│       • Importes invertidos                     │
│                                                 │
│  E. ALLOG (Auditoría)                           │
│     - Inserta registro con:                     │
│       • ALL_CODTRA = 1111                       │
│       • ALL_FLAG_EXT = 'E'                      │
│       • Referencia al documento original        │
└─────────────────────────────────────────────────┘
```

---

## TABLAS INVOLUCRADAS Y OPERACIONES

### 1. TABLA: ALLOG (Log de Auditoría)

#### A. LECTURA - Buscar documento a anular
```sql
SELECT 
    ALL_NUMOPER,        -- Número de operación a anular
    ALL_CODTRA,         -- Código de transacción original (guardado en ww_codtra_ext)
    ALL_CODCLIE,        -- Cliente/Proveedor
    ALL_CODART,         -- Artículo (si aplica)
    ALL_IMPORTE_AMORT,  -- Importe amortizado
    ALL_IMPORTE,        -- Importe total
    ALL_TIPMOV,         -- Tipo de movimiento
    ALL_NUMFAC,         -- Número de factura
    ALL_NUMSER,         -- Serie
    ALL_CP,             -- C=Cliente, P=Proveedor
    ALL_SIGNO_CAR,      -- Signo original de cartera
    all_sIGNO_ARM,      -- Signo original de almacén
    ALL_SIGNO_CCM,      -- Signo de cuenta corriente
    all_signo_caja,     -- Signo de caja
    ALL_FECHA_DIA,      -- Fecha original
    -- ... otros campos
FROM allog
WHERE ALL_NUMOPER = ?
  AND ALL_CODCIA = ?
  AND all_flag_ext <> 'E'  -- No anulados
```

**Variables clave que se cargan:**
- `PUB_NUM_OPER_EXT` = Número de operación original
- `ww_codtra_ext` = Código de transacción original (1401, 2401, etc.)
- `ww_fecha_dia_ext` = Fecha del documento original

#### B. INSERCIÓN - Registro de la anulación
```sql
INSERT INTO allog (
    ALL_CODCIA,
    ALL_FECHA_DIA,          -- Fecha de la anulación (puede ser diferente)
    ALL_NUMOPER,            -- Nuevo número de operación
    ALL_CODTRA,             -- ** 1111 ** (anulación)
    ALL_FLAG_EXT,           -- 'E' (Extornado)
    ALL_CODCLIE,            -- Mismo cliente/proveedor
    ALL_CODART,             -- Mismo artículo
    
    -- ** IMPORTES INVERTIDOS **
    ALL_IMPORTE_AMORT,      -- Signo invertido
    ALL_IMPORTE,            -- Signo invertido
    
    -- ** SIGNOS INVERTIDOS **
    ALL_SIGNO_CAR,          -- Invertido: si era 1, ahora -1
    all_sIGNO_ARM,          -- Invertido: si era 1, ahora -1
    ALL_SIGNO_CCM,          -- Invertido
    all_signo_caja,         -- Invertido
    
    -- Referencia al documento original
    ALL_NUMOPER2,           -- Referencia a PUB_NUM_OPER_EXT
    
    ALL_TIPMOV,             -- Mismo tipo de movimiento
    ALL_NUMFAC,             -- Misma factura
    ALL_NUMSER,             -- Misma serie
    ALL_FBG,                -- Mismo tipo documento
    ALL_CP,                 -- Mismo C/P
    ALL_CODUSU,             -- Usuario que anula
    ALL_HORA,               -- Hora de anulación
    -- ... otros campos
) VALUES (...)
```

---

### 2. TABLA: FACART (Detalle de Artículos)

#### A. LECTURA - Obtener detalle del documento
```sql
SELECT 
    FAR_CODART,         -- Código del artículo
    FAR_cantidad,       -- Cantidad
    FAR_equiv,          -- Equivalencia
    FAR_PRECIO,         -- Precio
    FAR_COSPRO,         -- Costo promedio
    far_signo_arm,      -- Signo de almacén original
    FAR_tipmov,         -- Tipo de movimiento
    FAR_NUMOPER,        -- Número de operación
    -- ... otros campos
FROM FACART
WHERE FAR_CODCIA = ?
  AND FAR_NUMSER = ?
  AND FAR_NUMFAC = ?
  AND FAR_tipmov = ?
  AND far_estado <> 'E'  -- No extornados
ORDER BY far_numsec
```

#### B. INSERCIÓN - Nuevos registros marcados como anulados
```sql
INSERT INTO FACART (
    FAR_CODCIA,
    FAR_tipmov,         -- Mismo tipo de movimiento
    FAR_NUMSER,         -- Misma serie
    FAR_NUMFAC,         -- Mismo número
    FAR_NUMSEC,         -- Nueva secuencia
    FAR_CODART,         -- Mismo artículo
    FAR_cantidad,       -- Misma cantidad
    FAR_equiv,          -- Misma equivalencia
    FAR_PRECIO,         -- Mismo precio
    FAR_COSPRO,         -- Mismo costo
    
    -- ** ESTADOS DE ANULACIÓN **
    far_estado,         -- 'E' (Extornado)
    FAR_ESTADO2,        -- 'E' (Extornado)
    
    -- Signos (pueden ser invertidos según lógica)
    far_signo_arm,      -- Invertido para revertir movimiento
    far_signo_car,      -- Invertido
    
    FAR_NUMOPER,        -- Nuevo número de operación
    FAR_fecha_compra,   -- Fecha de anulación
    FAR_CODCLIE,        -- Mismo cliente
    -- ... otros campos
) VALUES (...)
```

**NOTA IMPORTANTE:** Los registros en FACART NO se eliminan, se insertan nuevos con estado 'E'.

---

### 3. TABLA: ARTICULO (Almacén - Stock)

#### ACTUALIZACIÓN - Reversión del movimiento de stock
```sql
UPDATE ARTICULO SET
    -- ** REVERSIÓN DEL STOCK **
    arm_stock = arm_stock + (cantidad * -pub_signo_arm_original),
    
    -- Si el documento original sumó al stock (ingreso):
    --   pub_signo_arm_original = 1
    --   Ahora se resta: arm_stock -= cantidad
    
    -- Si el documento original restó del stock (salida):
    --   pub_signo_arm_original = -1
    --   Ahora se suma: arm_stock += cantidad
    
    -- Actualizar saldos según tipo
    ARM_saldo_s = ARM_saldo_s + (cantidad * -pub_signo_arm),  -- Si PUB_SO = 'A'
    ARM_Saldo_n = ARM_Saldo_n + (cantidad * -pub_signo_arm),  -- Si PUB_SO <> 'A'
    
    -- Actualizar contadores de movimientos
    ARM_SALIDAS = ARM_SALIDAS + cantidad,  -- Si ahora es salida
    ARM_INGRESOS = ARM_INGRESOS + cantidad  -- Si ahora es ingreso
    
WHERE ARM_CODCIA = ?
  AND ARM_CODART = ?
```

**Ejemplo práctico:**

**Documento original (Compra - 1401):**
- Ingreso de 100 unidades
- `pub_signo_arm = 1` (suma al stock)
- `arm_stock` pasó de 500 → 600

**Anulación (1111):**
- `pub_signo_arm = -1` (invierte el signo)
- `arm_stock = 600 + (100 * -1) = 500` ✓ Restaurado

---

### 4. TABLA: CARTERA (Cuentas por Cobrar/Pagar)

#### ACTUALIZACIÓN - Marca como anulado
```sql
UPDATE cartera SET
    -- ** MARCA COMO ANULADO **
    CAR_SITUACION = 'E',    -- Extornado
    
    -- Puede revertir el saldo (depende del caso)
    car_importe = car_importe + (importe_amort * -pub_signo_car_original),
    
    -- Mantiene enlaces del documento
    -- NO incrementa CAR_NUM_REN para anulaciones
    
WHERE car_CODCLIE = ?
  AND car_NUMFAC = ?
  AND CAR_CP = ?
  AND CAR_CODCIA = ?
```

**CASO ESPECIAL - ww_codtra_ext = 2760:**
```vb
If LK_CODTRA = 1111 And ww_codtra_ext = 2760 Then
    car_llave!car_importe = 0
    car_llave!CAR_FLAG_SO = "E"
    car_llave.Update
    ' NO registra en CARACU
End If
```

---

### 5. TABLA: CARACU (Historial de Cartera)

#### INSERCIÓN - Registro histórico de la anulación
```sql
INSERT INTO caracu (
    CAA_CP,             -- 'C' o 'P'
    CAA_CODCLIE,        -- Cliente/Proveedor
    CAA_CODCIA,         -- Compañía
    CAA_TIPDOC,         -- Tipo de documento
    CAA_FECHA,          -- Fecha de anulación
    CAA_NUM_OPER,       -- Número de operación de anulación
    CAA_SERDOC,         -- Serie del documento original
    CAA_NUMDOC,         -- Número del documento original
    
    -- ** IMPORTES INVERTIDOS **
    CAA_IMPORTE,        -- Importe con signo invertido
    CAA_TOTAL,          -- Total con signo invertido
    CAA_SALDO,          -- Saldo acumulado del cliente
    CAA_SALDO_CAR,      -- Saldo del documento
    
    CAA_FECHA_VCTO,     -- Fecha de vencimiento original
    caa_concepto,       -- Concepto del documento
    
    -- ** SIGNOS INVERTIDOS **
    caa_signo_car,      -- Signo invertido
    CAA_SIGNO_CCM,      -- Signo invertido
    CAA_SIGNO_CAJA,     -- Signo invertido
    
    -- ** MARCA COMO ANULADO **
    CAA_ESTADO,         -- 'E' (Extornado)
    
    CAA_TIPMOV,         -- Tipo de movimiento
    CAA_NUMSER,         -- Serie
    CAA_NUMFAC,         -- Número de factura
    CAA_NUMSER_C,       -- Serie documento relacionado
    CAA_NUMFAC_C,       -- Número documento relacionado
    CAA_FBG,            -- Tipo documento
    CAA_CODVEN,         -- Vendedor
    CAA_hora,           -- Hora de anulación
    CAA_CODUSU,         -- Usuario que anula
    -- ... otros campos
) VALUES (...)
```

**Código relevante (línea 20476):**
```vb
If LK_CODTRA = 1111 Then caa_histo!CAA_ESTADO = "E"
```

---

### 6. TABLA: LOTES (LOT_LLAVE) - Si aplica

#### ACTUALIZACIÓN - Reversión de movimiento de lotes
```sql
UPDATE lotes SET
    LOT_SALDOS = LOT_SALDOS + (cantidad_lote * -pub_signo_arm_original)
WHERE LOT_CODCIA = ?
  AND LOT_NROLOTE = ?
  AND lot_codart = ?
```

**NOTA:** En la anulación, el sistema **NO actualiza lotes** (línea 5942):
```vb
If LK_CODTRA = 1111 Then
    ParaLot_count = 0
    GoTo SINLOTE
End If
```

---

## LÓGICA ESPECÍFICA DE ANULACIÓN

### 1. Inicialización
```vb
If LK_CODTRA = 1122 Or LK_CODTRA = 1111 Then
   PUB_FBG = "X"           -- Marca especial para anulaciones
   i_numser.Text = ""      -- No usa serie específica
End If
```

### 2. Carga del Documento Original

**Desde Grid_all (usuario selecciona):**
```vb
' Línea 8172
i_num_oper.Text = Grid_all.TextMatrix(Grid_all.Row, 1)  -- Obtiene ALL_NUMOPER2

' Línea 20256-20267
PUB_NUM_OPER = all_llave!ALL_NUMOPER
PUB_NUM_OPER_EXT = all_llave!ALL_NUMOPER     -- Guarda el número original
ww_codtra_ext = all_llave!ALL_CODTRA          -- Tipo de transacción original
```

### 3. Inversión de Signos

**Almacén (línea 5850-5854):**
```vb
If LK_CODTRA = 1111 Then
  If all_llave!ALL_tipmov = 102 Then
    pub_signo_arm = Val(grid_fac.TextMatrix(fila, 12))  -- Lee del grid
  End If
End If
```

**Cartera (línea 7241):**
```vb
If LK_CODTRA = 1111 Then 
    car_llave!CAR_SITUACION = "E"  -- Marca como extornado
End If
```

**FACART (línea 6071-6074):**
```vb
If LK_CODTRA = 1111 Or LK_CODTRA = 1133 Then
    far_llave!far_estado = "E"
    far_llave!FAR_ESTADO2 = "E"
End If
```

### 4. Casos Especiales por Tipo de Documento Original

#### Caso A: Anulación de Compra (ww_codtra_ext = 1401)
```vb
If LK_CODTRA = 1111 And ww_codtra_ext = 1401 Then
    ' Elimina estados de la compra
    DELETE FROM TABESTADOS 
    WHERE TAE_CODCIA = ? AND TAE_TIPMOV = ? 
    AND TAE_NUMSER = ? AND TAE_NUMFAC = ?
    
    ' Marca la relación de compra como liquidada
    UPDATE RELCOMPRA SET REL_LIQUIDO = '9' 
    WHERE REL_CODCIA = ? AND REL_CP = 'P' 
    AND REL_NUMSER = ? AND REL_NUMFAC = ?
End If
```

#### Caso B: Anulación de Venta (ww_codtra_ext = 2401)
```vb
If LK_CODTRA = 1111 And ww_codtra_ext = 2401 Then
    If LOC_PEDIDO <> -1 Then
        ANULAR_PED  -- Anula el pedido relacionado
    Else
        UPDATE PEDIDOS SET PED_SITUACION = ' ', PED_FORMA = ' ' 
        WHERE PED_CODCIA = ? AND PED_NUMSER = ? 
        AND PED_NUMFAC = ? AND PED_TIPMOV = 201
    End If
End If
```

#### Caso C: Anulación de Otros (ww_codtra_ext = 2760)
```vb
If LK_CODTRA = 1111 And ww_codtra_ext = 2760 Then
    ' Caso especial: marca anulado pero NO registra en CARACU
    car_llave!car_importe = 0
    car_llave!CAR_FLAG_SO = "E"
    car_llave.Update
    GoTo CASO_2760  -- Salta el registro en CARACU
End If
```

#### Caso D: No anular si es transferencia entre almacenes (ww_codtra_ext = 2107, 1402)
```vb
If LK_CODTRA = 1111 And ww_codtra_ext = 2107 Then Return
If LK_CODTRA = 1111 And ww_codtra_ext = 1402 Then Return
```

### 5. Fecha de Anulación

**Permite usar fecha diferente a la original:**
```vb
If LK_CODTRA = 1111 Then
    far_llave!FAR_fecha_compra = i_fecha_compra.Text  -- Fecha de anulación
End If
```

---

## VALIDACIONES ESPECÍFICAS

### 1. No incrementar CAR_NUM_REN en anulaciones
```vb
' Línea 7262
If PUB_IMPORTE_AMORT <> 0 And LK_CODTRA <> 1111 And LK_CODTRA <> 1122 ...
   car_llave!CAR_NUM_REN = car_llave!CAR_NUM_REN + 1
End If
```

### 2. No actualizar fecha de vencimiento en anulaciones
```vb
' Línea 7254
If (i_fecha_vcto.Visible = True And LK_CODTRA <> 1111) Or gridl.Visible ...
   car_llave!car_fecha_vcto = PUB_FECHA_VCTO
End If
```

### 3. Verificar flag de log para confirmar anulación
```vb
' Línea 5232
If Trim(LOC_FLAG_ALLOG) = "" Or (LK_CODTRA = 1111 And Trim(loc_flag_1111) = "") Then
    CN.Execute "Rollback Transaction"
    MsgBox "Operación incompleta, NO SE REGISTRO!"
    End
End If
```

### 4. No permitir anulación de documentos ya anulados
```sql
SELECT * FROM allog
WHERE ALL_NUMOPER = ?
  AND all_flag_ext <> 'E'  -- Filtro importante
```

---

## FLUJO DE DATOS PASO A PASO

### EJEMPLO: Anulación de una Compra

**Documento original (Compra - 1401):**
```
ALL_NUMOPER = 239
ALL_CODTRA = 1401
ALL_CODCLIE = 8451
ALL_NUMFAC = 19324
ALL_IMPORTE_AMORT = 1697.50
ALL_SIGNO_CAR = 1        (suma a deuda)
all_sIGNO_ARM = 1        (suma a stock)
FAR_cantidad = 10 unidades
ARM_stock antes: 100
ARM_stock después: 110   (100 + 10*1)
CAR_importe: 1697.50     (deuda con proveedor)
```

**Proceso de anulación:**

**PASO 1: Usuario selecciona en Grid_all**
```vb
i_num_oper.Text = 239  -- Número de operación a anular
```

**PASO 2: Sistema lee ALLOG**
```sql
SELECT * FROM allog WHERE ALL_NUMOPER = 239
```
Variables:
- `PUB_NUM_OPER_EXT = 239`
- `ww_codtra_ext = 1401`
- Carga todos los datos originales

**PASO 3: Sistema lee FACART**
```sql
SELECT * FROM FACART 
WHERE FAR_NUMFAC = 19324 
  AND far_estado <> 'E'
```

**PASO 4: Actualiza ALMACÉN (ARTICULO)**
```sql
UPDATE ARTICULO SET
    arm_stock = 110 + (10 * -1) = 100  ✓ Restaurado
WHERE ARM_CODART = ?
```

**PASO 5: Inserta en FACART (marcado como anulado)**
```sql
INSERT INTO FACART (
    FAR_NUMFAC = 19324,
    FAR_cantidad = 10,
    far_estado = 'E',      -- Extornado
    FAR_ESTADO2 = 'E',
    far_signo_arm = -1,    -- Invertido
    FAR_NUMOPER = 450      -- Nuevo número de operación
)
```

**PASO 6: Actualiza CARTERA**
```sql
UPDATE cartera SET
    CAR_SITUACION = 'E',
    car_importe = 1697.50 + (1697.50 * -1) = 0  ✓ Cancelado
WHERE car_NUMFAC = 19324
```

**PASO 7: Inserta en CARACU**
```sql
INSERT INTO caracu (
    CAA_NUMFAC = 19324,
    CAA_IMPORTE = -1697.50,  -- Negativo (anulación)
    CAA_ESTADO = 'E',         -- Extornado
    CAA_NUM_OPER = 450
)
```

**PASO 8: Inserta en ALLOG**
```sql
INSERT INTO allog (
    ALL_NUMOPER = 450,        -- Nuevo número
    ALL_CODTRA = 1111,        -- Anulación
    ALL_FLAG_EXT = 'E',       -- Extornado
    ALL_NUMOPER2 = 239,       -- Referencia al original
    ALL_IMPORTE_AMORT = -1697.50,
    ALL_SIGNO_CAR = -1,       -- Invertido
    all_sIGNO_ARM = -1        -- Invertido
)
```

**PASO 9: Operaciones especiales (si aplica)**
```sql
-- Para compras (ww_codtra_ext = 1401)
DELETE FROM TABESTADOS WHERE ...
UPDATE RELCOMPRA SET REL_LIQUIDO = '9' WHERE ...
```

---

## RESULTADOS DESPUÉS DE LA ANULACIÓN

**Tabla ARTICULO (Almacén):**
```
ARM_stock: 100  (restaurado al valor anterior)
```

**Tabla CARTERA:**
```
CAR_SITUACION: 'E' (marcado como extornado)
car_importe: 0 (deuda cancelada)
```

**Tabla FACART (2 registros para el mismo documento):**
```
Registro 1 (Original):
  far_estado: 'N'
  FAR_cantidad: 10
  far_signo_arm: 1
  FAR_NUMOPER: 239

Registro 2 (Anulación):
  far_estado: 'E'      ← EXTORNADO
  FAR_ESTADO2: 'E'
  FAR_cantidad: 10
  far_signo_arm: -1    ← INVERTIDO
  FAR_NUMOPER: 450
```

**Tabla CARACU (2 registros):**
```
Registro 1 (Original):
  CAA_IMPORTE: 1697.50
  CAA_ESTADO: 'N'
  CAA_NUM_OPER: 239

Registro 2 (Anulación):
  CAA_IMPORTE: -1697.50  ← NEGATIVO
  CAA_ESTADO: 'E'         ← EXTORNADO
  CAA_NUM_OPER: 450
```

**Tabla ALLOG (2 registros):**
```
Registro 1 (Original):
  ALL_CODTRA: 1401
  ALL_NUMOPER: 239
  all_flag_ext: 'N'

Registro 2 (Anulación):
  ALL_CODTRA: 1111       ← ANULACIÓN
  ALL_NUMOPER: 450
  ALL_NUMOPER2: 239      ← REFERENCIA
  all_flag_ext: 'E'      ← EXTORNADO
```

---

## CONSIDERACIONES IMPORTANTES

### 1. No se eliminan registros
- Los registros originales **NO se eliminan**
- Se crean **nuevos registros** marcados como extornados ('E')
- Permite trazabilidad completa de anulaciones

### 2. Los lotes NO se revierten
```vb
If LK_CODTRA = 1111 Then
    ParaLot_count = 0
    GoTo SINLOTE
End If
```
**Razón:** Evitar complejidad en el manejo de lotes

### 3. Fecha de anulación puede ser diferente
- El documento original tiene su fecha
- La anulación puede registrarse en otra fecha
- Esto afecta reportes por período

### 4. Casos que NO se pueden anular
```vb
If LK_CODTRA = 1111 And ww_codtra_ext = 2107 Then Return
If LK_CODTRA = 1111 And ww_codtra_ext = 1402 Then Return
```
- Transferencias entre almacenes (2107)
- Otros movimientos especiales (1402)

### 5. Flag de validación crítico
```vb
loc_flag_1111 = "A"  -- Debe marcarse durante el proceso
```
Si este flag no se marca, la transacción se revierte automáticamente.

---

## QUERIES DE CONSULTA ÚTILES

### 1. Ver documentos anulados
```sql
SELECT 
    a1.ALL_NUMOPER AS num_anulacion,
    a1.ALL_FECHA_DIA AS fecha_anulacion,
    a1.ALL_CODUSU AS usuario_anula,
    a2.ALL_NUMOPER AS num_original,
    a2.ALL_CODTRA AS tipo_doc_original,
    a2.ALL_FECHA_DIA AS fecha_original,
    a2.ALL_NUMFAC AS num_factura,
    a2.ALL_IMPORTE_AMORT AS importe_original
FROM allog a1
INNER JOIN allog a2 ON a1.ALL_NUMOPER2 = a2.ALL_NUMOPER
WHERE a1.ALL_CODTRA = 1111
  AND a1.ALL_CODCIA = '25'
ORDER BY a1.ALL_FECHA_DIA DESC
```

### 2. Ver historial completo de un documento
```sql
SELECT 
    f.FAR_NUMOPER,
    f.far_estado,
    f.FAR_ESTADO2,
    f.FAR_cantidad,
    f.far_signo_arm,
    a.ALL_CODTRA,
    a.ALL_FECHA_DIA,
    CASE 
        WHEN f.far_estado = 'E' THEN 'ANULADO'
        ELSE 'ACTIVO'
    END AS estado_descriptivo
FROM FACART f
LEFT JOIN allog a ON f.FAR_NUMOPER = a.ALL_NUMOPER
WHERE f.FAR_NUMFAC = 19324
  AND f.FAR_CODCIA = '25'
ORDER BY f.FAR_NUMOPER
```

### 3. Auditar reversiones de stock
```sql
SELECT 
    a.ALL_FECHA_DIA,
    a.ALL_NUMFAC,
    a.ALL_CODART,
    f.FAR_cantidad,
    f.far_signo_arm,
    CASE 
        WHEN a.ALL_CODTRA = 1111 THEN 'ANULACIÓN'
        ELSE 'ORIGINAL'
    END AS tipo_operacion,
    a.ALL_CODUSU
FROM allog a
INNER JOIN FACART f ON a.ALL_NUMOPER = f.FAR_NUMOPER
WHERE a.ALL_NUMFAC = 19324
  AND a.ALL_CODCIA = '25'
ORDER BY a.ALL_NUMOPER
```

---

## RESUMEN DE CAMPOS CLAVE

| Campo | Tabla | Valor en Original | Valor en Anulación |
|-------|-------|-------------------|-------------------|
| `ALL_CODTRA` | ALLOG | 1401, 2401, etc. | **1111** |
| `all_flag_ext` | ALLOG | 'N' | **'E'** |
| `far_estado` | FACART | 'N' | **'E'** |
| `FAR_ESTADO2` | FACART | 'N', 'L', etc. | **'E'** |
| `CAR_SITUACION` | CARTERA | '', 'A', etc. | **'E'** |
| `CAA_ESTADO` | CARACU | 'N' | **'E'** |
| `ALL_SIGNO_CAR` | ALLOG | 1 o -1 | **Invertido** |
| `all_sIGNO_ARM` | ALLOG | 1 o -1 | **Invertido** |
| `ALL_NUMOPER2` | ALLOG | 0 o ref. | **Ref. al original** |

---

## CONCLUSIÓN

La transacción **LK_CODTRA = 1111** es un proceso complejo que:

1. ✅ **Lee el documento original** completo desde ALLOG, FACART, CARTERA
2. ✅ **Revierte los movimientos de almacén** invirtiendo los signos
3. ✅ **Crea nuevos registros marcados como 'E'** en todas las tablas
4. ✅ **Mantiene trazabilidad** mediante `ALL_NUMOPER2`
5. ✅ **NO elimina datos** - todo queda registrado
6. ✅ **Maneja casos especiales** según el tipo de documento original
7. ✅ **Permite fecha de anulación diferente** a la fecha original

Es crítico para el sistema de auditoría y control de inventarios, permitiendo corregir errores manteniendo un registro completo de todas las operaciones.

---

**Elaborado por:** Sistema de Análisis  
**Fecha:** 28 de enero de 2026  
**Versión:** 1.0
