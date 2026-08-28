<?php

namespace App\Controllers;

use App\Models\CmPacientesModel;
use App\Models\CmMedicosModel;
use App\Models\CmMedicosHorariosModel;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class CmCitas extends BaseController
{
    public function actualizar_paciente()
    {
        $db = \Config\Database::connect();
        $cita_id = $this->request->getPost('cita_id');
        $paciente_id = $this->request->getPost('paciente_id');
        $nombre = trim($this->request->getPost('nombre'));
        $dni = trim($this->request->getPost('dni'));
        $telefono = trim($this->request->getPost('telefono'));
        $fecha_nac = $this->request->getPost('fecha_nac');
        $tipo_sangre = $this->request->getPost('tipo_sangre');
        $contacto_emergencia = trim($this->request->getPost('contacto_emergencia') ?? '');
        $telefono_emergencia = trim($this->request->getPost('telefono_emergencia') ?? '');
        $alergias = trim($this->request->getPost('alergias') ?? '');
        $enfermedades_cronicas = trim($this->request->getPost('enfermedades_cronicas') ?? '');
        $observaciones_medicas = trim($this->request->getPost('observaciones_medicas') ?? '');
        
        if (!$cita_id || !$paciente_id || empty($nombre)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Datos incompletos']);
        }
        
        $cita = $db->table('CM_CITAS')->where('id', $cita_id)->get()->getRow();
        if (!$cita) return $this->response->setJSON(['status' => 'error', 'msg' => 'Cita no encontrada']);
        
        $paciente = $db->table('CM_PACIENTES')->where('id', $paciente_id)->get()->getRow();
        if (!$paciente) return $this->response->setJSON(['status' => 'error', 'msg' => 'Paciente no encontrado']);
        
        $cliente_id = $paciente->cliente_id;
        $dnilen = strlen($dni);
        
        $db->query("UPDATE CLIENTES SET CLI_NOMBRE = ?, CLI_NOMBRE_ESPOSO = ?, CLI_RUC_ESPOSA = ?, CLI_RUC_ESPOSO = ?, CLI_TELEF1 = ?, CLI_FECHA_NAC = ? WHERE CLI_CODCLIE = ?",
            [substr($nombre, 0, 120), substr($nombre, 0, 120), $dnilen == 8 ? $dni : '', $dnilen == 11 ? $dni : '', substr($telefono, 0, 12), $fecha_nac ?: null, $cliente_id]);
        
        $db->query("UPDATE CM_PACIENTES SET tipo_sangre=?, contacto_emergencia=?, telefono_emergencia=?, alergias=?, enfermedades_cronicas=?, observaciones_medicas=? WHERE id=?",
            [$tipo_sangre, $contacto_emergencia, $telefono_emergencia, $alergias, $enfermedades_cronicas, $observaciones_medicas, $paciente_id]);
        
        return $this->response->setJSON(['status' => 'success', 'msg' => 'Datos actualizados']);
    }

    public function ver_paciente()
    {
        $cita_id = $this->request->getPost('cita_id');
        if (!$cita_id) return $this->response->setJSON(['status' => 'error']);
        
        $db = \Config\Database::connect();
        $cita = $db->query("
            SELECT cc.*, P.id AS paciente_id, C.CLI_NOMBRE, C.CLI_RUC_ESPOSA AS DNI, C.CLI_TELEF1,
                   C.CLI_FECHA_NAC, FLOOR(DATEDIFF(DAY, C.CLI_FECHA_NAC, GETDATE()) / 365.25) AS edad,
                   H.fecha_especifica, (M.nombres + ' ' + M.apellidos) AS medico, M.especialidad,
                   P.tipo_sangre, P.alergias, P.enfermedades_cronicas, P.contacto_emergencia, P.telefono_emergencia, P.observaciones_medicas,
                   CASE cc.estado WHEN 0 THEN 'Inscrito' WHEN 1 THEN 'Confirmado' WHEN 4 THEN 'Pendiente' WHEN 2 THEN 'Atendido' WHEN 3 THEN 'Anulado' END AS estado_nombre,
                   STUFF((SELECT ', ' + A.ART_NOMBRE FROM CM_CITAS_SERVICIOS CS INNER JOIN ARTI A ON A.ART_KEY = CS.art_key WHERE CS.cita_id = cc.id FOR XML PATH('')), 1, 2, '') AS servicios_extra
            FROM CM_CITAS cc
            INNER JOIN CM_PACIENTES P ON P.id = cc.paciente_id
            " . $this->joinClientesDedup() . "
            INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = cc.horario_id
            LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id
            WHERE cc.id = ?
        ", [$cita_id])->getRow();
        
        if (!$cita) return $this->response->setJSON(['status' => 'error', 'msg' => 'Cita no encontrada']);
        
        // Citas previas del paciente
        $historial = $db->query("
            SELECT cc.id, H.fecha_especifica, (M.nombres + ' ' + M.apellidos) AS medico,
                   cc.total, CASE cc.estado WHEN 0 THEN 'Inscrito' WHEN 1 THEN 'Confirmado' WHEN 4 THEN 'Pendiente' WHEN 2 THEN 'Atendido' WHEN 3 THEN 'Anulado' END AS estado_nombre
            FROM CM_CITAS cc
            INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = cc.horario_id
            LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id
            WHERE cc.paciente_id = ? AND cc.id <> ?
            ORDER BY cc.id DESC
        ", [$cita->paciente_id, $cita_id])->getResult();
        
        return $this->response->setJSON(['status' => 'success', 'cita' => $cita, 'historial' => $historial]);
    }

    public function listado_data()
    {
        $db = \Config\Database::connect();
        $horario_id = $this->request->getPost('horario_id');
        $estado = $this->request->getPost('estado');
        $fecha_desde = $this->request->getPost('fecha_desde');
        $fecha_hasta = $this->request->getPost('fecha_hasta');
        
        $params = [];
        $sql = "SELECT cc.id, cc.paciente_id, cc.estado, cc.total, cc.saldo,
                       H.fecha_especifica, (M.nombres + ' ' + M.apellidos) AS medico,
                       C.CLI_NOMBRE, C.CLI_RUC_ESPOSA AS DNI, C.CLI_TELEF1, C.CLI_FECHA_NAC,
                       FLOOR(DATEDIFF(DAY, C.CLI_FECHA_NAC, GETDATE()) / 365.25) AS edad,
                       CASE cc.estado WHEN 0 THEN 'Inscrito' WHEN 1 THEN 'Confirmado' WHEN 4 THEN 'Pendiente' WHEN 2 THEN 'Atendido' WHEN 3 THEN 'Anulado' END AS estado_nombre
                FROM CM_CITAS cc
                INNER JOIN CM_PACIENTES P ON P.id = cc.paciente_id
                " . $this->joinClientesDedup() . "
                INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = cc.horario_id
                LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id
                WHERE 1=1
                  AND H.fecha_especifica >= CAST(GETDATE() AS DATE)";
        
        if ($horario_id) { $sql .= " AND cc.horario_id = ?"; $params[] = $horario_id; }
        if ($estado !== null && $estado !== '') { $sql .= " AND cc.estado = ?"; $params[] = intval($estado); }
        // Filtro por fecha de inscripcion (CC.created_at): vacio = todos
        if ($fecha_desde) { $sql .= " AND cc.created_at >= ?"; $params[] = $fecha_desde; }
        if ($fecha_hasta) { $sql .= " AND cc.created_at < DATEADD(DAY, 1, ?)"; $params[] = $fecha_hasta; }
        
        $sql .= " ORDER BY cc.id DESC";
        
        $citas = $db->query($sql, $params)->getResult();
        return $this->response->setJSON($citas);
    }

    public function listado()
    {
        $db = \Config\Database::connect();
        
        // Filtros
        $horario_id = $this->request->getGet('horario_id');
        $estado = $this->request->getGet('estado');
        $fecha_desde = $this->request->getGet('fecha_desde');
        $fecha_hasta = $this->request->getGet('fecha_hasta');
        
        $params = [];
        $sql = "SELECT cc.*, H.fecha_especifica, H.hora_inicio,
                       (M.nombres + ' ' + M.apellidos) AS medico, M.especialidad,
                       C.CLI_NOMBRE, C.CLI_RUC_ESPOSA AS DNI,
                       ISNULL(CS.servicios_count, 0) AS servicios_count,
                       CASE cc.estado WHEN 0 THEN 'Inscrito' WHEN 1 THEN 'Confirmado' WHEN 4 THEN 'Pendiente' WHEN 2 THEN 'Atendido' WHEN 3 THEN 'Anulado' END AS estado_nombre
                FROM CM_CITAS cc
                INNER JOIN CM_PACIENTES P ON P.id = cc.paciente_id
                " . $this->joinClientesDedup() . "
                INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = cc.horario_id
                LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id
                LEFT JOIN (SELECT cita_id, COUNT(*) AS servicios_count FROM CM_CITAS_SERVICIOS GROUP BY cita_id) CS ON CS.cita_id = cc.id
                WHERE 1=1
                  AND H.fecha_especifica >= CAST(GETDATE() AS DATE)";
        
        if ($horario_id) { $sql .= " AND cc.horario_id = ?"; $params[] = $horario_id; }
        if ($estado !== null && $estado !== '') { $sql .= " AND cc.estado = ?"; $params[] = intval($estado); }
        // Filtro por fecha de inscripcion (CC.created_at): vacio = todos
        if ($fecha_desde) { $sql .= " AND cc.created_at >= ?"; $params[] = $fecha_desde; }
        if ($fecha_hasta) { $sql .= " AND cc.created_at < DATEADD(DAY, 1, ?)"; $params[] = $fecha_hasta; }
        
        $sql .= " ORDER BY H.fecha_especifica DESC, cc.created_at DESC";
        
        $citas = $db->query($sql, $params)->getResult();
        
        // Horarios para filtro: solo campañas de hoy hacia adelante (las pasadas no se muestran)
        $horarios = $db->query("SELECT H.id, H.fecha_especifica, (M.nombres + ' ' + M.apellidos) AS medico FROM CM_MEDICOS_HORARIOS H LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id WHERE H.estado = 1 AND H.fecha_especifica >= CAST(GETDATE() AS DATE) ORDER BY H.fecha_especifica DESC")->getResult();
        
        // Series
        $local_pago = session()->get('caja') ? str_pad(session()->get('caja'), 2, '0', STR_PAD_LEFT) : '01';
        $series = $this->getSeriesLocal($db, $local_pago);

        return view('cm_citas/listado', [
            'citas' => $citas,
            'horarios' => $horarios,
            'filtros' => ['horario_id' => $horario_id, 'estado' => $estado, 'fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta],
            'titulo' => 'Todas las Citas',
            'series' => $series,
            'local_pago' => $local_pago,
            'menu' => ['p' => 40, 'i' => 50]
        ]);
    }

    public function index()
    {
        $db = \Config\Database::connect();
        
        // Traer horarios activos con médicos y conteo de citas migradas
        $sql = "SELECT h.*, m.nombres, m.apellidos, m.especialidad,
                       ISNULL(cc.citas_count, 0) AS pacientes_inscritos,
                       ISNULL(P.PRE_PRE1, 0) AS precio,
                       A.ART_NOMBRE AS servicio_nombre
                FROM CM_MEDICOS_HORARIOS h
                LEFT JOIN CM_MEDICOS m ON m.id = h.medico_id
                LEFT JOIN ARTI A ON A.ART_KEY = h.cod_art_servicio
                LEFT JOIN PRECIOS P ON P.PRE_CODART = h.cod_art_servicio AND P.PRE_FLAG_UNIDAD = 'A' AND P.PRE_CODCIA = 25
                LEFT JOIN (
                    SELECT horario_id, COUNT(*) AS citas_count
                    FROM CM_CITAS
                    WHERE estado IN (0, 1, 2, 4)
                    GROUP BY horario_id
                ) cc ON cc.horario_id = h.id
                WHERE h.estado = 1
                  AND h.fecha_especifica >= CAST(GETDATE() AS DATE)
                ORDER BY h.fecha_especifica ASC, h.hora_inicio ASC";
        
        $campanias = $db->query($sql)->getResult();

        $local_pago = session()->get('caja') ? str_pad(session()->get('caja'), 2, '0', STR_PAD_LEFT) : '01';
        $series = $this->getSeriesLocal($db, $local_pago);

        $data = [
            'titulo' => 'Dashboard de Citas Médicas',
            'campanias' => $campanias,
            'series' => $series,
            'local_pago' => $local_pago,
            'menu' => ['p' => 40, 'i' => 46]
        ];
        
        return view('cm_citas/index', $data);
    }

    public function get_servicios_disponibles()
    {
        $db = \Config\Database::connect();
        $servicios = $db->query("
            SELECT A.ART_KEY, A.ART_NOMBRE, ISNULL(P.PRE_PRE1, 0) AS precio
            FROM ARTI A
            LEFT JOIN PRECIOS P ON P.PRE_CODART = A.ART_KEY AND P.PRE_FLAG_UNIDAD = 'A' AND P.PRE_CODCIA = 25
            WHERE A.ART_FAMILIA = 594 AND A.ART_SITUACION = 0
            ORDER BY A.ART_NOMBRE
        ")->getResult();
        return $this->response->setJSON($servicios);
    }

    public function get_pacientes_inscritos()
    {
        $horario_id = $this->request->getPost('horario_id');
        if (!$horario_id) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();
        $sql = "SELECT cc.id, cc.paciente_id, cc.estado, cc.orden, cc.fecha,
                       cc.saldo, cc.total,
                       c.CLI_NOMBRE, c.CLI_RUC_ESPOSA AS DNI, c.CLI_TELEF1,
                       CASE cc.estado 
                           WHEN 0 THEN 'Inscrito'
                           WHEN 1 THEN 'Confirmado'
                           WHEN 4 THEN 'Pendiente'
                           WHEN 2 THEN 'Atendido'
                           WHEN 3 THEN 'Anulado'
                           ELSE 'Desconocido'
                       END AS estado_nombre
                FROM CM_CITAS cc
                INNER JOIN CM_PACIENTES p ON p.id = cc.paciente_id
                " . $this->joinClientesDedup('p') . "
                WHERE cc.horario_id = ?
                  AND cc.estado IN (0, 1, 2, 4)
                ORDER BY cc.orden, cc.created_at";
        
        $result = $db->query($sql, [$horario_id])->getResult();
        return $this->response->setJSON($result);
    }

    public function cobrar_pendiente()
    {
        $db = \Config\Database::connect();
        $cita_id = $this->request->getPost('cita_id');
        
        if ($cita_id === null || $cita_id === '') {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Falta ID de cita']);
        }
        
        // Obtener cita con horario y precio
        $cita = $db->query("
            SELECT CC.*, P.cliente_id,
                   H.cod_art_servicio, ISNULL(PR.PRE_PRE1, 0) AS precio
            FROM CM_CITAS CC
            INNER JOIN CM_PACIENTES P ON P.id = CC.paciente_id
            INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = CC.horario_id
            LEFT JOIN PRECIOS PR ON PR.PRE_CODART = H.cod_art_servicio AND PR.PRE_FLAG_UNIDAD = 'A' AND PR.PRE_CODCIA = 25
            WHERE CC.id = ?
        ", [$cita_id])->getRow();
        
        if (!$cita) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Cita no encontrada']);
        }
        
        if ($cita->estado != 0) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'La cita ya fue pagada o atendida']);
        }
        
        $monto_total = $cita->precio ? floatval($cita->precio) : 50.00;
        $cod_art = $cita->cod_art_servicio ?? 0;
        
        if ($monto_total <= 0 || !$cod_art) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'El horario no tiene servicio/precio asignado.']);
        }
        
        // Servicios adicionales ya registrados en la reserva (no se vuelven a insertar)
        $ya_registrados = [];
        foreach ($db->query("SELECT art_key, precio, cantidad FROM CM_CITAS_SERVICIOS WHERE cita_id = ?", [$cita_id])->getResult() as $s) {
            $ya_registrados[] = intval($s->art_key);
            $monto_total += floatval($s->precio) * floatval($s->cantidad);
        }
        
        // Servicios extra agregados en el momento del cobro
        $servicios_extra = array_values(array_diff(
            array_map('intval', (array)($this->request->getPost('servicios_extra') ?: [])),
            $ya_registrados
        ));
        foreach ($servicios_extra as $art_key) {
            $p = $db->query("SELECT ISNULL(PRE_PRE1,0) AS precio FROM PRECIOS WHERE PRE_CODART=? AND PRE_FLAG_UNIDAD='A' AND PRE_CODCIA='25'", [$art_key])->getRow();
            $monto_total += $p ? floatval($p->precio) : 0;
        }
        
        $forma_pago = $this->request->getPost('forma_pago') ?? 'EFECTIVO';
        $local_pago = session()->get('caja') ? str_pad(session()->get('caja'), 2, '0', STR_PAD_LEFT) : '01';
        $tipo_comp = $this->request->getPost('tipo_comprobante') ?: 'B';
        $tipo_nombre = $tipo_comp == 'F' ? 'FACTURA' : ($tipo_comp == 'G' ? 'GUIA' : 'BOLETA');
        
        // Nuevo flujo: SOLO marcar pagado + registrar en CM_PAGOS (sin comprobante SUNAT)
        $db->transStart();
        
        $pago = $this->registrarPago($db, $cita_id, $monto_total, $forma_pago, $local_pago);
        
        $obs_actual = $cita->observaciones ? trim($cita->observaciones) : '';
        $nueva_obs = trim($obs_actual . ' | Pagado. Ticket: ' . $pago['ticket'] . ' - Comprobante: ' . $tipo_nombre, ' |');
        $db->query("UPDATE CM_CITAS SET estado = 1, total = ?, saldo = 0, observaciones = ?, updated_at = GETDATE() WHERE id = ?", [$monto_total, substr($nueva_obs, 0, 500), $cita_id]);
        
        // Procesar servicios extra (solo CM_CITAS_SERVICIOS, sin FACART)
        if (!empty($servicios_extra)) {
            $this->procesarServiciosExtra($db, $cita_id, $servicios_extra);
        }
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al registrar el pago']);
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'msg' => 'Pago registrado. Ticket: ' . $pago['ticket'] . ' | S/ ' . number_format($monto_total, 2),
            'ticket' => ['nro' => $pago['ticket'], 'monto' => $monto_total, 'pago_id' => $pago['id'], 'cita_id' => $cita_id]
        ]);
    }

    public function cobrar_procedimiento()
    {
        $db = \Config\Database::connect();
        $cita_id = $this->request->getPost('cita_id');
        $art_key = $this->request->getPost('art_key');
        $precio = floatval($this->request->getPost('precio'));
        $observacion = $this->request->getPost('observacion') ?? '';
        
        if (!$cita_id || !$art_key || $precio <= 0) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Datos incompletos']);
        }
        
        $cita = $db->table('CM_CITAS')->where('id', $cita_id)->get()->getRow();
        if (!$cita || !in_array($cita->estado, [1, 4])) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'La cita ya está atendida o no está en estado válido para agregar servicios']);
        }
        
        $paciente = $db->table('CM_PACIENTES')->where('id', $cita->paciente_id)->get()->getRow();
        $local_pago = session()->get('caja') ? str_pad(session()->get('caja'), 2, '0', STR_PAD_LEFT) : '01';
        $forma_pago = $this->request->getPost('forma_pago') ?? 'EFECTIVO';
        
        $db->transStart();
        
        // Registrar en CM_CITAS_SERVICIOS
        $db->table('CM_CITAS_SERVICIOS')->insert([
            'cita_id'       => $cita_id,
            'art_key'       => $art_key,
            'precio'        => $precio,
            'cantidad'      => 1,
            'observaciones' => $observacion,
        ]);
        
        // Registrar pago en CM_PAGOS (ticket de constancia)
        $pago = $this->registrarPago($db, $cita_id, $precio, $forma_pago, $local_pago);
        
        // Actualizar total de la cita
        $db->query("UPDATE CM_CITAS SET total = total + ?, updated_at = GETDATE() WHERE id = ?", [$precio, $cita_id]);
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al registrar el pago del procedimiento']);
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'msg' => 'Pago registrado. Ticket: ' . $pago['ticket'] . ' | S/ ' . number_format($precio, 2),
            'ticket' => ['nro' => $pago['ticket'], 'monto' => $precio, 'pago_id' => $pago['id'], 'cita_id' => $cita_id]
        ]);
    }

    public function ticket($pago_id = null)
    {
        if (!$pago_id) return redirect()->to('cmCitas/listado');
        $db = \Config\Database::connect();
        $pago = $this->getPagoData($db, $pago_id);
        if (!$pago) return redirect()->to('cmCitas/listado');
        return view('cm_citas/ticket', ['pago' => $pago]);
    }

    public function imprimir_ticket_termico($pago_id = null)
    {
        $pago_id = $pago_id ?? $this->request->getPost('pago_id');
        if (!$pago_id) return $this->response->setJSON(['status' => 'error', 'msg' => 'Falta pago']);
        $db = \Config\Database::connect();
        $pago = $this->getPagoData($db, $pago_id);
        if (!$pago) return $this->response->setJSON(['status' => 'error', 'msg' => 'Pago no encontrado']);

        $local = intval($pago->local_pago);
        if ($local == 0) $local = 1;

        $imp = config('Impresoras');
        $ruta = $imp->ticketeras[$local] ?? null;
        if (!$ruta) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No hay ticketera configurada para el local ' . $local]);
        }

        try {
            $connector = new WindowsPrintConnector($ruta);
            $printer = new Printer($connector);

            // Ticket para el paciente (constancia de pago)
            $this->printTicketPaciente($printer, $pago, $local);

            // Ticket de depósito de dinero (solo si el pago fue adelantado en otro local)
            if ($local != 4) {
                $this->printTicketDeposito($printer, $pago, $local);
            }

            $printer->close();
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Ticket enviado a la ticketera']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al imprimir: ' . $e->getMessage()]);
        }
    }

    private function getPagoData($db, $pago_id)
    {
        return $db->query("
            SELECT P.*, C.CLI_NOMBRE, C.CLI_RUC_ESPOSA AS DNI, H.fecha_especifica, H.hora_inicio,
                   (M.nombres + ' ' + M.apellidos) AS medico, CC.estado AS cita_estado
            FROM CM_PAGOS P
            INNER JOIN CM_CITAS CC ON CC.id = P.cita_id
            INNER JOIN CM_PACIENTES PC ON PC.id = CC.paciente_id
            " . $this->joinClientesDedup('PC') . "
            INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = CC.horario_id
            LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id
            WHERE P.id = ?
        ", [$pago_id])->getRow();
    }

    private function getMarca($local)
    {
        $imp = config('Impresoras');
        return $imp->marcas[$local] ?? $imp->marcas[1];
    }

    private function printCabecera($printer, $local)
    {
        $fac = config('Facturacion');
        $marca = $this->getMarca($local);
        try {
            $logo = EscposImage::load(FCPATH . 'dist/img/' . $marca['logo'], false);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->graphics($logo);
            $printer->feed();
        } catch (\Exception $e) {
            // logo no disponible, continuar sin imagen
        }
        $printer->setFont(Printer::FONT_B);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text($marca['nombre'] . "\n");
        $printer->text("RUC: " . $fac->ruc . "\n");
        $printer->text("Jr. Huallaga Nro 601 - Juanjuí - Mcal Cacéres - San Martín\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("----------------------------------------------------------------\n");
    }

    private function printTicketPaciente($printer, $pago, $local)
    {
        $this->printCabecera($printer, $local);
        $printer->setFont(Printer::FONT_B);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(1, 1);
        $printer->text("CONSTANCIA DE PAGO\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("----------------------------------------------------------------\n");
        $printer->text("TICKET Nro  : " . ($pago->ticket_nro ?: 'PAGO-' . $pago->id) . "\n");
        $printer->text("Fecha       : " . date('d/m/Y H:i', strtotime($pago->fecha_pago)) . "\n");
        $printer->text("----------------------------------------------------------------\n");
        $printer->text("Paciente    : " . trim($pago->CLI_NOMBRE) . "\n");
        if (!empty($pago->DNI)) $printer->text("DNI         : " . trim($pago->DNI) . "\n");
        $printer->text("Medico      : " . trim($pago->medico ?: '-') . "\n");
        if ($pago->fecha_especifica) $printer->text("Fecha cita  : " . date('d/m/Y', strtotime($pago->fecha_especifica)) . "\n");
        if ($pago->hora_inicio) $printer->text("Hora cita   : " . substr($pago->hora_inicio, 0, 5) . "\n");
        $printer->text("Forma pago  : " . trim($pago->forma_pago) . "\n");
        $printer->text("----------------------------------------------------------------\n");
        $printer->setTextSize(2, 2);
        $printer->text("MONTO: S/ " . number_format($pago->monto, 2) . "\n");
        $printer->setTextSize(1, 1);
        $printer->text("----------------------------------------------------------------\n");
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Este ticket es solo una constancia.\n");
        $printer->text("El comprobante (Boleta/Factura)\nse emitira el dia de la cita.\n");
        $printer->feed(3);
        $printer->cut();
        $printer->pulse();
    }

    private function printTicketDeposito($printer, $pago, $local)
    {
        $imp = config('Impresoras');
        $nombreLocal = $imp->locales[$local] ?? 'LOCAL';
        $this->printCabecera($printer, $local);
        $printer->setFont(Printer::FONT_B);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("DEPOSITO DE DINERO\n");
        $printer->setTextSize(3, 3);
        $printer->text($nombreLocal . "\n");
        $printer->setTextSize(1, 1);
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("----------------------------------------------------------------\n");
        $printer->text("Fecha       : " . date('d-m-Y', strtotime($pago->fecha_pago)) . "\n");
        $printer->text("Responsable : " . trim($pago->usuario_cajero ?: 'CAJERO') . "\n");
        $printer->text("Ticket orig.: " . ($pago->ticket_nro ?: '') . "\n");
        $printer->text("----------------------------------------------------------------\n");
        $printer->text("CONCEPTO    : PAGO ADELANTADO CITA MEDICA\n");
        $printer->text("IMPORTE TOTAL : S/ ");
        $printer->setTextSize(2, 2);
        $printer->text(number_format($pago->monto, 2) . "\n");
        $printer->setTextSize(1, 1);
        $printer->text("----------------------------------------------------------------\n");
        $printer->feed(3);
        $printer->cut();
        $printer->pulse();
    }

    public function cambiar_estado()
    {
        $db = \Config\Database::connect();
        $cita_id = $this->request->getPost('cita_id');
        $nuevo_estado = $this->request->getPost('estado');
        
        if (!$cita_id || !in_array($nuevo_estado, ['2', '3'])) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Datos inválidos']);
        }
        
        $cita = $db->table('CM_CITAS')->where('id', $cita_id)->get()->getRow();
        if (!$cita) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Cita no encontrada']);
        }
        
        if ($nuevo_estado == '2' && !in_array($cita->estado, [1, 4])) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Solo se puede atender/cerrar una cita pagada o pendiente']);
        }
        
        // === ANULACION: solo citas con reserva sin pago (estado 0) ===
        if ($nuevo_estado == '3') {
            if ($cita->estado != 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'msg' => 'Solo se puede anular una cita con reserva sin pago. Las citas pagadas o atendidas requieren un proceso de anulación con nota de crédito (pendiente de implementar).'
                ]);
            }
            
            $db->transStart();
            
            $db->query("UPDATE CM_CITAS SET estado = 3, updated_at = GETDATE() WHERE id = ?", [$cita_id]);
            
            // Actualizar cupos
            $db->query("UPDATE CM_MEDICOS_HORARIOS SET cupos_ocupados = (
                            SELECT COUNT(*) FROM CM_CITAS WHERE horario_id = ? AND estado IN (0,1,2,4)
                        ) WHERE id = ?", [$cita->horario_id, $cita->horario_id]);
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al anular la cita']);
            }
            
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Cita anulada']);
        }
        
        // === ATENDER / CERRAR ===
        $db->query("UPDATE CM_CITAS SET estado = ?, updated_at = GETDATE() WHERE id = ?", [(int)$nuevo_estado, $cita_id]);
        
        // Actualizar cupos
        $db->query("UPDATE CM_MEDICOS_HORARIOS SET cupos_ocupados = (
                        SELECT COUNT(*) FROM CM_CITAS WHERE horario_id = ? AND estado IN (0,1,2,4)
                    ) WHERE id = ?", [$cita->horario_id, $cita->horario_id]);
        
        $msg = ($cita->estado == 4) ? 'Cita cerrada. Exámenes pendientes finalizados.' : 'Cita marcada como Atendido';
        return $this->response->setJSON(['status' => 'success', 'msg' => $msg]);
    }

    private function procesarServiciosExtra($db, $cita_id, $servicios_ids)
    {
        if (empty($servicios_ids)) return;
        
        $ids = is_array($servicios_ids) ? $servicios_ids : explode(',', $servicios_ids);
        
        foreach ($ids as $art_key) {
            $art_key = intval($art_key);
            if (!$art_key) continue;
            
            $precio = $db->query("SELECT ISNULL(PRE_PRE1, 0) AS precio FROM PRECIOS WHERE PRE_CODART = ? AND PRE_FLAG_UNIDAD = 'A' AND PRE_CODCIA = '25'", [$art_key])->getRow();
            $monto = $precio ? floatval($precio->precio) : 0;
            if ($monto <= 0) continue;
            
            // Solo registrar en CM_CITAS_SERVICIOS (sin FACART/ALLOG: no hay comprobante SUNAT)
            $db->table('CM_CITAS_SERVICIOS')->insert([
                'cita_id' => $cita_id, 'art_key' => $art_key, 'precio' => $monto, 'cantidad' => 1,
            ]);
        }
    }

    /**
     * Registra el pago y numera el ticket a partir del IDENTITY del propio registro,
     * de modo que dos cajas simultaneas nunca obtengan el mismo TKT.
     *
     * @return array{id:int, ticket:string}
     */
    private function registrarPago($db, $cita_id, $monto, $forma_pago, $local_pago)
    {
        $usuario = session()->get('nombre') ?? session()->get('usuario') ?? 'CAJERO';
        $db->table('CM_PAGOS')->insert([
            'cita_id'      => $cita_id,
            'monto'        => $monto,
            'forma_pago'   => $forma_pago,
            'local_pago'   => $local_pago,
            'usuario_cajero' => $usuario,
            'estado'       => 1,
        ]);
        $pago_id = $this->getLastInsertId($db);
        $ticket_nro = 'TKT-' . str_pad($pago_id, 6, '0', STR_PAD_LEFT);
        $db->table('CM_PAGOS')->where('id', $pago_id)->update(['ticket_nro' => $ticket_nro]);

        return ['id' => $pago_id, 'ticket' => $ticket_nro];
    }

    public function reservar_cita()
    {
        $db = \Config\Database::connect();
        
        $horario_id  = $this->request->getPost('horario_id');
        $paciente_id = $this->request->getPost('paciente_id');
        $pagar_ahora = $this->request->getPost('pagar_ahora'); // '1' o '0'
        
        if (!$horario_id || ($paciente_id === null || $paciente_id === '')) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Faltan datos de horario o paciente']);
        }
        
        // Si paciente_id es 0, viene de CLIENTES sin CM_PACIENTES → auto-crear
        if ($paciente_id == '0' || $paciente_id === 0) {
            $cliente_id = $this->request->getPost('cliente_id');
            if (!$cliente_id) {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'Falta cliente_id para crear paciente']);
            }
            $db->query("INSERT INTO CM_PACIENTES (cliente_id, estado) VALUES (?, 1)", [$cliente_id]);
            $paciente_id = $this->getLastInsertId($db);
        }
        
        // Obtener paciente
        $paciente = $db->table('CM_PACIENTES')->where('id', $paciente_id)->get()->getRow();
        if (!$paciente) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Paciente no encontrado']);
        }
        $cliente_id = $paciente->cliente_id;
        
        // Obtener horario con artículo de servicio y precio real
        $horario = $db->query("
            SELECT H.*, P.PRE_PRE1 AS precio
            FROM CM_MEDICOS_HORARIOS H
            LEFT JOIN PRECIOS P ON P.PRE_CODART = H.cod_art_servicio AND P.PRE_FLAG_UNIDAD = 'A' AND P.PRE_CODCIA = 25
            WHERE H.id = ?
        ", [$horario_id])->getRow();
        
        if (!$horario || ($horario->cupos_ocupados >= $horario->cupos_totales)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No hay cupos disponibles']);
        }
        
        $monto_total = $horario->precio ? floatval($horario->precio) : 50.00;
        $cod_art_servicio = $horario->cod_art_servicio ?? 0;
        
        if ($pagar_ahora == '1' && ($monto_total <= 0 || !$cod_art_servicio)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'El horario no tiene servicio/precio asignado. Configúrelo en cmHorarios.']);
        }
        
        // Calcular servicios extra
        $servicios_extra = $this->request->getPost('servicios_extra') ?: [];
        $total_extra = 0;
        if (!empty($servicios_extra)) {
            foreach ($servicios_extra as $art_key) {
                $p = $db->query("SELECT ISNULL(PRE_PRE1,0) AS precio FROM PRECIOS WHERE PRE_CODART=? AND PRE_FLAG_UNIDAD='A' AND PRE_CODCIA='25'", [intval($art_key)])->getRow();
                $total_extra += $p ? floatval($p->precio) : 0;
            }
        }
        $monto_total += $total_extra;
        
        // Si paga ahora, SOLO se registra el pago (sin generar comprobante SUNAT)
        $ticket_nro = null;
        $pago_id = null;
        $local_pago = session()->get('caja') ? str_pad(session()->get('caja'), 2, '0', STR_PAD_LEFT) : '01';
        if ($pagar_ahora == '1') {
            $forma_pago = $this->request->getPost('forma_pago') ?? 'EFECTIVO';
            $estado = 1; // pagado
            $msg = 'Pago registrado. El comprobante se emitirá el día de la consulta.';
        } else {
            $forma_pago = 'EFECTIVO';
            $estado = 0; // inscrito sin pago
            $msg = 'Reserva confirmada. Pago pendiente de S/ ' . number_format($monto_total, 2) . ' para el día de la consulta.';
        }
        
        $db->transStart();
        
        // Registrar cita
        $db->table('CM_CITAS')->insert([
            'paciente_id'    => $paciente_id,
            'horario_id'     => $horario_id,
            'cliente_id'     => $cliente_id,
            'estado'         => $estado,
            'orden'          => 0,
            'orden_atencion' => 0,
            'total'          => $pagar_ahora == '1' ? $monto_total : 0,
            'saldo'          => $pagar_ahora == '1' ? 0 : $monto_total,
            'observaciones'  => $pagar_ahora == '1' ? 'Pagado' : 'Pago pendiente',
            'local_origen'   => $local_pago,
        ]);

        $cita_id = $this->getLastInsertId($db);
        
        // Registrar pago en CM_PAGOS si pagó (ticket de constancia para el cajero)
        if ($pagar_ahora == '1') {
            $pago = $this->registrarPago($db, $cita_id, $monto_total, $forma_pago, $local_pago);
            $ticket_nro = $pago['ticket'];
            $pago_id = $pago['id'];
            $msg = 'Pago registrado. Ticket: ' . $ticket_nro . ' | S/ ' . number_format($monto_total, 2) . '. El comprobante se emitirá el día de la consulta.';
        }
        
        // Los servicios adicionales se registran siempre: si no se paga ahora ya estan
        // sumados al saldo de la cita y deben cobrarse el dia de la consulta.
        if (!empty($servicios_extra)) {
            $this->procesarServiciosExtra($db, $cita_id, $servicios_extra);
        }
        
        // Actualizar cupos
        $db->query("UPDATE CM_MEDICOS_HORARIOS SET cupos_ocupados = (
                        SELECT COUNT(*) FROM CM_CITAS WHERE horario_id = ? AND estado IN (0,1,2,4)
                    ) WHERE id = ?", [$horario_id, $horario_id]);
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al registrar la cita']);
        }
        
        return $this->response->setJSON([
            'status'       => 'success',
            'msg'          => $msg,
            'estado'       => $estado,
            'ticket'       => ['nro' => $ticket_nro, 'monto' => $monto_total, 'pago_id' => $pago_id, 'cita_id' => $cita_id],
        ]);
    }

    public function reporte()
    {
        $db = \Config\Database::connect();
        $mes = $this->request->getGet('mes') ?: date('m');
        $anio = $this->request->getGet('anio') ?: date('Y');

        $pagos = $db->query("
            SELECT P.*, C.CLI_NOMBRE, H.fecha_especifica, (M.nombres + ' ' + M.apellidos) AS medico,
                   CASE P.estado WHEN 1 THEN 'Pagado' WHEN 2 THEN 'Comprobante emitido' WHEN 3 THEN 'Anulado' END AS estado_nombre
            FROM CM_PAGOS P
            INNER JOIN CM_CITAS CC ON CC.id = P.cita_id
            INNER JOIN CM_PACIENTES PC ON PC.id = CC.paciente_id
            " . $this->joinClientesDedup('PC') . "
            INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = CC.horario_id
            LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id
            WHERE YEAR(P.fecha_pago) = ? AND MONTH(P.fecha_pago) = ?
            ORDER BY P.fecha_pago DESC
        ", [$anio, $mes])->getResult();

        $comprobantes = $db->query("
            SELECT CM.*, C.CLI_NOMBRE,
                   CASE CM.estado_sunat WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Enviado' WHEN 2 THEN 'Aceptado' WHEN 3 THEN 'Rechazado' WHEN 4 THEN 'Anulado' END AS sunat_nombre
            FROM CM_COMPROBANTES CM
            INNER JOIN CM_CITAS CC ON CC.id = CM.cita_id
            INNER JOIN CM_PACIENTES PC ON PC.id = CC.paciente_id
            " . $this->joinClientesDedup('PC') . "
            WHERE YEAR(CM.fecha_emision) = ? AND MONTH(CM.fecha_emision) = ?
            ORDER BY CM.fecha_emision DESC
        ", [$anio, $mes])->getResult();

        $total_pagos = 0;
        foreach ($pagos as $p) $total_pagos += $p->monto;

        $total_emitidos = 0;
        foreach ($comprobantes as $c) $total_emitidos += $c->monto;

        return view('cm_citas/reporte', [
            'pagos' => $pagos,
            'comprobantes' => $comprobantes,
            'total_pagos' => $total_pagos,
            'total_emitidos' => $total_emitidos,
            'mes' => $mes,
            'anio' => $anio,
            'titulo' => 'Reporte de Consultorio',
            'menu' => ['p' => 40, 'i' => 50]
        ]);
    }

    public function emitir_comprobante()
    {
        $db = \Config\Database::connect();
        $cita_id = $this->request->getPost('cita_id');
        $tipo_doc = $this->request->getPost('tipo_documento') ?: 'B'; // 'B' Boleta, 'F' Factura, 'G' Guia
        $local_id = $this->request->getPost('local_id') ?: (session()->get('caja') ? str_pad(session()->get('caja'), 2, '0', STR_PAD_LEFT) : '01');
        $servicios_ids = $this->request->getPost('servicios_ids') ?: []; // items a incluir
        $cliente_nombre = trim($this->request->getPost('cliente_nombre') ?? '');
        $cliente_tipo_doc = $this->request->getPost('cliente_tipo_doc') ?: '';
        $cliente_num_doc = trim($this->request->getPost('cliente_num_doc') ?? '');
        
        if (!$cita_id) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Falta cita_id']);
        }
        
        $cita = $db->table('CM_CITAS')->where('id', $cita_id)->get()->getRow();
        if (!$cita) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Cita no encontrada']);
        }
        if (!in_array($cita->estado, [1, 2, 4])) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Solo se puede emitir comprobante de una cita pagada, pendiente o atendida']);
        }
        
        // Factura requiere RUC + razon social
        if ($tipo_doc == 'F') {
            if (empty($cliente_num_doc) || strlen($cliente_num_doc) != 11) {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'Para Factura es obligatorio el RUC (11 dígitos)']);
            }
            if (empty($cliente_nombre)) {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'Para Factura es obligatoria la razón social']);
            }
            $cliente_tipo_doc = '6';
        } else {
            // Boleta/Guia: por defecto usar DNI del paciente
            $cliente_tipo_doc = $cliente_tipo_doc ?: '1';
            if (empty($cliente_num_doc)) {
                $pc = $db->query("SELECT C.CLI_RUC_ESPOSA AS DNI FROM CM_CITAS CC INNER JOIN CM_PACIENTES P ON P.id = CC.paciente_id INNER JOIN CLIENTES C ON C.CLI_CODCLIE = P.cliente_id WHERE CC.id = ?", [$cita_id])->getRow();
                $cliente_num_doc = $pc->DNI ?? '';
            }
            if (empty($cliente_nombre)) {
                $cliente_nombre = $this->getClienteNombre($db, $cita_id);
            }
        }
        
        // Obtener servicios pendientes de facturar de la cita
        $sqlServicios = "SELECT CS.*, A.ART_NOMBRE AS descripcion
                         FROM CM_CITAS_SERVICIOS CS
                         LEFT JOIN ARTI A ON A.ART_KEY = CS.art_key
                         WHERE CS.cita_id = ? AND ISNULL(CS.facturado, 0) = 0";
        $params = [$cita_id];
        
        if (!empty($servicios_ids)) {
            $ids = array_map('intval', (array)$servicios_ids);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sqlServicios .= " AND CS.id IN ($placeholders)";
            $params = array_merge($params, $ids);
        }
        
        $servicios = $db->query($sqlServicios, $params)->getResult();
        
        // Si no hay servicios reales y se selecciono la consulta base sintetica (id 0) o no se selecciono nada,
        // usar la consulta base del horario (solo si la cita no tiene comprobante emitido)
        $ids_seleccionados = array_map('intval', (array)$servicios_ids);
        if (empty($servicios) && (empty($servicios_ids) || in_array(0, $ids_seleccionados))) {
            $tiene_comp = $db->query("SELECT COUNT(*) AS n FROM CM_COMPROBANTES WHERE cita_id = ?", [$cita_id])->getRow()->n;
            if ($tiene_comp == 0) {
                $base = $db->query("
                    SELECT H.cod_art_servicio AS art_key, ISNULL(A.ART_NOMBRE, 'CONSULTA MEDICA') AS descripcion,
                           ISNULL(NULLIF(CC.total, 0), ISNULL(P.PRE_PRE1, 0)) AS precio
                    FROM CM_CITAS CC
                    INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = CC.horario_id
                    LEFT JOIN ARTI A ON A.ART_KEY = H.cod_art_servicio
                    LEFT JOIN PRECIOS P ON P.PRE_CODART = H.cod_art_servicio AND P.PRE_FLAG_UNIDAD = 'A' AND P.PRE_CODCIA = 25
                    WHERE CC.id = ?
                ", [$cita_id])->getRow();
                if ($base && $base->precio > 0) {
                    $servicios = [(object)[
                        'id' => 0,
                        'art_key' => $base->art_key,
                        'precio' => $base->precio,
                        'cantidad' => 1,
                        'descripcion' => $base->descripcion,
                    ]];
                }
            }
        }
        
        if (empty($servicios)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No hay servicios pendientes de facturar para esta cita']);
        }
        
        // Obtener config de serie para el local + tipo
        $tipo_doc_cfg = ($tipo_doc == 'F') ? '01' : (($tipo_doc == 'G') ? '09' : '03');
        $serie_cfg = $db->query("
            SELECT id, prefijo FROM CM_SERIE_DOCUMENTOS
            WHERE local_id = ? AND tipo_documento = ? AND tipo_servicio = 'CONSULTORIO' AND estado = 1
        ", [intval($local_id), $tipo_doc_cfg])->getRow();
        
        if (!$serie_cfg) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No hay serie configurada para local ' . $local_id . ' y tipo ' . $tipo_doc . ' en CM_SERIE_DOCUMENTOS']);
        }
        if (empty(trim($serie_cfg->prefijo ?? ''))) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'La serie del local ' . $local_id . ' tipo ' . $tipo_doc . ' no tiene prefijo configurado (ej. B001 / F001) en CM_SERIE_DOCUMENTOS']);
        }
        $serie = trim($serie_cfg->prefijo);
        
        // Calcular monto total de los servicios seleccionados
        $monto = 0;
        foreach ($servicios as $s) {
            $monto += ($s->precio * $s->cantidad);
        }
        
        // Obtener pago de la cita
        $pago = $db->table('CM_PAGOS')->where('cita_id', $cita_id)->orderBy('id', 'DESC')->get()->getRow();
        
        $db->transStart();
        
        // Reservar el correlativo: el UPDATE bloquea la fila hasta el COMMIT,
        // evitando correlativos duplicados entre cajas concurrentes
        $upd = $db->query("UPDATE CM_SERIE_DOCUMENTOS SET correlativo_actual = correlativo_actual + 1 WHERE id = ?", [$serie_cfg->id]);
        if ($upd === false) {
            $db->transComplete();
            $err = $db->error();
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No se pudo reservar el correlativo. Detalle: ' . ($err['message'] ?? 'Error SQL')]);
        }
        $corrRow = $db->query("SELECT correlativo_actual FROM CM_SERIE_DOCUMENTOS WHERE id = ?", [$serie_cfg->id])->getRow();
        if (!$corrRow) {
            $db->transComplete();
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No se pudo leer el correlativo del comprobante']);
        }
        $correlativo = intval($corrRow->correlativo_actual);
        
        $db->table('CM_COMPROBANTES')->insert([
            'cita_id'          => $cita_id,
            'pago_id'          => $pago ? $pago->id : null,
            'tipo_documento'   => $tipo_doc,
            'serie'            => $serie,
            'correlativo'      => $correlativo,
            'monto'            => $monto,
            'local_id'         => $local_id,
            'usuario_asistente'=> session()->get('nombre') ?? session()->get('usuario') ?? '',
            'cliente_nombre'   => substr($cliente_nombre, 0, 120),
            'cliente_tipo_doc' => $cliente_tipo_doc,
            'cliente_num_doc'  => $cliente_num_doc,
            'estado_sunat'     => 0,
        ]);
        
        $comprobante_id = $this->getLastInsertId($db);
        
        // Insertar detalles del comprobante
        foreach ($servicios as $s) {
            $db->table('CM_COMPROBANTE_DETALLE')->insert([
                'comprobante_id'   => $comprobante_id,
                'cita_servicio_id' => ($s->id > 0) ? $s->id : null,
                'art_key'          => $s->art_key,
                'descripcion'      => substr($s->descripcion ?: ('Servicio #' . $s->art_key), 0, 200),
                'cantidad'         => $s->cantidad,
                'precio'           => $s->precio,
                'subtotal'         => $s->precio * $s->cantidad,
            ]);
        }
        
        // Marcar servicios como facturados (solo los reales de CM_CITAS_SERVICIOS)
        $servIds = array_values(array_filter(array_map(function($s){ return $s->id; }, $servicios), function($id){ return $id > 0; }));
        if (!empty($servIds)) {
            $db->query("UPDATE CM_CITAS_SERVICIOS SET facturado = 1 WHERE id IN (" . implode(',', array_fill(0, count($servIds), '?')) . ")", $servIds);
        }
        
        // Marcar pago como "comprobante emitido"
        if ($pago) {
            $db->table('CM_PAGOS')->where('id', $pago->id)->update(['estado' => 2]);
        }
        
        // Marcar cita
        $ref_comp = $tipo_doc . '-' . $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);
        $obs_actual = $cita->observaciones ? trim($cita->observaciones) : '';
        $nueva_obs = $obs_actual ? ($obs_actual . ' | Comprobante: ' . $ref_comp) : ('Comprobante: ' . $ref_comp);
        $db->query("UPDATE CM_CITAS SET observaciones = ?, updated_at = GETDATE() WHERE id = ?", [substr($nueva_obs, 0, 500), $cita_id]);
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al emitir comprobante']);
        }
        
        // Imprimir el comprobante en la ticketera (best effort, no interrumpe el guardado)
        $this->imprimirComprobanteTermico($comprobante_id);
        
        return $this->response->setJSON([
            'status' => 'success',
            'msg' => 'Comprobante emitido: ' . $ref_comp . ' | S/ ' . number_format($monto, 2),
            'comprobante' => [
                'id' => $comprobante_id,
                'tipo' => $tipo_doc,
                'serie' => $serie,
                'correlativo' => $correlativo,
                'monto' => $monto
            ]
        ]);
    }

    private function imprimirComprobanteTermico($comprobante_id)
    {
        try {
            $db = \Config\Database::connect();
            $comp = $db->query("SELECT * FROM CM_COMPROBANTES WHERE id = ?", [$comprobante_id])->getRow();
            if (!$comp) return false;
            $detalles = $db->query("SELECT * FROM CM_COMPROBANTE_DETALLE WHERE comprobante_id = ? ORDER BY id", [$comprobante_id])->getResult();

            $local = intval($comp->local_id);
            if ($local == 0) $local = 1;
            $imp = config('Impresoras');
            $ruta = $imp->ticketeras[$local] ?? null;
            if (!$ruta) return false;

            $connector = new WindowsPrintConnector($ruta);
            $printer = new Printer($connector);

            $tipoNombre = $comp->tipo_documento == 'F' ? 'FACTURA' : ($comp->tipo_documento == 'G' ? 'GUIA' : 'BOLETA');

            $this->printCabecera($printer, $local);

            $printer->setFont(Printer::FONT_B);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($tipoNombre . "\n");
            $printer->text(trim($comp->serie) . "-" . str_pad($comp->correlativo, 8, '0', STR_PAD_LEFT) . "\n");
            $printer->text("Fecha Emisión: " . date('d/m/Y H:i', strtotime($comp->fecha_emision)) . "\n");
            $printer->text("Responsable : " . trim($comp->usuario_asistente ?: 'CONSULTORIO') . "\n");
            $printer->text("----------------------------------------------------------------\n");
            $printer->text("Cliente: " . trim($comp->cliente_nombre ?: '') . "\n");
            if ($comp->tipo_documento == 'F') {
                $printer->text("RUC : " . trim($comp->cliente_num_doc ?: '') . "\n");
            } elseif ($comp->tipo_documento == 'B' && !empty($comp->cliente_num_doc)) {
                $printer->text("DNI : " . trim($comp->cliente_num_doc) . "\n");
            }
            $printer->text("----------------------------------------------------------------\n");
            $printer->text("DESCRIPCION                       CANT.     P.U.     IMPORTE\n");
            foreach ($detalles as $d) {
                $printer->text(substr(trim($d->descripcion), 0, 32) . "\n");
                $printer->text(str_pad($d->cantidad, 26, ' ', STR_PAD_LEFT) . str_pad(number_format($d->precio, 2), 9, ' ', STR_PAD_LEFT) . str_pad(number_format($d->subtotal, 2), 11, ' ', STR_PAD_LEFT) . "\n");
            }
            $printer->text("----------------------------------------------------------------\n");
            $printer->text(str_pad("TOTAL :", 46, ' ', STR_PAD_LEFT) . " S/. ");
            $printer->setTextSize(2, 2);
            $printer->text(number_format($comp->monto, 2) . "\n");
            $printer->setTextSize(1, 1);
            $formatter = new \Luecano\NumeroALetras\NumeroALetras();
            $printer->text("SON: " . $formatter->toMoney($comp->monto, 2, 'SOLES', 'CENTIMOS') . "\n");
            $printer->text("----------------------------------------------------------------\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("GRACIAS POR SU VISITA !\n");
            $printer->feed(3);
            $printer->cut();
            $printer->pulse();
            $printer->close();
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error imprimiendo comprobante termico: ' . $e->getMessage());
            return false;
        }
    }

    public function imprimir_comprobante($comprobante_id = null)
    {
        $comprobante_id = $comprobante_id ?? $this->request->getPost('comprobante_id');
        if (!$comprobante_id) return $this->response->setJSON(['status' => 'error', 'msg' => 'Falta comprobante']);
        $ok = $this->imprimirComprobanteTermico($comprobante_id);
        if ($ok) {
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Comprobante enviado a la ticketera']);
        }
        return $this->response->setJSON(['status' => 'error', 'msg' => 'No se pudo imprimir el comprobante']);
    }

    public function get_pagos_cita()
    {
        $cita_id = $this->request->getPost('cita_id');
        if (!$cita_id) return $this->response->setJSON([]);
        $db = \Config\Database::connect();
        $pagos = $db->query("
            SELECT P.*, CASE P.estado WHEN 1 THEN 'Pagado' WHEN 2 THEN 'Comprobante emitido' WHEN 3 THEN 'Anulado' END AS estado_nombre,
                   ISNULL(A.ART_NOMBRE, 'CITA MEDICA') AS concepto
            FROM CM_PAGOS P
            LEFT JOIN CM_CITAS CC ON CC.id = P.cita_id
            LEFT JOIN CM_MEDICOS_HORARIOS H ON H.id = CC.horario_id
            LEFT JOIN ARTI A ON A.ART_KEY = H.cod_art_servicio
            WHERE P.cita_id = ?
            ORDER BY P.fecha_pago DESC
        ", [$cita_id])->getResult();

        // Adjuntar los comprobantes emitidos para cada pago
        foreach ($pagos as $p) {
            $p->comprobantes = $db->query("
                SELECT id, tipo_documento, serie, correlativo, monto
                FROM CM_COMPROBANTES
                WHERE cita_id = ? AND pago_id = ?
                ORDER BY id
            ", [$cita_id, $p->id])->getResult();
        }

        return $this->response->setJSON($pagos);
    }

    public function get_servicios_pendientes_comprobante()
    {
        $cita_id = $this->request->getPost('cita_id');
        if (!$cita_id) return $this->response->setJSON([]);
        $db = \Config\Database::connect();
        $servicios = $db->query("
            SELECT CS.id, CS.art_key, CS.precio, CS.cantidad, (CS.precio * CS.cantidad) AS subtotal,
                   ISNULL(A.ART_NOMBRE, 'Servicio #' + CAST(CS.art_key AS VARCHAR)) AS descripcion,
                   ISNULL(CS.facturado, 0) AS facturado
            FROM CM_CITAS_SERVICIOS CS
            LEFT JOIN ARTI A ON A.ART_KEY = CS.art_key
            WHERE CS.cita_id = ? AND ISNULL(CS.facturado, 0) = 0
            ORDER BY CS.id
        ", [$cita_id])->getResult();
        
        // Si no hay servicios registrados, ofrecer la consulta base SOLO si la cita no tiene comprobante emitido
        if (empty($servicios)) {
            $tiene_comp = $db->query("SELECT COUNT(*) AS n FROM CM_COMPROBANTES WHERE cita_id = ?", [$cita_id])->getRow()->n;
            if ($tiene_comp == 0) {
                $base = $db->query("
                    SELECT H.cod_art_servicio AS art_key, ISNULL(A.ART_NOMBRE, 'CONSULTA MEDICA') AS descripcion,
                           ISNULL(NULLIF(CC.total, 0), ISNULL(P.PRE_PRE1, 0)) AS precio
                    FROM CM_CITAS CC
                    INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = CC.horario_id
                    LEFT JOIN ARTI A ON A.ART_KEY = H.cod_art_servicio
                    LEFT JOIN PRECIOS P ON P.PRE_CODART = H.cod_art_servicio AND P.PRE_FLAG_UNIDAD = 'A' AND P.PRE_CODCIA = 25
                    WHERE CC.id = ?
                ", [$cita_id])->getRow();
                if ($base && $base->precio > 0) {
                    $servicios = [(object)[
                        'id' => 0,
                        'art_key' => $base->art_key,
                        'precio' => $base->precio,
                        'cantidad' => 1,
                        'subtotal' => $base->precio,
                        'descripcion' => $base->descripcion,
                        'facturado' => 0
                    ]];
                }
            }
        }
        return $this->response->setJSON($servicios);
    }

    private function getClienteNombre($db, $cita_id)
    {
        $row = $db->query("SELECT C.CLI_NOMBRE FROM CM_CITAS CC INNER JOIN CM_PACIENTES P ON P.id = CC.paciente_id INNER JOIN CLIENTES C ON C.CLI_CODCLIE = P.cliente_id WHERE CC.id = ?", [$cita_id])->getRow();
        return $row ? $row->CLI_NOMBRE : '';
    }

    /**
     * Series configuradas del local, indexadas por tipo de documento SUNAT.
     * Devuelve el prefijo (serie real del comprobante, ej. B001), no el correlativo.
     *
     * @return array<string, string>
     */
    private function getSeriesLocal($db, $local_id)
    {
        $rows = $db->query("
            SELECT tipo_documento, prefijo FROM CM_SERIE_DOCUMENTOS
            WHERE local_id = ? AND tipo_servicio = 'CONSULTORIO' AND estado = 1
        ", [intval($local_id)])->getResult();
        
        $series = ['03' => '', '01' => '', '09' => ''];
        foreach ($rows as $r) {
            if (array_key_exists($r->tipo_documento, $series)) {
                $series[$r->tipo_documento] = trim($r->prefijo ?? '');
            }
        }
        return $series;
    }

    private function joinClientesDedup($aliasP = 'P')
    {
        // Deduplica CLIENTES por CLI_CODCLIE (prioriza CLI_CP='C') para evitar filas duplicadas
        return "INNER JOIN (
                    SELECT CLI_CODCLIE, CLI_NOMBRE, CLI_RUC_ESPOSA, CLI_RUC_ESPOSO, CLI_TELEF1, CLI_FECHA_NAC,
                           ROW_NUMBER() OVER (PARTITION BY CLI_CODCLIE ORDER BY CASE WHEN CLI_CP = 'C' THEN 0 ELSE 1 END, CLI_CODCLIE) AS rn
                    FROM CLIENTES
                ) C ON C.CLI_CODCLIE = {$aliasP}.cliente_id AND C.rn = 1";
    }

    /**
     * Obtiene el último IDENTITY insertado en la sesión.
     * El driver SQLSRV de CI4 usa SCOPE_IDENTITY() en un batch separado (devuelve 0),
     * por eso se usa @@IDENTITY, que es de ámbito de sesión.
     */
    private function getLastInsertId($db)
    {
        $row = $db->query("SELECT @@IDENTITY AS id")->getRow();
        return $row ? intval($row->id) : 0;
    }
}
