<div class="form">

<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'history-form',
	'enableAjaxValidation'=>false,
));

//richiamo tutte le funzioni javascript
include ('js_pin.php');
$visible =  WebApp::isMobileDevice();
?>

<div class='section__content section__content--p30'>
	<div class='container-fluid container-wallet'>
		<div class="row">
			<div class="col-lg-12">

				<div class="au-card au-card--no-shadow au-card--no-pad bg-overlay--semitransparent">
					<div class="card-header text-light">
						<i class="fa fa-star sync-star"></i>
						<span class="card-title"><?php echo Yii::t('lang','Transactions');?></span>
						<div class="show-rescan text-success">
							<div class="sync-blockchain float-right"></div>
							<div class="sync-difference"></div>
						</div>
					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card m-b-30">
							<?php
							Yii::import('zii.widgets.grid.CGridView');
							class SpecialGridView extends CGridView {
								public $from_address;
							}
							$this->widget('SpecialGridView', array(
								'id' => 'tokens-grid',
								'hideHeader' => true,
								'htmlOptions' => array('class' => 'table table-borderless  table-data4 table-wallet text-light'),
								  'dataProvider'=>$modelc->search(),
								  'from_address'   => $from_address,          // your special parameter
									'pager'=>array(
								        //'header'=>'Go to page:',
								        //'cssFile'=>Yii::app()->theme->baseUrl
										'cssFile'=>Yii::app()->request->baseUrl."/css/yiipager.css",
								        'prevPageLabel'=>'<',
								        'nextPageLabel'=>'>',
								        'firstPageLabel'=>'<<',
								        'lastPageLabel'=>'>>',
								    ),

								'columns' => array(
									array(
										'type'=>'raw',
										'name'=>'',
										'value'=>'WebApp::typeTransaction($data->type)',
										'htmlOptions'=>array('style'=>'width:1px;'),

											),

									array(
										'name'=>'',
										'type'=>'raw',
										//'value' => 'CHtml::link(CHtml::encode(date("d/m/Y H:i:s",$data->invoice_timestamp)), Yii::app()->createUrl("wallet/details")."&id=".CHtml::encode(crypt::Encrypt($data->id_token)))',
										'value' => 'CHtml::link(WebApp::dateLN($data->invoice_timestamp,$data->id_token), Yii::app()->createUrl("tokens/view",["id"=>crypt::Encrypt($data->id_token)]) )',
										//'value' => 'CHtml::link(CHtml::encode(date("Y-m-d H:i:s",$data->invoice_timestamp)), Yii::app()->createUrl("tokens/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_token)))',
										//'value' => 'crypt::Encrypt($data->id_token)<br>date("d/m/Y H:i:s",$data->invoice_timestamp)',


											),
									array(
										'type'=>'raw',
										'name'=>'',
										'value'=>'CHtml::link(WebApp::walletStatus($data->status), Yii::app()->createUrl("tokens/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_token)))',
										'cssClassExpression' => '( $data->status == "sent" ) ? "denied" : (( $data->status == "complete" ) ? "process" : "desc incorso")',
											),
									array(
										'type'=>'raw',
													'name'=>'',
										'value'=>'WebApp::typePrice($data->token_price,(($data->from_address == $this->grid->from_address) ? "sent" : "received"))',
										'htmlOptions'=>array('style'=>'text-align:center;'),
											),

									// [
									// 	'type'=>'raw',
											//     'name'=>'fiat_price',
									// 	'value'=>'$data->fiat_price',
									// 	'visible'=>!$visible,
											// ],
									//
									// [
									// 	'type'=>'raw',
											//     'name'=>'rate',
									// 	'value'=>'$data->rate',
									// 	'visible'=>!$visible,
											// ],
									[
										'type'=>'raw',
										'name'=>'to_address',
										'value'=>'CHtml::link($data->to_address, Yii::app()->createUrl("tokens/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_token)))',
										'visible'=>!$visible,
									],

									array(
										'type'=>'raw',
										'name'=>'',
										'value'=>'WebApp::isConfirmedLock('.$actualBlockNumberDec.',$data->blocknumber)',
										'htmlOptions'=>array('style'=>'width:50px;'),

									),
									// array(
									// 	'type'=>'raw',
									// 	'name'=>'',
									// 	'value'=>'Yii::app()->controller->isConfirmedNumber('.$actualBlockNumberDec.',$data->blocknumber)',
									// 	'htmlOptions'=>array('style'=>'width:1px;'),
									// ),

								)
							));
							?>
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
