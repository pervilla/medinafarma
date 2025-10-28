<?php

namespace App\Controllers;

use App\Models\FacartModel;
use App\Models\CajaMovimientosModel;
use App\Controllers\BaseController;
use App\Models\ArticuloModel;
use Luecano\NumeroALetras\NumeroALetras;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use SimpleSoftwareIO\QrCode\Generator;
use Dompdf\Dompdf;
class Comprobante extends BaseController
{
    public function verdoc()
    {
        $session = session();
        $uri = $this->request->getUri();
        $local = $uri->getSegment(3);
        $tipmov = $uri->getSegment(4);
        $numser = $uri->getSegment(5);
        $numfac = $uri->getSegment(6);
        $fecha = $uri->getSegment(7);
        $Facart = new FacartModel();
        $data['locales'] = array(1 => "CENTRO", 2 => "JUANJUICILLO", 3 => "PEÑAMEZA");
        $data['facart'] = $Facart->get_comprobante($numser, $numfac, $tipmov, date('d/m/Y', strtotime($fecha)), $local);

        $formatter = new NumeroALetras();

        $data['total'] = "SON: " . $formatter->toMoney($data['facart'][0]->FAR_BRUTO, 2, 'SOLES', 'CENTIMOS');
        $qrcode = new Generator;
        $chl = "20450337839|01|F0" . $data['facart'][0]->FAR_NUMSER . "|" . $data['facart'][0]->FAR_NUMFAC . "|0.00|" . $data['facart'][0]->FAR_BRUTO . "|" . date('Y-m-d', strtotime($data['facart'][0]->FAR_FECHA)) . "|6|";
        $data['qr'] = $qrcode->size(100)->generate($chl);
        //var_export($data); die();
        return view('comprobante/index_ver', $data);
    }
    public function doc()
    {
        $session = session();
        $uri = $this->request->getUri();
        $local = $uri->getSegment(3);
        $tipmov = $uri->getSegment(4);
        $numser = $uri->getSegment(5);
        $numfac = $uri->getSegment(6);
        $fecha = $uri->getSegment(7);
        $Facart = new FacartModel();
        $Facart = $Facart->get_comprobante($numser, $numfac, $tipmov, date('d/m/Y', strtotime($fecha)), $local);

        $locales = array(1 => "CENTRO", 2 => "JUANJUICILLO", 3 => "PEÑAMEZA");
        if (count($Facart) > 0) {

            if ($local == 1 || $session->get('user_id') == 'ADMIN') {
                $connector = new WindowsPrintConnector("smb://asesor:159357@ventas2/6-EPSON TM-T20III Receipt");
            } elseif ($local == 2) {
                $connector = new WindowsPrintConnector("smb://asesor:159357@server02/6-EPSON TM-T20II Receipt");
            } elseif ($local == 3) {
                $connector = new WindowsPrintConnector("smb://asesor:159357@medinaimpresora/6-EPSON TM-T20II Receipt5");
            }
            if ($Facart[0]->FAR_FBG == 'B') {
                $documento = "BOLETA : BO";
                $tipoDoc = empty(trim($Facart[0]->CLI_RUC_ESPOSA)) ? '' : 'DNI:' . $Facart[0]->CLI_RUC_ESPOSA . "\n";
                $direccion = "";
            } elseif ($Facart[0]->FAR_FBG == 'F') {
                $documento = "FACTURA : FA";
                $tipoDoc = 'RUC : ' . TRIM($Facart[0]->CLI_RUC_ESPOSO) . "\n";
                $direccion = 'DIRECCION : ' . TRIM($Facart[0]->CLI_CASA_DIREC) . ' ' . TRIM($Facart[0]->CLI_CASA_NUM) . "\n";
            } elseif ($Facart[0]->FAR_FBG == 'G') {
                $documento = "GUIA : GU";
                $tipoDoc = '';
                $direccion = "";
            }
            $cliente = $Facart[0]->FAR_CODCLIE == 1 ? $Facart[0]->FAR_CLIENTE : $Facart[0]->CLI_NOMBRE;
            $logo = EscposImage::load(FCPATH . 'dist\img\medinafarma-black.jpg', false);
            $printer = new Printer($connector);
            $copia = 0; // 'COP_SEC' => 1, 'COP_DESCRIP' => 'CLIENTE'
            foreach ($Facart as $val) {

                if ($copia != $val->COP_SEC) {
                    $copia = $val->COP_SEC;
                    $printer->setFont(Printer::FONT_B);
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->graphics($logo);
                    $printer->feed();
                    $printer->text("INVERSIONES SAN MARTIN S.C.R.L.\n");
                    $printer->text("RUC: 20450337839\n");
                    $printer->text("Jr. Huallaga Nro 601 - Juanjuí - Mcal Cacéres - San Martín\n");
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text("----------------------------------------------------------------" . "\n");

                    $printer->text($documento);
                    $printer->text(TRIM($val->FAR_NUMSER) . "-" . $val->FAR_NUMFAC . "\n");
                    $printer->text("Fecha Emisión: ");
                    $printer->text(date('d-m-Y', strtotime($val->FAR_FECHA)) . " " . date("h:i A", strtotime($val->FAR_HORA)) . "\n");
                    $printer->text("Responsable: ");
                    $printer->text($val->VEM_NOMBRE . "\n");

                    $printer->text("----------------------------------------------------------------" . "\n");
                    $printer->text("Cliente: ");
                    $printer->text(trim($cliente) . "\n");
                    $printer->text($tipoDoc);
                    $printer->text($direccion);
                    $printer->text("----------------------------------------------------------------" . "\n");
                    $printer->text("PRODUCTO                         CANT.     P/U.         IMPORTE\n");
                    $printer->text("----------------------------------------------------------------" . "\n");
                    foreach ($Facart as $val2) {
                        if ($copia == $val2->COP_SEC) {
                            $concepto = str_pad(substr(trim($val2->ART_NOMBRE), 0, 64), 64, " ");
                            $monto = str_pad($val2->FAR_CANTIDAD_P / $val2->FAR_EQUIV, 40, " ", STR_PAD_LEFT);
                            $monto .= " ";
                            $monto .= str_pad(substr($val2->FAR_DESCRI, 0, 5), 5, " ", STR_PAD_LEFT);
                            $monto .= str_pad(number_format((float)round($val2->FAR_PRECIO, 2, PHP_ROUND_HALF_DOWN), 2, '.', ','), 8, " ", STR_PAD_LEFT);
                            $monto .= str_pad(number_format((float)round($val2->FAR_PRECIO * $val2->FAR_CANTIDAD_P / $val2->FAR_EQUIV, 2, PHP_ROUND_HALF_DOWN), 2, '.', ','), 8, " ", STR_PAD_LEFT);
                            $printer->text($concepto . "\n");
                            $printer->text($monto . "\n");
                        }
                    }

                    $printer->text("----------------------------------------------------------------" . "\n");
                    $printer->text(str_pad(substr("Subtotal :", 0, 15), 46, " ", STR_PAD_LEFT));
                    $printer->text(" S/. ");
                    $printer->text(str_pad(number_format((float)round($val->FAR_BRUTO, 2, PHP_ROUND_HALF_DOWN), 2, '.', ','), 12, " ", STR_PAD_LEFT) . "\n");

                    $printer->text(str_pad(substr("DESCUENTO :", 0, 15), 46, " ", STR_PAD_LEFT));
                    $printer->text(" S/. ");
                    $printer->text(str_pad(number_format((float)round(0.00, 2, PHP_ROUND_HALF_DOWN), 2, '.', ','), 12, " ", STR_PAD_LEFT) . "\n");

                    $printer->text(str_pad(substr("ISC :", 0, 15), 46, " ", STR_PAD_LEFT));
                    $printer->text(" S/. ");
                    $printer->text(str_pad(number_format((float)round(0.00, 2, PHP_ROUND_HALF_DOWN), 2, '.', ','), 12, " ", STR_PAD_LEFT) . "\n");

                    $printer->text(str_pad(substr("IGV (18%) :", 0, 15), 46, " ", STR_PAD_LEFT));
                    $printer->text(" S/. ");
                    $printer->text(str_pad(number_format((float)round(0.00, 2, PHP_ROUND_HALF_DOWN), 2, '.', ','), 12, " ", STR_PAD_LEFT) . "\n");


                    $printer->text(str_pad(substr("IMPORTE TOTAL :", 0, 15), 46, " ", STR_PAD_LEFT));
                    $printer->text(" S/. ");
                    $printer->setTextSize(2, 2);
                    $printer->text(str_pad(number_format((float)round($val->FAR_BRUTO, 2, PHP_ROUND_HALF_DOWN), 2, '.', ','), 6, " ", STR_PAD_LEFT) . "\n");

                    $printer->setTextSize(1, 1);

                    $formatter = new NumeroALetras();

                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text("SON: " . $formatter->toMoney($val->FAR_BRUTO, 2, 'SOLES', 'CENTIMOS') . "\n");

                    $printer->text("Forma de pago: CONTADO \n");
                    if ($val->FAR_FBG == 'F') {
                        $printer->qrCode("20450337839|01|F0" . $val->FAR_NUMSER . "|" . $val->FAR_NUMFAC . "|0.00|" . $val->FAR_BRUTO . "|" . date('Y-m-d', strtotime($val->FAR_FECHA)) . "|6|", Printer::QR_ECLEVEL_L, 5);
                    }
                    $printer->text("\n");

                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->text("-- " . $val->COP_DESCRIP . " --\n");
                    $printer->text("Representación impresa de la " . "\n");
                    $printer->text("Obligado a ser Emisor Electrónico mediante la Resolución de Superintendecia" . "\n");
                    $printer->text("N° 155-2017/SUNAT-Anexo IV  \n");
                    $printer->text(" " . "\n");
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->text("GRACIAS POR SU VISITA !  \n");

                    $printer->feed(3);
                    $printer->cut();
                }
            }
            $printer->pulse();
            $printer->close();
        }
        return $this->response->redirect(site_url('operaciones/ventas'));
    }
    public function deposito()
    {
        $session = session();
        $uri = $this->request->getUri();
        $local = $uri->getSegment(3);
        $nromov = $uri->getSegment(4);

        $CajaMov = new CajaMovimientosModel();
        $movimientos = $CajaMov->get_movimiento($nromov,$local);   

        $locales = array(1 => "CENTRO", 2 => "JUANJUICILLO", 3 => "PEÑAMEZA");
        if ($movimientos) {

            if ($local == 1 || $session->get('user_id') == 'ADMIN') {
                $connector = new WindowsPrintConnector("smb://asesor:159357@ventas2/6-EPSON TM-T20III Receipt");
            } elseif ($local == 2) {
                $connector = new WindowsPrintConnector("smb://asesor:159357@server02/6-EPSON TM-T20II Receipt");
            } elseif ($local == 3) {
                $connector = new WindowsPrintConnector("smb://asesor:159357@medinaimpresora/6-EPSON TM-T20II Receipt5");
            }

            $printer = new Printer($connector);
            $printer->setFont(Printer::FONT_B);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer -> text("█████▓▒░░ DEPOSITO DE DINERO ░░▒▓█████ \n");
            $printer -> setTextSize(4, 4);
            $printer -> text($locales[$local]."\n");
            $printer -> setTextSize(1,1);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Fecha: ");
            $printer->text(date('d-m-Y', strtotime($movimientos->CAJ_FECHA)) . "\n");
            $printer->text("Responsable: ");
            $printer->text($movimientos->VEM_NOMBRE . "\n");
            $printer->text("Caja Nro: ");
            $printer->text($movimientos->CAJ_NRO . "\n");
            $printer->text("----------------------------------------------------------------" . "\n");
            $printer->text("CONCEPTO : ".$movimientos->CMV_DESCRIPCION."\n");            
            $printer->text("IMPORTE TOTAL : S/. ");
            $printer->setTextSize(2, 2);
            $printer->text(str_pad(number_format((float)round($movimientos->CMV_MONTO, 2, PHP_ROUND_HALF_DOWN), 2, '.', ','), 6, " ", STR_PAD_LEFT) . "\n");
            
            $printer->setTextSize(1, 1);
            $printer->text("----------------------------------------------------------------" . "\n");
            $formatter = new NumeroALetras();
            
            $printer->text("SON: " . $formatter->toMoney($movimientos->CMV_MONTO, 2, 'SOLES', 'CENTIMOS') . "\n");
            $printer->feed(3);
            $printer->cut();

            $printer->pulse();
            $printer->close();
        }
        return $this->response->redirect(site_url('caja/dia'));
    }
    public function inventario_bebidas()
    {
        $session = session();
        $uri = $this->request->getUri();
        $local = $uri->getSegment(3);
        $nrocaja = $uri->getSegment(4);

        $CajaMov = new CajaMovimientosModel();
        $movimientos = $CajaMov->get_caja_datos($nrocaja,$local);   
        
        $articulo = new ArticuloModel;
        $bebidas = $articulo->get_stock_articulos(0,$local,'articulo','caja',array('466, 526, 537, 257'));
        $locales = array(1 => "CENTRO", 2 => "JUANJUICILLO", 3 => "PEÑAMEZA");
        if ($movimientos) {

            if ($local == 1 || $session->get('user_id') == 'ADMIN') {
                $connector = new WindowsPrintConnector("smb://asesor:159357@ventas2/6-EPSON TM-T20III Receipt");
            } elseif ($local == 2) {
                $connector = new WindowsPrintConnector("smb://asesor:159357@server02/6-EPSON TM-T20II Receipt");
            } elseif ($local == 3) {
                $connector = new WindowsPrintConnector("smb://asesor:159357@medinaimpresora/6-EPSON TM-T20II Receipt5");
            }

            $printer = new Printer($connector);
            $printer->setFont(Printer::FONT_B);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer -> text("████▓▒░░ INVENTARIO DE BEBIDAS ░░▒▓████\n");
            $printer -> setTextSize(4, 4);
            $printer -> text($locales[$local]."\n");
            $printer -> setTextSize(1,1);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Fecha: ");
            $printer->text(date('d-m-Y', strtotime($movimientos->CAJ_FECHA)) . "\n");
            $printer->text("Responsable: ");
            $printer->text($movimientos->VEM_NOMBRE . "\n");
            $printer->text("Caja Nro: ");
            $printer->text($movimientos->CAJ_NRO . "\n");
            $printer->text("┌───────────────────────────────┬───────┬───────┬──────┬──────┐\n");
            $printer->text(" PRODUCTO                        SIST.   FISIC.  FALTA.  SOBRA.\n");
            $printer->text("└───────────────────────────────┴───────┴───────┴──────┴──────┘\n");
            foreach ($bebidas as $val2) {
                
                    $concepto = str_pad(substr(trim($val2->ART_NOMBRE), 0, 30), 30, " ");
                    $monto = str_pad($val2->STOCK, 9, " ", STR_PAD_LEFT);
                    $monto .= "         -       +      ";
                    $printer->text($concepto);
                    $printer->text($monto . "\n");
                    $printer->text("└───────────────────────────────┴───────┴───────┴──────┴──────┘\n");
            }     
            


            $printer->setTextSize(1, 1);
           
           
            $printer -> text("┌────────────────────────────┬─────────────────┐\n");
            $printer -> text("│ CAJERO:                    │ FIRMA:          │\n");
            $printer -> text("│ ".str_pad(trim($movimientos->VEM_NOMBRE), 26)." │                 │\n");
            $printer -> text("│                            │                 │\n");
            $printer -> text("└────────────────────────────┴─────────────────┘\n");

            $printer->feed(3);
            $printer->cut();

            $printer->pulse();
            $printer->close();
        }
        return $this->response->redirect(site_url('caja/dia'));
    }
    public function createpdf()
    {
        $session = session();
        $uri = $this->request->getUri();
        $local = $uri->getSegment(3);
        $tipmov = $uri->getSegment(4);
        $numser = $uri->getSegment(5);
        $numfac = $uri->getSegment(6);
        $fecha = $uri->getSegment(7);
        $Facart = new FacartModel();
        $data['locales'] = array(1 => "CENTRO", 2 => "JUANJUICILLO", 3 => "PEÑAMEZA");
        $data['facart'] = $Facart->get_comprobante($numser, $numfac, $tipmov, date('d/m/Y', strtotime($fecha)), $local);

        $formatter = new NumeroALetras();

        $data['total_texto'] = "SON: " . $formatter->toMoney($data['facart'][0]->FAR_BRUTO, 2, 'SOLES', 'CENTIMOS');
        $qrcode = new Generator;
        $chl = "20450337839|01|F0" . $data['facart'][0]->FAR_NUMSER . "|" . $data['facart'][0]->FAR_NUMFAC . "|0.00|" . $data['facart'][0]->FAR_BRUTO . "|" . date('Y-m-d', strtotime($data['facart'][0]->FAR_FECHA)) . "|6|";

        $data['qr'] = $qrcode

        ->size(120)
        ->margin(2)
        ->errorCorrection('M')
        ->generate($chl);

        if($data['facart'][0]->FAR_FBG=='B'){
            $documento= "BO";
        }elseif ($data['facart'][0]->FAR_FBG=='F') {
            $documento= "FA";
        }elseif ($data['facart'][0]->FAR_FBG=='G') {
            $documento= "GO";
        }
        $nro_Boleta = $documento.TRIM($data['facart'][0]->FAR_NUMSER)."-".$data['facart'][0]->FAR_NUMFAC;
        //return view('pdf/factura', $data);
//var_export($data); die();

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('pdf/factura', $data));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($nro_Boleta);/**/
    }
}
