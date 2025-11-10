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
		add_menu_page(
			__( 'Hoko 360', 'hoko-360' ),           // Título de la página
			__( 'Hoko 360', 'hoko-360' ),           // Título del menú
			'manage_options',                        // Capacidad requerida
			'hoko-360',                              // Slug del menú
			array( $this, 'display_auth_page' ),    // Función callback
			'dashicons-admin-generic',               // Icono
			56                                       // Posición
		);
	}

	/**
	 * Muestra la página de autentificación.
	 */
	public function display_auth_page() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/hoko-admin-auth.php';
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

		// Validar datos
		if ( empty( $email ) || empty( $password ) ) {
			wp_send_json_error( array( 'message' => __( 'Por favor completa todos los campos.', 'hoko-360' ) ) );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Por favor ingresa un email válido.', 'hoko-360' ) ) );
		}

		// Realizar petición a la API de Hoko
		$response = wp_remote_post(
			'https://v4.hoko.com.co/api/login',
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
			// Guardar token u otros datos si es necesario
			if ( isset( $data['token'] ) ) {
				update_option( 'hoko_360_auth_token', $data['token'] );
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
}
