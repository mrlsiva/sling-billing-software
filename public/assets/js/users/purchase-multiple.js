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
        newRow.find('select, input').each(function () {
            const name = $(this).attr('name');
            if (name) {
                const newName = name.replace('[0]', '[' + rowIndex + ']');
                $(this).attr('name', newName);
            }
        });

        // Append to container
        $('#productsContainer').append(newRow);

        // Bind events for this row
        bindRowEvents(newRow);

        rowIndex++;
        updateRemoveButtons();
    }

    // Function to bind events to a product row
    function bindRowEvents(row) {
        const rowElement = row[0];

        // Category change event
        row.find('.category-select').on('change', function () {
            const category = $(this).val();
            const subCategorySelect = row.find('.sub-category-select');
            const productSelect = row.find('.product-select');

            // Clear dependent selects
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
                    }
                });
            }
        });

        // Calculation events
        row.find('.quantity-input, .price-input, .tax-input, .discount-input').on('input', function () {
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

    // Function to calculate costs for a specific row
    function calculateRowCosts(row) {
        const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
        const price = parseFloat(row.find('.price-input').val()) || 0;
        const tax = parseFloat(row.find('.tax-input').val()) || 0;
        const discount = parseFloat(row.find('.discount-input').val()) || 0;

        if (quantity > 0 && price > 0) {
            let netCost = quantity * price;
            let grossCost = netCost;

            // Apply tax
            if (tax > 0) {
                grossCost = netCost * (1 + (tax / 100));
            }

            // Apply discount
            grossCost = grossCost - discount;
            if (grossCost < 0) grossCost = 0;

            row.find('.net-cost-input').val(netCost.toFixed(2));
            row.find('.gross-cost-input').val(grossCost.toFixed(2));
        } else {
            row.find('.net-cost-input').val('');
            row.find('.gross-cost-input').val('');
        }

        updateTotalSummary();
    }

    // Function to update total summary
    function updateTotalSummary() {
        let totalNet = 0;
        let totalTax = 0;
        let totalDiscount = 0;
        let grandTotal = 0;

        $('.product-row').each(function () {
            const row = $(this);
            const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
            const price = parseFloat(row.find('.price-input').val()) || 0;
            const tax = parseFloat(row.find('.tax-input').val()) || 0;
            const discount = parseFloat(row.find('.discount-input').val()) || 0;
            const grossCost = parseFloat(row.find('.gross-cost-input').val()) || 0;

            if (quantity > 0 && price > 0) {
                const netCost = quantity * price;
                totalNet += netCost;
                totalTax += (netCost * tax / 100);
                totalDiscount += discount;
                grandTotal += grossCost;
            }
        });

        $('#totalNetCost').text(totalNet.toFixed(2));
        $('#totalTax').text(totalTax.toFixed(2));
        $('#totalDiscount').text(totalDiscount.toFixed(2));
        $('#grandTotal').text(grandTotal.toFixed(2));
    }

    // Function to update product numbers
    function updateProductNumbers() {
        $('.product-row').each(function (index) {
            $(this).find('.product-number').text(index + 1);
        });
    }

    // Function to update remove buttons (disable if only one row)
    function updateRemoveButtons() {
        const rowCount = $('.product-row').length;
        $('.remove-product-row').prop('disabled', rowCount <= 1);
    }

    // Form submission validation
    $('#purchaseOrderCreate').on('submit', function (e) {
        let isValid = true;
        let errorMessages = [];

        // Check main form fields
        const vendor = $('#vendor').val();
        const invoiceDate = $('#invoice_date').val();

        if (!vendor || vendor === '') {
            isValid = false;
            errorMessages.push('Vendor is required.');
        }

        if (!invoiceDate || invoiceDate === '') {
            isValid = false;
            errorMessages.push('Invoice Date is required.');
        }

        // Check if at least one product row exists
        if ($('.product-row').length === 0) {
            isValid = false;
            errorMessages.push('Please add at least one product.');
        }

        // Validate only filled product rows
        $('.product-row').each(function (index) {
            const row = $(this);
            const productNumber = index + 1;

            // Check if this row has any content (at least category selected)
            const category = row.find('.category-select').val();

            // If category is selected, then validate this row completely
            if (category && category !== '') {
                const subCategory = row.find('.sub-category-select').val();
                const product = row.find('.product-select').val();
                const quantity = parseFloat(row.find('.quantity-input').val());
                const price = parseFloat(row.find('.price-input').val());

                // Debug logging
                console.log(`Validating Product #${productNumber}:`, {
                    category: category,
                    subCategory: subCategory,
                    product: product,
                    quantity: quantity,
                    price: price
                });

                if (!subCategory || subCategory === '') {
                    isValid = false;
                    errorMessages.push(`Product #${productNumber}: Sub Category is required.`);
                }

                if (!product || product === '') {
                    isValid = false;
                    errorMessages.push(`Product #${productNumber}: Product is required.`);
                }

                if (!quantity || quantity <= 0 || isNaN(quantity)) {
                    isValid = false;
                    errorMessages.push(`Product #${productNumber}: Valid quantity is required.`);
                }

                if (!price || price <= 0 || isNaN(price)) {
                    isValid = false;
                    errorMessages.push(`Product #${productNumber}: Valid price is required.`);
                }
            }
            // If category is not selected, we skip validation for this row
        });

        // Check if at least one product row is actually filled
        let hasValidProducts = false;
        $('.product-row').each(function () {
            const category = $(this).find('.category-select').val();
            if (category && category !== '') {
                hasValidProducts = true;
            }
        });

        if (!hasValidProducts) {
            isValid = false;
            errorMessages.push('Please fill in at least one product.');
        }

        if (!isValid) {
            e.preventDefault();
            alert(errorMessages.join('\n'));
            return false;
        }

        return true;
    });

    // Date validation
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
});