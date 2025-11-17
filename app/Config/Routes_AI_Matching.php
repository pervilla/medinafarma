<?php

/**
 * AI Matching Routes
 * 
 * Agregar estas rutas en app/Config/Routes.php
 */

// AI Matching Module
$routes->group('aimatching', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'AIMatching::index');
    $routes->post('processBatch', 'AIMatching::processBatch');
    $routes->post('processWithCloudflare', 'AIMatching::processWithCloudflare');
    $routes->get('review', 'AIMatching::review');
    $routes->post('approve', 'AIMatching::approve');
    $routes->post('reject', 'AIMatching::reject');
    $routes->post('batchApprove', 'AIMatching::batchApprove');
    $routes->post('batchReject', 'AIMatching::batchReject');
    $routes->get('stats', 'AIMatching::stats');
});
