function validationInput(data) {
	$.ajax({
		url 		: "/cabinet/helpers/ajaxValidation.php",
		type 		: "post",
		dataType 	: "json",
		data 		: {'data' : data},
		// после получения ответа сервера
		success: function(response){
			// console.log(response);
			if (response.status === 'error') {
				$('div[data-validation-message=' + response.input_name + ']').show();
				$('.btn-send').attr('disabled','disabled');
				$('div[data-validation-message=' + response.input_name + ']').text(response.message);	
			} else {
				$('div[data-validation-message=' + response.input_name + ']').hide();
				$('.btn-send').removeAttr('disabled');
			}
		}
	});
}

$(document).ready(function(){
	$(".validation_input").change(function() {
		data = {
			'input_name'		: $(this).attr('name'),
			'input_value' 		: $(this).val(),
			'validation_type' 	: $(this).attr('data-validation')
		}
		//console.log(data);
  		validationInput(data);
	});
})



