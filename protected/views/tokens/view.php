<div class="form">
<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'details-form',
	'enableAjaxValidation'=>false,
));

//richiamo tutte le funzioni javascript
include ('js_pin.php');


// il block explorer
$explorer = false;
$settings=Settings::load();
if (isset($settings->poa_blockexplorer) && $settings->poa_blockexplorer != ''){
	$explorer = $settings->poa_blockexplorer;
}
?>
<div class='section__content section__content--p30'>
	<div class='container-fluid '>
		<div class="row" id="details">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad bg-overlay--semitransparent">
					<div class="card-header text-light">
						<i class="fa fa-star sync-star"></i>
						<span class="card-title"><?php echo Yii::t('lang','Transaction details');?></span>
					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card m-b-30">
							<?php $this->widget('zii.widgets.CDetailView', array(
								//'htmlOptions' => array('class' => 'table table-borderless table-striped'),
								'htmlOptions' => array('class' => 'table text-light'),
								//'htmlOptions' => array('class' => 'table table-data4 f05'),
								'data'=>$model,
								'attributes'=>array(
									// array(
									// 	'label'=>'Codice transazione',
									// 	'value'=>crypt::Encrypt($model->id_token),
									// ),
									['name'=>Yii::t('model','id'),
										'type'=>'raw',
										'value'=>crypt::Encrypt($model->id_token),
										],
									array(
										'type'=>'raw',
										'name'=>Yii::t('model','status'),
										//'value'=>WebApp::walletStatus($model->status),
										'value' => ( $model->status == "new" || $model->status == 'sending') ?
										(
											CHtml::ajaxLink(
											    WebApp::walletStatus($model->status),          // the link body (it will NOT be HTML-encoded.)
											    array('blockchain/checkTransaction'."&id=".CHtml::encode(crypt::Encrypt($model->id_token))), // the URL for the AJAX request. If empty, it is assumed to be the current URL.
											    array(
											        'update'=>'.btn-outline-dark',
											        'beforeSend' => 'function() {
											           $(".btn-outline-dark").text(Yii.t("js","Checking..."));
											        }',
											        'complete' => 'function() {
													  	location.reload(true);
											        }',
											    )
											)
										) : WebApp::walletStatus($model->status),
									),
									array(
										'label'=>Yii::t('model','Date'),
										'value'=>WebApp::dateView($model->invoice_timestamp),
										'type'=>'raw',
									),
									array(
										'label'=>Yii::t('model','Price'),
										//'value'=>$model->token_price,
										'type'=>'raw',
										'value'=>WebApp::typePrice($model->token_price,(($model->from_address == $from_address) ? "sent" : "received")),

									),
									array(
										'label'=>Yii::t('model','From'),
										'type'=>'raw',

										'value' => ($explorer == false ? WebApp::fromUser($model->from_address) :
											CHtml::link(
												WebApp::fromUser($model->from_address),
												$explorer . WebApp::isEthAddress($model->from_address),
												array("target"=>"_blank")
											)
										)
										
									),
									array(
										'label'=>Yii::t('model','To'),
										'type'=>'raw',

										'value' => ($explorer == false ? WebApp::fromUser($model->to_address) :
											CHtml::link(
												WebApp::fromUser($model->to_address),
												$explorer . WebApp::isEthAddress($model->to_address),
												array("target"=>"_blank")
											)
										)
									),
									array(
										'label'=>Yii::t('model','Tx Hash'),
										'type'=>'raw',
										'value' => ($explorer == false ? $model->txhash :
											CHtml::link(
												CHtml::encode($model->txhash),
												$explorer . WebApp::isEthAddress($model->txhash),
												array("target"=>"_blank")
											)
										)
									),
									array(
										'type'=>'raw',
										'label'=>Yii::t('model','Confirms'),
										'value'=>WebApp::isConfirmedLock($actualBlockNumberDec,$model->blocknumber),
									),
									array(
										//'type'=>'raw',
										'label'=>Yii::t('model','Message'),
										'value'=>(null !== TokensMemo::model()->findByAttributes(['id_token'=>$model->id_token]) ? crypt::Decrypt(TokensMemo::model()->findByAttributes(['id_token'=>$model->id_token])->memo) : '') ,
									),

								),
							)); ?>
						</div>
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
<?php $this->endWidget(); ?>
</div><!-- form -->
