<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlanillaTables extends Migration
{
    public function up()
    {
        // 1. Table planilla_afps
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'porcentaje' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,4', // Allow detailed percentages
                'default'    => 0.00,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('planilla_afps');

        // Insert Default AFPs
        $this->db->table('planilla_afps')->insertBatch([
            ['nombre' => 'INTEGRA', 'porcentaje' => 13.00], // Example approx
            ['nombre' => 'HABITAT', 'porcentaje' => 12.50],
            ['nombre' => 'PROFUTURO', 'porcentaje' => 13.00],
            ['nombre' => 'ONP', 'porcentaje' => 13.00],
            ['nombre' => 'PRIMA', 'porcentaje' => 13.00],
        ]);


        // 2. Table planilla_config_empleados
        $this->forge->addField([
            'vem_codven' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'sueldo_basico' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'tipo_comision' => [
                'type'       => 'VARCHAR', // VENTAS, FIJO, NINGUNO
                'constraint' => '20', 
                'default'    => 'NINGUNO',
            ],
            'afp_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'asignacion_familiar' => [
                'type'       => 'TINYINT', // 1 or 0
                'default'    => 0,
            ],
        ]);
        $this->forge->addKey('vem_codven', true);
        // Note: We don't add FK to VEMAEST rigidly because it might be in a different DB or engine, 
        // but logically it links there.
        $this->forge->createTable('planilla_config_empleados');


        // 3. Table planillas
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'anio' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'mes' => [
                'type'       => 'INT',
                'constraint' => 2,
            ],
            'fecha_inicio' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'fecha_corte' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'estado' => [
                'type'       => 'VARCHAR', // BORRADOR, PROCESADA, PAGADA
                'constraint' => '20',
                'default'    => 'BORRADOR',
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('planillas');

        // 4. Table planilla_detalles
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'planilla_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'vem_codven' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'dias_trabajados' => [
                'type'       => 'INT',
                'default'    => 30,
            ],
            'sueldo_basico' => [
                 'type'       => 'DECIMAL',
                 'constraint' => '10,2',
                 'default'    => 0.00,
            ],
            'asignacion_familiar' => [
                 'type'       => 'DECIMAL',
                 'constraint' => '10,2',
                 'default'    => 0.00,
            ],
            'comision_ventas' => [
                 'type'       => 'DECIMAL',
                 'constraint' => '10,2',
                 'default'    => 0.00,
            ],
            'afp_monto' => [
                 'type'       => 'DECIMAL',
                 'constraint' => '10,2',
                 'default'    => 0.00,
            ],
            'adelantos' => [
                 'type'       => 'DECIMAL',
                 'constraint' => '10,2',
                 'default'    => 0.00,
            ],
            'creditos' => [
                 'type'       => 'DECIMAL',
                 'constraint' => '10,2',
                 'default'    => 0.00,
            ],
            'faltantes' => [
                 'type'       => 'DECIMAL',
                 'constraint' => '10,2',
                 'default'    => 0.00,
            ],
            'total_neto' => [
                 'type'       => 'DECIMAL',
                 'constraint' => '10,2',
                 'default'    => 0.00,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('planilla_id', 'planillas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('planilla_detalles');
    }

    public function down()
    {
        $this->forge->dropTable('planilla_detalles');
        $this->forge->dropTable('planillas');
        $this->forge->dropTable('planilla_config_empleados');
        $this->forge->dropTable('planilla_afps');
    }
}
