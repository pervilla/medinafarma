<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers;

use App\Models\AllogModel;
use App\Models\CajaModel;
use App\Models\CajaMovimientosModel;
use App\Models\VemaestModel;
use CodeIgniter\I18n\Time;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

/**
 * Description of Caja
 *
 * @author José Luis
 */
class Caja extends BaseController {

    private $motivos = [
        '1' => 'PAGO COMPRA',
        '2' => 'DEVOLUCIÓN PRODUCTO',
        '3' => 'CAMBIO PRODUCTO',
        '4' => 'SOBRANTE CAJA',
        '5' => 'FALTANTE CAJA',
        '6' => 'ADELANTO',
        '7' => 'CREDITO',
        '8' => 'PAGO TARJETA',
        '9' => 'PAGO QR',
        '10' => 'DEPOSITO',
        '11' => 'GASTOS BOTICA',
        '12' => 'DELIVERY',
        '13' => 'PAGO A MEDICO',
        '14' => 'FALTO DIGITAR COMPRA'
    ];

    public function index() {  
        $session = session();
        if($session->get('caja')){
            $caja = $session->get('caja');
            switch($caja){
                case '1': $data['color']='success'; break;
                case '2': $data['color']='danger'; break;
                case '3': $data['color']='info'; break;
            }
        }else{
            $caja = 1;
            $session->set('caja', $caja);
            $data['color']='success';
        }  
        $isadmin=$session->get('user_id')=='ADMIN'?true:false;
        $data['caja']=$caja; 
        $smes=$session->get('mes_caja')?$session->get('mes_caja'):date('m');
        $session->set('mes_caja', $smes);
        $sano=$session->get('anio_caja')?$session->get('anio_caja'):date('Y');
        $session->set('anio_caja', $sano);
        $Allog = new AllogModel();
        $Caja = new CajaModel();
        $movimientos = $Caja->get_ventas_dia_det('', $smes, $sano,'', $caja,$isadmin);
        $dia = array('01'=>0);
        foreach($movimientos as $mov){
            $dia[date("d", strtotime($mov->ALL_FECHA_DIA))] = (array_key_exists(date("d", strtotime($mov->ALL_FECHA_DIA)),$dia)? $dia[date("d", strtotime($mov->ALL_FECHA_DIA))]:0) + $mov->TOT_VENTAS; //PARA GRAFICO
        }
        foreach ($dia as $key => $value) {
            $data['dias'][] = $key;
            $data['centro'][] = $value;
            $data['pmeza'][] = 0;
        }
        $data['ventas'] = $movimientos;
        $data['menu']['p'] = 10;
        $data['menu']['i'] = 11;
        return view('caja/index', $data);
    }
    public function reporte_cajas(){
        $session = session();
        if($session->get('caja')){
            $data['ncaja'] = $session->get('caja');
            switch($data['ncaja']){
                case '1': $data['color']='success'; break;
                case '2': $data['color']='danger'; break;
                case '3': $data['color']='info'; break;
            }
        }else{
            $data['ncaja'] = 1;
            $data['color']='success';
            $session->set('caja', $data['ncaja']);
        }   
        $isadmin=$session->get('user_id')=='ADMIN'?true:false;
        $smes=$session->get('mes_caja')?$session->get('mes_caja'):date('m');
        $session->set('mes_caja', $smes);
        $sano=$session->get('anio_caja')?$session->get('anio_caja'):date('Y');
        $session->set('anio_caja', $sano);
        $Allog = new AllogModel();
        $Caja = new CajaModel();
        $Emp = new VemaestModel();
        $data['empleados'] = $emp = $Emp->get_empleado('');
        $movimientos = $Caja->get_ventas_dia_det('', $smes, $sano, '',$data['ncaja'],$isadmin);
        //var_export($movimientos);
        $dia = array();
        foreach($movimientos as $mov){
            $d = date("d", strtotime($mov->ALL_FECHA_DIA));
            $dia[$d]['DIA'] = $d;
            foreach($emp as $e){
                $dia[$d][trim($e->VEM_NOMBRE)] = trim($e->VEM_NOMBRE)==trim($mov->VEM_NOMBRE)?$mov->TOT_EFECTIVO+$mov->TOT_MOVIM:($dia[$d][trim($e->VEM_NOMBRE)]?$dia[$d][trim($e->VEM_NOMBRE)]:0); 
            }
            $dia[$d]['TOT_DIA'] = $dia[$d]['TOT_DIA']+$mov->TOT_EFECTIVO+$mov->TOT_MOVIM;
        }
        $data['movimientos']=$dia;       

        return view('caja/reporte_cajas',$data);
    }

    public function diario() {
        $session = session();        
        if($session->get('caja')){
            $data['ncaja'] = $session->get('caja');
            switch($data['ncaja']){
                case '1': $data['color']='success'; break;
                case '2': $data['color']='danger'; break;
                case '3': $data['color']='info'; break;
            }
        }else{
            $data['ncaja'] = 1;
            $data['color']='success';
            $session->set('caja', $data['ncaja']);
        }
            $smes=$session->get('mes_caja')?$session->get('mes_caja'):date('m');
            $session->set('mes_caja', $smes); 
            $sano=$session->get('anio_caja')?$session->get('anio_caja'):date('Y');
            $session->set('anio_caja', $sano);

        $Caja = new CajaModel();
        $data['ventas'] = $Caja->get_ventas_dia('', $smes, $sano,'',$data['ncaja']);
        $Emp = new VemaestModel();
        $data['empleados'] = $Emp->get_empleado('');
        $data['motivo_gasto'] = $this->motivos;
        $data['menu']['p'] = 10;
        $data['menu']['i'] = 12;
        return view('caja/index_diario', $data);
    }
public function dia(){
    $session = session();        
        if($session->get('caja')){
            $caja = $session->get('caja');
            switch($caja){
                case '1': $data['color']='success'; break;
                case '2': $data['color']='danger'; break;
                case '3': $data['color']='info'; break;
            }
        }else{
            $caja = 0;
            $data['color']='';
            $session->set('caja', $caja);
        }
            $smes=$session->get('mes_caja')?$session->get('mes_caja'):date('m');
            $session->set('mes_caja', $smes); 
            $sano=$session->get('anio_caja')?$session->get('anio_caja'):date('Y');
            $session->set('anio_caja', $sano);            

        $Emp = new VemaestModel();
        $data['empleados'] = $Emp->get_empleado('');
        $data['motivo_gasto'] = $this->motivos;
        $data['menu']['p'] = 10;
        $data['menu']['i'] = 14;
    return view('caja/index_caja',$data);
}

public function get_comprobantes(){
    $session = session();
    $caja = $session->get('caja');
    $fecha = date('d/m/Y');
    
    $Allog = new AllogModel();
    $comprobantes = $Allog->get_comprobantes_dia($fecha, $caja);
    
    return $this->response->setJSON($comprobantes);
}
public function get_cajas_dia(){
    $ani = $this->request->getVar('anio');
    $mes = $this->request->getVar('mes');
    $dia = $this->request->getVar('dia');
    $est = $this->request->getVar('est');
    $loc = $this->request->getVar('loc');
    $caj = $this->request->getVar('caj');
    $Caja = new CajaModel();
    if($loc==0){$cajas = array();}else{$cajas = $Caja->get_ventas_dia($dia,$mes,$ani,$caj,$loc);};
    return $this->response->setJSON($cajas);
}
    public function diarios(){
        $data = array();
        return view('caja/index_diarios', $data);
    }
    public function abrircaja() {
        $session = session();        
        $caja = $session->get('caja');
        $request = service('request');
        $fecha = date('d/m/Y', strtotime($request->getPost('CAJ_FECHA2')));
        
        $Caja = new CajaModel();
        
        // Verificar si ya existe una caja abierta en esta fecha
        if($Caja->verificar_caja_abierta($fecha, $caja)) {
            $session->setFlashdata('error', 'Ya existe una caja abierta para esta fecha');
            return $this->response->redirect(site_url('caja/diario'));
        }
        
        $data = array(
            'CAJ_CODVEN' => $request->getPost('VEM_CODVEN'),
            'CAJ_FECHA' => $fecha,
            'CAJ_ESTADO' => 1
        );
        
        $Caja->crear_caja($data,$caja);
        return $this->response->redirect(site_url('caja/diario'));
    }
    public function abrircaja2() {
        $lcal = $this->request->getVar('local');
        $resp = $this->request->getVar('resp');
        $fecha = date('d/m/Y');
        
        $Caja = new CajaModel();
        
        // Verificar si ya existe una caja abierta en esta fecha
        if($Caja->verificar_caja_abierta($fecha, $lcal)) {
            return $this->response->setJSON(['error' => 'Ya existe una caja abierta para esta fecha']);
        }
        
        $data = array(
            'CAJ_CODVEN' => $resp,
            'CAJ_FECHA' => $fecha,
            'CAJ_ESTADO' => 1
        );
        
        $desc= $Caja->crear_caja($data,$lcal);
        return $this->response->setJSON($desc);
    }

    public function cerracaja() {
        $session = session();        
        $caja = $session->get('caja');
        $request2 = service('request');        
        $caja = $request2->getPost('LOCAL')?$request2->getPost('LOCAL'):$caja;
        $nrocaja = $request2->getPost('CAJ_NRO');
        $numser = $request2->getPost('CAJ_NUMSER');
        $numfact = $request2->getPost('CAJ_NUMFAC');
        $efectiv = $request2->getPost('CAJ_EFECTIVO');
        $fecha = $request2->getPost('CAJ_FECHA');
        $hoy = Time::parse($fecha, 'America/Lima');
        //actualizar en allog
        $Caja = new CajaModel();
        $cajaAnt = $Caja->get_caja('', '', '', 0, $nrocaja,'',$caja);
//var_export($cajaAnt); die();
        
        $ant = Time::parse($cajaAnt[0]->CAJ_FECHA, 'America/Lima');
        $diff = $hoy->difference($ant);
        //Obtener all_numoper 
        $nrop = $diff->getDays() == 0 ? ($cajaAnt[0]->CAJ_NUMOPER + 1) : 2;
        $Allog = new AllogModel();
        $numop2 = $Allog->get_nro_oper($hoy->toLocalizedString('d-M-Y'), $numfact,$caja);
        $Allog->set_cierre_caja($hoy->toLocalizedString('d-M-Y'), $numop2->ALL_NUMOPER, $nrop, $nrocaja,$caja);
        $Caja->cerrar_caja($numop2->ALL_NUMOPER, $numser, $numfact, $efectiv, $nrocaja,$caja);
        return $this->response->redirect(site_url('caja/diario'));
        
    }
    public function cerrar_caja2(){
        $local = $this->request->getVar('local');
        $cajan = $this->request->getVar('CAJ_NRO'); /* ALL_CAJA_NRO */
        $numse = $this->request->getVar('CAJ_NUMSER');
        $numfa = $this->request->getVar('CAJ_NUMFAC'); /* ALL_NUMFAC */
        $efect = $this->request->getVar('CAJ_EFECTIVO');
        $fecha = $this->request->getVar('CAJ_FECHA');
        //$hoy = Time::parse($fecha, 'America/Lima');
        $today = date( 'd-m-Y', strtotime( 'today' ) );
        
        /** nro de operacion de cierre */
        $Allog = new AllogModel();
        $numopAllog = $Allog->get_nro_oper($today, $numfa,$local);

        /** datos de posible caja anterior */
        $Caja = new CajaModel();
        $cajaAnt = $Caja->get_caja_anterior($today,$numopAllog->ALL_NUMOPER,$local);
        $numopAnt=(count((array)$cajaAnt)>0)?$cajaAnt->CAJ_NUMOPER+1:2;
        try {
            $Allog->set_cierre_caja($today, $numopAllog->ALL_NUMOPER, $numopAnt, $cajan,$local);
            $Caja->cerrar_caja($numopAllog->ALL_NUMOPER, $numse, $numfa, $efect, $cajan,$local);
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
        return true;
    }

    public function editarcaja(){
        $session = session();        
        $caja = $session->get('caja');
        $uri = $this->request->getUri();
        if($session->get('user_id')=='ADMIN'){
            $nrocaja = $uri->getSegment(3);
            $Caja = new CajaModel();
            $Caja->editar_caja($nrocaja,$caja);
        }else{            
            $session->setFlashdata('item', 'No tiene permisos para está operación');
        }  
        return $this->response->redirect(site_url('caja/diario'));
    }
    public function bloqueacaja(){
        $session = session();      
        $uri = $this->request->getUri();  
        $caja = $session->get('caja');
        if($session->get('user_id')=='ADMIN'){
            $nrocaja = $uri->getSegment(3);
            $Caja = new CajaModel();
            $Caja->bloquea_caja($nrocaja,$caja);
        }else{            
            $session->setFlashdata('item', 'No tiene permisos para está operación');
        }         
        return $this->response->redirect(site_url('caja/diario'));
    }
    public function imprimircaja(){
        $session = session();      
        $uri = $this->request->getUri();
        $local = $uri->getSegment(4);
        $nrocaja = $uri->getSegment(3);
        $isadmin = $session->get('user_id')=='ADMIN'?true:false;
        $Caja = new CajaModel();        
        $sano=$session->get('anio_caja')?$session->get('anio_caja'):date('Y');
        $session->set('anio_caja', $sano);
        $fecha = date('d/m/Y');
        $locales = array(1=>"CENTRO",2=>"JUANJUICILLO",3=>"PEÑAMEZA");
        $revisado = '';
        $cajac = $Caja->get_caja('',  '', $sano, 0, '',$nrocaja,$local);
        if($session->get('user_id')=='ADMIN'||date('d/m/Y', strtotime($cajac[0]->CAJ_FECHA))==$fecha){
            $cajar = $Caja->get_ventas_dia_det(date('d', strtotime($cajac[0]->CAJ_FECHA)),date('m', strtotime($cajac[0]->CAJ_FECHA)),date('Y', strtotime($cajac[0]->CAJ_FECHA)),$nrocaja,$local,$isadmin);
            $CajaMov = new CajaMovimientosModel();
            $movimientos = $CajaMov->get_movimientos($cajar[0]->ALL_CAJA_NRO?$cajar[0]->ALL_CAJA_NRO:0,$local);
            if($local==1 || $session->get('user_id')=='ADMIN'){
                $connector = new WindowsPrintConnector("smb://asesor:159357@ventas2/6-EPSON TM-T20III Receipt");  
                $revisado = $session->get('user_id')=='ADMIN'?'**REVISADO**':'';              
            }elseif($local==2){                
                $connector = new WindowsPrintConnector("smb://asesor:159357@server02/6-EPSON TM-T20II Receipt");
            }elseif($local==3){
                $connector = new WindowsPrintConnector("smb://asesor:159357@medinaimpresora/6-EPSON TM-T20II Receipt5");
            }
            
            $printer = new Printer($connector);
            $printer->setFont();  
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            //$logo = EscposImage::load(FCPATH.'dist\img\medinafarma-black.jpg', false);
            //$printer -> graphics($logo);        
            $printer -> feed();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer -> text("█████▓▒░░ REPORTE DE CIERRE DE CAJA ░░▒▓█████ \n");
            $printer -> setTextSize(4, 4);
            $printer -> text($locales[$local]."\n");
            $printer -> setTextSize(1,1);
            $printer -> setJustification(Printer::JUSTIFY_LEFT);
            $printer -> text("╔═══════╦════════════╗  ╔══════╦═══════╗\n");
            $printer -> text("║ FECHA ║ ".date('d-m-Y', strtotime($cajar[0]->ALL_FECHA_DIA))." ║  ║ CAJA ║ ".str_pad($nrocaja, 5, " ", STR_PAD_LEFT)." ║\n");
            $printer -> text("╚═══════╩════════════╝  ╚══════╩═══════╝\n");        
            $printer -> text("╔═════════╦══════════╗  ╔══════╦═══════════════╗\n");
            $printer -> text("║ CLIENTE ║ ".str_pad($cajac[0]->CAJ_NUMOPER, 8, " ", STR_PAD_LEFT)." ║  ║ DOCU ║ ".str_pad($cajac[0]->CAJ_NUMFAC, 13, " ", STR_PAD_LEFT)." ║\n");  
            $printer -> text("╚═════════╩══════════╝  ╚══════╩═══════════════╝\n");
            $printer -> text("╔═══════╦══════════════╗ \n");
            $printer -> text("║ MONTO ║ S/. " . str_pad(number_format((float)round( $cajac[0]->CAJ_EFECTIVO ,2, PHP_ROUND_HALF_DOWN),2,'.',','), 8, " ", STR_PAD_LEFT)." ║ $revisado\n");
            $printer -> text("╚═══════╩══════════════╝ \n");
            $printer -> text("┌─────────────┐ \n");
            $printer -> text("│ MOVIMIENTOS │ \n");
            $printer -> text("├─────────────┴──────────────────┬─────────────┐\n");
            $printer -> text("│ CONCEPTO                       │    MONTO    │\n");
            $printer -> text("├────────────────────────────────┼─────────────┤\n");

            // Agrupar movimientos por tipo
            $agrupados = [];
            foreach ($movimientos as $val) {
                $tipo = $val->CMV_TIPO;
                if(!isset($agrupados[$tipo])){
                    $agrupados[$tipo] = [
                        'nombre' => $this->motivos[$tipo],
                        'items' => [],
                        'total' => 0
                    ];
                }
                $agrupados[$tipo]['items'][] = $val;
                $agrupados[$tipo]['total'] += floatval($val->CMV_MONTO);
            }

            $find = array('á','é','í','ó','ú','â','ê','î','ô','û','ã','õ','ç','ñ','Á','É','Í','Ó','Ú','Â','Ê','Î','Ô','Û','Ã','Õ','Ç','Ñ');
            $repl = array('a','e','i','o','u','a','e','i','o','u','a','o','c','n','A','E','I','O','U','A','E','I','O','U','A','O','C','N');

            foreach ($agrupados as $grupo) {
                // Imprimir cada item del grupo
                foreach($grupo['items'] as $val){
                    $detalle = "";
                    if(($val->CMV_TIPO == 6 || $val->CMV_TIPO == 7) && !empty($val->VEM_NOMBRE)){
                        $detalle .= $val->VEM_NOMBRE." - ";
                    }
                    $detalle .= $val->CMV_DESCRIPCION;
                    $detalle = str_replace($find, $repl, $detalle);
                    
                    $partes = str_split(trim($detalle), 30);
                    $a=0;
                    foreach($partes as $parte){
                        $concepto = str_pad(substr($parte, 0, 30),30," ");
                        $monto = $a==0?"S/." . str_pad(number_format($val->CMV_MONTO,2), 8, " ", STR_PAD_LEFT):'           '; 
                        $printer -> text("│ ".$concepto." │ ".$monto." │\n");
                        $a++;
                    }
                }
                
                // Imprimir subtotal del grupo
                $printer -> text("├────────────────────────────────┼─────────────┤\n");
                $printer -> text("│ SUBTOTAL ".str_pad($grupo['nombre'], 22)."│ S/." . str_pad(number_format($grupo['total'],2), 8, " ", STR_PAD_LEFT)." │\n");
                $printer -> text("├────────────────────────────────┼─────────────┤\n");
            }   

            $printer -> text("└────────────────────────────────┴─────────────┘\n");
            $printer -> text("                                               \n");
            $printer -> text("                                 ┌─────────────┐\n");
            $printer -> text("             MONTO TOTAL DE CAJA │ S/." . str_pad(number_format((float)round(($cajar[0]->TOT_MOVIM+$cajar[0]->TOT_EFECTIVO),2, PHP_ROUND_HALF_DOWN),2,'.',''), 8, " ", STR_PAD_LEFT)." │\n");
            $printer -> text("                                 ├─────────────┤\n");
            $printer -> text("               MONTO DEL SISTEMA │ S/." . str_pad(number_format((float)round( $cajar[0]->TOT_VENTAS ,2, PHP_ROUND_HALF_DOWN),2,'.',''), 8, " ", STR_PAD_LEFT)." │\n");
            $printer -> text("                                 ├─────────────┤\n");
            $printer -> text("                      DIFERENCIA │ S/." . str_pad(number_format((float)round(($cajar[0]->TOT_MOVIM+$cajar[0]->TOT_EFECTIVO)-$cajar[0]->TOT_VENTAS,2, PHP_ROUND_HALF_DOWN),2,'.',''), 8, " ", STR_PAD_LEFT)." │\n");
            $printer -> text("                                 └─────────────┘\n");
            $printer -> text("┌────────────────────────────┬─────────────────┐\n");
            $printer -> text("│ CAJERO:                    │ FIRMA:          │\n");
            $printer -> text("│ ".str_pad(trim($cajar[0]->VEM_NOMBRE), 26)." │                 │\n");
            $printer -> text("│                            │                 │\n");
            $printer -> text("└────────────────────────────┴─────────────────┘\n");
            $printer -> text("OBSERVACIONES:\n");
            /* Footer */
            $printer -> feed(2);
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            /* Barcodes - see barcode.php for more detail */
            $printer->setBarcodeHeight(80);
            $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
            $printer->barcode($nrocaja);

            
            $printer -> text("Fecha de Impresión:".date('d-m-Y h:i:s a', time())."\n");

            /* Cut the receipt and open the cash drawer */
            $printer -> cut();
            $printer -> pulse();
            $printer -> close();
        }else{            
            $session->setFlashdata('item', 'No tiene permisos para está operación');
        } 
        return $this->response->redirect(site_url('caja/diario'));    
        
    }
        /**
     * VER HISTORIA
     */
    public function ver_caja($nrocaja,$local,$print=0){
        $session = session();      
        $uri = $this->request->getUri();
        $local = $uri->getSegment(4);
        $data['local'] =  $local;
        $nrocaja = $uri->getSegment(3);
        $data['print'] = $uri->getSegment(5);        
        $data['path'] = (site_url('caja/ver_caja/'.$nrocaja.'/'.$local.'/1'));
        $data['nrocaja'] =  $nrocaja;
        $data['motivos'] =  $this->motivos;
        $isadmin = $session->get('user_id')=='ADMIN'?true:false;
        $Caja = new CajaModel();
        $sano=$session->get('anio_caja')?$session->get('anio_caja'):date('Y');
        $session->set('anio_caja', $sano);
        $fecha = date('d/m/Y');
        $data['locales'] = array(1=>"CENTRO",2=>"JUANJUICILLO",3=>"PEÑAMEZA");
        $revisado = '';
        $cajac = $Caja->get_caja('',  '', $sano, 0, '',$nrocaja,$local);
        
        $data['caja'] = $cajac;
        if($session->get('user_id')=='ADMIN'||date('d/m/Y', strtotime($cajac[0]->CAJ_FECHA))==$fecha){
            $cajar = $Caja->get_ventas_dia_det(date('d', strtotime($cajac[0]->CAJ_FECHA)),date('m', strtotime($cajac[0]->CAJ_FECHA)),date('Y', strtotime($cajac[0]->CAJ_FECHA)),$nrocaja,$local,$isadmin);
            $data['cajar']=$cajar;
            $CajaMov = new CajaMovimientosModel();
            $data['movimientos'] = $CajaMov->get_movimientos($cajar[0]->ALL_CAJA_NRO?$cajar[0]->ALL_CAJA_NRO:0,$local);    
            //var_export($data); die();
            return view('caja/ver_caja',$data);        
        }else{  
            echo "No tiene permisos";          
            $session->setFlashdata('item', 'No tiene permisos para está operación');
        } 
        
    }
    public function agregar_movimiento() {
        $session = session();        
        $caja = $session->get('caja');
        $request = service('request');
        $CajaMov = new CajaMovimientosModel();
        if($request->getPost('cmv_caja')>0){
          $data = array(
            'CMV_CAJA' => $request->getPost('cmv_caja'),
            'CMV_TIPO' => $request->getPost('cmv_tipo'),
            'CMV_CODVEN' => $request->getPost('cmv_codven'),
            'CMV_DESCRIPCION' => strtoupper($request->getPost('cmv_descri')),
            'CMV_MONTO' => $request->getPost('cvm_monto')
            );            
            $CajaMov->crear_movimiento($data,$caja);  
        }        
        $movimientos = $CajaMov->get_movimientos($request->getPost('cmv_caja'),$caja);
        foreach ($movimientos as $val) {
            $moti = $this->motivos[$val->CMV_TIPO];
            $descripcion = $val->CMV_DESCRIPCION;
            if(($val->CMV_TIPO == 6 || $val->CMV_TIPO == 7) && !empty($val->VEM_NOMBRE)){
                $descripcion = "<strong>".$val->VEM_NOMBRE."</strong><br>".$descripcion;
            }
            $tr = "<tr><td>$moti</td><td>$descripcion</td><td>$val->CMV_MONTO</td>";            
            $tr.= "<td><a href='#' class='nav-link' title='Eliminar'><span class='float-right badge bg-danger'><i class='fas fa-trash' onclick='quitar_mov($val->CMV_NRO)'></i></span></a></td>";
            echo $tr;
        }
    }
    public function listar_movimientos(){
        $session = session();        
        $caja = $session->get('caja');
        $nroc = $this->request->getVar('nro_caja');
        $local = $this->request->getVar('local');
        $CajaMov = new CajaMovimientosModel();        
        $movimientos = $CajaMov->get_movimientos($nroc,$caja);
        return $this->response->setJSON($movimientos);
    }
    public function get_nro_doc(){
        $session = session();        
        $caja = $session->get('caja');
        $Allog = new AllogModel();
        $allog = $Allog->get_nro_doc(date('d/m/Y'),$caja);
        echo json_encode($allog);
    }
    public function eliminar_movimiento(){
        $session = session();        
        $caja = $this->request->getVar('local')?$this->request->getVar('local'):$session->get('caja');
        $data = $this->request->getVar('cmv_nro');
        $CajaMov = new CajaMovimientosModel();
        $CajaMov->delete_movimiento($data,$caja);
    }
    public function editar_caja2(){
        $local = $this->request->getVar('local');
        $caja = $this->request->getVar('caja');
        $resp = $this->request->getVar('resp');
        $Caja = new CajaModel();
        $Caja->editar_caja2($resp,$caja,$local);
    }
    public function editar_caja3(){
        $local = $this->request->getVar('local');
        $caja = $this->request->getVar('caja');
        $efec = $this->request->getVar('efec');
        $Caja = new CajaModel();
        $Caja->editar_caja3($efec,$caja,$local);
    }
    public function eliminarcaja(){   
        $uri = $this->request->getUri();     
        $caja = $uri->getSegment(3);
        $local = $uri->getSegment(4);
        $Caja = new CajaModel();
        $Caja->eliminar_caja($caja,$local);
        return $this->response->redirect(site_url('caja/diario'));
    }
	public function set_caja(){
		$data = $this->request->getVar('caja');
		$session = session();
        $session->set('caja', $data);        
		return 'ok'; 
	}
	public function set_mes(){
		$mes = $this->request->getVar('mes');
        $anio = $this->request->getVar('anio');
		$session = session();
        $session->set('mes_caja', $mes);    
        $session->set('anio_caja', $anio);       
		return 'ok'; 
	}
    public function set_vendedor(){
		$data = $this->request->getVar('vendedor');
		$session = session();
        $session->set('vendedor', $data);        
		return 'ok'; 
	}
    public function print(){

           // echo nl2br("█████▓▒░░ REPORTE DE CIERRE DE CAJA ░░▒▓█████ \n");
           // echo nl2br("\n");
           // echo nl2br("Fecha de Impresión:".date('d-m-Y h:i:s a', time())."\n");

            return view('pdf/ticket_caja');
        
    }
}