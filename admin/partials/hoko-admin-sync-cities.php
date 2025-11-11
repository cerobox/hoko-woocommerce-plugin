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
			<p><?php _e( 'Sincroniza los estados y ciudades desde Hoko 360 para usarlos en tus órdenes de compra.', 'hoko-360' ); ?></p>
			
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
