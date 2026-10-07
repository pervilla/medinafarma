<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\AnulacionModel;

class TestAnular extends BaseCommand
{
    protected $group       = 'Test';
    protected $name        = 'test:anular';
    protected $description = 'Prueba la anulacion 1111 en modo dryRun (rollback).';

    public function run(array $params)
    {
        $numOper = (int) ($params[0] ?? 92);
        $fecha   = $params[1] ?? '2026-10-06';
        $server  = (int) ($params[2] ?? 1);

        CLI::write("Probando anulacion numOper={$numOper} fecha={$fecha} server={$server}", 'yellow');

        $model  = new AnulacionModel();
        $result = $model->anularDocumento($numOper, $fecha, $server, 'ADMIN', 'TEST DRYRUN', true);

        CLI::write(print_r($result, true), $result['status'] ? 'green' : 'red');
    }
}
