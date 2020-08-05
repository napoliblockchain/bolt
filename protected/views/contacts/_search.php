<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'action' => Yii::app()->createUrl('contacts/add'),
		'method' => 'get',
	)); ?>
	<div class="form-group">
		<div class="input-group">
			<div class="input-group-addon">
				<i class="fa fa-search"></i>
			</div>
			<?php echo $form->textField($model,'username',array(
				'placeholder'=>Yii::t('lang','Find contacts by username'),
				'size'=>50,'maxlength'=>50,
				'class'=>'form-control',
				'style'=>'height:40px;',
			)); ?>
		</div>
		<?php echo $form->error($model,'username',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo CHtml::submitButton(Yii::t('lang','Search'), array(
			'class' => 'btn alert-primary text-light',
			'style' => 'min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;',
		)); ?>
	</div>
	<?php $this->endWidget(); ?>
</div><!-- form -->
