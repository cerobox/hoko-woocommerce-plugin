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
			var country = $('#hoko_country').val();
			var email = $('#hoko_email').val();
			var password = $('#hoko_password').val();
			
			// Validación básica
			if (!country || !email || !password) {
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
					country: country,
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
		
		// Manejo del botón de crear orden en Hoko (usando delegación de eventos)
		$(document).on('click', '.hoko-create-order', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var $spinner = $button.siblings('.spinner');
			var orderId = $button.data('order-id');
			var $row = $button.closest('tr');
			
			console.log('Botón clickeado, Order ID:', orderId);
			
			// Validar que tenemos el order ID
			if (!orderId) {
				alert('Error: No se pudo obtener el ID de la orden.');
				return;
			}
			
			// Confirmar acción
			if (!confirm('¿Estás seguro de que deseas crear esta orden en Hoko?')) {
				return;
			}
			
			// Deshabilitar botón y mostrar spinner
			$button.prop('disabled', true);
			$spinner.addClass('is-active');
			
			// Realizar petición AJAX
			$.ajax({
				url: hokoAdmin.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'hoko_create_order',
					nonce: hokoAdmin.nonce,
					order_id: orderId
				},
				success: function(response) {
					if (response.success) {
						// Actualizar estado en la tabla
						var $statusCell = $row.find('.hoko-sync-status');
						$statusCell
							.removeClass('hoko-sync-pending hoko-sync-failed')
							.addClass('hoko-sync-synced')
							.text('Sincronizado');
						
						// Reemplazar botón con checkmark
						$button.parent().html(
							'<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> Sincronizado'
						);
						
						// Mostrar mensaje de éxito
						showOrderMessage($row, 'success', response.data.message);
					} else {
						// Actualizar estado a fallido
						var $statusCell = $row.find('.hoko-sync-status');
						$statusCell
							.removeClass('hoko-sync-pending hoko-sync-synced')
							.addClass('hoko-sync-failed')
							.text('Fallido');
						
						// Agregar mensaje de error
						if (!$row.find('.hoko-sync-message').length) {
							$statusCell.after('<br><small class="hoko-sync-message">' + response.data.message + '</small>');
						} else {
							$row.find('.hoko-sync-message').text(response.data.message);
						}
						
						showOrderMessage($row, 'error', response.data.message);
					}
				},
				error: function(xhr, status, error) {
					showOrderMessage($row, 'error', 'Error en la conexión: ' + error);
				},
				complete: function() {
					// Rehabilitar botón y ocultar spinner
					$button.prop('disabled', false);
					$spinner.removeClass('is-active');
				}
			});
		});
		
		/**
		 * Muestra un mensaje de respuesta para una orden específica
		 */
		function showOrderMessage($row, type, message) {
			var className = type === 'success' ? 'notice notice-success' : 'notice notice-error';
			var $message = $('<tr class="hoko-order-message"><td colspan="7"><div class="' + className + '"><p>' + message + '</p></div></td></tr>');
			
			// Remover mensaje anterior si existe
			$row.next('.hoko-order-message').remove();
			
			// Insertar nuevo mensaje
			$row.after($message);
			
			// Ocultar mensaje después de 5 segundos
			setTimeout(function() {
				$message.fadeOut(function() {
					$(this).remove();
				});
			}, 5000);
		}
		
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
