# Guía Completa - Cloudflare Workers desde Cero

## Paso 1: Crear Cuenta en Cloudflare (GRATIS)

1. Ve a: https://dash.cloudflare.com/sign-up
2. Registra tu email y contraseña
3. Verifica tu email
4. **NO necesitas agregar un dominio** para usar Workers

## Paso 2: Instalar Node.js (si no lo tienes)

1. Descarga desde: https://nodejs.org/
2. Instala la versión LTS (recomendada)
3. Verifica instalación:
```bash
node --version
npm --version
```

## Paso 3: Instalar Wrangler CLI

Abre tu terminal (CMD o PowerShell) y ejecuta:

```bash
npm install -g wrangler
```

Verifica instalación:
```bash
wrangler --version
```

## Paso 4: Login en Cloudflare

```bash
wrangler login
```

Esto abrirá tu navegador. Haz clic en "Allow" para autorizar Wrangler.

## Paso 5: Desplegar el Worker

Navega a la carpeta del proyecto:

```bash
cd e:\DESARROLLO\medinafarma\cloudflare
```

Despliega:

```bash
wrangler deploy
```

**¡Listo!** Verás un mensaje como:
```
Published pharma-matching (1.23 sec)
  https://pharma-matching.tu-cuenta.workers.dev
```

## Paso 6: Copiar la URL del Worker

Copia la URL que aparece (ejemplo: `https://pharma-matching.tu-cuenta.workers.dev`)

## Paso 7: Configurar en CodeIgniter

Edita `app/Config/AIMatching.php`:

```php
public $cloudflareWorkerUrl = 'https://pharma-matching.tu-cuenta.workers.dev';
```

## Paso 8: Probar

1. Ve a: `http://localhost/medinafarma/aimatching`
2. Click en "Procesar con Cloudflare"
3. ¡Debería funcionar!

---

## Comandos Útiles

### Ver logs en tiempo real
```bash
wrangler tail
```

### Probar localmente antes de desplegar
```bash
wrangler dev
```
Luego prueba en: http://localhost:8787

### Actualizar el Worker
```bash
wrangler deploy
```

### Ver tus Workers desplegados
```bash
wrangler deployments list
```

---

## Troubleshooting

### Error: "wrangler: command not found"

Reinstala:
```bash
npm install -g wrangler
```

### Error: "Not logged in"

```bash
wrangler login
```

### Error: "Account not found"

Asegúrate de haber creado una cuenta en https://dash.cloudflare.com

### Ver el Worker en el Dashboard

1. Ve a: https://dash.cloudflare.com
2. Click en "Workers & Pages"
3. Verás tu worker "pharma-matching"
4. Click para ver estadísticas y logs

---

## Probar el Worker Manualmente

Usa Postman o curl:

```bash
curl -X POST https://pharma-matching.tu-cuenta.workers.dev \
  -H "Content-Type: application/json" \
  -d '{
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
        "ART_NOMBRE": "PARACETAMOL 500MG TABLETA"
      }
    ]
  }'
```

Deberías recibir:
```json
{
  "success": true,
  "results": [...],
  "processed": 1
}
```

---

## Límites del Plan Gratuito

- ✅ 100,000 requests/día
- ✅ 10ms CPU time por request
- ✅ Sin límite de Workers
- ✅ Sin tarjeta de crédito requerida

Si necesitas más:
- **Paid Plan**: $5/mes por 10 millones de requests

---

## Seguridad (Opcional)

### Agregar autenticación simple

Edita `worker.js`:

```javascript
export default {
  async fetch(request, env) {
    // Verificar token
    const authHeader = request.headers.get('Authorization');
    if (authHeader !== 'Bearer TU_TOKEN_SECRETO') {
      return new Response('Unauthorized', { status: 401 });
    }
    
    // ... resto del código
  }
};
```

Luego en CodeIgniter:

```php
$client = \Config\Services::curlrequest();
$response = $client->post($workerUrl, [
    'json' => [...],
    'headers' => [
        'Authorization' => 'Bearer TU_TOKEN_SECRETO'
    ]
]);
```

---

## Monitoreo

Dashboard: https://dash.cloudflare.com/workers

Verás:
- Requests por día
- Errores
- Latencia promedio
- Uso de CPU

---

## Preguntas Frecuentes

**¿Necesito dominio?**
No, Cloudflare te da un subdominio gratis: `*.workers.dev`

**¿Necesito tarjeta de crédito?**
No para el plan gratuito.

**¿Puedo usar mi propio dominio?**
Sí, pero necesitas agregar tu dominio a Cloudflare primero.

**¿Cuánto tarda en desplegar?**
~10 segundos.

**¿Puedo tener múltiples Workers?**
Sí, ilimitados en plan gratuito.

**¿Funciona en Perú?**
Sí, Cloudflare tiene edge servers globales, incluyendo cerca de Perú.

---

## Soporte

- Documentación: https://developers.cloudflare.com/workers/
- Discord: https://discord.cloudflare.com
- Foro: https://community.cloudflare.com/
