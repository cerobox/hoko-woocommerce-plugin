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
		
		// Manejo del formulario de confirmación de orden
		$('#hoko-confirm-form').on('submit', function(e) {
			e.preventDefault();
			
			var $form = $(this);
			var $submitButton = $('#hoko-confirm-submit');
			var $spinner = $form.find('.spinner');
			var $message = $('#hoko-confirm-message');
			
			// Serializar datos del formulario
			var formData = $form.serializeArray();
			var postData = {
				action: 'hoko_create_order',
				nonce: hokoAdmin.nonce
			};
			
			// Convertir datos del formulario a objeto
			$.each(formData, function(i, field) {
				var name = field.name;
				var value = field.value;
				
				// Manejar arrays anidados (customer, stocks, measures)
				if (name.indexOf('[') !== -1) {
					var parts = name.match(/([^\[]+)\[([^\]]+)\](?:\[([^\]]+)\])?/);
					if (parts) {
						var mainKey = parts[1];
						var subKey = parts[2];
						var subSubKey = parts[3];
						
						if (!postData[mainKey]) {
							postData[mainKey] = {};
						}
						
						if (subSubKey) {
							// Caso: stocks[0][sku]
							if (!postData[mainKey][subKey]) {
								postData[mainKey][subKey] = {};
							}
							postData[mainKey][subKey][subSubKey] = value;
						} else {
							// Caso: customer[name]
							postData[mainKey][subKey] = value;
						}
					}
				} else {
					postData[name] = value;
				}
			});
			
			// Deshabilitar botón y mostrar spinner
			$submitButton.prop('disabled', true);
			$spinner.addClass('is-active');
			$message.hide();
			
			// Realizar petición AJAX
			$.ajax({
				url: hokoAdmin.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: postData,
				success: function(response) {
					if (response.success) {
						showConfirmMessage('success', response.data.message);
						// Redirigir a la página de órdenes después de 2 segundos
						setTimeout(function() {
							window.location.href = hokoAdmin.ordersUrl;
						}, 2000);
					} else {
						showConfirmMessage('error', response.data.message);
					}
				},
				error: function(xhr, status, error) {
					showConfirmMessage('error', 'Error en la conexión: ' + error);
				},
				complete: function() {
					// Rehabilitar botón y ocultar spinner
					$submitButton.prop('disabled', false);
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
		
		/**
		 * Muestra un mensaje en la página de confirmación
		 */
		function showConfirmMessage(type, message) {
			var $message = $('#hoko-confirm-message');
			var className = type === 'success' ? 'notice notice-success' : 'notice notice-error';
			
			$message
				.removeClass('notice-success notice-error')
				.addClass(className)
				.html('<p>' + message + '</p>')
				.show();
			
			// Scroll hacia el mensaje
			$('html, body').animate({
				scrollTop: $message.offset().top - 100
			}, 500);
		}
		
	});

})(jQuery);
