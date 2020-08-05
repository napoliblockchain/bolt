<?php
//richiamo tutte le funzioni javascript
include ('js_pin.php');

?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-md-12">
					<div class="au-card au-card--no-shadow au-card--no-pad bg-overlay--semitransparent">
					<div class="card-header ">
						<span class="card-title "><?php echo Yii::t('lang','Remove 2-factor authentication');?></span>
					</div>
					<div class="card-body card-block">
						<?php $this->renderPartial('_form2faRemove', array(
							'model'=>$model,
						)); ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo Logo::footer(); ?>
	</div>
</div>
<!-- RICHIESTA PIN -->
<div class="modal fade " id="pinRequestModal" tabindex="-1" role="dialog" aria-labelledby="pinRequestModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-sm" role="document">
		<div class="modal-content alert-dark text-light " style="min-width:360px;">
			<div class="modal-header">
				<h5 class="modal-title" id="pinRequestModalLabel"><?php echo Yii::t('lang','PIN Request');?></h5>
			</div>
			<div class="modal-body ">
				<center>
					<input type='hidden' id='pin_password' class='form-control' readonly="readonly"/>
                    <input type='hidden' id='pin_password_confirm' class='form-control' readonly="readonly"/>
                </center>
                <div class="pin-confirm-numpad pin-newframe-numpad"></div>
			</div>
			<div class="modal-footer">
				<div class="form-group">
					<button type="button" disabled="disabled" class="btn alert-primary disabled text-light" id="pinRequestButton" style="min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;">
						<i class="fa fa-thumbs-up"></i> <?php echo Yii::t('lang','Confirm');?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
