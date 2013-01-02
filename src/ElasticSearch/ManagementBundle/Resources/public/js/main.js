$(function() {
    $(".ppal").click(function(){
        var value = $(this).attr("id");
        $(".left_menu > li:not(.ppal)").hide();
        $("."+value).toggle();
    });

    $(".show_model").click(function(){
        var url = $(this).attr('href');
        var callback = $(this).attr('data-callback');
        $.ajax({
            url: url,
            success: function (data) {
                $("#modal_div").html(data);
                $("#windowModal").modal("toggle");
                $(".modal_button").bind('click', function(){
                    eval(callback + "('" + url + "')");
                });
                return false;
            }
        });
        return false;
    });

    $('.toggle_box').click(function(){
        var box = $(this).attr('data-box');
        $('#'+box).toggle();
    });
});

function modalAction(url) {
    $.ajax({
        url: url + '&doaction=true',
        success: function (data) {
            $("#modal_div").html(data);
            $(".modal-backdrop").remove();
            $("#windowModal").modal("show");
            return false;
        }
    });
}

function deleteAction(url) {
    $.ajax({
        url: url + '&doaction=true',
        success: function (data) {
            $("#modal_div").html(data);
            $(".modal-backdrop").remove();
            $("#windowModal").modal("show");
            setTimeout(function() {
                location.reload();
            }, 3000);
            return false;
        }
    });
}