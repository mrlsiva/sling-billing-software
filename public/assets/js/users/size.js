$('#addSize').on('submit', function (e) {
    e.preventDefault();

    let form = this;
    let formData = new FormData(form);

    $.ajax({
        url: document.querySelector('meta[name="size-store-url"]').content,
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
            $('#addSizeButton').prop('disabled', true).html('<i class="ri-loader-4-line"></i> Submitting...');
        },

        success: function (response) {
            console.log(response);

            if (response.status === true) 
            {
                let currentUrl = window.location.pathname;

                if (currentUrl.includes('sizes')) 
                {
                    const event = new CustomEvent("toast", {
                        detail: {
                            text: response.message,
                            gravity: "top",
                            position: "right",
                            className: "success", // or "error" based on response
                            duration: 5000,
                            close: true,
                        }
                    });

                    document.dispatchEvent(event);

                    // optional delay so user can see the toast
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 800);
                }
                else 
                {
                    let size = response.data;

                    /* Container where checkboxes live */
                    let sizesSection = $('#sizes-section .row');

                    /* Avoid duplicate checkbox */
                    if ($('#size_' + size.id).length === 0) {

                        sizesSection.append(`
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input class="form-check-input size-checkbox"
                                           type="checkbox"
                                           name="sizes[]"
                                           value="${size.id}"
                                           id="size_${size.id}"
                                           checked>

                                    <label class="form-check-label" for="size_${size.id}">
                                        ${size.name}
                                    </label>
                                </div>
                            </div>
                        `);
                    }

                    /* Show section if hidden */
                    $('#sizes-section').show();

                    /* Close modal */
                    $('#sizeAdd').modal('hide');

                    /* Reset form */
                    $('#addSize')[0].reset();

                    /* Restore submit button */
                    $('#addSizeButton').prop('disabled', false).html('<i class="ri-save-line"></i> Submit');

                }
            }
            else
            {
                alert(response.message);
                $('#addSizeButton').prop('disabled', false).html('<i class="ri-save-line"></i> Submit');
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
            $('#addSizeButton').prop('disabled', false).html('<i class="ri-save-line"></i> Submit');
        }
    });
});

function size_edit(element) {
    var system_id = $(element).data("system_id");
    console.log(system_id);
    jQuery.ajax({
        url : 'edit',
        type: 'GET',
        dataType: 'json',
        data: {id:system_id},

        success: function (data) {
            console.log(data);
            document.getElementById("size").value = data.name;
            document.getElementById("size_id").value = system_id;
            $('#sizeEdit').modal('show');
        },
        error: function (xhr) {
            alert("Failed to load size.");
            console.log(xhr.responseText);
        }
    });
}

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