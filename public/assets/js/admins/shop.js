function copyBankDetails() {
	const text = document.getElementById('bank-details').innerText;
	navigator.clipboard.writeText(text).then(() => {
    }).catch(err => {
        console.error("Failed to copy: ", err);
    });
}


$(document).ready(function () {
    // Custom rules
    $.validator.addMethod("pwcheck", function (value) {
        return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])/.test(value);
    }, "Password must contain uppercase, lowercase, number, and special character.");

    $.validator.addMethod("filesize", function (value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    }, "File must be less than 2MB.");

    $.validator.addMethod("notEqualTo", function (value, element, param) {
        return this.optional(element) || value !== $(param).val();
    }, "Fields must be different.");

    $("#shopCreate").validate({
        rules: {
            logo: {
                required: true,
                extension: "jpg|jpeg|png|gif",
                filesize: 2048 * 1024 // 2MB
            },
            name: {
                required: true,
                maxlength: 50
            },
            email: {
                email: true
            },
            phone: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 10,
                notEqualTo: "#phone1"
            },
            phone1: {
                digits: true,
                minlength: 10,
                maxlength: 10,
                notEqualTo: "#phone"
            },
            password: {
                required: true,
                minlength: 6,
                maxlength: 20,
                pwcheck: true
            },
            password_confirmation: {
                equalTo: "#password"
            },
            address: {
                maxlength: 100
            },
            slug_name: {
                required: true,
                maxlength: 50,
                pattern: /^[a-zA-Z0-9_-]+$/
            },
            user_name: {
                required: true,
                maxlength: 20,
                pattern: /^[a-zA-Z0-9_-]+$/
            },
            gst: {
                pattern: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i
            },
            bank: {
                maxlength: 50
            },
            holder_name: {
                maxlength: 50
            },
            account_number: {
                digits: true,
                minlength: 9,
                maxlength: 18,
                equalTo: "#confirm_account_number"
            },
            confirm_account_number: {
                equalTo: "#account_number"
            },
            branch: {
                maxlength: 50
            },
            ifsc_code: {
                pattern: /^[A-Z]{4}0[A-Z0-9]{6}$/i
            }
        },
        messages: {
            logo: {
                required: "Shop logo is required",
                extension: "Only jpg, jpeg, png, gif are allowed",
                filesize: "File must be less than 2MB"
            },
            name: {
                required: "Shop name is required",
                maxlength: "Shop name cannot exceed 50 characters"
            },
            phone: {
                required: "Mobile number is required",
                digits: "Only numbers allowed",
                minlength: "Must be 10 digits",
                maxlength: "Must be 10 digits",
                notEqualTo: "Phone and alternate phone must be different"
            },
            phone1: {
                notEqualTo: "Alternate phone must be different from phone"
            },
            password: {
                required: "Password is required",
                minlength: "Password must be at least 6 characters",
                maxlength: "Password cannot exceed 20 characters"
            },
            password_confirmation: {
                equalTo: "Passwords do not match"
            },
            slug_name: {
                required: "Slug name is required",
                maxlength: "Slug cannot exceed 50 characters",
                pattern: "Only letters, numbers, dashes, and underscores allowed"
            },
            user_name: {
                required: "User name is required",
                maxlength: "User name cannot exceed 20 characters",
                pattern: "Only letters, numbers, dashes, and underscores allowed"
            },
            gst: {
                pattern: "Enter a valid GSTIN"
            },
            account_number: {
                equalTo: "Account number and confirmation must match"
            },
            confirm_account_number: {
                equalTo: "Account number and confirmation must match"
            },
            ifsc_code: {
                pattern: "Enter a valid IFSC code"
            }
        },
        errorElement: "span",
        errorClass: "text-danger",
        highlight: function (element) {
            $(element).addClass("is-invalid");
        },
        unhighlight: function (element) {
            $(element).removeClass("is-invalid");
        },
        errorPlacement: function (error, element) {
            // Place error after the input, not inside the label
            if (element.parent(".input-group").length) {
                error.insertAfter(element.parent()); // for input groups
            } else {
                error.insertAfter(element); // normal inputs
            }
        }
    });
});

$(document).ready(function () {
    // 🔹 Custom validation rules
    $.validator.addMethod("pwcheck", function (value) {
        return value === "" || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])/.test(value);
    }, "Password must contain uppercase, lowercase, number, and special character.");

    $.validator.addMethod("filesize", function (value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    }, "File must be less than 2MB.");

    $.validator.addMethod("notEqualTo", function (value, element, param) {
        return this.optional(element) || value !== $(param).val();
    }, "Fields must be different.");

    $("#shopUpdate").validate({
        rules: {
            logo: {
                extension: "jpg|jpeg|png|gif",
                filesize: 2048 * 1024 // 2MB
            },
            name: {
                required: true,
                maxlength: 50
            },
            email: {
                email: true
            },
            phone: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 10,
                notEqualTo: "#phone1"
            },
            phone1: {
                digits: true,
                minlength: 10,
                maxlength: 10,
                notEqualTo: "#phone"
            },
            address: {
                maxlength: 100
            },
            slug_name: {
                required: true,
                maxlength: 50,
                pattern: /^[a-zA-Z0-9_-]+$/
            },
            user_name: {
                required: true,
                maxlength: 20,
                pattern: /^[a-zA-Z0-9_-]+$/
            },
            gst: {
                pattern: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i
            },
            password: {
                required: false, // not required by default
                minlength: {
                    param: 6,
                    depends: function (element) {
                        return $(element).val().length > 0; // only validate if not empty
                    }
                },
                maxlength: {
                    param: 20,
                    depends: function (element) {
                        return $(element).val().length > 0;
                    }
                },
                pwcheck: {
                    depends: function (element) {
                        return $(element).val().length > 0;
                    }
                }
            },
            password_confirmation: {
                equalTo: "#password"
            },
            bank: {
                maxlength: 50
            },
            holder_name: {
                maxlength: 50
            },
            account_number: {
                digits: true,
                minlength: 9,
                maxlength: 18,
                equalTo: "#confirm_account_number"
            },
            confirm_account_number: {
                equalTo: "#account_number"
            },
            branch: {
                maxlength: 50
            },
            ifsc_code: {
                pattern: /^[A-Z]{4}0[A-Z0-9]{6}$/i
            }
        },
        messages: {
            logo: {
                extension: "Only jpg, jpeg, png, gif allowed",
                filesize: "File must be less than 2MB"
            },
            name: {
                required: "Shop name is required",
                maxlength: "Max 50 characters allowed"
            },
            phone: {
                required: "Mobile number is required",
                digits: "Only numbers allowed",
                minlength: "Must be 10 digits",
                maxlength: "Must be 10 digits",
                notEqualTo: "Phone and alternate phone must be different"
            },
            phone1: {
                notEqualTo: "Alternate phone must be different from phone"
            },
            slug_name: {
                required: "Slug is required",
                maxlength: "Max 50 characters allowed",
                pattern: "Only letters, numbers, dashes and underscores allowed"
            },
            user_name: {
                required: "User name is required",
                maxlength: "Max 20 characters allowed",
                pattern: "Only letters, numbers, dashes and underscores allowed"
            },
            gst: {
                pattern: "Enter a valid GST number"
            },
            password: {
                minlength: "Min 6 characters",
                maxlength: "Max 20 characters"
            },
            password_confirmation: {
                equalTo: "Passwords do not match"
            },
            account_number: {
                equalTo: "Account number and confirmation must match"
            },
            confirm_account_number: {
                equalTo: "Account number and confirmation must match"
            },
            ifsc_code: {
                pattern: "Enter a valid IFSC code"
            }
        },
        errorElement: "span",
        errorClass: "text-danger",
        errorPlacement: function (error, element) {
            if (element.parent(".input-group").length) {
                error.insertAfter(element.parent()); // For input groups
            } else {
                error.insertAfter(element); // Normal inputs
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

