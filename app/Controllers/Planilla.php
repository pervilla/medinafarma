<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PlanillaModel;
use App\Models\PlanillaDetalleModel;
use App\Models\PlanillaConfigEmpleadoModel;
use App\Models\PlanillaAfpModel;
use App\Models\VemaestModel;
use App\Models\FacartModel;
use App\Models\CajaMovimientosModel;
use App\Models\PlanillaReglaComisionModel;
use App\Models\ArticuloModel;
use App\Models\PlanillaDescuentoModel;
use App\Models\PlanillaExtraModel;

class Planilla extends BaseController
{
    protected $planillaModel;
    protected $detalleModel;
    protected $configModel;
    protected $afpModel;
    protected $vemaestModel;
    protected $facartModel;
    protected $cajaModel;
    protected $reglaModel;
    protected $articuloModel;
    protected $descuentoModel;
    protected $extraModel;

    public function __construct()
    {
        $this->planillaModel = new PlanillaModel();
        $this->detalleModel  = new PlanillaDetalleModel();
        $this->configModel   = new PlanillaConfigEmpleadoModel();
        $this->afpModel      = new PlanillaAfpModel();
        $this->vemaestModel  = new VemaestModel();
        $this->facartModel   = new FacartModel();
        $this->cajaModel     = new CajaMovimientosModel();
        $this->reglaModel    = new PlanillaReglaComisionModel();
        $this->articuloModel = new ArticuloModel();
        $this->descuentoModel = new PlanillaDescuentoModel();
        $this->extraModel = new PlanillaExtraModel();
    }

    public function index()
    {
        $data['planillas'] = $this->planillaModel->orderBy('id', 'DESC')->findAll();
        return view('planilla/index', $data);
    }

    public function create()
    {
        $data['afps'] = $this->afpModel->findAll();
        // We might need to pass recent months or years
        return view('planilla/create', $data);
    }

    public function get_empleados_config()
    {
        // For the config view
        $empleados = $this->vemaestModel->get_empleado('');
        $configs = $this->configModel->findAll();
        $afps = $this->afpModel->findAll();

        // Merge config into employees
        $configMap = [];
        foreach ($configs as $cfg) {
            $configMap[$cfg->vem_codven] = $cfg;
        }

        foreach ($empleados as &$emp) {
            if (isset($configMap[$emp->VEM_CODVEN])) {
                $emp->config = $configMap[$emp->VEM_CODVEN];
            } else {
                $emp->config = null;
            }
        }

        return $this->response->setJSON([
            'empleados' => $empleados,
            'afps' => $afps
        ]);
    }

    public function save_config()
    {
        try {
            $data = $this->request->getJSON();
            if (!$data) {
                return $this->response->setJSON(['success' => false, 'message' => 'No JSON data']);
            }

            foreach ($data as $row) {
                // Sanitize AFP ID (empty string -> null)
                $afpId = !empty($row->afp_id) ? $row->afp_id : null;
                
                // Check if exists to determine insert/update (or use save with useAutoIncrement=false should work)
                // But to be safe with manual PKs, let's use replace or explicit check
                
                $dataToSave = [
                    'vem_codven' => $row->vem_codven,
                    'sueldo_basico' => $row->sueldo_basico,
                    'tipo_comision' => $row->tipo_comision,
                    'afp_id' => $afpId,
                    'asignacion_familiar' => $row->asignacion_familiar,
                    'comision_fijo_monto' => $row->comision_fijo_monto ?? 0
                ];

                if (!$this->configModel->save($dataToSave)) {
                     log_message('error', 'Save Config Error for ' . $row->vem_codven . ': ' . json_encode($this->configModel->errors()));
                }
            }
            return $this->response->setJSON(['success' => true]);
        } catch (\Throwable $th) {
            log_message('error', 'Save Config Exception: ' . $th->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    // The core calculation logic
    public function generate_data()
    {
        $mes = $this->request->getGet('mes');
        $anio = $this->request->getGet('anio');
        $fechaInicio = $this->request->getGet('fecha_inicio');
        $fechaCorte = $this->request->getGet('fecha_corte');
        
        // Fallback if not provided (though UI provides it)
        if(!$fechaInicio) $fechaInicio = "$anio-$mes-01";
        if(!$fechaCorte) $fechaCorte = date('Y-m-d');

        // 1. Get Active Employees
        $empleados = $this->vemaestModel->get_empleado('');

        // 2. Get Configurations
        $configs = $this->configModel->findAll();
        $configMap = [];
        foreach ($configs as $cfg) {
            $configMap[$cfg->vem_codven] = $cfg;
        }

        // 3. Get AFPs
        $afps = $this->afpModel->findAll();
        $afpMap = [];
        foreach ($afps as $afp) {
            $afpMap[$afp->id] = $afp;
        }

        // 4. Commission Rules Setup
        $rulesRaw = $this->reglaModel->findAll();
        $defaultPct = 0;
        $globalDiscount = 0; // Tax Discount %
        $familyRules = []; // Map FamilyID -> Percentage
        
        foreach($rulesRaw as $r){
            if($r->tipo == 'DEFAULT') $defaultPct = $r->porcentaje;
            elseif($r->tipo == 'GLOBAL_DISCOUNT') $globalDiscount = $r->porcentaje;
            elseif($r->tipo == 'FAMILIA') $familyRules[$r->referencia_id] = $r->porcentaje;
        }

        // 5. Get Detailed Sales for Commission Calc
        // Returns rows: VEM_CODVEN, FAR_CODART, ART_FAMILIA, VENTA_NETA
        $ventasDetalle = $this->facartModel->get_ventas_detalle_empleado($fechaInicio, $fechaCorte);
        
        // Calculate Commission Per Employee
        $comisionMap = [];
        foreach ($ventasDetalle as $venta) {
            $empId = $venta->FAR_CODVEN;
            $familyId = $venta->ART_FAMILIA; // From new Join
            $monto = $venta->VENTA_NETA;
            
            // Determine Percentage (Family Rule or Default)
            $pct = isset($familyRules[$familyId]) ? $familyRules[$familyId] : $defaultPct;
            
            // Calc
            $comisionItem = $monto * ($pct / 100);

            if (!isset($comisionMap[$empId])) $comisionMap[$empId] = 0;
            $comisionMap[$empId] += $comisionItem;
        }

        // 6. Get Credits/Advances
        // NOW USING DATES
        $creditosRaw = $this->cajaModel->get_creditos($fechaInicio, $fechaCorte);
        $creditoMap = [];
        foreach ($creditosRaw as $c) {
            $creditoMap[$c->CMV_CODVEN] = $c->DEUDA;
        }

        // 7. Get Pending Discounts (Faltantes)
        // NOW USING DATES RANGE
        $discountsRaw = $this->descuentoModel
            ->where('fecha >=', $fechaInicio)
            ->where('fecha <=', $fechaCorte)
            ->where('estado', 'PENDIENTE')
            ->findAll();
        
        $discountMap = [];
        foreach($discountsRaw as $d){
            if(!isset($discountMap[$d->vem_codven])) $discountMap[$d->vem_codven] = 0;
            $discountMap[$d->vem_codven] += $d->monto;
        }
        
        // 8. Get Pending Extras (Overtime/Holidays)
        // NOW USING DATES RANGE
        $extrasRaw = $this->extraModel
            ->where('fecha >=', $fechaInicio)
            ->where('fecha <=', $fechaCorte)
            ->where('estado', 'PENDIENTE')
            ->findAll();
        
        $extrasMap = [];
        foreach($extrasRaw as $e){
            $extrasMap[$e->vem_codven][] = $e;
        }

        // 9. Build the grid
        $grid = [];
        foreach ($empleados as $emp) {
            $id = $emp->VEM_CODVEN;
            $cfg = $configMap[$id] ?? null;

            if (!$cfg) {
                $sueldo = 0;
                $tipoComision = 'NINGUNO';
                $afpId = null;
                $asigFam = 0;
                $fijoMonto = 0;
            } else {
                $sueldo = $cfg->sueldo_basico;
                $tipoComision = $cfg->tipo_comision;
                $afpId = $cfg->afp_id;
                $asigFam = $cfg->asignacion_familiar ? 102.50 : 0;
                $fijoMonto = $cfg->comision_fijo_monto ?? 0;
            }

            // Calculate Commission
            // Calculate Commission
            $comision = 0;
            $comisionVariable = 0;
            $comisionFija = 0;

            // 1. Variable Component (Sales Rules)
            if ($tipoComision == 'VENTAS' || $tipoComision == 'MIXTO') {
                $rawCommission = $comisionMap[$id] ?? 0;
                // Apply Global Tax Discount to Variable Part
                // User logic: Base = Total / 1.18 (IGV Extraction)
                // $globalDiscount is 18. So we divide by 1.18
                if ($globalDiscount > 0) {
                    $factor = 1 + ($globalDiscount / 100);
                    $comisionVariable = $rawCommission / $factor;
                    $taxAmount = $rawCommission - $comisionVariable;
                } else {
                    $comisionVariable = $rawCommission;
                    $taxAmount = 0;
                }
            }

            // 2. Fixed Component
            if ($tipoComision == 'FIJO' || $tipoComision == 'MIXTO') {
                $comisionFija = $fijoMonto;
            }

            $comision = $comisionVariable + $comisionFija;

            // Rounding
            $comision = round($comision, 2);
            
            // Info String using standard numeric formatting
            $infoVar = number_format($comisionVariable + ($taxAmount ?? 0), 2);
            $infoFijo = number_format($comisionFija, 2);
            $infoDesc = number_format($taxAmount ?? 0, 2);
            $comisionInfo = "Ventas: $infoVar | Fijo: $infoFijo | Desc: $infoDesc";

            // Calculate Extras (Overtime / Feriados)
            $montoExtras = 0;
            $horas25 = 0;
            $horas35 = 0;
            $horas100 = 0;
            $diasFeriado = 0;

            if (isset($extrasMap[$id]) && $sueldo > 0) {
                // Hourly rate: Sueldo / 30 / 8
                $valorHora = $sueldo / 30 / 8;
                $valorDia = $sueldo / 30;

                foreach ($extrasMap[$id] as $extra) {
                    if ($extra->tipo == 'HORA_EXTRA_25') {
                        $montoExtras += $valorHora * 1.25 * $extra->cantidad;
                        $horas25 += $extra->cantidad;
                    } elseif ($extra->tipo == 'HORA_EXTRA_35') {
                         $montoExtras += $valorHora * 1.35 * $extra->cantidad;
                         $horas35 += $extra->cantidad;
                    } elseif ($extra->tipo == 'HORA_EXTRA_100') {
                         $montoExtras += $valorHora * 1.00 * $extra->cantidad;
                         $horas100 += $extra->cantidad;
                    } elseif ($extra->tipo == 'FERIADO') {
                         $montoExtras += $valorDia * 1 * $extra->cantidad;
                         $diasFeriado += $extra->cantidad;
                    }
                }
            }
            $montoExtras = round($montoExtras, 2);

            // Calculate AFP
            $afpNombre = '';
            $afpPorc = 0;
            $afpMonto = 0;
            
            // Base to calc AFP includes Extras? 
            // In Peru, Overtime is generally subject to AFP/ONP.
            // Asig Fam is also subject.
            $baseImponible = $sueldo + $asigFam + $comision + $montoExtras;
            $totalBruto = $baseImponible; 

            if ($afpId && isset($afpMap[$afpId])) {
                $afpObj = $afpMap[$afpId];
                $afpNombre = $afpObj->nombre;
                $afpPorc = $afpObj->porcentaje;
                $afpMonto = round($baseImponible * ($afpPorc / 100), 2);
            }

            // Discounts
            $descuentosCaja = $creditoMap[$id] ?? 0;
            $descuentosFaltantes = $discountMap[$id] ?? 0;
            
            // Total Net
             $totalNeto = $totalBruto - $afpMonto - $descuentosCaja - $descuentosFaltantes;

            $grid[] = [
                'vem_codven' => $id,
                'nombre' => $emp->VEM_NOMBRE,
                'dias_trabajados' => 30,
                'sueldo_basico' => $sueldo,
                'asignacion_familiar' => $asigFam,
                // Pass raw commission or final? Final.
                'comision_ventas' => $comision, 
                'comision_info' => $comisionInfo, // New info field
                'extras_monto' => $montoExtras, // New Field
                'extras_info' => "HE25: $horas25 | HE35: $horas35 | HE100: $horas100 | Fer: $diasFeriado", // Info string
                'afp_id' => $afpId,
                'afp_nombre' => $afpNombre,
                'afp_monto' => $afpMonto,
                'adelantos' => $descuentosCaja, 
                'creditos' => 0, 
                'faltantes' => $descuentosFaltantes,
                'total_neto' => $totalNeto
            ];
        }

        return $this->response->setJSON($grid);
    }

    public function store()
    {
        try {
            $json = $this->request->getJSON();
            if (!$json) {
                return $this->response->setJSON(['success' => false, 'message' => 'No JSON data received']);
            }

            $header = $json->header ?? null;
            $detalles = $json->detalles ?? null;

            if (!$header || !$detalles) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid data format']);
            }

            // Fix SQL Server Date Format (YYYYMMDD is safe for strings, but for queries Y-m-d)
            $fechaInicio = date('Y-m-d', strtotime($header->fecha_inicio));
            $fechaCorte  = date('Y-m-d', strtotime($header->fecha_corte));
            $now = date('Ymd H:i:s');

            // Save Header
            $planillaId = $this->planillaModel->insert([
                'anio' => $header->anio,
                'mes' => $header->mes,
                'fecha_inicio' => $fechaInicio,
                'fecha_corte' => $fechaCorte,
                'estado' => 'BORRADOR', // Or value passed
                'usuario_id' => 1, // Placeholder for Auth user
                'created_at' => $now,
                'updated_at' => $now
            ]);

            if (!$planillaId) {
                 return $this->response->setJSON(['success' => false, 'message' => 'Failed to insert Header', 'errors' => $this->planillaModel->errors()]);
            }

            // Process Discounts: Mark them as PROCESADO and link to planillaId
            // We do this for all PENDING discounts in this Month/Year
            // Process Discounts: Mark them as PROCESADO and link to planillaId
            // We do this for all PENDING discounts in this Month/Year
            // Process Discounts: Mark them as PROCESADO and link to planillaId
            // We do this for all PENDING discounts in this DATE RANGE
            $this->descuentoModel
                ->where('fecha >=', $fechaInicio) // Correct format YYYY-MM-DD
                ->where('fecha <=', $fechaCorte)
                ->where('estado', 'PENDIENTE')
                ->set(['estado' => 'PROCESADO', 'planilla_id' => $planillaId, 'updated_at' => $now])
                ->update();

            // Process Extras: Mark them as PROCESADO and link to planillaId
            $this->extraModel
                ->where('fecha >=', $fechaInicio)
                ->where('fecha <=', $fechaCorte)
                ->where('estado', 'PENDIENTE')
                ->set(['estado' => 'PROCESADO', 'planilla_id' => $planillaId, 'updated_at' => $now])
                ->update();

            // Save Details
            foreach ($detalles as $det) {
                $this->detalleModel->insert([
                    'planilla_id' => $planillaId,
                    'vem_codven' => $det->vem_codven,
                    'dias_trabajados' => $det->dias_trabajados,
                    'sueldo_basico' => $det->sueldo_basico,
                    'asignacion_familiar' => $det->asignacion_familiar,
                    'comision_ventas' => $det->comision_ventas,
                    'afp_monto' => $det->afp_monto,
                    'adelantos' => $det->adelantos,
                    'creditos' => $det->creditos,
                    'faltantes' => $det->faltantes,
                    'total_neto' => $det->total_neto
                ]);
            }

            return $this->response->setJSON(['success' => true, 'id' => $planillaId]);

        } catch (\Throwable $th) {
            log_message('error', 'Planilla Store Error: ' . $th->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function export($id)
    {
        $planilla = $this->planillaModel->find($id);
        $detalles = $this->detalleModel->where('planilla_id', $id)
            ->join('vemaest', 'vemaest.vem_codven = planilla_detalles.vem_codven')
            ->join('planilla_afps', 'planilla_afps.id = (SELECT afp_id FROM planilla_config_empleados WHERE vem_codven = planilla_detalles.vem_codven)', 'left')
            ->select('planilla_detalles.*, vemaest.VEM_NOMBRE, planilla_afps.nombre as afp_nombre')
            ->findAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $sheet->setCellValue('A1', 'PLANILLA DE PAGOS');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'Periodo: ' . $planilla->anio . '-' . str_pad($planilla->mes, 2, '0', STR_PAD_LEFT));
        $sheet->setCellValue('A3', 'Fecha Corte Comisiones: ' . $planilla->fecha_corte);

        // Headers
        $headers = [
            'A5' => 'Nº',
            'B5' => 'APELLIDOS Y NOMBRES',
            'C5' => 'DÍAS',
            'D5' => 'BASICO',
            'E5' => 'ASIG. FAM.',
            'F5' => 'COMISION',
            'G5' => 'BRUTO',
            'H5' => 'AFP/ONP',
            'I5' => 'DESC. AFP',
            'J5' => 'ADELANTOS/CRED.',
            'K5' => 'OTROS DESC.',
            'L5' => 'NETO A PAGAR'
        ];

        foreach($headers as $cell => $val){
            $sheet->setCellValue($cell, $val);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $row = 6;
        foreach($detalles as $idx => $d) {
            $bruto = $d->sueldo_basico + $d->asignacion_familiar + $d->comision_ventas;
            
            $sheet->setCellValue('A'.$row, $idx + 1);
            $sheet->setCellValue('B'.$row, $d->VEM_NOMBRE);
            $sheet->setCellValue('C'.$row, $d->dias_trabajados);
            $sheet->setCellValue('D'.$row, $d->sueldo_basico);
            $sheet->setCellValue('E'.$row, $d->asignacion_familiar);
            $sheet->setCellValue('F'.$row, $d->comision_ventas);
            $sheet->setCellValue('G'.$row, $bruto);
            $sheet->setCellValue('H'.$row, $d->afp_nombre);
            $sheet->setCellValue('I'.$row, $d->afp_monto);
            $sheet->setCellValue('J'.$row, $d->adelantos + $d->creditos);
            $sheet->setCellValue('K'.$row, $d->faltantes);
            $sheet->setCellValue('L'.$row, $d->total_neto);

            $row++;
        }

        // AutoSize Columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A5:L'.($row-1))->applyFromArray($styleArray);


        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Planilla_' . $planilla->anio . '_' . $planilla->mes . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
        $writer->save('php://output');
        exit;
    }

    public function delete($id)
    {
        $planilla = $this->planillaModel->find($id);
        if ($planilla) {
            $this->detalleModel->where('planilla_id', $id)->delete();
            $this->planillaModel->delete($id);
            return $this->response->setJSON(['success' => true]);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Planilla no encontrada']);
    }
    public function edit($id)
    {
        $planilla = $this->planillaModel->find($id);
        if (!$planilla) {
            return redirect()->to(site_url('planilla'));
        }
        
        // Fetch details with names
        $detalles = $this->detalleModel->where('planilla_id', $id)
            ->join('vemaest', 'vemaest.vem_codven = planilla_detalles.vem_codven')
            ->join('planilla_afps', 'planilla_afps.id = (SELECT afp_id FROM planilla_config_empleados WHERE vem_codven = planilla_detalles.vem_codven)', 'left')
            ->select('planilla_detalles.*, vemaest.VEM_NOMBRE as nombre, planilla_afps.nombre as afp_nombre')
            ->findAll();

        $data = [
            'planilla' => $planilla,
            'detalles' => $detalles
        ];

        return view('planilla/edit', $data);
    }

    public function update()
    {
        try {
            $json = $this->request->getJSON();
            if (!$json) {
                return $this->response->setJSON(['success' => false, 'message' => 'No JSON data']);
            }

            $id = $json->id;
            $header = $json->header;
            $detalles = $json->detalles;

            // Fix SQL Server Date Format (YYYYMMDD is safe)
            $now = date('Ymd H:i:s');

            // Update Header (State only for now, maybe dates if needed but risky)
            // For now only state transition support or just saving content
            $this->planillaModel->update($id, [
                'estado' => $header->estado,
                'updated_at' => $now
            ]);

            // Update Details
            // Strategy: Loop and update individually. 
            foreach ($detalles as $det) {
                // Determine missing vs 0 ?
                $this->detalleModel->update($det->id, [
                    'dias_trabajados' => $det->dias_trabajados,
                    'sueldo_basico' => $det->sueldo_basico,
                    'asignacion_familiar' => $det->asignacion_familiar,
                    'comision_ventas' => $det->comision_ventas,
                    'afp_monto' => $det->afp_monto,
                    'adelantos' => $det->adelantos,
                    'creditos' => $det->creditos,
                    'faltantes' => $det->faltantes,
                    'total_neto' => $det->total_neto
                ]);
            }

            return $this->response->setJSON(['success' => true]);

        } catch (\Throwable $th) {
             log_message('error', 'Planilla Update Error: ' . $th->getMessage());
             return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $th->getMessage()]);
        }
    }
    // --- Commission Rules Management ---
    public function reglas()
    {
        // Now returns Families instead of Products
        $data['rules'] = $this->reglaModel->where('tipo', 'FAMILIA')->findAll();
        
        // first() uses OFFSET which fails on old SQL Server. Using findAll() since we expect 1 row.
        $defaults = $this->reglaModel->where('tipo', 'DEFAULT')->findAll(); 
        $data['defaultRule'] = $defaults[0] ?? null;

        $globals = $this->reglaModel->where('tipo', 'GLOBAL_DISCOUNT')->findAll();
        $data['globalDiscount'] = $globals[0] ?? null;
        
        return view('planilla/reglas_comision', $data);
    }

    public function save_rule()
    {
        try {
            $tipo = $this->request->getPost('tipo');
            $refId = $this->request->getPost('referencia_id') ?: null; // Handle empty
            $pct = $this->request->getPost('porcentaje');
            $desc = $this->request->getPost('descripcion');

            if ($tipo == 'DEFAULT' || $tipo == 'GLOBAL_DISCOUNT') {
                $existingRules = $this->reglaModel->where('tipo', $tipo)->findAll();
                $existing = $existingRules[0] ?? null;
                
                if ($existing) {
                    $this->reglaModel->update($existing->id, ['porcentaje' => $pct, 'updated_at' => date('Ymd H:i:s')]);
                } else {
                    $this->reglaModel->insert([
                        'tipo' => $tipo, 
                        'porcentaje' => $pct, 
                        'descripcion' => ($tipo == 'GLOBAL_DISCOUNT' ? 'Descuento Global Impuestos' : 'Default Rule'),
                        'created_at' => date('Ymd H:i:s')
                    ]);
                }
            } else {
                // Family Rule (formerly Product Rule, or any generic multi-rule)
                // Assuming validation prevents duplicates for same Family
                $this->reglaModel->insert([
                    'tipo' => $tipo,
                    'referencia_id' => $refId,
                    'porcentaje' => $pct,
                    'descripcion' => $desc,
                    'created_at' => date('Ymd H:i:s'),
                    'updated_at' => date('Ymd H:i:s')
                ]);
            }
            return $this->response->setJSON(['success' => true]);
        } catch (\Throwable $th) {
            return $this->response->setJSON(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function delete_rule()
    {
        $id = $this->request->getPost('id');
        $this->reglaModel->delete($id);
        return $this->response->setJSON(['success' => true]);
    }

    public function get_familias()
    {
        // Fetch Families (TABLAS 122)
        // Using direct query or Model if exists. Using generic db query here for speed.
        $db = \Config\Database::connect();
        $query = $db->query("SELECT TAB_NUMTAB, TAB_NOMLARGO FROM TABLAS WHERE TAB_TIPREG = 122 AND TAB_CODCIA = 25 ORDER BY TAB_NOMLARGO");
        $results = $query->getResult();
        
        $data = [];
        foreach($results as $r){
            $data[] = ['id' => $r->TAB_NUMTAB, 'nombre' => trim($r->TAB_NOMLARGO)];
        }
        return $this->response->setJSON($data);
    }

    // --- Employee Discounts Management ---
    public function descuentos()
    {
        $data['empleados'] = $this->vemaestModel->get_empleado('');
        return view('planilla/descuentos', $data);
    }

    public function get_descuentos()
    {
        $mes = $this->request->getGet('mes');
        $anio = $this->request->getGet('anio');

        $descuentos = $this->descuentoModel
            ->where('MONTH(fecha)', $mes)
            ->where('YEAR(fecha)', $anio)
            ->orderBy('fecha', 'DESC')
            ->findAll();

        // Get Employee Names locally to avoid complex joins if models are different DBs or manual
        // But Vemaest is SQL SRV too.
        // Let's iterate and attach names
        $empleados = $this->vemaestModel->get_empleado('');
        $empMap = [];
        foreach ($empleados as $e) $empMap[$e->VEM_CODVEN] = $e->VEM_NOMBRE;

        foreach ($descuentos as $d) {
            $d->nombre_empleado = $empMap[$d->vem_codven] ?? 'Unknown';
        }

        return $this->response->setJSON(['data' => $descuentos]);
    }

    public function save_descuento()
    {
        try {
            $id = $this->request->getPost('id');
            $data = [
                'vem_codven' => $this->request->getPost('vem_codven'),
                'tipo' => $this->request->getPost('tipo'),
                'monto' => $this->request->getPost('monto'),
                'fecha' => $this->request->getPost('fecha'),
                'observacion' => $this->request->getPost('observacion'),
                 // Only update timestamp if exists
            ];

            if ($id) {
                // Check if processed
                $curr = $this->descuentoModel->find($id);
                if ($curr && $curr->estado == 'PROCESADO') {
                    return $this->response->setJSON(['success' => false, 'message' => 'No se puede editar un descuento ya procesado.']);
                }
                $data['updated_at'] = date('Ymd H:i:s');
                $this->descuentoModel->update($id, $data);
            } else {
                $data['created_at'] = date('Ymd H:i:s');
                $data['estado'] = 'PENDIENTE';
                $this->descuentoModel->insert($data);
            }
            return $this->response->setJSON(['success' => true]);
        } catch (\Throwable $th) {
            return $this->response->setJSON(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function delete_descuento()
    {
        $id = $this->request->getPost('id');
        $curr = $this->descuentoModel->find($id);
        if ($curr && $curr->estado == 'PROCESADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede eliminar un descuento procesado.']);
        }
        $this->descuentoModel->delete($id);
        return $this->response->setJSON(['success' => true]);
    }

    // --- Overtime & Holidays Management ---
    public function extras()
    {
        $data['empleados'] = $this->vemaestModel->get_empleado('');
        return view('planilla/extras', $data);
    }

    public function get_extras()
    {
        $mes = $this->request->getGet('mes');
        $anio = $this->request->getGet('anio');

        $extras = $this->extraModel
            ->where('MONTH(fecha)', $mes)
            ->where('YEAR(fecha)', $anio)
            ->orderBy('fecha', 'DESC')
            ->findAll();

        $empleados = $this->vemaestModel->get_empleado('');
        $empMap = [];
        foreach ($empleados as $e) $empMap[$e->VEM_CODVEN] = $e->VEM_NOMBRE;

        foreach ($extras as $d) {
            $d->nombre_empleado = $empMap[$d->vem_codven] ?? 'Unknown';
        }

        return $this->response->setJSON(['data' => $extras]);
    }

    public function save_extra()
    {
        try {
            $id = $this->request->getPost('id');
            $data = [
                'vem_codven' => $this->request->getPost('vem_codven'),
                'tipo' => $this->request->getPost('tipo'),
                'cantidad' => $this->request->getPost('cantidad'),
                'fecha' => $this->request->getPost('fecha'),
                'observacion' => $this->request->getPost('observacion'),
            ];

            if ($id) {
                $curr = $this->extraModel->find($id);
                if ($curr && $curr->estado == 'PROCESADO') {
                    return $this->response->setJSON(['success' => false, 'message' => 'No se puede editar un registro procesado.']);
                }
                $data['updated_at'] = date('Ymd H:i:s');
                $this->extraModel->update($id, $data);
            } else {
                $data['created_at'] = date('Ymd H:i:s');
                $data['estado'] = 'PENDIENTE';
                $this->extraModel->insert($data);
            }
            return $this->response->setJSON(['success' => true]);
        } catch (\Throwable $th) {
            return $this->response->setJSON(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function delete_extra()
    {
        $id = $this->request->getPost('id');
        $curr = $this->extraModel->find($id);
        if ($curr && $curr->estado == 'PROCESADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede eliminar un registro procesado.']);
        }
        $this->extraModel->delete($id);
        return $this->response->setJSON(['success' => true]);
    }
}
