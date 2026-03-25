<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ImportFactModel;

class RecepcionProductos extends BaseController
{
    public function index()
    {
        $data['menu']['p'] = 30;
        $data['menu']['i'] = 39;
        return view('recepcion_productos/index', $data);
    }

    public function listarDocumentos()
    {
        $cliente = $this->request->getVar('cliente');
        $startDate = $this->request->getVar('startDate');
        $endDate = $this->request->getVar('endDate');

        $ImportFactModel = new ImportFactModel();
        $documentos = $ImportFactModel->listarDocumentosParaRecepcion($cliente, $startDate, $endDate);
        return $this->response->setJSON($documentos);
    }

    public function obtenerProductos()
    {
        $ids = $this->request->getVar('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Debe seleccionar al menos una factura.'
            ]);
        }

        $ImportFactModel = new ImportFactModel();
        $productos = $ImportFactModel->getDetalleProductosFacturas($ids);
        return $this->response->setJSON([
            'status' => 200,
            'data' => $productos
        ]);
    }

    public function generarReporte()
    {
        $facturas = $this->request->getVar('facturas');
        $proveedor = $this->request->getVar('proveedor');

        if (empty($facturas) || !is_array($facturas)) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Debe seleccionar al menos una factura.'
            ]);
        }

        $ImportFactModel = new ImportFactModel();

        // Datos de la cabecera del reporte
        $dataReporte = [
            'CLI_CODCLIE' => $proveedor['codclie'] ?? '',
            'RUC' => $proveedor['ruc'] ?? '',
            'RAZON_SOCIAL' => $proveedor['nombre'] ?? '',
            'FECHA_RECEPCION' => date('Y-m-d'),
            'USUARIO' => session()->get('user_name') ?? 'SISTEMA'
        ];

        $facturasData = [];
        foreach ($facturas as $f) {
            $facturasData[] = [
                'id' => $f['id'],
                'nro_factura' => $f['nro_factura']
            ];
        }

        $idReporte = $ImportFactModel->crearReporteRecepcion($dataReporte, $facturasData);

        if ($idReporte) {
            // Obtener datos completos para el PDF
            $reporteCompleto = $ImportFactModel->getReporteCompleto($idReporte);
            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Reporte generado exitosamente.',
                'id_reporte' => $idReporte,
                'data' => $reporteCompleto
            ]);
        }

        return $this->response->setJSON([
            'status' => 500,
            'message' => 'Error al generar el reporte.'
        ]);
    }

    public function listarReportes()
    {
        $ImportFactModel = new ImportFactModel();
        $reportes = $ImportFactModel->listarReportesGenerados();
        return $this->response->setJSON($reportes);
    }

    public function anularReporte()
    {
        $id = $this->request->getVar('id');
        if (empty($id)) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'ID de reporte requerido.'
            ]);
        }

        $ImportFactModel = new ImportFactModel();
        $result = $ImportFactModel->anularReporte($id);

        if ($result) {
            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Reporte anulado exitosamente.'
            ]);
        }

        return $this->response->setJSON([
            'status' => 500,
            'message' => 'Error al anular el reporte.'
        ]);
    }

    public function verReporte()
    {
        $id = $this->request->getVar('id');
        if (empty($id)) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'ID de reporte requerido.'
            ]);
        }

        $ImportFactModel = new ImportFactModel();
        $reporteCompleto = $ImportFactModel->getReporteCompleto($id);

        if ($reporteCompleto) {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $reporteCompleto
            ]);
        }

        return $this->response->setJSON([
            'status' => 404,
            'message' => 'Reporte no encontrado.'
        ]);
    }
}
