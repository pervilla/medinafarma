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
            $db->query("INSERT INTO CM_HISTORIA (cita_id, paciente_id) VALUES (?, ?)", [$cita_id, $cita->paciente_id]);
            $historia = $db->table('CM_HISTORIA')->where('cita_id', $cita_id)->get()->getRow();
        }
        
        return view('cm_historia/triaje', [
            'cita' => $cita,
            'historia' => $historia,
            'titulo' => 'Triaje - ' . $cita->CLI_NOMBRE,
            'menu' => ['p' => 40, 'i' => 46]
        ]);
    }
    
    public function imprimir_triaje($cita_id = null)
    {
        if (!$cita_id) return redirect()->to('cmCitas/listado');
        
        $db = \Config\Database::connect();
        
        // Obtener cita con datos del paciente
        $cita = $db->query("
            SELECT cc.*, P.id AS paciente_id, P.alergias, P.enfermedades_cronicas, P.observaciones_medicas, P.tipo_sangre, P.contacto_emergencia, P.telefono_emergencia,
                   C.CLI_NOMBRE, C.CLI_RUC_ESPOSA AS DNI, C.CLI_TELEF1,
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
            $db->query("INSERT INTO CM_HISTORIA (cita_id, paciente_id) VALUES (?, ?)", [$cita_id, $cita->paciente_id]);
            $historia = $db->table('CM_HISTORIA')->where('cita_id', $cita_id)->get()->getRow();
        }
        
        return view('cm_historia/imprimir_triaje', [
            'cita' => $cita,
            'historia' => $historia,
            'titulo' => 'Hoja de Triaje - ' . $cita->CLI_NOMBRE
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
        
        $db->query("UPDATE CM_HISTORIA SET presion_arterial=?, temperatura=?, peso=?, talla=?, saturacion=?, frec_cardiaca=?, frec_respiratoria=?, updated_at=GETDATE() WHERE id=?",
            [$this->request->getPost('presion_arterial'), $this->request->getPost('temperatura'),
             $this->request->getPost('peso'), $this->request->getPost('talla'),
             $this->request->getPost('saturacion'), $this->request->getPost('frec_cardiaca'),
             $this->request->getPost('frec_respiratoria'), $historia_id]);
        
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
        // resultado_atencion: guardar | atendido | pendiente
        $resultado = $this->request->getPost('resultado_atencion') ?: 'guardar';
        
        $historia_estado = ($resultado == 'guardar') ? 1 : 2;
        $db->query("UPDATE CM_HISTORIA SET examen_clinico=?, plan_trabajo=?, indicaciones=?, estado=?, updated_at=GETDATE() WHERE id=?",
            [$this->request->getPost('examen_clinico'), $this->request->getPost('plan_trabajo'),
             $this->request->getPost('indicaciones'), $historia_estado, $historia_id]);
        
        $cita_estado = null;
        if ($resultado == 'atendido') {
            $cita_estado = 2;
            $msg = 'Atención finalizada. Cita marcada como atendida.';
        } elseif ($resultado == 'pendiente') {
            $cita_estado = 4;
            $msg = 'Atención guardada con exámenes pendientes. La cita podrá cerrarse cuando se completen.';
        } else {
            $msg = 'Atención guardada. Puede continuar después.';
        }
        
        if ($cita_estado !== null) {
            $db->query("UPDATE CM_CITAS SET estado = ?, updated_at = GETDATE() WHERE id = ?", [$cita_estado, $cita_id]);
        }
        
        return redirect()->to('cmHistoria/atencion/' . $cita_id)->with('msg', $msg);
    }
    
    public function actualizar_diagnostico()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');
        
        $allowed = ['tipo', 'caso', 'alta'];
        if (!in_array($field, $allowed)) return $this->response->setJSON(['status' => 'error']);
        
        $db->query("UPDATE CM_HISTORIA_DIAGNOSTICO SET {$field} = ? WHERE id = ?", [$value, $id]);
        return $this->response->setJSON(['status' => 'ok']);
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
    
    public function buscar_articulo()
    {
        $term = $this->request->getPost('term');
        $db = \Config\Database::connect();
        $sql = "SELECT TOP 20 ART_KEY, RTRIM(ART_NOMBRE) AS ART_NOMBRE
                FROM ARTI
                WHERE ART_SITUACION = 0 AND ART_NOMBRE LIKE ?
                ORDER BY ART_NOMBRE";
        $result = $db->query($sql, ['%'.$term.'%'])->getResult();
        return $this->response->setJSON($result);
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
    
    public function receta($cita_id = null)
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
        
        return view('cm_historia/receta', [
            'cita' => $cita, 'historia' => $historia,
            'diagnosticos' => $diagnosticos, 'recetas' => $recetas
        ]);
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
