<?php

namespace App\Controllers;

use App\Models\CmPacientesModel;
use App\Models\CmMedicosModel;
use App\Models\CmMedicosHorariosModel;

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
                   CASE cc.estado WHEN 0 THEN 'Inscrito' WHEN 1 THEN 'Confirmado' WHEN 2 THEN 'Atendido' WHEN 3 THEN 'Anulado' END AS estado_nombre,
                   STUFF((SELECT ', ' + A.ART_NOMBRE FROM CM_CITAS_SERVICIOS CS INNER JOIN ARTI A ON A.ART_KEY = CS.art_key WHERE CS.cita_id = cc.id FOR XML PATH('')), 1, 2, '') AS servicios_extra
            FROM CM_CITAS cc
            INNER JOIN CM_PACIENTES P ON P.id = cc.paciente_id
            INNER JOIN CLIENTES C ON C.CLI_CODCLIE = P.cliente_id
            INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = cc.horario_id
            LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id
            WHERE cc.id = ?
        ", [$cita_id])->getRow();
        
        if (!$cita) return $this->response->setJSON(['status' => 'error', 'msg' => 'Cita no encontrada']);
        
        // Citas previas del paciente
        $historial = $db->query("
            SELECT cc.id, H.fecha_especifica, (M.nombres + ' ' + M.apellidos) AS medico,
                   cc.total, CASE cc.estado WHEN 0 THEN 'Inscrito' WHEN 1 THEN 'Confirmado' WHEN 2 THEN 'Atendido' WHEN 3 THEN 'Anulado' END AS estado_nombre
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
        
        // Si es nulo (no enviado en la peticion), poner por defecto hoy
        if ($fecha_desde === null) $fecha_desde = date('Y-m-d');
        if ($fecha_hasta === null) $fecha_hasta = date('Y-m-d');
        
        $params = [];
        $sql = "SELECT cc.id, cc.paciente_id, cc.estado, cc.total, cc.saldo,
                       H.fecha_especifica, (M.nombres + ' ' + M.apellidos) AS medico,
                       C.CLI_NOMBRE, C.CLI_RUC_ESPOSA AS DNI, C.CLI_TELEF1, C.CLI_FECHA_NAC,
                       FLOOR(DATEDIFF(DAY, C.CLI_FECHA_NAC, GETDATE()) / 365.25) AS edad,
                       CASE cc.estado WHEN 0 THEN 'Inscrito' WHEN 1 THEN 'Confirmado' WHEN 2 THEN 'Atendido' WHEN 3 THEN 'Anulado' END AS estado_nombre
                FROM CM_CITAS cc
                INNER JOIN CM_PACIENTES P ON P.id = cc.paciente_id
                INNER JOIN CLIENTES C ON C.CLI_CODCLIE = P.cliente_id
                INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = cc.horario_id
                LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id
                WHERE 1=1";
        
        if ($horario_id) { $sql .= " AND cc.horario_id = ?"; $params[] = $horario_id; }
        if ($estado !== null && $estado !== '') { $sql .= " AND cc.estado = ?"; $params[] = intval($estado); }
        if ($fecha_desde) { $sql .= " AND H.fecha_especifica >= ?"; $params[] = $fecha_desde; }
        if ($fecha_hasta) { $sql .= " AND H.fecha_especifica <= ?"; $params[] = $fecha_hasta; }
        
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
        
        if ($fecha_desde === null) $fecha_desde = date('Y-m-d');
        if ($fecha_hasta === null) $fecha_hasta = date('Y-m-d');
        
        $params = [];
        $sql = "SELECT cc.*, H.fecha_especifica, H.hora_inicio,
                       (M.nombres + ' ' + M.apellidos) AS medico, M.especialidad,
                       C.CLI_NOMBRE, C.CLI_RUC_ESPOSA AS DNI,
                       ISNULL(CS.servicios_count, 0) AS servicios_count,
                       CASE cc.estado WHEN 0 THEN 'Inscrito' WHEN 1 THEN 'Confirmado' WHEN 2 THEN 'Atendido' WHEN 3 THEN 'Anulado' END AS estado_nombre
                FROM CM_CITAS cc
                INNER JOIN CM_PACIENTES P ON P.id = cc.paciente_id
                INNER JOIN CLIENTES C ON C.CLI_CODCLIE = P.cliente_id
                INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = cc.horario_id
                LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id
                LEFT JOIN (SELECT cita_id, COUNT(*) AS servicios_count FROM CM_CITAS_SERVICIOS GROUP BY cita_id) CS ON CS.cita_id = cc.id
                WHERE 1=1";
        
        if ($horario_id) { $sql .= " AND cc.horario_id = ?"; $params[] = $horario_id; }
        if ($estado !== null && $estado !== '') { $sql .= " AND cc.estado = ?"; $params[] = intval($estado); }
        if ($fecha_desde) { $sql .= " AND H.fecha_especifica >= ?"; $params[] = $fecha_desde; }
        if ($fecha_hasta) { $sql .= " AND H.fecha_especifica <= ?"; $params[] = $fecha_hasta; }
        
        $sql .= " ORDER BY H.fecha_especifica DESC, cc.created_at DESC";
        
        $citas = $db->query($sql, $params)->getResult();
        
        // Horarios para filtro
        $horarios = $db->query("SELECT H.id, H.fecha_especifica, (M.nombres + ' ' + M.apellidos) AS medico FROM CM_MEDICOS_HORARIOS H LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id WHERE H.estado = 1 ORDER BY H.fecha_especifica DESC")->getResult();
        
        // Series
        $local_pago = session()->get('caja') ? str_pad(session()->get('caja'), 2, '0', STR_PAD_LEFT) : '01';
        $series = [
            '03' => $this->getSerieComprobante($db, $local_pago, 'B') ? $this->getSerieComprobante($db, $local_pago, 'B')->serie_actual : ($local_pago == '02' ? 20 : ($local_pago == '03' ? 22 : 21)),
            '01' => $this->getSerieComprobante($db, $local_pago, 'F') ? $this->getSerieComprobante($db, $local_pago, 'F')->serie_actual : ($local_pago == '02' ? 20 : ($local_pago == '03' ? 22 : 21)),
            '09' => $this->getSerieComprobante($db, $local_pago, 'G') ? $this->getSerieComprobante($db, $local_pago, 'G')->serie_actual : ($local_pago == '02' ? 20 : ($local_pago == '03' ? 22 : 21)),
        ];

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
                    WHERE estado IN (0, 1, 2)
                    GROUP BY horario_id
                ) cc ON cc.horario_id = h.id
                WHERE h.estado = 1
                  AND h.fecha_especifica >= CAST(GETDATE() AS DATE)
                ORDER BY h.fecha_especifica ASC, h.hora_inicio ASC";
        
        $campanias = $db->query($sql)->getResult();

        $local_pago = session()->get('caja') ? str_pad(session()->get('caja'), 2, '0', STR_PAD_LEFT) : '01';
        $series = [
            '03' => $this->getSerieComprobante($db, $local_pago, 'B') ? $this->getSerieComprobante($db, $local_pago, 'B')->serie_actual : ($local_pago == '02' ? 20 : ($local_pago == '03' ? 22 : 21)),
            '01' => $this->getSerieComprobante($db, $local_pago, 'F') ? $this->getSerieComprobante($db, $local_pago, 'F')->serie_actual : ($local_pago == '02' ? 20 : ($local_pago == '03' ? 22 : 21)),
            '09' => $this->getSerieComprobante($db, $local_pago, 'G') ? $this->getSerieComprobante($db, $local_pago, 'G')->serie_actual : ($local_pago == '02' ? 20 : ($local_pago == '03' ? 22 : 21)),
        ];

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
                           WHEN 2 THEN 'Atendido'
                           WHEN 3 THEN 'Anulado'
                           ELSE 'Desconocido'
                       END AS estado_nombre
                FROM CM_CITAS cc
                INNER JOIN CM_PACIENTES p ON p.id = cc.paciente_id
                INNER JOIN CLIENTES c ON c.CLI_CODCLIE = p.cliente_id
                WHERE cc.horario_id = ?
                  AND cc.estado IN (0, 1, 2)
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
        
        // Servicios extra
        $servicios_extra = $this->request->getPost('servicios_extra') ?: [];
        $total_extra = 0;
        if (!empty($servicios_extra)) {
            foreach ($servicios_extra as $art_key) {
                $p = $db->query("SELECT ISNULL(PRE_PRE1,0) AS precio FROM PRECIOS WHERE PRE_CODART=? AND PRE_FLAG_UNIDAD='A' AND PRE_CODCIA='25'", [intval($art_key)])->getRow();
                $total_extra += $p ? floatval($p->precio) : 0;
            }
        }
        $monto_total += $total_extra;
        
        $tipo_comprobante = $this->request->getPost('tipo_comprobante') ?: '03';
        $tipo_comp_letra = $this->mapTipoComprobante($tipo_comprobante);
        $forma_pago = $this->request->getPost('forma_pago') ?? 'EFECTIVO';
        $local_pago = session()->get('caja') ? str_pad(session()->get('caja'), 2, '0', STR_PAD_LEFT) : '01';
        
        // Ejecutar SP con @CitaId para que actualice CM_CITAS atomicamente (local/fallback)
        $sql = "DECLARE @NumFac INT, @NumSer INT, @Resultado VARCHAR(500);
                EXEC SP_CM_GenerarComprobante 
                     @HorarioId = ?, @PacienteId = ?, @ClienteId = ?, @LocalPago = ?, 
                     @TipoComprobante = ?, @FormaPago = ?, @MontoTotal = ?,
                     @CodArtServicio = ?, @CitaId = ?,
                     @NumFac = @NumFac OUTPUT, @NumSer = @NumSer OUTPUT, @Resultado = @Resultado OUTPUT;
                SELECT @NumFac as NumFac, @NumSer as NumSer, @Resultado as Resultado;";
        
        $query = $db->query($sql, [
            $cita->horario_id, $cita->paciente_id, $cita->cliente_id,
            $local_pago, $tipo_comp_letra, $forma_pago, $monto_total, $cod_art, $cita_id
        ]);
        $result = $query->getRow();
        
        if (!$result || strpos($result->Resultado, 'ERROR') === 0) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error en cobro: ' . ($result ? $result->Resultado : 'Error SQL')]);
        }
        
        $ubicacion = '';
        $sp_actualizo_cita = false;
        if (strpos($result->Resultado, 'REMOTO') !== false) {
            $ubicacion = ' (local ' . $local_pago . ')';
        } elseif (strpos($result->Resultado, 'FALLBACK') !== false) {
            $ubicacion = ' (local ' . $local_pago . ' no disponible, creado en principal)';
            $sp_actualizo_cita = true;
        } elseif (strpos($result->Resultado, 'LOCAL') !== false) {
            $sp_actualizo_cita = true;
        }
        
        // Si el SP NO actualizo CM_CITAS (caso remoto), lo hace PHP
        if (!$sp_actualizo_cita) {
            $ref = $tipo_comp_letra . '-' . $result->NumSer . '-' . $result->NumFac;
            $db->query("UPDATE CM_CITAS SET estado = 1, total = ?, saldo = 0, observaciones = 'Pagado: " . $ref . $ubicacion . "', updated_at = GETDATE() WHERE id = ?", [$monto_total, $cita_id]);
        }
        
        // Procesar servicios extra
        if (!empty($servicios_extra)) {
            $allog = $db->query("SELECT ALL_NUMOPER FROM ALLOG WHERE ALL_NUMSER = ? AND ALL_NUMFAC = ? AND ALL_TIPMOV = 10", [$result->NumSer, $result->NumFac])->getRow();
            $numoper = $allog ? $allog->ALL_NUMOPER : 0;
            $this->procesarServiciosExtra($db, $cita_id, $result->NumSer, $result->NumFac, $numoper, $cita->cliente_id, $tipo_comp_letra, $servicios_extra);
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'msg' => $tipo_comp_letra . '-' . $result->NumSer . '-' . $result->NumFac . ' | S/ ' . number_format($monto_total, 2) . $ubicacion,
            'comprobante' => ['serie' => $result->NumSer, 'correlativo' => $result->NumFac, 'tipo' => $tipo_comp_letra]
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
        if (!$cita || !in_array($cita->estado, [1, 2])) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Cita no válida para agregar procedimiento']);
        }
        
        $paciente = $db->table('CM_PACIENTES')->where('id', $cita->paciente_id)->get()->getRow();
        $local_pago = session()->get('caja') ? str_pad(session()->get('caja'), 2, '0', STR_PAD_LEFT) : '01';
        $tipo_comp = $this->request->getPost('tipo_comprobante') ?: '03';
        $tipo_letra = $this->mapTipoComprobante($tipo_comp);
        
        // Generar comprobante
        $sql = "DECLARE @NumFac INT, @NumSer INT, @Resultado VARCHAR(500);
                EXEC SP_CM_GenerarComprobante 
                     @HorarioId = ?, @PacienteId = ?, @ClienteId = ?, @LocalPago = ?, 
                     @TipoComprobante = ?, @FormaPago = 'EFECTIVO', @MontoTotal = ?,
                     @CodArtServicio = ?,
                     @NumFac = @NumFac OUTPUT, @NumSer = @NumSer OUTPUT, @Resultado = @Resultado OUTPUT;
                SELECT @NumFac as NumFac, @NumSer as NumSer, @Resultado as Resultado;";
        
        $query = $db->query($sql, [
            $cita->horario_id, $cita->paciente_id, $paciente->cliente_id,
            $local_pago, $tipo_letra, $precio, $art_key
        ]);
        $result = $query->getRow();
        
        if (!$result || strpos($result->Resultado, 'ERROR') === 0) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error: ' . ($result->Resultado ?? 'SP')]);
        }
        
        // Registrar en CM_CITAS_SERVICIOS
        $db->table('CM_CITAS_SERVICIOS')->insert([
            'cita_id'       => $cita_id,
            'art_key'       => $art_key,
            'precio'        => $precio,
            'cantidad'      => 1,
            'observaciones' => $observacion,
        ]);
        
        // Actualizar total de la cita
        $db->query("UPDATE CM_CITAS SET total = total + ?, updated_at = GETDATE() WHERE id = ?", [$precio, $cita_id]);
        
        return $this->response->setJSON([
            'status' => 'success',
            'msg' => $tipo_letra . '-' . $result->NumSer . '-' . $result->NumFac . ' | S/ ' . number_format($precio, 2),
            'comprobante' => ['serie' => $result->NumSer, 'correlativo' => $result->NumFac, 'tipo' => $tipo_letra, 'monto' => $precio]
        ]);
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
        
        if ($nuevo_estado == '2' && $cita->estado != 1) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Solo se puede atender una cita pagada']);
        }
        
        $db->query("UPDATE CM_CITAS SET estado = ?, updated_at = GETDATE() WHERE id = ?", [(int)$nuevo_estado, $cita_id]);
        
        // Actualizar cupos
        $db->query("UPDATE CM_MEDICOS_HORARIOS SET cupos_ocupados = (
                        SELECT COUNT(*) FROM CM_CITAS WHERE horario_id = ? AND estado IN (0,1,2)
                    ) WHERE id = ?", [$cita->horario_id, $cita->horario_id]);
        
        $nombres = ['2' => 'Atendido', '3' => 'Anulado'];
        return $this->response->setJSON(['status' => 'success', 'msg' => 'Cita marcada como ' . $nombres[$nuevo_estado]]);
    }

    private function procesarServiciosExtra($db, $cita_id, $serie, $numfac, $numoper, $cliente_id, $tipo_comp, $servicios_ids)
    {
        if (empty($servicios_ids)) return;
        
        $ids = is_array($servicios_ids) ? $servicios_ids : explode(',', $servicios_ids);
        $total_extra = 0;
        $numsec = 2;
        $fecha = date('Y-m-d H:i:s');
        $hora = date('H:i:s');
        
        foreach ($ids as $art_key) {
            $art_key = intval($art_key);
            if (!$art_key) continue;
            
            $precio = $db->query("SELECT ISNULL(PRE_PRE1, 0) AS precio FROM PRECIOS WHERE PRE_CODART = ? AND PRE_FLAG_UNIDAD = 'A' AND PRE_CODCIA = '25'", [$art_key])->getRow();
            $monto = $precio ? floatval($precio->precio) : 0;
            if ($monto <= 0) continue;
            
            // Insertar en CM_CITAS_SERVICIOS
            $db->table('CM_CITAS_SERVICIOS')->insert([
                'cita_id' => $cita_id, 'art_key' => $art_key, 'precio' => $monto, 'cantidad' => 1,
            ]);
            
            // Insertar FACART mínimo (columnas esenciales, el resto DEFAULT)
            $db->query("INSERT INTO FACART (FAR_TIPMOV, FAR_CODCIA, FAR_NUMSER, FAR_FBG, FAR_NUMFAC, FAR_NUMSEC, FAR_FECHA, FAR_NUMOPER, FAR_CODCLIE, FAR_CODART, FAR_PRECIO, FAR_BRUTO, FAR_EQUIV, FAR_CANTIDAD, FAR_CONCEPTO, FAR_DESCRI, FAR_SIGNO_ARM, FAR_ESTADO, FAR_CODUSU, FAR_FECHA_PRO, FAR_FECHA_CAN, FAR_HORA)
                        VALUES (10, '25', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, 'SERV. ADIC. CONSULTORIO', 'UND', -1, 'N', 'OPER03', ?, ?, ?)",
                [str_pad($serie, 3, ' ', STR_PAD_LEFT), $tipo_comp, $numfac, $numsec, $fecha, $numoper, $cliente_id, $art_key, $monto, $monto, $fecha, $fecha, $hora]);
            
            $total_extra += $monto;
            $numsec++;
        }
        
        // Actualizar total de ALLOG
        if ($total_extra > 0) {
            $db->query("UPDATE ALLOG SET ALL_IMPORTE = ALL_IMPORTE + ?, ALL_BRUTO = ALL_BRUTO + ?, ALL_NETO = ALL_NETO + ? WHERE ALL_NUMSER = ? AND ALL_NUMFAC = ? AND ALL_NUMOPER = ? AND ALL_CODCIA = '25'",
                [$total_extra, $total_extra, $total_extra, str_pad($serie, 3, ' ', STR_PAD_LEFT), $numfac, $numoper]);
        }
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
            $paciente_id = $db->insertID();
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
        
        // Si paga ahora, ejecutar SP de cobro
        if ($pagar_ahora == '1') {
            $tipo_comprobante = $this->request->getPost('tipo_comprobante') ?: '03';
            $forma_pago = $this->request->getPost('forma_pago') ?? 'EFECTIVO';
            $local_pago = session()->get('caja') ? str_pad(session()->get('caja'), 2, '0', STR_PAD_LEFT) : '01';
            $tipo_comp_letra = $this->mapTipoComprobante($tipo_comprobante);
            $serie_info = $this->getSerieComprobante($db, $local_pago, $tipo_comp_letra);
            
            $sql = "DECLARE @NumFac INT, @NumSer INT, @Resultado VARCHAR(500);
                    EXEC SP_CM_GenerarComprobante 
                         @HorarioId = ?, @PacienteId = ?, @ClienteId = ?, @LocalPago = ?, 
                         @TipoComprobante = ?, @FormaPago = ?, @MontoTotal = ?,
                         @CodArtServicio = ?,
                         @NumFac = @NumFac OUTPUT, @NumSer = @NumSer OUTPUT, @Resultado = @Resultado OUTPUT;
                    SELECT @NumFac as NumFac, @NumSer as NumSer, @Resultado as Resultado;";
            
            $query = $db->query($sql, [$horario_id, $paciente_id, $cliente_id, $local_pago, $tipo_comp_letra, $forma_pago, $monto_total, $cod_art_servicio]);
            $result = $query->getRow();
            
            if (!$result || strpos($result->Resultado, 'ERROR') === 0) {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'Error en cobro: ' . ($result ? $result->Resultado : 'Error SQL')]);
            }
            
            $estado = 1; // confirmado con pago
            $ubicacion = '';
            if (strpos($result->Resultado, 'REMOTO') !== false) {
                $ubicacion = ' (comprobante creado en local ' . $local_pago . ')';
            } elseif (strpos($result->Resultado, 'FALLBACK') !== false) {
                $ubicacion = ' (local ' . $local_pago . ' no disponible, creado en servidor principal)';
            }
            $msg = $tipo_comp_letra . '-' . $result->NumSer . '-' . $result->NumFac . ' | S/ ' . number_format($monto_total, 2) . $ubicacion;
            $comprobante = ['serie' => $result->NumSer, 'correlativo' => $result->NumFac, 'tipo' => $tipo_comp_letra, 'monto' => $monto_total];
            $ref_comprobante = $tipo_comp_letra . '-' . $result->NumSer . '-' . $result->NumFac;
        } else {
            $estado = 0; // inscrito sin pago
            $msg = 'Reserva confirmada. Pago pendiente de S/ ' . number_format($monto_total, 2) . ' para el día de la consulta.';
            $comprobante = null;
            $ref_comprobante = null;
            $ubicacion = '';
        }
        
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
            'observaciones'  => $pagar_ahora == '1' ? 'Pagado: ' . $ref_comprobante . $ubicacion : 'Pago pendiente',
            'local_origen'   => $local_pago ?? '01',
        ]);

        $cita_id = $db->insertID();
        
        // Procesar servicios extra si aplica
        if ($pagar_ahora == '1' && !empty($servicios_extra)) {
            $allog = $db->query("SELECT ALL_NUMOPER FROM ALLOG WHERE ALL_NUMSER = ? AND ALL_NUMFAC = ? AND ALL_TIPMOV = 10", [$result->NumSer, $result->NumFac])->getRow();
            $numoper = $allog ? $allog->ALL_NUMOPER : 0;
            $this->procesarServiciosExtra($db, $cita_id, $result->NumSer, $result->NumFac, $numoper, $cliente_id, $tipo_comp_letra, $servicios_extra);
        }
        
        // Actualizar cupos
        $db->query("UPDATE CM_MEDICOS_HORARIOS SET cupos_ocupados = (
                        SELECT COUNT(*) FROM CM_CITAS WHERE horario_id = ? AND estado IN (0,1,2)
                    ) WHERE id = ?", [$horario_id, $horario_id]);
        
        return $this->response->setJSON([
            'status'       => 'success',
            'msg'          => $msg,
            'estado'       => $estado,
            'comprobante'  => $comprobante
        ]);
    }

    public function cobrar_cita()
    {
        $db = \Config\Database::connect();
        
        $horario_id = $this->request->getPost('horario_id');
        $paciente_id = $this->request->getPost('paciente_id');
        $tipo_comprobante = $this->request->getPost('tipo_comprobante'); // 03 o 01
        $forma_pago = $this->request->getPost('forma_pago') ?? 'EFECTIVO';
        
        // Asignamos el Local activo de la sesión del usuario (1, 2 o 3)
        $local_pago = session()->get('caja') ? str_pad(session()->get('caja'), 2, '0', STR_PAD_LEFT) : '01';
        
        // 1. Obtener los datos del Paciente (Para saber quién es el Titular/Cliente)
        $paciente = $db->table('CM_PACIENTES')->where('id', $paciente_id)->get()->getRow();
        if (!$paciente) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Paciente no encontrado']);
        }
        $cliente_id = $paciente->cliente_id;

        // 2. Obtener los datos del Horario (Campaña) para saber el costo
        // NOTA: Definimos un costo fijo para el ejemplo (S/ 50.00)
        // Lo ideal sería agregarlo a la tabla CM_MEDICOS_HORARIOS en el futuro
        $monto_total = 50.00; 

        $tipo_comp_letra = $this->mapTipoComprobante($tipo_comprobante);
        $serie_info = $this->getSerieComprobante($db, $local_pago, $tipo_comp_letra);

        // 3. Ejecutar el Stored Procedure SP_CM_GenerarComprobante
        $sql = "DECLARE @NumFac INT, @NumSer INT, @Resultado VARCHAR(500);
                EXEC SP_CM_GenerarComprobante 
                     @HorarioId = ?, @PacienteId = ?, @ClienteId = ?, @LocalPago = ?, 
                     @TipoComprobante = ?, @FormaPago = ?, @MontoTotal = ?, 
                     @NumFac = @NumFac OUTPUT, @NumSer = @NumSer OUTPUT, @Resultado = @Resultado OUTPUT;
                SELECT @NumFac as NumFac, @NumSer as NumSer, @Resultado as Resultado;";

        $query = $db->query($sql, [
            $horario_id,
            $paciente_id,
            $cliente_id,
            $local_pago,
            $tipo_comp_letra,
            $forma_pago,
            $monto_total
        ]);

        $result = $query->getRow();

        if ($result && strpos($result->Resultado, 'ERROR') !== 0) {
            // 4. Registrar la cita en CM_CITAS
            $ubicacion_cobro = '';
            if (strpos($result->Resultado, 'REMOTO') !== false) {
                $ubicacion_cobro = ' (local ' . $local_pago . ')';
            } elseif (strpos($result->Resultado, 'FALLBACK') !== false) {
                $ubicacion_cobro = ' (local ' . $local_pago . ' no disponible, creado en principal)';
            }
            $db->table('CM_CITAS')->insert([
                'paciente_id'      => $paciente_id,
                'horario_id'       => $horario_id,
                'cliente_id'       => $cliente_id,
                'estado'           => 1,
                'orden'            => 0,
                'orden_atencion'   => 0,
                'total'            => $monto_total,
                'saldo'            => 0,
                'fecha'            => date('Y-m-d'),
                'hora'             => date('H:i:s'),
                'observaciones'    => 'Pagado: ' . $tipo_comp_letra . '-' . $result->NumSer . '-' . $result->NumFac . $ubicacion_cobro,
                'local_origen'     => $local_pago,
                'created_at'       => date('Y-m-d H:i:s'),
            ]);

            // 5. Actualizar cupos ocupados en CM_MEDICOS_HORARIOS
            $db->query("UPDATE CM_MEDICOS_HORARIOS SET cupos_ocupados = (
                            SELECT COUNT(*) FROM CM_CITAS 
                            WHERE horario_id = ? AND estado IN (0,1,2)
                        ) WHERE id = ?", [$horario_id, $horario_id]);

            return $this->response->setJSON([
                'status' => 'success', 
                'msg' => 'Comprobante generado. ' . $tipo_comp_letra . '-' . $result->NumSer . '-' . $result->NumFac,
                'comprobante' => [
                    'serie' => $result->NumSer,
                    'correlativo' => $result->NumFac,
                    'tipo' => $tipo_comp_letra
                ],
                'referencia' => [
                    'local' => $local_pago,
                    'serie' => $result->NumSer,
                    'numero' => $result->NumFac,
                    'tipo' => $tipo_comp_letra
                ]
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error', 
                'msg' => 'Error al generar comprobante: ' . ($result ? $result->Resultado : 'Error SQL')
            ]);
        }
    }

    private function mapTipoComprobante($tipo)
    {
        if ($tipo == '01') return 'F';
        if ($tipo == '09') return 'G';
        return 'B';
    }

    private function getSerieComprobante($db, $local_id, $fbg)
    {
        if ($fbg == 'F')      $tipo_doc = '01';
        elseif ($fbg == 'G')  $tipo_doc = '09';
        else                  $tipo_doc = '03';
        $serie = $db->query("SELECT TOP 1 prefijo, serie_actual FROM CM_SERIE_DOCUMENTOS WHERE local_id = ? AND tipo_documento = ? AND tipo_servicio = 'CONSULTORIO' AND estado = 1", [intval($local_id), $tipo_doc])->getRow();
        return $serie ?: null;
    }
}
