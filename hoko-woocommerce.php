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
}
register_activation_hook( __FILE__, 'hoko_360_activate' );

/**
 * Código que se ejecuta durante la desactivación del plugin.
 */
function hoko_360_deactivate() {
	// Código de desactivación aquí
}
register_deactivation_hook( __FILE__, 'hoko_360_deactivate' );
