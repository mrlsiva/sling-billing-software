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

jQuery(document).ready(function ()
{
	jQuery('select[name="category_id"]').on('change',function(){
		var category = jQuery(this).val();
		if(category)
		{
			jQuery.ajax({
				url : '../get_sub_category',
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

$(document).ready(function () {

    // Custom rule: file size
    $.validator.addMethod("filesize", function (value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    }, "File must be less than 2MB.");

    // Custom validator: no only spaces
    jQuery.validator.addMethod("noSpace", function(value, element) {
        return $.trim(value).length > 0;
    }, "This field cannot be empty");

    // On form submit or input, trim fields automatically
    $("#productCreate input[type='text']").on("blur", function () {
        $(this).val($.trim($(this).val()));
    });

    $("#productCreate").validate({
        rules: {
            image: {
                extension: "jpg|jpeg|png|gif",
                filesize: 2048 * 1024 // 2MB
            },
            category: { required: true },
            sub_category: { required: true },
            name: {
                required: true,
                maxlength: 50,
                noSpace: true   // ✅ ensure trimmed & not empty
            },
            code: {
                required: true,
                maxlength: 50,
                noSpace: true   // ✅ ensure trimmed & not empty
            },
            hsn_code: {
                maxlength: 50,
                noSpace: true   // ✅ trimmed check
            },
            price: {
                required: true,
                number: true,
                min: 1
            },
            tax: { required: true },
            metric: { required: true },
            discount_type: {
                required: function () {
                    return $("#discount").val() !== "";
                }
            },
            discount: {
                required: function () {
                    return $("#discount_type").val() !== "";
                },
                number: true,
                min: 0
            },
            quantity: {
                number: true,
                min: 0
            }
        },
        messages: {
            name: {
                required: "Product name is required.",
                maxlength: "Max 50 characters allowed.",
                noSpace: "Product name cannot be empty or spaces only."
            },
            code: {
                required: "Product code is required.",
                maxlength: "Max 50 characters allowed.",
                noSpace: "Product code cannot be empty or spaces only."
            },
            hsn_code: {
                maxlength: "Max 50 characters allowed.",
                noSpace: "HSN code cannot be empty or spaces only."
            }
        },
        errorElement: "span",
        errorClass: "text-danger",
        errorPlacement: function (error, element) {
            if (element.parent(".input-group").length) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element) {
            $(element).addClass("is-invalid");
        },
        unhighlight: function (element) {
            $(element).removeClass("is-invalid");
        }
    });


});


$(document).ready(function () {
    $("#productEdit").validate({
        rules: {
            image: {
                extension: "jpg|jpeg|png|gif",
                filesize: 2048 * 1024 // 2 MB
            },
            category_id: { required: true },
            sub_category: { required: true },
            name: {
                required: true,
                maxlength: 50
            },
            code: {
                required: true,
                maxlength: 50
            },
            hsn_code: {
                maxlength: 50
            },
            price: {
                required: true,
                number: true,
                min: 1
            },
            tax: { required: true },
            metric: { required: true },
            discount_type: {
                required: function () {
                    return $("#discount").val().length > 0;
                }
            },
            discount: {
                required: function () {
                    return $("#discount_type").val().length > 0;
                },
                number: true,
                min: 0
            },
            quantity: {
                number: true,
                min: 0
            }
        },
        messages: {
            image: {
                extension: "Only jpg, jpeg, png, gif files are allowed",
                filesize: "Image must be less than 2MB"
            },
            category_id: "Please select a category",
            sub_category: "Please select a sub-category",
            name: {
                required: "Product name is required",
                maxlength: "Product name must not exceed 50 characters"
            },
            code: {
                required: "Product code is required",
                maxlength: "Product code must not exceed 50 characters"
            },
            hsn_code: {
                maxlength: "HSN code must not exceed 50 characters"
            },
            price: {
                required: "Selling price is required",
                number: "Please enter a valid number",
                min: "Price must be at least 1"
            },
            tax: "Please select a tax option",
            metric: "Please select a metric",
            discount_type: "Select discount type when discount is entered",
            discount: {
                required: "Enter discount when discount type is selected",
                number: "Discount must be a number",
                min: "Discount must not be negative"
            },
            quantity: {
                number: "Quantity must be a number",
                min: "Quantity cannot be negative"
            }
        },
        errorElement: "span",
        errorClass: "text-danger",
        highlight: function (element) {
            $(element).addClass("is-invalid");
        },
        unhighlight: function (element) {
            $(element).removeClass("is-invalid");
        }
    });

    // Custom validator for file size
    $.validator.addMethod("filesize", function (value, element, param) {
        if (element.files.length === 0) {
            return true; // no file selected
        }
        return element.files[0].size <= param;
    });
});

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

