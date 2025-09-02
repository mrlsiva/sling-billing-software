
$(document).ready(function () {

    // 🔹 Custom Methods
    $.validator.addMethod("pwcheck", function (value) {
        return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])/.test(value);
    }, "Password must include upper, lower, number, and special character.");

    $.validator.addMethod("filesize", function (value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    }, "File must be less than 2MB.");

    $.validator.addMethod("notEqualTo", function (value, element, param) {
        return this.optional(element) || value !== $(param).val();
    }, "Fields must be different.");

    // 🔹 Apply validation
    $("#branchCreate").validate({
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
                email: true // uniqueness backend only
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
            gst: {
                pattern: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i
            },
            password: {
                required: true,
                minlength: 6,
                maxlength: 20,
                pwcheck: true
            },
            password_confirmation: {
                required: true,
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
                required: "Branch name is required",
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
                notEqualTo: "Alternate phone must be different"
            },
            gst: {
                pattern: "Enter a valid GST number"
            },
            password: {
                required: "Password is required",
                minlength: "Min 6 characters",
                maxlength: "Max 20 characters"
            },
            password_confirmation: {
                required: "Confirm password is required",
                equalTo: "Passwords do not match"
            },
            slug_name: {
                required: "Slug is required",
                maxlength: "Max 50 characters",
                pattern: "Only letters, numbers, dashes and underscores allowed"
            },
            user_name: {
                required: "User name is required",
                maxlength: "Max 20 characters",
                pattern: "Only letters, numbers, dashes and underscores allowed"
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

    // 🔹 Custom Methods
    $.validator.addMethod("pwcheck", function (value) {
        return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])/.test(value);
    }, "Password must include upper, lower, number, and special character.");

    $.validator.addMethod("filesize", function (value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    }, "File must be less than 2MB.");

    $.validator.addMethod("notEqualTo", function (value, element, param) {
        return this.optional(element) || value !== $(param).val();
    }, "Fields must be different.");

    // ✅ GST regex
    $.validator.addMethod("gst", function (value, element) {
        return this.optional(element) || /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i.test(value);
    }, "Enter a valid GST number.");

    // ✅ IFSC regex
    $.validator.addMethod("ifsc", function (value, element) {
        return this.optional(element) || /^[A-Z]{4}0[A-Z0-9]{6}$/i.test(value);
    }, "Enter a valid IFSC code.");

    // ✅ Slug/User_name regex
    $.validator.addMethod("alpha_dash", function (value, element) {
        return this.optional(element) || /^[a-zA-Z0-9_-]+$/.test(value);
    }, "Only letters, numbers, dashes and underscores allowed.");

    // 🔹 Apply validation
    $("#branchEdit").validate({
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
                email: true // uniqueness handled by backend
            },
            phone: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 10,
                notEqualTo: "#phone1"
                // uniqueness handled by backend
            },
            phone1: {
                digits: true,
                minlength: 10,
                maxlength: 10,
                notEqualTo: "#phone"
                // uniqueness handled by backend
            },
            gst: {
                gst: true
                // uniqueness handled by backend
            },
            password: {
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
                alpha_dash: true
                // uniqueness handled by backend
            },
            user_name: {
                required: true,
                maxlength: 20,
                alpha_dash: true
                // uniqueness handled by backend
            },
            payment_method: {
                required: true
            },
            payment_date: {
                required: true,
                date: true,
                max: new Date().toISOString().split("T")[0] // before_or_equal:today
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
                ifsc: true
            }
        },
        messages: {
            logo: {
                extension: "Only jpg, jpeg, png, gif allowed",
                filesize: "File must be less than 2MB"
            },
            name: {
                required: "Branch name is required",
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
                notEqualTo: "Alternate phone must be different"
            },
            gst: {
                gst: "Enter a valid GST number"
            },
            password: {
                minlength: "Min 6 characters",
                maxlength: "Max 20 characters"
            },
            password_confirmation: {
                equalTo: "Passwords do not match"
            },
            slug_name: {
                required: "Slug is required",
                maxlength: "Max 50 characters"
            },
            user_name: {
                required: "User name is required",
                maxlength: "Max 20 characters"
            },
            payment_method: {
                required: "Payment method is required"
            },
            payment_date: {
                required: "Payment date is required",
                max: "Payment date cannot be in the future"
            },
            account_number: {
                equalTo: "Account number and confirmation must match"
            },
            confirm_account_number: {
                equalTo: "Account number and confirmation must match"
            },
            ifsc_code: {
                ifsc: "Enter a valid IFSC code"
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



