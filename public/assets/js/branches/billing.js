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
					console.log(data);

					jQuery('select[name="sub_category"]').empty();
					$('select[name="sub_category"]').append('<option value="">'+ "Select" +'</option>');
					jQuery.each(data, function(key,value){
						console.log(value.name)
						$('select[name="sub_category"]').append('<option value="'+ value.id +'">'+ value.name +'</option>');
					});					
					
				}
			});
		}
	});
});

$(document).on('click', '#pagination a', function (e) {
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    loadProducts(page);
});

function loadProducts(page = 1) {
    let sub_category = jQuery('select[name="sub_category"]').val();
    let category = jQuery("#category").val();
    let filter = jQuery("#filterInput").val();

    jQuery.ajax({
        url: 'get_product',
        type: 'GET',
        dataType: 'json',
        data: {
            page: page,
            category: category,
            sub_category: sub_category,
            filter: filter
        },
        success: function (response) {

            let html = '<div class="row">';
            response.data.forEach(function (stock) {
                let cardClass = '';
                let badgeClass = '';

                if (stock.quantity === 0) {
                    cardClass = 'bg-soft-danger';
                    badgeClass = 'bg-danger';
                } else if (stock.quantity <= 5) {
                    cardClass = 'bg-soft-warning';
                    badgeClass = 'bg-warning';
                } else {
                    badgeClass = 'bg-soft-success';
                }

                html += `
                    <div class="col-md-4">
                        <div class="card ${cardClass}">
                            <div class="card-body p-2">
                                <div class="d-flex flex-column">
                                    <a href="#!" class="w-100 text-dark fs-12 fw-semibold text-truncate">
                                        ${stock.product.name} 
                                    </a>
                                    <a class="fs-10 text-dark fw-normal mb-0 w-100 text-truncate">
                                        ${stock.product.category.name} - ${stock.product.sub_category.name}   
                                    </a>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <div>
                                        <p class="text-dark fw-semibold fs-12 mb-0">Rs ${stock.product.price}</p>
                                    </div>
                                    <div class="d-flex align-content-center gap-1">
                                        <p class="mb-0 fs-12">${stock.quantity}</p>
                                        <p class="badge ${badgeClass} fs-10 mb-1 text-dark py-1 px-2">Qty</p>
                                        ${stock.quantity > 0 ? `
                                            <button type="button" 
                                                class="bg-light text-dark border-0 rounded fs-20 lh-1 h-100"
                                                onclick="add_to_cart(this)"
                                                data-system_id="${stock.product_id}">
                                                +
                                            </button>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += `</div>`;

            $("#productContainer").html(html);
            $("#pagination").html(response.pagination);
        }
    });
}

$(document).ready(function () {
    let currentPage = 1;

    $('select[name="sub_category"]').on('change', function () {
        currentPage = 1;
        loadProducts(currentPage);
    });

    $('#category').on('change', function () {
        currentPage = 1;
        loadProducts(currentPage);
    });

    $('#checkbox-veg').on('change', function () {
        currentPage = 1;
        loadProducts(currentPage);
    });

    $(document).on('click', '#pagination a', function (e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        currentPage = page;
        loadProducts(currentPage);
    });

    loadProducts(currentPage);
});


// Add product to cart or increase quantity
function add_to_cart(element) {
    var system_id = $(element).data("system_id");

    // Check if already in cart
    var $existingItem = $('#cart_item').find('[data-product-id="' + system_id + '"]');

    if ($existingItem.length) {
        var $qtyInput = $existingItem.find('.qty-input');
        var currentQty = parseInt($qtyInput.val());
        var maxQty = parseInt($existingItem.data('stock-qty'));

        if (currentQty < maxQty) {
            $qtyInput.val(currentQty + 1);
            updateCartSummary();
        } else {
            alert("Cannot add more. Stock limit reached (" + maxQty + ").");
        }
    } else {

        // Fetch product details
        $.ajax({
            url: 'get_product_detail',
            type: 'GET',
            dataType: 'json',
            data: { id: system_id },
            success: function(data) {
                var maxQty = parseInt(data.stock.quantity);

                if (maxQty <= 0) {
                    alert("This product is out of stock.");
                    return;
                }

                $("#cart_item").append(`
                    <div class="border border-light mt-3 p-2 rounded" 
                         data-product-id="${data.id}" 
                         data-price="${data.price}"
                         data-tax_amount="${data.tax_amount}" 
                         data-tax-id="${data.tax_id}" 
                         data-stock-qty="${maxQty}">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div>
                                <a class="text-dark fs-12 fw-bold">${data.name}</a>
                                <p class="fs-10 my-1">${data.category.name} - ${data.sub_category.name}</p>
                            </div>
                            <div class="ms-lg-auto">
                                <div class="input-step border bg-body-secondary p-1 mt-1 rounded d-inline-flex overflow-visible">
                                    <button type="button" class="minus bg-light text-dark border-0 rounded fs-20 lh-1 h-100">-</button>
                                    <input type="number" class="qty-input text-dark text-center border-0 bg-body-secondary rounded h-100" value="1" min="0" max="${maxQty}" readonly>
                                    <button type="button" class="plus bg-light text-dark border-0 rounded fs-20 lh-1 h-100" data-system_id="${data.id}">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between px-1">
                            <div>
                                <p class="text-dark fw-semibold fs-16 mb-0">₹${data.price}</p>
                            </div>
                            <div class="d-flex align-content-center gap-1">
                                <a href="#!" class="btn btn-soft-danger avatar-xs rounded d-flex align-items-center justify-content-center remove-item">
                                    <i class="ri-delete-bin-5-line align-middle fs-12"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                `);

                updateCartSummary();
            }
        });
    }
}

// Remove product from cart
function remove_from_cart(element) {
    $(element).closest('[data-product-id]').remove();
    updateCartSummary();
}

// Update totals, tax, and amount
function updateCartSummary() {
    var totalItems = 0;
    var subTotal = 0;
    var totalTax = 0;

    $('#cart_item').find('[data-product-id]').each(function() {
        var qty = parseInt($(this).find('.qty-input').val());
        var price = parseFloat($(this).data('price'));        // total price WITH tax (per item)
        var tax_amount = parseFloat($(this).data('tax_amount')); // tax portion (per item)

        totalItems += qty;
        subTotal += (price - tax_amount) * qty;  // only base price part
        totalTax  += tax_amount * qty;           // tax part
    });

    var totalAmount = subTotal + totalTax; // OR just sum(price * qty)

    $('#total_item').text(totalItems + ' (Items)');
    $('#sub_total').text('₹' + subTotal.toFixed(2));
    $('#tax').text('₹' + totalTax.toFixed(2));
    $('#amount').text('₹' + totalAmount.toFixed(2));
    $('#amount_text').text('₹' + totalAmount.toFixed(2));
    $('#amount_text1').text('Payable Amount: ₹' + totalAmount.toFixed(2));

    if(totalItems == 0) {
        $('#order_detail').addClass('secret');
        $('#empty_order_detail').removeClass('secret');
        $('#payment_tab').removeAttr('href data-bs-toggle aria-expanded').addClass('disabled');
    } else {
        $('#order_detail').removeClass('secret');
        $('#empty_order_detail').addClass('secret');
        $('#payment_tab')
            .attr({
                href: '#profileTabsJustified',
                'data-bs-toggle': 'tab',
                'aria-expanded': 'true'
            }).removeClass('disabled');
    }
}


// Delegated event handling
$(document).on('click', '.plus', function () {
    var $item = $(this).closest('[data-product-id]');
    var $qtyInput = $item.find('.qty-input');
    var currentQty = parseInt($qtyInput.val());
    var maxQty = parseInt($item.data('stock-qty'));

    if (currentQty < maxQty) {
        $qtyInput.val(currentQty + 1);
        updateCartSummary();
    } else {
        alert("Cannot add more. Stock limit reached (" + maxQty + ").");
    }
});

$(document).on('click', '.minus', function () {
    var $item = $(this).closest('[data-product-id]');
    var $qtyInput = $item.find('.qty-input');
    var qty = parseInt($qtyInput.val());

    if (qty > 1) {
        $qtyInput.val(qty - 1);
    } else {
        $item.remove();
    }

    updateCartSummary();
});

$(document).on('click', '.remove-item', function () {
    remove_from_cart(this);
});

// Clear cart on click
$(document).on('click', '#clear_cart', function (e) {
    e.preventDefault();
    $('#cart_item').empty();
    updateCartSummary();
});


$(document).ready(function () {
    $("#phone").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: 'suggest-customer-phone',
                type: 'get',
                data: { phone: request.term },
                success: function (data) {
                	console.log(data);
                    response(data.phones); // expects an array
                }
            });
        },
        minLength: 1 // start suggesting after 1 digit
    });
});


$(document).ready(function () {
    $("#phone").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: 'suggest-customer-phone',
                type: 'get',
                data: { phone: request.term },
                success: function (data) {
                    response(data.phones); // Send phone array to autocomplete
                }
            });
        },
        minLength: 1,
        select: function (event, ui) {
            // When user selects a phone number from suggestions
            var phone = ui.item.value;

            $.ajax({
                url: 'get_customer_detail',
                type: 'GET',
                dataType: 'json',
                data: { phone: phone },
                success: function (data) {

                    console.log(data);

                    $("#customer").val(data.id);
                    $("#alt_phone").val(data.alt_phone).prop('disabled', true);
                    $("#name").val(data.name).prop('disabled', true);
                    $("#address").val(data.address).prop('disabled', true);
                    
                    jQuery('select[name="gender"]').empty();
                    $('select[name="gender"]').append('<option value="">'+ "Select" +'</option>');
                    if(data.gender_id == 1)
                    {
                        $('select[name="gender"]').append('<option value="1" selected>'+ "Female" +'</option>');
                        $('select[name="gender"]').append('<option value="2">'+ "Male" +'</option>');
                    }
                    else if(data.gender_id == 2)
                    {
                        $('select[name="gender"]').append('<option value="1">'+ "Female" +'</option>');
                        $('select[name="gender"]').append('<option value="2" selected>'+ "Male" +'</option>');
                    }
                    $('select[name="gender"]').prop('disabled', true);
                    $("#dob").val(data.dob).prop('disabled', true);

                }
            });
        }
    });
});

$(document).ready(function () {
    // Handle form submission via AJAX
    $('#customer_add').on('submit', function (e) {
        e.preventDefault(); // Prevent normal form submit

        $.ajax({
            url: 'customer_store', // Change to your actual route
            method: 'POST',
            data: $(this).serialize(),
            success: function (response) {
               alert('Customer added successfully');
                $('#customerAdd').modal('hide');
                $('#customer_add')[0].reset();
            },
            error: function (xhr) {
                alert('Something went wrong!');
            }
        });
    });

    // If you want to trigger submit from JS somewhere else:
    function submitCustomerForm() {
        document.getElementById('customer_add').requestSubmit(); // With validation
    }

    $("#phone").on("input", function () {
        if ($(this).val().trim() === "") {
            $("#customer").val("");
            $("#alt_phone").val("").prop('disabled', false);
            $("#name").val("").prop('disabled', false);
            $("#address").val("").prop('disabled', false);
            $('select[name="gender"]').empty().append('<option value="">Select</option>').append('<option value="1">'+ "Female" +'</option>').append('<option value="2">'+ "Male" +'</option>').prop('disabled', false);
            $("#dob").val("").prop('disabled', false);
        }
    });
});

document.getElementById('next_tab_user_info').addEventListener('click', function(e) {
    e.preventDefault();
    let nextTab = document.querySelector('a[href="#messagesTabsJustified"]');
    let tab = new bootstrap.Tab(nextTab);
    tab.show();
});

document.getElementById('next_tab_payment_info').addEventListener('click', function(e) {
    e.preventDefault();
    let nextTab = document.querySelector('a[href="#profileTabsJustified"]');
    let tab = new bootstrap.Tab(nextTab);
    tab.show();
});

document.getElementById('previous_tab_home_info').addEventListener('click', function(e) {
    e.preventDefault();
    let nextTab = document.querySelector('a[href="#homeTabsJustified"]');
    let tab = new bootstrap.Tab(nextTab);
    tab.show();
});

document.getElementById('previous_tab_user_info').addEventListener('click', function(e) {
    e.preventDefault();
    let nextTab = document.querySelector('a[href="#messagesTabsJustified"]');
    let tab = new bootstrap.Tab(nextTab);
    tab.show();
});

jQuery(document).ready(function ()
{
    jQuery('select[name="payment"]').on('change',function(){
        var payment = jQuery(this).val();
        if(payment)
        {
            if(payment == 1)
            {
                $('#cash').removeClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if(payment == 2)
            {
                $('#cash').addClass('secret');
                $('#card').removeClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if(payment == 3)
            {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').removeClass('secret');
            }
            else if(payment == 4)
            {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').removeClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if(payment == 5)
            {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').removeClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if(payment == 6)
            {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').removeClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if(payment == 7)
            {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').removeClass('secret');
                $('#upi').addClass('secret');
            }
            
        }
    });
});

function appendPaymentRow(method, amount) {
    let tbody = $("#payment-info-body");
    let existingRow = tbody.find(`tr[data-method="${method}"]`);

    if (existingRow.length) {
        // Replace with latest amount
        existingRow.find("td").eq(1).text(`₹${parseFloat(amount).toFixed(2)}`);
    } else {
        // Add as new entry
        tbody.append(`
            <tr data-method="${method}">
                <td>${method}</td>
                <td>₹${parseFloat(amount).toFixed(2)}</td>
            </tr>
        `);
    }

    updateTotal();
}

function updateTotal() {
    let total = 0;
    $("#payment-info-body tr").each(function () {
        let amt = parseFloat($(this).find("td").eq(1).text().replace("₹", "")) || 0;
        total += amt;
    });
    $("#received_cash").text(`Total Cash: ₹${total.toFixed(2)}`);
}


// The rest stays the same
function cash_add() {
    let cash_amount = $("#cash_amount").val().trim();
    if (cash_amount === "" || isNaN(cash_amount) || parseFloat(cash_amount) <= 0) {
        alert('Amount is required');
        return;
    }
    appendPaymentRow("Cash", cash_amount);
    $("#cash_amount").val("");
}

function card_add() {
    let card_number = $("#card_number").val().trim();
    let card_name = $("#card_name").val().trim();
    let card_amount = $("#card_amount").val().trim();

    if (card_number === "" || card_name === "" || card_amount === "" || isNaN(card_amount) || parseFloat(card_amount) <= 0) {
        alert('Invalid Input');
        return;
    }
    if (!/^\d{8,}$/.test(card_number)) {
        alert('Invalid Card Number (min 8 digits)');
        return;
    }
    appendPaymentRow(`Card - ${card_name}`, card_amount);
    $("#card_number, #card_name, #card_amount").val("");
}

function finance_add() {
    let finance_card = $("#finance_card").val().trim();
    let finance_type = $("#finance_type").val().trim();
    let finance_amount = $("#finance_amount").val().trim();

    if (finance_card === "" || finance_type === "" || finance_amount === "" || isNaN(finance_amount) || parseFloat(finance_amount) <= 0) {
        alert('Invalid Input');
        return;
    }
    if (!/^\d{8,}$/.test(finance_card)) {
        alert('Invalid Finance Card Number (min 8 digits)');
        return;
    }
    appendPaymentRow(`Finance - ${$("#finance_type option:selected").text()}`, finance_amount);
    $("#finance_card, #finance_type, #finance_amount").val("");
}

function exchange_add() {
    let exchange_amount = $("#exchange_amount").val().trim();
    if (exchange_amount === "" || isNaN(exchange_amount) || parseFloat(exchange_amount) <= 0) {
        alert('Invalid Input');
        return;
    }
    appendPaymentRow("Exchange", exchange_amount);
    $("#exchange_amount").val("");
}

function credit_add() {
    let credit_amount = $("#credit_amount").val().trim();
    if (credit_amount === "" || isNaN(credit_amount) || parseFloat(credit_amount) <= 0) {
        alert('Invalid Input');
        return;
    }
    appendPaymentRow("Credit", credit_amount);
    $("#credit_amount").val("");
}

function cheque_add() {
    let cheque_number = $("#cheque_number").val().trim();
    let cheque_amount = $("#cheque_amount").val().trim();

    if (cheque_number === "" || cheque_amount === "" || isNaN(cheque_amount) || parseFloat(cheque_amount) <= 0) {
        alert('Invalid Input');
        return;
    }
    if (!/^\d{6,}$/.test(cheque_number)) {
        alert('Invalid Cheque Number (min 6 digits)');
        return;
    }
    appendPaymentRow("Cheque", cheque_amount);
    $("#cheque_number, #cheque_amount").val("");
}

function upi_add() {
    let upi_amount = $("#upi_amount").val().trim();
    if (upi_amount === "" || isNaN(upi_amount) || parseFloat(upi_amount) <= 0) {
        alert('Invalid Input');
        return;
    }
    appendPaymentRow("UPI", upi_amount);
    $("#upi_amount").val("");
}









