<?php
/*
 * Seguripago: Plantilla para respuesta de pago inmediato (SeguriCrédito) rechazado
 * --------------------------------------------------------------------------------
 * Creado el 9 de octubre de 2013
 *
 * Esta plantilla es llamada desde:
 * 02_ejemplo_recepcion_trama_de_seguripago_inmediato.php
 */
ini_set("display_errors", false);
$medio_de_pago = $data["medio_pago"]=="1"?"Visa":($data["medio_pago"]=="2"?"Mastercard":($data["medio_pago"]=="3"?"American Express":"Otro"));
?>
<center>
&#33;Lamentamos que no puedas realizar la compra<br />
 en <?php echo "Nombre de mi Tienda"; ?>!<br /><br />
Hola <?php echo "Nombre de Mi cliente"; ?>:
<br /><br />
Tu n&uacute;mero de pedido <?php echo $data["num_pedido"] ?> ( <?php echo $data["num_transaccion"]; ?> ),
 no fue aceptado por <?php echo $medio_de_pago; ?> por <?php echo $data["txt_respuesta"]; ?><br /><br />

La transacci&oacute;n fue realizada el <?php echo date("d/m/Y", $data["fecha_hora_trans"]); ?>
 a las <?php echo date("H:i", $data["fecha_hora_trans"]); ?>,
 al intentar pagar un monto de <?php echo $data["moneda"]=="PEN"?"S/.":$data["moneda"]; ?> <?php echo $data["importe"]; ?>.
 <br /><br />
 Al no poder efectuar la compra, no habr&aacute; ning&uacute;n cobro.
<br /><br />
&#33;Si deseas, vuelve a intentar la compra y disfruta de las ofertas!



<br /><br />
<button onclick="window.print();">Imprimir</button>
</center>