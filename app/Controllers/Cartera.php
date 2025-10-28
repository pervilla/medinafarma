<?php

namespace App\Controllers;

use App\Models\CarteraModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class Cartera extends BaseController
{
    protected $documentoModel;

    public function __construct()
    {
        $this->documentoModel = new CarteraModel();
    }

    public function index()
    {
        $data['menu']['p'] = 30;
        $data['menu']['i'] = 34;
        return view('cartera/index',$data);
    }

    public function get_documentos()
    {
        try {
            $proveedor = $this->request->getPost('proveedor');
            $fecha_inicio = $this->request->getPost('fecha_inicio'); // formato DD/MM/YYYY
            $fecha_fin = $this->request->getPost('fecha_fin');       // formato DD/MM/YYYY
            $filtro_estado = $this->request->getPost('estado');     // 'PENDIENTE' o 'PAGADO'
    
            $data = $this->documentoModel->getDocumentos(
                $proveedor,
                $fecha_inicio,
                $fecha_fin,
                $filtro_estado
            );
    
            $draw = $this->request->getPost('draw') ? intval($this->request->getPost('draw')) : 1;
    
            echo json_encode([
                "draw" => $draw,
                "recordsTotal" => count($data),
                "recordsFiltered" => count($data),
                "data" => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function imprimir($id)
    {
        $documento = $this->documentoModel->getDocumentoById($id);
        return view('cartera/imprimir', ['doc' => $documento]);
    }

    public function exportar($formato)
    {
        try {
            // Aumentar memoria y tiempo si es necesario
            ini_set('memory_limit', '256M');
            set_time_limit(0);
    
            // Obtener parámetros del filtro
            $proveedor = $this->request->getVar('proveedor');
            $fecha_inicio = $this->request->getVar('fecha_inicio'); // DD/MM/YYYY
            $fecha_fin = $this->request->getVar('fecha_fin');       // DD/MM/YYYY
            $estado = $this->request->getVar('estado');             // PENDIENTE o PAGADO
    
            // Obtener datos filtrados (ej. 20 registros como máximo)
            $data = $this->documentoModel->getDocumentos($proveedor, $fecha_inicio, $fecha_fin, $estado);
    
            if ($formato === 'excel') {
                return $this->exportarExcel($data);
            } elseif ($formato === 'pdf') {
                return $this->exportarPdf($data);
            }
    
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            die("Error al generar el archivo: " . $e->getMessage());
        }
    }
    

    private function exportarExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Documento');
        $sheet->setCellValue('B1', 'Tipo');
        $sheet->setCellValue('C1', 'Fecha Ingreso');
        $sheet->setCellValue('D1', 'Vencimiento');
        $sheet->setCellValue('E1', 'Monto');
        $sheet->setCellValue('F1', 'Estado');
        $sheet->setCellValue('G1', 'Proveedor');

        $row = 2;
        foreach ($data as $item) {
            $nro = $item['CAR_NUMSER_C'].'-'.$item['CAR_NUMFAC_C'];
            $sheet->setCellValue('A' . $row, $nro );
            $sheet->setCellValue('B' . $row, $item['CAR_TIPDOC']);
            $sheet->setCellValue('C' . $row, $item['CAR_FECHA_INGR']);
            $sheet->setCellValue('D' . $row, $item['CAR_FECHA_VCTO']);
            $sheet->setCellValue('E' . $row, $item['CAR_IMPORTE']);
            $sheet->setCellValue('F' . $row, $item['ESTADO']);
            $sheet->setCellValue('G' . $row, $item['CLI_NOMBRE']);
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="reporte_facturas.xlsx"');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }

    public function exportarPdf($data)
{
    // Cargar la vista como cadena HTML
    $html = view('cartera/imprimir', ['data' => $data]);

    // Configurar Dompdf
    $options = new Options();
    $options->set('isRemoteEnabled', true); // Permite cargar imágenes remotas o desde assets
    $dompdf = new Dompdf($options);

    // Cargar HTML y configurar tamaño
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait'); // Formato horizontal para tablas largas
    $dompdf->render();

    // Descargar el PDF
    $dompdf->stream("reporte_pagos_proveedor.pdf", ["Attachment" => false]);
}
}