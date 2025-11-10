<?php
/**
 * Plugin Name:       Hoko WooCommerce
 * Plugin URI:        https://github.com/cerobox/hoko-woocommerce-plugin
 * Description:       Plugin de integración de WooCommerce con funcionalidades personalizadas. / WooCommerce integration plugin with custom functionalities.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Cerobox
 * Author URI:        https://github.com/cerobox
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hoko-woocommerce
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * @package HokoWooCommerce
 */

// Si este archivo es llamado directamente, abortar.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Código que se ejecuta durante la activación del plugin.
 */
function hoko_woocommerce_activate() {
	// Verificar si WooCommerce está activo
	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_die( 
			esc_html__( 'Este plugin requiere WooCommerce. Por favor instala y activa WooCommerce primero.', 'hoko-woocommerce' ) 
		);
	}
}
register_activation_hook( __FILE__, 'hoko_woocommerce_activate' );

/**
 * Código que se ejecuta durante la desactivación del plugin.
 */
function hoko_woocommerce_deactivate() {
	// Código de desactivación aquí
}
register_deactivation_hook( __FILE__, 'hoko_woocommerce_deactivate' );
