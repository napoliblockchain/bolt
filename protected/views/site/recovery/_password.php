<div class="form">
<?php

$myPwdScript = <<<JS
$("input[name='confirm-button']").click(function(){
	var password = $('#Users_password').val();

	if (password.length <8) {
		document.getElementById("crypt_password_em_").style.backgroundColor = 'red';
		document.getElementById("crypt_password_em_").style.color = 'white';

		$('#crypt_password_em_').show().text(Yii.t('js','Password too short! Use at least 8 characters.'));
		return false;
	}
	$('#passwords-form').submit();
});
JS;
Yii::app()->clientScript->registerScript('myPwdScript', $myPwdScript);


$this->pageTitle=Yii::app()->name . ' - '.Yii::t('lang','Set Password');
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'passwords-form',
	'enableClientValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
));
?>

<div class="login-wrap">

	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
		</div>
		<div class="form-group">
			<strong>
				<center><?php echo Yii::t('lang','Enter the new password');?></center>
			</strong>
		</div>
		<div class="login-form">
			<!-- BUGFIX x Chrome che riempie in automatico i campi successivi -->
			<input style="display:none">
			<input type="password" style="display:none">
			<!-- end bugfix -->

			<div class="form-group">
				<label><?php echo Yii::t('lang','Password');?></label>
				<div class="input-group">
                    <div class="input-group-addon">
                        <img style="height:25px;" src="css/images/ic_vpn_key.svg">
                    </div>
					<?php
					$model->password = null;
					echo $form->passwordField($model,'password',array('placeholder'=>'Password','class'=>'form-control','style'=>'height:45px;','onkeyup'=>"validatePassword(this.value,'crypt_password_em_');")); ?>
					<div class="invalid-feedback" id="crypt_password_em_" ></div>

				</div>
				<?php echo $form->error($model,'password',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<label><?php echo Yii::t('lang','Repeat Password');?></label>
				<div class="input-group">
                    <div class="input-group-addon">
                        <img class='text-success' style="height:25px;" src="css/images/ic_vpn_key_confirm.png">
                    </div>
					<?php echo $form->passwordField($model,'password_confirm',array('placeholder'=>Yii::t('lang','Repeat Password'),'class'=>'form-control','style'=>'height:45px;')); ?>

                </div>
				<?php echo $form->error($model,'password_confirm',array('class'=>'alert alert-danger')); ?>
			</div>
			<?php echo CHtml::submitButton(Yii::t('lang','Confirm'), array('class' => 'au-btn au-btn--block au-btn--blue m-b-20','name'=>'confirm-button')); ?>
			<?php echo Logo::footer(); ?>
		</div>

	</div>
</div>
<?php $this->endWidget(); ?>
