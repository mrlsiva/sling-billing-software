jQuery(document).ready(function () {
    let rowIndex = 0;
    addProductRow(); // default first row

    $('#addProductRow').click(function () {
        addProductRow();
    });

    function addProductRow() {
        const template = $('#productRowTemplate').html();
        const newRow = $(template).clone();

        newRow.attr('data-row-index', rowIndex);
        newRow.find('.product-number').text(rowIndex + 1);

        // Update input names and ids
        newRow.find('select, input, label').each(function () {
            const name = $(this).attr('name');
            const id = $(this).attr('id');
            const forAttr = $(this).attr('for');
            if (name) $(this).attr('name', name.replace('[0]', '[' + rowIndex + ']'));
            if (id) $(this).attr('id', id.replace('[0]', '[' + rowIndex + ']'));
            if (forAttr) $(this).attr('for', forAttr.replace('[0]', '[' + rowIndex + ']'));
        });

        $('#productsContainer').append(newRow);

        // Default values
        newRow.find('.quantity-input').val(1);
        newRow.find('.tax-input').val('0');
        newRow.find('.net-cost-input').val('');
        newRow.find('.gross-cost-input').val('');

        bindRowEvents(newRow);
        calculateRowCosts(newRow);
        rowIndex++;
        updateRemoveButtons();
    }

    function bindRowEvents(row) {
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

        row.find('.quantity-input, .price-input, .tax-input, .discount-input').on('input', function () {
            calculateRowCosts(row);
        });

        row.find('.remove-product-row').on('click', function () {
            row.remove();
            updateProductNumbers();
            updateRemoveButtons();
            updateTotalSummary();
        });
    }

    function calculateRowCosts(row) {
        const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
        const price = parseFloat(row.find('.price-input').val()) || 0;
        const tax = parseFloat(row.find('.tax-input').val()) || 0;
        const discount = parseFloat(row.find('.discount-input').val()) || 0;
        let netCost = quantity * price;
        let grossCost = netCost * (1 + tax / 100) - discount;
        if (grossCost < 0) grossCost = 0;
        row.find('.net-cost-input').val(netCost.toFixed(2));
        row.find('.gross-cost-input').val(grossCost.toFixed(2));
        updateTotalSummary();
    }

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

    function updateProductNumbers() {
        $('.product-row').each(function (i) {
            $(this).find('.product-number').text(i + 1);
        });
    }

    function updateRemoveButtons() {
        const count = $('.product-row').length;
        $('.remove-product-row').prop('disabled', count <= 1);
    }

    // Ensure due date >= invoice date
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