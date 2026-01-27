<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlanillaExtras extends Migration
{
    public function up()
    {
        // Table: planilla_extras
        // Check if table exists
        $tables = $this->db->listTables();
        $tableName = 'planilla_extras';

        if (!in_array($tableName, $tables)) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'vem_codven' => [
                    'type'       => 'INT',
                ],
                'tipo' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50', // 'HORA_EXTRA_25', 'HORA_EXTRA_35', 'FERIADO'
                ],
                'fecha' => [
                    'type' => 'DATE',
                ],
                'cantidad' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                ],
                'observacion' => [
                    'type' => 'VARCHAR',
                    'constraint' => '255',
                    'null' => true,
                ],
                'estado' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20', // 'PENDIENTE', 'PROCESADO'
                    'default'    => 'PENDIENTE',
                ],
                'planilla_id' => [
                    'type'       => 'INT',
                    'unsigned'   => true,
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
            $this->forge->createTable($tableName);
        }
    }

    public function down()
    {
        $this->forge->dropTable('planilla_extras', true);
    }
}
