<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers;

use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use CodeIgniter\I18n\Time;

use Greenter\Model\Sale\Document;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Summary\SummaryPerception;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;
/**
 * Description of Facturador
 *
 * @author José Luis
 */
class Facturador extends BaseController {

        public function diario(){
                return view('facturador/diario');
        }
        public function pem(){
                $pfx = file_get_contents(__DIR__ . '\..\..\writable\certificado\marymed.p12');
                $password = 'a1b2c3d4';

                $certificate = new X509Certificate($pfx, $password);
                $pem = $certificate->export(X509ContentType::PEM);

                file_put_contents(__DIR__ . '\..\..\writable\certificado\marymed.pem', $pem);
        }


        public function resumen(){
                /*$see = new See();
                $see->setCertificate(file_get_contents(__DIR__ . '\..\..\writable\certificado\certificate.pem'));
                $see->setService(SunatEndpoints::FE_BETA);
                $see->setClaveSOL('20450337839', 'INVSAN18', 'facsanmar18');
*/
                $see = new See();
                $see->setCertificate(file_get_contents(__DIR__ . '\..\..\writable\certificado\marymed.pem'));
                $see->setService(SunatEndpoints::WSDL_ENDPOINT);
                $see->setClaveSOL('10422781191', 'MARYMEDI', 'Mar1M3d');
/**/
                $company = new Company();
                $company->setRuc('10422781191')
                ->setRazonSocial('MARIA DORISEVIA MEDINA ROJAS')
                ->setNombreComercial('BREO MARKET')
                ->setAddress((new Address())
                        ->setUbigueo('220601')
                        ->setDepartamento('SAN MARTIN')
                        ->setProvincia('MARISCAL CACERES')
                        ->setDistrito('JUANJUI')
                        ->setUrbanizacion('JUANJUI')
                        ->setDireccion('JR PROGRESO 400')
                        ->setCodLocal('0000')
        );

        $detiail1 = new SummaryDetail();
        $detiail1->setTipoDoc('03')
            ->setSerieNro('B001-1')
            ->setEstado('3')
            ->setClienteTipo('1')
            ->setClienteNro('00000000')
            ->setTotal(129.555)
            ->setMtoOperGravadas(20)
            ->setMtoOperInafectas(24.4)
            ->setMtoOperExoneradas(50)
            ->setMtoOperExportacion(10.555)
            ->setMtoOtrosCargos(21)
            ->setMtoIGV(3.6);
        
        $detiail2 = new SummaryDetail();
        $detiail2->setTipoDoc('07')
            ->setSerieNro('B001-4')
            ->setDocReferencia((new Document())
                ->setTipoDoc('03')
                ->setNroDoc('0001-122'))
            ->setEstado('1')
            ->setClienteTipo('1')
            ->setClienteNro('00000000')
            ->setTotal(200)
            ->setMtoOperGravadas(40)
            ->setMtoOperExoneradas(30)
            ->setMtoOperInafectas(120)
            ->setMtoIGV(7.2)
            ->setMtoISC(2.8);
        
        $detiail3 = new SummaryDetail();
        $detiail3->setTipoDoc('03')
            ->setSerieNro('B001-2')
            ->setEstado('1')
            ->setClienteTipo('1')
            ->setClienteNro('00000000')
            ->setPercepcion((new SummaryPerception())
                ->setCodReg('01')
                ->setTasa(2.00)
                ->setMtoBase(119.00)
                ->setMto(2.00)
                ->setMtoTotal(121.38))
            ->setTotal(119)
            ->setMtoOperGravadas(20)
            ->setMtoOperInafectas(24.4)
            ->setMtoOperExoneradas(50)
            ->setMtoOtrosCargos(21)
            ->setMtoIGV(3.6);
        
        $sum = new Summary();
        // Fecha Generacion menor que Fecha Resumen
        $sum->setFecGeneracion(new \DateTime('-3days'))
            ->setFecResumen(new \DateTime('-1days'))
            ->setCorrelativo('001')
            ->setCompany($company)
            ->setDetails([$detiail1, $detiail2, $detiail3]);

$result = $see->send($sum);
// Guardar XML
file_put_contents($sum->getName().'.xml',$see->getFactory()->getLastXml());

if (!$result->isSuccess()) {
    echo "<br> Error Sunat <br>";
    var_dump($result->getError());
    //exit();
}

$ticket = $result->getTicket();
echo "<br> Error Sunat <br>";
echo 'Ticket : '.$ticket.PHP_EOL;

$statusResult = $see->getStatus($ticket);
if (!$statusResult->isSuccess()) {
    // Si hubo error al conectarse al servicio de SUNAT.
    var_dump($statusResult->getError());
    return;
}

echo $statusResult->getCdrResponse()->getDescription();
// Guardar CDR
file_put_contents('R-'.$sum->getName().'.zip', $statusResult->getCdrZip());
echo "<br>FIM<br>";

        }





    public function index2() {

        $see = new See();
        $see->setCertificate(file_get_contents(__DIR__ . '\..\..\writable\certificado\certificate.pem'));
        $see->setService(SunatEndpoints::FE_BETA);
        $see->setClaveSOL('20000000001', 'MODDATOS', 'moddatos');
        
        
        $company = new Company();
        $company->setRuc('20000000001')
                ->setRazonSocial('EMPRESA SAC')
                ->setNombreComercial('EMPRESA')
                ->setAddress((new Address())
                        ->setUbigueo('150101')
                        ->setDepartamento('LIMA')
                        ->setProvincia('LIMA')
                        ->setDistrito('LIMA')
                        ->setUrbanizacion('Albar')
                        ->setDireccion('AV ROSALES'));

        $detail = new SummaryDetail();
        $detail->setTipoDoc('07') // Nota de Credito
                ->setSerieNro('B001-4')
                ->setDocReferencia((new Document()) // Documento relacionado (Boleta)
                        ->setTipoDoc('03')
                        ->setNroDoc('B001-1'))
                ->setEstado('1') // Emisión
                ->setClienteTipo('1') // Tipo documento identidad: DNI
                ->setClienteNro('00000000') // Nro de documento identidad
                ->setTotal(200)
                ->setMtoOperGravadas(40)
                ->setMtoOperExoneradas(50)
                ->setMtoOperInafectas(100)
                ->setMtoIGV(7.2)
                ->setMtoISC(2.8);

        $detail2 = new SummaryDetail();
        $detail2->setTipoDoc('03') // Boleta
                ->setSerieNro('B001-2')
                ->setEstado('3') // Anulación
                ->setClienteTipo('1')
                ->setClienteNro('00000000')
                ->setTotal(119)
                ->setMtoOperGravadas(20)
                ->setMtoOperInafectas(24.4)
                ->setMtoOperExoneradas(50)
                ->setMtoOtrosCargos(21)
                ->setMtoIGV(3.6);

        $resumen = new Summary();
        $resumen->setFecGeneracion(Time::parse(('2021-05-04'), 'America/Lima')) // Fecha de emisión de las boletas.
                ->setFecResumen(Time::parse(('2021-05-05'), 'America/Lima')) // Fecha de envío del resumen diario.
                ->setCorrelativo('001') // Correlativo, necesario para diferenciar de otros Resumen diario del mismo día.
                ->setCompany($company)
                ->setDetails([$detail, $detail2]);

        $result = $see->send($resumen);
// Guardar XML
        file_put_contents($resumen->getName() . '.xml',
                $see->getFactory()->getLastXml());

        if (!$result->isSuccess()) {
            // Si hubo error al conectarse al servicio de SUNAT.
            var_dump($result->getError());
            exit();
        }

        $ticket = $result->getTicket();
        echo 'Ticket : ' . $ticket . PHP_EOL;

        $statusResult = $see->getStatus($ticket);
        if (!$statusResult->isSuccess()) {
            // Si hubo error al conectarse al servicio de SUNAT.
            var_dump($statusResult->getError());
            return;
        }

        echo $statusResult->getCdrResponse()->getDescription();
// Guardar CDR
        file_put_contents('R-' . $resumen->getName() . '.zip', $statusResult->getCdrZip());
    }



    public function boleta() {


        $see = new See();
        $certificate = file_get_contents(__DIR__ . '\..\..\writable\certificado\marymed.pem');
        if ($certificate === false) {
            throw new Exception('No se pudo cargar el certificado');
        }
        $see->setCertificate($certificate);
        
        $see->setService(SunatEndpoints::FE_PRODUCCION);
        $see->setClaveSOL('10422781191', 'MARYMEDI', 'Mar1M3d');
        $see->setCachePath(__DIR__ . '/../cache');
/**/
        $company = new Company();
        $company->setRuc('10422781191')
        ->setRazonSocial('MARIA DORISEVIA MEDINA ROJAS')
        ->setNombreComercial('BREO MARKET')
        ->setAddress((new Address())
                ->setUbigueo('220601')
                ->setDepartamento('SAN MARTIN')
                ->setProvincia('MARISCAL CACERES')
                ->setDistrito('JUANJUI')
                ->setUrbanizacion('JUANJUI')
                ->setDireccion('JR PROGRESO 400')
                ->setCodLocal('0000')
);


/*
    
        $comprobante_id = $this->uri->segment(3);


        $comprobante = $this->comprobantes_model->select($comprobante_id);

        //var_export($comprobante); die();
        $detalle = $this->items_model->select('', $comprobante_id);       


        //var_export($detalle); die();
        $detraccion = $this->elemento_adicionales_model->select('', '', 'activo');
*/
// Cliente
$client = new Client();
$client->setTipoDoc('1')
    ->setNumDoc('46712369')
    ->setRznSocial('MARIA RAMOS ARTEAGA');

// Emisor
        $address = (new Address())
        ->setUbigueo('220601')
        ->setDepartamento('SAN MARTIN')
        ->setProvincia('MARISCAL CACERES')
        ->setDistrito('JUANJUI')
        ->setUrbanizacion('JUANJUI')
        ->setDireccion('JR PROGRESO 400')
        ->setCodLocal('0000'); // Codigo de establecimiento asignado por SUNAT, 0000 por defecto.

        $company = (new Company())
        ->setRuc('10422781191')
        ->setRazonSocial('MARIA DORISEVIA MEDINA ROJAS')
        ->setNombreComercial('BREO MARKET')
                ->setAddress($address);

        // Venta
        $date = new \DateTime('04/03/2023');

        $invoice = new Invoice();
        $invoice->setFormaPago(new FormaPagoContado());

        
        $invoice
        ->setUblVersion('2.1')
        ->setTipoOperacion('0101')
        ->setTipoDoc('03')
        ->setSerie('B001')
        ->setCorrelativo('18217')
        ->setFechaEmision($date)
        ->setTipoMoneda('PEN')
        ->setCompany($company)
        ->setClient($client)
        ->setMtoOperExoneradas(3.80)
        ->setMtoIGV(0)
        ->setTotalImpuestos(0)
        ->setValorVenta(3.80)
        ->setSubTotal(3.80)
	->setRedondeo(0)
        ->setMtoImpVenta(3.80);

        $items = array();
        //foreach($detalle as $row){
		$item = new SaleDetail();
		$item->setCodProducto(1000)
		->setUnidad('NIU')
		->setCantidad(1)
		->setDescripcion('BLUSA')
		->setMtoBaseIgv(3.8)
		->setPorcentajeIgv(0)
		->setIgv(0)
		->setTipAfeIgv('20')
		->setTotalImpuestos(0)
		->setDescuento(0)
		->setMtoValorVenta(3.8)
		->setMtoValorUnitario(3.8)
		->setMtoPrecioUnitario(3.8)
		;                
		$items[] = $item;
        //}
        //$num = new Numletras();
        $importe_letra = 'TRES CON 80/00 SOLES';
               

        $legend = (new Legend())
                ->setCode('1000') // Monto en letras - Catalog. 52
                ->setValue($importe_letra);

        $invoice->setDetails($items)
                ->setLegends([$legend]);

       // var_export($invoice); die();
        $result = $see->send($invoice);

// Guardar XML firmado digitalmente.
file_put_contents('C:/Doscar/' .$invoice->getName() . '.xml', $see->getFactory()->getLastXml());


// Verificamos que la conexión con SUNAT fue exitosa.
        if (!$result->isSuccess()) {
            // Mostrar error al conectarse a SUNAT.
            echo 'Codigo Error: ' . $result->getError()->getCode();
            echo 'Mensaje Error: ' . $result->getError()->getMessage();
            exit();
        }

// Guardamos el CDR
        file_put_contents('C:/Doscar/R-' . $invoice->getName() . '.zip', $result->getCdrZip());

        $cdr = $result->getCdrResponse();

        $code = (int) $cdr->getCode();

        if ($code === 0) {
            echo 'ESTADO: ACEPTADA' . PHP_EOL;

            //$this->comprobantes_model->modificar(array('enviado_sunat' => 1), $comprobante_id);
            //$this->session->set_flashdata('mensaje', 'Envio exitoso!');

            if (count($cdr->getNotes()) > 0) {
                echo 'OBSERVACIONES:' . PHP_EOL;
                // Corregir estas observaciones en siguientes emisiones.
                var_dump($cdr->getNotes());
            }
        } else if ($code >= 2000 && $code <= 3999) {
            echo 'ESTADO: RECHAZADA' . PHP_EOL;
        } else {
            /* Esto no debería darse, pero si ocurre, es un CDR inválido que debería tratarse como un error-excepción. */
            /* code: 0100 a 1999 */
            echo 'Excepción';
        }

        echo $cdr->getDescription() . PHP_EOL;

    }





    public function index() {

        $see = new See();
        $see->setCertificate(file_get_contents(__DIR__ . '\..\..\writable\certificado\certificate.pem'));
        $see->setService(SunatEndpoints::FE_BETA);
        $see->setClaveSOL('20000000001', 'MODDATOS', 'moddatos');

// Cliente
        $client = (new Client())
                ->setTipoDoc('6')
                ->setNumDoc('20000000001')
                ->setRznSocial('EMPRESA X');

// Emisor
        $address = (new Address())
                ->setUbigueo('150101')
                ->setDepartamento('LIMA')
                ->setProvincia('LIMA')
                ->setDistrito('LIMA')
                ->setUrbanizacion('-')
                ->setDireccion('Av. Villa Nueva 221')
                ->setCodLocal('0000'); // Codigo de establecimiento asignado por SUNAT, 0000 por defecto.

        $company = (new Company())
                ->setRuc('20123456789')
                ->setRazonSocial('GREEN SAC')
                ->setNombreComercial('GREEN')
                ->setAddress($address);

// Venta
        $invoice = (new Invoice())
                ->setUblVersion('2.1')
                ->setTipoOperacion('0101') // Venta - Catalog. 51
                ->setTipoDoc('01') // Factura - Catalog. 01 
                ->setSerie('F001')
                ->setCorrelativo('1')
                ->setFechaEmision(Time::parse(('2021-05-05'), 'America/Lima')) //(Time::parse($fecha, 'America/Lima')) // Zona horaria: Lima
                ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
                ->setTipoMoneda('PEN') // Sol - Catalog. 02
                ->setCompany($company)
                ->setClient($client)
                ->setMtoOperGravadas(100.00)
                ->setMtoIGV(18.00)
                ->setTotalImpuestos(18.00)
                ->setValorVenta(100.00)
                ->setSubTotal(118.00)
                ->setMtoImpVenta(118.00)
        ;

        $item = (new SaleDetail())
                ->setCodProducto('P001')
                ->setUnidad('NIU') // Unidad - Catalog. 03
                ->setCantidad(2)
                ->setMtoValorUnitario(50.00)
                ->setDescripcion('PRODUCTO 1')
                ->setMtoBaseIgv(100)
                ->setPorcentajeIgv(18.00) // 18%
                ->setIgv(18.00)
                ->setTipAfeIgv('10') // Gravado Op. Onerosa - Catalog. 07
                ->setTotalImpuestos(18.00) // Suma de impuestos en el detalle
                ->setMtoValorVenta(100.00)
                ->setMtoPrecioUnitario(59.00)
        ;

        $legend = (new Legend())
                ->setCode('1000') // Monto en letras - Catalog. 52
                ->setValue('SON DOSCIENTOS TREINTA Y SEIS CON 00/100 SOLES');

        $invoice->setDetails([$item])
                ->setLegends([$legend]);

        $result = $see->send($invoice);

// Guardar XML firmado digitalmente.
        file_put_contents($invoice->getName() . '.xml',
                $see->getFactory()->getLastXml());

// Verificamos que la conexión con SUNAT fue exitosa.
        if (!$result->isSuccess()) {
            // Mostrar error al conectarse a SUNAT.
            echo 'Codigo Error: ' . $result->getError()->getCode();
            echo 'Mensaje Error: ' . $result->getError()->getMessage();
            exit();
        }

// Guardamos el CDR
        file_put_contents('R-' . $invoice->getName() . '.zip', $result->getCdrZip());

        $cdr = $result->getCdrResponse();

        $code = (int) $cdr->getCode();

        if ($code === 0) {
            echo 'ESTADO: ACEPTADA' . PHP_EOL;
            if (count($cdr->getNotes()) > 0) {
                echo 'OBSERVACIONES:' . PHP_EOL;
                // Corregir estas observaciones en siguientes emisiones.
                var_dump($cdr->getNotes());
            }
        } else if ($code >= 2000 && $code <= 3999) {
            echo 'ESTADO: RECHAZADA' . PHP_EOL;
        } else {
            /* Esto no debería darse, pero si ocurre, es un CDR inválido que debería tratarse como un error-excepción. */
            /* code: 0100 a 1999 */
            echo 'Excepción';
        }

        echo $cdr->getDescription() . PHP_EOL;

        echo "facturadorooo";
    }

}
