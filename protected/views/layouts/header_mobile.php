<header class="header-mobile d-block d-lg-none">
	<div class="header-mobile__bar">
		<div class="container-fluid">
			<div class="header-mobile-inner">
				<?php Logo::header(); ?>

				<button class="jamburger jamburger--slider btn" type="button">
					<img style="max-width: 40px;" src='css/images/gift.png' />
					<span class="jamburger-box">
						<span class="jamburger-inner"></span>
					</span>
				</button>


				<button class="hamburger hamburger--slider" type="button">
					<span class="hamburger-box">
						<span class="hamburger-inner"></span>
					</span>
				</button>
			</div>
		</div>
	</div>

	<nav class="jambar-mobile">
		<div class="container-fluid">
			<ul class="jambar-mobile__list list-unstyled">
				<li class="active">
					<!-- <a href="<?php echo Yii::app()->createUrl('merchants/index');?>"> -->
					<a href="https://www.comune.napoli.it/blockchain" target="_blank">
						<i class="fas fa-industry"></i> <?php echo Yii::t('lang','Supporter activities');?> </a>
				</li>
				<li>
					<a href="https://www.comune.napoli.it/blockchain" target="_blank">
						<i class="fas fa-map-marker-alt"></i> <?php echo Yii::t('lang','Where can I earn the coupons');?> </a>
				</li>
			</ul>
		</div>
	</nav>


	<nav class="navbar-mobile">
		<div class="container-fluid">
			<ul class="navbar-mobile__list list-unstyled">
			 <?php
			if (Yii::app()->user->isGuest)
			{
			?>
						<li>
							<a class="js-arrow" href="<?php echo Yii::app()->createUrl('site/login'); ?>">
							<i class="fas fa-sign-in-alt"></i><?php echo Yii::t('lang','Sign in');?></a>
						</li>
			<?php
			}else{
			?>

			<li class="active">
				<a class="js-arrow" href="index.php?r=wallet/index">
					Wallet <i class="fa fa-home"></i></a>
			</li>

			<li>
				<a href="index.php?r=tokens/index">
					<?php echo Yii::t('lang','Transactions');?> <i class="fa fa-star"></i></a>
			</li>

			<li>
				<a href="index.php?r=contacts/index">
					 <?php echo Yii::t('lang','Contacts');?> <i class="fa fa-users"></i></a>
			</li>
			<li>
				<a href='<?php echo Yii::app()->createUrl('site/contactForm'); ?>' target="_blank">
					 <?php echo Yii::t('lang','Bug report');?> <i class="fa fa-bug"></i></a>
			</li>
			<li>
				<a href="<?php echo Yii::app()->createUrl('settings/index',array('id'=>crypt::Encrypt(Yii::app()->user->objUser['id_user'])));?>">
					 <?php echo Yii::t('lang','Settings');?> <i class="fa fa-gear"></i></a>
			</li>
			<li>
				<a href="<?php echo Yii::app()->createUrl('users/view').'&id='.crypt::Encrypt(Yii::app()->user->objUser['id_user']);?>">
						<?php echo Yii::t('lang','Profile');?> <i class="zmdi zmdi-face" style="font-size: 1.2em;"></i> </a>
			</li>
			<li>
				<div class="delete-serviceWorker">
						<a href="<?php echo Yii::app()->createUrl('site/logout');?>" >
	 					<?php echo Yii::t('lang','Logout');?> <i class="fa fa-power-off"></i></a>
				</div>
			</li>


			<?php } ?>
			</ul>
		</div>
	</nav>
</header>
