<?php

namespace App\Controllers;

use App\Models\CalendarioModel;

class Calendario extends BaseController
{
    public function index()
    {
        $session = session();
        $data['menu']['p'] = 80; // Administración section
        $data['menu']['i'] = 81; // Calendario item
        return view('calendario/index', $data);
    }

    public function generar()
    {
        $session = session();
        $anio = $this->request->getPost('anio');
        $server = $this->request->getPost('server');
        
        $model = new CalendarioModel();
        $msg = $model->generar_calendario($anio, $server);
        
        $session->setFlashdata('mensaje', $msg);
        return redirect()->to(site_url('calendario'));
    }

    public function cerrar()
    {
        $session = session();
        $server = $this->request->getPost('server');
        
        if (!$server) {
            $session->setFlashdata('error', 'Debe seleccionar un servidor.');
            return redirect()->to(site_url('calendario'));
        }

        $model = new CalendarioModel();
        $msg = $model->cerrar_dia($server);
        
        // Check if the message starts with '1_' (success) or '0_' (error)
        if (strpos($msg, '1_') === 0) {
            // Success message - remove the '1_' prefix
            $session->setFlashdata('mensaje', substr($msg, 2));
        } else if (strpos($msg, '0_') === 0) {
            // Error message - remove the '0_' prefix
            $session->setFlashdata('error', substr($msg, 2));
        } else {
            // Generic message (database error, etc.)
            $session->setFlashdata('error', $msg);
        }
        
        return redirect()->to(site_url('calendario'));
    }
}
