<div class="login-wrap">
	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
		</div>
		

		<div class="col-md-12">
			<div class="card border border-primary bg-teal">
				<div class="card-header text-primary">
					<strong class="card-title"><?php echo Yii::t('lang','Account activation');?></strong>
				</div>
				<div class="card-body">
					<div class="alert alert-danger">
						<?php echo Yii::t('lang','Error! The account has not been enabled.'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
