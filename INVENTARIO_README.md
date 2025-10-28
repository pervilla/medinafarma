# Módulo de Inventario - MedinaFarma

## Descripción
Sistema completo de gestión de inventarios con interfaz responsive y moderna, diseñado para facilitar el proceso de conteo físico de productos en farmacias.

## Características Principales

### 🎯 Dashboard Principal
- Vista general de todos los inventarios activos
- Estadísticas en tiempo real de progreso
- Gestión de responsables y asignación de productos
- Interfaz responsive para dispositivos móviles y desktop

### 👥 Gestión de Responsables
- Asignación de empleados a inventarios específicos
- Configuración de proporciones para distribución equitativa
- Distribución automática o manual de productos
- Seguimiento del progreso individual

### 📱 Interfaz de Conteo
- Modo optimizado para tablets y móviles
- Búsqueda rápida de productos
- Entrada intuitiva de cantidades físicas
- Cálculo automático de diferencias
- Sistema de comentarios y observaciones

### 📊 Reportes y Seguimiento
- Progreso en tiempo real
- Estadísticas por responsable
- Exportación de datos
- Historial de cambios

## Estructura de Archivos

```
app/
├── Controllers/
│   └── Inventario.php          # Controlador principal
├── Models/
│   └── InventarioModel.php     # Modelo de datos
└── Views/inventario/
    ├── dashboard.php           # Dashboard principal
    ├── conteo.php             # Interfaz de conteo móvil
    ├── index_listado.php      # Lista de productos mejorada
    └── index_inventario_ct.php # Vista original (legacy)

public/css/
└── inventario.css             # Estilos personalizados
```

## Rutas Principales

- `/inventario` → Redirige al dashboard
- `/inventario/dashboard` → Dashboard principal
- `/inventario/conteo/{local}/{inv}/{vendedor}` → Interfaz de conteo
- `/inventario/lista/{local}/{inv}/{vendedor}/{total}` → Lista de productos

## Funcionalidades por Pantalla

### Dashboard (`/inventario/dashboard`)
1. **Crear Inventario**
   - Seleccionar local (Central/Juanjuicillo)
   - Descripción del inventario
   - Generación automática de productos con stock

2. **Gestionar Responsables**
   - Agregar empleados al inventario
   - Configurar proporciones de asignación
   - Distribuir productos automáticamente

3. **Monitoreo**
   - Ver progreso general
   - Estadísticas por inventario
   - Acceso rápido a reportes

### Interfaz de Conteo (`/inventario/conteo`)
1. **Características Móviles**
   - Diseño optimizado para tablets
   - Navegación táctil intuitiva
   - Entrada rápida de datos

2. **Funcionalidades**
   - Lista de productos asignados
   - Búsqueda instantánea
   - Entrada de stock físico
   - Cálculo automático de diferencias
   - Sistema de comentarios
   - Guardado automático cada 30 segundos

3. **Filtros y Búsqueda**
   - Filtrar por estado (Todos/Pendientes/Contados)
   - Búsqueda por código o nombre
   - Navegación rápida a productos específicos

### Lista de Productos (`/inventario/lista`)
1. **Vista Tabular Completa**
   - Todos los productos asignados
   - Edición inline de cantidades
   - Exportación a Excel
   - Guardado masivo

2. **Estadísticas en Tiempo Real**
   - Contador de productos contados/pendientes
   - Barra de progreso visual
   - Porcentaje de avance

## Base de Datos

### Tablas Principales

1. **INVENTARIOS**
   - `inv_id`: ID único del inventario
   - `inv_descripcion`: Descripción del inventario
   - `inv_fecha`: Fecha de creación
   - `inv_estado`: Estado activo/inactivo
   - `inv_local`: Local asignado
   - `inv_total_items`: Total de productos

2. **INVENTARIO_RESPONSABLES**
   - `inr_id`: ID único del responsable
   - `inv_id`: ID del inventario
   - `vem_codven`: Código del vendedor/empleado
   - `inr_proporcion`: Proporción asignada

3. **INVENTARIO_DETALLE**
   - `ind_id`: ID único del detalle
   - `inv_id`: ID del inventario
   - `art_key`: Código del artículo
   - `vem_codven`: Responsable asignado
   - `arm_stock`: Stock del sistema
   - `ind_stock_fisico`: Stock físico contado
   - `ind_diferencia`: Diferencia calculada
   - `ind_estado`: Estado (pendiente/contado/revisado)
   - `ind_observaciones`: Comentarios

## Flujo de Trabajo Recomendado

1. **Preparación**
   - Crear nuevo inventario desde el dashboard
   - Asignar responsables con proporciones adecuadas
   - Distribuir productos automáticamente

2. **Ejecución**
   - Los responsables acceden a la interfaz de conteo
   - Realizan el conteo físico usando tablets/móviles
   - El sistema guarda automáticamente el progreso

3. **Supervisión**
   - Monitorear progreso desde el dashboard
   - Revisar diferencias significativas
   - Generar reportes finales

## Características Técnicas

### Responsive Design
- Bootstrap 4 para compatibilidad móvil
- Interfaz adaptativa para diferentes tamaños de pantalla
- Optimización táctil para tablets

### Performance
- Carga asíncrona de datos con AJAX
- Paginación inteligente
- Guardado automático para evitar pérdida de datos

### Usabilidad
- Búsqueda instantánea
- Filtros dinámicos
- Feedback visual inmediato
- Tooltips y ayuda contextual

## Instalación y Configuración

1. Asegurar que las tablas estén creadas en la base de datos
2. Verificar que las rutas estén configuradas en `Routes.php`
3. Incluir los archivos CSS personalizados en el template
4. Configurar permisos de usuario según sea necesario

## Soporte y Mantenimiento

- El código está documentado para facilitar el mantenimiento
- Estructura modular para futuras expansiones
- Compatibilidad con CodeIgniter 4
- Preparado para múltiples locales/sucursales

## Próximas Mejoras Sugeridas

1. **Reportes Avanzados**
   - Gráficos de progreso
   - Análisis de diferencias
   - Exportación a PDF

2. **Notificaciones**
   - Alertas por diferencias significativas
   - Notificaciones push para móviles

3. **Integración**
   - Sincronización con sistema de ventas
   - API para aplicaciones móviles nativas

4. **Auditoría**
   - Log de cambios detallado
   - Trazabilidad completa de modificaciones