$(document).ready(function () {
    $('.branch').select2({
        width: '100%',
        placeholder: 'Select'
    });

    $('.transfer-to-select').select2({
        width: '100%',
        placeholder: 'Select',
        dropdownParent: $('#stockTransfer')
    });

    $('.branch-select').select2({
        width: '100%',
        placeholder: 'Select',
        dropdownParent: $('#stockTransfer')
    });

    $('.category-select').select2({
        width: '100%',
        placeholder: 'Select',
        dropdownParent: $('#stockTransfer')
    });

    $('.sub_category-select').select2({
        width: '100%',
        placeholder: 'Select',
        dropdownParent: $('#stockTransfer')
    });

    $('.product-select').select2({
        width: '100%',
        placeholder: 'Select',
        dropdownParent: $('#stockTransfer')
    });

    $('#transfer_to').on('change', function () {
        if ($(this).val() == '1') {
            $('#branch_field').show();
        } else {
            $('#branch_field').hide();
        }
    });

    // Trigger on page load
    $('#transfer_to').trigger('change');
});

jQuery(document).ready(function ()
{
	jQuery('select[name="category"]').on('change',function(){
		var category = jQuery(this).val();
		if(category)
		{
			jQuery.ajax({
				url : 'get_sub_category',
				type: 'GET',
				dataType: 'json',
				data: { id: category },
				success:function(data)
				{
					//console.log(data);

					jQuery('select[name="sub_category"]').empty();
					$('select[name="sub_category"]').append('<option value="">'+ "Select" +'</option>');
					jQuery.each(data, function(key,value){
						//console.log(value.name)
						$('select[name="sub_category"]').append('<option value="'+ value.id +'">'+ value.name +'</option>');
					});

					var subCategorySelect = $('select[name="sub_category"]');
					if (subCategorySelect.data('select2')) subCategorySelect.select2('destroy');
					subCategorySelect.select2({ width: '100%', placeholder: 'Select', dropdownParent: $('#stockTransfer') });
				}
			});
		}
	});
});

jQuery(document).ready(function ()
{
	jQuery('select[name="sub_category"]').on('change',function(){
		var sub_category = jQuery(this).val();
		var category = jQuery("#category").val();
		if(sub_category)
		{
			jQuery.ajax({
				url : 'get_product',
				type: 'GET',
				dataType: 'json',
				data: { sub_category: sub_category, category: category },
				success:function(data)
				{
					//console.log(data);

					jQuery('select[name="product"]').empty();
					$('select[name="product"]').append('<option value="">'+ "Select" +'</option>');
					jQuery.each(data, function(key,value){
						//console.log(value.name)
						$('select[name="product"]').append('<option value="'+ value.id +'">'+ value.name +' - '+ value.code +'</option>');
					});

					var productSelect = $('select[name="product"]');
					if (productSelect.data('select2')) productSelect.select2('destroy');
					productSelect.select2({ width: '100%', placeholder: 'Select', dropdownParent: $('#stockTransfer') });
				}
			});
		}
	});
});

/* ============================
   PRODUCT → PRODUCT DETAILS
============================ */
jQuery(document).ready(function () {

    jQuery('select[name="product"]').on('change', function () {

        var product = jQuery(this).val();

        if (product) {

            jQuery.ajax({
                url: 'get_product_detail',
                type: 'GET',
                dataType: 'json',
                data: { product: product },

                success: function (data) {

                    console.log(data);
                    
                    $("#unit").val(data.product.product.metric.name);
                    $("#available").val(data.quantity);
                    $("#price").val(data.product.product.discounted_price);
                    $("#queue_stock").val(data.totalQueueQuantity);

                    if (data.totalQueueQuantity > 0) {
                        $("#queueQtyText")
                            .removeClass("d-none")
                            .text("Queued Quantity: " + data.totalQueueQuantity);
                    } else {
                        $("#queueQtyText")
                            .addClass("d-none")
                            .text("");
                    }

                    // ENABLE / DISABLE ADD-TO-LIST BUTTON
                    if (data.quantity == 0) {
                        $('#add_to_transfer_list').prop('disabled', true)
                            .attr('data-bs-original-title', 'You can’t transfer a product with 0 quantity.')
                            .tooltip('dispose').tooltip('show');

                    } else {
                        $('#add_to_transfer_list').prop('disabled', false)
                            .attr('data-bs-original-title', 'Click to add this product to the transfer list')
                            .tooltip('dispose').tooltip();
                    }

                    /* ============================
                       VARIATION LIST UI
                    ============================ */
                    $("#variations_section").html("");

                    data.variations.forEach(function (v) {
                        if (v.quantity > 0 && (v.size != null || v.colour != null)) {
                            document.getElementById('quantity').readOnly = true;
                            $("#variations_section").append(`
                                <div class="row mb-2 p-2 border rounded">
                                    <div class="col-md-3"><strong>Size:</strong> ${v.size?.name ?? "-"}</div>
                                    <div class="col-md-3"><strong>Colour:</strong> ${v.colour?.name ?? "-"}</div>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control variation-qty"
                                            data-max="${v.quantity - v.queue_qty}"
                                            max="${v.quantity - v.queue_qty}" min="0"
                                            name="variation_qty[${v.id}]"
                                            placeholder="Available: ${v.quantity}">
                                        <small class="text-muted">
                                            Stock: ${v.quantity}
                                            ${v.queue_qty > 0
                                                ? ` | <span class="text-danger">Queued: ${v.queue_qty}</span>`
                                                : ''
                                            }
                                        </small>
                                    </div>
                                </div>
                            `);
                        }
                        else
                        {
                            document.getElementById('quantity').readOnly = false;
                        }
                    });

                    /* ============================
                       IMEI LIST
                    ============================ */
                    $("#imei_list").html("");

                    let validImeis = data.imeis.filter(i => i && i.trim() !== "");

                    if (validImeis.length > 0) {
                        $("#imei_section").show();

                        validImeis.forEach(function (imei, index) {
                            $("#imei_list").append(`
                                <div class="form-check" style="min-width:120px;">
                                    <input type="checkbox" class="form-check-input imei-checkbox"
                                           name="imeis[]" value="${imei}" id="imei_${index}">
                                    <label for="imei_${index}" class="form-check-label">${imei}</label>
                                </div>
                            `);
                        });

                    } else {
                        $("#imei_section").hide();
                    }

                }
            });
        }
    });

    $(document).on('input', '.variation-qty', function () {
        let max = parseInt($(this).attr('max')) || 0;
        let value = parseInt($(this).val()) || 0;

        if (value > max) {
            $(this).val(max);
        }
    });


    /* ============================
       IMEI VALIDATION
    ============================ */

    $(document).on('change', '.imei-checkbox', function () {
        let allowed = parseInt($("#quantity").val());
        let selected = $(".imei-checkbox:checked").length;

        if (allowed && selected > allowed) {
            $(this).prop('checked', false);

            const event = new CustomEvent("toast", {
                detail: {
                    text: "You can select only " + allowed + " IMEIs.",
                    gravity: "top",
                    position: "right",
                    className: "success",
                    duration: 5000,
                    close: true,
                }
            });

            document.dispatchEvent(event);
        }
    });

    $("#quantity").on('input', function () {

        let allowed = parseInt($(this).val());
        let selected = $(".imei-checkbox:checked").length;

        if (allowed < selected) {

            const event = new CustomEvent("toast", {
                detail: {
                    text: "Quantity reduced! Removing extra selected IMEIs.",
                    gravity: "top",
                    position: "right",
                    className: "success",
                    duration: 5000,
                    close: true,
                }
            });

            document.dispatchEvent(event);
            $(".imei-checkbox:checked").slice(allowed).prop('checked', false);
        }
    });
});


/* ============================
   VARIATION TOTAL → QUANTITY
============================ */
$(document).on("input", ".variation-qty", function () {

    let max = parseInt($(this).attr("data-max"));
    let val = parseInt($(this).val());

    if (val > max) {
        $(this).val(max);
        val = max;
    }

    updateMainQuantity();
});

function updateMainQuantity() {
    let total = 0;
    $(".variation-qty").each(function () {
        total += parseInt($(this).val()) || 0;
    });

    $("#quantity").val(total);
}

function transferToast(message) {
    const event = new CustomEvent("toast", {
        detail: {
            text: message,
            gravity: "top",
            position: "right",
            className: "success",
            duration: 5000,
            close: true,
        }
    });

    document.dispatchEvent(event);
}

function resetTransferPicker() {
    $('select[name="product"]').val(null).trigger('change');
    $('#unit').val('');
    $('#available').val('');
    $('#price').val('');
    $('#quantity').val('').prop('readOnly', false);
    $('#queue_stock').val('');
    $('#queueQtyText').addClass('d-none').text('');
    $('#variations_section').html('');
    $('#imei_list').html('');
    $('#imei_section').hide();
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('transfer_submit');
    const cartBody = document.getElementById('transfer_cart_body');
    const cartSection = document.getElementById('transfer_cart_section');
    const transferBtn = document.getElementById('transfer');

    function updateCartVisibility() {
        const hasItems = cartBody.children.length > 0;
        cartSection.classList.toggle('d-none', !hasItems);
        transferBtn.disabled = !hasItems;
        $('.transfer-to-select').prop('disabled', hasItems);
        $('.branch-select').prop('disabled', hasItems);
    }

    document.getElementById('add_to_transfer_list').addEventListener('click', function () {

        const transferTo = $('.transfer-to-select').val();
        const branch = $('.branch-select').val();
        const productSelect = $('select[name="product"]');
        const productId = productSelect.val();
        const productName = productSelect.find('option:selected').text();

        if (!transferTo) {
            transferToast('Please select where to transfer to.');
            return;
        }

        if (transferTo == '1' && !branch) {
            transferToast('Please select a branch.');
            return;
        }

        if (!productId) {
            transferToast('Please select a product.');
            return;
        }

        const existing = cartBody.querySelector('tr[data-product-id="' + productId + '"]');
        if (existing) {
            transferToast('This product is already in the list. Remove it first to change quantity or price.');
            return;
        }

        const available      = parseInt(document.getElementById('available').value, 10) || 0;
        const queue_quantity = parseInt(document.getElementById('queue_stock').value, 10) || 0;
        const quantity        = parseInt(document.getElementById('quantity').value, 10) || 0;
        const price           = parseFloat(document.getElementById('price').value) || 0;
        const remainingStock  = available - queue_quantity;

        if (quantity <= 0) {
            transferToast("Quantity must be greater than 0.");
            return;
        }

        if (!price || price <= 0) {
            transferToast("Please enter a valid price.");
            return;
        }

        if (quantity > available) {
            transferToast("Quantity can't be greater than available stock.");
            return;
        }

        if (quantity > remainingStock) {
            transferToast(`Only ${remainingStock} items are available after considering queued stock.`);
            return;
        }

        const variationQty = {};
        $('.variation-qty').each(function () {
            const val = parseInt($(this).val(), 10) || 0;
            if (val > 0) {
                const match = /variation_qty\[(\d+)\]/.exec($(this).attr('name'));
                if (match) {
                    variationQty[match[1]] = val;
                }
            }
        });

        const imeis = $('.imei-checkbox:checked').map(function () { return $(this).val(); }).get();

        if ($('#imei_section').is(':visible') && imeis.length !== quantity) {
            transferToast('Please select exactly ' + quantity + ' IMEI number(s).');
            return;
        }

        const amount = quantity * price;

        const row = document.createElement('tr');
        row.setAttribute('data-product-id', productId);
        row.innerHTML = '<td>' + $('<div>').text(productName).html() + '</td>' +
            '<td>' + quantity + '</td>' +
            '<td>' + price.toFixed(2) + '</td>' +
            '<td>' + amount.toFixed(2) + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-cart-item"><i class="ri-delete-bin-line"></i></button></td>';

        $(row).data('item', {
            product_id: productId,
            quantity: quantity,
            price: price,
            imeis: imeis,
            variation_qty: variationQty
        });

        cartBody.appendChild(row);
        updateCartVisibility();
        resetTransferPicker();
    });

    cartBody.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-cart-item');
        if (!btn) return;

        btn.closest('tr').remove();
        updateCartVisibility();
    });

    form.addEventListener('submit', function (e) {

        if (cartBody.children.length === 0) {
            e.preventDefault();
            transferToast('Add at least one product to the transfer list.');
            return;
        }

        // Disabled fields are excluded from form submission, so re-enable
        // these (locked for UX once the cart has items) right before the
        // native submit reads the form's values.
        $('.transfer-to-select').prop('disabled', false);
        $('.branch-select').prop('disabled', false);

        form.querySelectorAll('input[data-cart-input]').forEach(function (el) { el.remove(); });

        Array.from(cartBody.children).forEach(function (row, index) {
            const item = $(row).data('item');

            appendHiddenInput(form, `items[${index}][product_id]`, item.product_id);
            appendHiddenInput(form, `items[${index}][quantity]`, item.quantity);
            appendHiddenInput(form, `items[${index}][price]`, item.price);

            item.imeis.forEach(function (imei) {
                appendHiddenInput(form, `items[${index}][imeis][]`, imei);
            });

            Object.keys(item.variation_qty).forEach(function (variationId) {
                appendHiddenInput(form, `items[${index}][variation_qty][${variationId}]`, item.variation_qty[variationId]);
            });
        });
    });

    function appendHiddenInput(form, name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.setAttribute('data-cart-input', '1');
        input.name = name;
        input.value = value;
        form.appendChild(input);
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
