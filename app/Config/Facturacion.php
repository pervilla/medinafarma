<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Facturacion extends BaseConfig
{
    public $ruc = '20450337839';
    public $razonSocial = 'INVERSIONES SAN MARTIN S.C.R.L.';
    public $usuarioSol = 'INVSAN18';
    public $claveSol = 'facsanmar18';
    public $certificadoPath = WRITEPATH . 'certificado/inversiones2025_cert_out.pem';
    
    // Production endpoints
    public $endpointFactura = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService';
    public $endpointGuia = 'https://e-guiaremision.sunat.gob.pe/ol-ti-itemision-guia-gem/billService';
    public $endpointRetencion = 'https://e-factura.sunat.gob.pe/ol-ti-itemision-otroscpe-gem/billService';

    // Beta/Test endpoints (useful for testing if needed)
    public $endpointBeta = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
    
    // Environment (true = Production, false = Beta)
    public $production = false; 
}
