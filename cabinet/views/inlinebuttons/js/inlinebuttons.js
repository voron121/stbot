/**
 * Проверит строку на соотвествие url (TRUE если ссылка FALSE если не ссылка)
 * @param {type} str
 * @returns {Boolean}
 */
function isURL(str) {
  pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
    '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
    '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
    '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
  return !!pattern.test(str);
}

/**
 * 
 * @param {type} str
 * @returns {Boolean}
 */
function isTelegramURL(str) {
  pattern = new RegExp('^(@+\w{1,})?'); 
  return !!pattern.test(str);
}

/**
 * 
 * @param {type} str
 * @returns {Boolean}
 */
function isStringNumber(str) {
    return parseInt(str.replace( /\D/g, '')) || false;
}

/**
 * Проверит поля ввода с кнопками на корректность данных
 * @returns {validation_result}
 */
function validateButtonsData(type) {
    validation_result = {'isValid' : 'true', 'message' : ''};
    if ("counter" == type) {
        text  = $(".buttons_list_editor").find("input[name=button_text]").val(); 
        if (text.length > 20) {
            validation_result = {'isValid' : 'false', 'message' : 'Длина текста привышает 20 символов!'};
        } else if (text.trim().length == 0) {
            validation_result = {'isValid' : 'false', 'message' : 'Введите текст'};
        } else if (isStringNumber(text)) {
            validation_result = {'isValid' : 'false', 'message' : 'В тексте не должно быть чисел!'};
        } 
    } else {
        text    = $(".buttons_list_editor").find("input[name=button_text]").val(); 
        url     = $(".buttons_list_editor").find("input[name=button_url]").val();
        if ("" === text.trim() || "" === url.trim()) {
            validation_result = {'isValid' : 'false', 'message' : 'Пожалуйста заполните все поля!'};
        } else if (false == isURL(url)) {
            validation_result = {'isValid' : 'false', 'message' : 'Пожалуйста укажите корректный url вида https://site.com!'};
        }
    }
    return validation_result;
}

/**
 * Перерисовывает кнопки с расчетом ширины для каждой кнопки в зависимости от 
 * количества кнопок в ряду
 * @returns {undefined}
 */
function renderButtons() {
    addBtn          = $(".hidden .action_btn").outerWidth(true) * $(".hidden .action_btn").length; 
    containerWidth  = $(".buttons_list .buttons_row").outerWidth(true);
    btn             = (addBtn / containerWidth) * 100 ;
    rows            = $(".buttons_list > div.buttons_row");
    // пересчитаем ширину для кнопок в каждой строке
    rows.each(function(index){
        width = 0;
        items = $(this).find('.inline_button_wrap').length;
        width = ((100 - btn.toFixed(0)) / items).toFixed(5)  + "%";
        $(this).find('.inline_button_wrap').css({'width' : width}); 
    }); 
}

/**
 * Конструирует JSON с кнопками
 * @returns {String}
 */
function buttonsToJSON() {
    row     = [];
    rows    = $(".buttons_list .buttons_row"); 
    rows.each(function() {
        btn_list    = [];
        items       = $(this).find('.inline_button');
        items.each(function(index) { 
            callback = $(this).attr("button-callback");
            if (typeof callback == "undefined") {
                btn_list.push({
                    "text"  : $(this).attr("button-text"),
                    "url"   : $(this).attr("button-url")  
                });
            } else {
                btn_list.push({
                    "text"          : $(this).attr("button-text"),
                    "callback_data" : $(this).attr("button-callback") 
                });
            } 
        });
        row.push(btn_list);
    });
    buttons = {"inline_keyboard" : row};
    return JSON.stringify(buttons);
}

/**
 * Создает кнопку 
 * @param {type} text
 * @param {type} value
 * @returns {button|jQuery}
 */
function createButton(text, value, type) {
    button  = $(".hidden .inline_button_wrap").clone(true);
    button.find(".inline_button").attr("button-text", text);
    
    if ("link" == type) {
        button.find(".inline_button").attr("button-url", value);
    } else if ("counter" == type) {
        button.find(".inline_button").attr("button-callback", value);
    }
    
    button.find(".inline_button").find(".text").text(text);
    return button;
} 

/**
 * Открывает окно с редактированием параметров кнопки
 * @param {type} item
 * @returns {undefined}
 */
function showEditWindow(item) { 
    if ($('.buttons_list .edit_window').length > 0) {
        $('.buttons_list .edit_window').remove();
    }
    pos     = item.position();
    width   = item.outerWidth();
    editWindowWidth = width + 'px';
    if ($('.buttons_list .inline_button').length > 2) {
        editWindowWidth = 50 + '%';
    }
    editWindow  = $(".edit_window").clone(true);
    callback    = item.attr("button-callback");
    editWindow.find('input[name=button_text]').val(item.attr("button-text"));
    if (typeof callback == "undefined") {
        editWindow.find('input[name=button_url]').val(item.attr("button-url"));
    } else {
        editWindow.find("label[for=button_url]").remove();
        editWindow.find('input[name=button_url]').remove();
    }
    editWindow.css({left: pos.left + "px", width : editWindowWidth});
    editWindow.insertBefore(item);
}

/**
 * Редактирование кнопки
 * @param {type} item
 * @returns {undefined}
 */
function editButton(item) {
    showEditWindow(item);
    $('#edit_inline_button').on('click', function() {
        item.attr('button-text', $('.edit_window').find('input[name=button_text]').val());
        item.attr('button-url', $('.edit_window').find('input[name=button_url]').val());
        item.find(".text").text($('.edit_window').find('input[name=button_text]').val());
        $('.buttons_list .edit_window').remove();
        $("input[name=buttons]").val(buttonsToJSON()); 
    });
    $("#close_edit_inline_button").on("click", function() {
        $('.buttons_list .edit_window').remove();
    });
}

/**
 * Добавляет новую строку для кнопок
 * @returns {undefined}
 */
function addRow() {
    row_number  = $(".buttons_row").length + 1;
    row         = $(".hidden > .buttons_row").clone(true);  
    row.find(".inline_button_add").attr("data-row", row_number);
    row.attr("data-row", row_number);
    $(".buttons_list").append(row);
}

/**
 * Отображает сообщение
 * @param {type} message
 * @param {type} status
 * @returns {undefined}
 */
function showMessage(message, status) {
    css  = 'danger';
    if ("success" === status) {
        css  = 'success';
    }
    msg  = '<div class="alert alert-'+css+'">';
    msg += '<b>'+message+'</b>';
    msg += '</div>';
    $(".message").html(msg);
}

/**
 * Отображает список полей для добавления кнопок в зависимости от типа кнопок
 * @param {type} type
 * @returns {undefined}
 */
function showButtonsForm(type) {
    $(".modal-body").find(".buttons_list_editor").empty();
    if ("link" == type) { 
        inputs = $(".hidden .link_button").clone(true);
        $(".modal-body .buttons_list_editor").append(inputs);
    }  else if ("counter" == type) {
        counter_id  = Math.round((new Date()).getTime() + Math.floor(Math.random() * 999)); 
        callback    = 'counter_' + counter_id;
        
        inputs = $(".hidden .counter_button").clone(true);
        inputs.find("input[name=button_callback]").val(callback);
        $(".modal-body .buttons_list_editor").append(inputs);
    }
}
// -------------------------------------------------------------------------- //
 
$(document).ready(function () {
    renderButtons();
    
    $("select[name=buttons_mode]").change(function() {
        showButtonsForm($(this).val()); 
    });
    
    $("div.add_button").on("click", function() { 
        $("#create_inline_button").attr("data-row", $(this).attr("data-row"));
    });
    
    $("div.delete_row").on("click", function() { 
        $(this).closest(".buttons_row").remove();
    });
    
    $("#create_inline_button").on("click", function() {
        btn_type = "link";
        if ($(".modal-body .counter_button").length > 0) {
            btn_type = "counter";
        }
        validation_result = validateButtonsData(btn_type);
        if ('false' === validation_result['isValid']) {
            showMessage(validation_result['message'], 'error'); 
        } else {
            $(".message").text("");
            text        = $(".buttons_list_editor").find("input[name=button_text]").val(); 
            row_number  = $(this).attr("data-row");
            row         = $(".buttons_list").find("div.buttons_row[data-row="+row_number+"]");
            if ("link" == btn_type) {
                data = $(".buttons_list_editor").find("input[name=button_url]").val();
            } else if ("counter" == btn_type) {
                data = $(".buttons_list_editor").find("input[name=button_callback]").val();
            } 
            $(row).append(createButton(text, data, btn_type));
            $("input[name=buttons]").val(buttonsToJSON());
            $(".modal-body").find(".buttons_list_editor").empty();
            renderButtons();
        }
    });
    
    $("div.inline_button").on("click", function() {
        editButton($(this));
    });
    
    $("div.remove_button").on("click", function() {
        row             = $(this).closest('div.buttons_row');
        buttons_count   = $(this).closest('.buttons_row').find('.inline_button_wrap').length;
        $(this).closest(".inline_button_wrap").remove();
        if ((buttons_count - 1) == 0) {
            row.remove();
        }
        $("input[name=buttons]").val(buttonsToJSON());
        renderButtons();
    });
    
    $("#add_inline_button").on("click", function() {
        addRow();
    });
});
 
