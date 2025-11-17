# Despliegue en Cloudflare Workers

## Ventajas vs AWS Lambda

- ✅ **Gratis**: 100,000 requests/día
- ✅ **Más rápido**: Edge computing global
- ✅ **Sin cold start**: Siempre activo
- ✅ **Más simple**: Sin configuración compleja
- ✅ **Sin dependencias**: JavaScript nativo

## Instalación

### 1. Instalar Wrangler CLI

```bash
npm install -g wrangler
```

### 2. Login en Cloudflare

```bash
wrangler login
```

### 3. Desplegar Worker

```bash
cd cloudflare
wrangler deploy
```

## Configuración en CodeIgniter

Actualizar `app/Config/AIMatching.php`:

```php
public $cloudflareWorkerUrl = 'https://pharma-matching.tu-cuenta.workers.dev';
```

## Uso

El worker recibe:

```json
{
  "products": [
    {
      "Cod_Prod": 123,
      "Nom_Prod": "PARACETAMOL 500MG",
      "Concent": "500MG"
    }
  ],
  "articles": [
    {
      "ART_KEY": 456,
      "ART_NOMBRE": "PARACETAMOL 500MG TAB"
    }
  ]
}
```

Retorna:

```json
{
  "success": true,
  "results": [
    {
      "cod_prod": 123,
      "product_name": "PARACETAMOL 500MG",
      "matches": [
        {
          "art_key": 456,
          "article_name": "PARACETAMOL 500MG TAB",
          "score": 0.95,
          "details": {
            "name_similarity": 0.93,
            "concentration_similarity": 1.0
          }
        }
      ]
    }
  ],
  "processed": 1
}
```

## Testing Local

```bash
wrangler dev
```

Luego probar en: http://localhost:8787

## Costos

- **Gratis**: 100,000 requests/día
- **Paid**: $5/mes por 10 millones de requests adicionales

## Monitoreo

Dashboard: https://dash.cloudflare.com/workers
