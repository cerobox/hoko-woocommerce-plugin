<?php
/**
 * Proporciona la vista de confirmación para crear una orden en Hoko.
 *
 * @package    Hoko360
 * @subpackage Hoko360/admin/partials
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div class="wrap">
	<h1><?php esc_html_e( 'Confirmar Orden de Compra', 'hoko-woocommerce' ); ?></h1>
	
	<?php if ( ! $is_authenticated ) : ?>
		<!-- Mensaje de no autenticado -->
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'No has iniciado sesión', 'hoko-woocommerce' ); ?></strong><br>
				<?php
				printf(
					/* translators: %s: enlace a la página de iniciar sesión */
					esc_html__( 'Debes %s para acceder a esta funcionalidad.', 'hoko-woocommerce' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=hoko-360' ) ) . '">' . esc_html__( 'iniciar sesión', 'hoko-woocommerce' ) . '</a>'
				);
				?>
			</p>
		</div>
	<?php elseif ( ! $order ) : ?>
		<!-- Orden no encontrada -->
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Orden no encontrada', 'hoko-woocommerce' ); ?></strong><br>
				<?php esc_html_e( 'La orden que intentas confirmar no existe.', 'hoko-woocommerce' ); ?>
			</p>
		</div>
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=hoko-360-orders' ) ); ?>" class="button">
				<?php esc_html_e( 'Volver a Órdenes', 'hoko-woocommerce' ); ?>
			</a>
		</p>
	<?php elseif ( $sync_status === 1 && ! empty( $hoko_order_id ) ) : ?>
		<!-- Orden ya sincronizada -->
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'Orden ya sincronizada', 'hoko-woocommerce' ); ?></strong><br>
				<?php 
				printf(
					/* translators: %s: ID de la orden en Hoko */
					esc_html__( 'Esta orden ya fue creada en Hoko con el ID: %s', 'hoko-woocommerce' ),
					'<strong>' . esc_html( $hoko_order_id ) . '</strong>'
				);
				?>
			</p>
			<?php if ( ! empty( $sync_message ) ) : ?>
				<p><?php echo esc_html( $sync_message ); ?></p>
			<?php endif; ?>
		</div>
		
		<!-- Detalles de la orden (solo lectura) -->
		<div class="hoko-confirm-container">
			<div class="hoko-confirm-card">
				<h2><?php esc_html_e( 'Detalles de la Orden', 'hoko-woocommerce' ); ?> #<?php echo esc_html( $order->get_order_number() ); ?></h2>
				
				<!-- Información de la orden -->
				<div class="hoko-confirm-section">
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Fecha', 'hoko-woocommerce' ); ?></th>
							<td><?php echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Estado', 'hoko-woocommerce' ); ?></th>
							<td><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Total', 'hoko-woocommerce' ); ?></th>
							<td><strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'ID Orden Hoko', 'hoko-woocommerce' ); ?></th>
							<td><strong><?php echo esc_html( $hoko_order_id ); ?></strong></td>
						</tr>
					</table>
				</div>

				<!-- Información del cliente -->
				<div class="hoko-confirm-section">
					<h3><?php esc_html_e( 'Información del Cliente', 'hoko-woocommerce' ); ?></h3>
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Nombre', 'hoko-woocommerce' ); ?></th>
							<td><?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Teléfono', 'hoko-woocommerce' ); ?></th>
							<td><?php echo esc_html( $order->get_billing_phone() ); ?></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Dirección', 'hoko-woocommerce' ); ?></th>
							<td><?php echo esc_html( $order->get_billing_address_1() . ( $order->get_billing_address_2() ? ' ' . $order->get_billing_address_2() : '' ) ); ?></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Email', 'hoko-woocommerce' ); ?></th>
							<td><?php echo esc_html( $order->get_billing_email() ); ?></td>
						</tr>
					</table>
				</div>

				<!-- Productos -->
				<div class="hoko-confirm-section">
					<h3><?php esc_html_e( 'Productos', 'hoko-woocommerce' ); ?></h3>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Producto', 'hoko-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'SKU', 'hoko-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Cantidad', 'hoko-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Precio Unitario', 'hoko-woocommerce' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
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
									<td><?php echo esc_html( $item->get_name() ); ?></td>
									<td><?php echo esc_html( $sku ); ?></td>
									<td><?php echo esc_html( $quantity ); ?></td>
									<td><?php echo esc_html( number_format( $unit_price, 2, '.', '' ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=hoko-360-orders' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Volver a Órdenes', 'hoko-woocommerce' ); ?>
					</a>
				</p>
			</div>
		</div>
	<?php else : ?>
		<!-- Formulario de confirmación -->
		<div class="hoko-confirm-container">
			<div class="hoko-confirm-card">
				<h2><?php esc_html_e( 'Detalles de la Orden', 'hoko-woocommerce' ); ?> #<?php echo esc_html( $order->get_order_number() ); ?></h2>
				
				<form id="hoko-confirm-form" method="post">
					<!-- Información de la orden -->
					<div class="hoko-confirm-section">
						<table class="form-table">
							<tr>
								<th scope="row"><?php esc_html_e( 'Fecha', 'hoko-woocommerce' ); ?></th>
								<td><?php echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Estado', 'hoko-woocommerce' ); ?></th>
								<td><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Total', 'hoko-woocommerce' ); ?></th>
								<td><strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong></td>
							</tr>
						</table>
					</div>

					<!-- Información del cliente -->
					<div class="hoko-confirm-section">
						<h3>
                            <?php esc_html_e( 'Información del Cliente', 'hoko-woocommerce' ); ?>
                            - <?php
                            $billing_city = $order->get_billing_city();
                            $billing_state = $order->get_billing_state();

                            if ( $billing_city && $billing_state ) {
                                echo esc_html( $billing_city ) . ', ' . esc_html( $billing_state );
                            } elseif ( $billing_city ) {
                                echo esc_html( $billing_city );
                            } elseif ( $billing_state ) {
                                echo esc_html( $billing_state );
                            } else {
                                echo 'No especificados';
                            }
                            ?>
                        </h3>
                        <input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
                        <input type="hidden" id="billing_city" value="<?php echo esc_attr( $billing_city ); ?>">
                        <input type="hidden" id="billing_state" value="<?php echo esc_attr( $billing_state ); ?>">
						<table class="form-table">
							<tr>
								<th scope="row"><label for="customer_name"><?php esc_html_e( 'Nombre', 'hoko-woocommerce' ); ?> <span class="required">*</span></label></th>
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
                                <th scope="row"><label for="customer_phone"><?php esc_html_e( 'Teléfono', 'hoko-woocommerce' ); ?> <span class="required">*</span></label></th>
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
                                <th scope="row"><label for="customer_address"><?php esc_html_e( 'Dirección', 'hoko-woocommerce' ); ?> <span class="required">*</span></label></th>
                                <td>
                                    <input
                                            type="text"
                                            id="customer_address"
                                            class="regular-text"
                                            name="customer[address]"
                                            value="<?php echo esc_attr( $order->get_billing_address_1() . ( $order->get_billing_address_2() ? ' ' . $order->get_billing_address_2() : '' ) ); ?>"
                                            required
                                    >
                                </td>
                            </tr>
							<tr>
								<th scope="row"><label for="customer_email"><?php esc_html_e( 'Email', 'hoko-woocommerce' ); ?></label></th>
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
								<th scope="row"><label for="customer_identification"><?php esc_html_e( 'Identificación', 'hoko-woocommerce' ); ?></label></th>
								<td>
									<input 
										type="text" 
										id="customer_identification" 
										name="customer[identification]" 
										class="regular-text" 
										value="<?php echo esc_attr( $order->get_meta( '_billing_document', true ) ?: '0000000000' ); ?>"
										required
									>
								</td>
							</tr>
						</table>
					</div>

					<!-- Productos -->
					<div class="hoko-confirm-section">
						<table class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Producto', 'hoko-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'SKU', 'hoko-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Cantidad', 'hoko-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Precio Unitario', 'hoko-woocommerce' ); ?></th>
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
											<?php echo esc_html( $quantity ); ?>
											<input type="hidden" name="stocks[<?php echo esc_attr( $item_index ); ?>][amount]" value="<?php echo esc_attr( $quantity ); ?>">
										</td>
										<td>
											<?php echo esc_html( number_format( $unit_price, 2, '.', '' ) ); ?>
											<input type="hidden" name="stocks[<?php echo esc_attr( $item_index ); ?>][price]" value="<?php echo esc_attr( number_format( $unit_price, 2, '.', '' ) ); ?>">
										</td>
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
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="declared_value"><?php esc_html_e( 'Valor Declarado', 'hoko-woocommerce' ); ?> <span class="required">*</span></label></th>
                                <td>
                                    <input 
                                        type="number" 
                                        id="declared_value" 
                                        name="declared_value" 
                                        class="regular-text" 
                                        value="10000" 
                                        min="1" 
                                        step="1"
                                        required
                                    >
                                    <p class="description"><?php esc_html_e( 'Valor declarado del paquete para el seguro de envío.', 'hoko-woocommerce' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="payment"><?php esc_html_e( 'Método de Pago', 'hoko-woocommerce' ); ?></label></th>
                                <td>
                                    <select id="payment" name="payment" class="regular-text">
                                        <option value="0"><?php esc_html_e( 'Pago contra entrega', 'hoko-woocommerce' ); ?></option>
                                        <option value="1"><?php esc_html_e( 'Pago crédito', 'hoko-woocommerce' ); ?></option>
                                    </select>
                                    <p class="description">
                                        Recuerda que si usas el pago crédito, debes tener suficiente saldo disponible en la wallet de tu tienda
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="contain"><?php esc_html_e( 'Contenido', 'hoko-woocommerce' ); ?></label></th>
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
                                    <p class="description"><?php esc_html_e( 'Descripción breve del contenido del paquete (máx. 100 caracteres).', 'hoko-woocommerce' ); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="quotation">
                        <button type="button" class="button" id="hoko-quote-shipping">
                            <?php esc_html_e( 'Cotizar envío', 'hoko-woocommerce' ); ?>
                        </button>
                        <div id="hoko-quotation-results" style="display: none;">
                            <!-- Aquí se mostrarán las opciones de transporte -->
                        </div>
                        <input type="hidden" id="selected_courier_id" name="selected_courier_id" value="">
                        <input type="hidden" id="selected_courier_value" name="selected_courier_value" value="">
                    </div>

					<!-- Mensaje de respuesta -->
					<div id="hoko-confirm-message" style="display: none;"></div>

					<!-- Botones de acción -->
					<p class="submit">
						<button type="submit" class="button button-primary button-large" id="hoko-confirm-submit" disabled>
							<?php esc_html_e( 'Confirmar y Crear Orden en Hoko', 'hoko-woocommerce' ); ?>
						</button>
						<span class="spinner"></span>
					</p>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>
