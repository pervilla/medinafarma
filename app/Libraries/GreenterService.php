<?php

namespace App\Libraries;

use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;
use Greenter\Ws\Services\SoapClient;
use Greenter\Ws\Services\BillSender;
use Greenter\Ws\Services\ExtService;
use Greenter\Report\XmlUtils;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Config\Facturacion;

class GreenterService
{
    // ... existing properties ...
    protected $config;
    protected $see;
    protected $extService;

    // ... existing constructor ...
    public function __construct()
    {
        $this->config = new Facturacion();
        $this->see = $this->createSee();
    }
    
    // ... createSee, getSee, getExtService ...

    private function createSee()
    {
        $see = new See();
        $see->setCertificate(file_get_contents($this->config->certificadoPath));
        $see->setService($this->config->production ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);
        $see->setClaveSOL($this->config->ruc, $this->config->usuarioSol, $this->config->claveSol);
        return $see;
    }

    public function getSee()
    {
        return $this->see;
    }

    public function getExtService() 
    {
        if (!$this->extService) {
            $this->extService = new ExtService();
            $this->extService->setClaveSOL($this->config->ruc, $this->config->usuarioSol, $this->config->claveSol);
            $this->extService->setService($this->config->production ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);
        }
        return $this->extService;
    }

    public function getCompanyObject()
    {
        $address = new Address();
        $address->setUbigueo('220601')
            ->setDepartamento('San Martin')
            ->setProvincia('Mariscal Cáceres')
            ->setDistrito('Juanjuí')
            ->setUrbanizacion('-')
            ->setDireccion('Jr. Huallaga 601')
            ->setCodLocal('0000'); // TODO: Make dynamic if handling multiple branches with different codes

        $company = new Company();
        $company->setRuc($this->config->ruc)
            ->setRazonSocial($this->config->razonSocial)
            ->setNombreComercial('INVERSIONES SAN MARTIN S.C.R.L.')
            ->setAddress($address);
            
        return $company;
    }

    /**
     * Send individual invoice (Factura/Boleta) to SUNAT
     * 
     * @param array $headerData Invoice header from ALLOG
     * @param array $detailsData Invoice details from facart
     * @param string $tipoDoc Tipo documento: '01' Factura, '03' Boleta
     * @return \Greenter\Model\Response\SunatResponse
     */
    public function enviarFactura($headerData, $detailsData, $tipoDoc = '01')
    {
        $invoice = new \Greenter\Model\Sale\Invoice();
        
        // Set basic info
        $invoice->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Venta interna
            ->setTipoDoc($tipoDoc)
            ->setSerie($headerData['Serie'])
            ->setCorrelativo($headerData['Numero'])
            ->setFechaEmision(new \DateTime($headerData['Fecha']))
            ->setFormaPago(new \Greenter\Model\Sale\FormaPagos\FormaPagoContado()) // Contado
            ->setTipoMoneda('PEN'); // Soles
        
        // Set company
        $invoice->setCompany($this->getCompanyObject());
        
        // Set client
        $client = new \Greenter\Model\Client\Client();
        $client->setTipoDoc($headerData['ClienteTipoDoc'])
            ->setNumDoc($headerData['ClienteNumDoc'])
            ->setRznSocial($headerData['ClienteNombre']);
        
        if (!empty($headerData['ClienteDireccion'])) {
            $clientAddress = new \Greenter\Model\Sale\Address();
            $clientAddress->setDireccion($headerData['ClienteDireccion']);
            $client->setAddress($clientAddress);
        }
        
        $invoice->setClient($client);
        
        // Determine if operation is taxed or exonerated
        $isExonerated = ($headerData['IGV'] == 0);
        $tipAfeIgv = $isExonerated ? '20' : '10'; // 20=Exonerado, 10=Gravado
        
        // Add details (products)
        $items = [];
        foreach ($detailsData as $idx => $detail) {
            $item = new \Greenter\Model\Sale\SaleDetail();
            $item->setCodProducto($detail['Codigo'])
                ->setUnidad('NIU') // TODO: Get from product master
                ->setCantidad($detail['Cantidad'])
                ->setDescripcion($detail['Descripcion'])
                ->setMtoBaseIgv($detail['BaseImponible'])
                ->setPorcentajeIgv($isExonerated ? 0 : 18.00)
                ->setIgv($detail['IGV'])
                ->setTipAfeIgv($tipAfeIgv)
                ->setTotalImpuestos($detail['IGV'])
                ->setMtoValorVenta($detail['BaseImponible'])
                ->setMtoValorUnitario($detail['PrecioUnitario'])
                ->setMtoPrecioUnitario($detail['PrecioUnitario']); // Same as unit value for exonerated
            
            $items[] = $item;
        }
        
        $invoice->setDetails($items);
        
        // Set totals based on tax status
        if ($isExonerated) {
            $invoice->setMtoOperExoneradas($headerData['BaseImponible'])
                ->setMtoIGV(0)
                ->setTotalImpuestos(0)
                ->setValorVenta($headerData['BaseImponible'])
                ->setSubTotal($headerData['Total'])
                ->setMtoImpVenta($headerData['Total']);
        } else {
            $invoice->setMtoOperGravadas($headerData['BaseImponible'])
                ->setMtoIGV($headerData['IGV'])
                ->setTotalImpuestos($headerData['IGV'])
                ->setValorVenta($headerData['BaseImponible'])
                ->setSubTotal($headerData['Total'])
                ->setMtoImpVenta($headerData['Total']);
        }
        
        // Send to SUNAT
        $result = $this->see->send($invoice);
        
        return $result;
    }
}
