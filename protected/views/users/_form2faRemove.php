<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'users-form',
	'enableAjaxValidation'=>false,
)); ?>
<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>
	<div class="col-md ">
		<div class="form-group">
			<div class="input-group">
				<div class="input-group-addon"><?php echo Yii::t('lang','Verification code');?></div>
				<?php echo $form->textField($model,'ga_cod',array('class'=>'form-control')); ?>
			</div>
			<?php echo $form->error($model,'ga_cod',array('class'=>'alert alert-danger')); ?>
		</div>
	</div>


	<div class="form-group">
		<?php echo CHtml::submitButton(Yii::t('lang','Confirm'), array(
			'class' => 'btn alert-primary text-light',
			'style' => 'min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;',
		)); ?>
	</div>


<?php $this->endWidget(); ?>

</div><!-- form -->
