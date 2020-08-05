<div class="form">
<?php
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('lang','Contact Us');
$this->breadcrumbs=array(
	'Contact',
);
$settings = Settings::load();
$reCaptcha2PublicKey = $settings->reCaptcha2PublicKey;

?>



<?php if(Yii::app()->user->hasFlash('contact')): ?>

	<div class="login-wrap">
		<div class="login-content">
			<div class="login-logo">
				<?php Logo::login(); ?>
				<h1><?php echo Yii::t('lang','Contact Us'); ?></h1>
			</div>

			<div class="login-form">
				<div class="form-group">
					<center>
						<?php echo Yii::app()->user->getFlash('contact'); ?>
					</center>
				</div>

				<div class="form-group">
					<center>
						<?php echo CHtml::submitButton(Yii::t('lang','Close'), array('onclick'=>'js:self.close();','class' => 'btn btn-primary')); ?>
					</center>
				</div>

				<?php echo Logo::footer(); ?>
			</div>
		</div>
	</div>


<?php else: ?>





<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'contact-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
	 'htmlOptions' => array('enctype' => 'multipart/form-data'),
)); ?>

<div class="login-wrap">
	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
			<h1><?php echo Yii::t('lang','Contact Us'); ?></h1>
			<p class="text-success">
			<?php echo Yii::t('lang','If you have software issues or other questions, please fill out the following form to contact us. Thank you.'); ?>
			</p>
		</div>

		<div class="login-form">
			<p class="note"><i><?php echo Yii::t('lang','Fields with <span class="required">*</span> are required.'); ?></i></p>
			<div class="sufee-alert alert with-close alert-warning alert-dismissible fade show" style="display:<?php echo strlen($form->errorSummary($model)) == 156 ? "none;" : "" ; ?>;">
				<?php echo $form->errorSummary($model); ?>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>


			<div class="form-group">
				<?php echo $form->labelEx($model,'name'); ?>
				<?php echo $form->textField($model,'name',array('placeholder'=>Yii::t('model','Name'),'class'=>'form-control')); ?>
				<?php echo $form->error($model,'name',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'email'); ?>
				<?php echo $form->textField($model,'email',array('placeholder'=>Yii::t('model','Email address'),'class'=>'form-control')); ?>
				<?php echo $form->error($model,'email',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'subject'); ?>
				<?php echo $form->textField($model,'subject',array('placeholder'=>Yii::t('model','Subject'),'class'=>'form-control')); ?>
				<?php echo $form->error($model,'subject',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'body'); ?>
				<?php echo $form->textArea($model,'body',array('placeholder'=>Yii::t('model','Write message'),'class'=>'form-control','rows'=>6, 'cols'=>50)); ?>
				<?php echo $form->error($model,'body',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'attach'); ?>
				<?php echo $form->fileField($model,'attach',array('placeholder'=>Yii::t('model','Attach image'),'class'=>'form-control')); ?>
				<?php echo $form->error($model,'attach',array('class'=>'alert alert-danger')); ?>
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


			<?php echo CHtml::submitButton(Yii::t('lang','Submit'), array('class' => 'au-btn au-btn--block au-btn--blue m-b-20')); ?>
			<?php echo Logo::footer(); ?>
		</div>
	</div>
</div>


<?php $this->endWidget(); ?>

</div><!-- form -->

<?php endif; ?>
