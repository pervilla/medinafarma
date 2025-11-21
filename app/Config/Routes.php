<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
 $routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}

/*
 * -------------------------
 * RUTAS INVENTARIO
 * -------------------------
*/
$routes->get('inventario/dashboard', 'Inventario::dashboard');
$routes->get('inventario/lista/(:num)/(:num)', 'Inventario::lista/$1/$2');
$routes->get('inventario/lista/(:num)/(:num)/(:num)', 'Inventario::lista/$1/$2/$3');
$routes->get('inventario/lista/(:num)/(:num)/(:num)/(:num)', 'Inventario::lista/$1/$2/$3/$4');
$routes->get('inventario/conteo/(:num)/(:num)/(:num)', 'Inventario::conteo/$1/$2/$3');
$routes->post('inventario/actualizar_conteo', 'Inventario::actualizar_conteo');
$routes->post('inventario/buscar_producto', 'Inventario::buscar_producto');
$routes->post('inventario/distribuir_automatico', 'Inventario::distribuir_automatico');
$routes->post('inventario/cerrar_inventario', 'Inventario::cerrar_inventario');
$routes->post('inventario/generar_guias', 'Inventario::generar_guias');

/*
 * -------------------------
 * SERVIDOR IMAGENES
 * -------------------------
*/
$routes->match(['get', 'post'], 'imageRender/(:segment)/(:segment)', 'RenderImage::index/$1/$2');


// Rutas DIGEMID
$routes->get('digemid', 'Digemid::index');
$routes->post('digemid/generar', 'Digemid::generar');
$routes->get('digemid/descargar/(:any)', 'Digemid::descargar/$1');

// Rutas Relación DIGEMID
$routes->get('digemidrelacion', 'DigemidRelacion::index');
$routes->post('digemidrelacion/buscar', 'DigemidRelacion::buscar');
$routes->post('digemidrelacion/buscarArticulos', 'DigemidRelacion::buscarArticulos');
$routes->post('digemidrelacion/relacionar', 'DigemidRelacion::relacionar');
$routes->post('digemidrelacion/eliminar', 'DigemidRelacion::eliminar');
$routes->get('digemidrelacion/relacionados', 'DigemidRelacion::relacionados');
$routes->get('digemidrelacion/sinRelacionarLimitado', 'DigemidRelacion::sinRelacionarLimitado');

// Rutas Errores DIGEMID
$routes->get('digemiderrores', 'DigemidErrores::index');
$routes->post('digemiderrores/procesarCsv', 'DigemidErrores::procesarCsv');
$routes->get('digemiderrores/listarErrores', 'DigemidErrores::listarErrores');
$routes->get('digemiderrores/sinRelacionar', 'DigemidErrores::sinRelacionar');
$routes->post('digemiderrores/actualizarEstado', 'DigemidErrores::actualizarEstado');

/*
 * -------------------------
 * RUTAS CITAS CON SERVICIOS Y PAGOS
 * -------------------------
 */
$routes->get('citas', 'Citas::index');
$routes->get('citas/index', 'Citas::index');
$routes->get('citas/index/(:num)', 'Citas::index/$1');
$routes->post('citas/getCitasConServicios', 'Citas::getCitasConServicios');
$routes->post('citas/getCitaDetalle', 'Citas::getCitaDetalle');
$routes->post('citas/crearCita', 'Citas::crearCita');
$routes->post('citas/buscarServicios', 'Citas::buscarServicios');
$routes->post('citas/agregarServicio', 'Citas::agregarServicio');
$routes->post('citas/eliminarServicio', 'Citas::eliminarServicio');
$routes->post('citas/registrarPago', 'Citas::registrarPago');
$routes->post('citas/procesarPago', 'Citas::procesarPago');
$routes->post('citas/anularPago', 'Citas::anularPago');
$routes->post('citas/generarComprobante', 'Citas::generarComprobante');
$routes->post('citas/cambiarEstadoCita', 'Citas::cambiarEstadoCita');
$routes->get('citas/reportePagos', 'Citas::reportePagos');
$routes->post('citas/getReportePagos', 'Citas::getReportePagos');

/*
 * -------------------------
 * RUTAS CALENDARIO
 * -------------------------
 */
$routes->get('calendario', 'Calendario::index');
$routes->post('calendario/generar', 'Calendario::generar');
$routes->post('calendario/cerrar', 'Calendario::cerrar');
