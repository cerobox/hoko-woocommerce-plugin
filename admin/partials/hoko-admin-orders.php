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
				<p><?php esc_html_e( 'Sincroniza las órdenes de WooCommerce con Hoko.', 'hoko-360' ); ?></p>
				
				<?php if ( empty( $orders ) ) : ?>
					<div class="notice notice-info inline">
						<p><?php esc_html_e( 'No hay órdenes disponibles para sincronizar.', 'hoko-360' ); ?></p>
					</div>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Orden', 'hoko-360' ); ?></th>
								<th><?php esc_html_e( 'Cliente', 'hoko-360' ); ?></th>
								<th><?php esc_html_e( 'Total', 'hoko-360' ); ?></th>
								<th><?php esc_html_e( 'Estado', 'hoko-360' ); ?></th>
								<th><?php esc_html_e( 'Fecha', 'hoko-360' ); ?></th>
								<th><?php esc_html_e( 'Acciones', 'hoko-360' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $orders as $hoko_order_data ) : ?>
								<?php
								$order = $hoko_order_data['order'];
								$hoko_sync_status = $hoko_order_data['sync_status'];
								$hoko_sync_message = $hoko_order_data['sync_message'];
								$hoko_order_id = $hoko_order_data['hoko_order_id'];
								
								// Determinar clase de estado
								$hoko_status_class = 'pending';
								$hoko_status_text = __( 'Pendiente', 'hoko-360' );
								if ( $hoko_sync_status === 1 ) {
									$hoko_status_class = 'synced';
									$hoko_status_text = __( 'Sincronizado', 'hoko-360' );
								} elseif ( $hoko_sync_status === 2 ) {
									$hoko_status_class = 'failed';
									$hoko_status_text = __( 'Fallido', 'hoko-360' );
								}
								?>
								<tr data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
									<td>
										<strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong>
										<?php if ( $hoko_order_id ) : ?>
											<?php /* translators: %s: Hoko order ID */ ?>
											<br><small><?php echo esc_html( sprintf( __( 'Hoko ID: %s', 'hoko-360' ), $hoko_order_id ) ); ?></small>
										<?php endif; ?>
									</td>
									<td>
										<?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?>
										<br><small><?php echo esc_html( $order->get_billing_email() ); ?></small>
									</td>
									<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
									<td>
										<span class="hoko-sync-status hoko-sync-<?php echo esc_attr( $hoko_status_class ); ?>">
											<?php echo esc_html( $hoko_status_text ); ?>
										</span>
										<?php if ( $hoko_sync_message ) : ?>
											<br><small class="hoko-sync-message"><?php echo esc_html( $hoko_sync_message ); ?></small>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) ); ?></td>
									<td>
										<?php if ( $hoko_sync_status !== 1 ) : ?>
											<a 
												href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=hoko-360-order-confirm&order_id=' . $order->get_id() ), 'hoko_order_confirm_' . $order->get_id() ) ); ?>" 
												class="button button-primary"
											>
												<?php esc_html_e( 'Crear Orden', 'hoko-360' ); ?>
											</a>
										<?php else : ?>
											<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
											<?php esc_html_e( 'Sincronizado', 'hoko-360' ); ?>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
