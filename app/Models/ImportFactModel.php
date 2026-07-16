<?php

namespace App\Models;

use CodeIgniter\Model;

class ImportFactModel extends Model
{

    var $table = 'import_fact';
    protected $db;
    protected $dbpm;
    protected $dbjj;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->dbpm = \Config\Database::connect('pmeza');
        $this->dbjj = \Config\Database::connect('juanjuicillo');
    }

    public function getClient($ruc)
    {
        $sql = "SELECT CLI_CODCLIE,CLI_NOMBRE,CLI_AUTO1 FROM clientes WHERE cli_cp = 'P' and cli_ruc_esposo = ?";
        $query = $this->db->query($sql, [$ruc]);
        return $query->getRow();
    }
    public function listarDocumentos($cliente, $startDate, $endDate, $tipoDoc = null, $estadoDoc = null)
    {
        $params = [];
        $sql = 'SELECT * ';
        $sql .= "FROM dbo.IMPORT_FACT as T1 ";
        $sql .= "LEFT JOIN dbo.clientes as T2 ON(T1.RUC=T2.CLI_RUC_ESPOSO and cli_cp = 'P') ";
        $sql .= "WHERE FECHA BETWEEN ? AND ? ";
        $params[] = $startDate;
        $params[] = $endDate;
        if ($cliente) {
            $sql .= "AND T2.CLI_CODCLIE = ? ";
            $params[] = $cliente;
        }
        if ($tipoDoc !== null && $tipoDoc !== '') {
            $sql .= "AND T1.codCpe = ? ";
            $params[] = $tipoDoc;
        }
        if ($estadoDoc !== null && $estadoDoc !== '') {
            $sql .= "AND T1.ESTADO = ? ";
            $params[] = $estadoDoc;
        }
        $query = $this->db->query($sql, $params);
        return $query->getResult();
    }
    public function listarDetalleDocumentos($id)
    {
        $sql = 'SELECT DISTINCT t1.*,T2.ART_NOMBRE,T3.ARM_COSPRO,T2.ART_SITUACION,';
        $sql .= 'CASE WHEN EXISTS(SELECT 1 FROM dbo.PRECIOS T5 INNER JOIN dbo.IMPORT_FACT T4 ON T1.IDFACT=T4.ID INNER JOIN dbo.IMPORT_ART T6 ON T1.COD_PROD=T6.COD_PROD AND T6.CLI_CODCLIE=T4.CLI_CODCLI WHERE T5.PRE_CODART=T1.ART_KEY AND T5.PRE_EQUIV=T6.FAR_EQUIV) THEN 1 ELSE 0 END AS EQUIV_EXISTE ';
        $sql .= "FROM dbo.IMPORT_FACT_DET as T1 ";
        $sql .= "LEFT JOIN dbo.ARTI as T2 ON(T1.ART_KEY=T2.ART_KEY) ";
        $sql .= "LEFT JOIN dbo.ARTICULO as T3 ON(T1.ART_KEY=T3.ARM_CODART) ";
        $sql .= "WHERE IDFACT = $id";
        $query = $this->db->query($sql);
        return $query->getResult();
    }
    public function promediar_costos($id, $ids)
    {
        $sql = 'SELECT SUM(PRECIO*CANTIDAD) TOTAL, SUM(CANTIDAD) CANTIDAD ';
        $sql .= "FROM dbo.IMPORT_FACT_DET ";
        $sql .= "WHERE IDFACT = $id AND ID IN ($ids)";
        $query = $this->db->query($sql);
        return $query->getRow();
    }
    public function excluir_productos($id, $ids)
    {
        $sql = "UPDATE dbo.IMPORT_FACT_DET ";
        $sql .= "SET ESTADO = 0 ";
        $sql .= "WHERE IDFACT = $id AND ID IN ($ids)";
        $query = $this->db->simpleQuery($sql);
        return $query;
    }
    public function actualizar_total($id, $nuevo_total)
    {
        if (empty($id) || !is_numeric($id)) {
            throw new \InvalidArgumentException('El ID del documento es inválido.');
        }
        if (!is_numeric($nuevo_total)) {
            throw new \InvalidArgumentException('El nuevo total debe ser un número.');
        }
        $this->db->table('IMPORT_FACT')
            ->where('ID', $id)
            ->update(['TOTAL' => $nuevo_total]);
        return $this->db->affectedRows() > 0;
    }
    public function distribuir_monto($id_factura, $monto)
    {
        if (!is_numeric($monto) || !is_numeric($id_factura)) {
            throw new \InvalidArgumentException('El monto y el ID de la factura deben ser números.');
        }
        $items = $this->db->table('IMPORT_FACT_DET')
            ->select('ID, CANTIDAD, TOTAL_SIST')
            ->where('IDFACT', $id_factura)
            ->get()
            ->getResult();
        if (empty($items)) {
            throw new \RuntimeException('No se encontraron ítems para la factura especificada.');
        }
        $total_factura = array_sum(array_column($items, 'TOTAL_SIST'));
        if ($total_factura <= 0) {
            throw new \RuntimeException('El total de la factura debe ser mayor a 0.');
        }
        $factor = $monto / $total_factura;
        foreach ($items as $item) {
            $nuevo_total = $item->TOTAL_SIST + ($item->TOTAL_SIST * $factor);
            $nuevo_precio = $nuevo_total / $item->CANTIDAD;
            if ($item->CANTIDAD <= 0) {
                throw new \RuntimeException('La cantidad de un ítem no puede ser 0 o negativa.');
            }
            $this->db->table('IMPORT_FACT_DET')
                ->where('ID', $item->ID)
                ->where('IDFACT', $id_factura)
                ->update([
                    'TOTAL_SIST' => $nuevo_total,
                    'PRECIO' => $nuevo_precio
                ]);
        }
        return true;
    }

    public function eliminar_items($id, $ids)
    {
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        $ids = array_map('intval', $ids);
        return $this->db->table('IMPORT_FACT_DET')
            ->where('IDFACT', $id)
            ->whereIn('ID', $ids)
            ->delete();
    }
    public function crear_lista($data)
    {
        // Verificar si 'comprobantes' existe y es un array
        if (!isset($data->data->comprobantes) || !is_array($data->data->comprobantes)) {
            return false;
        }

        foreach ($data->data->comprobantes as $comprobante) {
            // Verificar si existen las propiedades necesarias
            if (
                !isset(
                    $comprobante->datosEmisor->numRuc,
                    $comprobante->numSerie,
                    $comprobante->numCpe
                )
            ) {
                continue; // Saltar comprobantes con datos incompletos
            }

            $ruc = $comprobante->datosEmisor->numRuc;
            $razonSocial = substr((string)$comprobante->datosEmisor->desRazonSocialEmis, 0, 50);
            $nroFactura = $comprobante->numSerie . '-' . $comprobante->numCpe;
            $fecha = isset($comprobante->fecEmision) ? date('Y-m-d', strtotime(str_replace('/', '-', $comprobante->fecEmision))) : null;
            $estadoCpe = $comprobante->indEstadoCpe ?? null;
            $procedencia = $comprobante->indProcedencia ?? null;
            $cpeRel = $comprobante->numCpeRel ?? null;
            $codCpe = $comprobante->codCpe ?? null;

            // Preparar los datos para insertar o actualizar
            $facturaData = [
                'RUC' => $ruc,
                'desRazonSocialEmis' => $razonSocial,
                'NRO_FACTURA' => $nroFactura,
                'FECHA' => $fecha,
                'indEstadoCpe' => $estadoCpe,
                'indProcedencia' => $procedencia,
                'numCpeRel' => $cpeRel,
                'codCpe' => $codCpe,
                'ESTADO' => 10,
                // Agregar más campos si es necesario
            ];

            // Llamar a crear_factura para insertar o actualizar
            $this->crear_factura($facturaData);
        }

        return true;
    }
    public function crear_lista_sire($data)
{
$data = $data->data; // Verificar si 'registros' existe y es un array
    if (!isset($data->registros) || !is_array($data->registros)) {
        return false;
    }
    
    foreach ($data->registros as $registro) {
        // Verificar si existen las propiedades necesarias
        if (
            !isset(
                $registro->numDocIdentidadProveedor,
                $registro->numSerieCDP,
                $registro->numCDP
            )
        ) {
            continue; // Saltar registros con datos incompletos
        }

        $ruc = $registro->numDocIdentidadProveedor;
        $razonSocial = substr((string)$registro->nomRazonSocialProveedor, 0, 50); // Limitar a 50 caracteres
        $nroFactura = $registro->numSerieCDP . '-' . $registro->numCDP;
        $fecha = $registro->fecEmision; // La fecha ya está en formato Y-m-d
        $estadoCpe = $registro->codEstadoComprobante ?? null;
        $procedencia = $registro->indFuenteCP ?? null; // Cambiado a indFuenteCP
        $cpeRel = $registro->numCDPRangoFinal ?? null; // Cambiado a numCDPRangoFinal
        $codCpe = $registro->codTipoCDP ?? null; // Cambiado a codTipoCDP
        $total = $registro->montos->mtoTotalCp;
        // Preparar los datos para insertar o actualizar
        $facturaData = [
            'RUC' => $ruc,
            'desRazonSocialEmis' => $razonSocial,
            'NRO_FACTURA' => $nroFactura,
            'FECHA' => $fecha,
            'indEstadoCpe' => $estadoCpe,
            'indProcedencia' => $procedencia,
            'numCpeRel' => $cpeRel,
            'codCpe' => $codCpe,           
            'TOTAL' => $total,
            // Agregar más campos si es necesario
        ];
//var_export($facturaData); die();
        // Llamar a crear_factura para insertar o actualizar
        $this->crear_factura($facturaData);
    }

    return true;
}
    public function crear_factura($data)
    {
        // Calcular VENCIMIENTO basado en CLI_AUTO1 del cliente
        $cliente = $this->db->table('clientes')
            ->select('CLI_AUTO1')
            ->where('CLI_RUC_ESPOSO', $data['RUC'])
            ->where('cli_cp', 'P')
            ->get()
            ->getRow();

        $diasCredito = ($cliente && is_numeric($cliente->CLI_AUTO1)) ? (int)$cliente->CLI_AUTO1 : 0;
        
        if (!empty($data['FECHA'])) {
            $fechaEmision = new \DateTime($data['FECHA']);
            $fechaEmision->modify("+$diasCredito days");
            $data['VENCIMIENTO'] = $fechaEmision->format('Y-m-d');
        }

        // Buscar el registro existente
        $registroExistente = $this->db->table('IMPORT_FACT')
            ->where('RUC', $data['RUC'])
            ->where('NRO_FACTURA', $data['NRO_FACTURA'])
            ->get()
            ->getRow();

        if ($registroExistente) {
            // Si existe, actualizar el registro
            $this->db->table('IMPORT_FACT')
                ->where('RUC', $data['RUC'])
                ->where('NRO_FACTURA', $data['NRO_FACTURA'])
                ->update($data);

            // Retorna el ID del registro actualizado
            return $registroExistente->ID; // Usamos el campo "ID" de la tabla
        } else {
            // Si no existe, insertar nuevo registro
            $this->db->table('IMPORT_FACT')->insert($data);
            return $this->db->insertID(); // Retorna el ID del nuevo registro
        }
    }
    public function crear_factura_bkp($data)
    {
        $this->db->table('IMPORT_FACT')->insert($data);
        return $this->db->insertID();
    }
    public function crear_factura_detalle($data): array|bool|int
    {
        $res =  $this->db->table('IMPORT_FACT_DET')->insertBatch($data);
        $this->actualiza_productos();
        
        // Aplicar reglas de extracción automáticamente si hay datos
        if ($res && !empty($data) && isset($data[0]['IDFACT'])) {
            $this->aplicarReglasExtraccion($data[0]['IDFACT']);
        }
        
        return $res;
    }
    public function update_producto($data)
    {
        $builder = $this->db->table('IMPORT_ART');
        $clicod = $data['CLI_CODCLIE'];
        $codprd = $data['COD_PROD'];
        $artkey = $data['ART_KEY'];

        // Comprobar si el registro ya existe
        $exists = $builder->where('CLI_CODCLIE', $clicod)
            ->where('COD_PROD', $codprd)
            ->countAllResults();

        if ($exists > 0) {
            // Actualizar si existe
            $builder->where('CLI_CODCLIE', $clicod)
                ->where('COD_PROD', $codprd)
                ->update($data);
        } else {
            // Insertar si no existe
            $builder->insert($data);
        }

        // Actualizar equivalencias y productos
        $this->actualiza_equiv_prod($artkey);
        $this->actualiza_productos();

        return true; // Retorna true al completar
    }

    public function actualiza_productos()
    {
        
        $sql= "UPDATE FD
                SET 
                FD.ART_KEY = IA.ART_KEY,
                FD.FAR_EQUIV = IA.FAR_EQUIV,
                FD.PRECIO = CASE 
                            WHEN FD.CANTIDAD_INI = 0 OR IA.FACTOR = 0 THEN 0 
                            ELSE (TOTAL_SIST/FD.CANTIDAD_INI)/IA.FACTOR 
                            END,
                FD.CANTIDAD = FD.CANTIDAD_INI * IA.FACTOR
                FROM 
                DBO.IMPORT_FACT AS IFA 
                INNER JOIN dbo.IMPORT_FACT_DET as FD ON(FD.IDFACT=IFA.ID AND IFA.ESTADO=0)
                INNER JOIN dbo.IMPORT_ART AS IA ON(FD.COD_PROD=IA.COD_PROD AND IA.CLI_CODCLIE=IFA.CLI_CODCLI)";
        $this->db->simpleQuery($sql);
    }
    public function actualiza_equiv_prod($artkey)
    {
        $sql = "UPDATE A 
                SET 
                A.FAR_EQUIV = CASE When PRE_UNIDAD LIKE 'DOC%' Then 1 ELSE PRE_EQUIV END,
                A.FACTOR =1
                FROM 
                dbo.PRECIOS AS P
                INNER JOIN DBO.IMPORT_ART AS A ON(P.PRE_CODART=A.ART_KEY)
                WHERE P.PRE_FLAG_UNIDAD = 'A' and P.PRE_CODART=$artkey ";
        $this->db->simpleQuery($sql);
    }
    public function actualiza_item_fact($id, $idfact, $precio)
    {
        $sql = "UPDATE 
                dbo.IMPORT_FACT_DET  
                SET 
                PRECIO = $precio,
                TOTAL_SIST = CANTIDAD*$precio
                WHERE  
                ID IN ($idfact) and
                IDFACT = $id ";
        return  $this->db->simpleQuery($sql);
    }

    public function actualiza_item_art($codclie, $artkey, $equiv, $factr)
    {
        $sql = "UPDATE 
                dbo.IMPORT_ART  
                SET 
                FAR_EQUIV=$equiv,
                FACTOR=$factr
                WHERE  
                CLI_CODCLIE = $codclie and
                ART_KEY = $artkey ";
        // echo $sql; die();
        $result = $this->db->simpleQuery($sql);
        $this->actualiza_productos();
        return $result;
    }

    public function crea_compra($idfact, $codclie)
    {
        try {
            // Usar una tabla temporal para capturar el OUTPUT
            $sql = "DECLARE @mensaje_out varchar(255);
                    
                    EXEC [dbo].[sp_crea_compra] 
                        @ID_FACT = ?,
                        @FAR_CODCLIE = ?,
                        @mensaje = @mensaje_out OUTPUT;
                    
                    SELECT @mensaje_out as mensaje;";
            
            $query = $this->db->query($sql, [$idfact, $codclie]);
            
            if (!$query) {
                $error = $this->db->error();
                log_message('critical', 'Error DB en crea_compra: ' . print_r($error, true));
                return [(object)['mensaje' => 'Error de base de datos']];
            }
            
            // Intentar obtener el resultado
            $result = $query->getRow();
            log_message('info', 'Resultado SP crea_compra: ' . print_r($result, true));
            
            if ($result && isset($result->mensaje)) {
                log_message('info', 'Compra creada exitosamente: ' . $result->mensaje);
                return [(object)['mensaje' => $result->mensaje]];
            }
            
            // Fallback: intentar como array
            $resultArray = $query->getResultArray();
            if (!empty($resultArray) && isset($resultArray[0]['mensaje'])) {
                return [(object)['mensaje' => $resultArray[0]['mensaje']]];
            }
            
            log_message('warning', 'SP ejecutado pero sin mensaje de retorno');
            return [(object)['mensaje' => 'Compra procesada sin confirmación']];
            
        } catch (\Exception $e) {
            log_message('critical', 'Excepción en crea_compra: ' . $e->getMessage());
            return [(object)['mensaje' => 'Error: ' . $e->getMessage()]];
        }
    }
    public function desc_promocion($idfact, $id1, $id2, $cant)
    {
        $cant = (is_numeric($cant) && $cant !== '') ? floatval($cant) : 0;
        $sp = " DECLARE @COSPRO NUMERIC(11,4)
                EXEC @COSPRO = [dbo].[sp_actualizar_costo_bonificacion] 
                @ID_1 = ?,
                @ID_2 = ?,
                @IDFACT = ?,
                @CANTIDA = ?,
                @PAQUETE =  0,
                @COSPRO =  0
                SELECT @COSPRO ";
        $params = [$id1, $id2, $idfact, $cant];
        $query = $this->db->query($sp, $params);
    }

    public function eliminar_items_import($id)
    {
        $this->db->transStart();
        
        // 1. Eliminar contenido en IMPORT_FACT_DET
        $this->db->table('IMPORT_FACT_DET')->where('IDFACT', $id)->delete();
        
        // 2. Cambiar ESTADO=10 si es que su ESTADO=0
        $this->db->table('IMPORT_FACT')
            ->where('ID', $id)
            ->where('ESTADO', 0)
            ->update(['ESTADO' => 10]);
            
        $this->db->transComplete();
        return $this->db->transStatus();
    }

    public function check_comprobante_existente($ruc, $nro_factura)
    {
        $parts = explode('-', $nro_factura);
        if (count($parts) < 2) return false;
        
        // Extraer solo la parte numérica de la serie (ej: F001 -> 1)
        $serie = trim($parts[0]);
        $serie_num = intval(preg_replace('/[^0-9]/', '', $serie));
        
        // Número de factura (ej: 270313 -> 270313)
        $numero = intval($parts[1]);

        return $this->db->table('allog')
            ->where('ALL_NUMSER_C', (string)$serie_num)
            ->where('ALL_NUMFAC_C', $numero)
            ->where('ALL_RUC', (string)$ruc)
            ->where('ALL_TIPMOV', 20) // Compras
            ->where('ALL_FLAG_EXT <>', 'E')
            ->countAllResults() > 0;
    }

    public function actualizarEstadoProcesado($id)
    {
        return $this->db->table('IMPORT_FACT')
            ->where('ID', $id)
            ->update(['ESTADO' => 1]);
    }

    public function verificar_productos_inactivos($idfact)
    {
        return $this->db->table('IMPORT_FACT_DET as FD')
            ->select('FD.DES_PROD')
            ->join('ARTI as A', 'FD.ART_KEY = A.ART_KEY')
            ->where('FD.IDFACT', $idfact)
            ->where('A.ART_SITUACION', 1)
            ->get()
            ->getResult();
    }

    public function verificar_unidades_validas($idfact)
    {
        $sql = "SELECT FD.DES_PROD 
                FROM IMPORT_FACT_DET FD
                WHERE FD.IDFACT = ? 
                AND NOT EXISTS (
                    SELECT 1 FROM PRECIOS P 
                    WHERE P.PRE_CODART = FD.ART_KEY 
                    AND P.PRE_EQUIV = FD.FAR_EQUIV
                )";
        return $this->db->query($sql, [$idfact])->getResult();
    }

    // =============================================
    // MÉTODOS PARA RELACIONAR NOTAS DE CRÉDITO
    // =============================================

    /**
     * Obtiene información de relación entre una NC y su comprobante original,
     * o entre un comprobante y sus NCs asociadas.
     */
    public function getRelacionNotaCredito($id)
    {
        $doc = $this->db->table('IMPORT_FACT')->where('ID', $id)->get()->getRow();
        if (!$doc) return null;

        if ($doc->codCpe == '07') {
            // Es una NC: buscar el comprobante que referencia
            if (empty($doc->numCpeRel)) {
                return ['tipo' => 'nc_sin_ref', 'numCpeRel' => null, 'mensaje' => 'Sin referencia'];
            }

            $numRef = trim($doc->numCpeRel);

            // Intentar match exacto (numCpeRel contiene el NRO_FACTURA completo)
            $ref = $this->db->table('IMPORT_FACT')
                ->where('NRO_FACTURA', $numRef)
                ->get()
                ->getRow();

            // Si no, intentar por ALL_NUMFACT (numCpeRel es solo el correlativo)
            if (!$ref && is_numeric($numRef)) {
                $ref = $this->db->table('IMPORT_FACT')
                    ->where('ALL_NUMFACT', (int)$numRef)
                    ->get()
                    ->getRow();
            }

            // Si no, LIKE
            if (!$ref) {
                $ref = $this->db->table('IMPORT_FACT')
                    ->like('NRO_FACTURA', $numRef)
                    ->get()
                    ->getRow();
            }

            return [
                'tipo' => 'nc',
                'numCpeRel' => $doc->numCpeRel,
                'ref_encontrada' => $ref ? true : false,
                'ref_nro' => $ref ? $ref->NRO_FACTURA : null,
                'ref_id' => $ref ? $ref->ID : null,
                'ref_estado' => $ref ? $ref->ESTADO : null
            ];
        }

        // Es factura/boleta: buscar NCs que la referencien
        $parts = explode('-', $doc->NRO_FACTURA);
        $numero = trim(end($parts));

        $ncs = [];
        // numCpeRel es columna INT, solo comparar con el correlativo numérico
        if (is_numeric($numero)) {
            $ncs = $this->db->table('IMPORT_FACT')
                ->where('codCpe', '07')
                ->where('numCpeRel', (int)$numero)
                ->get()
                ->getResult();
        }

        if (!empty($ncs)) {
            $ncList = array_map(function ($nc) {
                return $nc->NRO_FACTURA;
            }, $ncs);
            return [
                'tipo' => 'factura',
                'tiene_nc' => true,
                'notas_credito' => $ncList,
                'nc_count' => count($ncs),
                'nc_ids' => array_map(function ($nc) {
                    return $nc->ID;
                }, $ncs)
            ];
        }

        return null;
    }

    /**
     * Actualiza la referencia de una NC con los datos obtenidos de SUNAT.
     */
    public function actualizarReferenciaNC($id, $numCpeRel)
    {
        return $this->db->table('IMPORT_FACT')
            ->where('ID', $id)
            ->update(['numCpeRel' => $numCpeRel]);
    }

    /**
     * Obtiene la información del comprobante original referenciado por una NC
     * para mostrarlo en el detalle (incluye datos del proveedor).
     */
    public function getDocumentoReferenciado($numCpeRel, $ruc)
    {
        if (empty($numCpeRel)) return null;

        $sql = "SELECT T1.*, T2.CLI_NOMBRE
                FROM dbo.IMPORT_FACT T1
                LEFT JOIN dbo.clientes T2 ON (T1.RUC = T2.CLI_RUC_ESPOSO AND T2.cli_cp = 'P')
                WHERE T1.NRO_FACTURA = ? AND T1.RUC = ?";
        $query = $this->db->query($sql, [$numCpeRel, $ruc]);
        $result = $query->getRow();
        if ($result) return $result;

        // Fallback: solo por NRO_FACTURA
        $sql2 = "SELECT T1.*, T2.CLI_NOMBRE
                FROM dbo.IMPORT_FACT T1
                LEFT JOIN dbo.clientes T2 ON (T1.RUC = T2.CLI_RUC_ESPOSO AND T2.cli_cp = 'P')
                WHERE T1.NRO_FACTURA = ?";
        $query2 = $this->db->query($sql2, [$numCpeRel]);
        return $query2->getRow();
    }

    // =============================================
    // MÉTODOS PARA F-BMF-10 RECEPCIÓN DE PRODUCTOS
    // =============================================

    /**
     * Lista facturas con estado=0 (compra ingresada o con detalle) para la vista de recepción,
     * indicando si ya están incluidas en un reporte activo.
     */
    public function listarDocumentosParaRecepcion($cliente, $startDate, $endDate)
    {
        $params = [];
        $sql = "SELECT T1.ID, T1.RUC, T1.NRO_FACTURA, T1.FECHA, T1.TOTAL, T1.CLI_CODCLI,
                       T2.CLI_CODCLIE, T2.CLI_NOMBRE,
                       T1.desRazonSocialEmis,
                       CASE WHEN RD.ID IS NOT NULL THEN 1 ELSE 0 END AS EN_REPORTE,
                       RD.ID_REPORTE
                FROM dbo.IMPORT_FACT AS T1
                LEFT JOIN dbo.clientes AS T2 ON (T1.RUC = T2.CLI_RUC_ESPOSO AND T2.cli_cp = 'P')
                LEFT JOIN dbo.RECEPCION_REPORTE_DET AS RD ON (T1.ID = RD.ID_FACT)
                LEFT JOIN dbo.RECEPCION_REPORTE AS RR ON (RD.ID_REPORTE = RR.ID AND RR.ESTADO = 1)
                WHERE T1.ESTADO in(0,1)
                AND T1.FECHA BETWEEN ? AND ? ";
        $params[] = $startDate;
        $params[] = $endDate;
        if ($cliente) {
            $sql .= "AND T2.CLI_CODCLIE = ? ";
            $params[] = $cliente;
        }
        $sql .= "ORDER BY T1.FECHA DESC, T1.ID DESC";
        $query = $this->db->query($sql, $params);
        return $query->getResult();
    }

    /**
     * Obtiene los productos de múltiples facturas para generar el PDF del reporte.
     */
    public function getDetalleProductosFacturas($idsFact)
    {
        if (empty($idsFact)) return [];
        
        $idsStr = implode(',', array_map('intval', $idsFact));
        
        $sql = "SELECT FD.IDFACT, FD.DES_PROD, FD.CANTIDAD, FD.CANTIDAD_INI, FD.LOTE, FD.VENCIMIENTO,
                       A.ART_NOMBRE,
                       F.NRO_FACTURA, F.RUC, F.desRazonSocialEmis, F.FECHA,
                       C.CLI_NOMBRE
                FROM dbo.IMPORT_FACT_DET AS FD
                INNER JOIN dbo.IMPORT_FACT AS F ON (FD.IDFACT = F.ID)
                LEFT JOIN dbo.ARTI AS A ON (FD.ART_KEY = A.ART_KEY)
                LEFT JOIN dbo.clientes AS C ON (F.RUC = C.CLI_RUC_ESPOSO AND C.cli_cp = 'P')
                WHERE FD.IDFACT IN ($idsStr)
                ORDER BY F.NRO_FACTURA, FD.ID";
        $query = $this->db->query($sql);
        return $query->getResult();
    }

    /**
     * Crea un nuevo reporte de recepción y registra las facturas asociadas.
     */
    public function crearReporteRecepcion($data, $facturas)
    {
        $this->db->transStart();

        $this->db->table('RECEPCION_REPORTE')->insert($data);
        $idReporte = $this->db->insertID();

        if ($idReporte && !empty($facturas)) {
            $detalles = [];
            foreach ($facturas as $fact) {
                $detalles[] = [
                    'ID_REPORTE' => $idReporte,
                    'ID_FACT' => $fact['id'],
                    'NRO_FACTURA' => $fact['nro_factura']
                ];
            }
            $this->db->table('RECEPCION_REPORTE_DET')->insertBatch($detalles);
        }

        $this->db->transComplete();

        if ($this->db->transStatus()) {
            return $idReporte;
        }
        return false;
    }

    /**
     * Lista todos los reportes de recepción generados.
     */
    public function listarReportesGenerados()
    {
        $sql = "SELECT R.ID, R.CLI_CODCLIE, R.RUC, R.RAZON_SOCIAL, 
                       R.FECHA_RECEPCION, R.FECHA_GENERACION, R.USUARIO, R.ESTADO,
                       STUFF((SELECT ', ' + RD.NRO_FACTURA 
                        FROM RECEPCION_REPORTE_DET RD 
                        WHERE RD.ID_REPORTE = R.ID 
                        FOR XML PATH('')), 1, 2, '') AS FACTURAS
                FROM dbo.RECEPCION_REPORTE R
                ORDER BY R.ID DESC";
        $query = $this->db->query($sql);
        return $query->getResult();
    }

    /**
     * Anula un reporte de recepción, liberando las facturas.
     */
    public function anularReporte($id)
    {
        return $this->db->table('RECEPCION_REPORTE')
            ->where('ID', $id)
            ->update(['ESTADO' => 0]);
    }

    /**
     * Obtiene los datos completos de un reporte (cabecera + facturas + productos) 
     * para regenerar el PDF.
     */
    public function getReporteCompleto($idReporte)
    {
        // Cabecera del reporte
        $reporte = $this->db->table('RECEPCION_REPORTE')
            ->where('ID', $idReporte)
            ->get()->getRow();

        if (!$reporte) return null;

        // Obtener IDs de facturas del reporte
        $detalles = $this->db->table('RECEPCION_REPORTE_DET')
            ->where('ID_REPORTE', $idReporte)
            ->get()->getResult();

        $idsFact = array_column($detalles, 'ID_FACT');

        // Obtener productos
        $productos = $this->getDetalleProductosFacturas($idsFact);

        return [
            'reporte' => $reporte,
            'facturas' => $detalles,
            'productos' => $productos
        ];
    }

    /**
     * Obtiene las reglas de extracción aplicables para un RUC o código de cliente.
     */
    public function getReglasExtraccion($ruc = null, $cli_codcli = null)
    {
        $builder = $this->db->table('IMPORT_EXTRACCION_REGLAS')
            ->where('ESTADO', 1);

        if ($ruc || $cli_codcli) {
            $builder->groupStart();
            if ($ruc) {
                $builder->orWhere('RUC', $ruc);
            }
            if ($cli_codcli) {
                $builder->orWhere('CLI_CODCLI', $cli_codcli);
            }
            $builder->groupEnd();
        }

        return $builder->get()->getResult();
    }

    /**
     * Aplica las reglas de extracción a todos los ítems de una factura.
     */
    public function aplicarReglasExtraccion($idfact)
    {
        // 1. Obtener datos de la factura (RUC, CLI_CODCLI)
        $factura = $this->db->table('IMPORT_FACT')
            ->where('ID', $idfact)
            ->get()
            ->getRow();

        if (!$factura) {
            return ['status' => 'error', 'message' => 'Factura no encontrada'];
        }

        // 2. Obtener reglas aplicables
        $reglas = $this->getReglasExtraccion($factura->RUC, $factura->CLI_CODCLI);
        if (empty($reglas)) {
            return ['status' => 'warning', 'message' => 'No hay reglas configuradas para este proveedor/cliente'];
        }

        // 3. Obtener ítems de la factura
        $items = $this->db->table('IMPORT_FACT_DET')
            ->where('IDFACT', $idfact)
            ->get()
            ->getResult();

        $count = 0;
        foreach ($items as $item) {
            $lote = null;
            $vencimiento = null;
            $matched = false;

            foreach ($reglas as $regla) {
                // Extraer Lote
                if (!empty($regla->REGEX_LOTE)) {
                    // Usar @ como delimitador para evitar conflictos con / en regex
                    if (@preg_match('@' . $regla->REGEX_LOTE . '@i', $item->DES_PROD, $matches)) {
                        $lote = isset($matches[1]) ? trim($matches[1]) : null;
                        $matched = true;
                    }
                }

                // Extraer Vencimiento
                if (!empty($regla->REGEX_VENCIMIENTO)) {
                    if (@preg_match('@' . $regla->REGEX_VENCIMIENTO . '@i', $item->DES_PROD, $matches)) {
                        $vencimiento = isset($matches[1]) ? trim($matches[1]) : null;
                        $matched = true;
                    }
                }

                if ($matched) break; // Usar la primera regla que coincida
            }

            if ($matched) {
                $this->db->table('IMPORT_FACT_DET')
                    ->where('ID', $item->ID)
                    ->where('IDFACT', $idfact)
                    ->update([
                        'LOTE' => $lote,
                        'VENCIMIENTO' => $vencimiento
                    ]);
                $count++;
            }
        }

        return [
            'status' => 'success',
            'message' => "Se procesaron $count ítems correctamente.",
            'count' => $count
        ];
    }
}
