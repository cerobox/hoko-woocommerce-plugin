(function($) {
	'use strict';

	/**
	 * Valida el formato del objeto customer según la especificación
	 */
	function validateCustomerFormat(customer) {
		if (!customer) {
			return 'Los datos del cliente son inválidos.';
		}
		
		if (!customer.name || customer.name.trim() === '') {
			return 'El nombre del cliente es requerido.';
		}
		if (customer.name.length > 100) {
			return 'El nombre del cliente no puede exceder 100 caracteres.';
		}
		
		if (!customer.email || customer.email.trim() === '') {
			return 'El email del cliente es requerido.';
		}
		var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if (!emailRegex.test(customer.email)) {
			return 'El formato del email no es válido.';
		}
		if (customer.email.length > 100) {
			return 'El email no puede exceder 100 caracteres.';
		}
		
		if (!customer.identification || customer.identification.trim() === '') {
			return 'La identificación del cliente es requerida.';
		}
		if (customer.identification.length > 20) {
			return 'La identificación no puede exceder 20 caracteres.';
		}
		
		if (!customer.phone || customer.phone.trim() === '') {
			return 'El teléfono del cliente es requerido.';
		}
		var phoneRegex = /^[0-9\s\-\+\(\)]+$/;
		if (!phoneRegex.test(customer.phone)) {
			return 'El formato del teléfono no es válido. Solo se permiten dígitos y caracteres especiales comunes (+, -, (), espacios).';
		}
		if (customer.phone.length > 20) {
			return 'El teléfono no puede exceder 20 caracteres.';
		}
		
		if (!customer.address || customer.address.trim() === '') {
			return 'La dirección del cliente es requerida.';
		}
		if (customer.address.length > 200) {
			return 'La dirección no puede exceder 200 caracteres.';
		}

		if (!customer.city || customer.city.trim() === '') {
			return 'La ciudad del cliente es requerida.';
		}
		
		return null;
	}

	/**
	 * Formatea el objeto customer exactamente como lo requiere la API
	 */
	function formatCustomerForAPI(customer) {
		return {
			"name": customer.name,
			"email": customer.email,
			"identification": customer.identification,
			"phone": customer.phone,
			"address": customer.address,
			"city_id": customer.city_id
		};
	}

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
		
		// Manejo del botón de refresh token
		$('#hoko-refresh-token-button').on('click', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var $spinner = $button.siblings('.spinner');
			var $message = $('#hoko-auth-message');
			
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
					action: 'hoko_refresh_token',
					nonce: hokoAdmin.nonce
				},
				success: function(response) {
					//console.log('HOKO Refresh Token Response:', response);

					if (response.data && response.data.debug) {
						//console.log('📊 HOKO Debug Data:', response.data.debug);
					}

					if (response.success) {
						showMessage('success', response.data.message);
						// Actualizar la fecha de refresh en la UI
						if (response.data.refresh_time) {
							var $refreshTime = $('#token-refresh-time');
							if ($refreshTime.length) {
								$refreshTime.text('Token refrescado: ' + response.data.refresh_time);
							}
						}
					} else {
						// Mensajes específicos según el tipo de error
						if (response.data.no_credentials) {
							showMessage('error', response.data.message + ' <br><br><strong>Nota:</strong> Esta sesión fue creada antes de implementar el refresh automático.');
						} else if (response.data.credentials_invalid) {
							showMessage('error', response.data.message);
						} else {
							showMessage('error', response.data.message);
						}
						
						// Si requiere re-autenticación, mostrar botón de logout
						if (response.data.require_reauth) {
							var $logoutBtn = $('#hoko-logout-button');
							if ($logoutBtn.length) {
								$logoutBtn.addClass('button-primary').removeClass('button-secondary');
								$logoutBtn.text('Cerrar sesión y volver a autenticar');
							}
						}
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
		
		// Manejo del botón de cotización de envío
		$('#hoko-quote-shipping').on('click', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var $resultsDiv = $('#hoko-quotation-results');
			var $message = $('#hoko-confirm-message');
			
			// Obtener datos del formulario para la cotización
			var orderId = $('input[name="order_id"]').val();
			var billingCity = $('#billing_city').val();
			var billingState = $('#billing_state').val();
			var payment = $('#payment').val();
			var declaredValue = $('#declared_value').val() || 10000;
			
			// Calcular collection_value como la suma de (precio * cantidad) de todos los productos
			var collectionValue = 0;
			$('input[name*="[price]"]').each(function() {
				var $priceInput = $(this);
				var price = parseFloat($priceInput.val()) || 0;
				
				// Obtener el índice del producto
				var name = $priceInput.attr('name');
				var matches = name.match(/stocks\[(\d+)\]\[price\]/);
				if (matches) {
					var index = matches[1];
					var $amountInput = $('input[name="stocks[' + index + '][amount]"]');
					var amount = parseInt($amountInput.val()) || 0;
					collectionValue += (price * amount);
				}
			});
			
			// Validar campos requeridos
			if (!billingCity || !billingState) {
				showConfirmMessage('error', 'No se encontró la ciudad y departamento de facturación.');
				return;
			}
			
			// Obtener stock_ids de los productos
			var stockIds = [];
			$('input[name*="[sku]"]').each(function() {
				var sku = $(this).val();
				if (sku) {
					stockIds.push(sku);
				}
			});
			
			if (stockIds.length === 0) {
				showConfirmMessage('error', 'No se encontraron productos para cotizar.');
				return;
			}
			
			// Deshabilitar botón y mostrar estado de carga
			$button.prop('disabled', true).text('Cotizando...');
			$message.hide();
			$resultsDiv.hide();
			
			// Resetear botón de confirmación y campos ocultos
			$('#hoko-confirm-submit').prop('disabled', true);
			$('#selected_courier_id').val('');
			$('#selected_courier_value').val('');
			
			// Realizar petición AJAX para cotización
			$.ajax({
				url: hokoAdmin.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'hoko_get_shipping_quotation',
					nonce: hokoAdmin.nonce,
					order_id: orderId,
					stock_ids: stockIds.join(','),
					city: billingCity,
					state: billingState,
					payment: payment,
					declared_value: declaredValue,
					collection_value: collectionValue
				},
				success: function(response) {
					if (response.success) {
						displayQuotationResults(response.data.quotations);
					} else {
						showConfirmMessage('error', response.data.message);
						// Si requiere re-autenticación, redirigir a la página de auth
						if (response.data.require_reauth) {
							setTimeout(function() {
								window.location.href = hokoAdmin.authPageUrl || 'admin.php?page=hoko-360-auth';
							}, 2000);
						}
					}
				},
				error: function(xhr, status, error) {
					showConfirmMessage('error', 'Error en la conexión: ' + error);
				},
				complete: function() {
					// Rehabilitar botón
					$button.prop('disabled', false).text('Cotizar envío');
				}
			});
		});
		
		/**
		 * Muestra los resultados de la cotización como radio buttons
		 */
		function displayQuotationResults(quotations) {
			var $resultsDiv = $('#hoko-quotation-results');
			
			if (!quotations || quotations.length === 0) {
				showConfirmMessage('error', 'No se encontraron opciones de envío disponibles.');
				return;
			}
			
			// Ordenar cotizaciones por valor (de menor a mayor)
			quotations.sort(function(a, b) {
				return a.value - b.value;
			});
			
			// Generar HTML para las opciones
			var html = '<h4>Selecciona una transportadora:</h4>';
			html += '<div class="quotation-options">';
			
			quotations.forEach(function(quotation, index) {
				var courierId = quotation.courier_id;
				var courierName = quotation.courier_name;
				var courierLogo = quotation.courier_logo;
				var value = quotation.value;
				var formattedValue = new Intl.NumberFormat('es-CO', {
					style: 'currency',
					currency: 'COP'
				}).format(value);
				
				var isSelected = index === 0 ? 'selected' : '';
				
				html += '<div class="quotation-option ' + isSelected + '">';
				html += '<label>';
				html += '<input type="radio" name="courier_option" value="' + courierId + '" data-value="' + value + '" ' + (index === 0 ? 'checked' : '') + '>';
				html += '<img src="' + courierLogo + '" alt="' + courierName + '">';
				html += '<strong>' + courierName + '</strong>';
				html += '<span>' + formattedValue + '</span>';
				html += '</label>';
				html += '</div>';
			});
			
			html += '</div>';
			
			// Mostrar resultados
			$resultsDiv.html(html).slideDown();
			
		// Manejar selección de transportadora
			$('input[name="courier_option"]').on('change', function() {
				var selectedId = $(this).val();
				var selectedValue = $(this).data('value');
				
				// Actualizar campos ocultos
				$('#selected_courier_id').val(selectedId);
				$('#selected_courier_value').val(selectedValue);
				
				// Habilitar botón de confirmación
				$('#hoko-confirm-submit').prop('disabled', false);
				
				// Actualizar estado visual de las opciones
				$('.quotation-option').removeClass('selected');
				$(this).closest('.quotation-option').addClass('selected');
			});
			
			// Establecer valores iniciales (primera opción)
			var $firstOption = $('input[name="courier_option"]:checked');
			if ($firstOption.length > 0) {
				$('#selected_courier_id').val($firstOption.val());
				$('#selected_courier_value').val($firstOption.data('value'));
				// Habilitar botón de confirmación si hay una opción preseleccionada
				$('#hoko-confirm-submit').prop('disabled', false);
			}
		}

		// Manejo del formulario de confirmación de orden
		$('#hoko-confirm-form').on('submit', function(e) {
			e.preventDefault();
			
			var $form = $(this);
			var $submitButton = $('#hoko-confirm-submit');
			var $spinner = $form.find('.spinner');
			var $message = $('#hoko-confirm-message');
			
			// Validar que se haya seleccionado una transportadora
			var selectedCourierId = $('#selected_courier_id').val();
			if (!selectedCourierId) {
				showConfirmMessage('error', 'Por favor cotiza el envío y selecciona una transportadora antes de crear la orden.');
				return;
			}
			
			// Validar formato del objeto customer
			var billingCity = $('#billing_city').val().trim();
			var billingState = $('#billing_state').val().trim();
			var cityFormatted = '';
			
			if (billingCity && billingState) {
				cityFormatted = billingCity + ', ' + billingState;
			} else if (billingCity) {
				cityFormatted = billingCity;
			} else if (billingState) {
				cityFormatted = billingState;
			}
			
			var customerData = {
				name: $('#customer_name').val().trim(),
				email: $('#customer_email').val().trim(),
				identification: $('#customer_identification').val().trim(),
				phone: $('#customer_phone').val().trim(),
				address: $('#customer_address').val().trim(),
				city: cityFormatted
			};
			
			var validationError = validateCustomerFormat(customerData);
			if (validationError) {
				showConfirmMessage('error', validationError);
				return;
			}
			
			// Formatear el customer según el formato exacto de la API
			var formattedCustomer = formatCustomerForAPI(customerData);
			var customerJSON = JSON.stringify(formattedCustomer);
			
			// Construir stocks como JSON string
			var stocksData = {};
			$form.find('input[name^="stocks["]').each(function() {
				var $input = $(this);
				var name = $input.attr('name');
				var value = $input.val();
				
				// Parsear stocks[0][sku], stocks[0][amount], stocks[0][price]
				var matches = name.match(/stocks\[(\d+)\]\[(\w+)\]/);
				if (matches && value) {
					var index = matches[1];
					var field = matches[2];
					
					// El SKU es la clave del objeto stocks
					if (field === 'sku') {
						if (!stocksData[value]) {
							stocksData[value] = {};
						}
						// Guardar el índice para asociar amount y price
						$input.data('stock-key', value);
					} else if (field === 'amount' || field === 'price') {
						// Buscar el SKU correspondiente a este índice
						var sku = $form.find('input[name="stocks[' + index + '][sku]"]').val();
						if (sku) {
							if (!stocksData[sku]) {
								stocksData[sku] = {};
							}
							stocksData[sku][field] = field === 'amount' ? parseInt(value) : parseFloat(value);
						}
					}
				}
			});
			var stocksJSON = JSON.stringify(stocksData);
			
			// Preparar datos para enviar - serialización manual
			var formDataParts = [];
			formDataParts.push('action=' + encodeURIComponent('hoko_create_order'));
			formDataParts.push('nonce=' + encodeURIComponent(hokoAdmin.nonce));
			formDataParts.push('customer=' + encodeURIComponent(customerJSON));
			formDataParts.push('stocks=' + encodeURIComponent(stocksJSON));
			
			// Agregar otros campos del formulario (excluyendo customer, measures, stocks, billing_city y billing_state)
			var $formInputs = $form.find('input, select, textarea').not('[name^="customer["]').not('[name^="measures["]').not('[name^="stocks["]').not('#billing_city').not('#billing_state');
			$formInputs.each(function() {
				var $input = $(this);
				var name = $input.attr('name');
				var value = $input.val();
				
				if (name && value !== undefined && value !== '') {
					formDataParts.push(encodeURIComponent(name) + '=' + encodeURIComponent(value));
				}
			});
			
			var postDataString = formDataParts.join('&');
			
			// Deshabilitar botón y mostrar spinner
			$submitButton.prop('disabled', true);
			$spinner.addClass('is-active');
			$message.hide();
			
			// Realizar petición AJAX
			$.ajax({
				url: hokoAdmin.ajaxurl,
				type: 'POST',
				data: postDataString,
				contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
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
		 * Reset quotation results and disable confirm button
		 */
		function resetQuotation() {
			$('#hoko-quotation-results').hide();
			$('#hoko-confirm-submit').prop('disabled', true);
			$('#selected_courier_id').val('');
			$('#selected_courier_value').val('');
		}

		// Monitor form changes to reset quotation
		$('#declared_value, #payment, #measures_height, #measures_width, #measures_length, #measures_weight').on('change', function() {
			resetQuotation();
		});

		// Monitor product quantity and price changes
		$(document).on('change', 'input[name*="[amount]"], input[name*="[price]"]', function() {
			resetQuotation();
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
