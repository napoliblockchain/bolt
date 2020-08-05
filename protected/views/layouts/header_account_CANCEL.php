<div class="account-wrap">

	<div class="account-item clearfix">

		<?php
		if (!empty(Yii::app()->user->objUser['provider'])){
		?>
			<div class="image img-cir img-40">
				<img src="<?php echo Yii::app()->user->objUser['picture']; ?>" alt="<?php echo Yii::app()->user->objUser['picture']; ?>">
			</div>
		
			<!-- <img style="width:25px; top:28px; left:-15px;" class="imgProvider image img-cir img-40" src="css/images/<?php echo Yii::app()->user->objUser['provider']; ?>.svg" alt="img"> -->


		<?php }else{ ?>
			<div class="image img-cir img-40 text-success">
				<i class="fa fa-user" style="font-size: 1em;"></i>
			</div>
	<?php } ?>
	</div>

</div>
