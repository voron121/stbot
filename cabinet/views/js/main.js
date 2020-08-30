$(document).ready(function(){
    $('.delete_item').click(function() {
        $(".show_action").show();
        $(".close_action").hide();
        $(".ajmodal").attr('item-id', '');
        $(".ajmodal").attr('itm', '');
                 
        item_id = $(this).attr('item-id');
        item    = $(this).attr('itm');
        file    = $(this).attr('file');
        
        $.ajax({
            url     : "/cabinet/helpers/ajax/ajaxHelper.php", 
            type    : "post", 
            dataType: "json", 
            data: { 
                "item_id"   : item_id,
                "item"      : item,
                "file"      : file,          
                "action"    : 'delete'
            },
            // после получения ответа сервера
            success: function(data){
                $("#ajaxModal").modal('show');
                $(".ajmodal").attr('item-id', data.itmid);
                $(".ajmodal").attr('itm', data.itm);
                $(".ajmodal").attr('file', data.file);
                if ("show" === data.close) {
                    $(".show_action").hide();
                    $(".close_action").show();
                }
                $('.ajmsg').html(data.message);  
            }
        });
    });
    
    $('.delete_item_accept').click(function() {
        item_id = $(this).attr('item-id');
        item    = $(this).attr('itm');
        file    = $(this).attr('file');
        
        $.ajax({
            url     : "/cabinet/helpers/ajax/ajaxHelper.php", 
            type    : "post", 
            dataType: "json", 
            data: { 
                "item_id"   : item_id,
                "item"      : item,
                "file"      : file,
                "action"    : 'delete_accept'
            },
            // после получения ответа сервера
            success: function(data){
                $("#ajaxModal").modal('show');
                if ("show" === data.close) {
                    $(".show_action").hide();
                    $(".close_action").show();
                }
                $('.ajmsg').html(data.message);  
            }
        });
    });
    //------------------------------------------------------------------------//
    
    $(".mobile_header_button").on("click", function() {
        if ($(".cabinet_header").hasClass("cabinet_header_open")) {
            $(".cabinet_header").removeClass("cabinet_header_open");
        } else {
            $(".cabinet_header").addClass("cabinet_header_open");
        }
    });

    $("#mobile_menu_area").swipe( {
        //Generic swipe handler for all directions
        swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
        if ("left" === direction) {
            $(".admin_cp_sidebar").removeClass("admin_cp_sidebar_show");
        } else if ("right" === direction) {
            $(".admin_cp_sidebar").addClass("admin_cp_sidebar_show");
        }},
        threshold:0
    });

    $("#close_cp_sidebar").on("click", function() {
        $(".admin_cp_sidebar").removeClass("admin_cp_sidebar_show");
    });
   

})
 
 