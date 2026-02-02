# Requerimiento: Módulo de Flujo de Caja / Plan de Cuentas

## 1. Objetivo
Implementar un módulo unificado que permita:
1. **Registrar, clasificar y reportar todos los egresos (gastos)** de la botica, integrando un plan de cuentas contable.
2. **Automatizar el cálculo y registro de intereses moratorios** cuando se pagan facturas vencidas de proveedores.
3. **Replicar la funcionalidad de pagos a proveedores** del sistema VB6 actual (LK_CODTRA=5360).
4. **Proporcionar un control detallado del flujo de caja** con reportes integrados de ingresos vs egresos.

## 2. Alcance
- Registro de egresos (gastos) con categorización contable.
- Configuración de plan de cuentas (categorías y subcategorías).
- **Cálculo automático de intereses moratorios** para facturas vencidas de proveedores.
- **Replicación de funcionalidad VB6 LK_CODTRA=5360** (pagos a proveedores).
- Reportes de flujo de caja (entradas vs salidas).
- Integración con el módulo de caja existente.
- **Integración transaccional** con tablas VB6 (CARTERA, CARACU, ALLOG).
- No incluye registro de ingresos (ya gestionado por módulo de ventas).

## 3. Necesidades de Negocio
La botica necesita registrar los siguientes tipos de gastos:
- Movilidad (transportes, pasajes)
- Remuneraciones (sueldos, bonos)
- Compra de bienes (insumos, materiales)
- Pago de letras (capital e intereses por mora)
- Servicios básicos (luz, agua, internet)
- Telefonía
- Alquileres
- Obligaciones financieras (préstamos)
- AFP (aportes)
- Essalud (seguro de salud)
- Mensajería
- Refrigerios
- Otros gastos operativos

## 4. Funcionalidades Propuestas

### 4.1. Configuración de Plan de Cuentas
- CRUD de cuentas contables (código, nombre, tipo: ingreso/egreso, categoría padre).
- Asignación de categorías a los tipos de gastos listados.
- Posibilidad de agregar nuevas cuentas.

### 4.2. Registro de Egresos
- Formulario con:
  - Fecha
  - Local (caja) (centro, Juanjuicillo, Peñameza)
  - Cuenta contable (selección desde plan de cuentas)
  - Subcuenta (opcional)
  - Descripción detallada
  - Monto
  - Comprobante (tipo, serie, número)
  - Forma de pago (efectivo, transferencia, tarjeta)
  - Responsable (empleado)
  - Estado (pagado, pendiente)
  - Fecha de vencimiento (para letras)
  - Intereses moratorios (calculados automáticamente)
- Validación de montos y duplicados.

### 4.3. Integración con Caja Actual
- Cada egreso registrado debe reflejarse como un movimiento en la tabla `CAJA_MOVIMIENTOS` (tipo "GASTOS BOTICA" o nuevos tipos).
- Afectar el saldo de caja correspondiente al local.
- Mantener consistencia con el cierre de caja.

### 4.4. Consultas y Reportes
- **Flujo de Caja Diario/Semanal/Mensual**: Comparativa ingresos (ventas) vs egresos (gastos).
- **Reporte por Cuenta**: Total gastado por categoría en un período.
- **Estado de Letras por Pagar**: Listado de letras pendientes con fechas de vencimiento e intereses.
- **Intereses Moratorios Pagados**: Detalle de intereses pagados por proveedor y factura.
- **Estado de Cuentas por Pagar**: Facturas pendientes con proyección de intereses.
- **Gastos por Local**: Desglose por cada sucursal.
- **Histórico de Gastos**: Filtros por fecha, cuenta, local, tipo de egreso.

### 4.5. Programación de Pagos Recurrentes
- Para gastos fijos (alquiler, servicios) se puede programar periodicidad (mensual, trimestral).
- Generación automática de registros de egreso en fechas definidas.

### 4.6. Aprobaciones (Opcional)
- Flujo de aprobación para gastos mayores a un monto definido.

### 4.7. Cálculo Automático de Intereses Moratorios
- **Integración con Pagos a Proveedores**: Cuando se pague una factura vencida, el sistema calculará automáticamente los intereses moratorios.
- **Fórmula de cálculo**: Interés = Saldo × Tasa Anual × (Días Mora / 365)
  - Tasa sugerida: 5% anual (0.05)
  - Días Mora = Fecha Pago - Fecha Vencimiento (solo días positivos)
- **Configuración flexible**: Permitir editar el monto de interés calculado antes de confirmar el pago.
- **Registro automático**: Los intereses se registrarán como egreso en la cuenta "6.2.1 - Intereses por Mora" del plan de cuentas.
- **Trazabilidad**: Cada registro de interés mantendrá referencia a la factura original (car_NUMFAC) y proveedor (car_CODCLIE).
- **Validaciones**:
  - No permitir pago parcial del interés (todo o nada)
  - Validar que el interés calculado no exceda límites configurados
  - Permitir anulación con reversión de intereses registrados

## 5. Integración con Sistema Actual

### 5.1. Replicación de Transacción VB6 LK_CODTRA=5360
- **Objetivo**: Replicar exactamente la funcionalidad de pagos a proveedores del sistema VB6 actual.
- **Tablas afectadas**:
  - `CARTERA`: Actualización de `car_importe` (saldo) con operación: `car_importe = car_importe + (monto * -1)`
  - `CARACU`: Inserción de registro histórico del pago con `CAA_CODTRA = 5360`, `CAA_IMPORTE` negativo
  - `ALLOG`: Inserción de auditoría con `ALL_CODTRA = 5360`, `ALL_IMPORTE_AMORT` negativo, `ALL_SIGNO_CAR = -1`
- **Validaciones replicadas**:
  - Saldo no puede quedar negativo después del pago
  - Importe debe ser > 0 (excepto usuarios ADMIN/SUPER)
  - Importe no puede exceder el saldo pendiente
  - Transacciones atómicas para garantizar consistencia

### 5.2. Sincronización de Datos entre VB6 y CodeIgniter 4
- **Estrategia**: Ambos sistemas comparten la misma base de datos SQL Server
- **Lectura/Escritura**: CI4 puede leer y escribir directamente en las tablas VB6 respetando la estructura
- **Prevención de conflictos**:
  - Campo `ORIGEN` en tablas (`VB6`, `CI4`) para trazabilidad
  - Secuencia separada para `ALL_NUMOPER` generado por CI4
  - Bloqueos pesimistas en actualizaciones de `CARTERA`
- **Consistencia**: Transacciones DB para operaciones que afectan múltiples tablas

### 5.3. Integración con Módulo de Caja Existente
- Cada egreso registrado (incluyendo intereses) se refleja en `CAJA_MOVIMIENTOS`
- Afectación automática del saldo de caja correspondiente al local
- Mantenimiento de consistencia con cierres de caja diarios
- Tipos de movimiento extendidos: `PAGO_PROVEEDOR`, `INTERES_MORA`, `GASTO_OPERATIVO`

## 6. Opciones de Implementación

### Opción A: Extender Módulo de Caja
- Agregar nuevos tipos de movimiento en `$motivos` del controlador `Caja`.
- Ampliar el formulario de movimientos para incluir campos adicionales (cuenta contable, comprobante).
- Ventaja: Rápido, usa infraestructura existente.
- Desventaja: Puede quedar limitado para necesidades contables avanzadas y **no soporta bien la integración con pagos a proveedores VB6**.

### Opción B: Módulo Independiente de Gastos (RECOMENDADO)
- Crear nuevo controlador `Gastos` y `PagosProveedores` con modelos y vistas especializadas.
- Nueva tabla `EGRESOS` relacionada con `PLAN_CUENTAS` y tablas VB6.
- **Ventajas**:
  - Mayor flexibilidad para funcionalidades contables avanzadas
  - Separación clara de responsabilidades
  - Mejor integración con transacción VB6 LK_CODTRA=5360
  - Escalabilidad para futuras funcionalidades
- **Desventaja**: Mayor tiempo de desarrollo (compensado por beneficios a largo plazo)

**Decisión**: Opción B seleccionada, ya que permite un diseño limpio, escalable y **soporta completamente la automatización de intereses moratorios e integración VB6**.

## 7. Estructura de Base de Datos Propuesta

### Tabla `PLAN_CUENTAS`
- `PC_ID` int, PK
- `PC_CODIGO` varchar(20) (ej: 6.1.2)
- `PC_NOMBRE` varchar(100)
- `PC_TIPO` char(1) (I:Ingreso, E:Egreso)
- `PC_PADRE` int (referencia a PC_ID)
- `PC_ACTIVO` bit

### Tabla `EGRESOS`
- `EGR_ID` int, PK
- `EGR_FECHA` date
- `EGR_LOCAL` int (1,2,3)
- `EGR_CUENTA_ID` int (FK a PLAN_CUENTAS)
- `EGR_DESCRIPCION` varchar(255)
- `EGR_MONTO` decimal(12,2)
- **Comprobante**
  - `EGR_COMPROBANTE_TIPO` varchar(10) (FA, BO, etc.)
  - `EGR_COMPROBANTE_SERIE` varchar(10)
  - `EGR_COMPROBANTE_NUMERO` varchar(20)
- **Pago**
  - `EGR_FORMA_PAGO` varchar(20) (EFECTIVO, TRANSFERENCIA, TARJETA)
  - `EGR_RESPONSABLE` int (FK a VEMAEST)
  - `EGR_ESTADO` varchar(20) (pagado, pendiente, anulado)
- **Letras y vencimientos**
  - `EGR_FECHA_VCTO` date (para letras)
  - `EGR_INTERESES` decimal(12,2) DEFAULT 0
- **Relaciones específicas para intereses de compras**
  - `EGR_TIPO_EGRESO` varchar(30) (NORMAL, INTERES_MORA, LETRA)
  - `EGR_FACTURA_REF` int (Referencia a car_NUMFAC si es interés)
  - `EGR_PROVEEDOR_COD` int (car_CODCLIE)
- **Integración con caja**
  - `EGR_CAJA_MOV_ID` int (FK a CAJA_MOVIMIENTOS)
- **Auditoría**
  - `EGR_USUARIO` varchar(50)
  - `EGR_FECHA_REGISTRO` datetime DEFAULT GETDATE()
  - `EGR_OBSERVACIONES` varchar(500)

### Relaciones:
- `EGRESOS.EGR_CUENTA_ID` -> `PLAN_CUENTAS.PC_ID`
- `EGRESOS.EGR_RESPONSABLE` -> `VEMAEST.VEM_CODVEN`
- `EGRESOS.EGR_CAJA_MOV_ID` -> `CAJA_MOVIMIENTOS.CM_ID`
- `EGRESOS.EGR_LOCAL` referencia a locales (1,2,3)
- `EGRESOS.EGR_FACTURA_REF` referencia a `CARTERA.car_NUMFAC` (para trazabilidad)
- `EGRESOS.EGR_PROVEEDOR_COD` referencia a `CLIENTES.cli_codclie`

## 8. Interfaz de Usuario
- Menú principal: "Flujo de Caja" o "Gastos".
- Submenús: Registrar Gasto, Plan de Cuentas, Reportes.
- Listado de gastos con filtros.
- Formulario de registro amigable.

## 9. Cronograma Estimado (Plan Unificado)

### Fase 1: Configuración BD y Plan de Cuentas (4 días)
- Crear migraciones unificadas para `PLAN_CUENTAS` y `EGRESOS`
- Seed de cuentas iniciales (GASTOS, INTERESES POR MORA, etc.)
- Modelos base: `PlanCuentaModel`, `EgresoModel`
- Configurar conexión SQL Server y relaciones con tablas VB6

### Fase 2: Módulo de Pagos a Proveedores (6 días)
- Controlador `PagosProveedores` con vistas de listado/filtros
- Formulario de pago con cálculo automático de intereses
- Integración transaccional con CARTERA, CARACU, ALLOG (replicación LK_CODTRA=5360)
- Registro automático de intereses en tabla EGRESOS
- Validaciones VB6 replicadas (saldo no negativo, importes válidos)

### Fase 3: Módulo de Egresos Manuales (4 días)
- Controlador `Egresos` con CRUD completo
- Selector jerárquico de cuentas contables
- Integración con `CAJA_MOVIMIENTOS`
- Soporte para letras, intereses y comprobantes
- Formularios de registro amigable con validaciones

### Fase 4: Reportes y Consultas (5 días)
- Flujo de caja diario/semanal/mensual (ingresos vs egresos)
- Reporte de intereses moratorios por proveedor
- Estado de cuentas por pagar con proyección de intereses
- Gastos por categoría y local
- Exportación a Excel de todos los reportes

### Fase 5: Pruebas y Ajustes (3 días)
- Pruebas de integración con sistema VB6
- Validación de cálculos de intereses y transacciones
- Pruebas de concurrencia (evitar pagos duplicados)
- Ajustes finales, optimización y documentación

**Total estimado: 22 días hábiles**

## 10. Consideraciones Técnicas

### 10.1. Tecnologías
- **Framework**: CodeIgniter 4 (existente)
- **Base de datos**: SQL Server 2019+ (compartida con sistema VB6)
- **Frontend**: Bootstrap 5, JavaScript (jQuery compatible)
- **Reportes**: PHPExcel / PhpSpreadsheet para exportación a Excel

### 10.2. Seguridad
- **Autenticación**: Sesiones CodeIgniter 4 integradas con sistema actual
- **Permisos por rol**:
  - ADMIN: Acceso completo, puede editar intereses
  - CONTADOR: Registro de pagos y egresos, reportes
  - CAJERO: Solo registro de pagos (no puede editar intereses)
  - VENDEDOR: Solo consulta de facturas pendientes
- **Validaciones**: CSRF, XSS prevention, sanitización de inputs
- **Logs**: Auditoría completa de operaciones sensibles

### 10.3. Rendimiento
- **Índices recomendados**:
  ```sql
  CREATE INDEX IDX_CARTERA_PENDIENTES ON cartera(car_importe) WHERE car_importe > 0;
  CREATE INDEX IDX_EGRESOS_FECHA ON EGRESOS(EGR_FECHA, EGR_TIPO_EGRESO);
  CREATE INDEX IDX_PLAN_CUENTAS_ACTIVO ON PLAN_CUENTAS(PC_ACTIVO, PC_TIPO);
  ```
- **Cálculos de interés**: Realizados en PHP para flexibilidad, caché de resultados
- **Bloqueos**: Bloqueos pesimistas en actualizaciones de CARTERA para evitar conflictos

### 10.4. Integración con VB6
- **Transacciones atómicas**: Garantizar consistencia entre múltiples tablas
- **Secuencia separada**: Para `ALL_NUMOPER` generado por CI4, evitar colisiones
- **Campo ORIGEN**: En tablas CARACU/ALLOG para identificar fuente (`VB6`, `CI4`)
- **Validación de estado**: Verificar que VB6 no modifique registros creados por CI4

### 10.5. Backup y Recuperación
- **Backup automático**: Integrado en rutinas existentes de SQL Server
- **Puntos de restauración**: Antes de operaciones masivas de pago
- **Log de auditoría**: Todos los cambios registrados en tablas de log
- **Recuperación de errores**: Transacciones rollback en caso de fallo

### 10.6. Mantenimiento
- **Monitoreo**: Alertas para pagos duplicados o inconsistencias
- **Actualizaciones**: Migraciones CodeIgniter para cambios de esquema
- **Documentación**: Mantenimiento de documentación técnica y de usuario

## 11. Anexos

### 11.1. Ejemplo de Cálculo de Interés Moratorio
```
Datos:
- Saldo pendiente: S/ 1,697.50
- Fecha vencimiento: 09/01/2026
- Fecha pago: 28/01/2026
- Tasa anual: 5% (0.05)

Cálculo:
Días mora = 28/01/2026 - 09/01/2026 = 19 días
Interés = 1697.50 × 0.05 × (19 / 365)
Interés = 1697.50 × 0.05 × 0.05205
Interés = S/ 4.43

Total a pagar = Capital (1697.50) + Interés (4.43) = S/ 1,701.93
```

### 11.2. Flujo de Trabajo para Pago con Interés
1. **Selección de factura**: Usuario selecciona factura vencida desde listado
2. **Cálculo automático**: Sistema calcula días de mora e interés
3. **Revisión y ajuste**: Usuario puede editar monto de interés (solo ADMIN/CONTADOR)
4. **Confirmación**: Usuario confirma forma de pago y local
5. **Procesamiento**:
   - Actualiza CARTERA (saldo a 0)
   - Inserta en CARACU (histórico con CAA_CODTRA=5360)
   - Inserta en ALLOG (auditoría con ALL_CODTRA=5360)
   - Registra interés en EGRESOS (tipo INTERES_MORA)
   - Actualiza CAJA_MOVIMIENTOS (salida de efectivo)
6. **Confirmación final**: Sistema muestra comprobante y referencia

### 11.3. Cuentas Contables Sugeridas
```
Código  | Nombre                    | Tipo  | Padre
--------|---------------------------|-------|-------
6       | GASTOS                    | E     | NULL
6.1     | Gastos Operativos         | E     | 6
6.1.1   | Movilidad                 | E     | 6.1
6.1.2   | Servicios Básicos         | E     | 6.1
6.2     | Gastos Financieros        | E     | 6
6.2.1   | Intereses por Mora        | E     | 6.2  ⭐
6.2.2   | Comisiones Bancarias      | E     | 6.2
6.3     | Remuneraciones            | E     | 6
6.3.1   | Sueldos                   | E     | 6.3
6.3.2   | AFP                       | E     | 6.3
6.3.3   | Essalud                   | E     | 6.3
```

### 11.4. Código PHP de Ejemplo (Cálculo de Interés)
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
*Documento preparado para revisión y ajustes.*