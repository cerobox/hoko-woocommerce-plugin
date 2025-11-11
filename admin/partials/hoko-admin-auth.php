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
						$auth_email = get_option( 'hoko_360_auth_email', '' );
						$auth_time  = get_option( 'hoko_360_auth_time', '' );
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
				
				<p><?php esc_html_e( 'Ya tienes una sesión activa con Hoko 360.', 'hoko-360' ); ?></p>
				
				<p class="submit">
					<button type="button" class="button button-secondary" id="hoko-logout-button">
						<?php esc_html_e( 'Cerrar sesión', 'hoko-360' ); ?>
					</button>
					<span class="spinner"></span>
				</p>
				
			<?php else : ?>
				<!-- Formulario de login -->
				<p><?php esc_html_e( 'Ingresa tus credenciales para conectar con Hoko 360.', 'hoko-360' ); ?></p>
				
				<form id="hoko-auth-form" method="post">
				<table class="form-table" role="presentation">
					<tbody>
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
