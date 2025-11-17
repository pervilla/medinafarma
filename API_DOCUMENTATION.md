# API Documentation - AI Matching Module

## Endpoints

### 1. Dashboard

**GET** `/aimatching`

Muestra el dashboard principal con estadísticas.

**Response:** HTML View

---

### 2. Procesar Lote

**POST** `/aimatching/processBatch`

Procesa un lote de productos sin emparejar y genera sugerencias.

**Request Body:**
```json
{
  "batch_size": 50
}
```

**Response:**
```json
{
  "success": true,
  "processed": 50,
  "suggestions": 35,
  "message": "Procesados 50 productos, 35 sugerencias generadas"
}
```

**Códigos de Estado:**
- `200`: Procesamiento exitoso
- `400`: Parámetros inválidos
- `500`: Error del servidor

---

### 3. Procesar con AWS

**POST** `/aimatching/processWithAWS`

Procesa productos usando AWS Lambda y Comprehend Medical.

**Request Body:**
```json
{}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "results": [...],
    "processed": 10
  }
}
```

**Errores:**
```json
{
  "success": false,
  "message": "Error AWS: Invalid credentials"
}
```

---

### 4. Revisar Sugerencias

**GET** `/aimatching/review`

Muestra sugerencias pendientes de validación.

**Response:** HTML View con lista de sugerencias

---

### 5. Aprobar Sugerencia

**POST** `/aimatching/approve`

Aprueba una sugerencia y crea la relación.

**Request Body:**
```json
{
  "id": 123
}
```

**Response:**
```json
{
  "success": true,
  "message": "Sugerencia aprobada"
}
```

**Errores:**
```json
{
  "success": false,
  "message": "Error al aprobar"
}
```

---

### 6. Rechazar Sugerencia

**POST** `/aimatching/reject`

Rechaza una sugerencia.

**Request Body:**
```json
{
  "id": 123,
  "reason": "Productos diferentes"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Sugerencia rechazada"
}
```

---

### 7. Aprobar en Lote

**POST** `/aimatching/batchApprove`

Aprueba automáticamente todas las sugerencias con score ≥85%.

**Request Body:**
```json
{}
```

**Response:**
```json
{
  "success": true,
  "approved": 15,
  "message": "15 sugerencias aprobadas automáticamente"
}
```

---

### 8. Estadísticas

**GET** `/aimatching/stats`

Obtiene estadísticas del sistema.

**Response:**
```json
{
  "total_digemid": 5000,
  "matched": 3500,
  "unmatched": 1500,
  "pending_suggestions": 250,
  "match_rate": 70.00,
  "avg_approved_score": 0.8756
}
```

---

## Funciones Helper

### normalizar_producto($texto)

Normaliza texto farmacéutico.

```php
$normalizado = normalizar_producto('PARACETAMOL 500MG');
// Output: "paracetamol 500mg"
```

### extraer_concentracion($texto)

Extrae concentraciones del texto.

```php
$conc = extraer_concentracion('IBUPROFENO 400MG TABLETA');
// Output: "400mg"
```

### extraer_forma_farmaceutica($texto)

Extrae la forma farmacéutica.

```php
$forma = extraer_forma_farmaceutica('AMOXICILINA 500MG CAPSULA');
// Output: "capsula"
```

### similitud_levenshtein($str1, $str2)

Calcula similitud usando distancia Levenshtein.

```php
$sim = similitud_levenshtein('paracetamol', 'paracetamol');
// Output: 1.0 (100%)
```

### similitud_jaccard($str1, $str2)

Calcula similitud usando índice Jaccard.

```php
$sim = similitud_jaccard('ibuprofeno 400mg', 'ibuprofeno 400 mg');
// Output: 0.75
```

### calcular_score_matching($producto1, $producto2)

Calcula score de matching combinado.

```php
$score = calcular_score_matching(
    [
        'nombre' => 'PARACETAMOL 500MG',
        'concentracion' => '500mg',
        'forma_farmaceutica' => 'tableta'
    ],
    [
        'nombre' => 'PARACETAMOL 500MG TAB',
        'concentracion' => '500mg',
        'forma_farmaceutica' => 'tableta'
    ]
);
// Output: 0.95
```

### aplicar_sinonimos($texto)

Aplica sinónimos farmacéuticos.

```php
$texto = aplicar_sinonimos('acetaminofen 500mg');
// Output: "paracetamol 500mg"
```

---

## Modelo de Datos

### digemid_matching_suggestions

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| cod_prod | VARCHAR(20) | Código producto DIGEMID |
| art_key | VARCHAR(20) | Código artículo establecimiento |
| score | DECIMAL(5,4) | Score de confianza (0-1) |
| status | VARCHAR(20) | pending/approved/rejected |
| validated_by | INT | ID usuario validador |
| validated_at | DATETIME | Fecha validación |
| metadata | NVARCHAR(MAX) | JSON con detalles |
| created_at | DATETIME | Fecha creación |

### digemid_matching_history

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| cod_prod | VARCHAR(20) | Código producto DIGEMID |
| art_key | VARCHAR(20) | Código artículo |
| score | DECIMAL(5,4) | Score |
| action | VARCHAR(20) | approved/rejected |
| user_id | INT | ID usuario |
| created_at | DATETIME | Fecha |

---

## Configuración

### AIMatching Config

```php
// app/Config/AIMatching.php

public $autoMatchThreshold = 0.85;  // Auto-aprobación
public $suggestThreshold = 0.70;    // Sugerencia
public $minThreshold = 0.50;        // Mínimo considerar
public $batchSize = 50;             // Tamaño lote
public $maxSuggestions = 5;         // Sugerencias por producto
```

---

## Ejemplos de Uso

### Ejemplo 1: Procesar Lote Completo

```javascript
// Frontend
$.post('/aimatching/processBatch', {batch_size: 100}, function(response) {
    console.log(`Procesados: ${response.processed}`);
    console.log(`Sugerencias: ${response.suggestions}`);
});
```

### Ejemplo 2: Aprobar Sugerencia

```javascript
$.post('/aimatching/approve', {id: 123}, function(response) {
    if (response.success) {
        alert('Aprobado exitosamente');
    }
});
```

### Ejemplo 3: Obtener Estadísticas

```javascript
$.get('/aimatching/stats', function(stats) {
    console.log(`Tasa de matching: ${stats.match_rate}%`);
});
```

### Ejemplo 4: Uso de Helpers en PHP

```php
// En un controlador o modelo
helper('pharma');

$producto = 'PARACETAMOL 500MG TABLETA';
$normalizado = normalizar_producto($producto);
$concentracion = extraer_concentracion($producto);
$forma = extraer_forma_farmaceutica($producto);

echo "Normalizado: $normalizado\n";
echo "Concentración: $concentracion\n";
echo "Forma: $forma\n";
```

---

## Códigos de Error

| Código | Descripción |
|--------|-------------|
| 200 | Éxito |
| 400 | Parámetros inválidos |
| 401 | No autorizado |
| 404 | Recurso no encontrado |
| 500 | Error del servidor |

---

## Rate Limiting

No implementado actualmente. Considerar para producción:
- 100 requests/minuto por IP
- 1000 requests/hora por usuario

---

## Seguridad

- Validar sesión de usuario
- Sanitizar inputs
- Usar prepared statements
- Validar permisos por rol
- Logs de auditoría en historial
