<?php

namespace App\Controllers;

use App\Models\CmPacientesModel;
use App\Models\CmPacienteResponsableModel;
use App\Models\ClientesModel;

class CmPacientes extends BaseController
{
    public function index()
    {
        $data = [
            'titulo' => 'Directorio de Pacientes Clínicos',
            'menu' => ['p' => 40, 'i' => 49]
        ];
        
        return view('cm_pacientes/index', $data);
    }

    public function get_pacientes()
    {
        $db = \Config\Database::connect();
        $busqueda = $this->request->getPost('busqueda');
        $params = [];
        
        // Buscar en CM_PACIENTES + CLIENTES (pacientes ya registrados)
        $sql = "SELECT p.id, p.cliente_id, p.tipo_sangre, p.alergias, p.enfermedades_cronicas,
                       p.contacto_emergencia, p.telefono_emergencia,
                       c.CLI_NOMBRE, c.CLI_RUC_ESPOSA as DNI
                FROM CM_PACIENTES p
                INNER JOIN CLIENTES c ON c.CLI_CODCLIE = p.cliente_id
                WHERE 1=1
                  AND c.CLI_CP = 'C' ";
        
        if (!empty($busqueda)) {
            $sql .= "AND (c.CLI_NOMBRE LIKE ? OR c.CLI_RUC_ESPOSA LIKE ? OR c.CLI_RUC_ESPOSO LIKE ?) ";
            $like = '%' . $busqueda . '%';
            $params = [$like, $like, $like];
        }
        $sql .= "ORDER BY p.id DESC";
        
        $pacientes = $db->query($sql, $params)->getResult();
        
        // Si no hay resultados, buscar en CLIENTES directamente (no registrados aun en CM_PACIENTES)
        if (empty($pacientes) && !empty($busqueda)) {
            $sql2 = "SELECT 0 as id, c.CLI_CODCLIE as cliente_id, NULL as tipo_sangre, NULL as alergias,
                            NULL as enfermedades_cronicas, NULL as contacto_emergencia, NULL as telefono_emergencia,
                            c.CLI_NOMBRE, c.CLI_RUC_ESPOSA as DNI
                     FROM CLIENTES c
                     WHERE c.CLI_CP = 'C'
                       AND NOT EXISTS (SELECT 1 FROM CM_PACIENTES p2 WHERE p2.cliente_id = c.CLI_CODCLIE)
                       AND (c.CLI_NOMBRE LIKE ? OR c.CLI_RUC_ESPOSA LIKE ? OR c.CLI_RUC_ESPOSO LIKE ?)
                     ORDER BY c.CLI_CODCLIE DESC";
            $like = '%' . $busqueda . '%';
            $pacientes = $db->query($sql2, [$like, $like, $like])->getResult();
        }
        
        return $this->response->setJSON($pacientes);
    }

    public function buscar_titular()
    {
        $busqueda = $this->request->getPost('busqueda');
        if (empty($busqueda)) return $this->response->setJSON([]);

        $clientesModel = new ClientesModel();
        $resultados = $clientesModel->get_personas($busqueda, null, 25, 'C', 'simple', false);
        return $this->response->setJSON($resultados);
    }

    public function guardar()
    {
        $pacienteModel = new CmPacientesModel();
        $id = $this->request->getPost('id');
        
        $data = [
            'cliente_id' => $this->request->getPost('cliente_id'),
            'tipo_sangre' => $this->request->getPost('tipo_sangre'),
            'alergias' => $this->request->getPost('alergias'),
            'enfermedades_cronicas' => $this->request->getPost('enfermedades_cronicas'),
            'contacto_emergencia' => $this->request->getPost('contacto_emergencia'),
            'telefono_emergencia' => $this->request->getPost('telefono_emergencia'),
            'observaciones_medicas' => $this->request->getPost('observaciones_medicas'),
            'consentimiento_datos' => $this->request->getPost('consentimiento_datos') ? 1 : 0,
            'estado' => 1
        ];

        if ($id) {
            $pacienteModel->update($id, $data);
            $msg = 'Paciente actualizado';
        } else {
            if ($pacienteModel->insert($data)) {
                $msg = 'Paciente guardado';
            } else {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al guardar']);
            }
        }
        return $this->response->setJSON(['status' => 'success', 'msg' => $msg]);
    }

    public function importar_desde_dni()
    {
        $dni = trim($this->request->getPost('dni'));
        if (empty($dni) || !ctype_digit($dni) || !in_array(strlen($dni), [8, 11])) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Ingrese un DNI (8 dígitos) o RUC (11 dígitos) válido']);
        }

        $db = \Config\Database::connect();
        $longitud = strlen($dni);
        $tipoDoc = $longitud == 8 ? 1 : 2;
        $clientesModel = new \App\Models\ClientesModel();
        $existente = $clientesModel->get_pos_id($dni, $tipoDoc);
        
        // Siempre buscar en BD local primero
        if ($existente) {
            $cli = $db->query("SELECT CLI_CODCLIE, CLI_NOMBRE, CLI_CASA_DIREC, CLI_TELEF1, CLI_FECHA_NAC, CLI_RUC_ESPOSA, CLI_RUC_ESPOSO FROM CLIENTES WHERE CLI_CODCLIE = ?", [$existente])->getRow();
            $paciente = $db->table('CM_PACIENTES')->where('cliente_id', $existente)->get()->getRow();
            $pid = $paciente ? $paciente->id : null;
            $fnac_db = ($cli->CLI_FECHA_NAC && $cli->CLI_FECHA_NAC != '1900-01-01 00:00:00.000') ? trim($cli->CLI_FECHA_NAC) : null;
            // Convertir a YYYY-MM-DD para input HTML (soporta DD/MM/YYYY, YYYY-MM-DD y datetime)
            $fnac_html = null;
            if ($fnac_db) {
                // Formato DD/MM/YYYY → YYYY-MM-DD
                if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $fnac_db, $m)) {
                    $fnac_html = $m[3] . '-' . $m[2] . '-' . $m[1];
                }
                // Formato YYYY-MM-DD HH:MM:SS → YYYY-MM-DD
                elseif (preg_match('#^(\d{4})-(\d{2})-(\d{2})#', $fnac_db, $m)) {
                    $fnac_html = $m[1] . '-' . $m[2] . '-' . $m[3];
                }
                // Intentar strtotime como fallback
                else {
                    $ts = strtotime($fnac_db);
                    if ($ts) $fnac_html = date('Y-m-d', $ts);
                }
            }
            
            $dir = trim($cli->CLI_CASA_DIREC);
            $tel = trim($cli->CLI_TELEF1);
            $completo = !empty($fnac_html) && !empty($dir);
            
            return $this->response->setJSON([
                'status' => 'exists',
                'msg' => 'Encontrado en BD local',
                'paciente_id' => $pid, 'cliente_id' => $existente,
                'nombre' => $cli->CLI_NOMBRE, 'direccion' => $dir, 'telefono' => $tel,
                'fecha_nac' => $fnac_html, 'dni' => $dni,
                'datos_completos' => $completo
            ]);
        }
        
        // No existe en BD local - intentar Factiliza
        $forzar = $this->request->getPost('forzar_api') == '1';
        if (!$forzar) {
            return $this->response->setJSON(['status' => 'not_found', 'msg' => 'No encontrado en BD local. ¿Importar de Factiliza?']);
        }
        
        // Llamar a Factiliza
        $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI1NTgiLCJuYW1lIjoiUEVSVklMTEEiLCJlbWFpbCI6InBlcmV6dmlsbGFsdGFAZ21haWwuY29tIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiY29uc3VsdG9yIn0.tAPf6AgpLBrOBIaiuH8zxVtB4gNG05e52zHWPP0q40o';
        $endpoint = $longitud == 8 ? 'dni' : 'ruc';
        $url = 'https://api.factiliza.com/v1/' . $endpoint . '/info/' . $dni;
        
        try {
            $client = \Config\Services::curlrequest();
            $response = $client->get($url, ['headers' => ['Authorization' => 'Bearer ' . $apiKey], 'timeout' => 10]);
            $body = json_decode($response->getBody());
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No se pudo consultar Factiliza. Verifique conexión.']);
        }
        
        if (!isset($body->data)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No se encontraron datos para ' . $dni]);
        }
        
        $data = $body->data;
        $nombre = trim(strtoupper(substr(($data->nombres ?? '') . ' ' . ($data->apellido_paterno ?? '') . ' ' . ($data->apellido_materno ?? ''), 0, 120)));
        $direccion = trim(strtoupper(substr($data->direccion ?? '', 0, 120)));
        $fecha_nac_db = $data->fecha_nacimiento ?? null;
        
        // Crear CLIENTES
        $cod = $clientesModel->get_max_id();
        $clientesModel->set_persona([
            'CLI_CODCLIE' => $cod, 'CLI_CODCIA' => 25, 'CLI_CP' => 'C',
            'CLI_NOMBRE' => $nombre, 'CLI_NOMBRE_ESPOSO' => $nombre,
            'CLI_CASA_DIREC' => $direccion, 'CLI_CASA_NUM' => 0,
            'CLI_TRAB_DIREC' => substr($direccion, 0, 30),
            'CLI_RUC_ESPOSO' => $longitud == 11 ? $dni : '', 'CLI_RUC_ESPOSA' => $longitud == 8 ? $dni : '',
            'CLI_123' => 1, 'CLI_TELEF1' => '', 'CLI_ESTADO' => 'A', 'CLI_MONEDA' => 'S',
            'CLI_AUTO1' => '07', 'CLI_OTRO_CONTR' => 1, 'CLI_TIPO_BLOQ1' => 1,
            'CLI_GRUPO' => 1, 'CLI_DIA_VISITA' => 3, 'CLI_LUGAR_CASA' => 1, 'CLI_LUGAR_TRAB' => 1,
            'CLI_CUENTA_CONTAB2' => 1, 'CLI_DIAS_FAC' => 2, 'CLI_TIPOCLI' => 7,
            'CLI_FECHA_NAC' => $fecha_nac_db, 'CLI_FECHAHORA' => date('y/m/d h:i a') . ' CM',
            'CLI_LETRA' => 0, 'CLI_LIMCRE' => 0, 'CLI_FECHA_FAC' => 0, 'CLI_PORDESCTO' => 0,
            'CLI_SALDO' => 0, 'CLI_DIAS_CRED' => 0, 'CLI_LIMCRE2' => 0,
            'CLI_CASA_ZONA' => 0, 'CLI_CASA_SUBZONA' => 0, 'CLI_TRAB_ZONA' => 0, 'CLI_TRAB_SUBZONA' => 0,
            'CLI_TRAB_PROV' => 0, 'CLI_ZONA_NEW' => 0, 'CLI_CASA_NUM' => 0, 'CLI_TRAB_NUM' => 0,
            'CLI_SUBGRUPO' => 0, 'CLI_DIVISION' => 0, 'CLI_HISTORIA' => 0, 'CLI_CASA1' => '',
            'CLI_CASA2' => '', 'CLI_REGPUB1' => '', 'CLI_REGPUB2' => '', 'CLI_AUTOAVALUO' => '', 'CLI_PRENDA' => '',
            'CLI_AUTO2' => '', 'CLI_IGV_INCLUIDO' => '', 'CLI_NOMBRE_ESPOSA' => '', 'CLI_NOMBRE_EMPRESA' => '',
            'CLI_TELEF2' => '', 'CLI_RUC_EMPRESA' => '', 'CLI_TIPO_BLOQ2' => '', 'CLI_TIPO_BLOQ3' => '', 'CLI_TIPO_BLOQ4' => '',
            'CLI_DET_TOT' => '', 'CLI_NOM_LET1' => '', 'CLI_NOM_LET2' => '', 'CLI_CODART' => '', 'CLI_NUCLEO' => '',
            'CLI_CUENTA_CONTAB' => '', 'CLI_CIA_REF' => '', 'CLI_PRECIOS' => '', 'CLI_PROGRAMADO' => '',
            'CLI_CUENTA_CONTAB22' => '', 'CLI_TIPO' => '', 'CLI_CIARELA' => '', 'CLI_MARCAID' => '',
        ]);
        $clientesModel->set_dir_persona([
            'CODCIA' => 25, 'CODCLI' => $cod, 'CP' => 'C', 'DIREC' => substr($direccion, 0, 60),
            'DIRCOMP' => substr($direccion, 0, 100), 'REF' => '',
            'CLI_LUGAR_TRAB' => 0, 'CLI_TRAB_ZONA' => 0, 'CLI_CASA_SUBZONA' => 0, 'CLI_TRAB_SUBZONA' => 0, 'NUMERO' => 0
        ]);
        
        $db->query("INSERT INTO CM_PACIENTES (cliente_id, estado) VALUES (?, 1)", [$cod]);
        $r = $db->query("SELECT @@IDENTITY AS id")->getRow();
        $paciente_id = $r ? intval($r->id) : 0;
        
        // Convertir a YYYY-MM-DD para HTML
        $fnac_html = null;
        if ($fecha_nac_db && preg_match('#(\d{2})/(\d{2})/(\d{4})#', $fecha_nac_db, $m)) {
            $fnac_html = $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        
        return $this->response->setJSON([
            'status' => 'success', 'msg' => 'Importado: ' . $nombre,
            'paciente_id' => $paciente_id, 'cliente_id' => $cod,
            'nombre' => $nombre, 'direccion' => $direccion, 'fecha_nac' => $fnac_html
        ]);
    }

    public function completar_desde_api()
    {
        $dni = trim($this->request->getPost('dni'));
        $cliente_id = $this->request->getPost('cliente_id');
        
        if (empty($dni) || !ctype_digit($dni)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'DNI inválido']);
        }
        
        $longitud = strlen($dni);
        $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI1NTgiLCJuYW1lIjoiUEVSVklMTEEiLCJlbWFpbCI6InBlcmV6dmlsbGFsdGFAZ21haWwuY29tIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiY29uc3VsdG9yIn0.tAPf6AgpLBrOBIaiuH8zxVtB4gNG05e52zHWPP0q40o';
        $endpoint = $longitud == 8 ? 'dni' : 'ruc';
        $url = 'https://api.factiliza.com/v1/' . $endpoint . '/info/' . $dni;
        
        try {
            $client = \Config\Services::curlrequest();
            $response = $client->get($url, ['headers' => ['Authorization' => 'Bearer ' . $apiKey], 'timeout' => 10]);
            $body = json_decode($response->getBody());
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error de conexión con Factiliza']);
        }
        
        if (!isset($body->data)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Sin datos para ' . $dni]);
        }
        
        $data = $body->data;
        $direccion = trim(strtoupper(substr($data->direccion ?? '', 0, 120)));
        $fecha_nac_api = $data->fecha_nacimiento ?? null; // DD/MM/YYYY desde API
        
        $db = \Config\Database::connect();
        // Guardar en BD como viene de la API (DD/MM/YYYY es el formato que usa CLIENTES)
        $db->query("UPDATE CLIENTES SET CLI_CASA_DIREC=?, CLI_TRAB_DIREC=?, CLI_FECHA_NAC=? WHERE CLI_CODCLIE=?",
            [$direccion, substr($direccion, 0, 30), $fecha_nac_api, $cliente_id]);
        
        // Convertir a YYYY-MM-DD para input HTML
        $fnac_html = null;
        if ($fecha_nac_api && preg_match('#(\d{2})/(\d{2})/(\d{4})#', $fecha_nac_api, $m)) {
            $fnac_html = $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        
        return $this->response->setJSON([
            'status' => 'success', 'msg' => 'Datos completados desde Factiliza',
            'direccion' => $direccion, 'fecha_nac' => $fnac_html
        ]);
    }

    public function guardar_desde_modal()
    {
        $db = \Config\Database::connect();
        $codigo = $this->request->getPost('codigo');
        $dni = trim($this->request->getPost('dni'));
        $nombre = trim(strtoupper($this->request->getPost('nombre')));
        $telefono = trim($this->request->getPost('telefono'));
        $direccion = trim($this->request->getPost('direccion'));
        $fecha_nac = $this->request->getPost('fecha_nac');
        // Convertir YYYY-MM-DD (HTML input) a DD/MM/YYYY (formato BD)
        if ($fecha_nac && preg_match('#(\d{4})-(\d{2})-(\d{2})#', $fecha_nac, $m)) {
            $fecha_nac = $m[3] . '/' . $m[2] . '/' . $m[1];
        }
        
        if (empty($nombre) || strlen($nombre) < 3) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Nombre inválido']);
        }
        
        $clientesModel = new \App\Models\ClientesModel();
        $dnilen = strlen($dni);
        
        // Si ya tiene código, actualizar; si no, crear nuevo
        if ($codigo && $codigo > 0) {
            $db->query("UPDATE CLIENTES SET CLI_NOMBRE=?, CLI_NOMBRE_ESPOSO=?, CLI_TELEF1=?, CLI_CASA_DIREC=?, CLI_TRAB_DIREC=?, CLI_FECHA_NAC=?, CLI_RUC_ESPOSA=?, CLI_RUC_ESPOSO=? WHERE CLI_CODCLIE=?",
                [substr($nombre, 0, 120), substr($nombre, 0, 120), substr($telefono, 0, 12), substr($direccion, 0, 120), substr($direccion, 0, 30),
                 $fecha_nac ?: null, $dnilen == 8 ? $dni : '', $dnilen == 11 ? $dni : '', $codigo]);
        } else {
            $codigo = $clientesModel->get_max_id();
            $data = [
                'CLI_CODCLIE' => $codigo, 'CLI_CODCIA' => 25, 'CLI_CP' => 'C',
                'CLI_NOMBRE' => substr($nombre, 0, 120), 'CLI_NOMBRE_ESPOSO' => substr($nombre, 0, 120), 'CLI_NOMBRE_ESPOSA' => '', 'CLI_NOMBRE_EMPRESA' => '',
                'CLI_123' => 1, 'CLI_TELEF1' => substr($telefono, 0, 12), 'CLI_TELEF2' => '',
                'CLI_CASA_DIREC' => substr($direccion, 0, 120), 'CLI_CASA_NUM' => 0, 'CLI_CASA_ZONA' => 0, 'CLI_CASA_SUBZONA' => 0,
                'CLI_TRAB_DIREC' => substr($direccion, 0, 30), 'CLI_TRAB_NUM' => 0, 'CLI_TRAB_ZONA' => 0, 'CLI_TRAB_SUBZONA' => 0, 'CLI_TRAB_PROV' => 0,
                'CLI_RUC_ESPOSO' => $dnilen == 11 ? $dni : '', 'CLI_RUC_ESPOSA' => $dnilen == 8 ? $dni : '', 'CLI_RUC_EMPRESA' => '',
                'CLI_CASA1' => '', 'CLI_CASA2' => '', 'CLI_REGPUB1' => '', 'CLI_REGPUB2' => '', 'CLI_AUTOAVALUO' => '', 'CLI_PRENDA' => '',
                'CLI_AUTO1' => '07', 'CLI_AUTO2' => '', 'CLI_IGV_INCLUIDO' => '', 'CLI_OTRO_CONTR' => 1, 'CLI_LETRA' => 0, 'CLI_LIMCRE' => 0,
                'CLI_FECHA_FAC' => 0, 'CLI_TIPO_BLOQ1' => 1, 'CLI_TIPO_BLOQ2' => '', 'CLI_TIPO_BLOQ3' => '', 'CLI_TIPO_BLOQ4' => '',
                'CLI_DET_TOT' => '', 'CLI_NOM_LET1' => '', 'CLI_NOM_LET2' => '', 'CLI_GRUPO' => 1, 'CLI_SUBGRUPO' => 0, 'CLI_DIVISION' => 0,
                'CLI_ESTADO' => 'A', 'CLI_MONEDA' => 'S', 'CLI_CODART' => '', 'CLI_NUCLEO' => '', 'CLI_CUENTA_CONTAB' => '', 'CLI_CIA_REF' => '',
                'CLI_PORDESCTO' => 0, 'CLI_SALDO' => 0, 'CLI_PRECIOS' => '', 'CLI_DIA_VISITA' => 3, 'CLI_ZONA_NEW' => 0,
                'CLI_PROGRAMADO' => '', 'CLI_LUGAR_CASA' => 1, 'CLI_LUGAR_TRAB' => 1, 'CLI_CUENTA_CONTAB2' => 1, 'CLI_DIAS_CRED' => 0, 'CLI_DIAS_FAC' => 2,
                'CLI_CUENTA_CONTAB22' => '', 'CLI_LIMCRE2' => 0, 'CLI_TIPO' => '', 'CLI_FECHAHORA' => date('y/m/d h:i a') . ' CM',
                'CLI_CIARELA' => '', 'CLI_MARCAID' => '', 'CLI_TIPOCLI' => 7, 'CLI_FECHA_NAC' => $fecha_nac ?: null, 'CLI_HISTORIA' => 0
            ];
            $clientesModel->set_persona($data);
            $clientesModel->set_dir_persona([
                'CODCIA' => 25, 'CODCLI' => $codigo, 'CP' => 'C', 'DIREC' => substr($direccion, 0, 60),
                'DIRCOMP' => substr($direccion, 0, 100), 'CLI_LUGAR_TRAB' => 0, 'REF' => '', 'CLI_TRAB_ZONA' => 0, 'CLI_CASA_SUBZONA' => 0, 'CLI_TRAB_SUBZONA' => 0, 'NUMERO' => 0
            ]);
        }
        
        // Verificar si ya es paciente CM_PACIENTES
        $paciente = $db->table('CM_PACIENTES')->where('cliente_id', $codigo)->get()->getRow();
        if (!$paciente) {
            $db->query("INSERT INTO CM_PACIENTES (cliente_id, estado) VALUES (?, 1)", [$codigo]);
            $r = $db->query("SELECT @@IDENTITY AS id")->getRow();
            $paciente_id = $r ? intval($r->id) : 0;
        } else {
            $paciente_id = $paciente->id;
        }
        
        return $this->response->setJSON(['status' => 'success', 'paciente_id' => $paciente_id, 'nombre' => $nombre]);
    }

    public function get_one()
    {
        $id = $this->request->getPost('id');
        if (!$id) return $this->response->setJSON(null);
        $db = \Config\Database::connect();
        $p = $db->query("SELECT p.*, c.CLI_NOMBRE FROM CM_PACIENTES p INNER JOIN CLIENTES c ON c.CLI_CODCLIE = p.cliente_id WHERE p.id = ?", [$id])->getRow();
        return $this->response->setJSON($p);
    }
}