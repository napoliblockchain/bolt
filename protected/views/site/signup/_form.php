<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */
?>
<style>
.modal-backdrop {
  display: contents;
}
</style>

<div class="form">

<?php
$this->pageTitle=Yii::app()->name . ' - '. Yii::t('lang','Signup');
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'register-form',
	'enableClientValidation'=>false,
	// 'clientOptions'=>array(
	// 	'validateOnSubmit'=>true,
	// ),
));

#echo "<pre>".print_r($_POST,true)."</pre>";
$settings = Settings::load();
$reCaptcha2PublicKey = $settings->reCaptcha2PublicKey;


$model->password_confirm = $model->password;


$script = <<<JS
    var formName = 'UsersSignup'; //da cambiare in base al Form di appartenenza
    var checkpassword = document.querySelector('#submit_button');


    //verifico password wallet immessa
    checkpassword.addEventListener('click', function(e){
        e.preventDefault();

		var password = $('#'+formName+'_password').val();
        if (password.length <8) {
    		document.getElementById("password_em_").style.color = 'red';
    		$('#password_em_').text(Yii.t('js','Password too short! Use at least 8 characters.'));
            $('#'+formName+'_password').focus();
    		return ;
    	}
        $('form').submit();
    });

JS;
Yii::app()->clientScript->registerScript('script', $script);
?>

<div class="login-wrap">
	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
		</div>

		<div class="form-group">
			<strong>
				<center><?php echo Yii::t('lang','Sign up'); ?></center>
			</strong>
		</div>
		<div class="login-form">
            <!-- BUGFIX x Chrome che riempie in automatico i campi successivi -->
			<input style="display:none">
			<input type="password" style="display:none">
			<!-- end bugfix -->

			<div class="form-group">
				<div class="input-group">
                    <div class="input-group-addon">
						<img style="height:25px;" src="css/images/ic_account_circle.svg">
                    </div>
					<?php echo $form->textField($model,'email',array('placeholder'=>'Email','class'=>'form-control','style'=>'height:45px;')); ?>
				</div>
				<?php echo $form->error($model,'email',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<div class="input-group">
                    <div class="input-group-addon">
						<img style="height:25px;" src="css/images/ic_vpn_key.svg">
                    </div>
					<?php echo $form->passwordField($model,'password',array('placeholder'=>'Password','class'=>'form-control','style'=>'height:45px;','onkeyup'=>"validatePassword(this.value,'password_em_')")); ?>
	            </div>
                <div class="invalid-feedback" id="password_em_" ></div>
				<?php echo $form->error($model,'password',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<div class="input-group">
                    <div class="input-group-addon">
						<img style="height:25px;" src="css/images/ic_vpn_key_confirm.png">
                    </div>
					<?php echo $form->passwordField($model,'password_confirm',array('placeholder'=>Yii::t('lang','Confirm Password'),'class'=>'form-control','style'=>'height:45px;')); ?>
	            </div>
			</div>
            <div class="form-group" style="text-align:left;">
                <?php
                $form->widget('application.extensions.reCaptcha2.SReCaptcha', array(
                        'name' => 'reCaptcha', //is requred
                        'siteKey' => $reCaptcha2PublicKey, //is requred
                        'model' => $form,
                        'lang' => 'it-IT',
                        //'attribute' => 'reCaptcha' //if we use model name equal attribute or customize attribute
                    )
                );
                ?>
                <?php echo $form->error($model,'reCaptcha',array('class'=>'alert alert-danger')); ?>
            </div>
            <div class='delete-serviceWorker'>
                <?php echo CHtml::submitButton(Yii::t('lang','Sign up'), array('class' => 'au-btn au-btn--block au-btn--blue m-b-20','id'=>'submit_button')); ?>
			</div>
      <?php echo Logo::footer(); ?>
		</div>
	</div>

</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
