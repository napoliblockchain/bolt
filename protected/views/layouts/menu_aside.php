<aside class="menu-sidebar d-none d-lg-block">
	<div  class="logo">
		<a href="<?php echo Yii::app()->createUrl('site/index'); ?>">
			<?php Logo::header(); ?>
		</a>
	</div>
	<div id="page-vesuvio"></div>
	<div class="menu-sidebar__content js-scrollbar1">
		<nav class="navbar-sidebar">
			<?php
			if (Yii::app()->user->isGuest)
			{
			?>
				<ul class="list-unstyled navbar__list">
					<li class="active">
						<a class="js-arrow" href="<?php echo Yii::app()->createUrl('site/login'); ?>">
							<i class="fas fa-sign-in-alt"></i><?php echo Yii::t('lang','Sign in');?></a>
					</li>
				</ul>
			<?php
			}else{
				?>
				<ul class="list-unstyled navbar__list">
					<li class="active">
						<a class="js-arrow" href="index.php?r=wallet/index">
							 <i class="fa fa-home"></i>Wallet</a>
					</li>

					<li>
						<a href="index.php?r=tokens/index">
							 <i class="fa fa-star"></i><?php echo Yii::t('lang','Transactions');?></a>
					</li>

					<li>
						<a href="index.php?r=contacts/index">
							 <i class="fa fa-users"></i><?php echo Yii::t('lang','Contacts');?></a>
					</li>
					<li>
						<!-- <a href="<?php echo Yii::app()->createUrl('merchants/index');?>"> -->
						<a href="https://www.comune.napoli.it/blockchain" target="_blank">
							<i class="fas fa-industry"></i><?php echo Yii::t('lang','Supporter activities');?></a>
					</li>
					<li>
						<a href="<?php echo Yii::app()->createUrl('site/contactForm'); ?>" target="_blank">
							 <i class="fa fa-bug"></i><?php echo Yii::t('lang','Bug report');?></a>
					</li>
					<li>
						<a href="<?php echo Yii::app()->createUrl('settings/index',array('id'=>crypt::Encrypt(Yii::app()->user->objUser['id_user'])));?>">
							 <i class="fa fa-gear"></i><?php echo Yii::t('lang','Settings');?> </a>
					</li>
					<li>
						<a href="<?php echo Yii::app()->createUrl('users/view').'&id='.crypt::Encrypt(Yii::app()->user->objUser['id_user']);?>">
								<i class="zmdi zmdi-face" style="font-size: 1.2em;"></i><?php echo Yii::t('lang','Profile');?></a>
					</li>
					<li>
						<div class="delete-serviceWorker">
								<a href="<?php echo Yii::app()->createUrl('site/logout');?>" >
								<i class="fa fa-power-off"></i><?php echo Yii::t('lang','Logout');?> </a>
						</div>
					</li>

				</ul>

			<?php } ?>
		</nav>
	</div>
</aside>
