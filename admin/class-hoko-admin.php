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
	public function enqueue_styles() {
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
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/hoko-admin.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		// Pasar datos al JavaScript
		wp_localize_script(
			$this->plugin_name,
			'hokoAdmin',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'hoko_auth_nonce' ),
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
		
		require_once plugin_dir_path( __FILE__ ) . 'partials/hoko-admin-orders.php';
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
