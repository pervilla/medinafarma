<?php

namespace App\Controllers;

use App\Models\AnulacionModel;

class Anulaciones extends BaseController
{
    /**
     * Anula un documento de venta (replica formgen LK_CODTRA = 1111).
     * POST: numOper, fecha (Y-m-d), server (1/2/3), concepto
     */
    public function anularComprobante()
    {
        $session = session();
        if ($session->get('user_id') != 'ADMIN') {
            return $this->response->setJSON(['status' => 403, 'message' => 'No tiene permisos para anular documentos.']);
        }

        $numOper  = (int) $this->request->getVar('numOper');
        $fecha    = trim((string) $this->request->getVar('fecha'));
        $server   = (int) $this->request->getVar('server');
        $concepto = trim((string) $this->request->getVar('concepto'));

        if ($numOper <= 0 || $fecha === '') {
            return $this->response->setJSON(['status' => 400, 'message' => 'Datos incompletos para la anulación.']);
        }

        $usuario = $session->get('user_id') ?: 'ADMIN';

        // Comentario de auditoría: Anulado desde web PC:<nombre> IP:<ip> - <concepto original>
        $ip      = $this->request->getIPAddress();
        $esLocal = in_array($ip, ['127.0.0.1', '::1'], true);
        $host    = '';
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
            $h = @gethostbyaddr($ip);
            if ($h && $h !== $ip) {
                $host = $h;
            }
        }
        if ($esLocal) {
            $origen = $host !== '' ? 'PC:' . $host : 'local';
        } elseif ($host !== '') {
            $origen = 'PC:' . $host . ' IP:' . $ip;
        } else {
            $origen = 'IP:' . $ip;
        }
        $comentario = 'Anulado desde web ' . $origen . ($concepto !== '' ? ' - ' . $concepto : '');
        $comentario = substr($comentario, 0, 150);

        try {
            $AnulacionModel = new AnulacionModel();
            $result = $AnulacionModel->anularDocumento($numOper, $fecha, $server, $usuario, $comentario);

            if ($result['status']) {
                return $this->response->setJSON(['status' => 200, 'message' => $result['message']]);
            }

            return $this->response->setJSON(['status' => 400, 'message' => $result['message']]);
        } catch (\Throwable $e) {
            log_message('error', 'Anulaciones::anularComprobante ' . $e->getMessage());
            return $this->response->setJSON(['status' => 500, 'message' => 'Error al anular: ' . $e->getMessage()]);
        }
    }
}
