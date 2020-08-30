function saveShedule(data) 
{
    $.ajax({
        url: "/cabinet/helpers/scheduler.php",
        type: "post",
        dataType: "json",
        data: data, 
        // после получения ответа сервера
        success: function (response) {
            if (response.status === 'error') {
                $('.shedulerFormMessage').removeClass('alert-success').addClass('alert-danger').text(response.message).show();
            } else {
                $('.shedulerFormMessage').removeClass('alert-danger').addClass('alert-success').text(response.message).show();
                $('.shedulerForm').hide();
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $('.shedulerFormMessage').removeClass('alert-success').addClass('alert-danger').text("Ошибка запроса!").show();
        }
    });
}

function renderSchedulerActions(item, state) 
{
    var scheduler_actions = {
        "post" : {
            "ACTIVE" : {
                "PUBLISH" : "Опубликовать запись",
            },
            "PUBLISHED" : {
                "UNPUBLISH" : "Удалить запись"
            }
        },
        "poll" : {
            "ACTIVE" : {
                "PUBLISH" : "Опубликовать запись",
            },
            "PUBLISHED" : {
                "CLOSE"     : "Закрыть опрос",
                "UNPUBLISH" : "Удалить опрос"
            },
            "CLOSE" : {
                "UNPUBLISH" : "Удалить опрос",
            },
        },
    };
    var item_actions_list = scheduler_actions[item][state];
    
    var actions = "<select name=\"action\" id=\"action\" class=\"form-control\">";
    for (key in item_actions_list) {
        actions += "<option value=\""+key+"\">"+item_actions_list[key]+"</option>";
    }
    actions += "</select>";
    
    $("#scheeduler_actions").html(actions);
}

$(document).ready(function () 
{
    time = new Date();
    time.setMinutes( time.getMinutes() + 5 );
    
    $(".add_schedule").on("click", function() {
        renderSchedulerActions( $(this).attr("item-type"), $(this).attr("item-status"));
    });
    
    $('#scheduler').on('show.bs.modal', function (e) {
        $('.shedulerForm').show();
        $('.shedulerFormMessage').hide();
        $('.modal_actions_close').hide();
        $('.modal_actions_open').show();
    });

    $('#date').datetimepicker({
        format          : 'YYYY-MM-DD',
        locale          : 'ru',
        minDate         : Date.now(),
        showTodayButton : true,
    });

    $('#time').datetimepicker({
        format  : 'HH:mm',
        locale  : 'ru', 
        minDate : time,
    });
    
    
    $(".add_schedule").click(function () {
        $('input[name=id]').val($(this).attr('item-id'));
        $('input[name=type]').val($(this).attr('item-type'));
        $('input[name=channel_id]').val($(this).attr('item-channel_id'));
    });

    $('body').on('click', '.save_shedule', function (e) {
        data = {
            'id'            : $('input[name=id]').val(),
            'type'          : $('input[name=type]').val(),
            'channel_id'    : $('input[name=channel_id]').val(),
            'date'          : $('input[name=date]').val(),
            'time'          : $('input[name=time]').val(),
            'action'        : $("#action option:selected").val(),
            'request_type'  : 'ajax',
        }
        saveShedule(data);
        $('.modal_actions_close').show();
        $('.modal_actions_open').hide();
    });
    
    $('.close_sheduler_modal').click(function() {
        location.reload(true);
    });
})



