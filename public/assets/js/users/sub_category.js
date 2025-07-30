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