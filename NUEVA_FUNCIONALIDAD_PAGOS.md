# Nueva Funcionalidad: Pago Directo desde Citas

## Descripción
Se ha implementado una nueva funcionalidad que permite generar comprobantes de venta directamente desde las citas cuando se presiona el botón "Pagar", replicando la lógica del sistema Visual Basic 6.

## Cambios Implementados

### 1. Controlador Citas.php
- **Nuevo método `procesarPago()`**: Procesa el pago directo y genera comprobante inmediatamente
- **Ruta agregada**: `POST /citas/procesarPago`

### 2. Modelo CitaPagoModel.php
- **Nuevo método `generarComprobanteDirecto()`**: Genera comprobante siguiendo la lógica del VB6
- **Configuración según especificaciones**:
  - `@FAR_TIPMOV = 10` (venta)
  - `@FAR_CODCIA = 25` (código empresa fijo)
  - Series por local: LOCAL 01=21, LOCAL 02=20, LOCAL 03=22
  - Tipos de comprobante: F=Factura, B=Boleta

### 3. Vista citas_servicios.php
- **Botón "Pagar"** agregado en el panel de servicios
- **Modal de pago directo** con opciones de forma de pago, tipo de comprobante y local
- **JavaScript** para manejar el flujo de pago directo

## Flujo de Funcionamiento

### Lógica Actual (Nueva)
1. Usuario selecciona servicios en la cita
2. Presiona botón "Pagar"
3. Sistema toma el total de servicios seleccionados
4. Genera comprobante inmediatamente en tablas ALLOG y FACART
5. Registra el pago asociado al comprobante
6. Si se agregan más servicios, se puede generar un nuevo comprobante

### Tablas Afectadas
- **ALLOG**: Cabecera del comprobante (movimiento contable)
- **FACART**: Detalle del comprobante (artículos/servicios)
- **CITAS_PAGOS**: Registro del pago realizado

## Campos Importantes

### Tabla ALLOG
```sql
ALL_CODCIA = '25'           -- Código empresa (fijo)
ALL_TIPMOV = 10             -- Tipo movimiento (venta)
ALL_FBG = 'B'/'F'          -- Tipo comprobante
ALL_NUMSER = 21/20/22       -- Serie según local
ALL_NUMFAC = [consecutivo]  -- Número correlativo
ALL_SIGNO_CAR = 1           -- Signo positivo (venta)
ALL_CP = 'C'                -- Cliente
ALL_ESTADO = 'N'            -- Normal
```

### Tabla FACART
```sql
FAR_TIPMOV = 10             -- Tipo movimiento (venta)
FAR_CODCIA = '25'           -- Código empresa
FAR_SIGNO_ARM = 0           -- Sin afectar stock (servicios)
FAR_SIGNO_CAR = 1           -- Positivo (venta)
FAR_CP = 'C'                -- Cliente
FAR_ESTADO = 'N'            -- Normal
FAR_MONEDA = 'S'            -- Soles
```

## Configuración de Series por Local

```php
$numser_map = [
    '01' => '21',  // LOCAL 01 = Serie 21
    '02' => '20',  // LOCAL 02 = Serie 20
    '03' => '22'   // LOCAL 03 = Serie 22
];
```

## Ventajas de la Nueva Implementación

1. **Simplicidad**: Un solo clic para pagar y generar comprobante
2. **Consistencia**: Sigue la misma lógica del sistema VB6 existente
3. **Flexibilidad**: Permite múltiples comprobantes por cita si se agregan servicios
4. **Trazabilidad**: Mantiene registro completo de pagos y comprobantes
5. **Integración**: Se integra perfectamente con el sistema contable existente

## Uso

1. Abrir gestión de cita
2. Agregar servicios necesarios
3. Presionar botón "Pagar" en el panel de servicios
4. Seleccionar forma de pago, tipo de comprobante y local
5. Confirmar - el sistema genera automáticamente el comprobante

## Notas Técnicas

- Los servicios no afectan stock (FAR_SIGNO_ARM = 0)
- Se calcula IGV automáticamente (18%)
- Los números de comprobante son consecutivos por serie
- Se mantiene compatibilidad con el sistema de reportes existente
- La funcionalidad original de pagos manuales se mantiene intacta