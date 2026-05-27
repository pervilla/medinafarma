<?php

namespace App\Controllers;

use App\Models\CmMedicosModel;
use App\Models\CmMedicosHorariosModel;

class CmHorarios extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $sql = "SELECT h.*, (m.nombres + ' ' + m.apellidos) AS medico,
                       A.ART_NOMBRE AS servicio, 
                       ISNULL(P.PRE_PRE1, 0) AS precio
                FROM CM_MEDICOS_HORARIOS h
                LEFT JOIN CM_MEDICOS m ON m.id = h.medico_id
                LEFT JOIN ARTI A ON A.ART_KEY = h.cod_art_servicio
                LEFT JOIN PRECIOS P ON P.PRE_CODART = h.cod_art_servicio AND P.PRE_FLAG_UNIDAD = 'A' AND P.PRE_CODCIA = 25
                ORDER BY h.fecha_especifica DESC, h.hora_inicio ASC";
        
        $horarios = $db->query($sql)->getResult();
        
        $medicosModel = new CmMedicosModel();
        $medicos = $medicosModel->where('estado', 1)->orderBy('apellidos', 'ASC')->findAll();

        return view('cm_horarios/index', [
            'horarios' => $horarios,
            'medicos' => $medicos,
            'servicios' => $this->getServicios(),
            'titulo' => 'Programar Horarios / Campañas',
            'menu' => ['p' => 40, 'i' => 48]
        ]);
    }

    private function getServicios()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT A.ART_KEY, A.ART_NOMBRE, ISNULL(P.PRE_PRE1, 0) AS precio
            FROM ARTI A
            LEFT JOIN PRECIOS P ON P.PRE_CODART = A.ART_KEY AND P.PRE_FLAG_UNIDAD = 'A' AND P.PRE_CODCIA = 25
            WHERE A.ART_FAMILIA = 594 AND A.ART_SITUACION = 0
            ORDER BY A.ART_NOMBRE
        ")->getResult();
    }

    public function get_horario()
    {
        $id = $this->request->getPost('id');
        $db = \Config\Database::connect();
        $h = $db->table('CM_MEDICOS_HORARIOS')->where('id', $id)->get()->getRow();
        return $this->response->setJSON($h);
    }

    public function guardar()
    {
        $model = new CmMedicosHorariosModel();
        $id = $this->request->getPost('id');
        
        $data = [
            'medico_id'                 => $this->request->getPost('medico_id'),
            'local_id'                  => $this->request->getPost('local_id') ?? 1,
            'dia_semana'                => $this->request->getPost('dia_semana') ?? 1,
            'fecha_especifica'          => $this->request->getPost('fecha_especifica') ?: null,
            'hora_inicio'               => $this->request->getPost('hora_inicio'),
            'hora_fin'                  => $this->request->getPost('hora_fin'),
            'cupos_totales'             => $this->request->getPost('cupos_totales') ?? 10,
            'tiempo_por_atencion_minutos' => $this->request->getPost('tiempo_por_atencion_minutos') ?? 15,
            'cod_art_servicio'          => $this->request->getPost('cod_art_servicio') ?: null,
            'estado'                    => $this->request->getPost('estado') ?? 1,
        ];

        if ($id) {
            $model->update($id, $data);
            $msg = 'Horario actualizado';
        } else {
            $model->insert($data);
            $msg = 'Horario programado';
        }

        return redirect()->to('cmHorarios')->with('msg', $msg);
    }
}
