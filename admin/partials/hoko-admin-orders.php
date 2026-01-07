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
				<strong><?php esc_html_e( 'No has iniciado sesión', 'hoko-woocommerce-plugin' ); ?></strong><br>
				<?php
				printf(
					/* translators: %s: enlace a la página de iniciar sesión */
					esc_html__( 'Debes %s para acceder a esta funcionalidad.', 'hoko-woocommerce-plugin' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=hoko-360' ) ) . '">' . esc_html__( 'iniciar sesión', 'hoko-woocommerce-plugin' ) . '</a>'
				);
				?>
			</p>
		</div>
	<?php else : ?>
		<!-- Contenido de órdenes de compra -->
		<div class="hoko-orders-container">
			<div class="hoko-orders-card">
				<h2><?php esc_html_e( 'Gestión de Órdenes de Compra', 'hoko-woocommerce-plugin' ); ?></h2>
				<p><?php esc_html_e( 'Sincroniza las órdenes de WooCommerce con Hoko.', 'hoko-woocommerce-plugin' ); ?></p>
				
				<?php if ( empty( $orders ) ) : ?>
					<div class="notice notice-info inline">
						<p><?php esc_html_e( 'No hay órdenes disponibles para sincronizar.', 'hoko-woocommerce-plugin' ); ?></p>
					</div>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Orden', 'hoko-woocommerce-plugin' ); ?></th>
								<th><?php esc_html_e( 'Cliente', 'hoko-woocommerce-plugin' ); ?></th>
								<th><?php esc_html_e( 'Total', 'hoko-woocommerce-plugin' ); ?></th>
								<th><?php esc_html_e( 'Estado', 'hoko-woocommerce-plugin' ); ?></th>
								<th><?php esc_html_e( 'Fecha', 'hoko-woocommerce-plugin' ); ?></th>
								<th><?php esc_html_e( 'Acciones', 'hoko-woocommerce-plugin' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $orders as $order_data ) : ?>
								<?php
								$order = $order_data['order'];
								$sync_status = $order_data['sync_status'];
								$sync_message = $order_data['sync_message'];
								$hoko_order_id = $order_data['hoko_order_id'];
								
								// Determinar clase de estado
								$status_class = 'pending';
								$status_text = __( 'Pendiente', 'hoko-woocommerce-plugin' );
								if ( $sync_status === 1 ) {
									$status_class = 'synced';
									$status_text = __( 'Sincronizado', 'hoko-woocommerce-plugin' );
								} elseif ( $sync_status === 2 ) {
									$status_class = 'failed';
									$status_text = __( 'Fallido', 'hoko-woocommerce-plugin' );
								}
								?>
								<tr data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
									<td>
										<strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong>
										<?php if ( $hoko_order_id ) : ?>
											<br><small><?php echo esc_html( sprintf( __( 'Hoko ID: %s', 'hoko-woocommerce-plugin' ), $hoko_order_id ) ); ?></small>
										<?php endif; ?>
									</td>
									<td>
										<?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?>
										<br><small><?php echo esc_html( $order->get_billing_email() ); ?></small>
									</td>
									<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
									<td>
										<span class="hoko-sync-status hoko-sync-<?php echo esc_attr( $status_class ); ?>">
											<?php echo esc_html( $status_text ); ?>
										</span>
										<?php if ( $sync_message ) : ?>
											<br><small class="hoko-sync-message"><?php echo esc_html( $sync_message ); ?></small>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) ); ?></td>
									<td>
										<?php if ( $sync_status !== 1 ) : ?>
											<a 
												href="<?php echo esc_url( admin_url( 'admin.php?page=hoko-360-order-confirm&order_id=' . $order->get_id() ) ); ?>" 
												class="button button-primary"
											>
												<?php esc_html_e( 'Crear Orden', 'hoko-woocommerce-plugin' ); ?>
											</a>
										<?php else : ?>
											<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
											<?php esc_html_e( 'Sincronizado', 'hoko-woocommerce-plugin' ); ?>
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
