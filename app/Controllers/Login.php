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
        $session = session();
        if($session->get('logged_in')){
            return redirect()->to(base_url('dashboard'));
        }
        $data = array();
         return view('login/index',$data);
    }
    public function auth()
    {
        $session = session();
        $user = strtoupper($this->request->getVar('user') ?? $_POST['email'] ?? ''); // Handle both form and ajax inputs
        $password = $this->request->getVar('pwd') ?? $_POST['password'] ?? '';
        $remember = $this->request->getVar('remember');
        
        $Usuarios = new UsuariosModel();
        
        // Basic validation
        if(empty($user) || empty($password)) {
             $session->setFlashdata('error', 'Usuario y contraseña son requeridos');
             if(! $this->request->isAJAX()) {
                 return redirect()->to(base_url());
             }
             echo "Faltan datos";
             return;
        }

        $usuario = trim($Usuarios->get_usuario($user,$password));
        
        if($usuario){
            $ses_data = [
                'user_id'       => $user,
                'user_name'     => $usuario,
                'user_email'    => "",
                'logged_in'     => TRUE,
                'rol'           => 'ADMIN',
                'tienePermiso'  => TRUE
            ];
            $session->set($ses_data);
            
            // "Remember Me" Logic (Trusted PC)
            if ($remember) {
                $encrypter = \Config\Services::encrypter();
                $tokenData = json_encode([
                    'user_id' => $user,
                    'expiry' => time() + (30 * 24 * 3600) // 30 days
                ]);
                $encryptedToken = bin2hex($encrypter->encrypt($tokenData));
                
                setcookie('remember_token', $encryptedToken, time() + (30 * 24 * 3600), '/', '', false, true);
            }

            if($this->request->isAJAX()){
                echo $usuario;
            } else {
                return redirect()->to(base_url('dashboard')); // Redirect to dashboard or home
            }
            
        }else{
            $session->setFlashdata('error', 'Usuario o contraseña incorrectos');
            if($this->request->isAJAX()){
                 echo ""; // AJAX expects truthy value for success
            } else {
                 return redirect()->to(base_url());
            }
        }
    }
    public function user(){
        $user = $this->request->getVar('user');
        $Usuarios = new UsuariosModel();
        $usuario = $Usuarios->get_usuario($user,'');
        echo trim($usuario);
    }
    public function close(){        
        $session = session();
        $session->destroy();
        // Clear Remember Me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        echo "Sesion cerrada";
    }
}
