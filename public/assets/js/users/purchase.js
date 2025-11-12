jQuery(document).ready(function () {
    let rowIndex = 0;

    // Initialize with one product row
    addProductRow();

    // Add Product Row Button Click
    $('#addProductRow').click(function () {
        addProductRow();
    });

    // Function to add a new product row
    function addProductRow() {
        const template = $('#productRowTemplate').html();
        const newRow = $(template).clone();

        // Update row index and form names
        newRow.attr('data-row-index', rowIndex);
        newRow.find('.product-number').text(rowIndex + 1);

        // Update all form field names with current index
        newRow.find('select, input, label').each(function () {
            const name = $(this).attr('name');
            const id = $(this).attr('id');
            const forAttr = $(this).attr('for');

            if (name) $(this).attr('name', name.replace('[0]', '[' + rowIndex + ']'));
            if (id) $(this).attr('id', id.replace('[0]', '[' + rowIndex + ']'));
            if (forAttr) $(this).attr('for', forAttr.replace('[0]', '[' + rowIndex + ']'));
        });

        // Default values
        newRow.find('.quantity-input').val(1);
        newRow.find('.tax-input').val('0');
        newRow.find('.net-cost-input').val('');
        newRow.find('.gross-cost-input').val('');

        // Append to container
        $('#productsContainer').append(newRow);

        // Bind events for this row
        bindRowEvents(newRow);
        calculateRowCosts(newRow);

        rowIndex++;
        updateRemoveButtons();
    }

    // Function to bind events to a product row
    function bindRowEvents(row) {
        // Category change event
        row.find('.category-select').on('change', function () {
            const category = $(this).val();
            const subCategorySelect = row.find('.sub-category-select');
            const productSelect = row.find('.product-select');

            subCategorySelect.empty().append('<option value=""> Select </option>');
            productSelect.empty().append('<option value=""> Select </option>');

            if (category) {
                $.ajax({
                    url: '../../products/get_sub_category',
                    type: 'GET',
                    dataType: 'json',
                    data: { id: category },
                    success: function (data) {
                        $.each(data, function (key, value) {
                            subCategorySelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                });
            }
        });

        // Sub Category change event
        row.find('.sub-category-select').on('change', function () {
            const subCategory = $(this).val();
            const category = row.find('.category-select').val();
            const productSelect = row.find('.product-select');

            productSelect.empty().append('<option value=""> Select </option>');

            if (category && subCategory) {
                $.ajax({
                    url: 'get_product',
                    type: 'GET',
                    dataType: 'json',
                    data: { category: category, sub_category: subCategory },
                    success: function (data) {
                        $.each(data, function (key, value) {
                            productSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                });
            }
        });

        // Product change event
        row.find('.product-select').on('change', function () {
            const product = $(this).val();
            const unitInput = row.find('.unit-input');
            const metricDisplay = row.find('.metric-display');

            if (product) {
                $.ajax({
                    url: 'get_product_detail',
                    type: 'GET',
                    dataType: 'json',
                    data: { product: product },
                    success: function (data) {
                        unitInput.val(data.metric.id);
                        metricDisplay.text("(" + data.metric.name + ")");

                        // Auto-fill price and tax if available
                        if (data.price) {
                            row.find('.price-input').val(parseFloat(data.price).toFixed(2));
                        }
                        row.find('.quantity-input').val(1);
                        const firstNonZeroTax = row.find('.tax-input option').not('[value="0"]').first().val() || "0";
                        row.find('.tax-input').val(firstNonZeroTax);
                        calculateRowCosts(row);
                    }
                });
            }
        });

        // IMEI Checkbox event
        row.find('.enable-imei-checkbox').on('change', function () {
            const isChecked = $(this).is(':checked');
            const imeiContainer = row.find('.imei-container');
            if (isChecked) {
                imeiContainer.show();
                generateIMEIInputs(row);
            } else {
                imeiContainer.hide().empty();
            }
        });

        // Quantity, price, tax, discount change → recalc
        row.find('.quantity-input, .price-input, .tax-input, .discount-input').on('input', function () {
            const quantity = parseInt(row.find('.quantity-input').val()) || 0;

            // Restrict quantity to 60
            if (quantity > 60) {
                alert('Please enter quantity below 60 at a time. For larger quantities, please create multiple purchase orders.');
                row.find('.quantity-input').val('');
                row.find('.net-cost-input').val('');
                row.find('.gross-cost-input').val('');
                row.find('.imei-container').empty();
                updateTotalSummary();
                return;
            }

            // Regenerate IMEI inputs if checkbox is checked
            const imeiCheckbox = row.find('.enable-imei-checkbox');
            if (imeiCheckbox.is(':checked')) {
                generateIMEIInputs(row);
            }

            calculateRowCosts(row);
        });

        // Remove row event
        row.find('.remove-product-row').on('click', function () {
            row.remove();
            updateProductNumbers();
            updateRemoveButtons();
            updateTotalSummary();
        });
    }

    // Function to generate IMEI input fields based on quantity
    function generateIMEIInputs(row) {
        const quantity = parseInt(row.find('.quantity-input').val()) || 0;
        const imeiContainer = row.find('.imei-container');
        const rowIndex = row.attr('data-row-index');

        imeiContainer.empty();

        if (quantity > 0 && quantity <= 60) {
            for (let i = 1; i <= quantity; i++) {
                const imeiInput = $(`
                    <div class="col-md-3">
                        <div class="input-group mb-2">
                            <span class="input-group-text">#${i}</span>
                            <input type="text" 
                                   name="products[${rowIndex}][imei][]" 
                                   class="form-control imei-input" 
                                   placeholder="IMEI/Serial #${i}"
                                   pattern="[0-9A-Za-z-]+"
                                   title="Enter valid IMEI/Serial number">
                        </div>
                    </div>
                `);
                imeiContainer.append(imeiInput);
            }
        }
    }

    // Function to calculate costs for a specific row
    function calculateRowCosts(row) {
        const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
        const price = parseFloat(row.find('.price-input').val()) || 0;
        const tax = parseFloat(row.find('.tax-input').val()) || 0;
        const discount = parseFloat(row.find('.discount-input').val()) || 0;

        let netCost = quantity * price;
        let grossCost = netCost * (1 + tax / 100) - discount;
        if (grossCost < 0) grossCost = 0;

        if (quantity && price) {
            row.find('.net-cost-input').val(netCost.toFixed(2));
            row.find('.gross-cost-input').val(grossCost.toFixed(2));
        } else {
            row.find('.net-cost-input, .gross-cost-input').val('');
        }

        updateTotalSummary();
    }

    // Function to update total summary
    function updateTotalSummary() {
        let totalNet = 0, totalTax = 0, totalDiscount = 0, grandTotal = 0;

        $('.product-row').each(function () {
            const row = $(this);
            const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
            const price = parseFloat(row.find('.price-input').val()) || 0;
            const tax = parseFloat(row.find('.tax-input').val()) || 0;
            const discount = parseFloat(row.find('.discount-input').val()) || 0;
            const netCost = quantity * price;

            totalNet += netCost;
            totalTax += (netCost * tax / 100);
            totalDiscount += discount;
            grandTotal += (netCost * (1 + tax / 100) - discount);
        });

        $('#totalNetCost').text(totalNet.toFixed(2));
        $('#totalTax').text(totalTax.toFixed(2));
        $('#totalDiscount').text(totalDiscount.toFixed(2));
        $('#grandTotal').text(grandTotal.toFixed(2));
    }

    // Function to update product numbers
    function updateProductNumbers() {
        $('.product-row').each(function (i) {
            $(this).find('.product-number').text(i + 1);
        });
    }

    // Function to update remove buttons
    function updateRemoveButtons() {
        const count = $('.product-row').length;
        $('.remove-product-row').prop('disabled', count <= 1);
    }

    // Form date validation
    const invoiceDate = document.getElementById("invoice_date");
    const dueDate = document.getElementById("due_date");
    if (invoiceDate && dueDate) {
        invoiceDate.addEventListener("change", function () {
            dueDate.min = this.value;
            if (dueDate.value < this.value) {
                dueDate.value = this.value;
            }
        });
    }

    // Form validation before submit
    $('#purchaseOrderCreate').on('submit', function (e) {
        let isValid = true;
        let errorMessages = [];

        const vendor = $('#vendor').val();
        const invoiceDateVal = $('#invoice_date').val();

        if (!vendor) errorMessages.push('Vendor is required.');
        if (!invoiceDateVal) errorMessages.push('Invoice Date is required.');

        $('.product-row').each(function (index) {
            const row = $(this);
            const category = row.find('.category-select').val();
            const subCategory = row.find('.sub-category-select').val();
            const product = row.find('.product-select').val();
            const quantity = parseFloat(row.find('.quantity-input').val());
            const price = parseFloat(row.find('.price-input').val());

            if (category) {
                if (!subCategory) errorMessages.push(`Product #${index + 1}: Sub Category is required.`);
                if (!product) errorMessages.push(`Product #${index + 1}: Product is required.`);
                if (!quantity || quantity <= 0) errorMessages.push(`Product #${index + 1}: Valid quantity is required.`);
                if (!price || price <= 0) errorMessages.push(`Product #${index + 1}: Valid price is required.`);
            }
        });

        if (errorMessages.length > 0) {
            e.preventDefault();
            alert(errorMessages.join('\n'));
        }
    });
});

// Load purchase details in modal
function purchase_detail(id) {
    $.ajax({
        url: id + "/get_detail",
        type: "GET",
        success: function (html) {
            $("#purchaseDetail .modal-body").html(html);
            $("#purchaseDetail").modal("show");
        },
        error: function (xhr) {
            alert("Failed to load details");
            console.error(xhr.responseText);
        }
    });
}
