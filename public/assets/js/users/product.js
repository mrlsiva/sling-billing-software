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
    $.validator.addMethod("noSpace", function (value, element) {
        return value == '' || (value.trim().length > 0 && value.indexOf(" ") === -1);
    }, "No spaces are allowed.");

    // On form submit or input, trim fields automatically
    $("#productCreate input[type='text']").on("blur", function () {
        $(this).val($.trim($(this).val()));
    });

    $("#productCreate").validate({
        rules: {
            image: {
                extension: "jpg|jpeg|png|gif|webp",
                filesize: 2048 * 1024 // 2MB
            },
            category: { required: true },
            sub_category: { required: true },
            name: {
                required: true,
                maxlength: 50,
                noSpace: false   // ✅ ensure trimmed & not empty
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
                noSpace: "Product name cannot be empty."
            },
            code: {
                required: "Product code is required.",
                maxlength: "Max 50 characters allowed.",
                noSpace: "Product code cannot be empty or contain spaces."
            },
            hsn_code: {
                maxlength: "Max 50 characters allowed.",
                noSpace: "HSN code cannot be empty or contain spaces."
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
    // ✅ Custom validator for file size
    $.validator.addMethod("filesize", function (value, element, param) {
        if (element.files.length === 0) {
            return true; // no file selected
        }
        return element.files[0].size <= param;
    }, "File size must be less than 2MB");

    // ✅ Custom validator for no spaces at all
    $.validator.addMethod("noSpace", function (value, element) {
        return this.optional(element) || (value.indexOf(" ") === -1);
    }, "No spaces are allowed.");

    $("#productEdit").validate({
        rules: {
            image: {
                extension: "jpg|jpeg|png|gif|webp",
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
                maxlength: 50,
                noSpace: true // ❌ disallow spaces
            },
            hsn_code: {
                maxlength: 50,
                noSpace: true // ❌ disallow spaces
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
                extension: "Only jpg, jpeg, png, gif, webp files are allowed",
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
                maxlength: "Product code must not exceed 50 characters",
                noSpace: "Spaces are not allowed in product code"
            },
            hsn_code: {
                maxlength: "HSN code must not exceed 50 characters",
                noSpace: "Spaces are not allowed in HSN code"
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



$(function() {
  var $toggle = $('#is_size_differentiation_available');
  if ($toggle.length === 0) {
    $toggle = $('input[name="is_size_differentiation_available"]'); // fallback
  }
  var $sizes = $('#sizes-section');

  if ($toggle.length === 0) {
    console.error('Checkbox not found.');
    return;
  }
  if ($sizes.length === 0) {
    console.error('Sizes section not found.');
    return;
  }

  function update() {
    if ($toggle.is(':checked')) {
      $sizes.show();
    } else {
      $sizes.hide();
      $('.size-checkbox').prop('checked', false);
    }
  }

  // init and bind
  update();
  $toggle.on('change', update);
});

document.addEventListener('DOMContentLoaded', function () {

    // Colour checkbox toggle
    const colourToggle = document.getElementById('is_colour_differentiation_available');
    const coloursSection = document.getElementById('colours-section');

    if (colourToggle && coloursSection) {

        function updateColours() {
            if (colourToggle.checked) {
                coloursSection.style.display = 'block';
            } else {
                coloursSection.style.display = 'none';
                document.querySelectorAll('.colour-checkbox').forEach(cb => cb.checked = false);
            }
        }

        updateColours(); // initialize on load
        colourToggle.addEventListener('change', updateColours);
    }
});




