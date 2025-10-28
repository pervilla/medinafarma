<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers;

use App\Models\TablasModel;
use App\Models\CampaniaModel;
use App\Models\CitasModel;
use App\Models\ClientesModel;
use App\Models\MedioModel;
use App\Models\Server03Model;
use App\Models\HistoriaModel;
use App\Models\AllogModel;

/**
 * Description of Operaciones
 *
 * @author José Luis
 */
class Consultorio extends BaseController {
    private $tipo_diagnostico = [
        '0' => '',
        '1' => 'DEFINITIVO',
        '2' => 'PRESUNTIVO'
    ];
    private $caso_diagnostico = [
        '0' => '',
        '1' => 'NUEVO',
        '2' => 'REPETIDO'
    ];
    private $alta_diagnostico = [
        '0' => '',
        '1' => 'SI',
        '2' => 'NO'
    ];
    public function index() {
        setlocale(LC_ALL, 'es_ES');
        $CampaniaModel = new CampaniaModel();
        $campania = $CampaniaModel->get_campanias();
        $MedioModel = new MedioModel();
        $medios = $MedioModel->get_medios();        
        $data['campanias'] = $campania;
        $data['medios'] = $medios;
        $data['menu']['p'] = 40;
        $data['menu']['i'] = 41;
        return view('consultorio/index', $data);
    }
    public function pagos(){
        $data['menu']['p'] = 40;
        $data['menu']['i'] = 45;
        return view('consultorio/index_pagos',$data);
    }

    public function pacientes() {
        // Redirigir al nuevo sistema de gestión de citas con servicios
        $CIT_CODCAMP = $this->request->uri->getSegment(3);
        if ($CIT_CODCAMP) {
            return redirect()->to("/citas/index/{$CIT_CODCAMP}");
        } else {
            return redirect()->to('/citas/index');
        }
    }

    /**
     * Vista de citas con servicios y pagos (nueva funcionalidad)
     */
    public function citasServicios() {
        setlocale(LC_ALL, 'es_ES');
        $CIT_CODCAMP = $this->request->uri->getSegment(3);
        $CampaniaModel = new CampaniaModel();
        $campania = $CampaniaModel->get_campanias();
        $MedioModel = new MedioModel();
        $medios = $MedioModel->get_medios();
        $data['campanias_cod'] = $CIT_CODCAMP;
        $data['campanias'] = $campania;
        $data['medios'] = $medios;
        $data['estado'] = '';
        $data['menu']['p'] = 40;
        $data['menu']['i'] = 41;
        return view('consultorio/citas_servicios', $data);
    }
    public function todos() {
        setlocale(LC_ALL, 'es_ES');
        $CIT_CODCAMP = $this->request->uri->getSegment(3);
        $CampaniaModel = new CampaniaModel();
        $campania = $CampaniaModel->get_campanias(null,1);
        $MedioModel = new MedioModel();
        $medios = $MedioModel->get_medios();
        $data['campanias_cod'] = $CIT_CODCAMP;
        $data['campanias'] = $campania;
        $data['medios'] = $medios;
        $data['estado'] = 'todos';
        return view('consultorio/index_pacientes', $data);
    }
    public function confirmados() {
        setlocale(LC_ALL, 'es_ES');
        $CIT_CODCAMP = $this->request->uri->getSegment(3);
        $CIT_CODCAMP = $CIT_CODCAMP>0?$CIT_CODCAMP:null;
        $CampaniaModel = new CampaniaModel();
        $campania = $CampaniaModel->get_campanias($CIT_CODCAMP);
        $data['campanias'] = $campania;

        $MedioModel = new MedioModel();
        $medios = $MedioModel->get_medios();

        $data['campanias_cod'] = $CIT_CODCAMP;
        
        $data['medios'] = $medios;
        $data['menu']['p'] = 40;
        $data['menu']['i'] = 43;
        return view('consultorio/index_consultorio', $data);
    }   
    public function campania(){
        $CampaniaModel = new CampaniaModel();
        $campania = $CampaniaModel->get_campanias(null,'todos');
        $tipcampa = $CampaniaModel->get_tipos_campanias();
        $ClientesModel = new ClientesModel();
        $medicos = $ClientesModel->get_personas('','25','M','');

        $data['campanias'] = $campania;
        $data['tipocampa'] = $tipcampa;
        $data['medicos'] = $medicos;
        $data['menu']['p'] = 40;
        $data['menu']['i'] = 42;
        return view('consultorio/index_campania', $data);
    }
    public function atencion(){
        $CIT_CODCAMP = $this->request->uri->getSegment(3);
        $CIT_CODCIT = $this->request->uri->getSegment(4);
        $CIT_CODCLIE = $this->request->uri->getSegment(5);
        $ClientesModel = new ClientesModel();
        $clientes = $ClientesModel->get_personas($CIT_CODCLIE,'25','','');       
        $HistoriaModel = new HistoriaModel();
        a:
        $nrohist = $HistoriaModel->get_nro_historia($CIT_CODCIT,$CIT_CODCLIE,$CIT_CODCAMP);
        if($nrohist){
            $historia = $HistoriaModel->get_historia($nrohist);
        }else{
            $data = array('HIS_CODCIT' => $CIT_CODCIT,'HIS_CODCLI' => $CIT_CODCLIE,'HIS_CODCAMP' => $CIT_CODCAMP); 
            $HistoriaModel->set_historia($data);
            goto a;
        }
        
        $historias = $HistoriaModel->get_historias($CIT_CODCLIE);
        $data['historias'] = $historias;
        $data['clientes'] = $clientes[0];
        $data['historia'] = $historia[0];
        $data['triaje'] = 'no';
        return view('consultorio/index_atencion',$data);
    }
    /**
     * UTILIZAR PARA TRIAJE
     */
    public function triaje(){
        $CIT_CODCAMP = $this->request->uri->getSegment(3);
        $CIT_CODCIT = $this->request->uri->getSegment(4);
        $CIT_CODCLIE = $this->request->uri->getSegment(5);
        $ClientesModel = new ClientesModel();
        $clientes = $ClientesModel->get_personas($CIT_CODCLIE,'25','','');       
        $HistoriaModel = new HistoriaModel();
        a:
        $nrohist = $HistoriaModel->get_nro_historia($CIT_CODCIT,$CIT_CODCLIE,$CIT_CODCAMP);
        if($nrohist){
            $historia = $HistoriaModel->get_historia($nrohist);
        }else{
            $data = array('HIS_CODCIT' => $CIT_CODCIT,'HIS_CODCLI' => $CIT_CODCLIE,'HIS_CODCAMP' => $CIT_CODCAMP); 
            $HistoriaModel->set_historia($data);
            goto a;
        }
        
        $historias = $HistoriaModel->get_historias($CIT_CODCLIE);
        $data['historias'] = $historias;
        $data['clientes'] = $clientes[0];
        $data['historia'] = $historia[0];
        $data['triaje'] = 'si';
        return view('consultorio/index_atencion',$data);
    }

    /**
     * VER HISTORIA
     */
    public function historia(){
        $CIT_CODCAMP = $this->request->uri->getSegment(3);
        $CIT_CODCIT = $this->request->uri->getSegment(4);
        $CIT_CODCLIE = $this->request->uri->getSegment(5);
        $ClientesModel = new ClientesModel();
        $clientes = $ClientesModel->get_personas($CIT_CODCLIE,'25','','');       
        $HistoriaModel = new HistoriaModel();
        $nrohist = $HistoriaModel->get_nro_historia($CIT_CODCIT,$CIT_CODCLIE,$CIT_CODCAMP);
        $historia = $HistoriaModel->get_historia($nrohist);
        $diagnost = $HistoriaModel->get_historia_diag($nrohist);        
        $receta = $HistoriaModel->get_historia_receta($nrohist);
        $data['clientes'] = $clientes[0];
        $data['historia'] = $historia[0];        
        $data['diagnostico'] = $diagnost;
        $data['receta']= $receta;
        $data['tipo_diagnostico']=$this->tipo_diagnostico;
        $data['caso_diagnostico']=$this->caso_diagnostico;
        $data['alta_diagnostico']=$this->alta_diagnostico;
        //var_export($data);
        return view('consultorio/index_historia',$data);
    }

    /**
     * HISTORIA CLÍNICA FORMAL
     */
    public function historiaFormal(){
        $CIT_CODCAMP = $this->request->uri->getSegment(3);
        $CIT_CODCIT = $this->request->uri->getSegment(4);
        $CIT_CODCLIE = $this->request->uri->getSegment(5);
        $ClientesModel = new ClientesModel();
        $clientes = $ClientesModel->get_personas($CIT_CODCLIE,'25','','');       
        $HistoriaModel = new HistoriaModel();
        $nrohist = $HistoriaModel->get_nro_historia($CIT_CODCIT,$CIT_CODCLIE,$CIT_CODCAMP);
        $historia = $HistoriaModel->get_historia($nrohist);
        $diagnost = $HistoriaModel->get_historia_diag($nrohist);        
        $receta = $HistoriaModel->get_historia_receta($nrohist);
        $data['clientes'] = $clientes[0];
        $data['historia'] = $historia[0];        
        $data['diagnostico'] = $diagnost;
        $data['receta']= $receta;
        $data['tipo_diagnostico']=$this->tipo_diagnostico;
        $data['caso_diagnostico']=$this->caso_diagnostico;
        $data['alta_diagnostico']=$this->alta_diagnostico;
        return view('consultorio/historia_clinica_formal',$data);
    }
     /**
     * VER HISTORIA
     */
    public function historial(){
        $data['menu']['p'] = 40;
        $data['menu']['i'] = 44;
        return view('consultorio/index_historial',$data);
    }   
    /**
     * VER RECETA
     */
    public function receta(){
        $CIT_CODCAMP = $this->request->uri->getSegment(3);
        $CIT_CODCIT = $this->request->uri->getSegment(4);
        $CIT_CODCLIE = $this->request->uri->getSegment(5);
        $ClientesModel = new ClientesModel();
        $clientes = $ClientesModel->get_personas($CIT_CODCLIE,'25','','');       
        $HistoriaModel = new HistoriaModel();
        $nrohist = $HistoriaModel->get_nro_historia($CIT_CODCIT,$CIT_CODCLIE,$CIT_CODCAMP);
        $historia = $HistoriaModel->get_historia($nrohist);
        $diagnost = $HistoriaModel->get_historia_diag($nrohist);        
        $receta = $HistoriaModel->get_historia_receta($nrohist);
        $data['clientes'] = $clientes[0];
        $data['historia'] = $historia[0];        
        $data['diagnostico'] = $diagnost;
        $data['receta']= $receta;
        $data['tipo_diagnostico']=$this->tipo_diagnostico;
        $data['caso_diagnostico']=$this->caso_diagnostico;
        $data['alta_diagnostico']=$this->alta_diagnostico;
        return view('consultorio/index_receta',$data);
    }
    public function actualiza_historia(){
        $CODHIS = $this->request->getVar('CODHIS');
        $CODCAM = $this->request->getVar('CODCAM');
        $CODCIT = $this->request->getVar('CODCIT');
        if($this->request->getVar('TRIAJE')=='si'){
            $CitasModel = new CitasModel();
            $orden = $CitasModel->get_orden_atencion($CODCAM,$CODCIT);
            if($orden->ORD==0){$CitasModel->set_orden_atencion($CODCIT,$orden->CIT_ORD_ATENCION);}
        }
        $data = array();
        if(!empty($this->request->getVar('PREART_MM'))){$data['HIS_PREART_MM'] = $this->request->getVar('PREART_MM');}
        if(!empty($this->request->getVar('PREART_HG'))){$data['HIS_PREART_HG'] = $this->request->getVar('PREART_HG');}
        if(!empty($this->request->getVar('TEMPER'))){$data['HIS_TEMPERATURA'] = $this->request->getVar('TEMPER');}
        if(!empty($this->request->getVar('PESO'))){$data['HIS_PESO'] = $this->request->getVar('PESO');}
        if(!empty($this->request->getVar('TALLA'))){$data['HIS_TALLA'] = $this->request->getVar('TALLA');}
        if(!empty($this->request->getVar('SATUR'))){$data['HIS_SATURACION'] = $this->request->getVar('SATUR');}
        if(!empty($this->request->getVar('FRE_CARD'))){$data['HIS_FRE_CARD'] = $this->request->getVar('FRE_CARD');}
        if(!empty($this->request->getVar('FRE_RESP'))){$data['HIS_FRE_RESP'] = $this->request->getVar('FRE_RESP');}
        if(!empty($this->request->getVar('EXA_CLI'))){$data['HIS_EXA_CLI'] = $this->request->getVar('EXA_CLI');}
        if(!empty($this->request->getVar('PLAN_TRAB'))){$data['HIS_PLAN_TRAB'] = $this->request->getVar('PLAN_TRAB');}
        if(!empty($this->request->getVar('INDIC'))){$data['HIS_INDICACIONES'] = $this->request->getVar('INDIC');}
        if(!empty($this->request->getVar('ESTADO'))){$data['HIS_ESTADO'] = $this->request->getVar('ESTADO')=='on'?1:0;}
        $HistoriaModel = new HistoriaModel();
       $upd = $HistoriaModel->upd_historia($CODHIS,$data); 

    }
    public function get_cie_nombre() {
        $cie = $this->request->getVar('cie');
        $dsc = $this->request->getVar('term');
        $HistoriaModel = new HistoriaModel();
        $desc = $HistoriaModel->get_cie_descripcion($cie,$dsc);
        //var_export($operaciones);
        return $this->response->setJSON($desc);
    }
    public function delete_cie(){
        $his = $this->request->getVar('his');
        $cie = $this->request->getVar('cie');
        $HistoriaModel = new HistoriaModel();
        $desc = $HistoriaModel->delete_cie($his,$cie);
        return$this->response->setJSON($desc);
    }
    /********************
     *** DIAGNOSTICO ****
     ********************/
    public function guarda_diagnostico(){
        $data = array(
            'HISD_CODHIS' => $this->request->getVar('his'),
            'HISD_CIE_CODIGO' => $this->request->getVar('cie'),
            'HISD_CIE_DESCRIPCION' => $this->request->getVar('dsc')
        ); 
        $HistoriaModel = new HistoriaModel();
        $desc = $HistoriaModel->set_historia_diag($data);
        return $desc;
    }
    public function actualiza_diagnostico(){
        $his = $this->request->getVar('his');
        $a = $this->request->getVar('a');
        $b = $this->request->getVar('b');
        $c = $this->request->getVar('c');
        $data = array();
        if($this->request->getVar('c')==1){$data['HISD_TIPO'] = $a;}
        if($this->request->getVar('c')==2){$data['HISD_CASO'] = $a;}
        if($this->request->getVar('c')==3){$data['HISD_ALTA'] = $a;}

        $HistoriaModel = new HistoriaModel();
        $desc = $HistoriaModel->upd_historia_diag($his,$b,$data);
    }
    public function get_diagnostico(){
        $his = $this->request->getVar('his');
        $HistoriaModel = new HistoriaModel();
        $desc = $HistoriaModel->get_historia_diag($his);

        return json_encode($desc);
    }

    /********************
     *** RECETA *********
     ********************/
    public function guarda_receta(){
        $data = array(
            'HISR_CODHIS' => $this->request->getVar('his'),
            'HISR_CODART' => $this->request->getVar('car'),
            'HISR_NOMART' => $this->request->getVar('mar'),
            'HISR_CANT' => $this->request->getVar('cnt'),
            'HISR_DIAS' => $this->request->getVar('dia'),
            'HISR_INDICACIONES' => $this->request->getVar('ind')
        ); 
        if($this->request->getVar('upd')==0){
            $HistoriaModel = new HistoriaModel();
            $desc = $HistoriaModel->set_historia_receta($data);
        }elseif($this->request->getVar('upd')==1){
            $HistoriaModel = new HistoriaModel();
            $desc = $HistoriaModel->upd_historia_receta($data);
        }
        
        return $desc;
    }

    public function get_receta(){
        $his = $this->request->getVar('his');
        $HistoriaModel = new HistoriaModel();
        $desc = $HistoriaModel->get_historia_receta($his);
        return$this->response->setJSON($desc);
    }
    public function delete_receta(){
        $his = $this->request->getVar('his');
        $car = $this->request->getVar('car');
        $HistoriaModel = new HistoriaModel();
        $desc = $HistoriaModel->delete_historia_receta($his,$car);
        return$this->response->setJSON($desc);
    }
    

    public function get_citas() {
        $camp = $this->request->getVar('camp');
        $esta = $this->request->getVar('esta');
        $CitasModel = new CitasModel();
        $citas = $CitasModel->get_citas($camp,$esta);
        //var_export($operaciones);
        return $this->response->setJSON($citas);
    }
    public function get_orden(){
        $camp = $this->request->getVar('camp');    
        $CitasModel = new CitasModel();
        $orden = $CitasModel->get_orden($camp);
        $orden = $orden?$orden:1;    
        return $this->response->setJSON($orden);
    }
    public function get_monto(){
        $serie = $this->request->getVar('serie');    
        $numer = $this->request->getVar('numer');  
        $Server03Model = new Server03Model();
        $monto = $Server03Model->get_monto($serie,$numer);        
        echo 'S/. '.$monto;
    }
    public function get_personas2() {
        $camp = $this->request->getVar('term');
        $ClientesModel = new ClientesModel();
        $citas = $ClientesModel->get_personas2($camp);
        //var_export($operaciones);
        return $this->response->setJSON($citas);
    }    
    public function get_pacientes(){        
        $parametro = $this->request->getVar('param');
        $CitasModel = new CitasModel();
        $pacientes = $CitasModel->get_personas_citas($parametro);   
        return $this->response->setJSON($pacientes);
    }
    public function get_atenciones(){        
        $codcliente = $this->request->getVar('codcli');
        $CitasModel = new CitasModel();
        $pacientes = $CitasModel->get_atenciones($codcliente);   
        return $this->response->setJSON($pacientes);
    }
    public function get_pagos(){
        $fecha = $this->request->getVar('fecha');
        $porciones = explode("-", $fecha);
        $fecha01 = date('d/m/Y', strtotime($porciones[0])); 
        $fecha02 = date('d/m/Y', strtotime($porciones[1]));
        $local = $this->request->getVar('local');
        $AllogModel = new AllogModel();
        $pagos = $AllogModel->get_pagos_consulta($local,$fecha01,$fecha02);   
        return $this->response->setJSON($pagos);
    }

    public function set_campania(){
        $def = '31/12/1969';
        $cal = $this->request->getVar('calendario');
        $data = array(
            'CAM_CODMED' => $this->request->getVar('cliente'),
            'CAM_FEC_INI' => $cal==1?date('d/m/Y', strtotime($this->request->getVar('fechaini'))):$def,
            'CAM_FEC_FIN' => $cal==1?date('d/m/Y', strtotime($this->request->getVar('fechafin'))):$def,
            'CAM_HOR_INI' => date('d/m/Y h:i:s A', strtotime($this->request->getVar('fechaini'))),
            'CAM_HOR_FIN' => date('d/m/Y h:i:s A', strtotime($this->request->getVar('fechafin'))),
            'CAM_DESCRIP' => $this->request->getVar('descrip'),
            'CAM_ID'=>$this->request->getVar('idcampania')
        ); 
        $Caja = new CampaniaModel();
        $data = $Caja->crear_campania($data);
        return $data;
    }
    public function save_cita(){
        $data = array(
            'CIT_CODCLIE' => $this->request->getVar('cli'),
            'CIT_CODCAMP' => $this->request->getVar('cam'),
            'CIT_ORD' => $this->request->getVar('ord'),
            'CIT_PAGO' => $this->request->getVar('pag')=='on'?1:0,
            'CIT_SERIE' => $this->request->getVar('ser'),
            'CIT_NUMERO' => $this->request->getVar('fac'),
            'CIT_CODMEDIO' => $this->request->getVar('med')
        ); 
        $Cita = new CitasModel();
        $Cita->set_citas($data);
        return true;
    }
    public function editar_cita(){
        $data = array(
            'CIT_PAGO' => 1,
            'CIT_SERIE' => $this->request->getVar('ser'),
            'CIT_NUMERO' => $this->request->getVar('nro')
        ); 
        $Cita = new CitasModel();
        $Cita->upd_cita($this->request->getVar('cit'),$data);
        return true;
    }
    public function confirma_cita(){
        $his = $this->request->getVar('cit');
        $est = $this->request->getVar('est');
        $data = array();
        $data['CIT_ESTADO'] = $est;
        $Cita = new CitasModel();
        $desc = $Cita->confirma_cita($his,$data);
        return$this->response->setJSON($desc);
    }
    public function eliminar_cita(){
        $his = $this->request->getVar('cit');
        $est = $this->request->getVar('est');
        $Cita = new CitasModel();
        $desc = $Cita->elimina_cita($his,'');
        return$this->response->setJSON($desc);
    }
}