<?php
$myCgridviewScript = <<<JS

function stopPropagation(id,flag){
	$('#'+id).on('click', 'ul.yiiPager li a', function(e){
		e.stopPropagation();
		e.preventDefault();

		var page = this.text;
		var search = $('#Socialusers_username').val();
		var url = $(this).attr('href')+'&ricerca='+search;

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

JS;
Yii::app()->clientScript->registerScript('myCgridviewScript', $myCgridviewScript, CClientScript::POS_END);
?>
