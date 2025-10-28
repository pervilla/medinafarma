<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers;

use App\Models\PedidoModel;
use DonatelloZa\RakePlus\RakePlus;


/**
 * Description of Caja
 *
 * @author José Luis
 */
class Pos extends BaseController {
public function index(){
    $session = session();
    $session->set('igv', 0);   
    $session->set('tipo_igv_id', 1); 
 $data['categoria'] = array();
 $data['comprobante_id'] = 1;
 
    return view('pos/index',$data);
}

public function parafraseos(){
    $text = "Dolor abdominal que puede variar en intensidad y llegar a ser muy agudo. Los cólicos pueden venir acompañados de otros problemas digestivos como náuseas, vómitos o diarreas.";

    $phrases = RakePlus::create($text, 'es_AR')->get();
    
    print_r($phrases);
    
}
public function items(){
    $pedido = new PedidoModel();

    return $this->response->setJSON($pedido->get_pedido());
    
}
}