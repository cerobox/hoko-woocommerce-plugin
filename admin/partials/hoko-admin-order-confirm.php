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

$states = get_hoko_states();
$cities = get_hoko_cities();
$states_cities_map = array();

if ( $states && $cities ) {
	foreach ( $cities as $city ) {
		$state_id = $city['state_id'];
		if ( ! isset( $states_cities_map[$state_id] ) ) {
			$states_cities_map[$state_id] = array();
		}
		$states_cities_map[$state_id][] = $city;
	}
}

$current_city_id = $order->get_meta( '_hoko_city_id', true );
$current_state_id = '';
if ( $current_city_id && $cities ) {
	foreach ( $cities as $city ) {
		if ( $city['city_id'] == $current_city_id ) {
			$current_state_id = $city['state_id'];
			break;
		}
	}
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
				<h2><?php esc_html_e( 'Detalles de la Orden', 'hoko-360' ); ?> #<?php echo esc_html( $order->get_order_number() ); ?></h2>
				
				<form id="hoko-confirm-form" method="post">
					<!-- Información de la orden -->
					<div class="hoko-confirm-section">
						<table class="form-table">
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
						<h3>
                            <?php esc_html_e( 'Información del Cliente', 'hoko-360' ); ?>
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
								<th scope="row"><label for="customer_email"><?php esc_html_e( 'Email', 'hoko-360' ); ?></label></th>
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
								<th scope="row"><label for="customer_identification"><?php esc_html_e( 'Identificación', 'hoko-360' ); ?></label></th>
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
                            <tr>
                                <th scope="row"><label for="customer_address"><?php esc_html_e( 'Dirección', 'hoko-360' ); ?> <span class="required">*</span></label></th>
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
						</table>
					</div>

					<!-- Productos -->
					<div class="hoko-confirm-section">
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

					<!-- Medidas del paquete -->
					<div class="hoko-confirm-section">
						<h3><?php esc_html_e( 'Medidas del paquete', 'hoko-360' ); ?></h3>
						<ul class="measures">
                            <li>
                                <input type="number" id="measures_height" name="measures[height]" value="10" min="1" required>
                                <label for="measures_height"><?php esc_html_e( 'Alto (CM)', 'hoko-360' ); ?></label>
                            </li>
                            <li>
                                <input type="number" id="measures_width" name="measures[width]" value="10" min="1" required>
                                <label for="measures_width"><?php esc_html_e( 'Ancho (CM)', 'hoko-360' ); ?></label>
                            </li>
                            <li>
                                <input type="number" id="measures_length" name="measures[length]" value="10" min="1" required>
                                <label for="measures_length"><?php esc_html_e( 'Largo (CM)', 'hoko-360' ); ?></label>
                            </li>
                            <li>
                                <input type="number" id="measures_weight" name="measures[weight]" value="1" step="0.1" min="0.1" required>
                                <label for="measures_weight"><?php esc_html_e( 'Peso (KG)', 'hoko-360' ); ?></label>
                            </li>
						</ul>
					</div>

                    <!-- Configuración de envío -->
                    <div class="hoko-confirm-section">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="declared_value"><?php esc_html_e( 'Valor Declarado', 'hoko-360' ); ?> <span class="required">*</span></label></th>
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
                                    <p class="description"><?php esc_html_e( 'Valor declarado del paquete para el seguro de envío.', 'hoko-360' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="payment"><?php esc_html_e( 'Método de Pago', 'hoko-360' ); ?></label></th>
                                <td>
                                    <select id="payment" name="payment" class="regular-text">
                                        <option value="0"><?php esc_html_e( 'Pago contra entrega', 'hoko-360' ); ?></option>
                                        <option value="1"><?php esc_html_e( 'Pago crédito', 'hoko-360' ); ?></option>
                                    </select>
                                    <p class="description">
                                        Recuerda que si usas el pago crédito, debes tener suficiente saldo disponible en la wallet de tu tienda
                                    </p>
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

                    <div class="quotation">
                        <button type="button" class="button" id="hoko-quote-shipping">
                            <?php esc_html_e( 'Cotizar envío', 'hoko-360' ); ?>
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
							<?php esc_html_e( 'Confirmar y Crear Orden en Hoko', 'hoko-360' ); ?>
						</button>
						<span class="spinner"></span>
					</p>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>

<?php
function get_hoko_states() {
	global $wpdb;
	
	$states_table = $wpdb->prefix . 'hoko_country_states';
	$states = $wpdb->get_results( "SELECT state_id, state_name FROM $states_table ORDER BY state_name ASC", ARRAY_A );
	
	return $states;
}

function get_hoko_cities() {
	global $wpdb;
	
	$cities_table = $wpdb->prefix . 'hoko_country_cities';
	$cities = $wpdb->get_results( "SELECT city_id, city_name, state_id FROM $cities_table ORDER BY city_name ASC", ARRAY_A );
	
	return $cities;
}
?>
