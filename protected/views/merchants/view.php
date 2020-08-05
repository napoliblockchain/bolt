<?php
$viewName = 'Commerciante';

$linkToMerchantAddress = '#';
$disabled = 'disabled';

if ($merchantAddress <> '0x0'){
	$linkToMerchantAddress = Yii::app()->createUrl('wallet/index',array('linkToAvatarAddress'=>$merchantAddress));
	$disabled = '';
}
include ('js_pin.php');
?>
<div class='section__content section__content--p30'>
<div class='container-fluid'>
	<div class="row">
		<div class="col-lg-12">
			<div class="au-card au-card--no-shadow au-card--no-pad bg-overlay--semitransparent">
				<div class="card-header text-light">
					<i class="fas fa-industry"></i>
					<span class="card-title">Dettagli <?php echo $viewName;?></span>
				</div>
				<div class="card-body">
						<div class="table-responsive table--no-card m-b-30">
						<?php $this->widget('zii.widgets.CDetailView', array(
						'htmlOptions' => array('class' => 'table text-light'),
							'data'=>$model,
							'attributes'=>array(
								//'id_merchant',
								//'alias',

								'denomination',
								'address',
								//'cap',
								array(
						            'label'=>'Città',
						            'value'=>$model->cap.' - '.
											ComuniItaliani::model()->findByPk($model->city)->citta.
												' ('. ComuniItaliani::model()->findByPk($model->city)->sigla.')'

						        ),
								'county',
								[
									'label'=>'Regole Token',
									'value'=>'link a napoliblockchain.it/merchants/id_merchant',
									]



							),
						));
						?>
					</div>
				</div>
				<div class="card-footer">

					<a href='<?php echo $linkToMerchantAddress; ?>'>
						<button <?php echo $disabled; ?> type="button" class="btn alert-success text-light <?php echo $disabled; ?>" style="min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;">
								<i class="fa fa-upload"></i> <?php echo Yii::t('lang','Send');?>
						</button>
					</a>


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
		<div class="modal-content alert-dark text-light ">
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
					<button type="button" disabled="disabled" class="btn btn-primary disabled" id="pinRequestButton"><?php echo Yii::t('lang','Confirm');?></button>
				</div>
			</div>
		</div>
	</div>
</div>
