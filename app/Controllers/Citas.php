<?php

namespace App\Controllers;

use App\Models\CitasModel;
use App\Models\CitaServicioModel;
use App\Models\CitaPagoModel;
use App\Models\CampaniaModel;
use App\Models\MedioModel;
use App\Models\ClientesModel;

/**
 * Controlador para gestión de citas con servicios y pagos
 */
class Citas extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Vista principal de gestión de citas
     */
    public function index()
    {
        $campaniaId = $this->request->uri->getSegment(3);
        
        $campaniaModel = new CampaniaModel();
        $campanias = $campaniaModel->get_campanias();
        
        $medioModel = new MedioModel();
        $medios = $medioModel->get_medios();

        $data = [
            'campanias' => $campanias,
            'medios' => $medios,
            'campanias_cod' => $campaniaId,
            'menu' => ['p' => 40, 'i' => 41]
        ];

        return view('consultorio/citas_servicios', $data);
    }

    /**
     * Obtener lista de citas con servicios y pagos (AJAX)
     */
    public function getCitasConServicios()
    {
        try {
            $campaniaId = $this->request->getVar('campania');
            $estado = $this->request->getVar('estado');
            $fechaInicio = $this->request->getVar('fecha_inicio');
            $fechaFin = $this->request->getVar('fecha_fin');

            $citasModel = new CitasModel();
            $citas = $citasModel->getCitasConServicios($campaniaId, $estado, $fechaInicio, $fechaFin);

            return $this->response->setJSON([
                'success' => true,
                'data' => $citas
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener información detallada de una cita (AJAX)
     */
    public function getCitaDetalle()
    {
        try {
            $citaId = $this->request->getPost('cita_id');
            
            if (empty($citaId) || !is_numeric($citaId)) {
                throw new \Exception('ID de cita no válido');
            }
            
            $citasModel = new CitasModel();
            $serviciosModel = new CitaServicioModel();
            $pagosModel = new CitaPagoModel();
            
            // Obtener información de la cita
            $cita = $citasModel->getCitaExtendida((int)$citaId);
            
            if (!$cita) {
                throw new \Exception('Cita no encontrada');
            }

            // Obtener servicios
            $servicios = $serviciosModel->getServiciosByCita((int)$citaId);
            
            // Obtener pagos
            $pagos = $pagosModel->getPagosByCita((int)$citaId);
            
            // Calcular totales
            $totalServicios = $serviciosModel->getTotalServicios((int)$citaId);
            $totalPendiente = $serviciosModel->getTotalServiciosPendientes((int)$citaId);
            $totalPagado = $pagosModel->getTotalPagado((int)$citaId);
            $saldo = $totalServicios - $totalPagado;

            return $this->response->setJSON([
                'success' => true,
                'cita' => $cita,
                'servicios' => $servicios,
                'pagos' => $pagos,
                'totales' => [
                    'total_servicios' => $totalServicios,
                    'total_pendiente' => $totalPendiente,
                    'total_pagado' => $totalPagado,
                    'saldo' => $saldo
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Crear nueva cita (AJAX) 
     */
    public function crearCita()
    {
        try {
            $clienteId = $this->request->getPost('cliente_id');
            $campaniaId = $this->request->getPost('campania_id');
            $medioId = $this->request->getPost('medio_id');
            $fecha = $this->request->getPost('fecha');
            $hora = $this->request->getPost('hora');
            $observaciones = $this->request->getPost('observaciones') ?: '';
            $localOrigen = $this->request->getPost('local_origen') ?: '01';

            // Validaciones básicas
            if (empty($clienteId) || empty($campaniaId) || empty($medioId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Faltan datos obligatorios: cliente, campaña y medio'
                ]);
            }

            // Obtener orden consecutivo
            $citasModel = new CitasModel();
            $orden = $citasModel->get_orden($campaniaId);

            // Datos completos según estructura de tabla (todos los campos NOT NULL)
            $data = [
                'CIT_CODCLIE' => (int)$clienteId,
                'CIT_CODCAMP' => (int)$campaniaId,
                'CIT_ORD' => $orden ?: 1,
                'CIT_ORD_ATENCION' => 0,
                'CIT_PAGO' => 0, // bit NOT NULL - 0 = sin pago
                'CIT_SERIE' => 0, // int NOT NULL - 0 = sin serie
                'CIT_NUMERO' => 0, // int NOT NULL - 0 = sin número
                'CIT_CODMEDIO' => (int)$medioId,
                'CIT_ESTADO' => 0, // 0 = INSCRITO
                'CIT_FECHA' => $fecha ?: date('Y-m-d H:i:s'), // datetime NOT NULL
                'CIT_HORA' => $hora ?: date('H:i:s'), // varchar(8)
                'CIT_OBSERVACIONES' => $observaciones, // varchar(500) NULL
                'CIT_LOCAL_ORIGEN' => $localOrigen, // char(2) NOT NULL 
                'CIT_TOTAL' => 0.00, // numeric(11, 2) NOT NULL
                'CIT_SALDO' => 0.00  // numeric(11, 2) NOT NULL
            ];

            // Usar insert directo en lugar del método que puede tener conflictos
            $result = $this->db->table('CITAS')->insert($data);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Cita creada exitosamente'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Error al insertar la cita en la base de datos'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Buscar servicios para agregar a cita (AJAX)
     */
    public function buscarServicios()
    {
        try {
            $busqueda = $this->request->getVar('busqueda');
            $serviciosModel = new CitaServicioModel();
            $servicios = $serviciosModel->buscarServicios($busqueda);

            return $this->response->setJSON([
                'success' => true,
                'data' => $servicios
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Agregar servicio a una cita (AJAX)
     */
    public function agregarServicio()
    {
        try {
            // Obtener datos del POST de forma segura
            $citaId = $this->request->getPost('cita_id');
            $articuloId = $this->request->getPost('articulo_id');
            $cantidad = $this->request->getPost('cantidad') ?: 1;
            $precio = $this->request->getPost('precio');
            $observaciones = $this->request->getPost('observaciones') ?: '';

            // Validaciones básicas
            if (empty($citaId) || empty($articuloId) || empty($precio)) {
                throw new \Exception('Datos incompletos para agregar el servicio');
            }

            if (!is_numeric($citaId) || !is_numeric($articuloId) || !is_numeric($cantidad) || !is_numeric($precio)) {
                throw new \Exception('Los datos numéricos no son válidos');
            }

            $data = [
                'CNS_CODCIT' => (int)$citaId,
                'CNS_CODART' => (int)$articuloId,
                'CNS_CANTIDAD' => (int)$cantidad,
                'CNS_PRECIO' => (float)$precio,
                'CNS_OBSERVACIONES' => (string)$observaciones
            ];

            $serviciosModel = new CitaServicioModel();
            $result = $serviciosModel->agregarServicio($data);

            if ($result) {
                // Actualizar totales de la cita
                $this->actualizarTotalesCita($data['CNS_CODCIT']);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Servicio agregado exitosamente'
                ]);
            } else {
                throw new \Exception('Error al insertar el servicio en la base de datos');
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Eliminar servicio de una cita (AJAX)
     */
    public function eliminarServicio()
    {
        try {
            $servicioId = $this->request->getPost('servicio_id');
            $citaId = $this->request->getPost('cita_id');

            if (empty($servicioId) || empty($citaId)) {
                throw new \Exception('Datos incompletos para eliminar el servicio');
            }

            $serviciosModel = new CitaServicioModel();
            $result = $serviciosModel->eliminarServicio((int)$servicioId);

            if ($result) {
                // Actualizar totales de la cita
                $this->actualizarTotalesCita((int)$citaId);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Servicio eliminado exitosamente'
                ]);
            } else {
                throw new \Exception('Error al eliminar el servicio');
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Registrar pago para una cita (AJAX)
     */
    public function registrarPago()
    {
        try {
            $citaId = $this->request->getPost('cita_id');
            $monto = $this->request->getPost('monto');
            $formaPago = $this->request->getPost('forma_pago');
            $referencia = $this->request->getPost('referencia') ?: '';
            $localPago = $this->request->getPost('local_pago');

            // Validaciones
            if (empty($citaId) || empty($monto) || empty($formaPago) || empty($localPago)) {
                throw new \Exception('Datos incompletos para registrar el pago');
            }

            if (!is_numeric($monto) || $monto <= 0) {
                throw new \Exception('El monto debe ser un número positivo');
            }

            $data = [
                'CIT_CODCIT' => (int)$citaId,
                'CTP_MONTO' => (float)$monto,
                'CTP_FORMA_PAGO' => (string)$formaPago,
                'CTP_REFERENCIA' => (string)$referencia,
                'CTP_LOCAL_PAGO' => (string)$localPago,
                'CTP_ESTADO' => 1 // PAGADO
            ];

            $pagosModel = new CitaPagoModel();
            $result = $pagosModel->registrarPago($data);

            if ($result) {
                // Actualizar totales de la cita
                $this->actualizarTotalesCita($data['CIT_CODCIT']);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Pago registrado exitosamente'
                ]);
            } else {
                throw new \Exception('Error al registrar el pago');
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Procesar pago directo - Genera comprobante inmediatamente (AJAX)
     */
    public function procesarPago()
    {
        try {
            $citaId = $this->request->getPost('cita_id');
            $localPago = $this->request->getPost('local_pago');
            $tipoComprobante = $this->request->getPost('tipo_comprobante') ?: 'B';
            $formaPago = $this->request->getPost('forma_pago') ?: 'EFECTIVO';
            $referencia = $this->request->getPost('referencia') ?: '';

            log_message('error', 'DEBUG: Citas Controller: procesarPago iniciado - Cita: ' . $citaId . ', Local: ' . $localPago);

            if (empty($citaId) || empty($localPago)) {
                throw new \Exception('Datos incompletos para procesar el pago');
            }

            log_message('error', 'DEBUG: Citas Controller: Creando CitaPagoModel');
            $pagosModel = new CitaPagoModel();
            
            log_message('error', 'DEBUG: Citas Controller: Llamando procesarPagoCompleto');
            $result = $pagosModel->procesarPagoCompleto($citaId, $localPago, $tipoComprobante, $formaPago, $referencia);
            
            log_message('info', 'Citas Controller: Resultado procesarPagoCompleto: ' . json_encode($result));

            if ($result['success']) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Pago procesado exitosamente',
                    'comprobante' => [
                        'tipo' => $result['tipo'],
                        'serie' => $result['serie'],
                        'numero' => $result['numfac'],
                        'total' => $result['total']
                    ]
                ]);
            } else {
                throw new \Exception($result['error']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Citas Controller: Error en procesarPago - ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generar comprobante para una cita (AJAX) - Método original mantenido
     */
    public function generarComprobante()
    {
        try {
            $citaId = $this->request->getPost('cita_id');
            $localPago = $this->request->getPost('local_pago');
            $tipoComprobante = $this->request->getPost('tipo_comprobante') ?: 'B'; // B=Boleta, F=Factura
            $pagoId = $this->request->getPost('pago_id');

            // Validaciones
            if (empty($citaId) || empty($localPago)) {
                throw new \Exception('Datos incompletos para generar comprobante');
            }

            $pagosModel = new CitaPagoModel();
            $result = $pagosModel->generarComprobante($citaId, $localPago, $tipoComprobante);

            if ($result['success']) {
                // Marcar pago como con comprobante generado
                if ($pagoId) {
                    $pagosModel->actualizarPago($pagoId, [
                        'CTP_GENERO_COMPROBANTE' => 1,
                        'CTP_TIPO_COMPROBANTE' => $tipoComprobante,
                        'CTP_SERIE' => $result['serie'],
                        'CTP_NUMERO' => $result['numfac'],
                        'CTP_CODCIA' => '25'
                    ]);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Comprobante generado exitosamente',
                    'comprobante' => [
                        'tipo' => $tipoComprobante,
                        'serie' => $result['serie'],
                        'numero' => $result['numfac'],
                        'total' => $result['total']
                    ]
                ]);
            } else {
                throw new \Exception($result['error']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Anular pago (AJAX)
     */
    public function anularPago()
    {
        try {
            $pagoId = $this->request->getPost('pago_id');
            $citaId = $this->request->getPost('cita_id');

            if (empty($pagoId) || empty($citaId)) {
                throw new \Exception('Datos incompletos para anular el pago');
            }

            $pagosModel = new CitaPagoModel();
            $result = $pagosModel->anularPago((int)$pagoId);

            if ($result) {
                // Actualizar totales de la cita
                $this->actualizarTotalesCita((int)$citaId);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Pago anulado exitosamente'
                ]);
            } else {
                throw new \Exception('Error al anular el pago');
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cambiar estado de la cita (AJAX)
     */
    public function cambiarEstadoCita()
    {
        try {
            $citaId = $this->request->getPost('cita_id');
            $nuevoEstado = $this->request->getPost('estado');

            if (empty($citaId) || !is_numeric($nuevoEstado)) {
                throw new \Exception('Datos incompletos o inválidos');
            }

            $data = ['CIT_ESTADO' => (int)$nuevoEstado];
            $citasModel = new CitasModel();
            $result = $citasModel->upd_cita((int)$citaId, $data);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Estado actualizado exitosamente'
                ]);
            } else {
                throw new \Exception('Error al actualizar el estado');
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener reporte de pagos (AJAX)
     */
    public function getReportePagos()
    {
        try {
            $fechaInicio = $this->request->getPost('fecha_inicio');
            $fechaFin = $this->request->getPost('fecha_fin');
            $local = $this->request->getPost('local');

            $pagosModel = new CitaPagoModel();
            $pagos = $pagosModel->getReportePagos($fechaInicio, $fechaFin, $local);

            return $this->response->setJSON([
                'success' => true,
                'data' => $pagos
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Función auxiliar para actualizar totales de la cita
     */
    private function actualizarTotalesCita($citaId)
    {
        try {
            // Calcular total de servicios
            $query1 = $this->db->query("SELECT SUM(CNS_CANTIDAD * CNS_PRECIO) as TOTAL FROM CITAS_SERVICIOS WHERE CNS_CODCIT = ?", [$citaId]);
            $totalServicios = $query1->getRow()->TOTAL ?: 0;
            
            // Calcular total pagado
            $query2 = $this->db->query("SELECT SUM(CTP_MONTO) as TOTAL FROM CITAS_PAGOS WHERE CIT_CODCIT = ? AND CTP_ESTADO = 1", [$citaId]);
            $totalPagado = $query2->getRow()->TOTAL ?: 0;
            
            $saldo = $totalServicios - $totalPagado;

            // Actualizar totales en la cita
            $this->db->table('CITAS')->where('CIT_CODCIT', $citaId)->update([
                'CIT_TOTAL' => $totalServicios,
                'CIT_SALDO' => $saldo
            ]);
            
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error actualizando totales de cita ' . $citaId . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vista para reportes de pagos
     */
    public function reportePagos()
    {
        $data = [
            'menu' => ['p' => 40, 'i' => 45]
        ];

        return view('consultorio/reporte_pagos', $data);
    }

    /**
     * Función de compatibilidad con el sistema existente
     * Mantener la funcionalidad original de pacientes
     */
    public function pacientes()
    {
        // Redirigir al nuevo sistema de citas
        return redirect()->to('/consultorio/citas');
    }
}
