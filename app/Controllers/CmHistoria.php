<?php

namespace App\Controllers;

class CmHistoria extends BaseController
{
    public function triaje($cita_id = null)
    {
        if (!$cita_id) return redirect()->to('cmCitas/listado');
        
        $db = \Config\Database::connect();
        
        // Obtener cita con datos del paciente
        $cita = $db->query("
            SELECT cc.*, P.id AS paciente_id, C.CLI_NOMBRE, C.CLI_RUC_ESPOSA AS DNI, C.CLI_TELEF1,
                   C.CLI_FECHA_NAC, FLOOR(DATEDIFF(DAY, C.CLI_FECHA_NAC, GETDATE()) / 365.25) AS edad,
                   H.fecha_especifica, (M.nombres + ' ' + M.apellidos) AS medico
            FROM CM_CITAS cc
            INNER JOIN CM_PACIENTES P ON P.id = cc.paciente_id
            INNER JOIN CLIENTES C ON C.CLI_CODCLIE = P.cliente_id
            INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = cc.horario_id
            LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id
            WHERE cc.id = ?
        ", [$cita_id])->getRow();
        
        if (!$cita) return redirect()->to('cmCitas/listado');
        
        // Buscar o crear historia
        $historia = $db->table('CM_HISTORIA')->where('cita_id', $cita_id)->get()->getRow();
        if (!$historia) {
            $db->table('CM_HISTORIA')->insert([
                'cita_id' => $cita_id,
                'paciente_id' => $cita->paciente_id,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $historia = $db->table('CM_HISTORIA')->where('cita_id', $cita_id)->get()->getRow();
        }
        
        return view('cm_historia/triaje', [
            'cita' => $cita,
            'historia' => $historia,
            'titulo' => 'Triaje - ' . $cita->CLI_NOMBRE,
            'menu' => ['p' => 40, 'i' => 46]
        ]);
    }
    
    public function guardar_triaje()
    {
        $db = \Config\Database::connect();
        $historia_id = $this->request->getPost('historia_id');
        $cita_id = $this->request->getPost('cita_id');
        
        if (!$historia_id) {
            return redirect()->back()->with('error', 'Historia no encontrada');
        }
        
        $data = [
            'presion_arterial' => $this->request->getPost('presion_arterial'),
            'temperatura' => $this->request->getPost('temperatura'),
            'peso' => $this->request->getPost('peso'),
            'talla' => $this->request->getPost('talla'),
            'saturacion' => $this->request->getPost('saturacion'),
            'frec_cardiaca' => $this->request->getPost('frec_cardiaca'),
            'frec_respiratoria' => $this->request->getPost('frec_respiratoria'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $db->table('CM_HISTORIA')->where('id', $historia_id)->update($data);
        
        return redirect()->to('cmHistoria/atencion/' . $cita_id)->with('msg', 'Triaje guardado');
    }
    
    public function atencion($cita_id = null)
    {
        if (!$cita_id) return redirect()->to('cmCitas/listado');
        
        $db = \Config\Database::connect();
        
        $cita = $db->query("
            SELECT cc.*, P.id AS paciente_id, C.CLI_NOMBRE, C.CLI_RUC_ESPOSA AS DNI, C.CLI_TELEF1,
                   C.CLI_FECHA_NAC, FLOOR(DATEDIFF(DAY, C.CLI_FECHA_NAC, GETDATE()) / 365.25) AS edad,
                   H.fecha_especifica, (M.nombres + ' ' + M.apellidos) AS medico
            FROM CM_CITAS cc
            INNER JOIN CM_PACIENTES P ON P.id = cc.paciente_id
            INNER JOIN CLIENTES C ON C.CLI_CODCLIE = P.cliente_id
            INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = cc.horario_id
            LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id
            WHERE cc.id = ?
        ", [$cita_id])->getRow();
        
        if (!$cita) return redirect()->to('cmCitas/listado');
        
        $historia = $db->table('CM_HISTORIA')->where('cita_id', $cita_id)->get()->getRow();
        if (!$historia) {
            return redirect()->to('cmHistoria/triaje/' . $cita_id);
        }
        
        $diagnosticos = $db->table('CM_HISTORIA_DIAGNOSTICO')->where('historia_id', $historia->id)->get()->getResult();
        $recetas = $db->table('CM_HISTORIA_RECETA')->where('historia_id', $historia->id)->get()->getResult();
        
        return view('cm_historia/atencion', [
            'cita' => $cita,
            'historia' => $historia,
            'diagnosticos' => $diagnosticos,
            'recetas' => $recetas,
            'titulo' => 'Atención - ' . $cita->CLI_NOMBRE,
            'menu' => ['p' => 40, 'i' => 46]
        ]);
    }
    
    public function guardar_atencion()
    {
        $db = \Config\Database::connect();
        $historia_id = $this->request->getPost('historia_id');
        $cita_id = $this->request->getPost('cita_id');
        
        $data = [
            'examen_clinico' => $this->request->getPost('examen_clinico'),
            'plan_trabajo' => $this->request->getPost('plan_trabajo'),
            'indicaciones' => $this->request->getPost('indicaciones'),
            'estado' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $db->table('CM_HISTORIA')->where('id', $historia_id)->update($data);
        
        // Marcar cita como atendida
        $db->query("UPDATE CM_CITAS SET estado = 2, updated_at = GETDATE() WHERE id = ?", [$cita_id]);
        
        return redirect()->to('cmHistoria/atencion/' . $cita_id)->with('msg', 'Atención guardada. Cita marcada como atendida.');
    }
    
    public function guardar_diagnostico()
    {
        $db = \Config\Database::connect();
        $historia_id = $this->request->getPost('historia_id');
        
        $db->table('CM_HISTORIA_DIAGNOSTICO')->insert([
            'historia_id' => $historia_id,
            'cie_codigo' => $this->request->getPost('cie_codigo'),
            'cie_descripcion' => $this->request->getPost('cie_descripcion'),
            'tipo' => $this->request->getPost('tipo'),
            'caso' => $this->request->getPost('caso'),
            'alta' => $this->request->getPost('alta'),
        ]);
        
        return redirect()->back()->with('msg', 'Diagnóstico agregado');
    }
    
    public function guardar_receta()
    {
        $db = \Config\Database::connect();
        $historia_id = $this->request->getPost('historia_id');
        
        $db->table('CM_HISTORIA_RECETA')->insert([
            'historia_id' => $historia_id,
            'art_key' => $this->request->getPost('art_key'),
            'nombre_articulo' => $this->request->getPost('nombre_articulo'),
            'cantidad' => $this->request->getPost('cantidad') ?: 1,
            'dias' => $this->request->getPost('dias') ?: 1,
            'indicaciones' => $this->request->getPost('indicaciones'),
        ]);
        
        return redirect()->back()->with('msg', 'Receta agregada');
    }
    
    public function eliminar_diagnostico($id = null)
    {
        if (!$id) return redirect()->back();
        $db = \Config\Database::connect();
        $db->table('CM_HISTORIA_DIAGNOSTICO')->where('id', $id)->delete();
        return redirect()->back()->with('msg', 'Diagnóstico eliminado');
    }
    
    public function eliminar_receta($id = null)
    {
        if (!$id) return redirect()->back();
        $db = \Config\Database::connect();
        $db->table('CM_HISTORIA_RECETA')->where('id', $id)->delete();
        return redirect()->back()->with('msg', 'Receta eliminada');
    }
    
    public function ver($cita_id = null)
    {
        if (!$cita_id) return redirect()->to('cmCitas/listado');
        
        $db = \Config\Database::connect();
        
        $cita = $db->query("
            SELECT cc.*, C.CLI_NOMBRE, C.CLI_RUC_ESPOSA AS DNI, FLOOR(DATEDIFF(DAY, C.CLI_FECHA_NAC, GETDATE()) / 365.25) AS edad,
                   H.fecha_especifica, (M.nombres + ' ' + M.apellidos) AS medico
            FROM CM_CITAS cc
            INNER JOIN CM_PACIENTES P ON P.id = cc.paciente_id
            INNER JOIN CLIENTES C ON C.CLI_CODCLIE = P.cliente_id
            INNER JOIN CM_MEDICOS_HORARIOS H ON H.id = cc.horario_id
            LEFT JOIN CM_MEDICOS M ON M.id = H.medico_id
            WHERE cc.id = ?
        ", [$cita_id])->getRow();
        
        if (!$cita) return redirect()->to('cmCitas/listado');
        
        $historia = $db->table('CM_HISTORIA')->where('cita_id', $cita_id)->get()->getRow();
        $diagnosticos = [];
        $recetas = [];
        
        if ($historia) {
            $diagnosticos = $db->table('CM_HISTORIA_DIAGNOSTICO')->where('historia_id', $historia->id)->get()->getResult();
            $recetas = $db->table('CM_HISTORIA_RECETA')->where('historia_id', $historia->id)->get()->getResult();
        }
        
        return view('cm_historia/ver', [
            'cita' => $cita,
            'historia' => $historia,
            'diagnosticos' => $diagnosticos,
            'recetas' => $recetas,
            'titulo' => 'Historia Clínica',
            'menu' => ['p' => 40, 'i' => 46]
        ]);
    }
}
