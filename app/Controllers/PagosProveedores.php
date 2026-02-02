<?php

namespace App\Controllers;

use App\Models\CarteraModel;
use App\Models\ClientesModel;
use App\Models\AllogModel;
use App\Models\CaracuModel;
use App\Models\PlanCuentaModel;
use App\Models\EgresoModel;
use App\Models\CajaMovimientosModel;
use App\Models\VemaestModel;

class PagosProveedores extends BaseController
{
    /**
     * Tasa de interés moratorio anual (5%)
     */
    private $tasaInteresAnual = 0.05;
    
    /**
     * Listado de facturas pendientes de pago
     */
    public function index()
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('ver_facturas')) {
            return redirect()->to('/')->with('error', 'No tiene permisos para acceder a esta sección');
        }
        
        $data = [
            'menu' => ['p' => 30, 'i' => 36], // Operaciones -> Pagos a Proveedores
            'titulo' => 'Cuentas por Pagar'
        ];
        
        // Obtener filtros
        $proveedor = $this->request->getGet('proveedor');
        $fechaDesde = $this->request->getGet('fecha_desde');
        $fechaHasta = $this->request->getGet('fecha_hasta');
        $local = $this->request->getGet('local') ?? $session->get('caja') ?? 1;
        $estado = $this->request->getGet('estado'); // vencidas, por_vencer, todas
        
        // Obtener facturas pendientes
        $carteraModel = new CarteraModel();
        $facturas = $carteraModel->getFacturasPendientesProveedores($proveedor, $fechaDesde, $fechaHasta, $local, $estado);
        
        // Calcular intereses proyectados para cada factura
        foreach ($facturas as &$factura) {
            if ($factura['dias_mora'] > 0) {
                $factura['interes_proyectado'] = $this->calcularInteresMoratorio(
                    $factura['saldo_pendiente'],
                    $factura['fecha_vencimiento'],
                    date('Y-m-d')
                );
                $factura['total_a_pagar'] = $factura['saldo_pendiente'] + $factura['interes_proyectado'];
            } else {
                $factura['interes_proyectado'] = 0;
                $factura['total_a_pagar'] = $factura['saldo_pendiente'];
            }
        }
        
        $data['facturas'] = $facturas;
        $data['filtros'] = [
            'proveedor' => $proveedor,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'local' => $local,
            'estado' => $estado
        ];
        
        // Obtener lista de proveedores para filtro
        $clientesModel = new ClientesModel();
        $data['proveedores'] = $clientesModel->getProveedoresActivos();
        
        return view('pagos_proveedores/index', $data);
    }
    
    /**
     * Mostrar formulario para registrar pago de una factura
     */
    public function pagar($numfac)
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('pagar_facturas')) {
            return redirect()->to('/pagosproveedores')->with('error', 'No tiene permisos para realizar pagos');
        }
        
        $carteraModel = new CarteraModel();
        $clientesModel = new ClientesModel();
        
        // Obtener información de la factura
        $factura = $carteraModel->getFacturaByNumero($numfac, 'P'); // 'P' para proveedores
        
        if (!$factura) {
            return redirect()->to('/pagosproveedores')->with('error', 'Factura no encontrada');
        }
        
        if ($factura['car_importe'] <= 0) {
            return redirect()->to('/pagosproveedores')->with('error', 'La factura ya está pagada');
        }
        
        // Obtener información del proveedor
        // Usamos consulta personalizada porque la tabla CLIENTES no tiene columna 'id'
        $proveedorResult = $clientesModel->get_personas(null, $factura['car_CODCLIE'], null, 'P', 'completo', false);
        $proveedor = null;
        if (!empty($proveedorResult)) {
            // Convertir objeto a array y keys a minúsculas para compatibilidad con vista
            $proveedorArray = (array) $proveedorResult[0];
            $proveedor = [];
            foreach ($proveedorArray as $key => $value) {
                $proveedor[strtolower($key)] = $value;
            }
        }
        
        // Calcular días de mora e interés
        $fechaHoy = date('Y-m-d');
        $diasMora = max(0, (strtotime($fechaHoy) - strtotime($factura['car_fecha_vcto'])) / 86400);
        $interesCalculado = $this->calcularInteresMoratorio(
            $factura['car_importe'],
            $factura['car_fecha_vcto'],
            $fechaHoy
        );
        
        $data = [
            'menu' => ['p' => 30, 'i' => 36],
            'titulo' => 'Registrar Pago a Proveedor',
            'factura' => $factura,
            'proveedor' => $proveedor,
            'dias_mora' => $diasMora,
            'interes_calculado' => $interesCalculado,
            'total_a_pagar' => $factura['car_importe'] + $interesCalculado,
            'locales' => [
                1 => 'Centro',
                2 => 'Juanjuicillo',
                3 => 'Peñameza'
            ],
            'formas_pago' => [
                'EFECTIVO' => 'Efectivo',
                'TRANSFERENCIA' => 'Transferencia Bancaria',
                'TARJETA' => 'Tarjeta de Crédito/Débito'
            ]
        ];
        
        return view('pagos_proveedores/pagar', $data);
    }
    
    /**
     * Procesar el pago de una factura (con o sin intereses)
     */
    public function procesarPago()
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('pagar_facturas')) {
            return redirect()->to('/pagosproveedores')->with('error', 'No tiene permisos para realizar pagos');
        }
        
        // Validar datos
        $validation = \Config\Services::validation();
        $validation->setRules([
            'numfac' => 'required|integer',
            'monto_capital' => 'required|decimal|greater_than[0]',
            'monto_interes' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'pagar_interes' => 'permit_empty|in_list[0,1]',
            'forma_pago' => 'required|in_list[EFECTIVO,TRANSFERENCIA,TARJETA]',
            'local' => 'required|in_list[1,2,3]',
            'fecha_pago' => 'required|valid_date'
        ]);
        
        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        // Obtener datos del formulario
        $numfac = $this->request->getPost('numfac');
        $montoCapital = (float) $this->request->getPost('monto_capital');
        $montoInteres = (float) $this->request->getPost('monto_interes') ?? 0;
        $pagarInteres = (bool) $this->request->getPost('pagar_interes');
        $formaPago = $this->request->getPost('forma_pago');
        $local = (int) $this->request->getPost('local');
        $fechaPago = $this->request->getPost('fecha_pago');
        $fechaPagoFormat = date('d/m/Y', strtotime($fechaPago));
        $observaciones = $this->request->getPost('observaciones');
        $usuario = $session->get('user_id');
        
        // Iniciar transacción
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // 1. Validar factura y saldo
            $carteraModel = new CarteraModel();
            $factura = $carteraModel->getFacturaByNumero($numfac, 'P');
            
            if (!$factura) {
                throw new \Exception('Factura no encontrada');
            }
            
            if ($factura['car_importe'] < $montoCapital) {
                throw new \Exception('El monto a pagar excede el saldo pendiente');
            }
            
            // 2. Obtener número de operación
            $allogModel = new AllogModel();
            $numOperacion = $allogModel->obtenerNuevoNumOperacion($fechaPago);
            
            // 3. Actualizar CARTERA
            $nuevoSaldo = $factura['car_importe'] - $montoCapital;
            $carteraModel->actualizarSaldoFactura($numfac, $nuevoSaldo, $factura['car_CODCLIE'], $factura['CAR_CODCIA'], $factura['cp'] ?? 'P');
            
            // 4. Registrar en CARACU (historial)
            $caracuModel = new CaracuModel();
            // Construir concepto similar a VB: "FA./ 1-8521 FA./ 1-8521"
            $serie = $factura['car_SERDOC'] ?? 0;
            $numfac = $factura['car_NUMFAC'] ?? $factura['car_NUMDOC'];
            $concepto = "FA./ {$serie}-{$numfac} FA./ {$serie}-{$numfac}";
            
            $caracuData = [
                'CAA_CP' => $factura['cp'] ?? 'P',
                'CAA_CODCLIE' => $factura['car_CODCLIE'],
                'CAA_CODCIA' => $factura['CAR_CODCIA'],
                'CAA_TIPDOC' => !empty($factura['CAR_TIPDOC']) ? $factura['CAR_TIPDOC'] : 'FA',
                'CAA_FECHA' => $fechaPagoFormat,
                'CAA_NUM_OPER' => $numOperacion,
                'CAA_SERDOC' => $factura['car_SERDOC'],
                'CAA_NUMDOC' => $factura['car_NUMDOC'],
                'CAA_IMPORTE' => -$montoCapital, // Negativo porque es pago (reducción)
                'CAA_SALDO_CAR' => $nuevoSaldo,
                'CAA_NUMFAC' => $factura['car_NUMFAC'],
                'CAA_SIGNO_CAR' => -1,
                'CAA_CODTRA' => 5360,
                'CAA_CODUSU' => $usuario,
                'CAA_NUMSER' => $factura['car_SERDOC'], // Mismo que serie documento
                'CAA_CONCEPTO' => $concepto
            ];
            log_message('debug', 'CARACU data: ' . print_r($caracuData, true));
            $caracuModel->registrarMovimiento($caracuData);
            
            // 5. Registrar en ALLOG (auditoría)
            $allogData = [
                'ALL_CODCIA' => $factura['CAR_CODCIA'],
                'ALL_FECHA_DIA' => $fechaPagoFormat,
                'ALL_FECHA_PRO' => $fechaPagoFormat,
                'ALL_NUMOPER' => $numOperacion,
                'ALL_CODTRA' => 5360,
                'ALL_CODCLIE' => $factura['car_CODCLIE'],
                'ALL_IMPORTE_AMORT' => $montoCapital, // Positivo como en VB
                'ALL_SIGNO_CAR' => -1,
                'ALL_NUMFAC' => $factura['car_NUMFAC'],
                'ALL_CP' => 'P',
                'ALL_CODUSU' => $usuario,
                // Campos adicionales para coincidir con VB
                'ALL_TIPDOC' => !empty($factura['CAR_TIPDOC']) ? $factura['CAR_TIPDOC'] : 'FA',
                'ALL_SERDOC' => $factura['car_SERDOC'],
                'ALL_NUMDOC' => $factura['car_NUMDOC'],
                'ALL_MONEDA_CAJA' => 'S',
                'ALL_MONEDA_CLI' => 'S',
                'ALL_MONEDA_CCM' => ' ',
                'ALL_AUTOCON' => 'Pago de Facturas P:' . $concepto,
                'ALL_FECHA_VCTO' => $factura['car_fecha_vcto'] ? date('d/m/Y', strtotime($factura['car_fecha_vcto'])) . ' 00:00' : null
            ];
            log_message('debug', 'ALLOG data: ' . print_r($allogData, true));
            $allogModel->registrarMovimiento($allogData);
            
            // 6. Si hay interés, registrar en EGRESOS
            $egresoId = null;
            if ($pagarInteres && $montoInteres > 0) {
                $egresoModel = new EgresoModel();
                $egresoId = $egresoModel->registrarInteresMora([
                    'fecha_pago' => $fechaPago,
                    'local' => $local,
                    'descripcion' => "Interés mora Fact. {$numfac} - " . ($factura['proveedor_nombre'] ?? 'Proveedor'),
                    'monto_interes' => $montoInteres,
                    'forma_pago' => $formaPago,
                    'factura_ref' => $numfac,
                    'proveedor_cod' => $factura['car_CODCLIE'],
                    'usuario' => $usuario,
                    'observaciones' => $observaciones,
                    'registrar_caja' => true
                ]);
            }
            
            // 7. Registrar movimiento en CAJA (total del pago)
           /* $cajaMovModel = new CajaMovimientosModel(); 
           Solo registra movimientos de caja vendedor.
            $montoTotalCaja = -($montoCapital + ($pagarInteres ? $montoInteres : 0));
            
            $cajaMovModel->registrarMovimiento([
                'CM_FECHA' => $fechaPagoSql,
                'CM_CAJA_ID' => $local,
                'CM_MOTIVO' => 'PAGO_PROVEEDOR',
                'CM_MONTO' => $montoTotalCaja,
                'CM_DESCRIPCION' => "Pago Fact. {$numfac}" . ($pagarInteres ? " + interés" : ""),
                'CM_USUARIO' => $usuario,
                'CM_REFERENCIA' => "ALL-{$numOperacion}" . ($egresoId ? ", EGR-{$egresoId}" : ""),
                'CM_ESTADO' => 'CONFIRMADO'
            ]);
            */
            // Confirmar transacción
            $db->transComplete();
            
            if ($db->transStatus() === FALSE) {
                throw new \Exception('Error al registrar el pago en la base de datos');
            }
            
            // Registrar log de auditoría
            log_message('info', "Pago registrado: Factura {$numfac}, Monto: {$montoCapital}, Interés: {$montoInteres}, Usuario: {$usuario}");
            
            return redirect()->to('/pagosproveedores')
                           ->with('success', 'Pago registrado correctamente. Nº Operación: ' . $numOperacion);
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en pago proveedor: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Calcular interés moratorio
     */
    private function calcularInteresMoratorio($saldo, $fechaVcto, $fechaPago = null)
    {
        $fechaPago = $fechaPago ?? date('Y-m-d');
        $diasMora = max(0, (strtotime($fechaPago) - strtotime($fechaVcto)) / 86400);
        
        if ($diasMora <= 0) {
            return 0;
        }
        
        $interes = $saldo * $this->tasaInteresAnual * ($diasMora / 365);
        return round($interes, 2);
    }
    
    /**
     * Verificar permisos del usuario
     */
    private function tienePermiso($accion)
    {
        $session = session();
        $usuario = $session->get('user_id');
        $rol = $session->get('rol'); // Asumir que existe campo rol
        
        // Mapeo de permisos por rol
        $permisos = [
            'ADMIN' => ['ver_facturas', 'pagar_facturas', 'editar_interes'],
            'CONTADOR' => ['ver_facturas', 'pagar_facturas', 'editar_interes'],
            'CAJERO' => ['ver_facturas', 'pagar_facturas'],
            'VENDEDOR' => ['ver_facturas']
        ];
        
        $permisosRol = $permisos[$rol] ?? [];
        return in_array($accion, $permisosRol);
    }
    
    /**
     * API: Obtener cálculo de interés en tiempo real
     */
    public function calcularInteres()
    {
        $saldo = (float) $this->request->getGet('saldo');
        $fechaVcto = $this->request->getGet('fecha_vcto');
        $fechaPago = $this->request->getGet('fecha_pago') ?? date('Y-m-d');
        
        if (!$saldo || !$fechaVcto) {
            return $this->response->setJSON(['error' => 'Parámetros incompletos']);
        }
        
        $interes = $this->calcularInteresMoratorio($saldo, $fechaVcto, $fechaPago);
        $diasMora = max(0, (strtotime($fechaPago) - strtotime($fechaVcto)) / 86400);
        
        return $this->response->setJSON([
            'dias_mora' => (int) $diasMora,
            'interes_calculado' => $interes,
            'total_a_pagar' => $saldo + $interes,
            'tasa_anual' => ($this->tasaInteresAnual * 100) . '%'
        ]);
    }
    
    /**
     * Reporte de intereses moratorios pagados
     */
    public function reporteIntereses()
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('ver_facturas')) {
            return redirect()->to('/')->with('error', 'No tiene permisos para acceder a esta sección');
        }
        
        $fechaDesde = $this->request->getGet('fecha_desde') ?? date('Y-m-01');
        $fechaHasta = $this->request->getGet('fecha_hasta') ?? date('Y-m-d');
        $proveedor = $this->request->getGet('proveedor');
        
        $egresoModel = new EgresoModel();
        $intereses = $egresoModel->getInteresesMoratorios($fechaDesde, $fechaHasta, $proveedor);
        
        $data = [
            'menu' => ['p' => 30, 'i' => 36],
            'titulo' => 'Reporte de Intereses Moratorios',
            'intereses' => $intereses,
            'filtros' => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'proveedor' => $proveedor
            ],
            'total_intereses' => array_sum(array_column($intereses, 'EGR_MONTO'))
        ];
        
        // Obtener lista de proveedores para filtro
        $clientesModel = new ClientesModel();
        $data['proveedores'] = $clientesModel->getProveedoresActivos();
        
        return view('pagos_proveedores/reporte_intereses', $data);
    }
    
    /**
     * Exportar reporte a Excel
     */
    public function exportarExcel()
    {
        $fechaDesde = $this->request->getGet('fecha_desde') ?? date('Y-m-01');
        $fechaHasta = $this->request->getGet('fecha_hasta') ?? date('Y-m-d');
        $tipo = $this->request->getGet('tipo'); // intereses, pagos, etc.
        
        // Implementar exportación a Excel usando PhpSpreadsheet
        // Por ahora solo redirigimos
        return redirect()->back()->with('info', 'Funcionalidad de exportación en desarrollo');
    }
}