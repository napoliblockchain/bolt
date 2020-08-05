<?php
Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );
new JsTrans('js',Yii::app()->language); // javascript translation

$getTelegramUserAddress = Yii::app()->createUrl('contacts/getuseraddress');
$myCgridviewScript = <<<JS

function stopPropagation(id,flag){
	$('#'+id).on('click', 'ul.yiiPager li a', function(e){
		e.stopPropagation();
		e.preventDefault();

		var page = this.text;
		var url = $(this).attr('href');

		if (true){
			$.fn.yiiGridView.update(id, {
				type: 'GET',
				url: url,
				success: function() {
					$('#'+id).yiiGridView('update',{
						url: url
					});
					console.log('[yiiGridView] update');//4
					stopPropagation(id,false);
				},
			});
		}
	});
}

function addContact(id_social){
	console.log('[selected Social User id]',id_social);
	$.ajax({
			url:'{$getTelegramUserAddress}',
			type: "POST",
			data: {'id_social': id_social},
			dataType: "json",
			success:function(data){
				$("#WalletTokenForm_to").val(data.address);
				$('#scrollmodalContacts').modal('hide');
			},
			error: function(j){
				console.log(j);
			}
	});
}

JS;
Yii::app()->clientScript->registerScript('myCgridviewScript', $myCgridviewScript, CClientScript::POS_END);
?>
<!-- 51sangiu -->
