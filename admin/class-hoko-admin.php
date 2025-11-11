<?php
/**
 * La funcionalidad específica del área de administración del plugin.
 *
 * @package    Hoko360
 * @subpackage Hoko360/admin
 */

class Hoko_Admin {

	/**
	 * El ID de este plugin.
	 *
	 * @var string $plugin_name El ID de este plugin.
	 */
	private $plugin_name;

	/**
	 * La versión de este plugin.
	 *
	 * @var string $version La versión actual de este plugin.
	 */
	private $version;

	/**
	 * Inicializa la clase y establece sus propiedades.
	 *
	 * @param string $plugin_name El nombre de este plugin.
	 * @param string $version La versión de este plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Registra los estilos para el área de administración.
	 */
	public function enqueue_styles( $hook ) {
		// Solo cargar en páginas de Hoko 360
		if ( strpos( $hook, 'hoko-360' ) === false ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/hoko-woocommerce-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Registra los scripts para el área de administración.
	 */
	public function enqueue_scripts( $hook ) {
		// Solo cargar en páginas de Hoko 360
		if ( strpos( $hook, 'hoko-360' ) === false ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/hoko-admin.js',
			array( 'jquery' ),
			$this->version,
			true // Cargar en el footer
		);

		// Pasar datos al JavaScript
		wp_localize_script(
			$this->plugin_name,
			'hokoAdmin',
			array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'hoko_auth_nonce' ),
				'ordersUrl' => admin_url( 'admin.php?page=hoko-360-orders' ),
			)
		);
	}

	/**
	 * Registra el menú de administración.
	 */
	public function add_admin_menu() {
		// Obtener el icono personalizado
		$icon_url = $this->get_menu_icon();

		// Menú principal
		add_menu_page(
			__( 'Hoko 360', 'hoko-360' ),           // Título de la página
			__( 'Hoko 360', 'hoko-360' ),           // Título del menú
			'manage_options',                        // Capacidad requerida
			'hoko-360',                              // Slug del menú
			array( $this, 'display_auth_page' ),    // Función callback
			$icon_url,                               // Icono personalizado
			56                                       // Posición
		);

		// Submenú: Iniciar sesión
		add_submenu_page(
			'hoko-360',                              // Slug del menú padre
			__( 'Iniciar sesión', 'hoko-360' ),     // Título de la página
			__( 'Iniciar sesión', 'hoko-360' ),     // Título del submenú
			'manage_options',                        // Capacidad requerida
			'hoko-360',                              // Slug (mismo que el padre para que sea la primera opción)
			array( $this, 'display_auth_page' )     // Función callback
		);

		// Submenú: Órdenes de compra
		add_submenu_page(
			'hoko-360',                              // Slug del menú padre
			__( 'Órdenes de compra', 'hoko-360' ),  // Título de la página
			__( 'Órdenes de compra', 'hoko-360' ),  // Título del submenú
			'manage_options',                        // Capacidad requerida
			'hoko-360-orders',                       // Slug del submenú
			array( $this, 'display_orders_page' )   // Función callback
		);

		// Submenú oculto: Confirmar orden
		add_submenu_page(
			null,                                    // Sin menú padre (oculto)
			__( 'Confirmar Orden', 'hoko-360' ),    // Título de la página
			__( 'Confirmar Orden', 'hoko-360' ),    // Título del submenú
			'manage_options',                        // Capacidad requerida
			'hoko-360-order-confirm',                // Slug del submenú
			array( $this, 'display_order_confirm_page' ) // Función callback
		);
	}

	/**
	 * Obtiene el icono del menú (SVG codificado en base64).
	 *
	 * @return string URL del icono o dashicon.
	 */
	private function get_menu_icon() {
		$icon_path = plugin_dir_path( __FILE__ ) . 'images/hoko-icon.svg';
		
		if ( file_exists( $icon_path ) ) {
			$icon_svg = file_get_contents( $icon_path );
			// Codificar SVG en base64 para usarlo como data URI
			return 'data:image/svg+xml;base64,' . base64_encode( $icon_svg );
		}
		
		// Fallback a dashicon si no existe el archivo
		return 'dashicons-admin-generic';
	}

	/**
	 * Muestra la página de autentificación.
	 */
	public function display_auth_page() {
		// Obtener token guardado
		$token = get_option( 'hoko_360_auth_token', '' );
		$is_authenticated = ! empty( $token );
		
		require_once plugin_dir_path( __FILE__ ) . 'partials/hoko-admin-auth.php';
	}

	/**
	 * Muestra la página de órdenes de compra.
	 */
	public function display_orders_page() {
		// Verificar si está autenticado
		$token = get_option( 'hoko_360_auth_token', '' );
		$is_authenticated = ! empty( $token );
		
		// Obtener órdenes de WooCommerce
		$orders = array();
		if ( $is_authenticated ) {
			$orders = $this->get_woocommerce_orders();
		}
		
		require_once plugin_dir_path( __FILE__ ) . 'partials/hoko-admin-orders.php';
	}

	/**
	 * Muestra la página de confirmación de orden.
	 */
	public function display_order_confirm_page() {
		// Verificar si está autenticado
		$token = get_option( 'hoko_360_auth_token', '' );
		$is_authenticated = ! empty( $token );
		
		// Obtener ID de orden desde parámetros
		$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
		
		// Obtener orden de WooCommerce
		$order = null;
		if ( $order_id && $is_authenticated ) {
			$order = wc_get_order( $order_id );
		}
		
		require_once plugin_dir_path( __FILE__ ) . 'partials/hoko-admin-order-confirm.php';
	}

	/**
	 * Obtiene las órdenes de WooCommerce con su estado de sincronización.
	 *
	 * @param int $limit Número de órdenes a obtener.
	 * @return array Lista de órdenes.
	 */
	private function get_woocommerce_orders( $limit = 20 ) {
		global $wpdb;
		
		$orders = wc_get_orders(
			array(
				'limit'   => $limit,
				'orderby' => 'date',
				'order'   => 'DESC',
				'status'  => array( 'wc-processing', 'wc-completed', 'wc-pending' ),
			)
		);
		
		$table_name = $wpdb->prefix . 'hoko_orders';
		$orders_data = array();
		
		foreach ( $orders as $order ) {
			$order_id = $order->get_id();
			
			// Obtener estado de sincronización
			$sync_data = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM $table_name WHERE order_id = %d",
					$order_id
				)
			);
			
			$orders_data[] = array(
				'order'      => $order,
				'sync_status' => $sync_data ? (int) $sync_data->sync_status : 0,
				'sync_message' => $sync_data ? $sync_data->sync_message : '',
				'hoko_order_id' => $sync_data ? $sync_data->hoko_order_id : '',
				'synced_at' => $sync_data ? $sync_data->synced_at : '',
			);
		}
		
		return $orders_data;
	}

	/**
	 * Obtiene el token de autentificación guardado.
	 *
	 * @return string Token de autentificación o cadena vacía si no existe.
	 */
	public function get_auth_token() {
		return get_option( 'hoko_360_auth_token', '' );
	}

	/**
	 * Verifica si el usuario está autenticado.
	 *
	 * @return bool True si está autenticado, false en caso contrario.
	 */
	public function is_authenticated() {
		$token = $this->get_auth_token();
		return ! empty( $token );
	}

	/**
	 * Obtiene el endpoint de API según el país.
	 *
	 * @param string $country Código del país.
	 * @return string URL del endpoint.
	 */
	private function get_api_endpoint( $country ) {
		$endpoints = array(
			'colombia' => 'https://v4.hoko.com.co/api/login',
			'ecuador'  => 'https://hoko.com.ec/api/login',
			'usa'      => 'https://hoko360.com/api/login',
		);

		return isset( $endpoints[ $country ] ) ? $endpoints[ $country ] : $endpoints['colombia'];
	}

	/**
	 * Obtiene la URL base de la API según el país (sin /api/login).
	 *
	 * @param string $country Código del país.
	 * @return string URL base de la API.
	 */
	public function get_api_base_url( $country = '' ) {
		if ( empty( $country ) ) {
			$country = get_option( 'hoko_360_auth_country', 'colombia' );
		}

		$base_urls = array(
			'colombia' => 'https://v4.hoko.com.co/api',
			'ecuador'  => 'https://hoko.com.ec/api',
			'usa'      => 'https://hoko360.com/api',
		);

		return isset( $base_urls[ $country ] ) ? $base_urls[ $country ] : $base_urls['colombia'];
	}

	/**
	 * Maneja la petición AJAX de autentificación.
	 */
	public function handle_auth_request() {
		// Verificar nonce
		check_ajax_referer( 'hoko_auth_nonce', 'nonce' );

		// Verificar permisos
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'hoko-360' ) ) );
		}

		// Obtener datos del formulario
		$email    = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$password = isset( $_POST['password'] ) ? $_POST['password'] : '';
		$country  = isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : 'colombia';

		// Validar datos
		if ( empty( $email ) || empty( $password ) || empty( $country ) ) {
			wp_send_json_error( array( 'message' => __( 'Por favor completa todos los campos.', 'hoko-360' ) ) );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Por favor ingresa un email válido.', 'hoko-360' ) ) );
		}

		// Validar país
		$valid_countries = array( 'colombia', 'ecuador', 'usa' );
		if ( ! in_array( $country, $valid_countries, true ) ) {
			wp_send_json_error( array( 'message' => __( 'País no válido.', 'hoko-360' ) ) );
		}

		// Obtener endpoint según el país
		$api_endpoint = $this->get_api_endpoint( $country );

		// Realizar petición a la API de Hoko
		$response = wp_remote_post(
			$api_endpoint,
			array(
				'method'  => 'POST',
				'timeout' => 45,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'email'    => $email,
						'password' => $password,
					)
				),
			)
		);

		// Verificar si hay error en la petición
		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Error al conectar con el servidor: ', 'hoko-360' ) . $response->get_error_message(),
				)
			);
		}

		// Obtener el código de respuesta
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$data          = json_decode( $response_body, true );

		// Verificar respuesta exitosa
		if ( $response_code === 200 ) {
			// Guardar token - la API devuelve el token dentro de data.token
			if ( isset( $data['token'] ) ) {
				// Guardar el token
				update_option( 'hoko_360_auth_token', sanitize_text_field( $data['token'] ) );
				
				// Guardar el país conectado
				update_option( 'hoko_360_auth_country', $country );
				
				// Guardar también el email del usuario autenticado
				update_option( 'hoko_360_auth_email', $email );
				
				// Guardar timestamp de autentificación
				update_option( 'hoko_360_auth_time', current_time( 'timestamp' ) );
			}

			wp_send_json_success(
				array(
					'message' => __( 'Autentificación exitosa.', 'hoko-360' ),
					'data'    => $data,
				)
			);
		} else {
			// Error en la autentificación
			$error_message = isset( $data['message'] ) ? $data['message'] : __( 'Error en la autentificación.', 'hoko-360' );
			wp_send_json_error( array( 'message' => $error_message ) );
		}
	}

	/**
	 * Maneja la petición AJAX para crear orden en Hoko.
	 */
	public function handle_create_order_request() {
		// Verificar nonce
		check_ajax_referer( 'hoko_auth_nonce', 'nonce' );

		// Verificar permisos
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'hoko-360' ) ) );
		}

		// Verificar autenticación
		$token = $this->get_auth_token();
		if ( empty( $token ) ) {
			wp_send_json_error( array( 'message' => __( 'No estás autenticado.', 'hoko-360' ) ) );
		}

		// Obtener ID de orden
		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		if ( ! $order_id ) {
			wp_send_json_error( array( 'message' => __( 'ID de orden no válido.', 'hoko-360' ) ) );
		}

		// Obtener orden de WooCommerce
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wp_send_json_error( array( 'message' => __( 'Orden no encontrada.', 'hoko-360' ) ) );
		}

		// Preparar datos para Hoko desde el formulario
		$hoko_data = $this->prepare_order_data_from_form();

		// Obtener país y endpoint
		$country = get_option( 'hoko_360_auth_country', 'colombia' );
		$api_url = $this->get_api_base_url( $country ) . '/member/order/createV2';

		// Realizar petición a Hoko
		$response = wp_remote_post(
			$api_url,
			array(
				'method'  => 'POST',
				'timeout' => 45,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $token,
				),
				'body'    => wp_json_encode( $hoko_data ),
			)
		);

		// Verificar errores de conexión
		if ( is_wp_error( $response ) ) {
			$this->save_order_sync_status( $order_id, 2, $response->get_error_message(), null, $country );
			wp_send_json_error(
				array(
					'message' => __( 'Error al conectar con Hoko: ', 'hoko-360' ) . $response->get_error_message(),
				)
			);
		}

		// Procesar respuesta
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$data          = json_decode( $response_body, true );

		if ( $response_code === 200 || $response_code === 201 ) {
			// Éxito
			$hoko_order_id = isset( $data['id'] ) ? $data['id'] : ( isset( $data['order_id'] ) ? $data['order_id'] : null );
			$this->save_order_sync_status( $order_id, 1, __( 'Orden creada exitosamente.', 'hoko-360' ), $hoko_order_id, $country );
			
			wp_send_json_success(
				array(
					'message' => __( 'Orden creada exitosamente en Hoko.', 'hoko-360' ),
					'data'    => $data,
				)
			);
		} else {
			// Error
			$error_message = isset( $data['message'] ) ? $data['message'] : __( 'Error al crear la orden.', 'hoko-360' );
			$this->save_order_sync_status( $order_id, 2, $error_message, null, $country );
			
			wp_send_json_error( array( 'message' => $error_message ) );
		}
	}

	/**
	 * Prepara los datos de la orden desde el formulario de confirmación.
	 *
	 * @return array Datos formateados para Hoko.
	 */
	private function prepare_order_data_from_form() {
		// Datos del cliente
		$customer = array(
			'name'           => isset( $_POST['customer']['name'] ) ? sanitize_text_field( $_POST['customer']['name'] ) : '',
			'email'          => isset( $_POST['customer']['email'] ) ? sanitize_email( $_POST['customer']['email'] ) : '',
			'identification' => isset( $_POST['customer']['identification'] ) ? sanitize_text_field( $_POST['customer']['identification'] ) : '',
			'phone'          => isset( $_POST['customer']['phone'] ) ? sanitize_text_field( $_POST['customer']['phone'] ) : '',
			'address'        => isset( $_POST['customer']['address'] ) ? sanitize_text_field( $_POST['customer']['address'] ) : '',
			'city_id'        => isset( $_POST['customer']['city_id'] ) ? sanitize_text_field( $_POST['customer']['city_id'] ) : '1',
		);

		// Productos (stocks)
		$stocks = array();
		if ( isset( $_POST['stocks'] ) && is_array( $_POST['stocks'] ) ) {
			foreach ( $_POST['stocks'] as $stock_data ) {
				$sku = isset( $stock_data['sku'] ) ? sanitize_text_field( $stock_data['sku'] ) : '';
				if ( $sku ) {
					$stocks[ $sku ] = array(
						'amount' => isset( $stock_data['amount'] ) ? absint( $stock_data['amount'] ) : 1,
						'price'  => isset( $stock_data['price'] ) ? floatval( $stock_data['price'] ) : 0,
					);
				}
			}
		}

		// Método de pago
		$payment = isset( $_POST['payment'] ) ? absint( $_POST['payment'] ) : 0;

		// Courier ID
		$courier_id = isset( $_POST['courier_id'] ) ? absint( $_POST['courier_id'] ) : 44;

		// Contenido
		$contain = isset( $_POST['contain'] ) ? sanitize_text_field( $_POST['contain'] ) : '';

		// Medidas
		$measures = array(
			'height' => isset( $_POST['measures']['height'] ) ? sanitize_text_field( $_POST['measures']['height'] ) : '10',
			'width'  => isset( $_POST['measures']['width'] ) ? sanitize_text_field( $_POST['measures']['width'] ) : '10',
			'length' => isset( $_POST['measures']['length'] ) ? sanitize_text_field( $_POST['measures']['length'] ) : '10',
			'weight' => isset( $_POST['measures']['weight'] ) ? sanitize_text_field( $_POST['measures']['weight'] ) : '1',
		);

		// ID externo
		$external_id = isset( $_POST['external_id'] ) ? sanitize_text_field( $_POST['external_id'] ) : '';

		return array(
			'customer'    => $customer,
			'stocks'      => $stocks,
			'payment'     => $payment,
			'courier_id'  => $courier_id,
			'contain'     => $contain,
			'measures'    => $measures,
			'external_id' => $external_id,
		);
	}

	/**
	 * Prepara los datos de la orden de WooCommerce para enviar a Hoko.
	 *
	 * @param WC_Order $order Orden de WooCommerce.
	 * @return array Datos formateados para Hoko.
	 */
	private function prepare_order_data( $order ) {
		// Datos del cliente
		$customer = array(
			'name'           => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			'email'          => $order->get_billing_email(),
			'identification' => $order->get_meta( '_billing_document', true ) ?: '0000000000',
			'phone'          => $order->get_billing_phone(),
			'address'        => $order->get_billing_address_1() . ( $order->get_billing_address_2() ? ' ' . $order->get_billing_address_2() : '' ),
			'city_id'        => '1', // Por defecto, puede configurarse
		);

		// Productos (stocks)
		$stocks = array();
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( $product ) {
				$product_id = $product->get_id();
				$sku        = $product->get_sku() ?: $product_id;
				
				$stocks[ $sku ] = array(
					'amount' => $item->get_quantity(),
					'price'  => floatval( $item->get_total() / $item->get_quantity() ),
				);
			}
		}

		// Método de pago (0 por defecto, puede configurarse)
		$payment = 0;

		// Courier ID (44 por defecto, puede configurarse)
		$courier_id = 44;

		// Contenido (descripción de productos)
		$contain = '';
		$items_names = array();
		foreach ( $order->get_items() as $item ) {
			$items_names[] = $item->get_name();
		}
		$contain = implode( ', ', $items_names );
		if ( strlen( $contain ) > 100 ) {
			$contain = substr( $contain, 0, 97 ) . '...';
		}

		// Medidas (valores por defecto, pueden configurarse)
		$measures = array(
			'height' => '10',
			'width'  => '10',
			'length' => '10',
			'weight' => '1',
		);

		// ID externo (número de orden de WooCommerce)
		$external_id = $order->get_order_number();

		return array(
			'customer'    => $customer,
			'stocks'      => $stocks,
			'payment'     => $payment,
			'courier_id'  => $courier_id,
			'contain'     => $contain,
			'measures'    => $measures,
			'external_id' => $external_id,
		);
	}

	/**
	 * Guarda el estado de sincronización de una orden.
	 *
	 * @param int    $order_id       ID de la orden de WooCommerce.
	 * @param int    $sync_status    Estado de sincronización (0=pending, 1=synced, 2=failed).
	 * @param string $sync_message   Mensaje de sincronización.
	 * @param string $hoko_order_id  ID de la orden en Hoko.
	 * @param string $country        País de sincronización.
	 */
	private function save_order_sync_status( $order_id, $sync_status, $sync_message = '', $hoko_order_id = null, $country = '' ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'hoko_orders';
		
		$data = array(
			'order_id'     => $order_id,
			'sync_status'  => $sync_status,
			'sync_message' => $sync_message,
			'country'      => $country,
		);
		
		if ( $hoko_order_id ) {
			$data['hoko_order_id'] = $hoko_order_id;
		}
		
		if ( $sync_status === 1 ) {
			$data['synced_at'] = current_time( 'mysql' );
		}
		
		// Verificar si ya existe
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM $table_name WHERE order_id = %d",
				$order_id
			)
		);
		
		if ( $exists ) {
			// Actualizar
			$wpdb->update(
				$table_name,
				$data,
				array( 'order_id' => $order_id ),
				array( '%d', '%d', '%s', '%s' ),
				array( '%d' )
			);
		} else {
			// Insertar
			$wpdb->insert(
				$table_name,
				$data,
				array( '%d', '%d', '%s', '%s' )
			);
		}
	}

	/**
	 * Maneja la petición AJAX para cerrar sesión.
	 */
	public function handle_logout_request() {
		// Verificar nonce
		check_ajax_referer( 'hoko_auth_nonce', 'nonce' );

		// Verificar permisos
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'hoko-360' ) ) );
		}

		// Eliminar token y datos de autentificación
		delete_option( 'hoko_360_auth_token' );
		delete_option( 'hoko_360_auth_country' );
		delete_option( 'hoko_360_auth_email' );
		delete_option( 'hoko_360_auth_time' );

		wp_send_json_success(
			array(
				'message' => __( 'Sesión cerrada exitosamente.', 'hoko-360' ),
			)
		);
	}
}
