<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Models\ImportFactModel;
use DateTime;

class SireSunat extends BaseController
{
    use ResponseTrait;
    protected $response;

    public function __construct()
    {
        $this->response = service('response');
    }

    public function index()
    {
        return $this->testSunatAPI();
    }

    private function getToken()
    {
        $cache = \Config\Services::cache();
        $cachedToken = $cache->get('sunat_token');
        if ($cachedToken) {
            return $cachedToken;
        }
        $client = \Config\Services::curlrequest();
        $url = "https://api-seguridad.sunat.gob.pe/v1/clientessol/1bcc2e4f-5113-4f42-b5ce-bb058a44558b/oauth2/token/";

        $response = $client->post($url, [
            'form_params' => [
                'grant_type'    => 'password',
                'scope'         => 'https://api-sire.sunat.gob.pe',
                'client_id'     => '1bcc2e4f-5113-4f42-b5ce-bb058a44558b',
                'client_secret' => 'kKaPjyTo27LzZt7rmyFWtQ==',
                'username'      => '20450337839NSERSPIA',
                'password'      => 'bithaddye'
            ],
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']
        ]);

        $tokenData = json_decode($response->getBody(), true);
        $cache->save('sunat_token', $tokenData, $tokenData['expires_in'] - 100);

        return $tokenData;
    }

    private function getApiResponse($url)
{
    $tokenData = $this->getToken();
    if (!isset($tokenData['access_token'])) {
        return [
            'status' => 404,
            'message' => 'No se pudo obtener token.',
            'data' => []
        ];
    }

    $token = $tokenData['access_token'];

    $client = \Config\Services::curlrequest();

    try {
        $response = $client->get($url, [
            'headers' => [
                'Authorization' => "Bearer $token",
                'Content-Type' => 'application/json;charset=utf-8',
                'Accept' => 'application/json, text/plain, */*',
                'Accept-Encoding' => 'gzip, deflate, br, zstd',
                'Accept-Language' => 'es-PE,es;q=0.9,en-US;q=0.8,en;q=0.7,es-419;q=0.6',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'Host' => 'api-cpe.sunat.gob.pe',
                'Origin' => 'https://e-factura.sunat.gob.pe',
                'Referer' => 'https://e-factura.sunat.gob.pe/',
                'sec-ch-ua' => '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"Windows"',
                'Sec-Fetch-Site' => 'same-site',
                'Sec-Fetch-Mode' => 'cors',
                'Sec-Fetch-Dest' => 'empty',
                // Los cookies se manejarán automáticamente si es necesario
            ],
            'timeout' => 30,
            'http_errors' => false,
            'allow_redirects' => true,
            'verify' => true, // Verificar SSL
        ]);

        // Obtener el cuerpo de la respuesta
        $body = $response->getBody();
        
        // Si la respuesta está comprimida con gzip, descomprimir
        $contentEncoding = $response->getHeaderLine('Content-Encoding');
        if (strpos($contentEncoding, 'gzip') !== false) {
            $body = gzdecode($body);
        }

        // Convertir JSON en objeto
        $data = json_decode($body);

        // Verificar que la respuesta sea válida
        if (json_last_error() !== JSON_ERROR_NONE) {
            return (object) [
                'status' => 500,
                'message' => 'Error al decodificar JSON: ' . json_last_error_msg(),
                'data' => (object) []
            ];
        }

        // Verificar el código de estado HTTP
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            return (object) [
                'status' => $statusCode,
                'message' => 'Error HTTP: ' . $statusCode,
                'data' => $data
            ];
        }

        // Verificar si existe la propiedad cntTotalReg o adaptar según la respuesta real
        if (isset($data->cntTotalReg)) {
            // Si no hay comprobantes, devolver un mensaje claro
            if ($data->cntTotalReg == 0) {
                return (object) [
                    'status' => 404,
                    'message' => 'No se encontraron comprobantes.',
                    'data' => (object) []
                ];
            }
        }

        // Enviar los datos correctamente formateados
        return (object) [
            'status' => 200,
            'message' => 'Datos obtenidos correctamente.',
            'data' => $data
        ];
    } catch (\Exception $e) {
        return (object) [
            'status' => 500,
            'message' => 'Error en la conexión: ' . $e->getMessage(),
            'data' => (object) []
        ];
    }
}

    private function getApiResponseSire($url)
    {
        $tokenData = $this->getToken();
        if (!isset($tokenData['access_token'])) {
            return [
                'status' => 404,
                'message' => 'No se pudo obtener token.',
                'data' => []
            ];
        }

        $token = $tokenData['access_token'];

        $client = \Config\Services::curlrequest();

        try {
            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => "Bearer $token",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'timeout' => 30
            ]);

            // Convertir JSON en objeto
            $data = json_decode($response->getBody());



            // Verificar que la respuesta sea válida
            if (!$data || !isset($data->paginacion->totalRegistros)) {
                return (object) [
                    'status' => 500,
                    'message' => 'Respuesta inválida del servicio externo.',
                    'data' => (object) []
                ];
            }

            // Si no hay comprobantes, devolver un mensaje claro
            if ($data->paginacion->totalRegistros == 0) {
                return (object) [
                    'status' => 404,
                    'message' => 'No se encontraron comprobantes.',
                    'data' => (object) []
                ];
            }

            // Enviar los datos correctamente formateados
            return (object) [
                'status' => 200,
                'message' => 'Datos obtenidos correctamente.',
                'data' => $data
            ];
        } catch (\Exception $e) {
            return (object) [
                'status' => 500,
                'message' => 'Error en la conexión: ' . $e->getMessage(),
                'data' => (object) []
            ];
        }
    }

    private function procesarRespuestaYGuardar($data)
    {
        // Verificar si 'comprobantes' existe y no está vacío
        if (!isset($data->data->comprobantes) || !is_array($data->data->comprobantes) || empty($data->data->comprobantes)) {
            return $this->respond(['status' => 400, 'message' => 'No se encontraron comprobantes en la respuesta.']);
        }

        // Obtener el primer comprobante
        $comprobante = $data->data->comprobantes[0];

        // Verificar si el RUC del receptor es el esperado
        if ((string)$comprobante->datosReceptor->numDocIdeRecep !== '20450337839') {
            return $this->respond(['status' => 403, 'message' => 'El RUC no corresponde a la empresa.']);
        }

        // Buscar el cliente en la base de datos
        $importFactModel = new ImportFactModel();
        $cliente = $importFactModel->getClient($comprobante->datosEmisor->numRuc);

        if (!$cliente) {
            return $this->respond(['status' => 404, 'message' => 'Cliente no encontrado.']);
        }

        // Calcular la fecha de vencimiento
        $fechaVenc = date('d/m/Y', strtotime($comprobante->fecEmision . " + {$cliente->CLI_AUTO1} days"));
        $valor = isset($comprobante->procedenciaMasiva->mtoImporteTotal) 
        ? $comprobante->procedenciaMasiva->mtoImporteTotal 
        : ($comprobante->procedenciaIndivual->mtoImporteTotal ?? null);
        // Preparar los datos de la factura
        $factura = [
            'CLI_CODCLI' => $cliente->CLI_CODCLIE,
            'RUC' => $comprobante->datosEmisor->numRuc,
            'NRO_FACTURA' => "{$comprobante->numSerie}-{$comprobante->numCpe}",
            'FECHA' => $comprobante->fecEmision,
            'VENCIMIENTO' => $fechaVenc,
            'ALL_NUMSER' => (int) preg_replace('/[^0-9]+/', '', $comprobante->numSerie),
            'ALL_NUMFACT' => $comprobante->numCpe,
            'TOTAL' => $valor,
            'ESTADO' => 0
        ];

        // Crear la factura en la base de datos
        $idImport = $importFactModel->crear_factura($factura);

        if ($idImport) {
            // Procesar los productos de la factura
            $i = 0; // Declarar $i fuera de la función array_map
            $productos = array_map(function ($item) use ($idImport, &$i) {
                $totalBruto = round($item->mtoValUnitario * $item->cntItems, 4);
                $totalNeto = round($item->mtoImpTotal, 4);
                $igv = $totalNeto - $totalBruto;
                $codigoProd = (!empty($item->desCodigo) && $item->desCodigo !== '-')
                    ? $item->desCodigo
                    : substr(md5(trim($item->desItem)), 0, 30);

                return [
                    'ID' => ++$i, // Incrementar $i antes de usarlo
                    'IDFACT' => $idImport,
                    'COD_PROD' => $codigoProd,
                    'DES_PROD' => substr(trim($item->desItem), 0, 99),
                    'CANTIDAD_INI' => (int) $item->cntItems,
                    'PRECIO' => $item->mtoValUnitario,
                    'TOTAL' => $totalBruto,
                    'TOTAL_SIST' => $totalNeto,
                    'TOTAL_IGV' => $igv,
                    'IGV' => $igv > 0 ? 1 : 0
                ];
            }, $comprobante->informacionItems);

            // Insertar los productos en la base de datos
            $importFactModel->crear_factura_detalle($productos);
        }

        return $this->respond(['status' => 200, 'message' => 'Comprobante importado con éxito.']);
    }

    public function getComprobanteHtml($ruc, $tipoDoc, $nroFactura, $tipoData = '2')
    {
        $tokenData = $this->getToken();
        if (!isset($tokenData['access_token'])) {
            return false;
        }

        $token = $tokenData['access_token'];
        $url = "https://api-cpe.sunat.gob.pe/v1/contribuyente/consultacpe/comprobantes/{$ruc}-{$tipoDoc}-{$nroFactura}-{$tipoData}";
        
        $client = \Config\Services::curlrequest();
        
        try {
            $response = $client->get($url, [
            'headers' => [
                'Authorization' => "Bearer $token",
                'Content-Type' => 'application/json;charset=utf-8',
                'Accept' => 'application/json, text/plain, */*',
                'Accept-Encoding' => 'gzip, deflate, br, zstd',
                'Accept-Language' => 'es-PE,es;q=0.9,en-US;q=0.8,en;q=0.7,es-419;q=0.6',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'Host' => 'api-cpe.sunat.gob.pe',
                'Origin' => 'https://e-factura.sunat.gob.pe',
                'Referer' => 'https://e-factura.sunat.gob.pe/',
                'sec-ch-ua' => '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"Windows"',
                'Sec-Fetch-Site' => 'same-site',
                'Sec-Fetch-Mode' => 'cors',
                'Sec-Fetch-Dest' => 'empty',
                // Los cookies se manejarán automáticamente si es necesario
            ],
            'timeout' => 30,
            'http_errors' => false,
            'allow_redirects' => true,
            'verify' => true, // Verificar SSL
        ]);
            
                // Obtener el cuerpo de la respuesta
        $body = $response->getBody();
        
        // Si la respuesta está comprimida con gzip, descomprimir
        $contentEncoding = $response->getHeaderLine('Content-Encoding');
        if (strpos($contentEncoding, 'gzip') !== false) {
            $body = gzdecode($body);
        }

        // Convertir JSON en objeto
        $data = json_decode($body);


            
            if (!$data || !isset($data->comprobantes) || empty($data->comprobantes)) {
                return false;
            }
            
            return $this->generarHtmlComprobante($data->comprobantes[0]);
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo HTML del comprobante: ' . $e->getMessage());
            return false;
        }
    }

    public function getDetalleComprobante($ruc, $tipoDoc, $serie, $numero, $rpta)
    {
        $url = "https://api-cpe.sunat.gob.pe/v1/contribuyente/consultacpe/comprobantes/$ruc-$tipoDoc-$serie-$numero-$rpta";
        $apiResponse = $this->getApiResponse($url);

        // Depuración: Verificar la respuesta de la API
        log_message('debug', 'Datos recibidos en getDetalleComprobante: ' . print_r($apiResponse, true));

        // Verificar si la respuesta es válida
        if (!isset($apiResponse->status) || $apiResponse->status != 200) {
            return $this->respond($apiResponse, $apiResponse->status ?? 400);
        }

        // Pasar la respuesta a procesarRespuestaYGuardar
        return $this->procesarRespuestaYGuardar($apiResponse);
    }

    public function getComprobantesPorFecha($codEstado, $codDocIde, $fecEmisionIni, $fecEmisionFin)
    {
        $url = "https://api-cpe.sunat.gob.pe/v1/contribuyente/consultacpe/comprobantes/-01-2?codEstado=$codEstado&codDocIde=$codDocIde&fecEmisionIni=$fecEmisionIni&fecEmisionFin=$fecEmisionFin";
        $apiResponse = $this->getApiResponse($url);

        // Depuración: Verificar la respuesta de la API
        log_message('debug', 'Datos recibidos en getComprobantesPorFecha: ' . print_r($apiResponse, true));

        // Verificar si la respuesta es válida
        if (!isset($apiResponse->status) || $apiResponse->status != 200) {
            return $this->response
                ->setStatusCode(400)
                ->setContentType('application/json')
                ->setJSON($apiResponse);
        }

        // Pasar la respuesta a crear_lista
        $importFactModel = new ImportFactModel();
        $importFactModel->crear_lista($apiResponse);

        return $this->response
            ->setStatusCode(200)
            ->setContentType('application/json')
            ->setJSON(['status' => 200, 'message' => 'Comprobantes importados con éxito.']);
    }

    public function getComprobantesPorFechaSire($codEstado, $codDocIde, $tipoDoc, $fecEmisionIni, $fecEmisionFin)
    {
        // Formatear las fechas para extraer el año y el mes
        $fecEmisionIniDate = DateTime::createFromFormat('d/m/Y', $fecEmisionIni);
        $fecEmisionFinDate = DateTime::createFromFormat('d/m/Y', $fecEmisionFin);

        if (!$fecEmisionIniDate || !$fecEmisionFinDate) {
            return $this->response
                ->setStatusCode(400)
                ->setContentType('application/json')
                ->setJSON(['status' => 400, 'message' => 'Formato de fecha inválido.']);
        }

        $yearMonth = $fecEmisionIniDate->format('Ym'); // Formato: AñoMes (ej: 202503)
        
        // Determinar codTipoOpe basado en el tipo de documento
        $codTipoOpe = ($tipoDoc === 'F7') ? '7' : '2'; // 7 para notas de crédito, 2 para facturas

        // Construir la nueva URL dinámicamente
        $url = "https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rce/propuesta/web/propuesta/{$yearMonth}/busqueda?codTipoOpe={$codTipoOpe}&fecEmisionIni={$fecEmisionIniDate->format('Y-m-d')}&fecEmisionFin={$fecEmisionFinDate->format('Y-m-d')}&page=1&perPage=100";

        $apiResponse = $this->getApiResponseSire($url);

        // Depuración: Verificar la respuesta de la API
        log_message('debug', 'Datos recibidos en getComprobantesPorFecha: ' . print_r($apiResponse, true));

        // Verificar si la respuesta es válida
        if (!isset($apiResponse->status) || $apiResponse->status != 200) {
            return $this->response
                ->setStatusCode(400)
                ->setContentType('application/json')
                ->setJSON($apiResponse);
        }

        // Pasar la respuesta a crear_lista
        $importFactModel = new ImportFactModel();
        $importFactModel->crear_lista_sire($apiResponse);

        return $this->response
            ->setStatusCode(200)
            ->setContentType('application/json')
            ->setJSON(['status' => 200, 'message' => 'Comprobantes importados con éxito.']);
    }

    public function testSunatAPI()
    {
        // Paso 1: Obtener el token
        $tokenData = $this->getToken();

        if (isset($tokenData['access_token'])) {
            $token = $tokenData['access_token'];
            echo "Token obtenido: {$token}<br>";

            // Paso 2: Solicitar la generación del reporte
            $ruc = '20100220700'; // Ruc
            $tipoDoc = '01'; // 1 = Factura o 03 Boleta
            $serie = 'F700'; // 2 = SErie de comprobante
            $numero = 323667; // Número de comprobante
            $rpta = 2; // Rpta json
            return $this->getDetalleComprobante_bkp($ruc, $tipoDoc, $serie, $numero, $rpta, $token);
            //var_export($documento);
        } else {
            echo "Error obteniendo el token: " . json_encode($tokenData);
        }
    }
    public function getDetalleComprobante_bkp($ruc, $tipoDoc, $serie, $numero, $rpta, $token)
    {
        // Construir la URL de la nueva API
        $url = "https://api-cpe.sunat.gob.pe/v1/contribuyente/consultacpe/comprobantes/$ruc-$tipoDoc-$serie-$numero-$rpta";
        //https://api-cpe.sunat.gob.pe/v1/contribuyente/consultacpe/comprobantes/-01-2?codEstado=0&codDocIde=6&fecEmisionIni=01/03/2025&fecEmisionFin=05/03/2025
        // Configuración del cURL
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token"
            ),
        ));

        $response = curl_exec($curl);
        $curlError = curl_error($curl); // Captura errores de cURL
        curl_close($curl);

        // Verifica si hubo un error en la conexión cURL
        if ($curlError) {
            return $this->response->setJSON([
                'status' => 500,
                'message' => 'Error en la conexión con el servicio externo: ' . $curlError
            ]);
        }

        $data = json_decode($response, false);

        // Verifica si la respuesta es válida
        if (!$data || !isset($data->cntTotalReg)) {
            return $this->response->setJSON([
                'status' => 500,
                'message' => 'Respuesta inválida del servicio externo.'
            ]);
        }

        // Verifica si se encontraron comprobantes
        if ($data->cntTotalReg == 0) {
            return $this->response->setJSON([
                'status' => 404,
                'message' => 'No se encontraron comprobantes.'
            ]);
        }

        // Obtener el primer comprobante de la lista
        $comprobante = $data->comprobantes[0];

        // Verificar si el RUC del receptor coincide con el esperado
        if ((string)$comprobante->datosReceptor->numDocIdeRecep != '20450337839') {
            return $this->response->setJSON([
                'status' => 403,
                'message' => 'El RUC no corresponde a la empresa.'
            ]);
        }

        // Procesar el comprobante
        $ImportFactModel = new ImportFactModel();
        $cliente = $ImportFactModel->getClient($comprobante->datosEmisor->numRuc);

        if ($cliente) {
            $fechaVenc = DateTime::createFromFormat('!d/m/Y', $comprobante->fecEmision)->getTimestamp();
            $fechaVenc = date('d/m/Y', strtotime(date('d-m-Y', $fechaVenc) . "+ {$cliente->CLI_AUTO1} days"));

            $factura = [
                'CLI_CODCLI' => $cliente->CLI_CODCLIE,
                'RUC' => $comprobante->datosReceptor->numDocIdeRecep,
                'NRO_FACTURA' => $comprobante->numSerie . "-" . $comprobante->numCpe,
                'FECHA' => $comprobante->fecEmision,
                'VENCIMIENTO' => $fechaVenc,
                'ALL_NUMSER' => intval(preg_replace('/[^0-9]+/', '', $comprobante->numSerie), 10),
                'ALL_NUMFACT' => $comprobante->numCpe,
                'TOTAL' => $comprobante->procedenciaMasiva->mtoImporteTotal,
                'ESTADO' => 0
            ];
            $IdImport = $ImportFactModel->crear_factura($factura);

            if ($IdImport) {
                $productos = [];
                foreach ($comprobante->informacionItems as $item) {
                    $totalBruto = round($item->mtoValUnitario * $item->cntItems, 4);
                    $totalNeto = round($item->mtoImpTotal, 4);
                    $igv = $totalNeto - $totalBruto;
                    $codigoProd = (!empty($item->desCodigo) && $item->desCodigo !== '-')
                        ? $item->desCodigo
                        : substr(md5(trim($item->desItem)), 0, 30);

                    $productos[] = [
                        'ID' => $item->desCodigo,
                        'IDFACT' => $IdImport,
                        'COD_PROD' => $codigoProd,
                        'DES_PROD' => substr(trim($item->desItem), 0, 99),
                        'CANTIDAD_INI' => intval($item->cntItems),
                        'PRECIO' => $item->mtoValUnitario,
                        'TOTAL' => $totalBruto,
                        'TOTAL_SIST' => $totalNeto,
                        'TOTAL_IGV' => $igv,
                        'IGV' => $igv > 0 ? 1 : 0
                    ];
                }
                $ImportFactModel->crear_factura_detalle($productos);
            }
        }

        return $this->response->setJSON([
            'status' => 200,
            'message' => 'Comprobante importado con éxito.'
        ]);
    }
    
    private function generarHtmlComprobante($comprobante)
    {
        $tipoDoc = $comprobante->codCpe === '01' ? 'FACTURA' : 'NOTA DE CRÉDITO';
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $tipoDoc . ' ' . $comprobante->numSerie . '-' . $comprobante->numCpe . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .company-info { border: 1px solid #000; padding: 10px; margin-bottom: 20px; }
        .doc-info { border: 1px solid #000; padding: 10px; margin-bottom: 20px; text-align: center; }
        .client-info { margin-bottom: 20px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .items-table th { background-color: #f0f0f0; }
        .totals { text-align: right; }
        .total-amount { font-weight: bold; font-size: 16px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . $tipoDoc . ' ELECTRÓNICA</h1>
    </div>
    
    <div class="company-info">
        <strong>EMISOR:</strong><br>
        RUC: ' . $comprobante->datosEmisor->numRuc . '<br>
        ' . $comprobante->datosEmisor->desRazonSocialEmis . '<br>
        ' . ($comprobante->datosEmisor->desNomComercialEmis ?? '') . '<br>
        ' . $comprobante->datosEmisor->desDirEmis . '<br>
    </div>
    
    <div class="doc-info">
        <strong>' . $comprobante->numSerie . '-' . str_pad($comprobante->numCpe, 8, '0', STR_PAD_LEFT) . '</strong><br>
        Fecha de Emisión: ' . $comprobante->fecEmision . '<br>
        Moneda: ' . $comprobante->codMoneda . '
    </div>
    
    <div class="client-info">
        <strong>CLIENTE:</strong><br>
        RUC: ' . $comprobante->datosReceptor->numDocIdeRecep . '<br>
        ' . $comprobante->datosReceptor->desRazonSocialRecep . '<br>
        ' . $comprobante->datosReceptor->dirDetCliente . '
    </div>
    
    <table class="items-table">
        <thead>
            <tr>
                <th>Cant.</th>
                <th>Unidad</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Valor Unit.</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($comprobante->informacionItems as $item) {
            $html .= '<tr>
                <td>' . number_format($item->cntItems, 2) . '</td>
                <td>' . $item->desUnidadMedida . '</td>
                <td>' . $item->desCodigo . '</td>
                <td>' . $item->desItem . '</td>
                <td>S/ ' . number_format($item->mtoValUnitario, 2) . '</td>
                <td>S/ ' . number_format($item->mtoImpTotal, 2) . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
    </table>
    
    <div class="totals">
        <p><strong>Total Exonerado: S/ ' . number_format($comprobante->procedenciaMasiva->mtoTotalValVentaExonerado, 2) . '</strong></p>
        <p><strong>IGV: S/ ' . number_format($comprobante->procedenciaMasiva->mtoSumIGV, 2) . '</strong></p>
        <p class="total-amount"><strong>TOTAL: S/ ' . number_format($comprobante->procedenciaMasiva->mtoImporteTotal, 2) . '</strong></p>
        <p><em>' . $comprobante->desMtoTotalLetras . '</em></p>
    </div>
    
</body>
</html>';
        
        return $html;
    }
}
