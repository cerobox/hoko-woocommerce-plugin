<?php
/**
 * La funcionalidad específica del área de administración del plugin.
 *
 * @package    Hoko360
 * @subpackage Hoko360/admin
 */

class Hoko_Admin {

	/**
	 * Configuración de endpoints de API por país.
	 *
	 * @var array
	 */
	private $api_endpoints = array(
		'colombia' => array(
			'login' => 'https://hoko.com.co/api/login',
			'base'  => 'https://hoko.com.co/api'
		),
		'ecuador' => array(
			'login' => 'https://hoko.com.ec/api/login',
			'base'  => 'https://hoko.com.ec/api'
		),
		'usa' => array(
			'login' => 'https://hoko360.com/api/login',
			'base'  => 'https://hoko360.com/api'
		)
	);

	/**
	 * Cache para token de autentificación.
	 *
	 * @var string|null
	 */
	private $cached_token = null;

	/**
	 * Cache para datos de autentificación.
	 *
	 * @var array|null
	 */
	private $cached_auth_data = null;

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
				'authUrl'   => admin_url( 'admin.php?page=hoko-360-auth' ),
			)
		);
	}

	/**
	 * Registra el menú de administración.
	 */
	public function add_admin_menu() {
		// Obtener el icono personalizado
		$icon_url = $this->get_menu_icon();

		// Menú principal (apunta a Órdenes de compra)
		add_menu_page(
			__( 'Hoko', 'hoko-360' ),           // Título de la página
			__( 'Hoko', 'hoko-360' ),           // Título del menú
			'manage_options',                        // Capacidad requerida
			'hoko-360-orders',                       // Slug del menú (apunta a órdenes)
			array( $this, 'display_orders_page' ),  // Función callback
			$icon_url,                               // Icono personalizado
			56                                       // Posición
		);

		// Submenú: Órdenes de compra (primera opción)
		add_submenu_page(
			'hoko-360-orders',                        // Slug del menú padre
			__( 'Órdenes de compra', 'hoko-360' ),  // Título de la página
			__( 'Órdenes de compra', 'hoko-360' ),  // Título del submenú
			'manage_options',                        // Capacidad requerida
			'hoko-360-orders',                       // Slug (mismo que el padre para que sea la primera opción)
			array( $this, 'display_orders_page' )   // Función callback
		);

		
		// Submenú: Iniciar sesión
		add_submenu_page(
			'hoko-360-orders',                        // Slug del menú padre
			__( 'Iniciar sesión', 'hoko-360' ),     // Título de la página
			__( 'Iniciar sesión', 'hoko-360' ),     // Título del submenú
			'manage_options',                        // Capacidad requerida
			'hoko-360-auth',                         // Slug del submenú
			array( $this, 'display_auth_page' )     // Función callback
		);

		// Submenú: Sincronizar ciudades
		add_submenu_page(
			'hoko-360-orders',                        // Slug del menú padre
			__( 'Sincronizar ciudades', 'hoko-360' ), // Título de la página
			__( 'Sincronizar ciudades', 'hoko-360' ), // Título del submenú
			'manage_options',                        // Capacidad requerida
			'hoko-360-sync-cities',                  // Slug del submenú
			array( $this, 'display_sync_cities_page' ) // Función callback
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
	 * Obtiene el icono del menú como URL.
	 *
	 * @return string URL del icono o dashicon.
	 */
	private function get_menu_icon() {
		$icon_path = plugin_dir_path( __FILE__ ) . 'images/hoko-icon.png';
		
		if ( file_exists( $icon_path ) ) {
			// Usar URL del archivo en lugar de base64
			return plugin_dir_url( __FILE__ ) . 'images/hoko-icon.png';
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
	 * Obtiene las órdenes de WooCommerce con su estado de sincronización (optimizado).
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
				'return'  => 'objects',
			)
		);
		
		if ( empty( $orders ) ) {
			return array();
		}

		// Obtener IDs de órdenes para consulta batch
		$order_ids = wp_list_pluck( $orders, 'id' );
		$placeholders = implode( ',', array_fill( 0, count( $order_ids ), '%d' ) );
		
		$table_name = $wpdb->prefix . 'hoko_orders';
		$sync_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT order_id, sync_status, sync_message, hoko_order_id, synced_at FROM $table_name WHERE order_id IN ($placeholders)",
				$order_ids
			)
		);
		
		// Crear mapa de datos de sincronización
		$sync_map = array();
		foreach ( $sync_data as $data ) {
			$sync_map[ $data->order_id ] = $data;
		}
		
		// Combinar datos
		$orders_data = array();
		foreach ( $orders as $order ) {
			$order_id = $order->get_id();
			$sync = isset( $sync_map[ $order_id ] ) ? $sync_map[ $order_id ] : null;
			
			$orders_data[] = array(
				'order'         => $order,
				'sync_status'   => $sync ? (int) $sync->sync_status : 0,
				'sync_message'  => $sync ? $sync->sync_message : '',
				'hoko_order_id' => $sync ? $sync->hoko_order_id : '',
				'synced_at'     => $sync ? $sync->synced_at : '',
			);
		}
		
		return $orders_data;
	}

	/**
	 * Obtiene el token de autentificación guardado (con cache).
	 *
	 * @return string Token de autentificación o cadena vacía si no existe.
	 */
	public function get_auth_token() {
		if ( $this->cached_token === null ) {
			$this->cached_token = get_option( 'hoko_360_auth_token', '' );
		}
		return $this->cached_token;
	}

	/**
	 * Verifica si el usuario está autenticado.
	 *
	 * @return bool True si está autenticado, false en caso contrario.
	 */
	public function is_authenticated() {
		return ! empty( $this->get_auth_token() );
	}

	/**
	 * Obtiene datos de autentificación cacheados.
	 *
	 * @return array Datos de autentificación.
	 */
	private function get_auth_data() {
		if ( $this->cached_auth_data === null ) {
			$this->cached_auth_data = array(
				'token'   => $this->get_auth_token(),
				'country' => get_option( 'hoko_360_auth_country', 'colombia' ),
				'email'   => get_option( 'hoko_360_auth_email', '' ),
				'time'    => get_option( 'hoko_360_auth_time', 0 )
			);
		}
		return $this->cached_auth_data;
	}

	/**
	 * Obtiene el endpoint de API según el país.
	 *
	 * @param string $country Código del país.
	 * @return string URL del endpoint.
	 */
	private function get_api_endpoint( $country ) {
		return isset( $this->api_endpoints[ $country ]['login'] ) 
			? $this->api_endpoints[ $country ]['login'] 
			: $this->api_endpoints['colombia']['login'];
	}

	/**
	 * Obtiene la URL base de la API según el país.
	 *
	 * @param string $country Código del país.
	 * @return string URL base de la API.
	 */
	public function get_api_base_url( $country = '' ) {
		if ( empty( $country ) ) {
			$auth_data = $this->get_auth_data();
			$country = $auth_data['country'];
		}

		return isset( $this->api_endpoints[ $country ]['base'] ) 
			? $this->api_endpoints[ $country ]['base'] 
			: $this->api_endpoints['colombia']['base'];
	}

	/**
	 * Verifica nonce y permisos para peticiones AJAX.
	 */
	private function verify_ajax_request() {
		check_ajax_referer( 'hoko_auth_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'hoko-360' ) ) );
		}
	}

	/**
	 * Maneja la petición AJAX de autentificación.
	 */
	public function handle_auth_request() {
		$this->verify_ajax_request();

		// Obtener y validar datos del formulario
		$email    = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$password = isset( $_POST['password'] ) ? $_POST['password'] : '';
		$country  = isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : 'colombia';

		$this->validate_auth_data( $email, $password, $country );

		// Realizar petición a la API de Hoko
		$response = $this->make_api_request( $this->get_api_endpoint( $country ), array(
			'email'    => $email,
			'password' => $password,
		) );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Error al conectar con el servidor: ', 'hoko-360' ) . $response->get_error_message(),
				)
			);
		}

		$this->process_auth_response( $response, $country, $email );
	}

	/**
	 * Valida datos de autentificación.
	 */
	private function validate_auth_data( $email, $password, $country ) {
		if ( empty( $email ) || empty( $password ) || empty( $country ) ) {
			wp_send_json_error( array( 'message' => __( 'Por favor completa todos los campos.', 'hoko-360' ) ) );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Por favor ingresa un email válido.', 'hoko-360' ) ) );
		}

		if ( ! in_array( $country, array_keys( $this->api_endpoints ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'País no válido.', 'hoko-360' ) ) );
		}
	}

	/**
	 * Realiza petición a la API de Hoko.
	 */
	private function make_api_request( $endpoint, $data, $token = '', $method = 'POST' ) {
		$headers = array( 'Content-Type' => 'application/json' );
		if ( $token ) {
			$headers['Authorization'] = 'Bearer ' . $token;
		}

		// Preparar argumentos de la petición
		$args = array(
			'method'  => $method,
			'timeout' => 45,
			'headers' => $headers,
		);

		// Agregar body solo para métodos POST/PUT/PATCH
		if ( in_array( $method, array( 'POST', 'PUT', 'PATCH' ), true ) && ! empty( $data ) ) {
			// Construir JSON manualmente para manejar strings JSON anidados
			$json_parts = array();
			foreach ( $data as $key => $value ) {
				$json_key = json_encode( $key );
				
				// Si el valor ya es un string JSON válido (customer, stocks o measures), codificarlo como string
				if ( is_string( $value ) && in_array( $key, array( 'customer', 'stocks', 'measures' ), true ) ) {
					$test_decode = json_decode( $value );
					if ( json_last_error() === JSON_ERROR_NONE ) {
						$json_parts[] = $json_key . ':' . json_encode( $value );
					} else {
						$json_parts[] = $json_key . ':' . json_encode( $value );
					}
				} else {
					$json_parts[] = $json_key . ':' . json_encode( $value );
				}
			}
			
			$args['body'] = '{' . implode( ',', $json_parts ) . '}';
		}

		// Realizar petición
		$response = wp_remote_request( $endpoint, $args );
		
		return $response;
	}

	/**
	 * Procesa respuesta de autentificación.
	 */
	private function process_auth_response( $response, $country, $email ) {
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$data          = json_decode( $response_body, true );

		if ( $response_code === 200 && isset( $data['token'] ) ) {
			// Actualizar cache
			$this->cached_token = sanitize_text_field( $data['token'] );
			$this->cached_auth_data = array(
				'token'   => $this->cached_token,
				'country' => $country,
				'email'   => $email,
				'time'    => current_time( 'timestamp' )
			);

			// Guardar en base de datos
			update_option( 'hoko_360_auth_token', $this->cached_token );
			update_option( 'hoko_360_auth_country', $country );
			update_option( 'hoko_360_auth_email', $email );
			update_option( 'hoko_360_auth_time', $this->cached_auth_data['time'] );

			wp_send_json_success(
				array(
					'message' => __( 'Autentificación exitosa.', 'hoko-360' ),
					'data'    => $data,
				)
			);
		} else {
			$error_message = isset( $data['message'] ) ? $data['message'] : __( 'Error en la autentificación.', 'hoko-360' );
			wp_send_json_error( array( 'message' => $error_message ) );
		}
	}

	/**
	 * Maneja la petición AJAX para crear orden en Hoko.
	 */
	public function handle_create_order_request() {
		$this->verify_ajax_request();

		// Verificar autenticación
		if ( ! $this->is_authenticated() ) {
			wp_send_json_error( array( 'message' => __( 'No estás autenticado.', 'hoko-360' ) ) );
		}

		// Validar y obtener orden
		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		if ( ! $order_id ) {
			wp_send_json_error( array( 'message' => __( 'ID de orden no válido.', 'hoko-360' ) ) );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wp_send_json_error( array( 'message' => __( 'Orden no encontrada.', 'hoko-360' ) ) );
		}

		// Preparar datos y realizar petición
		$hoko_data = $this->prepare_order_data_from_form();
		$auth_data = $this->get_auth_data();
		$api_url = $this->get_api_base_url( $auth_data['country'] ) . '/member/order/create';

		$response = $this->make_api_request( $api_url, $hoko_data, $auth_data['token'] );

		if ( is_wp_error( $response ) ) {
			$this->save_order_sync_status( $order_id, 2, $response->get_error_message(), null, $auth_data['country'] );
			wp_send_json_error(
				array(
					'message' => __( 'Error al conectar con Hoko: ', 'hoko-360' ) . $response->get_error_message(),
				)
			);
		}

		$this->process_order_response( $response, $order_id, $auth_data['country'] );
	}

	/**
	 * Procesa respuesta de creación de orden.
	 */
	private function process_order_response( $response, $order_id, $country ) {
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$data          = json_decode( $response_body, true );

		if ( $response_code === 200 || $response_code === 201 ) {
			$hoko_order_id = isset( $data['id'] ) ? $data['id'] : ( isset( $data['order_id'] ) ? $data['order_id'] : null );
			$this->save_order_sync_status( $order_id, 1, __( 'Orden creada exitosamente.', 'hoko-360' ), $hoko_order_id, $country );
			
			wp_send_json_success(
				array(
					'message' => __( 'Orden creada exitosamente en Hoko.', 'hoko-360' ),
					'data'    => $data,
				)
			);
		} else {
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
		// Obtener la orden para capturar billing_city y billing_state
		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$order = wc_get_order( $order_id );
		
		// Obtener customer como string JSON (NO decodificar, la API de Hoko espera un string)
		$customer_json = $_POST['customer'] ?? '{}';
		$customer_json = stripslashes( $customer_json );
		
		// Validar que sea un JSON válido
		$customer_test = json_decode( $customer_json, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$customer_json = '{}';
		}
		
		// Agregar billing_city y billing_state desde la orden
		if ( $order ) {
			$customer_data = json_decode( $customer_json, true );
			$customer_data['city'] = $order->get_billing_city();
			$customer_data['state'] = $order->get_billing_state();
			$customer_json = json_encode( $customer_data );
		}
		
		// Obtener stocks como string JSON
		$stocks_json = $_POST['stocks'] ?? '{}';
		if ( is_array( $_POST['stocks'] ?? null ) ) {
			$stocks = $this->sanitize_stocks_data( $_POST['stocks'] );
			$stocks_json = json_encode( $stocks );
		} else {
			$stocks_json = stripslashes( $stocks_json );
			$stocks_test = json_decode( $stocks_json, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				$stocks_json = '{}';
			}
		}
		
		// Obtener measures como string JSON
		$measures_json = $_POST['measures'] ?? '{}';
		if ( is_array( $_POST['measures'] ?? null ) ) {
			$measures_data = array(
				'height' => isset( $_POST['measures']['height'] ) ? sanitize_text_field( $_POST['measures']['height'] ) : '10',
				'width'  => isset( $_POST['measures']['width'] ) ? sanitize_text_field( $_POST['measures']['width'] ) : '10',
				'length' => isset( $_POST['measures']['length'] ) ? sanitize_text_field( $_POST['measures']['length'] ) : '10',
				'weight' => isset( $_POST['measures']['weight'] ) ? sanitize_text_field( $_POST['measures']['weight'] ) : '1',
			);
			$measures_json = json_encode( $measures_data );
		} else {
			$measures_json = stripslashes( $measures_json );
			$measures_test = json_decode( $measures_json, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				$measures_json = '{"height":"10","width":"10","length":"10","weight":"1"}';
			}
		}

		return array(
			'customer'    => $customer_json,
			'stocks'      => $stocks_json,
			'payment'     => isset( $_POST['payment'] ) ? absint( $_POST['payment'] ) : 0,
			'courier_id'  => isset( $_POST['selected_courier_id'] ) ? absint( $_POST['selected_courier_id'] ) : 44,
			'contain'     => isset( $_POST['contain'] ) ? sanitize_text_field( $_POST['contain'] ) : '',
			'measures'    => $measures_json,
			'external_id' => isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '',
		);
	}

	/**
	 * Sanitiza datos del cliente.
	 */
	private function sanitize_customer_data( $customer_data ) {
		if ( is_string( $customer_data ) ) {
			$decoded_data = json_decode( $customer_data, true );
			if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded_data ) ) {
				$customer_data = $decoded_data;
			} else {
				$customer_data = array();
			}
		}
		
		if ( ! is_array( $customer_data ) ) {
			$customer_data = array();
		}
		
		return array(
			'name'           => isset( $customer_data['name'] ) ? sanitize_text_field( $customer_data['name'] ) : '',
			'email'          => isset( $customer_data['email'] ) ? sanitize_email( $customer_data['email'] ) : '',
			'identification' => isset( $customer_data['identification'] ) ? sanitize_text_field( $customer_data['identification'] ) : '',
			'phone'          => isset( $customer_data['phone'] ) ? sanitize_text_field( $customer_data['phone'] ) : '',
			'address'        => isset( $customer_data['address'] ) ? sanitize_text_field( $customer_data['address'] ) : '',
			'city_id'        => isset( $customer_data['city_id'] ) ? sanitize_text_field( $customer_data['city_id'] ) : '1',
		);
	}

	/**
	 * Sanitiza datos de stocks.
	 */
	private function sanitize_stocks_data( $stocks_data ) {
		$stocks = array();
		if ( is_array( $stocks_data ) ) {
			foreach ( $stocks_data as $stock_data ) {
				$sku = isset( $stock_data['sku'] ) ? sanitize_text_field( $stock_data['sku'] ) : '';
				if ( $sku ) {
					$stocks[ $sku ] = array(
						'amount' => isset( $stock_data['amount'] ) ? absint( $stock_data['amount'] ) : 1,
						'price'  => isset( $stock_data['price'] ) ? floatval( $stock_data['price'] ) : 0,
					);
				}
			}
		}
		return $stocks;
	}

	/**
	 * Sanitiza datos de medidas.
	 */
	private function sanitize_measures_data( $measures_data ) {
		return array(
			'height' => isset( $measures_data['height'] ) ? sanitize_text_field( $measures_data['height'] ) : '10',
			'width'  => isset( $measures_data['width'] ) ? sanitize_text_field( $measures_data['width'] ) : '10',
			'length' => isset( $measures_data['length'] ) ? sanitize_text_field( $measures_data['length'] ) : '10',
			'weight' => isset( $measures_data['weight'] ) ? sanitize_text_field( $measures_data['weight'] ) : '1',
		);
	}

	/**
	 * Prepara los datos de la orden de WooCommerce para enviar a Hoko (optimizado).
	 *
	 * @param WC_Order $order Orden de WooCommerce.
	 * @return array Datos formateados para Hoko.
	 */
	private function prepare_order_data( $order ) {
		$customer = array(
			'name'           => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			'email'          => $order->get_billing_email(),
			'identification' => $order->get_meta( '_billing_document', true ) ?: '0000000000',
			'phone'          => $order->get_billing_phone(),
			'address'        => $this->format_billing_address( $order ),
			'city_id'        => '1',
		);

		$stocks = $this->get_order_stocks( $order );
		$contain = $this->get_order_contain( $order );

		return array(
			'customer'    => $customer,
			'stocks'      => $stocks,
			'payment'     => 0,
			'courier_id'  => 44,
			'contain'     => $contain,
			'measures'    => array( 'height' => '10', 'width' => '10', 'length' => '10', 'weight' => '1' ),
			'external_id' => $order->get_order_number(),
		);
	}

	/**
	 * Formatea la dirección de facturación.
	 */
	private function format_billing_address( $order ) {
		$address = $order->get_billing_address_1();
		$address2 = $order->get_billing_address_2();
		return $address . ( $address2 ? ' ' . $address2 : '' );
	}

	/**
	 * Obtiene stocks de la orden.
	 */
	private function get_order_stocks( $order ) {
		$stocks = array();
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( $product ) {
				$sku = $product->get_sku() ?: $product->get_id();
				$stocks[ $sku ] = array(
					'amount' => $item->get_quantity(),
					'price'  => floatval( $item->get_total() / $item->get_quantity() ),
				);
			}
		}
		return $stocks;
	}

	/**
	 * Obtiene contenido/descripción de la orden.
	 */
	private function get_order_contain( $order ) {
		$items_names = wp_list_pluck( $order->get_items(), 'name' );
		$contain = implode( ', ', $items_names );
		return strlen( $contain ) > 100 ? substr( $contain, 0, 97 ) . '...' : $contain;
	}

	/**
	 * Guarda el estado de sincronización de una orden (optimizado con UPSERT).
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
		
		// Usar REPLACE INTO para simplificar lógica (más eficiente)
		$wpdb->replace(
			$table_name,
			$data,
			array( '%d', '%d', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Muestra la página de sincronización de ciudades.
	 */
	public function display_sync_cities_page() {
		// Verificar si está autenticado
		$is_authenticated = $this->is_authenticated();
		
		require_once plugin_dir_path( __FILE__ ) . 'partials/hoko-admin-sync-cities.php';
	}

	/**
	 * Maneja la petición AJAX para sincronizar estados y ciudades.
	 */
	public function handle_sync_cities_request() {
		$this->verify_ajax_request();

		// Verificar autenticación
		if ( ! $this->is_authenticated() ) {
			wp_send_json_error( array( 'message' => __( 'No estás autenticado.', 'hoko-360' ) ) );
		}

		$auth_data = $this->get_auth_data();
		$results = array(
			'states' => array( 'synced' => 0, 'errors' => 0 ),
			'cities' => array( 'synced' => 0, 'errors' => 0 ),
			'details' => array()
		);

		try {
			// Sincronizar estados
			$results['states'] = $this->sync_states( $auth_data['token'], $auth_data['country'] );
			
			// Sincronizar ciudades
			$results['cities'] = $this->sync_cities( $auth_data['token'], $auth_data['country'] );

			wp_send_json_success( array(
				'message' => __( 'Sincronización completada exitosamente.', 'hoko-360' ),
				'results' => $results
			) );

		} catch ( Exception $e ) {
			wp_send_json_error( array(
				'message' => __( 'Error en la sincronización: ', 'hoko-360' ) . $e->getMessage()
			) );
		}
	}

	/**
	 * Sincroniza estados desde Hoko API.
	 */
	private function sync_states( $token, $country ) {
		$api_url = $this->get_api_base_url( $country ) . '/member/get-states';
		$response = $this->make_api_request( $api_url, array(), $token, 'GET' );

		if ( is_wp_error( $response ) ) {
			$error_msg = 'Error al obtener estados: ' . $response->get_error_message();
			throw new Exception( __( $error_msg, 'hoko-360' ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$data = json_decode( $response_body, true );

		if ( $response_code !== 200 ) {
			$error_message = isset( $data['message'] ) ? $data['message'] : __( 'Error al obtener estados', 'hoko-360' );
			throw new Exception( $error_message );
		}

		return $this->save_states( $data );
	}

	/**
	 * Sincroniza ciudades desde Hoko API.
	 */
	private function sync_cities( $token, $country ) {
		$api_url = $this->get_api_base_url( $country ) . '/member/get-cities';
		$response = $this->make_api_request( $api_url, array(), $token, 'GET' );

		if ( is_wp_error( $response ) ) {
			throw new Exception( __( 'Error al obtener ciudades: ', 'hoko-360' ) . $response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$data = json_decode( $response_body, true );

		if ( $response_code !== 200 ) {
			$error_message = isset( $data['message'] ) ? $data['message'] : __( 'Error al obtener ciudades', 'hoko-360' );
			throw new Exception( $error_message );
		}

		return $this->save_cities( $data );
	}

	/**
	 * Guarda estados en la base de datos.
	 */
	private function save_states( $states_data ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'hoko_country_states';
		$synced = 0;
		$errors = 0;

		// Crear tabla si no existe
		$this->ensure_states_table();

		// Limpiar estados existentes
		$wpdb->query( "TRUNCATE TABLE $table_name" );

		if ( is_array( $states_data ) ) {
			foreach ( $states_data as $state ) {
				// La API usa 'cod' en lugar de 'id' y 'name' para el nombre
				if ( isset( $state['cod'] ) && isset( $state['name'] ) ) {
					$result = $wpdb->insert(
						$table_name,
						array(
							'state_id' => sanitize_text_field( $state['cod'] ),
							'state_name' => sanitize_text_field( $state['name'] ),
							'state_code' => sanitize_text_field( $state['cod'] ), // Usar 'cod' como código también
							'created_at' => current_time( 'mysql' )
						),
						array( '%d', '%s', '%s', '%s' )
					);
					
					if ( $result !== false ) {
						$synced++;
					} else {
						$errors++;
					}
				} else {
					$errors++;
				}
			}
		}

		return array( 'synced' => $synced, 'errors' => $errors );
	}

	/**
	 * Guarda ciudades en la base de datos.
	 */
	private function save_cities( $cities_data ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'hoko_country_cities';
		$synced = 0;
		$errors = 0;

		// Crear tabla si no existe
		$this->ensure_cities_table();

		// Limpiar ciudades existentes
		$wpdb->query( "TRUNCATE TABLE $table_name" );

		if ( is_array( $cities_data ) ) {
			foreach ( $cities_data as $city ) {
				// La API usa 'id', 'name' y 'department_id' (no 'state_id')
				if ( isset( $city['id'] ) && isset( $city['name'] ) && isset( $city['department_id'] ) ) {
					$result = $wpdb->insert(
						$table_name,
						array(
							'city_id' => sanitize_text_field( $city['id'] ),
							'city_name' => sanitize_text_field( $city['name'] ),
							'state_id' => sanitize_text_field( $city['department_id'] ), // Mapear department_id a state_id
							'created_at' => current_time( 'mysql' )
						),
						array( '%d', '%s', '%d', '%s' )
					);
					
					if ( $result !== false ) {
						$synced++;
					} else {
						$errors++;
					}
				} else {
					$errors++;
				}
			}
		}

		return array( 'synced' => $synced, 'errors' => $errors );
	}

	/**
	 * Crea tabla de estados si no existe.
	 */
	private function ensure_states_table() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'hoko_country_states';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			state_id bigint(20) NOT NULL,
			state_name varchar(100) NOT NULL,
			state_code varchar(10) DEFAULT '',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY state_id (state_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Crea tabla de ciudades si no existe.
	 */
	private function ensure_cities_table() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'hoko_country_cities';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			city_id bigint(20) NOT NULL,
			city_name varchar(100) NOT NULL,
			state_id bigint(20) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY city_id (city_id),
			KEY state_id (state_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Maneja la petición AJAX para obtener ciudades por estado.
	 */
	public function handle_get_cities_by_state_request() {
		$this->verify_ajax_request();

		// Obtener ID del estado
		$state_id = isset( $_POST['state_id'] ) ? absint( $_POST['state_id'] ) : 0;
		
		if ( ! $state_id ) {
			wp_send_json_error( array( 'message' => __( 'ID de estado no válido.', 'hoko-360' ) ) );
		}

		// Obtener ciudades de la base de datos
		global $wpdb;
		$cities_table = $wpdb->prefix . 'hoko_country_cities';
		$cities = $wpdb->get_results( 
			$wpdb->prepare( 
				"SELECT city_id, city_name FROM $cities_table WHERE state_id = %d ORDER BY city_name ASC", 
				$state_id 
			), 
			ARRAY_A 
		);

		wp_send_json_success( array( 'cities' => $cities ) );
	}

	/**
	 * Maneja la petición AJAX para obtener cotización de envío.
	 */
	public function handle_shipping_quotation_request() {
		$this->verify_ajax_request();

		// Obtener parámetros de la cotización
		$stock_ids = isset( $_POST['stock_ids'] ) ? sanitize_text_field( $_POST['stock_ids'] ) : '';
		$city = isset( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : '';
		$state = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
		$payment = isset( $_POST['payment'] ) ? absint( $_POST['payment'] ) : 0;
		$declared_value = isset( $_POST['declared_value'] ) ? absint( $_POST['declared_value'] ) : 10000;
		$width = isset( $_POST['width'] ) ? absint( $_POST['width'] ) : 10;
		$height = isset( $_POST['height'] ) ? absint( $_POST['height'] ) : 10;
		$length = isset( $_POST['length'] ) ? absint( $_POST['length'] ) : 10;
		$weight = isset( $_POST['weight'] ) ? floatval( $_POST['weight'] ) : 1;
		$collection_value = isset( $_POST['collection_value'] ) ? absint( $_POST['collection_value'] ) : 150000;

		// Validar parámetros requeridos
		if ( ! $stock_ids || ! $city || ! $state ) {
			wp_send_json_error( array( 'message' => __( 'Faltan parámetros requeridos para la cotización.', 'hoko-360' ) ) );
		}

		// Obtener token de autentificación
		$token = $this->get_auth_token();
		if ( ! $token ) {
			wp_send_json_error( array( 'message' => __( 'No estás autenticado. Por favor inicia sesión nuevamente.', 'hoko-360' ) ) );
		}

		// Obtener país configurado
		$country = get_option( 'hoko_360_auth_country', 'colombia' );
		if ( ! isset( $this->api_endpoints[ $country ] ) ) {
			wp_send_json_error( array( 'message' => __( 'País no configurado correctamente.', 'hoko-360' ) ) );
		}

		// Construir URL de la API
		$api_url = $this->api_endpoints[ $country ]['base'] . '/member/stock/quotation';

		// Preparar datos para la petición
		$post_data = array(
			'stock_ids' => $stock_ids,
			'city' => $city,
			'state' => $state,
			'payment' => $payment,
			'declared_value' => $declared_value,
			'width' => $width,
			'height' => $height,
			'length' => $length,
			'weight' => $weight,
			'collection_value' => $collection_value
		);

		// Realizar petición a la API
		$response = wp_remote_post(
			$api_url,
			array(
				'method' => 'POST',
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type' => 'application/json',
				),
				'body' => json_encode( $post_data ),
				'timeout' => 30,
			)
		);

		// Verificar si hay error en la petición
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => __( 'Error al conectar con la API de Hoko: ', 'hoko-360' ) . $response->get_error_message() ) );
		}

		// Obtener código de estado y cuerpo de la respuesta
		$status_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Verificar código de estado
		if ( $status_code !== 200 ) {
			wp_send_json_error( array( 'message' => __( 'Error en la respuesta de la API. Código: ', 'hoko-360' ) . $status_code ) );
		}

		// Decodificar respuesta JSON
		$data = json_decode( $response_body, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( array( 'message' => __( 'Error al procesar la respuesta de la API.', 'hoko-360' ) ) );
		}

		// Verificar si la respuesta es exitosa
		if ( ! isset( $data['status'] ) || $data['status'] !== 'success' ) {
			$error_message = isset( $data['message'] ) ? $data['message'] : __( 'Error desconocido al obtener cotización.', 'hoko-360' );
			wp_send_json_error( array( 'message' => $error_message ) );
		}

		// Verificar si hay cotizaciones disponibles
		if ( ! isset( $data['quotations'] ) || empty( $data['quotations'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No se encontraron cotizaciones disponibles para esta ruta.', 'hoko-360' ) ) );
		}

		// Enviar respuesta exitosa con las cotizaciones
		wp_send_json_success( array(
			'message' => __( 'Cotización obtenida exitosamente.', 'hoko-360' ),
			'quotations' => $data['quotations']
		) );
	}

	/**
	 * Maneja la petición AJAX para cerrar sesión.
	 */
	public function handle_logout_request() {
		$this->verify_ajax_request();

		// Limpiar cache
		$this->cached_token = null;
		$this->cached_auth_data = null;

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
