<?php

namespace App\Controllers;

use App\Models\PlanCuentaModel;
use App\Models\EgresoModel;
use App\Models\CajaMovimientosModel;

class Egresos extends BaseController
{
    /**
     * Listado de egresos registrados
     */
    public function index()
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('ver_egresos')) {
            return redirect()->to('/')->with('error', 'No tiene permisos para acceder a esta sección');
        }
        
        $data = [
            'menu' => ['p' => 30, 'i' => 37], // Operaciones -> Gastos y Egresos
            'titulo' => 'Registro de Gastos y Egresos'
        ];
        
        // Obtener filtros
        $fechaDesde = $this->request->getGet('fecha_desde') ?? date('Y-m-01');
        $fechaHasta = $this->request->getGet('fecha_hasta') ?? date('Y-m-d');
        $local = $this->request->getGet('local') ?? $session->get('caja') ?? 1;
        $cuentaId = $this->request->getGet('cuenta_id');
        $estado = $this->request->getGet('estado');
        
        // Obtener egresos
        $egresoModel = new EgresoModel();
        $filters = [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'local' => $local,
            'cuenta_id' => $cuentaId,
            'estado' => $estado
        ];
        $data['egresos'] = $egresoModel->getEgresos($filters);
        
        // Obtener totales
        $data['total_egresos'] = array_sum(array_column($data['egresos'], 'EGR_MONTO'));
        
        // Obtener cuentas para filtro
        $planCuentaModel = new PlanCuentaModel();
        $data['cuentas'] = $planCuentaModel->getOpcionesDropdown('E', true);
        
        $data['filtros'] = [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'local' => $local,
            'cuenta_id' => $cuentaId,
            'estado' => $estado
        ];
        
        return view('egresos/index', $data);
    }
    
    /**
     * Mostrar formulario para registrar un nuevo egreso
     */
    public function crear()
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('crear_egresos')) {
            return redirect()->to('/egresos')->with('error', 'No tiene permisos para crear egresos');
        }
        
        $planCuentaModel = new PlanCuentaModel();
        
        $data = [
            'menu' => ['p' => 30, 'i' => 37],
            'titulo' => 'Registrar Nuevo Gasto',
            'cuentas' => $planCuentaModel->getOpcionesDropdown('E', false),
            'locales' => [
                1 => 'Local 1',
                2 => 'Local 2', 
                3 => 'Local 3'
            ],
            'formas_pago' => [
                'EFECTIVO' => 'Efectivo',
                'TARJETA' => 'Tarjeta',
                'TRANSFERENCIA' => 'Transferencia',
                'CHEQUE' => 'Cheque'
            ],
            'comprobantes_tipo' => [
                'FACTURA' => 'Factura',
                'BOLETA' => 'Boleta',
                'RECIBO' => 'Recibo',
                'TICKET' => 'Ticket'
            ]
        ];
        
        return view('egresos/crear', $data);
    }
    
    /**
     * Procesar registro de nuevo egreso
     */
    public function guardar()
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('crear_egresos')) {
            return redirect()->to('/egresos')->with('error', 'No tiene permisos para crear egresos');
        }
        
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'fecha' => 'required|valid_date',
            'local' => 'required|integer|in_list[1,2,3]',
            'cuenta_id' => 'required|integer',
            'descripcion' => 'required|max_length[255]',
            'monto' => 'required|decimal',
            'forma_pago' => 'required|in_list[EFECTIVO,TARJETA,TRANSFERENCIA,CHEQUE]',
            'estado' => 'permit_empty|in_list[pagado,pendiente]'
        ]);
        
        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        $egresoModel = new EgresoModel();
        
        try {
            $fecha = $this->request->getPost('fecha');
            if ($fecha) {
                // Convertir Y-m-d a d-m-Y si es necesario
                $timestamp = strtotime($fecha);
                if ($timestamp !== false) {
                    $fecha = date('d-m-Y', $timestamp);
                }
            }

            $egresoId = $egresoModel->registrarEgresoNormal([
                'fecha' => $fecha,
                'local' => $this->request->getPost('local'),
                'cuenta_id' => $this->request->getPost('cuenta_id'),
                'descripcion' => $this->request->getPost('descripcion'),
                'monto' => $this->request->getPost('monto'),
                'forma_pago' => $this->request->getPost('forma_pago'),
                'estado' => $this->request->getPost('estado') ?? 'pagado',
                'comprobante_tipo' => $this->request->getPost('comprobante_tipo'),
                'comprobante_serie' => $this->request->getPost('comprobante_serie'),
                'comprobante_numero' => $this->request->getPost('comprobante_numero'),
                'responsable' => $this->request->getPost('responsable'),
                'usuario' => $session->get('user_id'),
                'observaciones' => $this->request->getPost('observaciones'),
                'registrar_caja' => true
            ]);
            
            return redirect()->to('/egresos')->with('success', "Egreso registrado exitosamente (ID: {$egresoId})");
            
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al registrar el egreso: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostrar formulario para editar un egreso existente
     */
    public function editar($egresoId)
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('editar_egresos')) {
            return redirect()->to('/egresos')->with('error', 'No tiene permisos para editar egresos');
        }
        
        $egresoModel = new EgresoModel();
        $planCuentaModel = new PlanCuentaModel();
        
        $egreso = $egresoModel->find($egresoId);
        
        if (!$egreso) {
            return redirect()->to('/egresos')->with('error', 'Egreso no encontrado');
        }
        
        $data = [
            'menu' => ['p' => 30, 'i' => 37],
            'titulo' => 'Editar Egreso',
            'egreso' => $egreso,
            'cuentas' => $planCuentaModel->getOpcionesDropdown('E', false),
            'locales' => [
                1 => 'Local 1',
                2 => 'Local 2', 
                3 => 'Local 3'
            ],
            'formas_pago' => [
                'EFECTIVO' => 'Efectivo',
                'TARJETA' => 'Tarjeta',
                'TRANSFERENCIA' => 'Transferencia',
                'CHEQUE' => 'Cheque'
            ],
            'comprobantes_tipo' => [
                'FACTURA' => 'Factura',
                'BOLETA' => 'Boleta',
                'RECIBO' => 'Recibo',
                'TICKET' => 'Ticket'
            ],
            'estados' => [
                'pagado' => 'Pagado',
                'pendiente' => 'Pendiente'
            ]
        ];
        
        return view('egresos/editar', $data);
    }
    
    /**
     * Procesar actualización de egreso
     */
    public function actualizar($egresoId)
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('editar_egresos')) {
            return redirect()->to('/egresos')->with('error', 'No tiene permisos para editar egresos');
        }
        
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'fecha' => 'required|valid_date',
            'local' => 'required|integer|in_list[1,2,3]',
            'cuenta_id' => 'required|integer',
            'descripcion' => 'required|max_length[255]',
            'monto' => 'required|decimal',
            'forma_pago' => 'required|in_list[EFECTIVO,TARJETA,TRANSFERENCIA,CHEQUE]',
            'estado' => 'required|in_list[pagado,pendiente]'
        ]);
        
        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        $egresoModel = new EgresoModel();
        
        // Verificar que el egreso exista
        $egreso = $egresoModel->find($egresoId);
        if (!$egreso) {
            return redirect()->to('/egresos')->with('error', 'Egreso no encontrado');
        }
        
        // Actualizar datos básicos
        $fecha = $this->request->getPost('fecha');
        if ($fecha) {
            $timestamp = strtotime($fecha);
            if ($timestamp !== false) {
                $fecha = date('d-m-Y', $timestamp);
            }
        }

        $updateData = [
            'EGR_FECHA' => $fecha,
            'EGR_LOCAL' => $this->request->getPost('local'),
            'EGR_CUENTA_ID' => $this->request->getPost('cuenta_id'),
            'EGR_DESCRIPCION' => $this->request->getPost('descripcion'),
            'EGR_MONTO' => $this->request->getPost('monto'),
            'EGR_FORMA_PAGO' => $this->request->getPost('forma_pago'),
            'EGR_ESTADO' => $this->request->getPost('estado'),
            'EGR_COMPROBANTE_TIPO' => $this->request->getPost('comprobante_tipo'),
            'EGR_COMPROBANTE_SERIE' => $this->request->getPost('comprobante_serie'),
            'EGR_COMPROBANTE_NUMERO' => $this->request->getPost('comprobante_numero'),
            'EGR_RESPONSABLE' => $this->request->getPost('responsable'),
            'EGR_OBSERVACIONES' => $this->request->getPost('observaciones'),
            'EGR_USUARIO' => $session->get('user_id'),
            'EGR_FECHA_REGISTRO' => date('d-m-Y H:i:s')
        ];
        
        // Si el estado cambia a pagado y antes estaba pendiente, registrar movimiento en caja
        if ($updateData['EGR_ESTADO'] === 'pagado' && $egreso['EGR_ESTADO'] === 'pendiente') {
            // Registrar movimiento en caja
            $cajaMovModel = new CajaMovimientosModel();
            $movimientoId = $cajaMovModel->registrarMovimiento([
                'CM_FECHA' => $updateData['EGR_FECHA'],
                'CM_CAJA_ID' => $updateData['EGR_LOCAL'],
                'CM_MOTIVO' => 'NORMAL',
                'CM_MONTO' => -$updateData['EGR_MONTO'],
                'CM_DESCRIPCION' => $updateData['EGR_DESCRIPCION'],
                'CM_USUARIO' => $updateData['EGR_USUARIO'],
                'CM_REFERENCIA' => "EGR-{$egresoId}",
                'CM_ESTADO' => 'CONFIRMADO'
            ], $updateData['EGR_LOCAL']);
            
            $updateData['EGR_CAJA_MOV_ID'] = $movimientoId;
        }
        
        // Si el estado cambia a pendiente y antes estaba pagado, eliminar movimiento en caja (opcional)
        if ($updateData['EGR_ESTADO'] === 'pendiente' && $egreso['EGR_ESTADO'] === 'pagado' && !empty($egreso['EGR_CAJA_MOV_ID'])) {
            // Opcional: marcar movimiento como anulado
            // Por simplicidad, no eliminamos el movimiento, solo quitamos la referencia
            $updateData['EGR_CAJA_MOV_ID'] = null;
        }
        
        $egresoModel->update($egresoId, $updateData);
        
        return redirect()->to('/egresos')->with('success', 'Egreso actualizado exitosamente');
    }
    
    /**
     * Procesar actualización masiva de cuenta de gasto
     */
    public function actualizarCuentasMasivo()
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('editar_egresos')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tiene permisos para realizar esta acción'
            ]);
        }
        
        $ids = $this->request->getPost('ids');
        $cuentaId = $this->request->getPost('cuenta_id');
        
        if (empty($ids) || empty($cuentaId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Faltan datos requeridos (IDs o Cuenta)'
            ]);
        }
        
        $egresoModel = new EgresoModel();
        
        try {
            $result = $egresoModel->actualizarCuentaMasivo($ids, $cuentaId);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Se han actualizado ' . count($ids) . ' egresos exitosamente'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se pudieron actualizar los registros'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Eliminar un egreso (solo si no tiene movimientos relacionados)
     */
    public function eliminar($egresoId)
    {
        $session = session();
        
        // Verificar permisos
        if (!$this->tienePermiso('eliminar_egresos')) {
            return redirect()->to('/egresos')->with('error', 'No tiene permisos para eliminar egresos');
        }
        
        $egresoModel = new EgresoModel();
        $egreso = $egresoModel->find($egresoId);
        
        if (!$egreso) {
            return redirect()->to('/egresos')->with('error', 'Egreso no encontrado');
        }
        
        // Verificar si ya tiene movimiento de caja registrado
        if (!empty($egreso['EGR_CAJA_MOV_ID'])) {
            return redirect()->to('/egresos')->with('error', 'No se puede eliminar un egreso con movimiento de caja registrado');
        }
        
        $egresoModel->delete($egresoId);
        
        return redirect()->to('/egresos')->with('success', 'Egreso eliminado exitosamente');
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
            'ADMIN' => ['ver_egresos', 'crear_egresos', 'editar_egresos', 'eliminar_egresos'],
            'CONTADOR' => ['ver_egresos', 'crear_egresos', 'editar_egresos', 'eliminar_egresos'],
            'CAJERO' => ['ver_egresos', 'crear_egresos', 'editar_egresos'],
            'VENDEDOR' => ['ver_egresos']
        ];
        
        $permisosRol = $permisos[$rol] ?? [];
        return in_array($accion, $permisosRol);
    }
}