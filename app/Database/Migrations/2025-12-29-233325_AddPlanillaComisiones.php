<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlanillaComisiones extends Migration
{
    public function up()
    {
        // 1. Add comision_fijo_monto to planilla_config_empleados
        if (!$this->db->fieldExists('comision_fijo_monto', 'planilla_config_empleados')) {
            $fields = [
                'comision_fijo_monto' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0.00,
                ],
            ];
            $this->forge->addColumn('planilla_config_empleados', $fields);
        }

        // 2. Create table planilla_reglas_comision
        if (!$this->db->tableExists('planilla_reglas_comision')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'tipo' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'DEFAULT', // 'DEFAULT', 'PRODUCTO'
                ],
                'referencia_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true, // ID of product if type=PRODUCTO
                ],
                 'nombre_producto' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null'       => true, // Optional duplicate for display speed
                ],
                'porcentaje' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 0.00,
                ],
                'descripcion' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '200',
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
            $this->forge->createTable('planilla_reglas_comision');

            // Insert Default Rule if table just created
            $this->db->table('planilla_reglas_comision')->insert([
                'tipo'        => 'DEFAULT',
                'porcentaje'  => 0.00,
                'descripcion' => 'Comisión por Defecto para productos sin regla',
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }


    }

    public function down()
    {
        $this->forge->dropTable('planilla_reglas_comision');
        $this->forge->dropColumn('planilla_config_empleados', 'comision_fijo_monto');
    }
}
