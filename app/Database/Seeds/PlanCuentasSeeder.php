<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanCuentasSeeder extends Seeder
{
    public function run()
    {
        // Desactivar verificación de claves foráneas temporalmente
        $this->db->disableForeignKeyChecks();

        // Vaciar tabla primero (opcional, para desarrollo)
        $this->db->table('PLAN_CUENTAS')->truncate();

        // Mapa para guardar ID de cada código
        $idMap = [];

        // Datos en orden jerárquico (primero padres, luego hijos)
        $cuentas = [
            // Nivel 1: Grupos principales (sin padre)
            [
                'PC_CODIGO' => '6',
                'PC_NOMBRE' => 'GASTOS',
                'PC_TIPO' => 'E',
                'PC_PADRE' => null,
                'PC_DESCRIPCION' => 'Gastos generales de la empresa',
                'PC_ACTIVO' => 1
            ],
            
            // Nivel 2: Subgrupos bajo GASTOS (6) - padre se asignará después
            [
                'PC_CODIGO' => '6.1',
                'PC_NOMBRE' => 'Gastos Operativos',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6', // código del padre, no ID
                'PC_DESCRIPCION' => 'Gastos de operación del negocio',
                'PC_ACTIVO' => 1
            ],
            [
                'PC_CODIGO' => '6.2',
                'PC_NOMBRE' => 'Gastos Financieros',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6',
                'PC_DESCRIPCION' => 'Gastos relacionados con financiamiento',
                'PC_ACTIVO' => 1
            ],
            [
                'PC_CODIGO' => '6.3',
                'PC_NOMBRE' => 'Remuneraciones',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6',
                'PC_DESCRIPCION' => 'Gastos de personal',
                'PC_ACTIVO' => 1
            ],
            
            // Nivel 3: Cuentas bajo Gastos Operativos (6.1)
            [
                'PC_CODIGO' => '6.1.1',
                'PC_NOMBRE' => 'Movilidad',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.1',
                'PC_DESCRIPCION' => 'Transporte, pasajes, combustible',
                'PC_ACTIVO' => 1
            ],
            [
                'PC_CODIGO' => '6.1.2',
                'PC_NOMBRE' => 'Servicios Básicos',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.1',
                'PC_DESCRIPCION' => 'Luz, agua, internet, teléfono',
                'PC_ACTIVO' => 1
            ],
            [
                'PC_CODIGO' => '6.1.3',
                'PC_NOMBRE' => 'Alquileres',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.1',
                'PC_DESCRIPCION' => 'Alquiler de locales y equipos',
                'PC_ACTIVO' => 1
            ],
            [
                'PC_CODIGO' => '6.1.4',
                'PC_NOMBRE' => 'Mensajería',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.1',
                'PC_DESCRIPCION' => 'Servicios de mensajería y courier',
                'PC_ACTIVO' => 1
            ],
            [
                'PC_CODIGO' => '6.1.5',
                'PC_NOMBRE' => 'Refrigerios',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.1',
                'PC_DESCRIPCION' => 'Alimentación del personal',
                'PC_ACTIVO' => 1
            ],
            [
                'PC_CODIGO' => '6.1.6',
                'PC_NOMBRE' => 'Otros Gastos Operativos',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.1',
                'PC_DESCRIPCION' => 'Otros gastos de operación',
                'PC_ACTIVO' => 1
            ],
            
            // Nivel 3: Cuentas bajo Gastos Financieros (6.2)
            [
                'PC_CODIGO' => '6.2.1',
                'PC_NOMBRE' => 'Intereses por Mora',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.2',
                'PC_DESCRIPCION' => 'Intereses moratorios por pagos atrasados',
                'PC_ACTIVO' => 1
            ],
            [
                'PC_CODIGO' => '6.2.2',
                'PC_NOMBRE' => 'Comisiones Bancarias',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.2',
                'PC_DESCRIPCION' => 'Comisiones por servicios bancarios',
                'PC_ACTIVO' => 1
            ],
            [
                'PC_CODIGO' => '6.2.3',
                'PC_NOMBRE' => 'Gastos por Préstamos',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.2',
                'PC_DESCRIPCION' => 'Intereses y gastos de préstamos',
                'PC_ACTIVO' => 1
            ],
            
            // Nivel 3: Cuentas bajo Remuneraciones (6.3)
            [
                'PC_CODIGO' => '6.3.1',
                'PC_NOMBRE' => 'Sueldos',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.3',
                'PC_DESCRIPCION' => 'Sueldos del personal',
                'PC_ACTIVO' => 1
            ],
            [
                'PC_CODIGO' => '6.3.2',
                'PC_NOMBRE' => 'AFP',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.3',
                'PC_DESCRIPCION' => 'Aportes a AFP',
                'PC_ACTIVO' => 1
            ],
            [
                'PC_CODIGO' => '6.3.3',
                'PC_NOMBRE' => 'Essalud',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.3',
                'PC_DESCRIPCION' => 'Seguro de salud',
                'PC_ACTIVO' => 1
            ],
            [
                'PC_CODIGO' => '6.3.4',
                'PC_NOMBRE' => 'Bonificaciones',
                'PC_TIPO' => 'E',
                'PC_PADRE' => '6.3',
                'PC_DESCRIPCION' => 'Bonos y gratificaciones',
                'PC_ACTIVO' => 1
            ],
        ];

        // Primera pasada: insertar cuentas de nivel 1 (sin padre)
        foreach ($cuentas as $cuenta) {
            if ($cuenta['PC_PADRE'] === null) {
                $data = $cuenta;
                unset($data['PC_PADRE']); // Ya es null
                $this->db->table('PLAN_CUENTAS')->insert($data);
                $idMap[$cuenta['PC_CODIGO']] = $this->db->insertID();
            }
        }

        // Segunda pasada: insertar cuentas de nivel 2 (con padre conocido)
        foreach ($cuentas as $cuenta) {
            if ($cuenta['PC_PADRE'] !== null && isset($idMap[$cuenta['PC_PADRE']])) {
                $data = $cuenta;
                $data['PC_PADRE'] = $idMap[$cuenta['PC_PADRE']];
                $this->db->table('PLAN_CUENTAS')->insert($data);
                $idMap[$cuenta['PC_CODIGO']] = $this->db->insertID();
            }
        }

        // Tercera pasada: insertar cuentas de nivel 3 (con padre conocido)
        // Ya se insertaron en la segunda pasada porque todas tienen padre en el mapa
        // Pero por seguridad, hacemos una pasada para las que no se hayan insertado
        foreach ($cuentas as $cuenta) {
            if (!isset($idMap[$cuenta['PC_CODIGO']]) && isset($idMap[$cuenta['PC_PADRE']])) {
                $data = $cuenta;
                $data['PC_PADRE'] = $idMap[$cuenta['PC_PADRE']];
                $this->db->table('PLAN_CUENTAS')->insert($data);
                $idMap[$cuenta['PC_CODIGO']] = $this->db->insertID();
            }
        }

        // Reactivar verificación de claves foráneas
        $this->db->enableForeignKeyChecks();
    }
}