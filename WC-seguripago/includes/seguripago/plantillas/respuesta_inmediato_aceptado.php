<?php
/*
 * Seguripago: Plantilla para respuesta de pago inmediato (SeguriCrédito) aceptado
 * -------------------------------------------------------------------------------
 * Creado el 9 de octubre de 2013
 *
 * Esta plantilla es llamada desde:
 * 02_ejemplo_recepcion_trama_de_seguripago_inmediato.php
 */
ini_set("display_errors", false);
$medio_de_pago = $data["medio_pago"]=="1"?"Visa":($data["medio_pago"]=="2"?"Mastercard":($data["medio_pago"]=="3"?"American Express":"Otro"));
?>
<center>
Gracias por comprar en <?php echo "Mi Tienda de ejemplo"; ?><br /><br /><br />
Hola <?php echo "Nombre de Mi cliente" ?><br /><br />

Tu pedido <?php echo $data["num_pedido"] ?> ( <?php echo $data["num_transaccion"]; ?> ),
 fue aceptado por <?php echo $medio_de_pago; ?> con el c&oacute;digo <?php echo $data["num_referencia"]; ?>
 el <?php echo date("d/m/Y", $data["fecha_hora_trans"]); ?> a las <?php echo date("H:i", $data["fecha_hora_trans"]); ?>,
 al realizar el pago de <?php echo $data["moneda"]=="PEN"?"S/.":$data["moneda"]; ?>
	<?php echo $data["importe"]; ?>.<br />
  N&uacute;mero de Tarjeta: <?php echo $data["num_tarjeta"]; ?><br/>
  <?php echo (isset($data["nom_tarjetahabiente"])? "Nombre de la Tarjeta Habiente: ".$data["nom_tarjetahabiente"]: " " ); ?><br/>


&#33;No te olvides! Las mejores ofertas en Mi Tienda.
<div style="width:263px;height:55px;"></div>
<br /><br />
<button onclick="window.print();">Imprimir</button>
</center>