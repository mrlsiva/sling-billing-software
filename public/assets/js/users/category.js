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