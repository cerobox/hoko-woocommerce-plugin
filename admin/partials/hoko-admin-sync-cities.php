<?php
/**
 * Plantilla para la página de sincronización de ciudades.
 *
 * @package    Hoko360
 * @subpackage Hoko360/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Obtener última sincronización
$last_sync = get_last_sync_info();

?>

<div class="wrap hoko-admin">
	<h1><?php _e( 'Sincronizar ciudades', 'hoko-360' ); ?></h1>
	
	<?php if ( ! $is_authenticated ) : ?>
		<div class="notice notice-warning">
			<p><?php _e( 'Debes iniciar sesión para sincronizar las ciudades.', 'hoko-360' ); ?></p>
		</div>
		<p>
			<a href="<?php echo admin_url( 'admin.php?page=hoko-360-auth' ); ?>" class="button button-primary">
				<?php _e( 'Ir a Iniciar sesión', 'hoko-360' ); ?>
			</a>
		</p>
	<?php else : ?>
		<div class="card">
			<h2><?php _e( 'Sincronización de Ciudades', 'hoko-360' ); ?></h2>
			<p><?php _e( 'Sincroniza los estados y ciudades desde Hoko para usarlos en tus órdenes de compra.', 'hoko-360' ); ?></p>
			
			<?php if ( $last_sync ) : ?>
				<div class="hoko-last-sync">
					<h3><?php _e( 'Última sincronización', 'hoko-360' ); ?></h3>
					<div class="sync-summary">
						<div class="sync-stat">
							<strong><?php _e( 'Fecha:', 'hoko-360' ); ?></strong> 
							<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_sync['date'] ) ) ); ?>
						</div>
						<div class="sync-stat">
							<strong><?php _e( 'Estados:', 'hoko-360' ); ?></strong> 
							<?php echo esc_html( $last_sync['states_count'] ); ?> <?php _e( 'sincronizados', 'hoko-360' ); ?>
						</div>
						<div class="sync-stat">
							<strong><?php _e( 'Ciudades:', 'hoko-360' ); ?></strong> 
							<?php echo esc_html( $last_sync['cities_count'] ); ?> <?php _e( 'sincronizadas', 'hoko-360' ); ?>
						</div>
						<?php if ( $last_sync['errors'] > 0 ) : ?>
							<div class="sync-stat" style="background: #fcf0f1;">
								<strong><?php _e( 'Errores:', 'hoko-360' ); ?></strong> 
								<?php echo esc_html( $last_sync['errors'] ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
			
			<div class="hoko-sync-section">
				<div class="hoko-sync-info">
					<p><strong><?php _e( '¿Qué se sincronizará?', 'hoko-360' ); ?></strong></p>
					<ul>
						<li><?php _e( 'Estados/Departamentos disponibles', 'hoko-360' ); ?></li>
						<li><?php _e( 'Ciudades por cada estado/departamento', 'hoko-360' ); ?></li>
					</ul>
				</div>
				
				<div class="hoko-sync-actions">
					<button type="button" id="hoko-sync-cities-btn" class="button button-primary">
						<span class="dashicons dashicons-update-alt"></span>
						<?php _e( 'Sincronizar Estados y Ciudades', 'hoko-360' ); ?>
					</button>
					<span class="spinner"></span>
				</div>
				
				<div id="hoko-sync-message"></div>
				
				<div id="hoko-sync-results" style="display: none;">
					<h3><?php _e( 'Resultados de la sincronización', 'hoko-360' ); ?></h3>
					<div id="hoko-sync-stats"></div>
					<div id="hoko-sync-details"></div>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>

<?php
/**
 * Obtiene información de la última sincronización
 */
function get_last_sync_info() {
	global $wpdb;
	
	$states_table = $wpdb->prefix . 'hoko_country_states';
	$cities_table = $wpdb->prefix . 'hoko_country_cities';
	
	// Obtener conteo de estados y ciudades
	$states_count = $wpdb->get_var( "SELECT COUNT(*) FROM $states_table" );
	$cities_count = $wpdb->get_var( "SELECT COUNT(*) FROM $cities_table" );
	
	// Obtener fecha de última sincronización (más reciente de ambas tablas)
	$last_state_date = $wpdb->get_var( "SELECT MAX(created_at) FROM $states_table" );
	$last_city_date = $wpdb->get_var( "SELECT MAX(created_at) FROM $cities_table" );
	
	$last_sync_date = max( $last_state_date, $last_city_date );
	
	if ( ! $last_sync_date || $states_count == 0 ) {
		return null;
	}
	
	return array(
		'date' => $last_sync_date,
		'states_count' => intval( $states_count ),
		'cities_count' => intval( $cities_count ),
		'errors' => 0 // Podríamos calcular esto si guardamos logs de errores
	);
}
?>
