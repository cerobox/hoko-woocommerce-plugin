<?php
/**
 * Plugin Name:       Hoko 360
 * Plugin URI:        https://github.com/cerobox/hoko-woocommerce-plugin
 * Description:       Plugin de integración con WooCommerce para Hoko 360. / WooCommerce integration plugin for Hoko 360.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Cerobox
 * Author URI:        https://github.com/cerobox
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hoko-360
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * @package Hoko360
 */

// Si este archivo es llamado directamente, abortar.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Código que se ejecuta durante la activación del plugin.
 */
function hoko_360_activate() {
	// Verificar si WooCommerce está activo
	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_die( 
			esc_html__( 'Este plugin requiere WooCommerce. Por favor instala y activa WooCommerce primero.', 'hoko-360' ) 
		);
	}
	
	// Crear tabla para tracking de órdenes
	hoko_360_create_orders_table();
	hoko_360_create_states_table();
	hoko_360_create_cities_table();
}
register_activation_hook( __FILE__, 'hoko_360_activate' );

/**
 * Crea la tabla para tracking de órdenes sincronizadas.
 */
function hoko_360_create_orders_table() {
	global $wpdb;
	
	$table_name      = $wpdb->prefix . 'hoko_orders';
	$charset_collate = $wpdb->get_charset_collate();
	
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		order_id bigint(20) NOT NULL,
		hoko_order_id varchar(100) DEFAULT NULL,
		sync_status tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=pending, 1=synced, 2=failed',
		sync_message text DEFAULT NULL,
		country varchar(20) DEFAULT NULL,
		synced_at datetime DEFAULT NULL,
		created_at datetime DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY order_id (order_id),
		KEY sync_status (sync_status)
	) $charset_collate;";
	
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

/**
 * Crea la tabla para estados.
 */
function hoko_360_create_states_table() {
	global $wpdb;
	
	$table_name      = $wpdb->prefix . 'hoko_country_states';
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
	
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

/**
 * Crea la tabla para ciudades.
 */
function hoko_360_create_cities_table() {
	global $wpdb;
	
	$table_name      = $wpdb->prefix . 'hoko_country_cities';
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
	
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

/**
 * Código que se ejecuta durante la desactivación del plugin.
 */
function hoko_360_deactivate() {
	// Código de desactivación aquí
}
register_deactivation_hook( __FILE__, 'hoko_360_deactivate' );

/**
 * Cargar la clase principal del admin.
 */
require_once plugin_dir_path( __FILE__ ) . 'admin/class-hoko-admin.php';

/**
 * Inicializar el plugin.
 */
function hoko_360_run() {
	$plugin_name = 'hoko-360';
	$version     = '1.0.0';
	
	$plugin_admin = new Hoko_Admin( $plugin_name, $version );
	
	// Hooks para el área de administración
	add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ) );
	add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ) );
	add_action( 'admin_menu', array( $plugin_admin, 'add_admin_menu' ) );
	
	// Hooks para manejar peticiones AJAX
	add_action( 'wp_ajax_hoko_authenticate', array( $plugin_admin, 'handle_auth_request' ) );
	add_action( 'wp_ajax_hoko_logout', array( $plugin_admin, 'handle_logout_request' ) );
	add_action( 'wp_ajax_hoko_refresh_token', array( $plugin_admin, 'handle_refresh_token_request' ) );
	add_action( 'wp_ajax_hoko_create_order', array( $plugin_admin, 'handle_create_order_request' ) );
	add_action( 'wp_ajax_hoko_sync_cities', array( $plugin_admin, 'handle_sync_cities_request' ) );
	add_action( 'wp_ajax_hoko_get_cities_by_state', array( $plugin_admin, 'handle_get_cities_by_state_request' ) );
	add_action( 'wp_ajax_hoko_get_shipping_quotation', array( $plugin_admin, 'handle_shipping_quotation_request' ) );
}
add_action( 'plugins_loaded', 'hoko_360_run' );
