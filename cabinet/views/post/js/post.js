$('#TelegramMessageText').trumbowyg({
    btns: [
        ['viewHTML'],
        ['undo', 'redo'],
        //['formatting'],
        ['strong', 'em'],
        ['link'],
        ['removeformat'],
        ['emoji']
    ]
});

$('#TelegramMessageText').on('tbwchange ', function () {
    var validation;
    data = {
        'input_name': $(this).attr('name'),
        'input_value': $(this).val(),
        'validation_type': $(this).attr('data-validation')
    }
    clearTimeout(validation);
    validation = setTimeout(function () {
        validationInput(data);
    }, 1000);
});
 