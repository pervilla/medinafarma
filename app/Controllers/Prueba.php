<?php

namespace App\Controllers;

use App\Models\Server03Model;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Prueba extends BaseController
{
      public function index()
      {
            $Facart3_model = new Server03Model();
            $comisiones3 = $Facart3_model->get_comisiones_simple('2', '2021');
            var_export($comisiones3);
      }
      public function pdf(){
            return view('pdf/factura');
      }
}
