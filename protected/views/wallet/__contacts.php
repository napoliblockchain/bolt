<style>
.account-item{
	min-width: 120px;
}
</style>


<div class="table-responsive table--no-card m-b-40">
	<?php
	$this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'contacts-grid',
        'ajaxUpdate'=>true,
		'htmlOptions' => array('class' => 'table table-borderless table-data4 table-wallet text-light'),
		'hideHeader' => true,
		'dataProvider'=>$dataProvider,
        'ajaxUpdate'=> true, //'contacts-grid',
        'afterAjaxUpdate' => 'stopPropagation("contacts-grid",true)', //funzinoe da richiamare dopo update
        'pager'=>array(
    			//'header'=>'Go to page:',
    			//'cssFile'=>Yii::app()->theme->baseUrl
    			'cssFile'=>Yii::app()->request->baseUrl."/css/yiipager.css",
    			'prevPageLabel'=>'<',
    			'nextPageLabel'=>'>',
    			'firstPageLabel'=>'<<',
    			'lastPageLabel'=>'>>',
    	),
		'columns' => array(
			array(
				'name'=>'',
				'type' => 'raw',
				'value'=>'"<a href=\"#\" onclick=\"addContact($data->id_social);\"><div id=\"contact_02_".$data->id_social."\" class=\"account-item clearfix \">". CHtml::image(Socialusers::model()->findByPk($data->id_social)->picture, "img",array("class"=>"image img-cir img-120")) ."</div>" . CHtml::image("css/images/".Socialusers::model()->findByPk($data->id_social)->oauth_provider.".svg", "img", array("class"=>"imgProvider image img-cir img-40")) . "</a>"',
				'htmlOptions'=>array('style'=>'max-width: 60px; max-height:45px;'),
			),
			array(
				'name'=>'Name',
				'type' => 'raw',
				//'value'=>'"<div id=\"contact_03_".$data->id_social."\" class=\"account-item clearfix\">".Socialusers::model()->findByPk($data->id_social)->first_name ." ". Socialusers::model()->findByPk($data->id_social)->last_name ."<br/><small class=\"text-secondary\">". Socialusers::model()->findByPk($data->id_social)->username ."</small><br/><small class=\"text-success\">". Wallets::model()->findByAttributes([\'id_user\'=>$data->id_user])->wallet_address ."</small></div>"',
                'value'=>'"<a href=\"#\" onclick=\"addContact($data->id_social);\"><div id=\"contact_03_".$data->id_social."\" class=\"account-item clearfix\">".Socialusers::model()->findByPk($data->id_social)->first_name ." ". Socialusers::model()->findByPk($data->id_social)->last_name ."<br/><small class=\"text-secondary\">". Socialusers::model()->findByPk($data->id_social)->username ."</small></div></a>"',
			),
			[
				'name'=>'',
				'value' =>'',
				'htmlOptions'=>array('style'=>'width: 10%;'),
				]
		)
	));



	?>
</div>
