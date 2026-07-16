<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Document;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use App\Models\SunatModel;
use App\Models\SunatLogModel;

class SunatController extends BaseController {

    private $empresas = [
        '20450337839' => [
            'ruc' => '20450337839',
            'razon_social' => 'INVERSIONES SAN MARTIN S.C.R.L.',
            'nombre_comercial' => 'INVERSIONES SAN MARTIN',
            'usuario_sol' => 'INVSAN18',
            'clave_sol' => 'facsanmar18',
            'cert_path' => 'inversiones2025_cert_out.pem',
            'series' => ['10' => 'BO10/FA10', '11' => 'BO11/FA11'],
            'tipo' => ['boletas', 'facturas']
        ],
        '10422781191' => [
            'ruc' => '10422781191',
            'razon_social' => 'MARIA DORISEVIA MEDINA ROJAS',
            'nombre_comercial' => 'BREO MARKET',
            'usuario_sol' => 'MARYMEDI',
            'clave_sol' => 'Mar1M3d',
            'cert_path' => 'marymed2025.pem',
            'series' => ['B001' => 'B001'],
            'tipo' => ['boletas']
        ]
    ];

    public function panel() {
        if (session()->get('user_id') != 'ADMIN') {
            return redirect()->to('/');
        }
        return view('sunat/panel', [
            'empresas' => $this->empresas,
            'menu'     => ['p' => 90, 'i' => 91]
        ]);
    }

    private function _post(string $key): ?string {
        $value = $this->request->getPost($key);
        if ($value === null && !empty($_POST[$key])) {
            $value = $_POST[$key];
        }
        return $value;
    }

    private function _nextCorrelativo(string $empresa_ruc): string {
        $fecha = $this->_post('fecha');
        $serie = $this->_post('serie');
        $logModel = new SunatLogModel();
        // Contar resúmenes HOY para esta empresa (global para evitar duplicados en nombre)
        $countHoy = $logModel->db->table('sunat_resumenes')
            ->where('empresa_ruc', $empresa_ruc)
            ->where('fecha_resumen', date('Y-m-d'))
            ->countAllResults();
        $next = $countHoy + 1;
        // Incluir identificador de serie para distinguir S10 de S11
        $serieId = ($serie == '10') ? '1' : (($serie == '11') ? '2' : '3');
        return $serieId . str_pad($next, 2, '0', STR_PAD_LEFT);
    }

    private function _getCompanyAddress(string $ruc): Address {
        if ($ruc == '10422781191') {
            return (new Address())
                ->setUbigueo('220601')
                ->setDepartamento('SAN MARTIN')
                ->setProvincia('MARISCAL CACERES')
                ->setDistrito('JUANJUI')
                ->setDireccion('JR. PROGRESO 400');
        }
        return (new Address())
            ->setUbigueo('220601')
            ->setDepartamento('SAN MARTIN')
            ->setProvincia('MARISCAL CACERES')
            ->setDistrito('JUANJUI')
            ->setDireccion('JR. HUALLAGA 601');
    }

    private function _cronLog(string $msg) {
        $line = "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL;
        file_put_contents(WRITEPATH . 'logs/sunat_cron_' . date('Ymd') . '.log', $line, FILE_APPEND | LOCK_EX);
    }

    private function getSee($empresa_ruc) {
        $empresa = $this->empresas[$empresa_ruc];
        $see = new See();
        $see->setCertificate(file_get_contents(__DIR__ . '/../../writable/certificado/' . $empresa['cert_path']));
        $see->setService(SunatEndpoints::FE_PRODUCCION); 
        $see->setClaveSOL($empresa_ruc, $empresa['usuario_sol'], $empresa['clave_sol']);
        return $see;
    }

    public function api_pendientes() {
        $fecha = $this->_post('fecha');
        $empresa_ruc = $this->_post('empresa_ruc');
        $serie = $this->_post('serie');

        $sunatModel = new SunatModel();
        if ($empresa_ruc == '20450337839') {
            $comprobantes = $sunatModel->getComprobantesSanMartin($fecha, $serie);
            $debug_sql    = $sunatModel->getLastSql();
        } else {
            $comprobantes = $sunatModel->getBoletasMarymed($fecha);
            $debug_sql    = '';
        }

        $logModel = new SunatLogModel();
        $yaExiste = $logModel->yaExisteResumen($empresa_ruc, $serie, $fecha);

        return $this->response->setJSON([
            'status'    => 'success', 
            'data'      => $comprobantes,
            'ya_existe' => $yaExiste,
            'debug'     => ['count' => count($comprobantes), 'sql' => $debug_sql]
        ]);
    }

    public function generar_resumen() {
        $fecha = $this->_post('fecha');
        $empresa_ruc = $this->_post('empresa_ruc');
        $serie = $this->_post('serie');

        $sunatModel = new SunatModel();
        $logModel = new SunatLogModel();

        if ($empresa_ruc == '20450337839') {
            $comprobantes = $sunatModel->getComprobantesSanMartin($fecha, $serie);
        } else {
            $comprobantes = $sunatModel->getBoletasMarymed($fecha);
        }

        // --- BLOQUEO DE DUPLICADOS ---
        if ($logModel->yaExisteResumen($empresa_ruc, $serie, $fecha)) {
            return $this->response->setJSON([
                'status'  => 'warning',
                'message' => 'Ya existe un resumen PENDIENTE o ACEPTADO para esta empresa, serie y fecha. No se puede generar otro.'
            ]);
        }

        // Filtrar solo boletas para el resumen (tipo_fbg = 'B')
        $boletas = array_filter($comprobantes, function($c) { return $c->tipo_fbg == 'B'; });

        if (empty($boletas)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No hay boletas para la fecha seleccionada']);
        }

        $empresaConfig = $this->empresas[$empresa_ruc];
        $company = new Company();
        $company->setRuc($empresa_ruc)
                ->setRazonSocial($empresaConfig['razon_social'])
                ->setNombreComercial($empresaConfig['nombre_comercial'])
                ->setAddress($this->_getCompanyAddress($empresa_ruc));

        $details = [];
        foreach ($boletas as $b) {
            $detail = new SummaryDetail();
            
            // Logica LEY AMAZONIA N° 27037: Exonerado de IGV
            // Monto Operaciones Exoneradas = Total
            // Monto IGV = 0
            
            $serie_sunat = ($empresa_ruc == '10422781191') ? $b->serie : 'BO' . $b->serie;
            $cliente_tipo = empty($b->cliente_dni) ? (empty($b->cliente_ruc) ? '0' : '6') : '1';
            $cliente_nro = empty($b->cliente_dni) ? (empty($b->cliente_ruc) ? '00000000' : $b->cliente_ruc) : $b->cliente_dni;

            $detail->setTipoDoc('03') // 03 = Boleta
                ->setSerieNro($serie_sunat . '-' . $b->numero)
                ->setEstado('1') // 1: Adicionar
                ->setClienteTipo($cliente_tipo)
                ->setClienteNro($cliente_nro)
                ->setTotal($b->total)
                ->setMtoOperExoneradas($b->total) // Ley Amazonia N° 27037
                ->setMtoOperGravadas(0)
                ->setMtoIGV(0);
                
            $details[] = $detail;
        }

        $sum = new Summary();
        $sum->setFecGeneracion(new \DateTime($fecha))
            ->setFecResumen(new \DateTime())
            ->setCorrelativo($this->_nextCorrelativo($empresa_ruc))
            ->setCompany($company)
            ->setDetails($details);

        $see = $this->getSee($empresa_ruc);
        $result = $see->send($sum);

        $xml_path = WRITEPATH . 'uploads/' . $sum->getName() . '.xml';
        file_put_contents($xml_path, $see->getFactory()->getLastXml());

        if (!$result->isSuccess()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => $result->getError()->getMessage(),
                'code' => $result->getError()->getCode()
            ]);
        }

        $ticket = $result->getTicket();

        // Registrar en BD (incluye la serie para control de duplicados)
        $logModel->insertResumen([
            'empresa_ruc'      => $empresa_ruc,
            'serie'            => $serie,
            'fecha_generacion' => $fecha,
            'fecha_resumen'    => date('Y-m-d'),
            'correlativo'      => 1,
            'ticket'           => $ticket,
            'estado_sunat'     => 'PENDIENTE',
            'xml_path'         => $xml_path,
            'mensaje_sunat'    => 'Enviado a SUNAT, ticket: ' . $ticket
        ]);

        return $this->response->setJSON([
            'status'  => 'success', 
            'ticket'  => $ticket,
            'message' => 'Resumen enviado exitosamente a SUNAT.'
        ]);
    }

    public function historial() {
        if (session()->get('user_id') != 'ADMIN') {
            return redirect()->to('/');
        }
        $logModel = new SunatLogModel();
        $resumenes = $logModel->getResumenesHistorial(200);
        return view('sunat/historial', [
            'empresas'  => $this->empresas,
            'resumenes' => $resumenes,
            'menu'      => ['p' => 90, 'i' => 92]
        ]);
    }

    public function consultar_ticket() {
        $ticket = $this->_post('ticket');
        $empresa_ruc = $this->_post('empresa_ruc');

        try {
            $logModel = new SunatLogModel();
            $see = $this->getSee($empresa_ruc);
            
            $statusResult = $see->getStatus($ticket);

            if (!$statusResult->isSuccess()) {
                $error = $statusResult->getError();
                $errorCode = $error->getCode();
                $errorMsg = $error->getMessage();
                if (empty($errorMsg)) {
                    $errorMsg = "Código SUNAT: " . ($errorCode ?: 'sin código');
                }
            $logModel->updateResumen($ticket, [
                'estado_sunat' => ($errorCode == '98') ? 'PENDIENTE' : 'ERROR',
                'mensaje_sunat' => mb_substr($errorMsg, 0, 1000)
            ]);
                return $this->response->setJSON([
                    'status' => 'error', 
                    'message' => $errorMsg,
                    'code' => $errorCode
                ]);
            }

            $cdrResponse = $statusResult->getCdrResponse();
            $code = (int) $cdrResponse->getCode();
            $descripcion = $cdrResponse->getDescription();
            
            $estado = 'EXCEPCION';
            if ($code === 0) {
                $estado = 'ACEPTADA';
            } else if ($code >= 2000 && $code <= 3999) {
                $estado = 'RECHAZADA';
            }

            $cdr_path = WRITEPATH . 'uploads/R-' . $ticket . '.zip';
            file_put_contents($cdr_path, $statusResult->getCdrZip());

            $logModel->updateResumen($ticket, [
                'estado_sunat' => $estado,
                'cdr_path' => $cdr_path,
                'mensaje_sunat' => mb_substr($descripcion, 0, 1000)
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'estado' => $estado,
                'descripcion' => $descripcion
            ]);
        } catch (\Exception $e) {
            log_message('error', 'consultar_ticket exception: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al consultar SUNAT: ' . $e->getMessage()
            ]);
        }
    }

    // ==========================================
    // FACTURAS
    // ==========================================

    public function cron_manual() {
        if (session()->get('user_id') != 'ADMIN') {
            return redirect()->to('/');
        }
        $dias = (int)($this->_post('dias') ?: 3);
        $log = [];
        $log[] = "=== CRON MANUAL INICIADO ===";
        $log[] = "Días: $dias";
        $this->_cronLog("=== CRON MANUAL INICIADO ===");
        $this->_cronLog("Días: $dias");

        $sunatModel = new SunatModel();
        $logModel = new SunatLogModel();

        $controller = new SunatController();
        $controller->initController(\Config\Services::request(), \Config\Services::response(), \Config\Services::logger());

        $fechaFin = date('Y-m-d', strtotime('-1 day'));
        $fechaInicio = date('Y-m-d', strtotime($fechaFin . " -" . ($dias - 1) . " days"));
        $log[] = "Rango: $fechaInicio → $fechaFin";

        $empresas = [
            ['ruc' => '20450337839', 'serie' => '10', 'nombre' => 'INVERSIONES (Serie 10-201)'],
            ['ruc' => '20450337839', 'serie' => '11', 'nombre' => 'INVERSIONES (Serie 11-200)'],
            ['ruc' => '10422781191', 'serie' => 'B001', 'nombre' => 'MARYMED'],
        ];

        $fechaActual = $fechaInicio;
        while ($fechaActual <= $fechaFin) {
            $log[] = "--- Procesando: $fechaActual ---";

            foreach ($empresas as $emp) {
                if ($logModel->yaExisteResumen($emp['ruc'], $emp['serie'], $fechaActual)) {
                    $log[] = "  ↺ {$emp['nombre']}: ya existe, salta.";
                    continue;
                }
                $log[] = "  → {$emp['nombre']}...";
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = ['fecha' => $fechaActual, 'empresa_ruc' => $emp['ruc'], 'serie' => $emp['serie']];
                try {
                    $res = json_decode($controller->generar_resumen()->getBody(), true);
                    $log[] = "  " . ($res['status'] == 'success' ? "✓ Ticket: {$res['ticket']}" : "✗ {$res['message']}");
                } catch (\Exception $e) {
                    $log[] = "  ✗ Error: " . $e->getMessage();
                }
            }

            // Facturas solo INVERSIONES
            foreach (['10', '11'] as $serie) {
                $facturas = $sunatModel->getFacturasSanMartin($fechaActual, $serie);
                if (empty($facturas)) continue;
                foreach ($facturas as $f) {
                    $serieFa = 'FA' . $serie;
                    $exist = $logModel->db->table('sunat_comprobantes')
                        ->where('empresa_ruc', '20450337839')
                        ->where('tipo_doc', '01')
                        ->where('serie', $serieFa)
                        ->where('correlativo', (int)$f->numero)
                        ->get()->getRow();
                    if ($exist) continue;

                    $_POST = ['fecha' => $fechaActual, 'serie' => $serie, 'numero' => $f->numero];
                    try {
                        $res = json_decode($controller->enviar_factura()->getBody(), true);
                        $log[] = "  FA{$serie}-{$f->numero}: " . ($res['status'] == 'success' ? '✓ ACEPTADA' : "✗ {$res['message']}");
                    } catch (\Exception $e) {
                        $log[] = "  FA{$serie}-{$f->numero}: ✗ Error: " . $e->getMessage();
                    }
                }
            }
            $fechaActual = date('Y-m-d', strtotime($fechaActual . ' +1 day'));
        }

        // Consultar tickets pendientes
        $pendientes = $logModel->db->table('sunat_resumenes')
            ->where('estado_sunat', 'PENDIENTE')
            ->where('ticket IS NOT NULL')
            ->get()->getResult();
        if ($pendientes) {
            $log[] = "--- Consultando {$pendientes} tickets pendientes ---";
            foreach ($pendientes as $p) {
                $_POST = ['ticket' => $p->ticket, 'empresa_ruc' => $p->empresa_ruc];
                try {
                    $res = json_decode($controller->consultar_ticket()->getBody(), true);
                    $log[] = "  Ticket {$p->ticket}: {$res['estado']}";
                } catch (\Exception $e) {
                    $log[] = "  Ticket {$p->ticket}: ✗ " . $e->getMessage();
                }
            }
        }

        $log[] = "=== CRON MANUAL FINALIZADO ===";
        log_message('info', 'CRON_MANUAL: ' . json_encode($log));
        return $this->response->setJSON(['status' => 'success', 'log' => $log]);
    }

    public function comprobantes() {
        if (session()->get('user_id') != 'ADMIN') {
            return redirect()->to('/');
        }
        return view('sunat/comprobantes', [
            'empresas'     => $this->empresas,
            'comprobantes' => (new SunatLogModel())->getComprobantesHistorial(200),
            'menu'         => ['p' => 90, 'i' => 94]
        ]);
    }

    public function facturas() {
        if (session()->get('user_id') != 'ADMIN') {
            return redirect()->to('/');
        }
        return view('sunat/facturas', [
            'empresas' => $this->empresas,
            'menu'     => ['p' => 90, 'i' => 93]
        ]);
    }

    public function api_facturas_pendientes() {
        $fecha = $this->_post('fecha');
        $serie = $this->_post('serie');

        $sunatModel = new SunatModel();
        $facturas = $sunatModel->getFacturasSanMartin($fecha, $serie);

        // Verificar cuáles ya fueron enviadas
        $logModel = new SunatLogModel();
        foreach ($facturas as $f) {
            $serieFa = 'FA' . $f->serie;
            $builder = $logModel->db->table('sunat_comprobantes');
            $builder->where('empresa_ruc', '20450337839');
            $builder->where('tipo_doc', '01');
            $builder->where('serie', $serieFa);
            $builder->where('correlativo', (int)$f->numero);
            $existing = $builder->get()->getRow();
            $f->enviada = ($existing !== null);
            $f->estado_envio = $existing ? $existing->estado_sunat : null;
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'data'     => $facturas,
            'debug'    => ['count' => count($facturas)]
        ]);
    }

    public function api_factura_detalles() {
        $serie = $this->_post('serie');
        $numero = $this->_post('numero');

        $sunatModel = new SunatModel();
        $detalles = $sunatModel->getDetallesSanMartin($serie, $numero);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $detalles
        ]);
    }

    public function enviar_factura() {
        $fecha = $this->_post('fecha');
        $serie = $this->_post('serie');
        $numero = $this->_post('numero');
        $empresa_ruc = '20450337839'; // Solo inversiones tiene facturas

        $sunatModel = new SunatModel();
        $logModel = new SunatLogModel();

        // Buscar la factura específica
        $facturas = $sunatModel->getFacturasSanMartin($fecha, $serie);
        $factura = null;
        foreach ($facturas as $f) {
            if ($f->numero == $numero) {
                $factura = $f;
                break;
            }
        }

        if (!$factura) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Factura no encontrada para la fecha indicada.'
            ]);
        }

        // Obtener detalle de productos
        $detalles = $sunatModel->getDetallesSanMartin($serie, $numero);

        // --- Construir Company ---
        $empresaConfig = $this->empresas[$empresa_ruc];
        $company = new Company();
        $company->setRuc($empresa_ruc)
            ->setRazonSocial($empresaConfig['razon_social'])
            ->setNombreComercial($empresaConfig['nombre_comercial'])
            ->setAddress($this->_getCompanyAddress($empresa_ruc));

        // --- Construir Cliente ---
        $clienteTipo = (strlen($factura->cliente_ruc) == 11) ? '6' : '1';
        $client = (new Client())
            ->setTipoDoc($clienteTipo)
            ->setNumDoc($factura->cliente_ruc)
            ->setRznSocial($factura->cliente_nombre);

        // --- Construir Factura ---
        $serieFa = 'FA' . $serie;
        $total = $factura->total;

        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc('01')
            ->setSerie($serieFa)
            ->setCorrelativo($numero)
            ->setFechaEmision(new \DateTime($factura->fecha_emision))
            ->setFormaPago(new FormaPagoContado())
            ->setTipoMoneda('PEN')
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperExoneradas($total)
            ->setMtoIGV(0)
            ->setTotalImpuestos(0)
            ->setValorVenta($total)
            ->setSubTotal($total)
            ->setMtoImpVenta($total);

        // --- Construir Detalle (items) ---
        $items = [];
        foreach ($detalles as $d) {
            $valorUnitario = (float)$d->precio_unitario;
            $totalItem = (float)$d->total_item;

            $item = (new SaleDetail())
                ->setCodProducto($d->cod_producto)
                ->setUnidad('NIU')
                ->setCantidad((float)$d->cantidad)
                ->setDescripcion($d->nombre_producto)
                ->setMtoValorUnitario($valorUnitario)
                ->setMtoBaseIgv($totalItem)
                ->setPorcentajeIgv(0)
                ->setIgv(0)
                ->setTipAfeIgv('20')
                ->setTotalImpuestos(0)
                ->setMtoValorVenta($totalItem)
                ->setMtoPrecioUnitario($valorUnitario);
            $items[] = $item;
        }

        if (empty($items)) {
            // Si no hay detalle, crear un item genérico
            $item = (new SaleDetail())
                ->setCodProducto('000000')
                ->setUnidad('NIU')
                ->setCantidad(1)
                ->setDescripcion('VENTA')
                ->setMtoValorUnitario($total)
                ->setMtoBaseIgv($total)
                ->setPorcentajeIgv(0)
                ->setIgv(0)
                ->setTipAfeIgv('20')
                ->setTotalImpuestos(0)
                ->setMtoValorVenta($total)
                ->setMtoPrecioUnitario($total);
            $items[] = $item;
        }
        $invoice->setDetails($items);

        // --- Enviar a SUNAT ---
        $see = $this->getSee($empresa_ruc);
        $result = $see->send($invoice);

        $xmlPath = WRITEPATH . 'uploads/' . $invoice->getName() . '.xml';
        file_put_contents($xmlPath, $see->getFactory()->getLastXml());

        if (!$result->isSuccess()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $result->getError()->getMessage(),
                'code' => $result->getError()->getCode()
            ]);
        }

        $cdr = $result->getCdrResponse();
        $codigo = (int)$cdr->getCode();
        $descripcion = $cdr->getDescription();

        $estado = 'EXCEPCION';
        if ($codigo === 0) {
            $estado = 'ACEPTADA';
            $descripcion = $cdr->getDescription();
        } else if ($codigo >= 2000 && $codigo <= 3999) {
            $estado = 'RECHAZADA';
        }

        $cdrPath = WRITEPATH . 'uploads/R-' . $invoice->getName() . '.zip';
        file_put_contents($cdrPath, $result->getCdrZip());

        // Registrar en BD
        $logModel->insertComprobante([
            'empresa_ruc'  => $empresa_ruc,
            'tipo_doc'     => '01',
            'serie'        => $serieFa,
            'correlativo'  => (int)$numero,
            'fecha_emision'=> $factura->fecha_emision,
            'xml_path'     => $xmlPath,
            'estado_sunat' => $estado,
            'cdr_path'     => $cdrPath,
            'mensaje_sunat'=> mb_substr($descripcion, 0, 1000)
        ]);

        return $this->response->setJSON([
            'status'      => 'success',
            'estado'      => $estado,
            'descripcion' => $descripcion
        ]);
    }
}
