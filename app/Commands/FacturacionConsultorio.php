<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\GreenterService;

class FacturacionConsultorio extends BaseCommand
{
    protected $group       = 'Facturacion';
    protected $name        = 'facturacion:consultorio';
    protected $description = 'Envia comprobantes de consultorio pendientes a SUNAT (tabla CM_COMPROBANTES).';

    protected $greenter;

    public function run(array $params)
    {
        CLI::write('Iniciando envio de comprobantes de consultorio...', 'green');

        $this->greenter = new GreenterService();

        $db = \Config\Database::connect('default');
        $db->initialize();
        if (!$db->connID) {
            CLI::error('No se pudo conectar a la base principal.');
            return;
        }

        $pendientes = $db->query("
            SELECT CC.*, CT.estado AS cita_estado, CT.paciente_id, CT.cliente_id, CT.horario_id, CT.total AS cita_total,
                   C.CLI_RUC_ESPOSA AS DNI, C.CLI_RUC_ESPOSO AS RUC
            FROM CM_COMPROBANTES CC
            INNER JOIN CM_CITAS CT ON CT.id = CC.cita_id
            INNER JOIN CM_PACIENTES PC ON PC.id = CT.paciente_id
            INNER JOIN CLIENTES C ON C.CLI_CODCLIE = PC.cliente_id
            WHERE CC.estado_sunat = 0
            ORDER BY CC.id
        ")->getResult();

        if (empty($pendientes)) {
            CLI::write('No hay comprobantes pendientes.', 'light_gray');
            return;
        }

        CLI::write('Encontrados ' . count($pendientes) . ' comprobantes pendientes.', 'cyan');

        foreach ($pendientes as $comp) {
            $this->enviarComprobante($db, $comp);
        }

        CLI::write('Proceso finalizado.', 'green');
    }

    private function enviarComprobante($db, $comp)
    {
        $numDoc = $comp->tipo_documento . '-' . $comp->serie . '-' . str_pad($comp->correlativo, 8, '0', STR_PAD_LEFT);
        CLI::write("Procesando: $numDoc", 'yellow');

        try {
            // Documento del cliente guardado al emitir (RUC para Factura, DNI para Boleta)
            $clienteDoc = trim($comp->cliente_num_doc ?? '');
            $clienteTipo = $comp->cliente_tipo_doc ?: (($comp->tipo_documento == 'F') ? '6' : '1');
            $clienteNombre = trim($comp->cliente_nombre ?? '');
            if (empty($clienteDoc)) {
                // Fallback: datos del paciente
                $clienteDoc = ($comp->tipo_documento == 'F') ? trim($comp->RUC ?? '') : trim($comp->DNI ?? '');
                if (empty($clienteDoc)) $clienteDoc = '-';
            }
            if (empty($clienteNombre)) $clienteNombre = 'CLIENTE GENERICO';

            // Detalles desde CM_COMPROBANTE_DETALLE (items del comprobante)
            $detalles = $db->query("
                SELECT ISNULL(D.art_key, 0) AS Codigo, D.descripcion AS Descripcion,
                       D.cantidad AS Cantidad, D.precio AS PrecioUnitario,
                       D.subtotal AS BaseImponible
                FROM CM_COMPROBANTE_DETALLE D
                WHERE D.comprobante_id = ?
                ORDER BY D.id
            ", [$comp->id])->getResultArray();

            if (empty($detalles)) {
                // Sin detalles registrados: usar un solo item generico
                $detalles = [[
                    'Codigo' => 'SERV001',
                    'Descripcion' => 'CONSULTA MEDICA',
                    'Cantidad' => 1,
                    'PrecioUnitario' => floatval($comp->monto),
                    'BaseImponible' => floatval($comp->monto)
                ]];
            }

            $totalBase = 0;
            foreach ($detalles as &$d) {
                $d['IGV'] = 0; // Exonerado
                $d['Cantidad'] = floatval($d['Cantidad'] ?: 1);
                $d['PrecioUnitario'] = floatval($d['PrecioUnitario']);
                $d['BaseImponible'] = floatval($d['BaseImponible']);
                $totalBase += $d['BaseImponible'];
            }

            // Convertir serie para Greenter (serie + correlativo)
            $serieGreenter = strtoupper($comp->tipo_documento) . substr(str_pad($comp->serie, 3, '0', STR_PAD_LEFT), -2);
            $tipoDocGreenter = ($comp->tipo_documento == 'F') ? '01' : '03';

            $headerData = [
                'Serie' => $serieGreenter,
                'Numero' => str_pad($comp->correlativo, 8, '0', STR_PAD_LEFT),
                'Fecha' => date('Y-m-d', strtotime($comp->fecha_emision)),
                'ClienteTipoDoc' => $clienteTipo,
                'ClienteNumDoc' => $clienteDoc,
                'ClienteNombre' => $clienteNombre,
                'ClienteDireccion' => '',
                'BaseImponible' => $totalBase,
                'IGV' => 0,
                'Total' => $totalBase
            ];

            $result = $this->greenter->enviarFactura($headerData, $detalles, $tipoDocGreenter);

            if (!$result->isSuccess()) {
                $err = $result->getError();
                $msgErr = $err ? ($err->getCode() . ' - ' . $err->getMessage()) : 'Error desconocido';
                CLI::error("  Error: $msgErr");
                $this->marcarRechazado($db, $comp->id, $msgErr);
                return;
            }

            // Exito: guardar ticket
            $ticket = $result->getTicket();
            CLI::write("  Enviado OK. Ticket: $ticket", 'green');
            $this->marcarEnviado($db, $comp->id, $ticket);

        } catch (\Throwable $e) {
            CLI::error('  Excepcion: ' . $e->getMessage());
            $this->marcarRechazado($db, $comp->id, substr($e->getMessage(), 0, 200));
        }
    }

    private function marcarEnviado($db, $id, $ticket)
    {
        $db->query("UPDATE CM_COMPROBANTES SET estado_sunat = 1, cdr_ticket = ?, fecha_envio = GETDATE() WHERE id = ?", [$ticket, $id]);
    }

    private function marcarRechazado($db, $id, $obs)
    {
        $db->query("UPDATE CM_COMPROBANTES SET estado_sunat = 3, observaciones = ?, fecha_envio = GETDATE() WHERE id = ?", [$obs, $id]);
    }
}
