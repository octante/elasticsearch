$(function() {

    $('select[name=index_name]').change(function(){
        loadTypesDropdown($(this).val());
    });
});

function loadTypesDropdown (indice_name)
{
    $.ajax({
        url: 'query_tool/get_types_dropdown?indice_name=' + indice_name,
        success: function (data) {
            $("#types_dropdown").html(data);
            return false;
        }
    });
}