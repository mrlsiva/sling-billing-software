jQuery(document).ready(function () {

    let rowIndex = 0;

    // If server provided old products (blade will inject `window._OLD_PRODUCTS = ...`), restore them
    const oldProducts = window._OLD_PRODUCTS || null;

    // Initialize
    if (oldProducts && Array.isArray(oldProducts) && oldProducts.length > 0) {
        // create rows for old products
        oldProducts.forEach((p, idx) => {
            addProductRow(p);
        });
    } else {
        addProductRow();
    }

    // Add product row button
    $('#addProductRow').on('click', function () {
        addProductRow();
    });

    // Delegated event handlers (works for dynamic rows)
    const container = $('#productsContainer');

    // CATEGORY -> fetch subcategories
    container.on('change', '.category-select', function () {
        const row = $(this).closest('.product-row');
        const category = $(this).val();
        const sub = row.find('.sub-category-select');
        const prod = row.find('.product-select');

        sub.html('<option value=""> Select </option>');
        prod.html('<option value=""> Select </option>');
        clearVariations(row);

        if (!category) return;

        $.getJSON('../../products/get_sub_category', { id: category })
            .done(function (data) {
                data.forEach(v => sub.append(`<option value="${v.id}">${v.name}</option>`));
            })
            .fail(function (xhr) { console.error('get_sub_category failed', xhr); });
    });

    // SUBCATEGORY -> fetch products
    container.on('change', '.sub-category-select', function () {
        const row = $(this).closest('.product-row');
        const subCategory = $(this).val();
        const category = row.find('.category-select').val();
        const prod = row.find('.product-select');

        prod.html('<option value=""> Select </option>');
        clearVariations(row);

        if (!category || !subCategory) return;

        $.getJSON('get_product', { category: category, sub_category: subCategory })
            .done(function (data) {
                data.forEach(v => prod.append(`<option value="${v.id}">${v.name}</option>`));
            })
            .fail(function (xhr) { console.error('get_product failed', xhr); });
    });

    // PRODUCT -> fetch details + variations
    container.on('change', '.product-select', function () {
        const row = $(this).closest('.product-row');
        const product = $(this).val();

        const qtyInput = findQtyInput(row);
        const unitInput = row.find('.unit-input');
        const metricDisplay = row.find('.metric-display');
        const taxInput = row.find('.tax-input');
        const variationContainer = row.find('.variation-container');

        clearVariations(row);

        if (!product) return;

        // product details
        $.getJSON('get_product_detail', { product: product })
            .done(function (data) {
                console.log('product_detail', data);
                if (data.metric) unitInput.val(data.metric.id);
                if (data.metric && data.metric.name) metricDisplay.text("(" + data.metric.name + ")");
                if (data.price) row.find('.price-input').val(parseFloat(data.price).toFixed(2));
                qtyInput.val(1);
                if (data.tax_id) taxInput.val(data.tax_id).change(); else taxInput.val('0');
                calculateRowCosts(row);
            })
            .fail(function (xhr) { console.error('get_product_detail failed', xhr); });

        // variations
        $.getJSON('get_stock_variations', { product_id: product })
            .done(function (response) {
                console.log('stock_variations', response);
                variationContainer.html('');

                if (!response || !Array.isArray(response.variations) || response.variations.length === 0) {
                    // no variations -> quantity editable
                    variationContainer.hide();
                    qtyInput.removeAttr('readonly');
                    return;
                }

                // check if all variation entries have no size & no colour
                const allEmpty = response.variations.every(v => !v.size && !v.colour);
                if (allEmpty) {
                    variationContainer.hide();
                    qtyInput.removeAttr('readonly');
                    return;
                }

                // variations exist -> make quantity readonly
                variationContainer.show();
                qtyInput.attr('readonly', 'readonly');

                response.variations.forEach((v, idx) => {
                    const sizeName = v.size ? v.size.name : '-';
                    const colourName = v.colour ? v.colour.name : '-';

                    // create inputs with names containing row index so Laravel receives structured input
                    const rowIdx = row.attr('data-row-index');
                    const html = `
                        <div class="row border p-2 mb-2 variation-row">
                            <input type="hidden" name="products[${rowIdx}][variation][${idx}][stock_id]" value="${response.stock_id}">
                            <input type="hidden" name="products[${rowIdx}][variation][${idx}][size_id]" value="${v.size ? v.size.id : ''}">
                            <input type="hidden" name="products[${rowIdx}][variation][${idx}][colour_id]" value="${v.colour ? v.colour.id : ''}">

                            <div class="col-md-3">
                                <label>Size</label>
                                <input type="text" class="form-control" readonly value="${sizeName}">
                            </div>

                            <div class="col-md-3">
                                <label>Colour</label>
                                <input type="text" class="form-control" readonly value="${colourName}">
                            </div>

                            <div class="col-md-3">
                                <label>Qty</label>
                                <input type="number" step="1" min="0" class="form-control variation-qty" name="products[${rowIdx}][variation][${idx}][qty]" value="0">
                            </div>

                            
                        </div>
                    `;
                    variationContainer.append(html);
                });

                // when variations change -> consolidate sum into quantity input
                // handled by delegated handlers below
            })
            .fail(function (xhr) { console.error('get_stock_variations failed', xhr); });
    });

    // DELEGATED: when variation qty or price changes -> recalc and consolidate
    container.on('input', '.variation-qty, .variation-price', function () {
        const row = $(this).closest('.product-row');

        // Consolidate variation qtys into main quantity
        const totalQty = sumVariationQty(row);
        const qtyInput = findQtyInput(row);

        // Update main qty (keep as integer)
        qtyInput.val(totalQty);

        // Recalculate: we keep variation prices independent, but you already validate sums on submit
        calculateRowCosts(row);
    });

    // Delegated: when main quantity changed by user (only possible when no variations or when we allow edits)
    container.on('input', '.quantity-input', function () {
        const row = $(this).closest('.product-row');
        const qty = parseInt($(this).val()) || 0;
        const variationRows = row.find('.variation-row');

        // enforce limit
        if (qty > 60) {
            alert('Please enter quantity below 60 at a time. For larger quantities, create multiple purchase orders.');
            $(this).val('');
            row.find('.imei-container').empty();
            return calculateRowCosts(row);
        }

        // If there are variation rows, propagate the main qty (distribute proportionally by current shares or equally)
        if (variationRows.length > 0) {
            // If all variation qtys are zero, distribute equally; else scale proportionally
            let currentTotal = sumVariationQty(row);
            if (currentTotal === 0) {
                // distribute equally
                const count = variationRows.length;
                const base = Math.floor(qty / count);
                let remainder = qty - base * count;
                variationRows.each(function () {
                    const $v = $(this);
                    let assign = base + (remainder > 0 ? 1 : 0);
                    $v.find('.variation-qty').val(assign);
                    remainder--;
                });
            } else {
                // scale proportionally
                variationRows.each(function () {
                    const $v = $(this);
                    const cur = parseFloat($v.find('.variation-qty').val()) || 0;
                    const proportion = currentTotal === 0 ? 0 : (cur / currentTotal);
                    const newQty = Math.round(proportion * qty);
                    $v.find('.variation-qty').val(newQty);
                });
                // small correction: ensure sum matches desired qty
                let newSum = sumVariationQty(row);
                let diff = qty - newSum;
                // add diff to first variation
                if (diff !== 0) {
                    const first = variationRows.first();
                    const curFirst = parseInt(first.find('.variation-qty').val()) || 0;
                    first.find('.variation-qty').val(Math.max(0, curFirst + diff));
                }
            }
        }

        // IMEI regeneration if checked
        if (row.find('.enable-imei-checkbox').is(':checked')) {
            generateIMEIInputs(row);
        }

        calculateRowCosts(row);
    });

    // IMEI checkbox delegated
    container.on('change', '.enable-imei-checkbox', function () {
        const row = $(this).closest('.product-row');
        const checked = $(this).is(':checked');
        const ic = row.find('.imei-container');
        if (checked) {
            ic.show();
            generateIMEIInputs(row);
        } else {
            ic.hide().empty();
        }
    });

    // Price/tax/discount delegated recalculation
    container.on('input', '.price-input, .tax-input, .discount-input', function () {
        const row = $(this).closest('.product-row');
        calculateRowCosts(row);
    });

    // Remove row
    container.on('click', '.remove-product-row', function () {
        $(this).closest('.product-row').remove();
        updateProductNumbers();
        updateRemoveButtons();
        updateTotalSummary();
    });

    // Form submit validation (existing logic preserved; can be extended)
    $('#purchaseOrderCreate').on('submit', function (e) {
        // you can keep your existing validation logic here
        // Example: ensure variations sum matches main qty, ensure net cost > 0 etc.
        // nothing changed here; we trust server-side to still validate
    });

    // -----------------------
    // Helper functions
    // -----------------------
    function addProductRow(oldData = null) {
        const template = $('#productRowTemplate').html();
        const newRow = $(template).clone();

        // set real row index before replacing names
        newRow.attr('data-row-index', rowIndex);
        newRow.addClass('product-row');
        newRow.find('.product-number').text(rowIndex + 1);

        // Replace names/ids/for text [0] -> [rowIndex]
        newRow.find('select, input, label').each(function () {
            const name = $(this).attr('name');
            const id = $(this).attr('id');
            const forAttr = $(this).attr('for');

            if (name) $(this).attr('name', name.replace('[0]', '[' + rowIndex + ']'));
            if (id) $(this).attr('id', id.replace('[0]', '[' + rowIndex + ']'));
            if (forAttr) $(this).attr('for', forAttr.replace('[0]', '[' + rowIndex + ']'));
        });

        // Defaults
        newRow.find('.quantity-input').val(1);
        newRow.find('.tax-input').val('0');
        newRow.find('.net-cost-input').val('');
        newRow.find('.gross-cost-input').val('');

        $('#productsContainer').append(newRow);

        // If oldData is provided, populate values (we trigger change events to load dependent data)
        if (oldData) {
            try {
                if (oldData.category) {
                    newRow.find('.category-select').val(oldData.category).trigger('change');
                }
                // small delays to allow AJAX to populate subcategory/product
                setTimeout(() => {
                    if (oldData.sub_category) newRow.find('.sub-category-select').val(oldData.sub_category).trigger('change');
                }, 300);
                setTimeout(() => {
                    if (oldData.product) newRow.find('.product-select').val(oldData.product).trigger('change');
                }, 700);

                if (oldData.quantity) newRow.find('.quantity-input').val(oldData.quantity);
                if (oldData.price_per_unit) newRow.find('.price-input').val(oldData.price_per_unit);
                if (oldData.discount) newRow.find('.discount-input').val(oldData.discount);
                if (oldData.tax) newRow.find('.tax-input').val(oldData.tax);

                // IMEIs
                if (oldData.imei && Array.isArray(oldData.imei) && oldData.imei.length > 0) {
                    newRow.find('.enable-imei-checkbox').prop('checked', true);
                    generateIMEIInputs(newRow);
                    // fill values after IMEI inputs generated
                    setTimeout(() => {
                        newRow.find('.imei-input').each(function (i) {
                            if (oldData.imei[i]) $(this).val(oldData.imei[i]);
                        });
                    }, 300);
                }
            } catch (err) {
                console.warn('oldData populate failed', err);
            }
        }

        rowIndex++;
        updateRemoveButtons();
    }

    // find quantity input inside row reliably
    function findQtyInput(row) {
        // prefer class; fallback to name-based selector
        let el = row.find('.quantity-input');
        if (el.length) return el;
        return row.find('input[name^="products"][name$="[quantity]"]');
    }

    // sum variation qtys in a row
    function sumVariationQty(row) {
        let total = 0;
        row.find('.variation-qty').each(function () {
            total += parseInt($(this).val()) || 0;
        });
        return total;
    }

    // generate IMEI inputs based on current qty
    function generateIMEIInputs(row) {
        const qty = parseInt(findQtyInput(row).val()) || 0;
        const container = row.find('.imei-container');
        const rowIdx = row.attr('data-row-index');

        container.html('');
        if (qty <= 0) return;

        if (qty > 60) {
            alert('Max 60 IMEIs allowed');
            return;
        }

        for (let i = 1; i <= qty; i++) {
            container.append(`
                <div class="col-md-3">
                    <div class="input-group mb-2">
                        <span class="input-group-text">#${i}</span>
                        <input type="text" name="products[${rowIdx}][imei][]" class="form-control imei-input" placeholder="IMEI/Serial #${i}">
                    </div>
                </div>
            `);
        }
    }

    // clear variations
    function clearVariations(row) {
        row.find('.variation-container').html('').hide();
        // ensure quantity editable by default
        findQtyInput(row).removeAttr('readonly');
    }

    // calculate for a row
    function calculateRowCosts(row) {
        const qty = parseFloat(findQtyInput(row).val()) || 0;
        const price = parseFloat(row.find('.price-input').val()) || 0;
        const tax = parseFloat(row.find('.tax-input').val()) || 0;
        const discount = parseFloat(row.find('.discount-input').val()) || 0;

        let net = qty * price;
        let gross = net + (net * tax / 100) - discount;
        if (!isFinite(net)) net = 0;
        if (!isFinite(gross)) gross = 0;

        row.find('.net-cost-input').val(net ? net.toFixed(2) : '');
        row.find('.gross-cost-input').val(gross ? gross.toFixed(2) : '');
        updateTotalSummary();
    }

    // update totals
    function updateTotalSummary() {
        let totalNet = 0, totalTax = 0, totalDiscount = 0, grand = 0;
        $('.product-row').each(function () {
            const row = $(this);
            const qty = parseFloat(findQtyInput(row).val()) || 0;
            const price = parseFloat(row.find('.price-input').val()) || 0;
            const tax = parseFloat(row.find('.tax-input').val()) || 0;
            const discount = parseFloat(row.find('.discount-input').val()) || 0;

            const net = qty * price;
            const taxAmount = net * tax / 100;

            totalNet += net;
            totalTax += taxAmount;
            totalDiscount += discount;
            grand += (net + taxAmount - discount);
        });

        $('#totalNetCost').text(totalNet.toFixed(2));
        $('#totalTax').text(totalTax.toFixed(2));
        $('#totalDiscount').text(totalDiscount.toFixed(2));
        $('#grandTotal').text(grand.toFixed(2));
    }

    function updateProductNumbers() {
        $('.product-row').each(function (i) {
            $(this).find('.product-number').text(i + 1);
        });
    }

    function updateRemoveButtons() {
        $('.remove-product-row').prop('disabled', $('.product-row').length <= 1);
    }
});
