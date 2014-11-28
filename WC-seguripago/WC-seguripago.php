<?php
/*
 * Seguripago: Plantilla para respuesta de pago diferido (SeguriCash y otros)
 * --------------------------------------------------------------------------
 * Creado el 21 de octubre de 2013
 *
 * Esta plantilla es llamada desde la api de SeguriPago:
 * $ruta_logo               : Ruta del logo del comercio.
 * $nombre_comercio : Nombre de la empresa/comercio que aparecerá en la orden de pago.
 * $completo                : Booleano que indica si se debe mostrar la plantilla en modo completo o no.
 * $num_transaccion : Número de transaccion de SeguriPago.
 * $importe                 : Importe.
 * $moneda                  : Moneda.
 * $cod_producto        : Código de Producto: (1) SeguriCrédito, (2) SegurCash.
 */
//----------- Validando origen del pedido ------------
if(!isset($modo, $ruta_logo, $completo, $num_transaccion, $importe, $moneda, $cod_producto)) exit();

if(!($modo=="prod")) {
    echo "<h2 style='color:#f00;'>Modo de Test</h2>";
}

include("css/estilos.css.php");
ini_set("display_errors", false);

//----------- Identificando producto de SeguriPago -------------
$producto = $cod_producto=="1"?"SeguriCr&eacute;dito":($cod_producto=="2"?"SeguriCash":"Otro");

//----------- Rutas de las imágenes ----------
$ruta_relativa = substr(dirname(realpath(__FILE__)).'/',strlen($_SERVER['DOCUMENT_ROOT']));

//---------- Ruta del host ----------
$host = sprintf(
    "%s://%s",
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')? 'https' : 'http',
    $_SERVER['HTTP_HOST']/*,
    $_SERVER['REQUEST_URI']*/
  );

//---------- Rutas de imágenes ------------
$logo_seguripago = $host.$ruta_relativa."img/logo-seguripago.jpg";
$imagen_scotia = $host.$ruta_relativa."img/scotiabank.png";
$imagen_bcp = $host.$ruta_relativa."img/bcp.jpg";

?>
<div style="<?php echo $sp_css_body; ?>">
    <div id="print" style="<?php echo $sp_css_mensaje; ?>">





        <?php
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& CABECERA &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
        ?>

        <?php //-------------------------- Zona de Logo ------------------------------ ?>
        <center>
            <div style="<?php echo $sp_css_logo; ?>">
                <table border="0" width="660px" align="center" style="border:0px;margin:auto;">
                    <tr>
                        <td width="50%" align="left" valign="top">
                            <img title="SeguriCash" width="226" height="91" alt="SeguriCash" src="<?php echo $logo_seguripago; ?>" />
                        </td>
                        <td width="50%" align="right" valign="top">
                            <img src="<?php echo $ruta_logo; ?>" />
                        </td>
                    </tr>
                </table>
            </div>
        </center>

        <?php //--------------- Zona de Precio (líneas punteadas) -------------------- ?>
        <div style="<?php echo $sp_css_precio; ?>">
            <span style="<?php echo $sp_css_importe_pagar; ?>">
                                <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td width="50%">
                                            <table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
                                                <tr>
                                                    <td valign="top">
                                                        <span style="font-size: 13.05px; line-height: normal; font-weight:normal;">&nbsp;&nbsp;&nbsp;C&oacute;digo de pago:</span>
                                                    </td>
                                                    <td valign="top">
                                                        <span style="font-size: 19px;font-weight: bold;">SeguriPago <?php echo $num_transaccion; ?></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td valign="top">
                                                        <span style="font-size: 13.05px; line-height: normal; font-weight:normal;">&nbsp;&nbsp;&nbsp;Comercio:</span>
                                                    </td>
                                                    <td valign="top">
                                                        <span style="font-size: 13.05px; line-height: normal; font-weight:normal;"><?php echo $nombre_comercio; ?></span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <?php //<td><img class="codigo" alt="" src=""></td> //-- Código de barra anulado ?>
                                        <td width="50%">
                                            <center>
                                                <table cellspacing="0" cellpadding="0" border="0">
                                                    <tr>
                                                        <td width="115" valign="top">
                                                            <span style="font-size: 13.05px; line-height: normal; font-weight:normal;">&nbsp;&nbsp;&nbsp;Importe a pagar:</span>
                                                        </td>
                                                        <td valign="top">
                                                            <span style="font-size: 19px;font-weight: bold;"><?php echo $moneda=="PEN"?"S/.":$moneda; ?> <?php echo $importe; ?></span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td width="115" valign="top">
                                                            <span style="font-size: 13.05px; line-height: normal; font-weight:normal;">&nbsp;&nbsp;&nbsp;Fecha de Vencimiento:</span>
                                                        </td>
                                                        <td valign="top">
                                                            <span style="font-size: 19px;font-weight: bold;"><?php echo $fecha_vencimiento; ?></span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </center>
                                        </td>
                                    </tr>
                                </table>
                                <br />
            </span>
        </div>
        <?php
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&& FIN DE CABECERA &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
        ?>




        <?php
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& DETALLE &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
        ?>
        <h4 style="<?php echo $sp_css_modos_titulo; ?>">Puedes realizar el pago en banca electr&oacute;nica, ventanilla o agentes <strong>antes del</strong></h4>
        <div style="<?php echo $sp_css_modos; ?>">

                                    <div style="<?php echo $sp_css_banco1; ?>">
                                                <img width="102" height="36" alt="" src="<?php echo $imagen_scotia; ?>" style="<?php echo $sp_css_banco_imagen; ?>" />
                                                <center>
                                                <p style="<?php echo $sp_css_primera_linea; ?>margin-bottom:10px;">
                                                    Su recibo estar&aacute; <strong>disponible inmediatamente.</strong>
                                                </p>
                                                </center>
                                                <?php if($completo) : ?>
                                                                <p style="margin-bottom: 7px;" ><b>1. En las ventanillas de las Agencias de Scotiabank o Cajero Express:</b></p>
                                                                <ul style="<?php echo $sp_css_modos_ul; ?>">
                                                                        <li>Ac&eacute;rquese a una agencia Scotiabank o Cajero Express.</li>
                                                                        <li>Indique que desea realizar el pago de SeguriPago. SeguriPago-soles <strong>(BT 50/186)</strong>.</li>
                                                                        <li>Indique que el servicio a pagar es Seguripago-soles.</li>
                                                                        <li>Indicar su c&oacute;digo de pago <strong><?php echo $num_transaccion; ?></strong> y se le entregar&aacute; su voucher de Pago.</li>
                                                                </ul>

                                                                <p style="margin-bottom: 7px;" ><b>2. V&iacute;a Scotia en L&iacute;nea (Banca Por Internet Scotiabank):</b></p>
                                                                <ul style="<?php echo $sp_css_modos_ul; ?>">
                                                                        <li>Ingrese a Scotia en L&iacute;nea</li>
                                                                        <li>Seleccione su Tarjeta de Acceso y D&eacute;bito e ingrese los 8 &uacute;ltimos d&iacute;gitos de su tarjeta y la &quot;clave principal&quot;.</li>
                                                                        <li>En el men&uacute; principal, seleccione Pagos &ndash; Otras Instituciones.</li>
                                                                        <li>En el tipo de Instituci&oacute;n seleccione Otros y consulte.</li>
                                                                        <li>Seleccionar la instituci&oacute;n seg&uacute;n servicio: SeguriPago. SeguriPago-soles.</li>
                                                                        <li>Ingrese su c&oacute;digo de pago <strong><?php echo $num_transaccion; ?></strong> y el sistema le mostrar&aacute; una pantalla que podr&aacute; imprimir o enviar a una direcci&oacute;n de correo electr&oacute;nico como constancia de pago.</li>
                                                                </ul>
                                                <?php endif ?>
                                                <br />
                                    </div>


                                    <div style="<?php echo $sp_css_banco2; ?>">
                                                <img width="102" height="36" alt="" src="<?php echo $imagen_bcp; ?>" style="<?php echo $sp_css_banco_imagen; ?>" />
                                                <center>
                                                <p style="<?php echo $sp_css_primera_linea; ?>margin-bottom: 5px;" >
                                                    Su recibo estar&aacute; <strong>disponible inmediatamente.</strong>
                                                </p>
                                                </center>
                                                <?php if($completo) : ?>

                                                                <p style="margin-bottom: 7px;" ><b>1. En una Agencia BCP:</b></p>
                                                                <ul style="<?php echo $sp_css_modos_ul; ?>">
                                                                        <li>Imprima esta boleta.</li>
                                                                        <li>Dir&iacute;jase a una agencia BCP</li>
                                                                        <li>Indique que va a realizar un pago a la  empresa: SeguriPago</li>
                                                                        <li>Indique que el servicio a pagar es SEGURIPAGO - soles</li>
                                                                        <li>Indique el c&oacute;digo de pago de SEGURIPAGO: <strong><?php echo $num_transaccion; ?></strong>   </li>
                                                                        <li>Se le remitir&aacute; su voucher de Pago.</li>
                                                                </ul>

                                                                <p style="margin-bottom: 7px;" ><b>2. En un Agente BCP:</b></p>
                                                                <ul style="<?php echo $sp_css_modos_ul; ?>">
                                                                        <li>Imprima esta boleta.</li>
                                                                        <li>Dir&iacute;jase a un agente BCP</li>
                                                                        <li>Indique que va a realizar un pago a la  empresa: Seguripago, c&oacute;digo de agente 08051.</li>
                                                                        <li>Indique que el servicio a pagar es SEGURIPAGO - soles</li>
                                                                        <li>Indique el c&oacute;digo de Pago de SEGURIPAGO: <strong><?php echo $num_transaccion; ?></strong>  </li>
                                                                        <li>Se le remitir&aacute;; su voucher de Pago</li>
                                                                </ul>

                                                                <p style="margin-bottom: 7px;" ><b>3. V&iacute;a internet BCP, seguir los siguientes pagos:</b></p>
                                                                <ul style="<?php echo $sp_css_modos_ul; ?>">
                                                                        <li>Ingresa en tu cuenta.</li>
                                                                        <li>Selecciona la opci&oacute;n Pago de Servicios ubicada en la columna izquierda.</li>
                                                                        <li>Selecciona Empresas Diversas, buscar SeguriPago</li>
                                                                        <li>Selecciona el servicio SEGURIPAGO - soles</li>
                                                                        <li>Ingresa el siguiente C&oacute;digo de Pago: <strong><?php echo $num_transaccion; ?></strong>  </li>
                                                                </ul>
                                                <?php endif ?>
                                    </div>

        </div>
        <?php
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&& FIN DE DETALLE &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
        ?>





    </div>
</div>
