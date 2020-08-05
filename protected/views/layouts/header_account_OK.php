<div class="account-wrap">

	<div class="account-item clearfix">

		<?php
		if (!empty(Yii::app()->user->objUser['provider'])){
		?>
			<div class="image img-cir img-120">
				<img src="<?php echo Yii::app()->user->objUser['picture']; ?>" alt="<?php echo Yii::app()->user->objUser['picture']; ?>">
			</div>
			<img style="width:25px; top:28px; left:-15px;" class="imgProvider image img-cir img-40" src="css/images/<?php echo Yii::app()->user->objUser['provider']; ?>.svg" alt="img">
			<div class="content text-dark">
				<h5 class="name">
					<?php echo Yii::app()->user->objUser['name'].chr(32).Yii::app()->user->objUser['surname']; ?>
				</h5>
				<?php echo '@'.Yii::app()->user->objUser['username']; ?>
			</div>

		<?php }else{ ?>
			<div class="image img-cir img-120 text-success">
				<i class="fa fa-user" style="font-size: 3em;"></i>
			</div>
			<!-- <?php //echo Yii::app()->user->objUser['name'].chr(32).Yii::app()->user->objUser['surname']; ?>
			<p class="name"><?php //echo Yii::app()->user->objUser['email']; ?></p> -->
			<div class="content">
				<h5 class="name">
					<?php echo Yii::app()->user->objUser['name'].chr(32).Yii::app()->user->objUser['surname']; ?>
				</h5>
				<span class="email"><?php echo Yii::app()->user->name; ?></span>
			</div>

	<?php } ?>
	</div>

</div>
