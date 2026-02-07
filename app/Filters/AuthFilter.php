<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsuariosModel;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $path = $request->uri->getPath();

        // Assets and resources to ignore
        if (strpos($path, 'assets/') === 0 || 
            strpos($path, 'plugins/') === 0 || 
            strpos($path, 'dist/') === 0 ||
            strpos($path, 'css/') === 0 ||
            strpos($path, 'js/') === 0) {
            return;
        }

        if ($session->get('logged_in')) {
            return;
        }

        // Check for Remember Me cookie
        if (isset($_COOKIE['remember_token'])) {
            try {
                $encrypter = \Config\Services::encrypter();
                $encryptedToken = hex2bin($_COOKIE['remember_token']);
                $tokenData = json_decode($encrypter->decrypt($encryptedToken), true);

                if (isset($tokenData['user_id']) && isset($tokenData['expiry'])) {
                    if (time() < $tokenData['expiry']) {
                        // Cookie is valid, log the user in
                        // We need to fetch the user name. 
                        $db = \Config\Database::connect();
                        $query = $db->query("SELECT USU_NOMBRE FROM Usuarios WHERE USU_KEY = ?", [$tokenData['user_id']]);
                        $row = $query->getRow();

                        if ($row) {
                             $ses_data = [
                                'user_id'       => $tokenData['user_id'],
                                'user_name'     => $row->USU_NOMBRE,
                                'user_email'    => "",
                                'logged_in'     => TRUE,
                                'rol'           => 'ADMIN',
                                'tienePermiso'  => TRUE
                            ];
                            $session->set($ses_data);
                            
                            // If we were on the login page, redirect to dashboard
                            if ($path === '/' || $path === '') {
                                return redirect()->to(base_url('dashboard'));
                            }
                            return;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Invalid token, ignore
            }
        }
        
        // List of public pages that don't require login
        $publicPages = [
            '/', 
            '', 
            'loginMe', 
            'login/auth', 
            'login/user', // AJAX user lookup
            'forgotPassword'
        ];
        
        // Check if current path is in public pages
        // We use in_array for exact match, but we might need to be careful about leading slashes
        // getPath() usually returns path relative to baseURL without leading slash, but let's be robust.
        $trimmedPath = trim($path, '/');
        
        $isLoginPage = in_array($trimmedPath, $publicPages);
        
        // If it's not the login page and we are not logged in, redirect to login
        if (! $session->get('logged_in') && ! $isLoginPage) {
             return redirect()->to(base_url('/'));
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
