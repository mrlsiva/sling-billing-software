document.addEventListener("click", function(e) {
	if (e.target.closest("a[data-bs-target='#vendorEdit']")) {
		const btn = e.target.closest("a[data-bs-target='#vendorEdit']");

		document.getElementById("vendor_id").value      = btn.getAttribute("data-id");
		document.getElementById("vendor_name").value    = btn.getAttribute("data-name");
		document.getElementById("vendor_phone").value   = btn.getAttribute("data-phone");
		document.getElementById("vendor_email").value   = btn.getAttribute("data-email");
		document.getElementById("vendor_address").value = btn.getAttribute("data-address");
		document.getElementById("vendor_address1").value= btn.getAttribute("data-address1");
		document.getElementById("vendor_city").value    = btn.getAttribute("data-city");
		document.getElementById("vendor_state").value   = btn.getAttribute("data-state");
		document.getElementById("vendor_gst").value     = btn.getAttribute("data-gst");
	}
});

document.addEventListener("DOMContentLoaded", function () {
	let searchInput = document.getElementById("searchInput");
	let clearFilter = document.getElementById("clearFilter");

	function toggleClear() {
		if (searchInput.value.trim() !== "") {
			clearFilter.style.display = "inline-flex";
		} else {
			clearFilter.style.display = "none";
		}
	}

	// Run on load (for prefilled request values)
	toggleClear();

	// Run on typing
	searchInput.addEventListener("input", toggleClear);
});

$('#addVendor').on('submit', function (e) {
	e.preventDefault();

	let form = this;
	let formData = new FormData(form);

	$.ajax({
		url: document.querySelector('meta[name="vendor-store-url"]').content,
		type: "POST",
		data: formData,
		dataType: "json",

	        /* REQUIRED FOR FormData */
		processData: false,
		contentType: false,

	        /* CSRF */
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},

		beforeSend: function () {
			$('#addVendorButton').prop('disabled', true).html('<i class="ri-loader-4-line"></i> Submitting...');
		},

		success: function (response) {
			console.log(response);

			if (response.status === true) 
			{
				let currentUrl = window.location.pathname;

				if (currentUrl.includes('vendors/index')) 
				{
					alert(response.message);
					window.location.href = response.redirect;
				}
				else 
				{
					let vendor = response.data;

					let vendorSelect = $('#vendor');

	                    /* Append vendor if not exists */
					if (vendorSelect.find('option[value="' + vendor.id + '"]').length === 0) {
						vendorSelect.append(
							`<option value="${vendor.id}">
	                                ${vendor.name}
						</option>`
						);
					}

	                    /* Select newly created vendor */
					vendorSelect.val(vendor.id).trigger('change');

	                    /* Close modal */
					$('#vendorAdd').modal('hide');

	                    /* Reset form */
					$('#addVendor')[0].reset();

	                    /* Restore submit button */
					$('#addVendorButton').prop('disabled', false).html('<i class="ri-save-line"></i> Submit');
				}
			}
			else
			{
				alert(response.message);
				$('#addVendorButton').prop('disabled', false).html('<i class="ri-save-line"></i> Submit');
			}
		},

		error: function (xhr, status, error) {
			console.error('STATUS:', xhr.status);
			console.error('RESPONSE:', xhr.responseText);
			console.error('ERROR:', error);

			let message = 'Something went wrong. Please try again.';

	            /* Laravel Validation Errors (422) */
			if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
				let errors = xhr.responseJSON.errors;
				let messages = [];

				$.each(errors, function (key, value) {
					messages.push(value[0]);
				});

				message = messages.join('\n');
			}

	            /* CSRF expired (419) */
			else if (xhr.status === 419) {
				message = 'Session expired. Please refresh the page.';
			}

	            /* Server error */
			else if (xhr.responseJSON && xhr.responseJSON.message) {
				message = xhr.responseJSON.message;
			}

			alert(message);

	            /* Restore submit button */
			$('#addVendorButton').prop('disabled', false).html('<i class="ri-save-line"></i> Submit');
		}
	});
});

document.addEventListener("DOMContentLoaded", function () {
	let editForm = document.getElementById("editVendor");

	editForm.addEventListener("submit", function (e) {
		let name = document.getElementById("vendor_name").value.trim();
		let phone = document.getElementById("vendor_phone").value.trim();
		let gst = document.getElementById("vendor_gst").value.trim();
		let errors = [];

		        // Name validation
		if (!name) {
			errors.push("Name is required");
		}

		// Phone validation (10 digits only)
		let phoneRegex = /^[0-9]{10}$/;
		if (!phone) {
			errors.push("Phone is required");
		} else if (!phoneRegex.test(phone)) {
			errors.push("Phone must be a 10-digit number");
		}

		// GST validation (only if provided)
		if (gst) {
			let gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i;
			if (!gstRegex.test(gst)) {
				errors.push("Invalid GST format");
			}
		}

		// If errors, stop submit and alert
		if (errors.length > 0) {
			e.preventDefault();
			alert(errors.join("\n"));
		}
	});
});