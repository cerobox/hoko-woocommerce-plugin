<?php
/**
 * Disparado durante la desinstalación del plugin.
 *
 * @package Hoko360
 */

// Si uninstall no es llamado desde WordPress, entonces salir.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Aquí puedes agregar código para limpiar opciones, tablas, etc.
// Por ejemplo:
// delete_option( 'hoko_woocommerce_option_name' );
