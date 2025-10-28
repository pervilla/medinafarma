<?php

namespace App\Controllers;
use App\Models\ClientesModel;
use App\Models\AllogModel;
use App\Models\FacartModel;
use App\Models\HistoriaModel;
use App\Models\CitasModel;
use Peru\Jne\DniFactory;
use Peru\Sunat\RucFactory;

class Personas extends BaseController {

    private $factilizaApiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI1NTgiLCJuYW1lIjoiUEVSVklMTEEiLCJlbWFpbCI6InBlcmV6dmlsbGFsdGFAZ21haWwuY29tIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiY29uc3VsdG9yIn0.tAPf6AgpLBrOBIaiuH8zxVtB4gNG05e52zHWPP0q40o';
    private $factilizaBaseUrl = 'https://api.factiliza.com/v1/';

    public function index() {     
        $data['menu']['p'] = 60;
        $data['menu']['i'] = 61;
        return view('personas/index',$data);
    }
    
    public function unir(){
        $data['menu']['p'] = 60;
        $data['menu']['i'] = 63;
        return view('personas/index_unir',$data);
    }
    
    public function get_personas(){
        $busqueda = $this->request->getVar('busqueda');
        $tipoCli = $this->request->getVar('tipoCli');
        
        $ClientesModel = new ClientesModel();
        $clientes = $ClientesModel->get_personas('','',$tipoCli,$busqueda);
        return $this->response->setJSON($clientes);
    }
    
    public function get_proveedores() {
        $camp = $this->request->getVar('term');
        $ClientesModel = new ClientesModel();
        $citas = $ClientesModel->get_proveedores($camp);
        return $this->response->setJSON($citas);
    }  

    public function save_persona($request=false){
        try {
            $this->request = $request ? $request : $this->request;
            
            $cod = $this->request->getVar('cod');
            $ruc = trim($this->request->getVar('ruc'));
            $nom = trim(strtoupper($this->request->getVar('nom')));
            $dir = trim(strtoupper($this->request->getVar('dir')));
            $tel = trim($this->request->getVar('tel'));
            $est = $this->request->getVar('est');
            $nac = $this->request->getVar('nac') ? $this->request->getVar('nac') : null;
            $his = $this->request->getVar('his') == 'on' ? 1 : 0;
            $tps = $this->request->getVar('tps');
            
            if (empty($ruc) || !in_array(strlen($ruc), [8, 11]) || !ctype_digit($ruc)) {
                $ruc='';
            }
            
            if (empty($nom) || strlen($nom) < 3) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Nombre inválido']);
            }
            
            if (empty($dir)) {
                $dir='';
            }
            
            $ClientesModel = new ClientesModel();
            
            if (empty($cod)) {
                $cod = $ClientesModel->get_max_id();
            }
            
            if ($est == 'nuevo') {
                // Solo validar duplicados si hay documento
                if (!empty($ruc)) {
                    $tipoDoc = strlen($ruc) == 8 ? 1 : 2;
                    $existente = $ClientesModel->get_pos_id($ruc, $tipoDoc);
                    if (!empty($existente)) {
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Documento ya registrado']);
                    }
                }                

                $data = array(
                    'CLI_CODCLIE' => $cod,          'CLI_CODCIA' => 25,         'CLI_CP' => $tps,            'CLI_NOMBRE' => substr($nom, 0, 120),
                    'CLI_NOMBRE_ESPOSO' => substr($nom, 0, 120),    'CLI_NOMBRE_ESPOSA' => '',  'CLI_NOMBRE_EMPRESA' => '', 'CLI_123' => 1,
                    'CLI_TELEF1' => substr($tel, 0, 12), 'CLI_TELEF2' => '',         'CLI_CASA_DIREC' => substr($dir, 0, 120),   'CLI_CASA_NUM' => 0,
                    'CLI_CASA_ZONA' => 0,           'CLI_CASA_SUBZONA' => 0,    'CLI_TRAB_DIREC' => substr($dir, 0, 30),   'CLI_TRAB_NUM' => 0,
                    'CLI_TRAB_ZONA' => 0,           'CLI_TRAB_SUBZONA' => 0,    'CLI_TRAB_PROV' => 0,       'CLI_RUC_ESPOSO' => strlen($ruc)==11?$ruc:'',
                    'CLI_RUC_ESPOSA' => strlen($ruc)==8?$ruc:'',                'CLI_RUC_EMPRESA' => '',    'CLI_CASA1' => '',
                    'CLI_CASA2' => '',              'CLI_REGPUB1' => '',        'CLI_REGPUB2' => '',        'CLI_AUTOAVALUO' => '',
                    'CLI_PRENDA' => '',             'CLI_AUTO1' => '07',           'CLI_AUTO2' => '',          'CLI_IGV_INCLUIDO' =>'',
                    'CLI_OTRO_CONTR' => 1,          'CLI_LETRA' => 0,           'CLI_LIMCRE' => 0,          'CLI_FECHA_FAC' => 0,
                    'CLI_TIPO_BLOQ1' => 1,          'CLI_TIPO_BLOQ2' => '',     'CLI_TIPO_BLOQ3' => '',     'CLI_TIPO_BLOQ4' => '',
                    'CLI_DET_TOT' =>  '',           'CLI_NOM_LET1' => '',       'CLI_NOM_LET2' => '',       'CLI_GRUPO' => 1,
                    'CLI_SUBGRUPO' => 0,            'CLI_DIVISION' => 0,        'CLI_ESTADO' => 'A',        'CLI_MONEDA' => 'S',
                    'CLI_CODART' => '',             'CLI_NUCLEO' =>  '',        'CLI_CUENTA_CONTAB' =>'',   'CLI_CIA_REF' =>'',
                    'CLI_PORDESCTO' => 0,           'CLI_SALDO' => 0,           'CLI_PRECIOS' =>'',         'CLI_DIA_VISITA' => 3,
                    'CLI_ZONA_NEW' => 0,            'CLI_PROGRAMADO' =>'',      'CLI_LUGAR_CASA' => 1,      'CLI_LUGAR_TRAB' => 1,
                    'CLI_CUENTA_CONTAB2' => 1,      'CLI_DIAS_CRED' => 0,       'CLI_DIAS_FAC' => 2,        'CLI_CUENTA_CONTAB22' =>'',
                    'CLI_LIMCRE2' => 0,             'CLI_TIPO' =>'',            'CLI_FECHAHORA' => date('y/m/d h:i a') . ' WEB',       'CLI_CIARELA' =>'',
                    'CLI_MARCAID' =>'',         'CLI_TIPOCLI' => 7,         'CLI_FECHA_NAC' => $nac,
                    'CLI_HISTORIA' => $his
                );
                $data2 = [
                    'CODCIA' => 25, 'CODCLI' => $cod, 'CP' => $tps,
                    'DIREC' => substr($dir, 0, 60), 'DIRCOMP' => substr($dir, 0, 100),
                    'CLI_LUGAR_TRAB' => 0,'REF' => '','CLI_TRAB_ZONA' => 0,'CLI_CASA_SUBZONA' => 0,'CLI_TRAB_SUBZONA' => 0,'NUMERO' => 0
                ];
                
                $resultado1 = $ClientesModel->set_persona($data);
                $resultado2 = $ClientesModel->set_dir_persona($data2);
                
                if ($resultado1 && $resultado2) {
                    return $this->response->setJSON([
                        'status' => 'success', 
                        'message' => 'Persona creada', 
                        'data' => [
                            'codigo' => $cod,
                            'documento' => $ruc,
                            'nombre' => $nom,
                            'direccion' => $dir,
                            'telefono' => $tel,
                            'fecha_nacimiento' => $nac
                        ]
                    ]);
                }
            }
            
            if ($est == 'editar') {
                $data = [
                    'CLI_CODCLIE' => $cod, 'CLI_NOMBRE' => substr($nom, 0, 120),
                    'CLI_NOMBRE_ESPOSO' => substr($nom, 0, 120), 'CLI_TELEF1' => substr($tel, 0, 12),
                    'CLI_CASA_DIREC' => substr($dir, 0, 120), 'CLI_TRAB_DIREC' => substr($dir, 0, 30),
                    'CLI_FECHA_NAC' => $nac, 'CLI_RUC_ESPOSO' => strlen($ruc) == 11 ? $ruc : '',
                    'CLI_RUC_ESPOSA' => strlen($ruc) == 8 ? $ruc : '', 'CLI_CP' => $tps
                ];
                
                $resultado = $ClientesModel->editar_persona($data, 0);
                
                if ($resultado) {
                    return $this->response->setJSON(['status' => 'success', 'message' => 'Persona actualizada', 'codigo' => $cod]);
                }
            }
            
            return $this->response->setJSON(['status' => 'error', 'message' => 'Error al procesar']);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en save_persona: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Error interno']);
        }
    }

    public function get_persona_sunat(){
        try {
            $documento = trim($this->request->getVar('ruc'));
            
            if (empty($documento)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Debe ingresar un documento',
                    'data' => null
                ]);
            }
            
            $longitud = strlen($documento);
            
            if (!in_array($longitud, [8, 11])) {
                return $this->response->setJSON([
                    'status' => 'error', 
                    'message' => 'El documento debe tener 8 dígitos (DNI) o 11 dígitos (RUC)',
                    'data' => null
                ]);
            }
            
            if (!ctype_digit($documento)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'El documento solo debe contener números',
                    'data' => null
                ]);
            }
            
            $ClientesModel = new ClientesModel();
            $tipoDoc = $longitud == 8 ? 1 : 2;
            
            $clienteExistente = $ClientesModel->get_pos_id($documento, $tipoDoc);
            
            if (!empty($clienteExistente)) {
                $cliente = $ClientesModel->get_personas($clienteExistente, '', '', '')[0];
                return $this->response->setJSON([
                    'status' => 'exists',
                    'message' => 'El documento ya está registrado',
                    'data' => [
                        'documento' => $longitud == 8 ? $cliente->CLI_RUC_ESPOSA : $cliente->CLI_RUC_ESPOSO,
                        'nombre' => $cliente->CLI_NOMBRE,
                        'direccion' => $cliente->CLI_CASA_DIREC,
                        'telefono' => $cliente->CLI_TELEF1,
                        'codigo' => $cliente->CLI_CODCLIE,
                        'fecha_nacimiento' => $cliente->CLI_FECHA_NAC
                    ]
                ]);
            }
            
            $datosApi = $this->consultarFactiliza($documento, $longitud);
            
            if ($datosApi['status'] === 'error') {
                return $this->response->setJSON($datosApi);
            }
            
            $nuevoId = $ClientesModel->get_max_id();
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Datos obtenidos correctamente',
                'data' => array_merge($datosApi['data'], ['codigo' => $nuevoId])
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en get_persona_sunat: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error interno del servidor',
                'data' => null
            ]);
        }
    }
    
    private function consultarFactiliza($documento, $longitud) {
        try {
            $apiKey = $this->factilizaApiKey;
            if (empty($apiKey)) {
                return ['status' => 'error', 'message' => 'API key no configurada'];
            }
            
            $endpoint = $longitud == 8 ? 'dni' : 'ruc';
            $baseUrl = $this->factilizaBaseUrl;
            $url = $baseUrl . $endpoint . '/info/' . $documento;
            
            $client = \Config\Services::curlrequest();
            
            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json'
                ],
                'timeout' => 10
            ]);
            
            $data = json_decode($response->getBody(), true);
            
            if (!$data || $data['status'] !== 200 || !$data['success']) {
                return [
                    'status' => 'error',
                    'message' => $data['message'] ?? 'Documento no encontrado'
                ];
            }
            
            $info = $data['data'];
            
            if ($longitud == 8) {
                return [
                    'status' => 'success',
                    'data' => [
                        'documento' => $info['numero'],
                        'nombre' => $info['nombre_completo'],
                        'direccion' => $info['direccion_completa'] ?? '',
                        'telefono' => '',
                        'fecha_nacimiento' => $info['fecha_nacimiento'] ?? ''
                    ]
                ];
            } else {
                return [
                    'status' => 'success', 
                    'data' => [
                        'documento' => $info['numero'],
                        'nombre' => $info['nombre_o_razon_social'],
                        'direccion' => $info['direccion_completa'] ?? '',
                        'telefono' => ''
                    ]
                ];
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error consultando Factiliza: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Error al consultar el servicio externo'];
        }
    }

    public function get_persona_sunat_ant(){
        $ruc = $this->request->getVar('ruc');
        $tam = strlen($ruc);
        $tip = $tam==8?1:($tam==11?2:0);
        $ClientesModel = new ClientesModel();
        $codcli = $ClientesModel->get_pos_id($ruc,$tip);
        if(empty($codcli)){
            $new_id = $ClientesModel->get_max_id();
        }else{
            $clientes = $ClientesModel->get_personas($codcli,'','','');
            $tip = 3;
        }        
        if($tip==1){
            ob_start(); 
            $data = file_get_contents("http://dniruc.apisperu.com/api/v1/dni/".$ruc."?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6IlBFUkVaVklMTEFMVEFAR01BSUwuQ09NIn0.cE-L-BK4BuFEIbyFwYT8ACFSobwLeVK7Xc4u9QAuvn0");
            $info = json_decode($data, true);            
            if($data==='[]'){
                $datos = array(0 => 'nada');
                echo json_encode($datos);
                }else{
                $datos = array(
                0 => $info['dni'], 
                1 => $info['nombres']." ".$info['apellidoPaterno']." ".$info['apellidoMaterno'],
                2 => '',
                3 => '',
                4 => $new_id,
                5 => 'nuevo',
                6 => ''
                );
                echo json_encode($datos);
            }
            ob_end_flush();
        }elseif($tip==2){
            ob_start(); 
            $data = file_get_contents("http://dniruc.apisperu.com/api/v1/ruc/".$ruc."?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6IlBFUkVaVklMTEFMVEFAR01BSUwuQ09NIn0.cE-L-BK4BuFEIbyFwYT8ACFSobwLeVK7Xc4u9QAuvn0");
            $info = json_decode($data, true);            
            if($data==='[]'){
                $datos = array(0 => 'nada');
                echo json_encode($datos);
                }else{
                $datos = array(
                0 => $info['ruc'], 
                1 => $info['razonSocial'],
                2 => $info['direccion'],
                3 => $info['nombreComercial'],
                4 => $new_id,
                5 => 'nuevo',
                6 => ''
                );
                echo json_encode($datos);
            }
            ob_end_flush();
        }elseif($tip==3){
            $client = $clientes[0];
            $datos = array(
                0 => $tam==8?$client->CLI_RUC_ESPOSA:($tam==11?$client->CLI_RUC_ESPOSO:0), 
                1 => $client->CLI_NOMBRE,
                2 => $client->CLI_CASA_DIREC,
                3 => $client->CLI_TELEF1,
                4 => $client->CLI_CODCLIE,
                5 => 'existe',
                6 => $client->CLI_FECHA_NAC
                );
                echo json_encode($datos);
        }         
    }

    public function get_persona_id($codcli=false){
        $codcli = $codcli?trim($codcli):trim($this->request->getVar('codcli'));        
        $ClientesModel = new ClientesModel();
        $clientes = $ClientesModel->get_personas($codcli,'','','');        
        if($clientes){
            $client = $clientes[0];
            $datos = array(
                0 => $client->CLI_RUC_ESPOSA, 
                1 => $client->CLI_NOMBRE,
                2 => $client->CLI_CASA_DIREC,
                3 => $client->CLI_TELEF1,
                4 => $client->CLI_CODCLIE,
                5 => 'existe',
                6 => $client->CLI_FECHA_NAC
                );
                echo json_encode($datos);
        }else{
            $datos = array(0 => null);
                echo json_encode($datos);
        }                
    }

    public function unir_personas($cod=false,$cod2=false){
        $cod = $cod?trim($cod):trim($this->request->getVar('cod'));  
        $cod2 = $cod2?trim($cod2):trim($this->request->getVar('cod2'));  
        $this->save_persona($this->request);
        $Allog = new AllogModel();
        $Allog->actualizar_personas($cod,$cod2);
        $Facrt = new FacartModel();
        $Facrt->actualizar_personas($cod,$cod2);
        $Citas = new CitasModel();
        $Citas->actualizar_personas($cod,$cod2);
        $Histo = new HistoriaModel();
        $Histo->actualizar_personas($cod,$cod2);
        $Clien = new ClientesModel();
        $rpta = $Clien->eliminar_persona($cod2,1);
        return $rpta;
    }

    public function dni($dni){        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.factiliza.com/pe/v1/dni/info/$dni",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI1NTgiLCJuYW1lIjoiUEVSVklMTEEiLCJlbWFpbCI6InBlcmV6dmlsbGFsdGFAZ21haWwuY29tIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiY29uc3VsdG9yIn0.tAPf6AgpLBrOBIaiuH8zxVtB4gNG05e52zHWPP0q40o'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response, false);
        if ($data->status == 200) {
            $datos = array(
                0 => $data->data->numero, 
                1 => str_replace(",", "", $data->data->nombre_completo),
                2 => $data->data->direccion_completa,
                3 => '',
                4 => '',
                5 => 'nuevo',
                6 => ''
                );
                return $datos;
        }else{
             $datos = array(0 => 'nada');
             return $datos;
        }
    }
}