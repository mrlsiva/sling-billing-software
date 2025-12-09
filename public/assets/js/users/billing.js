jQuery(document).ready(function () {
    jQuery('select[name="category"]').on('change', function () {
        var category = jQuery(this).val();
        if (category) {
            jQuery.ajax({
                url: 'get_sub_category',
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

$(document).on('click', '#pagination a', function (e) {
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    loadProducts(page);
});

function loadProducts(page = 1) {
    let sub_category = jQuery('select[name="sub_category"]').val();
    let category = jQuery('select[name="category"]').val();
    let product = jQuery('input[name="product"]').val();
    let filter = jQuery("#filterInput").val();

    console.log(sub_category);
    console.log(category);
    console.log(filter);
    console.log(product);

    jQuery.ajax({
        url: 'get_product',
        type: 'GET',
        dataType: 'json',
        data: {
            page: page,
            category: category,
            sub_category: sub_category,
            product: product,
            filter: filter
        },
        success: function (response) {

            console.log(response);

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
                        <div class="card ${cardClass}" onclick="add_to_cart(this)" data-system_id="${stock.product.id}" style="cursor:pointer;"> 
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
                                        ${stock.quantity == '-1' ? `
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

    $('select[name="category"]').on('change', function () {
        currentPage = 1;
        setTimeout(() => {
            loadProducts(currentPage);
        }, 500);
    });

    $('#checkbox-veg').on('change', function () {
        currentPage = 1;
        loadProducts(currentPage);
    });

    jQuery('input[name="product"]').on('input', function() {
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
    if (!system_id) system_id = element;

    $.ajax({
        url: 'get_product_detail',
        type: 'GET',
        dataType: 'json',
        data: { id: system_id },
        success: function (data) {

            // -----------------------------  
            // IF PRODUCT HAS VARIATIONS  
            // -----------------------------
            if (data.variations && data.variations.length > 0) {

                let modalBody = '';

                data.variations.forEach(function (v) {
                    modalBody += `
                        <tr>
                            <td>${v.size_name ?? '-'}</td>
                            <td>${v.colour_name ?? '-'}</td>
                            <td>₹${v.price}</td>
                            <td>${v.quantity}</td>
                            <td>
                                <button class="btn btn-sm btn-primary"
                                    onclick="addVariationToCart(${data.id}, ${v.id})">
                                    Add
                                </button>
                            </td>
                        </tr>
                    `;
                });

                $("#variationModalBody").html(modalBody);
                $("#variationModal").modal("show");
                return;
            }

            // -----------------------------  
            // NO VARIATIONS → DEFAULT LOGIC  
            // -----------------------------

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
                return;
            }

            let hasImei = data.stock.imei && data.stock.imei.trim() !== "";
            var maxQty = parseInt(data.stock.quantity);

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
                            <p class="text-dark fw-semibold fs-16 mb-0">₹${data.price} <span class="fs-10">(${data.tax.name}%)</span></p>
                        </div>
                        <div class="d-flex align-content-center gap-1">
                            ${hasImei ?
                                `<a href="#!" class="btn btn-soft-info avatar-xs rounded d-flex align-items-center justify-content-center imei-btn" onclick="openImeiModal(${data.id})">
                                    <i class="ri-barcode-line align-middle fs-12"></i>
                                </a>` : ``}

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

function addVariationToCart(productId, variationId) {

    $.ajax({
        url: "get_variation_detail",
        type: "GET",
        data: { id: variationId },
        success: function (v) {

            $("#variationModal").modal("hide");

            // Prevent duplicate variation in cart
            var existing = $('#cart_item').find('[data-variation-id="' + variationId + '"]');
            if (existing.length) {
                var input = existing.find(".qty-input");
                var current = parseInt(input.val());
                var max = parseInt(existing.data("stock-qty"));

                if (current < max) {
                    input.val(current + 1);
                    updateCartSummary();
                } else {
                    alert("Stock limit reached!");
                }
                return;
            }

            $("#cart_item").append(`
                <div class="border border-light mt-3 p-2 rounded" 
                     data-product-id="${productId}"
                     data-variation-id="${variationId}"
                     data-price="${v.price}"
                     data-tax_amount="${v.tax_amount}"
                     data-stock-qty="${v.quantity}">

                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div>
                            <a class="text-dark fs-12 fw-bold">${v.product_name}</a>
                            <p class="fs-10 my-1">${v.size_name} - ${v.colour_name}</p>
                        </div>

                        <div class="ms-lg-auto">
                            <div class="input-step border bg-body-secondary p-1 mt-1 rounded d-inline-flex overflow-visible">
                                <button class="minus bg-light text-dark border-0 rounded fs-20 lh-1 h-100">-</button>
                                <input type="number" class="qty-input border-0 bg-body-secondary text-center" value="1" readonly>
                                <button class="plus bg-light text-dark border-0 rounded fs-20 lh-1 h-100">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between px-1">
                        <p class="text-dark fw-semibold fs-16 mb-0">₹${v.price} <span class="fs-10">(${v.tax}%)</span></p>
                        <a href="#!" class="btn btn-soft-danger avatar-xs rounded remove-item">
                            <i class="ri-delete-bin-5-line fs-12"></i>
                        </a>
                    </div>
                </div>
            `);


            updateCartSummary();
        }
    });

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

    $('#cart_item').find('[data-product-id]').each(function () {
        var qty = parseInt($(this).find('.qty-input').val());
        var price = parseFloat($(this).data('price'));        // total price WITH tax (per item)
        var tax_amount = parseFloat($(this).data('tax_amount')); // tax portion (per item)

        totalItems += qty;
        subTotal += (price - tax_amount) * qty;  // only base price part
        totalTax += tax_amount * qty;           // tax part
    });

    var totalAmount = subTotal + totalTax; // OR just sum(price * qty)

    $('#total_item').text(totalItems + ' (Items)');
    $('#sub_total').text('₹' + subTotal.toFixed(2));
    $('#tax').text('₹' + totalTax.toFixed(2));
    $('#amount').text('₹' + totalAmount.toFixed(2));
    $('#amount_text').text('₹' + totalAmount.toFixed(2));
    $('#amount_text1').text('Payable Amount: ₹' + totalAmount.toFixed(2));

    if (totalItems == 0) {
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

    //saveCartToSession();
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
                    $("#pincode").val(data.pincode).prop('disabled', true);

                    jQuery('select[name="gender"]').empty();
                    $('select[name="gender"]').append('<option value="">' + "Select" + '</option>');
                    if (data.gender_id == 1) {
                        $('select[name="gender"]').append('<option value="1" selected>' + "Female" + '</option>');
                        $('select[name="gender"]').append('<option value="2">' + "Male" + '</option>');
                    }
                    else if (data.gender_id == 2) {
                        $('select[name="gender"]').append('<option value="1">' + "Female" + '</option>');
                        $('select[name="gender"]').append('<option value="2" selected>' + "Male" + '</option>');
                    }
                    $('select[name="gender"]').prop('disabled', true);
                    $("#dob").val(data.dob).prop('disabled', true);
                    $("#gst").val(data.gst).prop('disabled', true);

                }
            });
        }
    });
});

document.getElementById('next_tab_user_info').addEventListener('click', function (e) {
    e.preventDefault();
    let nextTab = document.querySelector('a[href="#messagesTabsJustified"]');
    let tab = new bootstrap.Tab(nextTab);
    tab.show();
});

document.getElementById('next_tab_payment_info').addEventListener('click', function (e) {
    e.preventDefault();
    let nextTab = document.querySelector('a[href="#profileTabsJustified"]');
    let tab = new bootstrap.Tab(nextTab);
    tab.show();
});

document.getElementById('previous_tab_home_info').addEventListener('click', function (e) {
    e.preventDefault();
    let nextTab = document.querySelector('a[href="#homeTabsJustified"]');
    let tab = new bootstrap.Tab(nextTab);
    tab.show();
});

document.getElementById('previous_tab_user_info').addEventListener('click', function (e) {
    e.preventDefault();
    let nextTab = document.querySelector('a[href="#messagesTabsJustified"]');
    let tab = new bootstrap.Tab(nextTab);
    tab.show();
});

jQuery(document).ready(function () {
    jQuery('select[name="payment"]').on('change', function () {
        var payment = jQuery(this).val();
        if (payment) {
            if (payment == 1) {
                $('#cash').removeClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if (payment == 2) {
                $('#cash').addClass('secret');
                $('#card').removeClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if (payment == 3) {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').removeClass('secret');
            }
            else if (payment == 4) {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').removeClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if (payment == 5) {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').removeClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if (payment == 6) {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').removeClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if (payment == 7) {
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

$(document).ready(function () {
    $("#amount_fill").on("change", function () {
        let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

        if ($(this).is(":checked")) {
            $("#cash_amount").val(payable);
        } else {
            $("#cash_amount").val(""); // clear if unchecked
        }
    });
});

$(document).ready(function () {
    $("#card_fill").on("change", function () {
        let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

        if ($(this).is(":checked")) {
            $("#card_amount").val(payable);
        } else {
            $("#card_amount").val(""); // clear if unchecked
        }
    });
});

$(document).ready(function () {
    $("#finance_fill").on("change", function () {
        let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

        if ($(this).is(":checked")) {
            $("#finance_amount").val(payable);
        } else {
            $("#finance_amount").val(""); // clear if unchecked
        }
    });
});

$(document).ready(function () {
    $("#exchange_fill").on("change", function () {
        let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

        if ($(this).is(":checked")) {
            $("#exchange_amount").val(payable);
        } else {
            $("#exchange_amount").val(""); // clear if unchecked
        }
    });
});

$(document).ready(function () {
    $("#cheque_fill").on("change", function () {
        let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

        if ($(this).is(":checked")) {
            $("#cheque_amount").val(payable);
        } else {
            $("#cheque_amount").val(""); // clear if unchecked
        }
    });
});

$(document).ready(function () {
    $("#credit_fill").on("change", function () {
        let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

        if ($(this).is(":checked")) {
            $("#credit_amount").val(payable);
        } else {
            $("#credit_amount").val(""); // clear if unchecked
        }
    });
});

$(document).ready(function () {
    $("#credit_fill").on("change", function () {
        let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

        if ($(this).is(":checked")) {
            $("#credit_amount").val(payable);
        } else {
            $("#credit_amount").val(""); // clear if unchecked
        }
    });
});

$(document).ready(function () {
    $("#upi_fill").on("change", function () {
        let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

        if ($(this).is(":checked")) {
            $("#upi_amount").val(payable);
        } else {
            $("#upi_amount").val(""); // clear if unchecked
        }
    });
});


function appendPaymentRow(method, amount, extraData = {}) {
    let tbody = $("#payment-info-body");
    let rowId = Date.now(); // unique row id for multiple entries

    // prepare display text
    let displayMethod = method;
    if (method === "Card" && extraData.card_name) {
        displayMethod = `Card - ${extraData.card_name}`;
    } else if (method === "Finance" && extraData.finance_type) {
        displayMethod = `Finance - ${extraData.finance_type}`;
    } else if (method === "Cheque" && extraData.cheque_number) {
        displayMethod = `Cheque - ${extraData.cheque_number}`;
    }

    // methods that must stay unique
    const globalUniqueMethods = ["Cash", "Credit", "Exchange", "UPI"];

    if (globalUniqueMethods.includes(method)) {
        // overwrite existing
        let existingRow = tbody.find(`tr[data-method="${method}"]`);
        if (existingRow.length) {
            existingRow.attr("data-extra", JSON.stringify(extraData));
            existingRow.find("td").eq(0).text(displayMethod);
            existingRow.find("td").eq(1).text(`₹${parseFloat(amount).toFixed(2)}`);
        } else {
            tbody.append(`
                <tr data-id="${rowId}" data-method="${method}" data-extra='${JSON.stringify(extraData)}'>
                    <td>${displayMethod}</td>
                    <td>₹${parseFloat(amount).toFixed(2)}</td>
                    <td><button type="button" class="btn btn-sm btn-danger delete-row"><i class="ri-delete-bin-line"></i></button></td>
                </tr>
            `);
        }
    }
    else if (method === "Finance") {
        // unique per finance type (check by ID)
        let existingRow = tbody.find(`tr[data-method="Finance"]`).filter(function () {
            let rowExtra = $(this).attr("data-extra");
            if (!rowExtra) return false;
            try {
                let parsed = JSON.parse(rowExtra);
                return parsed.finance_type === extraData.finance_type; // match by ID
            } catch (e) {
                return false;
            }
        });

        if (existingRow.length) {
            // overwrite existing row for same finance type
            existingRow.attr("data-extra", JSON.stringify(extraData));
            existingRow.find("td").eq(0).text(`Finance - ${extraData.finance_type_name}`);
            existingRow.find("td").eq(1).text(`₹${parseFloat(amount).toFixed(2)}`);
        } else {
            // insert new row for new finance type
            tbody.append(`
                <tr data-id="${rowId}" data-method="Finance" data-extra='${JSON.stringify(extraData)}'>
                    <td>Finance - ${extraData.finance_type_name}</td>
                    <td>₹${parseFloat(amount).toFixed(2)}</td>
                    <td><button type="button" class="btn btn-sm btn-danger delete-row"><i class="ri-delete-bin-line"></i></button></td>
                </tr>
            `);
        }
    }
    else {
        // Card, Cheque → always allow multiple
        tbody.append(`
            <tr data-id="${rowId}" data-method="${method}" data-extra='${JSON.stringify(extraData)}'>
                <td>${displayMethod}</td>
                <td>₹${parseFloat(amount).toFixed(2)}</td>
                <td><button type="button" class="btn btn-sm btn-danger delete-row"><i class="ri-delete-bin-line"></i></button></td>
            </tr>
        `);
    }

    updateTotal();
    //saveCartToSession();
}

$(document).on("click", ".delete-row", function () {
    $(this).closest("tr").remove(); // remove row
    updateTotal(); // recalc total
});

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

    // Validate amount
    if (cash_amount === "" || isNaN(cash_amount) || parseFloat(cash_amount) <= 0) {
        alert('Amount is required');
        return;
    }

    // Convert to number
    cash_amount = parseFloat(cash_amount);

    // Get current values
    let received = parseFloat($("#received_cash").text().replace(/[^\d.-]/g, "")) || 0;
    let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

    // Validate against payable amount
    if ((cash_amount + received) > payable) {
        alert("Received amount should not be greater than payable amount.");
        return;
    }

    // Add payment row
    appendPaymentRow("Cash", cash_amount);

    // Clear input and uncheck "Full Amount"
    $("#cash_amount").val("");
    $("#amount_fill").prop("checked", false);
}


function card_add() {
    let card_number = $("#card_number").val().trim();
    let card_name = $("#card_name").val().trim();
    let card_amount = $("#card_amount").val().trim();

    // Validate card name
    if (card_name === "") {
        alert('Card name cannot be empty');
        return;
    }

    // Validate amount
    if (card_amount === "" || isNaN(card_amount) || parseFloat(card_amount) <= 0) {
        alert('Please enter a valid positive amount');
        return;
    }

    // Convert to number
    card_amount = parseFloat(card_amount);

    // Validate card number only if entered
    if (card_number !== "" && !/^\d{13,19}$/.test(card_number)) {
        alert('Invalid card number (must be 13–19 digits)');
        return;
    }

    // Get current received and payable amounts
    let received = parseFloat($("#received_cash").text().replace(/[^\d.-]/g, "")) || 0;
    let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

    // Check total does not exceed payable
    if ((card_amount + received) > payable) {
        alert("Received amount should not be greater than payable amount.");
        return;
    }

    // Add payment row
    appendPaymentRow("Card", card_amount, {
        card_name: card_name,
        card_number: card_number
    });

    // Clear inputs and uncheck "Full Amount"
    $("#card_number, #card_name, #card_amount").val("");
    $("#card_fill").prop("checked", false);
}



function finance_add() {
    let finance_card = $("#finance_card").val().trim();
    let finance_type = $("#finance_type").val().trim();
    let finance_amount = $("#finance_amount").val().trim();

    // Basic validation
    if (finance_type === "" || finance_amount === "" || isNaN(finance_amount) || parseFloat(finance_amount) <= 0) {
        alert('Invalid Input');
        return;
    }

    // Convert amount to number
    finance_amount = parseFloat(finance_amount);

    // Validate finance card only if entered
    if (finance_card !== "" && !/^\d{8,}$/.test(finance_card)) {
        alert('Invalid Finance Card Number (min 8 digits)');
        return;
    }

    // Get readable finance type name
    let finance_type_text = $("#finance_type option:selected").text();

    // Get current totals
    let received = parseFloat($("#received_cash").text().replace(/[^\d.-]/g, "")) || 0;
    let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

    // Prevent overpayment
    if ((finance_amount + received) > payable) {
        alert("Received amount should not be greater than payable amount.");
        return;
    }

    // Add finance payment row
    appendPaymentRow("Finance", finance_amount, {
        finance_type: finance_type,         // ID for DB
        finance_type_name: finance_type_text, // Display name for UI
        finance_card: finance_card
    });

    // Reset inputs and checkbox
    $("#finance_card, #finance_type, #finance_amount").val("");
    $("#finance_fill").prop("checked", false);
}



function exchange_add() {
    let exchange_amount = $("#exchange_amount").val().trim();

    // Validate amount
    if (exchange_amount === "" || isNaN(exchange_amount) || parseFloat(exchange_amount) <= 0) {
        alert('Invalid Input');
        return;
    }

    exchange_amount = parseFloat(exchange_amount);

    // Check payable vs received
    let received = parseFloat($("#received_cash").text().replace(/[^\d.-]/g, "")) || 0;
    let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

    if ((exchange_amount + received) > payable) {
        alert("Received amount should not be greater than payable amount.");
        return;
    }

    appendPaymentRow("Exchange", exchange_amount);

    $("#exchange_amount").val("");
    $("#exchange_fill").prop("checked", false);
}


function credit_add() {
    let credit_amount = $("#credit_amount").val().trim();

    if (credit_amount === "" || isNaN(credit_amount) || parseFloat(credit_amount) <= 0) {
        alert('Invalid Input');
        return;
    }

    credit_amount = parseFloat(credit_amount);

    let received = parseFloat($("#received_cash").text().replace(/[^\d.-]/g, "")) || 0;
    let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

    if ((credit_amount + received) > payable) {
        alert("Received amount should not be greater than payable amount.");
        return;
    }

    appendPaymentRow("Credit", credit_amount);

    $("#credit_amount").val("");
    $("#credit_fill").prop("checked", false);
}


function cheque_add() {
    let cheque_number = $("#cheque_number").val().trim();
    let cheque_amount = $("#cheque_amount").val().trim();

    if (cheque_number === "" || cheque_amount === "" || isNaN(cheque_amount) || parseFloat(cheque_amount) <= 0) {
        alert('Invalid Input');
        return;
    }

    cheque_amount = parseFloat(cheque_amount);

    if (!/^\d{6,}$/.test(cheque_number)) {
        alert('Invalid Cheque Number (min 6 digits)');
        return;
    }

    let received = parseFloat($("#received_cash").text().replace(/[^\d.-]/g, "")) || 0;
    let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

    if ((cheque_amount + received) > payable) {
        alert("Received amount should not be greater than payable amount.");
        return;
    }

    appendPaymentRow("Cheque", cheque_amount, {
        cheque_number: cheque_number
    });

    $("#cheque_number, #cheque_amount").val("");
    $("#cheque_fill").prop("checked", false);
}


function upi_add() {
    let upi_amount = $("#upi_amount").val().trim();

    if (upi_amount === "" || isNaN(upi_amount) || parseFloat(upi_amount) <= 0) {
        alert('Invalid Input');
        return;
    }

    upi_amount = parseFloat(upi_amount);

    let received = parseFloat($("#received_cash").text().replace(/[^\d.-]/g, "")) || 0;
    let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;

    if ((upi_amount + received) > payable) {
        alert("Received amount should not be greater than payable amount.");
        return;
    }

    appendPaymentRow("UPI", upi_amount);

    $("#upi_amount").val("");
    $("#upi_fill").prop("checked", false);
}



function submit() {

    let phone = $("#phone").val().trim();
    let altPhone = $("#alt_phone").val().trim();
    let name = $("#name").val().trim();
    let address = $("#address").val().trim();
    let pincode = $("#pincode").val().trim();
    let gender = $("#gender").val();
    let dob = $("#dob").val();
    let gst = $("#gst").val();
    let billed_by = $("#billed_by").val();

    // --- Customer validation ---
    if (!/^[0-9]{10}$/.test(phone)) {
        alert("Please enter a valid 10-digit Phone number.");
        return;
    }

    if (altPhone !== "" && !/^[0-9]{10}$/.test(altPhone)) {
        alert("Alternate Phone must be a valid 10-digit number.");
        return;
    }

    if (altPhone !== "" && phone === altPhone) {
        alert("Phone and Alternate Phone cannot be the same.");
        return;
    }

    if (name === "") {
        alert("Name is required.");
        return;
    }

    if (address === "") {
        alert("Address is required.");
        return;
    }

    if (billed_by === "") {
        alert("Billed by is required.");
        return;
    }

    // Get payable and received amounts
    let payable = parseFloat($("#amount_text1").text().replace(/[^\d.-]/g, "")) || 0;
    let received = parseFloat($("#received_cash").text().replace(/[^\d.-]/g, "")) || 0;

    // Compare both
    if (payable !== received) {
        alert("Received amount (" + received.toFixed(2) + ") must be equal to Payable amount (" + payable.toFixed(2) + ").");
        return; // Stop submit
    }

    // collect cart items
    let cartData = [];
    $('#cart_item').find('[data-product-id]').each(function () {
        let qty = parseInt($(this).find('.qty-input').val());
        let price = parseFloat($(this).data('price'));
        let tax_amount = parseFloat($(this).data('tax_amount'));
        let imeis = $(this).attr('data-imei') ? $(this).attr('data-imei').split(',') : [];

        cartData.push({
            product_id: $(this).data('product-id'),
            variation_id: $(this).data('variation-id') || null,  // NEW
            size: $(this).data('size') || null,                  // NEW
            colour: $(this).data('colour') || null,              // NEW
            qty: qty,
            price: price,
            tax_amount: tax_amount,
            imeis: imeis
        });
    });

    // collect payment info
    let paymentData = [];
    $("#payment-info-body tr").each(function () {
        let method = $(this).data("method");
        let amt = parseFloat($(this).find("td").eq(1).text().replace("₹", "")) || 0;
        let extra = $(this).data("extra") ? JSON.parse($(this).attr("data-extra")) : {};

        paymentData.push({
            method: method,
            amount: amt,
            extra: extra
        });
    });

    // collect customer info
    let customer = {
        phone: $("#phone").val().trim(),
        alt_phone: $("#alt_phone").val().trim(),
        name: $("#name").val().trim(),
        address: $("#address").val().trim(),
        pincode: $("#pincode").val().trim(),
        gender: $("#gender").val(),
        dob: $("#dob").val(),
        gst: $("#gst").val(),

    };

    console.log(customer);
    console.log(cartData);
    console.log(paymentData);

    // ajax submit
    $.ajax({
        url: "store",
        method: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            cart: cartData,
            payments: paymentData,
            customer: customer,
            billed_by: billed_by
        },
        success: function (data) {
            console.log("Order stored:", data);

            if(data.status == 'success')
            {
                window.open(data.order_id + '/get_bill', '_blank');
                location.reload();
            }
            else
            {
                alert(data.message);
            }

            //alert('Order Saved');
            // example: redirect to success page
            // window.location.href = "/order/success/" + data.order_id;
        },
        error: function (xhr) {
            console.error("Error saving order:", xhr.responseText);
            alert("Failed to save order. Please try again.");
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    let input = document.getElementById("scanner-input");

    // Keep focus on hidden field only if user isn't typing somewhere else
    function refocus() {
        if (document.activeElement === document.body || document.activeElement === input) {
            input.focus();
        }
    }

    input.addEventListener("change", function() {
        let productId = this.value.trim();

        if (productId) {
            add_to_cart(productId);
        }

        // reset for next scan
        this.value = "";
        refocus();
    });

    // Initial focus
    refocus();
});

// IMEI selection functionality
function openImeiModal(productId) {
    $('#imeiModalProductId').val(productId);
    $('#imeiModalTitle').text('Select IMEI Numbers for Product #' + productId);

    // Get currently selected IMEIs for this product
    const cartItem = $(`[data-product-id="${productId}"]`);
    const currentImeis = cartItem.attr('data-imei') 
        ? cartItem.attr('data-imei').split(',') 
        : [];

    console.log(productId);

    $.ajax({
        url: 'get_imei_product',
        type: 'GET',
        dataType: 'json',
        data: { product: productId },
        success: function (imeiNumbers) {
            console.log(imeiNumbers);
            let imeiHtml = `
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ri-search-line"></i>
                        </span>
                        <input type="text" class="form-control" id="imeiSearchBox" placeholder="Search IMEI numbers..." onkeyup="filterImeiList()">
                    </div>
                </div>
                <div id="imeiCheckboxContainer" class="row">
            `;

            imeiNumbers.forEach((imei, index) => {
                const isChecked = currentImeis.includes(imei) ? 'checked' : '';
                imeiHtml += `
                    <div class="col-md-4 mb-2 imei-item" data-imei="${imei}">
                        <div class="form-check">
                            <input class="form-check-input imei-checkbox" 
                                   type="checkbox" 
                                   name="selectedImei"   <-- ADD THIS
                                   value="${imei}" 
                                   id="imei_${index}" 
                                   ${isChecked}>
                            <label class="form-check-label" for="imei_${index}">
                                ${imei}
                            </label>
                        </div>
                    </div>
                `;

            });

            imeiHtml += '</div>';

            $('#imeiList').html(imeiHtml);
            $('#imeiModal').modal('show');
        }
    });
}
 
function selectImei() {
    const selectedImeis = [];
    $('input[name="selectedImei"]:checked').each(function () {
        selectedImeis.push($(this).val());
    });

    const productId = $('#imeiModalProductId').val();
    const cartItem = $(`[data-product-id="${productId}"]`);
    const qty = parseInt(cartItem.find('.qty-input').val()) || 1;

    if (selectedImeis.length === 0) {
        showToast('error', 'Please select at least one IMEI number');
        return;
    }

    if (selectedImeis.length > qty) {
        showToast('error', `Quantity = ${qty}. You selected ${selectedImeis.length} IMEIs!`);
        return;
    }

    const imeiText = selectedImeis.length > 1 ?
        `${selectedImeis.length} IMEIs selected` :
        `IMEI: ${selectedImeis[0]}`;

    cartItem.find('.selected-imei').text(imeiText);
    cartItem.find('.imei-selected').show();
    cartItem.attr('data-imei', selectedImeis.join(','));

    $('#imeiModal').modal('hide');
    showToast('success', `${selectedImeis.length} IMEI(s) selected successfully`);
}

// Helper functions for IMEI management
function filterImeiList() {
    const searchTerm = $('#imeiSearchBox').val().trim();

    if (searchTerm === '') {
        $('.imei-item').show();
        return;
    }

    // Check if search term contains only numbers
    const isNumericSearch = /^\d+$/.test(searchTerm);

    $('.imei-item').each(function () {
        const imeiNumber = $(this).attr('data-imei');
        let shouldShow = false;

        if (isNumericSearch) {
            // For numeric search, check if IMEI contains the sequence of numbers
            shouldShow = imeiNumber.includes(searchTerm);
        } else {
            // For text search, do case-insensitive matching
            shouldShow = imeiNumber.toLowerCase().includes(searchTerm.toLowerCase());
        }

        if (shouldShow) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function clearAllImei() {
    $('.imei-checkbox').prop('checked', false);
    $('#imeiSearchBox').val('');
    $('.imei-item').show();
}

// Show toast notification
function showToast(type, message) {
    const toastHtml = `
        <div class="toast align-items-center text-white border-0 ${type === 'success' ? 'bg-success' : 'bg-danger'}" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    $('body').append(toastHtml);
    $('.toast').toast('show');

    // Auto remove after 3 seconds
    setTimeout(() => {
        $('.toast').remove();
    }, 3000);
}

// Add this HTML for the IMEI modal at the end of the page
$(document).ready(function () {
    const imeiModalHtml = `
        <!-- IMEI Selection Modal -->
        <div class="modal fade" id="imeiModal" tabindex="-1" aria-labelledby="imeiModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="imeiModalTitle">Select IMEI Numbers</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Available IMEI Numbers:</label>
                            <div id="imeiList">
                                <!-- IMEI checkboxes will be populated here -->
                            </div>
                        </div>
                        <input type="hidden" id="imeiModalProductId" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" onclick="clearAllImei()">Clear All</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="selectImei()">Select IMEIs</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Append the modal to body if it doesn't exist
    if ($('#imeiModal').length === 0) {
        $('body').append(imeiModalHtml);
    }
});









