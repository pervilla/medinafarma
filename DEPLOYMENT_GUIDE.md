# Guía de Despliegue - Módulo AI Matching

## Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                    CodeIgniter 4 Application                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ AIMatching   │  │ AIMatching   │  │   pharma     │      │
│  │ Controller   │─▶│    Model     │  │   helper     │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│         │                  │                                 │
│         ▼                  ▼                                 │
│  ┌──────────────────────────────────┐                       │
│  │      SQL Server 2008 R2          │                       │
│  │  - digemid_productos             │                       │
│  │  - ARTICULO                      │                       │
│  │  - digemid_matching_suggestions  │                       │
│  │  - digemid_matching_history      │                       │
│  └──────────────────────────────────┘                       │
└─────────────────────────────────────────────────────────────┘
                           │
                           │ (Opcional)
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                      AWS Cloud Services                      │
│  ┌──────────────┐         ┌──────────────────┐             │
│  │   Lambda     │────────▶│   Comprehend     │             │
│  │   Function   │         │    Medical       │             │
│  └──────────────┘         └──────────────────┘             │
└─────────────────────────────────────────────────────────────┘
```

## Instalación Local (Sin AWS)

### 1. Copiar Archivos

Todos los archivos ya están creados en:
- `app/Config/AIMatching.php`
- `app/Helpers/pharma_helper.php`
- `app/Models/AIMatchingModel.php`
- `app/Controllers/AIMatching.php`
- `app/Views/ai_matching/dashboard.php`
- `app/Views/ai_matching/review.php`
- `app/Database/Migrations/2024-01-01-000001_CreateAIMatchingTables.php`

### 2. Ejecutar Migración de Base de Datos

```bash
php spark migrate
```

Esto creará las tablas:
- `digemid_matching_suggestions`
- `digemid_matching_history`

### 3. Configurar Rutas

Agregar en `app/Config/Routes.php`:

```php
$routes->group('aimatching', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'AIMatching::index');
    $routes->post('processBatch', 'AIMatching::processBatch');
    $routes->post('processWithAWS', 'AIMatching::processWithAWS');
    $routes->get('review', 'AIMatching::review');
    $routes->post('approve', 'AIMatching::approve');
    $routes->post('reject', 'AIMatching::reject');
    $routes->post('batchApprove', 'AIMatching::batchApprove');
    $routes->get('stats', 'AIMatching::stats');
});
```

### 4. Cargar Helper

Agregar en `app/Config/Autoload.php`:

```php
public $helpers = ['pharma'];
```

### 5. Acceder al Módulo

Navegar a: `http://tu-dominio/aimatching`

## Instalación con AWS (Opcional)

### Prerrequisitos

- Cuenta AWS activa
- AWS CLI instalado y configurado
- Permisos para crear Lambda, IAM, CloudWatch

### 1. Configurar Credenciales AWS

Agregar en `.env`:

```env
AWS_ACCESS_KEY=tu_access_key
AWS_SECRET_KEY=tu_secret_key
AWS_REGION=us-east-1
```

### 2. Desplegar Infraestructura

```bash
cd aws
chmod +x deploy.sh
./deploy.sh
```

O manualmente:

```bash
# Desplegar CloudFormation
aws cloudformation deploy \
    --template-file cloudformation/ai-matching-stack.yaml \
    --stack-name pharma-ai-matching-stack \
    --capabilities CAPABILITY_NAMED_IAM \
    --region us-east-1

# Empaquetar Lambda
cd lambda
pip install -r requirements.txt -t .
zip -r ../pharma-matching.zip .

# Actualizar función
aws lambda update-function-code \
    --function-name pharma-matching-processor \
    --zip-file fileb://pharma-matching.zip \
    --region us-east-1
```

### 3. Verificar Despliegue

```bash
aws cloudformation describe-stacks \
    --stack-name pharma-ai-matching-stack \
    --query 'Stacks[0].Outputs'
```

## Uso del Sistema

### Flujo de Trabajo

1. **Dashboard** (`/aimatching`)
   - Ver estadísticas de matching
   - Iniciar procesamiento por lotes
   - Aprobar sugerencias de alta confianza

2. **Procesamiento Local**
   - Click en "Iniciar Procesamiento Local"
   - Seleccionar tamaño de lote (10-100 productos)
   - El sistema genera sugerencias automáticamente

3. **Procesamiento AWS** (Opcional)
   - Click en "Procesar con AWS Lambda"
   - Usa Comprehend Medical para análisis avanzado
   - Mayor precisión en extracción de entidades

4. **Revisión** (`/aimatching/review`)
   - Ver sugerencias pendientes ordenadas por score
   - Aprobar o rechazar cada sugerencia
   - Ver detalles del análisis

5. **Aprobación Automática**
   - Sugerencias con score ≥85% pueden auto-aprobarse
   - Click en "Aprobar Alta Confianza"

### Algoritmo de Matching

**Componentes del Score (0-1):**

1. **Similitud de Nombre (50%)**
   - Distancia Levenshtein normalizada
   - Índice Jaccard de palabras
   - Aplicación de sinónimos farmacéuticos

2. **Similitud de Concentración (30%)**
   - Extracción de dosis (mg, ml, g, etc.)
   - Comparación exacta

3. **Forma Farmacéutica (20%)**
   - Tableta, cápsula, jarabe, etc.
   - Coincidencia exacta o parcial

**Umbrales:**
- **≥85%**: Auto-aprobación recomendada
- **70-84%**: Sugerencia para revisión
- **50-69%**: Revisión manual obligatoria
- **<50%**: Descartado

## Configuración Avanzada

### Ajustar Umbrales

Editar `app/Config/AIMatching.php`:

```php
public $autoMatchThreshold = 0.85;  // Cambiar según necesidad
public $suggestThreshold = 0.70;
public $minThreshold = 0.50;
```

### Agregar Sinónimos

```php
public $synonyms = [
    'paracetamol' => ['acetaminofen', 'acetaminofén'],
    'ibuprofeno' => ['ibuprofén'],
    // Agregar más...
];
```

### Ajustar Tamaño de Lote

```php
public $batchSize = 50;  // Productos por lote
public $maxSuggestions = 5;  // Sugerencias por producto
```

## Mantenimiento

### Limpiar Sugerencias Rechazadas

```php
// En controller o comando CLI
$this->matchingModel->clearOldRejections(30); // Días
```

### Ver Estadísticas

```bash
curl http://tu-dominio/aimatching/stats
```

### Logs

- **Local**: `writable/logs/`
- **AWS**: CloudWatch Logs `/aws/lambda/pharma-matching-processor`

## Costos AWS (Estimados)

**Lambda:**
- 512 MB RAM, 300s timeout
- ~$0.0000166667 por segundo
- 1000 ejecuciones/mes: ~$5

**Comprehend Medical:**
- $0.01 por unidad (100 caracteres)
- 10,000 productos: ~$50-100

**Total estimado:** $55-105/mes para 10,000 productos

## Troubleshooting

### Error: Tabla no existe
```bash
php spark migrate
```

### Error: Helper no encontrado
Verificar `app/Config/Autoload.php` incluye `'pharma'`

### Error AWS: Credenciales inválidas
Verificar `.env` tiene AWS_ACCESS_KEY y AWS_SECRET_KEY

### Score muy bajo
- Revisar sinónimos en configuración
- Ajustar umbrales
- Verificar normalización de texto

## Mejoras Futuras

1. **Machine Learning**
   - Entrenar modelo con historial de decisiones
   - Usar AWS SageMaker

2. **Procesamiento Asíncrono**
   - Cola de trabajos con SQS
   - Procesamiento en background

3. **API REST**
   - Exponer endpoints para integraciones
   - Documentación OpenAPI

4. **Dashboard Analítico**
   - Gráficos de tendencias
   - Métricas de precisión del algoritmo

## Soporte

Para problemas o consultas:
1. Revisar logs en `writable/logs/`
2. Verificar configuración en `app/Config/AIMatching.php`
3. Consultar documentación de CodeIgniter 4
