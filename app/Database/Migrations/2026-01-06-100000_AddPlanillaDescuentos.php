<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlanillaDescuentos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vem_codven' => [
                'type'       => 'INT',
                'unsigned'   => true,
            ],
            'tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => '50', // INVENTARIO, FALTANTE, CAJA, OTRO
            ],
            'monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
            ],
            'fecha' => [
                'type' => 'DATE',
            ],
            'observacion' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'PENDIENTE', // PENDIENTE, PROCESADO
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
        $this->forge->createTable('planilla_descuentos');
    }

    public function down()
    {
        $this->forge->dropTable('planilla_descuentos');
    }
}
