<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEgresosTable extends Migration
{
    public function up()
    {

        $this->forge->addField([
            'EGR_ID' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'EGR_FECHA' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'EGR_LOCAL' => [
                'type'       => 'INT',
                'constraint' => 1,
                'null'       => false,
                'comment'    => '1=Centro, 2=Juanjuicillo, 3=Peñameza',
            ],
            'EGR_CUENTA_ID' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'EGR_DESCRIPCION' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'EGR_MONTO' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
            ],
            // Comprobante
            'EGR_COMPROBANTE_TIPO' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
                'comment'    => 'FA=Factura, BO=Boleta, RH=Recibo por Honorarios, etc.',
            ],
            'EGR_COMPROBANTE_SERIE' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
            ],
            'EGR_COMPROBANTE_NUMERO' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
            ],
            // Pago
            'EGR_FORMA_PAGO' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'default'    => 'EFECTIVO',
                'comment'    => 'EFECTIVO, TRANSFERENCIA, TARJETA',
            ],
            'EGR_RESPONSABLE' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'FK a VEMAEST(VEM_CODVEN)',
            ],
            'EGR_ESTADO' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'pagado',
                'comment'    => 'pagado, pendiente, anulado',
            ],
            // Letras y vencimientos
            'EGR_FECHA_VCTO' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'EGR_INTERESES' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            // Relaciones específicas para intereses de compras
            'EGR_TIPO_EGRESO' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'default'    => 'NORMAL',
                'comment'    => 'NORMAL, INTERES_MORA, LETRA',
            ],
            'EGR_FACTURA_REF' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Referencia a car_NUMFAC si es interés',
            ],
            'EGR_PROVEEDOR_COD' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'car_CODCLIE',
            ],
            // Integración con caja
            'EGR_CAJA_MOV_ID' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'FK a CAJA_MOVIMIENTOS(CM_ID)',
            ],
            // Auditoría
            'EGR_USUARIO' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'EGR_FECHA_REGISTRO' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'EGR_OBSERVACIONES' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('EGR_ID', true);
        // $this->forge->addForeignKey('EGR_CUENTA_ID', 'PLAN_CUENTAS', 'PC_ID', 'CASCADE', 'NO ACTION');
        // Nota: No agregamos FK a VEMAEST porque puede estar en otra base de datos
        // Nota: No agregamos FK a CAJA_MOVIMIENTOS para evitar dependencias circulares
        $this->forge->createTable('EGRESOS');

        // Índices para mejorar rendimiento
        $this->forge->addKey(['EGR_FECHA', 'EGR_TIPO_EGRESO']);
        $this->forge->addKey('EGR_FACTURA_REF');
        $this->forge->addKey('EGR_PROVEEDOR_COD');
    }

    public function down()
    {
        $this->forge->dropTable('EGRESOS');
    }
}