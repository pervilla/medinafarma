<?php

namespace App\Controllers;

use App\Models\CmMedicosModel;

class CmMedicos extends BaseController
{
    public function index()
    {
        $model = new CmMedicosModel();
        $medicos = $model->orderBy('apellidos', 'ASC')->findAll();
        return view('cm_medicos/index', ['medicos' => $medicos, 'titulo' => 'Directorio de Médicos', 'menu' => ['p' => 40, 'i' => 47]]);
    }

    public function guardar()
    {
        $model = new CmMedicosModel();
        $id = $this->request->getPost('id');
        
        $data = [
            'nombres'      => $this->request->getPost('nombres'),
            'apellidos'    => $this->request->getPost('apellidos'),
            'dni'          => $this->request->getPost('dni'),
            'cmp'          => $this->request->getPost('cmp'),
            'rne'          => $this->request->getPost('rne'),
            'especialidad' => $this->request->getPost('especialidad'),
            'telefono'     => $this->request->getPost('telefono'),
            'estado'       => $this->request->getPost('estado') ?? 1,
        ];

        if ($id) {
            $model->update($id, $data);
            $msg = 'Médico actualizado';
        } else {
            $model->insert($data);
            $msg = 'Médico registrado';
        }

        return redirect()->to('cmMedicos')->with('msg', $msg);
    }

    public function get_medico()
    {
        $id = $this->request->getPost('id');
        $model = new CmMedicosModel();
        return $this->response->setJSON($model->find($id));
    }
}
