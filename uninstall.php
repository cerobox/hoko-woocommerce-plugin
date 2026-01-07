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

// Eliminar tablas de ciudades y estados (ya no se utilizan)
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}hoko_country_cities" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}hoko_country_states" );
