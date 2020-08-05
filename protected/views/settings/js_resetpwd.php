<?php
$resetPwdURL = Yii::app()->createUrl('users/resetpwd'); //

$resetPwd = <<<JS

	var ajax_loader_url = 'css/images/loading.gif';
	const resetButtonModal = document.querySelector('#resetpwd-button');

	resetButtonModal.addEventListener('click', function() {
		$.ajax({
			url:'{$resetPwdURL}',
			type: "POST",
			data: {id: '{$idUserCrypted}'},
			beforeSend: function() {
				$('.resetpwd__content').hide();
				$('.resetpwd__content').after('<div class="bitpay-pairing__loading"><center><img width=15 src="'+ajax_loader_url+'"></center></div>');
			},
			dataType: "json",
			success:function(data){
				$('.bitpay-pairing__loading').remove();
			 	$('.pwdreset-button').hide();
				$('.responsepwd__button').show();
				$('.responsepwd__text').text(data.txt);
			},
			error: function(j){
				//something happened!!!
				$('.responsepwd__button').show();
				$('.responsepwd__text').show(j);
			}
		});
	});
JS;

Yii::app()->clientScript->registerScript('resetPwd', $resetPwd);

?>
