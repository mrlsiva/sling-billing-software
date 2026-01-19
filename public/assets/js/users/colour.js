$('#addColour').on('submit', function (e) {
    e.preventDefault();

    let form = this;
    let formData = new FormData(form);

    $.ajax({
        url: document.querySelector('meta[name="colour-store-url"]').content,
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
            $('#addColourButton').prop('disabled', true).html('<i class="ri-loader-4-line"></i> Submitting...');
        },

        success: function (response) {
            console.log(response);

            if (response.status === true) 
            {
                let currentUrl = window.location.pathname;

                if (currentUrl.includes('colours')) 
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
                    let colour = response.data;

                    /* Container where colour checkboxes live */
                    let coloursSection = $('#colours-section .row');

                    /* Avoid duplicate checkbox */
                    if ($('#colour_' + colour.id).length === 0) {

                        coloursSection.append(`
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input class="form-check-input colour-checkbox"
                                           type="checkbox"
                                           name="colours[]"
                                           value="${colour.id}"
                                           id="colour_${colour.id}"
                                           checked>

                                    <label class="form-check-label" for="colour_${colour.id}">
                                        ${colour.name}
                                    </label>
                                </div>
                            </div>
                        `);
                    }

                    /* Show section if hidden */
                    $('#colours-section').show();

                    /* Close modal */
                    $('#colourAdd').modal('hide');

                    /* Reset form */
                    $('#addColour')[0].reset();

                    /* Restore submit button */
                    $('#addColourButton')
                        .prop('disabled', false)
                        .html('<i class="ri-save-line"></i> Submit');
                }

            }
            else
            {
                alert(response.message);
                $('#addColourButton').prop('disabled', false).html('<i class="ri-save-line"></i> Submit');
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
            $('#addColourButton').prop('disabled', false).html('<i class="ri-save-line"></i> Submit');
        }
    });
});

function colour_edit(element) {
    var system_id = $(element).data("system_id");
    console.log(system_id);
    jQuery.ajax({
        url : 'edit',
        type: 'GET',
        dataType: 'json',
        data: {id:system_id},

        success: function (data) {
            console.log(data);
            document.getElementById("colour").value = data.name;
            document.getElementById("colour_id").value = system_id;
            $('#colourEdit').modal('show');
        },
        error: function (xhr) {
            alert("Failed to load colour.");
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