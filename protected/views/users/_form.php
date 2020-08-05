<?php

?>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'users-form',
	'enableAjaxValidation'=>false,
)); ?>
<?php //echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>

		<div class="form-group">
			<?php echo $form->labelEx($social,'first_name'); ?>
			<?php echo $form->textField($social,'first_name',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($social,'first_name',array('class'=>'alert alert-danger')); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($social,'last_name'); ?>
			<?php echo $form->textField($social,'last_name',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($social,'last_name',array('class'=>'alert alert-danger')); ?>
		</div>
		<div class="form-group">
			<?php echo $form->labelEx($social,'username'); ?>
			<?php echo $form->textField($social,'username',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($social,'username',array('class'=>'alert alert-danger')); ?>
		</div>


	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? Yii::t('model','Insert') : Yii::t('model','Save')), array(
			'class' => 'btn alert-primary text-light',
			'style' => 'min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;',
		)); ?>
	</div>


<?php $this->endWidget(); ?>

</div><!-- form -->
