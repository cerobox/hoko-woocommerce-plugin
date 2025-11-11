<?php
/**
 * Proporciona la vista del área de administración para órdenes de compra.
 *
 * @package    Hoko360
 * @subpackage Hoko360/admin/partials
 */

// Si este archivo es llamado directamente, abortar.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<?php if ( ! $is_authenticated ) : ?>
		<!-- Mensaje de no autenticado -->
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'No has iniciado sesión', 'hoko-360' ); ?></strong><br>
				<?php
				printf(
					/* translators: %s: enlace a la página de iniciar sesión */
					esc_html__( 'Debes %s para acceder a esta funcionalidad.', 'hoko-360' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=hoko-360' ) ) . '">' . esc_html__( 'iniciar sesión', 'hoko-360' ) . '</a>'
				);
				?>
			</p>
		</div>
	<?php else : ?>
		<!-- Contenido de órdenes de compra -->
		<div class="hoko-orders-container">
			<div class="hoko-orders-card">
				<h2><?php esc_html_e( 'Gestión de Órdenes de Compra', 'hoko-360' ); ?></h2>
				<p><?php esc_html_e( 'Aquí podrás gestionar y sincronizar las órdenes de WooCommerce con Hoko 360.', 'hoko-360' ); ?></p>
				
				<div class="notice notice-info inline">
					<p><?php esc_html_e( 'Esta funcionalidad estará disponible próximamente.', 'hoko-360' ); ?></p>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
