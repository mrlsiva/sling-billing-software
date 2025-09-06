function finance_edit(element) {
    var system_id = $(element).data("system_id");
    console.log(system_id);
    jQuery.ajax({
        url : 'edit',
        type: 'GET',
        dataType: 'json',
        data: {id:system_id},

        success: function (data) {
            console.log(data);
            document.getElementById("finance").value = data.name;
            document.getElementById("finance_id").value = system_id;
            $('#financeEdit').modal('show');
        },
        error: function (xhr) {
            alert("Failed to load finance.");
            console.log(xhr.responseText);
        }
    });
}