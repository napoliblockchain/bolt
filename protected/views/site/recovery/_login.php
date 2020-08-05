<div class="form">
<?php
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('lang','Password recovery');
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'recoverypassword-form',
	'enableClientValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
));
$settings = Settings::load();
$reCaptcha2PublicKey = $settings->reCaptcha2PublicKey;
?>

<div class="login-wrap">

	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
		</div>
		<div class="form-group">
			<strong>
				<center><?php echo Yii::t('lang','Password recovery'); ?></center>
			</strong>
		</div>
		<div class="login-form">
			<!-- BUGFIX x Chrome che riempie in automatico i campi successivi -->
			<input style="display:none">
			<input type="password" style="display:none">
			<?php echo $form->hiddenField($model,'password',array('value'=>'email')); ?>
			<!-- end bugfix -->
			<div class="form-group">
				<div class="input-group">
                    <div class="input-group-addon">
						<img style="height:25px;" src="css/images/ic_account_circle.svg">
                    </div>
					<?php echo $form->textField($model,'username',array('placeholder'=>Yii::t('lang','Email address'),'class'=>'form-control','style'=>'height:45px;')); ?>

				</div>
				<?php echo $form->error($model,'username',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php
				$form->widget('application.extensions.reCaptcha2.SReCaptcha', array(
						'name' => 'reCaptcha', //is requred
						'siteKey' => $reCaptcha2PublicKey, //is requred
						'model' => $form,
						'lang' => 'it-IT',
						//'attribute' => 'reCaptcha' //if we use model name equal attribute or customize attribute
					)
				); ?>
			</div>
			<?php echo CHtml::submitButton(Yii::t('lang','Recovery'), array('class' => 'au-btn au-btn--block au-btn--blue m-b-20','id'=>'accedi-button')); ?>
			<?php echo Logo::footer(); ?>
		</div>

	</div>
</div>



<?php $this->endWidget(); ?>
