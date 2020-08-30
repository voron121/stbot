function selectTime(el) {
    el.find("input.time_checkbox").attr("value", 0);
    el.find("span").text("-");
    el.removeClass('sheduler_selected_time');
}

function unselectTime(el) {
    el.find("input.time_checkbox").attr("value", 1);
    el.find("span").text("+");
    el.addClass('sheduler_selected_time');
}

$(".sheduler_time").click(function(){ 
    $(this).on();
    if ( $(this).find("input.time_checkbox").val() == 1 ) {
        selectTime($(this));
    } else {
        unselectTime($(this));
    }
});

function timeSelectEvent(el) {
    if ( el.find("input.time_checkbox").val() == 1 ) {
        selectTime(el);
    } else {
        unselectTime(el);
    }
    renderCheckboxes();
}  

function selectAllDays(el) {
    day     = el.attr('data-dayb');
    items   = $(".sheduler_day[data-daynumber="+day+"]").find('.sheduler_time');
    items.each(function(i, elem) {
        if (el.is(':checked')) {
            unselectTime($(this));
        } else {
            selectTime($(this));
        }
    });
}

function selectAllTimes(el) {
    time    = el.attr('data-timeb');
    items   = $(".sheduler_day").find('.sheduler_time');
    items.each(function(i, elem) {
        item_input  = $(this).find('input[data-time="'+time+'"]');
        item        = item_input.closest('.sheduler_time');
        if (el.is(':checked')) {
            unselectTime(item);
        } else {
            selectTime(item);
        }
    });
}
// TODO: костыльный и жуткий метод. переделать
function renderCheckboxes() {
    daysc       = [];
    timesc      = [];
    items       = $(".sheduler_time");
    times       = $(".sheduler_time_text");
    days        = $(".sheduler_day");
    
    times.each(function(i, elem) {
        time        = $(this).find('input.timeb_checkbox');
        time_index  = time.attr("data-timeb");
        days.each(function(i, elem) {
            test = $(this).find("input[data-time="+time_index+"]");
            if (test.val() == 1) {
                if (typeof(timesc[time_index]) == 'undefined' || timesc[time_index] === null) {
                    timesc.push([time_index]);
                    timesc[time_index] = 1;
                } else {
                    timesc[time_index]++;
                } 
                 
            }
            
        }); 
    }); 
    
    items.each(function(i, elem) {
        item_input  = $(this).find('input.time_checkbox');
        item_day    = item_input.attr('data-day');
        item_time   = item_input.attr('data-time');
         
        if (item_input.val() == 1) {
            if (typeof(daysc[item_day]) == 'undefined' || daysc[item_day] === null) {
                daysc.push([item_day]);
                daysc[item_day] = 1;
            } else {
                daysc[item_day]++;
            } 
        }
    });
    
    $.each(daysc, function(i, elem) {
        if (elem === 24) {
            $(".dayb_checkbox[data-dayb="+i+"]").attr("checked", true);
        } else {
            $(".dayb_checkbox[data-dayb="+i+"]").attr("checked", false);
        }
    });
    $.each(timesc, function(i, elem) {
        if (elem === 7) {
            $(".timeb_checkbox[data-timeb="+i+"]").attr("checked", true);
        } else {
            $(".timeb_checkbox[data-timeb="+i+"]").attr("checked", false);
        }
    });  
}

//----------------------------------------------------------------------------//

$(document).ready(function(){ 
    renderCheckboxes();
    
    $(".dayb_checkbox").click(function(){ 
        selectAllDays($(this));
    });
    
    $(".timeb_checkbox").click(function(){ 
        selectAllTimes($(this));
    });
    
    $(".sheduler_time").click(function(){ 
        timeSelectEvent($(this))
    });
    
    $('.sheduler_time').mousedown(function(){
        timeSelectEvent($(this));
        $('.sheduler_time').on('mouseenter',function(){
          timeSelectEvent($(this));
        });
    }).mouseup(function(){
      $('.sheduler_time').off('mouseenter');
    });
})



