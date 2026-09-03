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
use App\Models\EgresoModel;
use App\Models\PlanCuentaModel;
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
        '14' => 'FALTO DIGITAR COMPRA',
        '15' => 'DESCUENTO POR FALTANTES',
    ];
    
    private $cuentaEgresosMap = [
        11 => 1, // GASTOS BOTICA - cuenta por defecto
        12 => 1, // DELIVERY - cuenta por defecto
        13 => 1  // PAGO A MEDICO - cuenta por defecto
    ];
    
    /**
     * Convierte fecha de SQL Server a formato YYYY-mm-dd
     */
    private function convertirFechaSqlServer($fechaRaw)
    {
        $fecha = null;
        log_message('error', 'DEBUG convertirFechaSqlServer: raw=' . var_export($fechaRaw, true));
        
        if (empty($fechaRaw)) {
             $fecha = date('d-m-Y');
        }
        
        // Si es objeto DateTime
        elseif (is_object($fechaRaw) && method_exists($fechaRaw, 'format')) {
             $fecha = $fechaRaw->format('d-m-Y');
        }
        
        // Si es string
        elseif (is_string($fechaRaw)) {
            $fechaRaw = trim($fechaRaw);
            
            // SQL Server a veces devuelve fechas con milisegundos: '2026-01-30 00:00:00.000'
            // Eliminar milisegundos
            $fechaRaw = preg_replace('/\.\d+/', '', $fechaRaw);
            log_message('error', 'DEBUG convertirFechaSqlServer: after millis removal=' . $fechaRaw);
            
            // Intentar con strtotime primero
            $timestamp = strtotime($fechaRaw);
            log_message('error', 'DEBUG convertirFechaSqlServer: strtotime result=' . var_export($timestamp, true));
            if ($timestamp !== false) {
                $fecha = date('d-m-Y', $timestamp);
                log_message('error', 'DEBUG convertirFechaSqlServer: strtotime success, fecha=' . $fecha);
            } else {
                // Intentar formatos específicos - priorizar formatos SQL Server comunes
                $formats = [
                    'Ymd',           // 20260130 (SQL Server char común)
                    'Y-m-d H:i:s',   // 2026-01-30 00:00:00
                    'Y-m-d',         // 2026-01-30
                    'd/m/Y',         // 30/01/2026
                    'd-m-Y',         // 30-01-2026
                    'm/d/Y',         // 01/30/2026
                    'Y-m-d\TH:i:s',  // Formato ISO con T
                    'Y-m-d H:i:s.u', // Con microsegundos
                    'd/m/Y H:i:s',   // 30/01/2026 00:00:00
                    'd-m-Y H:i:s',   // 30-01-2026 00:00:00
                    'm/d/Y H:i:s',   // 01/30/2026 00:00:00
                ];
                
                foreach ($formats as $format) {
                    $dateTime = \DateTime::createFromFormat($format, $fechaRaw);
                    log_message('error', 'DEBUG convertirFechaSqlServer: trying format ' . $format . ' with "' . $fechaRaw . '" result=' . var_export($dateTime, true));
                    if ($dateTime !== false) {
                         $fecha = $dateTime->format('d-m-Y');
                        log_message('error', 'DEBUG convertirFechaSqlServer: format ' . $format . ' success, fecha=' . $fecha);
                        break;
                    }
                }
            }
        }
        
        // Validar que la fecha sea válida
        if ($fecha) {
             $dateTime = \DateTime::createFromFormat('d-m-Y', $fecha);
            if ($dateTime !== false) {
                // Verificar que los componentes coincidan (evitar fechas como 2026-02-31)
                 if ($dateTime->format('d-m-Y') === $fecha) {
                    return $fecha;
                }
            }
        }
        
        // Si todo falla, usar fecha actual
        log_message('error', 'DEBUG convertirFechaSqlServer: No se pudo convertir fecha, usando fecha actual. Raw: ' . var_export($fechaRaw, true));
         return date('d-m-Y');
    }


    
    /**
     * Actualiza referencia CMV_EGRESO_ID en tabla CAJA_MOVIMIENTOS
     */
    private function actualizarReferenciaEgreso($cmv_nro, $egreso_id, $local)
    {
        try {
            $db = \Config\Database::connect();
            if ($local == 2) {
                $db = \Config\Database::connect('juanjuicillo');
            } elseif ($local == 3) {
                $db = \Config\Database::connect('pmeza');
            }
            
            $tableName = 'CAJA_MOVIMIENTOS';
            $columns = $db->getFieldNames($tableName);
            
            if (in_array('CMV_EGRESO_ID', $columns)) {
                $builder = $db->table($tableName);
                $builder->where('CMV_NRO', $cmv_nro);
                $builder->update(['CMV_EGRESO_ID' => $egreso_id]);
                log_message('debug', 'Referencia actualizada: CMV_NRO=' . $cmv_nro . ' -> EGR_ID=' . $egreso_id);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error actualizando referencia: ' . $e->getMessage());
        }
    }

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
                default: $data['color']=''; break;
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
                $connector = new WindowsPrintConnector("smb://asesor:159357@ventas2/6-EPSON TM-T20IV Receipt");  
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

    public function actualizar_movimiento() {
        $session = session();        
        $caja = $session->get('caja');
        $request = service('request');
        $CajaMov = new CajaMovimientosModel();
        
        $nro = $request->getPost('cmv_nro');
        if($nro > 0){
            $data = array(
                'CMV_TIPO' => $request->getPost('cmv_tipo'),
                'CMV_CODVEN' => $request->getPost('cmv_codven'),
                'CMV_DESCRIPCION' => strtoupper($request->getPost('cmv_descri')),
                'CMV_MONTO' => $request->getPost('cvm_monto')
            );
            $CajaMov->update_movimiento($nro, $data, $caja);
            return $this->response->setJSON(['success' => true, 'message' => 'Movimiento actualizado correctamente']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'ID de movimiento no válido']);
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

    /**
     * Exportar movimiento de caja a egresos
     */
    public function exportarAEgresos()
    {
        ini_set('memory_limit', '256M'); // Aumentar memoria para evitar error
        $session = session();
        // Para pruebas, si no hay usuario, usar ID 1
        if (!$session->get('user_id')) {
            $session->set('user_id', 1);
        }
        $cmv_nro = $this->request->getPost('cmv_nro');
        $local = $this->request->getPost('local') ?? $session->get('caja'); // local actual (1,2,3)
        if (!$local) {
            $local = 1; // default centro
        }
        
        if (!$cmv_nro) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de movimiento no proporcionado'
            ]);
        }
        
        // Obtener datos del movimiento
        $cajaMovModel = new CajaMovimientosModel();
        $movimiento = $cajaMovModel->get_movimiento($cmv_nro, $local);
        log_message('error', 'DEBUG exportarAEgresos: movimiento objeto keys: ' . json_encode(array_keys(get_object_vars($movimiento))));
        log_message('error', 'DEBUG exportarAEgresos: CAJ_FECHA value: ' . (isset($movimiento->CAJ_FECHA) ? var_export($movimiento->CAJ_FECHA, true) : 'NO EXISTE'));
        
        if (!$movimiento) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Movimiento no encontrado'
            ]);
        }
        
        // Verificar si ya fue exportado (por CMV_EGRESO_ID)
        if (isset($movimiento->CMV_EGRESO_ID) && $movimiento->CMV_EGRESO_ID > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Este movimiento ya fue exportado a egresos (ID: ' . $movimiento->CMV_EGRESO_ID . ')'
            ]);
        }
        
        // Verificar adicionalmente en la tabla EGRESOS por si acaso
        $egresoModel = new EgresoModel();
        $egresoExistente = $egresoModel->where('EGR_CAJA_MOV_ID', $cmv_nro)->get()->getRowArray();
        if ($egresoExistente) {
            // Actualizar referencia en CAJA_MOVIMIENTOS si no existe
            if (!isset($movimiento->CMV_EGRESO_ID) || $movimiento->CMV_EGRESO_ID <= 0) {
                $this->actualizarReferenciaEgreso($cmv_nro, $egresoExistente['EGR_ID'], $local);
            }
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Este movimiento ya fue exportado a egresos (EGR_ID: ' . $egresoExistente['EGR_ID'] . ')'
            ]);
        }
        
        // Verificar si es exportable (tipos definidos en el mapa)
        if (!in_array($movimiento->CMV_TIPO, array_keys($this->cuentaEgresosMap))) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Este tipo de movimiento no puede exportarse a egresos'
            ]);
        }
        
        // Mapear tipo de movimiento a cuenta de egreso
        $cuenta_id = $this->cuentaEgresosMap[$movimiento->CMV_TIPO] ?? 1;
        
        // Obtener fecha de la caja y convertir a formato SQL Server compatible
        $fechaRaw = isset($movimiento->CAJ_FECHA) ? $movimiento->CAJ_FECHA : date('Y-m-d');
        $fecha = $this->convertirFechaSqlServer($fechaRaw);
        
        // Usar la fecha convertida
        $fechaIso = $fecha;
        
        // Log para debugging - información detallada
        log_message('error', 'DEBUG exportarAEgresos: Fecha raw: ' . var_export($fechaRaw, true));
        log_message('error', 'DEBUG exportarAEgresos: Fecha raw tipo: ' . gettype($fechaRaw));
        log_message('error', 'DEBUG exportarAEgresos: Fecha usada: ' . $fecha);
        
        try {
            $egresoModel = new EgresoModel();
            $planCuentaModel = new PlanCuentaModel();
            
            // Verificar si la cuenta existe, si no usar cuenta por defecto
            $cuenta = $planCuentaModel->find($cuenta_id);
            if (!$cuenta) {
                // Buscar primera cuenta de egreso activa
                $cuentas = $planCuentaModel->getCuentasActivas('E');
                if (!empty($cuentas)) {
                    $cuenta_id = $cuentas[0]['PC_ID'];
                } else {
                    $cuenta_id = 1; // fallback
                }
            }
            
            // Preparar datos para el egreso
            $egresoData = [
                'fecha' => $fechaIso, // formato ISO 8601 para SQL Server datetime
                'local' => $local,
                'cuenta_id' => $cuenta_id,
                'descripcion' => 'CAJA: ' . $movimiento->CMV_DESCRIPCION,
                'monto' => $movimiento->CMV_MONTO,
                'forma_pago' => 'EFECTIVO',
                'estado' => 'pagado',
                'usuario' => $session->get('user_id'),
                'observaciones' => 'Exportado desde caja movimiento ID: ' . $cmv_nro,
                'caja_mov_id' => $cmv_nro, // Referencia al movimiento de caja
                'registrar_caja' => false // Ya existe el movimiento en caja
            ];
            
            // Log detallado de datos
            log_message('error', 'Datos del egreso a crear: ' . json_encode($egresoData));
            
            // Los egresos se centralizan en la base de datos del servidor por defecto (local 1)
            // pero conservan el local de origen en el campo EGR_LOCAL para trazabilidad
            $egresoId = $egresoModel->registrarEgresoNormal($egresoData);
            
            // Actualizar movimiento con referencia al egreso
            // Necesitamos actualizar la tabla CAJA_MOVIMIENTOS.CMV_EGRESO_ID
            // Primero verificar si la columna existe, si no, no actualizar
            $db = \Config\Database::connect();
            if ($local == 2) {
                $db = \Config\Database::connect('juanjuicillo');
            } elseif ($local == 3) {
                $db = \Config\Database::connect('pmeza');
            }
            
            // Verificar si existe la columna CMV_EGRESO_ID
            $tableName = 'CAJA_MOVIMIENTOS';
            $columns = $db->getFieldNames($tableName);
            
            if (in_array('CMV_EGRESO_ID', $columns)) {
                $builder = $db->table($tableName);
                $builder->where('CMV_NRO', $cmv_nro);
                $builder->update(['CMV_EGRESO_ID' => $egresoId]);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Movimiento exportado exitosamente a egresos (ID: ' . $egresoId . ')',
                'egreso_id' => $egresoId
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en exportarAEgresos: ' . $e->getMessage() . ' - CMV_NRO: ' . $cmv_nro . ' - Local: ' . $local);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al exportar a egresos: ' . $e->getMessage()
            ]);
        }
    }



    /**
     * Método de depuración para ver formato de fecha (sin autenticación requerida)
     */
    public function debugFecha($cmv_nro = null, $local = null)
    {
        // No requerir autenticación para depuración
        if (!$cmv_nro) {
            return 'Falta cmv_nro. Uso: /caja/debugFecha/<cmv_nro>/<local opcional>';
        }
        if (!$local) {
            $local = 1; // default
        }
        
        $cajaMovModel = new CajaMovimientosModel();
        $movimiento = $cajaMovModel->get_movimiento($cmv_nro, $local);
        
        if (!$movimiento) {
            return 'Movimiento no encontrado (cmv_nro: ' . $cmv_nro . ', local: ' . $local . ')';
        }
        
        echo '<pre>';
        echo 'CMV_NRO: ' . $cmv_nro . "\n";
        echo 'Local: ' . $local . "\n";
        echo 'CAJ_FECHA: ' . (isset($movimiento->CAJ_FECHA) ? var_export($movimiento->CAJ_FECHA, true) : 'NO EXISTE') . "\n";
        echo 'Tipo CAJ_FECHA: ' . (isset($movimiento->CAJ_FECHA) ? gettype($movimiento->CAJ_FECHA) : 'N/A') . "\n";
        
        // Probar conversión
        $fechaRaw = isset($movimiento->CAJ_FECHA) ? $movimiento->CAJ_FECHA : date('Y-m-d');
        $fechaConvertida = $this->convertirFechaSqlServer($fechaRaw);
        
        echo 'Fecha raw: ' . var_export($fechaRaw, true) . "\n";
        echo 'Fecha convertida: ' . $fechaConvertida . "\n";
        
        // Mostrar campos relevantes
        echo "\nCampos relevantes:\n";
        $relevantKeys = ['CAJ_FECHA', 'CMV_TIPO', 'CMV_MONTO', 'CMV_DESCRIPCION', 'CMV_EGRESO_ID'];
        foreach ($relevantKeys as $key) {
            if (isset($movimiento->$key)) {
                echo $key . ': ' . var_export($movimiento->$key, true) . "\n";
            }
        }
        
        echo "\n¿Exportable? (tipos 11,12,13): " . (in_array($movimiento->CMV_TIPO ?? 0, [11,12,13]) ? 'Sí' : 'No') . "\n";
        
        echo '</pre>';
        return '';
    }

    public function debugSchema()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('EGRESOS');
        echo '<pre>';
        foreach ($fields as $field) {
            echo $field->name . ' - ' . $field->type . ' - ' . $field->max_length . ' - ' . ($field->nullable ? 'NULL' : 'NOT NULL') . "\n";
        }
        echo '</pre>';
        return '';
    }

}