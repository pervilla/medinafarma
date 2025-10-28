<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use CodeIgniter\Model;
use Exception;

/**
 * Description of PreciosModel
 *
 * @author José Luis
 */
class PreciosModel extends Model
{

    var $table = 'precios';
    protected $db;
    protected $dbpm;
    protected $dbjj;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function get_precios($key)
    {
        $sql = 'SELECT PRE_CODCIA,PRE_CODART,PRE_SECUENCIA,PRE_POR1,PRE_POR2,'
            . 'PRE_COSTO,PRE_PRE1,PRE_PRE2,PRE_UNIDAD,PRE_EQUIV,'
            . 'PRE_FLAG_UNIDAD,PRE_COSTO_ANT  ';
        $sql .= "FROM dbo.PRECIOS ";
        $sql .= "WHERE PRE_CODART > " . $key;
        $query = $this->db->query($sql);
        return $query->getResult();
    }

    public function get_precios_edit($key)
    {
        $sql = 'SELECT PRE_CODCIA,PRE_CODART,PRE_SECUENCIA,PRE_POR1,PRE_POR2,';
        $sql .= 'PRE_POR3,PRE_POR4,PRE_POR5,PRE_COSTO,PRE_PRE1,PRE_PRE2,PRE_PRE3,';
        $sql .= 'PRE_PRE4,PRE_PRE5,PRE_UNIDAD,PRE_EQUIV, ';
        $sql .= 'ARM_COSPRO*PRE_EQUIV ARM_COSPRO ';
        $sql .= 'FROM dbo.PRECIOS ';
        $sql .= 'IMNER JOIN dbo.ARTICULO ON(ARM_CODART=PRE_CODART) ';


        $sql .= "WHERE PRE_CODART = $key ";
        $sql .= 'AND PRE_CODCIA = 25;';
        $query = $this->db->query($sql);
        return $query->getResult();
    }
    public function set_precios($cod, $sec, $por1, $por2, $por3, $por4, $por5, $pre1, $pre2, $pre3, $pre4, $pre5)
    {
        try {
            // 🔍 Validación de datos
            if (empty($cod)) {
                return [
                    'success' => false,
                    'message' => 'Código de artículo requerido'
                ];
            }

            if (!is_numeric($sec)) {
                return [
                    'success' => false,
                    'message' => 'Secuencia debe ser numérica'
                ];
            }

            // 🧹 Sanitizar y convertir valores numéricos
            $porcentajes = [
                floatval($por1 ?? 0),
                floatval($por2 ?? 0),
                floatval($por3 ?? 0),
                floatval($por4 ?? 0),
                floatval($por5 ?? 0)
            ];

            $precios = [
                floatval($pre1 ?? 0),
                floatval($pre2 ?? 0),
                floatval($pre3 ?? 0),
                floatval($pre4 ?? 0),
                floatval($pre5 ?? 0)
            ];

            // 🔒 Construir query segura con prepared statements
            $sql = "UPDATE DBO.PRECIOS SET 
                    PRE_POR1 = ?, PRE_POR2 = ?, PRE_POR3 = ?, PRE_POR4 = ?, PRE_POR5 = ?,
                    PRE_PRE1 = ?, PRE_PRE2 = ?, PRE_PRE3 = ?, PRE_PRE4 = ?, PRE_PRE5 = ?
                    WHERE PRE_CODART = ? AND PRE_SECUENCIA = ?";

            // Preparar parámetros
            $params = array_merge($porcentajes, $precios, [$cod, intval($sec)]);

            // 🚀 Ejecutar query preparada
            $query = $this->db->query($sql, $params);

            if ($query === false) {
                // Obtener error específico de la base de datos
                $error = $this->db->error();

                return [
                    'success' => false,
                    'message' => 'Error al actualizar en base de datos',
                    'db_error' => $error['message'] ?? 'Error desconocido'
                ];
            }

            // 📊 Verificar si se actualizó algún registro
            $affected_rows = $this->db->affectedRows();

            if ($affected_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'No se encontró el registro para actualizar',
                    'codigo' => $cod,
                    'secuencia' => $sec
                ];
            }

            return [
                'success' => true,
                'message' => 'Precios actualizados correctamente',
                'affected_rows' => $affected_rows,
                'codigo' => $cod,
                'secuencia' => $sec
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en modelo: ' . $e->getMessage(),
                'codigo' => $cod ?? 'N/A',
                'secuencia' => $sec ?? 'N/A'
            ];
        }
    }

    public function get_equiv($key)
    {
        $sql = 'SELECT CAST(PRE_EQUIV AS INT) PRE_EQUIV,RTRIM(PRE_UNIDAD) PRE_UNIDAD ';
        $sql .= "FROM dbo.PRECIOS ";
        $sql .= "WHERE PRE_CODART = " . $key;

        $query = $this->db->query($sql);
        return $query->getResult();
    }

    public function aplicarPreciosTemporales(float $porcentaje): array
    {
        $query = $this->db->query(
            "EXEC sp_GestionPreciosTemporales @Accion = 'APLICAR', @PorcentajeGanancia = ?",
            [$porcentaje]
        );

        $result = $query->getRow();
        return [
            'filas_afectadas' => $result->FilasAfectadas ?? 0,
            'porcentaje' => $porcentaje
        ];
    }

    /**
     * Elimina los precios temporales
     */
    public function eliminarPreciosTemporales(): array
    {
        $query = $this->db->query(
            "EXEC sp_GestionPreciosTemporales @Accion = 'ELIMINAR'"
        );

        $result = $query->getRow();
        return [
            'filas_afectadas' => $result->FilasAfectadas ?? 0
        ];
    }
}
