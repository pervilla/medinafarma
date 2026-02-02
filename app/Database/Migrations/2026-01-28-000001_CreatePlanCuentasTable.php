<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlanCuentasTable extends Migration
{
    public function up()
    {

        $this->forge->addField([
            'PC_ID' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'PC_CODIGO' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => false,
            ],
            'PC_NOMBRE' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => false,
            ],
            'PC_TIPO' => [
                'type'       => 'CHAR',
                'constraint' => '1',
                'null'       => false,
                'comment'    => 'I=Ingreso, E=Egreso',
            ],
            'PC_PADRE' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'PC_ACTIVO' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'PC_DESCRIPCION' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('PC_ID', true);
        // $this->forge->addForeignKey('PC_PADRE', 'PLAN_CUENTAS', 'PC_ID', 'NO ACTION', 'NO ACTION');
        $this->forge->createTable('PLAN_CUENTAS');
    }


    public function down()
    {
        $this->forge->dropTable('PLAN_CUENTAS');
    }
}