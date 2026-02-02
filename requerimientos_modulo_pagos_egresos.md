# DOCUMENTO DE REQUERIMIENTOS - MÓDULO DE PAGOS A PROVEEDORES Y FLUJO DE CAJA

## CONTEXTO Y OBJETIVO

Este documento consolida los requerimientos para desarrollar un módulo en CodeIgniter 4 que:
1. **Replique la funcionalidad de pagos a proveedores** (LK_CODTRA = 5360) del sistema VB6 actual
2. **Extienda la funcionalidad** para registrar intereses moratorios en el módulo de Egresos/Flujo de Caja
3. **Integre ambos sistemas** manteniendo consistencia en la base de datos compartida

---

## PARTE 1: ANÁLISIS DEL SISTEMA ACTUAL (VB6 - Transacción 5360)

### 1.1. Descripción General

**LK_CODTRA = 5360** es una transacción de **aplicación de pagos a documentos de proveedores** (compras pendientes). Es similar a las transacciones 2725 y 2735, compartiendo validaciones y comportamiento.

### 1.2. Flujo de Datos Actual

```
┌─────────────┐
│   Usuario   │
│  ingresa    │
│  datos de   │
│   pago      │
└──────┬──────┘
       │
       v
┌─────────────────────────────────────┐
│  1. CONSULTA - Lee de BD            │
│  --------------------------------   │
│  • CARTERA (car_llave)              │
│    - Saldo actual del documento     │
│    - Datos del documento            │
│  • CLIENTES (cli_llave)             │
│    - Información del proveedor      │
└──────┬──────────────────────────────┘
       │
       v
┌─────────────────────────────────────┐
│  2. VALIDACIONES                    │
│  --------------------------------   │
│  • Importe > 0                      │
│  • Importe <= Saldo disponible      │
│  • Saldo no puede quedar negativo   │
└──────┬──────────────────────────────┘
       │
       v
┌─────────────────────────────────────┐
│  3. ACTUALIZACIÓN - Escribe en BD   │
│  --------------------------------   │
│  A. CARTERA                         │
│     - Actualiza car_importe (saldo) │
│     - Incrementa car_num_ren        │
│     - Actualiza car_fecha_vcto      │
│                                     │
│  B. CARACU (Historial)              │
│     - Inserta registro del pago     │
│                                     │
│  C. ALLOG (Auditoría)               │
│     - Inserta registro completo     │
└─────────────────────────────────────┘
```

### 1.3. Tablas Involucradas y Operaciones

#### TABLA: CARTERA (car_llave)
**Operación:** UPDATE

**Campos que se CONSULTAN:**
```sql
SELECT 
    car_importe,           -- Saldo actual
    car_fecha_vcto,        -- Fecha de vencimiento
    car_concepto,          -- Concepto del documento
    car_fbg,               -- Tipo documento (F/B/G)
    car_NUMSER,            -- Serie
    car_NUMFAC,            -- Número de factura
    CAR_MONEDA,            -- Moneda (S/D)
    car_fecha_sunat,       -- Fecha de emisión
    CAR_NUM_REN            -- Número de renovaciones
FROM cartera
WHERE car_CODCLIE = ? 
  AND car_NUMFAC = ?
  AND car_CP = 'P'         -- P = Proveedor
```

**Campos que se ACTUALIZAN:**
```sql
UPDATE cartera SET
    car_importe = car_importe + (? * -1),  -- Resta el monto pagado
    car_fecha_vcto = ?,                     -- Actualiza fecha vencimiento (si aplica)
    CAR_NUM_REN = CAR_NUM_REN + 1          -- Incrementa contador
WHERE car_CODCLIE = ? 
  AND car_NUMFAC = ?
  AND car_CP = 'P'
```

**NOTA IMPORTANTE:** El campo `car_concepto` **NO se modifica** en transacciones 5360.

---

#### TABLA: CARACU (Historial de Movimientos)
**Operación:** INSERT

**Campos insertados:**
```sql
INSERT INTO caracu (
    CAA_CP,                -- 'P' para Proveedor
    CAA_CODCLIE,           -- Código del proveedor
    CAA_CODCIA,            -- Código de compañía
    CAA_TIPDOC,            -- Tipo de documento (FA, BO, etc.)
    CAA_FECHA,             -- Fecha del pago
    CAA_NUM_OPER,          -- Número de operación (correlativo)
    CAA_SERDOC,            -- Serie del documento original
    CAA_NUMDOC,            -- Número del documento original
    CAA_IMPORTE,           -- Monto del pago (negativo)
    CAA_SALDO,             -- Saldo acumulado del cliente/proveedor
    CAA_FECHA_VCTO,        -- Fecha de vencimiento
    CAA_CONCEPTO,          -- Descripción del movimiento
    CAA_SIGNO_CAR,         -- -1 para pagos
    CAA_SIGNO_CCM,         -- 0 (no afecta cta cte)
    CAA_ESTADO,            -- Estado del registro
    CAA_SALDO_CAR,         -- Saldo del documento después del pago
    CAA_NUMSER,            -- Serie de la factura
    CAA_NUMFAC,            -- Número de factura
    CAA_SIGNO_CAJA,        -- -1 si afecta caja
    CAA_HORA,              -- Hora del registro
    CAA_CODUSU,            -- Usuario que registra
    CAA_NUMSER_C,          -- Serie del comprobante de pago
    CAA_NUMFAC_C,          -- Número del comprobante de pago
    CAA_FLAG_SO,           -- Flag especial
    CAA_TIPO_CAMBIO,       -- Tipo de cambio del día
    CAA_CODTRA,            -- 5360
    CAA_FECHA_COBRO        -- Fecha de cobro/pago
) VALUES (?, ?, ?, ...)
```

---

#### TABLA: ALLOG (Log de Auditoría)
**Operación:** INSERT

**Campos principales insertados:**
```sql
INSERT INTO allog (
    ALL_CODCIA,            -- Código de compañía
    ALL_FECHA_DIA,         -- Fecha del día
    ALL_NUMOPER,           -- Número de operación
    ALL_CODTRA,            -- 5360
    ALL_FLAG_EXT,          -- Flag de extorno ('N' normal)
    ALL_CODCLIE,           -- Código del proveedor
    ALL_IMPORTE_AMORT,     -- ** MONTO DEL PAGO **
    ALL_IMPORTE,           -- Importe total
    ALL_CODUSU,            -- Usuario
    ALL_FBG,               -- Tipo documento
    ALL_CP,                -- 'P' = Proveedor
    ALL_TIPDOC,            -- Tipo de documento
    ALL_NUMSER,            -- Serie
    ALL_NUMFAC,            -- Número de factura
    ALL_MONEDA_CLI,        -- Moneda del documento
    ALL_MONEDA_CAJA,       -- Moneda de caja
    ALL_SECUENCIA,         -- Secuencia de transacción
    ALL_SIGNO_CAR,         -- -1 (resta al saldo)
    all_signo_caja,        -- 0 o -1 según afecte caja
    ALL_SIGNO_CCM,         -- 0
    all_sIGNO_ARM,         -- 0
    ALL_FECHA_CAN,         -- Fecha de cancelación
    ALL_SITUACION,         -- Situación del documento
    ALL_HORA,              -- Hora del registro
    ALL_NUMOPER2,          -- Número de operación secundario
    all_CODCAJ,            -- Código de caja
    ALL_TIPO_CAMBIO        -- Tipo de cambio
) VALUES (?, ?, ?, ...)
```

---

### 1.4. Validaciones Críticas del Sistema VB6

#### Validación 1: Saldo No Puede Ser Negativo
```vb
If LK_CODTRA = 2725 Or LK_CODTRA = 2735 Or LK_CODTRA = 5360 Then
  If car_llave!car_importe < 0 And PUB_TIPDOC <> "ER" Then
    MsgBox "Revisar el Saldo del Documento. No puede ser saldo Negativo"
    exito = False
  End If
End If
```
**Traducción:** Después de aplicar el pago, el saldo no puede quedar negativo.

#### Validación 2: Importe No Puede Ser Cero
```vb
If Val(i_importe.Text) = 0 And Val(i_importe_amort.Text) = 0 Then
    MsgBox "No Procede en importe en 0.00"
End If
```
**Excepción:** Usuarios ADMIN o SUPER pueden grabar con importe 0.

#### Validación 3: Importe Mayor al Saldo
```vb
If Val(gridl.TextMatrix(gridl.Row, 5)) > Val(gridl.TextMatrix(gridl.Row, 25)) Then
  MsgBox "El importe ingresado es mayor que el Saldo..."
End If
```
**Traducción:** El monto a pagar no puede exceder el saldo pendiente.

---

### 1.5. Ejemplo Práctico con Datos Reales

#### ESCENARIO: Pago de 2 facturas de 3

**Facturas de compra registradas:**
- Factura 19324: S/ 1,697.50 (pagada el 28/01/2026)
- Factura 19337: S/ 575.21 (pagada el 28/01/2026)
- Factura 19338: S/ 1,276.43 (SIN PAGAR)

**Estado en CARTERA antes del pago:**
```sql
car_NUMFAC  | car_importe (saldo)
------------|--------------------
19324       | 1697.50
19337       | 575.21
19338       | 1276.43
```

**Transacciones de pago (5360) ejecutadas:**

**Pago 1 (ALL_NUMOPER = 74):**
```sql
-- ALLOG
ALL_CODTRA = 5360
ALL_NUMFAC = 19324
ALL_IMPORTE_AMORT = -1697.50  -- Negativo porque es pago
ALL_SIGNO_CAR = -1

-- CARTERA (actualización)
car_importe = 1697.50 + (1697.50 * -1) = 0  ✓

-- CARACU (registro histórico)
CAA_IMPORTE = -1697.50
CAA_SALDO_CAR = 0  -- Saldo del documento después del pago
```

**Pago 2 (ALL_NUMOPER = 75):**
```sql
-- ALLOG
ALL_CODTRA = 5360
ALL_NUMFAC = 19337
ALL_IMPORTE_AMORT = -575.21
ALL_SIGNO_CAR = -1

-- CARTERA
car_importe = 575.21 + (575.21 * -1) = 0  ✓

-- CARACU
CAA_IMPORTE = -575.21
CAA_SALDO_CAR = 0
```

**Estado en CARTERA después del pago:**
```sql
car_NUMFAC  | car_importe (saldo)
------------|--------------------
19324       | 0.00         ✓ PAGADA
19337       | 0.00         ✓ PAGADA
19338       | 1276.43      ⚠ PENDIENTE
```

---

## PARTE 2: NUEVO REQUERIMIENTO - MÓDULO DE PAGOS CON INTERESES

### 2.1. Problemática Actual

Actualmente, cuando se paga una factura con mora:
- ✅ El sistema registra el **pago del capital** (monto original de la factura)
- ❌ Los **intereses moratorios** se calculan y registran **manualmente en Excel**
- ❌ No hay trazabilidad de los intereses en el sistema
- ❌ No se reflejan en el flujo de caja

### 2.2. Solución Propuesta

**Desarrollar en CodeIgniter 4 un módulo que:**

1. **Permita registrar pagos a proveedores** (replicando funcionalidad 5360)
2. **Calcule automáticamente intereses moratorios** cuando hay mora
3. **Registre los intereses como un egreso** en el plan de cuentas
4. **Mantenga sincronización** con las tablas del sistema VB6

---

### 2.3. Flujo Propuesto para Pago con Intereses

```
┌─────────────────────────────────────────────────┐
│  PASO 1: Usuario selecciona factura a pagar    │
│  ----------------------------------------       │
│  • Sistema muestra:                             │
│    - Monto original: S/ 1,000.00                │
│    - Saldo pendiente: S/ 1,000.00               │
│    - Fecha vencimiento: 15/12/2025              │
│    - Días de mora: 44 días                      │
│    - Interés moratorio: S/ 50.00 (calculado)    │
│    - TOTAL A PAGAR: S/ 1,050.00                 │
└────────────┬────────────────────────────────────┘
             │
             v
┌─────────────────────────────────────────────────┐
│  PASO 2: Usuario confirma el pago               │
│  ----------------------------------------       │
│  • Monto a pagar al capital: S/ 1,000.00        │
│  • Monto de intereses: S/ 50.00                 │
│  • Forma de pago: Efectivo / Transferencia      │
│  • Cuenta bancaria (si aplica)                  │
└────────────┬────────────────────────────────────┘
             │
             v
┌─────────────────────────────────────────────────┐
│  PASO 3: Sistema registra en BD (VB6)          │
│  ----------------------------------------       │
│  A. ACTUALIZA CARTERA                           │
│     UPDATE cartera SET                          │
│       car_importe = 0                           │
│     WHERE car_NUMFAC = 19324                    │
│                                                 │
│  B. INSERTA EN CARACU (historial)               │
│     INSERT INTO caracu (...)                    │
│     VALUES (-1000.00, ...)                      │
│                                                 │
│  C. INSERTA EN ALLOG (auditoría)                │
│     INSERT INTO allog (...)                     │
│     ALL_CODTRA = 5360                           │
│     ALL_IMPORTE_AMORT = -1000.00                │
└────────────┬────────────────────────────────────┘
             │
             v
┌─────────────────────────────────────────────────┐
│  PASO 4: Sistema registra INTERÉS como EGRESO  │
│  ----------------------------------------       │
│  INSERT INTO EGRESOS (                          │
│    EGR_FECHA,           -- 28/01/2026           │
│    EGR_LOCAL,           -- 1 (Centro)           │
│    EGR_CUENTA_ID,       -- ID de "Intereses"    │
│    EGR_DESCRIPCION,     -- "Interés mora Fact..." │
│    EGR_MONTO,           -- 50.00                │
│    EGR_COMPROBANTE_REF, -- Referencia a factura │
│    EGR_FORMA_PAGO,      -- Efectivo              │
│    EGR_RESPONSABLE,     -- Usuario               │
│    EGR_ESTADO,          -- 'pagado'              │
│    EGR_TIPO_EGRESO,     -- 'INTERES_MORA'        │
│    EGR_FACTURA_REF      -- 19324                 │
│  )                                               │
└────────────┬────────────────────────────────────┘
             │
             v
┌─────────────────────────────────────────────────┐
│  PASO 5: Actualiza movimiento de caja          │
│  ----------------------------------------       │
│  INSERT INTO CAJA_MOVIMIENTOS (                 │
│    MOTIVO,              -- 'PAGO_PROVEEDOR'     │
│    MONTO,               -- -1050.00             │
│    DESCRIPCION,         -- Detalle del pago     │
│    ...                                          │
│  )                                              │
└─────────────────────────────────────────────────┘
```

---

## PARTE 3: ESTRUCTURA DE BASE DE DATOS PROPUESTA

### 3.1. Plan de Cuentas

#### Tabla: PLAN_CUENTAS
```sql
CREATE TABLE PLAN_CUENTAS (
    PC_ID           INT IDENTITY(1,1) PRIMARY KEY,
    PC_CODIGO       VARCHAR(20) NOT NULL,           -- Ej: "6.1.2", "6.3.4"
    PC_NOMBRE       VARCHAR(100) NOT NULL,          -- "Intereses por Mora"
    PC_TIPO         CHAR(1) NOT NULL,               -- 'I' = Ingreso, 'E' = Egreso
    PC_PADRE        INT NULL,                       -- FK a PC_ID (para jerarquía)
    PC_ACTIVO       BIT DEFAULT 1,
    PC_DESCRIPCION  VARCHAR(255),
    
    CONSTRAINT FK_PC_PADRE FOREIGN KEY (PC_PADRE) REFERENCES PLAN_CUENTAS(PC_ID)
)
```

**Cuentas sugeridas para el caso de uso:**
```
PC_CODIGO | PC_NOMBRE                    | PC_TIPO | PC_PADRE
----------|------------------------------|---------|----------
6         | GASTOS                       | E       | NULL
6.1       | Gastos Operativos            | E       | 6
6.1.1     | Movilidad                    | E       | 6.1
6.1.2     | Servicios Básicos            | E       | 6.1
6.2       | Gastos Financieros           | E       | 6
6.2.1     | Intereses por Mora           | E       | 6.2  ⭐
6.2.2     | Comisiones Bancarias         | E       | 6.2
6.3       | Remuneraciones               | E       | 6
6.3.1     | Sueldos                      | E       | 6.3
6.3.2     | AFP                          | E       | 6.3
6.3.3     | Essalud                      | E       | 6.3
```

---

### 3.2. Tabla de Egresos

#### Tabla: EGRESOS
```sql
CREATE TABLE EGRESOS (
    EGR_ID                  INT IDENTITY(1,1) PRIMARY KEY,
    EGR_FECHA               DATE NOT NULL,                  
    EGR_LOCAL               INT NOT NULL,                   -- 1, 2, 3 (Centro, JJ, PM)
    EGR_CUENTA_ID           INT NOT NULL,                   -- FK a PLAN_CUENTAS
    EGR_DESCRIPCION         VARCHAR(255) NOT NULL,
    EGR_MONTO               DECIMAL(12,2) NOT NULL,
    
    -- Comprobante
    EGR_COMPROBANTE_TIPO    VARCHAR(10),                    -- 'FA', 'BO', 'RH', etc.
    EGR_COMPROBANTE_SERIE   VARCHAR(10),
    EGR_COMPROBANTE_NUMERO  VARCHAR(20),
    
    -- Pago
    EGR_FORMA_PAGO          VARCHAR(20),                    -- 'EFECTIVO', 'TRANSFERENCIA', 'TARJETA'
    EGR_RESPONSABLE         INT,                            -- FK a VEMAEST (opcional)
    EGR_ESTADO              VARCHAR(20) DEFAULT 'pagado',   -- 'pagado', 'pendiente', 'anulado'
    
    -- Letras y vencimientos
    EGR_FECHA_VCTO          DATE NULL,                      -- Para letras
    EGR_INTERESES           DECIMAL(12,2) DEFAULT 0,        -- Intereses moratorios
    
    -- Relaciones
    EGR_CAJA_MOV_ID         INT NULL,                       -- FK a CAJA_MOVIMIENTOS
    
    -- Campos específicos para intereses de compras
    EGR_TIPO_EGRESO         VARCHAR(30),                    -- 'NORMAL', 'INTERES_MORA', 'LETRA', etc.
    EGR_FACTURA_REF         INT NULL,                       -- Referencia a car_NUMFAC si es interés
    EGR_PROVEEDOR_COD       INT NULL,                       -- car_CODCLIE
    
    -- Auditoría
    EGR_USUARIO             VARCHAR(50),
    EGR_FECHA_REGISTRO      DATETIME DEFAULT GETDATE(),
    EGR_OBSERVACIONES       VARCHAR(500),
    
    CONSTRAINT FK_EGR_CUENTA FOREIGN KEY (EGR_CUENTA_ID) REFERENCES PLAN_CUENTAS(PC_ID),
    CONSTRAINT FK_EGR_RESPONSABLE FOREIGN KEY (EGR_RESPONSABLE) REFERENCES VEMAEST(VEM_CODVEN),
    CONSTRAINT FK_EGR_CAJA_MOV FOREIGN KEY (EGR_CAJA_MOV_ID) REFERENCES CAJA_MOVIMIENTOS(CM_ID)
)
```

---

### 3.3. Relación con Sistema Actual

```
┌────────────────┐
│    CARTERA     │  ← Sistema VB6 (Facturas de compra)
│  car_NUMFAC    │
│  car_importe   │
└───────┬────────┘
        │
        │ Referencia
        │
┌───────▼────────┐
│    EGRESOS     │  ← Nuevo módulo CI4 (Registro de intereses)
│ EGR_FACTURA_REF│
│ EGR_TIPO_EGRESO│ = 'INTERES_MORA'
└────────────────┘
```

---

## PARTE 4: FUNCIONALIDADES DEL MÓDULO

### 4.1. Módulo de Pagos a Proveedores

#### Pantalla: Listar Facturas Pendientes
**Ruta:** `/pagos-proveedores` o `/cuentas-por-pagar`

**Funcionalidades:**
- Mostrar listado de facturas con saldo pendiente (car_importe > 0)
- Filtros:
  - Por proveedor
  - Por rango de fechas
  - Por local
  - Por estado (vencidas, por vencer, todas)
- Columnas:
  - Proveedor
  - Número de factura
  - Fecha emisión
  - Fecha vencimiento
  - Monto original
  - Saldo pendiente
  - Días de mora (si aplica)
  - Interés calculado
  - Acciones: [Pagar] [Ver detalle]

**Ejemplo de query:**
```sql
SELECT 
    c.car_CODCLIE,
    cli.cli_nombre AS proveedor,
    c.car_NUMFAC,
    c.car_fecha_sunat AS fecha_emision,
    c.car_fecha_vcto AS fecha_vencimiento,
    c.CAR_IMP_INI AS monto_original,
    c.car_importe AS saldo_pendiente,
    CASE 
        WHEN c.car_fecha_vcto < GETDATE() 
        THEN DATEDIFF(day, c.car_fecha_vcto, GETDATE())
        ELSE 0 
    END AS dias_mora,
    CASE 
        WHEN c.car_fecha_vcto < GETDATE() 
        THEN c.car_importe * 0.05 * DATEDIFF(day, c.car_fecha_vcto, GETDATE()) / 365
        ELSE 0 
    END AS interes_calculado
FROM cartera c
INNER JOIN clientes cli ON c.car_CODCLIE = cli.cli_codclie AND c.CAR_CODCIA = cli.cli_codcia
WHERE c.CAR_CP = 'P'           -- Solo proveedores
  AND c.car_importe > 0        -- Con saldo pendiente
  AND c.CAR_CODCIA = '25'      -- Código de compañía
ORDER BY c.car_fecha_vcto ASC
```

---

#### Pantalla: Registrar Pago
**Ruta:** `/pagos-proveedores/pagar/{numfac}`

**Formulario:**
```
┌─────────────────────────────────────────────────┐
│  REGISTRAR PAGO A PROVEEDOR                     │
├─────────────────────────────────────────────────┤
│  Proveedor:     [DECO S.A.C]                    │
│  Factura Nº:    [001-19324]                     │
│  Fecha emisión: [11/10/2025]                    │
│  Fecha vencto:  [09/01/2026]                    │
│                                                 │
│  ┌───────────────────────────────────────────┐ │
│  │ DETALLE DEL PAGO                          │ │
│  ├───────────────────────────────────────────┤ │
│  │ Monto original:      S/ 1,697.50          │ │
│  │ Saldo pendiente:     S/ 1,697.50          │ │
│  │ Días de mora:        19 días              │ │
│  │ Tasa de interés:     5% anual             │ │
│  │ ───────────────────────────────────────── │ │
│  │ Interés moratorio:   S/   4.43  [Editable]│ │
│  │ ═══════════════════════════════════════   │ │
│  │ TOTAL A PAGAR:       S/ 1,701.93          │ │
│  └───────────────────────────────────────────┘ │
│                                                 │
│  Monto a pagar:  [1697.50]   ⚠ Debe pagar      │
│                              el capital completo│
│                                                 │
│  Pagar interés:  [✓] Sí  [ ] No                │
│  Monto interés:  [4.43]                         │
│                                                 │
│  Forma de pago:  [Efectivo ▼]                   │
│  Cuenta bancaria:[----------]  (si transfieren) │
│  Local:          [Centro ▼]                     │
│  Fecha de pago:  [28/01/2026]                   │
│  Observaciones:  [____________________________] │
│                                                 │
│  [Cancelar]              [Registrar Pago]       │
└─────────────────────────────────────────────────┘
```

**Validaciones:**
1. ✅ Monto capital = Saldo pendiente (o menor si es pago parcial autorizado)
2. ✅ Monto interés >= 0
3. ✅ Si hay mora, sugerir interés pero permitir editarlo
4. ✅ Verificar saldo disponible en caja
5. ✅ Usuario tiene permisos de pago

---

#### Lógica de Negocio (Pseudocódigo)

```php
// Controlador: PagosProveedores.php

public function registrarPago() {
    // 1. Validar datos de entrada
    $numfac = $this->request->getPost('numfac');
    $monto_capital = $this->request->getPost('monto_capital');
    $monto_interes = $this->request->getPost('monto_interes');
    $pagar_interes = $this->request->getPost('pagar_interes'); // true/false
    $forma_pago = $this->request->getPost('forma_pago');
    $local = $this->request->getPost('local');
    $fecha_pago = $this->request->getPost('fecha_pago');
    
    // 2. Consultar factura en CARTERA
    $factura = $this->carteraModel->obtenerFactura($numfac);
    
    // 3. Validaciones
    if ($monto_capital > $factura->car_importe) {
        return redirect()->back()->with('error', 'El monto excede el saldo pendiente');
    }
    
    if ($monto_capital <= 0) {
        return redirect()->back()->with('error', 'El monto debe ser mayor a cero');
    }
    
    // 4. Iniciar transacción
    $db = \Config\Database::connect();
    $db->transStart();
    
    try {
        // A. Actualizar CARTERA
        $nuevoSaldo = $factura->car_importe - $monto_capital;
        $this->carteraModel->actualizarSaldo($numfac, $nuevoSaldo);
        
        // B. Insertar en CARACU (historial)
        $numOperacion = $this->obtenerNuevoNumOperacion();
        $this->caracuModel->registrarMovimiento([
            'CAA_CP' => 'P',
            'CAA_CODCLIE' => $factura->car_CODCLIE,
            'CAA_CODCIA' => $factura->CAR_CODCIA,
            'CAA_TIPDOC' => $factura->CAR_TIPDOC,
            'CAA_FECHA' => $fecha_pago,
            'CAA_NUM_OPER' => $numOperacion,
            'CAA_SERDOC' => $factura->car_SERDOC,
            'CAA_NUMDOC' => $factura->car_NUMDOC,
            'CAA_IMPORTE' => -$monto_capital,  // Negativo
            'CAA_SALDO_CAR' => $nuevoSaldo,
            'CAA_NUMFAC' => $factura->car_NUMFAC,
            'CAA_SIGNO_CAR' => -1,
            'CAA_CODTRA' => 5360,
            'CAA_CODUSU' => session('username'),
            // ... otros campos
        ]);
        
        // C. Insertar en ALLOG
        $this->allogModel->registrarMovimiento([
            'ALL_CODCIA' => $factura->CAR_CODCIA,
            'ALL_FECHA_DIA' => $fecha_pago,
            'ALL_NUMOPER' => $numOperacion,
            'ALL_CODTRA' => 5360,
            'ALL_FLAG_EXT' => 'N',
            'ALL_CODCLIE' => $factura->car_CODCLIE,
            'ALL_IMPORTE_AMORT' => -$monto_capital,
            'ALL_SIGNO_CAR' => -1,
            'ALL_NUMFAC' => $factura->car_NUMFAC,
            'ALL_CP' => 'P',
            'ALL_CODUSU' => session('username'),
            // ... otros campos
        ]);
        
        // D. Si hay interés, registrar en EGRESOS
        if ($pagar_interes && $monto_interes > 0) {
            $this->egresosModel->registrarEgreso([
                'EGR_FECHA' => $fecha_pago,
                'EGR_LOCAL' => $local,
                'EGR_CUENTA_ID' => $this->obtenerCuentaIntereses(), // ID de "Intereses por Mora"
                'EGR_DESCRIPCION' => "Interés mora Fact. {$factura->car_NUMFAC} - {$factura->proveedor}",
                'EGR_MONTO' => $monto_interes,
                'EGR_COMPROBANTE_REF' => $factura->car_NUMFAC,
                'EGR_FORMA_PAGO' => $forma_pago,
                'EGR_ESTADO' => 'pagado',
                'EGR_TIPO_EGRESO' => 'INTERES_MORA',
                'EGR_FACTURA_REF' => $factura->car_NUMFAC,
                'EGR_PROVEEDOR_COD' => $factura->car_CODCLIE,
                'EGR_USUARIO' => session('username'),
            ]);
        }
        
        // E. Registrar movimiento en CAJA
        $montoTotalCaja = -($monto_capital + ($pagar_interes ? $monto_interes : 0));
        $this->cajaMovimientosModel->registrarMovimiento([
            'CM_FECHA' => $fecha_pago,
            'CM_CAJA_ID' => $local,
            'CM_MOTIVO' => 'PAGO_PROVEEDOR',
            'CM_MONTO' => $montoTotalCaja,
            'CM_DESCRIPCION' => "Pago Fact. {$factura->car_NUMFAC} + interés",
            'CM_USUARIO' => session('username'),
        ]);
        
        // Confirmar transacción
        $db->transComplete();
        
        if ($db->transStatus() === FALSE) {
            return redirect()->back()->with('error', 'Error al registrar el pago');
        }
        
        return redirect()->to('/pagos-proveedores')
                        ->with('success', 'Pago registrado correctamente');
                        
    } catch (\Exception $e) {
        $db->transRollback();
        log_message('error', 'Error en pago: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}
```

---

### 4.2. Módulo de Egresos (Plan de Cuentas)

#### Funcionalidades:

1. **CRUD de Plan de Cuentas**
   - Crear, editar, eliminar cuentas
   - Estructura jerárquica (padre-hijo)
   - Activar/desactivar cuentas

2. **Registro Manual de Egresos**
   - Formulario para gastos generales
   - Categorización según plan de cuentas
   - Adjuntar comprobantes (opcional)

3. **Consulta de Egresos**
   - Listado filtrado por:
     - Fecha
     - Cuenta
     - Local
     - Tipo de egreso
   - Exportar a Excel

4. **Reportes**
   - Flujo de caja (ingresos vs egresos)
   - Gastos por categoría
   - Histórico de intereses pagados
   - Estado de cuentas por pagar

---

## PARTE 5: REPORTES Y CONSULTAS

### 5.1. Reporte de Flujo de Caja

```sql
-- Ingresos (Ventas)
SELECT 
    'INGRESO' AS tipo,
    ALL_FECHA_DIA AS fecha,
    SUM(ALL_IMPORTE) AS monto
FROM allog
WHERE ALL_CODTRA IN (2401, 2409)  -- Ventas
  AND ALL_FECHA_DIA BETWEEN @fecha_inicio AND @fecha_fin
GROUP BY ALL_FECHA_DIA

UNION ALL

-- Egresos (Pagos a proveedores - capital)
SELECT 
    'EGRESO_CAPITAL' AS tipo,
    ALL_FECHA_DIA AS fecha,
    SUM(ABS(ALL_IMPORTE_AMORT)) AS monto
FROM allog
WHERE ALL_CODTRA = 5360
  AND ALL_FECHA_DIA BETWEEN @fecha_inicio AND @fecha_fin
GROUP BY ALL_FECHA_DIA

UNION ALL

-- Egresos (Intereses y otros gastos)
SELECT 
    'EGRESO_OTROS' AS tipo,
    EGR_FECHA AS fecha,
    SUM(EGR_MONTO) AS monto
FROM EGRESOS
WHERE EGR_FECHA BETWEEN @fecha_inicio AND @fecha_fin
  AND EGR_ESTADO <> 'anulado'
GROUP BY EGR_FECHA

ORDER BY fecha, tipo
```

---

### 5.2. Reporte de Intereses Moratorios

```sql
SELECT 
    e.EGR_FECHA AS fecha_pago,
    e.EGR_FACTURA_REF AS num_factura,
    c.car_fecha_sunat AS fecha_emision,
    c.car_fecha_vcto AS fecha_vencimiento,
    DATEDIFF(day, c.car_fecha_vcto, e.EGR_FECHA) AS dias_mora,
    c.CAR_IMP_INI AS monto_factura,
    e.EGR_MONTO AS monto_interes,
    cli.cli_nombre AS proveedor,
    e.EGR_DESCRIPCION
FROM EGRESOS e
INNER JOIN cartera c ON e.EGR_FACTURA_REF = c.car_NUMFAC
INNER JOIN clientes cli ON c.car_CODCLIE = cli.cli_codclie
WHERE e.EGR_TIPO_EGRESO = 'INTERES_MORA'
  AND e.EGR_FECHA BETWEEN @fecha_inicio AND @fecha_fin
ORDER BY e.EGR_FECHA DESC
```

---

### 5.3. Estado de Cuentas por Pagar (con proyección de intereses)

```sql
SELECT 
    c.car_NUMFAC AS num_factura,
    cli.cli_nombre AS proveedor,
    c.car_fecha_sunat AS fecha_emision,
    c.car_fecha_vcto AS fecha_vencimiento,
    c.CAR_IMP_INI AS monto_original,
    c.car_importe AS saldo_pendiente,
    CASE 
        WHEN c.car_fecha_vcto < GETDATE() 
        THEN DATEDIFF(day, c.car_fecha_vcto, GETDATE())
        ELSE 0 
    END AS dias_mora,
    CASE 
        WHEN c.car_fecha_vcto < GETDATE() 
        THEN ROUND(c.car_importe * 0.05 * DATEDIFF(day, c.car_fecha_vcto, GETDATE()) / 365, 2)
        ELSE 0 
    END AS interes_proyectado,
    c.car_importe + 
    CASE 
        WHEN c.car_fecha_vcto < GETDATE() 
        THEN ROUND(c.car_importe * 0.05 * DATEDIFF(day, c.car_fecha_vcto, GETDATE()) / 365, 2)
        ELSE 0 
    END AS total_a_pagar
FROM cartera c
INNER JOIN clientes cli ON c.car_CODCLIE = cli.cli_codclie
WHERE c.CAR_CP = 'P'
  AND c.car_importe > 0
  AND c.CAR_CODCIA = '25'
ORDER BY 
    CASE WHEN c.car_fecha_vcto < GETDATE() THEN 1 ELSE 2 END,
    c.car_fecha_vcto ASC
```

---

## PARTE 6: CONSIDERACIONES DE INTEGRACIÓN

### 6.1. Sincronización de Datos

**Escenario:** Sistema VB6 y CodeIgniter 4 comparten la misma base de datos SQL Server.

**Estrategia:**
1. ✅ **Lectura:** CI4 puede leer directamente CARTERA, CARACU, ALLOG
2. ✅ **Escritura:** CI4 escribe en las mismas tablas respetando la estructura
3. ✅ **Transacciones:** Usar transacciones DB para garantizar consistencia
4. ⚠️ **Conflictos:** Validar que VB6 no modifique registros creados por CI4

**Recomendación:** Agregar campo `ORIGEN` en registros para identificar fuente:
```sql
ALTER TABLE caracu ADD CAA_ORIGEN VARCHAR(10) DEFAULT 'VB6'
-- Valores: 'VB6', 'CI4'
```

---

### 6.2. Validación de Integridad

**Antes de actualizar CARTERA:**
```php
// Verificar que la factura existe y tiene saldo
$factura = $this->db->query("
    SELECT car_importe, car_NUMFAC
    FROM cartera
    WHERE car_NUMFAC = ? AND CAR_CP = 'P'
", [$numfac])->getRow();

if (!$factura) {
    throw new \Exception('Factura no encontrada');
}

if ($factura->car_importe < $monto_capital) {
    throw new \Exception('El monto excede el saldo disponible');
}

// Bloqueo pesimista (evitar doble pago)
$this->db->query("
    UPDATE cartera WITH (UPDLOCK)
    SET car_importe = car_importe
    WHERE car_NUMFAC = ?
", [$numfac]);
```

---

### 6.3. Número de Operación (ALL_NUMOPER)

**Problema:** Evitar colisiones entre VB6 y CI4 al generar números de operación.

**Solución:**
```php
public function obtenerNuevoNumOperacion() {
    // Obtener el máximo número de operación del día
    $fecha = date('Y-m-d');
    
    $result = $this->db->query("
        SELECT ISNULL(MAX(ALL_NUMOPER), 0) + 1 AS nuevo_num
        FROM allog
        WHERE ALL_FECHA_DIA = ?
    ", [$fecha])->getRow();
    
    return $result->nuevo_num;
}
```

**Alternativa con secuencia:**
```sql
-- Crear secuencia para CI4
CREATE SEQUENCE SEQ_NUMOPER_CI4
    START WITH 50000
    INCREMENT BY 1;

-- Usar en CI4
SELECT NEXT VALUE FOR SEQ_NUMOPER_CI4
```

---

## PARTE 7: PLAN DE IMPLEMENTACIÓN

### Fase 1: Configuración Inicial (3 días)
- [x] Crear tablas `PLAN_CUENTAS` y `EGRESOS`
- [x] Poblar plan de cuentas inicial
- [x] Crear modelos en CI4 para tablas existentes (Cartera, Caracu, Allog)
- [x] Configurar conexión a SQL Server

### Fase 2: Módulo de Pagos a Proveedores (5 días)
- [x] Pantalla de listado de facturas pendientes
- [x] Formulario de registro de pago
- [x] Lógica de actualización de CARTERA
- [x] Inserción en CARACU y ALLOG
- [x] Cálculo automático de intereses moratorios
- [x] Validaciones

### Fase 3: Módulo de Egresos (4 días)
- [x] CRUD de Plan de Cuentas
- [x] Registro automático de intereses en EGRESOS
- [x] Registro manual de otros gastos
- [x] Integración con CAJA_MOVIMIENTOS

### Fase 4: Reportes (3 días)
- [x] Reporte de flujo de caja
- [x] Reporte de intereses moratorios
- [x] Estado de cuentas por pagar
- [x] Exportación a Excel

### Fase 5: Pruebas y Ajustes (3 días)
- [x] Pruebas de integración con VB6
- [x] Validación de cálculos de intereses
- [x] Pruebas de concurrencia
- [x] Ajustes finales

**Total estimado:** 18 días hábiles

---

## PARTE 8: CONSIDERACIONES TÉCNICAS

### 8.1. Tecnologías
- **Framework:** CodeIgniter 4
- **Base de datos:** SQL Server (compartida con VB6)
- **Frontend:** Bootstrap 5 + JavaScript
- **Reportes:** PHPExcel o similar

### 8.2. Seguridad
- Autenticación con sesiones CI4
- Permisos por rol (ADMIN, CAJERO, CONTADOR)
- Validación de entrada (CSRF, XSS)
- Logs de auditoría

### 8.3. Rendimiento
- Índices en tablas:
  ```sql
  CREATE INDEX IDX_CARTERA_PENDIENTES 
  ON cartera(car_importe) 
  WHERE car_importe > 0;
  
  CREATE INDEX IDX_EGRESOS_FECHA 
  ON EGRESOS(EGR_FECHA, EGR_TIPO_EGRESO);
  ```

### 8.4. Backup
- Los datos se respaldan en la rutina actual de SQL Server
- Configurar jobs para backup incremental

---

## PARTE 9: CASOS DE USO DETALLADOS

### Caso de Uso 1: Pago Total con Interés

**Actor:** Cajero  
**Precondición:** Factura con saldo pendiente y mora

**Flujo:**
1. Cajero accede a "Cuentas por Pagar"
2. Sistema muestra facturas pendientes
3. Cajero selecciona factura 19324 (S/ 1,697.50, 19 días de mora)
4. Sistema calcula interés: S/ 4.43
5. Cajero confirma pago de:
   - Capital: S/ 1,697.50
   - Interés: S/ 4.43
6. Sistema actualiza:
   - CARTERA: `car_importe = 0`
   - CARACU: Registro histórico del pago
   - ALLOG: Auditoría (ALL_CODTRA = 5360)
   - EGRESOS: Interés de S/ 4.43
   - CAJA_MOVIMIENTOS: Salida de S/ 1,701.93
7. Sistema muestra confirmación

**Postcondición:** Factura pagada completamente

---

### Caso de Uso 2: Pago Parcial

**Actor:** Cajero  
**Precondición:** Factura con saldo pendiente

**Flujo:**
1. Cajero selecciona factura 19338 (S/ 1,276.43)
2. Cajero ingresa monto parcial: S/ 500.00
3. Sistema valida:
   - ✅ Monto > 0
   - ✅ Monto <= Saldo
4. Sistema actualiza:
   - CARTERA: `car_importe = 776.43`
   - CARACU, ALLOG: Registran pago de S/ 500.00
5. Factura queda con saldo pendiente de S/ 776.43

---

### Caso de Uso 3: Registro de Gasto Operativo

**Actor:** Administrador  
**Precondición:** Plan de cuentas configurado

**Flujo:**
1. Admin accede a "Registrar Gasto"
2. Selecciona:
   - Cuenta: "6.1.1 - Movilidad"
   - Monto: S/ 50.00
   - Local: Centro
   - Comprobante: Boleta 001-12345
3. Sistema registra en EGRESOS
4. Sistema actualiza CAJA_MOVIMIENTOS
5. Confirmación

---

## PARTE 10: ANEXOS

### Anexo A: Cálculo de Interés Moratorio

**Fórmula sugerida:**
```
Interés = Saldo × Tasa Anual × (Días Mora / 365)

Ejemplo:
Saldo = S/ 1,697.50
Tasa = 5% anual (0.05)
Días Mora = 19 días

Interés = 1697.50 × 0.05 × (19 / 365)
Interés = S/ 4.43
```

**Implementación en PHP:**
```php
public function calcularInteresMoratorio($saldo, $fechaVcto, $fechaPago = null) {
    $fechaPago = $fechaPago ?? date('Y-m-d');
    $diasMora = max(0, (strtotime($fechaPago) - strtotime($fechaVcto)) / 86400);
    
    if ($diasMora <= 0) {
        return 0;
    }
    
    $tasaAnual = 0.05; // 5%
    $interes = $saldo * $tasaAnual * ($diasMora / 365);
    
    return round($interes, 2);
}
```

---

### Anexo B: Estructura de Permisos

| Rol        | Ver Facturas | Pagar | Editar Interés | Config Plan Cuentas | Reportes |
|------------|--------------|-------|----------------|---------------------|----------|
| ADMIN      | ✅           | ✅    | ✅             | ✅                  | ✅       |
| CONTADOR   | ✅           | ✅    | ✅             | ✅                  | ✅       |
| CAJERO     | ✅           | ✅    | ❌             | ❌                  | ✅       |
| VENDEDOR   | ✅           | ❌    | ❌             | ❌                  | ❌       |

---

## CONCLUSIÓN

Este documento proporciona una visión completa de:
1. ✅ Cómo funciona el pago de facturas en el sistema VB6 actual (LK_CODTRA = 5360)
2. ✅ Qué tablas se afectan y cómo
3. ✅ La nueva funcionalidad requerida: Registro de intereses moratorios
4. ✅ Estructura de BD propuesta (PLAN_CUENTAS, EGRESOS)
5. ✅ Plan de implementación en CodeIgniter 4
6. ✅ Reportes y consultas necesarios
7. ✅ Consideraciones de integración y sincronización

**Próximos pasos:**
1. Revisión y aprobación de este documento
2. Inicio de Fase 1: Configuración de BD
3. Desarrollo iterativo siguiendo el plan
4. Pruebas en ambiente de desarrollo antes de producción

---

**Elaborado por:** [Tu nombre]  
**Fecha:** 28 de enero de 2026  
**Versión:** 1.0
