<?php
/* @var $this UsersController */
/* @var $model Users */

?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-7">
					<div class="au-card au-card--no-shadow au-card--no-pad bg-overlay--semitransparent">
					<div class="card-header">
						Balance <?php echo $balances['address']; ?>
					</div>
					<div class="card-body card-block">
            <div class="btn btn-primary"  style="width:100%;">
              <h2>
              <strong><i class="zmdi zmdi-star-outline text-light"></i></strong>
              <strong><span class="text-light"><?php echo $balances['token']; ?></span></strong>
              </h2>
            </div>

            <div class="btn btn-primary"  style="width:100%; ">
              <h2>
              <i class="fab fa-ethereum text-light"></i>
              <small><span class="text-light"><?php echo $balances['gas']; ?></span></small>
              </h2>
            </div>
					</div>
				</div>
			</div>
		</div>
		<?php echo Logo::footer(); ?>
	</div>
</div>
