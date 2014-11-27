<?php
/**
 * API DE CONEXIÓN CON SEGURIPAGO
 * ==============================
 * Versión: 1.0
 *
 * Creado en octubre de 2013
 * Actualizado al 7 de octubre de 2013
 */


/**
 * Constantes
 */

//-- URL de Seguripago en modo test (para pruebas), para envío de trama
define("SEGURIPAGO_URL_PAGOIN_TEST", "http://test.seguripago.pe/pagoin.php");

//-- URL de Seguripago en modo producción, para envío de trama
define("SEGURIPAGO_URL_PAGOIN_PRODUCCION", "https://pagoin.seguripago.pe/pagoin.php");

//-- URL de Seguripago en modo test (para pruebas), para confirmar recepción de data de pago
define("SEGURIPAGO_URL_PAGOOUT_ACUSE_TEST", "http://test.seguripago.pe/pagoin_acuse.php");

//-- URL de Seguripago en modo producción, para confirmar recepción de data de pago
define("SEGURIPAGO_URL_PAGOOUT_ACUSE_PRODUCCION", "https://pagoin.seguripago.pe/pagoin_acuse.php");









/**
 * CLASE PARA ENVÍO DE TRAMA
 * =========================
 *
 * Formato de la trama enviada (método POST)
 * -----------------------------------------
 * I1: Código de identificación del socio.
 * I2: Número de pedido del socio (Máx. 15 caracteres).
 * I3: Fecha/hora de venta, según socio, en formato Unix timestamp
 * I4: Moneda de venta (PEN/USD)
 * I5: Importe de venta (flotante)
 * I6: Array(código de cliente,nombre,apellido,razón social,tipo documento,numero documento,email,dirección,país,sexo)
 *     implodado con '//'
 * I7: Array(código de artículo,cantidad,precio) implodado con '//'
 * I8: Fecha/Hora de vencimiento del pago (Para medios de pago batch) (timestamp)
 * I9: HMAC-SHA1 de la data con la key del socio.
 * I10: Corresponde a la interface que quiere que se muestre: Puede ser (H)orizontal o (V)ertical,
 *      si está en blanco o no existe, se considerará lo especificado en la configuración del Socio.
 * I11: Indica los medios de pago que serán excluidos de esta compra (NUEVO!!)
 *
 */
class seguripagoEnvio {

	private $idSocio;		//-- Identificador de Socio
	private $key;				//-- Key de encriptación
	private $modo;			//-- Modo: 'test' o 'prod'

	/**
	 * Constructor
	 * @param type $idSocio: Identificador del Socio
	 * @param type $key: Key del Socio
	 * @param type $modo: Modo: 'test' o 'prod'
	 */
	function __construct($idSocio, $key, $modo) {
		$this->idSocio = $idSocio;
		$this->key = $key;
		$this->modo = $modo;
	}

	/**
	 * Método para envío de trama
	 * @param type $data: Datos a enviar
	 */
	function enviar($data) {
		/**
		* PROCESO DE ENCRIPTACIÓN DE DATA Y ASIGNACIÓN DE VARIABLES
		*/

		/**
		* Array de datos que serán encriptados
		*/
		$arrDatos=array();

		/**
		* Asignación de variables
		*/
		$arrDatos[] = $I1 = $this->idSocio;													//-- Código de e-commerce, proporcionado por Seguripago
		$arrDatos[] = $I2 = $data['num_pedido'];										//-- Número de pedido del e-commerce
		$arrDatos[] = $I3 = $data['fecha_hora'];										//-- Fecha/Hora de envío (Unixtime)
		$arrDatos[] = $I4 = $data['moneda'];												//-- Moneda (ISO 4217)
		$arrDatos[] = $I5 = $data['importe'];												//-- Importe de venta
		$arrDatos[] = $I6 = empty($data['cliente'])?'':implode("//",$data['cliente']);		//-- Serializando array datos del cliente, dejar en blanco si no se desea enviar datos de cliente.
		$arrDatos[] = $I7 = empty($data['articulo'])?'':implode("//",$data['articulo']);		//-- Serializando array de datos de artículo, dejar en blanco si no se desea enviar datos de artículos
		$arrDatos[] = $I8 = $data['vencimiento'];										//-- Fecha / Hora de vencimiento (Unixtime), en este ejemplo establecemos que el pago vensa en 72 horas
		$arrDatos[] = $this->key;																		//-- Key proporcionado por Seguripago

		/**
		* ALGORITMO DE ENCRIPTACIÓN DE DATOS
		*/
		$cadena = implode("", $arrDatos);								//-- Serializando toda la data a encriptar
		$hash = hash_hmac("sha1", $cadena, $this->key);	//-- Encriptando data
		$I9 = $hash;																		//-- Asignando data encriptada al campo 9

		/**
		* Estableciendo el tipo de pantalla de pago a mostrar.
		* En este caso se está especificando pantalla horizontal.
		* Ver la documentación para conocer otros tipos de pantalla que se pueden
		* utilizar.
		* No enviar este parámetro para utilizar la configuración establecido en
		* Seguripago, para el comercio.
		*/
		$I10 = empty($data['pantalla'])?'H':$data['pantalla'];

		/**
		* Indica el producto de Seguripago que no se quiere utilizar.
		* Ver la documentación para más detalles.
		* Este parámetro está todavía en versión de prueba.
		*/
		$I11 = empty($data['obviar'])?'':$data['obviar'];

		/**
		 * Estableciendo la URL a donde se enviará la trama
		 */
		$url_seguripago_pagoin = $this->modo=='prod'?SEGURIPAGO_URL_PAGOIN_PRODUCCION:SEGURIPAGO_URL_PAGOIN_TEST;


		/**
		* ENVIANDO LA TRAMA A SEGURIPAGO
		*/
		?>
<html>
	<body onload="document.frm.submit();">
		<form name=frm action="<?php echo $url_seguripago_pagoin; ?>" method=POST>
			<input type="hidden" name="I1" value = "<?php echo $I1; ?>">
			<input type="hidden" name="I2" value = "<?php echo $I2; ?>">
			<input type="hidden" name="I3" value = "<?php echo $I3; ?>">
			<input type="hidden" name="I4" value = "<?php echo $I4; ?>">
			<input type="hidden" name="I5" value = "<?php echo $I5; ?>">
			<input type="hidden" name="I6" value = "<?php echo $I6; ?>">
			<input type="hidden" name="I7" value = "<?php echo $I7; ?>">
			<input type="hidden" name="I8" value = "<?php echo $I8; ?>">
			<input type="hidden" name="I9" value = "<?php echo $I9; ?>">
			<input type="hidden" name="I10" value ="<?php echo $I10; ?>">
			<input type="hidden" name="I11" value ="<?php echo $I11; ?>">
		</form>
	</body>
</html>
		<?php
	}
}












/**
 * CLASE PARA RECEPCIÓN DE TRAMA - INMEDIATO
 * =========================================
 *
 * Formato de la trama recibida (método POST)
 * ------------------------------------------
 * O1: Codigo de socio
 * O2: Numero de pedido: Número con el cuál está asociado el pago en el e-commerce.
 * O3: Numero de transaccion SeguriPago: Este número debe ser mostrado como
 *     referencia al usuario, ya que le permitirá identificar el pago cuando vaya
 *     a cancelar al banco o tienda. También sirve para que el usuario pueda
 *     reportar algún incidente en el pago.
 * O4: Fecha/hora de transaccion (timestamp)
 * O5: Moneda de la transaccion
 * O6: Importe de la transaccion (aprobado)
 * O7: Resultado de la transaccion. Aprobado (1), No aprobado (2)
 * O8: Respuesta / Accion
 * O9: Texto respuesta
 * O10: Medio de pago utilizado para SeguriCrédito (en Seguricash se envía cero (0))
 * O11: Tipo de respuesta. Inmediato (1), Batch (2)
 * O12: Codigo de autorizacion
 * O13: Numero de referencia generado por el medio de pago
 * O14: HASH de la transaccion. No utilizado todavía.
 * O15: Código del Producto de SeguriPago: (1) SeguriCrédito, (2) SeguriCash.
 *
 */
class seguripagoRecepcionInmediato {

	private $idSocio;		//-- Identificador de Socio
	private $key;				//-- Key de encriptación
	private $modo;			//-- Modo: 'test' o 'prod'
	private $data=null;	//-- Data recibida por POST

	/**
	 * Constructor
	 * @param type $idSocio: Identificador del Socio
	 * @param type $key: Key del Socio
	 * @param type $modo: Modo: 'test' o 'prod'
	 */
	function seguripagoRecepcionInmediato($idSocio, $key, $modo) {
		$this->idSocio = $idSocio;
		$this->key = $key;
		$this->modo = $modo;
	}

	/**
	 * Método para envío de trama
	 * @param type $data: Datos a enviar
	 */
	function recibir() {

		/**
		 * Validando que los datos existan
		 */
		if(!isset($_POST, $_POST['O1'], $_POST['O2'], $_POST['O3'], $_POST['O4'], $_POST['O5'], $_POST['O6'], $_POST['O7'], $_POST['O8'], $_POST['O9'], $_POST['O10'], $_POST['O10'], $_POST['O12'], $_POST['O13'], $_POST['O14'], $_POST['O15'], $_POST['O16'], $_POST['O17'])) {echo "Error al recepcionar datos."; exit();}

		/**
		 * Asignando variables
		 */
		$data = array(
			"idSocio"							=> $_POST['O1'],	//-- Identificador de Socio
			"num_pedido"					=> $_POST['O2'],	//-- Número de pedido de Socio
			"num_transaccion"			=> $_POST['O3'],	//-- Número de transacción generado por Seguripago
			"fecha_hora_trans"		=> $_POST['O4'],	//-- Fecha/hora de transacción en Unixtime
			"moneda"							=> $_POST['O5'],	//-- Moneda
			"importe"							=> $_POST['O6'],	//-- Importe aprobado
			"resultado"						=> $_POST['O7'],	//-- Resultado de la transaccion. Aprobado (1), No aprobado (2)
			"cod_respuesta"				=> $_POST['O8'],	//-- Código de respuesta, generado por el medio de pago
			"txt_respuesta"				=> $_POST['O9'],	//-- Texto descriptivo de respuestas, generado por el medio de pago
			"medio_pago"					=> $_POST['O10'],	//-- Código de Medio de pago utilizado para SeguriCrédito (si es Seguricash se envía cero (0)). (1) Visa, (2) Mastercard, (3) American Express
			"tipo_respuesta"			=> $_POST['O11'],	//-- Tipo de respuestas: Inmediato (1), Batch (2)
			"cod_autoriza"				=> $_POST['O12'],	//-- Código de autorización, enviado por algunos medios de pago
			"num_referencia"			=> $_POST['O13'],	//-- Número de referencia, enviado por algunos medios e pago
			"hash"								=> $_POST['O14'],	//-- Resultado de la transaccion. Aprobado (1), No aprobado (2)
			"cod_producto"				=> $_POST['O15'],	//-- Código del Producto de SeguriPago: (1) SeguriCrédito, (2) SeguriCash.
			"num_tarjeta"					=> $_POST['O16'],	//-- Número de tarjeta de crédito asteriscada
			"nom_tarjetahabiente"	=> $_POST['O17'],	//-- Nombre del tarjetahabiente
			"fecha_vencimiento"	=> $_POST['O18'],	//-- Fecha de Vencimiento
		);

		/**
		 * Validando que el número de pedido no esté vacío
		 */
		if(empty($data['num_pedido'])) {echo "Error en n&uacute;mero de pedido."; exit();}

		/**
		* Validación hash
		*/
		if($data['cod_producto']=="1") {
			$hash = Seguripago_HASH_Generation($data['idSocio'], $data['num_pedido'], $data['cod_autoriza'], $data['num_referencia']);
			if($hash<>$data['hash']) {echo "Error en validaci&oacute;n de hash."; exit();}
		}

		$this->data = $data;

		return $data;
	}






	/**
	 * Función para confirmar la recepción de la data
	 */
	function confirmar() {
		if(empty($this->data)) {echo "Array de datos vac&iacute;o, llame al m&eacute;todo recibir()."; exit();}

		if($this->data["tipo_respuesta"] == "1") {

			/**
			* Estableciendo la URL a donde se enviará la confirmación
			*/
			$url = $this->modo=='prod'?SEGURIPAGO_URL_PAGOOUT_ACUSE_PRODUCCION:SEGURIPAGO_URL_PAGOOUT_ACUSE_TEST;

			/**
			 * Generando hash
			 */
			$hash = hash_hmac("sha1", $this->data["idSocio"].$this->data["num_transaccion"], $this->key);

			/**
			 * Enviando confirmación por medio de ajax
			 */
			?>
				<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js?ver=3.5.2'></script>
				<script>
					var jqxhr = $.ajax({
						url				: '<?php print $url; ?>',
						type			: 'GET',
						async			: true,
						dataType	: 'jsonp',
						data : {
								I1:'<?php echo $this->data["idSocio"]; ?>',
								I2:'<?php echo $this->data["num_transaccion"]; ?>',
								I3:'<?php echo $hash; ?>'
							}
						});
				</script>
			<?php
		}
	}





	/**
	* Devuelve el html (renderizado) de la pantalla de Seguricash
	 * @param type $ruta_logo: Ruta del logo del comercio.
	 * @param type $nombre_comercio: Nombre del empresa/comercio, con el que aparecerá en el renderizado de la orden de pago.
	 * @param type $completo: Booleano que indica si se muestra plantilla en modo completo o no.
	 * @return type: Devuelve el html renderizado.
	 */
	function pantallaSeguricash($ruta_logo, $nombre_comercio, $completo=true) {
		if(empty($this->data)) {echo "Array de datos vac&iacute;o, llame al m&eacute;todo recibir()."; exit();}

		$render = new seguripagoPlantillas($this->idSocio, $this->key, $this->modo);

		$html = $render->seguricashLocal($ruta_logo, $nombre_comercio, $completo, $this->data['num_transaccion'], $this->data['importe'], $this->data['moneda'], $this->data['cod_producto'],$this->data['fecha_vencimiento']);

		return $html;
	}



}




















/**
 * CLASE PARA RECEPCIÓN DE TRAMA - DIFERIDO
 * ========================================
 *
 * Formato de la trama recibida (método POST)
 * ------------------------------------------
 * O1: Codigo de socio
 * O2: Numero de pedido: Número con el cuál está asociado el pago en el e-commerce.
 * O3: Numero de transaccion SeguriPago: Este número debe ser mostrado como
 *     referencia al usuario, ya que le permitirá identificar el pago cuando vaya
 *     a cancelar al banco o tienda. También sirve para que el usuario pueda
 *     reportar algún incidente en el pago.
 * O4: Fecha/hora de transaccion (timestamp)
 * O5: Moneda de la transaccion
 * O6: Importe de la transaccion (aprobado)
 * O7: Resultado de la transaccion. Aprobado (1), No aprobado (2)
 * O8: Respuesta / Accion
 * O9: Texto respuesta
 * O10: Medio de pago utilizado para SeguriCrédito (en Seguricash se envía cero (0))
 * O11: Tipo de respuesta. Inmediato (1), Batch (2)
 * O12: Codigo de autorizacion
 * O13: Numero de referencia generado por el medio de pago
 * O14: HASH de la transaccion. No utilizado todavía.
 * O15: Código del Producto de SeguriPago: (1) SeguriCrédito, (2) SeguriCash.
 *
 */
class seguripagoRecepcionDiferido {

	private $idSocio;		//-- Identificador de Socio
	private $key;				//-- Key de encriptación
	private $modo;			//-- Modo: 'test' o 'prod'
	private $data=null;	//-- Data recibida por POST

	/**
	 * Constructor
	 * @param type $idSocio: Identificador del Socio
	 * @param type $key: Key del Socio
	 * @param type $modo: Modo: 'test' o 'prod'
	 */
	function seguripagoRecepcionDiferido($idSocio, $key, $modo) {
		$this->idSocio = $idSocio;
		$this->key = $key;
		$this->modo = $modo;
	}

	/**
	 * Método para envío de trama
	 * @param type $data: Datos a enviar
	 */
	function recibir() {

		/**
		 * Validando que los datos existan
		 */
		if(!isset($_POST, $_POST['O1'], $_POST['O2'], $_POST['O3'], $_POST['O4'], $_POST['O5'], $_POST['O6'], $_POST['O7'], $_POST['O8'], $_POST['O9'], $_POST['O10'], $_POST['O10'], $_POST['O12'], $_POST['O13'], $_POST['O14'], $_POST['O15'], $_POST['O16'], $_POST['O17'])) return '01';

		/**
		 * Asignando variables
		 */
		$data = array(
			"idSocio"							=> $_POST['O1'],	//-- Identificador de Socio
			"num_pedido"					=> $_POST['O2'],	//-- Número de pedido de Socio
			"num_transaccion"			=> $_POST['O3'],	//-- Número de transacción generado por Seguripago
			"fecha_hora_trans"		=> $_POST['O4'],	//-- Fecha/hora de transacción en Unixtime
			"moneda"							=> $_POST['O5'],	//-- Moneda
			"importe"							=> $_POST['O6'],	//-- Importe aprobado
			"resultado"						=> $_POST['O7'],	//-- Resultado de la transaccion. Aprobado (1), No aprobado (2)
			"cod_respuesta"				=> $_POST['O8'],	//-- Código de respuesta, generado por el medio de pago
			"txt_respuesta"				=> $_POST['O9'],	//-- Texto descriptivo de respuestas, generado por el medio de pago
			"medio_pago"					=> $_POST['O10'],	//-- Código de Medio de pago utilizado para SeguriCrédito (si es Seguricash se envía cero (0)). (1) Visa, (2) Mastercard, (3) American Express
			"tipo_respuesta"			=> $_POST['O11'],	//-- Tipo de respuestas: Inmediato (1), Batch (2)
			"cod_autoriza"				=> $_POST['O12'],	//-- Código de autorización, enviado por algunos medios de pago
			"num_referencia"			=> $_POST['O13'],	//-- Número de referencia, enviado por algunos medios e pago
			"hash"								=> $_POST['O14'],	//-- Resultado de la transaccion. Aprobado (1), No aprobado (2)
			"cod_producto"				=> $_POST['O15'],	//-- Código del Producto de SeguriPago: (1) SeguriCrédito, (2) SeguriCash.
			"num_tarjeta"					=> $_POST['O16'],	//-- Número de tarjeta de crédito asteriscada
			"nom_tarjetahabiente"	=> $_POST['O17'],	//-- Nombre del tarjetahabiente
			"fecha_vencimiento"	=> $_POST['O18'],	//-- Fecha de Vencimiento
		);

		/**
		 * Validando que el número de pedido no esté vacío
		 */
		if(empty($data['num_pedido'])) return '02';

		/**
		* Validación hash
		*/
		$hash = Seguripago_HASH_Generation($data['idSocio'], $data['num_pedido'], $data['cod_autoriza'], $data['num_referencia']);
		if($hash<>$data['hash']) return '03';

		$this->data = $data;

		return $data;
	}






	/**
	 * Función para confirmar la recepción de la data
	 */
	function confirmar() {
		echo "1";
	}


}










/**
 * Clase que devuelve el renderizado de plantillas
 */
class seguripagoPlantillas {

	private $idSocio;		//-- Identificador de Socio
	private $key;				//-- Key de encriptación
	private $modo;			//-- Modo: 'test' o 'prod'

	/**
	 * Constructor
	 * @param type $idSocio: Identificador del Socio
	 * @param type $key: Key del Socio
	 * @param type $modo: Modo: 'test' o 'prod'
	 */
	function seguripagoPlantillas($idSocio, $key, $modo) {
		$this->idSocio = $idSocio;
		$this->key = $key;
		$this->modo = $modo;
	}

	/**
	 * Método que renderizado de pantalla de SeguriCash que consulta de la plantilla local
	 * @param type $ruta_logo: URL del logo del comercio
	 * @param type $nombre_comercio: Nombre del empresa/comercio, con el que aparecerá en el renderizado de la orden de pago.
	 * @param type $completo: Booleano que indica si la plantilla se mostrará completo o no
	 * @param type $num_transaccion: Número de trasacción de SeguriPago
	 * @param type $importe: Importe
	 * @param type $moneda: Moneda, por defecto 'PEN'
	 * @param type $cod_producto: Producto: (1) SeguriCrédito, (2) SeguriCash, por defecto: 1
	 */
	function seguricashLocal($ruta_logo, $nombre_comercio, $completo, $num_transaccion, $importe, $moneda="PEN", $cod_producto=1,$fecha_vencimiento) {
		$modo = $this->modo;
		ob_start();
		include "plantillas/plantilla_seguricash.php";
		return ob_get_clean();
	}
}







/**
 * &&&&&&&&&&&&&&&&&&&&&&&&&& FUNCIONES ACCESITARIAS &&&&&&&&&&&&&&&&&&&&&&&&&&&
 */


/* HASH Generation para Seguripago
	*
	* Input:
	*   I1: Codigo de Socio
	*   I2: Numero de pedido
	*   I3: Codigo de autorizacion
	*   I4: Numero de transaccion o referencia
	*
*/
function Seguripago_HASH_Generation($socio,$pedido,$autoriza,$referencia) {

	$arrDatos = array();
	$salt = "SEGURIPAGO";

	$arrDatos[] = "";
	$arrDatos[] = $socio;
	$arrDatos[] = $pedido;
	$arrDatos[] = $autoriza;
	$arrDatos[] = $referencia;
	$arrDatos[] = $salt;

	return sha1(implode($arrDatos));

}

?>
