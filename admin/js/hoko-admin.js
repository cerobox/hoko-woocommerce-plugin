(function($) {
	'use strict';

	/**
	 * Maneja el formulario de autentificación y logout
	 */
	$(document).ready(function() {
		
		// Manejo del formulario de login
		$('#hoko-auth-form').on('submit', function(e) {
			e.preventDefault();
			
			var $form = $(this);
			var $submitButton = $('#hoko-auth-submit');
			var $spinner = $form.find('.spinner');
			var $message = $('#hoko-auth-message');
			
			// Obtener datos del formulario
			var email = $('#hoko_email').val();
			var password = $('#hoko_password').val();
			
			// Validación básica
			if (!email || !password) {
				showMessage('error', 'Por favor completa todos los campos.');
				return;
			}
			
			// Deshabilitar botón y mostrar spinner
			$submitButton.prop('disabled', true);
			$spinner.addClass('is-active');
			$message.hide();
			
			// Realizar petición AJAX
			$.ajax({
				url: hokoAdmin.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'hoko_authenticate',
					nonce: hokoAdmin.nonce,
					email: email,
					password: password
				},
				success: function(response) {
					if (response.success) {
						showMessage('success', response.data.message);
						// Recargar la página después de 1 segundo para mostrar el estado autenticado
						setTimeout(function() {
							location.reload();
						}, 1000);
					} else {
						showMessage('error', response.data.message);
					}
				},
				error: function(xhr, status, error) {
					showMessage('error', 'Error en la conexión: ' + error);
				},
				complete: function() {
					// Rehabilitar botón y ocultar spinner
					$submitButton.prop('disabled', false);
					$spinner.removeClass('is-active');
				}
			});
		});
		
		// Manejo del botón de logout
		$('#hoko-logout-button').on('click', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var $spinner = $button.siblings('.spinner');
			var $message = $('#hoko-auth-message');
			
			// Confirmar acción
			if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
				return;
			}
			
			// Deshabilitar botón y mostrar spinner
			$button.prop('disabled', true);
			$spinner.addClass('is-active');
			$message.hide();
			
			// Realizar petición AJAX
			$.ajax({
				url: hokoAdmin.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'hoko_logout',
					nonce: hokoAdmin.nonce
				},
				success: function(response) {
					if (response.success) {
						showMessage('success', response.data.message);
						// Recargar la página después de 1 segundo
						setTimeout(function() {
							location.reload();
						}, 1000);
					} else {
						showMessage('error', response.data.message);
					}
				},
				error: function(xhr, status, error) {
					showMessage('error', 'Error en la conexión: ' + error);
				},
				complete: function() {
					// Rehabilitar botón y ocultar spinner
					$button.prop('disabled', false);
					$spinner.removeClass('is-active');
				}
			});
		});
		
		/**
		 * Muestra un mensaje de respuesta
		 */
		function showMessage(type, message) {
			var $message = $('#hoko-auth-message');
			var className = type === 'success' ? 'notice notice-success' : 'notice notice-error';
			
			$message
				.removeClass('notice-success notice-error')
				.addClass(className)
				.html('<p>' + message + '</p>')
				.slideDown();
			
			// Ocultar mensaje después de 5 segundos
			setTimeout(function() {
				$message.slideUp();
			}, 5000);
		}
		
	});

})(jQuery);
