<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Controllers\SunatController;
use App\Models\SunatModel;
use App\Models\SunatLogModel;

class SunatCron extends BaseCommand
{
    protected $group = 'Sunat';
    protected $name = 'sunat:cron';
    protected $description = 'Envía resúmenes diarios y facturas pendientes a SUNAT.';
    protected $usage = 'sunat:cron [--date=Y-m-d] [--days=3]';
    protected $arguments = [];
    protected $options = [
        '--date' => 'Fecha específica a procesar (YYYY-MM-DD). Por defecto: hoy',
        '--days' => 'Cantidad de días hacia atrás a procesar. Por defecto: 3',
    ];

    private $logFile = '';
    private $startTime = '';

    public function run(array $params)
    {
        $this->startTime = date('Y-m-d H:i:s');
        $this->logFile = WRITEPATH . 'logs/sunat_cron_' . date('Ymd') . '.log';

        $this->log("=== CRON SUNAT INICIADO ===");
        $this->log("Inicio: {$this->startTime}");

        // Determinar rango de fechas
        $hoy = date('Y-m-d');
        $fechaFin = $this->getOption('date') ?? $hoy;
        $diasAtras = (int)($this->getOption('days') ?? 3);

        if (!$this->getOption('date')) {
            $fechaFin = date('Y-m-d', strtotime('-1 day'));
        }

        $fechaInicio = date('Y-m-d', strtotime($fechaFin . " -" . ($diasAtras - 1) . " days"));

        $this->log("Rango: $fechaInicio → $fechaFin (dias=$diasAtras)");
        $this->log("--date=" . var_export($this->getOption('date'), true));
        $this->log("--days=" . var_export($this->getOption('days'), true));

        CLI::write("Rango: $fechaInicio → $fechaFin", 'yellow');

        $controller = new SunatController();
        $controller->initController(\Config\Services::request(), \Config\Services::response(), \Config\Services::logger());

        $logModel = new SunatLogModel();
        $sunatModel = new SunatModel();

        $empresas = [
            ['ruc' => '20450337839', 'serie' => '10', 'nombre' => 'INVERSIONES (S10)'],
            ['ruc' => '20450337839', 'serie' => '11', 'nombre' => 'INVERSIONES (S11)'],
            ['ruc' => '10422781191', 'serie' => 'B001', 'nombre' => 'MARYMED'],
        ];

        $seriesFactura = ['10', '11'];

        $fechaActual = $fechaInicio;
        while ($fechaActual <= $fechaFin) {
            $this->log("--- DIA: $fechaActual ---");
            CLI::write("DIA: $fechaActual", 'cyan');

            foreach ($empresas as $emp) {
                $yaExiste = $logModel->yaExisteResumen($emp['ruc'], $emp['serie'], $fechaActual);
                $this->log("  {$emp['nombre']} serie={$emp['serie']}: yaExisteResumen=" . ($yaExiste ? 'SI' : 'NO'));

                if ($yaExiste) {
                    CLI::write("  ↺ {$emp['nombre']}: salta (ya existe)", 'yellow');
                    continue;
                }

                // Antes de enviar, verificar datos en BD
                $sunatModel2 = new SunatModel();
                try {
                    if ($emp['ruc'] == '20450337839') {
                        $comprobantes = $sunatModel2->getComprobantesSanMartin($fechaActual, $emp['serie']);
                    } else {
                        $comprobantes = $sunatModel2->getBoletasMarymed($fechaActual);
                    }
                    $total = count($comprobantes);
                    $this->log("  {$emp['nombre']}: {$total} comprobantes encontrados en BD");
                } catch (\Exception $e) {
                    $this->log("  {$emp['nombre']}: ERROR BD: " . $e->getMessage());
                    CLI::write("  ✗ {$emp['nombre']}: error BD - {$e->getMessage()}", 'red');
                    continue;
                }

                if (empty($comprobantes)) {
                    $this->log("  {$emp['nombre']}: SIN DATOS en BD, se salta");
                    CLI::write("  ✗ {$emp['nombre']}: sin datos en BD", 'red');
                    continue;
                }

                CLI::write("  → {$emp['nombre']}...", 'cyan');
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = [
                    'fecha' => $fechaActual,
                    'empresa_ruc' => $emp['ruc'],
                    'serie' => $emp['serie'],
                ];

                try {
                    $response = $controller->generar_resumen();
                    $data = json_decode($response->getBody(), true);
                    $status = $data['status'] ?? 'unknown';
                    $ticket = $data['ticket'] ?? 'N/A';
                    $message = $data['message'] ?? 'sin mensaje';

                    $this->log("  {$emp['nombre']}: status=$status ticket=$ticket msg=$message");

                    if ($status === 'success') {
                        CLI::write("  ✓ Ticket: $ticket", 'green');
                    } else {
                        CLI::write("  ✗ $message", 'red');
                    }
                } catch (\Throwable $e) {
                    $this->log("  {$emp['nombre']}: EXCEPCION: " . $e->getMessage());
                    CLI::error("  ✗ Excepción: " . $e->getMessage());
                }
            }

            // Facturas
            foreach ($seriesFactura as $serie) {
                try {
                    $facturas = $sunatModel->getFacturasSanMartin($fechaActual, $serie);
                    $this->log("  Facturas FA{$serie}: " . count($facturas) . " encontradas");
                } catch (\Exception $e) {
                    $this->log("  Facturas FA{$serie}: ERROR BD: " . $e->getMessage());
                    continue;
                }

                if (empty($facturas)) continue;

                foreach ($facturas as $factura) {
                    $serieFa = 'FA' . $serie;
                    $db = $logModel->db;
                    $existing = $db->table('sunat_comprobantes')
                        ->where('empresa_ruc', '20450337839')
                        ->where('tipo_doc', '01')
                        ->where('serie', $serieFa)
                        ->where('correlativo', (int)$factura->numero)
                        ->get()->getRow();

                    if ($existing) {
                        $this->log("  FA{$serie}-{$factura->numero}: ya enviada ({$existing->estado_sunat}), salta");
                        continue;
                    }

                    $_POST = ['fecha' => $fechaActual, 'serie' => $serie, 'numero' => $factura->numero];
                    try {
                        $response = $controller->enviar_factura();
                        $data = json_decode($response->getBody(), true);
                        $this->log("  FA{$serie}-{$factura->numero}: " . ($data['status'] ?? 'error') . " - " . ($data['message'] ?? ''));
                    } catch (\Exception $e) {
                        $this->log("  FA{$serie}-{$factura->numero}: EXCEPCION: " . $e->getMessage());
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

        $this->log("Tickets pendientes por consultar: " . count($pendientes));

        foreach ($pendientes as $p) {
            $this->log("Consultando ticket: {$p->ticket} ({$p->empresa_ruc})");
            $_POST = ['ticket' => $p->ticket, 'empresa_ruc' => $p->empresa_ruc];
            try {
                $response = $controller->consultar_ticket();
                $data = json_decode($response->getBody(), true);
                $this->log("Ticket {$p->ticket}: " . ($data['estado'] ?? 'error') . " - " . ($data['descripcion'] ?? $data['message'] ?? ''));
            } catch (\Exception $e) {
                $this->log("Ticket {$p->ticket}: EXCEPCION: " . $e->getMessage());
            }
            usleep(500000);
        }

        $fin = date('Y-m-d H:i:s');
        $this->log("=== CRON FINALIZADO === Fin: $fin");
    }

    private function log(string $msg)
    {
        $line = "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL;
        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }

    private function getOption(string $name): ?string
    {
        $opt = CLI::getOption($name);
        return ($opt === null || $opt === '') ? null : $opt;
    }
}
