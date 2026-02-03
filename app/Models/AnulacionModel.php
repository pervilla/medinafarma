<?php

namespace App\Models;

use CodeIgniter\Model;

class AnulacionModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Anulación compleja (LK_CODTRA = 1111)
     * Basado en el flujo de analisis_LK_CODTRA_1111_anulaciones.md
     */
    public function anularDocumento($numOperOriginal, $usuario)
    {
        $this->db->transStart();

        // 1. Obtener documento original del ALLOG
        $allogOriginal = $this->db->table('allog')
            ->where('ALL_NUMOPER', $numOperOriginal)
            ->where('ALL_FLAG_EXT <>', 'E')
            ->get()->getRow();

        if (!$allogOriginal) {
            return ['status' => false, 'message' => 'Operación original no encontrada o ya anulada.'];
        }

        // 2. Obtener detalle original de FACART
        $detalleOriginal = $this->db->table('FACART')
            ->where('FAR_NUMOPER', $numOperOriginal)
            ->where('far_estado <>', 'E')
            ->get()->getResult();

        // 3. Generar nuevo número de operación para la anulación
        $nuevoNumOper = $this->obtenerNuevoNumOper($allogOriginal->ALL_FECHA_DIA);

        // 4. Procesar Detalle (FACART), Stock (ARTICULO) y Lote (LOTE)
        foreach ($detalleOriginal as $item) {
            // Revertir Stock
            $nuevoSignoArm = $item->far_signo_arm * -1;
            $cambioStock = $item->FAR_cantidad * $nuevoSignoArm;
            
            $this->db->table('ARTICULO')
                ->where('ARM_CODART', $item->FAR_CODART)
                ->where('ARM_CODCIA', $item->FAR_CODCIA)
                ->set('arm_stock', "arm_stock + ($cambioStock)", false)
                ->update();

            // Revertir Lote
            $this->db->table('LOTE')
                ->where('LOT_CODART', $item->FAR_CODART)
                ->where('LOT_CODCIA', $item->FAR_CODCIA)
                ->set('LOT_SALDOS', "LOT_SALDOS + ($cambioStock)", false)
                ->update();

            // Insertar registro espejo en FACART
            $itemAnulacion = (array)$item;
            unset($itemAnulacion['FAR_NUMSEC']); 
            $itemAnulacion['far_estado'] = 'E';
            $itemAnulacion['FAR_ESTADO2'] = 'E';
            $itemAnulacion['FAR_NUMOPER'] = $nuevoNumOper;
            $itemAnulacion['far_signo_arm'] = $nuevoSignoArm;
            $itemAnulacion['far_signo_car'] = $item->far_signo_car * -1;
            $itemAnulacion['FAR_FECHA_COMPRA'] = date('Y-m-d'); 
            $itemAnulacion['FAR_HORA'] = date('H:i:s');

            $this->db->table('FACART')->insert($itemAnulacion);

            // 5. Insertar en TABESTADOS (Auditoría de estados)
            $this->db->table('TABESTADOS')->insert([
                'TAE_CODCIA' => $item->FAR_CODCIA,
                'TAE_TIPMOV' => $item->FAR_TIPMOV,
                'TAE_NUMSER' => $item->FAR_NUMSER,
                'TAE_NUMFAC' => $item->FAR_NUMFAC,
                'TAE_NUMSEC' => $item->FAR_NUMSEC, // Usamos la misma secuencia para ligar
                'TAE_FECHA_COMPRA' => date('Y-m-d'),
                'TAE_CODCLIE' => $item->FAR_CODCLIE,
                'TAE_PRECIO' => $item->FAR_PRECIO,
                'TAE_CANTIDAD' => $item->FAR_cantidad,
                'TAE_EQUIV' => $item->FAR_equiv,
                'TAE_DESCRI' => $item->FAR_DESCRI,
                'TAE_COSPRO' => $item->FAR_COSPRO,
                'TAE_CODART' => $item->FAR_CODART,
                'TAE_ESTADO' => 'E',
                'TAE_CODUSU' => $usuario,
                'TAE_HORA' => date('H:i:s')
            ]);
        }

        // 6. Actualizar CARTERA
        $this->db->table('cartera')
            ->where('car_NUMFAC', $allogOriginal->ALL_NUMFAC)
            ->where('car_NUMSER', $allogOriginal->ALL_NUMSER)
            ->where('CAR_CP', $allogOriginal->ALL_CP)
            ->update([
                'CAR_SITUACION' => 'E',
                // No ponemos importe 0 para mantener referencia, pero la situación manda
            ]);

        // 7. Insertar en CARACU
        $this->db->table('CARACU')->insert([
            'CAA_CP' => $allogOriginal->ALL_CP,
            'CAA_CODCLIE' => $allogOriginal->ALL_CODCLIE,
            'CAA_CODCIA' => $allogOriginal->ALL_CODCIA,
            'CAA_TIPDOC' => $allogOriginal->ALL_TIPDOC,
            'CAA_FECHA' => date('Y-m-d'),
            'CAA_NUM_OPER' => $nuevoNumOper,
            'CAA_SERDOC' => $allogOriginal->ALL_SERDOC,
            'CAA_NUMDOC' => $allogOriginal->ALL_NUMDOC,
            'CAA_IMPORTE' => $allogOriginal->ALL_IMPORTE_AMORT * -1,
            'CAA_SALDO' => 0, // En anulación total el saldo de este doc queda en 0
            'CAA_CONCEPTO' => 'ANULACION OPER:' . $numOperOriginal,
            'CAA_SIGNO_CAR' => $allogOriginal->ALL_SIGNO_CAR * -1,
            'CAA_ESTADO' => 'E',
            'CAA_NUMSER' => $allogOriginal->ALL_NUMSER,
            'CAA_NUMFAC' => $allogOriginal->ALL_NUMFAC,
            'CAA_TIPMOV' => $allogOriginal->ALL_TIPMOV,
            'CAA_HORA' => date('H:i:s'),
            'CAA_CODUSU' => $usuario,
            'CAA_CODTRA' => 1111
        ]);

        // 8. Insertar en ALLOG (el registro 1111)
        $allogAnulacion = (array)$allogOriginal;
        $allogAnulacion['ALL_NUMOPER'] = $nuevoNumOper;
        $allogAnulacion['ALL_CODTRA'] = 1111;
        $allogAnulacion['ALL_FLAG_EXT'] = 'E';
        $allogAnulacion['ALL_NUMOPER2'] = $numOperOriginal;
        $allogAnulacion['ALL_FECHA_DIA'] = date('Y-m-d');
        $allogAnulacion['ALL_FECHA_PRO'] = date('Y-m-d');
        $allogAnulacion['ALL_IMPORTE_AMORT'] = $allogOriginal->ALL_IMPORTE_AMORT * -1;
        $allogAnulacion['ALL_SIGNO_CAR'] = $allogOriginal->ALL_SIGNO_CAR * -1;
        $allogAnulacion['ALL_SIGNO_ARM'] = $allogOriginal->ALL_SIGNO_ARM * -1;
        $allogAnulacion['ALL_SIGNO_CAJA'] = $allogOriginal->ALL_SIGNO_CAJA * -1;
        $allogAnulacion['ALL_CODUSU'] = $usuario;
        $allogAnulacion['ALL_HORA'] = date('H:i:s');
        $allogAnulacion['ALL_CONCEPTO'] = 'ANULACION OPER:' . $numOperOriginal;

        $this->db->table('allog')->insert($allogAnulacion);

        // 9. Marcar el registro original como anulado
        $this->db->table('allog')
            ->where('ALL_NUMOPER', $numOperOriginal)
            ->update(['ALL_FLAG_EXT' => 'E']);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return ['status' => false, 'message' => 'Error al procesar la anulación en la base de datos.'];
        }

        return ['status' => true, 'message' => 'Documento anulado correctamente.', 'numOper' => $nuevoNumOper];
    }

    private function obtenerNuevoNumOper($fecha)
    {
        $res = $this->db->table('allog')
            ->selectMax('ALL_NUMOPER')
            ->where('ALL_FECHA_DIA', $fecha)
            ->get()->getRow();
        return ($res->ALL_NUMOPER ?? 0) + 1;
    }
}
