<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Anulación de documentos de venta replicando formgen VB6 LK_CODTRA = 1111.
 *
 * Flujo (ANEXO_CON21 + ACT1/ACT7/ACT8/ACT13 + registra_caa + EXTORNA_LOTE):
 *  1. Carga el documento original de ALLOG.
 *  2. Invierte los signos CAR/ARM/CAJA/CCM.
 *  3. Inserta el espejo en ALLOG con ALL_CODTRA = 1111 y ALL_FLAG_EXT = 'E'.
 *  4. Inserta el espejo en FACART (fila por fila) con FAR_ESTADO = 'E'.
 *  5. Revierte stock (ARTICULO) y lotes (LOTE).
 *  6. Si es al crédito: ajusta CARTERA, CLIENTES e inserta CARACU.
 *  7. Marca el documento original como extornado.
 */
class AnulacionModel extends Model
{
    /** @var \CodeIgniter\Database\BaseConnection */
    protected $db;
    /** @var \CodeIgniter\Database\BaseConnection */
    protected $dbpm;
    /** @var \CodeIgniter\Database\BaseConnection */
    protected $dbjj;

    public function __construct()
    {
        parent::__construct();
        $this->db   = \Config\Database::connect();
        $this->dbpm = \Config\Database::connect('pmeza');
        $this->dbjj = \Config\Database::connect('juanjuicillo');
    }

    /**
     * @param int    $numOper    ALL_NUMOPER del documento original
     * @param string $fechaDia   Fecha del documento en formato Y-m-d o d/m/Y
     * @param int    $server     1=Centro, 2=Juanjuicillo, 3=Peñameza
     * @param string $usuario    Usuario que anula (ALL_CODUSU)
     * @param string $comentario Comentario de auditoría
     * @param bool   $dryRun     true = ejecuta y hace rollback (solo pruebas)
     */
    public function anularDocumento($numOper, $fechaDia, $server, $usuario, $comentario, $dryRun = false)
    {
        $db = $this->dbFor($server);

        $numOper = (int) $numOper;
        $usuario = substr(trim((string) $usuario), 0, 10);
        if ($usuario === '') {
            $usuario = 'ADMIN';
        }
        $comentario = substr(trim((string) $comentario), 0, 150);
        if ($comentario === '') {
            $comentario = 'Anulado desde web';
        }

        // Fecha determinística (servidor en Español: siempre usar estilo 23)
        $fechaSql = $this->normalizarFecha($fechaDia);
        if ($fechaSql === '') {
            return ['status' => false, 'message' => 'Fecha de operación inválida.'];
        }

        $db->transBegin();

        try {
            // 1. Documento original -------------------------------------------------
            $orig = $db->query(
                "SELECT TOP 1 * FROM ALLOG
                 WHERE ALL_CODCIA = '25'
                   AND ALL_NUMOPER = ?
                   AND ALL_FECHA_DIA = CONVERT(date, ?, 23)
                   AND ALL_TIPMOV = 10
                 ORDER BY ALL_CODTRA",
                [$numOper, $fechaSql]
            )->getRowArray();

            if (!$orig) {
                throw new \RuntimeException('Operación no encontrada.');
            }

            $flagExt = trim((string) $orig['ALL_FLAG_EXT']);
            if ($flagExt === 'E') {
                throw new \RuntimeException('La operación ya fue anulada (Extornada).');
            }
            if ($flagExt === 'X') {
                throw new \RuntimeException('La operación no es extornable.');
            }
            if ((int) $orig['ALL_CODTRA'] === 1111) {
                throw new \RuntimeException('La operación ya es una anulación.');
            }

            $codcia   = $orig['ALL_CODCIA'];
            $tipmov   = (int) $orig['ALL_TIPMOV'];
            $fbg      = $orig['ALL_FBG'];
            $numser   = $orig['ALL_NUMSER'];
            $numfac   = $orig['ALL_NUMFAC'];
            $signoCar = (int) $orig['ALL_SIGNO_CAR'];

            // 2. Nuevo correlativo (fecha del servidor) -----------------------------
            $row = $db->query(
                "SELECT ISNULL(MAX(ALL_NUMOPER), 0) + 1 AS nuevo
                 FROM ALLOG
                 WHERE ALL_CODCIA = ? AND ALL_FECHA_DIA = CAST(GETDATE() AS date)",
                [$codcia]
            )->getRowArray();
            $newOper = (int) $row['nuevo'];

            // 3. Espejo ALLOG -------------------------------------------------------
            $this->insertAllogEspejo($db, $orig, $newOper, $usuario, $comentario);
            $this->check($db, 'ALLOG');

            // 4. Detalle original ---------------------------------------------------
            $detalle = $db->query(
                "SELECT * FROM FACART
                 WHERE FAR_CODCIA = ? AND FAR_TIPMOV = ? AND FAR_FBG = ?
                   AND FAR_NUMSER = ? AND FAR_NUMFAC = ? AND FAR_ESTADO <> 'E'
                 ORDER BY FAR_NUMSEC",
                [$codcia, $tipmov, $fbg, $numser, $numfac]
            )->getResultArray();

            // Numsec espejo = mayor secuencia original + 200 (convención VB6)
            $maxSec = 0;
            foreach ($detalle as $d) {
                $maxSec = max($maxSec, (int) $d['FAR_NUMSEC']);
            }
            $maxSec += 200;

            // 5. Marcar detalle original como extornado (igual que VB6) ------------
            if (!empty($detalle)) {
                $db->query(
                    "UPDATE FACART SET FAR_ESTADO = 'E', FAR_ESTADO2 = 'E'
                     WHERE FAR_CODCIA = ? AND FAR_TIPMOV = ? AND FAR_FBG = ?
                       AND FAR_NUMSER = ? AND FAR_NUMFAC = ? AND FAR_ESTADO <> 'E'",
                    [$codcia, $tipmov, $fbg, $numser, $numfac]
                );
                $this->check($db, 'FACART_ORIG');
            }

            // 6. Espejo FACART + reversión de stock y lote --------------------------
            foreach ($detalle as $item) {
                $this->insertFacartEspejo($db, $item, ++$maxSec, $newOper);
                $this->check($db, 'FACART');
                $this->revertirStock($db, $item);
                $this->check($db, 'STOCK');
                $this->revertirLote($db, $item);
                $this->check($db, 'LOTE');
            }

            // 7. Cartera / cliente / CARACU (solo crédito) --------------------------
            $saldoCarNuevo = null;
            if ($signoCar !== 0) {
                $saldoCarNuevo = $this->revertirCartera($db, $orig, $newOper, $usuario, $comentario, $signoCar);
                $this->check($db, 'CARTERA');
            }

            // 8. Marcar original como extornado ------------------------------------
            $db->query(
                "UPDATE ALLOG SET ALL_FLAG_EXT = 'E'
                 WHERE ALL_CODCIA = ? AND ALL_NUMOPER = ?
                   AND ALL_FECHA_DIA = CONVERT(date, ?, 23)
                   AND ALL_TIPMOV = ? AND ALL_FBG = ? AND ALL_NUMSER = ? AND ALL_NUMFAC = ?",
                [$codcia, $numOper, $fechaSql, $tipmov, $fbg, $numser, $numfac]
            );
            $this->check($db, 'ORIGINAL');

            if ($db->transStatus() === false) {
                $err = $db->error();
                throw new \RuntimeException('Error de base de datos durante la anulación: ' . json_encode($err));
            }

            $preview = null;
            if ($dryRun) {
                $preview = $this->generarPreview($db, $orig, $newOper, $tipmov);
                $db->transRollback();
            } else {
                $db->transCommit();
            }

            return [
                'status'  => true,
                'message' => 'Documento anulado correctamente. Nueva operación: ' . $newOper,
                'numOper' => $newOper,
                'dryRun'  => $dryRun,
                'preview' => $preview,
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    // ---------------------------------------------------------------------------
    // Internos
    // ---------------------------------------------------------------------------

    private function dbFor($server)
    {
        switch ((int) $server) {
            case 2:
                return $this->dbjj;
            case 3:
                return $this->dbpm;
            default:
                return $this->db;
        }
    }

    private function q($ident)
    {
        return '[' . str_replace(']', ']]', $ident) . ']';
    }

    private function check($db, $label)
    {
        $err = $db->error();
        if (!empty($err['code']) && !empty($err['message'])) {
            throw new \RuntimeException('[' . $label . '] code=' . $err['code'] . ' msg=' . $err['message']);
        }
    }

    /**
     * Inserta una fila usando expresiones SQL para columnas especiales (ej. fechas).
     * Evita enlazar cadenas de fecha que el servidor (idioma Español) malinterpreta.
     */
    private function insertRow($db, $table, array $data, array $expresiones = [])
    {
        $cols = [];
        $ph = [];
        $binds = [];
        foreach ($data as $c => $v) {
            $cols[] = $this->q($c);
            if (array_key_exists($c, $expresiones)) {
                $ph[] = $expresiones[$c];
            } else {
                $ph[] = '?';
                $binds[] = $v;
            }
        }
        $sql = 'INSERT INTO ' . $this->q($table) . ' (' . implode(',', $cols) . ') VALUES (' . implode(',', $ph) . ')';
        $db->query($sql, $binds);
    }

    /**
     * Columnas de tipo fecha/hora de una tabla (cacheado por request).
     */
    private function datetimeCols($db, $table)
    {
        static $cache = [];
        if (!isset($cache[$table])) {
            $rows = $db->query(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_NAME = ? AND DATA_TYPE IN ('datetime','date','smalldatetime')",
                [$table]
            )->getResultArray();
            $cache[$table] = array_map(function ($r) {
                return $r['COLUMN_NAME'];
            }, $rows);
        }
        return $cache[$table];
    }

    private function normalizarFecha($fecha)
    {
        $fecha = trim((string) $fecha);
        if ($fecha === '') {
            return '';
        }
        // Ya viene como Y-m-d (el grid entrega ALL_FECHA_PRO en estilo 23)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $fecha;
        }
        // d/m/Y
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $fecha, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        $ts = strtotime($fecha);
        return $ts ? date('Y-m-d', $ts) : '';
    }

    /**
     * Inserta el registro espejo en ALLOG copiando todas las columnas.
     */
    private function insertAllogEspejo($db, array $orig, $newOper, $usuario, $comentario)
    {
        $overrides = [
            'ALL_FECHA_DIA'     => 'CAST(GETDATE() AS date)',
            'ALL_NUMOPER'       => (string) $newOper,
            'ALL_CODTRA'        => '1111',
            'ALL_CODTRA_EXT'    => '1111',
            'ALL_FLAG_EXT'      => "'E'",
            'ALL_NUMOPER2'      => (string) $newOper,
            'ALL_NUM_OPER2'     => '0',
            'ALL_SIGNO_CAR'     => '-ISNULL(ALL_SIGNO_CAR, 0)',
            'ALL_SIGNO_ARM'     => '-ISNULL(ALL_SIGNO_ARM, 0)',
            'ALL_SIGNO_CAJA'    => '-ISNULL(ALL_SIGNO_CAJA, 0)',
            'ALL_SIGNO_CCM'     => '-ISNULL(ALL_SIGNO_CCM, 0)',
            'ALL_CODUSU'        => $db->escape($usuario),
            'ALL_HORA'          => 'GETDATE()',
            'ALL_CONCEPTO'      => $db->escape($comentario),
            'ALL_FECHA_PRO'     => 'CAST(GETDATE() AS date)',
            'ALL_FECHA_CAN'     => 'CAST(GETDATE() AS date)',
            'ALL_FECHA_SUNAT'   => 'CAST(GETDATE() AS date)',
        ];

        $cols = $db->getFieldNames('ALLOG');
        $insertCols = [];
        $selectExprs = [];
        foreach ($cols as $c) {
            $insertCols[]  = $this->q($c);
            $selectExprs[] = isset($overrides[$c]) ? $overrides[$c] : $this->q($c);
        }

        $sql = 'INSERT INTO ALLOG (' . implode(',', $insertCols) . ') '
             . 'SELECT ' . implode(',', $selectExprs) . ' FROM ALLOG '
             . 'WHERE ALL_CODCIA = ? AND ALL_NUMOPER = ? AND ALL_FECHA_DIA = CONVERT(date, ?, 23) '
             . 'AND ALL_TIPMOV = ? AND ALL_FBG = ? AND ALL_NUMSER = ? AND ALL_NUMFAC = ?';

        $db->query($sql, [
            $orig['ALL_CODCIA'],
            (int) $orig['ALL_NUMOPER'],
            substr($orig['ALL_FECHA_DIA'], 0, 10),
            (int) $orig['ALL_TIPMOV'],
            $orig['ALL_FBG'],
            $orig['ALL_NUMSER'],
            $orig['ALL_NUMFAC'],
        ]);
    }

    /**
     * Inserta una línea espejo en FACART (una a una, igual que VB6, para los triggers).
     */
    private function insertFacartEspejo($db, array $item, $newSec, $newOper)
    {
        $newOperSmall = $newOper > 32767 ? 0 : $newOper;

        $item['FAR_NUMSEC']    = $newSec;
        $item['FAR_ESTADO']    = 'E';
        $item['FAR_ESTADO2']   = 'E';
        $item['FAR_SIGNO_ARM'] = -1 * (int) $item['FAR_SIGNO_ARM'];
        $item['FAR_SIGNO_CAR'] = -1 * (int) $item['FAR_SIGNO_CAR'];
        $item['FAR_NUMOPER']   = $newOper;
        $item['FAR_NUMOPER2']  = $newOperSmall;
        $item['FAR_FLAG_SO']   = 'X';
        $item['FAR_HORA']      = date('h:i:s A');

        // Las columnas datetime se envían con CAST(GETDATE() AS date) para evitar
        // la ambigüedad de formato del servidor (idioma Español).
        $expr = [];
        foreach ($this->datetimeCols($db, 'FACART') as $dc) {
            $expr[$dc] = 'CAST(GETDATE() AS date)';
        }

        $this->insertRow($db, 'FACART', $item, $expr);
    }

    /**
     * Revierte el stock del artículo según la línea original.
     */
    private function revertirStock($db, array $item)
    {
        $cant     = (float) $item['FAR_CANTIDAD'];
        $revSign  = -1 * (int) $item['FAR_SIGNO_ARM'];
        $delta    = $cant * $revSign;
        $flagSo   = trim((string) $item['FAR_FLAG_SO']);
        $ingreso  = $revSign === 1 ? $cant : 0;
        $salida   = $revSign === -1 ? $cant : 0;
        $esA      = $flagSo === 'A' ? $delta : 0;
        $esN      = $flagSo === 'A' ? 0 : $delta;

        $sql = "UPDATE ARTICULO SET
                    ARM_STOCK    = ISNULL(ARM_STOCK, 0) + ?,
                    ARM_SALDO_S  = ISNULL(ARM_SALDO_S, 0) + ?,
                    ARM_SALDO_N  = ISNULL(ARM_SALDO_N, 0) + ?,
                    ARM_INGRESOS = ISNULL(ARM_INGRESOS, 0) + ?,
                    ARM_SALIDAS  = ISNULL(ARM_SALIDAS, 0) + ?
                WHERE ARM_CODCIA = ? AND ARM_CODART = ?";

        $db->query($sql, [$delta, $esA, $esN, $ingreso, $salida, $item['FAR_CODCIA'], $item['FAR_CODART']]);
    }

    /**
     * Revierte el lote (EXTORNA_LOTE). Si el lote exacto no existe, intenta
     * ubicar otro lote del mismo artículo que pueda absorber el movimiento.
     */
    private function revertirLote($db, array $item)
    {
        $codlot  = trim((string) $item['FAR_CODLOT']);
        $cant    = (float) $item['FAR_CANTIDAD_P'];
        if ($cant == 0.0) {
            $cant = (float) $item['FAR_CANTIDAD'];
        }
        $revSign = -1 * (int) $item['FAR_SIGNO_ARM'];
        $delta   = $cant * $revSign;

        if ($codlot === '') {
            return;
        }

        $lote = $db->query(
            "SELECT LOT_NROLOTE, LOT_SALDOS FROM LOTE
             WHERE LOT_CODCIA = ? AND LOT_CODART = ? AND LOT_NROLOTE = ?",
            [$item['FAR_CODCIA'], $item['FAR_CODART'], $codlot]
        )->getRowArray();

        if ($lote) {
            $db->query(
                "UPDATE LOTE SET LOT_SALDOS = ISNULL(LOT_SALDOS, 0) + ?
                 WHERE LOT_CODCIA = ? AND LOT_CODART = ? AND LOT_NROLOTE = ?",
                [$delta, $item['FAR_CODCIA'], $item['FAR_CODART'], $codlot]
            );
            return;
        }

        // BUSCA_MEJOR: primer lote del artículo que pueda absorber el movimiento.
        $lotes = $db->query(
            "SELECT LOT_NROLOTE, LOT_SALDOS FROM LOTE
             WHERE LOT_CODCIA = ? AND LOT_CODART = ?
             ORDER BY LOT_FECHA_VCTO",
            [$item['FAR_CODCIA'], $item['FAR_CODART']]
        )->getResultArray();

        foreach ($lotes as $l) {
            if (((float) $l['LOT_SALDOS'] + $delta) >= 0) {
                $db->query(
                    "UPDATE LOTE SET LOT_SALDOS = ISNULL(LOT_SALDOS, 0) + ?
                     WHERE LOT_CODCIA = ? AND LOT_CODART = ? AND LOT_NROLOTE = ?",
                    [$delta, $item['FAR_CODCIA'], $item['FAR_CODART'], $l['LOT_NROLOTE']]
                );
                return;
            }
        }

        log_message('warning', 'AnulacionModel: no se encontro lote para codart ' . $item['FAR_CODART'] . ' (' . $codlot . ')');
    }

    /**
     * Ajusta cartera, cliente e inserta el histórico CARACU. Devuelve el saldo
     * del documento resultante.
     */
    private function revertirCartera($db, array $orig, $newOper, $usuario, $comentario, $signoCar)
    {
        $invSign = -1 * $signoCar;
        $importe = (float) $orig['ALL_IMPORTE_AMORT'];
        $mov     = $importe * $invSign;

        // Cartera del documento
        $db->query(
            "UPDATE CARTERA SET
                 CAR_IMPORTE = ISNULL(CAR_IMPORTE, 0) + ?,
                 CAR_SITUACION = 'E'
             WHERE CAR_CODCIA = ? AND CAR_CP = ? AND CAR_CODCLIE = ?
               AND CAR_NUMSER = ? AND CAR_NUMFAC = ?",
            [
                $mov,
                $orig['ALL_CODCIA'],
                $orig['ALL_CP'],
                $orig['ALL_CODCLIE'],
                (int) $orig['ALL_NUMSER'],
                $orig['ALL_NUMFAC'],
            ]
        );

        $car = $db->query(
            "SELECT CAR_IMPORTE FROM CARTERA
             WHERE CAR_CODCIA = ? AND CAR_CP = ? AND CAR_CODCLIE = ?
               AND CAR_NUMSER = ? AND CAR_NUMFAC = ?",
            [
                $orig['ALL_CODCIA'],
                $orig['ALL_CP'],
                $orig['ALL_CODCLIE'],
                (int) $orig['ALL_NUMSER'],
                $orig['ALL_NUMFAC'],
            ]
        )->getRowArray();
        $saldoCar = $car ? (float) $car['CAR_IMPORTE'] : 0.0;

        // Saldo del cliente
        $db->query(
            "UPDATE CLIENTES SET CLI_SALDO = ISNULL(CLI_SALDO, 0) + ?
             WHERE CLI_CODCIA = ? AND CLI_CODCLIE = ?",
            [$mov, $orig['ALL_CODCIA'], $orig['ALL_CODCLIE']]
        );

        $cli = $db->query(
            "SELECT CLI_SALDO FROM CLIENTES WHERE CLI_CODCIA = ? AND CLI_CODCLIE = ?",
            [$orig['ALL_CODCIA'], $orig['ALL_CODCLIE']]
        )->getRowArray();
        $saldoCli = $cli ? (float) $cli['CLI_SALDO'] : 0.0;

        // CARACU (marca 'E' como formgen para 1111)
        $data = [
            'CAA_CP'         => $orig['ALL_CP'],
            'CAA_CODCLIE'    => $orig['ALL_CODCLIE'],
            'CAA_CODCIA'     => $orig['ALL_CODCIA'],
            'CAA_TIPDOC'     => $orig['ALL_TIPDOC'] !== null ? $orig['ALL_TIPDOC'] : ' ',
            'CAA_FECHA'      => date('Y-m-d'),
            'CAA_NUM_OPER'   => $newOper,
            'CAA_SERDOC'     => $orig['ALL_SERDOC'],
            'CAA_NUMDOC'     => $orig['ALL_NUMDOC'],
            'CAA_IMPORTE'    => $mov,
            'CAA_SALDO'      => $saldoCli,
            'CAA_CONCEPTO'   => $comentario,
            'CAA_SIGNO_CAR'  => $invSign,
            'CAA_ESTADO'     => 'E',
            'CAA_NUMSER'     => (int) $orig['ALL_NUMSER'],
            'CAA_NUMFAC'     => $orig['ALL_NUMFAC'],
            'CAA_TIPMOV'     => (int) $orig['ALL_TIPMOV'],
            'CAA_FBG'        => $orig['ALL_FBG'],
            'CAA_HORA'       => date('Y-m-d H:i:s'),
            'CAA_CODUSU'     => $usuario,
            'CAA_CODTRA'     => 1111,
            'CAA_SALDO_CAR'  => $saldoCar,
            'CAA_TOTAL'      => abs($importe) * $invSign,
            'CAA_SIGNO_CAJA' => $orig['ALL_SIGNO_CAJA'],
        ];
        $this->insertRow($db, 'CARACU', $data, [
            'CAA_FECHA' => 'CAST(GETDATE() AS date)',
            'CAA_HORA'  => 'GETDATE()',
        ]);

        return $saldoCar;
    }

    /**
     * Datos del resultado (solo para pruebas con dryRun, antes del rollback).
     */
    private function generarPreview($db, array $orig, $newOper, $tipmov)
    {
        $codcia = $orig['ALL_CODCIA'];
        $fbg    = $orig['ALL_FBG'];
        $numser = $orig['ALL_NUMSER'];
        $numfac = $orig['ALL_NUMFAC'];

        $preview = [
            'allog' => $db->query(
                "SELECT ALL_NUMOPER, ALL_CODTRA, ALL_FLAG_EXT, ALL_SIGNO_ARM, ALL_SIGNO_CAJA,
                        ALL_IMPORTE_AMORT, ALL_CONCEPTO, ALL_NUMFAC, ALL_FBG, ALL_NUMSER,
                        CONVERT(varchar(19), ALL_FECHA_DIA, 120) AS ALL_FECHA_DIA
                 FROM ALLOG
                 WHERE ALL_CODCIA = ? AND ALL_NUMOPER = ? AND ALL_FECHA_DIA = CAST(GETDATE() AS date)",
                [$codcia, $newOper]
            )->getRowArray(),
            'facart' => $db->query(
                "SELECT FAR_NUMOPER, FAR_NUMSEC, FAR_CODART, FAR_ESTADO, FAR_ESTADO2,
                        FAR_SIGNO_ARM, FAR_SIGNO_CAR, FAR_CANTIDAD, RTRIM(FAR_FLAG_SO) AS FAR_FLAG_SO,
                        CONVERT(varchar(10), FAR_FECHA, 23) AS FAR_FECHA,
                        CONVERT(varchar(10), FAR_FECHA_COMPRA, 23) AS FAR_FECHA_COMPRA
                 FROM FACART
                 WHERE FAR_CODCIA = ? AND FAR_TIPMOV = ? AND FAR_FBG = ?
                   AND FAR_NUMSER = ? AND FAR_NUMFAC = ?
                 ORDER BY FAR_NUMSEC",
                [$codcia, $tipmov, $fbg, $numser, $numfac]
            )->getResultArray(),
            'stock' => $db->query(
                "SELECT A.ARM_CODART, A.ARM_STOCK, A.ARM_INGRESOS, A.ARM_SALIDAS, A.ARM_SALDO_S, A.ARM_SALDO_N
                 FROM ARTICULO A
                 WHERE A.ARM_CODCIA = ?
                   AND A.ARM_CODART IN (SELECT FAR_CODART FROM FACART WHERE FAR_NUMOPER = ? AND FAR_FBG = ? AND FAR_NUMSER = ? AND FAR_NUMFAC = ?)",
                [$codcia, $newOper, $fbg, $numser, $numfac]
            )->getResultArray(),
            'lote' => $db->query(
                "SELECT L.LOT_CODART, L.LOT_NROLOTE, L.LOT_SALDOS
                 FROM LOTE L
                 WHERE L.LOT_CODCIA = ?
                   AND L.LOT_CODART IN (SELECT FAR_CODART FROM FACART WHERE FAR_NUMOPER = ? AND FAR_FBG = ? AND FAR_NUMSER = ? AND FAR_NUMFAC = ?)",
                [$codcia, $newOper, $fbg, $numser, $numfac]
            )->getResultArray(),
        ];

        if ((int) $orig['ALL_SIGNO_CAR'] !== 0) {
            $preview['cartera'] = $db->query(
                "SELECT CAR_IMPORTE, CAR_SITUACION FROM CARTERA
                 WHERE CAR_CODCIA = ? AND CAR_CP = ? AND CAR_CODCLIE = ? AND CAR_NUMSER = ? AND CAR_NUMFAC = ?",
                [$codcia, $orig['ALL_CP'], $orig['ALL_CODCLIE'], (int) $orig['ALL_NUMSER'], $numfac]
            )->getResultArray();
            $preview['caracu'] = $db->query(
                "SELECT CAA_NUM_OPER, CAA_IMPORTE, CAA_SIGNO_CAR, CAA_ESTADO, CAA_CONCEPTO,
                        CAA_CODCLIE, CAA_NUMFAC, CONVERT(varchar(10), CAA_FECHA, 23) AS CAA_FECHA
                 FROM CARACU WHERE CAA_CODCIA = ? AND CAA_NUM_OPER = ? AND CAA_CODCLIE = ? AND CAA_NUMFAC = ?",
                [$codcia, $newOper, $orig['ALL_CODCLIE'], $numfac]
            )->getResultArray();
            $preview['cliente'] = $db->query(
                "SELECT CLI_SALDO FROM CLIENTES WHERE CLI_CODCIA = ? AND CLI_CODCLIE = ?",
                [$codcia, $orig['ALL_CODCLIE']]
            )->getResultArray();
        }

        return $preview;
    }
}
