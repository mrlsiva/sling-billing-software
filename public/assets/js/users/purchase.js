jQuery(document).ready(function () {
    jQuery('select[name="category"]').on('change', function () {
        var category = jQuery(this).val();
        if (category) {
            jQuery.ajax({
                url: '../../products/get_sub_category',
                type: 'GET',
                dataType: 'json',
                data: { id: category },
                success: function (data) {
                    console.log(data);

                    jQuery('select[name="sub_category"]').empty();
                    $('select[name="sub_category"]').append('<option value="">' + "Select" + '</option>');
                    jQuery.each(data, function (key, value) {
                        console.log(value.name)
                        $('select[name="sub_category"]').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });

                }
            });
        }
    });
});

jQuery(document).ready(function () {
    jQuery('select[name="sub_category"]').on('change', function () {
        var sub_category = jQuery(this).val();
        var category = jQuery('#category').val();
        jQuery.ajax({
            url: 'get_product',
            type: 'GET',
            dataType: 'json',
            data: { category: category, sub_category: sub_category },
            success: function (data) {
                console.log(data);

                jQuery('select[name="product"]').empty();
                $('select[name="product"]').append('<option value="">' + "Select" + '</option>');
                jQuery.each(data, function (key, value) {
                    console.log(value.name)
                    $('select[name="product"]').append('<option value="' + value.id + '">' + value.name + '</option>');
                });

            }
        });
    });
});

jQuery(document).ready(function ()
{
	jQuery('select[name="product"]').on('change',function(){
		var product = jQuery(this).val();
			jQuery.ajax({
				url : 'get_product_detail',
				type: 'GET',
				dataType: 'json',
				data: { product: product },
				success:function(data)
				{
					console.log(data);
					document.getElementById("unit").value = data.metric.id;
					// show metric name next to label
                	jQuery("#metric_name").text("(" + data.metric.name + ")");
                }
			});
	});
});

const invoiceDate = document.getElementById("invoice_date");
const dueDate = document.getElementById("due_date");

invoiceDate.addEventListener("change", function () {
    dueDate.min = this.value; // block earlier dates
    if (dueDate.value < this.value) {
        dueDate.value = this.value; // auto-correct if invalid
    }
});


function calculateCosts() {
    let qtyInput = document.getElementById("quantity");
    let priceInput = document.getElementById("price_per_unit");
    let taxInput = document.getElementById("tax");
    let discountInput = document.getElementById("discount");

    let netInput = document.getElementById("net_cost");
    let grossInput = document.getElementById("gross_cost");

    let quantity = parseFloat(qtyInput.value);
    let price = parseFloat(priceInput.value);
    let tax = taxInput.value === "" ? 0 : parseFloat(taxInput.value);
    let discount = discountInput.value === "" ? 0 : parseFloat(discountInput.value);

    // Reset error messages
    document.getElementById("quantity_error")?.classList.add("d-none");
    document.getElementById("price_error")?.classList.add("d-none");
    document.getElementById("tax_error")?.classList.add("d-none");

    let valid = true;

    // ✅ Quantity validation
    if (quantity <= 0 || isNaN(quantity)) {
        qtyInput.value = "";
        document.getElementById("quantity_error")?.classList.remove("d-none");
        valid = false;
    }

    // ✅ Price validation
    if (price <= 0 || isNaN(price)) {
        priceInput.value = "";
        document.getElementById("price_error")?.classList.remove("d-none");
        valid = false;
    }

    // ✅ Tax validation
    if (tax < 0 || isNaN(tax)) {
        taxInput.value = "";
        document.getElementById("tax_error")?.classList.remove("d-none");
        tax = 0;
    }

    // ✅ Discount validation (cannot be negative)
    if (discount < 0 || isNaN(discount)) {
        discountInput.value = "";
        discount = 0;
    }

    // Stop if qty or price invalid
    if (!valid) {
        netInput.value = "";
        grossInput.value = "";
        return;
    }

    // ✅ Calculation
    let netCost = quantity * price;
    let grossCost = 0;
    if(tax != 0)
    {
        grossCost = netCost * (1 + (tax / 100));
        //console.log(grossCost);
    }
    else
    {
        grossCost = netCost;
    }

    // Apply discount (absolute amount, not %)
    grossCost = grossCost - discount;
    if (grossCost < 0) grossCost = 0; // prevent negative total

    netInput.value = netCost.toFixed(2);
    grossInput.value = grossCost.toFixed(2);
}

// ✅ Attach listeners
["quantity", "price_per_unit", "tax", "discount"].forEach(id => {
    document.getElementById(id).addEventListener("input", calculateCosts);
});



function purchase_detail(id) {
    $.ajax({
        url: id + "/get_detail",   // match your route
        type: "GET",
        success: function (html) {
            // insert the returned blade partial into modal body
            $("#purchaseDetail .modal-body").html(html);

            // open the modal
            $("#purchaseDetail").modal("show");
        },
        error: function (xhr) {
            alert("Failed to load details");
            console.error(xhr.responseText);
        }
    });
}




