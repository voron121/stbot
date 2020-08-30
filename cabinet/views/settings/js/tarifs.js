function createMessage(message, status) {
    if ("error" == status) {
        message = '<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+message+'</div>';
    } else {
        message = '<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+message+'</div>';
    }
    return message;
}
//--------------------------------------------------//


var max_height = 0;
$('.tarif_wraper').each(function() {
    if (max_height < $(this).height()) {
        max_height = $(this).height()
    }
});
$('.tarif_wraper').height(max_height);

//--------------------------------------------------//



$('.tarif_action_btn').on("click", function() {
    subscription_id = $(this).attr('data-tarif');
    $.ajax({
        url     : "/cabinet/helpers/tarifHelper.php", 
        type    : "post", 
        dataType: "json", 
        data: { 
            "subscription_id"   : subscription_id,
        },
        // после получения ответа сервера
        success: function(data){ 
            $('.msg').html(createMessage(data.message, data.status));  
        }
    });
});
