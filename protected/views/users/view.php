<?php
//richiamo tutte le funzioni javascript
include ('js_pin.php');

$modifyURL = Yii::app()->createUrl('users/update').'&id='.crypt::Encrypt($model->id_user);
?>
<div class='section__content section__content--p30'>
<div class='container-fluid'>

	<div class="row">
		<div class="col-lg-6">
			<div class="au-card au-card--no-shadow au-card--no-pad bg-overlay--semitransparent">
				<div class="card-header ">
					<i class="fa fa-user"></i>
					<span class="card-title "><?php echo Yii::t('lang','Profile');?></span>
				</div>
				<div class="card-body ">
					<div class="table-responsive table--no-card m-b-40">
						<?php $this->widget('zii.widgets.CDetailView', array(
							'htmlOptions' => array('class' => 'table table-borderless  table-earning text-light'),
							'data'=>$model,
							'attributes'=>array(
								[
									'label'=>Yii::t('model','Image'),
									'type'=>'raw',
									'value'	=>'<div class="image img-cir img-40">
										<img src="'.Yii::app()->user->objUser['picture'].'" alt="'.Yii::app()->user->objUser['picture'].'">
									</div>',
								],
								[
									'label'=>Yii::t('model','email'),
									'value'=>$social->email,
									'visible'=>($social->oauth_provider <> '' ? false : true)

									],
								[
									'label'=>Yii::t('model','First name'),
									'value'=>$social->first_name,
								],
								[
									'label'=>Yii::t('model','Last name'),
									'value'=>$social->last_name,
								],
								[
									'label'=>Yii::t('model','Username'),
									'value'=>$social->username,
								],
								// [
								// 	'label'=>'Telegram ID',
								// 	'value'=>$model->telegram_id,
								// 	'visible'=>($model->telegram_id == 0 ? false : true)
								//
								// ],

							),
						));
						?>
					</div>
					<?php if ($social->oauth_provider == 'email' ){ ?>
					<div class="row">
						<div class="col-lg-6">
						<a href="<?php echo $modifyURL;?>">
							<button type="button" class="btn alert-warning text-light" style="min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;">
								<i class="fa fa-edit"></i> <?php echo Yii::t('lang','change');?>
							</button>
						</a>
					</div>
					</div>
				<?php } ?>
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
