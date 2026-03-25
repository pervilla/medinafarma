<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReporteRentablesModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class Reportes extends BaseController
{
    protected $rentablesModel;

    public function __construct()
    {
        $this->rentablesModel = new ReporteRentablesModel();
    }

    public function rentables()
    {
        $data['menu']['p'] = 20; // Empleados
        $data['menu']['i'] = 25; // Reporte Rentables
        return view('analisis/index_rentables', $data);
    }

    public function get_data_rentables()
    {
        $fechaInicio = $this->request->getVar('fecha_inicio');
        $fechaFin = $this->request->getVar('fecha_fin');

        if (empty($fechaInicio) || empty($fechaFin)) {
            $fechaInicio = date('Y-m-01');
            $fechaFin = date('Y-m-d');
        }

        // 1. Check server availability
        $serverCheck = $this->rentablesModel->check_servers_availability();
        if (!$serverCheck['success']) {
            return $this->response->setStatusCode(503)->setJSON([
                'success' => false,
                'message' => $serverCheck['message']
            ]);
        }

        try {
            // 2. Fetch data
            $data = $this->rentablesModel->get_ventas_rentables_empleado($fechaInicio, $fechaFin);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            log_message('error', 'get_data_rentables error: ' . $th->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al obtener los datos del servidor.'
            ]);
        }
    }

    public function export_rentables_excel()
    {
        $fechaInicio = $this->request->getVar('fecha_inicio');
        $fechaFin = $this->request->getVar('fecha_fin');

        if (empty($fechaInicio) || empty($fechaFin)) {
            $fechaInicio = date('Y-m-01');
            $fechaFin = date('Y-m-d');
        }

        $serverCheck = $this->rentablesModel->check_servers_availability();
        if (!$serverCheck['success']) {
            echo "<h1>Error 503: Servidores Inaccesibles</h1>";
            echo "<p>{$serverCheck['message']}</p>";
            echo "<a href='javascript:history.back()'>Volver</a>";
            exit;
        }

        try {
            $data = $this->rentablesModel->get_ventas_rentables_empleado($fechaInicio, $fechaFin);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Title
            $sheet->setCellValue('A1', 'REPORTE CONSOLIDADO: VENTAS DE PRODUCTOS RENTABLES (LOCAL + JCL + PMZ)');
            $sheet->mergeCells('A1:E1');
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);

            $sheet->setCellValue('A2', "Periodo: $fechaInicio al $fechaFin");
            $sheet->setCellValue('A3', "Generado el: " . date('Y-m-d H:i:s'));
            
            // Headers
            $headers = [
                'A5' => 'Nº', 
                'B5' => 'EMPLEADO', 
                'C5' => 'TOTAL BRUTO (S/)', 
                'D5' => 'VENTA RENTABLE (S/)', 
                'E5' => '% RENTABLE'
            ];
            foreach ($headers as $cell => $val) {
                $sheet->setCellValue($cell, $val);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Data
            $row = 6;
            $totalBruto = 0;
            $totalRentable = 0;
            foreach ($data as $idx => $d) {
                $sheet->setCellValue('A' . $row, $idx + 1);
                $sheet->setCellValue('B' . $row, $d['nombre']);
                $sheet->setCellValue('C' . $row, $d['total_bruto']);
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->setCellValue('D' . $row, $d['total_ventas']);
                $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->setCellValue('E' . $row, $d['pct_rentable'] / 100);
                $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('0.00%');
                
                $totalBruto += $d['total_bruto'];
                $totalRentable += $d['total_ventas'];
                $row++;
            }

            // Totals Row
            $sheet->setCellValue('B' . $row, 'TOTAL ACUMULADO');
            $sheet->getStyle('B' . $row)->getFont()->setBold(true);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            $sheet->setCellValue('C' . $row, $totalBruto);
            $sheet->getStyle('C' . $row)->getFont()->setBold(true);
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet->setCellValue('D' . $row, $totalRentable);
            $sheet->getStyle('D' . $row)->getFont()->setBold(true);
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            $pctTotal = $totalBruto > 0 ? ($totalRentable / $totalBruto) : 0;
            $sheet->setCellValue('E' . $row, $pctTotal);
            $sheet->getStyle('E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('0.00%');

            // Cell Sizing & AutoSize
            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Borders
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ];
            $sheet->getStyle('A5:E' . $row)->applyFromArray($styleArray);

            ob_end_clean(); // Ensure no output buffering issues
            ob_start();

            $writer = new Xlsx($spreadsheet);
            $filename = "Reporte_Rentables_{$fechaInicio}_{$fechaFin}.xlsx";

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Throwable $th) {
            echo "<h1>Error 500: Fallo Interno al Generar Excel</h1>";
            echo "<p>{$th->getMessage()}</p>";
            exit;
        }
    }
}
