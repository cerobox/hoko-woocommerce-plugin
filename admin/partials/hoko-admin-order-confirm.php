<?php
/**
 * Proporciona la vista de confirmación para crear una orden en Hoko.
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
	<h1><?php esc_html_e( 'Confirmar Orden de Compra', 'hoko-360' ); ?></h1>
	
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
	<?php elseif ( ! $order ) : ?>
		<!-- Orden no encontrada -->
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Orden no encontrada', 'hoko-360' ); ?></strong><br>
				<?php esc_html_e( 'La orden que intentas confirmar no existe.', 'hoko-360' ); ?>
			</p>
		</div>
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=hoko-360-orders' ) ); ?>" class="button">
				<?php esc_html_e( 'Volver a Órdenes', 'hoko-360' ); ?>
			</a>
		</p>
	<?php else : ?>
		<!-- Formulario de confirmación -->
		<div class="hoko-confirm-container">
			<div class="hoko-confirm-card">
				<h2><?php esc_html_e( 'Detalles de la Orden', 'hoko-360' ); ?></h2>
				
				<form id="hoko-confirm-form" method="post">
					<!-- Información de la orden -->
					<div class="hoko-confirm-section">
						<h3><?php esc_html_e( 'Información General', 'hoko-360' ); ?></h3>
						<table class="form-table">
							<tr>
								<th scope="row"><?php esc_html_e( 'Número de Orden', 'hoko-360' ); ?></th>
								<td>
									<strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong>
									<input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Fecha', 'hoko-360' ); ?></th>
								<td><?php echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Estado', 'hoko-360' ); ?></th>
								<td><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Total', 'hoko-360' ); ?></th>
								<td><strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong></td>
							</tr>
						</table>
					</div>

					<!-- Información del cliente -->
					<div class="hoko-confirm-section">
						<h3><?php esc_html_e( 'Información del Cliente', 'hoko-360' ); ?></h3>
						<table class="form-table">
							<tr>
								<th scope="row"><label for="customer_name"><?php esc_html_e( 'Nombre', 'hoko-360' ); ?> <span class="required">*</span></label></th>
								<td>
									<input 
										type="text" 
										id="customer_name" 
										name="customer[name]" 
										class="regular-text" 
										value="<?php echo esc_attr( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?>"
										required
									>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="customer_email"><?php esc_html_e( 'Email', 'hoko-360' ); ?> <span class="required">*</span></label></th>
								<td>
									<input 
										type="email" 
										id="customer_email" 
										name="customer[email]" 
										class="regular-text" 
										value="<?php echo esc_attr( $order->get_billing_email() ); ?>"
										required
									>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="customer_identification"><?php esc_html_e( 'Identificación', 'hoko-360' ); ?> <span class="required">*</span></label></th>
								<td>
									<input 
										type="text" 
										id="customer_identification" 
										name="customer[identification]" 
										class="regular-text" 
										value="<?php echo esc_attr( $order->get_meta( '_billing_document', true ) ?: '0000000000' ); ?>"
										required
									>
									<p class="description"><?php esc_html_e( 'Número de documento de identidad del cliente.', 'hoko-360' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="customer_phone"><?php esc_html_e( 'Teléfono', 'hoko-360' ); ?> <span class="required">*</span></label></th>
								<td>
									<input 
										type="text" 
										id="customer_phone" 
										name="customer[phone]" 
										class="regular-text" 
										value="<?php echo esc_attr( $order->get_billing_phone() ); ?>"
										required
									>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="customer_address"><?php esc_html_e( 'Dirección', 'hoko-360' ); ?> <span class="required">*</span></label></th>
								<td>
									<input 
										type="text" 
										id="customer_address" 
										name="customer[address]" 
										class="large-text" 
										value="<?php echo esc_attr( $order->get_billing_address_1() . ( $order->get_billing_address_2() ? ' ' . $order->get_billing_address_2() : '' ) ); ?>"
										required
									>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="customer_city_id"><?php esc_html_e( 'ID de Ciudad', 'hoko-360' ); ?> <span class="required">*</span></label></th>
								<td>
									<input 
										type="text" 
										id="customer_city_id" 
										name="customer[city_id]" 
										class="small-text" 
										value="1"
										required
									>
									<p class="description"><?php esc_html_e( 'ID de la ciudad en Hoko (por defecto: 1).', 'hoko-360' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<!-- Productos -->
					<div class="hoko-confirm-section">
						<h3><?php esc_html_e( 'Productos', 'hoko-360' ); ?></h3>
						<table class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Producto', 'hoko-360' ); ?></th>
									<th><?php esc_html_e( 'SKU', 'hoko-360' ); ?></th>
									<th><?php esc_html_e( 'Cantidad', 'hoko-360' ); ?></th>
									<th><?php esc_html_e( 'Precio Unitario', 'hoko-360' ); ?></th>
									<th><?php esc_html_e( 'Total', 'hoko-360' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$item_index = 0;
								foreach ( $order->get_items() as $item ) : 
									$product = $item->get_product();
									if ( ! $product ) {
										continue;
									}
									$product_id = $product->get_id();
									$sku = $product->get_sku() ?: $product_id;
									$quantity = $item->get_quantity();
									$unit_price = floatval( $item->get_total() / $quantity );
								?>
									<tr>
										<td>
											<?php echo esc_html( $item->get_name() ); ?>
											<input type="hidden" name="stocks[<?php echo esc_attr( $item_index ); ?>][sku]" value="<?php echo esc_attr( $sku ); ?>">
										</td>
										<td><?php echo esc_html( $sku ); ?></td>
										<td>
											<input 
												type="number" 
												name="stocks[<?php echo esc_attr( $item_index ); ?>][amount]" 
												class="small-text" 
												value="<?php echo esc_attr( $quantity ); ?>"
												min="1"
												required
											>
										</td>
										<td>
											<input 
												type="number" 
												name="stocks[<?php echo esc_attr( $item_index ); ?>][price]" 
												class="regular-text" 
												value="<?php echo esc_attr( number_format( $unit_price, 2, '.', '' ) ); ?>"
												step="0.01"
												min="0"
												required
											>
										</td>
										<td><?php echo wp_kses_post( wc_price( $item->get_total() ) ); ?></td>
									</tr>
								<?php 
									$item_index++;
								endforeach; 
								?>
							</tbody>
						</table>
					</div>

					<!-- Configuración de envío -->
					<div class="hoko-confirm-section">
						<h3><?php esc_html_e( 'Configuración de Envío', 'hoko-360' ); ?></h3>
						<table class="form-table">
							<tr>
								<th scope="row"><label for="payment"><?php esc_html_e( 'Método de Pago', 'hoko-360' ); ?></label></th>
								<td>
									<select id="payment" name="payment" class="regular-text">
										<option value="0"><?php esc_html_e( 'Efectivo', 'hoko-360' ); ?></option>
										<option value="1"><?php esc_html_e( 'Tarjeta', 'hoko-360' ); ?></option>
										<option value="2"><?php esc_html_e( 'Transferencia', 'hoko-360' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="courier_id"><?php esc_html_e( 'ID de Courier', 'hoko-360' ); ?> <span class="required">*</span></label></th>
								<td>
									<input 
										type="text" 
										id="courier_id" 
										name="courier_id" 
										class="small-text" 
										value="44"
										required
									>
									<p class="description"><?php esc_html_e( 'ID del servicio de mensajería en Hoko (por defecto: 44).', 'hoko-360' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="contain"><?php esc_html_e( 'Contenido', 'hoko-360' ); ?></label></th>
								<td>
									<?php
									$items_names = array();
									foreach ( $order->get_items() as $item ) {
										$items_names[] = $item->get_name();
									}
									$contain = implode( ', ', $items_names );
									if ( strlen( $contain ) > 100 ) {
										$contain = substr( $contain, 0, 97 ) . '...';
									}
									?>
									<input 
										type="text" 
										id="contain" 
										name="contain" 
										class="large-text" 
										value="<?php echo esc_attr( $contain ); ?>"
										maxlength="100"
									>
									<p class="description"><?php esc_html_e( 'Descripción breve del contenido del paquete (máx. 100 caracteres).', 'hoko-360' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<!-- Medidas del paquete -->
					<div class="hoko-confirm-section">
						<h3><?php esc_html_e( 'Medidas del Paquete', 'hoko-360' ); ?></h3>
						<table class="form-table">
							<tr>
								<th scope="row"><label for="measures_height"><?php esc_html_e( 'Alto (cm)', 'hoko-360' ); ?> <span class="required">*</span></label></th>
								<td>
									<input 
										type="number" 
										id="measures_height" 
										name="measures[height]" 
										class="small-text" 
										value="10"
										min="1"
										required
									>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="measures_width"><?php esc_html_e( 'Ancho (cm)', 'hoko-360' ); ?> <span class="required">*</span></label></th>
								<td>
									<input 
										type="number" 
										id="measures_width" 
										name="measures[width]" 
										class="small-text" 
										value="10"
										min="1"
										required
									>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="measures_length"><?php esc_html_e( 'Largo (cm)', 'hoko-360' ); ?> <span class="required">*</span></label></th>
								<td>
									<input 
										type="number" 
										id="measures_length" 
										name="measures[length]" 
										class="small-text" 
										value="10"
										min="1"
										required
									>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="measures_weight"><?php esc_html_e( 'Peso (kg)', 'hoko-360' ); ?> <span class="required">*</span></label></th>
								<td>
									<input 
										type="number" 
										id="measures_weight" 
										name="measures[weight]" 
										class="small-text" 
										value="1"
										step="0.1"
										min="0.1"
										required
									>
								</td>
							</tr>
						</table>
					</div>

					<!-- ID Externo -->
					<div class="hoko-confirm-section">
						<h3><?php esc_html_e( 'Identificación Externa', 'hoko-360' ); ?></h3>
						<table class="form-table">
							<tr>
								<th scope="row"><label for="external_id"><?php esc_html_e( 'ID Externo', 'hoko-360' ); ?></label></th>
								<td>
									<input 
										type="text" 
										id="external_id" 
										name="external_id" 
										class="regular-text" 
										value="<?php echo esc_attr( $order->get_order_number() ); ?>"
										readonly
									>
									<p class="description"><?php esc_html_e( 'Número de orden de WooCommerce para referencia.', 'hoko-360' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<!-- Mensaje de respuesta -->
					<div id="hoko-confirm-message" style="display: none;"></div>

					<!-- Botones de acción -->
					<p class="submit">
						<button type="submit" class="button button-primary button-large" id="hoko-confirm-submit">
							<?php esc_html_e( 'Confirmar y Crear Orden en Hoko', 'hoko-360' ); ?>
						</button>
						<span class="spinner"></span>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=hoko-360-orders' ) ); ?>" class="button button-large">
							<?php esc_html_e( 'Cancelar', 'hoko-360' ); ?>
						</a>
					</p>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>
