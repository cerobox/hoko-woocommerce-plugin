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
			<p><?php _e( 'Esta sección estará disponible próximamente.', 'hoko-360' ); ?></p>
			
			<!-- Contenido futuro para sincronización de ciudades -->
			<div class="hoko-placeholder">
				<p><?php _e( 'Aquí podrás sincronizar las ciudades disponibles en Hoko 360 con tu tienda WooCommerce.', 'hoko-360' ); ?></p>
			</div>
		</div>
	<?php endif; ?>
</div>
