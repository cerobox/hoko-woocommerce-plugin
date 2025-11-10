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

			<div id="hoko-auth-message" class="hoko-message" style="display: none;"></div>
		</div>
	</div>
</div>
