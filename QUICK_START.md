# Quick Start - AI Matching Module

## Instalación Rápida (5 minutos)

### 1. Instalar Dependencias (Opcional para AWS)

```bash
composer require aws/aws-sdk-php
```

### 2. Ejecutar Migración

```bash
php spark migrate
```

### 3. Agregar Rutas

Copiar el contenido de `app/Config/Routes_AI_Matching.php` en `app/Config/Routes.php`

O simplemente agregar:

```php
$routes->group('aimatching', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'AIMatching::index');
    $routes->post('processBatch', 'AIMatching::processBatch');
    $routes->get('review', 'AIMatching::review');
    $routes->post('approve', 'AIMatching::approve');
    $routes->post('reject', 'AIMatching::reject');
    $routes->post('batchApprove', 'AIMatching::batchApprove');
});
```

### 4. Cargar Helper

En `app/Config/Autoload.php`:

```php
public $helpers = ['pharma'];
```

### 5. Acceder

Navegar a: `http://localhost/medinafarma/aimatching`

## Uso Básico

1. **Dashboard**: Ver estadísticas
2. **Procesar**: Click en "Iniciar Procesamiento Local"
3. **Revisar**: Click en "Revisar Sugerencias"
4. **Aprobar/Rechazar**: Validar cada sugerencia

## Sin AWS (Solo Local)

El sistema funciona completamente sin AWS usando:
- Algoritmos de similitud textual (Levenshtein, Jaccard)
- Normalización de texto farmacéutico
- Extracción de concentraciones con regex
- Matching por forma farmacéutica

## Con AWS (Opcional)

Para usar AWS Comprehend Medical:

1. Configurar `.env`:
```env
AWS_ACCESS_KEY=tu_key
AWS_SECRET_KEY=tu_secret
```

2. Desplegar Lambda:
```bash
cd aws
./deploy.sh
```

3. Click en "Procesar con AWS Lambda"

## Estructura de Archivos Creados

```
medinafarma/
├── app/
│   ├── Config/
│   │   ├── AIMatching.php              ✓ Configuración
│   │   └── Routes_AI_Matching.php      ✓ Rutas
│   ├── Controllers/
│   │   └── AIMatching.php              ✓ Controlador principal
│   ├── Models/
│   │   └── AIMatchingModel.php         ✓ Modelo de datos
│   ├── Helpers/
│   │   └── pharma_helper.php           ✓ Funciones de procesamiento
│   ├── Views/
│   │   └── ai_matching/
│   │       ├── dashboard.php           ✓ Vista principal
│   │       └── review.php              ✓ Vista de revisión
│   └── Database/
│       └── Migrations/
│           └── 2024-01-01-000001_CreateAIMatchingTables.php ✓
├── aws/
│   ├── lambda/
│   │   ├── pharma_matching.py          ✓ Función Lambda
│   │   └── requirements.txt            ✓
│   ├── cloudformation/
│   │   └── ai-matching-stack.yaml      ✓ Infraestructura
│   └── deploy.sh                       ✓ Script de despliegue
├── DEPLOYMENT_GUIDE.md                 ✓ Guía completa
└── QUICK_START.md                      ✓ Esta guía
```

## Verificación

### Verificar Tablas

```sql
SELECT * FROM digemid_matching_suggestions
SELECT * FROM digemid_matching_history
```

### Verificar Helper

```php
echo normalizar_producto('PARACETAMOL 500MG');
// Output: paracetamol 500mg
```

### Verificar Rutas

```bash
php spark routes | grep aimatching
```

## Próximos Pasos

1. Procesar primer lote de productos
2. Revisar sugerencias generadas
3. Ajustar umbrales en `app/Config/AIMatching.php`
4. Agregar sinónimos específicos de tu catálogo
5. (Opcional) Configurar AWS para análisis avanzado

## Soporte

Ver `DEPLOYMENT_GUIDE.md` para documentación completa.
