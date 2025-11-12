<?php
/**
 * Proporciona la vista del área de administración para la autentificación.
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
	
	<div class="hoko-auth-container">
		<div class="hoko-auth-card">
			<h2><?php esc_html_e( 'Autentificación', 'hoko-360' ); ?></h2>
			
			<?php if ( $is_authenticated ) : ?>
				<!-- Usuario autenticado -->
				<div class="notice notice-success inline">
					<p>
						<strong><?php esc_html_e( '✓ Sesión activa', 'hoko-360' ); ?></strong><br>
						<?php
						$auth_email   = get_option( 'hoko_360_auth_email', '' );
						$auth_country = get_option( 'hoko_360_auth_country', '' );
						$auth_time    = get_option( 'hoko_360_auth_time', '' );
						
						// Mapeo de países
						$countries = array(
							'colombia' => __( 'Colombia', 'hoko-360' ),
							'ecuador'  => __( 'Ecuador', 'hoko-360' ),
							'usa'      => __( 'Estados Unidos', 'hoko-360' ),
						);
						
						if ( $auth_country && isset( $countries[ $auth_country ] ) ) {
							/* translators: %s: país conectado */
							printf( esc_html__( 'País: %s', 'hoko-360' ), esc_html( $countries[ $auth_country ] ) );
							echo '<br>';
						}
						if ( $auth_email ) {
							/* translators: %s: email del usuario autenticado */
							printf( esc_html__( 'Usuario: %s', 'hoko-360' ), esc_html( $auth_email ) );
							echo '<br>';
						}
						if ( $auth_time ) {
							/* translators: %s: fecha de autenticación */
							printf( esc_html__( 'Autenticado el: %s', 'hoko-360' ), esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $auth_time ) ) );
						}
						?>
					</p>
				</div>
				
				<p><?php esc_html_e( 'Ya tienes una sesión activa con Hoko.', 'hoko-360' ); ?></p>
				
				<p class="submit">
					<button type="button" class="button button-secondary" id="hoko-logout-button">
						<?php esc_html_e( 'Cerrar sesión', 'hoko-360' ); ?>
					</button>
					<span class="spinner"></span>
				</p>
				
			<?php else : ?>
				<!-- Formulario de login -->
				<p><?php esc_html_e( 'Ingresa tus credenciales para conectar con Hoko.', 'hoko-360' ); ?></p>
				
				<form id="hoko-auth-form" method="post">
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								<label for="hoko_country">
									<?php esc_html_e( 'País', 'hoko-360' ); ?>
									<span class="required">*</span>
								</label>
							</th>
							<td>
								<select id="hoko_country" name="country" class="regular-text" required>
									<option value="colombia"><?php esc_html_e( 'Colombia', 'hoko-360' ); ?></option>
									<option value="ecuador"><?php esc_html_e( 'Ecuador', 'hoko-360' ); ?></option>
									<option value="usa"><?php esc_html_e( 'Estados Unidos', 'hoko-360' ); ?></option>
								</select>
								<p class="description">
									<?php esc_html_e( 'Selecciona el país al que deseas conectarte.', 'hoko-360' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="hoko_email">
									<?php esc_html_e( 'Email', 'hoko-360' ); ?>
									<span class="required">*</span>
								</label>
							</th>
							<td>
								<input 
									type="email" 
									id="hoko_email" 
									name="email" 
									class="regular-text" 
									required 
									autocomplete="email"
								/>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="hoko_password">
									<?php esc_html_e( 'Contraseña', 'hoko-360' ); ?>
									<span class="required">*</span>
								</label>
							</th>
							<td>
								<input 
									type="password" 
									id="hoko_password" 
									name="password" 
									class="regular-text" 
									required 
									autocomplete="current-password"
								/>
							</td>
						</tr>
					</tbody>
				</table>

					<p class="submit">
						<button type="submit" class="button button-primary" id="hoko-auth-submit">
							<?php esc_html_e( 'Autentificar', 'hoko-360' ); ?>
						</button>
						<span class="spinner"></span>
					</p>
				</form>
			<?php endif; ?>

			<div id="hoko-auth-message" class="hoko-message" style="display: none;"></div>
		</div>
	</div>
</div>
