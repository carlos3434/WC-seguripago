<?php
/**
 * Plugin Name: WooCommerce Seguripago
 *
 * Description: Pasarela de pago Seguripago para WooCommerce.
 * Author: Juan carlos rojas toralva
 * Author URI:
 * Version: 1.0.0
 * License:
 * Text Domain: wc_seguripago_payment_gateway
 * Domain Path: languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * se comprueba si está activo WooCommerce
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {


    //cargar plugin
    add_action( 'plugins_loaded', 'init_wc_seguripago_payment_gateway', 0 );

    function init_wc_seguripago_payment_gateway() {

        //salir si no esta activa la clase
        if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }

        //definir ruta del plugin
        DEFINE ('PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );

        /**
         * Pasarela pago: Seguripago
         *
         * Proporciona a seguripago un Estándar de pasarela de pago.
         *
         * @class         WC_seguripago
         * @extends        WC_Payment_Gateway
         * @version        0.1
         * @package
         * @author         juan carlos rojas toralva
         */

        class WC_seguripago extends WC_Payment_Gateway {

            /**
             * Constructor de la pasarela de pago
             *
             * @access public
             * @return void
             */

            public function __construct()
            {

                global $woocommerce;

                $this->includes();
                $this->id                   = 'seguripago';
                $this->icon                 = home_url() . '/wp-content/plugins/' . dirname( plugin_basename( __FILE__ ) ) . '/assets/images/seguripago.jpg';
                $this->method_title         = __( 'seguripago', 'WC-seguripago' );
                $this->method_description   = __( 'Acepta tarjetas de credito o debito, cash, efectivo o transferencias bancarias', 'WC-seguripago' );
                //boton para seleccionar seguripago en la lista de pasarelas de pago
                $this->order_button_text    = __( 'Proceder al pago', 'WC-seguripago' );
                $this->has_fields           = false;

                //Cargue los campos de formulario.
                $this->init_form_fields();

                //Cargue los ajustes.
                $this->init_settings();

                // definicion de variables
                $this->title                = $this->get_option( 'title' );
                $this->description          = $this->get_option( 'description' );
                $this->sp_idSocio           = $this->get_option( 'sp_idSocio' );
                $this->sp_key               = $this->get_option( 'sp_key' );
                $this->sp_modo              = $this->get_option( 'sp_modo' );
                $this->sp_nombre_comercio   = $this->get_option( 'sp_nombre_comercio' );

                add_action('woocommerce_api_'. strtolower(get_class($this)), array($this, 'check_response' ) );//1
                add_action('woocommerce_receipt_seguripago', array(&$this, 'receipt_page'));
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

                add_action( 'woocommerce_thankyou_'. strtolower(get_class($this)), array($this, 'custom_thankyou_page' ));

                add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'custom_thankyou_page' ) );

                if ( !$this->is_valid_for_use() ) $this->enabled = false;

                // Active logs.
                if ( 'test' == $this->sp_modo ) {
                    if ( class_exists( 'WC_Logger' ) ) {
                        $this->log = new WC_Logger();
                    } else {
                        $this->log = WC_seguripago::woocommerce_instance()->logger();
                    }
                }

            }
            public function log_payment($mensaje = ''){
                $this->log->add( $this->id, 'Respuesta no válida Recibido de Seguripago. Error: '.$mensaje );
                return "error al recepcionar los datos";


            }

            /**
             * Mensaje de pagina gracias
             *
             * @access public
             * @param  nothing
             * @return string
             */
            public function custom_thankyou_page()
            {

                global $woocommerce;
                //respuesta del comercion tras envio de post desde seguripago
                $sp_recepcion = new seguripagoRecepcionInmediato($this->sp_idSocio, $this->sp_key, $this->sp_modo);

                $data = $sp_recepcion->recibir();
                $id_order = ltrim($data['num_pedido'], '0');
                $id_order = (int) $id_order;
                $order = new WC_Order( $id_order );
                /**
                 * Valide aquí si el número de pedido ($data['num_pedido']) existe en su sistema.
                 */

                if ( !isset($order->id))
                    $mensaje = $this->log_payment("numero de orden invalido");

                /**
                 * Valide aquí si el número de pedido ya fue cancelado.
                 */
                if ( $order->get_status() <> 'pending' )
                    $mensaje = $this->log_payment("estado de pedido");

                /**
                 * Valide aquí si el importe informado por SeguriPago ($data['importe']) coincide
                 * con el monto registrado en su sistema.
                 */
                //$importe = (int) $data['importe'];

                if ( $order->get_total() <> (int) $data['importe'])
                    $mensaje = $this->log_payment("importe del pedido");

                /**
                 * Validar aquí el vencimiento del pago.
                 */
                //$data['fecha_vencimiento']

                //validando la moneda
                if ( 'PEN' <> $order->get_order_currency())
                    $mensaje = $this->log_payment("moneda del pedido");

                if ( isset($mensaje) ) return $mensaje;
                /**
                 * Actualice su base de datos con la información recibida.
                 */

                $sp_recepcion->confirmar();



                if($data['resultado'] == "1")
                {

                    /**
                     * MENSAJE PARA PAGO INMEDIATO (SeguriCrédito)
                     */
                    if($data['tipo_respuesta'] == "1")
                    {
                        /**
                         * Envíar aquí un mensaje al usuario indicando que la operación fue aceptada
                         * y dar otra información que crea conveniente.
                         */

                        include("includes/seguripago/plantillas/respuesta_inmediato_aceptado.php");

                        // Mark order processing
                        $order->update_status( 'processing' );

                        /**
                         * MENSAJE PARA PAGO DIFERIDO (SeguriCash y otros)
                         */
                    }
                    else
                    {
                        /**
                         * Envíar aquí un mensaje al usuario indicando que se generó un número de cupón
                         * a ser cancelado en las entidades financieras BCP, Scotia, según lo establecido
                         * en SeguriPago.
                         *
                         * Se pasa como parámetro la ruta del logo del comercio y el segundo parámetro
                         * indica si se mostrará la orden de pago, en modo completo (true) o resumido (false - default)
                         * Ver MANUAL.txt para más detalle.
                         */
                        $ruta_logo = PLUGIN_DIR . 'assets/images/qhatu-logo.png';
                        $html = $sp_recepcion->pantallaSeguricash( $ruta_logo, $this->title, true);

                        /**
                         * Enviar $html por correo (opcional)
                         */

                        $subject = sprintf( __( 'Pendiente de pago para la Orden %s ', 'Seguripago' ), $id_order );

                        $customer = new WC_Customer( $order_id );

                        //Filtro para indicar que email debe ser enviado en modo HTML
                        add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));

                        wp_mail( $order->billing_email, $subject, $html, 'header');

                        echo "<center><div style='width:700px;'>$html</div></center>";

                        // Change the status to pending / unpaid
                        //$order->update_status('pending');

                    }
                /**
                 * ---------------------- MENSAJE PARA PAGO DESAPROBADO ------------------------
                 */
                } else {
                    /**
                     * Envíar aquí un mensaje al usuario indicando que la operación no fue aceptada.
                     */
                    $mailer = WC()->mailer();

                    $message = $mailer->wrap_message( 'Error al pagar' , 'Pago a través de seguripago falló' );

                    $subject = sprintf( __( 'Pendiente de pago para la Orden %s ', 'Seguripago' ), $id_order );

                    //Filtro para indicar que email debe ser enviado en modo HTML
                    add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));

                    wp_mail( $order->billing_email, $subject, $message, 'header');

                    // Mark order complete
                    $message = 'Pago a través de seguripago falló';
                    $order->update_status('failed', $message );

                    include("includes/seguripago/plantillas/respuesta_inmediato_rechazado.php");
                }
                // Empty cart and clear session
                $woocommerce->cart->empty_cart();

            }

            /**
             * Includes.
             *
             * @return void
             */
            private function includes()
            {
                include_once("includes/seguripago/seguripago_api.php");
            }

            /**
             * Comprueba la respuesta del API.
             *
             * @access public
             * @param  nothing
             * @return void
             */
             function check_response()
            {

                $sp_recepcion = new seguripagoRecepcionDiferido($this->sp_idSocio, $this->sp_key, $this->sp_modo);
                $data = $sp_recepcion->recibir();
                if(!is_array($data)) {
                    switch($data) {
                        case '01': echo "Error al recepcionar datos."; break;
                        case '02': echo "Error en n&uacute;mero de pedido."; break;
                        case '03': echo "Error en validaci&oacute;n de hash."; break;
                    }
                    exit();
                }

                /**
                 * ---------------------- PROCESANDO PAGO APROBADO ---------------------------
                 */
                if($data["resultado"] == "1") {
                    /**
                    * Informar al usuario, por correo, informando de la aprobación de su pago,
                    * indicar información adicional para que acceda al producto o servicio.
                    */

                    $id_order = ltrim($data['num_pedido'], '0');

                    $id_order = (int) $id_order;

                    $order = new WC_Order( $id_order );

                    // Mark order complete
                    //$order->payment_complete();
                    // Mark order processing
                    $order->update_status( 'processing' );
                }

                /**
                 * Enviando confirmación de recibo de datos
                 */
                $sp_recepcion->confirmar();

            }

            /**
             * Compruebe si este pasarela de pago está habilitada y disponible en el país del usuario
             *
             * @access public
             * @return bool
             */
            function is_valid_for_use() {
                if (!in_array(get_woocommerce_currency(), array('PEN'))) return false;
                return true;

            }

            /**
             * Las opciones del panel de administración
             *
             * @since 1.0.0
             */
            public function admin_options() {
                //configuracion por woocomerce
                ?>
                <h3><?php _e('seguripago', 'wc_seguripago_payment_gateway'); ?></h3>
                <table class="form-table">
                    <?php
                    if ( $this->is_valid_for_use() ) :
                        // Generate the HTML For the settings form
                        $this->generate_settings_html();
                    else :
                        ?>
                        <div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'wc_seguripago_payment_gateway' ); ?></strong>: <?php _e( 'Seguripago no soporta su moneda tienda..', 'wc_seguripago_payment_gateway' ); ?></p></div>
                        <?php
                    endif;
                    ?>
                </table><!--/.form-table-->
                <?php
            }

            /**
             * Inicializar configuración de la pasarela de pago. Los campos de formulario
             *
             * @access public
             * @return void
             */
            function init_form_fields()
            {
                global $woocommerce;

                $this->form_fields = array(
                    'enabled' => array
                                (
                                    'title' => __( 'Enable/Disable', 'wc_seguripago_payment_gateway' ),
                                    'type' => 'checkbox',
                                    'label' => __( 'Enable seguripago', 'wc_seguripago_payment_gateway' ),
                                    'default' => 'yes'
                                ),
                    'title' => array
                                (
                                    'title' => __( 'Title', 'wc_seguripago_payment_gateway' ),
                                    'type' => 'text',
                                    'description' => __( 'This is the title the customer can see when checking out', 'wc_seguripago_payment_gateway' ),
                                    'default' => __( 'seguripago', 'wc_seguripago_payment_gateway' )
                                ),
                    'description' => array
                                (
                                    'title' => __( 'Description', 'wc_seguripago_payment_gateway' ),
                                    'type' => 'text',
                                    'description' => __( 'This is the description the customer can see when checking out', 'wc_seguripago_payment_gateway' ),
                                    'default' => __("Pay with Credit Card via seguripago", 'wc_seguripago_payment_gateway')
                                ),
                    'sp_idSocio' => array
                                (
                                    'title' => __( 'id', 'wc_seguripago_payment_gateway' ),
                                    'type' => 'text',
                                    'required'    => true,
                                    'description' => __( 'identificador publico del socio', 'wc_seguripago_payment_gateway' ),
                                    //'default' => __(home_url() . "/?wc-api=WC_seguripago" , 'wc_seguripago_payment_gateway')
                                    'default' => "65"
                                ),
                    'sp_key' => array
                                (
                                    'title' => __( 'key', 'wc_seguripago_payment_gateway' ),
                                    'type' => 'text',
                                    'required'    => true,
                                    'description' => __( 'key generado para la identificacion del socio', 'wc_seguripago_payment_gateway' ),
                                    'default' => '58c44a36871ae77f1b966dfae445fcbc'
                                ),
                    'sp_modo' => array
                                (
                                    'title' => __( 'Entorno', 'wc_seguripago_payment_gateway' ),
                                    'type' => 'select',
                                    'required'    => true,
                                    'description' => __( 'Este es el nombre del entrono en el que se ejecuta el comercio', 'wc_seguripago_payment_gateway' ),
                                    'default'     => 'test',
                                    'options'     => array(
                                'test' => __('test', 'wc_seguripago_payment_gateway' ),
                                'prod' => __('prod', 'wc_seguripago_payment_gateway' ),
                                ),
                                ),
                    'sp_nombre_comercio' => array
                                (
                                    'title' => __( 'Nombre comercio', 'wc_seguripago_payment_gateway' ),
                                    'type' => 'text',
                                    'required'    => true,
                                    'description' => __( 'Nombre asignado al comercio', 'wc_seguripago_payment_gateway' ),
                                    'default' => 'woocomerce'
                                )
                );
            }

            /**
             * Obtener ruta plantillas
             *
             * @return string
             */
            public static function get_templates_path() {
                return plugin_dir_path( __FILE__ ) . 'templates/';
            }
            /**
             * Procesar el pago y devolver el resultado
             *
             * @access public
             * @param int $order_id
             * @return array
             */
            function process_payment( $order_id )
            {

                //cuando hace click  en proceder al pago, luego de elegir seguripago
                //finalizar-comprar/        1

                $order = new WC_Order( $order_id );

                return array
                (
                    'result'     => 'success',
                    'redirect'    => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))))
                );
            }
            /**
             * La página de salida de la orden recibida.
             *
             * @param  $order_id Order ID.
             *
             * @return string
             */
            function receipt_page( $order_id )
            {
                //antes de enviar informacion a seguripago -> finalizar-comprar/     2

                $order = new WC_Order( $order_id );

                $data = array();
                $numero_pedido=str_pad($order->id, 8, "0", STR_PAD_LEFT);

                $sp_envio = new seguripagoEnvio($this->sp_idSocio, $this->sp_key, $this->sp_modo);
                /**
                 * Array con la data a enviar
                 */
                $data = array(
                    'num_pedido'    => $numero_pedido,                //-- $numero_pedido
                    'fecha_hora'    => strtotime($order->order_date),                                //-- Fecha/Hora de creación en Unixtime
                    'moneda'            => 'PEN',                                    //-- Moneda (ISO 4217)
                    'importe'            => number_format($order->get_total(), 2, '.', ''),                                    //-- Importe
                    'vencimiento'    => (time() + 72 * 3600),    //-- Fecha/Hora de vencimiento en Unixtime
                    //'cliente'            => $dato_cliente_array,        //-- Datos de cliente, opcional
                    //'articulo'        => $dato_articulo_array,    //-- Datos de artículo, opcional
                    //'pantalla'        => 'H',                                        //-- Tipo de pantalla a utilizar: (H)orizontal, (V)ertical, opcional
                    //'obviar'            => '1',                                        //-- Producto de Seguripago que no quiere que aparezca: (1) SeguriCrédito, (2) SeguriCash, opcional.
                );
                /**
                 * Enviamos trama a través del méotodo de Seguripago
                 */
                $sp_envio->enviar($data);
            }

        }
        /**
         * añadir pasarela de pago a WooCommerce.
         *
         * @param   array $methods Métodos de pago WooCommerce.
         *
         * @return  array          metodos de pago con seguripago
         */
        function woocommerce_seguripago_add_gateway( $methods )
        {
            $methods[] = 'WC_seguripago';
            return $methods;
        }
        add_filter( 'woocommerce_payment_gateways', 'woocommerce_seguripago_add_gateway' );

    }

}
