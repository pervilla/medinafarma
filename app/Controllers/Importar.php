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
        $SireSunat = new SireSunat();
        // Llamar al método getComprobantesPorFecha
        return $SireSunat->getDetalleComprobante($ruc, $cod, $serie, $numero, $rpta);
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
        $crearcompra = $ImportFactModel->crea_compra($idfact, $codclie);
        return $this->response->setJSON($crearcompra);
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
        
        return $this->response->setStatusCode(404)->setBody('Comprobante no encontrado');
    }
}
