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

?>

<div class="login-wrap">
	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
		</div>

		<div class="login-form">
            <div class="card border border-light bg-transparent">
				<div class="card-header text-light">
                    <strong>
        				<center><?php echo Yii::t('lang','Signup'); ?></center>
        			</strong>
				</div>
				<div class="card-body">
					<div>
                        <center>
						<div style="text-align: justify;">
							<?php echo Yii::t('lang','Your registration request has been confirmed.');?><br>
							<?php echo Yii::t('lang','You will receive an email to confirm your subscription.');?>
						</div>
					    </center>
					</div>
				</div>
				<div class="card-footer">
					<a class="js-arrow" href="<?php echo Yii::app()->createUrl('site/login'); ?>">
						<button class="btn btn-primary">
							<i class="fas fa-sign-out-alt"></i>  LOGIN
						</button>
					</a>
				</div>
			</div>

			<?php echo Logo::footer(); ?>
		</div>
	</div>

</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
