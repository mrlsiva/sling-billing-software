jQuery(document).ready(function ()
{
    jQuery('select[name="category"]').one('focus', function () {

        let select = jQuery(this);
        if (select.find('option').length > 1) 
        {
            return;
        }
        jQuery.ajax({
            url : '../sub_categories/get_category',
            type: 'GET',
            dataType: 'json',
            success:function(data)
            {
                console.log(data);

                jQuery('select[name="category"]').empty();
                $('select[name="category"]').append('<option value="">'+ "Select" +'</option>');
                jQuery.each(data, function(key,value){
                    console.log(value.name)
                    $('select[name="category"]').append('<option value="'+ value.id +'">'+ value.name +'</option>');
                });                 

            }
        });

    });
});


$('#addSubCategory').on('submit', function (e) {
    e.preventDefault();

    let form = this;
    let formData = new FormData(form);

    $.ajax({
        url: "../sub_categories/store",
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
            $('#subCategorySubmit').prop('disabled', true).html('<i class="ri-loader-4-line"></i> Submitting...');
        },

        success: function (response) {
            console.log(response);

            if (response.status === true) 
            {
                let currentUrl = window.location.pathname;

                if (currentUrl.includes('sub_categories')) 
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
                else {
                    let subCategory = response.data;

                    /* 1. Select parent category */
                    $('#category').val(subCategory.category_id).trigger('change');

                    /* 2. Append sub category if not exists */
                    let subSelect = $('#sub_category');

                    if (subSelect.find('option[value="' + subCategory.id + '"]').length === 0) {
                        subSelect.append(
                            `<option value="${subCategory.id}">${subCategory.name}</option>`
                        );
                    }

                    /* 3. Select new sub category */
                    subSelect.val(subCategory.id).trigger('change');

                    /* 4. Close modal */
                    $('#subCategoryAdd').modal('hide');

                    /* 5. Reset form */
                    $('#addSubCategory')[0].reset();

                    /* 6. Feedback */
                    //alert(response.message);
                }
            }
            else
            {
                alert(response.message);
                $('#subCategorySubmit').prop('disabled', false).html('<i class="ri-save-line"></i> Submit');
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


function sub_category_edit(element) {
    var system_id = $(element).data("system_id");
    console.log(system_id);
    jQuery.ajax({
        url : 'edit',
        type: 'GET',
        dataType: 'json',
        data: {id:system_id},

        success: function (data) {
            console.log(data.sub_category.category_id);
            document.getElementById("sub_category_name").value = data.sub_category.name;
            document.getElementById("sub_category_id").value = data.sub_category.id;
            
             document.getElementById("category_id").value = data.sub_category.category_id;

            $('#subCategoryEdit').modal('show');
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