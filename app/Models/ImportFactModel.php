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
        $sql = "SELECT CLI_CODCLIE,CLI_NOMBRE,CLI_AUTO1 FROM clientes WHERE cli_cp = 'P' and cli_ruc_esposo = $ruc";
        $query = $this->db->query($sql);
        return $query->getRow();
    }
    public function listarDocumentos($cliente, $startDate, $endDate)
    {
        $sql = 'SELECT * ';
        $sql .= "FROM dbo.IMPORT_FACT as T1 ";
        $sql .= "LEFT JOIN dbo.clientes as T2 ON(T1.RUC=T2.CLI_RUC_ESPOSO and cli_cp = 'P') ";
        $sql .= "WHERE FECHA BETWEEN '$startDate' AND '$endDate' ";
        $sql .= $cliente?"AND T2.CLI_CODCLIE = $cliente ":"";
        $query = $this->db->query($sql);
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
        $sp = " DECLARE @mensaje varchar(255)
                EXEC @mensaje = [dbo].[sp_crea_compra] 
                @ID_FACT = ?,
                @FAR_CODCLIE = ?,
                @mensaje =  ''
                SELECT @mensaje ";
        $params = [$idfact, $codclie];
        $query = $this->db->query($sp, $params);
        return $query->getResult();
    }
    public function desc_promocion($idfact, $id1, $id2, $cant)
    {
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
        return $query->getResult();
    }
}
