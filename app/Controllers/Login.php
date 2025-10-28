<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers;
use App\Models\UsuariosModel;
/**
 * Description of Login
 *
 * @author José Luis
 */
class Login extends BaseController{
    //put your code here
    public function index() {
        $data = array();
         return view('login/index',$data);
    }
    public function auth()
    {
        $session = session();
        $user = strtoupper($this->request->getVar('user'));
        $password = $this->request->getVar('pwd');
        $Usuarios = new UsuariosModel();
        $usuario = trim($Usuarios->get_usuario($user,$password));
        if($usuario){
            $ses_data = [
                'user_id'       => $user,
                'user_name'     => $usuario,
                'user_email'    => "",
                'logged_in'     => TRUE
            ];
            $session->set($ses_data);
            echo $usuario;
        }else{
            $session->setFlashdata('msg', 'Password Incorrecto');
        }
    }
    public function user(){
        //echo "userrrr"; die();
        $user = $this->request->getVar('user');
        $Usuarios = new UsuariosModel();
        $usuario = $Usuarios->get_usuario($user,'');
        echo trim($usuario);
    }
    public function close(){        
        $session = session();
        $session->destroy();
        echo "Sesion cerrada";
    }
}
