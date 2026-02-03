<?php

namespace App\Controllers;

use App\Models\AnulacionModel;
use App\Models\ImportFactModel;

class Anulaciones extends BaseController
{
    public function anularComprobante()
    {
        $session = session();
        if ($session->get('user_id') != 'ADMIN') {
            return $this->response->setJSON(['status' => 403, 'message' => 'No tiene permisos para anular documentos.']);
        }

        $numOper = $this->request->getVar('numOper');
        $idImport = $this->request->getVar('idImport'); // Opcional, para el caso de Compras desde módulo Importar
        
        $AnulacionModel = new AnulacionModel();
        $result = $AnulacionModel->anularDocumento($numOper, 'ADMIN');

        if ($result['status']) {
            // Si viene del módulo Importar, actualizar su estado a 0
            if ($idImport) {
                $ImportFactModel = new ImportFactModel();
                $this->db->table('IMPORT_FACT')
                    ->where('ID', $idImport)
                    ->update(['ESTADO' => 0]);
            }
            return $this->response->setJSON(['status' => 200, 'message' => $result['message']]);
        } else {
            return $this->response->setJSON(['status' => 400, 'message' => $result['message']]);
        }
    }
}
