<?php

namespace App\Controllers\Reportes;

use App\Controllers\BaseController;
use App\Models\RegventaModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Regventa extends BaseController
{
    private $igv_rate = 18;
    protected $regventaModel;

    public function __construct()
    {
        $this->regventaModel = new RegventaModel();
    }

    public function index()
    {
        $session = session();
        $codcia = $session->get('codcia') ?: '25';
        $server = $this->request->getVar('server') ?: 1;

        $data['vendedores'] = $this->regventaModel->get_vendedores($codcia, $server);
        $data['clientes']   = $this->regventaModel->get_clientes($codcia, $server);
        $data['fecha1']     = $this->request->getVar('fecha1') ?: date('01/m/Y'); // Inicio de mes por defecto
        $data['fecha2']     = $this->request->getVar('fecha2') ?: date('d/m/Y');
        $data['server']     = $server;

        return view('reportes/regventa_form', $data);
    }

    public function generar()
    {
        $session = session();
        
        $fecha1_raw = trim($this->request->getPost('fecha1'));
        $fecha2_raw = trim($this->request->getPost('fecha2'));
        $server     = $this->request->getPost('server') ?: 1;

        $fecha1 = $this->_parse_fecha($fecha1_raw);
        $fecha2 = $this->_parse_fecha($fecha2_raw);

        if (!$fecha1 || !$fecha2 || $fecha1 > $fecha2) {
            return redirect()->to('reportes/regventa')->with('error', 'Fecha inválida o rango incorrecto.');
        }

        $params = [
            'fecha1'        => $fecha1,
            'fecha2'        => $fecha2,
            'TD_F'          => $this->request->getPost('tipo_f') ? 'F' : '',
            'TD_B'          => $this->request->getPost('tipo_b') ? 'B' : '',
            'TD_N'          => $this->request->getPost('tipo_n') ? 'N' : '',
            'TD_D'          => $this->request->getPost('tipo_d') ? 'D' : '',
            'serie'         => trim($this->request->getPost('serie') ?? ''),
            'codcias'       => $this->_get_codcias(),
            'codvendedores' => $this->request->getPost('vendedores') ?: [],
            'codclientes'   => $this->request->getPost('clientes') ?: [],
        ];

        $wsTexto = 'TIPO: ';
        if ($params['TD_F']) $wsTexto .= '- FACTURA ';
        if ($params['TD_B']) $wsTexto .= '- BOLETA ';
        if ($params['TD_N']) $wsTexto .= '- NCRE. ';
        if ($params['TD_D']) $wsTexto .= '- NDEB. ';

        $filas_reporte = [];
        $totales_global = ['valor_venta' => 0.0, 'exonerado' => 0.0, 'igv' => 0.0, 'precio_total' => 0.0];
    ini_set('sqlsrv.ClientBufferMaxKBSize', '51200');
    ini_set('pdo_sqlsrv.client_buffer_max_kb_size', '51200');
        // Ciclo 1: Facturas y Boletas
        if ($params['TD_F'] || $params['TD_B']) {
            $rows = $this->regventaModel->get_facturas_boletas($params, $server);
            $grupo = $this->_procesar_grupo($rows, 1, $params, $server);
            $filas_reporte = array_merge($filas_reporte, $grupo['filas']);
            $this->_sumar_totales($totales_global, $grupo['totales']);
        }

        // Ciclo 2: Notas de Crédito
        if ($params['TD_N']) {
            $rows = $this->regventaModel->get_notas_credito($params, $server);
            log_message('info', 'REGVENTA: Notas de Crédito encontradas: ' . count($rows));
            
            $grupo = $this->_procesar_grupo($rows, 2, $params, $server);
            $filas_reporte = array_merge($filas_reporte, $grupo['filas']);
            $this->_sumar_totales($totales_global, $grupo['totales']);
        }

        // Ciclo 3: Notas de Débito
        if ($params['TD_D']) {
            $rows = $this->regventaModel->get_notas_debito($params, $server);
            log_message('info', 'REGVENTA: Notas de Débito encontradas: ' . count($rows));
            
            $grupo = $this->_procesar_grupo($rows, 3, $params, $server);
            $filas_reporte = array_merge($filas_reporte, $grupo['filas']);
            $this->_sumar_totales($totales_global, $grupo['totales']);
        }

        $nombres_sedes = [
            1 => 'BOTICA MEDINAFARMA - CENTRO',
            2 => 'BOTICA MEDINAFARMA - JUANJUICILLO',
            3 => 'BOTICA MEDINAFARMA - P. MEZA',
        ];

        $this->_generar_excel($filas_reporte, $totales_global, [
            'wsTexto' => $wsTexto,
            'fecha1'  => $fecha1_raw,
            'fecha2'  => $fecha2_raw,
            'empresa' => $nombres_sedes[$server] ?? 'BOTICA MEDINAFARMA',
            'server'  => $server
        ]);
    }

    private function _procesar_grupo($rows, $wcontrol, $params, $server)
    {
        $filas = [];
        $totales = ['valor_venta' => 0.0, 'exonerado' => 0.0, 'igv' => 0.0, 'precio_total' => 0.0];
        $ws_signo = ($wcontrol == 2) ? -1 : 1;

        if (empty($rows)) return compact('filas', 'totales');

        $doc_actual = null;
        $w_exo = $st_cospro = $wq_bruto = $wq_impto = 0.0;
        $wpas_numsec = 0;
        $id_condi = 0;
        $wq_fbg = $wq_fecha = $wq_ruc = $wq_nombre = $wq_codclie = $wq_serie = $wq_docu = $wq_estado = '';
        $wq_estado = null;

        $key_doc = function($r) {
            return $r['FAR_NUMFAC'] . '|' . $r['FAR_NUMSER'] . '|' . $r['FAR_FBG'] . '|' . $r['FAR_TIPMOV'];
        };

        $flush_doc = function() use (
            &$filas, &$totales, &$w_exo, &$st_cospro, &$wpas_numsec,
            &$wq_bruto, &$wq_impto, &$id_condi, &$wq_fbg,
            &$wq_fecha, &$wq_ruc, &$wq_nombre, &$wq_codclie,
            &$wq_serie, &$wq_docu, &$wq_estado, $ws_signo
        ) {
            if ($wq_estado === null) return;

            if ($wq_fbg === 'B') {
                $igv_calc = round(($wq_bruto - $w_exo) * ($this->igv_rate / 100), 2);
                $diferencia = $wq_impto - $igv_calc;
                if ($diferencia >= -0.05 && $diferencia < 0.03) {
                    $wq_bruto += abs($diferencia);
                    $wq_impto -= abs($diferencia);
                }
            }

            $cod_sunat = ['F' => '01', 'B' => '03', 'N' => '07', 'D' => '08'];
            $wq_condi = $cod_sunat[$wq_fbg] ?? '00';

            $base_imp = $exo = $igv = $total = 0.0;

            if ($wq_estado !== 'E') {
                if ($wq_fbg === 'N') {
                    $base_imp = round(($wq_bruto + $w_exo), 2);
                    $exo = round($w_exo * -1, 2);
                } else {
                    $base_imp = round($wq_bruto - $w_exo, 2);
                    $exo = round($w_exo, 2);
                }
                $igv = round($wq_impto, 2);
                $total = round($base_imp + $exo + $igv, 2);

                $totales['valor_venta'] += $base_imp;
                $totales['exonerado'] += $exo;
                $totales['igv'] += $igv;
                $totales['precio_total'] += $total;
            }

            $nombre_display = ($wq_estado === 'E') ? '[ANULADO] ' . $wq_nombre : $wq_nombre;

            $filas[] = [
                'fecha'       => $wq_fecha,
                'nombre'      => $nombre_display,
                'ruc'         => $wq_ruc,
                'cod_sunat'   => $wq_condi,
                'fbg'         => $wq_fbg,
                'serie'       => $wq_serie,
                'numero'      => $wq_docu,
                'base_imp'    => $base_imp,
                'exonerado'   => $exo,
                'igv'         => $igv,
                'total'       => $total,
                'condicion'   => $id_condi,
                'costo_venta' => round($st_cospro, 2),
                'numsec'      => $wpas_numsec,
                'estado'      => $wq_estado,
                'tipo_fila'   => 'detalle',
            ];

            $w_exo = $st_cospro = $wq_bruto = $wq_impto = 0.0;
            $wpas_numsec = 0;
            $id_condi = 0;
            $wq_estado = null;
        };

        $current_key = null;

        foreach ($rows as $r) {
            if (!empty($params['serie']) && trim($r['FAR_NUMSER']) !== trim($params['serie'])) continue;

            $k = $key_doc($r);
            if ($current_key !== null && $k !== $current_key) $flush_doc();
            $current_key = $k;

            $ws_tc = 1.0;
            if ($r['FAR_MONEDA'] === 'D') {
                // Usar FAR_FECHA_COMPRA en lugar de FAR_FECHA para coincidir con Visual Basic
                $fecha_compra = isset($r['FAR_FECHA_COMPRA']) ? $r['FAR_FECHA_COMPRA'] : $r['FAR_FECHA'];
                $ws_tc = $this->regventaModel->get_tipo_cambio($fecha_compra, $r['FAR_CODCIA'], $server);
                if ($ws_tc <= 0) {
                    log_message('error', 'REGVENTA: Sin tipo de cambio para ' . $fecha_compra);
                }
            }

            // Redondear a 2 decimales como en Visual Basic (Format("...", "0.00"))
            $bruto_item = round((floatval($r['FAR_BRUTO']) - floatval($r['FAR_TOT_DESCTO'])) * $ws_tc * $ws_signo, 2);
            $impto_item = round(floatval($r['FAR_IMPTO']) * $ws_tc * $ws_signo, 2);

            if ($r['FAR_EX_IGV'] === 'A') $w_exo += floatval($r['FAR_SUBTOTAL']);

            if ($r['FAR_ESTADO'] === 'E') {
                $bruto_item = $impto_item = $w_exo = $st_cospro = 0.0;
            } else {
                // Cálculo de costo de venta según Visual Basic
                if ($r['FAR_EX_IGV'] === 'A') {
                    // Productos exonerados: no se divide por (1 + IGV)
                    $st_cospro += floatval($r['FAR_CANTIDAD']) * floatval($r['FAR_COSPRO']);
                } else {
                    // Productos gravados: se divide por (1 + IGV)
                    $st_cospro += floatval($r['FAR_CANTIDAD']) * (floatval($r['FAR_COSPRO']) / (1 + ($this->igv_rate / 100)));
                }
                $wpas_numsec = intval($r['FAR_NUM_LOTE']);
            }

            $wq_bruto = $bruto_item;
            $wq_impto = $impto_item;
            $id_condi = intval($r['FAR_SIGNO_CAR']);
            $wq_fbg = trim($r['FAR_FBG']);
            // Usar FAR_FECHA_COMPRA en lugar de FAR_FECHA para coincidir con Visual Basic
            $fecha_compra = isset($r['FAR_FECHA_COMPRA']) ? $r['FAR_FECHA_COMPRA'] : $r['FAR_FECHA'];
            $wq_fecha = date('d/m/Y', strtotime($fecha_compra));
            $wq_estado = $r['FAR_ESTADO'];
            $wq_serie = "'" . $r['FAR_NUMSER'];
            $wq_docu = "'" . $r['FAR_NUMFAC'];
            $wq_codclie = $r['CLI_CODCLIE2'];
            $wq_nombre = $r['CLI_NOMBRE'];
            $wq_ruc = ($wq_fbg === 'B') ? trim($r['CLI_RUC_ESPOSA']) : trim($r['CLI_RUC_ESPOSO']);
        }

        $flush_doc();

        if (!empty($filas)) {
            $tipo_label = '';
            if ($wcontrol == 1) {
                if (!empty($params['TD_F']) && !empty($params['TD_B'])) $tipo_label = 'Total Fact./Bol.';
                elseif (!empty($params['TD_F'])) $tipo_label = 'Total Ventas';
                else $tipo_label = 'Total Boletas';
            } elseif ($wcontrol == 2) {
                $tipo_label = 'Total N.Creditos';
            } else {
                $tipo_label = 'Total N.Debito';
            }

            $filas[] = [
                'tipo_fila'   => 'subtotal',
                'label'       => $tipo_label,
                'valor_venta' => $totales['valor_venta'],
                'exonerado'   => $totales['exonerado'],
                'igv'         => $totales['igv'],
                'total'       => $totales['precio_total'],
            ];
        }

        return compact('filas', 'totales');
    }

    private function _generar_excel($filas, $totales_global, $meta)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reg.Ventas');

        // Estilos base
        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];

        // Cabeceras de la empresa y reporte
        $sheet->setCellValue('A1', $meta['empresa']);
        $sheet->setCellValue('A2', 'REGISTRO DE VENTAS (CIA)');
        $sheet->setCellValue('A3', $meta['wsTexto'] . ' DEL ' . $meta['fecha1'] . ' al ' . $meta['fecha2']);
        
        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(12);

        // Encabezados de columnas (Formato solicitado)
        $cols = [
            'A' => 'Fecha',
            'B' => 'Cliente',
            'C' => 'RUC',
            'D' => 'Tip',
            'E' => 'T.DOC',
            'F' => 'Ser.',
            'G' => 'Nº Docum.',
            'H' => 'Valor Venta',
            'I' => 'Vta. Exoner.',
            'J' => 'Igv',
            'K' => 'Precio Venta'
        ];

        foreach ($cols as $col => $val) {
            $sheet->setCellValue($col . '5', $val);
            $sheet->getStyle($col . '5')->applyFromArray($styleHeader);
        }

        $row = 6;
        foreach ($filas as $f) {
            if ($f['tipo_fila'] === 'detalle') {
                $sheet->setCellValue('A' . $row, $f['fecha']);
                $sheet->setCellValue('B' . $row, $f['nombre']);
                $sheet->setCellValue('C' . $row, $f['ruc']);
                $sheet->setCellValue('D' . $row, $f['cod_sunat']);
                $sheet->setCellValue('E' . $row, $f['fbg']);
                $sheet->setCellValue('F' . $row, $f['serie']);
                $sheet->setCellValue('G' . $row, $f['numero']);
                
                if ($f['estado'] !== 'E') {
                    $sheet->setCellValue('H' . $row, $f['base_imp']);
                    $sheet->setCellValue('I' . $row, $f['exonerado']);
                    $sheet->setCellValue('J' . $row, $f['igv']);
                    $sheet->setCellValue('K' . $row, $f['total']);
                } else {
                    $sheet->setCellValue('H' . $row, 0);
                    $sheet->setCellValue('I' . $row, 0);
                    $sheet->setCellValue('J' . $row, 0);
                    $sheet->setCellValue('K' . $row, 0);
                }
            } elseif ($f['tipo_fila'] === 'subtotal') {
                $sheet->setCellValue('A' . $row, $f['label']);
                $sheet->mergeCells('A' . $row . ':G' . $row);
                $sheet->setCellValue('H' . $row, $f['valor_venta']);
                $sheet->setCellValue('I' . $row, $f['exonerado']);
                $sheet->setCellValue('J' . $row, $f['igv']);
                $sheet->setCellValue('K' . $row, $f['total']);
                $sheet->getStyle('A' . $row . ':K' . $row)->getFont()->setBold(true);
            }
            $row++;
        }

        // Total General
        $sheet->setCellValue('A' . $row, 'TOTALES');
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('H' . $row, $totales_global['valor_venta']);
        $sheet->setCellValue('I' . $row, $totales_global['exonerado']);
        $sheet->setCellValue('J' . $row, $totales_global['igv']);
        $sheet->setCellValue('K' . $row, $totales_global['precio_total']);
        $sheet->getStyle('A' . $row . ':K' . $row)->getFont()->setBold(true);

        // Formato numérico
        $sheet->getStyle('H6:K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // Anchos de columna
        $widths = ['A'=>12, 'B'=>40, 'C'=>15, 'D'=>8, 'E'=>8, 'F'=>8, 'G'=>12, 'H'=>12, 'I'=>12, 'J'=>12, 'K'=>12];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $filename = 'REGVENTA_' . $meta['server'] . '_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function _parse_fecha($str)
    {
        $str = trim($str);
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $str, $m)) {
            // Formato YYYYMMDD es el más seguro en SQL Server independientemente del local de la BD
            return $m[3] . $m[2] . $m[1];
        }
        return null;
    }

    private function _sumar_totales(&$global, $grupo)
    {
        foreach ($global as $k => &$v) {
            $v += $grupo[$k] ?? 0;
        }
    }

    private function _get_codcias()
    {
        $session = session();
        $codcia = $session->get('codcia');
        // Si no hay codcia en sesión o es 01, forzamos 25 que es el que tiene datos reales en FACART
        if (!$codcia || $codcia == '01') {
            return ['25'];
        }
        return [$codcia];
    }
}
