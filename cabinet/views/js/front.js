$(document).ready(function(){
    var max_height = 0;
    $('.light_item_wraper > .light_item').each(function() {
        if (max_height < $(this).height()) {
            max_height = $(this).height()
        }
    });
    $('.light_item_wraper > .light_item').height(max_height);

    var tarifs_max_height = 0;
    $('.tarifs_wraper > .light_item').each(function() {
        if (tarifs_max_height < $(this).height()) {
            tarifs_max_height = $(this).height()
        }
    });
    $('.tarifs_wraper > .light_item').height(tarifs_max_height);


    $('.t_slider').bxSlider({
        'controls' 		: false,
        'useCSS'		: true,
        'pager'			: false,
        'auto'			: true,
        'randomStart'	: false,
        'responsive'	: true,  
        'slideMargin'	: 50,
        'light_item'	: 1,
        'breaks'		: [{screen:0, slides:1, pager:false},{screen:460, slides:1},{screen:768, slides:3}]
    }); 
    //------------------------------------------------------------------------//
    
    function constructMessage(status, message) {
        alert_style = "alert-danger";
        if ("success" == status) {
            alert_style = "alert-success";
        }
        message  = '<div class="alert '+alert_style+'">' + message + '</div>';
        return message;
    }
    //------------------------------------------------------------------------//
    
    $('.btn-reg').click(function(){ 
        var u_login             = $('input[name=u_login]').val();
        var u_password          = $('input[name=u_password]').val();
        var u_password_retry    = $('input[name=u_password_retry]').val(); 
        $.ajax({
            url     : "/cabinet/helpers/registration.php?action=aregistration", // куда отправляем
            type    : "post",  
            dataType: "json", 
            data: {  
                "u_login"           : u_login,
                "u_password"        : u_password,
                "u_password_retry"  : u_password_retry
            }, 
            success: function(data){
                message = constructMessage(data.status, data.message);
                $('.messages').html(message); 
                 if ("success" == data.status) {
                    $(".register-form-wrap").hide();
                }
            }
        });
    });
});