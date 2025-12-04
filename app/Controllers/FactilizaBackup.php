<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Models\ImportFactModel;
use DateTime;

class FactilizaBackup extends BaseController
{
    use ResponseTrait;
    protected $response;

    // Credenciales fijas para Factiliza
    private $factilizaConfig = [
        'api_url' => 'https://api.factiliza.com/v1/sunat/json',
        'bearer_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI1NTgiLCJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYvaWRlbnRpdHkvY2xhaW1zL3JvbGUiOiJjb25zdWx0b3IifQ.K8PwFsfNIpIl2ve0KJ2F08JZYLdGaBEx6_PvMRCm_Mw',
        'ruc' => '20450337839',
        'usuario' => 'NSERSPIA',
        'password' => 'bithaddye'
    ];

    public function __construct()
    {
        $this->response = service('response');
    }

    /**
     * Obtiene el detalle de un comprobante desde Factiliza
     * 
     * @param string $rucProveedor RUC del proveedor emisor
     * @param string $tipoDoc Tipo de documento (01=Factura, 07=Nota de Crédito)
     * @param string $serie Serie del comprobante
     * @param string $numero Número correlativo del comprobante
     * @return object Respuesta procesada
     */
    public function getDetalleComprobante($rucProveedor, $tipoDoc, $serie, $numero)
    {
        $client = \Config\Services::curlrequest();

        // Preparar el body de la petición
        $requestBody = [
            'ruc' => $this->factilizaConfig['ruc'],
            'usuario' => $this->factilizaConfig['usuario'],
            'password' => $this->factilizaConfig['password'],
            'proveedor' => $rucProveedor,
            'tipo_doc' => $tipoDoc,
            'serie' => $serie,
            'correlativo' => $numero
        ];

        try {
            $response = $client->post($this->factilizaConfig['api_url'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->factilizaConfig['bearer_token'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => $requestBody,
                'timeout' => 30,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $data = json_decode($body);

            // Verificar que la respuesta sea válida
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errorMsg = 'Error al decodificar JSON de Factiliza: ' . json_last_error_msg();
                log_message('error', $errorMsg);
                return (object) [
                    'status' => 500,
                    'message' => $errorMsg,
                    'data' => (object) []
                ];
            }

            // Verificar el código de estado HTTP
            if ($statusCode !== 200) {
                $errorMsg = "Error HTTP desde Factiliza: {$statusCode}";
                log_message('error', $errorMsg . " - " . ($data->message ?? ''));
                return (object) [
                    'status' => $statusCode,
                    'message' => $errorMsg . ' - ' . ($data->message ?? 'Sin mensaje de error'),
                    'data' => $data
                ];
            }

            // Verificar si la respuesta fue exitosa según Factiliza
            if (!isset($data->success) || $data->success !== true) {
                $errorMsg = $data->message ?? 'No se encontró el comprobante en Factiliza.';
                log_message('warning', "Factiliza: {$errorMsg}");
                return (object) [
                    'status' => 404,
                    'message' => $errorMsg,
                    'data' => (object) []
                ];
            }

            // Transformar la respuesta de Factiliza al formato SUNAT
            $transformedData = $this->transformarRespuestaFactiliza($data);

            // Procesar y guardar el comprobante
            return $this->procesarRespuestaYGuardar($transformedData);

        } catch (\Exception $e) {
            $errorMsg = 'Error en Factiliza API: ' . $e->getMessage();
            log_message('error', $errorMsg);
            return (object) [
                'status' => 500,
                'message' => 'Error en la conexión con Factiliza: ' . $e->getMessage(),
                'data' => (object) []
            ];
        }
    }

    /**
     * Transforma la respuesta de Factiliza al formato compatible con SUNAT
     * 
     * @param object $factilizaData Datos de Factiliza
     * @return object Datos en formato SUNAT
     */
    private function transformarRespuestaFactiliza($factilizaData)
    {
        $data = $factilizaData->data;

        // Convertir fecha de DD/MM/YYYY a formato estándar
        $fechaEmision = $data->fechaEmision;
        
        // Transformar al formato esperado por el sistema (similar a SUNAT)
        $transformedData = (object) [
            'status' => 200,
            'message' => 'Datos obtenidos correctamente desde Factiliza.',
            'data' => (object) [
                'cntTotalReg' => 1,
                'comprobantes' => [
                    (object) [
                        'codCpe' => $data->tipoComprobante,
                        'numSerie' => $data->serieComprobante,
                        'numCpe' => (string) $data->numeroComprobante,
                        'fecEmision' => $fechaEmision,
                        'codMoneda' => $data->codigoMoneda ?? 'PEN',
                        
                        // Datos del emisor (proveedor)
                        'datosEmisor' => (object) [
                            'numRuc' => $data->numeroRuc,
                            'desRazonSocialEmis' => $data->razonSocial,
                            'desNomComercialEmis' => $data->razonComercial ?? $data->razonSocial,
                            'desDirEmis' => $data->nombreCalle ?? ''
                        ],
                        
                        // Datos del receptor (nuestra empresa)
                        'datosReceptor' => (object) [
                            'numDocIdeRecep' => $data->numeroDocumentoCliente,
                            'desRazonSocialRecep' => $data->nombreCliente,
                            'dirDetCliente' => ''
                        ],
                        
                        // Totales
                        'procedenciaMasiva' => (object) [
                            'mtoImporteTotal' => $data->montoTotalGeneral,
                            'mtoTotalValVenta' => $data->totalValorVenta,
                            'mtoTotalValVentaExonerado' => $data->totalValorVentaExonerado ?? 0,
                            'mtoSumIGV' => $data->totalIGV ?? 0
                        ],
                        
                        'procedenciaIndivual' => (object) [
                            'mtoImporteTotal' => $data->montoTotalGeneral
                        ],
                        
                        'desMtoTotalLetras' => $data->montoTotalTexto ?? '',
                        
                        // Información de items/productos
                        'informacionItems' => $this->transformarItems($data->detalleComprobanteBean ?? [])
                    ]
                ]
            ]
        ];

        return $transformedData;
    }

    /**
     * Transforma los items de Factiliza al formato SUNAT
     * 
     * @param array $itemsFactiliza Items de Factiliza
     * @return array Items en formato SUNAT
     */
    private function transformarItems($itemsFactiliza)
    {
        $items = [];

        foreach ($itemsFactiliza as $item) {
            // Limpiar cantidad (puede venir como "2.00")
            $cantidad = floatval($item->cantidad ?? 0);
            
            // Limpiar precios
            $valorUnitario = floatval($item->valorVtaUnitario ?? 0);
            $precioUnitario = floatval($item->precioUnitario ?? 0);
            
            // Calcular total
            $mtoImpTotal = $cantidad * $precioUnitario;

            $items[] = (object) [
                'cntItems' => $cantidad,
                'desUnidadMedida' => $item->unidadMedidaDesc ?? 'UNIDAD',
                'desCodigo' => $item->codigoItem ?? '-',
                'desItem' => $item->descripcion ?? '',
                'mtoValUnitario' => $valorUnitario,
                'mtoImpTotal' => $mtoImpTotal
            ];
        }

        return $items;
    }

    /**
     * Procesa la respuesta transformada y guarda en la base de datos
     * Similar al método en SireSunat.php
     * 
     * @param object $data Datos transformados
     * @return array Respuesta JSON
     */
    private function procesarRespuestaYGuardar($data)
    {
        // Verificar si 'comprobantes' existe y no está vacío
        if (!isset($data->data->comprobantes) || !is_array($data->data->comprobantes) || empty($data->data->comprobantes)) {
            return (object) ['status' => 400, 'message' => 'No se encontraron comprobantes en la respuesta de Factiliza.'];
        }

        // Obtener el primer comprobante
        $comprobante = $data->data->comprobantes[0];

        // Verificar si el RUC del receptor es el esperado
        if ((string)$comprobante->datosReceptor->numDocIdeRecep !== '20450337839') {
            return (object) ['status' => 403, 'message' => 'El RUC no corresponde a la empresa.'];
        }

        // Buscar el cliente en la base de datos
        $importFactModel = new ImportFactModel();
        $cliente = $importFactModel->getClient($comprobante->datosEmisor->numRuc);

        if (!$cliente) {
            return (object) ['status' => 404, 'message' => 'Cliente no encontrado.'];
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

        // Verificar si el comprobante ya existe
        $db = \Config\Database::connect();
        $comprobanteExistente = $db->table('IMPORT_FACT')
            ->where('RUC', $factura['RUC'])
            ->where('NRO_FACTURA', $factura['NRO_FACTURA'])
            ->get()
            ->getRow();

        $esActualizacion = false;
        
        if ($comprobanteExistente) {
            // Si ya existe y tiene estado diferente de 10 (ya fue procesado), no permitir re-importar
            if ($comprobanteExistente->ESTADO != 10 && $comprobanteExistente->ESTADO != 0) {
                log_message('warning', "Comprobante {$factura['NRO_FACTURA']} ya fue importado y procesado (Estado: {$comprobanteExistente->ESTADO})");
                return (object) [
                    'status' => 409, 
                    'message' => 'El comprobante ya fue importado anteriormente y está en proceso o completado. No se puede re-importar.'
                ];
            }
            
            // Si existe con estado 10 o 0 (pendiente), eliminar los detalles antiguos
            log_message('info', "Comprobante {$factura['NRO_FACTURA']} ya existe (ID: {$comprobanteExistente->ID}), actualizando...");
            $db->table('IMPORT_FACT_DET')
                ->where('IDFACT', $comprobanteExistente->ID)
                ->delete();
            
            $idImport = $comprobanteExistente->ID;
            $esActualizacion = true;
            
            // Actualizar la factura existente
            $db->table('IMPORT_FACT')
                ->where('ID', $idImport)
                ->update($factura);
        } else {
            // Crear la factura en la base de datos
            $idImport = $importFactModel->crear_factura($factura);
        }

        if ($idImport) {
            // Procesar los productos de la factura
            $i = 0;
            $productos = array_map(function ($item) use ($idImport, &$i) {
                $totalBruto = round($item->mtoValUnitario * $item->cntItems, 4);
                $totalNeto = round($item->mtoImpTotal, 4);
                $igv = $totalNeto - $totalBruto;
                $codigoProd = (!empty($item->desCodigo) && $item->desCodigo !== '-')
                    ? $item->desCodigo
                    : substr(md5(trim($item->desItem)), 0, 30);

                return [
                    'ID' => ++$i,
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

        $mensaje = $esActualizacion 
            ? 'Comprobante actualizado exitosamente desde Factiliza (servicio de respaldo).' 
            : 'Comprobante importado con éxito desde Factiliza (servicio de respaldo).';
        
        return (object) ['status' => 200, 'message' => $mensaje];
    }
    
    /**
     * Obtiene los datos del comprobante desde Factiliza SIN guardar en BD
     * Solo para visualización
     * 
     * @param string $rucProveedor RUC del proveedor emisor
     * @param string $tipoDoc Tipo de documento
     * @param string $serie Serie del comprobante
     * @param string $numero Número correlativo
     * @return object|null Datos del comprobante o null si falla
     */
    public function getComprobanteSoloVista($rucProveedor, $tipoDoc, $serie, $numero)
    {
        $client = \Config\Services::curlrequest();

        $requestBody = [
            'ruc' => $this->factilizaConfig['ruc'],
            'usuario' => $this->factilizaConfig['usuario'],
            'password' => $this->factilizaConfig['password'],
            'proveedor' => $rucProveedor,
            'tipo_doc' => $tipoDoc,
            'serie' => $serie,
            'correlativo' => $numero
        ];

        try {
            $response = $client->post($this->factilizaConfig['api_url'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->factilizaConfig['bearer_token'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => $requestBody,
                'timeout' => 30,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $data = json_decode($body);

            if (json_last_error() !== JSON_ERROR_NONE || $statusCode !== 200) {
                return null;
            }

            if (!isset($data->success) || $data->success !== true) {
                return null;
            }

            // Retornar los datos directamente sin guardar
            return $data->data;

        } catch (\Exception $e) {
            log_message('error', 'Error al obtener comprobante desde Factiliza (solo vista): ' . $e->getMessage());
            return null;
        }
    }
}
