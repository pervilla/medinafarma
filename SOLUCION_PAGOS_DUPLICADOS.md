# Solución para Evitar Pagos Duplicados en Citas

## Problema Identificado
En el módulo de citas, cuando se procesaba un pago mediante "Procesar Pago", se generaba un comprobante por todos los servicios de la cita, pero no se marcaba qué servicios ya habían sido pagados. Esto permitía generar múltiples comprobantes por los mismos servicios.

## Solución Implementada

### 1. Modificación de la Base de Datos
- **Archivo**: `ALTER_CITAS_SERVICIOS_ADD_PAGADO.sql`
- **Cambio**: Se agregó la columna `CNS_PAGADO` (BIT) a la tabla `CITAS_SERVICIOS`
- **Propósito**: Marcar qué servicios ya han sido incluidos en un comprobante

### 2. Nuevo Stored Procedure
- **Archivo**: `SP_GenerarComprobanteConsultorio_v3.sql`
- **Mejoras**:
  - Solo procesa servicios con `CNS_PAGADO = 0` (no pagados)
  - Marca automáticamente los servicios como pagados (`CNS_PAGADO = 1`) después de generar el comprobante
  - Valida que existan servicios pendientes antes de procesar
  - Incluye parámetros adicionales para forma de pago y referencia

### 3. Actualización del Modelo CitaServicioModel
- **Archivo**: `app/Models/CitaServicioModel.php`
- **Nuevos métodos**:
  - `getTotalServiciosPendientes()`: Calcula el total de servicios no pagados
  - `marcarServiciosComoPagados()`: Marca servicios como pagados manualmente
- **Modificaciones**:
  - `getServiciosByCita()`: Incluye el estado de pago de cada servicio
  - Agregado `CNS_PAGADO` a los campos permitidos

### 4. Actualización del Modelo CitaPagoModel
- **Archivo**: `app/Models/CitaPagoModel.php`
- **Cambios**:
  - `generarComprobanteDirecto()`: Actualizado para usar SP v3 con parámetros adicionales
  - `procesarPagoCompleto()`: Mejorado para manejar forma de pago y referencia

### 5. Actualización del Controlador
- **Archivo**: `app/Controllers/Citas.php`
- **Mejoras**:
  - `getCitaDetalle()`: Incluye total de servicios pendientes
  - Mejor manejo de parámetros en `procesarPago()`

### 6. Actualización de la Vista
- **Archivo**: `app/Views/consultorio/citas_servicios.php`
- **Mejoras en la interfaz**:
  - Nueva columna "Estado Pago" en la tabla de servicios
  - Fila adicional mostrando "PENDIENTE DE PAGO"
  - Servicios pagados se muestran en verde y no se pueden eliminar
  - El botón "Pagar" solo procesa servicios pendientes
  - Validación para evitar procesar pagos cuando no hay servicios pendientes

## Flujo de Funcionamiento

### Antes (Problema)
1. Usuario agrega servicios a una cita
2. Usuario hace clic en "Procesar Pago"
3. Se genera comprobante por TODOS los servicios
4. Usuario puede volver a hacer clic en "Procesar Pago"
5. Se genera OTRO comprobante por los MISMOS servicios ❌

### Después (Solución)
1. Usuario agrega servicios a una cita (todos marcados como `CNS_PAGADO = 0`)
2. Usuario hace clic en "Procesar Pago"
3. Se genera comprobante SOLO por servicios con `CNS_PAGADO = 0`
4. Los servicios procesados se marcan como `CNS_PAGADO = 1`
5. Si usuario intenta "Procesar Pago" nuevamente, el sistema muestra "No hay servicios pendientes de pago" ✅

## Beneficios

1. **Evita pagos duplicados**: Imposible generar múltiples comprobantes por los mismos servicios
2. **Trazabilidad**: Se puede ver claramente qué servicios han sido pagados
3. **Flexibilidad**: Permite pagos parciales (pagar algunos servicios y dejar otros pendientes)
4. **Interfaz clara**: El usuario ve visualmente qué servicios están pagados y cuáles pendientes
5. **Compatibilidad**: Los cambios son retrocompatibles con datos existentes

## Archivos Modificados

1. `app/Models/CitaServicioModel.php`
2. `app/Models/CitaPagoModel.php`
3. `app/Controllers/Citas.php`
4. `app/Views/consultorio/citas_servicios.php`
5. `SP_GenerarComprobanteConsultorio_v3.sql` (nuevo)
6. `ALTER_CITAS_SERVICIOS_ADD_PAGADO.sql` (nuevo)

## Instrucciones de Implementación

1. **Ejecutar el script de base de datos**:
   ```sql
   -- Ejecutar ALTER_CITAS_SERVICIOS_ADD_PAGADO.sql en SQL Server
   ```

2. **Crear el nuevo stored procedure**:
   ```sql
   -- Ejecutar SP_GenerarComprobanteConsultorio_v3.sql en SQL Server
   ```

3. **Los archivos PHP ya están actualizados** y funcionarán automáticamente una vez que se ejecuten los scripts de base de datos.

## Pruebas Recomendadas

1. Crear una nueva cita con servicios
2. Verificar que todos los servicios aparecen como "PENDIENTE"
3. Procesar pago y verificar que se genera comprobante
4. Verificar que los servicios ahora aparecen como "PAGADO" en verde
5. Intentar procesar pago nuevamente y verificar mensaje de error
6. Agregar nuevos servicios y verificar que solo estos aparecen como pendientes
7. Procesar pago parcial y verificar funcionamiento correcto

## Notas Técnicas

- La columna `CNS_PAGADO` es de tipo BIT con valor por defecto 0
- Los registros existentes se marcan automáticamente como no pagados (0)
- El stored procedure v3 es completamente independiente del original
- La solución es retrocompatible y no afecta funcionalidades existentes