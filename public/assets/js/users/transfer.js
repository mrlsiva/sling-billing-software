jQuery(document).ready(function ()
{
	jQuery('select[name="category"]').on('change',function(){
		var category = jQuery(this).val();
		if(category)
		{
			jQuery.ajax({
				url : 'transfer/get_sub_category',
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
					subCategorySelect.select2({ width: '100%', placeholder: 'Select' });
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
				url : 'transfer/get_product',
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
					productSelect.select2({ width: '100%', placeholder: 'Select' });
				}
			});
		}
	});
});

jQuery(document).ready(function () {

    jQuery('select[name="product"]').on('change', function () {

        var product = jQuery(this).val();

        if (product) {

            jQuery.ajax({
                url: 'transfer/get_product_detail',
                type: 'GET',
                dataType: 'json',
                data: { product: product },

                success: function (data) {

                    //console.log(data);
                    //console.log(data.imeis.length);

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


                    if (data.quantity == 0) {
                        $('#transfer').prop('disabled', true)
                            .attr('data-bs-original-title', 'You can’t transfer a product with 0 quantity.')
                            .tooltip('dispose').tooltip('show');
                    } else {
                        $('#transfer').prop('disabled', false)
                            .attr('data-bs-original-title', 'Click to transfer this product')
                            .tooltip('dispose').tooltip();
                    }

                    $("#variations_section").html("");

                    let hasVariation = false;

                    //console.log(data.variations);
                    $("#variations_section").html('');
                    data.variations.forEach(function (v) {

                        if (v.quantity > 0 && (v.size != null || v.colour != null)) {
                            hasVariation = true; // ✅ mark true
                            //console.log(v.queue_qty);
                            
                            $("#variations_section").append(`
                                <div class="row mb-2 p-2 border rounded">
                                    <div class="col-md-3"><strong>Size:</strong> ${v.size?.name ?? "-"}</div>
                                    <div class="col-md-3"><strong>Colour:</strong> ${v.colour?.name ?? "-"}</div>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control variation-qty" max="${v.quantity - v.queue_qty}" min="0" name="variation_qty[${v.id}]" placeholder="Available: ${v.quantity}">
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
                    });

                    // ✅ Set once AFTER loop
                    document.getElementById('quantity').readOnly = hasVariation;


                    // IMEI CHECKBOXES INLINE
                    $("#imei_list").html("");

                    let validImeis = data.imeis.filter(i => i && i.trim() !== "");

                    if (validImeis.length > 0) {
                        $("#imei_section").show();

                        $("#imei_list").html("");

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

    // Limit IMEI selection by quantity
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
            setTimeout(() => {
                window.location.href = response.redirect;
            }, 800);
        }
    });

    // Handle quantity changes
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



document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('transfer_submit');

    form.addEventListener('submit', function (e) {
        const available      = parseInt(document.getElementById('available').value, 10) || 0;
        const quantity       = parseInt(document.getElementById('quantity').value, 10) || 0;
        const queue_quantity = parseInt(document.getElementById('queue_stock').value, 10) || 0;

        const remainingStock = available - queue_quantity;

        if (quantity > available) {
            e.preventDefault();
            showToast("Quantity can't be greater than available stock.");
            return;
        }

        if (quantity > remainingStock) {
            e.preventDefault();
            showToast(`Only ${remainingStock} items are available after considering queued stock.`);
            return;
        }
    });

    function showToast(message) {
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

// When any variation qty changes
$(document).on("input", ".variation-qty", function () {

    let max = parseInt($(this).attr("data-max"));
    let val = parseInt($(this).val());

    // 1️⃣ Prevent higher than allowed
    if (val > max) {
        $(this).val(max);
        val = max;
    }

    // 2️⃣ Update total quantity
    updateMainQuantity();
});

function updateMainQuantity() {
    let total = 0;

    $(".variation-qty").each(function () {
        let val = parseInt($(this).val()) || 0;
        total += val;
    });

    $("#quantity").val(total);
}

// Enforce selection limit
$(document).on('change', '.imei-checkbox', function () {
    let maxQty = parseInt($('#quantity').val()) || 0;
    let checkedCount = $('.imei-checkbox:checked').length;

    if (checkedCount > maxQty) {
        this.checked = false;

        const event = new CustomEvent("toast", {
            detail: {
                text: "You can select only " + maxQty + " IMEI(s).",
                gravity: "top",
                position: "right",
                className: "success",
                duration: 5000,
                close: true,
            }
        });

        document.dispatchEvent(event);
        return false;
    }
});