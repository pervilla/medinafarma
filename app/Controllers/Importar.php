<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use App\Controllers\SireSunat;
use XmlReader;
use App\Models\ImportFactModel;
use DateTime;
use ZipArchive; // Importa la clase ZipArchive
use App\Libraries\SimpleHtmlDom;
class Importar extends BaseController
{
    public function index()
    {
        $data['menu']['p'] = 30;
        $data['menu']['i'] = 35;
        return view('importar/index', $data);
    }

    
    public function listaComprasSire()
    {
        // Obtener los datos enviados por POST
        $codEstado = $this->request->getVar('codEstado');
        $codDocIde = $this->request->getVar('codDocIde');
        $tipoDoc = $this->request->getVar('tipoDoc');
        $fecEmisionIni = $this->request->getVar('fecEmisionIni');
        $fecEmisionFin = $this->request->getVar('fecEmisionFin');

        // Validar que los datos no estén vacíos
        if (
            !isset($codEstado) || !isset($codDocIde) || !isset($tipoDoc) || !isset($fecEmisionIni) || !isset($fecEmisionFin) ||
            $codEstado === '' || $codDocIde === '' || $tipoDoc === '' || $fecEmisionIni === '' || $fecEmisionFin === ''
        ) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Todos los campos son obligatorios.'
            ]);
        }
        $SireSunat = new SireSunat();
        // Llamar al método getComprobantesPorFecha
        return $SireSunat->getComprobantesPorFechaSire($codEstado, $codDocIde, $tipoDoc, $fecEmisionIni, $fecEmisionFin);
    }
    public function importaComprobanteSunat()
    {
        // Obtener los datos enviados por POST
        $ruc = $this->request->getVar('ruc');
        $nro = $this->request->getVar('nro');
        $cod = $this->request->getVar('cod');
        $rpta = 2;
        $cod = $cod=='07'?'F7':$cod;
        
        // Validar que los datos no estén vacíos
        if ($ruc === '' || $nro === '' || $cod === '') {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Todos los campos son obligatorios.'
            ]);
        }

        $doc = explode("-", $nro);
        if (count($doc) < 2) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Formato incorrecto en el número de comprobante.'
            ]);
        }
        
        $serie = $doc[0]; 
        $numero = $doc[1];
        
        // PASO 1: Intentar primero con SUNAT
        $SireSunat = new SireSunat();
        $resultado = $SireSunat->getDetalleComprobante($ruc, $cod, $serie, $numero, $rpta);
        
        // Verificar si SUNAT respondió exitosamente
        // El método respond() devuelve un objeto Response, necesitamos verificar el status code
        if (is_object($resultado) && method_exists($resultado, 'getStatusCode')) {
            $statusCode = $resultado->getStatusCode();
            if ($statusCode == 200) {
                log_message('info', "Comprobante {$nro} importado exitosamente desde SUNAT");
                return $resultado;
            }
        }
        
        // PASO 2: Si SUNAT falló, intentar con Factiliza (respaldo)
        log_message('warning', "SUNAT falló para comprobante {$nro}. Intentando con Factiliza (respaldo)...");
        
        try {
            $FactilizaBackup = new \App\Controllers\FactilizaBackup();
            $resultadoFactiliza = $FactilizaBackup->getDetalleComprobante($ruc, $cod, $serie, $numero);
            
            // Verificar si Factiliza respondió exitosamente
            $resultadoFactilizaArray = json_decode(json_encode($resultadoFactiliza), true);
            
            if (isset($resultadoFactilizaArray['status']) && $resultadoFactilizaArray['status'] == 200) {
                log_message('info', "Comprobante {$nro} importado exitosamente desde Factiliza (respaldo)");
                return $this->response->setStatusCode(200)->setJSON($resultadoFactilizaArray);
            }
            
            // Si Factiliza también falló, devolver el error
            log_message('error', "Factiliza también falló para comprobante {$nro}");
            return $this->response->setJSON([
                'status' => 500,
                'message' => 'No se pudo importar el comprobante. SUNAT y Factiliza fallaron. Error: ' . ($resultadoFactilizaArray['message'] ?? 'Error desconocido')
            ]);
            
        } catch (\Exception $e) {
            log_message('error', "Error al intentar con Factiliza para {$nro}: " . $e->getMessage());
            return $this->response->setJSON([
                'status' => 500,
                'message' => 'No se pudo importar el comprobante. SUNAT falló y hubo un error al intentar con Factiliza: ' . $e->getMessage()
            ]);
        }
    }
    public function listarDocumentos()
    {
        $cliente = $this->request->getVar('cliente');
        $startDate = $this->request->getVar('startDate');
        $endDate = $this->request->getVar('endDate');
        $ImportFactModel = new ImportFactModel();
        $listarDocumentos = $ImportFactModel->listarDocumentos($cliente, $startDate, $endDate);
        return $this->response->setJSON($listarDocumentos);
    }
    public function listarDetalleDocumentos()
    {
        $id = $this->request->getVar('id');
        $ImportFactModel = new ImportFactModel();
        $listarDocumentos = $ImportFactModel->listarDetalleDocumentos($id);
        return $this->response->setJSON($listarDocumentos);
    }

    /**
     * Verifica si un comprobante pendiente ya existe en el sistema (ingreso manual)
     * y lo marca como procesado (ESTADO=1) si se encuentra.
     */
    public function verificar_ingresos_manuales()
    {
        $ids = $this->request->getVar('ids'); // Opcional: lista de IDs específicos
        $ImportFactModel = new ImportFactModel();
        
        $db = \Config\Database::connect();
        $builder = $db->table('IMPORT_FACT');
        $builder->where('ESTADO', 0); // Solo pendientes
        
        if (!empty($ids)) {
            $builder->whereIn('ID', $ids);
        }
        
        $pendientes = $builder->get()->getResult();
        $actualizados = 0;
        $detalles = [];

        foreach ($pendientes as $fact) {
            if ($ImportFactModel->check_comprobante_existente($fact->RUC, $fact->NRO_FACTURA)) {
                $ImportFactModel->actualizarEstadoProcesado($fact->ID);
                $actualizados++;
                $detalles[] = $fact->NRO_FACTURA;
            }
        }

        return $this->response->setJSON([
            'status' => 200,
            'actualizados' => $actualizados,
            'mensaje' => $actualizados > 0 
                ? "Se sincronizaron $actualizados facturas que ya estaban ingresadas manualmente."
                : "No se encontraron nuevos ingresos manuales para los comprobantes pendientes.",
            'detalles' => $detalles
        ]);
    }
    public function update_producto()
    {
        // Get and validate input
        $cli = trim($this->request->getVar('cli'));
        $cod = trim($this->request->getVar('cod'));
        $art = trim($this->request->getVar('art'));
    
        if (empty($cli) || empty($cod) || empty($art)) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Todos los campos son obligatorios.'
            ]);
        }
    
        try {
            $productos = [
                'CLI_CODCLIE' => $cli,
                'COD_PROD' => $cod,
                'ART_KEY' => $art
            ];
    
            $ImportFactModel = new ImportFactModel();
            $result = $ImportFactModel->update_producto($productos);
            
            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Producto actualizado correctamente',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error updating product: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 500,
                'message' => 'Error al actualizar el producto: ' . $e->getMessage()
            ]);
        }
    }
    public function actualizaProd()
    {
        $id = $this->request->getVar('id');
        $idfact = $this->request->getVar('idfact');
        $cantidad = $this->request->getVar('cantidad');
        $precio = round($this->request->getVar('total') / $cantidad, 4);
        $ImportFactModel = new ImportFactModel();
        $actualizaProduc = $ImportFactModel->actualiza_item_fact($idfact, $id, $precio);
        return $actualizaProduc;
    }
    public function desc_promocion()
    {
        $id1 = $this->request->getVar('ids');
        $id2 = $this->request->getVar('idcmbb');
        $idfact = $this->request->getVar('idfact');
        $cant = $this->request->getVar('canti');
        $ImportFactModel = new ImportFactModel();
        $actualizaProduc = $ImportFactModel->desc_promocion($idfact, $id1, $id2, $cant);
        return $actualizaProduc;
    }
    public function crea_compra()
    {
        $codclie = $this->request->getVar('codclie');
        $idfact = $this->request->getVar('idfact');
        $ImportFactModel = new ImportFactModel();

        // 1. Obtener datos del comprobante para validación de duplicados
        $db = \Config\Database::connect();
        $comp = $db->table('IMPORT_FACT')->where('ID', $idfact)->get()->getRow();
        if (!$comp) {
            return $this->response->setJSON(['status' => 400, 'message' => 'Comprobante no encontrado']);
        }

        // VALIDACIÓN 1: No ingresar si ya existe el comprobante
        if ($ImportFactModel->check_comprobante_existente($comp->RUC, $comp->NRO_FACTURA)) {
            return $this->response->setJSON(['status' => 400, 'message' => 'El comprobante ' . $comp->NRO_FACTURA . ' ya ha sido ingresado previamente al sistema.']);
        }

        // VALIDACIÓN 2: No ingresar si existe productos con ART_SITUACION=1
        $inactivos = $ImportFactModel->verificar_productos_inactivos($idfact);
        if (!empty($inactivos)) {
            $nombres = array_column($inactivos, 'DES_PROD');
            return $this->response->setJSON([
                'status' => 400, 
                'message' => 'No se puede ingresar la compra porque contiene productos desactivados: ' . implode(', ', $nombres)
            ]);
        }

        // VALIDACIÓN 3: No ingresar si la unidad del producto ya no existe (en PRECIOS)
        $unidadesInvalidas = $ImportFactModel->verificar_unidades_validas($idfact);
        if (!empty($unidadesInvalidas)) {
            $nombres = array_column($unidadesInvalidas, 'DES_PROD');
            return $this->response->setJSON([
                'status' => 400, 
                'message' => 'No se puede ingresar la compra porque hay productos con unidades/equivalencias no válidas en el sistema: ' . implode(', ', $nombres)
            ]);
        }

        $result = $ImportFactModel->crea_compra($idfact, $codclie);
        log_message('error', 'Resultado de crea_compra: ' . print_r($result, true));
        if ($result && isset($result[0])) {
            $msgResult = (array)$result[0];
            $mensaje = reset($msgResult); // Obtener el primer valor de la primera fila
            
            if (is_numeric($mensaje)) {
                $mensaje_html = "La compra ha sido procesada correctamente.<br><br>";
                $mensaje_html .= "<b>Guía Interna:</b> <span class='badge badge-success' style='font-size: 1rem;'>1-" . $mensaje . "</span><br>";
                $mensaje_html .= "<b>Proveedor:</b> " . ($comp->desRazonSocialEmis ?? 'N/A') . "<br>";
                $mensaje_html .= "<b>Documento:</b> " . ($comp->NRO_FACTURA ?? 'N/A') . "<br>";
                $mensaje_html .= "<b>Total:</b> S/ " . number_format($comp->TOTAL ?? 0, 2);
                
                return $this->response->setJSON(['status' => 200, 'message' => $mensaje_html]);
            }
            
            return $this->response->setJSON(['status' => 200, 'message' => $mensaje]);
        }
        return $this->response->setJSON(['status' => 500, 'message' => 'Error inesperado al procesar la compra']);
    }
    public function cambiar_monto()
    {
        $idfact = $this->request->getVar('idfact');
        $total = $this->request->getVar('total');
        $ImportFactModel = new ImportFactModel();
        $crearcompra = $ImportFactModel->actualizar_total($idfact, $total);
    }
    public function agregar_flete()
    {
        $idfact = $this->request->getVar('idfact');
        $vflete = $this->request->getVar('vflete');
        $ImportFactModel = new ImportFactModel();
        $agregarflete = $ImportFactModel->distribuir_monto($idfact, $vflete);
        return $this->response->setJSON($agregarflete);
    }
    public function promediar_costos()
    {
        $idscmb = implode(",", $this->request->getVar('ids'));
        $idfact = $this->request->getVar('idfact');
        $ImportFactModel = new ImportFactModel();
        $prom = $ImportFactModel->promediar_costos($idfact, $idscmb);
        $newp = $prom->TOTAL / $prom->CANTIDAD;
        $actualizaProduc = $ImportFactModel->actualiza_item_fact($idfact, $idscmb, $newp);
        return $actualizaProduc;
    }
    public function eliminar_items()
    {
        $idscmb = implode(",", $this->request->getVar('ids'));
        $idfact = $this->request->getVar('idfact');
        $ImportFactModel = new ImportFactModel();
        $actualizaProduc = $ImportFactModel->eliminar_items($idfact, $idscmb);
        return $actualizaProduc;
    }

    public function eliminar_compra()
    {
        $id = $this->request->getVar('id');
        $ImportFactModel = new ImportFactModel();
        
        // Obtener el comprobante para ver su estado
        $db = \Config\Database::connect();
        $comp = $db->table('IMPORT_FACT')->where('ID', $id)->get()->getRow();
        
        if (!$comp) {
            return $this->response->setJSON(['status' => 400, 'message' => 'Comprobante no encontrado']);
        }

        if ($comp->ESTADO == 1) {
            // Caso complejo: Anulaciones (Punto 3)
            return $this->response->setJSON(['status' => 300, 'message' => 'El comprobante ya fue ingresado (ESTADO=1). Se requiere anular el ingreso.']);
        }

        $result = $ImportFactModel->eliminar_items_import($id);
        
        if ($result) {
            return $this->response->setJSON(['status' => 200, 'message' => 'Comprobante restablecido correctamente']);
        } else {
            return $this->response->setJSON(['status' => 500, 'message' => 'Error al eliminar el contenido']);
        }
    }


    public function excluir_productos()
    {
        $idscmb = implode(",", $this->request->getVar('ids'));
        $idfact = $this->request->getVar('idfact');
        $ImportFactModel = new ImportFactModel();
        $prom = $ImportFactModel->excluir_productos($idfact, $idscmb);
        return $prom;
    }
    public function actualizar_equiv()
    {
        $codclie = $this->request->getVar('codclie');
        $artkey = $this->request->getVar('artkey');
        $equiv = $this->request->getVar('equiv');
        $factr = $this->request->getVar('factr');
        $ImportFactModel = new ImportFactModel();
        $actualizaProduc = $ImportFactModel->actualiza_item_art($codclie, $artkey, $equiv, $factr);
        return $actualizaProduc;
    }
    
    public function procesarReglas()
    {
        $idfact = $this->request->getVar('idfact');
        if (!$idfact) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID de factura no proporcionado']);
        }

        $ImportFactModel = new ImportFactModel();
        $result = $ImportFactModel->aplicarReglasExtraccion($idfact);

        return $this->response->setJSON($result);
    }
    
    public function procesarFactura()
    {
        if (!isset($_FILES['factura_html']) || $_FILES['factura_html']['error'] !== UPLOAD_ERR_OK) {
            return "Error al subir el archivo.";
        }

        // Cargar el contenido del HTML
        $htmlContent = file_get_contents($_FILES['factura_html']['tmp_name']);

        // Crear un objeto Simple HTML DOM
        //$html = str_get_html($htmlContent);
        $simpleHtmlDom = new SimpleHtmlDom();
        $html = $simpleHtmlDom->strGetHtml($htmlContent);

        if (!$html) {
            return "Error al procesar el HTML.";
        }

        // Extraer información de la factura
        $dataFacturaCompleta = $this->extraerDatosFactura($html);
        $detallesProductos = $this->extraerDetallesProductos($html);

        // Verificar si el número sigue vacío
        if (empty($dataFacturaCompleta['numero'])) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Error: No se encontró el número de factura en el HTML.'
            ]);
        }
        $dataFacturaCompleta['numero'] = preg_replace('/-0+/', '-', $dataFacturaCompleta['numero']);

        // Crear objeto de modelo
        $ImportFactModel = new ImportFactModel();
        $cliente = $ImportFactModel->getClient($dataFacturaCompleta['ruc_emisor'] ?? null);

        if ($cliente) {
            $fechaVenc = DateTime::createFromFormat('!Y-m-d', $dataFacturaCompleta['fecha_emision'] ?? date('Y-m-d'))->getTimestamp();
            $fechaVenc = date('d/m/Y', strtotime(date('d-m-Y', $fechaVenc) . "+ {$cliente->CLI_AUTO1} days"));
            $dataSerNum = explode("-", $dataFacturaCompleta['numero'] ?? '');
            // ✅ Conversión de tipos numéricos
            $factura = [
                'CLI_CODCLI' => $cliente->CLI_CODCLIE,
                'RUC' => $dataFacturaCompleta['ruc_emisor'] ?? '',
                'NRO_FACTURA' => ($dataFacturaCompleta['numero'] ?? ''),
                'FECHA' => $dataFacturaCompleta['fecha_emision'] ?? '',
                'VENCIMIENTO' => $fechaVenc,
                'ALL_NUMSER' => intval(preg_replace('/[^0-9]+/', '', $dataSerNum[0] ?? '')), // Solo números
                'ALL_NUMFACT' => intval($dataSerNum[1] ?? ''), // Asegurar número
                'TOTAL' => floatval(array_sum(array_column($detallesProductos, 'valor_unitario'))), // Convertir a decimal,
                'ESTADO' => 0
            ];

            // Insertar factura en la base de datos
            $IdImport = $ImportFactModel->crear_factura($factura);

            if ($IdImport) {
                $productos = [];
                $nuevoId = 1;
                foreach ($detallesProductos as $fact) {
                    $totalBruto = round($fact['valor_unitario'] * $fact['cantidad'], 4);
                    $totalNeto = round($fact['precio_unitario'] * $fact['cantidad'], 4);
                    $igv = $totalNeto - $totalBruto;
                    $codigoProd = (!empty($fact['codigo']) && $fact['codigo'] !== '-')
                        ? $fact['codigo']
                        : substr(md5(trim($fact['descripcion'])), 0, 30);

                    $productos[] = [
                        'ID' => $nuevoId++,
                        'IDFACT' => $IdImport,
                        'COD_PROD' => $codigoProd,
                        'DES_PROD' => substr(trim($fact['descripcion']), 0, 99),
                        'CANTIDAD_INI' => intval($fact['cantidad']),
                        'PRECIO' => floatval($fact['precio_unitario']), // Convertir a decimal
                        'TOTAL' => $fact['valor_unitario'] == 0 ? 0 : $totalNeto,
                        'TOTAL_SIST' => $fact['valor_unitario'] == 0 ? 0 : $totalNeto,
                        'TOTAL_IGV' => $igv,
                        'IGV' => $igv > 0 ? 1 : 0
                    ];
                }

                $ImportFactModel->crear_factura_detalle($productos);
            }
        }

        // Respuesta JSON
        return $this->response->setJSON([
            'status' => 200,
            'message' => 'Factura importada con éxito.'
        ]);
    }

    // Método para normalizar el texto y eliminar caracteres especiales
    private function normalizeText($text)
    {
        $text = trim($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // Decodificar entidades HTML
        $text = mb_strtoupper($text, 'UTF-8'); // Convertir a mayúsculas
        $text = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'], ['A', 'E', 'I', 'O', 'U', 'U'], $text); // Quitar tildes
        $text = preg_replace('/\s+/', '-', $text); // ✅ Reemplazar espacios múltiples por un guion

        return preg_replace('/[^A-Z0-9-]/', '', $text); // ✅ Mantener letras, números y guiones
    }



    private function extraerDatosFactura($html)
    {
        $datos = [];

        // Buscar todas las filas <tr> dentro de la tabla "comprobante"
        $filas = $html->find('table.comprobante tr');

        foreach ($filas as $fila) {
            $tablaInterna = $fila->find('table', 0);
            if ($tablaInterna) {
                // Verificar si la tabla interna pertenece a los datos de la factura o del emisor
                $esDatosFactura = strpos($fila->plaintext, 'TIPO DE COMPROBANTE') !== false;
                $esDatosEmisor = strpos($fila->plaintext, 'RUC') !== false;

                if ($esDatosFactura) {
                    // Extraer datos de la factura
                    foreach ($tablaInterna->find('tr') as $subfila) {
                        $celdas = $subfila->find('td');
                        if (count($celdas) >= 5) {
                            // Extraer datos con clave-valor en una misma fila
                            $clave1 = $this->normalizeText(trim(str_replace(':', '', $celdas[0]->plaintext)));
                            $valor1 = trim(str_replace(':', '', $celdas[1]->plaintext));
                            $clave2 = $this->normalizeText(trim(str_replace(':', '', $celdas[3]->plaintext)));
                            $valor2 = trim(str_replace(':', '', $celdas[4]->plaintext));

                            if ($clave1 === 'TIPO-DE-COMPROBANTE') $datos['tipo_comprobante'] = $valor1;
                            if ($clave1 === 'FECHA-DE-EMISION') $datos['fecha_emision'] = $valor1;
                            if ($clave2 === 'NUMERO') $datos['numero'] = $valor2;
                            if ($clave2 === 'MONEDA') $datos['moneda'] = $valor2;
                        }
                    }
                }

                if ($esDatosEmisor) {
                    // Extraer datos del emisor
                    foreach ($tablaInterna->find('tr') as $subfila) {
                        $celdas = $subfila->find('td');
                        if (count($celdas) >= 2) {
                            $clave = $this->normalizeText(trim(str_replace(':', '', $celdas[0]->plaintext)));
                            $valor = trim(str_replace(':', '', $celdas[1]->plaintext));

                            if ($clave === 'RUC') $datos['ruc_emisor'] = $valor;
                            if ($clave === 'RAZON-SOCIAL') $datos['razon_social_emisor'] = $valor;
                        }
                    }
                }
            }
        }

        return $datos;
    }

    private function extraerDetallesProductos($html)
    {
        $productos = [];

        // Buscar la tabla de productos dentro del HTML
        $tablaProductos = $html->find('table.form-table', 1);
        if (!$tablaProductos) {
            echo "No se encontró la tabla de productos.<br>";
            return $productos;
        }

        // Procesar las filas de productos
        foreach ($tablaProductos->find('tr') as $i => $fila) {
            if ($i === 0) continue; // Omitir cabecera

            $celdas = $fila->find('td');
            if (count($celdas) >= 8) {
                $productos[] = [
                    'cantidad' => trim($celdas[0]->plaintext),
                    'unidad' => trim($celdas[1]->plaintext),
                    'codigo' => trim($celdas[2]->plaintext),
                    'descripcion' => trim($celdas[3]->plaintext),
                    'valor_unitario' => trim($celdas[4]->plaintext),
                    'precio_unitario' => trim($celdas[5]->plaintext),
                    'valor_venta' => trim($celdas[6]->plaintext),
                    'icbper' => trim($celdas[7]->plaintext),
                ];
            }
        }

        return $productos;
    }



    public function xml_read()
    {
        $file = $this->request->getVar('files') ? $this->request->getVar('files') : '20397180817-01-F002-167399.xml';
        // Open the XML file
        $patron = 'xmlns:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2 ..\xsd\maindoc\UBLPE-Invoice-2.1.xsd"';
        $str = implode("\n", file('facturas/' . $file));
        $fp = fopen('facturas/' . $file, 'w');
        $str = str_replace($patron, " ", $str);
        fwrite($fp, $str, strlen($str));
        fclose($fp);

        // Open the XML file
        $reader = new XMLReader();
        $reader->open('facturas/' . $file);
        $i = 0;
        $documento = array();

        // Iterate through the XML until we reach a <Product> node
        while ($reader->read()) {

            if ($reader->nodeType == XMLReader::ELEMENT && $reader->name === 'cbc:ID' && $i == 0) {
                $documento['nro_factura'] = $reader->readString();
                $i++;
            }
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->name === 'cbc:IssueDate') {
                $documento['fecha_factura'] = $reader->readString();
            }
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->name === 'cbc:DueDate') {
                $documento['vencimiento_factura'] = $reader->readString();
            }
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->name === 'cac:LegalMonetaryTotal') {
                $innerXML = $reader->readOuterXml();
                $innerReader = new XMLReader();
                $innerReader->xml($innerXML);
                $dataExtract = array('cbc:LineExtensionAmount', 'cbc:PayableAmount');
                while ($innerReader->read()) {
                    if ($innerReader->nodeType == XMLReader::ELEMENT) {
                        $innerNodeName = $innerReader->name;
                        $innerNodeValue = $innerReader->readString();
                        if (in_array($innerNodeName, $dataExtract)) {
                            $documento[$innerNodeName] = $innerNodeValue;
                        }
                    }
                }
                // Close the inner XMLReader
                $innerReader->close();
            }
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->name === 'cac:AccountingSupplierParty') {
                $innerXML = $reader->readOuterXml();
                $innerReader = new XMLReader();
                $innerReader->xml($innerXML);
                $dataExtract = array('cbc:ID');
                while ($innerReader->read()) {
                    if ($innerReader->nodeType == XMLReader::ELEMENT) {
                        $innerNodeName = $innerReader->name;
                        $innerNodeValue = $innerReader->readString();
                        if (in_array($innerNodeName, $dataExtract) && strlen($innerNodeValue) == 11) {
                            $documento['ruc_factura'] = $innerNodeValue;
                        }
                    }
                }
                // Close the inner XMLReader
                $innerReader->close();
            }
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->name === 'cac:InvoiceLine') {
                $innerXML = $reader->readOuterXml();
                $innerReader = new XMLReader();
                $innerReader->xml($innerXML);
                $dataExtract = array('cbc:ID', 'cbc:InvoicedQuantity', 'cbc:Description', 'cbc:PriceAmount', 'cbc:TaxAmount', 'cbc:LineExtensionAmount');
                $productData = [];
                $i = 0;
                while ($innerReader->read()) {
                    if ($innerReader->nodeType == XMLReader::ELEMENT) {
                        $innerNodeName = $innerReader->name;
                        $innerNodeValue = $innerReader->readString();
                        if (in_array($innerNodeName, $dataExtract)) {
                            $productData[$innerNodeName] = $innerNodeValue;
                            if ($innerNodeName == 'cbc:ID' && $i == 0) {
                                $productData['cbc:IDRow'] = $innerNodeValue;
                                $i++;
                            }
                        }
                    }
                }
                $batchData[] = $productData;
                // Close the inner XMLReader
                $innerReader->close();
            }
        }
        $reader->close();

        $dir    = 'facturas';
        $data['files'] = scandir($dir, SCANDIR_SORT_DESCENDING);
        $data['fls'] = $file;
        $data['factura'] = $batchData;
        $data['factura2'] = $documento;


        $ImportFactModel = new ImportFactModel();
        $cliente = $ImportFactModel->getClient($documento['ruc_factura']);
        $data['cliente'] = $cliente;

        if ($cliente) {
            $factura = array(
                'CLI_CODCLI' => $cliente->CLI_CODCLIE,
                'RUC' => $documento['ruc_factura'],
                'NRO_FACTURA' => $documento['nro_factura'],
                'FECHA' => $documento['fecha_factura'],
                'VENCIMIENTO' => $documento['vencimiento_factura'],
                'TOT_FACT' => $documento['cbc:LineExtensionAmount'],
                'TOT_FLET' => 0,
                'TOTAL' => $documento['cbc:PayableAmount']
            );
            $IdImport = $ImportFactModel->crear_factura($factura);
        }

        if ($IdImport) {
            $productos = array();
            foreach ($batchData as $fact) {
                $productos[] = array(
                    'ID' => $fact['cbc:IDRow'],
                    'IDFACT' => $IdImport,
                    'COD_PROD' => $fact['cbc:ID'],
                    'CANTIDAD' => intval($fact['cbc:InvoicedQuantity']),
                    'PRECIO' => $fact['cbc:PriceAmount'],
                    'TOTAL' => $fact['cbc:LineExtensionAmount'],
                    'TOTAL_SIST' => $fact['cbc:LineExtensionAmount'],
                    'IGV' => 0,
                    'PERCEPCION' => 0,
                    'FLETE' => 0
                );
            }
            $ImportFactModel->crear_factura_detalle($productos);
        }
        return view('importar/index', $data);
    }

    public function visorComprobante()
    {
        $ruc = $this->request->getVar('ruc');
        $tipoDoc = $this->request->getVar('tipoDoc');
        $nroFactura = $this->request->getVar('nroFactura');
        $tipoData = $this->request->getVar('tipoData') ?? '2';
        $tipoDoc = $tipoDoc=='07'?'F7':$tipoDoc;
        
        if (!$ruc || !$tipoDoc || !$nroFactura) {
            return $this->response->setStatusCode(400)->setBody('Parámetros faltantes');
        }
        
        // PASO 1: Intentar obtener HTML desde SUNAT
        $SireSunat = new SireSunat();
        $html = $SireSunat->getComprobanteHtml($ruc, $tipoDoc, $nroFactura, $tipoData);
        
        if ($html) {
            // Agregar estilos para impresión
            $printStyles = '
            <style>
                @media print {
                    body { font-size: 12px; }
                    .no-print { display: none !important; }
                    table { page-break-inside: avoid; }
                }
                .print-btn {
                    position: fixed;
                    top: 10px;
                    right: 10px;
                    z-index: 1000;
                    padding: 10px 20px;
                    background: #007bff;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                }
            </style>
            <button class="print-btn no-print" onclick="window.print()">Imprimir</button>
            ';
            
            $html = str_replace('</head>', $printStyles . '</head>', $html);
            
            return $this->response->setHeader('Content-Type', 'text/html')->setBody($html);
        }
        
        // PASO 2: Si SUNAT falló, intentar con Factiliza
        log_message('warning', "SUNAT no devolvió HTML para {$nroFactura}. Intentando con Factiliza...");
        
        try {
            // Separar serie y número
            $parts = explode('-', $nroFactura);
            if (count($parts) < 2) {
                return $this->response->setStatusCode(400)->setBody('Formato de factura inválido');
            }
            
            $serie = $parts[0];
            $numero = $parts[1];
            
            // Obtener datos desde Factiliza (SOLO VISTA, sin guardar)
            $FactilizaBackup = new \App\Controllers\FactilizaBackup();
            $datosFactiliza = $FactilizaBackup->getComprobanteSoloVista($ruc, $tipoDoc, $serie, $numero);
            
            if ($datosFactiliza) {
                // Generar HTML con los datos de Factiliza
                $htmlFactiliza = $this->generarHtmlDesdeFactiliza($datosFactiliza);
                
                log_message('info', "HTML generado desde Factiliza para {$nroFactura}");
                
                return $this->response->setHeader('Content-Type', 'text/html')->setBody($htmlFactiliza);
            }
            
        } catch (\Exception $e) {
            log_message('error', "Error al obtener HTML desde Factiliza: " . $e->getMessage());
        }
        
        return $this->response->setStatusCode(404)->setBody('Comprobante no encontrado en SUNAT ni en Factiliza');
    }
    
    /**
     * Genera HTML básico para visualizar comprobante desde datos de Factiliza
     */
    private function generarHtmlDesdeFactiliza($data)
    {
        // Extraer datos del comprobante
        $rucEmisor = $data->numeroRuc ?? 'N/A';
        $razonSocial = $data->razonSocial ?? 'N/A';
        $direccion = ($data->nombreCalle ?? '') . ', ' . ($data->nombreDistrito ?? '') . ', ' . ($data->nombreProvincia ?? '');
        
        $tipoDoc = $data->tipoComprobante ?? '01';
        $tipoDocNombre = $tipoDoc == '01' ? 'FACTURA ELECTRÓNICA' : ($tipoDoc == '03' ? 'BOLETA ELECTRÓNICA' : 'COMPROBANTE');
        $serie = $data->serieComprobante ?? '';
        $numero = $data->numeroComprobante ?? '';
        $fechaEmision = $data->fechaEmision ?? 'N/A';
        $fechaVencimiento = $data->fechaVencimiento ?? '-';
        
        $moneda = $data->codigoMoneda ?? 'PEN';
        $simboloMoneda = $data->simboloMoneda ?? 'S/';
        
        $tipoDocCliente = $data->tipoDocumentoCliente ?? '6';
        $numDocCliente = $data->numeroDocumentoCliente ?? '';
        $nombreCliente = $data->nombreCliente ?? '';
        
        $subtotal = number_format($data->totalValorVenta ?? 0, 2);
        $igv = number_format($data->totalIGV ?? 0, 2);
        $total = number_format($data->montoTotalGeneral ?? 0, 2);
        $totalTexto = $data->montoTotalTexto ?? '';
        
        $items = $data->detalleComprobanteBean ?? [];
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Comprobante - Factiliza</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
                .container { max-width: 800px; margin: 0 auto; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .header h2 { margin: 5px 0; }
                .badge { 
                    background: #28a745; 
                    color: white; 
                    padding: 5px 10px; 
                    border-radius: 3px; 
                    font-size: 0.9em;
                    display: inline-block;
                    margin-top: 10px;
                }
                .info-section { margin: 15px 0; }
                .info-row { display: flex; margin: 5px 0; }
                .info-label { font-weight: bold; width: 150px; }
                .info-value { flex: 1; }
                .box { border: 1px solid #ddd; padding: 10px; margin: 10px 0; }
                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .totals { margin-top: 20px; }
                .totals-row { display: flex; justify-content: flex-end; margin: 5px 0; }
                .totals-label { font-weight: bold; width: 150px; text-align: right; padding-right: 10px; }
                .totals-value { width: 120px; text-align: right; }
                .total-final { font-size: 1.2em; border-top: 2px solid #333; padding-top: 5px; margin-top: 5px; }
                .print-btn {
                    position: fixed;
                    top: 10px;
                    right: 10px;
                    z-index: 1000;
                    padding: 10px 20px;
                    background: #28a745;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                }
                @media print {
                    .no-print { display: none !important; }
                    body { font-size: 10px; }
                }
            </style>
        </head>
        <body>
            <button class="print-btn no-print" onclick="window.print()">Imprimir</button>
            
            <div class="container">
                <div class="header">
                    <h2>' . $tipoDocNombre . '</h2>
                    <h3>' . $serie . '-' . $numero . '</h3>
                    <span class="badge no-print">Fuente: Factiliza</span>
                </div>
                
                <div class="box">
                    <h3 style="margin-top: 0;">EMISOR</h3>
                    <div class="info-row">
                        <div class="info-label">RUC:</div>
                        <div class="info-value">' . $rucEmisor . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Razón Social:</div>
                        <div class="info-value">' . $razonSocial . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Dirección:</div>
                        <div class="info-value">' . $direccion . '</div>
                    </div>
                </div>
                
                <div class="box">
                    <h3 style="margin-top: 0;">CLIENTE</h3>
                    <div class="info-row">
                        <div class="info-label">Documento:</div>
                        <div class="info-value">' . ($tipoDocCliente == '6' ? 'RUC' : 'DNI') . ': ' . $numDocCliente . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Nombre:</div>
                        <div class="info-value">' . $nombreCliente . '</div>
                    </div>
                </div>
                
                <div class="box">
                    <h3 style="margin-top: 0;">DATOS DEL COMPROBANTE</h3>
                    <div class="info-row">
                        <div class="info-label">Fecha Emisión:</div>
                        <div class="info-value">' . $fechaEmision . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fecha Vencimiento:</div>
                        <div class="info-value">' . $fechaVencimiento . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Moneda:</div>
                        <div class="info-value">' . $moneda . '</div>
                    </div>
                </div>
                
                <h3>DETALLE DE ITEMS</h3>
                <table>
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">#</th>
                            <th width="10%">Código</th>
                            <th width="45%">Descripción</th>
                            <th class="text-center" width="10%">Cantidad</th>
                            <th class="text-right" width="15%">P. Unit.</th>
                            <th class="text-right" width="15%">Total</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        // Agregar items
        foreach ($items as $index => $item) {
            $cantidad = $item->cantidad ?? '0';
            $precioUnit = number_format($item->valorVtaUnitario ?? 0, 2);
            $totalItem = number_format(($item->cantidad ?? 0) * ($item->valorVtaUnitario ?? 0), 2);
            
            $html .= '
                        <tr>
                            <td class="text-center">' . ($index + 1) . '</td>
                            <td>' . ($item->codigoItem ?? '-') . '</td>
                            <td>' . ($item->descripcion ?? '') . '</td>
                            <td class="text-center">' . $cantidad . '</td>
                            <td class="text-right">' . $simboloMoneda . ' ' . $precioUnit . '</td>
                            <td class="text-right">' . $simboloMoneda . ' ' . $totalItem . '</td>
                        </tr>';
        }
        
        $html .= '
                    </tbody>
                </table>
                
                <div class="totals">
                    <div class="totals-row">
                        <div class="totals-label">SUBTOTAL:</div>
                        <div class="totals-value">' . $simboloMoneda . ' ' . $subtotal . '</div>
                    </div>
                    <div class="totals-row">
                        <div class="totals-label">IGV (18%):</div>
                        <div class="totals-value">' . $simboloMoneda . ' ' . $igv . '</div>
                    </div>
                    <div class="totals-row total-final">
                        <div class="totals-label">TOTAL:</div>
                        <div class="totals-value">' . $simboloMoneda . ' ' . $total . '</div>
                    </div>
                    <div class="totals-row">
                        <div class="totals-label"></div>
                        <div class="totals-value" style="font-size: 0.9em; font-style: italic;">' . $totalTexto . '</div>
                    </div>
                </div>
                
                <div class="box no-print" style="background: #fff3cd; border-color: #ffc107; margin-top: 20px;">
                    <p style="margin: 0;"><strong>Nota:</strong> Este comprobante fue obtenido desde el servicio de respaldo (Factiliza) 
                    porque no se pudo acceder a SUNAT en este momento. Los datos NO han sido guardados en la base de datos.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }

    public function searchInTransit()
    {
        $query = $this->request->getVar('query');
        if (empty($query)) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('IMPORT_FACT_DET d');
        $builder->select('d.DES_PROD, d.CANTIDAD, d.PRECIO, f.NRO_FACTURA, f.FECHA, f.RUC, c.CLI_NOMBRE as PROVEEDOR');
        $builder->join('IMPORT_FACT f', 'd.IDFACT = f.ID');
        $builder->join('clientes c', 'f.RUC = c.CLI_RUC_ESPOSO AND c.cli_cp = \'P\'', 'left');
        $builder->where('f.ESTADO', 0);
        $builder->like('d.DES_PROD', $query);
        $builder->orderBy('f.FECHA', 'DESC');
        
        $results = $builder->get()->getResult();

        return $this->response->setJSON($results);
    }
}
