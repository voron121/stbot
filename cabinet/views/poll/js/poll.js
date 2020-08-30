function addAnswer() {
	inputs = $('.answer');
	if (inputs.length >= 10) {
		return false;
	}
	input = $('.answer:last');
 	input.clone().find("input:text").val("").end().appendTo('.answers');
 	answerCounter();
}

function deleteAnswer(data) {
	inputs = $('.answer');
	if (inputs.length === 1) {
		return false;
	}
	data.closest('.answer').remove();
 	answerCounter();
}

function answerCounter() {
	if ($('.answer').length >= 10) {
		$(".add_answer").hide();
	} else {
		$(".add_answer").show();
	}
	$('.answer').each(function( index ) {
		$(this).find('span.answer_number').text(index + 1);
	});
}


$(document).ready(function(){
	answerCounter();
	$(".add_answer").click(function() {
  		addAnswer();
	});

	$('body').on('click', '.remove_answer', function(e) {
		deleteAnswer($(this));
	});
})



