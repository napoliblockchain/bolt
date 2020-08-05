<div class="form">

<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'merchants-form',
	'enableAjaxValidation'=>false,
));

//richiamo tutte le funzioni javascript
include ('js_pin.php');
$visible =  WebApp::isMobileDevice();

$criteria=new CDbCriteria;
  $criteria->compare('deleted',0,false);

?>

<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fas fa-industry"></i>
						<span class="card-title">Lista commercianti</span>
					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card m-b-40">
							<?php
							$this->widget('zii.widgets.grid.CGridView', array(
								//'htmlOptions' => array('class' => 'table table-borderless table-striped table-earning'),
								// 'htmlOptions' => array('class' => 'table table-borderless table-data3 table-earning '),
								//'htmlOptions' => array('class' => 'table table-wallet'),
                				'htmlOptions' => array(
									'class' => 'table table-borderless  table-data4 table-wallet text-light',
								),
							    'dataProvider'=>$modelc->search(),
			                  	'filter'=>$modelc,
			                  	'enablePagination'  => true,
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
							            'name'=>'denomination',
										'type'=>'raw',
										'value' => 'CHtml::link(CHtml::encode($data->denomination), Yii::app()->createUrl("merchants/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_merchant)))',
                    					'filter'=>CHtml::listData(Merchants::model()->orderByDenomination()->findAll($criteria),'denomination','denomination'),
							        ),
									[
	                                	'value' =>''
	                                ]
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
<?php $this->endWidget(); ?>
</div><!-- form -->
