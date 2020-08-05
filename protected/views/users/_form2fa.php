<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'users-form',
	'enableAjaxValidation'=>false,
)); ?>
<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>
	<div class="form-group">
		<?php echo $form->hiddenField($model,'ga_secret_key',array('value'=>$secret)); ?>
	</div>

	<div class="col typo-articles">
		<h4><?php echo Yii::t('lang','To use a 2-factor authentication application, follow the steps below:');?></h4>
		<p>
			<ol class="vue-ordered"  style="padding-left:40px;">
				<li><?php echo Yii::t('lang','Download a 2-factor authentication application like Microsoft Authenticator, or ');?> <a href="https://play.google.com/store" target="_blank"><?php echo Yii::t('lang','Google Authenticator');?></a></li>
				<br>
				<li><?php echo Yii::t('lang','Scan the QR Code, or manually enter this code into your application authentication.');?> </br></br>
					<p class="bg-dark text-light" style="text-align: center; border-radius:12px; font-size:1.2em;"><?=$secret?></p>

					<p style="text-align:center;" class="text-dark alert alert-light">
					   <img src="<?=$qrCodeUrl?>" />
				   </p>
				</li>
				<br>
				<li><?php echo Yii::t('lang','Once you have scanned or entered the code manually, your 2-factor authentication application will show you a unique code. Enter this code in the confirmation box below.');?></li>
			</ol>
		</p>
		<br>

		<div class="form-group">
			<div class="input-group">
				<div class="input-group-addon"><?php echo Yii::t('lang','Verification code');?></div>
				<?php echo $form->textField($model,'ga_cod',array('class'=>'form-control','style'=>'height:45px;')); ?>
			</div>
			<?php echo $form->error($model,'ga_cod',array('class'=>'alert alert-danger')); ?>
		</div>
		<div class="form-group">
			<?php echo CHtml::submitButton(Yii::t('lang','Confirm'), array(
				'class' => 'btn alert-primary text-light',
				'style' => 'min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;',
			)); ?>
		</div>

	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
