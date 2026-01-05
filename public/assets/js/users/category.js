$('#addCategory').on('submit', function (e) {
    e.preventDefault();

    let form = this;
    let formData = new FormData(form);

    $.ajax({
        url: "../categories/store",
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
            $('#categorySubmit').prop('disabled', true).html('<i class="ri-loader-4-line"></i> Submitting...');
        },

        success: function (response) {
            console.log(response);

            if (response.status === true) 
            {
                let currentUrl = window.location.pathname;

                if (currentUrl.includes('categories')) 
                {
                    alert(response.message);
                    window.location.href = response.redirect;
                }
                else {
                    let category = response.data;

                    let categorySelect = $('#category');

                    /* Append category if not exists */
                    if (categorySelect.find('option[value="' + category.id + '"]').length === 0) {
                        categorySelect.append(
                            `<option value="${category.id}">
                                ${category.name}
                            </option>`
                        );
                    }

                    /* Select newly created category */
                    categorySelect
                        .val(category.id)
                        .trigger('change');

                    /* Close modal */
                    $('#categoryAdd').modal('hide');

                    /* Reset form */
                    $('#addCategory')[0].reset();

                    /* Restore submit button */
                    $('#categorySubmit').prop('disabled', false).html('<i class="ri-save-line"></i> Submit');
                }
            }
            else
            {
                alert(response.message);
                $('#categorySubmit').prop('disabled', false).html('<i class="ri-save-line"></i> Submit');
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
            $('#categorySubmit').prop('disabled', false).html('<i class="ri-save-line"></i> Submit');
        }
    });
});


function category_edit(element) {
    var system_id = $(element).data("system_id");
    console.log(system_id);
    jQuery.ajax({
        url : 'edit',
        type: 'GET',
        dataType: 'json',
        data: {id:system_id},

        success: function (data) {
            console.log(data);
            document.getElementById("category_name").value = data.category_name;
            document.getElementById("category_id").value = system_id;
            $('#categoryEdit').modal('show');
        },
        error: function (xhr) {
            alert("Failed to load category.");
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
